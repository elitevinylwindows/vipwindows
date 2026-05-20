<?php

namespace App\Http\Controllers;

use App\Models\JobTimeLog;
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
}
