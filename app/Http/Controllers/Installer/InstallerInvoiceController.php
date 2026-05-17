<?php

namespace App\Http\Controllers\Installer;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
}
