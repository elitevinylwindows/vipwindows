<?php

namespace App\Http\Controllers\Master\Glass;

use App\Http\Controllers\Controller;
use App\Models\Series;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaneController extends Controller
{
    /**
     * Default pane types.
     */
    public static function basePaneTypes(): array
    {
        return ['double_pane', 'three_pane'];
    }

    /**
     * Pane Management page: pane types as rows, series as columns.
     */
    public function index()
    {
        $seriesList = Series::orderBy('series')->get(['id', 'series']);

        $dbPaneTypes = DB::table('elitevw_master_series_pane_types')
            ->distinct()
            ->pluck('pane_type')
            ->toArray();

        $paneTypes = array_values(array_unique(array_merge(self::basePaneTypes(), $dbPaneTypes)));

        $paneAssignments = DB::table('elitevw_master_series_pane_types')
            ->get()
            ->groupBy('series_id')
            ->map(fn ($rows) => $rows->pluck('pane_type')->toArray())
            ->toArray();

        return view('master.glass.panes', compact('seriesList', 'paneTypes', 'paneAssignments'));
    }

    /**
     * Store a new pane type (added via modal, saved with the grid).
     */
    public function store(Request $request)
    {
        $submitted = $request->input('pane_options', []);

        DB::beginTransaction();
        try {
            DB::table('elitevw_master_series_pane_types')->delete();

            foreach ($submitted as $paneType => $seriesIds) {
                if (! is_array($seriesIds)) continue;
                foreach ($seriesIds as $seriesId) {
                    DB::table('elitevw_master_series_pane_types')->insert([
                        'series_id'  => (int) $seriesId,
                        'pane_type'  => $paneType,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', 'Save failed: ' . $e->getMessage());
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Pane types saved.']);
        }

        return redirect()->back()->with('success', 'Pane types saved.');
    }

    /**
     * Update is the same bulk-save operation as store (grid-based).
     */
    public function update(Request $request)
    {
        return $this->store($request);
    }

    /**
     * Remove a pane type from all series.
     */
    public function destroy($paneType)
    {
        DB::table('elitevw_master_series_pane_types')
            ->where('pane_type', $paneType)
            ->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Pane type removed.']);
        }

        return redirect()->back()->with('success', 'Pane type removed.');
    }
}
