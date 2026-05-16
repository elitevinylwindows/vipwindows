@extends('layouts.app')

@section('title', 'Series Configurations')

@section('content')
<div class="p-4">
    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('admin.master.hub') }}" class="text-decoration-none" style="color: var(--vip-accent);">Master Data</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.master.series.index') }}" class="text-decoration-none" style="color: var(--vip-accent);">Series</a></li>
            <li class="breadcrumb-item active">Configurations</li>
        </ol>
    </nav>

    <div class="d-flex align-items-center justify-content-between mb-4">
        <h4 class="fw-bold mb-0" style="color: var(--vip-primary);">
            <i class="bi bi-sliders me-2" style="color: var(--vip-accent);"></i>Series Configurations
        </h4>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-dark" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="bi bi-upload me-1"></i> Import
            </button>
            <button class="btn btn-vip" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="bi bi-plus-lg me-1"></i> Add Configuration
            </button>
        </div>
    </div>

    {{-- Filter Row --}}
    <div class="card mb-3">
        <div class="card-body py-2">
            <div class="row align-items-center">
                <div class="col-auto">
                    <label class="form-label small mb-0 fw-semibold text-muted">Filter by Category:</label>
                </div>
                <div class="col-auto">
                    <select id="categoryFilter" class="form-select form-select-sm" style="width: 200px;">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <input type="text" id="searchFilter" class="form-control form-control-sm" placeholder="Search series type..." style="width: 220px;">
                </div>
            </div>
        </div>
    </div>

    {{-- Configurations Table --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="configTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Series Type</th>
                            <th>Category</th>
                            <th>Image</th>
                            <th class="text-center">Active</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($configurations as $i => $config)
                            <tr data-category="{{ $config->category }}" data-series-type="{{ strtolower($config->series_type) }}">
                                <td class="ps-4 text-muted">{{ $i + 1 }}</td>
                                <td class="fw-semibold">{{ $config->series_type }}</td>
                                <td>
                                    @if($config->category)
                                        <span class="badge bg-secondary">{{ $config->category }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($config->image)
                                        <img src="{{ $config->image }}" alt="" style="max-height: 40px; max-width: 60px;" class="rounded">
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-inline-block">
                                        <input class="form-check-input toggle-active" type="checkbox"
                                               data-id="{{ $config->id }}"
                                               {{ $config->is_active ? 'checked' : '' }}>
                                    </div>
                                </td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-outline-dark me-1"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editModal"
                                            data-id="{{ $config->id }}"
                                            data-series-type="{{ $config->series_type }}"
                                            data-category="{{ $config->category }}"
                                            data-image="{{ $config->image }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('admin.master.series.configurations.destroy', $config->id) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Delete this configuration?');">
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
                                <td colspan="6" class="text-center text-muted py-5">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                                    No configurations found.
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
        <form action="{{ route('admin.master.series.configurations.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header" style="background: var(--vip-primary); color: #fff;">
                    <h5 class="modal-title"><i class="bi bi-plus-lg me-2"></i>Add Configuration</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Series Type</label>
                        <input type="text" name="series_type" class="form-control" required placeholder="e.g. SH">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Category</label>
                        <input type="text" name="category" class="form-control" placeholder="e.g. Slider, Hung">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Image URL</label>
                        <input type="text" name="image" class="form-control" placeholder="https://...">
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
                    <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Configuration</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Series Type</label>
                        <input type="text" name="series_type" id="edit_series_type" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Category</label>
                        <input type="text" name="category" id="edit_category" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Image URL</label>
                        <input type="text" name="image" id="edit_image" class="form-control">
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

{{-- Import Modal --}}
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('admin.master.series.configurations.import') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header" style="background: var(--vip-primary); color: #fff;">
                    <h5 class="modal-title"><i class="bi bi-upload me-2"></i>Import Configurations</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">Paste CSV data below. Each line: <code>series_type, category, image_url</code></p>
                    <textarea name="import_data" class="form-control font-monospace" rows="10" required
                              placeholder="SH,Slider,&#10;DH,Hung,&#10;CAS,Casement,"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-vip"><i class="bi bi-upload me-1"></i> Import</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Populate edit modal
    document.getElementById('editModal').addEventListener('show.bs.modal', function (event) {
        const btn = event.relatedTarget;
        document.getElementById('editForm').action = '{{ url("admin/master/series/configurations") }}/' + btn.getAttribute('data-id');
        document.getElementById('edit_series_type').value = btn.getAttribute('data-series-type');
        document.getElementById('edit_category').value = btn.getAttribute('data-category');
        document.getElementById('edit_image').value = btn.getAttribute('data-image');
    });

    // Toggle active via AJAX
    document.querySelectorAll('.toggle-active').forEach(function (el) {
        el.addEventListener('change', function () {
            const id = this.getAttribute('data-id');
            fetch('{{ url("admin/master/series/configurations") }}/' + id + '/toggle-active', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            }).then(r => r.json()).catch(() => {
                this.checked = !this.checked;
                alert('Failed to update status.');
            });
        });
    });

    // Client-side filtering
    const categoryFilter = document.getElementById('categoryFilter');
    const searchFilter = document.getElementById('searchFilter');
    function filterRows() {
        const cat = categoryFilter.value.toLowerCase();
        const search = searchFilter.value.toLowerCase();
        document.querySelectorAll('#configTable tbody tr[data-category]').forEach(function (row) {
            const rowCat = (row.getAttribute('data-category') || '').toLowerCase();
            const rowType = (row.getAttribute('data-series-type') || '').toLowerCase();
            const matchCat = !cat || rowCat === cat;
            const matchSearch = !search || rowType.includes(search);
            row.style.display = (matchCat && matchSearch) ? '' : 'none';
        });
    }
    categoryFilter.addEventListener('change', filterRows);
    searchFilter.addEventListener('input', filterRows);
</script>
@endpush
@endsection
