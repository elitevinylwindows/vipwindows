<?php

namespace App\Http\Controllers\Installer;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\JobItem;
use App\Models\JobNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InstallerJobController extends Controller
{
    public function calendar(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $startOfMonth = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        // Get all jobs for the month
        $jobs = Job::where('assigned_to', Auth::id())
            ->whereNotNull('scheduled_date')
            ->whereBetween('scheduled_date', [$startOfMonth, $endOfMonth])
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

        return view('installer.calendar', compact(
            'jobs', 'jobsByDate', 'month', 'year', 'startOfMonth', 'endOfMonth',
            'totalMonth', 'pending', 'scheduled', 'inProgress', 'completed'
        ));
    }

    public function index(Request $request)
    {
        $status = $request->get('status', 'all');

        $query = Job::where('assigned_to', Auth::id())
            ->with(['jobNotes.author'])
            ->orderByDesc('created_at');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $jobs = $query->paginate(20);

        // Stats scoped to this installer
        $todayJobs = Job::where('assigned_to', Auth::id())->whereDate('scheduled_date', today())->count();
        $weekJobs = Job::where('assigned_to', Auth::id())->whereBetween('scheduled_date', [today(), today()->addDays(7)])->count();
        $inProgress = Job::where('assigned_to', Auth::id())->where('status', 'in_progress')->count();

        return view('installer.jobs.index', compact('jobs', 'status', 'todayJobs', 'weekJobs', 'inProgress'));
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
            'scheduled_date'   => 'nullable|date',
            'scheduled_time'   => 'nullable|string|max:20',
            'estimated_duration' => 'nullable|string|max:50',
            'notes'            => 'nullable|string|max:5000',
        ]);

        // Auto-generate job number: IJ-XX-0001
        $prefix = 'IJ-' . strtoupper(substr(Auth::user()->name, 0, 2)) . '-';
        $last = Job::where('job_number', 'like', $prefix . '%')->orderByDesc('job_number')->first();
        $next = $last ? (intval(substr($last->job_number, strlen($prefix))) + 1) : 1;
        $jobNumber = $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);

        $job = Job::create(array_merge($validated, [
            'job_number'  => $jobNumber,
            'status'      => !empty($validated['scheduled_date']) ? 'scheduled' : 'pending',
            'priority'    => $validated['priority'] ?? 'normal',
            'assigned_to' => Auth::id(),
            'created_by'  => Auth::id(),
        ]));

        if ($request->ajax()) {
            return response()->json(['success' => true, 'job' => $job]);
        }

        return redirect()->route('installer.jobs.index')->with('success', 'Job created successfully.');
    }

    public function show($id)
    {
        $job = Job::where('assigned_to', Auth::id())
            ->findOrFail($id);

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
        try {
            $items = $job->jobItems()->get()->toArray();
        } catch (\Exception $e) {}

        return response()->json([
            'job' => $job,
            'notes' => $notes,
            'items' => $items,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $job = Job::where('assigned_to', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:scheduled,in_progress,completed,cancelled',
        ]);

        $job->update([
            'status' => $validated['status'],
            'actual_start' => $validated['status'] === 'in_progress' ? now() : $job->actual_start,
            'actual_end' => $validated['status'] === 'completed' ? now() : $job->actual_end,
        ]);

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
            'notes'       => 'nullable|string|max:1000',
        ]);

        $sortOrder = ($job->jobItems()->max('sort_order') ?? 0) + 1;

        $item = JobItem::create([
            'job_id'      => $job->id,
            'description' => $validated['description'],
            'item_type'   => $validated['item_type'] ?? 'other',
            'qty'         => $validated['qty'],
            'notes'       => $validated['notes'] ?? null,
            'sort_order'  => $sortOrder,
        ]);

        return response()->json(['success' => true, 'item' => $item]);
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
            'scheduled_date'   => 'nullable|date',
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
}
