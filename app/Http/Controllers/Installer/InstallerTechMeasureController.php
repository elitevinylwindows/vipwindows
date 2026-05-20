<?php

namespace App\Http\Controllers\Installer;

use App\Http\Controllers\Controller;
use App\Models\TechMeasure;
use App\Models\TechMeasureItem;
use App\Models\TechMeasurePhoto;
use App\Models\VipMasterOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class InstallerTechMeasureController extends Controller
{
    /**
     * List tech measures assigned to this installer.
     */
    public function index(Request $request)
    {
        $user = Auth::guard('vip')->user();
        $status = $request->input('status', 'all');

        $query = TechMeasure::with(['calendarEvent', 'crew'])
            ->where(function ($q) use ($user) {
                $q->where('assigned_to', $user->id)
                  ->orWhereHas('crew', function ($cq) use ($user) {
                      $cq->whereHas('members', fn($mq) => $mq->where('crew_members.user_id', $user->id));
                  });
            })
            ->orderByDesc('created_at');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $measures = $query->paginate(50);

        // VIP Master options for dropdowns
        $unitOptions = VipMasterOption::optionsFor('unit');
        $frameTypeOptions = VipMasterOption::optionsFor('frame_type');
        $gridOptions = VipMasterOption::optionsFor('grid');
        $patternOptions = VipMasterOption::optionsFor('pattern');

        return view('installer.tech-measures.index', compact(
            'measures', 'status', 'unitOptions', 'frameTypeOptions', 'gridOptions', 'patternOptions'
        ));
    }

    /**
     * Show tech measure detail (JSON for AJAX).
     */
    public function show($id)
    {
        $user = Auth::guard('vip')->user();
        $measure = TechMeasure::with(['items.photos', 'photos', 'calendarEvent'])
            ->findOrFail($id);

        // Compute clock-in state from status + started_at
        $measureArray = $measure->toArray();
        $measureArray['is_clocked_in'] = ($measure->status === 'in_progress' && $measure->started_at);
        $measureArray['active_since'] = $measure->started_at;

        return response()->json([
            'measure' => $measureArray,
            'items' => $measure->items->map(function ($item) {
                return array_merge($item->toArray(), [
                    'photos' => $item->photos->map(fn($p) => [
                        'id' => $p->id,
                        'url' => asset('storage/' . $p->file_path),
                        'caption' => $p->caption,
                    ]),
                ]);
            }),
            'photos' => $measure->photos->where('tech_measure_item_id', null)->map(fn($p) => [
                'id' => $p->id,
                'url' => asset('storage/' . $p->file_path),
                'caption' => $p->caption,
            ])->values(),
        ]);
    }

    /**
     * Start a tech measure (clock in).
     */
    public function start($id)
    {
        $measure = TechMeasure::findOrFail($id);

        if ($measure->status === 'completed' || $measure->status === 'converted') {
            return response()->json(['error' => 'This measure is already completed.'], 422);
        }

        $measure->update([
            'status' => 'in_progress',
            'started_at' => $measure->started_at ?? now(),
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Complete a tech measure.
     */
    public function complete(Request $request, $id)
    {
        $measure = TechMeasure::findOrFail($id);

        $data = [
            'status' => 'completed',
            'completed_at' => now(),
        ];

        // Save frame & grid data along with completion
        if ($request->has('frame_type')) {
            $data['frame_type'] = $request->input('frame_type');
        }
        if ($request->has('retrofit_bottom_only')) {
            $data['retrofit_bottom_only'] = $request->boolean('retrofit_bottom_only');
        }
        if ($request->has('block_frame_bottom')) {
            $data['block_frame_bottom'] = $request->boolean('block_frame_bottom');
        }
        if ($request->has('has_grids')) {
            $data['has_grids'] = $request->boolean('has_grids');
        }
        if ($request->has('grid_list')) {
            $data['grid_list'] = $request->input('grid_list');
        }
        if ($request->has('grid_pattern')) {
            $data['grid_pattern'] = $request->input('grid_pattern');
        }

        $measure->update($data);

        return response()->json(['success' => true]);
    }

    /**
     * Add a measurement line item.
     */
    public function addItem(Request $request, $id)
    {
        $measure = TechMeasure::findOrFail($id);

        $validated = $request->validate([
            'room_label' => 'nullable|string|max:100',
            'opening_type' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:500',
            'series_id' => 'nullable|integer',
            'series_type' => 'nullable|string|max:100',
            'width' => 'nullable|string|max:50',
            'height' => 'nullable|string|max:50',
            'qty' => 'nullable|integer|min:1',
            'color_config' => 'nullable|string|max:50',
            'color_exterior' => 'nullable|string|max:100',
            'color_exterior_custom' => 'nullable|string|max:100',
            'color_interior' => 'nullable|string|max:100',
            'color_interior_custom' => 'nullable|string|max:100',
            'frame_type' => 'nullable|string|max:100',
            'glass_type' => 'nullable|string|max:100',
            'glass' => 'nullable|string|max:100',
            'grid' => 'nullable|string|max:100',
            'grid_pattern' => 'nullable|string|max:100',
            'grid_profile' => 'nullable|string|max:100',
            'grid_detail' => 'nullable|string|max:100',
            'tempered' => 'nullable|string|max:100',
            'tempered_fields' => 'nullable|array',
            'retrofit_bottom_only' => 'nullable|boolean',
            'no_logo_lock' => 'nullable|boolean',
            'double_lock' => 'nullable|boolean',
            'custom_lock_position' => 'nullable|boolean',
            'custom_vent_latch' => 'nullable|boolean',
            'knocked_down' => 'nullable|boolean',
            'existing_condition' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $validated['tech_measure_id'] = $measure->id;
        $validated['qty'] = $validated['qty'] ?? 1;
        $validated['sort_order'] = $measure->items()->count() + 1;

        $item = TechMeasureItem::create($validated);

        return response()->json([
            'success' => true,
            'item' => $item,
        ]);
    }

    /**
     * Update a measurement line item.
     */
    public function updateItem(Request $request, $measureId, $itemId)
    {
        $item = TechMeasureItem::where('tech_measure_id', $measureId)->findOrFail($itemId);
        $item->update($request->all());

        return response()->json(['success' => true, 'item' => $item->fresh()]);
    }

    /**
     * Remove a measurement line item.
     */
    public function removeItem($measureId, $itemId)
    {
        $item = TechMeasureItem::where('tech_measure_id', $measureId)->findOrFail($itemId);

        // Delete associated photos
        foreach ($item->photos as $photo) {
            Storage::disk('public')->delete($photo->file_path);
            $photo->delete();
        }

        $item->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Upload a photo (to measure or specific item).
     */
    public function uploadPhoto(Request $request, $id)
    {
        $request->validate([
            'photo' => 'required|image|max:10240',
            'item_id' => 'nullable|integer',
            'caption' => 'nullable|string|max:255',
        ]);

        $measure = TechMeasure::findOrFail($id);

        $path = $request->file('photo')->store('tech-measures/' . $id, 'public');

        $photo = TechMeasurePhoto::create([
            'tech_measure_id' => $measure->id,
            'tech_measure_item_id' => $request->input('item_id'),
            'file_path' => $path,
            'caption' => $request->input('caption'),
            'uploaded_by' => Auth::guard('vip')->id(),
        ]);

        return response()->json([
            'success' => true,
            'photo' => [
                'id' => $photo->id,
                'url' => asset('storage/' . $photo->file_path),
                'caption' => $photo->caption,
            ],
        ]);
    }

    /**
     * Delete a photo.
     */
    public function deletePhoto($measureId, $photoId)
    {
        $photo = TechMeasurePhoto::where('tech_measure_id', $measureId)->findOrFail($photoId);
        Storage::disk('public')->delete($photo->file_path);
        $photo->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Update general notes for a tech measure.
     */
    public function updateNotes(Request $request, $id)
    {
        $measure = TechMeasure::findOrFail($id);

        $data = [];
        if ($request->has('notes')) {
            $data['notes'] = $request->input('notes');
        }
        if ($request->has('frame_type')) {
            $data['frame_type'] = $request->input('frame_type');
        }
        if ($request->has('retrofit_bottom_only')) {
            $data['retrofit_bottom_only'] = $request->boolean('retrofit_bottom_only');
        }
        if ($request->has('block_frame_bottom')) {
            $data['block_frame_bottom'] = $request->boolean('block_frame_bottom');
        }

        if (!empty($data)) {
            $measure->update($data);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Update grid settings for a tech measure.
     */
    public function updateGrids(Request $request, $id)
    {
        $measure = TechMeasure::findOrFail($id);

        $validated = $request->validate([
            'has_grids' => 'nullable|boolean',
            'grid_list' => 'nullable|string|max:100',
            'grid_pattern' => 'nullable|string|max:100',
        ]);

        $measure->update([
            'has_grids' => $validated['has_grids'] ?? false,
            'grid_list' => $validated['grid_list'] ?? null,
            'grid_pattern' => $validated['grid_pattern'] ?? null,
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Download / print a PDF-ready view for a tech measure.
     */
    public function downloadPdf($id)
    {
        $measure = TechMeasure::with(['items', 'calendarEvent.service'])->findOrFail($id);
        $items = $measure->items()->orderBy('sort_order')->get();

        // Determine service title from calendar event
        $serviceTitle = 'Tech Measure';
        if ($measure->calendarEvent) {
            if ($measure->calendarEvent->service) {
                $serviceTitle = $measure->calendarEvent->service->name;
            } elseif ($measure->calendarEvent->title) {
                $serviceTitle = $measure->calendarEvent->title;
            }
        }

        return view('installer.tech-measures.pdf', compact('measure', 'items', 'serviceTitle'));
    }
}
