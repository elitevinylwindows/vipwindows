<?php

namespace App\Http\Controllers\Master\Prices;

use App\Http\Controllers\Controller;
use App\Models\Series;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MarkupController extends Controller
{
    /**
     * Display markup management page.
     */
    public function index()
    {
        $series = Series::orderBy('series')->get();

        $markups = DB::table('elitevw_master_markup as m')
            ->leftJoin('elitevw_master_series as s', 's.id', '=', 'm.series_id')
            ->select('m.*', 's.series as series_name')
            ->orderBy('s.series')
            ->get();

        return view('master.prices.markup', compact('series', 'markups'));
    }

    /**
     * Store a new markup entry.
     */
    public function store(Request $request)
    {
        $request->validate([
            'series_id'  => 'required|integer|exists:elitevw_master_series,id|unique:elitevw_master_markup,series_id',
            'percentage' => 'required|numeric|min:0|max:999',
        ]);

        DB::table('elitevw_master_markup')->insert([
            'series_id'  => $request->series_id,
            'percentage' => $request->percentage,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.master.prices.markup')
            ->with('success', 'Markup created successfully.');
    }

    /**
     * Update a markup entry.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'percentage' => 'required|numeric|min:0|max:999',
        ]);

        DB::table('elitevw_master_markup')
            ->where('id', $id)
            ->update([
                'percentage' => $request->percentage,
                'updated_at' => now(),
            ]);

        return redirect()->route('admin.master.prices.markup')
            ->with('success', 'Markup updated successfully.');
    }

    /**
     * Delete a markup entry.
     */
    public function destroy($id)
    {
        DB::table('elitevw_master_markup')
            ->where('id', $id)
            ->delete();

        return redirect()->route('admin.master.prices.markup')
            ->with('success', 'Markup deleted successfully.');
    }
}
