<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use App\Models\Job;
use App\Models\JobTimeLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminServiceJobController extends Controller
{
    /**
     * List all service events (admin view).
     */
    public function index(Request $request)
    {
        $status = $request->input('status', 'all');

        $query = CalendarEvent::with(['service', 'crew'])
            ->where(function ($q) {
                $q->where('event_status', '!=', 'rescheduled')
                  ->orWhereNull('event_status');
            })
            ->orderByDesc('event_date');

        if ($status === 'upcoming') {
            $query->where('event_date', '>=', today())
                  ->where(function ($q) {
                      $q->where('event_status', 'scheduled')
                        ->orWhereNull('event_status');
                  });
        } elseif ($status === 'completed') {
            $query->where('event_status', 'completed');
        } elseif ($status === 'today') {
            $query->whereDate('event_date', today());
        }

        $events = $query->paginate(50);

        return view('admin.service-jobs.index', compact('events', 'status'));
    }

    /**
     * Show service event detail (JSON for AJAX).
     */
    public function show($id)
    {
        $event = CalendarEvent::with(['service', 'crew'])->findOrFail($id);

        // Get the associated job if one exists
        $job = Job::where('service_id', $event->service_id)
            ->where('customer_name', $event->customer_name)
            ->where('scheduled_date', $event->event_date)
            ->first();

        $totalTimeMinutes = 0;
        if ($job) {
            $totalTimeMinutes = $job->timeLogs()
                ->whereNotNull('clock_out')
                ->sum('total_minutes');
        }

        return response()->json([
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'event_date' => $event->event_date?->format('M d, Y'),
                'event_time' => $event->event_time,
                'end_time' => $event->end_time,
                'customer_name' => $event->customer_name,
                'customer_email' => $event->customer_email,
                'customer_phone' => $event->customer_phone,
                'address' => $event->address,
                'service_name' => $event->service?->name ?? 'General Service',
                'service_color' => $event->service?->color ?? $event->color ?? '#c9a84c',
                'crew_name' => $event->crew?->name ?? '—',
                'event_status' => $event->event_status ?? 'scheduled',
                'installation_types' => $event->installation_types,
                'total_time_minutes' => $totalTimeMinutes,
            ],
            'job' => $job ? [
                'id' => $job->id,
                'job_number' => $job->job_number,
                'status' => $job->status,
                'notes' => $job->notes,
                'items' => $job->jobItems->map(fn($i) => [
                    'id' => $i->id,
                    'description' => $i->description,
                    'is_completed' => $i->is_completed,
                ]),
                'time_logs' => $job->timeLogs->map(fn($l) => [
                    'user_name' => $l->user?->name ?? '—',
                    'clock_in' => $l->clock_in?->format('M d g:i A'),
                    'clock_out' => $l->clock_out?->format('M d g:i A'),
                    'total_minutes' => $l->total_minutes,
                ]),
            ] : null,
        ]);
    }
}
