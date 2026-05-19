@extends('layouts.app')
@section('title', 'Team Members')

@section('content')
<div class="p-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Team Members</h4>
            <p class="text-muted mb-0 small">Manage admins, installers and schedulers</p>
        </div>
        <button class="btn btn-vip" data-bs-toggle="modal" data-bs-target="#addMemberModal">
            <i class="bi bi-person-plus me-1"></i> Add Member
        </button>
    </div>

    {{-- Stat cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body text-center py-3">
                    <div class="fs-3 fw-bold text-dark">{{ $stats['total'] }}</div>
                    <div class="small text-muted">Total Members</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body text-center py-3">
                    <div class="fs-3 fw-bold" style="color:#c9a84c;">{{ $stats['admins'] }}</div>
                    <div class="small text-muted">Admins</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body text-center py-3">
                    <div class="fs-3 fw-bold text-primary">{{ $stats['installers'] }}</div>
                    <div class="small text-muted">Installers</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body text-center py-3">
                    <div class="fs-3 fw-bold text-info">{{ $stats['schedulers'] }}</div>
                    <div class="small text-muted">Schedulers</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Members table --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Member</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th class="text-center">Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($members as $m)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                                         style="width:36px;height:36px;font-size:.85rem;font-weight:600;color:#fff;
                                                background:{{ $m->role === 'admin' ? '#c9a84c' : ($m->role === 'installer' ? '#0d6efd' : '#0dcaf0') }};">
                                        {{ strtoupper(substr($m->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $m->name }}</div>
                                        @if($m->id === auth('vip')->id())
                                            <span class="badge bg-warning text-dark" style="font-size:.6rem;">YOU</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="text-muted small">{{ $m->email }}</td>
                            <td class="text-muted small">{{ $m->phone ?? '—' }}</td>
                            <td>
                                @php
                                    $roleBadge = match($m->role) {
                                        'admin'     => 'bg-warning text-dark',
                                        'installer' => 'bg-primary',
                                        'scheduler' => 'bg-info text-dark',
                                        default     => 'bg-secondary',
                                    };
                                    $roleIcon = match($m->role) {
                                        'admin'     => 'bi-shield-lock',
                                        'installer' => 'bi-tools',
                                        'scheduler' => 'bi-calendar-check',
                                        default     => 'bi-person',
                                    };
                                @endphp
                                <span class="badge {{ $roleBadge }}">
                                    <i class="bi {{ $roleIcon }} me-1"></i>{{ ucfirst($m->role) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="form-check form-switch d-inline-block">
                                    <input class="form-check-input toggle-status" type="checkbox"
                                           data-id="{{ $m->id }}"
                                           {{ $m->status === 'active' ? 'checked' : '' }}
                                           {{ $m->id === auth('vip')->id() ? 'disabled' : '' }}>
                                </div>
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-outline-secondary edit-member-btn"
                                        data-id="{{ $m->id }}"
                                        data-name="{{ $m->name }}"
                                        data-email="{{ $m->email }}"
                                        data-phone="{{ $m->phone }}"
                                        data-role="{{ $m->role }}"
                                        data-status="{{ $m->status }}"
                                        title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                @if($m->id !== auth('vip')->id())
                                <form method="POST" action="{{ route('admin.team.destroy', $m->id) }}"
                                      class="d-inline" onsubmit="return confirm('Remove {{ addslashes($m->name) }} from the team?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Remove">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="bi bi-people fs-1 d-block mb-2 opacity-25"></i>
                                No team members yet. Click <strong>Add Member</strong> to get started.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Role descriptions --}}
    <div class="row g-3 mt-3">
        <div class="col-md-4">
            <div class="card border-0" style="background:rgba(201,168,76,.08);">
                <div class="card-body py-3">
                    <h6 class="fw-bold mb-1"><i class="bi bi-shield-lock me-1" style="color:#c9a84c;"></i> Admin</h6>
                    <p class="small text-muted mb-0">Full access to all features including settings, team management, and master data.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0" style="background:rgba(13,110,253,.06);">
                <div class="card-body py-3">
                    <h6 class="fw-bold mb-1"><i class="bi bi-tools me-1 text-primary"></i> Installer</h6>
                    <p class="small text-muted mb-0">Access to the installer portal: jobs, calendar, quotes, tech measures, and messaging.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0" style="background:rgba(13,202,240,.08);">
                <div class="card-body py-3">
                    <h6 class="fw-bold mb-1"><i class="bi bi-calendar-check me-1 text-info"></i> Scheduler</h6>
                    <p class="small text-muted mb-0">Limited access — can only view the calendar and create or manage events.</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Add Member Modal --}}
<div class="modal fade" id="addMemberModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.team.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add Team Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Phone</label>
                        <input type="text" name="phone" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                        <select name="role" class="form-select" required>
                            <option value="admin">Admin — Full access</option>
                            <option value="installer">Installer — Installer portal</option>
                            <option value="scheduler">Scheduler — Calendar only</option>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" required minlength="6">
                        <div class="form-text">Minimum 6 characters</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-vip"><i class="bi bi-person-plus me-1"></i> Add Member</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Edit Member Modal --}}
<div class="modal fade" id="editMemberModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="editMemberForm">
            @csrf @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Edit Team Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="editEmail" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Phone</label>
                        <input type="text" name="phone" id="editPhone" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                        <select name="role" id="editRole" class="form-select" required>
                            <option value="admin">Admin — Full access</option>
                            <option value="installer">Installer — Installer portal</option>
                            <option value="scheduler">Scheduler — Calendar only</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" id="editStatus" class="form-select" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold">New Password</label>
                        <input type="password" name="password" class="form-control" minlength="6">
                        <div class="form-text">Leave blank to keep current password</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-vip"><i class="bi bi-check-lg me-1"></i> Save Changes</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Edit member
    document.querySelectorAll('.edit-member-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            document.getElementById('editMemberForm').action = `/admin/team/${id}`;
            document.getElementById('editName').value = btn.dataset.name;
            document.getElementById('editEmail').value = btn.dataset.email;
            document.getElementById('editPhone').value = btn.dataset.phone || '';
            document.getElementById('editRole').value = btn.dataset.role;
            document.getElementById('editStatus').value = btn.dataset.status || 'active';
            new bootstrap.Modal(document.getElementById('editMemberModal')).show();
        });
    });

    // Toggle status
    document.querySelectorAll('.toggle-status').forEach(el => {
        el.addEventListener('change', async function() {
            const id = this.dataset.id;
            try {
                const res = await fetch(`/admin/team/${id}/toggle-status`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });
                if (!res.ok) throw new Error('Failed');
                const data = await res.json();
                // Update visually — the switch already toggled
            } catch (err) {
                this.checked = !this.checked; // revert
                alert('Could not update status. Please try again.');
            }
        });
    });
</script>
@endpush
