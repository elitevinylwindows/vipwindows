@extends('layouts.app')

@section('title', 'Grid Profiles')

@section('content')
<div class="p-4">
    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('admin.master.hub') }}" class="text-decoration-none" style="color: var(--vip-accent);">Master Data</a></li>
            <li class="breadcrumb-item"><a href="#" class="text-decoration-none" style="color: var(--vip-accent);">Grids</a></li>
            <li class="breadcrumb-item active">Grid Profiles</li>
        </ol>
    </nav>

    <div class="d-flex align-items-center justify-content-between mb-4">
        <h4 class="fw-bold mb-0" style="color: var(--vip-primary);">
            <i class="bi bi-border-all me-2" style="color: var(--vip-accent);"></i>Grid Profiles
        </h4>
        <button class="btn btn-vip" data-bs-toggle="modal" data-bs-target="#createModal">
            <i class="bi bi-plus-lg me-1"></i> Add Grid Profile
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
                            <th class="text-end">Width</th>
                            <th class="text-end">Thickness</th>
                            <th>Material</th>
                            <th>Description</th>
                            <th class="text-center">Active</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($gridProfiles as $i => $profile)
                            <tr>
                                <td class="ps-4 text-muted">{{ $i + 1 }}</td>
                                <td class="fw-semibold">{{ $profile->name }}</td>
                                <td>{{ $profile->code ?? '-' }}</td>
                                <td class="text-end">{{ $profile->width ?? '-' }}</td>
                                <td class="text-end">{{ $profile->thickness ?? '-' }}</td>
                                <td>{{ $profile->material ?? '-' }}</td>
                                <td class="text-muted small">{{ $profile->description ?? '-' }}</td>
                                <td class="text-center">
                                    @if($profile->is_active)
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                    @else
                                        <i class="bi bi-x-circle text-secondary"></i>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-outline-dark me-1"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editModal"
                                            data-id="{{ $profile->id }}"
                                            data-name="{{ $profile->name }}"
                                            data-code="{{ $profile->code }}"
                                            data-width="{{ $profile->width }}"
                                            data-thickness="{{ $profile->thickness }}"
                                            data-material="{{ $profile->material }}"
                                            data-description="{{ $profile->description }}"
                                            data-is-active="{{ $profile->is_active }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('admin.master.grids.profiles.destroy', $profile->id) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Delete this grid profile?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-5">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                                    No grid profiles found. Click <strong>Add Grid Profile</strong> to create one.
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
        <form action="{{ route('admin.master.grids.profiles.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header" style="background: var(--vip-primary); color: #fff;">
                    <h5 class="modal-title"><i class="bi bi-plus-lg me-2"></i>Add Grid Profile</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Name</label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g. 5/8 Flat">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Code</label>
                            <input type="text" name="code" class="form-control" placeholder="e.g. FL58">
                        </div>
                        <div class="col-4">
                            <label class="form-label fw-semibold">Width</label>
                            <input type="number" step="0.001" name="width" class="form-control" placeholder="e.g. 0.625">
                        </div>
                        <div class="col-4">
                            <label class="form-label fw-semibold">Thickness</label>
                            <input type="number" step="0.001" name="thickness" class="form-control" placeholder="e.g. 0.25">
                        </div>
                        <div class="col-4">
                            <label class="form-label fw-semibold">Material</label>
                            <input type="text" name="material" class="form-control" placeholder="e.g. Vinyl">
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
                    <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Grid Profile</h5>
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
                        <div class="col-4">
                            <label class="form-label fw-semibold">Width</label>
                            <input type="number" step="0.001" name="width" id="editWidth" class="form-control">
                        </div>
                        <div class="col-4">
                            <label class="form-label fw-semibold">Thickness</label>
                            <input type="number" step="0.001" name="thickness" id="editThickness" class="form-control">
                        </div>
                        <div class="col-4">
                            <label class="form-label fw-semibold">Material</label>
                            <input type="text" name="material" id="editMaterial" class="form-control">
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
        document.getElementById('editForm').action = '{{ url("admin/master/grids/profiles") }}/' + btn.getAttribute('data-id');
        document.getElementById('editName').value = btn.getAttribute('data-name');
        document.getElementById('editCode').value = btn.getAttribute('data-code') || '';
        document.getElementById('editWidth').value = btn.getAttribute('data-width') || '';
        document.getElementById('editThickness').value = btn.getAttribute('data-thickness') || '';
        document.getElementById('editMaterial').value = btn.getAttribute('data-material') || '';
        document.getElementById('editDescription').value = btn.getAttribute('data-description') || '';
        document.getElementById('editActive').checked = btn.getAttribute('data-is-active') == '1';
    });
</script>
@endpush
@endsection
