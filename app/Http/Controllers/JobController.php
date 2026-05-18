<?php

namespace App\Http\Controllers;

use App\Models\Crew;
use App\Models\Invoice;
use App\Models\Job;
use App\Models\JobItem;
use App\Models\JobNote;
use App\Models\Service;
use App\Models\VipQuote as Quote;
use App\Models\VipUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'all');

        $query = Job::with(['assignee', 'creator', 'service'])
            ->orderByDesc('created_at');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $jobs = $query->paginate(20);

        // Stats
        $todayJobs = Job::whereDate('scheduled_date', today())->count();
        $weekJobs = Job::whereBetween('scheduled_date', [today(), today()->addDays(7)])->count();
        $inProgress = Job::where('status', 'in_progress')->count();
        $completedMonth = Job::where('status', 'completed')
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->count();

        // For dropdowns in create modal
        $technicians = VipUser::whereIn('role', ['technician', 'installer'])->orderBy('name')->get();
        $crews = Crew::where('status', 'active')->with('members')->orderBy('name')->get();
        $quotes = Quote::where('status', 'sent')->orderByDesc('created_at')->get();
        $invoices = Invoice::orderByDesc('created_at')->get();
        $services = Service::where('is_active', true)->orderBy('name')->get();

        return view('jobs.index', compact(
            'jobs', 'status', 'todayJobs', 'weekJobs', 'inProgress',
            'completedMonth', 'technicians', 'crews', 'quotes', 'invoices', 'services'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'install_address' => 'nullable|string|max:500',
            'install_city' => 'nullable|string|max:100',
            'install_state' => 'nullable|string|max:50',
            'install_zip' => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'priority' => 'nullable|in:low,normal,high,urgent',
            'assignment_type' => 'nullable|in:crew,installer',
            'crew_id' => 'nullable|exists:crews,id',
            'assigned_to' => 'nullable|exists:vip_users,id',
            'scheduled_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:scheduled_date',
            'scheduled_time' => 'nullable|string|max:20',
            'estimated_duration' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'from_quote' => 'nullable|exists:vip_quotes,id',
            'from_invoice' => 'nullable|exists:vip_invoices,id',
            'service_id' => 'nullable|exists:vip_services,id',
            'items' => 'nullable|array',
            'items.*.service_id' => 'nullable|exists:vip_services,id',
            'items.*.qty' => 'nullable|integer|min:1',
        ]);

        // Generate job number
        $lastJob = Job::withTrashed()->orderByDesc('id')->first();
        $nextNum = $lastJob ? (int) substr($lastJob->job_number, 4) + 1 : 1;
        $jobNumber = 'JOB-' . str_pad($nextNum, 5, '0', STR_PAD_LEFT);

        $status = 'pending';
        if (!empty($validated['scheduled_date'])) {
            $status = 'scheduled';
        }

        $assignmentType = $validated['assignment_type'] ?? 'crew';
        $assignedTo = $assignmentType === 'installer' ? ($validated['assigned_to'] ?? null) : null;
        $crewId = $assignmentType === 'crew' ? ($validated['crew_id'] ?? null) : null;

        $job = Job::create([
            'job_number' => $jobNumber,
            'quote_id' => $validated['from_quote'] ?? null,
            'invoice_id' => $validated['from_invoice'] ?? null,
            'service_id' => $validated['service_id'] ?? null,
            'customer_name' => $validated['customer_name'],
            'customer_email' => $validated['customer_email'] ?? null,
            'customer_phone' => $validated['customer_phone'] ?? null,
            'install_address' => $validated['install_address'] ?? null,
            'install_city' => $validated['install_city'] ?? null,
            'install_state' => $validated['install_state'] ?? null,
            'install_zip' => $validated['install_zip'] ?? null,
            'description' => $validated['description'] ?? null,
            'status' => $status,
            'priority' => $validated['priority'] ?? 'normal',
            'assignment_type' => $assignmentType,
            'assigned_to' => $assignedTo,
            'crew_id' => $crewId,
            'scheduled_date' => $validated['scheduled_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'scheduled_time' => $validated['scheduled_time'] ?? null,
            'estimated_duration' => $validated['estimated_duration'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'created_by' => Auth::id(),
        ]);

        // Create service line items
        $items = $validated['items'] ?? [];
        $sortOrder = 0;
        $primaryServiceId = null;
        foreach ($items as $item) {
            if (empty($item['service_id']) || empty($item['qty'])) continue;

            $service = Service::find($item['service_id']);
            if (!$service) continue;

            if (!$primaryServiceId) $primaryServiceId = $service->id;

            // Calculate installer pay
            $unitPay = 0;
            switch ($service->installer_pay_type) {
                case 'percentage':
                    $unitPay = $service->base_price * ($service->installer_pay / 100);
                    break;
                case 'per_job':
                    $unitPay = $service->installer_pay;
                    break;
                default: // per_unit, per_hour
                    $unitPay = $service->installer_pay ?? 0;
            }

            JobItem::create([
                'job_id' => $job->id,
                'service_id' => $service->id,
                'description' => $service->name,
                'item_type' => 'service',
                'qty' => $item['qty'],
                'unit_pay' => $unitPay,
                'total_pay' => $unitPay * $item['qty'],
                'sort_order' => $sortOrder++,
            ]);
        }

        // Set primary service on job for calendar color
        if ($primaryServiceId) {
            $job->update(['service_id' => $primaryServiceId]);
        }

        return redirect()->route('admin.jobs.index')
            ->with('success', "Job {$jobNumber} created successfully.");
    }

    public function show($id)
    {
        $job = Job::with(['assignee', 'creator', 'quote', 'invoice', 'service', 'jobItems.service', 'jobNotes.author'])->findOrFail($id);

        return response()->json([
            'job' => $job,
            'assignee' => $job->assignee,
            'creator' => $job->creator,
            'notes' => $job->jobNotes,
            'items' => $job->jobItems,
        ]);
    }

    public function update(Request $request, $id)
    {
        $job = Job::findOrFail($id);

        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'install_address' => 'nullable|string|max:500',
            'install_city' => 'nullable|string|max:100',
            'install_state' => 'nullable|string|max:50',
            'install_zip' => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'priority' => 'nullable|in:low,normal,high,urgent',
            'service_id' => 'nullable|exists:vip_services,id',
            'scheduled_date' => 'nullable|date',
            'scheduled_time' => 'nullable|string|max:20',
            'estimated_duration' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $job->update($validated);

        return redirect()->route('admin.jobs.index')
            ->with('success', "Job {$job->job_number} updated.");
    }

    public function assign(Request $request, $id)
    {
        $job = Job::findOrFail($id);

        $validated = $request->validate([
            'assigned_to' => 'required|exists:vip_users,id',
            'scheduled_date' => 'nullable|date',
            'scheduled_time' => 'nullable|string|max:20',
        ]);

        $updates = ['assigned_to' => $validated['assigned_to']];

        if (!empty($validated['scheduled_date'])) {
            $updates['scheduled_date'] = $validated['scheduled_date'];
            $updates['scheduled_time'] = $validated['scheduled_time'] ?? null;
            if ($job->status === 'pending') {
                $updates['status'] = 'scheduled';
            }
        }

        $job->update($updates);

        return response()->json(['success' => true, 'message' => 'Job assigned.']);
    }

    public function updateStatus(Request $request, $id)
    {
        $job = Job::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:pending,scheduled,in_progress,completed,cancelled',
            'completion_notes' => 'nullable|string',
        ]);

        $updates = ['status' => $validated['status']];

        if ($validated['status'] === 'in_progress' && !$job->actual_start) {
            $updates['actual_start'] = now();
        }

        if ($validated['status'] === 'completed') {
            $updates['actual_end'] = now();
            if (!empty($validated['completion_notes'])) {
                $updates['completion_notes'] = $validated['completion_notes'];
            }
        }

        $job->update($updates);

        return response()->json(['success' => true, 'message' => 'Status updated.']);
    }

    public function addNote(Request $request, $id)
    {
        $job = Job::findOrFail($id);

        $validated = $request->validate([
            'note' => 'required|string',
        ]);

        JobNote::create([
            'job_id' => $job->id,
            'note' => $validated['note'],
            'added_by' => Auth::id(),
        ]);

        return response()->json(['success' => true, 'message' => 'Note added.']);
    }

    public function destroy($id)
    {
        $job = Job::findOrFail($id);
        $job->delete();

        return redirect()->route('admin.jobs.index')
            ->with('success', "Job {$job->job_number} deleted.");
    }
}
