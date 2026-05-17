<?php

namespace App\Http\Controllers\Installer;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class InstallerInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'all');

        $query = Invoice::where('created_by', Auth::id())
            ->orderByDesc('created_at');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $invoices = $query->paginate(20);

        return view('installer.invoices.index', compact('invoices', 'status'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name'    => 'required|string|max:255',
            'customer_email'   => 'nullable|email|max:255',
            'customer_phone'   => 'nullable|string|max:50',
            'customer_address' => 'nullable|string|max:500',
            'billing_address'  => 'nullable|string|max:500',
            'due_date'         => 'nullable|date',
            'tax_rate'         => 'nullable|numeric|min:0|max:100',
            'notes'            => 'nullable|string|max:5000',
        ]);

        // Auto-generate invoice number: II-XX-0001
        $prefix = 'II-' . strtoupper(substr(Auth::user()->name, 0, 2)) . '-';
        $last = Invoice::where('invoice_number', 'like', $prefix . '%')->orderByDesc('invoice_number')->first();
        $next = $last ? (intval(substr($last->invoice_number, strlen($prefix))) + 1) : 1;
        $invoiceNumber = $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);

        $invoice = Invoice::create(array_merge($validated, [
            'invoice_number' => $invoiceNumber,
            'status'         => 'draft',
            'tax_rate'       => $validated['tax_rate'] ?? 0,
            'subtotal'       => 0,
            'tax_amount'     => 0,
            'total'          => 0,
            'amount_paid'    => 0,
            'balance_due'    => 0,
            'created_by'     => Auth::id(),
        ]));

        if ($request->ajax()) {
            return response()->json(['success' => true, 'invoice' => $invoice]);
        }

        return redirect()->route('installer.invoices.index')->with('success', 'Invoice created successfully.');
    }

    public function show($id)
    {
        $invoice = Invoice::where('created_by', Auth::id())->findOrFail($id);

        $items = [];
        try {
            $items = $invoice->items()->get()->toArray();
        } catch (\Exception $e) {}

        return response()->json([
            'invoice' => $invoice,
            'items' => $items,
        ]);
    }

    public function addItem(Request $request, $id)
    {
        $invoice = Invoice::where('created_by', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'qty'         => 'required|numeric|min:0.01',
            'unit_price'  => 'required|numeric|min:0',
        ]);

        $total = round($validated['qty'] * $validated['unit_price'], 2);
        $sortOrder = ($invoice->items()->max('sort_order') ?? 0) + 1;

        $item = InvoiceItem::create([
            'invoice_id'  => $invoice->id,
            'description' => $validated['description'],
            'qty'         => $validated['qty'],
            'unit_price'  => $validated['unit_price'],
            'total'       => $total,
            'sort_order'  => $sortOrder,
        ]);

        $this->recalculateTotals($invoice);

        return response()->json(['success' => true, 'item' => $item, 'invoice' => $invoice->fresh()]);
    }

    public function removeItem($id, $itemId)
    {
        $invoice = Invoice::where('created_by', Auth::id())->findOrFail($id);
        $item = InvoiceItem::where('invoice_id', $invoice->id)->findOrFail($itemId);
        $item->delete();

        $this->recalculateTotals($invoice);

        return response()->json(['success' => true, 'invoice' => $invoice->fresh()]);
    }

    public function sendToCustomer(Request $request, $id)
    {
        $invoice = Invoice::where('created_by', Auth::id())->findOrFail($id);

        if (empty($invoice->customer_email)) {
            return response()->json(['error' => 'No customer email on this invoice.'], 422);
        }

        $items = $invoice->items()->get();
        $installerName = Auth::user()->name;

        Mail::send([], [], function ($message) use ($invoice, $items, $installerName) {
            $html = "<h2>Invoice {$invoice->invoice_number}</h2>"
                . "<p>From: {$installerName}</p>"
                . "<table border='1' cellpadding='6' cellspacing='0'><tr><th>Description</th><th>Qty</th><th>Rate</th><th>Amount</th></tr>";
            foreach ($items as $item) {
                $html .= "<tr><td>{$item->description}</td><td>{$item->qty}</td><td>\${$item->unit_price}</td><td>\${$item->total}</td></tr>";
            }
            $html .= "</table>"
                . "<p>Subtotal: \${$invoice->subtotal}<br>Tax: \${$invoice->tax_amount}<br><strong>Total: \${$invoice->total}</strong></p>";
            if ($invoice->due_date) {
                $html .= "<p>Due Date: {$invoice->due_date}</p>";
            }

            $message->to($invoice->customer_email, $invoice->customer_name)
                ->subject("Invoice {$invoice->invoice_number} from {$installerName}")
                ->html($html);
        });

        if ($invoice->status === 'draft') {
            $invoice->update(['status' => 'sent']);
        }

        return response()->json(['success' => true, 'message' => 'Invoice sent.']);
    }

    private function recalculateTotals(Invoice $invoice)
    {
        $subtotal = InvoiceItem::where('invoice_id', $invoice->id)->sum('total');
        $taxAmount = round($subtotal * ($invoice->tax_rate / 100), 2);
        $total = $subtotal + $taxAmount;

        $invoice->update([
            'subtotal'    => $subtotal,
            'tax_amount'  => $taxAmount,
            'total'       => $total,
            'balance_due' => $total - $invoice->amount_paid,
        ]);
    }
}
