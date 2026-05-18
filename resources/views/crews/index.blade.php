@extends('layouts.app')
@section('title', 'Crews')

@push('styles')
<style>
    .crew-wrapper {
        display: flex;
        height: calc(100vh - 120px);
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        overflow: hidden;
    }

    /* ── Left Rail ─────────────────────────────────────── */
    .crew-sidebar {
        width: 300px;
        min-width: 300px;
        background: var(--vip-primary);
        color: #fff;
        display: flex;
        flex-direction: column;
    }
    .crew-sidebar-header {
        padding: 14px 16px 12px;
        border-bottom: 1px solid rgba(255,255,255,.1);
        background: rgba(0,0,0,.15);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .crew-sidebar-header h6 {
        margin: 0; font-size: .85rem; font-weight: 700;
        letter-spacing: .5px; color: var(--vip-accent);
    }
    .crew-sidebar-header .btn-new {
        background: var(--vip-accent);
        color: var(--vip-primary);
        border: none;
        font-size: .72rem;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 5px;
        cursor: pointer;
    }
    .crew-sidebar-header .btn-new:hover { background: #d4b35a; color: #000; }

    .crew-sidebar-body { flex: 1; overflow-y: auto; padding: 4px 0; }
    .crew-sidebar-body::-webkit-scrollbar { width: 4px; }
    .crew-sidebar-body::-webkit-scrollbar-thumb { background: rgba(255,255,255,.2); border-radius: 4px; }

    .crew-item {
        display: block;
        padding: 10px 14px;
        color: rgba(255,255,255,.75);
        text-decoration: none;
        border-left: 3px solid transparent;
        border-bottom: 1px solid rgba(255,255,255,.05);
        cursor: pointer;
        transition: all .15s;
    }
    .crew-item:hover { background: rgba(255,255,255,.08); color: #fff; }
    .crew-item.active { background: rgba(201,168,76,.12); border-left-color: var(--vip-accent); color: #fff; }
    .crew-item .crew-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2px; }
    .crew-item .crew-name { font-weight: 600; font-size: .85rem; }
    .crew-item .crew-meta { font-size: .72rem; color: rgba(255,255,255,.45); }

    .crew-count-badge {
        font-size: .65rem;
        background: rgba(255,255,255,.15);
        padding: 2px 8px;
        border-radius: 10px;
    }

    /* ── Detail Panel ─────────────────────────────────── */
    .crew-detail { flex: 1; overflow-y: auto; padding: 2rem; }
    .crew-detail .empty-state {
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        height: 100%; color: #aaa;
    }
    .crew-detail .empty-state i { font-size: 3rem; margin-bottom: .5rem; }

    .detail-header {
        display: flex; justify-content: space-between; align-items: flex-start;
        margin-bottom: 1.5rem; padding-bottom: 1rem;
        border-bottom: 1px solid #eee;
    }
    .detail-header h5 { font-weight: 700; margin: 0; }

    .member-card {
        display: flex; align-items: center; gap: .75rem;
        padding: .75rem 1rem; background: #f9f8f5; border-radius: .5rem;
        margin-bottom: .5rem; transition: all .15s;
    }
    .member-card:hover { background: #f0efe9; }
    .member-avatar {
        width: 40px; height: 40px; border-radius: 50%;
        background: var(--vip-primary); color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: .8rem;
    }
    .member-info { flex: 1; }
    .member-info .name { font-weight: 600; font-size: .85rem; }
    .member-info .role { font-size: .72rem; color: #888; }
    .lead-badge { font-size: .65rem; background: var(--vip-accent); color: #fff; padding: 2px 8px; border-radius: 10px; }
</style>
@endpush

@section('content')
<div class="page-wrapper p-3">
    <div class="crew-wrapper">
        {{-- Left Rail --}}
        <div class="crew-sidebar">
            <div class="crew-sidebar-header">
                <h6><i class="bi bi-people-fill me-1"></i> CREWS</h6>
                <button class="btn-new" onclick="openCrewModal()"><i class="bi bi-plus"></i> New Crew</button>
            </div>
            <div class="crew-sidebar-body" id="crewList">
                @forelse($crews as $crew)
                    <div class="crew-item" data-id="{{ $crew->id }}" onclick="selectCrew({{ $crew->id }})">
                        <div class="crew-top">
                            <span class="crew-name">{{ $crew->name }}</span>
                            <span class="crew-count-badge">{{ $crew->members->count() }} <i class="bi bi-person-fill" style="font-size:.6rem;"></i></span>
                        </div>
                        <div class="crew-meta">
                            @if($crew->status === 'inactive') <span class="text-warning">Inactive</span> @endif
                            {{ $crew->members->pluck('name')->take(3)->join(', ') }}{{ $crew->members->count() > 3 ? '...' : '' }}
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4 opacity-50" style="font-size:.8rem;">
                        <i class="bi bi-people fs-3 d-block mb-1"></i>No crews yet
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Detail Panel --}}
        <div class="crew-detail" id="crewDetail">
            <div class="empty-state">
                <i class="bi bi-people"></i>
                <div class="small">Select a crew or create a new one</div>
            </div>
        </div>
    </div>
</div>

{{-- Create/Edit Crew Modal --}}
<div class="modal fade" id="crewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold" id="crewModalTitle">New Crew</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="crewEditId">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Crew Name</label>
                        <input type="text" id="crewName" class="form-control form-control-sm" placeholder="e.g. Crew A, North Team" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Status</label>
                        <select id="crewStatus" class="form-select form-select-sm">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label small">Description</label>
                        <textarea id="crewDesc" class="form-control form-control-sm" rows="2" placeholder="Optional notes about this crew"></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Members</label>
                        <p class="text-muted" style="font-size:.75rem;">Select installers or technicians to add to this crew. Check the star to set a crew lead.</p>
                        <div class="border rounded" style="max-height:250px; overflow-y:auto;">
                            @foreach($installers as $inst)
                                <label class="d-flex align-items-center gap-2 px-3 py-2 border-bottom" style="cursor:pointer; font-size:.85rem;">
                                    <input type="checkbox" class="form-check-input crew-member-check" value="{{ $inst->id }}" style="margin:0;">
                                    <div class="flex-fill">
                                        <span class="fw-semibold">{{ $inst->name }}</span>
                                        <span class="text-muted ms-1" style="font-size:.72rem;">{{ ucfirst($inst->role) }}{{ $inst->company_name ? ' — ' . $inst->company_name : '' }}</span>
                                    </div>
                                    <button type="button" class="btn btn-sm p-0 lead-star" data-user="{{ $inst->id }}" onclick="toggleLead(event, {{ $inst->id }})" title="Set as crew lead">
                                        <i class="bi bi-star" style="font-size:.9rem; color:#ccc;"></i>
                                    </button>
                                </label>
                            @endforeach
                            @if($installers->isEmpty())
                                <div class="text-center py-3 text-muted small">No active installers or technicians found.</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-vip" onclick="saveCrew()"><i class="bi bi-check me-1"></i> Save Crew</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
let selectedLeadId = null;

function selectCrew(id) {
    // Highlight in sidebar
    document.querySelectorAll('.crew-item').forEach(el => el.classList.remove('active'));
    const item = document.querySelector(`.crew-item[data-id="${id}"]`);
    if (item) item.classList.add('active');

    fetch(`{{ url('admin/crews') }}/${id}`, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            const c = data.crew;
            const members = c.members || [];
            const lead = members.find(m => m.pivot && m.pivot.is_lead);

            let membersHtml = '';
            if (members.length) {
                members.forEach(m => {
                    const isLead = m.pivot && m.pivot.is_lead;
                    membersHtml += `
                        <div class="member-card">
                            <div class="member-avatar">${m.name.substring(0,2).toUpperCase()}</div>
                            <div class="member-info">
                                <div class="name">${m.name} ${isLead ? '<span class="lead-badge"><i class="bi bi-star-fill" style="font-size:.55rem;"></i> Lead</span>' : ''}</div>
                                <div class="role">${m.role ? m.role.charAt(0).toUpperCase() + m.role.slice(1) : ''}${m.company_name ? ' — ' + m.company_name : ''}${m.phone ? ' · ' + m.phone : ''}</div>
                            </div>
                        </div>`;
                });
            } else {
                membersHtml = '<div class="text-center py-3 text-muted small">No members assigned yet.</div>';
            }

            document.getElementById('crewDetail').innerHTML = `
                <div class="detail-header">
                    <div>
                        <h5>${c.name} ${c.status === 'inactive' ? '<span class="badge bg-warning text-dark" style="font-size:.65rem;">Inactive</span>' : '<span class="badge bg-success" style="font-size:.65rem;">Active</span>'}</h5>
                        ${c.description ? '<p class="text-muted small mb-0">' + c.description + '</p>' : ''}
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-dark" onclick="editCrew(${c.id})"><i class="bi bi-pencil me-1"></i>Edit</button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteCrew(${c.id})"><i class="bi bi-trash me-1"></i>Delete</button>
                    </div>
                </div>

                <h6 class="fw-bold mb-3"><i class="bi bi-people me-2"></i>Members (${members.length})</h6>
                ${membersHtml}
            `;
        });
}

function openCrewModal() {
    document.getElementById('crewEditId').value = '';
    document.getElementById('crewName').value = '';
    document.getElementById('crewDesc').value = '';
    document.getElementById('crewStatus').value = 'active';
    selectedLeadId = null;

    document.querySelectorAll('.crew-member-check').forEach(cb => cb.checked = false);
    document.querySelectorAll('.lead-star i').forEach(i => { i.className = 'bi bi-star'; i.style.color = '#ccc'; });

    document.getElementById('crewModalTitle').textContent = 'New Crew';
    new bootstrap.Modal(document.getElementById('crewModal')).show();
}

function editCrew(id) {
    fetch(`{{ url('admin/crews') }}/${id}`, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            const c = data.crew;
            document.getElementById('crewEditId').value = c.id;
            document.getElementById('crewName').value = c.name;
            document.getElementById('crewDesc').value = c.description || '';
            document.getElementById('crewStatus').value = c.status;

            const memberIds = (c.members || []).map(m => m.id);
            const lead = (c.members || []).find(m => m.pivot && m.pivot.is_lead);
            selectedLeadId = lead ? lead.id : null;

            document.querySelectorAll('.crew-member-check').forEach(cb => {
                cb.checked = memberIds.includes(parseInt(cb.value));
            });
            document.querySelectorAll('.lead-star i').forEach(i => {
                const uid = parseInt(i.parentElement.dataset.user);
                if (uid === selectedLeadId) {
                    i.className = 'bi bi-star-fill'; i.style.color = 'var(--vip-accent)';
                } else {
                    i.className = 'bi bi-star'; i.style.color = '#ccc';
                }
            });

            document.getElementById('crewModalTitle').textContent = 'Edit Crew';
            new bootstrap.Modal(document.getElementById('crewModal')).show();
        });
}

function toggleLead(e, userId) {
    e.preventDefault();
    e.stopPropagation();

    // Also check the member checkbox
    const cb = document.querySelector(`.crew-member-check[value="${userId}"]`);
    if (cb) cb.checked = true;

    document.querySelectorAll('.lead-star i').forEach(i => { i.className = 'bi bi-star'; i.style.color = '#ccc'; });

    if (selectedLeadId === userId) {
        selectedLeadId = null;
    } else {
        selectedLeadId = userId;
        const star = document.querySelector(`.lead-star[data-user="${userId}"] i`);
        star.className = 'bi bi-star-fill'; star.style.color = 'var(--vip-accent)';
    }
}

function saveCrew() {
    const id = document.getElementById('crewEditId').value;
    const members = [];
    document.querySelectorAll('.crew-member-check:checked').forEach(cb => members.push(parseInt(cb.value)));

    const payload = {
        name: document.getElementById('crewName').value,
        description: document.getElementById('crewDesc').value,
        status: document.getElementById('crewStatus').value,
        members: members,
        lead_id: selectedLeadId,
    };

    const url = id ? `{{ url('admin/crews') }}/${id}` : '{{ route("admin.crews.store") }}';
    const method = id ? 'PUT' : 'POST';

    fetch(url, {
        method, headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('crewModal')).hide();
            location.reload();
        } else if (data.message) {
            alert(data.message);
        }
    });
}

function deleteCrew(id) {
    if (!confirm('Delete this crew? Members will not be deleted.')) return;
    fetch(`{{ url('admin/crews') }}/${id}`, {
        method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) location.reload();
        else if (data.message) alert(data.message);
    });
}
</script>
@endpush
@endsection
