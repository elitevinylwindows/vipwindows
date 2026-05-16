<?php

namespace App\Http\Controllers\Master\Colors;

use App\Http\Controllers\Controller;
use App\Models\ColorConfiguration;
use App\Models\ExteriorColor;
use App\Models\InteriorColor;
use App\Models\LaminateColor;
use Illuminate\Http\Request;

class ColorConfigurationController extends Controller
{
    public function index()
    {
        $configurations = ColorConfiguration::orderBy('name')->get();
        $exteriorColors = ExteriorColor::orderBy('name')->get();
        $interiorColors = InteriorColor::orderBy('name')->get();
        $laminateColors = LaminateColor::orderBy('name')->get();

        return view('master.colors.configurations', compact(
            'configurations',
            'exteriorColors',
            'interiorColors',
            'laminateColors'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:elitevw_master_colors_color_configurations,code',
            'exterior_source' => 'required|in:exterior,laminate',
            'exterior_color_id' => 'required|integer',
            'interior_source' => 'required|in:interior,laminate',
            'interior_color_id' => 'required|integer',
        ]);

        ColorConfiguration::create($request->only(
            'name', 'code', 'exterior_source', 'exterior_color_id',
            'interior_source', 'interior_color_id'
        ));

        return redirect()->route('admin.master.colors.configurations')
            ->with('success', 'Color configuration created successfully.');
    }

    public function update(Request $request, $id)
    {
        $config = ColorConfiguration::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:elitevw_master_colors_color_configurations,code,' . $id,
            'exterior_source' => 'required|in:exterior,laminate',
            'exterior_color_id' => 'required|integer',
            'interior_source' => 'required|in:interior,laminate',
            'interior_color_id' => 'required|integer',
        ]);

        $config->update($request->only(
            'name', 'code', 'exterior_source', 'exterior_color_id',
            'interior_source', 'interior_color_id'
        ));

        return redirect()->route('admin.master.colors.configurations')
            ->with('success', 'Color configuration updated successfully.');
    }

    public function destroy($id)
    {
        $config = ColorConfiguration::findOrFail($id);
        $config->delete();

        return redirect()->route('admin.master.colors.configurations')
            ->with('success', 'Color configuration deleted successfully.');
    }
}
