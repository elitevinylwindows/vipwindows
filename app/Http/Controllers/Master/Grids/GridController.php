<?php

namespace App\Http\Controllers\Master\Grids;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GridController extends Controller
{
    // ─── Grid Types ─────────────────────────────────────────────

    /**
     * Display grid types page.
     */
    public function types()
    {
        $gridTypes = DB::table('elitevw_master_grid_types')
            ->orderBy('name')
            ->get();

        return view('master.grids.types', compact('gridTypes'));
    }

    /**
     * Store a new grid type.
     */
    public function storeType(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100|unique:elitevw_master_grid_types,name',
            'code'        => 'nullable|string|max:50',
            'description' => 'nullable|string|max:255',
            'is_active'   => 'nullable|boolean',
        ]);

        DB::table('elitevw_master_grid_types')->insert([
            'name'        => $request->name,
            'code'        => $request->code,
            'description' => $request->description,
            'is_active'   => $request->boolean('is_active', true),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return redirect()->route('admin.master.grids.types')
            ->with('success', 'Grid type created successfully.');
    }

    /**
     * Update a grid type.
     */
    public function updateType(Request $request, $id)
    {
        $request->validate([
            'name'        => 'required|string|max:100|unique:elitevw_master_grid_types,name,' . $id,
            'code'        => 'nullable|string|max:50',
            'description' => 'nullable|string|max:255',
            'is_active'   => 'nullable|boolean',
        ]);

        DB::table('elitevw_master_grid_types')->where('id', $id)->update([
            'name'        => $request->name,
            'code'        => $request->code,
            'description' => $request->description,
            'is_active'   => $request->boolean('is_active', true),
            'updated_at'  => now(),
        ]);

        return redirect()->route('admin.master.grids.types')
            ->with('success', 'Grid type updated successfully.');
    }

    /**
     * Delete a grid type.
     */
    public function destroyType($id)
    {
        DB::table('elitevw_master_grid_types')->where('id', $id)->delete();

        return redirect()->route('admin.master.grids.types')
            ->with('success', 'Grid type deleted successfully.');
    }

    // ─── Grid Profiles ──────────────────────────────────────────

    /**
     * Display grid profiles page.
     */
    public function profiles()
    {
        $gridProfiles = DB::table('elitevw_master_grid_profiles')
            ->orderBy('name')
            ->get();

        return view('master.grids.profiles', compact('gridProfiles'));
    }

    /**
     * Store a new grid profile.
     */
    public function storeProfile(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100',
            'code'        => 'nullable|string|max:50',
            'width'       => 'nullable|numeric|min:0',
            'thickness'   => 'nullable|numeric|min:0',
            'material'    => 'nullable|string|max:100',
            'description' => 'nullable|string|max:255',
            'is_active'   => 'nullable|boolean',
        ]);

        DB::table('elitevw_master_grid_profiles')->insert([
            'name'        => $request->name,
            'code'        => $request->code,
            'width'       => $request->width,
            'thickness'   => $request->thickness,
            'material'    => $request->material,
            'description' => $request->description,
            'is_active'   => $request->boolean('is_active', true),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return redirect()->route('admin.master.grids.profiles')
            ->with('success', 'Grid profile created successfully.');
    }

    /**
     * Update a grid profile.
     */
    public function updateProfile(Request $request, $id)
    {
        $request->validate([
            'name'        => 'required|string|max:100',
            'code'        => 'nullable|string|max:50',
            'width'       => 'nullable|numeric|min:0',
            'thickness'   => 'nullable|numeric|min:0',
            'material'    => 'nullable|string|max:100',
            'description' => 'nullable|string|max:255',
            'is_active'   => 'nullable|boolean',
        ]);

        DB::table('elitevw_master_grid_profiles')->where('id', $id)->update([
            'name'        => $request->name,
            'code'        => $request->code,
            'width'       => $request->width,
            'thickness'   => $request->thickness,
            'material'    => $request->material,
            'description' => $request->description,
            'is_active'   => $request->boolean('is_active', true),
            'updated_at'  => now(),
        ]);

        return redirect()->route('admin.master.grids.profiles')
            ->with('success', 'Grid profile updated successfully.');
    }

    /**
     * Delete a grid profile.
     */
    public function destroyProfile($id)
    {
        DB::table('elitevw_master_grid_profiles')->where('id', $id)->delete();

        return redirect()->route('admin.master.grids.profiles')
            ->with('success', 'Grid profile deleted successfully.');
    }

    // ─── Grid Patterns ──────────────────────────────────────────

    /**
     * Display grid patterns page.
     */
    public function patterns()
    {
        $gridPatterns = DB::table('elitevw_master_grid_patterns')
            ->orderBy('name')
            ->get();

        return view('master.grids.patterns', compact('gridPatterns'));
    }

    /**
     * Store a new grid pattern.
     */
    public function storePattern(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:100',
            'code'           => 'nullable|string|max:50',
            'horizontal_bars'=> 'nullable|integer|min:0',
            'vertical_bars'  => 'nullable|integer|min:0',
            'description'    => 'nullable|string|max:255',
            'is_active'      => 'nullable|boolean',
        ]);

        DB::table('elitevw_master_grid_patterns')->insert([
            'name'            => $request->name,
            'code'            => $request->code,
            'horizontal_bars' => $request->horizontal_bars,
            'vertical_bars'   => $request->vertical_bars,
            'description'     => $request->description,
            'is_active'       => $request->boolean('is_active', true),
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        return redirect()->route('admin.master.grids.patterns')
            ->with('success', 'Grid pattern created successfully.');
    }

    /**
     * Update a grid pattern.
     */
    public function updatePattern(Request $request, $id)
    {
        $request->validate([
            'name'           => 'required|string|max:100',
            'code'           => 'nullable|string|max:50',
            'horizontal_bars'=> 'nullable|integer|min:0',
            'vertical_bars'  => 'nullable|integer|min:0',
            'description'    => 'nullable|string|max:255',
            'is_active'      => 'nullable|boolean',
        ]);

        DB::table('elitevw_master_grid_patterns')->where('id', $id)->update([
            'name'            => $request->name,
            'code'            => $request->code,
            'horizontal_bars' => $request->horizontal_bars,
            'vertical_bars'   => $request->vertical_bars,
            'description'     => $request->description,
            'is_active'       => $request->boolean('is_active', true),
            'updated_at'      => now(),
        ]);

        return redirect()->route('admin.master.grids.patterns')
            ->with('success', 'Grid pattern updated successfully.');
    }

    /**
     * Delete a grid pattern.
     */
    public function destroyPattern($id)
    {
        DB::table('elitevw_master_grid_patterns')->where('id', $id)->delete();

        return redirect()->route('admin.master.grids.patterns')
            ->with('success', 'Grid pattern deleted successfully.');
    }
}
