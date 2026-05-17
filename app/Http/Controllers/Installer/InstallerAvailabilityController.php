<?php

namespace App\Http\Controllers\Installer;

use App\Http\Controllers\Controller;
use App\Models\InstallerAvailability;
use App\Models\InstallerAvailabilityOverride;
use App\Models\InstallerBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InstallerAvailabilityController extends Controller
{
    /**
     * Show availability settings.
     */
    public function index()
    {
        $availability = InstallerAvailability::where('installer_id', Auth::id())
            ->orderBy('day_of_week')
            ->get()
            ->keyBy('day_of_week');

        $overrides = InstallerAvailabilityOverride::where('installer_id', Auth::id())
            ->where('override_date', '>=', today())
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
            'days'                        => 'required|array',
            'days.*.day_of_week'          => 'required|integer|between:0,6',
            'days.*.is_available'         => 'required|boolean',
            'days.*.start_time'           => 'nullable|date_format:H:i',
            'days.*.end_time'             => 'nullable|date_format:H:i',
            'days.*.slot_duration'        => 'nullable|integer|min:15|max:480',
            'days.*.max_bookings_per_slot' => 'nullable|integer|min:1|max:50',
        ]);

        foreach ($validated['days'] as $day) {
            InstallerAvailability::updateOrCreate(
                ['installer_id' => Auth::id(), 'day_of_week' => $day['day_of_week']],
                [
                    'is_available'         => $day['is_available'],
                    'start_time'           => $day['start_time'] ?? '08:00',
                    'end_time'             => $day['end_time'] ?? '17:00',
                    'slot_duration'        => $day['slot_duration'] ?? 60,
                    'max_bookings_per_slot' => $day['max_bookings_per_slot'] ?? 5,
                ]
            );
        }

        return response()->json(['success' => true, 'message' => 'Availability updated.']);
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

        $override = InstallerAvailabilityOverride::updateOrCreate(
            ['installer_id' => Auth::id(), 'override_date' => $validated['override_date']],
            $validated
        );

        return response()->json(['success' => true, 'override' => $override]);
    }

    /**
     * Remove a date override.
     */
    public function removeOverride($id)
    {
        InstallerAvailabilityOverride::where('installer_id', Auth::id())->findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    /**
     * Get available slots for a specific date (used by customer booking).
     */
    public static function getAvailableSlots(int $installerId, string $date): array
    {
        $carbon = \Carbon\Carbon::parse($date);
        $dayOfWeek = $carbon->dayOfWeek;

        // Check for date-specific override
        $override = InstallerAvailabilityOverride::where('installer_id', $installerId)
            ->where('override_date', $date)
            ->first();

        if ($override) {
            if (!$override->is_available) return [];

            $startTime = $override->start_time;
            $endTime = $override->end_time;
            $maxBookings = $override->max_bookings_per_slot;
        } else {
            // Use weekly schedule
            $avail = InstallerAvailability::where('installer_id', $installerId)
                ->where('day_of_week', $dayOfWeek)
                ->first();

            if (!$avail || !$avail->is_available) {
                // No availability set = assume fully open (Mon-Fri 8-5, 60min slots, 5 max)
                if ($dayOfWeek === 0 || $dayOfWeek === 6) return []; // closed weekends by default
                $startTime = '08:00';
                $endTime = '17:00';
                $maxBookings = 5;
                $slotDuration = 60;
            } else {
                $startTime = $avail->start_time;
                $endTime = $avail->end_time;
                $maxBookings = $avail->max_bookings_per_slot;
                $slotDuration = $avail->slot_duration;
            }
        }

        if (!isset($slotDuration)) {
            $avail = InstallerAvailability::where('installer_id', $installerId)
                ->where('day_of_week', $dayOfWeek)
                ->first();
            $slotDuration = $avail ? $avail->slot_duration : 60;
        }

        // Generate time slots
        $slots = [];
        $start = strtotime($startTime);
        $end = strtotime($endTime);
        $duration = $slotDuration * 60;

        // Count existing bookings per slot
        $bookings = InstallerBooking::where('installer_id', $installerId)
            ->where('booking_date', $date)
            ->whereIn('status', ['pending', 'confirmed'])
            ->get()
            ->groupBy(fn($b) => substr($b->booking_time, 0, 5))
            ->map->count();

        while ($start + $duration <= $end) {
            $slotTime = date('H:i', $start);
            $currentBookings = $bookings[$slotTime] ?? 0;
            $remaining = $maxBookings - $currentBookings;

            $slots[] = [
                'time'       => $slotTime,
                'label'      => date('g:i A', $start) . ' – ' . date('g:i A', $start + $duration),
                'booked'     => $currentBookings,
                'max'        => $maxBookings,
                'remaining'  => max(0, $remaining),
                'available'  => $remaining > 0,
            ];

            $start += $duration;
        }

        return $slots;
    }

    /**
     * API: Get slots for a date (for calendar/booking UI).
     */
    public function slotsForDate(Request $request)
    {
        $date = $request->get('date', today()->format('Y-m-d'));
        $installerId = $request->get('installer_id', Auth::id());

        $slots = self::getAvailableSlots($installerId, $date);

        return response()->json(['slots' => $slots, 'date' => $date]);
    }

    /**
     * Get bookings for a date range (for installer calendar).
     */
    public function bookings(Request $request)
    {
        $start = $request->get('start', today()->startOfMonth()->format('Y-m-d'));
        $end = $request->get('end', today()->endOfMonth()->format('Y-m-d'));

        $bookings = InstallerBooking::where('installer_id', Auth::id())
            ->whereBetween('booking_date', [$start, $end])
            ->orderBy('booking_date')
            ->orderBy('booking_time')
            ->get();

        return response()->json(['bookings' => $bookings]);
    }

    /**
     * Update a booking status.
     */
    public function updateBooking(Request $request, $id)
    {
        $booking = InstallerBooking::where('installer_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'status'          => 'nullable|in:pending,confirmed,completed,cancelled',
            'installer_notes' => 'nullable|string|max:2000',
        ]);

        $booking->update($validated);

        return response()->json(['success' => true, 'booking' => $booking->fresh()]);
    }
}
