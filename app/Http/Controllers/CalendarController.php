<?php

namespace App\Http\Controllers;

use App\Models\AdminAvailability;
use App\Models\CalendarEvent;
use App\Models\CalendarSlot;
use App\Models\InstallationOrder;
use App\Models\Job;
use App\Models\Service;
use App\Mail\ScheduleNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class CalendarController extends Controller
{
    /**
     * Admin calendar view — redesigned to match installer calendar style.
     */
    public function index(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));
        $startOfMonth = \Carbon\Carbon::parse($month . '-01')->startOfMonth();
        $endOfMonth   = $startOfMonth->copy()->endOfMonth();

        // For the grid: start from Sunday of the first week, end Saturday of last week
        $gridStart = $startOfMonth->copy()->startOfWeek(\Carbon\Carbon::SUNDAY);
        $gridEnd   = $endOfMonth->copy()->endOfWeek(\Carbon\Carbon::SATURDAY);

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

        // Build service color maps
        try {
            $serviceColors = Service::pluck('color', 'name')->toArray();
            $serviceColorById = Service::pluck('color', 'id')->toArray();
        } catch (\Exception $e) {
            $serviceColors = [];
            $serviceColorById = [];
        }

        // Scheduled Jobs for the calendar
        $allJobs = Job::with('service')
            ->whereNotNull('scheduled_date')
            ->whereBetween('scheduled_date', [$startOfMonth, $endOfMonth])
            ->orderBy('scheduled_date')
            ->get();

        $scheduledJobs = $allJobs->groupBy(fn($j) => $j->scheduled_date->format('Y-m-d'));

        // Calendar events (standalone, not from jobs)
        $calendarEvents = CalendarEvent::with('service')
            ->whereBetween('event_date', [$startOfMonth, $endOfMonth])
            ->orderBy('event_date')
            ->get()
            ->groupBy(fn($e) => $e->event_date->format('Y-m-d'));

        // Stats
        $totalJobs = $allJobs->count();
        $pendingJobs = $allJobs->where('status', 'pending')->count();
        $scheduledCount = $allJobs->where('status', 'scheduled')->count();
        $inProgressCount = $allJobs->where('status', 'in_progress')->count();
        $completedCount = Job::where('status', 'completed')
            ->whereBetween('scheduled_date', [$startOfMonth, $endOfMonth])
            ->count();
        $totalOrders = $scheduledOrders->flatten()->count();
        $totalEvents = CalendarEvent::whereBetween('event_date', [$startOfMonth, $endOfMonth])->count();

        // Availability for the modal
        $availability = AdminAvailability::orderBy('day_of_week')->get()->keyBy('day_of_week');

        // Services for the add event form
        $services = Service::where('is_active', true)->orderBy('name')->get();

        return view('calendar.index', compact(
            'slots', 'scheduledOrders', 'scheduledJobs', 'calendarEvents',
            'month', 'startOfMonth', 'endOfMonth', 'gridStart', 'gridEnd',
            'serviceColors', 'serviceColorById',
            'totalJobs', 'pendingJobs', 'scheduledCount', 'inProgressCount', 'completedCount',
            'totalOrders', 'totalEvents',
            'availability', 'services'
        ));
    }

    /**
     * Store a calendar event (standalone).
     */
    public function storeEvent(Request $request)
    {
        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string',
            'event_date'     => 'required|date',
            'event_time'     => 'nullable|string|max:20',
            'end_time'       => 'nullable|string|max:20',
            'end_date'       => 'nullable|date|after_or_equal:event_date',
            'service_id'     => 'nullable|exists:vip_services,id',
            'address'        => 'nullable|string|max:500',
            'customer_name'  => 'nullable|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:50',
        ]);

        $customerEmail = $validated['customer_email'] ?? null;
        $customerName  = $validated['customer_name'] ?? 'Customer';

        $validated['created_by'] = Auth::id();

        // Auto-set color from selected service (fallback to gold)
        if (!empty($validated['service_id'])) {
            $svcColor = Service::where('id', $validated['service_id'])->value('color');
            $validated['color'] = $svcColor ?: '#c9a84c';
        } else {
            $validated['color'] = '#c9a84c';
        }

        CalendarEvent::create($validated);

        // Send email notification to client if email provided
        if ($customerEmail && filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
            try {
                Mail::to($customerEmail)->send(new ScheduleNotification([
                    'title'         => $validated['title'],
                    'event_date'    => $validated['event_date'],
                    'start_time'    => $validated['event_time'] ?? null,
                    'end_time'      => $validated['end_time'] ?? null,
                    'address'       => $validated['address'] ?? null,
                    'description'   => $validated['description'] ?? null,
                    'customer_name' => $customerName,
                    'type'          => 'event',
                ]));
            } catch (\Exception $e) {
                // Log but don't block — email failure shouldn't prevent event creation
                \Log::warning('Schedule email failed: ' . $e->getMessage());
            }
        }

        return redirect()->route('admin.calendar.index', ['month' => \Carbon\Carbon::parse($validated['event_date'])->format('Y-m')])
            ->with('success', 'Event added to calendar.' . ($customerEmail ? ' Email notification sent.' : ''));
    }

    /**
     * Show a calendar event (JSON for edit modal).
     */
    public function showEvent($id)
    {
        return response()->json(CalendarEvent::findOrFail($id));
    }

    /**
     * Update a calendar event.
     */
    public function updateEvent(Request $request, $id)
    {
        $event = CalendarEvent::findOrFail($id);

        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string',
            'event_date'     => 'required|date',
            'event_time'     => 'nullable|string|max:20',
            'end_time'       => 'nullable|string|max:20',
            'end_date'       => 'nullable|date|after_or_equal:event_date',
            'service_id'     => 'nullable|exists:vip_services,id',
            'address'        => 'nullable|string|max:500',
            'customer_name'  => 'nullable|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:50',
        ]);

        // Auto-set color from selected service (fallback to gold)
        if (!empty($validated['service_id'])) {
            $svcColor = Service::where('id', $validated['service_id'])->value('color');
            $validated['color'] = $svcColor ?: '#c9a84c';
        } else {
            $validated['color'] = '#c9a84c';
        }

        $event->update($validated);

        return redirect()->route('admin.calendar.index', ['month' => \Carbon\Carbon::parse($validated['event_date'])->format('Y-m')])
            ->with('success', 'Event updated.');
    }

    /**
     * Send a reminder email for an event.
     */
    public function sendReminder($id)
    {
        $event = CalendarEvent::findOrFail($id);

        if (!$event->customer_email) {
            return response()->json(['error' => 'No client email on this event.'], 422);
        }

        try {
            Mail::to($event->customer_email)->send(new ScheduleNotification([
                'title'         => $event->title,
                'event_date'    => $event->event_date->format('Y-m-d'),
                'start_time'    => $event->event_time,
                'end_time'      => $event->end_time,
                'address'       => $event->address,
                'description'   => $event->description,
                'customer_name' => $event->customer_name ?? 'Customer',
                'type'          => 'event',
            ]));

            return response()->json(['success' => true, 'message' => 'Reminder sent to ' . $event->customer_email]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to send: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete a calendar event.
     */
    public function deleteEvent($id)
    {
        CalendarEvent::findOrFail($id)->delete();
        return back()->with('success', 'Event removed.');
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

        $slot->increment('current_bookings');

        $order->update([
            'scheduled_date' => $slot->slot_date,
            'scheduled_slot' => $slot->slot_time,
            'status'         => 'scheduled',
        ]);

        return view('calendar.confirmed', compact('order', 'slot'));
    }
}
