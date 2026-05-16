@extends('layouts.app')

@section('title', 'Pane Management')

@section('content')
<div class="p-4">
    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('admin.master.hub') }}" class="text-decoration-none" style="color: var(--vip-accent);">Master Data</a></li>
            <li class="breadcrumb-item"><span class="text-muted">Glass</span></li>
            <li class="breadcrumb-item active">Pane Management</li>
        </ol>
    </nav>

    <div class="d-flex align-items-center justify-content-between mb-4">
        <h4 class="fw-bold mb-0" style="color: var(--vip-primary);">
            <i class="bi bi-stack me-2" style="color: var(--vip-accent);"></i>Pane Management
        </h4>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-dark btn-sm" onclick="pmShowAddModal()">
                <i class="bi bi-plus-lg me-1"></i> Add Pane Type
            </button>
            <button class="btn btn-vip" id="paneSaveBtn" onclick="paneSave()">
                <i class="bi bi-check-lg me-1"></i> Save Changes
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <p class="text-muted small px-3 pt-3 mb-2">
                Check which pane configurations are available for each series.
            </p>
            <div class="table-responsive">
                <form id="paneForm">
                    @csrf
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4" style="min-width:160px;">Pane Type</th>
                                @foreach($seriesList as $s)
                                    <th class="text-center">{{ $s->series }}</th>
                                @endforeach
                                <th class="text-end pe-4" style="width:100px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="pmTableBody">
                            @php
                                $paneLabels = [
                                    'double_pane' => 'Double Pane',
                                    'three_pane'  => 'Three Pane',
                                ];
                            @endphp
                            @foreach($paneTypes as $pt)
                            <tr data-pane-type="{{ $pt }}">
                                <td class="ps-4 fw-semibold pm-type-label">{{ $paneLabels[$pt] ?? ucwords(str_replace('_', ' ', $pt)) }}</td>
                                @foreach($seriesList as $s)
                                <td class="text-center">
                                    <input class="form-check-input" type="checkbox"
                                           name="pane_options[{{ $pt }}][]"
                                           value="{{ $s->id }}"
                                           @if(in_array($pt, $paneAssignments[$s->id] ?? [])) checked @endif
                                           style="width:18px; height:18px; cursor:pointer;">
                                </td>
                                @endforeach
                                <td class="text-end pe-4">
                                    <button type="button" class="btn btn-sm btn-outline-dark me-1" onclick="pmShowEditModal(this)">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="pmShowDeleteModal(this)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Add / Edit Pane Type Modal --}}
<div class="modal fade" id="pmTypeModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--vip-primary); color: #fff;">
                <h5 class="modal-title" id="pmTypeModalTitle"><i class="bi bi-plus-lg me-2"></i>Add Pane Type</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="pmTypeMode" value="add">
                <input type="hidden" id="pmTypeEditKey" value="">
                <div class="mb-0">
                    <label for="pmTypeNameInput" class="form-label fw-semibold">Pane Type Name</label>
                    <input type="text" class="form-control" id="pmTypeNameInput" placeholder="e.g. Quad Pane">
                    <div class="invalid-feedback" id="pmTypeError"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-vip" id="pmTypeSubmitBtn" onclick="pmTypeSubmit()">
                    <i class="bi bi-plus-lg me-1"></i> Add
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="pmDeleteModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-trash me-2"></i>Delete Pane Type</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Are you sure you want to delete <strong id="pmDeleteName"></strong>?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="pmConfirmDelete()">
                    <i class="bi bi-trash me-1"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
var _seriesIds = @json($seriesList->pluck('id'));
var _seriesNames = @json($seriesList->pluck('series'));
var _pmDeleteRow = null;

function nameToKey(name) {
    return name.trim().toLowerCase().replace(/\s+/g, '_');
}

// ── Add Modal ──
function pmShowAddModal() {
    document.getElementById('pmTypeMode').value = 'add';
    document.getElementById('pmTypeEditKey').value = '';
    document.getElementById('pmTypeNameInput').value = '';
    document.getElementById('pmTypeNameInput').classList.remove('is-invalid');
    document.getElementById('pmTypeModalTitle').innerHTML = '<i class="bi bi-plus-lg me-2"></i>Add Pane Type';
    document.getElementById('pmTypeSubmitBtn').innerHTML = '<i class="bi bi-plus-lg me-1"></i> Add';
    var modal = new bootstrap.Modal(document.getElementById('pmTypeModal'));
    modal.show();
    setTimeout(function() { document.getElementById('pmTypeNameInput').focus(); }, 300);
}

// ── Edit Modal ──
function pmShowEditModal(btn) {
    var tr = btn.closest('tr');
    var oldKey = tr.dataset.paneType;
    var oldName = tr.querySelector('.pm-type-label').textContent.trim();
    document.getElementById('pmTypeMode').value = 'edit';
    document.getElementById('pmTypeEditKey').value = oldKey;
    document.getElementById('pmTypeNameInput').value = oldName;
    document.getElementById('pmTypeNameInput').classList.remove('is-invalid');
    document.getElementById('pmTypeModalTitle').innerHTML = '<i class="bi bi-pencil me-2"></i>Edit Pane Type';
    document.getElementById('pmTypeSubmitBtn').innerHTML = '<i class="bi bi-check-lg me-1"></i> Save';
    var modal = new bootstrap.Modal(document.getElementById('pmTypeModal'));
    modal.show();
    setTimeout(function() { document.getElementById('pmTypeNameInput').select(); }, 300);
}

// ── Submit (Add or Edit) ──
function pmTypeSubmit() {
    var input = document.getElementById('pmTypeNameInput');
    var name = input.value.trim();
    var mode = document.getElementById('pmTypeMode').value;
    var errorEl = document.getElementById('pmTypeError');
    var key = nameToKey(name);

    if (!name) {
        input.classList.add('is-invalid');
        errorEl.textContent = 'Please enter a pane type name.';
        return;
    }

    var editingOld = document.getElementById('pmTypeEditKey').value;
    var existing = document.querySelectorAll('#pmTableBody tr');
    for (var i = 0; i < existing.length; i++) {
        if (existing[i].dataset.paneType === key) {
            if (mode === 'edit' && existing[i].dataset.paneType === editingOld) continue;
            input.classList.add('is-invalid');
            errorEl.textContent = 'Pane type "' + name + '" already exists.';
            return;
        }
    }

    if (mode === 'edit') {
        var rows = document.querySelectorAll('#pmTableBody tr');
        for (var j = 0; j < rows.length; j++) {
            if (rows[j].dataset.paneType === editingOld) {
                rows[j].dataset.paneType = key;
                rows[j].querySelector('.pm-type-label').textContent = name;
                rows[j].querySelectorAll('input[type="checkbox"]').forEach(function(cb) {
                    cb.name = 'pane_options[' + key + '][]';
                });
                break;
            }
        }
    } else {
        pmAddTypeRow(key, name);
    }

    var modalEl = document.getElementById('pmTypeModal');
    var modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) modal.hide();
}

// ── Enter key submits ──
document.addEventListener('DOMContentLoaded', function() {
    var input = document.getElementById('pmTypeNameInput');
    if (input) {
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') { e.preventDefault(); pmTypeSubmit(); }
        });
        input.addEventListener('input', function() { this.classList.remove('is-invalid'); });
    }
});

// ── Add Row ──
function pmAddTypeRow(key, name) {
    var tbody = document.getElementById('pmTableBody');
    var tr = document.createElement('tr');
    tr.dataset.paneType = key;
    var html = '<td class="ps-4 fw-semibold pm-type-label">' + escHtml(name) + '</td>';
    _seriesIds.forEach(function(sid) {
        html += '<td class="text-center"><input class="form-check-input" type="checkbox" name="pane_options[' + escHtml(key) + '][]" value="' + sid + '" style="width:18px; height:18px; cursor:pointer;"></td>';
    });
    html += '<td class="text-end pe-4">';
    html += '<button type="button" class="btn btn-sm btn-outline-dark me-1" onclick="pmShowEditModal(this)"><i class="bi bi-pencil"></i></button>';
    html += '<button type="button" class="btn btn-sm btn-outline-danger" onclick="pmShowDeleteModal(this)"><i class="bi bi-trash"></i></button>';
    html += '</td>';
    tr.innerHTML = html;
    tbody.appendChild(tr);
}

// ── Delete ──
function pmShowDeleteModal(btn) {
    _pmDeleteRow = btn.closest('tr');
    document.getElementById('pmDeleteName').textContent = _pmDeleteRow.querySelector('.pm-type-label').textContent.trim();
    var modal = new bootstrap.Modal(document.getElementById('pmDeleteModal'));
    modal.show();
}

function pmConfirmDelete() {
    if (_pmDeleteRow) { _pmDeleteRow.remove(); _pmDeleteRow = null; }
    var modalEl = document.getElementById('pmDeleteModal');
    var modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) modal.hide();
}

// ── Helpers ──
function escHtml(str) {
    var d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}

// ── Save ──
function paneSave() {
    var btn = document.getElementById('paneSaveBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i> Saving...';

    var form = document.getElementById('paneForm');
    var formData = new FormData(form);

    var opts = {};
    form.querySelectorAll('input[type="checkbox"]:checked').forEach(function(cb) {
        var match = cb.name.match(/pane_options\[([^\]]+)\]/);
        if (match) {
            var pt = match[1];
            if (!opts[pt]) opts[pt] = [];
            opts[pt].push(cb.value);
        }
    });

    fetch('{{ route("admin.master.glass.panes.update") }}', {
        method: 'PUT',
        credentials: 'same-origin',
        headers: {
            'X-CSRF-TOKEN': formData.get('_token'),
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ pane_options: opts })
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Save Changes';
        if (d.success) {
            showToast('Pane types saved successfully.', 'success');
        } else {
            showToast(d.message || 'Save failed.', 'danger');
        }
    })
    .catch(function(err) {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Save Changes';
        showToast('Error saving: ' + err.message, 'danger');
    });
}

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
