<?php

namespace App\Http\Controllers\Installer;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InstallerAttendanceController extends Controller
{
    /**
     * Attendance page — clock in/out + history.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $active = AttendanceLog::activeFor($user->id);

        // History — default to current month
        $month = $request->input('month', now()->format('Y-m'));
        $start = \Carbon\Carbon::parse($month . '-01')->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $logs = AttendanceLog::where('user_id', $user->id)
            ->whereBetween('date', [$start, $end])
            ->orderByDesc('date')
            ->orderByDesc('clock_in')
            ->get();

        // Stats for the month
        $totalMinutes = $logs->whereNotNull('clock_out')->sum('total_minutes');
        $totalDays    = $logs->pluck('date')->unique()->count();
        $avgMinutes   = $totalDays > 0 ? round($totalMinutes / $totalDays) : 0;

        return view('installer.attendance.index', compact(
            'active', 'logs', 'month', 'start', 'end',
            'totalMinutes', 'totalDays', 'avgMinutes'
        ));
    }

    /**
     * Clock in.
     */
    public function clockIn()
    {
        $user = Auth::user();

        // Prevent double clock-in
        if (AttendanceLog::activeFor($user->id)) {
            return back()->with('error', 'You are already clocked in.');
        }

        AttendanceLog::create([
            'user_id'  => $user->id,
            'clock_in' => now(),
            'date'     => today(),
        ]);

        return back()->with('success', 'Clocked in at ' . now()->format('g:i A'));
    }

    /**
     * Clock out.
     */
    public function clockOut(Request $request)
    {
        $user = Auth::user();
        $active = AttendanceLog::activeFor($user->id);

        if (!$active) {
            return back()->with('error', 'You are not clocked in.');
        }

        $clockOut = now();
        $totalMinutes = $active->clock_in->diffInMinutes($clockOut);

        $active->update([
            'clock_out'     => $clockOut,
            'total_minutes' => $totalMinutes,
            'notes'         => $request->input('notes'),
        ]);

        $h = intdiv($totalMinutes, 60);
        $m = $totalMinutes % 60;

        return back()->with('success', "Clocked out. Shift: {$h}h {$m}m");
    }
}
