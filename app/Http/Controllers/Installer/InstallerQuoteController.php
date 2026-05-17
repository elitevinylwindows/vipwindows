<?php

namespace App\Http\Controllers\Installer;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InstallerQuoteController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'all');

        $query = Quote::where('entered_by', Auth::user()->name);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $quotes = $query->latest()->paginate(20);

        return view('installer.quotes.index', compact('quotes', 'status'));
    }

    public function create()
    {
        $customers = \App\Models\VipUser::where('role', 'customer')->orderBy('name')->get();
        $quotes = Quote::where('entered_by', Auth::user()->name)->latest()->take(50)->get();
        return view('installer.quotes.create', compact('customers', 'quotes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name'   => 'required|string|max:150',
            'customer_email'  => 'nullable|email|max:150',
            'customer_phone'  => 'nullable|string|max:30',
            'address'         => 'nullable|string|max:300',
            'city'            => 'nullable|string|max:100',
            'state'           => 'nullable|string|max:50',
            'zip'             => 'nullable|string|max:20',
            'notes'           => 'nullable|string|max:2000',
            'valid_days'      => 'nullable|integer|min:1|max:365',
        ]);

        $quoteNumber = 'VQ-' . strtoupper(substr(Auth::user()->name, 0, 2)) . '-' . now()->format('ymd') . '-' . rand(100, 999);

        $quote = Quote::create([
            'quote_number'    => $quoteNumber,
            'billing_name'    => $validated['customer_name'],
            'billing_email'   => $validated['customer_email'] ?? null,
            'billing_phone'   => $validated['customer_phone'] ?? null,
            'billing_address' => $validated['address'] ?? null,
            'billing_city'    => $validated['city'] ?? null,
            'billing_state'   => $validated['state'] ?? null,
            'billing_zip'     => $validated['zip'] ?? null,
            'notes'           => $validated['notes'] ?? null,
            'valid_until'     => now()->addDays($validated['valid_days'] ?? 30),
            'status'          => 'draft',
            'entered_by'      => Auth::user()->name,
        ]);

        return redirect()->route('installer.quotes.index')->with('success', 'Quote ' . $quoteNumber . ' created.');
    }

    public function show($id)
    {
        $quote = Quote::where('entered_by', Auth::user()->name)->findOrFail($id);

        $items = [];
        try {
            $items = $quote->items()->get()->toArray();
        } catch (\Exception $e) {}

        $subtotal = collect($items)->sum('total');

        return response()->json([
            'quote' => $quote,
            'items' => $items,
            'summary' => [
                'items_count' => count($items),
                'subtotal' => $subtotal,
            ],
        ]);
    }
}
