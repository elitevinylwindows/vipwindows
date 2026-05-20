<?php

namespace App\Http\Controllers;

use App\Models\VipUser;
use App\Models\Service;
use App\Models\JobTimeLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InstallerManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = VipUser::where('role', 'installer');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $installers = $query->orderByDesc('created_at')->paginate(20);

        // Get all services for the add/edit modals
        try {
            $services = Service::where('is_active', true)->orderBy('sort_order')->get();
        } catch (\Exception $e) {
            $services = collect();
        }

        return view('installers.index', compact('installers', 'services'));
    }

    public function show($id)
    {
        $installer = VipUser::where('role', 'installer')->findOrFail($id);

        // Get installer stats
        try {
            $jobCount = \App\Models\Job::where('assigned_to', $installer->id)->count();
        } catch (\Exception $e) { $jobCount = 0; }

        try {
            $completedJobCount = \App\Models\Job::where('assigned_to', $installer->id)->where('status', 'completed')->count();
        } catch (\Exception $e) { $completedJobCount = 0; }

        try {
            $activeJobCount = \App\Models\Job::where('assigned_to', $installer->id)->whereIn('status', ['scheduled', 'in_progress'])->count();
        } catch (\Exception $e) { $activeJobCount = 0; }

        // Get assigned services
        try {
            $assignedServices = $installer->services()->get()->map(function ($s) {
                return [
                    'id' => $s->id,
                    'name' => $s->name,
                    'code' => $s->code,
                    'base_price' => $s->base_price,
                    'custom_price' => $s->pivot->custom_price,
                ];
            });
        } catch (\Exception $e) {
            $assignedServices = collect();
        }

        // ─── Time-based pay from job_time_logs ───
        $thisMonthPay = 0;
        $thisMonthMinutes = 0;
        $allTimePay = 0;
        $allTimeMinutes = 0;
        $monthlyPay = collect();
        $recentTimeLogs = collect();

        try {
            $monthStart = Carbon::now()->startOfMonth();
            $monthEnd = Carbon::now()->endOfMonth();

            $thisMonthPay = JobTimeLog::where('user_id', $installer->id)
                ->whereNotNull('clock_out')
                ->whereBetween('clock_in', [$monthStart, $monthEnd])
                ->sum('earnings');

            $thisMonthMinutes = JobTimeLog::where('user_id', $installer->id)
                ->whereNotNull('clock_out')
                ->whereBetween('clock_in', [$monthStart, $monthEnd])
                ->sum('total_minutes');

            $allTimePay = JobTimeLog::where('user_id', $installer->id)
                ->whereNotNull('clock_out')
                ->sum('earnings');

            $allTimeMinutes = JobTimeLog::where('user_id', $installer->id)
                ->whereNotNull('clock_out')
                ->sum('total_minutes');

            $monthlyPay = JobTimeLog::where('user_id', $installer->id)
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

            $recentTimeLogs = JobTimeLog::where('user_id', $installer->id)
                ->whereNotNull('clock_out')
                ->with(['job.service'])
                ->orderByDesc('clock_in')
                ->take(10)
                ->get()
                ->map(fn($l) => [
                    'date' => $l->clock_in->format('M d, Y'),
                    'clock_in' => $l->clock_in->format('g:i A'),
                    'clock_out' => $l->clock_out->format('g:i A'),
                    'total_minutes' => $l->total_minutes,
                    'earnings' => $l->earnings,
                    'job_number' => $l->job?->job_number ?? '—',
                    'service_name' => $l->job?->service?->name ?? '—',
                    'service_color' => $l->job?->service?->color ?? '#6c757d',
                ]);

            // Pay breakdown by service type
            $payByService = JobTimeLog::where('job_time_logs.user_id', $installer->id)
                ->whereNotNull('job_time_logs.clock_out')
                ->join('vip_jobs', 'vip_jobs.id', '=', 'job_time_logs.job_id')
                ->leftJoin('vip_services', 'vip_services.id', '=', 'vip_jobs.service_id')
                ->select(
                    DB::raw("COALESCE(vip_services.name, 'Other') as service_name"),
                    DB::raw("COALESCE(vip_services.color, '#6c757d') as service_color"),
                    DB::raw('SUM(job_time_logs.earnings) as total_earnings'),
                    DB::raw('SUM(job_time_logs.total_minutes) as total_minutes'),
                    DB::raw('COUNT(DISTINCT job_time_logs.job_id) as total_jobs')
                )
                ->groupBy('vip_services.id', 'vip_services.name', 'vip_services.color')
                ->orderByDesc('total_earnings')
                ->get();
        } catch (\Exception $e) {
            // job_time_logs table may not exist yet
            $payByService = collect();
        }

        // Pending vs approved pay
        $pendingPay = 0;
        $approvedPay = 0;
        try {
            $pendingPay = JobTimeLog::where('user_id', $installer->id)
                ->whereNotNull('clock_out')
                ->where(function ($q) {
                    $q->where('pay_status', 'pending')->orWhereNull('pay_status');
                })
                ->sum('earnings');

            $approvedPay = JobTimeLog::where('user_id', $installer->id)
                ->whereNotNull('clock_out')
                ->where('pay_status', 'approved')
                ->sum('earnings');
        } catch (\Exception $e) {}

        return response()->json([
            'installer' => $installer,
            'stats' => [
                'jobs' => $jobCount,
                'completed_jobs' => $completedJobCount,
                'active_jobs' => $activeJobCount,
            ],
            'services' => $assignedServices,
            'pay' => [
                'this_month' => round($thisMonthPay, 2),
                'this_month_minutes' => $thisMonthMinutes,
                'all_time' => round($allTimePay, 2),
                'all_time_minutes' => $allTimeMinutes,
                'pending' => round($pendingPay, 2),
                'approved' => round($approvedPay, 2),
                'monthly' => $monthlyPay,
                'by_service' => $payByService,
                'recent_logs' => $recentTimeLogs,
            ],
        ]);
    }

    /**
     * Approve a single time log payment.
     */
    public function approvePay(Request $request, $logId)
    {
        $log = JobTimeLog::findOrFail($logId);
        $log->update(['pay_status' => 'approved']);

        return response()->json(['success' => true, 'message' => 'Payment approved.']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:150',
            'email'         => 'required|email|unique:vip_users,email',
            'phone'         => 'nullable|string|max:30',
            'company_name'  => 'nullable|string|max:150',
            'company_phone' => 'nullable|string|max:30',
            'company_email' => 'nullable|email|max:150',
            'company_website' => 'nullable|string|max:200',
            'address'       => 'nullable|string|max:300',
            'city'          => 'nullable|string|max:100',
            'state'         => 'nullable|string|max:50',
            'zip'           => 'nullable|string|max:20',
            'notes'         => 'nullable|string|max:1000',
            'services'      => 'nullable|array',
            'services.*'    => 'integer|exists:vip_services,id',
        ]);

        $installer = VipUser::create([
            'name'            => $validated['name'],
            'email'           => $validated['email'],
            'phone'           => $validated['phone'] ?? null,
            'company_name'    => $validated['company_name'] ?? null,
            'company_phone'   => $validated['company_phone'] ?? null,
            'company_email'   => $validated['company_email'] ?? null,
            'company_website' => $validated['company_website'] ?? null,
            'address'         => $validated['address'] ?? null,
            'city'            => $validated['city'] ?? null,
            'state'           => $validated['state'] ?? null,
            'zip'             => $validated['zip'] ?? null,
            'notes'           => $validated['notes'] ?? null,
            'role'            => 'installer',
            'password'        => Hash::make('VipInstaller2026!'),
        ]);

        // Sync services
        if (!empty($validated['services'])) {
            try {
                $installer->services()->sync($validated['services']);
            } catch (\Exception $e) {}
        }

        return redirect()->route('admin.installers.index')->with('success', 'Installer added. Default password: VipInstaller2026!');
    }

    public function update(Request $request, $id)
    {
        $installer = VipUser::where('role', 'installer')->findOrFail($id);

        $validated = $request->validate([
            'name'          => 'required|string|max:150',
            'email'         => 'required|email|unique:vip_users,email,' . $installer->id,
            'phone'         => 'nullable|string|max:30',
            'company_name'  => 'nullable|string|max:150',
            'company_phone' => 'nullable|string|max:30',
            'company_email' => 'nullable|email|max:150',
            'company_website' => 'nullable|string|max:200',
            'address'       => 'nullable|string|max:300',
            'city'          => 'nullable|string|max:100',
            'state'         => 'nullable|string|max:50',
            'zip'           => 'nullable|string|max:20',
            'notes'         => 'nullable|string|max:1000',
            'status'        => 'nullable|in:active,suspended',
            'services'      => 'nullable|array',
            'services.*'    => 'integer|exists:vip_services,id',
        ]);

        $serviceIds = $validated['services'] ?? [];
        unset($validated['services']);

        $installer->update($validated);

        // Sync services
        try {
            $installer->services()->sync($serviceIds);
        } catch (\Exception $e) {}

        return redirect()->route('admin.installers.index')->with('success', 'Installer updated.');
    }

    public function destroy($id)
    {
        VipUser::where('role', 'installer')->findOrFail($id)->delete();
        return redirect()->route('admin.installers.index')->with('success', 'Installer removed.');
    }
}
