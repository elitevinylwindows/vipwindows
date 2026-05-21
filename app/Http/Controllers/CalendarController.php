<?php

namespace App\Http\Controllers;

use App\Models\AdminAvailability;
use App\Models\CalendarEvent;
use App\Models\CalendarSlot;
use App\Models\Crew;
use App\Models\InstallationOrder;
use App\Models\Job;
use App\Models\Service;
use App\Models\TechMeasure;
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

        // Calendar events (standalone, not from jobs) — includes multi-day spanning
        $calendarEventsRaw = CalendarEvent::with('service', 'crew')
            ->where(function ($q) use ($startOfMonth, $endOfMonth) {
                $q->whereBetween('event_date', [$startOfMonth, $endOfMonth])
                  ->orWhere(function ($q2) use ($startOfMonth) {
                      $q2->where('event_date', '<', $startOfMonth)
                          ->where('end_date', '>=', $startOfMonth);
                  });
            })
            ->orderBy('event_date')
            ->get();

        // Expand multi-day events across all dates they span
        $calendarEvents = collect();
        foreach ($calendarEventsRaw as $ev) {
            $evStart = $ev->event_date->copy();
            $evEnd = $ev->end_date ? $ev->end_date->copy() : $evStart->copy();
            $cursor = $evStart->copy();
            while ($cursor <= $evEnd) {
                $dk = $cursor->format('Y-m-d');
                if (!$calendarEvents->has($dk)) {
                    $calendarEvents[$dk] = collect();
                }
                $calendarEvents[$dk]->push($ev);
                $cursor->addDay();
            }
        }

        // Stats
        $totalJobs = $allJobs->count();
        $pendingJobs = $allJobs->where('status', 'pending')->count();
        $scheduledCount = $allJobs->where('status', 'scheduled')->count();
        $inProgressCount = $allJobs->where('status', 'in_progress')->count();
        $completedCount = Job::where('status', 'completed')
            ->whereBetween('scheduled_date', [$startOfMonth, $endOfMonth])
            ->count();
        $totalOrders = $scheduledOrders->flatten()->count();
        $totalEvents = $calendarEventsRaw->count();

        // Availability for the modal
        $availability = AdminAvailability::orderBy('day_of_week')->get()->keyBy('day_of_week');

        // Services and crews for the add event form
        $services = Service::where('is_active', true)->orderBy('name')->get();
        $crews = Crew::where('status', 'active')->orderBy('name')->get();

        return view('calendar.index', compact(
            'slots', 'scheduledOrders', 'scheduledJobs', 'calendarEvents',
            'month', 'startOfMonth', 'endOfMonth', 'gridStart', 'gridEnd',
            'serviceColors', 'serviceColorById',
            'totalJobs', 'pendingJobs', 'scheduledCount', 'inProgressCount', 'completedCount',
            'totalOrders', 'totalEvents',
            'availability', 'services', 'crews'
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
            'crew_id'        => 'nullable|exists:crews,id',
            'address'        => 'nullable|string|max:500',
            'customer_name'  => 'nullable|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'installation_types' => 'nullable|array',
            'installation_types.*' => 'string|max:100',
        ]);

        $customerEmail = $validated['customer_email'] ?? null;
        $customerName  = $validated['customer_name'] ?? 'Customer';

        $validated['created_by'] = Auth::id();
        $validated['installation_types'] = $validated['installation_types'] ?? null;

        // Auto-set color from selected service (fallback to gold)
        if (!empty($validated['service_id'])) {
            $svcColor = Service::where('id', $validated['service_id'])->value('color');
            $validated['color'] = $svcColor ?: '#c9a84c';
        } else {
            $validated['color'] = '#c9a84c';
        }

        $event = CalendarEvent::create($validated);

        // Auto-create a TechMeasure if the service is a tech measure type
        $this->autoCreateTechMeasure($event);

        // Send email notification to client if email provided
        if ($customerEmail && filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
            try {
                $svcName = !empty($validated['service_id']) ? Service::find($validated['service_id'])?->name : null;
                Mail::to($customerEmail)->send(new ScheduleNotification([
                    'title'         => $validated['title'],
                    'event_date'    => $validated['event_date'],
                    'start_time'    => $validated['event_time'] ?? null,
                    'end_time'      => $validated['end_time'] ?? null,
                    'address'       => $validated['address'] ?? null,
                    'description'   => $validated['description'] ?? null,
                    'customer_name' => $customerName,
                    'service_name'  => $svcName,
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
        return response()->json(CalendarEvent::with('crew')->findOrFail($id));
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
            'crew_id'        => 'nullable|exists:crews,id',
            'address'        => 'nullable|string|max:500',
            'customer_name'  => 'nullable|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'installation_types' => 'nullable|array',
            'installation_types.*' => 'string|max:100',
        ]);

        $validated['installation_types'] = $validated['installation_types'] ?? null;

        // Auto-set color from selected service (fallback to gold)
        if (!empty($validated['service_id'])) {
            $svcColor = Service::where('id', $validated['service_id'])->value('color');
            $validated['color'] = $svcColor ?: '#c9a84c';
        } else {
            $validated['color'] = '#c9a84c';
        }

        $event->update($validated);

        // Sync TechMeasure if service is tech measure type
        $this->autoCreateTechMeasure($event);

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
                'service_name'  => $event->service?->name,
                'type'          => 'event',
            ]));

            return response()->json(['success' => true, 'message' => 'Reminder sent to ' . $event->customer_email]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to send: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Reschedule a calendar event with reason, notify customer.
     */
    public function rescheduleEvent(Request $request, $id)
    {
        $oldEvent = CalendarEvent::findOrFail($id);

        $validated = $request->validate([
            'new_date'        => 'required|date',
            'new_time'        => 'nullable|string|max:20',
            'new_end_time'    => 'nullable|string|max:20',
            'reason'          => 'nullable|string|max:2000',
            'notify_customer' => 'nullable',
        ]);

        $installerReason = $oldEvent->reschedule_reason;
        $adminReason = $validated['reason'] ?? null;
        $combinedReason = $installerReason;
        if ($adminReason) {
            $combinedReason = $combinedReason ? $installerReason . ' | Admin: ' . $adminReason : $adminReason;
        }

        // Mark old event as rescheduled — leave it on the original day
        $oldEvent->update([
            'event_status'      => 'rescheduled',
            'reschedule_reason' => $combinedReason,
            'rescheduled_at'    => now(),
        ]);

        // Create a brand new event on the new date, linked to the old one
        $newEvent = CalendarEvent::create([
            'title'              => $oldEvent->title,
            'description'        => $oldEvent->description,
            'event_date'         => $validated['new_date'],
            'event_time'         => $validated['new_time'] ?? $oldEvent->event_time,
            'end_time'           => $validated['new_end_time'] ?? $oldEvent->end_time,
            'end_date'           => $oldEvent->end_date,
            'color'              => $oldEvent->color,
            'service_id'         => $oldEvent->service_id,
            'crew_id'            => $oldEvent->crew_id,
            'created_by'         => $oldEvent->created_by,
            'address'            => $oldEvent->address,
            'customer_name'      => $oldEvent->customer_name,
            'customer_email'     => $oldEvent->customer_email,
            'customer_phone'     => $oldEvent->customer_phone,
            'installation_types' => $oldEvent->installation_types,
            'event_status'       => 'scheduled',
            'related_event_id'   => $oldEvent->id,
            'rescheduled_from_date' => $oldEvent->event_date,
            'rescheduled_from_time' => $oldEvent->event_time,
            'reschedule_reason'     => $combinedReason,
        ]);

        // Notify customer of reschedule (only if requested)
        $emailSent = false;
        $notifyCustomer = $request->input('notify_customer');
        if ($notifyCustomer && $newEvent->customer_email) {
            try {
                $oldDateFormatted = $oldEvent->event_date->format('l, F j, Y');
                $description = 'Your appointment has been rescheduled to a new date.';
                if ($installerReason) {
                    $description .= "\n\nReason: " . $installerReason;
                }
                if ($adminReason) {
                    $description .= "\n\nNote: " . $adminReason;
                }
                $description .= "\n\nPreviously scheduled: " . $oldDateFormatted . ($oldEvent->event_time ? ' at ' . $oldEvent->event_time : '');

                Mail::to($newEvent->customer_email)->send(new ScheduleNotification([
                    'title'         => $newEvent->title . ' — Rescheduled',
                    'event_date'    => $validated['new_date'],
                    'start_time'    => $validated['new_time'] ?? $oldEvent->event_time,
                    'end_time'      => $validated['new_end_time'] ?? $oldEvent->end_time,
                    'address'       => $newEvent->address,
                    'description'   => $description,
                    'customer_name' => $newEvent->customer_name ?? 'Customer',
                    'service_name'  => $newEvent->service?->name,
                    'type'          => 'event',
                ]));
                $emailSent = true;
            } catch (\Exception $e) {
                \Log::warning('Admin reschedule notification failed: ' . $e->getMessage());
            }
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'New event created.', 'email_sent' => $emailSent, 'new_event_id' => $newEvent->id]);
        }

        return redirect()->route('admin.calendar.index', ['month' => \Carbon\Carbon::parse($validated['new_date'])->format('Y-m')])
            ->with('success', 'New event created.' . ($emailSent ? ' Customer notified.' : ''));
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

    /**
     * Auto-create a TechMeasure from a calendar event if the service
     * name contains "measure" or "tech" (case-insensitive).
     * If one already exists for the event, update its crew/assignee.
     */
    protected function autoCreateTechMeasure(CalendarEvent $event): void
    {
        // Check if the service is a tech measure type
        $service = $event->service;
        $isTechMeasure = false;

        if ($service) {
            $name = strtolower($service->name ?? '');
            $isTechMeasure = str_contains($name, 'measure') || str_contains($name, 'tech measure');
        }

        // Also check the event title as a fallback
        if (!$isTechMeasure) {
            $title = strtolower($event->title ?? '');
            $isTechMeasure = str_contains($title, 'measure') || str_contains($title, 'tech measure');
        }

        if (!$isTechMeasure) {
            return;
        }

        $existing = TechMeasure::where('calendar_event_id', $event->id)->first();

        if ($existing) {
            // Update crew/assignee if changed
            $existing->update([
                'crew_id' => $event->crew_id,
                'assigned_to' => $event->crew_id
                    ? Crew::find($event->crew_id)?->members()->first()?->id
                    : $existing->assigned_to,
                'customer_name' => $event->customer_name ?: $existing->customer_name,
                'customer_email' => $event->customer_email ?: $existing->customer_email,
                'customer_phone' => $event->customer_phone ?: $existing->customer_phone,
                'address' => $event->address ?: $existing->address,
            ]);
            return;
        }

        // Create new TechMeasure
        TechMeasure::create([
            'calendar_event_id' => $event->id,
            'customer_name' => $event->customer_name,
            'customer_email' => $event->customer_email,
            'customer_phone' => $event->customer_phone,
            'address' => $event->address,
            'status' => 'pending',
            'assigned_to' => $event->crew_id
                ? Crew::find($event->crew_id)?->members()->first()?->id
                : null,
            'crew_id' => $event->crew_id,
            'created_by' => Auth::guard('vip')->id(),
        ]);
    }
}
