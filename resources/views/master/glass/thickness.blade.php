@extends('layouts.app')

@section('title', 'Glass Thickness')

@section('content')
<div class="p-4">
    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('admin.master.hub') }}" class="text-decoration-none" style="color: var(--vip-accent);">Master Data</a></li>
            <li class="breadcrumb-item"><span class="text-muted">Glass</span></li>
            <li class="breadcrumb-item active">Thickness</li>
        </ol>
    </nav>

    <div class="d-flex align-items-center justify-content-between mb-4">
        <h4 class="fw-bold mb-0" style="color: var(--vip-primary);">
            <i class="bi bi-rulers me-2" style="color: var(--vip-accent);"></i>Glass Thickness
        </h4>
    </div>

    {{-- Card 1: Thickness Options --}}
    <div class="card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between" style="background: var(--vip-primary); color: #fff;">
            <h6 class="mb-0"><i class="bi bi-rulers me-2"></i>Thickness Options</h6>
            <button class="btn btn-sm btn-outline-light" data-bs-toggle="modal" data-bs-target="#thicknessModal" onclick="openThkModal()">
                <i class="bi bi-plus-lg me-1"></i> Add Thickness
            </button>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0" id="thicknessTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" style="width:50px;">#</th>
                        <th>Thickness</th>
                        <th class="text-end pe-4" style="width:100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($thicknesses as $i => $t)
                        <tr>
                            <td class="ps-4 text-muted">{{ $i + 1 }}</td>
                            <td class="fw-semibold">{{ $t->label }}</td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-outline-dark me-1"
                                        onclick="editThk({{ json_encode($t) }})">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger"
                                        onclick="confirmDeleteThk({{ $t->id }}, '{{ addslashes($t->label) }}')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-5">
                                <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                                No thicknesses defined. Click <strong>Add Thickness</strong> to create one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Card 2: Glass Type to Thickness Assignments --}}
    <div class="card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between" style="background: var(--vip-primary); color: #fff;">
            <h6 class="mb-0"><i class="bi bi-grid-3x3 me-2"></i>Glass Type &rarr; Available Thicknesses</h6>
            <button class="btn btn-sm btn-outline-light" id="saveAssignBtn" onclick="saveAssignments()">
                <i class="bi bi-check-lg me-1"></i> Save
            </button>
        </div>
        <div class="card-body">
            <p class="text-muted small mb-3">
                For each glass type, check which thicknesses are available and pick a default from the dropdown.
                If nothing is checked, all thicknesses will appear in the quote.
            </p>
            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle mb-0" id="assignTable">
                    <thead class="table-light">
                        <tr id="assignHead">
                            <th style="min-width:130px;">Glass Type</th>
                            @foreach($thicknesses as $t)
                                <th class="text-center" style="min-width:70px;">{{ $t->label }}</th>
                            @endforeach
                            <th style="min-width:150px;">Default</th>
                        </tr>
                    </thead>
                    <tbody id="assignBody">
                        @foreach($glassTypes as $gt)
                            @php
                                $gtAssigns = $grouped[$gt] ?? [];
                                $assignedIds = collect($gtAssigns)->pluck('thickness_id')->toArray();
                                $defaultId = collect($gtAssigns)->where('is_default', true)->pluck('thickness_id')->first();
                                $safeGt = preg_replace('/[^a-zA-Z0-9]/', '_', $gt);
                            @endphp
                            <tr data-glass="{{ $gt }}">
                                <td class="fw-semibold">{{ $gt }}</td>
                                @foreach($thicknesses as $t)
                                    <td class="text-center">
                                        <input class="form-check-input assign-cb" type="checkbox"
                                               data-glass="{{ $gt }}" data-tid="{{ $t->id }}"
                                               @if(in_array($t->id, $assignedIds)) checked @endif
                                               style="width:16px; height:16px; cursor:pointer;">
                                    </td>
                                @endforeach
                                <td>
                                    <select class="form-select form-select-sm default-dd" id="dd_{{ $safeGt }}" data-glass="{{ $gt }}" style="font-size:12px;">
                                        <option value="">-- None --</option>
                                        @foreach($thicknesses as $t)
                                            <option value="{{ $t->id }}" @if($defaultId == $t->id) selected @endif>{{ $t->label }}</option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Card 3: Thickness Size Rules --}}
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between" style="background: var(--vip-primary); color: #fff;">
            <h6 class="mb-0"><i class="bi bi-aspect-ratio me-2"></i>Thickness Size Rules</h6>
            <button class="btn btn-sm btn-outline-light" data-bs-toggle="modal" data-bs-target="#ruleModal" onclick="openRuleModal()">
                <i class="bi bi-plus-lg me-1"></i> Add Rule
            </button>
        </div>
        <div class="card-body p-0">
            <p class="text-muted small px-3 pt-2 mb-1">
                Determines which thicknesses are valid for a given window size. Based on max square footage, max length (longer side), and max short side (shorter side).
            </p>
            <table class="table table-hover align-middle mb-0" id="sizeRulesTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Thickness</th>
                        <th>MM</th>
                        <th>Max Sq Ft</th>
                        <th>Max Length</th>
                        <th>Max Short Side</th>
                        <th class="text-end pe-4" style="width:100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sizeRules as $r)
                        <tr>
                            <td class="ps-4 fw-semibold">{{ $r->label }}</td>
                            <td>{{ $r->mm_value }} mm</td>
                            <td>{{ $r->max_sqft }} sq ft</td>
                            <td>{{ $r->max_length }}"</td>
                            <td>{{ $r->max_short_side }}"</td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-outline-dark me-1"
                                        onclick="editRule({{ json_encode($r) }})">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger"
                                        onclick="confirmDeleteRule({{ $r->id }})">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                No size rules defined. Click <strong>Add Rule</strong> to create one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add/Edit Thickness Modal --}}
<div class="modal fade" id="thicknessModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--vip-primary); color: #fff;">
                <h5 class="modal-title" id="thkModalTitle"><i class="bi bi-plus-lg me-2"></i>Add Thickness</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="thkForm">
                    @csrf
                    <input type="hidden" id="thkEditId" name="id">
                    <div class="mb-0">
                        <label for="thkLabel" class="form-label fw-semibold">Thickness</label>
                        <input type="text" class="form-control" id="thkLabel" name="label" placeholder='e.g. 3/4"' required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-vip" onclick="saveThk()">
                    <i class="bi bi-check-lg me-1"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Delete Thickness Confirm --}}
<div class="modal fade" id="deleteThkModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-trash me-2"></i>Delete Thickness</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Are you sure you want to remove <strong id="deleteThkName"></strong>?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteThkBtn">
                    <i class="bi bi-trash me-1"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Add/Edit Size Rule Modal --}}
<div class="modal fade" id="ruleModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--vip-primary); color: #fff;">
                <h5 class="modal-title" id="ruleModalTitle"><i class="bi bi-plus-lg me-2"></i>Add Size Rule</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="ruleEditId">
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold">Thickness Label</label>
                        <input type="text" class="form-control" id="ruleLabel" placeholder='e.g. 1/8"'>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">MM Value</label>
                        <input type="number" step="0.01" class="form-control" id="ruleMm" placeholder="e.g. 3.10">
                    </div>
                    <div class="col-4">
                        <label class="form-label fw-semibold">Max Sq Ft</label>
                        <input type="number" step="0.01" class="form-control" id="ruleMaxSqft" placeholder="e.g. 20">
                    </div>
                    <div class="col-4">
                        <label class="form-label fw-semibold">Max Length</label>
                        <input type="number" step="0.01" class="form-control" id="ruleMaxLength" placeholder='e.g. 80"'>
                    </div>
                    <div class="col-4">
                        <label class="form-label fw-semibold">Max Short Side</label>
                        <input type="number" step="0.01" class="form-control" id="ruleMaxShort" placeholder='e.g. 36"'>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-vip" onclick="saveRule()">
                    <i class="bi bi-check-lg me-1"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Delete Rule Confirm --}}
<div class="modal fade" id="deleteRuleModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-trash me-2"></i>Delete Size Rule</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Are you sure you want to remove this size rule?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteRuleBtn">
                    <i class="bi bi-trash me-1"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
var CSRF = '{{ csrf_token() }}';
var _glassTypes = @json($glassTypes);

// ════════════════════════════════════════════
// Thickness CRUD
// ════════════════════════════════════════════

function openThkModal(data) {
    document.getElementById('thkEditId').value = data ? data.id : '';
    document.getElementById('thkLabel').value = data ? data.label : '';
    document.getElementById('thkModalTitle').innerHTML = data
        ? '<i class="bi bi-pencil me-2"></i>Edit Thickness'
        : '<i class="bi bi-plus-lg me-2"></i>Add Thickness';
}

function editThk(data) {
    openThkModal(data);
    var modal = new bootstrap.Modal(document.getElementById('thicknessModal'));
    modal.show();
}

function saveThk() {
    var label = document.getElementById('thkLabel').value.trim();
    if (!label) { showToast('Thickness label is required.', 'danger'); return; }

    var id = document.getElementById('thkEditId').value;
    var url = id
        ? '{{ url("admin/master/glass/thickness") }}/' + id
        : '{{ route("admin.master.glass.thickness.store") }}';
    var method = id ? 'PUT' : 'POST';

    fetch(url, {
        method: method,
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ label: label })
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        if (d.success) {
            showToast(d.message || 'Saved!', 'success');
            location.reload();
        } else {
            showToast(d.message || 'Save failed.', 'danger');
        }
    })
    .catch(function(err) { showToast('Error: ' + err.message, 'danger'); });
}

var _deleteThkId = null;
function confirmDeleteThk(id, name) {
    _deleteThkId = id;
    document.getElementById('deleteThkName').textContent = name;
    var modal = new bootstrap.Modal(document.getElementById('deleteThkModal'));
    modal.show();
}

document.getElementById('confirmDeleteThkBtn')?.addEventListener('click', function() {
    if (!_deleteThkId) return;
    fetch('{{ url("admin/master/glass/thickness") }}/' + _deleteThkId, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        showToast('Thickness removed.', 'success');
        location.reload();
    });
});

// ════════════════════════════════════════════
// Glass Type -> Thickness Assignments
// ════════════════════════════════════════════

function saveAssignments() {
    var assignments = {};

    _glassTypes.forEach(function(gt) {
        var safeGt = gt.replace(/[^a-zA-Z0-9]/g, '_');
        var dd = document.getElementById('dd_' + safeGt);
        var defaultTid = dd ? parseInt(dd.value) || null : null;

        document.querySelectorAll('.assign-cb[data-glass="' + gt + '"]:checked').forEach(function(cb) {
            var tid = parseInt(cb.getAttribute('data-tid'));
            if (!assignments[gt]) assignments[gt] = [];
            assignments[gt].push({
                thickness_id: tid,
                is_default: (tid === defaultTid)
            });
        });

        if (defaultTid && (!assignments[gt] || !assignments[gt].some(function(a){ return a.thickness_id === defaultTid; }))) {
            if (!assignments[gt]) assignments[gt] = [];
            assignments[gt].push({ thickness_id: defaultTid, is_default: true });
            var cb = document.querySelector('.assign-cb[data-glass="' + gt + '"][data-tid="' + defaultTid + '"]');
            if (cb) cb.checked = true;
        }
    });

    var btn = document.getElementById('saveAssignBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i> Saving...';

    fetch('{{ route("admin.master.glass.thickness.assignments") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ assignments: assignments })
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Save';
        if (d.success) showToast('Assignments saved!', 'success');
        else showToast(d.message || 'Save failed', 'danger');
    })
    .catch(function(err) {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Save';
        showToast('Error: ' + err.message, 'danger');
    });
}

// ════════════════════════════════════════════
// Size Rules CRUD
// ════════════════════════════════════════════

function openRuleModal(data) {
    document.getElementById('ruleEditId').value = data ? data.id : '';
    document.getElementById('ruleLabel').value = data ? data.label : '';
    document.getElementById('ruleMm').value = data ? data.mm_value : '';
    document.getElementById('ruleMaxSqft').value = data ? data.max_sqft : '';
    document.getElementById('ruleMaxLength').value = data ? data.max_length : '';
    document.getElementById('ruleMaxShort').value = data ? data.max_short_side : '';
    document.getElementById('ruleModalTitle').innerHTML = data
        ? '<i class="bi bi-pencil me-2"></i>Edit Size Rule'
        : '<i class="bi bi-plus-lg me-2"></i>Add Size Rule';
}

function editRule(data) {
    openRuleModal(data);
    var modal = new bootstrap.Modal(document.getElementById('ruleModal'));
    modal.show();
}

function saveRule() {
    var label = document.getElementById('ruleLabel').value.trim();
    var mm = document.getElementById('ruleMm').value;
    var sqft = document.getElementById('ruleMaxSqft').value;
    var length = document.getElementById('ruleMaxLength').value;
    var short = document.getElementById('ruleMaxShort').value;

    if (!label) { showToast('Thickness label is required.', 'danger'); return; }
    if (!mm || mm <= 0) { showToast('MM value is required.', 'danger'); return; }
    if (!sqft || sqft <= 0) { showToast('Max Sq Ft is required.', 'danger'); return; }
    if (!length || length <= 0) { showToast('Max Length is required.', 'danger'); return; }
    if (!short || short <= 0) { showToast('Max Short Side is required.', 'danger'); return; }

    var payload = {
        id: document.getElementById('ruleEditId').value || null,
        label: label,
        mm_value: parseFloat(mm),
        max_sqft: parseFloat(sqft),
        max_length: parseFloat(length),
        max_short_side: parseFloat(short),
    };

    fetch('{{ route("admin.master.glass.thickness.size-rules.save") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify(payload)
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        if (d.success) {
            showToast(d.message || 'Size rule saved!', 'success');
            location.reload();
        } else {
            showToast(d.message || 'Save failed.', 'danger');
        }
    })
    .catch(function(err) { showToast('Error: ' + err.message, 'danger'); });
}

var _deleteRuleId = null;
function confirmDeleteRule(id) {
    _deleteRuleId = id;
    var modal = new bootstrap.Modal(document.getElementById('deleteRuleModal'));
    modal.show();
}

document.getElementById('confirmDeleteRuleBtn')?.addEventListener('click', function() {
    if (!_deleteRuleId) return;
    fetch('{{ url("admin/master/glass/thickness/size-rules") }}/' + _deleteRuleId, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        showToast('Size rule removed.', 'success');
        location.reload();
    })
    .catch(function(err) { showToast('Error: ' + err.message, 'danger'); });
});

// ════════════════════════════════════════════
// Toast Helper
// ════════════════════════════════════════════

function showToast(message, type) {
    var container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;';
        document.body.appendChild(container);
    }
    var toast = document.createElement('div');
    toast.className = 'alert alert-' + type + ' alert-dismissible fade show shadow-sm';
    toast.style.cssText = 'min-width:280px;font-size:14px;';
    toast.innerHTML = message + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    container.appendChild(toast);
    setTimeout(function() { toast.remove(); }, 4000);
}
</script>
@endpush
@endsection
