<?php

namespace App\Http\Controllers;

use App\Models\ServiceArea;
use Illuminate\Http\Request;

class ServiceAreaController extends Controller
{
    public function index()
    {
        $areas = ServiceArea::orderBy('sort_order')->orderBy('name')->get();
        return view('service-areas.admin-index', compact('areas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:300',
            'state'       => 'required|string|max:50',
            'sort_order'  => 'nullable|integer',
        ]);

        ServiceArea::create([
            'name'        => $validated['name'],
            'description' => $validated['description'],
            'state'       => $validated['state'],
            'sort_order'  => $validated['sort_order'] ?? 0,
            'is_active'   => true,
        ]);

        return redirect()->route('admin.content.index', ['#service-areas'])->with('success', 'Service area added.');
    }

    public function update(Request $request, $id)
    {
        $area = ServiceArea::findOrFail($id);

        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:300',
            'state'       => 'required|string|max:50',
            'sort_order'  => 'nullable|integer',
            'is_active'   => 'nullable|boolean',
        ]);

        $area->update([
            'name'        => $validated['name'],
            'description' => $validated['description'],
            'state'       => $validated['state'],
            'sort_order'  => $validated['sort_order'] ?? 0,
            'is_active'   => $request->has('is_active'),
        ]);

        return redirect()->route('admin.content.index', ['#service-areas'])->with('success', 'Service area updated.');
    }

    public function destroy($id)
    {
        ServiceArea::findOrFail($id)->delete();
        return redirect()->route('admin.content.index', ['#service-areas'])->with('success', 'Service area removed.');
    }
}
