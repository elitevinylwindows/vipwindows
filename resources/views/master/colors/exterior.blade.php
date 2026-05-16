@extends('layouts.app')

@section('title', 'Exterior Colors')

@section('content')
<div class="p-4">
    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('admin.master.hub') }}" class="text-decoration-none" style="color: var(--vip-accent);">Master Data</a></li>
            <li class="breadcrumb-item"><span class="text-muted">Colors</span></li>
            <li class="breadcrumb-item active">Exterior Colors</li>
        </ol>
    </nav>

    <div class="d-flex align-items-center justify-content-between mb-4">
        <h4 class="fw-bold mb-0" style="color: var(--vip-primary);">
            <i class="bi bi-brush me-2" style="color: var(--vip-accent);"></i>Exterior Colors
        </h4>
        <button class="btn btn-vip" data-bs-toggle="modal" data-bs-target="#createModal">
            <i class="bi bi-plus-lg me-1"></i> Add Color
        </button>
    </div>

    {{-- Search --}}
    <div class="card mb-3">
        <div class="card-body py-2">
            <div class="row align-items-center">
                <div class="col-auto">
                    <label class="form-label small mb-0 fw-semibold text-muted">Search:</label>
                </div>
                <div class="col-auto">
                    <input type="text" id="searchFilter" class="form-control form-control-sm" placeholder="Search by name or code..." style="width: 260px;">
                </div>
            </div>
        </div>
    </div>

    {{-- Colors Table --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="colorsTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Swatch</th>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Hex</th>
                            <th>Created</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($colors as $i => $color)
                            <tr data-search="{{ strtolower($color->code . ' ' . $color->name) }}">
                                <td class="ps-4 text-muted">{{ $i + 1 }}</td>
                                <td>
                                    @if($color->hex_color)
                                        <div style="width: 28px; height: 28px; border-radius: 4px; background: {{ $color->hex_color }}; border: 1px solid rgba(0,0,0,.15);"></div>
                                    @else
                                        <div style="width: 28px; height: 28px; border-radius: 4px; background: #eee; border: 1px solid rgba(0,0,0,.1);" class="d-flex align-items-center justify-content-center">
                                            <i class="bi bi-question text-muted small"></i>
                                        </div>
                                    @endif
                                </td>
                                <td><code>{{ $color->code }}</code></td>
                                <td class="fw-semibold">{{ $color->name }}</td>
                                <td class="text-muted small">{{ $color->hex_color ?? '-' }}</td>
                                <td class="text-muted small">{{ $color->created_at ? $color->created_at->format('M d, Y') : '-' }}</td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-outline-dark me-1"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editModal"
                                            data-id="{{ $color->id }}"
                                            data-name="{{ $color->name }}"
                                            data-code="{{ $color->code }}"
                                            data-hex="{{ $color->hex_color }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('admin.master.colors.exterior.destroy', $color->id) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Delete this color? This cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                                    No exterior colors found. Click <strong>Add Color</strong> to create one.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Create Modal --}}
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.master.colors.exterior.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header" style="background: var(--vip-primary); color: #fff;">
                    <h5 class="modal-title"><i class="bi bi-plus-lg me-2"></i>Add Exterior Color</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Code</label>
                        <input type="text" name="code" class="form-control" required placeholder="e.g. WH">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Name</label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. White">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Hex Color</label>
                        <div class="input-group">
                            <input type="color" class="form-control form-control-color" id="create_hex_picker" value="#ffffff">
                            <input type="text" name="hex_color" id="create_hex_text" class="form-control" placeholder="#FFFFFF" maxlength="7">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-vip"><i class="bi bi-check-lg me-1"></i> Create</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header" style="background: var(--vip-primary); color: #fff;">
                    <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Exterior Color</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Code</label>
                        <input type="text" name="code" id="edit_code" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Name</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Hex Color</label>
                        <div class="input-group">
                            <input type="color" class="form-control form-control-color" id="edit_hex_picker" value="#ffffff">
                            <input type="text" name="hex_color" id="edit_hex_text" class="form-control" placeholder="#FFFFFF" maxlength="7">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-vip"><i class="bi bi-check-lg me-1"></i> Update</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Color picker sync - Create
    document.getElementById('create_hex_picker').addEventListener('input', function () {
        document.getElementById('create_hex_text').value = this.value.toUpperCase();
    });
    document.getElementById('create_hex_text').addEventListener('input', function () {
        if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
            document.getElementById('create_hex_picker').value = this.value;
        }
    });

    // Color picker sync - Edit
    document.getElementById('edit_hex_picker').addEventListener('input', function () {
        document.getElementById('edit_hex_text').value = this.value.toUpperCase();
    });
    document.getElementById('edit_hex_text').addEventListener('input', function () {
        if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
            document.getElementById('edit_hex_picker').value = this.value;
        }
    });

    // Populate edit modal
    document.getElementById('editModal').addEventListener('show.bs.modal', function (event) {
        const btn = event.relatedTarget;
        document.getElementById('editForm').action = '{{ url("admin/master/colors/exterior") }}/' + btn.getAttribute('data-id');
        document.getElementById('edit_code').value = btn.getAttribute('data-code');
        document.getElementById('edit_name').value = btn.getAttribute('data-name');
        const hex = btn.getAttribute('data-hex') || '';
        document.getElementById('edit_hex_text').value = hex;
        document.getElementById('edit_hex_picker').value = hex && /^#[0-9A-Fa-f]{6}$/.test(hex) ? hex : '#ffffff';
    });

    // Search filter
    document.getElementById('searchFilter').addEventListener('input', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll('#colorsTable tbody tr[data-search]').forEach(function (row) {
            row.style.display = !q || row.getAttribute('data-search').includes(q) ? '' : 'none';
        });
    });
</script>
@endpush
@endsection
