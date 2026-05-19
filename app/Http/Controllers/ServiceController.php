<?php

namespace App\Http\Controllers;

use App\Models\InstallationType;
use App\Models\Service;
use App\Models\VipUser;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $services = Service::orderBy('sort_order')->get();
        $installTypes = InstallationType::orderBy('sort_order')->orderBy('name')->get();

        return view('services.index', compact('services', 'installTypes'));
    }

    public function show($id)
    {
        $service = Service::findOrFail($id);
        $installers = $service->installers()->get();

        return response()->json([
            'service' => $service,
            'installers' => $installers->map(function ($inst) {
                return [
                    'id' => $inst->id,
                    'name' => $inst->name,
                    'company_name' => $inst->company_name,
                    'custom_price' => $inst->pivot->custom_price,
                ];
            }),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:150',
            'description' => 'nullable|string|max:500',
            'base_price'  => 'required|numeric|min:0',
            'cost_price'  => 'nullable|numeric|min:0',
            'unit'        => 'required|in:per_job,per_hour,per_unit',
            'min_price'   => 'nullable|numeric|min:0',
            'max_price'   => 'nullable|numeric|min:0',
            'is_active'          => 'nullable|boolean',
            'color'              => 'nullable|string|max:7',
            'sort_order'         => 'nullable|integer',
            'installer_pay'      => 'nullable|numeric|min:0',
            'installer_pay_type' => 'nullable|in:per_job,per_hour,per_unit,percentage',
        ]);

        $validated['code'] = strtoupper(\Illuminate\Support\Str::slug($validated['name'], '_'));
        $base = $validated['code'];
        $i = 1;
        while (Service::where('code', $validated['code'])->exists()) {
            $validated['code'] = $base . '_' . $i++;
        }

        $validated['is_active'] = $request->has('is_active') ? true : ($request->input('is_active') ?? true);
        $validated['cost_price'] = $validated['cost_price'] ?? 0;
        $validated['color'] = $validated['color'] ?? '#0d6efd';
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['installer_pay'] = $validated['installer_pay'] ?? 0;
        $validated['installer_pay_type'] = $validated['installer_pay_type'] ?? 'per_unit';

        Service::create($validated);

        return redirect()->route('admin.services.index')->with('success', 'Service created.');
    }

    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);

        $validated = $request->validate([
            'name'        => 'required|string|max:150',
            'description' => 'nullable|string|max:500',
            'base_price'  => 'required|numeric|min:0',
            'cost_price'  => 'nullable|numeric|min:0',
            'unit'        => 'required|in:per_job,per_hour,per_unit',
            'min_price'   => 'nullable|numeric|min:0',
            'max_price'   => 'nullable|numeric|min:0',
            'is_active'          => 'nullable',
            'color'              => 'nullable|string|max:7',
            'sort_order'         => 'nullable|integer',
            'installer_pay'      => 'nullable|numeric|min:0',
            'installer_pay_type' => 'nullable|in:per_job,per_hour,per_unit,percentage',
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;
        $validated['cost_price'] = $validated['cost_price'] ?? 0;
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['installer_pay'] = $validated['installer_pay'] ?? $service->installer_pay ?? 0;
        $validated['installer_pay_type'] = $validated['installer_pay_type'] ?? $service->installer_pay_type ?? 'per_unit';

        $service->update($validated);

        return redirect()->route('admin.services.index')->with('success', 'Service updated.');
    }

    public function destroy($id)
    {
        Service::findOrFail($id)->delete();
        return redirect()->route('admin.services.index')->with('success', 'Service removed.');
    }

    public function toggleActive($id)
    {
        $service = Service::findOrFail($id);
        $service->update(['is_active' => !$service->is_active]);

        return response()->json(['success' => true, 'is_active' => $service->is_active]);
    }

    // ─── Installation Types CRUD ────────────────────────────────

    public function storeInstallType(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:150',
            'description'   => 'nullable|string|max:500',
            'price'         => 'required|numeric|min:0',
            'installer_pay' => 'required|numeric|min:0',
            'sort_order'    => 'nullable|integer',
        ]);

        $validated['is_active'] = true;
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        InstallationType::create($validated);

        return redirect()->route('admin.services.index')->with('success', 'Installation type added.');
    }

    public function updateInstallType(Request $request, $id)
    {
        $type = InstallationType::findOrFail($id);

        $validated = $request->validate([
            'name'          => 'required|string|max:150',
            'description'   => 'nullable|string|max:500',
            'price'         => 'required|numeric|min:0',
            'installer_pay' => 'required|numeric|min:0',
            'is_active'     => 'nullable',
            'sort_order'    => 'nullable|integer',
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $type->update($validated);

        return redirect()->route('admin.services.index')->with('success', 'Installation type updated.');
    }

    public function destroyInstallType($id)
    {
        InstallationType::findOrFail($id)->delete();
        return redirect()->route('admin.services.index')->with('success', 'Installation type removed.');
    }
}
