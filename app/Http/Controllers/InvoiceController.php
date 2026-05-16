<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Quote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'all');

        $query = Invoice::with(['items', 'creator'])
            ->orderByDesc('created_at');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $invoices = $query->paginate(20);

        // Quotes available for creating invoice from quote
        $quotes = Quote::where('status', 'sent')
            ->orderByDesc('created_at')
            ->get();

        return view('invoices.index', compact('invoices', 'status', 'quotes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'customer_address' => 'nullable|string|max:500',
            'billing_address' => 'nullable|string|max:500',
            'due_date' => 'nullable|date',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
            'from_quote' => 'nullable|exists:elitevw_sales_quotes,id',
        ]);

        // Generate invoice number
        $lastInvoice = Invoice::withTrashed()->orderByDesc('id')->first();
        $nextNum = $lastInvoice ? (int) substr($lastInvoice->invoice_number, 4) + 1 : 1;
        $invoiceNumber = 'INV-' . str_pad($nextNum, 5, '0', STR_PAD_LEFT);

        $invoice = Invoice::create([
            'invoice_number' => $invoiceNumber,
            'quote_id' => $validated['from_quote'] ?? null,
            'customer_name' => $validated['customer_name'],
            'customer_email' => $validated['customer_email'] ?? null,
            'customer_phone' => $validated['customer_phone'] ?? null,
            'customer_address' => $validated['customer_address'] ?? null,
            'billing_address' => $validated['billing_address'] ?? null,
            'status' => 'draft',
            'subtotal' => 0,
            'tax_rate' => $validated['tax_rate'] ?? 0,
            'tax_amount' => 0,
            'total' => 0,
            'amount_paid' => 0,
            'balance_due' => 0,
            'due_date' => $validated['due_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'created_by' => Auth::id(),
        ]);

        // Copy items from quote if specified
        if (!empty($validated['from_quote'])) {
            $quote = Quote::with('items')->find($validated['from_quote']);
            if ($quote) {
                $subtotal = 0;
                foreach ($quote->items as $i => $item) {
                    $qty = $item->qty ?: 1;
                    $unitPrice = $item->getRawOriginal('price') ?: $item->getRawOriginal('total');
                    $itemTotal = $qty * $unitPrice;
                    $subtotal += $itemTotal;

                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'description' => $item->description . ($item->series_type ? ' - ' . $item->series_type : ''),
                        'qty' => $qty,
                        'unit_price' => $unitPrice,
                        'total' => $itemTotal,
                        'sort_order' => $i,
                    ]);
                }

                $taxRate = $invoice->tax_rate;
                $taxAmount = round($subtotal * ($taxRate / 100), 2);
                $total = $subtotal + $taxAmount;

                $invoice->update([
                    'subtotal' => $subtotal,
                    'tax_amount' => $taxAmount,
                    'total' => $total,
                    'balance_due' => $total,
                ]);
            }
        }

        return redirect()->route('admin.invoices.index')
            ->with('success', "Invoice {$invoiceNumber} created successfully.");
    }

    public function show($id)
    {
        $invoice = Invoice::with(['items', 'creator', 'quote'])->findOrFail($id);

        return response()->json([
            'invoice' => $invoice,
            'items' => $invoice->items,
            'creator' => $invoice->creator,
        ]);
    }

    public function update(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);

        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'customer_address' => 'nullable|string|max:500',
            'billing_address' => 'nullable|string|max:500',
            'due_date' => 'nullable|date',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:draft,sent,paid,partial,overdue,cancelled',
        ]);

        $invoice->update($validated);

        // Recalculate totals if tax rate changed
        $this->recalculateTotals($invoice);

        return redirect()->route('admin.invoices.index')
            ->with('success', "Invoice {$invoice->invoice_number} updated.");
    }

    public function addItem(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);

        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'qty' => 'required|numeric|min:0.01',
            'unit_price' => 'required|numeric|min:0',
        ]);

        $itemTotal = round($validated['qty'] * $validated['unit_price'], 2);

        $maxSort = $invoice->items()->max('sort_order') ?? 0;

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'description' => $validated['description'],
            'qty' => $validated['qty'],
            'unit_price' => $validated['unit_price'],
            'total' => $itemTotal,
            'sort_order' => $maxSort + 1,
        ]);

        $this->recalculateTotals($invoice);

        return redirect()->route('admin.invoices.index')
            ->with('success', 'Item added to invoice.');
    }

    public function removeItem($id, $itemId)
    {
        $invoice = Invoice::findOrFail($id);
        $item = InvoiceItem::where('invoice_id', $invoice->id)->findOrFail($itemId);
        $item->delete();

        $this->recalculateTotals($invoice);

        return response()->json(['success' => true]);
    }

    public function recordPayment(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'nullable|string|max:50',
            'payment_date' => 'nullable|date',
            'payment_note' => 'nullable|string',
        ]);

        $newAmountPaid = $invoice->amount_paid + $validated['amount'];
        $newBalance = $invoice->total - $newAmountPaid;

        $status = $invoice->status;
        if ($newBalance <= 0) {
            $status = 'paid';
            $newBalance = 0;
        } elseif ($newAmountPaid > 0) {
            $status = 'partial';
        }

        $invoice->update([
            'amount_paid' => $newAmountPaid,
            'balance_due' => $newBalance,
            'status' => $status,
            'paid_date' => $status === 'paid' ? ($validated['payment_date'] ?? now()) : $invoice->paid_date,
        ]);

        // Append payment note to invoice notes
        if (!empty($validated['payment_note']) || !empty($validated['payment_method'])) {
            $paymentInfo = '[Payment ' . now()->format('M d, Y') . '] '
                . '$' . number_format($validated['amount'], 2)
                . ($validated['payment_method'] ? ' via ' . $validated['payment_method'] : '')
                . ($validated['payment_note'] ? ' - ' . $validated['payment_note'] : '');

            $invoice->update([
                'notes' => ($invoice->notes ? $invoice->notes . "\n" : '') . $paymentInfo,
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Payment recorded.']);
    }

    public function sendToCustomer(Request $request, $id)
    {
        $invoice = Invoice::with('items')->findOrFail($id);

        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        try {
            $html = view('emails.invoice', ['invoice' => $invoice])->render();

            Mail::html($html, function ($message) use ($validated, $invoice) {
                $message->to($validated['email'])
                    ->subject('Invoice #' . $invoice->invoice_number . ' from VIP Windows');
            });

            if ($invoice->status === 'draft') {
                $invoice->update(['status' => 'sent']);
            }

            return response()->json(['success' => true, 'message' => 'Invoice sent.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to send: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->delete();

        return redirect()->route('admin.invoices.index')
            ->with('success', "Invoice {$invoice->invoice_number} deleted.");
    }

    private function recalculateTotals(Invoice $invoice)
    {
        $invoice->refresh();
        $subtotal = $invoice->items()->sum('total');
        $taxAmount = round($subtotal * ($invoice->tax_rate / 100), 2);
        $total = $subtotal + $taxAmount;
        $balanceDue = $total - $invoice->amount_paid;

        $invoice->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total' => $total,
            'balance_due' => max(0, $balanceDue),
        ]);
    }
}
