<?php

namespace App\Http\Controllers\Master\Glass;

use App\Http\Controllers\Controller;
use App\Models\GlassOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GlassOptionController extends Controller
{
    /**
     * Default glass types (used as seed when DB has no custom types yet).
     */
    public static function baseGlassTypes(): array
    {
        return [
            'CLR',
            'LE3',
            'LAM',
            'Acid Etch',
            'Bamboo',
            'Bronze',
            'Delta Frost',
            'Flemish',
            'Glue Chip',
            'Grey',
            'Matte',
            'Obscure',
            'Rain',
            'Reed',
            'Sand Blast',
            'Solar Cool',
            'Solar Cool G',
        ];
    }

    /**
     * Resolve the visible glass types list from the database.
     */
    public static function resolveGlassTypes(): array
    {
        $visibleTypes = GlassOption::where('position', '_visible')
            ->distinct()
            ->pluck('glass_type')
            ->toArray();

        if (! empty($visibleTypes)) {
            return array_values(array_unique($visibleTypes));
        }

        $dbTypes = GlassOption::where('position', '!=', '_visible')
            ->distinct()
            ->pluck('glass_type')
            ->toArray();

        return array_values(array_unique(array_merge(self::baseGlassTypes(), $dbTypes)));
    }

    /**
     * Grid view: glass types as rows, positions (Outside / Middle / Inside) as columns.
     */
    public function index()
    {
        $positions = ['outside', 'middle', 'inside'];

        $assignments = GlassOption::where('position', '!=', '_visible')
            ->get()
            ->groupBy('position')
            ->map(fn ($items) => $items->pluck('glass_type')->toArray())
            ->toArray();

        $glassTypes = self::resolveGlassTypes();

        return view('master.glass.options', compact('glassTypes', 'positions', 'assignments'));
    }

    /**
     * Bulk-save the glass options grid via AJAX.
     */
    public function update(Request $request)
    {
        $submitted  = $request->input('options', []);
        $glassTypes = $request->input('glass_types', []);

        DB::beginTransaction();
        try {
            DB::table('elitevw_master_glass_options')->delete();

            // Save the visible glass types list (position = '_visible')
            foreach ($glassTypes as $gt) {
                DB::table('elitevw_master_glass_options')->insert([
                    'glass_type' => trim($gt),
                    'position'   => '_visible',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Save the checked position assignments
            foreach ($submitted as $position => $types) {
                if (! is_array($types)) continue;
                foreach ($types as $glassType) {
                    DB::table('elitevw_master_glass_options')->insert([
                        'glass_type' => trim($glassType),
                        'position'   => $position,
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
            return response()->json(['success' => true, 'message' => 'Glass options saved.']);
        }

        return redirect()->back()->with('success', 'Glass options saved.');
    }
}
