<?php

namespace App\Http\Controllers;

use App\Models\Crew;
use App\Models\VipUser;
use Illuminate\Http\Request;

class CrewController extends Controller
{
    public function index()
    {
        $crews = Crew::with('members')->orderBy('name')->get();

        // Get all installers + technicians for member assignment
        $installers = VipUser::whereIn('role', ['installer', 'technician'])
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('crews.index', compact('crews', 'installers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'members'     => 'nullable|array',
            'members.*'   => 'exists:vip_users,id',
            'lead_id'     => 'nullable|exists:vip_users,id',
        ]);

        $crew = Crew::create([
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        // Attach members
        if (!empty($validated['members'])) {
            $syncData = [];
            foreach ($validated['members'] as $userId) {
                $syncData[$userId] = [
                    'is_lead' => isset($validated['lead_id']) && $validated['lead_id'] == $userId,
                ];
            }
            $crew->members()->sync($syncData);
        }

        return response()->json(['success' => true, 'crew' => $crew->load('members')]);
    }

    public function show($id)
    {
        $crew = Crew::with('members')->findOrFail($id);
        return response()->json(['crew' => $crew]);
    }

    public function update(Request $request, $id)
    {
        $crew = Crew::findOrFail($id);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'status'      => 'nullable|in:active,inactive',
            'members'     => 'nullable|array',
            'members.*'   => 'exists:vip_users,id',
            'lead_id'     => 'nullable|exists:vip_users,id',
        ]);

        $crew->update([
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? $crew->description,
            'status'      => $validated['status'] ?? $crew->status,
        ]);

        // Sync members
        if (isset($validated['members'])) {
            $syncData = [];
            foreach ($validated['members'] as $userId) {
                $syncData[$userId] = [
                    'is_lead' => isset($validated['lead_id']) && $validated['lead_id'] == $userId,
                ];
            }
            $crew->members()->sync($syncData);
        }

        return response()->json(['success' => true, 'crew' => $crew->load('members')]);
    }

    public function destroy($id)
    {
        $crew = Crew::findOrFail($id);

        // Check if crew is assigned to any active orders
        $activeOrders = $crew->orders()->whereNotIn('status', ['completed', 'cancelled'])->count();
        if ($activeOrders > 0) {
            return response()->json([
                'success' => false,
                'message' => "This crew is assigned to {$activeOrders} active order(s). Reassign them before deleting.",
            ], 422);
        }

        $crew->delete();
        return response()->json(['success' => true]);
    }
}
