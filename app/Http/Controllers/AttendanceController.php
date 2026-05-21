<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobTimeLog;
use App\Models\Service;
use App\Models\TechMeasure;
use App\Models\VipUser;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * Admin attendance overview — all staff, job-based time tracking.
     */
    public function index(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));
        $start = \Carbon\Carbon::parse($month . '-01')->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $selectedUser = $request->input('user_id');

        $query = JobTimeLog::with(['user', 'job.service'])
            ->whereBetween('clock_in', [$start->startOfDay(), $end->endOfDay()])
            ->orderByDesc('clock_in');

        if ($selectedUser) {
            $query->where('user_id', $selectedUser);
        }

        $logs = $query->get();

        // Staff list for filter
        $staff = VipUser::whereIn('role', ['admin', 'technician', 'installer', 'scheduler'])
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        // Summary per user
        $summary = $logs->whereNotNull('clock_out')
            ->groupBy('user_id')
            ->map(function ($userLogs) {
                $user = $userLogs->first()->user;
                $totalMin = $userLogs->sum('total_minutes');
                $totalEarnings = $userLogs->sum('earnings');
                $days = $userLogs->map(fn($l) => $l->clock_in->format('Y-m-d'))->unique()->count();
                $jobCount = $userLogs->pluck('job_id')->unique()->count();
                return [
                    'user'           => $user,
                    'total_minutes'  => $totalMin,
                    'total_earnings' => $totalEarnings,
                    'total_days'     => $days,
                    'total_jobs'     => $jobCount,
                    'avg_minutes'    => $days > 0 ? round($totalMin / $days) : 0,
                ];
            })
            ->sortByDesc('total_minutes');

        // Currently clocked in (active time logs)
        $activeNow = JobTimeLog::with(['user', 'job.service'])
            ->whereNull('clock_out')
            ->orderByDesc('clock_in')
            ->get();

        return view('attendance.index', compact(
            'logs', 'staff', 'month', 'start', 'end',
            'selectedUser', 'summary', 'activeNow'
        ));
    }

    /**
     * One-time backfill: create job_time_logs for completed tech measures
     * that have no existing time log entries.
     */
    public function backfillTimeLogs()
    {
        $measures = TechMeasure::whereIn('status', ['completed', 'converted'])
            ->whereNotNull('started_at')
            ->whereNotNull('completed_at')
            ->with('calendarEvent')
            ->get();

        $created = 0;
        $skipped = 0;

        // Find the tech measure service for pay calculation
        $tmService = Service::where('code', 'tech_measure')
            ->orWhere('name', 'LIKE', '%tech%measure%')
            ->first();

        foreach ($measures as $measure) {
            // Find the assigned installer from the calendar event
            $event = $measure->calendarEvent;
            $assignedTo = $event?->assigned_to ?? $measure->assigned_to ?? null;
            if (!$assignedTo) {
                $skipped++;
                continue;
            }

            // Find or create the linked job
            $job = null;
            if ($measure->job_id) {
                $job = Job::find($measure->job_id);
            }
            if (!$job && $event) {
                $job = Job::where('service_id', $event->service_id)
                    ->where('customer_name', $event->customer_name)
                    ->where('scheduled_date', $event->event_date)
                    ->first();
            }
            if (!$job) {
                // Create a time-tracking job
                $job = Job::create([
                    'service_id'     => $event?->service_id ?? $tmService?->id,
                    'customer_name'  => $measure->customer_name ?? $event?->customer_name ?? 'Unknown',
                    'customer_email' => $measure->customer_email ?? '',
                    'customer_phone' => $measure->customer_phone ?? '',
                    'address'        => $measure->address ?? '',
                    'scheduled_date' => $event?->event_date ?? $measure->started_at->toDateString(),
                    'status'         => 'completed',
                    'job_number'     => 'TM-BF-' . $measure->id,
                    'assigned_to'    => $assignedTo,
                    'created_by'     => $assignedTo,
                    'notes'          => 'Backfilled time tracking for tech measure: ' . ($measure->customer_name ?? 'N/A'),
                ]);
            }

            // Ensure job is marked completed
            if ($job->status !== 'completed') {
                $job->update(['status' => 'completed']);
            }

            // Collect all user IDs — assigned user + crew members
            $userIds = collect([$assignedTo]);
            if ($measure->crew_id) {
                $crewMemberIds = \Illuminate\Support\Facades\DB::table('crew_members')
                    ->where('crew_id', $measure->crew_id)
                    ->pluck('user_id');
                $userIds = $userIds->merge($crewMemberIds)->unique();
            }

            $totalMinutes = $measure->started_at->diffInMinutes($measure->completed_at);

            // Calculate earnings
            $earnings = 0;
            $service = $job->service ?? $tmService;
            if ($service && $service->installer_pay > 0) {
                $earnings = match ($service->installer_pay_type) {
                    'per_hour'   => round(($totalMinutes / 60) * $service->installer_pay, 2),
                    'per_job'    => $service->installer_pay,
                    'percentage' => round(($service->base_price ?? 0) * ($service->installer_pay / 100), 2),
                    default      => $service->installer_pay,
                };
            }

            foreach ($userIds as $userId) {
                // Check if a time log already exists for this user+job
                $exists = JobTimeLog::where('job_id', $job->id)
                    ->where('user_id', $userId)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                JobTimeLog::create([
                    'job_id'        => $job->id,
                    'user_id'       => $userId,
                    'clock_in'      => $measure->started_at,
                    'clock_out'     => $measure->completed_at,
                    'total_minutes' => $totalMinutes,
                    'earnings'      => $earnings,
                    'notes'         => 'Backfilled from completed tech measure',
                ]);

                $created++;
            }
        }

        return redirect()->route('admin.attendance.index')
            ->with('success', "Backfill complete: {$created} time logs created, {$skipped} skipped (already existed or no assignee).");
    }
}
