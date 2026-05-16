@extends('layouts.app')

@section('title', 'Available Colors')

@section('content')
<div class="p-4">
    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('admin.master.hub') }}" class="text-decoration-none" style="color: var(--vip-accent);">Master Data</a></li>
            <li class="breadcrumb-item"><span class="text-muted">Colors</span></li>
            <li class="breadcrumb-item active">Available Colors</li>
        </ol>
    </nav>

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--vip-primary);">
                <i class="bi bi-paint-bucket me-2" style="color: var(--vip-accent);"></i>Available Colors per Series
            </h4>
            <p class="text-muted mb-0 small">Assign which color configurations are available for each window series.</p>
        </div>
    </div>

    @if($seriesList->isEmpty())
        <div class="card">
            <div class="card-body text-center text-muted py-5">
                <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                No series found. <a href="{{ route('admin.master.series.index') }}" style="color: var(--vip-accent);">Create a series</a> first.
            </div>
        </div>
    @else
        <div class="row g-4">
            @foreach($seriesList as $series)
                @php
                    $colors = $availableColors->get($series->id, collect());
                @endphp
                <div class="col-lg-6 col-xl-4">
                    <div class="card h-100">
                        <div class="card-header d-flex align-items-center justify-content-between py-3" style="background: var(--vip-primary); color: #fff;">
                            <span class="fw-semibold">
                                <i class="bi bi-collection me-1" style="color: var(--vip-accent);"></i>
                                {{ $series->series }}
                            </span>
                            <span class="badge bg-light text-dark">{{ $colors->count() }} colors</span>
                        </div>
                        <div class="card-body">
                            @if($colors->count())
                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    @foreach($colors as $color)
                                        <span class="badge bg-light text-dark border d-flex align-items-center gap-1" style="font-size: .8rem;">
                                            <code class="text-muted">{{ $color->color_code }}</code>
                                            {{ $color->color_name }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted small mb-3">No colors assigned yet.</p>
                            @endif
                        </div>
                        <div class="card-footer bg-transparent text-end">
                            <button class="btn btn-sm btn-outline-dark edit-colors-btn"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editColorsModal"
                                    data-series-id="{{ $series->id }}"
                                    data-series-name="{{ $series->series }}"
                                    data-colors="{{ $colors->map(fn($c) => ['color_code' => $c->color_code, 'color_name' => $c->color_name])->values()->toJson() }}">
                                <i class="bi bi-pencil me-1"></i> Edit Colors
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

{{-- Edit Colors Modal --}}
<div class="modal fade" id="editColorsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="editColorsForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header" style="background: var(--vip-primary); color: #fff;">
                    <h5 class="modal-title"><i class="bi bi-paint-bucket me-2"></i>Edit Available Colors &mdash; <span id="editSeriesName"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">Add or remove color entries for this series. Each entry needs a code and name.</p>

                    <div id="colorRows"></div>

                    <button type="button" class="btn btn-sm btn-outline-dark mt-2" id="addColorRow">
                        <i class="bi bi-plus-lg me-1"></i> Add Color
                    </button>

                    {{-- Quick-add from existing configurations --}}
                    @if($configurations->count())
                        <hr>
                        <p class="text-muted small mb-2 fw-semibold">Quick add from existing color configurations:</p>
                        <div class="d-flex flex-wrap gap-1">
                            @foreach($configurations as $config)
                                <button type="button" class="btn btn-sm btn-outline-secondary quick-add-btn"
                                        data-code="{{ $config->code }}" data-name="{{ $config->name }}">
                                    {{ $config->code }} - {{ $config->name }}
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-vip"><i class="bi bi-check-lg me-1"></i> Save Colors</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    const colorRowsContainer = document.getElementById('colorRows');

    function createColorRow(code, name) {
        const idx = colorRowsContainer.children.length;
        const row = document.createElement('div');
        row.className = 'row g-2 mb-2 align-items-center color-row';
        row.innerHTML =
            '<div class="col-md-4">' +
                '<input type="text" name="colors[' + idx + '][color_code]" class="form-control form-control-sm" placeholder="Code" value="' + (code || '') + '" required>' +
            '</div>' +
            '<div class="col-md-6">' +
                '<input type="text" name="colors[' + idx + '][color_name]" class="form-control form-control-sm" placeholder="Name" value="' + (name || '') + '" required>' +
            '</div>' +
            '<div class="col-md-2 text-end">' +
                '<button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-x-lg"></i></button>' +
            '</div>';
        colorRowsContainer.appendChild(row);

        row.querySelector('.remove-row').addEventListener('click', function () {
            row.remove();
            reindexRows();
        });
    }

    function reindexRows() {
        colorRowsContainer.querySelectorAll('.color-row').forEach(function (row, idx) {
            row.querySelectorAll('input').forEach(function (input) {
                input.name = input.name.replace(/colors\[\d+\]/, 'colors[' + idx + ']');
            });
        });
    }

    document.getElementById('addColorRow').addEventListener('click', function () {
        createColorRow('', '');
    });

    // Quick-add buttons
    document.querySelectorAll('.quick-add-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            createColorRow(this.getAttribute('data-code'), this.getAttribute('data-name'));
        });
    });

    // Populate edit modal
    document.getElementById('editColorsModal').addEventListener('show.bs.modal', function (event) {
        const btn = event.relatedTarget;
        const seriesId = btn.getAttribute('data-series-id');
        const seriesName = btn.getAttribute('data-series-name');
        const colors = JSON.parse(btn.getAttribute('data-colors') || '[]');

        document.getElementById('editColorsForm').action = '{{ url("admin/master/colors/available") }}/' + seriesId;
        document.getElementById('editSeriesName').textContent = seriesName;

        // Clear existing rows
        colorRowsContainer.innerHTML = '';

        // Populate rows
        colors.forEach(function (c) {
            createColorRow(c.color_code, c.color_name);
        });

        // If empty, add one blank row
        if (!colors.length) {
            createColorRow('', '');
        }
    });
</script>
@endpush

@push('styles')
<style>
    .quick-add-btn:hover {
        background-color: var(--vip-accent);
        border-color: var(--vip-accent);
        color: #fff;
    }
</style>
@endpush
@endsection
