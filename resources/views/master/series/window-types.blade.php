@extends('layouts.app')

@section('title', 'Window Types')

@section('content')
<div class="p-4">
    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('admin.master.hub') }}" class="text-decoration-none" style="color: var(--vip-accent);">Master Data</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.master.series.index') }}" class="text-decoration-none" style="color: var(--vip-accent);">Series</a></li>
            <li class="breadcrumb-item active">Window Types</li>
        </ol>
    </nav>

    <div class="d-flex align-items-center justify-content-between mb-4">
        <h4 class="fw-bold mb-0" style="color: var(--vip-primary);">
            <i class="bi bi-window me-2" style="color: var(--vip-accent);"></i>Window Type Assignments
        </h4>
    </div>

    <p class="text-muted mb-4">Assign available window types (e.g. SH, DH, CAS, PW, AW) to each series. Click a series to edit its window types.</p>

    <div class="row g-4">
        @forelse($series as $s)
            @php
                $types = isset($windowTypesBySeries[$s->id]) ? $windowTypesBySeries[$s->id] : collect();
            @endphp
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between py-3" style="background: var(--vip-primary); color: #fff;">
                        <span class="fw-semibold"><i class="bi bi-collection me-2" style="color: var(--vip-accent);"></i>{{ $s->series }}</span>
                        <button class="btn btn-sm btn-outline-light"
                                data-bs-toggle="modal"
                                data-bs-target="#editWTModal"
                                data-series-id="{{ $s->id }}"
                                data-series-name="{{ $s->series }}"
                                data-window-types="{{ $types->pluck('window_type')->implode(',') }}">
                            <i class="bi bi-pencil me-1"></i> Edit
                        </button>
                    </div>
                    <div class="card-body">
                        @if($types->isEmpty())
                            <span class="text-muted fst-italic">No window types assigned</span>
                        @else
                            @foreach($types as $wt)
                                <span class="badge bg-dark me-1 mb-1 px-3 py-2">{{ $wt->window_type }}</span>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center text-muted py-5">
                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                    No series found. <a href="{{ route('admin.master.series.index') }}">Create a series</a> first.
                </div>
            </div>
        @endforelse
    </div>
</div>

{{-- Edit Window Types Modal --}}
<div class="modal fade" id="editWTModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="editWTForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header" style="background: var(--vip-primary); color: #fff;">
                    <h5 class="modal-title"><i class="bi bi-window me-2"></i>Edit Window Types: <span id="editWTSeriesName"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">Enter one window type per line (e.g. SH, DH, CAS, PW, AW, FIX).</p>
                    <textarea name="window_types_raw" id="editWTTextarea" class="form-control font-monospace" rows="8"
                              placeholder="SH&#10;DH&#10;CAS"></textarea>
                    {{-- Hidden inputs will be created by JS --}}
                    <div id="editWTHiddenInputs"></div>
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
    const editWTModal = document.getElementById('editWTModal');
    editWTModal.addEventListener('show.bs.modal', function (event) {
        const btn = event.relatedTarget;
        const seriesId = btn.getAttribute('data-series-id');
        const seriesName = btn.getAttribute('data-series-name');
        const windowTypes = btn.getAttribute('data-window-types');

        document.getElementById('editWTForm').action = '{{ url("admin/master/series/window-types") }}/' + seriesId;
        document.getElementById('editWTSeriesName').textContent = seriesName;
        document.getElementById('editWTTextarea').value = windowTypes ? windowTypes.split(',').join('\n') : '';
    });

    // Convert textarea lines to hidden array inputs before submit
    document.getElementById('editWTForm').addEventListener('submit', function (e) {
        const container = document.getElementById('editWTHiddenInputs');
        container.innerHTML = '';
        const lines = document.getElementById('editWTTextarea').value.split('\n');
        lines.forEach(function (line) {
            line = line.trim();
            if (line) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'window_types[]';
                input.value = line;
                container.appendChild(input);
            }
        });
    });
</script>
@endpush
@endsection
