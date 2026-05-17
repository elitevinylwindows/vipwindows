<?php

namespace App\Http\Controllers\Installer;

use App\Http\Controllers\Controller;
use App\Models\VipUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class InstallerCustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = VipUser::where('role', 'customer');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        if ($type = $request->input('type')) {
            $query->where('customer_type', $type);
        }

        $customers = $query->orderByDesc('created_at')->paginate(30);
        return view('installer.customers.index', compact('customers'));
    }

    public function show($id)
    {
        $customer = VipUser::where('role', 'customer')->findOrFail($id);

        // Get quote count for this customer
        $quoteCount = 0;
        try {
            $quoteCount = \App\Models\Quote::where('billing_name', $customer->name)
                ->orWhere('customer_number', $customer->email)
                ->count();
        } catch (\Exception $e) {}

        return response()->json([
            'customer' => $customer,
            'stats' => [
                'quotes' => $quoteCount,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:150',
            'email'         => 'required|email|unique:vip_users,email',
            'phone'         => 'nullable|string|max:30',
            'address'       => 'nullable|string|max:300',
            'city'          => 'nullable|string|max:100',
            'state'         => 'nullable|string|max:50',
            'zip'           => 'nullable|string|max:20',
            'customer_type' => 'nullable|in:homeowner,business',
            'notes'         => 'nullable|string|max:1000',
        ]);

        VipUser::create([
            'name'          => $validated['name'],
            'email'         => $validated['email'],
            'phone'         => $validated['phone'] ?? null,
            'address'       => $validated['address'] ?? null,
            'city'          => $validated['city'] ?? null,
            'state'         => $validated['state'] ?? null,
            'zip'           => $validated['zip'] ?? null,
            'customer_type' => $validated['customer_type'] ?? 'homeowner',
            'notes'         => $validated['notes'] ?? null,
            'role'          => 'customer',
            'password'      => Hash::make('VipCustomer2026!'),
        ]);

        return redirect()->route('installer.customers.index')->with('success', 'Customer added.');
    }

    public function update(Request $request, $id)
    {
        $customer = VipUser::where('role', 'customer')->findOrFail($id);

        $validated = $request->validate([
            'name'          => 'required|string|max:150',
            'email'         => 'required|email|unique:vip_users,email,' . $customer->id,
            'phone'         => 'nullable|string|max:30',
            'address'       => 'nullable|string|max:300',
            'city'          => 'nullable|string|max:100',
            'state'         => 'nullable|string|max:50',
            'zip'           => 'nullable|string|max:20',
            'customer_type' => 'nullable|in:homeowner,business',
            'notes'         => 'nullable|string|max:1000',
        ]);

        $customer->update($validated);

        return redirect()->route('installer.customers.index')->with('success', 'Customer updated.');
    }

    public function destroy($id)
    {
        VipUser::where('role', 'customer')->findOrFail($id)->delete();
        return redirect()->route('installer.customers.index')->with('success', 'Customer removed.');
    }
}
