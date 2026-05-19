<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use App\Models\Crew;
use App\Models\TechMeasure;
use App\Models\TechMeasureItem;
use App\Models\TechMeasurePhoto;
use App\Models\VipUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        return view('admin.tech-measures.index', compact('measures', 'status', 'techs', 'crews'));
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
     * Convert a completed tech measure to a quote.
     */
    public function convertToQuote($id)
    {
        $measure = TechMeasure::with('items')->findOrFail($id);

        if ($measure->items->isEmpty()) {
            return response()->json(['error' => 'No measurement items to convert.'], 422);
        }

        // Create quote from measurement items
        $quote = \App\Models\VipQuote::create([
            'quote_number' => 'VQ-' . str_pad(\App\Models\VipQuote::max('id') + 1, 5, '0', STR_PAD_LEFT),
            'customer_name' => $measure->customer_name,
            'customer_email' => $measure->customer_email,
            'customer_phone' => $measure->customer_phone,
            'address' => $measure->fullAddress(),
            'status' => 'draft',
            'created_by' => Auth::guard('vip')->id(),
        ]);

        foreach ($measure->items as $item) {
            \App\Models\VipQuoteItem::create([
                'quote_id' => $quote->id,
                'description' => $item->description,
                'series_id' => $item->series_id,
                'series_type' => $item->series_type,
                'width' => $item->width,
                'height' => $item->height,
                'qty' => $item->qty,
                'glass' => $item->glass,
                'grid' => $item->grid,
                'color_config' => $item->color_config,
                'color_exterior' => $item->color_exterior,
                'color_exterior_custom' => $item->color_exterior_custom,
                'color_interior' => $item->color_interior,
                'color_interior_custom' => $item->color_interior_custom,
                'frame_type' => $item->frame_type,
                'glass_type' => $item->glass_type,
                'tempered' => $item->tempered,
                'tempered_fields' => $item->tempered_fields,
                'grid_pattern' => $item->grid_pattern,
                'grid_profile' => $item->grid_profile,
                'grid_detail' => $item->grid_detail,
                'retrofit_bottom_only' => $item->retrofit_bottom_only,
                'no_logo_lock' => $item->no_logo_lock,
                'double_lock' => $item->double_lock,
                'custom_lock_position' => $item->custom_lock_position,
                'custom_vent_latch' => $item->custom_vent_latch,
                'knocked_down' => $item->knocked_down,
                'shape_definition_id' => $item->shape_definition_id,
                'shape_params' => $item->shape_params,
                'shape_code' => $item->shape_code,
                'panel_dimensions' => $item->panel_dimensions,
                'internal_note' => $item->notes,
            ]);
        }

        $measure->update(['status' => 'converted']);

        return response()->json([
            'success' => true,
            'quote_id' => $quote->id,
            'message' => 'Quote created from tech measure.',
        ]);
    }
}
