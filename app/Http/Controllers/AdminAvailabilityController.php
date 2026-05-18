<?php

namespace App\Http\Controllers;

use App\Models\AdminAvailability;
use App\Models\AdminAvailabilityOverride;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAvailabilityController extends Controller
{
    /**
     * Get current weekly availability + future overrides.
     */
    public function index()
    {
        $availability = AdminAvailability::orderBy('day_of_week')
            ->get()
            ->keyBy('day_of_week');

        $overrides = AdminAvailabilityOverride::where('override_date', '>=', today())
            ->orderBy('override_date')
            ->get();

        return response()->json([
            'availability' => $availability,
            'overrides'    => $overrides,
        ]);
    }

    /**
     * Save weekly availability (bulk update all 7 days).
     */
    public function saveWeekly(Request $request)
    {
        $validated = $request->validate([
            'days'                         => 'required|array',
            'days.*.day_of_week'           => 'required|integer|between:0,6',
            'days.*.is_available'          => 'required|boolean',
            'days.*.start_time'            => 'nullable|date_format:H:i',
            'days.*.end_time'              => 'nullable|date_format:H:i',
            'days.*.slot_duration'         => 'nullable|integer|min:15|max:480',
            'days.*.max_bookings_per_slot' => 'nullable|integer|min:1|max:50',
        ]);

        foreach ($validated['days'] as $day) {
            AdminAvailability::updateOrCreate(
                ['day_of_week' => $day['day_of_week']],
                [
                    'is_available'          => $day['is_available'],
                    'start_time'            => $day['start_time'] ?? '08:00',
                    'end_time'              => $day['end_time'] ?? '17:00',
                    'slot_duration'         => $day['slot_duration'] ?? 60,
                    'max_bookings_per_slot' => $day['max_bookings_per_slot'] ?? 5,
                    'created_by'            => Auth::id(),
                ]
            );
        }

        return response()->json(['success' => true, 'message' => 'Weekly availability updated.']);
    }

    /**
     * Add a date-specific override (day off or custom hours).
     */
    public function addOverride(Request $request)
    {
        $validated = $request->validate([
            'override_date'         => 'required|date|after_or_equal:today',
            'is_available'          => 'required|boolean',
            'start_time'            => 'nullable|date_format:H:i',
            'end_time'              => 'nullable|date_format:H:i',
            'max_bookings_per_slot' => 'nullable|integer|min:1|max:50',
            'reason'                => 'nullable|string|max:255',
        ]);

        $override = AdminAvailabilityOverride::updateOrCreate(
            ['override_date' => $validated['override_date']],
            array_merge($validated, ['created_by' => Auth::id()])
        );

        return response()->json(['success' => true, 'override' => $override]);
    }

    /**
     * Remove a date override.
     */
    public function removeOverride($id)
    {
        AdminAvailabilityOverride::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}
