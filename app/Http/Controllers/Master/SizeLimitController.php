<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Series;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SizeLimitController extends Controller
{
    /**
     * Display size limits per series.
     */
    public function index()
    {
        $series = Series::orderBy('series')->get();

        $sizeLimits = DB::table('elitevw_master_series_size_limits')
            ->orderBy('series_id')
            ->get();

        // Group by series_id for easy lookup in the view
        $sizeLimitsBySeries = $sizeLimits->groupBy('series_id');

        return view('master.series.size-limits', compact('series', 'sizeLimitsBySeries'));
    }

    /**
     * Update size limits for a series.
     */
    public function update(Request $request, $seriesId)
    {
        $series = Series::findOrFail($seriesId);

        $request->validate([
            'min_width'  => 'nullable|numeric|min:0',
            'max_width'  => 'nullable|numeric|min:0',
            'min_height' => 'nullable|numeric|min:0',
            'max_height' => 'nullable|numeric|min:0',
            'max_ui'     => 'nullable|numeric|min:0',
        ]);

        DB::table('elitevw_master_series_size_limits')
            ->updateOrInsert(
                ['series_id' => $seriesId],
                [
                    'min_width'  => $request->min_width,
                    'max_width'  => $request->max_width,
                    'min_height' => $request->min_height,
                    'max_height' => $request->max_height,
                    'max_ui'     => $request->max_ui,
                    'updated_at' => now(),
                ]
            );

        return redirect()->route('admin.master.series.size-limits')
            ->with('success', "Size limits for {$series->series} updated.");
    }
}
