<?php

namespace App\Http\Controllers\Installer;

use App\Http\Controllers\Controller;
use App\Models\JobTimeLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InstallerAttendanceController extends Controller
{
    /**
     * Attendance page — job-based time tracking history.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $month = $request->input('month', now()->format('Y-m'));
        $start = \Carbon\Carbon::parse($month . '-01')->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        // Get crew IDs for this installer
        $crewIds = DB::table('crew_members')
            ->where('user_id', $user->id)
            ->pluck('crew_id')
            ->toArray();

        // Get time logs from jobs assigned to this user (direct or via crew)
        $logs = JobTimeLog::with(['job.service', 'user'])
            ->where('user_id', $user->id)
            ->whereBetween('clock_in', [$start->startOfDay(), $end->endOfDay()])
            ->orderByDesc('clock_in')
            ->get();

        // Currently active (clocked in, not out)
        $active = $logs->whereNull('clock_out')->first();

        // Stats for the month
        $completedLogs = $logs->whereNotNull('clock_out');
        $totalMinutes  = $completedLogs->sum('total_minutes');
        $totalEarnings = $completedLogs->sum('earnings');
        $totalDays     = $completedLogs->map(fn($l) => $l->clock_in->format('Y-m-d'))->unique()->count();
        $avgMinutes    = $totalDays > 0 ? round($totalMinutes / $totalDays) : 0;
        $totalJobs     = $completedLogs->pluck('job_id')->unique()->count();

        // Per-service breakdown
        $serviceBreakdown = $completedLogs->groupBy(fn($l) => $l->job?->service?->name ?? 'Unassigned')
            ->map(fn($group) => [
                'count'   => $group->pluck('job_id')->unique()->count(),
                'minutes' => $group->sum('total_minutes'),
            ])
            ->sortByDesc('minutes');

        return view('installer.attendance.index', compact(
            'active', 'logs', 'month', 'start', 'end',
            'totalMinutes', 'totalEarnings', 'totalDays', 'avgMinutes', 'totalJobs',
            'serviceBreakdown'
        ));
    }
}
