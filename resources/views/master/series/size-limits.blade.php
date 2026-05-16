@extends('layouts.app')

@section('title', 'Size Limits')

@section('content')
<div class="p-4">
    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('admin.master.hub') }}" class="text-decoration-none" style="color: var(--vip-accent);">Master Data</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.master.series.index') }}" class="text-decoration-none" style="color: var(--vip-accent);">Series</a></li>
            <li class="breadcrumb-item active">Size Limits</li>
        </ol>
    </nav>

    <div class="d-flex align-items-center justify-content-between mb-4">
        <h4 class="fw-bold mb-0" style="color: var(--vip-primary);">
            <i class="bi bi-rulers me-2" style="color: var(--vip-accent);"></i>Size Limits
        </h4>
    </div>

    <p class="text-muted mb-4">Set minimum/maximum width, height, and united inches (UI) for each series. These limits are used during quote configuration to validate window sizes.</p>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Series</th>
                            <th class="text-center">Min Width</th>
                            <th class="text-center">Max Width</th>
                            <th class="text-center">Min Height</th>
                            <th class="text-center">Max Height</th>
                            <th class="text-center">Max UI</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($series as $s)
                            @php
                                $limits = isset($sizeLimitsBySeries[$s->id]) ? $sizeLimitsBySeries[$s->id]->first() : null;
                            @endphp
                            <tr>
                                <td class="ps-4 fw-semibold">{{ $s->series }}</td>
                                <td class="text-center">{{ $limits->min_width ?? '-' }}</td>
                                <td class="text-center">{{ $limits->max_width ?? '-' }}</td>
                                <td class="text-center">{{ $limits->min_height ?? '-' }}</td>
                                <td class="text-center">{{ $limits->max_height ?? '-' }}</td>
                                <td class="text-center">{{ $limits->max_ui ?? '-' }}</td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-outline-dark"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editLimitModal"
                                            data-series-id="{{ $s->id }}"
                                            data-series-name="{{ $s->series }}"
                                            data-min-width="{{ $limits->min_width ?? '' }}"
                                            data-max-width="{{ $limits->max_width ?? '' }}"
                                            data-min-height="{{ $limits->min_height ?? '' }}"
                                            data-max-height="{{ $limits->max_height ?? '' }}"
                                            data-max-ui="{{ $limits->max_ui ?? '' }}">
                                        <i class="bi bi-pencil me-1"></i> Edit
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                                    No series found. <a href="{{ route('admin.master.series.index') }}">Create a series</a> first.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Edit Size Limits Modal --}}
<div class="modal fade" id="editLimitModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="editLimitForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header" style="background: var(--vip-primary); color: #fff;">
                    <h5 class="modal-title"><i class="bi bi-rulers me-2"></i>Size Limits: <span id="editLimitSeriesName"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Min Width</label>
                            <input type="number" step="0.01" name="min_width" id="edit_min_width" class="form-control" placeholder="e.g. 12">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Max Width</label>
                            <input type="number" step="0.01" name="max_width" id="edit_max_width" class="form-control" placeholder="e.g. 72">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Min Height</label>
                            <input type="number" step="0.01" name="min_height" id="edit_min_height" class="form-control" placeholder="e.g. 12">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Max Height</label>
                            <input type="number" step="0.01" name="max_height" id="edit_max_height" class="form-control" placeholder="e.g. 96">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Max UI (United Inches)</label>
                            <input type="number" step="0.01" name="max_ui" id="edit_max_ui" class="form-control" placeholder="e.g. 140">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-vip"><i class="bi bi-check-lg me-1"></i> Save</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('editLimitModal').addEventListener('show.bs.modal', function (event) {
        const btn = event.relatedTarget;
        const seriesId = btn.getAttribute('data-series-id');

        document.getElementById('editLimitForm').action = '{{ url("admin/master/series/size-limits") }}/' + seriesId;
        document.getElementById('editLimitSeriesName').textContent = btn.getAttribute('data-series-name');
        document.getElementById('edit_min_width').value = btn.getAttribute('data-min-width');
        document.getElementById('edit_max_width').value = btn.getAttribute('data-max-width');
        document.getElementById('edit_min_height').value = btn.getAttribute('data-min-height');
        document.getElementById('edit_max_height').value = btn.getAttribute('data-max-height');
        document.getElementById('edit_max_ui').value = btn.getAttribute('data-max-ui');
    });
</script>
@endpush
@endsection
