<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use App\Models\Crew;
use App\Models\TechMeasure;
use App\Models\TechMeasureItem;
use App\Models\TechMeasurePhoto;
use App\Models\VipMasterOption;
use App\Models\InstallationType;
use App\Models\VipUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TechMeasureController extends Controller
{
    /**
     * List all tech measures (admin view).
     */
    public function index(Request $request)
    {
        $status = $request->input('status', 'all');

        $query = TechMeasure::with(['assignee', 'crew', 'calendarEvent'])
            ->orderByDesc('created_at');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $measures = $query->paginate(50);

        $techs = VipUser::whereIn('role', ['installer', 'technician'])
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $crews = Crew::where('status', 'active')->orderBy('name')->get();

        $unitOptions = VipMasterOption::optionsFor('unit');
        $frameTypeOptions = VipMasterOption::optionsFor('frame_type');
        $gridOptions = VipMasterOption::optionsFor('grid');
        $patternOptions = VipMasterOption::optionsFor('pattern');

        $installationTypes = InstallationType::where('is_active', true)->orderBy('sort_order')->get();

        return view('admin.tech-measures.index', compact('measures', 'status', 'techs', 'crews', 'unitOptions', 'frameTypeOptions', 'gridOptions', 'patternOptions', 'installationTypes'));
    }

    /**
     * Show a tech measure detail (admin view with all items and photos).
     */
    public function show($id)
    {
        $measure = TechMeasure::with(['items.photos', 'photos', 'assignee', 'crew', 'calendarEvent'])
            ->findOrFail($id);

        // For JSON request (from left rail)
        if (request()->wantsJson()) {
            return response()->json([
                'measure' => $measure,
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
                ]),
            ]);
        }

        return view('admin.tech-measures.show', compact('measure'));
    }

    /**
     * Update tech measure details (admin edit).
     */
    public function update(Request $request, $id)
    {
        $measure = TechMeasure::findOrFail($id);

        $validated = $request->validate([
            'customer_name'  => 'nullable|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'address'        => 'nullable|string|max:500',
            'notes'          => 'nullable|string',
        ]);

        $measure->update($validated);

        return response()->json(['success' => true, 'measure' => $measure->fresh()]);
    }

    /**
     * Send email to tech measure customer (admin).
     */
    public function sendEmail(Request $request, $id)
    {
        $measure = TechMeasure::findOrFail($id);

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        if (!$measure->customer_email) {
            return response()->json(['error' => 'No customer email on file.'], 422);
        }

        \Illuminate\Support\Facades\Mail::raw($validated['message'], function ($mail) use ($measure, $validated) {
            $mail->to($measure->customer_email)
                 ->subject($validated['subject']);
        });

        return response()->json(['success' => true, 'message' => 'Email sent to ' . $measure->customer_email]);
    }

    /**
     * Create a tech measure from a calendar event.
     */
    public function createFromEvent(Request $request)
    {
        $validated = $request->validate([
            'calendar_event_id' => 'required|exists:calendar_events,id',
        ]);

        $event = CalendarEvent::with('crew')->findOrFail($validated['calendar_event_id']);

        // Check if measure already exists for this event
        $existing = TechMeasure::where('calendar_event_id', $event->id)->first();
        if ($existing) {
            return response()->json([
                'success' => true,
                'measure_id' => $existing->id,
                'message' => 'Tech measure already exists.',
            ]);
        }

        $measure = TechMeasure::create([
            'calendar_event_id' => $event->id,
            'customer_name' => $event->customer_name,
            'customer_email' => $event->customer_email,
            'customer_phone' => $event->customer_phone,
            'address' => $event->address,
            'status' => 'pending',
            'assigned_to' => $event->crew?->members()->first()?->id,
            'crew_id' => $event->crew_id,
            'created_by' => Auth::guard('vip')->id(),
        ]);

        return response()->json([
            'success' => true,
            'measure_id' => $measure->id,
        ]);
    }

    /**
     * Add a measurement item (admin).
     */
    public function addItem(Request $request, $id)
    {
        $measure = TechMeasure::findOrFail($id);

        $validated = $request->validate([
            'room_label'   => 'nullable|string|max:100',
            'description'  => 'nullable|string|max:500',
            'series_type'  => 'nullable|string|max:100',
            'width'        => 'nullable',
            'height'       => 'nullable',
            'qty'          => 'nullable|integer|min:1',
            'notes'        => 'nullable|string|max:500',
        ]);

        $item = $measure->items()->create(array_merge($validated, [
            'qty' => $validated['qty'] ?? 1,
        ]));

        return response()->json(['success' => true, 'item' => $item]);
    }

    /**
     * Update a measurement item (admin).
     */
    public function updateItem(Request $request, $measureId, $itemId)
    {
        $item = TechMeasureItem::where('tech_measure_id', $measureId)->findOrFail($itemId);
        $item->update($request->all());

        return response()->json(['success' => true, 'item' => $item->fresh()]);
    }

    /**
     * Remove a measurement item (admin).
     */
    public function removeItem($measureId, $itemId)
    {
        $item = TechMeasureItem::where('tech_measure_id', $measureId)->findOrFail($itemId);

        foreach ($item->photos as $photo) {
            Storage::disk('public')->delete($photo->file_path);
            $photo->delete();
        }

        $item->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Update notes / frame type (admin).
     */
    public function updateNotes(Request $request, $id)
    {
        $measure = TechMeasure::findOrFail($id);
        $data = $request->only(['notes', 'frame_type']);
        $measure->update($data);

        return response()->json(['success' => true]);
    }

    /**
     * Update grid settings (admin).
     */
    public function updateGrids(Request $request, $id)
    {
        $measure = TechMeasure::findOrFail($id);
        $measure->update($request->only(['has_grids', 'grid_list', 'grid_pattern']));

        return response()->json(['success' => true]);
    }

    /**
     * Upload a photo (admin).
     */
    public function uploadPhoto(Request $request, $id)
    {
        $request->validate([
            'photo'   => 'required|image|max:10240',
            'item_id' => 'nullable|integer',
            'caption' => 'nullable|string|max:255',
        ]);

        $measure = TechMeasure::findOrFail($id);
        $path = $request->file('photo')->store('tech-measures/' . $id, 'public');

        $photo = TechMeasurePhoto::create([
            'tech_measure_id'      => $measure->id,
            'tech_measure_item_id' => $request->input('item_id'),
            'file_path'            => $path,
            'caption'              => $request->input('caption'),
        ]);

        return response()->json(['success' => true, 'photo' => $photo]);
    }

    /**
     * Delete a photo (admin).
     */
    public function deletePhoto($measureId, $photoId)
    {
        $photo = TechMeasurePhoto::where('tech_measure_id', $measureId)->findOrFail($photoId);
        Storage::disk('public')->delete($photo->file_path);
        $photo->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Convert a completed tech measure to a job.
     */
    public function convertToQuote(Request $request, $id)
    {
        $measure = TechMeasure::with('items')->findOrFail($id);

        if ($measure->items->isEmpty()) {
            return response()->json(['error' => 'No measurement items to convert.'], 422);
        }

        // Validate PDF attachment
        $request->validate([
            'pdf' => 'required|file|mimes:pdf|max:20480',
        ]);

        // Store the PDF
        $pdfPath = $request->file('pdf')->store('tech-measures/' . $id . '/job-docs', 'public');

        // Parse line items and measurement prices
        $lineItems = json_decode($request->input('line_items', '[]'), true) ?: [];
        $measurementPrices = json_decode($request->input('measurement_prices', '[]'), true) ?: [];

        // Calculate totals
        $lineItemsTotal = collect($lineItems)->sum('total');
        $measurementsTotal = collect($measurementPrices)->sum('price');
        $grandTotal = $lineItemsTotal + $measurementsTotal;

        // Store conversion data on the measure
        $measure->update([
            'status' => 'converted',
            'converted_at' => now(),
            'converted_by' => Auth::guard('vip')->id(),
            'job_data' => json_encode([
                'line_items' => $lineItems,
                'measurement_prices' => $measurementPrices,
                'line_items_total' => $lineItemsTotal,
                'measurements_total' => $measurementsTotal,
                'grand_total' => $grandTotal,
                'pdf_path' => $pdfPath,
            ]),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tech measure converted to job.',
        ]);
    }
}
