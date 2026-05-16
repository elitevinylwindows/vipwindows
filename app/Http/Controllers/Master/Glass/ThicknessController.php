<?php

namespace App\Http\Controllers\Master\Glass;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ThicknessController extends Controller
{
    /**
     * Show the Thickness Manager page.
     */
    public function index()
    {
        $glassTypes = GlassOptionController::resolveGlassTypes();
        sort($glassTypes);

        $thicknesses = DB::table('elitevw_master_glass_thickness_options')
            ->where('active', true)
            ->orderBy('label')
            ->get();

        $assignments = DB::table('elitevw_master_glass_type_thicknesses')
            ->where('active', true)
            ->get();

        $grouped = [];
        foreach ($assignments as $a) {
            $grouped[$a->glass_type][] = [
                'thickness_id' => (int) $a->thickness_id,
                'is_default'   => (bool) $a->is_default,
            ];
        }

        $sizeRules = DB::table('elitevw_master_glass_thickness_size_rules')
            ->where('active', true)
            ->orderBy('sort_order')
            ->get();

        return view('master.glass.thickness', compact(
            'glassTypes', 'thicknesses', 'grouped', 'sizeRules'
        ));
    }

    /**
     * Store a new thickness option.
     */
    public function store(Request $request)
    {
        $request->validate([
            'label' => 'required|string|max:50',
        ]);

        $label = trim($request->label);
        $value = $request->input('value')
            ?: strtolower(preg_replace('/[\'"\s]+/', '_', str_replace('/', '_', $label)));
        $value = rtrim($value, '_');

        DB::table('elitevw_master_glass_thickness_options')->insert([
            'label'      => $label,
            'value'      => $value,
            'active'     => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Thickness created.']);
        }

        return redirect()->back()->with('success', 'Thickness created.');
    }

    /**
     * Update a thickness option.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'label' => 'required|string|max:50',
        ]);

        $label = trim($request->label);
        $value = $request->input('value')
            ?: strtolower(preg_replace('/[\'"\s]+/', '_', str_replace('/', '_', $label)));
        $value = rtrim($value, '_');

        DB::table('elitevw_master_glass_thickness_options')
            ->where('id', $id)
            ->update([
                'label'      => $label,
                'value'      => $value,
                'updated_at' => now(),
            ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Thickness updated.']);
        }

        return redirect()->back()->with('success', 'Thickness updated.');
    }

    /**
     * Delete a thickness option.
     */
    public function destroy($id)
    {
        DB::table('elitevw_master_glass_thickness_options')->where('id', $id)->delete();
        DB::table('elitevw_master_glass_type_thicknesses')->where('thickness_id', $id)->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Thickness removed.']);
        }

        return redirect()->back()->with('success', 'Thickness removed.');
    }

    /**
     * Save glass-type-to-thickness assignments (bulk AJAX).
     */
    public function saveAssignments(Request $request)
    {
        $assignments = $request->input('assignments', []);

        DB::beginTransaction();
        try {
            DB::table('elitevw_master_glass_type_thicknesses')->delete();

            foreach ($assignments as $glassType => $items) {
                if (! is_array($items)) continue;
                foreach ($items as $item) {
                    if (! isset($item['thickness_id'])) continue;
                    DB::table('elitevw_master_glass_type_thicknesses')->insert([
                        'glass_type'   => trim($glassType),
                        'thickness_id' => (int) $item['thickness_id'],
                        'is_default'   => ! empty($item['is_default']),
                        'active'       => true,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['success' => true, 'message' => 'Assignments saved.']);
    }

    /**
     * Save a thickness size rule (create or update).
     */
    public function saveSizeRule(Request $request)
    {
        $request->validate([
            'label'          => 'required|string|max:20',
            'mm_value'       => 'required|numeric|min:0',
            'max_sqft'       => 'required|numeric|min:0',
            'max_length'     => 'required|numeric|min:0',
            'max_short_side' => 'required|numeric|min:0',
        ]);

        $id    = $request->input('id');
        $label = trim($request->label);
        $value = strtolower(preg_replace('/[\'"\s]+/', '_', str_replace('/', '_', $label)));
        $value = rtrim($value, '_');

        $data = [
            'label'          => $label,
            'value'          => $value,
            'mm_value'       => $request->mm_value,
            'max_sqft'       => $request->max_sqft,
            'max_length'     => $request->max_length,
            'max_short_side' => $request->max_short_side,
            'updated_at'     => now(),
        ];

        if ($id) {
            DB::table('elitevw_master_glass_thickness_size_rules')->where('id', $id)->update($data);
        } else {
            $maxSort = DB::table('elitevw_master_glass_thickness_size_rules')->max('sort_order') ?? 0;
            $data['sort_order']  = $maxSort + 1;
            $data['active']      = true;
            $data['created_at']  = now();
            DB::table('elitevw_master_glass_thickness_size_rules')->insert($data);
        }

        return response()->json(['success' => true, 'message' => 'Size rule saved.']);
    }

    /**
     * Delete a thickness size rule.
     */
    public function destroySizeRule($id)
    {
        DB::table('elitevw_master_glass_thickness_size_rules')->where('id', $id)->delete();

        return response()->json(['success' => true, 'message' => 'Size rule removed.']);
    }
}
