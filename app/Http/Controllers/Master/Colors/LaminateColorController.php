<?php

namespace App\Http\Controllers\Master\Colors;

use App\Http\Controllers\Controller;
use App\Models\LaminateColor;
use Illuminate\Http\Request;

class LaminateColorController extends Controller
{
    public function index()
    {
        $colors = LaminateColor::orderBy('name')->get();

        return view('master.colors.laminate', compact('colors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:elitevw_master_colors_laminate_colors,code',
            'hex_color' => 'nullable|string|max:7',
        ]);

        LaminateColor::create($request->only('name', 'code', 'hex_color'));

        return redirect()->route('admin.master.colors.laminate')
            ->with('success', 'Laminate color created successfully.');
    }

    public function update(Request $request, $id)
    {
        $color = LaminateColor::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:50',
            'code' => 'required|string|max:50|unique:elitevw_master_colors_laminate_colors,code,' . $id,
            'hex_color' => 'nullable|string|max:7',
        ]);

        $color->update($request->only('name', 'code', 'hex_color'));

        return redirect()->route('admin.master.colors.laminate')
            ->with('success', 'Laminate color updated successfully.');
    }

    public function destroy($id)
    {
        $color = LaminateColor::findOrFail($id);
        $color->delete();

        return redirect()->route('admin.master.colors.laminate')
            ->with('success', 'Laminate color deleted successfully.');
    }
}
