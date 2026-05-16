@extends('layouts.app')

@section('title', 'Glass Options')

@section('content')
<div class="p-4">
    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('admin.master.hub') }}" class="text-decoration-none" style="color: var(--vip-accent);">Master Data</a></li>
            <li class="breadcrumb-item"><span class="text-muted">Glass</span></li>
            <li class="breadcrumb-item active">Glass Options</li>
        </ol>
    </nav>

    <div class="d-flex align-items-center justify-content-between mb-4">
        <h4 class="fw-bold mb-0" style="color: var(--vip-primary);">
            <i class="bi bi-layers me-2" style="color: var(--vip-accent);"></i>Glass Options
        </h4>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-dark btn-sm" id="goAddBtn" data-bs-toggle="modal" data-bs-target="#addTypeModal">
                <i class="bi bi-plus-lg me-1"></i> Add Glass Type
            </button>
            <button class="btn btn-vip" id="goSaveBtn" onclick="goSave()">
                <i class="bi bi-check-lg me-1"></i> Save Changes
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <p class="text-muted small px-3 pt-3 mb-2">
                Check which glass types are available for each pane position (Outside, Middle, Inside).
            </p>
            <div class="table-responsive">
                <form id="goForm">
                    @csrf
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4" style="min-width:160px;">Glass Type</th>
                                @foreach($positions as $pos)
                                    <th class="text-center">{{ ucfirst($pos) }}</th>
                                @endforeach
                                <th class="text-end pe-4" style="width:100px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="goTableBody">
                            @foreach($glassTypes as $gt)
                            <tr data-glass-type="{{ $gt }}">
                                <td class="ps-4 fw-semibold go-type-label">{{ $gt }}</td>
                                @foreach($positions as $pos)
                                <td class="text-center">
                                    <input class="form-check-input" type="checkbox"
                                           name="options[{{ $pos }}][]"
                                           value="{{ $gt }}"
                                           @if(in_array($gt, $assignments[$pos] ?? [])) checked @endif
                                           style="width:18px; height:18px; cursor:pointer;">
                                </td>
                                @endforeach
                                <td class="text-end pe-4">
                                    <button type="button" class="btn btn-sm btn-outline-dark me-1" onclick="goShowEditModal(this)">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="goDeleteType(this)">
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

{{-- Add Glass Type Modal --}}
<div class="modal fade" id="addTypeModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--vip-primary); color: #fff;">
                <h5 class="modal-title" id="goTypeModalTitle"><i class="bi bi-plus-lg me-2"></i>Add Glass Type</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="goTypeMode" value="add">
                <input type="hidden" id="goTypeEditRow" value="">
                <div class="mb-0">
                    <label for="goTypeNameInput" class="form-label fw-semibold">Glass Type Name</label>
                    <input type="text" class="form-control" id="goTypeNameInput" placeholder="e.g. Acid Etch">
                    <div class="invalid-feedback" id="goTypeError"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-vip" id="goTypeSubmitBtn" onclick="goTypeSubmit()">
                    <i class="bi bi-plus-lg me-1"></i> Add
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="goDeleteModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-trash me-2"></i>Delete Glass Type</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Are you sure you want to delete <strong id="goDeleteName"></strong>?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="goConfirmDelete()">
                    <i class="bi bi-trash me-1"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
var _positions = @json($positions);
var _goDeleteRow = null;

// ── Add Modal ──
function goShowAddModal() {
    document.getElementById('goTypeMode').value = 'add';
    document.getElementById('goTypeEditRow').value = '';
    document.getElementById('goTypeNameInput').value = '';
    document.getElementById('goTypeNameInput').classList.remove('is-invalid');
    document.getElementById('goTypeModalTitle').innerHTML = '<i class="bi bi-plus-lg me-2"></i>Add Glass Type';
    document.getElementById('goTypeSubmitBtn').innerHTML = '<i class="bi bi-plus-lg me-1"></i> Add';
    var modal = new bootstrap.Modal(document.getElementById('addTypeModal'));
    modal.show();
    setTimeout(function() { document.getElementById('goTypeNameInput').focus(); }, 300);
}

// ── Edit Modal ──
function goShowEditModal(btn) {
    var tr = btn.closest('tr');
    var oldName = tr.dataset.glassType;
    document.getElementById('goTypeMode').value = 'edit';
    document.getElementById('goTypeEditRow').value = oldName;
    document.getElementById('goTypeNameInput').value = oldName;
    document.getElementById('goTypeNameInput').classList.remove('is-invalid');
    document.getElementById('goTypeModalTitle').innerHTML = '<i class="bi bi-pencil me-2"></i>Edit Glass Type';
    document.getElementById('goTypeSubmitBtn').innerHTML = '<i class="bi bi-check-lg me-1"></i> Save';
    var modal = new bootstrap.Modal(document.getElementById('addTypeModal'));
    modal.show();
    setTimeout(function() { document.getElementById('goTypeNameInput').select(); }, 300);
}

// ── Submit (Add or Edit) ──
function goTypeSubmit() {
    var input = document.getElementById('goTypeNameInput');
    var name = input.value.trim();
    var mode = document.getElementById('goTypeMode').value;
    var errorEl = document.getElementById('goTypeError');

    if (!name) {
        input.classList.add('is-invalid');
        errorEl.textContent = 'Please enter a glass type name.';
        return;
    }

    var editingOld = document.getElementById('goTypeEditRow').value;
    var existing = document.querySelectorAll('#goTableBody tr');
    for (var i = 0; i < existing.length; i++) {
        if (existing[i].dataset.glassType.toLowerCase() === name.toLowerCase()) {
            if (mode === 'edit' && existing[i].dataset.glassType === editingOld) continue;
            input.classList.add('is-invalid');
            errorEl.textContent = 'Glass type "' + name + '" already exists.';
            return;
        }
    }

    if (mode === 'edit') {
        var rows = document.querySelectorAll('#goTableBody tr');
        for (var j = 0; j < rows.length; j++) {
            if (rows[j].dataset.glassType === editingOld) {
                rows[j].dataset.glassType = name;
                rows[j].querySelector('.go-type-label').textContent = name;
                rows[j].querySelectorAll('input[type="checkbox"]').forEach(function(cb) {
                    cb.value = name;
                });
                break;
            }
        }
    } else {
        addTypeRow(name);
    }

    var modalEl = document.getElementById('addTypeModal');
    var modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) modal.hide();
}

// ── Enter key submits ──
document.addEventListener('DOMContentLoaded', function() {
    var input = document.getElementById('goTypeNameInput');
    if (input) {
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') { e.preventDefault(); goTypeSubmit(); }
        });
        input.addEventListener('input', function() { this.classList.remove('is-invalid'); });
    }
});

// ── Add Row ──
function addTypeRow(name) {
    var tbody = document.getElementById('goTableBody');
    var tr = document.createElement('tr');
    tr.dataset.glassType = name;
    var html = '<td class="ps-4 fw-semibold go-type-label">' + escHtml(name) + '</td>';
    _positions.forEach(function(pos) {
        html += '<td class="text-center"><input class="form-check-input" type="checkbox" name="options[' + pos + '][]" value="' + escHtml(name) + '" style="width:18px; height:18px; cursor:pointer;"></td>';
    });
    html += '<td class="text-end pe-4">';
    html += '<button type="button" class="btn btn-sm btn-outline-dark me-1" onclick="goShowEditModal(this)"><i class="bi bi-pencil"></i></button>';
    html += '<button type="button" class="btn btn-sm btn-outline-danger" onclick="goDeleteType(this)"><i class="bi bi-trash"></i></button>';
    html += '</td>';
    tr.innerHTML = html;
    tbody.appendChild(tr);
}

// ── Delete ──
function goDeleteType(btn) {
    _goDeleteRow = btn.closest('tr');
    document.getElementById('goDeleteName').textContent = _goDeleteRow.dataset.glassType;
    var modal = new bootstrap.Modal(document.getElementById('goDeleteModal'));
    modal.show();
}

function goConfirmDelete() {
    if (_goDeleteRow) { _goDeleteRow.remove(); _goDeleteRow = null; }
    var modalEl = document.getElementById('goDeleteModal');
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
function goSave() {
    var btn = document.getElementById('goSaveBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-arrow-repeat me-1 spin-icon"></i> Saving...';

    var form = document.getElementById('goForm');
    var formData = new FormData(form);

    var allTypes = [];
    document.querySelectorAll('#goTableBody tr').forEach(function(tr) {
        allTypes.push(tr.dataset.glassType);
    });

    var opts = {};
    form.querySelectorAll('input[type="checkbox"]:checked').forEach(function(cb) {
        var match = cb.name.match(/options\[(\w+)\]/);
        if (match) {
            var pos = match[1];
            if (!opts[pos]) opts[pos] = [];
            opts[pos].push(cb.value);
        }
    });

    fetch('{{ route("admin.master.glass.options.update") }}', {
        method: 'PUT',
        credentials: 'same-origin',
        headers: {
            'X-CSRF-TOKEN': formData.get('_token'),
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ options: opts, glass_types: allTypes })
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Save Changes';
        if (d.success) {
            showToast('Glass options saved successfully.', 'success');
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
