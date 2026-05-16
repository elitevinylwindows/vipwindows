<?php

namespace App\Http\Controllers\Master\Colors;

use App\Http\Controllers\Controller;
use App\Models\ExteriorColor;
use Illuminate\Http\Request;

class ExteriorColorController extends Controller
{
    public function index()
    {
        $colors = ExteriorColor::orderBy('name')->get();

        return view('master.colors.exterior', compact('colors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:elitevw_master_colors_exterior_colors,code',
            'hex_color' => 'nullable|string|max:7',
        ]);

        ExteriorColor::create($request->only('name', 'code', 'hex_color'));

        return redirect()->route('admin.master.colors.exterior')
            ->with('success', 'Exterior color created successfully.');
    }

    public function update(Request $request, $id)
    {
        $color = ExteriorColor::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:elitevw_master_colors_exterior_colors,code,' . $id,
            'hex_color' => 'nullable|string|max:7',
        ]);

        $color->update($request->only('name', 'code', 'hex_color'));

        return redirect()->route('admin.master.colors.exterior')
            ->with('success', 'Exterior color updated successfully.');
    }

    public function destroy($id)
    {
        $color = ExteriorColor::findOrFail($id);
        $color->delete();

        return redirect()->route('admin.master.colors.exterior')
            ->with('success', 'Exterior color deleted successfully.');
    }
}
