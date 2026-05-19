<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VipMasterOption;
use Illuminate\Http\Request;

class VipMasterController extends Controller
{
    /**
     * VIP Master hub page.
     */
    public function index()
    {
        $counts = [
            'units'      => VipMasterOption::category('unit')->count(),
            'frame_types'=> VipMasterOption::category('frame_type')->count(),
            'grids'      => VipMasterOption::category('grid')->count(),
            'patterns'   => VipMasterOption::category('pattern')->count(),
        ];

        return view('admin.vip-master.index', compact('counts'));
    }

    /**
     * List items for a category (JSON).
     */
    public function list(Request $request, string $category)
    {
        $this->validateCategory($category);

        $items = VipMasterOption::category($category)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json(['items' => $items]);
    }

    /**
     * Store a new item.
     */
    public function store(Request $request, string $category)
    {
        $this->validateCategory($category);

        $validated = $request->validate([
            'name'      => 'required|string|max:150',
            'code'      => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
        ]);

        $maxSort = VipMasterOption::category($category)->max('sort_order') ?? 0;

        $item = VipMasterOption::create([
            'category'   => $category,
            'name'       => $validated['name'],
            'code'       => $validated['code'] ?? null,
            'is_active'  => $validated['is_active'] ?? true,
            'sort_order' => $maxSort + 1,
        ]);

        return response()->json(['success' => true, 'item' => $item]);
    }

    /**
     * Update an item.
     */
    public function update(Request $request, string $category, $id)
    {
        $this->validateCategory($category);

        $item = VipMasterOption::where('category', $category)->findOrFail($id);

        $validated = $request->validate([
            'name'      => 'required|string|max:150',
            'code'      => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
        ]);

        $item->update([
            'name'      => $validated['name'],
            'code'      => $validated['code'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json(['success' => true, 'item' => $item->fresh()]);
    }

    /**
     * Toggle active status.
     */
    public function toggle(string $category, $id)
    {
        $this->validateCategory($category);

        $item = VipMasterOption::where('category', $category)->findOrFail($id);
        $item->update(['is_active' => !$item->is_active]);

        return response()->json(['success' => true, 'is_active' => $item->is_active]);
    }

    /**
     * Delete an item.
     */
    public function destroy(string $category, $id)
    {
        $this->validateCategory($category);

        $item = VipMasterOption::where('category', $category)->findOrFail($id);
        $item->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Reorder items.
     */
    public function reorder(Request $request, string $category)
    {
        $this->validateCategory($category);

        $request->validate(['ids' => 'required|array']);

        foreach ($request->ids as $index => $id) {
            VipMasterOption::where('id', $id)->where('category', $category)->update(['sort_order' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * API: Get active options for a category (for tech measure dropdowns).
     */
    public function options(string $category)
    {
        $this->validateCategory($category);

        $items = VipMasterOption::optionsFor($category);

        return response()->json(['options' => $items]);
    }

    /**
     * Validate category parameter.
     */
    private function validateCategory(string $category): void
    {
        if (!in_array($category, ['unit', 'frame_type', 'grid', 'pattern'])) {
            abort(404, 'Invalid category.');
        }
    }
}
