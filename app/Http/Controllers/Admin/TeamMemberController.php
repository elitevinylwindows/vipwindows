<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VipUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TeamMemberController extends Controller
{
    /**
     * Team roles that can be managed here.
     */
    private const TEAM_ROLES = ['admin', 'installer', 'scheduler'];

    public function index()
    {
        $members = VipUser::whereIn('role', self::TEAM_ROLES)
            ->orderByRaw("FIELD(role, 'admin', 'scheduler', 'installer')")
            ->orderBy('name')
            ->get();

        $stats = [
            'total'      => $members->count(),
            'admins'     => $members->where('role', 'admin')->count(),
            'installers' => $members->where('role', 'installer')->count(),
            'schedulers' => $members->where('role', 'scheduler')->count(),
            'active'     => $members->where('status', 'active')->count(),
        ];

        return view('admin.team.index', compact('members', 'stats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:150',
            'email'    => 'required|email|unique:vip_users,email',
            'phone'    => 'nullable|string|max:30',
            'role'     => 'required|in:admin,installer,scheduler',
            'password' => 'required|string|min:6',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['status'] = 'active';

        VipUser::create($validated);

        return redirect()->route('admin.team.index')->with('success', 'Team member added.');
    }

    public function update(Request $request, $id)
    {
        $member = VipUser::whereIn('role', self::TEAM_ROLES)->findOrFail($id);

        $validated = $request->validate([
            'name'     => 'required|string|max:150',
            'email'    => 'required|email|unique:vip_users,email,' . $member->id,
            'phone'    => 'nullable|string|max:30',
            'role'     => 'required|in:admin,installer,scheduler',
            'status'   => 'required|in:active,inactive',
            'password' => 'nullable|string|min:6',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $member->update($validated);

        return redirect()->route('admin.team.index')->with('success', 'Team member updated.');
    }

    public function destroy($id)
    {
        $member = VipUser::whereIn('role', self::TEAM_ROLES)->findOrFail($id);

        // Prevent deleting yourself
        if ($member->id === auth('vip')->id()) {
            return redirect()->route('admin.team.index')->with('error', 'You cannot remove yourself.');
        }

        $member->delete();

        return redirect()->route('admin.team.index')->with('success', 'Team member removed.');
    }

    public function toggleStatus($id)
    {
        $member = VipUser::whereIn('role', self::TEAM_ROLES)->findOrFail($id);

        $member->update([
            'status' => $member->status === 'active' ? 'inactive' : 'active',
        ]);

        return response()->json([
            'success' => true,
            'status'  => $member->status,
        ]);
    }
}
