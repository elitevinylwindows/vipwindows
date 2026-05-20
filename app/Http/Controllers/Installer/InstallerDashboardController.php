<?php

namespace App\Http\Controllers\Installer;

use App\Http\Controllers\Controller;
use App\Models\VipQuote as Quote;
use App\Models\Job;
use App\Models\JobItem;
use App\Models\JobTimeLog;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InstallerDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $totalQuotes = Quote::where('entered_by', $user->name)->count();
        $sentQuotes = Quote::where('entered_by', $user->name)->where('status', 'sent')->count();
        $activeJobs = Job::where('assigned_to', $user->id)->whereIn('status', ['scheduled', 'in_progress'])->count();
        $completedJobs = Job::where('assigned_to', $user->id)->where('status', 'completed')->count();
        $pendingInvoices = Invoice::where('created_by', $user->id)->whereIn('status', ['sent', 'partial'])->count();
        $totalEarnings = Invoice::where('created_by', $user->id)->where('status', 'paid')->sum('total');

        // Recent activity
        $recentQuotes = Quote::where('entered_by', $user->name)->latest()->take(5)->get();
        $recentJobs = Job::where('assigned_to', $user->id)->with('jobItems.service')->latest()->take(5)->get();

        // Pay calculations from job items
        $totalJobPay = JobItem::whereHas('job', fn($q) => $q->where('assigned_to', $user->id))
            ->sum('total_pay');
        $completedJobPay = JobItem::whereHas('job', fn($q) => $q->where('assigned_to', $user->id)->where('status', 'completed'))
            ->sum('total_pay');
        $pendingJobPay = JobItem::whereHas('job', fn($q) => $q->where('assigned_to', $user->id)->whereIn('status', ['scheduled', 'in_progress']))
            ->sum('total_pay');

        // Recent completed jobs with pay for the earnings table
        $recentPaidJobs = Job::where('assigned_to', $user->id)
            ->where('status', 'completed')
            ->with('jobItems.service')
            ->latest()
            ->take(10)
            ->get();

        // ─── Time-based earnings from job_time_logs ───
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        // This month's time earnings
        $thisMonthTimePay = JobTimeLog::where('user_id', $user->id)
            ->whereNotNull('clock_out')
            ->whereBetween('clock_in', [$monthStart, $monthEnd])
            ->sum('earnings');

        $thisMonthTimeMinutes = JobTimeLog::where('user_id', $user->id)
            ->whereNotNull('clock_out')
            ->whereBetween('clock_in', [$monthStart, $monthEnd])
            ->sum('total_minutes');

        $thisMonthTimeJobs = JobTimeLog::where('user_id', $user->id)
            ->whereNotNull('clock_out')
            ->whereBetween('clock_in', [$monthStart, $monthEnd])
            ->distinct('job_id')
            ->count('job_id');

        // All-time time earnings
        $allTimeTimePay = JobTimeLog::where('user_id', $user->id)
            ->whereNotNull('clock_out')
            ->sum('earnings');

        // Monthly breakdown (last 6 months)
        $monthlyTimePay = JobTimeLog::where('user_id', $user->id)
            ->whereNotNull('clock_out')
            ->where('clock_in', '>=', Carbon::now()->subMonths(6)->startOfMonth())
            ->select(
                DB::raw("DATE_FORMAT(clock_in, '%Y-%m') as month"),
                DB::raw('SUM(earnings) as total_earnings'),
                DB::raw('SUM(total_minutes) as total_minutes'),
                DB::raw('COUNT(DISTINCT job_id) as total_jobs')
            )
            ->groupBy('month')
            ->orderByDesc('month')
            ->get();

        // Recent time logs with job details
        $recentTimeLogs = JobTimeLog::where('user_id', $user->id)
            ->whereNotNull('clock_out')
            ->with(['job.service'])
            ->orderByDesc('clock_in')
            ->take(15)
            ->get();

        return view('installer.dashboard', compact(
            'totalQuotes', 'sentQuotes', 'activeJobs', 'completedJobs',
            'pendingInvoices', 'totalEarnings', 'recentQuotes', 'recentJobs',
            'totalJobPay', 'completedJobPay', 'pendingJobPay', 'recentPaidJobs',
            'thisMonthTimePay', 'thisMonthTimeMinutes', 'thisMonthTimeJobs',
            'allTimeTimePay', 'monthlyTimePay', 'recentTimeLogs'
        ));
    }
}
