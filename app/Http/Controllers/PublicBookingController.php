<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Installer\InstallerAvailabilityController;
use App\Models\AdminAvailability;
use App\Models\AdminAvailabilityOverride;
use App\Models\CalendarEvent;
use App\Models\InstallerBooking;
use App\Models\InstallerService;
use App\Models\InstallationOrder;
use App\Models\VipUser;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PublicBookingController extends Controller
{
    /**
     * Public booking page for a specific installer (no login required).
     * URL: /book/{slug}
     */
    public function show($slug)
    {
        $installer = VipUser::where('booking_slug', $slug)
            ->where('role', 'installer')
            ->where('status', 'active')
            ->firstOrFail();

        $services = InstallerService::where('installer_id', $installer->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $selectedDate = request('date', today()->addDay()->format('Y-m-d'));
        $slots = InstallerAvailabilityController::getAvailableSlots($installer->id, $selectedDate);

        return view('public.book-installer', compact('installer', 'services', 'selectedDate', 'slots'));
    }

    /**
     * Get available slots via AJAX.
     */
    public function getSlots(Request $request, $slug)
    {
        $installer = VipUser::where('booking_slug', $slug)
            ->where('role', 'installer')
            ->firstOrFail();

        $request->validate(['date' => 'required|date|after_or_equal:today']);

        $slots = InstallerAvailabilityController::getAvailableSlots($installer->id, $request->date);

        return response()->json(['slots' => $slots]);
    }

    /**
     * Confirm booking (public, no login required).
     */
    public function confirm(Request $request, $slug)
    {
        $installer = VipUser::where('booking_slug', $slug)
            ->where('role', 'installer')
            ->where('status', 'active')
            ->firstOrFail();

        $validated = $request->validate([
            'customer_name'   => 'required|string|max:255',
            'customer_email'  => 'required|email|max:255',
            'customer_phone'  => 'nullable|string|max:50',
            'install_address' => 'required|string|max:500',
            'install_city'    => 'nullable|string|max:100',
            'install_state'   => 'nullable|string|max:50',
            'install_zip'     => 'nullable|string|max:20',
            'booking_date'    => 'required|date|after_or_equal:today',
            'booking_time'    => 'required|date_format:H:i',
            'service_type'    => 'required|string|max:100',
            'description'     => 'nullable|string|max:2000',
        ]);

        // Verify slot is still available
        $slots = InstallerAvailabilityController::getAvailableSlots($installer->id, $validated['booking_date']);
        $slotAvailable = collect($slots)->first(fn($s) => $s['time'] === $validated['booking_time'] && $s['available']);

        if (!$slotAvailable) {
            return back()->with('error', 'This time slot is no longer available. Please choose another.')->withInput();
        }

        // Generate booking number
        $last = InstallerBooking::orderByDesc('id')->first();
        $nextNum = $last ? ($last->id + 1) : 1;
        $bookingNumber = 'BK-' . str_pad($nextNum, 5, '0', STR_PAD_LEFT);

        // Match to existing customer if email matches
        $customer = VipUser::where('email', $validated['customer_email'])->where('role', 'customer')->first();

        InstallerBooking::create([
            'booking_number'  => $bookingNumber,
            'installer_id'    => $installer->id,
            'customer_id'     => $customer?->id,
            'customer_name'   => $validated['customer_name'],
            'customer_email'  => $validated['customer_email'],
            'customer_phone'  => $validated['customer_phone'],
            'install_address' => $validated['install_address'],
            'install_city'    => $validated['install_city'],
            'install_state'   => $validated['install_state'],
            'install_zip'     => $validated['install_zip'],
            'booking_date'    => $validated['booking_date'],
            'booking_time'    => $validated['booking_time'],
            'service_type'    => $validated['service_type'],
            'description'     => $validated['description'],
            'status'          => 'pending',
        ]);

        $installerName = $installer->company_name ?: $installer->name;

        return redirect()->route('public.book.success', $slug)->with('booking', [
            'number'    => $bookingNumber,
            'installer' => $installerName,
            'date'      => \Carbon\Carbon::parse($validated['booking_date'])->format('l, F j, Y'),
            'time'      => date('g:i A', strtotime($validated['booking_time'])),
        ]);
    }

    /**
     * Booking success page.
     */
    public function success($slug)
    {
        $installer = VipUser::where('booking_slug', $slug)->firstOrFail();
        $booking = session('booking');
        $installerName = $installer->company_name ?: $installer->name;

        return view('public.book-success', compact('installer', 'booking', 'installerName'));
    }

    /**
     * Public website booking (goes to VIP admin, not an installer).
     */
    public function websiteBook()
    {
        $selectedDate = request('date', today()->addDay()->format('Y-m-d'));
        $slots = self::getAdminSlots($selectedDate);

        return view('public.book-website', compact('selectedDate', 'slots'));
    }

    /**
     * Get admin calendar slots for a date (AJAX).
     */
    public function websiteSlots(Request $request)
    {
        $request->validate(['date' => 'required|date|after_or_equal:today']);
        $slots = self::getAdminSlots($request->date);

        return response()->json(['slots' => $slots]);
    }

    /**
     * Generate available time slots from admin availability settings.
     */
    public static function getAdminSlots(string $date): array
    {
        $dateObj = Carbon::parse($date);
        $dayOfWeek = $dateObj->dayOfWeek; // 0=Sun, 6=Sat

        // Check for date-specific override first
        $override = AdminAvailabilityOverride::where('override_date', $date)->first();

        if ($override) {
            if (!$override->is_available) {
                return []; // Day off
            }
            $startTime = $override->start_time ?? '08:00';
            $endTime = $override->end_time ?? '17:00';
            $maxBookings = $override->max_bookings_per_slot ?? 5;
            $slotDuration = 60; // overrides use default 60 min
        } else {
            // Use weekly schedule
            $avail = AdminAvailability::where('day_of_week', $dayOfWeek)->first();

            if (!$avail || !$avail->is_available) {
                return []; // Not available this day
            }

            $startTime = $avail->start_time ?? '08:00';
            $endTime = $avail->end_time ?? '17:00';
            $maxBookings = $avail->max_bookings_per_slot ?? 5;
            $slotDuration = $avail->slot_duration ?? 60;
        }

        // Count existing bookings per time slot for this date
        $existingBookings = InstallationOrder::where('scheduled_date', $date)
            ->where('source', 'website')
            ->whereNotIn('status', ['cancelled'])
            ->selectRaw('scheduled_slot, COUNT(*) as cnt')
            ->groupBy('scheduled_slot')
            ->pluck('cnt', 'scheduled_slot')
            ->toArray();

        // Also count calendar events that overlap this date
        $eventBookings = CalendarEvent::where('event_date', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('end_date')->where('event_date', $date)
                  ->orWhere('end_date', '>=', $date);
            })
            ->whereNull('rescheduled_to')
            ->count();

        // Generate time slots
        $slots = [];
        $current = Carbon::parse($date . ' ' . $startTime);
        $end = Carbon::parse($date . ' ' . $endTime);

        while ($current->lt($end)) {
            $timeStr = $current->format('H:i');
            $booked = ($existingBookings[$timeStr] ?? 0);
            $remaining = max(0, $maxBookings - $booked);

            $slots[] = [
                'time'      => $timeStr,
                'display'   => $current->format('g:i A'),
                'available' => $remaining > 0,
                'remaining' => $remaining,
                'max'       => $maxBookings,
            ];

            $current->addMinutes($slotDuration);
        }

        return $slots;
    }

    /**
     * Confirm website booking (goes to admin as VIP customer).
     */
    public function websiteConfirm(Request $request)
    {
        $validated = $request->validate([
            'customer_name'   => 'required|string|max:255',
            'customer_email'  => 'required|email|max:255',
            'customer_phone'  => 'required|string|max:50',
            'install_address' => 'required|string|max:500',
            'install_city'    => 'nullable|string|max:100',
            'install_state'   => 'nullable|string|max:50',
            'install_zip'     => 'nullable|string|max:20',
            'booking_date'    => 'required|date|after_or_equal:today',
            'booking_time'    => 'required|string',
            'service_type'    => 'required|string|max:100',
            'description'     => 'nullable|string|max:2000',
        ]);

        // Verify slot is still available
        $slots = self::getAdminSlots($validated['booking_date']);
        $slotAvailable = collect($slots)->first(fn($s) => $s['time'] === $validated['booking_time'] && $s['available']);

        if (!$slotAvailable) {
            return back()->with('error', 'This time slot is no longer available. Please choose another.')->withInput();
        }

        // Create installation order for admin
        InstallationOrder::create([
            'customer_name'    => $validated['customer_name'],
            'customer_email'   => $validated['customer_email'],
            'customer_phone'   => $validated['customer_phone'],
            'install_address'  => $validated['install_address'],
            'install_city'     => $validated['install_city'],
            'install_state'    => $validated['install_state'],
            'install_zip'      => $validated['install_zip'],
            'service_type'     => $validated['service_type'],
            'description'      => $validated['description'],
            'scheduled_date'   => $validated['booking_date'],
            'scheduled_slot'   => $validated['booking_time'],
            'status'           => 'scheduled',
            'source'           => 'website',
        ]);

        $dateFormatted = Carbon::parse($validated['booking_date'])->format('l, F j, Y');
        $timeFormatted = Carbon::parse($validated['booking_time'])->format('g:i A');

        return redirect()->route('public.book.website.success')->with('booking', [
            'date' => $dateFormatted,
            'time' => $timeFormatted,
        ]);
    }

    public function websiteSuccess()
    {
        return view('public.book-website-success');
    }
}
