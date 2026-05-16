<?php

namespace App\Http\Controllers;

use App\Models\ServiceRate;
use Illuminate\Http\Request;

class ServiceRateController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->get('category', 'all');

        $query = ServiceRate::orderBy('category')->orderBy('sort_order')->orderBy('name');

        if ($category !== 'all') {
            $query->where('category', $category);
        }

        $rates = $query->get();
        $grouped = $rates->groupBy('category');

        $categories = [
            'labor'              => 'Labor',
            'window_installation'=> 'Window Installation',
            'door_installation'  => 'Door Installation',
            'other_services'     => 'Other Services',
        ];

        return view('settings.rates', compact('rates', 'grouped', 'categories', 'category'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category'    => 'required|in:labor,window_installation,door_installation,other_services',
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'cost_rate'   => 'required|numeric|min:0',
            'charge_rate' => 'required|numeric|min:0',
            'unit'        => 'required|in:per_hour,per_unit,flat',
            'is_active'   => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;
        $validated['sort_order'] = ServiceRate::where('category', $validated['category'])->max('sort_order') + 1;

        ServiceRate::create($validated);

        return redirect()->route('admin.settings.rates', ['category' => $validated['category']])
            ->with('success', 'Service rate added successfully.');
    }

    public function update(Request $request, $id)
    {
        $rate = ServiceRate::findOrFail($id);

        $validated = $request->validate([
            'category'    => 'required|in:labor,window_installation,door_installation,other_services',
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'cost_rate'   => 'required|numeric|min:0',
            'charge_rate' => 'required|numeric|min:0',
            'unit'        => 'required|in:per_hour,per_unit,flat',
            'is_active'   => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        $rate->update($validated);

        return redirect()->route('admin.settings.rates', ['category' => $request->get('filter_category', 'all')])
            ->with('success', 'Service rate updated successfully.');
    }

    public function destroy($id)
    {
        $rate = ServiceRate::findOrFail($id);
        $rate->delete();

        return redirect()->route('admin.settings.rates')
            ->with('success', 'Service rate deleted successfully.');
    }
}
