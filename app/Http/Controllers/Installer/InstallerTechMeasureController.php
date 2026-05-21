<?php

namespace App\Http\Controllers\Installer;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\JobTimeLog;
use App\Models\Service;
use App\Models\TechMeasure;
use App\Models\TechMeasureItem;
use App\Models\TechMeasurePhoto;
use App\Models\VipMasterOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InstallerTechMeasureController extends Controller
{
    /**
     * List tech measures assigned to this installer.
     */
    public function index(Request $request)
    {
        $user = Auth::guard('vip')->user();
        $status = $request->input('status', 'all');

        $query = TechMeasure::with(['calendarEvent', 'crew'])
            ->where(function ($q) use ($user) {
                $q->where('assigned_to', $user->id)
                  ->orWhereHas('crew', function ($cq) use ($user) {
                      $cq->whereHas('members', fn($mq) => $mq->where('crew_members.user_id', $user->id));
                  });
            })
            ->orderByDesc('created_at');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $measures = $query->paginate(50);

        // VIP Master options for dropdowns
        $unitOptions = VipMasterOption::optionsFor('unit');
        $frameTypeOptions = VipMasterOption::optionsFor('frame_type');
        $gridOptions = VipMasterOption::optionsFor('grid');
        $patternOptions = VipMasterOption::optionsFor('pattern');

        return view('installer.tech-measures.index', compact(
            'measures', 'status', 'unitOptions', 'frameTypeOptions', 'gridOptions', 'patternOptions'
        ));
    }

    /**
     * Show tech measure detail (JSON for AJAX).
     */
    public function show($id)
    {
        $user = Auth::guard('vip')->user();
        // Fresh load to ensure we get the latest job_id from DB
        $measure = TechMeasure::with(['items.photos', 'photos', 'calendarEvent'])
            ->findOrFail($id);

        // Find linked job for time tracking
        $job = $this->findTimeTrackingJob($measure);

        // If findTimeTrackingJob didn't find via job_id, do a broader search:
        // look for ANY TM- job that has an active (unclosed) time log for this user
        if (!$job) {
            $job = Job::where('job_number', 'like', 'TM-%')
                ->whereHas('timeLogs', function ($q) use ($user) {
                    $q->where('user_id', $user->id)->whereNull('clock_out');
                })
                ->first();

            // Link it if found
            if ($job) {
                $measure->update(['job_id' => $job->id]);
            }
        }

        $activeLog = null;
        $totalTimeMinutes = 0;

        if ($job) {
            $activeLog = $job->timeLogs()->where('user_id', $user->id)->whereNull('clock_out')->first();
            $totalTimeMinutes = $job->timeLogs()
                ->where('user_id', $user->id)
                ->whereNotNull('clock_out')
                ->sum('total_minutes');
        }

        $measureArray = $measure->toArray();
        $measureArray['is_clocked_in'] = (bool) $activeLog;
        $measureArray['active_since'] = $activeLog?->clock_in?->toISOString();
        $measureArray['total_time_minutes'] = $totalTimeMinutes;
        $measureArray['time_tracking_job_id'] = $job?->id;

        return response()->json([
            'measure' => $measureArray,
            'items' => $measure->items->map(function ($item) {
                return array_merge($item->toArray(), [
                    'photos' => $item->photos->map(fn($p) => [
                        'id' => $p->id,
                        'url' => asset('storage/' . $p->file_path),
                        'caption' => $p->caption,
                    ]),
                ]);
            }),
            'photos' => $measure->photos->where('tech_measure_item_id', null)->map(fn($p) => [
                'id' => $p->id,
                'url' => asset('storage/' . $p->file_path),
                'caption' => $p->caption,
            ])->values(),
        ]);
    }

    /**
     * Start a tech measure (clock in).
     */
    public function start($id)
    {
        $measure = TechMeasure::findOrFail($id);

        if ($measure->status === 'completed' || $measure->status === 'converted') {
            return response()->json(['error' => 'This measure is already completed.'], 422);
        }

        $measure->update([
            'status' => 'in_progress',
            'started_at' => $measure->started_at ?? now(),
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Complete a tech measure.
     */
    public function complete(Request $request, $id)
    {
        $measure = TechMeasure::findOrFail($id);

        $data = [
            'status' => 'completed',
            'completed_at' => now(),
        ];

        // Save frame & grid data along with completion
        if ($request->has('frame_type')) {
            $data['frame_type'] = $request->input('frame_type');
        }
        if ($request->has('retrofit_bottom_only')) {
            $data['retrofit_bottom_only'] = $request->boolean('retrofit_bottom_only');
        }
        if ($request->has('block_frame_bottom')) {
            $data['block_frame_bottom'] = $request->boolean('block_frame_bottom');
        }
        if ($request->has('has_grids')) {
            $data['has_grids'] = $request->boolean('has_grids');
        }
        if ($request->has('grid_list')) {
            $data['grid_list'] = $request->input('grid_list');
        }
        if ($request->has('grid_pattern')) {
            $data['grid_pattern'] = $request->input('grid_pattern');
        }

        $measure->update($data);

        // Auto clock-out any active time logs OR create a time log if never clocked in
        $user = Auth::guard('vip')->user();
        $job = $this->findOrCreateTimeTrackingJob($measure);

        if ($job) {
            // Mark the job as completed
            $job->update(['status' => 'completed']);

            $clockOutTime = now();
            $clockInFallback = $measure->started_at ?? $measure->created_at;

            // Collect all user IDs that should get time logs (current user + crew members)
            $userIds = collect([$user->id]);
            if ($measure->crew_id) {
                $crewMemberIds = DB::table('crew_members')
                    ->where('crew_id', $measure->crew_id)
                    ->pluck('user_id');
                $userIds = $userIds->merge($crewMemberIds)->unique();
            }

            foreach ($userIds as $userId) {
                $activeLog = $job->timeLogs()->where('user_id', $userId)->whereNull('clock_out')->first();

                if ($activeLog) {
                    // Clock out the active session
                    $totalMinutes = $activeLog->clock_in->diffInMinutes($clockOutTime);
                    $earnings = $this->calculateEarnings($job, $totalMinutes);
                    $activeLog->update([
                        'clock_out'     => $clockOutTime,
                        'total_minutes' => $totalMinutes,
                        'earnings'      => $earnings,
                    ]);
                } else {
                    // No active log — auto-create one from started_at → completed_at
                    $totalMinutes = $clockInFallback->diffInMinutes($clockOutTime);
                    $earnings = $this->calculateEarnings($job, $totalMinutes);

                    // Only create if no completed log exists for this user+job
                    $existingLog = $job->timeLogs()->where('user_id', $userId)->whereNotNull('clock_out')->exists();
                    if (!$existingLog) {
                        JobTimeLog::create([
                            'job_id'        => $job->id,
                            'user_id'       => $userId,
                            'clock_in'      => $clockInFallback,
                            'clock_out'     => $clockOutTime,
                            'total_minutes' => $totalMinutes,
                            'earnings'      => $earnings,
                        ]);
                    }
                }
            }
        }

        return response()->json(['success' => true]);
    }

    /**
     * Clock in to a tech measure (start time tracking).
     */
    public function clockIn($id)
    {
        $user = Auth::guard('vip')->user();
        $measure = TechMeasure::with('calendarEvent')->findOrFail($id);

        if (in_array($measure->status, ['completed', 'converted'])) {
            return response()->json(['error' => 'This measure is already completed.'], 422);
        }

        // Auto-start the measure if pending
        if ($measure->status === 'pending') {
            $measure->update([
                'status' => 'in_progress',
                'started_at' => $measure->started_at ?? now(),
            ]);
        }

        // Find or create a job for time tracking
        $job = $this->findOrCreateTimeTrackingJob($measure);

        // Check if already clocked in
        $active = $job->timeLogs()->where('user_id', $user->id)->whereNull('clock_out')->first();
        if ($active) {
            return response()->json(['error' => 'You are already clocked in.'], 422);
        }

        $now = now();

        $log = JobTimeLog::create([
            'job_id'   => $job->id,
            'user_id'  => $user->id,
            'clock_in' => $now,
        ]);

        // Clock in crew members too
        if ($measure->crew_id) {
            $crewMemberIds = DB::table('crew_members')
                ->where('crew_id', $measure->crew_id)
                ->pluck('user_id')
                ->toArray();

            foreach ($crewMemberIds as $memberId) {
                if ($memberId == $user->id) continue;
                $memberActive = $job->timeLogs()->where('user_id', $memberId)->whereNull('clock_out')->exists();
                if (!$memberActive) {
                    JobTimeLog::create([
                        'job_id'   => $job->id,
                        'user_id'  => $memberId,
                        'clock_in' => $now,
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Clocked in.',
        ]);
    }

    /**
     * Clock out of a tech measure (stop time tracking).
     */
    public function clockOut($id)
    {
        $user = Auth::guard('vip')->user();
        $measure = TechMeasure::with('calendarEvent')->findOrFail($id);

        $job = $this->findTimeTrackingJob($measure);
        if (!$job) {
            return response()->json(['error' => 'No time tracking found.'], 422);
        }

        $active = $job->timeLogs()->where('user_id', $user->id)->whereNull('clock_out')->first();
        if (!$active) {
            return response()->json(['error' => 'You are not clocked in.'], 422);
        }

        $clockOut = now();
        $totalMinutes = $active->clock_in->diffInMinutes($clockOut);
        $earnings = $this->calculateEarnings($job, $totalMinutes);

        $active->update([
            'clock_out'     => $clockOut,
            'total_minutes' => $totalMinutes,
            'earnings'      => $earnings,
        ]);

        $payMsg = $earnings > 0 ? ' Pay: $' . number_format($earnings, 2) : '';

        return response()->json([
            'success' => true,
            'total_minutes' => $totalMinutes,
            'earnings' => $earnings,
            'message' => 'Clocked out. ' . floor($totalMinutes / 60) . 'h ' . ($totalMinutes % 60) . 'm logged.' . $payMsg,
        ]);
    }

    /**
     * Find the job used for time tracking on this tech measure.
     */
    private function findTimeTrackingJob(TechMeasure $measure): ?Job
    {
        // If already linked to a job, use that
        if ($measure->job_id) {
            return Job::find($measure->job_id);
        }

        // Look for auto-created time-tracking job via calendar event
        $event = $measure->calendarEvent;

        if ($event) {
            // Try matching by event's customer_name first
            $job = Job::where('service_id', $event->service_id)
                ->where('customer_name', $event->customer_name)
                ->where('scheduled_date', $event->event_date)
                ->first();

            if ($job) {
                // Link it for future lookups
                $measure->update(['job_id' => $job->id]);
                return $job;
            }

            // Try matching by measure's customer_name (job was created from measure data)
            $job = Job::where('customer_name', $measure->customer_name)
                ->where('scheduled_date', $event->event_date)
                ->where('job_number', 'like', 'TM-%')
                ->first();

            if ($job) {
                $measure->update(['job_id' => $job->id]);
                return $job;
            }
        }

        // Last resort: find any TM- job for this measure's customer with active time logs
        $job = Job::where('customer_name', $measure->customer_name)
            ->where('job_number', 'like', 'TM-%')
            ->whereHas('timeLogs', function ($q) {
                $q->where('user_id', \Illuminate\Support\Facades\Auth::guard('vip')->id());
            })
            ->first();

        if ($job) {
            $measure->update(['job_id' => $job->id]);
        }

        return $job;
    }

    /**
     * Find or create a job for time tracking from the calendar event.
     */
    private function findOrCreateTimeTrackingJob(TechMeasure $measure): Job
    {
        $existing = $this->findTimeTrackingJob($measure);
        if ($existing) return $existing;

        $event = $measure->calendarEvent;

        $job = Job::create([
            'service_id'     => $event?->service_id,
            'customer_name'  => $measure->customer_name,
            'customer_email' => $measure->customer_email,
            'customer_phone' => $measure->customer_phone,
            'address'        => $measure->address,
            'scheduled_date' => $event?->event_date,
            'status'         => 'in_progress',
            'job_number'     => 'TM-' . strtoupper(uniqid()),
            'assigned_to'    => Auth::guard('vip')->id(),
            'crew_id'        => $measure->crew_id,
            'notes'          => 'Time tracking for tech measure: ' . $measure->customer_name,
        ]);

        // Link the job back to the tech measure so findTimeTrackingJob always finds it
        $measure->update(['job_id' => $job->id]);

        return $job;
    }

    /**
     * Calculate earnings for a clock-out session.
     */
    private function calculateEarnings(Job $job, int $totalMinutes): float
    {
        $service = $job->service;
        if (!$service || $service->installer_pay <= 0) return 0;

        return match ($service->installer_pay_type) {
            'per_hour'   => round(($totalMinutes / 60) * $service->installer_pay, 2),
            'per_job'    => $service->installer_pay,
            'percentage' => round($service->base_price * ($service->installer_pay / 100), 2),
            default      => $service->installer_pay,
        };
    }

    /**
     * Add a measurement line item.
     */
    public function addItem(Request $request, $id)
    {
        $measure = TechMeasure::findOrFail($id);

        $validated = $request->validate([
            'room_label' => 'nullable|string|max:100',
            'opening_type' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:500',
            'series_id' => 'nullable|integer',
            'series_type' => 'nullable|string|max:100',
            'width' => 'nullable|string|max:50',
            'height' => 'nullable|string|max:50',
            'qty' => 'nullable|integer|min:1',
            'color_config' => 'nullable|string|max:50',
            'color_exterior' => 'nullable|string|max:100',
            'color_exterior_custom' => 'nullable|string|max:100',
            'color_interior' => 'nullable|string|max:100',
            'color_interior_custom' => 'nullable|string|max:100',
            'frame_type' => 'nullable|string|max:100',
            'glass_type' => 'nullable|string|max:100',
            'glass' => 'nullable|string|max:100',
            'grid' => 'nullable|string|max:100',
            'grid_pattern' => 'nullable|string|max:100',
            'grid_profile' => 'nullable|string|max:100',
            'grid_detail' => 'nullable|string|max:100',
            'tempered' => 'nullable|string|max:100',
            'tempered_fields' => 'nullable|array',
            'retrofit_bottom_only' => 'nullable|boolean',
            'no_logo_lock' => 'nullable|boolean',
            'double_lock' => 'nullable|boolean',
            'custom_lock_position' => 'nullable|boolean',
            'custom_vent_latch' => 'nullable|boolean',
            'knocked_down' => 'nullable|boolean',
            'existing_condition' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $validated['tech_measure_id'] = $measure->id;
        $validated['qty'] = $validated['qty'] ?? 1;
        $validated['sort_order'] = $measure->items()->count() + 1;

        $item = TechMeasureItem::create($validated);

        return response()->json([
            'success' => true,
            'item' => $item,
        ]);
    }

    /**
     * Update a measurement line item.
     */
    public function updateItem(Request $request, $measureId, $itemId)
    {
        $item = TechMeasureItem::where('tech_measure_id', $measureId)->findOrFail($itemId);
        $item->update($request->all());

        return response()->json(['success' => true, 'item' => $item->fresh()]);
    }

    /**
     * Remove a measurement line item.
     */
    public function removeItem($measureId, $itemId)
    {
        $item = TechMeasureItem::where('tech_measure_id', $measureId)->findOrFail($itemId);

        // Delete associated photos
        foreach ($item->photos as $photo) {
            Storage::disk('public')->delete($photo->file_path);
            $photo->delete();
        }

        $item->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Upload a photo (to measure or specific item).
     */
    public function uploadPhoto(Request $request, $id)
    {
        $request->validate([
            'photo' => 'required|image|max:10240',
            'item_id' => 'nullable|integer',
            'caption' => 'nullable|string|max:255',
        ]);

        $measure = TechMeasure::findOrFail($id);

        $path = $request->file('photo')->store('tech-measures/' . $id, 'public');

        $photo = TechMeasurePhoto::create([
            'tech_measure_id' => $measure->id,
            'tech_measure_item_id' => $request->input('item_id'),
            'file_path' => $path,
            'caption' => $request->input('caption'),
            'uploaded_by' => Auth::guard('vip')->id(),
        ]);

        return response()->json([
            'success' => true,
            'photo' => [
                'id' => $photo->id,
                'url' => asset('storage/' . $photo->file_path),
                'caption' => $photo->caption,
            ],
        ]);
    }

    /**
     * Delete a photo.
     */
    public function deletePhoto($measureId, $photoId)
    {
        $photo = TechMeasurePhoto::where('tech_measure_id', $measureId)->findOrFail($photoId);
        Storage::disk('public')->delete($photo->file_path);
        $photo->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Update general notes for a tech measure.
     */
    public function updateNotes(Request $request, $id)
    {
        $measure = TechMeasure::findOrFail($id);

        $data = [];
        if ($request->has('notes')) {
            $data['notes'] = $request->input('notes');
        }
        if ($request->has('frame_type')) {
            $data['frame_type'] = $request->input('frame_type');
        }
        if ($request->has('retrofit_bottom_only')) {
            $data['retrofit_bottom_only'] = $request->boolean('retrofit_bottom_only');
        }
        if ($request->has('block_frame_bottom')) {
            $data['block_frame_bottom'] = $request->boolean('block_frame_bottom');
        }

        if (!empty($data)) {
            $measure->update($data);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Update grid settings for a tech measure.
     */
    public function updateGrids(Request $request, $id)
    {
        $measure = TechMeasure::findOrFail($id);

        $validated = $request->validate([
            'has_grids' => 'nullable|boolean',
            'grid_list' => 'nullable|string|max:100',
            'grid_pattern' => 'nullable|string|max:100',
        ]);

        $measure->update([
            'has_grids' => $validated['has_grids'] ?? false,
            'grid_list' => $validated['grid_list'] ?? null,
            'grid_pattern' => $validated['grid_pattern'] ?? null,
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Download / print a PDF-ready view for a tech measure.
     */
    public function downloadPdf($id)
    {
        $measure = TechMeasure::with(['items', 'calendarEvent.service'])->findOrFail($id);
        $items = $measure->items()->orderBy('sort_order')->get();

        // Determine service title from calendar event
        $serviceTitle = 'Tech Measure';
        if ($measure->calendarEvent) {
            if ($measure->calendarEvent->service) {
                $serviceTitle = $measure->calendarEvent->service->name;
            } elseif ($measure->calendarEvent->title) {
                $serviceTitle = $measure->calendarEvent->title;
            }
        }

        return view('installer.tech-measures.pdf', compact('measure', 'items', 'serviceTitle'));
    }
}
