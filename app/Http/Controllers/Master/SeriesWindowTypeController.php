<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Series;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SeriesWindowTypeController extends Controller
{
    /**
     * Base window types used by the product navigator.
     */
    public static function baseWindowTypes(): array
    {
        return [
            ['code' => 'SH',  'name' => 'Single Hung'],
            ['code' => 'DH',  'name' => 'Double Hung'],
            ['code' => 'CM',  'name' => 'Casement'],
            ['code' => 'AW',  'name' => 'Awning'],
            ['code' => 'HS',  'name' => 'Horizontal Slider'],
            ['code' => 'PW',  'name' => 'Picture Window'],
            ['code' => 'SLD', 'name' => 'Sliding Door'],
            ['code' => 'SWD', 'name' => 'Swing Door'],
            ['code' => 'XX',  'name' => 'Specialty'],
        ];
    }

    /**
     * Display window type assignments per series.
     */
    public function index()
    {
        $series = Series::orderBy('series')->get();

        $windowTypes = DB::table('elitevw_master_series_window_types')
            ->orderBy('series_id')
            ->orderBy('window_type')
            ->get();

        // Group by series_id for easy lookup in the view
        $windowTypesBySeries = $windowTypes->groupBy('series_id');

        return view('master.series.window-types', compact('series', 'windowTypesBySeries'));
    }

    /**
     * Update window type assignments for a series.
     */
    public function update(Request $request, $seriesId)
    {
        $series = Series::findOrFail($seriesId);

        $request->validate([
            'window_types'   => 'nullable|array',
            'window_types.*' => 'string|max:100',
        ]);

        DB::beginTransaction();
        try {
            // Remove existing assignments
            DB::table('elitevw_master_series_window_types')
                ->where('series_id', $seriesId)
                ->delete();

            // Insert new assignments
            $windowTypes = $request->input('window_types', []);
            foreach ($windowTypes as $wt) {
                $wt = trim($wt);
                if ($wt !== '') {
                    DB::table('elitevw_master_series_window_types')->insert([
                        'series_id'   => $seriesId,
                        'window_type' => $wt,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.master.series.window-types')
                ->with('error', 'Update failed: ' . $e->getMessage());
        }

        return redirect()->route('admin.master.series.window-types')
            ->with('success', "Window types for {$series->series} updated.");
    }
}
