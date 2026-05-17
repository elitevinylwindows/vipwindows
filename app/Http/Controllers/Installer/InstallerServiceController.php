<?php

namespace App\Http\Controllers\Installer;

use App\Http\Controllers\Controller;
use App\Models\InstallerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InstallerServiceController extends Controller
{
    public function index()
    {
        $services = InstallerService::where('installer_id', Auth::id())
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json(['services' => $services]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'description'        => 'nullable|string|max:1000',
            'price'              => 'required|numeric|min:0',
            'price_type'         => 'required|in:flat,per_unit,per_hour,per_sqft',
            'estimated_duration' => 'nullable|integer|min:0',
            'is_active'          => 'nullable|boolean',
        ]);

        $sortOrder = (InstallerService::where('installer_id', Auth::id())->max('sort_order') ?? 0) + 1;

        $service = InstallerService::create(array_merge($validated, [
            'installer_id' => Auth::id(),
            'sort_order'   => $sortOrder,
            'is_active'    => $validated['is_active'] ?? true,
        ]));

        return response()->json(['success' => true, 'service' => $service]);
    }

    public function update(Request $request, $id)
    {
        $service = InstallerService::where('installer_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'description'        => 'nullable|string|max:1000',
            'price'              => 'required|numeric|min:0',
            'price_type'         => 'required|in:flat,per_unit,per_hour,per_sqft',
            'estimated_duration' => 'nullable|integer|min:0',
            'is_active'          => 'nullable|boolean',
        ]);

        $service->update($validated);

        return response()->json(['success' => true, 'service' => $service->fresh()]);
    }

    public function destroy($id)
    {
        InstallerService::where('installer_id', Auth::id())->findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}
