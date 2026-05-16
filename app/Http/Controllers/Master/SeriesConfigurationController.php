<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\SeriesConfiguration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SeriesConfigurationController extends Controller
{
    /**
     * Display all series configurations.
     */
    public function index()
    {
        $configurations = SeriesConfiguration::orderBy('series_type')->get();

        // Get distinct categories for filter dropdown
        $categories = SeriesConfiguration::select('category')
            ->distinct()
            ->whereNotNull('category')
            ->orderBy('category')
            ->pluck('category');

        return view('master.series.configurations', compact('configurations', 'categories'));
    }

    /**
     * Store a new configuration.
     */
    public function store(Request $request)
    {
        $request->validate([
            'series_type' => 'required|string|max:100',
            'category'    => 'nullable|string|max:100',
            'image'       => 'nullable|string|max:255',
        ]);

        SeriesConfiguration::create([
            'series_type' => $request->series_type,
            'category'    => $request->category,
            'image'       => $request->image,
            'is_active'   => true,
        ]);

        return redirect()->route('admin.master.series.configurations')
            ->with('success', 'Configuration created successfully.');
    }

    /**
     * Update a configuration.
     */
    public function update(Request $request, $id)
    {
        $config = SeriesConfiguration::findOrFail($id);

        $request->validate([
            'series_type' => 'required|string|max:100',
            'category'    => 'nullable|string|max:100',
            'image'       => 'nullable|string|max:255',
        ]);

        $config->update([
            'series_type' => $request->series_type,
            'category'    => $request->category,
            'image'       => $request->image,
        ]);

        return redirect()->route('admin.master.series.configurations')
            ->with('success', 'Configuration updated successfully.');
    }

    /**
     * Toggle active status.
     */
    public function toggleActive($id)
    {
        $config = SeriesConfiguration::findOrFail($id);
        $config->update(['is_active' => !$config->is_active]);

        return response()->json([
            'success'   => true,
            'is_active' => $config->is_active,
        ]);
    }

    /**
     * Delete a configuration.
     */
    public function destroy($id)
    {
        $config = SeriesConfiguration::findOrFail($id);
        $config->delete();

        return redirect()->route('admin.master.series.configurations')
            ->with('success', 'Configuration deleted successfully.');
    }

    /**
     * Import configurations from CSV / bulk data.
     */
    public function import(Request $request)
    {
        $request->validate([
            'import_data' => 'required|string',
        ]);

        $lines = array_filter(explode("\n", trim($request->import_data)));
        $imported = 0;

        DB::beginTransaction();
        try {
            foreach ($lines as $line) {
                $parts = str_getcsv(trim($line));
                if (count($parts) >= 1) {
                    SeriesConfiguration::create([
                        'series_type' => trim($parts[0]),
                        'category'    => isset($parts[1]) ? trim($parts[1]) : null,
                        'image'       => isset($parts[2]) ? trim($parts[2]) : null,
                        'is_active'   => true,
                    ]);
                    $imported++;
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.master.series.configurations')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }

        return redirect()->route('admin.master.series.configurations')
            ->with('success', "{$imported} configurations imported successfully.");
    }
}
