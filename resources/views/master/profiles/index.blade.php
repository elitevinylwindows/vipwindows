@extends('layouts.app')

@section('title', 'Profile Manager')

@section('content')
<div class="p-4">
    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('admin.master.hub') }}" class="text-decoration-none" style="color: var(--vip-accent);">Master Data</a></li>
            <li class="breadcrumb-item"><a href="#" class="text-decoration-none" style="color: var(--vip-accent);">Profiles &amp; Deductions</a></li>
            <li class="breadcrumb-item active">Profile Manager</li>
        </ol>
    </nav>

    <div class="d-flex align-items-center justify-content-between mb-4">
        <h4 class="fw-bold mb-0" style="color: var(--vip-primary);">
            <i class="bi bi-diagram-3 me-2" style="color: var(--vip-accent);"></i>Profile Manager
        </h4>
    </div>

    <div class="row g-4" style="min-height: 70vh;">
        {{-- Left Panel: Profile List --}}
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header py-3" style="background: var(--vip-primary); color: #fff;">
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="fw-semibold"><i class="bi bi-list-ul me-2"></i>Profile Sets</span>
                    </div>
                </div>
                <div class="card-body p-3">
                    {{-- Search / Filter --}}
                    <div class="mb-3">
                        <input type="text" id="profileSearch" class="form-control form-control-sm" placeholder="Search profiles...">
                    </div>
                    <div class="mb-3">
                        <select id="seriesFilter" class="form-select form-select-sm">
                            <option value="">All Series Types</option>
                            @foreach($series as $s)
                                <option value="{{ $s->series }}">{{ $s->series }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Profile List --}}
                    <div id="profileList" class="list-group list-group-flush" style="max-height: 55vh; overflow-y: auto;">
                        <div class="text-center text-muted py-4">
                            <div class="spinner-border spinner-border-sm" role="status"></div>
                            <div class="small mt-1">Loading profiles...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Panel: Profile Detail --}}
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header py-3" style="background: var(--vip-primary); color: #fff;">
                    <span class="fw-semibold"><i class="bi bi-pencil-square me-2"></i>Profile Detail</span>
                </div>
                <div class="card-body" id="profileDetail">
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-arrow-left-circle fs-1 d-block mb-2 opacity-25"></i>
                        Select a profile set from the list to view and edit its details.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const profilesUrl   = '{{ route("admin.master.profiles.list") }}';
    const profileUrl     = '{{ url("admin/master/profiles") }}';
    const manipUrl       = '{{ url("admin/master/profiles/manipulations") }}';
    const saveManipUrl   = '{{ route("admin.master.profiles.manipulations.save") }}';
    const csrfToken      = '{{ csrf_token() }}';

    let currentProfileId = null;
    let debounceTimer    = null;

    // Load profile list
    function loadProfiles() {
        const search = document.getElementById('profileSearch').value;
        const seriesType = document.getElementById('seriesFilter').value;
        const params = new URLSearchParams();
        if (search) params.set('search', search);
        if (seriesType) params.set('series_type', seriesType);

        fetch(profilesUrl + '?' + params.toString())
            .then(r => r.json())
            .then(profiles => {
                const list = document.getElementById('profileList');
                if (!profiles.length) {
                    list.innerHTML = '<div class="text-center text-muted py-4"><i class="bi bi-inbox fs-3 d-block mb-2 opacity-25"></i>No profiles found.</div>';
                    return;
                }
                list.innerHTML = profiles.map(p => `
                    <a href="#" class="list-group-item list-group-item-action py-2 profile-item ${p.id == currentProfileId ? 'active' : ''}"
                       data-id="${p.id}" onclick="loadProfile(${p.id}); return false;">
                        <div class="fw-semibold small">${p.code}</div>
                        <div class="text-muted" style="font-size: 0.75rem;">${p.name || p.manufacturer_system || ''}</div>
                    </a>
                `).join('');
            });
    }

    // Load single profile detail
    function loadProfile(id) {
        currentProfileId = id;
        // Highlight active item
        document.querySelectorAll('.profile-item').forEach(el => {
            el.classList.toggle('active', el.dataset.id == id);
        });

        fetch(profileUrl + '/' + id)
            .then(r => r.json())
            .then(profile => {
                renderProfileDetail(profile);
            });
    }

    function renderProfileDetail(p) {
        const detail = document.getElementById('profileDetail');
        detail.innerHTML = `
            <ul class="nav nav-tabs mb-3" role="tablist">
                <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-settings">Settings</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-components">Components (${p.components ? p.components.length : 0})</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-manipulations" id="manipTab">Manipulations</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-series-types">Series Types (${p.series_types ? p.series_types.length : 0})</a></li>
            </ul>

            <div class="tab-content">
                {{-- Settings Tab --}}
                <div class="tab-pane fade show active" id="tab-settings">
                    <form id="profileForm" onsubmit="saveProfile(event)">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Code</label>
                                <input type="text" class="form-control form-control-sm" value="${p.code || ''}" disabled>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Name</label>
                                <input type="text" name="name" class="form-control form-control-sm" value="${p.name || ''}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Manufacturer System</label>
                                <input type="text" name="manufacturer_system" class="form-control form-control-sm" value="${p.manufacturer_system || ''}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">Frame Pocket</label>
                                <input type="number" step="0.001" name="frame_pocket" class="form-control form-control-sm" value="${p.frame_pocket ?? ''}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">Interlock Overlap</label>
                                <input type="number" step="0.001" name="interlock_overlap" class="form-control form-control-sm" value="${p.interlock_overlap ?? ''}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">Miter Angle</label>
                                <input type="number" step="0.01" name="miter_angle" class="form-control form-control-sm" value="${p.miter_angle ?? ''}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">Product Type</label>
                                <input type="text" name="product_type" class="form-control form-control-sm" value="${p.product_type || ''}">
                            </div>

                            <div class="col-12"><hr class="my-1"><h6 class="fw-bold small text-muted">Frame Deductions</h6></div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">Frame H Deduction</label>
                                <input type="number" step="0.001" name="frame_horizontal_deduction" class="form-control form-control-sm" value="${p.frame_horizontal_deduction ?? ''}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">Frame V Deduction</label>
                                <input type="number" step="0.001" name="frame_vertical_deduction" class="form-control form-control-sm" value="${p.frame_vertical_deduction ?? ''}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">Frame Cut Type</label>
                                <input type="text" name="frame_cut_type" class="form-control form-control-sm" value="${p.frame_cut_type || ''}">
                            </div>

                            <div class="col-12"><hr class="my-1"><h6 class="fw-bold small text-muted">Sash Deductions</h6></div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">Sash H Deduction</label>
                                <input type="number" step="0.001" name="sash_horizontal_deduction" class="form-control form-control-sm" value="${p.sash_horizontal_deduction ?? ''}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">Sash V Deduction</label>
                                <input type="number" step="0.001" name="sash_vertical_deduction" class="form-control form-control-sm" value="${p.sash_vertical_deduction ?? ''}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">Sash Cut Type</label>
                                <input type="text" name="sash_cut_type" class="form-control form-control-sm" value="${p.sash_cut_type || ''}">
                            </div>

                            <div class="col-12"><hr class="my-1"><h6 class="fw-bold small text-muted">Other Deductions</h6></div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">Interlock Deduction</label>
                                <input type="number" step="0.001" name="interlock_deduction" class="form-control form-control-sm" value="${p.interlock_deduction ?? ''}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">Meeting Rail Ded.</label>
                                <input type="number" step="0.001" name="meeting_rail_deduction" class="form-control form-control-sm" value="${p.meeting_rail_deduction ?? ''}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">Active</label>
                                <select name="is_active" class="form-select form-select-sm">
                                    <option value="1" ${p.is_active ? 'selected' : ''}>Yes</option>
                                    <option value="0" ${!p.is_active ? 'selected' : ''}>No</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold small">Notes</label>
                                <textarea name="notes" class="form-control form-control-sm" rows="2">${p.notes || ''}</textarea>
                            </div>

                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-vip btn-sm"><i class="bi bi-check-lg me-1"></i> Save Changes</button>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- Components Tab --}}
                <div class="tab-pane fade" id="tab-components">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Code</th><th>Type</th><th>Orient.</th><th>Description</th>
                                    <th>Qty</th><th>Dim Source</th><th>Deduction</th><th>Addition</th>
                                    <th>Formula</th><th>Cut</th><th>Sort</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${(p.components || []).map(c => `
                                    <tr>
                                        <td class="fw-semibold small">${c.profile_code || ''}</td>
                                        <td class="small">${c.type || ''}</td>
                                        <td class="small">${c.orientation || ''}</td>
                                        <td class="small">${c.description || ''}</td>
                                        <td class="small text-center">${c.quantity ?? ''}</td>
                                        <td class="small">${c.dimension_source || ''}</td>
                                        <td class="small text-end">${c.deduction_value ?? ''}</td>
                                        <td class="small text-end">${c.addition_value ?? ''}</td>
                                        <td class="small">${c.formula || ''}</td>
                                        <td class="small">${c.cut_type || ''}</td>
                                        <td class="small text-center">${c.sort_order ?? ''}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                    ${!(p.components || []).length ? '<div class="text-center text-muted py-4 small">No components defined.</div>' : ''}
                </div>

                {{-- Manipulations Tab --}}
                <div class="tab-pane fade" id="tab-manipulations">
                    <div class="d-flex justify-content-end mb-2">
                        <button class="btn btn-vip btn-sm" onclick="openManipModal()"><i class="bi bi-plus-lg me-1"></i> Add Manipulation</button>
                    </div>
                    <div id="manipList">
                        <div class="text-center text-muted py-3"><div class="spinner-border spinner-border-sm"></div></div>
                    </div>
                </div>

                {{-- Series Types Tab --}}
                <div class="tab-pane fade" id="tab-series-types">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Series Type</th><th>Panels</th><th>Field Width</th>
                                    <th>Fix Glass H</th><th>Fix Glass V</th>
                                    <th>Sash Glass H</th><th>Sash Glass V</th>
                                    <th>Screen H</th><th>Screen V</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${(p.series_types || []).map(st => `
                                    <tr>
                                        <td class="fw-semibold small">${st.series_type || ''}</td>
                                        <td class="small text-center">${st.panel_count ?? ''}</td>
                                        <td class="small">${st.field_width_formula || ''}</td>
                                        <td class="small text-end">${st.fix_glass_h_deduction ?? ''}</td>
                                        <td class="small text-end">${st.fix_glass_v_deduction ?? ''}</td>
                                        <td class="small text-end">${st.sash_glass_h_deduction ?? ''}</td>
                                        <td class="small text-end">${st.sash_glass_v_deduction ?? ''}</td>
                                        <td class="small text-end">${st.screen_h_deduction ?? ''}</td>
                                        <td class="small text-end">${st.screen_v_deduction ?? ''}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                    ${!(p.series_types || []).length ? '<div class="text-center text-muted py-4 small">No series type mappings.</div>' : ''}
                </div>
            </div>
        `;

        // Load manipulations when tab is shown
        document.getElementById('manipTab').addEventListener('shown.bs.tab', function() {
            loadManipulations(p.id);
        });
    }

    // Save profile settings
    function saveProfile(e) {
        e.preventDefault();
        const form = document.getElementById('profileForm');
        const data = new FormData(form);
        const body = {};
        data.forEach((v, k) => body[k] = v);

        fetch(profileUrl + '/' + currentProfileId, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify(body)
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) showToast('Profile saved successfully.');
        });
    }

    // Load manipulations
    function loadManipulations(profileSetId) {
        fetch(manipUrl + '/' + profileSetId)
            .then(r => r.json())
            .then(manips => {
                const container = document.getElementById('manipList');
                if (!manips.length) {
                    container.innerHTML = '<div class="text-center text-muted py-4 small">No manipulations defined.</div>';
                    return;
                }
                container.innerHTML = `
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Seq</th><th>Field</th><th>Component</th><th>Position</th>
                                    <th>Frame Type</th><th>Diff 1</th><th>Diff 2</th><th>Diff</th>
                                    <th>Gaps</th><th>Active</th><th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${manips.map(m => `
                                    <tr>
                                        <td class="small">${m.seq}</td>
                                        <td class="small">${m.field_label || m.field_number || ''}</td>
                                        <td class="small">${m.component_type_label || m.component_type || ''}</td>
                                        <td class="small">${m.position || ''}</td>
                                        <td class="small">${m.frame_type || ''}</td>
                                        <td class="small text-end">${m.diff_size_1 ?? ''}</td>
                                        <td class="small text-end">${m.diff_size_2 ?? ''}</td>
                                        <td class="small text-end">${m.diff_size ?? ''}</td>
                                        <td class="small text-end">${m.gaps ?? ''}</td>
                                        <td class="small">${m.is_active ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>'}</td>
                                        <td class="small text-end">
                                            <button class="btn btn-sm btn-outline-dark" onclick='openManipModal(${JSON.stringify(m)})'><i class="bi bi-pencil"></i></button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteManip(${m.id})"><i class="bi bi-trash"></i></button>
                                        </td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                `;
            });
    }

    // Open manipulation modal
    function openManipModal(data = null) {
        const isEdit = !!data;
        const d = data || {};
        const html = `
            <div class="modal fade" id="manipModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header" style="background: var(--vip-primary); color: #fff;">
                            <h5 class="modal-title"><i class="bi bi-${isEdit ? 'pencil' : 'plus-lg'} me-2"></i>${isEdit ? 'Edit' : 'Add'} Manipulation</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="manipForm">
                                ${isEdit ? `<input type="hidden" name="id" value="${d.id}">` : ''}
                                <input type="hidden" name="profile_set_id" value="${currentProfileId}">
                                <div class="row g-3">
                                    <div class="col-md-2"><label class="form-label fw-semibold small">Seq</label><input type="number" name="seq" class="form-control form-control-sm" value="${d.seq ?? 0}" required></div>
                                    <div class="col-md-2"><label class="form-label fw-semibold small">Field #</label><input type="number" name="field_number" class="form-control form-control-sm" value="${d.field_number ?? ''}"></div>
                                    <div class="col-md-4"><label class="form-label fw-semibold small">Field Label</label><input type="text" name="field_label" class="form-control form-control-sm" value="${d.field_label || ''}"></div>
                                    <div class="col-md-4"><label class="form-label fw-semibold small">Component Type</label><input type="text" name="component_type" class="form-control form-control-sm" value="${d.component_type || ''}"></div>
                                    <div class="col-md-4"><label class="form-label fw-semibold small">Component Label</label><input type="text" name="component_type_label" class="form-control form-control-sm" value="${d.component_type_label || ''}"></div>
                                    <div class="col-md-4"><label class="form-label fw-semibold small">Position</label><input type="text" name="position" class="form-control form-control-sm" value="${d.position || ''}"></div>
                                    <div class="col-md-2"><label class="form-label fw-semibold small">H Multi</label><input type="number" name="h_multiplier" class="form-control form-control-sm" value="${d.h_multiplier ?? ''}"></div>
                                    <div class="col-md-2"><label class="form-label fw-semibold small">V Multi</label><input type="number" name="v_multiplier" class="form-control form-control-sm" value="${d.v_multiplier ?? ''}"></div>
                                    <div class="col-md-3"><label class="form-label fw-semibold small">Frame Type</label><input type="text" name="frame_type" class="form-control form-control-sm" value="${d.frame_type || ''}"></div>
                                    <div class="col-md-3"><label class="form-label fw-semibold small">Article Code</label><input type="text" name="article_code" class="form-control form-control-sm" value="${d.article_code || ''}"></div>
                                    <div class="col-md-3"><label class="form-label fw-semibold small">Mullion Orient.</label><input type="text" name="mullion_orientation" class="form-control form-control-sm" value="${d.mullion_orientation || ''}"></div>
                                    <div class="col-md-3"><label class="form-label fw-semibold small">Product Type</label><input type="text" name="product_type_code" class="form-control form-control-sm" value="${d.product_type_code || ''}"></div>
                                    <div class="col-md-3"><label class="form-label fw-semibold small">Diff Size 1</label><input type="number" step="0.001" name="diff_size_1" class="form-control form-control-sm" value="${d.diff_size_1 ?? ''}"></div>
                                    <div class="col-md-3"><label class="form-label fw-semibold small">Diff Size 2</label><input type="number" step="0.001" name="diff_size_2" class="form-control form-control-sm" value="${d.diff_size_2 ?? ''}"></div>
                                    <div class="col-md-3"><label class="form-label fw-semibold small">Diff Size</label><input type="number" step="0.001" name="diff_size" class="form-control form-control-sm" value="${d.diff_size ?? ''}"></div>
                                    <div class="col-md-3"><label class="form-label fw-semibold small">Gaps</label><input type="number" step="0.001" name="gaps" class="form-control form-control-sm" value="${d.gaps ?? ''}"></div>
                                    <div class="col-md-6"><label class="form-label fw-semibold small">Additional Condition</label><input type="text" name="additional_condition" class="form-control form-control-sm" value="${d.additional_condition || ''}"></div>
                                    <div class="col-md-3"><label class="form-label fw-semibold small">Product Variable</label><input type="text" name="product_variable" class="form-control form-control-sm" value="${d.product_variable || ''}"></div>
                                    <div class="col-md-3"><label class="form-label fw-semibold small">Active</label><select name="is_active" class="form-select form-select-sm"><option value="1" ${d.is_active !== false ? 'selected' : ''}>Yes</option><option value="0" ${d.is_active === false ? 'selected' : ''}>No</option></select></div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-vip" onclick="saveManip()"><i class="bi bi-check-lg me-1"></i> Save</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        // Remove existing
        document.getElementById('manipModal')?.remove();
        document.body.insertAdjacentHTML('beforeend', html);
        new bootstrap.Modal(document.getElementById('manipModal')).show();
    }

    // Save manipulation
    function saveManip() {
        const form = document.getElementById('manipForm');
        const data = new FormData(form);
        const body = {};
        data.forEach((v, k) => body[k] = v);

        fetch(saveManipUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify(body)
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                bootstrap.Modal.getInstance(document.getElementById('manipModal')).hide();
                loadManipulations(currentProfileId);
                showToast('Manipulation saved.');
            }
        });
    }

    // Delete manipulation
    function deleteManip(id) {
        if (!confirm('Delete this manipulation?')) return;
        fetch(manipUrl + '/delete/' + id, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken }
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                loadManipulations(currentProfileId);
                showToast('Manipulation deleted.');
            }
        });
    }

    // Toast helper
    function showToast(msg) {
        const existing = document.getElementById('vipToast');
        if (existing) existing.remove();
        document.body.insertAdjacentHTML('beforeend', `
            <div id="vipToast" class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
                <div class="toast show align-items-center text-white border-0" style="background: var(--vip-primary);">
                    <div class="d-flex">
                        <div class="toast-body"><i class="bi bi-check-circle me-1"></i> ${msg}</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" onclick="this.closest('#vipToast').remove()"></button>
                    </div>
                </div>
            </div>
        `);
        setTimeout(() => document.getElementById('vipToast')?.remove(), 3000);
    }

    // Debounced search
    document.getElementById('profileSearch').addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(loadProfiles, 300);
    });
    document.getElementById('seriesFilter').addEventListener('change', loadProfiles);

    // Initial load
    loadProfiles();
</script>
@endpush

@push('styles')
<style>
    .profile-item.active {
        background-color: var(--vip-primary) !important;
        color: #fff !important;
        border-color: var(--vip-primary) !important;
    }
    .profile-item.active .text-muted {
        color: rgba(255,255,255,0.7) !important;
    }
    .profile-item:hover:not(.active) {
        background-color: rgba(201, 168, 76, 0.08);
    }
</style>
@endpush
@endsection
