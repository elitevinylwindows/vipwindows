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
            ->with('items')
            ->orderByDesc('created_at');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $invoices = $query->paginate(20);

        return view('installer.invoices.index', compact('invoices', 'status'));
    }
}
