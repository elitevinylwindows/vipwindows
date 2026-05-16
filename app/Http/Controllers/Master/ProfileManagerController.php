<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\DeductionManipulation;
use App\Models\ProfileComponent;
use App\Models\ProfileSet;
use App\Models\Series;
use App\Models\SeriesTypeProfileSet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfileManagerController extends Controller
{
    /**
     * Display the profile manager page.
     */
    public function index()
    {
        $series = Series::orderBy('series')->get();

        return view('master.profiles.index', compact('series'));
    }

    /**
     * Get profile sets, optionally filtered by series type.
     */
    public function getProfiles(Request $request)
    {
        $query = ProfileSet::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('manufacturer_system', 'like', "%{$search}%");
            });
        }

        if ($request->filled('series_type')) {
            $seriesType = $request->series_type;
            $query->whereHas('seriesTypes', function ($q) use ($seriesType) {
                $q->where('series_type', $seriesType);
            });
        }

        $profiles = $query->orderBy('code')->get();

        return response()->json($profiles);
    }

    /**
     * Get a single profile set with components and series-type mappings.
     */
    public function getProfile($id)
    {
        $profile = ProfileSet::with(['components', 'seriesTypes'])->findOrFail($id);

        return response()->json($profile);
    }

    /**
     * Update a profile set.
     */
    public function update(Request $request, $id)
    {
        $profile = ProfileSet::findOrFail($id);

        $request->validate([
            'name'                       => 'nullable|string|max:255',
            'frame_pocket'               => 'nullable|numeric',
            'interlock_overlap'          => 'nullable|numeric',
            'sash_vertical_deduction'    => 'nullable|numeric',
            'frame_horizontal_deduction' => 'nullable|numeric',
            'frame_vertical_deduction'   => 'nullable|numeric',
            'sash_horizontal_deduction'  => 'nullable|numeric',
            'interlock_deduction'        => 'nullable|numeric',
            'meeting_rail_deduction'     => 'nullable|numeric',
            'miter_angle'                => 'nullable|numeric',
            'frame_cut_type'             => 'nullable|string|max:50',
            'sash_cut_type'              => 'nullable|string|max:50',
            'manufacturer_system'        => 'nullable|string|max:100',
            'product_type'               => 'nullable|string|max:100',
            'is_active'                  => 'nullable|boolean',
            'notes'                      => 'nullable|string',
        ]);

        $profile->update($request->only([
            'name', 'frame_pocket', 'interlock_overlap',
            'sash_vertical_deduction', 'frame_horizontal_deduction',
            'frame_vertical_deduction', 'sash_horizontal_deduction',
            'interlock_deduction', 'meeting_rail_deduction',
            'miter_angle', 'frame_cut_type', 'sash_cut_type',
            'manufacturer_system', 'product_type', 'is_active', 'notes',
        ]));

        return response()->json(['success' => true, 'profile' => $profile->fresh()]);
    }

    /**
     * Get deduction manipulations for a profile set.
     */
    public function getManipulations($profileSetId)
    {
        $manipulations = DeductionManipulation::where('profile_set_id', $profileSetId)
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
}
