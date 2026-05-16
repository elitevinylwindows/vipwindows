<?php

namespace App\Http\Controllers;

use App\Models\VipUser;
use App\Models\Quote;
use App\Models\Job;
use App\Models\Invoice;
use App\Models\InstallationOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CustomerManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = VipUser::where('role', 'customer');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $customers = $query->orderByDesc('created_at')->paginate(20);
        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:150',
            'email'   => 'required|email|unique:vip_users,email',
            'phone'   => 'nullable|string|max:30',
            'address' => 'nullable|string|max:300',
            'city'    => 'nullable|string|max:100',
            'state'   => 'nullable|string|max:50',
            'zip'     => 'nullable|string|max:20',
            'notes'         => 'nullable|string|max:1000',
            'customer_type' => 'nullable|in:homeowner,business',
        ]);

        VipUser::create([
            'name'          => $validated['name'],
            'email'         => $validated['email'],
            'phone'         => $validated['phone'] ?? null,
            'address'       => $validated['address'] ?? null,
            'city'          => $validated['city'] ?? null,
            'state'         => $validated['state'] ?? null,
            'zip'           => $validated['zip'] ?? null,
            'notes'         => $validated['notes'] ?? null,
            'customer_type' => $validated['customer_type'] ?? 'homeowner',
            'role'          => 'customer',
            'password'      => Hash::make('VipCustomer2026!'), // default password
        ]);

        return redirect()->route('admin.customers.index')->with('success', 'Customer added successfully. Default password: VipCustomer2026!');
    }

    public function show($id)
    {
        $customer = VipUser::where('role', 'customer')->findOrFail($id);

        // Quotes — shared table, should always exist
        try {
            $quotes = Quote::with('items')
                ->where(function ($q) use ($customer) {
                    $q->where('billing_email', $customer->email)
                      ->orWhere('customer_number', $customer->email);
                })
                ->orderByDesc('created_at')
                ->get();
        } catch (\Exception $e) {
            $quotes = collect();
        }

        // Jobs — vip_jobs table may not exist yet
        try {
            $jobs = Job::where('customer_email', $customer->email)
                ->orderByDesc('created_at')
                ->get();
        } catch (\Exception $e) {
            $jobs = collect();
        }

        // Invoices — vip_invoices table may not exist yet
        try {
            $invoices = Invoice::where('customer_email', $customer->email)
                ->orderByDesc('created_at')
                ->get();
        } catch (\Exception $e) {
            $invoices = collect();
        }

        // Installation orders — table may not exist yet
        try {
            $orders = InstallationOrder::where('customer_email', $customer->email)
                ->orderByDesc('created_at')
                ->get();
        } catch (\Exception $e) {
            $orders = collect();
        }

        return response()->json([
            'customer' => $customer,
            'quotes'   => $quotes->map(fn($q) => [
                'id'           => $q->id,
                'quote_number' => $q->quote_number,
                'status'       => $q->status,
                'items_count'  => $q->items->count(),
                'total'        => number_format($q->items->sum(fn($i) => $i->getRawOriginal('total')), 2),
                'created_at'   => $q->created_at?->format('M d, Y'),
            ]),
            'jobs'     => $jobs->map(fn($j) => [
                'id'             => $j->id,
                'job_number'     => $j->job_number,
                'status'         => $j->status,
                'priority'       => $j->priority,
                'install_address'=> $j->install_address,
                'scheduled_date' => $j->scheduled_date?->format('M d, Y'),
                'assigned_to'    => $j->assignee?->name ?? '—',
                'description'    => $j->description,
                'created_at'     => $j->created_at?->format('M d, Y'),
            ]),
            'invoices' => $invoices->map(fn($inv) => [
                'id'             => $inv->id,
                'invoice_number' => $inv->invoice_number,
                'status'         => $inv->status,
                'total'          => number_format($inv->total, 2),
                'balance_due'    => number_format($inv->balance_due, 2),
                'due_date'       => $inv->due_date?->format('M d, Y'),
                'created_at'     => $inv->created_at?->format('M d, Y'),
            ]),
            'orders'   => $orders->map(fn($o) => [
                'id'              => $o->id,
                'status'          => $o->status,
                'install_address' => ($o->install_address ?? '') . ', ' . ($o->install_city ?? ''),
                'scheduled_date'  => $o->scheduled_date?->format('M d, Y'),
                'created_at'      => $o->created_at?->format('M d, Y'),
            ]),
        ]);
    }

    public function edit($id)
    {
        $customer = VipUser::where('role', 'customer')->findOrFail($id);
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, $id)
    {
        $customer = VipUser::where('role', 'customer')->findOrFail($id);

        $validated = $request->validate([
            'name'    => 'required|string|max:150',
            'email'   => 'required|email|unique:vip_users,email,' . $customer->id,
            'phone'   => 'nullable|string|max:30',
            'address' => 'nullable|string|max:300',
            'city'    => 'nullable|string|max:100',
            'state'   => 'nullable|string|max:50',
            'zip'           => 'nullable|string|max:20',
            'notes'         => 'nullable|string|max:1000',
            'customer_type' => 'nullable|in:homeowner,business',
        ]);

        $customer->update($validated);

        return redirect()->route('admin.customers.index')->with('success', 'Customer updated.');
    }

    public function destroy($id)
    {
        VipUser::where('role', 'customer')->findOrFail($id)->delete();
        return redirect()->route('admin.customers.index')->with('success', 'Customer removed.');
    }
}
