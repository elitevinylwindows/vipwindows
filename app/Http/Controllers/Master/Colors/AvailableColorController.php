<?php

namespace App\Http\Controllers\Master\Colors;

use App\Http\Controllers\Controller;
use App\Models\Series;
use App\Models\SeriesAvailableColor;
use App\Models\ColorConfiguration;
use Illuminate\Http\Request;

class AvailableColorController extends Controller
{
    public function index()
    {
        $seriesList = Series::orderBy('series')->get();
        $configurations = ColorConfiguration::orderBy('name')->get();

        // Group available colors by series
        $availableColors = SeriesAvailableColor::all()->groupBy('series_id');

        return view('master.colors.available', compact('seriesList', 'configurations', 'availableColors'));
    }

    public function update(Request $request, $seriesId)
    {
        $request->validate([
            'colors' => 'nullable|array',
            'colors.*.color_code' => 'required|string|max:50',
            'colors.*.color_name' => 'required|string|max:100',
        ]);

        // Remove existing colors for this series
        SeriesAvailableColor::where('series_id', $seriesId)->delete();

        // Insert new set
        if ($request->has('colors')) {
            foreach ($request->colors as $color) {
                SeriesAvailableColor::create([
                    'series_id' => $seriesId,
                    'color_code' => $color['color_code'],
                    'color_name' => $color['color_name'],
                ]);
            }
        }

        return redirect()->route('admin.master.colors.available')
            ->with('success', 'Available colors updated for series.');
    }
}
