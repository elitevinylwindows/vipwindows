@extends('layouts.app')

@section('title', 'Grid Patterns')

@section('content')
<div class="p-4">
    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('admin.master.hub') }}" class="text-decoration-none" style="color: var(--vip-accent);">Master Data</a></li>
            <li class="breadcrumb-item"><a href="#" class="text-decoration-none" style="color: var(--vip-accent);">Grids</a></li>
            <li class="breadcrumb-item active">Grid Patterns</li>
        </ol>
    </nav>

    <div class="d-flex align-items-center justify-content-between mb-4">
        <h4 class="fw-bold mb-0" style="color: var(--vip-primary);">
            <i class="bi bi-grid-3x3-gap me-2" style="color: var(--vip-accent);"></i>Grid Patterns
        </h4>
        <button class="btn btn-vip" data-bs-toggle="modal" data-bs-target="#createModal">
            <i class="bi bi-plus-lg me-1"></i> Add Pattern
        </button>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Name</th>
                            <th>Code</th>
                            <th class="text-center">H Bars</th>
                            <th class="text-center">V Bars</th>
                            <th>Description</th>
                            <th class="text-center">Active</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($gridPatterns as $i => $pattern)
                            <tr>
                                <td class="ps-4 text-muted">{{ $i + 1 }}</td>
                                <td class="fw-semibold">{{ $pattern->name }}</td>
                                <td>{{ $pattern->code ?? '-' }}</td>
                                <td class="text-center">{{ $pattern->horizontal_bars ?? '-' }}</td>
                                <td class="text-center">{{ $pattern->vertical_bars ?? '-' }}</td>
                                <td class="text-muted small">{{ $pattern->description ?? '-' }}</td>
                                <td class="text-center">
                                    @if($pattern->is_active)
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                    @else
                                        <i class="bi bi-x-circle text-secondary"></i>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-outline-dark me-1"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editModal"
                                            data-id="{{ $pattern->id }}"
                                            data-name="{{ $pattern->name }}"
                                            data-code="{{ $pattern->code }}"
                                            data-horizontal-bars="{{ $pattern->horizontal_bars }}"
                                            data-vertical-bars="{{ $pattern->vertical_bars }}"
                                            data-description="{{ $pattern->description }}"
                                            data-is-active="{{ $pattern->is_active }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('admin.master.grids.patterns.destroy', $pattern->id) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Delete this grid pattern?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                                    No grid patterns found. Click <strong>Add Pattern</strong> to create one.
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
        <form action="{{ route('admin.master.grids.patterns.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header" style="background: var(--vip-primary); color: #fff;">
                    <h5 class="modal-title"><i class="bi bi-plus-lg me-2"></i>Add Grid Pattern</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Name</label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g. 2x2 Colonial">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Code</label>
                            <input type="text" name="code" class="form-control" placeholder="e.g. 2X2COL">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Horizontal Bars</label>
                            <input type="number" name="horizontal_bars" class="form-control" min="0" placeholder="e.g. 1">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Vertical Bars</label>
                            <input type="number" name="vertical_bars" class="form-control" min="0" placeholder="e.g. 1">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Description</label>
                            <input type="text" name="description" class="form-control" placeholder="Optional">
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" class="form-check-input" id="createActive" checked>
                                <label class="form-check-label" for="createActive">Active</label>
                            </div>
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
                    <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Grid Pattern</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Name</label>
                            <input type="text" name="name" id="editName" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Code</label>
                            <input type="text" name="code" id="editCode" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Horizontal Bars</label>
                            <input type="number" name="horizontal_bars" id="editHBars" class="form-control" min="0">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Vertical Bars</label>
                            <input type="number" name="vertical_bars" id="editVBars" class="form-control" min="0">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Description</label>
                            <input type="text" name="description" id="editDescription" class="form-control">
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" class="form-check-input" id="editActive">
                                <label class="form-check-label" for="editActive">Active</label>
                            </div>
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
    document.getElementById('editModal').addEventListener('show.bs.modal', function (event) {
        const btn = event.relatedTarget;
        document.getElementById('editForm').action = '{{ url("admin/master/grids/patterns") }}/' + btn.getAttribute('data-id');
        document.getElementById('editName').value = btn.getAttribute('data-name');
        document.getElementById('editCode').value = btn.getAttribute('data-code') || '';
        document.getElementById('editHBars').value = btn.getAttribute('data-horizontal-bars') || '';
        document.getElementById('editVBars').value = btn.getAttribute('data-vertical-bars') || '';
        document.getElementById('editDescription').value = btn.getAttribute('data-description') || '';
        document.getElementById('editActive').checked = btn.getAttribute('data-is-active') == '1';
    });
</script>
@endpush
@endsection
