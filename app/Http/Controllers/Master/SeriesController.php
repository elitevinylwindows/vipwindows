<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Series;
use Illuminate\Http\Request;

class SeriesController extends Controller
{
    /**
     * Display all series.
     */
    public function index()
    {
        $series = Series::orderBy('series')->get();

        return view('master.series.index', compact('series'));
    }

    /**
     * Store a new series.
     */
    public function store(Request $request)
    {
        $request->validate([
            'series' => 'required|string|max:100|unique:elitevw_master_series,series',
        ]);

        Series::create([
            'series' => $request->series,
        ]);

        return redirect()->route('admin.master.series.index')
            ->with('success', 'Series created successfully.');
    }

    /**
     * Update a series.
     */
    public function update(Request $request, $id)
    {
        $series = Series::findOrFail($id);

        $request->validate([
            'series' => 'required|string|max:100|unique:elitevw_master_series,series,' . $id,
        ]);

        $series->update([
            'series' => $request->series,
        ]);

        return redirect()->route('admin.master.series.index')
            ->with('success', 'Series updated successfully.');
    }

    /**
     * Delete a series.
     */
    public function destroy($id)
    {
        $series = Series::findOrFail($id);
        $series->delete();

        return redirect()->route('admin.master.series.index')
            ->with('success', 'Series deleted successfully.');
    }
}
