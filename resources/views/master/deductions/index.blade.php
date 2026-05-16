@extends('layouts.app')

@section('title', 'Deduction Manager')

@section('content')
<div class="p-4">
    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('admin.master.hub') }}" class="text-decoration-none" style="color: var(--vip-accent);">Master Data</a></li>
            <li class="breadcrumb-item"><a href="#" class="text-decoration-none" style="color: var(--vip-accent);">Profiles &amp; Deductions</a></li>
            <li class="breadcrumb-item active">Deduction Manager</li>
        </ol>
    </nav>

    <div class="d-flex align-items-center justify-content-between mb-4">
        <h4 class="fw-bold mb-0" style="color: var(--vip-primary);">
            <i class="bi bi-scissors me-2" style="color: var(--vip-accent);"></i>Deduction Manager
        </h4>
    </div>

    <p class="text-muted mb-4">Manage deduction rules per configuration type. Select a configuration to view and edit its deduction manipulations.</p>

    <div class="row g-4">
        {{-- Left Panel: Configuration List --}}
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header py-3" style="background: var(--vip-primary); color: #fff;">
                    <span class="fw-semibold"><i class="bi bi-sliders me-2"></i>Configurations</span>
                </div>
                <div class="card-body p-3">
                    <div class="row g-2 mb-3">
                        <div class="col-7">
                            <input type="text" id="configSearch" class="form-control form-control-sm" placeholder="Search configurations...">
                        </div>
                        <div class="col-5">
                            <select id="profileSetFilter" class="form-select form-select-sm">
                                <option value="">All Profile Sets</option>
                                @foreach($profileSets as $ps)
                                    <option value="{{ $ps->id }}">{{ $ps->code }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div id="configList" style="max-height: 60vh; overflow-y: auto;">
                        <div class="text-center text-muted py-4">
                            <div class="spinner-border spinner-border-sm"></div>
                            <div class="small mt-1">Loading...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Panel: Manipulations --}}
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header py-3 d-flex align-items-center justify-content-between" style="background: var(--vip-primary); color: #fff;">
                    <span class="fw-semibold"><i class="bi bi-list-ol me-2"></i>Deduction Rules</span>
                    <button class="btn btn-sm btn-outline-light" id="addManipBtn" style="display:none;" onclick="openDeductionModal()">
                        <i class="bi bi-plus-lg me-1"></i> Add Rule
                    </button>
                </div>
                <div class="card-body" id="manipPanel">
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-arrow-left-circle fs-1 d-block mb-2 opacity-25"></i>
                        Select a configuration from the list to manage its deduction rules.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const configsUrl     = '{{ route("admin.master.deductions.configurations") }}';
    const manipBaseUrl   = '{{ url("admin/master/deductions/manipulations") }}';
    const saveManipUrl   = '{{ route("admin.master.deductions.manipulations.save") }}';
    const bulkUpdateUrl  = '{{ route("admin.master.deductions.bulk-update") }}';
    const csrfToken      = '{{ csrf_token() }}';

    let currentConfigId  = null;
    let currentProfileSetId = null;
    let debounceTimer    = null;

    function loadConfigurations() {
        const search = document.getElementById('configSearch').value;
        const profileSetId = document.getElementById('profileSetFilter').value;
        const params = new URLSearchParams();
        if (search) params.set('search', search);
        if (profileSetId) params.set('profile_set_id', profileSetId);

        fetch(configsUrl + '?' + params.toString())
            .then(r => r.json())
            .then(configs => {
                const list = document.getElementById('configList');
                if (!configs.length) {
                    list.innerHTML = '<div class="text-center text-muted py-4 small">No configurations found.</div>';
                    return;
                }
                list.innerHTML = configs.map(c => `
                    <a href="#" class="list-group-item list-group-item-action py-2 config-item ${c.id == currentConfigId ? 'active' : ''}"
                       data-id="${c.id}" data-profile-set-id="${c.profile_set_id}"
                       onclick="selectConfig(${c.id}, ${c.profile_set_id}); return false;">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-semibold small">${c.series_type}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">${c.profile_set_code} - ${c.profile_set_name || ''}</div>
                            </div>
                            <span class="badge bg-secondary">${c.panel_count || 1}P</span>
                        </div>
                    </a>
                `).join('');
            });
    }

    function selectConfig(configId, profileSetId) {
        currentConfigId = configId;
        currentProfileSetId = profileSetId;
        document.querySelectorAll('.config-item').forEach(el => {
            el.classList.toggle('active', el.dataset.id == configId);
        });
        document.getElementById('addManipBtn').style.display = '';
        loadDeductionRules(configId);
    }

    function loadDeductionRules(configId) {
        fetch(manipBaseUrl + '/' + configId)
            .then(r => r.json())
            .then(manips => {
                const panel = document.getElementById('manipPanel');
                if (!manips.length) {
                    panel.innerHTML = '<div class="text-center text-muted py-4 small">No deduction rules defined for this configuration.</div>';
                    return;
                }
                panel.innerHTML = `
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;">Seq</th>
                                    <th>Field</th>
                                    <th>Component</th>
                                    <th>Position</th>
                                    <th>Frame Type</th>
                                    <th class="text-end">Diff 1</th>
                                    <th class="text-end">Diff 2</th>
                                    <th class="text-end">Diff</th>
                                    <th class="text-end">Gaps</th>
                                    <th class="text-center">Active</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${manips.map(m => `
                                    <tr>
                                        <td class="small">${m.seq}</td>
                                        <td class="small">${m.field_label || m.field_number || '-'}</td>
                                        <td class="small">${m.component_type_label || m.component_type || '-'}</td>
                                        <td class="small">${m.position || '-'}</td>
                                        <td class="small">${m.frame_type || '-'}</td>
                                        <td class="small text-end">${m.diff_size_1 ?? '-'}</td>
                                        <td class="small text-end">${m.diff_size_2 ?? '-'}</td>
                                        <td class="small text-end">${m.diff_size ?? '-'}</td>
                                        <td class="small text-end">${m.gaps ?? '-'}</td>
                                        <td class="small text-center">${m.is_active ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-x-circle text-secondary"></i>'}</td>
                                        <td class="small text-end">
                                            <button class="btn btn-sm btn-outline-dark" onclick='openDeductionModal(${JSON.stringify(m)})'><i class="bi bi-pencil"></i></button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteDeduction(${m.id})"><i class="bi bi-trash"></i></button>
                                        </td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                `;
            });
    }

    function openDeductionModal(data = null) {
        const isEdit = !!data;
        const d = data || {};
        const html = `
            <div class="modal fade" id="deductionModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header" style="background: var(--vip-primary); color: #fff;">
                            <h5 class="modal-title"><i class="bi bi-${isEdit ? 'pencil' : 'plus-lg'} me-2"></i>${isEdit ? 'Edit' : 'Add'} Deduction Rule</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="deductionForm">
                                ${isEdit ? `<input type="hidden" name="id" value="${d.id}">` : ''}
                                <input type="hidden" name="profile_set_id" value="${d.profile_set_id || currentProfileSetId}">
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
                            <button type="button" class="btn btn-vip" onclick="saveDeduction()"><i class="bi bi-check-lg me-1"></i> Save</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.getElementById('deductionModal')?.remove();
        document.body.insertAdjacentHTML('beforeend', html);
        new bootstrap.Modal(document.getElementById('deductionModal')).show();
    }

    function saveDeduction() {
        const form = document.getElementById('deductionForm');
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
                bootstrap.Modal.getInstance(document.getElementById('deductionModal')).hide();
                loadDeductionRules(currentConfigId);
                showToast('Deduction rule saved.');
            }
        });
    }

    function deleteDeduction(id) {
        if (!confirm('Delete this deduction rule?')) return;
        fetch(manipBaseUrl + '/delete/' + id, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken }
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                loadDeductionRules(currentConfigId);
                showToast('Deduction rule deleted.');
            }
        });
    }

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

    document.getElementById('configSearch').addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(loadConfigurations, 300);
    });
    document.getElementById('profileSetFilter').addEventListener('change', loadConfigurations);

    loadConfigurations();
</script>
@endpush

@push('styles')
<style>
    .config-item.active {
        background-color: var(--vip-primary) !important;
        color: #fff !important;
        border-color: var(--vip-primary) !important;
    }
    .config-item.active .text-muted {
        color: rgba(255,255,255,0.7) !important;
    }
    .config-item:hover:not(.active) {
        background-color: rgba(201, 168, 76, 0.08);
    }
</style>
@endpush
@endsection
