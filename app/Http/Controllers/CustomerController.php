<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Installer\InstallerAvailabilityController;
use App\Models\InstallerBooking;
use App\Models\VipQuote as Quote;
use App\Models\VipUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    /**
     * Customer dashboard — bookings, quotes.
     */
    public function dashboard()
    {
        $user = Auth::user();

        // Quotes sent to this customer
        $quotes = Quote::with('items')
            ->where(function ($q) use ($user) {
                $q->where('billing_email', $user->email)
                  ->orWhere('customer_number', $user->email);
            })
            ->where('status', 'sent')
            ->orderBy('created_at', 'desc')
            ->get();

        // Customer's bookings
        $bookings = InstallerBooking::where('customer_id', $user->id)
            ->orWhere('customer_email', $user->email)
            ->orderBy('booking_date', 'desc')
            ->get();

        $upcoming = $bookings->where('booking_date', '>=', today())
            ->whereIn('status', ['pending', 'confirmed']);
        $past = $bookings->where('booking_date', '<', today())
            ->merge($bookings->where('status', 'completed'));

        return view('customer.dashboard', compact('quotes', 'bookings', 'upcoming', 'past'));
    }

    /**
     * Show booking page — pick an installer and time slot.
     */
    public function bookInstallation(Request $request)
    {
        $user = Auth::user();

        // Find installers who have quoted this customer (most relevant)
        $quotedInstallerNames = Quote::where(function ($q) use ($user) {
                $q->where('billing_email', $user->email)
                  ->orWhere('customer_number', $user->email);
            })
            ->pluck('entered_by')
            ->unique();

        $quotedInstallers = VipUser::where('role', 'installer')
            ->where('status', 'active')
            ->whereIn('name', $quotedInstallerNames)
            ->get();

        // Also list all active installers
        $allInstallers = VipUser::where('role', 'installer')
            ->where('status', 'active')
            ->orderBy('company_name')
            ->orderBy('name')
            ->get();

        // Pre-selected installer (from query string, e.g. from a quote)
        $selectedInstaller = $request->get('installer_id');
        $selectedDate = $request->get('date', today()->addDay()->format('Y-m-d'));

        // Get available slots if installer selected
        $slots = [];
        if ($selectedInstaller) {
            $slots = InstallerAvailabilityController::getAvailableSlots($selectedInstaller, $selectedDate);
        }

        return view('customer.book', compact(
            'quotedInstallers', 'allInstallers', 'selectedInstaller',
            'selectedDate', 'slots'
        ));
    }

    /**
     * API: Get available slots for a date/installer combo.
     */
    public function getSlots(Request $request)
    {
        $request->validate([
            'installer_id' => 'required|exists:vip_users,id',
            'date'         => 'required|date|after_or_equal:today',
        ]);

        $slots = InstallerAvailabilityController::getAvailableSlots(
            $request->installer_id,
            $request->date
        );

        return response()->json(['slots' => $slots]);
    }

    /**
     * Book an installation directly.
     */
    public function confirmBooking(Request $request)
    {
        $validated = $request->validate([
            'installer_id'   => 'required|exists:vip_users,id',
            'booking_date'   => 'required|date|after_or_equal:today',
            'booking_time'   => 'required|date_format:H:i',
            'service_type'   => 'required|string|max:100',
            'install_address' => 'required|string|max:500',
            'install_city'   => 'nullable|string|max:100',
            'install_state'  => 'nullable|string|max:50',
            'install_zip'    => 'nullable|string|max:20',
            'description'    => 'nullable|string|max:2000',
            'quote_id'       => 'nullable|exists:vip_quotes,id',
        ]);

        $user = Auth::user();

        // Check slot is still available
        $slots = InstallerAvailabilityController::getAvailableSlots(
            $validated['installer_id'],
            $validated['booking_date']
        );

        $slotAvailable = collect($slots)->first(fn($s) => $s['time'] === $validated['booking_time'] && $s['available']);

        if (!$slotAvailable) {
            return back()->with('error', 'This time slot is no longer available. Please choose another.');
        }

        // Generate booking number: BK-XXXX
        $last = InstallerBooking::orderByDesc('id')->first();
        $nextNum = $last ? ($last->id + 1) : 1;
        $bookingNumber = 'BK-' . str_pad($nextNum, 5, '0', STR_PAD_LEFT);

        InstallerBooking::create([
            'booking_number'  => $bookingNumber,
            'installer_id'    => $validated['installer_id'],
            'customer_id'     => $user->id,
            'customer_name'   => $user->name,
            'customer_email'  => $user->email,
            'customer_phone'  => $user->phone,
            'install_address' => $validated['install_address'],
            'install_city'    => $validated['install_city'],
            'install_state'   => $validated['install_state'],
            'install_zip'     => $validated['install_zip'],
            'booking_date'    => $validated['booking_date'],
            'booking_time'    => $validated['booking_time'],
            'service_type'    => $validated['service_type'],
            'description'     => $validated['description'],
            'status'          => 'pending',
            'quote_id'        => $validated['quote_id'] ?? null,
        ]);

        $installer = VipUser::find($validated['installer_id']);
        $installerName = $installer->company_name ?: $installer->name;

        return redirect()->route('customer.dashboard')
            ->with('success', "Booking request submitted to {$installerName} for " .
                \Carbon\Carbon::parse($validated['booking_date'])->format('l, F j, Y') .
                ' at ' . date('g:i A', strtotime($validated['booking_time'])));
    }
}
