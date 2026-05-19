<?php

namespace App\Http\Controllers\Installer;

use App\Http\Controllers\Controller;
use App\Models\InstallerAvailability;
use App\Models\InstallerAvailabilityOverride;
use App\Models\InstallerBooking;
use App\Models\Crew;
use App\Models\Job;
use App\Models\JobItem;
use App\Models\JobNote;
use App\Models\JobTimeLog;
use App\Models\Service;
use App\Models\VipUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InstallerJobController extends Controller
{
    /**
     * Get crew IDs the current installer belongs to.
     */
    private function myCrewIds(): array
    {
        return DB::table('crew_members')
            ->where('user_id', Auth::id())
            ->pluck('crew_id')
            ->toArray();
    }

    /**
     * Scope a job query to include jobs assigned directly OR via crew membership.
     */
    private function scopeMyJobs($query)
    {
        $crewIds = $this->myCrewIds();

        return $query->where(function ($q) use ($crewIds) {
            $q->where('assigned_to', Auth::id());
            if (!empty($crewIds)) {
                $q->orWhereIn('crew_id', $crewIds);
            }
        });
    }

    /**
     * Find a job by ID that belongs to this installer (direct or via crew).
     */
    private function findMyJob($id)
    {
        $crewIds = $this->myCrewIds();

        return Job::where(function ($q) use ($crewIds) {
            $q->where('assigned_to', Auth::id());
            if (!empty($crewIds)) {
                $q->orWhereIn('crew_id', $crewIds);
            }
        })->findOrFail($id);
    }

    public function calendar(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $startOfMonth = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        // Get all jobs for the month — direct assignment OR crew membership
        $jobs = Job::with('service')->whereNotNull('scheduled_date')
            ->whereBetween('scheduled_date', [$startOfMonth, $endOfMonth])
            ->where(function ($q) {
                $crewIds = $this->myCrewIds();
                $q->where('assigned_to', Auth::id());
                if (!empty($crewIds)) {
                    $q->orWhereIn('crew_id', $crewIds);
                }
            })
            ->orderBy('scheduled_date')
            ->orderBy('scheduled_time')
            ->get();

        // Group by date
        $jobsByDate = $jobs->groupBy(fn($j) => $j->scheduled_date->format('Y-m-d'));

        // Stats
        $totalMonth = $jobs->count();
        $pending = $jobs->where('status', 'pending')->count();
        $scheduled = $jobs->where('status', 'scheduled')->count();
        $inProgress = $jobs->where('status', 'in_progress')->count();
        $completed = $jobs->where('status', 'completed')->count();

        // Get bookings for this month
        $bookings = InstallerBooking::where('installer_id', Auth::id())
            ->whereBetween('booking_date', [$startOfMonth, $endOfMonth])
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('booking_date')
            ->orderBy('booking_time')
            ->get();

        $bookingsByDate = $bookings->groupBy(fn($b) => $b->booking_date->format('Y-m-d'));
        $pendingBookings = $bookings->where('status', 'pending')->count();

        // Get availability settings
        $availability = InstallerAvailability::where('installer_id', Auth::id())
            ->orderBy('day_of_week')
            ->get()
            ->keyBy('day_of_week');

        return view('installer.calendar', compact(
            'jobs', 'jobsByDate', 'month', 'year', 'startOfMonth', 'endOfMonth',
            'totalMonth', 'pending', 'scheduled', 'inProgress', 'completed',
            'bookings', 'bookingsByDate', 'pendingBookings', 'availability'
        ));
    }

    public function index(Request $request)
    {
        $status = $request->get('status', 'all');

        $query = Job::with(['jobNotes.author', 'service'])
            ->orderByDesc('created_at');
        $this->scopeMyJobs($query);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $jobs = $query->paginate(20);

        // Stats scoped to this installer (direct + crew)
        $crewIds = $this->myCrewIds();
        $myJobScope = function ($q) use ($crewIds) {
            $q->where('assigned_to', Auth::id());
            if (!empty($crewIds)) $q->orWhereIn('crew_id', $crewIds);
        };
        $todayJobs = Job::where($myJobScope)->whereDate('scheduled_date', today())->count();
        $weekJobs = Job::where($myJobScope)->whereBetween('scheduled_date', [today(), today()->addDays(7)])->count();
        $inProgress = Job::where($myJobScope)->where('status', 'in_progress')->count();

        $services = Service::where('is_active', true)->orderBy('name')->get();
        $crews = Crew::where('status', 'active')->with('members')->orderBy('name')->get();
        $installers = VipUser::whereIn('role', ['installer', 'technician'])->where('status', 'active')->orderBy('name')->get();

        return view('installer.jobs.index', compact('jobs', 'status', 'todayJobs', 'weekJobs', 'inProgress', 'services', 'crews', 'installers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name'    => 'required|string|max:255',
            'customer_email'   => 'nullable|email|max:255',
            'customer_phone'   => 'nullable|string|max:50',
            'install_address'  => 'nullable|string|max:500',
            'install_city'     => 'nullable|string|max:100',
            'install_state'    => 'nullable|string|max:50',
            'install_zip'      => 'nullable|string|max:20',
            'description'      => 'nullable|string|max:5000',
            'priority'         => 'nullable|in:low,normal,high,urgent',
            'assignment_type'  => 'nullable|in:crew,installer',
            'crew_id'          => 'nullable|exists:crews,id',
            'assigned_to'      => 'nullable|exists:vip_users,id',
            'scheduled_date'   => 'nullable|date',
            'end_date'         => 'nullable|date|after_or_equal:scheduled_date',
            'scheduled_time'   => 'nullable|string|max:20',
            'estimated_duration' => 'nullable|string|max:50',
            'notes'            => 'nullable|string|max:5000',
        ]);

        // Auto-generate job number: IJ-XX-0001
        $prefix = 'IJ-' . strtoupper(substr(Auth::user()->name, 0, 2)) . '-';
        $last = Job::where('job_number', 'like', $prefix . '%')->orderByDesc('job_number')->first();
        $next = $last ? (intval(substr($last->job_number, strlen($prefix))) + 1) : 1;
        $jobNumber = $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);

        $assignmentType = $validated['assignment_type'] ?? 'crew';
        $assignedTo = $assignmentType === 'installer' ? ($validated['assigned_to'] ?? Auth::id()) : Auth::id();
        $crewId = $assignmentType === 'crew' ? ($validated['crew_id'] ?? null) : null;

        $job = Job::create(array_merge($validated, [
            'job_number'      => $jobNumber,
            'status'          => !empty($validated['scheduled_date']) ? 'scheduled' : 'pending',
            'priority'        => $validated['priority'] ?? 'normal',
            'assignment_type' => $assignmentType,
            'assigned_to'     => $assignedTo,
            'crew_id'         => $crewId,
            'created_by'      => Auth::id(),
        ]));

        if ($request->ajax()) {
            return response()->json(['success' => true, 'job' => $job]);
        }

        return redirect()->route('installer.jobs.index')->with('success', 'Job created successfully.');
    }

    public function show($id)
    {
        $job = $this->findMyJob($id);

        $notes = [];
        try {
            $notes = $job->jobNotes()->with('author')->latest()->get()->map(function ($n) {
                return [
                    'note' => $n->note,
                    'author' => $n->author?->name ?? 'System',
                    'created_at' => $n->created_at?->format('M d, Y g:ia'),
                ];
            })->toArray();
        } catch (\Exception $e) {}

        $items = [];
        $totalPay = 0;
        try {
            $items = $job->jobItems()->with('service')->get()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'description' => $item->description,
                    'item_type' => $item->item_type,
                    'qty' => $item->qty,
                    'unit_pay' => $item->unit_pay,
                    'total_pay' => $item->total_pay,
                    'completed' => $item->completed,
                    'notes' => $item->notes,
                    'sort_order' => $item->sort_order,
                    'service_name' => $item->service?->name,
                ];
            })->toArray();
            $totalPay = $job->jobItems()->sum('total_pay');
        } catch (\Exception $e) {}

        // Time tracking info
        $activeLog = null;
        $timeLogs = [];
        $totalTime = 0;
        try {
            $activeLog = $job->activeTimeLog(Auth::id());
            $timeLogs = $job->timeLogs()->with('user')->get()->map(function ($log) {
                return [
                    'id' => $log->id,
                    'clock_in' => $log->clock_in?->format('M d, g:ia'),
                    'clock_out' => $log->clock_out?->format('M d, g:ia'),
                    'total_minutes' => $log->total_minutes,
                    'is_active' => $log->isActive(),
                ];
            })->toArray();
            $totalTime = $job->timeLogs()->whereNotNull('clock_out')->sum('total_minutes');
        } catch (\Exception $e) {}

        return response()->json([
            'job' => $job,
            'notes' => $notes,
            'items' => $items,
            'total_pay' => $totalPay,
            'is_clocked_in' => (bool) $activeLog,
            'active_since' => $activeLog?->clock_in?->format('g:ia'),
            'time_logs' => $timeLogs,
            'total_time_minutes' => $totalTime,
            'image_url' => $job->image ? asset('storage/' . $job->image) : null,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $job = $this->findMyJob($id);

        $validated = $request->validate([
            'status' => 'required|in:scheduled,in_progress,completed,cancelled',
        ]);

        $job->update([
            'status' => $validated['status'],
            'actual_start' => $validated['status'] === 'in_progress' ? ($job->actual_start ?? now()) : $job->actual_start,
            'actual_end' => in_array($validated['status'], ['completed', 'cancelled']) ? now() : $job->actual_end,
        ]);

        // Auto-clock-out all active time logs when job is completed or cancelled
        if (in_array($validated['status'], ['completed', 'cancelled'])) {
            $activeLogs = $job->timeLogs()->whereNull('clock_out')->get();
            foreach ($activeLogs as $log) {
                $clockOut = now();
                $log->update([
                    'clock_out'     => $clockOut,
                    'total_minutes' => $log->clock_in->diffInMinutes($clockOut),
                ]);
            }
        }

        return back()->with('success', 'Job status updated.');
    }

    public function addNote(Request $request, $id)
    {
        $job = Job::where('assigned_to', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'note' => 'required|string|max:2000',
        ]);

        JobNote::create([
            'job_id' => $job->id,
            'note' => $validated['note'],
            'added_by' => Auth::id(),
        ]);

        return back()->with('success', 'Note added.');
    }

    public function addItem(Request $request, $id)
    {
        $job = Job::where('assigned_to', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'item_type'   => 'nullable|in:window,door,service,other',
            'qty'         => 'required|numeric|min:0.01',
            'service_id'  => 'nullable|exists:vip_services,id',
            'notes'       => 'nullable|string|max:1000',
        ]);

        $sortOrder = ($job->jobItems()->max('sort_order') ?? 0) + 1;

        // Calculate installer pay from service if linked
        $unitPay = 0;
        $totalPay = 0;
        if (!empty($validated['service_id'])) {
            $service = Service::find($validated['service_id']);
            if ($service) {
                if ($service->installer_pay_type === 'percentage') {
                    $unitPay = $service->base_price * ($service->installer_pay / 100);
                } elseif ($service->installer_pay_type === 'per_job') {
                    $unitPay = $service->installer_pay;
                    $totalPay = $service->installer_pay; // flat per job, not multiplied by qty
                } else {
                    $unitPay = $service->installer_pay; // per_unit or per_hour
                }

                if ($service->installer_pay_type !== 'per_job') {
                    $totalPay = $unitPay * $validated['qty'];
                }
            }
        }

        $item = JobItem::create([
            'job_id'      => $job->id,
            'service_id'  => $validated['service_id'] ?? null,
            'description' => $validated['description'],
            'item_type'   => $validated['item_type'] ?? 'other',
            'qty'         => $validated['qty'],
            'unit_pay'    => $unitPay,
            'total_pay'   => $totalPay,
            'notes'       => $validated['notes'] ?? null,
            'sort_order'  => $sortOrder,
        ]);

        $item->load('service');

        return response()->json([
            'success' => true,
            'item' => array_merge($item->toArray(), [
                'service_name' => $item->service?->name,
            ]),
        ]);
    }

    public function removeItem($id, $itemId)
    {
        $job = Job::where('assigned_to', Auth::id())->findOrFail($id);
        $item = JobItem::where('job_id', $job->id)->findOrFail($itemId);
        $item->delete();

        return response()->json(['success' => true]);
    }

    public function toggleItem(Request $request, $id, $itemId)
    {
        $job = Job::where('assigned_to', Auth::id())->findOrFail($id);
        $item = JobItem::where('job_id', $job->id)->findOrFail($itemId);
        $item->update(['completed' => !$item->completed]);

        return response()->json(['success' => true, 'completed' => $item->completed]);
    }

    public function update(Request $request, $id)
    {
        $job = Job::where('assigned_to', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'customer_name'    => 'required|string|max:255',
            'customer_email'   => 'nullable|email|max:255',
            'customer_phone'   => 'nullable|string|max:50',
            'install_address'  => 'nullable|string|max:500',
            'install_city'     => 'nullable|string|max:100',
            'install_state'    => 'nullable|string|max:50',
            'install_zip'      => 'nullable|string|max:20',
            'description'      => 'nullable|string|max:5000',
            'priority'         => 'nullable|in:low,normal,high,urgent',
            'assignment_type'  => 'nullable|in:crew,installer',
            'crew_id'          => 'nullable|exists:crews,id',
            'assigned_to'      => 'nullable|exists:vip_users,id',
            'scheduled_date'   => 'nullable|date',
            'end_date'         => 'nullable|date|after_or_equal:scheduled_date',
            'scheduled_time'   => 'nullable|string|max:20',
            'estimated_duration' => 'nullable|string|max:50',
            'notes'            => 'nullable|string|max:5000',
        ]);

        $job->update($validated);

        return response()->json(['success' => true, 'job' => $job->fresh()]);
    }

    public function destroy($id)
    {
        $job = Job::where('assigned_to', Auth::id())->findOrFail($id);
        $job->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Clock in to a job.
     */
    public function clockIn($id)
    {
        $job = $this->findMyJob($id);

        // Check if already clocked in
        $active = $job->activeTimeLog(Auth::id());
        if ($active) {
            return response()->json(['error' => 'You are already clocked in to this job.'], 422);
        }

        $now = now();

        // Clock in the current user
        $log = JobTimeLog::create([
            'job_id'   => $job->id,
            'user_id'  => Auth::id(),
            'clock_in' => $now,
        ]);

        // If job is assigned to a crew, clock in ALL crew members too
        if ($job->crew_id) {
            $crewMemberIds = DB::table('crew_members')
                ->where('crew_id', $job->crew_id)
                ->pluck('user_id')
                ->toArray();

            foreach ($crewMemberIds as $memberId) {
                if ($memberId == Auth::id()) continue; // already clocked in above

                // Only clock in if they don't already have an active log on this job
                $memberActive = $job->timeLogs()
                    ->where('user_id', $memberId)
                    ->whereNull('clock_out')
                    ->exists();

                if (!$memberActive) {
                    JobTimeLog::create([
                        'job_id'   => $job->id,
                        'user_id'  => $memberId,
                        'clock_in' => $now,
                    ]);
                }
            }
        }

        // Auto-set job to in_progress if not already
        if (in_array($job->status, ['pending', 'scheduled'])) {
            $job->update([
                'status' => 'in_progress',
                'actual_start' => $job->actual_start ?? $now,
            ]);
        }

        return response()->json(['success' => true, 'log' => $log, 'message' => 'Clocked in.']);
    }

    /**
     * Clock out of a job.
     */
    public function clockOut($id)
    {
        $job = $this->findMyJob($id);

        $active = $job->activeTimeLog(Auth::id());
        if (!$active) {
            return response()->json(['error' => 'You are not clocked in to this job.'], 422);
        }

        $clockOut = now();
        $totalMinutes = $active->clock_in->diffInMinutes($clockOut);

        $active->update([
            'clock_out'     => $clockOut,
            'total_minutes' => $totalMinutes,
        ]);

        return response()->json([
            'success' => true,
            'log' => $active->fresh(),
            'total_minutes' => $totalMinutes,
            'message' => 'Clocked out. ' . floor($totalMinutes / 60) . 'h ' . ($totalMinutes % 60) . 'm logged.',
        ]);
    }

    /**
     * Get time logs for a job.
     */
    public function timeLogs($id)
    {
        $job = Job::where('assigned_to', Auth::id())->findOrFail($id);

        $logs = $job->timeLogs()->with('user')->get()->map(function ($log) {
            return [
                'id' => $log->id,
                'user' => $log->user?->name ?? 'Unknown',
                'clock_in' => $log->clock_in?->format('M d, g:ia'),
                'clock_out' => $log->clock_out?->format('M d, g:ia'),
                'total_minutes' => $log->total_minutes,
                'is_active' => $log->isActive(),
            ];
        });

        $activeLog = $job->activeTimeLog(Auth::id());

        return response()->json([
            'logs' => $logs,
            'is_clocked_in' => (bool) $activeLog,
            'active_since' => $activeLog?->clock_in?->format('g:ia'),
        ]);
    }

    /**
     * Upload a single image for a job.
     */
    public function uploadImage(Request $request, $id)
    {
        $job = Job::where('assigned_to', Auth::id())->findOrFail($id);

        $request->validate([
            'image' => 'required|image|mimes:jpeg,jpg,png,webp|max:5120',
        ]);

        $file = $request->file('image');
        $filename = 'job_' . $job->id . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('job-images', $filename, 'public');

        $job->update(['image' => $path]);

        return response()->json([
            'success' => true,
            'image_url' => asset('storage/' . $path),
            'message' => 'Image uploaded.',
        ]);
    }
}
