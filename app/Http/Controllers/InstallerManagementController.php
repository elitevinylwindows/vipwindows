<?php

namespace App\Http\Controllers;

use App\Models\VipUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class InstallerManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = VipUser::where('role', 'installer');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $installers = $query->orderByDesc('created_at')->paginate(20);
        return view('installers.index', compact('installers'));
    }

    public function show($id)
    {
        $installer = VipUser::where('role', 'installer')->findOrFail($id);

        // Get installer stats
        try {
            $quoteCount = \App\Models\Quote::where('created_by', $installer->id)->count();
        } catch (\Exception $e) { $quoteCount = 0; }

        try {
            $jobCount = \App\Models\Job::where('assigned_to', $installer->id)->count();
        } catch (\Exception $e) { $jobCount = 0; }

        try {
            $invoiceCount = \App\Models\Invoice::where('created_by', $installer->id)->count();
        } catch (\Exception $e) { $invoiceCount = 0; }

        return response()->json([
            'installer' => $installer,
            'stats' => [
                'quotes' => $quoteCount,
                'jobs' => $jobCount,
                'invoices' => $invoiceCount,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:150',
            'email'         => 'required|email|unique:vip_users,email',
            'phone'         => 'nullable|string|max:30',
            'company_name'  => 'nullable|string|max:150',
            'company_phone' => 'nullable|string|max:30',
            'company_email' => 'nullable|email|max:150',
            'company_website' => 'nullable|string|max:200',
            'address'       => 'nullable|string|max:300',
            'city'          => 'nullable|string|max:100',
            'state'         => 'nullable|string|max:50',
            'zip'           => 'nullable|string|max:20',
            'notes'         => 'nullable|string|max:1000',
        ]);

        VipUser::create([
            'name'            => $validated['name'],
            'email'           => $validated['email'],
            'phone'           => $validated['phone'] ?? null,
            'company_name'    => $validated['company_name'] ?? null,
            'company_phone'   => $validated['company_phone'] ?? null,
            'company_email'   => $validated['company_email'] ?? null,
            'company_website' => $validated['company_website'] ?? null,
            'address'         => $validated['address'] ?? null,
            'city'            => $validated['city'] ?? null,
            'state'           => $validated['state'] ?? null,
            'zip'             => $validated['zip'] ?? null,
            'notes'           => $validated['notes'] ?? null,
            'role'            => 'installer',
            'password'        => Hash::make('VipInstaller2026!'),
        ]);

        return redirect()->route('admin.installers.index')->with('success', 'Installer added. Default password: VipInstaller2026!');
    }

    public function update(Request $request, $id)
    {
        $installer = VipUser::where('role', 'installer')->findOrFail($id);

        $validated = $request->validate([
            'name'          => 'required|string|max:150',
            'email'         => 'required|email|unique:vip_users,email,' . $installer->id,
            'phone'         => 'nullable|string|max:30',
            'company_name'  => 'nullable|string|max:150',
            'company_phone' => 'nullable|string|max:30',
            'company_email' => 'nullable|email|max:150',
            'company_website' => 'nullable|string|max:200',
            'address'       => 'nullable|string|max:300',
            'city'          => 'nullable|string|max:100',
            'state'         => 'nullable|string|max:50',
            'zip'           => 'nullable|string|max:20',
            'notes'         => 'nullable|string|max:1000',
            'status'        => 'nullable|in:active,suspended',
        ]);

        $installer->update($validated);

        return redirect()->route('admin.installers.index')->with('success', 'Installer updated.');
    }

    public function destroy($id)
    {
        VipUser::where('role', 'installer')->findOrFail($id)->delete();
        return redirect()->route('admin.installers.index')->with('success', 'Installer removed.');
    }
}
