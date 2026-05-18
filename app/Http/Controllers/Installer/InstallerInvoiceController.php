<?php

namespace App\Http\Controllers\Installer;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\VipQuote as Quote;
use App\Models\VipQuoteItem as QuoteItem;
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
            // Line items (optional, submitted as arrays)
            'items'              => 'nullable|array',
            'items.*.description' => 'required|string|max:255',
            'items.*.qty'         => 'required|numeric|min:0.01',
            'items.*.unit_price'  => 'required|numeric|min:0',
        ]);

        // Auto-generate invoice number: II-XX-0001
        $prefix = 'II-' . strtoupper(substr(Auth::user()->name, 0, 2)) . '-';
        $last = Invoice::where('invoice_number', 'like', $prefix . '%')->orderByDesc('invoice_number')->first();
        $next = $last ? (intval(substr($last->invoice_number, strlen($prefix))) + 1) : 1;
        $invoiceNumber = $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);

        $lineItems = $validated['items'] ?? [];
        unset($validated['items']);

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

        // Create line items if provided
        $sortOrder = 0;
        foreach ($lineItems as $item) {
            if (!empty($item['description']) && !empty($item['qty'])) {
                $sortOrder++;
                InvoiceItem::create([
                    'invoice_id'  => $invoice->id,
                    'description' => $item['description'],
                    'qty'         => $item['qty'],
                    'unit_price'  => $item['unit_price'] ?? 0,
                    'total'       => round(($item['qty'] ?? 0) * ($item['unit_price'] ?? 0), 2),
                    'sort_order'  => $sortOrder,
                ]);
            }
        }

        if ($sortOrder > 0) {
            $this->recalculateTotals($invoice);
        }

        if ($request->ajax()) {
            return response()->json(['success' => true, 'invoice' => $invoice->fresh()]);
        }

        return redirect()->route('installer.invoices.index')->with('success', 'Invoice created successfully.');
    }

    public function createFromQuote($quoteId)
    {
        $quote = Quote::where('entered_by', Auth::user()->name)->findOrFail($quoteId);

        // Generate invoice number
        $prefix = 'II-' . strtoupper(substr(Auth::user()->name, 0, 2)) . '-';
        $last = Invoice::where('invoice_number', 'like', $prefix . '%')->orderByDesc('invoice_number')->first();
        $next = $last ? (intval(substr($last->invoice_number, strlen($prefix))) + 1) : 1;
        $invoiceNumber = $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);

        $invoice = Invoice::create([
            'invoice_number'   => $invoiceNumber,
            'quote_id'         => $quote->id,
            'customer_name'    => $quote->billing_name ?: $quote->customer_name,
            'customer_email'   => $quote->billing_email,
            'customer_phone'   => $quote->billing_phone,
            'customer_address' => trim(($quote->billing_address ?? '') . ', ' . ($quote->billing_city ?? '') . ' ' . ($quote->billing_state ?? '') . ' ' . ($quote->billing_zip ?? ''), ', '),
            'status'           => 'draft',
            'tax_rate'         => 0,
            'subtotal'         => 0,
            'tax_amount'       => 0,
            'total'            => 0,
            'amount_paid'      => 0,
            'balance_due'      => 0,
            'due_date'         => now()->addDays(30)->format('Y-m-d'),
            'created_by'       => Auth::id(),
        ]);

        // Copy quote items as invoice line items
        $quoteItems = QuoteItem::where('quote_id', $quote->id)->get();
        $subtotal = 0;
        $sortOrder = 0;

        foreach ($quoteItems as $qi) {
            $qty = floatval($qi->qty ?? 1);
            $unitPrice = floatval($qi->total_price ?? $qi->price ?? 0);
            $lineTotal = round($qty * $unitPrice, 2);

            $desc = trim(($qi->series_name ?? '') . ' ' . ($qi->type_name ?? ''));
            if ($qi->width || $qi->height) {
                $desc .= ' ' . ($qi->width ?? '') . 'x' . ($qi->height ?? '');
            }
            if (empty(trim($desc))) {
                $desc = 'Window Item #' . ($sortOrder + 1);
            }

            InvoiceItem::create([
                'invoice_id'  => $invoice->id,
                'description' => $desc,
                'qty'         => $qty,
                'unit_price'  => $unitPrice,
                'total'       => $lineTotal,
                'sort_order'  => ++$sortOrder,
            ]);

            $subtotal += $lineTotal;
        }

        // Update totals
        $taxAmount = round($subtotal * ($invoice->tax_rate / 100), 2);
        $total = $subtotal + $taxAmount;
        $invoice->update([
            'subtotal'    => $subtotal,
            'tax_amount'  => $taxAmount,
            'total'       => $total,
            'balance_due' => $total,
        ]);

        return redirect()->route('installer.invoices.index')
            ->with('success', "Invoice {$invoiceNumber} created from quote {$quote->quote_number}.");
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
        $user = Auth::user();
        $installerName = $user->company_name ?: $user->name;
        $logoUrl = $user->company_logo_dark ? url('uploads/installer-logos/' . $user->company_logo_dark) : null;

        Mail::send([], [], function ($message) use ($invoice, $items, $installerName, $logoUrl, $user) {
            $html = '<div style="font-family:Arial,sans-serif; max-width:600px; margin:0 auto;">';
            if ($logoUrl) {
                $html .= '<div style="text-align:center; margin-bottom:20px;"><img src="' . $logoUrl . '" alt="' . e($installerName) . '" style="max-height:80px; max-width:250px;"></div>';
            }
            $html .= "<h2 style='color:#333; border-bottom:2px solid #c9a84c; padding-bottom:8px;'>Invoice {$invoice->invoice_number}</h2>"
                . "<p><strong>From:</strong> {$installerName}";
            if ($user->company_phone) $html .= " | {$user->company_phone}";
            if ($user->company_email) $html .= " | {$user->company_email}";
            $html .= "</p>"
                . "<p><strong>To:</strong> {$invoice->customer_name}</p>"
                . "<table border='0' cellpadding='8' cellspacing='0' style='width:100%; border-collapse:collapse; margin:16px 0;'>"
                . "<tr style='background:#f5f4f0;'><th style='text-align:left; border-bottom:1px solid #ddd;'>Description</th><th style='text-align:right; border-bottom:1px solid #ddd;'>Qty</th><th style='text-align:right; border-bottom:1px solid #ddd;'>Rate</th><th style='text-align:right; border-bottom:1px solid #ddd;'>Amount</th></tr>";
            foreach ($items as $item) {
                $html .= "<tr><td style='border-bottom:1px solid #eee;'>{$item->description}</td><td style='text-align:right; border-bottom:1px solid #eee;'>{$item->qty}</td><td style='text-align:right; border-bottom:1px solid #eee;'>\$" . number_format($item->unit_price, 2) . "</td><td style='text-align:right; border-bottom:1px solid #eee;'>\$" . number_format($item->total, 2) . "</td></tr>";
            }
            $html .= "</table>"
                . "<div style='text-align:right; margin-top:8px;'>"
                . "<p>Subtotal: \$" . number_format($invoice->subtotal, 2) . "<br>"
                . "Tax: \$" . number_format($invoice->tax_amount, 2) . "<br>"
                . "<strong style='font-size:1.1em; color:#c9a84c;'>Total: \$" . number_format($invoice->total, 2) . "</strong></p></div>";
            if ($invoice->due_date) {
                $html .= "<p><strong>Due Date:</strong> {$invoice->due_date}</p>";
            }
            if ($invoice->notes) {
                $html .= "<p style='color:#666; font-size:0.9em;'><strong>Notes:</strong> {$invoice->notes}</p>";
            }
            $html .= '</div>';

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
