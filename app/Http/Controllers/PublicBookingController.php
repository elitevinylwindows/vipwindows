<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Installer\InstallerAvailabilityController;
use App\Models\InstallerBooking;
use App\Models\InstallerService;
use App\Models\VipUser;
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

        // Use admin's calendar slots
        $slots = \App\Models\CalendarSlot::where('slot_date', $selectedDate)
            ->whereColumn('current_bookings', '<', 'max_bookings')
            ->orderBy('slot_time')
            ->get();

        return view('public.book-website', compact('selectedDate', 'slots'));
    }

    /**
     * Get admin calendar slots for a date.
     */
    public function websiteSlots(Request $request)
    {
        $request->validate(['date' => 'required|date|after_or_equal:today']);

        $slots = \App\Models\CalendarSlot::where('slot_date', $request->date)
            ->orderBy('slot_time')
            ->get()
            ->map(fn($s) => [
                'id'        => $s->id,
                'time'      => $s->slot_time,
                'remaining' => $s->bookingsRemaining(),
                'available' => $s->isAvailable(),
            ]);

        return response()->json(['slots' => $slots]);
    }

    /**
     * Confirm website booking (goes to admin as VIP customer).
     */
    public function websiteConfirm(Request $request)
    {
        $validated = $request->validate([
            'customer_name'   => 'required|string|max:255',
            'customer_email'  => 'required|email|max:255',
            'customer_phone'  => 'nullable|string|max:50',
            'install_address' => 'required|string|max:500',
            'install_city'    => 'nullable|string|max:100',
            'install_state'   => 'nullable|string|max:50',
            'install_zip'     => 'nullable|string|max:20',
            'slot_id'         => 'required|exists:install_calendar_slots,id',
            'service_type'    => 'required|string|max:100',
            'description'     => 'nullable|string|max:2000',
        ]);

        $slot = \App\Models\CalendarSlot::findOrFail($validated['slot_id']);

        if (!$slot->isAvailable()) {
            return back()->with('error', 'This slot is no longer available.')->withInput();
        }

        // Increment slot bookings
        $slot->increment('current_bookings');

        // Create installation order for admin
        \App\Models\InstallationOrder::create([
            'customer_name'    => $validated['customer_name'],
            'customer_email'   => $validated['customer_email'],
            'customer_phone'   => $validated['customer_phone'],
            'install_address'  => $validated['install_address'],
            'install_city'     => $validated['install_city'],
            'install_state'    => $validated['install_state'],
            'install_zip'      => $validated['install_zip'],
            'service_type'     => $validated['service_type'],
            'description'      => $validated['description'],
            'scheduled_date'   => $slot->slot_date,
            'scheduled_slot'   => $slot->slot_time,
            'status'           => 'scheduled',
            'source'           => 'website',
        ]);

        return redirect()->route('public.book.website.success')->with('booking', [
            'date' => $slot->slot_date->format('l, F j, Y'),
            'time' => $slot->slot_time,
        ]);
    }

    public function websiteSuccess()
    {
        return view('public.book-website-success');
    }
}
