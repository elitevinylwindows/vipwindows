<?php

namespace App\Http\Controllers\Installer;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\JobNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InstallerJobController extends Controller
{
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

        return response()->json([
            'job' => $job,
            'notes' => $notes,
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
}
