<?php

namespace App\Http\Controllers\Master\Colors;

use App\Http\Controllers\Controller;
use App\Models\InteriorColor;
use Illuminate\Http\Request;

class InteriorColorController extends Controller
{
    public function index()
    {
        $colors = InteriorColor::orderBy('name')->get();

        return view('master.colors.interior', compact('colors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:elitevw_master_colors_interior_colors,code',
            'hex_color' => 'nullable|string|max:7',
        ]);

        InteriorColor::create($request->only('name', 'code', 'hex_color'));

        return redirect()->route('admin.master.colors.interior')
            ->with('success', 'Interior color created successfully.');
    }

    public function update(Request $request, $id)
    {
        $color = InteriorColor::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:elitevw_master_colors_interior_colors,code,' . $id,
            'hex_color' => 'nullable|string|max:7',
        ]);

        $color->update($request->only('name', 'code', 'hex_color'));

        return redirect()->route('admin.master.colors.interior')
            ->with('success', 'Interior color updated successfully.');
    }

    public function destroy($id)
    {
        $color = InteriorColor::findOrFail($id);
        $color->delete();

        return redirect()->route('admin.master.colors.interior')
            ->with('success', 'Interior color deleted successfully.');
    }
}
