<?php

namespace App\Http\Controllers\Installer;

use App\Http\Controllers\Controller;
use App\Models\CalendarEvent;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InstallerServiceJobController extends Controller
{
    /**
     * List service events assigned to this installer (via crew).
     * Mirrors the tech measures pattern — left rail + detail panel.
     */
    public function index(Request $request)
    {
        $user = Auth::guard('vip')->user();
        $status = $request->input('status', 'all');

        // Get crews this user belongs to
        $crewIds = \App\Models\Crew::whereHas('members', fn($q) => $q->where('crew_members.user_id', $user->id))->pluck('id');

        $query = CalendarEvent::with(['service', 'crew'])
            ->whereIn('crew_id', $crewIds)
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

        return view('installer.service-jobs.index', compact('events', 'status'));
    }

    /**
     * Show service event detail (JSON for AJAX).
     */
    public function show($id)
    {
        $event = CalendarEvent::with(['service', 'crew'])->findOrFail($id);

        // Get the associated job if one exists
        $job = \App\Models\Job::where('service_id', $event->service_id)
            ->where('customer_name', $event->customer_name)
            ->where('scheduled_date', $event->event_date)
            ->first();

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
            ] : null,
        ]);
    }
}
