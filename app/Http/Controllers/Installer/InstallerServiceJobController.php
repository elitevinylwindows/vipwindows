<?php

namespace App\Http\Controllers\Installer;

use App\Http\Controllers\Controller;
use App\Models\CalendarEvent;
use App\Models\JobTimeLog;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        $user = Auth::guard('vip')->user();
        $event = CalendarEvent::with(['service', 'crew'])->findOrFail($id);

        // Get the associated job if one exists
        $job = \App\Models\Job::where('service_id', $event->service_id)
            ->where('customer_name', $event->customer_name)
            ->where('scheduled_date', $event->event_date)
            ->first();

        // Time tracking info
        $isClockedIn = false;
        $activeSince = null;
        $totalTimeMinutes = 0;

        if ($job) {
            $activeLog = $job->timeLogs()->where('user_id', $user->id)->whereNull('clock_out')->first();
            if ($activeLog) {
                $isClockedIn = true;
                $activeSince = $activeLog->clock_in->toISOString();
            }
            $totalTimeMinutes = $job->timeLogs()
                ->where('user_id', $user->id)
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
                'is_clocked_in' => $isClockedIn,
                'active_since' => $activeSince,
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
            ] : null,
        ]);
    }

    /**
     * Clock in to a service event (start time tracking).
     */
    public function clockIn($id)
    {
        $user = Auth::guard('vip')->user();
        $event = CalendarEvent::findOrFail($id);

        // Check if already clocked in (look for active time log via linked job or event)
        $job = $this->findOrCreateJob($event);

        $active = $job->timeLogs()->where('user_id', $user->id)->whereNull('clock_out')->first();
        if ($active) {
            return response()->json(['error' => 'You are already clocked in.'], 422);
        }

        $now = now();

        $log = JobTimeLog::create([
            'job_id'   => $job->id,
            'user_id'  => $user->id,
            'clock_in' => $now,
        ]);

        // Clock in crew members too
        if ($event->crew_id) {
            $crewMemberIds = DB::table('crew_members')
                ->where('crew_id', $event->crew_id)
                ->pluck('user_id')
                ->toArray();

            foreach ($crewMemberIds as $memberId) {
                if ($memberId == $user->id) continue;
                $memberActive = $job->timeLogs()->where('user_id', $memberId)->whereNull('clock_out')->exists();
                if (!$memberActive) {
                    JobTimeLog::create([
                        'job_id'   => $job->id,
                        'user_id'  => $memberId,
                        'clock_in' => $now,
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'log' => $log,
            'message' => 'Clocked in successfully.',
        ]);
    }

    /**
     * Clock out of a service event (stop time tracking).
     */
    public function clockOut($id)
    {
        $user = Auth::guard('vip')->user();
        $event = CalendarEvent::findOrFail($id);

        $job = $this->findOrCreateJob($event);

        $active = $job->timeLogs()->where('user_id', $user->id)->whereNull('clock_out')->first();
        if (!$active) {
            return response()->json(['error' => 'You are not clocked in.'], 422);
        }

        $clockOut = now();
        $totalMinutes = $active->clock_in->diffInMinutes($clockOut);

        $active->update([
            'clock_out'     => $clockOut,
            'total_minutes' => $totalMinutes,
        ]);

        return response()->json([
            'success' => true,
            'total_minutes' => $totalMinutes,
            'message' => 'Clocked out. ' . floor($totalMinutes / 60) . 'h ' . ($totalMinutes % 60) . 'm logged.',
        ]);
    }

    /**
     * Mark service event as completed.
     */
    public function complete($id)
    {
        $event = CalendarEvent::findOrFail($id);
        $event->update(['event_status' => 'completed']);

        // Clock out any active time logs
        $job = \App\Models\Job::where('service_id', $event->service_id)
            ->where('customer_name', $event->customer_name)
            ->where('scheduled_date', $event->event_date)
            ->first();

        if ($job) {
            $job->timeLogs()->whereNull('clock_out')->each(function ($log) {
                $clockOut = now();
                $log->update([
                    'clock_out'     => $clockOut,
                    'total_minutes' => $log->clock_in->diffInMinutes($clockOut),
                ]);
            });
        }

        return response()->json(['success' => true, 'message' => 'Service marked as completed.']);
    }

    /**
     * Find or create a Job record linked to this calendar event for time tracking.
     */
    private function findOrCreateJob(CalendarEvent $event)
    {
        $job = \App\Models\Job::where('service_id', $event->service_id)
            ->where('customer_name', $event->customer_name)
            ->where('scheduled_date', $event->event_date)
            ->first();

        if (!$job) {
            $job = \App\Models\Job::create([
                'service_id'     => $event->service_id,
                'customer_name'  => $event->customer_name,
                'customer_email' => $event->customer_email,
                'customer_phone' => $event->customer_phone,
                'address'        => $event->address,
                'scheduled_date' => $event->event_date,
                'status'         => 'in_progress',
                'job_number'     => 'SVC-' . strtoupper(uniqid()),
                'assigned_to'    => Auth::guard('vip')->id(),
                'crew_id'        => $event->crew_id,
                'notes'          => $event->description,
            ]);
        }

        return $job;
    }
}
