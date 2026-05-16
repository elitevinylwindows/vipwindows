<?php

namespace App\Http\Controllers\Master\Glass;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TemperedController extends Controller
{
    /**
     * Specialty / Tempered relationship page.
     * Grid: glass types (rows) x Tempered + Specialty (columns).
     */
    public function index()
    {
        $glassTypes = GlassOptionController::resolveGlassTypes();
        sort($glassTypes);

        // Global relationships (series_id = 0 means all series)
        $rawRels = DB::table('elitevw_master_glass_type_relationships')
            ->where('active', true)
            ->where('series_id', 0)
            ->get();

        $relationships = [];
        foreach ($rawRels as $r) {
            $relationships[0][$r->relationship_type][] = $r->glass_type;
        }

        return view('master.glass.tempered', compact('glassTypes', 'relationships'));
    }

    /**
     * Save tempered/specialty relationships (AJAX).
     */
    public function update(Request $request)
    {
        $seriesId  = $request->input('series_id', 0);
        $tempered  = $request->input('tempered', []);
        $specialty = $request->input('specialty', []);

        DB::beginTransaction();
        try {
            DB::table('elitevw_master_glass_type_relationships')
                ->where('series_id', $seriesId)
                ->delete();

            foreach ($tempered as $gt) {
                DB::table('elitevw_master_glass_type_relationships')->insert([
                    'series_id'         => $seriesId,
                    'glass_type'        => trim($gt),
                    'relationship_type' => 'tempered',
                    'active'            => true,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
            }

            foreach ($specialty as $gt) {
                DB::table('elitevw_master_glass_type_relationships')->insert([
                    'series_id'         => $seriesId,
                    'glass_type'        => trim($gt),
                    'relationship_type' => 'specialty',
                    'active'            => true,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['success' => true, 'message' => 'Relationships saved.']);
    }
}
