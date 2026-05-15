<?php

namespace App\Http\Controllers;

use App\Models\CalendarSlot;
use App\Models\InstallationOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalendarController extends Controller
{
    /**
     * Admin calendar view — manage availability slots.
     */
    public function index(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));
        $startOfMonth = \Carbon\Carbon::parse($month . '-01')->startOfMonth();
        $endOfMonth   = $startOfMonth->copy()->endOfMonth();

        $slots = CalendarSlot::whereBetween('slot_date', [$startOfMonth, $endOfMonth])
            ->orderBy('slot_date')
            ->orderBy('slot_time')
            ->get()
            ->groupBy(fn($s) => $s->slot_date->format('Y-m-d'));

        $scheduledOrders = InstallationOrder::whereBetween('scheduled_date', [$startOfMonth, $endOfMonth])
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->orderBy('scheduled_date')
            ->get()
            ->groupBy(fn($o) => $o->scheduled_date instanceof \Carbon\Carbon
                ? $o->scheduled_date->format('Y-m-d')
                : \Carbon\Carbon::parse($o->scheduled_date)->format('Y-m-d'));

        return view('calendar.index', compact('slots', 'scheduledOrders', 'month', 'startOfMonth', 'endOfMonth'));
    }

    /**
     * Create a new availability slot.
     */
    public function storeSlot(Request $request)
    {
        $validated = $request->validate([
            'slot_date'    => 'required|date|after_or_equal:today',
            'slot_time'    => 'required|string|max:20',
            'max_bookings' => 'required|integer|min:1|max:20',
        ]);

        CalendarSlot::create([
            'slot_date'        => $validated['slot_date'],
            'slot_time'        => $validated['slot_time'],
            'max_bookings'     => $validated['max_bookings'],
            'current_bookings' => 0,
            'created_by'       => Auth::id(),
        ]);

        return redirect()->route('admin.calendar.index', ['month' => \Carbon\Carbon::parse($validated['slot_date'])->format('Y-m')])
            ->with('success', 'Slot created.');
    }

    /**
     * Update an existing slot.
     */
    public function updateSlot(Request $request, $id)
    {
        $slot = CalendarSlot::findOrFail($id);
        $validated = $request->validate([
            'max_bookings' => 'required|integer|min:1|max:20',
        ]);

        $slot->update(['max_bookings' => $validated['max_bookings']]);

        return redirect()->route('admin.calendar.index', ['month' => $slot->slot_date->format('Y-m')])
            ->with('success', 'Slot updated.');
    }

    /**
     * Delete a slot (only if no bookings).
     */
    public function deleteSlot($id)
    {
        $slot = CalendarSlot::findOrFail($id);

        if ($slot->current_bookings > 0) {
            return back()->with('error', 'Cannot delete a slot that has bookings.');
        }

        $slot->delete();

        return redirect()->route('admin.calendar.index', ['month' => $slot->slot_date->format('Y-m')])
            ->with('success', 'Slot removed.');
    }

    /**
     * Public booking page — customer picks an available slot for their order.
     */
    public function showBooking($orderId)
    {
        $order = InstallationOrder::findOrFail($orderId);

        if (!in_array($order->status, ['pending', 'scheduled'])) {
            abort(403, 'This order cannot be scheduled at this time.');
        }

        $slots = CalendarSlot::where('slot_date', '>=', today())
            ->whereColumn('current_bookings', '<', 'max_bookings')
            ->orderBy('slot_date')
            ->orderBy('slot_time')
            ->get()
            ->groupBy(fn($s) => $s->slot_date->format('Y-m-d'));

        return view('calendar.booking', compact('order', 'slots'));
    }

    /**
     * Confirm a booking — customer selects a slot.
     */
    public function confirmBooking(Request $request, $orderId)
    {
        $order = InstallationOrder::findOrFail($orderId);

        if (!in_array($order->status, ['pending', 'scheduled'])) {
            abort(403, 'This order cannot be scheduled at this time.');
        }

        $validated = $request->validate([
            'slot_id' => 'required|integer|exists:install_calendar_slots,id',
        ]);

        $slot = CalendarSlot::findOrFail($validated['slot_id']);

        if (!$slot->isAvailable()) {
            return back()->with('error', 'This slot is no longer available. Please choose another.');
        }

        // Book the slot
        $slot->increment('current_bookings');

        $order->update([
            'scheduled_date' => $slot->slot_date,
            'scheduled_slot' => $slot->slot_time,
            'status'         => 'scheduled',
        ]);

        return view('calendar.confirmed', compact('order', 'slot'));
    }
}
