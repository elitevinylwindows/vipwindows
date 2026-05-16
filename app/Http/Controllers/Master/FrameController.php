<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FrameController extends Controller
{
    /**
     * Display frame types.
     */
    public function index()
    {
        $frameTypes = DB::table('elitevw_master_frame_types')
            ->orderBy('name')
            ->get();

        return view('master.frames.index', compact('frameTypes'));
    }

    /**
     * Store a new frame type.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100',
            'code'        => 'nullable|string|max:50',
            'description' => 'nullable|string|max:255',
            'depth'       => 'nullable|numeric|min:0',
            'material'    => 'nullable|string|max:100',
            'is_active'   => 'nullable|boolean',
        ]);

        DB::table('elitevw_master_frame_types')->insert([
            'name'        => $request->name,
            'code'        => $request->code,
            'description' => $request->description,
            'depth'       => $request->depth,
            'material'    => $request->material,
            'is_active'   => $request->boolean('is_active', true),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return redirect()->route('admin.master.frames.index')
            ->with('success', 'Frame type created successfully.');
    }

    /**
     * Update a frame type.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name'        => 'required|string|max:100',
            'code'        => 'nullable|string|max:50',
            'description' => 'nullable|string|max:255',
            'depth'       => 'nullable|numeric|min:0',
            'material'    => 'nullable|string|max:100',
            'is_active'   => 'nullable|boolean',
        ]);

        DB::table('elitevw_master_frame_types')->where('id', $id)->update([
            'name'        => $request->name,
            'code'        => $request->code,
            'description' => $request->description,
            'depth'       => $request->depth,
            'material'    => $request->material,
            'is_active'   => $request->boolean('is_active', true),
            'updated_at'  => now(),
        ]);

        return redirect()->route('admin.master.frames.index')
            ->with('success', 'Frame type updated successfully.');
    }

    /**
     * Delete a frame type.
     */
    public function destroy($id)
    {
        DB::table('elitevw_master_frame_types')->where('id', $id)->delete();

        return redirect()->route('admin.master.frames.index')
            ->with('success', 'Frame type deleted successfully.');
    }
}
