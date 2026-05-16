<?php

namespace App\Http\Controllers;

use App\Models\CalendarSlot;
use App\Models\InstallationOrder;
use App\Models\Quote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    /**
     * Customer dashboard — their bookings, installations, and quotes.
     */
    public function dashboard()
    {
        $user = Auth::user();

        $orders = InstallationOrder::where('customer_email', $user->email)
            ->orderBy('created_at', 'desc')
            ->get();

        $upcoming = $orders->whereIn('status', ['scheduled', 'in_progress']);
        $completed = $orders->where('status', 'completed');

        // Quotes sent to this customer (matched by email or customer_number)
        $quotes = Quote::with('items')
            ->where(function ($q) use ($user) {
                $q->where('billing_email', $user->email)
                  ->orWhere('customer_number', $user->email);
            })
            ->where('status', 'sent')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('customer.dashboard', compact('orders', 'upcoming', 'completed', 'quotes'));
    }

    /**
     * Book installation — pick from available slots.
     */
    public function bookInstallation()
    {
        $slots = CalendarSlot::where('slot_date', '>=', today())
            ->whereColumn('current_bookings', '<', 'max_bookings')
            ->orderBy('slot_date')
            ->orderBy('slot_time')
            ->get()
            ->groupBy(fn($s) => $s->slot_date->format('Y-m-d'));

        $user = Auth::user();

        // Get orders that belong to this customer and are pending/unscheduled
        $pendingOrders = InstallationOrder::where('customer_email', $user->email)
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('customer.book', compact('slots', 'pendingOrders'));
    }

    /**
     * Confirm booking for a specific order.
     */
    public function confirmBooking(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|integer|exists:installation_orders,id',
            'slot_id'  => 'required|integer|exists:install_calendar_slots,id',
        ]);

        $user = Auth::user();
        $order = InstallationOrder::where('id', $validated['order_id'])
            ->where('customer_email', $user->email)
            ->where('status', 'pending')
            ->firstOrFail();

        $slot = CalendarSlot::findOrFail($validated['slot_id']);

        if (!$slot->isAvailable()) {
            return back()->with('error', 'This slot is no longer available. Please choose another.');
        }

        $slot->increment('current_bookings');

        $order->update([
            'scheduled_date' => $slot->slot_date,
            'scheduled_slot' => $slot->slot_time,
            'status'         => 'scheduled',
        ]);

        return redirect()->route('customer.dashboard')
            ->with('success', 'Installation booked for ' . $slot->slot_date->format('l, F j, Y') . ' — ' . $slot->slot_time);
    }
}
