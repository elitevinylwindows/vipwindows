@extends('layouts.app')

@section('title', 'Tempered / Specialty Glass')

@section('content')
<div class="p-4">
    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('admin.master.hub') }}" class="text-decoration-none" style="color: var(--vip-accent);">Master Data</a></li>
            <li class="breadcrumb-item"><span class="text-muted">Glass</span></li>
            <li class="breadcrumb-item active">Tempered / Specialty</li>
        </ol>
    </nav>

    <div class="d-flex align-items-center justify-content-between mb-4">
        <h4 class="fw-bold mb-0" style="color: var(--vip-primary);">
            <i class="bi bi-fire me-2" style="color: var(--vip-accent);"></i>Tempered / Specialty Glass
        </h4>
        <button class="btn btn-vip" id="gtSaveBtn" onclick="gtSave()">
            <i class="bi bi-check-lg me-1"></i> Save Changes
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <p class="text-muted small mb-3">
                Check which glass types are available for <strong>Tempered</strong> and <strong>Specialty</strong>.
                These selections apply to all series and control what appears in the quote form.
            </p>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" style="min-width:160px;">Glass Type</th>
                            <th class="text-center" style="width:140px;">Tempered</th>
                            <th class="text-center" style="width:140px;">Specialty</th>
                        </tr>
                    </thead>
                    <tbody id="gtBody">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
var _glassTypes = @json($glassTypes);
var _relationships = @json($relationships);
var _csrf = '{{ csrf_token() }}';
var GLOBAL_SERIES = 0;

function renderGrid() {
    var rels = _relationships[GLOBAL_SERIES] || {};
    var temperedList = rels['tempered'] || [];
    var specialtyList = rels['specialty'] || [];
    var tbody = document.getElementById('gtBody');
    var html = '';

    _glassTypes.forEach(function(gt) {
        var tChecked = temperedList.indexOf(gt) !== -1 ? ' checked' : '';
        var sChecked = specialtyList.indexOf(gt) !== -1 ? ' checked' : '';
        html += '<tr>';
        html += '<td class="ps-4 fw-semibold">' + gt + '</td>';
        html += '<td class="text-center"><input class="form-check-input gt-tempered" type="checkbox" value="' + gt + '"' + tChecked + ' style="width:18px; height:18px; cursor:pointer;"></td>';
        html += '<td class="text-center"><input class="form-check-input gt-specialty" type="checkbox" value="' + gt + '"' + sChecked + ' style="width:18px; height:18px; cursor:pointer;"></td>';
        html += '</tr>';
    });

    tbody.innerHTML = html;
}

renderGrid();

function gtSave() {
    var tempered = [];
    var specialty = [];
    document.querySelectorAll('.gt-tempered:checked').forEach(function(cb) { tempered.push(cb.value); });
    document.querySelectorAll('.gt-specialty:checked').forEach(function(cb) { specialty.push(cb.value); });

    var btn = document.getElementById('gtSaveBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i> Saving...';

    fetch('{{ route("admin.master.glass.tempered.update") }}', {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': _csrf,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ series_id: GLOBAL_SERIES, tempered: tempered, specialty: specialty })
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Save Changes';
        if (d.success) {
            _relationships[GLOBAL_SERIES] = { tempered: tempered, specialty: specialty };
            showToast('Relationships saved successfully.', 'success');
        } else {
            showToast(d.message || 'Save failed.', 'danger');
        }
    })
    .catch(function(err) {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Save Changes';
        showToast('Error: ' + err.message, 'danger');
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
