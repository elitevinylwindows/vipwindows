<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\VipUser;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Service::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->input('filter') === 'active') {
            $query->where('is_active', true);
        } elseif ($request->input('filter') === 'inactive') {
            $query->where('is_active', false);
        }

        $services = $query->orderBy('sort_order')->paginate(20);
        return view('services.index', compact('services'));
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
            'code'        => 'required|string|max:30|unique:vip_services,code',
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
            'code'        => 'required|string|max:30|unique:vip_services,code,' . $service->id,
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

        $service->update($validated);

        return redirect()->route('admin.services.index')->with('success', 'Service updated.');
    }

    public function destroy($id)
    {
        Service::findOrFail($id)->delete();
        return redirect()->route('admin.services.index')->with('success', 'Service removed.');
    }

    /**
     * Toggle active/inactive status via AJAX
     */
    public function toggleActive($id)
    {
        $service = Service::findOrFail($id);
        $service->update(['is_active' => !$service->is_active]);

        return response()->json(['success' => true, 'is_active' => $service->is_active]);
    }
}
