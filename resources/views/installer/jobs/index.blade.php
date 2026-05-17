@extends('layouts.installer')
@section('title', 'My Jobs')

@push('styles')
<style>
    .iq-container { display: flex; height: calc(100vh - 56px); overflow: hidden; }

    /* ── Left Rail ─────────────────────────────── */
    .iq-rail {
        width: 320px; min-width: 320px;
        background: var(--vip-primary);
        color: #fff;
        display: flex; flex-direction: column;
        border-right: 1px solid rgba(255,255,255,.06);
    }
    .iq-rail-header { padding: 1.25rem 1rem .75rem; }
    .iq-rail-header h6 { font-size: .75rem; text-transform: uppercase; letter-spacing: 1.2px; color: rgba(255,255,255,.5); margin-bottom: .75rem; }
    .iq-rail-search { display: flex; gap: .5rem; }
    .iq-rail-search input {
        flex: 1; background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.12);
        color: #fff; border-radius: .375rem; padding: .4rem .75rem; font-size: .85rem;
    }
    .iq-rail-search input::placeholder { color: rgba(255,255,255,.4); }
    .iq-rail-search input:focus { outline: none; border-color: var(--vip-accent); }

    .iq-rail-tabs { display: flex; gap: 0; padding: 0 1rem; margin-top: .75rem; flex-wrap: wrap; }
    .iq-rail-tabs .tab-btn {
        flex: 1; text-align: center; padding: .4rem .25rem; font-size: .7rem;
        background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.1);
        color: rgba(255,255,255,.6); cursor: pointer; transition: all .15s;
    }
    .iq-rail-tabs .tab-btn:first-child { border-radius: .3rem 0 0 .3rem; }
    .iq-rail-tabs .tab-btn:last-child { border-radius: 0 .3rem .3rem 0; }
    .iq-rail-tabs .tab-btn.active { background: var(--vip-accent); color: #fff; border-color: var(--vip-accent); }

    .iq-rail-list { flex: 1; overflow-y: auto; padding: .5rem; }
    .iq-card {
        background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.08);
        border-radius: .5rem; padding: .75rem 1rem; margin-bottom: .5rem;
        cursor: pointer; transition: all .15s;
    }
    .iq-card:hover { background: rgba(255,255,255,.08); border-color: rgba(201,168,76,.3); }
    .iq-card.active { background: rgba(201,168,76,.12); border-color: var(--vip-accent); }
    .iq-card .q-number { font-weight: 600; font-size: .9rem; color: #fff; }
    .iq-card .q-customer { font-size: .78rem; color: rgba(255,255,255,.55); margin-top: 2px; }
    .iq-card .q-meta { display: flex; justify-content: space-between; align-items: center; margin-top: .35rem; }
    .iq-card .q-date { font-size: .7rem; color: rgba(255,255,255,.4); }
    .iq-card .q-badge { font-size: .6rem; padding: 2px 6px; border-radius: 3px; font-weight: 600; text-transform: uppercase; }
    .q-badge-pending { background: rgba(255,193,7,.25); color: #ffc107; }
    .q-badge-scheduled { background: rgba(23,162,184,.25); color: #17a2b8; }
    .q-badge-in_progress { background: rgba(0,123,255,.25); color: #5ba8ff; }
    .q-badge-completed { background: rgba(40,167,69,.25); color: #7ddf9b; }
    .q-badge-cancelled { background: rgba(220,53,69,.25); color: #dc3545; }

    .iq-rail-footer {
        padding: .75rem 1rem; border-top: 1px solid rgba(255,255,255,.08);
        font-size: .75rem; color: rgba(255,255,255,.4);
        display: flex; justify-content: space-between;
    }

    /* ── Main Panel ────────────────────────────── */
    .iq-main { flex: 1; overflow-y: auto; background: var(--vip-light); }
    .iq-main-toolbar {
        background: #fff; border-bottom: 1px solid rgba(0,0,0,.06);
        padding: .75rem 1.5rem; display: flex; align-items: center; justify-content: space-between;
    }
    .iq-main-toolbar h5 { font-size: 1rem; font-weight: 700; margin: 0; }
    .iq-detail-body { padding: 1.5rem; }

    .iq-empty-state {
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        height: 60vh; color: rgba(0,0,0,.35);
    }
    .iq-empty-state i { font-size: 3rem; margin-bottom: 1rem; }

    .iq-info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
    .iq-info-card { background: #fff; border-radius: .5rem; padding: 1rem; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
    .iq-info-card .label { font-size: .7rem; text-transform: uppercase; letter-spacing: .5px; color: rgba(0,0,0,.45); margin-bottom: .25rem; }
    .iq-info-card .value { font-size: .9rem; font-weight: 600; color: #111; }

    .note-card { background: #fff; border-radius: .375rem; padding: .6rem .75rem; margin-bottom: .5rem; box-shadow: 0 1px 3px rgba(0,0,0,.04); }

    .job-items-tbl { width: 100%; border-collapse: collapse; background: #fff; border-radius: .5rem; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
    .job-items-tbl th { font-size: .7rem; text-transform: uppercase; letter-spacing: .5px; color: rgba(0,0,0,.4); padding: .5rem .75rem; border-bottom: 1px solid rgba(0,0,0,.08); background: #fafafa; }
    .job-items-tbl td { padding: .5rem .75rem; font-size: .82rem; border-bottom: 1px solid rgba(0,0,0,.04); }
    .job-items-tbl .item-done td { text-decoration: line-through; opacity: .5; }
    .item-check { cursor: pointer; }

    @media (max-width: 991.98px) {
        .iq-container { flex-direction: column; height: auto; }
        .iq-rail { width: 100%; min-width: 100%; max-height: 45vh; }
    }
</style>
@endpush

@section('content')
<div class="iq-container">
    {{-- Left Rail --}}
    <div class="iq-rail">
        <div class="iq-rail-header">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">My Jobs</h6>
                <button class="btn btn-sm btn-vip" data-bs-toggle="modal" data-bs-target="#createJobModal"><i class="bi bi-plus-lg me-1"></i>New Job</button>
            </div>
            <div class="iq-rail-search">
                <input type="text" id="iqSearch" placeholder="Search jobs...">
            </div>
            <div class="iq-rail-tabs">
                <div class="tab-btn {{ $status === 'all' ? 'active' : '' }}" data-status="all">All</div>
                <div class="tab-btn {{ $status === 'pending' ? 'active' : '' }}" data-status="pending">Pending</div>
                <div class="tab-btn {{ $status === 'scheduled' ? 'active' : '' }}" data-status="scheduled">Sched</div>
                <div class="tab-btn {{ $status === 'in_progress' ? 'active' : '' }}" data-status="in_progress">Active</div>
                <div class="tab-btn {{ $status === 'completed' ? 'active' : '' }}" data-status="completed">Done</div>
            </div>
        </div>

        <div class="iq-rail-list">
            @forelse($jobs as $job)
                <div class="iq-card" data-id="{{ $job->id }}" data-search="{{ strtolower(($job->job_number ?? '') . ' ' . ($job->customer_name ?? '') . ' ' . ($job->install_city ?? '')) }}">
                    <div class="q-number">{{ $job->job_number ?? 'JOB-' . $job->id }}</div>
                    <div class="q-customer"><i class="bi bi-person me-1"></i>{{ $job->customer_name ?: 'No customer' }}</div>
                    <div class="q-meta">
                        <span class="q-date">{{ $job->scheduled_date?->format('M d, Y') ?? 'Not scheduled' }}</span>
                        <span class="q-badge q-badge-{{ $job->status }}">{{ ucfirst(str_replace('_', ' ', $job->status)) }}</span>
                    </div>
                </div>
            @empty
                <div class="text-center py-4" style="color:rgba(255,255,255,.4);">
                    <i class="bi bi-tools" style="font-size:2rem;"></i>
                    <p class="mt-2 mb-0">No jobs assigned yet</p>
                </div>
            @endforelse
        </div>

        <div class="iq-rail-footer">
            <span>{{ $jobs->total() }} job{{ $jobs->total() !== 1 ? 's' : '' }}</span>
            <span>{{ $jobs->where('status', 'in_progress')->count() }} active</span>
        </div>
    </div>

    {{-- Main Panel --}}
    <div class="iq-main">
        <div class="iq-main-toolbar">
            <h5 id="iqDetailTitle">Job Details</h5>
            <div id="iqToolbarActions"></div>
        </div>
        <div class="iq-detail-body" id="iqDetailBody">
            <div class="iq-empty-state">
                <i class="bi bi-tools"></i>
                <p>Select a job to view details</p>
            </div>
        </div>
    </div>
</div>
{{-- Create Job Modal --}}
<div class="modal fade" id="createJobModal" tabindex="-1" aria-labelledby="createJobModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="background:var(--vip-primary); color:#fff; border:1px solid rgba(255,255,255,.1);">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="createJobModalLabel"><i class="bi bi-tools me-2"></i>New Job</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="createJobForm" method="POST" action="{{ route('installer.jobs.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small text-white-50">Customer Name <span class="text-danger">*</span></label>
                            <input type="text" name="customer_name" class="form-control bg-dark text-white border-secondary" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-white-50">Customer Email</label>
                            <input type="email" name="customer_email" class="form-control bg-dark text-white border-secondary">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-white-50">Customer Phone</label>
                            <input type="text" name="customer_phone" class="form-control bg-dark text-white border-secondary">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-white-50">Priority</label>
                            <select name="priority" class="form-select bg-dark text-white border-secondary">
                                <option value="normal" selected>Normal</option>
                                <option value="low">Low</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>

                        <div class="col-12"><hr class="border-secondary my-1"><label class="form-label small text-white-50 mt-1">Install Address</label></div>
                        <div class="col-md-12">
                            <input type="text" name="install_address" class="form-control bg-dark text-white border-secondary" placeholder="Street address">
                        </div>
                        <div class="col-md-5">
                            <input type="text" name="install_city" class="form-control bg-dark text-white border-secondary" placeholder="City">
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="install_state" class="form-control bg-dark text-white border-secondary" placeholder="State">
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="install_zip" class="form-control bg-dark text-white border-secondary" placeholder="Zip">
                        </div>

                        <div class="col-12"><hr class="border-secondary my-1"></div>
                        <div class="col-md-4">
                            <label class="form-label small text-white-50">Scheduled Date</label>
                            <input type="date" name="scheduled_date" class="form-control bg-dark text-white border-secondary">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-white-50">Scheduled Time</label>
                            <input type="time" name="scheduled_time" class="form-control bg-dark text-white border-secondary">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-white-50">Est. Duration</label>
                            <input type="text" name="estimated_duration" class="form-control bg-dark text-white border-secondary" placeholder="e.g. 2 hours">
                        </div>
                        <div class="col-12">
                            <label class="form-label small text-white-50">Description</label>
                            <textarea name="description" rows="3" class="form-control bg-dark text-white border-secondary" placeholder="Job details..."></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label small text-white-50">Notes</label>
                            <textarea name="notes" rows="2" class="form-control bg-dark text-white border-secondary" placeholder="Internal notes..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-vip btn-sm"><i class="bi bi-check-lg me-1"></i>Create Job</button>
                </div>
            </form>
        </div>
    </div>
</div>
{{-- Edit Job Modal --}}
<div class="modal fade" id="editJobModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="background:var(--vip-primary); color:#fff; border:1px solid rgba(255,255,255,.1);">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Job</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small text-white-50">Customer Name <span class="text-danger">*</span></label>
                        <input type="text" name="customer_name" class="form-control bg-dark text-white border-secondary" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small text-white-50">Customer Email</label>
                        <input type="email" name="customer_email" class="form-control bg-dark text-white border-secondary">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small text-white-50">Customer Phone</label>
                        <input type="text" name="customer_phone" class="form-control bg-dark text-white border-secondary">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small text-white-50">Priority</label>
                        <select name="priority" class="form-select bg-dark text-white border-secondary">
                            <option value="normal">Normal</option>
                            <option value="low">Low</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                    <div class="col-12"><hr class="border-secondary my-1"><label class="form-label small text-white-50 mt-1">Install Address</label></div>
                    <div class="col-md-12">
                        <input type="text" name="install_address" class="form-control bg-dark text-white border-secondary" placeholder="Street address">
                    </div>
                    <div class="col-md-5">
                        <input type="text" name="install_city" class="form-control bg-dark text-white border-secondary" placeholder="City">
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="install_state" class="form-control bg-dark text-white border-secondary" placeholder="State">
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="install_zip" class="form-control bg-dark text-white border-secondary" placeholder="Zip">
                    </div>
                    <div class="col-12"><hr class="border-secondary my-1"></div>
                    <div class="col-md-4">
                        <label class="form-label small text-white-50">Scheduled Date</label>
                        <input type="date" name="scheduled_date" class="form-control bg-dark text-white border-secondary">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small text-white-50">Scheduled Time</label>
                        <input type="time" name="scheduled_time" class="form-control bg-dark text-white border-secondary">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small text-white-50">Est. Duration</label>
                        <input type="text" name="estimated_duration" class="form-control bg-dark text-white border-secondary" placeholder="e.g. 2 hours">
                    </div>
                    <div class="col-12">
                        <label class="form-label small text-white-50">Description</label>
                        <textarea name="description" rows="3" class="form-control bg-dark text-white border-secondary"></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label small text-white-50">Notes</label>
                        <textarea name="notes" rows="2" class="form-control bg-dark text-white border-secondary"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-vip btn-sm" onclick="saveEditJob()"><i class="bi bi-check-lg me-1"></i>Save Changes</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.iq-card');
    const detailBody = document.getElementById('iqDetailBody');
    const detailTitle = document.getElementById('iqDetailTitle');
    const toolbarActions = document.getElementById('iqToolbarActions');
    const csrf = document.querySelector('meta[name=csrf-token]').content;
    let currentJobId = null;
    let currentJobData = null;

    // Tab filters
    document.querySelectorAll('.iq-rail-tabs .tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const status = this.dataset.status;
            const url = new URL(window.location);
            if (status !== 'all') url.searchParams.set('status', status);
            else url.searchParams.delete('status');
            window.location = url;
        });
    });

    // Search
    document.getElementById('iqSearch').addEventListener('input', function() {
        const term = this.value.toLowerCase();
        document.querySelectorAll('.iq-card').forEach(card => {
            card.style.display = (!term || card.dataset.search.includes(term)) ? '' : 'none';
        });
    });

    // Load detail
    cards.forEach(card => {
        card.addEventListener('click', function() {
            cards.forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            loadDetail(this.dataset.id);
        });
    });

    function loadDetail(id) {
        detailBody.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-secondary"></div></div>';

        fetch(`/installer/jobs/${id}`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }})
            .then(r => r.json())
            .then(data => {
                const j = data.job;
                const notes = data.notes || [];
                const items = data.items || [];
                detailTitle.textContent = j.job_number || ('JOB-' + j.id);

                // Toolbar actions
                currentJobId = j.id;
                currentJobData = j;
                let actions = '';
                if (j.status === 'pending' || j.status === 'scheduled') {
                    actions += `<form method="POST" action="/installer/jobs/${j.id}/status" class="d-inline"><input type="hidden" name="_token" value="${csrf}"><input type="hidden" name="status" value="in_progress"><button class="btn btn-sm btn-primary"><i class="bi bi-play-fill me-1"></i>Start</button></form> `;
                } else if (j.status === 'in_progress') {
                    actions += `<form method="POST" action="/installer/jobs/${j.id}/status" class="d-inline"><input type="hidden" name="_token" value="${csrf}"><input type="hidden" name="status" value="completed"><button class="btn btn-sm btn-success"><i class="bi bi-check-lg me-1"></i>Complete</button></form> `;
                }
                actions += `<button class="btn btn-sm btn-outline-primary" onclick="openEditJob()" title="Edit"><i class="bi bi-pencil"></i></button> `;
                actions += `<button class="btn btn-sm btn-outline-danger" onclick="deleteJob(${j.id}, '${j.job_number || 'JOB-' + j.id}')" title="Delete"><i class="bi bi-trash"></i></button>`;
                toolbarActions.innerHTML = actions;

                // Notes HTML
                let notesHtml = '';
                if (notes.length) {
                    notesHtml = notes.map(n => `<div class="note-card"><div class="d-flex justify-content-between"><strong class="small">${n.author || 'System'}</strong><span class="text-muted small">${n.created_at || ''}</span></div><p class="mb-0 small mt-1">${n.note}</p></div>`).join('');
                } else {
                    notesHtml = '<p class="text-muted small">No notes yet.</p>';
                }

                detailBody.innerHTML = `
                    <div class="iq-info-grid">
                        <div class="iq-info-card"><div class="label">Customer</div><div class="value">${j.customer_name || '—'}</div></div>
                        <div class="iq-info-card"><div class="label">Phone</div><div class="value">${j.customer_phone || '—'}</div></div>
                        <div class="iq-info-card"><div class="label">Status</div><div class="value"><span class="badge badge-${j.status}">${j.status ? j.status.replace('_',' ').replace(/^./,c=>c.toUpperCase()) : '—'}</span></div></div>
                        <div class="iq-info-card"><div class="label">Scheduled</div><div class="value">${j.scheduled_date || 'Not set'}${j.scheduled_time ? ' @ ' + j.scheduled_time : ''}</div></div>
                        <div class="iq-info-card"><div class="label">Priority</div><div class="value">${j.priority ? j.priority.charAt(0).toUpperCase() + j.priority.slice(1) : 'Normal'}</div></div>
                        <div class="iq-info-card"><div class="label">Email</div><div class="value">${j.customer_email || '—'}</div></div>
                    </div>

                    ${j.install_address ? `
                    <div class="card mb-3" style="border:none; box-shadow:0 1px 4px rgba(0,0,0,.06);">
                        <div class="card-body py-2 px-3">
                            <div class="small text-muted text-uppercase mb-1" style="letter-spacing:.5px;">Install Address</div>
                            <div>${j.install_address}<br>${j.install_city || ''}, ${j.install_state || ''} ${j.install_zip || ''}</div>
                        </div>
                    </div>` : ''}

                    ${j.description ? `<div class="card mb-3" style="border:none; box-shadow:0 1px 4px rgba(0,0,0,.06);"><div class="card-body py-2 px-3"><div class="small text-muted text-uppercase mb-1" style="letter-spacing:.5px;">Description</div><p class="mb-0">${j.description}</p></div></div>` : ''}

                    <h6 class="mb-2 mt-3" style="font-size:.8rem; text-transform:uppercase; letter-spacing:.5px; color:rgba(0,0,0,.5);"><i class="bi bi-list-check me-1"></i>Installation Items</h6>
                    ${items.length ? `<table class="job-items-tbl mb-2">
                        <thead><tr><th style="width:30px;"></th><th>Item</th><th>Type</th><th class="text-center">Qty</th><th style="width:40px;"></th></tr></thead>
                        <tbody>${items.map(i => `<tr class="${i.completed ? 'item-done' : ''}">
                            <td class="text-center"><input type="checkbox" class="item-check" ${i.completed ? 'checked' : ''} onchange="toggleJobItem(${j.id}, ${i.id})"></td>
                            <td>${i.description}${i.notes ? '<br><small class="text-muted">' + i.notes + '</small>' : ''}</td>
                            <td><span class="badge bg-light text-dark" style="font-size:.65rem;">${i.item_type || 'other'}</span></td>
                            <td class="text-center">${parseFloat(i.qty)}</td>
                            <td class="text-center"><button class="btn btn-sm text-danger p-0" onclick="removeJobItem(${j.id}, ${i.id})" title="Remove"><i class="bi bi-x-lg" style="font-size:.7rem;"></i></button></td>
                        </tr>`).join('')}</tbody>
                    </table>` : '<p class="text-muted small">No items added yet.</p>'}

                    <div class="card mb-3" style="border:none; box-shadow:0 1px 4px rgba(0,0,0,.06);">
                        <div class="card-body py-2 px-3">
                            <div class="row g-2 align-items-end">
                                <div class="col-md-4"><input type="text" id="addJobItemDesc" class="form-control form-control-sm" placeholder="Description (e.g. Double Hung 36x48)"></div>
                                <div class="col-md-2">
                                    <select id="addJobItemType" class="form-select form-select-sm">
                                        <option value="window">Window</option>
                                        <option value="door">Door</option>
                                        <option value="service">Service</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-2"><input type="number" id="addJobItemQty" class="form-control form-control-sm" placeholder="Qty" value="1" min="1" step="1"></div>
                                <div class="col-md-3"><input type="text" id="addJobItemNotes" class="form-control form-control-sm" placeholder="Notes (optional)"></div>
                                <div class="col-md-1"><button class="btn btn-sm btn-vip w-100" onclick="addJobItem(${j.id})"><i class="bi bi-plus"></i></button></div>
                            </div>
                        </div>
                    </div>

                    <h6 class="mb-2 mt-4" style="font-size:.8rem; text-transform:uppercase; letter-spacing:.5px; color:rgba(0,0,0,.5);"><i class="bi bi-chat-left-text me-1"></i>Notes</h6>
                    ${notesHtml}

                    <form method="POST" action="/installer/jobs/${j.id}/note" class="mt-3">
                        <input type="hidden" name="_token" value="${csrf}">
                        <div class="input-group">
                            <input type="text" name="note" class="form-control" placeholder="Add a note..." required>
                            <button class="btn btn-vip" type="submit"><i class="bi bi-plus"></i></button>
                        </div>
                    </form>
                `;
            })
            .catch(() => {
                detailBody.innerHTML = '<div class="alert alert-danger m-4">Failed to load job details.</div>';
            });
    }

    // Job items management
    window.addJobItem = function(jobId) {
        const desc = document.getElementById('addJobItemDesc')?.value?.trim();
        const itemType = document.getElementById('addJobItemType')?.value;
        const qty = parseFloat(document.getElementById('addJobItemQty')?.value || 1);
        const notes = document.getElementById('addJobItemNotes')?.value?.trim();

        if (!desc) { alert('Please enter an item description.'); return; }

        fetch(`/installer/jobs/${jobId}/item`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify({ description: desc, item_type: itemType, qty: qty, notes: notes || null })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) loadDetail(jobId);
            else alert(data.error || 'Failed to add item.');
        })
        .catch(() => alert('Failed to add item.'));
    };

    window.removeJobItem = function(jobId, itemId) {
        if (!confirm('Remove this item?')) return;
        fetch(`/installer/jobs/${jobId}/item/${itemId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => { if (data.success) loadDetail(jobId); })
        .catch(() => alert('Failed to remove item.'));
    };

    window.toggleJobItem = function(jobId, itemId) {
        fetch(`/installer/jobs/${jobId}/item/${itemId}/toggle`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => { if (data.success) loadDetail(jobId); })
        .catch(() => alert('Failed to toggle item.'));
    };

    // Edit job
    window.openEditJob = function() {
        if (!currentJobData) return;
        const j = currentJobData;
        const modal = document.getElementById('editJobModal');
        modal.querySelector('[name="customer_name"]').value = j.customer_name || '';
        modal.querySelector('[name="customer_email"]').value = j.customer_email || '';
        modal.querySelector('[name="customer_phone"]').value = j.customer_phone || '';
        modal.querySelector('[name="install_address"]').value = j.install_address || '';
        modal.querySelector('[name="install_city"]').value = j.install_city || '';
        modal.querySelector('[name="install_state"]').value = j.install_state || '';
        modal.querySelector('[name="install_zip"]').value = j.install_zip || '';
        modal.querySelector('[name="priority"]').value = j.priority || 'normal';
        modal.querySelector('[name="scheduled_date"]').value = j.scheduled_date ? j.scheduled_date.substring(0,10) : '';
        modal.querySelector('[name="scheduled_time"]').value = j.scheduled_time || '';
        modal.querySelector('[name="estimated_duration"]').value = j.estimated_duration || '';
        modal.querySelector('[name="description"]').value = j.description || '';
        modal.querySelector('[name="notes"]').value = j.notes || '';
        new bootstrap.Modal(modal).show();
    };

    window.saveEditJob = function() {
        if (!currentJobId) return;
        const modal = document.getElementById('editJobModal');
        const formData = {};
        modal.querySelectorAll('input[name], select[name], textarea[name]').forEach(el => {
            formData[el.name] = el.value;
        });

        fetch(`/installer/jobs/${currentJobId}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify(formData)
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(modal).hide();
                loadDetail(currentJobId);
                // Update left rail card text
                const card = document.querySelector(`.iq-card[data-id="${currentJobId}"]`);
                if (card) {
                    card.querySelector('.q-customer').innerHTML = '<i class="bi bi-person me-1"></i>' + (formData.customer_name || 'No customer');
                }
            } else {
                alert('Failed to update job.');
            }
        })
        .catch(() => alert('Failed to update job.'));
    };

    // Delete job
    window.deleteJob = function(jobId, jobNumber) {
        if (!confirm(`Delete job ${jobNumber}? This cannot be undone.`)) return;
        fetch(`/installer/jobs/${jobId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const card = document.querySelector(`.iq-card[data-id="${jobId}"]`);
                if (card) card.remove();
                detailBody.innerHTML = '<div class="iq-empty-state"><i class="bi bi-tools"></i><p>Job deleted. Select another job.</p></div>';
                toolbarActions.innerHTML = '';
                detailTitle.textContent = 'Job Details';
            } else {
                alert('Failed to delete job.');
            }
        })
        .catch(() => alert('Failed to delete job.'));
    };

    // Auto-select first
    if (cards.length > 0) cards[0].click();
});
</script>
@endpush
