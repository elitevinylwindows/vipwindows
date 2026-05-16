<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\DeductionManipulation;
use App\Models\ProfileSet;
use App\Models\SeriesTypeProfileSet;
use App\Services\WindowConfigurator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeductionManagerController extends Controller
{
    /**
     * Display the deduction manager page.
     */
    public function index()
    {
        $profileSets = ProfileSet::where('is_active', true)
            ->orderBy('code')
            ->get();

        // Get distinct series types from the series_type_profile_sets table
        $seriesTypes = SeriesTypeProfileSet::select('series_type')
            ->distinct()
            ->orderBy('series_type')
            ->pluck('series_type');

        return view('master.deductions.index', compact('profileSets', 'seriesTypes'));
    }

    /**
     * Get configurations (series-type profile sets) with optional filters.
     */
    public function getConfigurations(Request $request)
    {
        $query = SeriesTypeProfileSet::with('profileSet');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('series_type', 'like', "%{$search}%")
                  ->orWhereHas('profileSet', function ($q2) use ($search) {
                      $q2->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('profile_set_id')) {
            $query->where('profile_set_id', $request->profile_set_id);
        }

        $configs = $query->orderBy('series_type')->get()->map(function ($c) {
            return [
                'id'                    => $c->id,
                'series_type'           => $c->series_type,
                'profile_set_id'        => $c->profile_set_id,
                'profile_set_code'      => $c->profileSet->code ?? '',
                'profile_set_name'      => $c->profileSet->name ?? '',
                'panel_count'           => $c->panel_count,
                'field_width_formula'   => $c->field_width_formula,
                'fix_glass_h_deduction' => $c->fix_glass_h_deduction,
                'fix_glass_v_deduction' => $c->fix_glass_v_deduction,
                'sash_glass_h_deduction'=> $c->sash_glass_h_deduction,
                'sash_glass_v_deduction'=> $c->sash_glass_v_deduction,
                'screen_h_deduction'    => $c->screen_h_deduction,
                'screen_v_deduction'    => $c->screen_v_deduction,
            ];
        });

        return response()->json($configs);
    }

    /**
     * Get deduction manipulations for a configuration (by profile set id).
     */
    public function getManipulations($configId)
    {
        // configId refers to a SeriesTypeProfileSet row
        $config = SeriesTypeProfileSet::findOrFail($configId);

        $manipulations = DeductionManipulation::where('profile_set_id', $config->profile_set_id)
            ->orderBy('seq')
            ->get();

        return response()->json($manipulations);
    }

    /**
     * Save (create or update) a deduction manipulation.
     */
    public function saveManipulation(Request $request)
    {
        $request->validate([
            'id'                   => 'nullable|integer|exists:elitevw_deduction_manipulations,id',
            'profile_set_id'       => 'required|integer|exists:elitevw_profile_sets,id',
            'seq'                  => 'required|integer|min:0',
            'field_number'         => 'nullable|integer',
            'field_label'          => 'nullable|string|max:100',
            'component_type'       => 'nullable|string|max:100',
            'component_type_label' => 'nullable|string|max:100',
            'position'             => 'nullable|string|max:50',
            'h_multiplier'         => 'nullable|integer',
            'v_multiplier'         => 'nullable|integer',
            'frame_type'           => 'nullable|string|max:50',
            'article_code'         => 'nullable|string|max:50',
            'mullion_orientation'  => 'nullable|string|max:50',
            'diff_size_1'          => 'nullable|numeric',
            'diff_size_2'          => 'nullable|numeric',
            'diff_size'            => 'nullable|numeric',
            'gaps'                 => 'nullable|numeric',
            'product_type_code'    => 'nullable|string|max:50',
            'additional_condition' => 'nullable|string|max:255',
            'product_variable'     => 'nullable|string|max:100',
            'is_active'            => 'nullable|boolean',
        ]);

        $data = $request->except(['id', '_token']);

        if ($request->filled('id')) {
            $manipulation = DeductionManipulation::findOrFail($request->id);
            $manipulation->update($data);
        } else {
            $manipulation = DeductionManipulation::create($data);
        }

        return response()->json(['success' => true, 'manipulation' => $manipulation->fresh()]);
    }

    /**
     * Delete a deduction manipulation.
     */
    public function deleteManipulation($id)
    {
        $manipulation = DeductionManipulation::findOrFail($id);
        $manipulation->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Bulk update manipulations (reorder, activate/deactivate).
     */
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'items'        => 'required|array',
            'items.*.id'   => 'required|integer|exists:elitevw_deduction_manipulations,id',
            'items.*.seq'  => 'required|integer|min:0',
            'items.*.is_active' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->items as $item) {
                DeductionManipulation::where('id', $item['id'])->update([
                    'seq'       => $item['seq'],
                    'is_active' => $item['is_active'] ?? true,
                ]);
            }
        });

        return response()->json(['success' => true]);
    }

    /**
     * Get panel layout preview for a given series type and dimensions.
     */
    public function panelLayout(Request $request)
    {
        $configurator = new WindowConfigurator();
        $seriesType = $request->input('series_type', '');
        $width  = (float) $request->input('width', 36);
        $height = (float) $request->input('height', 60);

        $panelCount  = $configurator->getPanelCount($seriesType);
        $fieldLayout = $configurator->getFieldLayout($seriesType, $width);

        return response()->json([
            'series_type'  => $seriesType,
            'panel_count'  => $panelCount,
            'field_layout' => $fieldLayout,
            'width'        => $width,
            'height'       => $height,
        ]);
    }
}
