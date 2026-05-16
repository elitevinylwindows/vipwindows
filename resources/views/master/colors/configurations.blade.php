@extends('layouts.app')

@section('title', 'Color Configurations')

@section('content')
<div class="p-4">
    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('admin.master.hub') }}" class="text-decoration-none" style="color: var(--vip-accent);">Master Data</a></li>
            <li class="breadcrumb-item"><span class="text-muted">Colors</span></li>
            <li class="breadcrumb-item active">Color Configurations</li>
        </ol>
    </nav>

    <div class="d-flex align-items-center justify-content-between mb-4">
        <h4 class="fw-bold mb-0" style="color: var(--vip-primary);">
            <i class="bi bi-sliders me-2" style="color: var(--vip-accent);"></i>Color Configurations
        </h4>
        <button class="btn btn-vip" data-bs-toggle="modal" data-bs-target="#createModal">
            <i class="bi bi-plus-lg me-1"></i> Add Configuration
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

    {{-- Configurations Table --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="configTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Exterior Side</th>
                            <th>Interior Side</th>
                            <th>Created</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($configurations as $i => $config)
                            @php
                                $extColor = $config->exteriorSide();
                                $intColor = $config->interiorSide();
                            @endphp
                            <tr data-search="{{ strtolower($config->code . ' ' . $config->name) }}">
                                <td class="ps-4 text-muted">{{ $i + 1 }}</td>
                                <td><code>{{ $config->code }}</code></td>
                                <td class="fw-semibold">{{ $config->name }}</td>
                                <td>
                                    @if($extColor)
                                        <span class="d-inline-flex align-items-center gap-1">
                                            @if($extColor->hex_color)
                                                <span style="display:inline-block;width:14px;height:14px;border-radius:3px;background:{{ $extColor->hex_color }};border:1px solid rgba(0,0,0,.15);"></span>
                                            @endif
                                            {{ $extColor->name }}
                                            <span class="badge bg-secondary small">{{ $config->exterior_source }}</span>
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($intColor)
                                        <span class="d-inline-flex align-items-center gap-1">
                                            @if($intColor->hex_color)
                                                <span style="display:inline-block;width:14px;height:14px;border-radius:3px;background:{{ $intColor->hex_color }};border:1px solid rgba(0,0,0,.15);"></span>
                                            @endif
                                            {{ $intColor->name }}
                                            <span class="badge bg-secondary small">{{ $config->interior_source }}</span>
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-muted small">{{ $config->created_at ? $config->created_at->format('M d, Y') : '-' }}</td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-outline-dark me-1"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editModal"
                                            data-id="{{ $config->id }}"
                                            data-name="{{ $config->name }}"
                                            data-code="{{ $config->code }}"
                                            data-exterior-source="{{ $config->exterior_source }}"
                                            data-exterior-color-id="{{ $config->exterior_color_id }}"
                                            data-interior-source="{{ $config->interior_source }}"
                                            data-interior-color-id="{{ $config->interior_color_id }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('admin.master.colors.configurations.destroy', $config->id) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Delete this configuration? This cannot be undone.');">
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
                                    No color configurations found. Click <strong>Add Configuration</strong> to create one.
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
    <div class="modal-dialog modal-lg">
        <form action="{{ route('admin.master.colors.configurations.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header" style="background: var(--vip-primary); color: #fff;">
                    <h5 class="modal-title"><i class="bi bi-plus-lg me-2"></i>Add Color Configuration</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Code</label>
                            <input type="text" name="code" class="form-control" required placeholder="e.g. WH-WH">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Name</label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g. White / White">
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-semibold mb-3"><i class="bi bi-brush me-1"></i> Exterior Side</h6>
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Source</label>
                                <select name="exterior_source" class="form-select source-select" data-target="create_exterior_color_id" required>
                                    <option value="exterior">Exterior Color</option>
                                    <option value="laminate">Laminate Color</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Color</label>
                                <select name="exterior_color_id" id="create_exterior_color_id" class="form-select" required>
                                    <optgroup label="Exterior Colors" data-source="exterior">
                                        @foreach($exteriorColors as $c)
                                            <option value="{{ $c->id }}" data-source="exterior">{{ $c->code }} - {{ $c->name }}</option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="Laminate Colors" data-source="laminate" style="display:none;">
                                        @foreach($laminateColors as $c)
                                            <option value="{{ $c->id }}" data-source="laminate" disabled>{{ $c->code }} - {{ $c->name }}</option>
                                        @endforeach
                                    </optgroup>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-semibold mb-3"><i class="bi bi-house me-1"></i> Interior Side</h6>
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Source</label>
                                <select name="interior_source" class="form-select source-select" data-target="create_interior_color_id" required>
                                    <option value="interior">Interior Color</option>
                                    <option value="laminate">Laminate Color</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Color</label>
                                <select name="interior_color_id" id="create_interior_color_id" class="form-select" required>
                                    <optgroup label="Interior Colors" data-source="interior">
                                        @foreach($interiorColors as $c)
                                            <option value="{{ $c->id }}" data-source="interior">{{ $c->code }} - {{ $c->name }}</option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="Laminate Colors" data-source="laminate" style="display:none;">
                                        @foreach($laminateColors as $c)
                                            <option value="{{ $c->id }}" data-source="laminate" disabled>{{ $c->code }} - {{ $c->name }}</option>
                                        @endforeach
                                    </optgroup>
                                </select>
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
    <div class="modal-dialog modal-lg">
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header" style="background: var(--vip-primary); color: #fff;">
                    <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Color Configuration</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Code</label>
                            <input type="text" name="code" id="edit_code" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Name</label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-semibold mb-3"><i class="bi bi-brush me-1"></i> Exterior Side</h6>
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Source</label>
                                <select name="exterior_source" id="edit_exterior_source" class="form-select source-select" data-target="edit_exterior_color_id" required>
                                    <option value="exterior">Exterior Color</option>
                                    <option value="laminate">Laminate Color</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Color</label>
                                <select name="exterior_color_id" id="edit_exterior_color_id" class="form-select" required>
                                    <optgroup label="Exterior Colors" data-source="exterior">
                                        @foreach($exteriorColors as $c)
                                            <option value="{{ $c->id }}" data-source="exterior">{{ $c->code }} - {{ $c->name }}</option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="Laminate Colors" data-source="laminate" style="display:none;">
                                        @foreach($laminateColors as $c)
                                            <option value="{{ $c->id }}" data-source="laminate" disabled>{{ $c->code }} - {{ $c->name }}</option>
                                        @endforeach
                                    </optgroup>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-semibold mb-3"><i class="bi bi-house me-1"></i> Interior Side</h6>
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Source</label>
                                <select name="interior_source" id="edit_interior_source" class="form-select source-select" data-target="edit_interior_color_id" required>
                                    <option value="interior">Interior Color</option>
                                    <option value="laminate">Laminate Color</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Color</label>
                                <select name="interior_color_id" id="edit_interior_color_id" class="form-select" required>
                                    <optgroup label="Interior Colors" data-source="interior">
                                        @foreach($interiorColors as $c)
                                            <option value="{{ $c->id }}" data-source="interior">{{ $c->code }} - {{ $c->name }}</option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="Laminate Colors" data-source="laminate" style="display:none;">
                                        @foreach($laminateColors as $c)
                                            <option value="{{ $c->id }}" data-source="laminate" disabled>{{ $c->code }} - {{ $c->name }}</option>
                                        @endforeach
                                    </optgroup>
                                </select>
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
    /**
     * Toggle options in a color select based on the chosen source (exterior/interior vs laminate).
     */
    function applySourceFilter(sourceSelect) {
        const targetId = sourceSelect.getAttribute('data-target');
        const colorSelect = document.getElementById(targetId);
        if (!colorSelect) return;

        const source = sourceSelect.value;

        colorSelect.querySelectorAll('option[data-source]').forEach(function (opt) {
            if (opt.getAttribute('data-source') === source) {
                opt.disabled = false;
                opt.style.display = '';
            } else {
                opt.disabled = true;
                opt.style.display = 'none';
                opt.selected = false;
            }
        });

        colorSelect.querySelectorAll('optgroup[data-source]').forEach(function (grp) {
            grp.style.display = grp.getAttribute('data-source') === source ? '' : 'none';
        });

        // Select first available option
        const firstAvailable = colorSelect.querySelector('option[data-source="' + source + '"]:not([disabled])');
        if (firstAvailable) firstAvailable.selected = true;
    }

    // Bind source selects
    document.querySelectorAll('.source-select').forEach(function (sel) {
        sel.addEventListener('change', function () { applySourceFilter(this); });
        applySourceFilter(sel);
    });

    // Populate edit modal
    document.getElementById('editModal').addEventListener('show.bs.modal', function (event) {
        const btn = event.relatedTarget;
        document.getElementById('editForm').action = '{{ url("admin/master/colors/configurations") }}/' + btn.getAttribute('data-id');
        document.getElementById('edit_code').value = btn.getAttribute('data-code');
        document.getElementById('edit_name').value = btn.getAttribute('data-name');

        const extSource = btn.getAttribute('data-exterior-source') || 'exterior';
        const extColorId = btn.getAttribute('data-exterior-color-id');
        const intSource = btn.getAttribute('data-interior-source') || 'interior';
        const intColorId = btn.getAttribute('data-interior-color-id');

        const extSourceSel = document.getElementById('edit_exterior_source');
        extSourceSel.value = extSource;
        applySourceFilter(extSourceSel);
        if (extColorId) document.getElementById('edit_exterior_color_id').value = extColorId;

        const intSourceSel = document.getElementById('edit_interior_source');
        intSourceSel.value = intSource;
        applySourceFilter(intSourceSel);
        if (intColorId) document.getElementById('edit_interior_color_id').value = intColorId;
    });

    // Search filter
    document.getElementById('searchFilter').addEventListener('input', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll('#configTable tbody tr[data-search]').forEach(function (row) {
            row.style.display = !q || row.getAttribute('data-search').includes(q) ? '' : 'none';
        });
    });
</script>
@endpush
@endsection
