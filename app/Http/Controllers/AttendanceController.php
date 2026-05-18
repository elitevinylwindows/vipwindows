<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\VipUser;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * Admin attendance overview — all staff.
     */
    public function index(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));
        $start = \Carbon\Carbon::parse($month . '-01')->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $selectedUser = $request->input('user_id');

        $query = AttendanceLog::with('user')
            ->whereBetween('date', [$start, $end])
            ->orderByDesc('date')
            ->orderByDesc('clock_in');

        if ($selectedUser) {
            $query->where('user_id', $selectedUser);
        }

        $logs = $query->get();

        // Staff list for filter
        $staff = VipUser::whereIn('role', ['installer', 'technician'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Summary per user
        $summary = $logs->whereNotNull('clock_out')
            ->groupBy('user_id')
            ->map(function ($userLogs) {
                $user = $userLogs->first()->user;
                $totalMin = $userLogs->sum('total_minutes');
                $days = $userLogs->pluck('date')->unique()->count();
                return [
                    'user'          => $user,
                    'total_minutes' => $totalMin,
                    'total_days'    => $days,
                    'avg_minutes'   => $days > 0 ? round($totalMin / $days) : 0,
                ];
            })
            ->sortByDesc('total_minutes');

        // Currently clocked in
        $activeNow = AttendanceLog::with('user')
            ->whereNull('clock_out')
            ->orderByDesc('clock_in')
            ->get();

        return view('attendance.index', compact(
            'logs', 'staff', 'month', 'start', 'end',
            'selectedUser', 'summary', 'activeNow'
        ));
    }
}
