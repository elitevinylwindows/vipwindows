<?php

namespace App\Http\Controllers\Master\Prices;

use App\Http\Controllers\Controller;
use App\Models\Series;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MatrixController extends Controller
{
    /**
     * Display price matrix management page.
     */
    public function index()
    {
        $series = Series::orderBy('series')->get();

        // Get distinct series types
        $seriesTypes = DB::table('elitevw_master_series_types')
            ->orderBy('series_type')
            ->get();

        return view('master.prices.matrix', compact('series', 'seriesTypes'));
    }

    /**
     * Get price matrix data for a given series and type (AJAX).
     */
    public function getData(Request $request)
    {
        $query = DB::table('elitevw_master_price_price_matrices as pm')
            ->leftJoin('elitevw_master_series as s', 's.id', '=', 'pm.series_id')
            ->leftJoin('elitevw_master_series_types as st', 'st.id', '=', 'pm.series_type_id')
            ->select('pm.*', 's.series as series_name', 'st.series_type');

        if ($request->filled('series_id')) {
            $query->where('pm.series_id', $request->series_id);
        }

        if ($request->filled('series_type_id')) {
            $query->where('pm.series_type_id', $request->series_type_id);
        }

        $prices = $query->orderBy('pm.width')->orderBy('pm.height')->get();

        return response()->json($prices);
    }

    /**
     * Store a new price matrix entry.
     */
    public function store(Request $request)
    {
        $request->validate([
            'series_id'      => 'required|integer|exists:elitevw_master_series,id',
            'series_type_id' => 'required|integer|exists:elitevw_master_series_types,id',
            'width'          => 'required|numeric|min:0',
            'height'         => 'required|numeric|min:0',
            'price'          => 'required|numeric|min:0',
        ]);

        $id = DB::table('elitevw_master_price_price_matrices')->insertGetId([
            'series_id'      => $request->series_id,
            'series_type_id' => $request->series_type_id,
            'width'          => $request->width,
            'height'         => $request->height,
            'price'          => $request->price,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        return response()->json(['success' => true, 'id' => $id]);
    }

    /**
     * Update a price matrix entry.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'width'  => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'price'  => 'required|numeric|min:0',
        ]);

        $data = ['price' => $request->price, 'updated_at' => now()];
        if ($request->filled('width'))  $data['width']  = $request->width;
        if ($request->filled('height')) $data['height'] = $request->height;

        DB::table('elitevw_master_price_price_matrices')
            ->where('id', $id)
            ->update($data);

        return response()->json(['success' => true]);
    }

    /**
     * Delete a price matrix entry.
     */
    public function destroy($id)
    {
        DB::table('elitevw_master_price_price_matrices')
            ->where('id', $id)
            ->delete();

        return response()->json(['success' => true]);
    }
}
