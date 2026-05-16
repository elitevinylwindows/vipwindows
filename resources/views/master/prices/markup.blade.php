@extends('layouts.app')

@section('title', 'Markup Management')

@section('content')
<div class="p-4">
    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('admin.master.hub') }}" class="text-decoration-none" style="color: var(--vip-accent);">Master Data</a></li>
            <li class="breadcrumb-item"><a href="#" class="text-decoration-none" style="color: var(--vip-accent);">Pricing</a></li>
            <li class="breadcrumb-item active">Markup</li>
        </ol>
    </nav>

    <div class="d-flex align-items-center justify-content-between mb-4">
        <h4 class="fw-bold mb-0" style="color: var(--vip-primary);">
            <i class="bi bi-percent me-2" style="color: var(--vip-accent);"></i>Markup Management
        </h4>
        <button class="btn btn-vip" data-bs-toggle="modal" data-bs-target="#createMarkupModal">
            <i class="bi bi-plus-lg me-1"></i> Add Markup
        </button>
    </div>

    <p class="text-muted mb-4">Set markup percentages per series. The markup is applied on top of the base price matrix values during quoting.</p>

    {{-- Markup Table --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Series</th>
                            <th class="text-end">Markup (%)</th>
                            <th class="text-muted small">Created</th>
                            <th class="text-muted small">Updated</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($markups as $i => $markup)
                            <tr>
                                <td class="ps-4 text-muted">{{ $i + 1 }}</td>
                                <td class="fw-semibold">{{ $markup->series_name ?? 'Series #' . $markup->series_id }}</td>
                                <td class="text-end">
                                    <span class="badge bg-dark fs-6">{{ number_format($markup->percentage, 2) }}%</span>
                                </td>
                                <td class="text-muted small">{{ $markup->created_at ? \Carbon\Carbon::parse($markup->created_at)->format('M d, Y') : '-' }}</td>
                                <td class="text-muted small">{{ $markup->updated_at ? \Carbon\Carbon::parse($markup->updated_at)->format('M d, Y') : '-' }}</td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-outline-dark me-1"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editMarkupModal"
                                            data-id="{{ $markup->id }}"
                                            data-series="{{ $markup->series_name }}"
                                            data-percentage="{{ $markup->percentage }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('admin.master.prices.markup.destroy', $markup->id) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Delete this markup?');">
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
                                    No markups configured. Click <strong>Add Markup</strong> to set one.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Create Markup Modal --}}
<div class="modal fade" id="createMarkupModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.master.prices.markup.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header" style="background: var(--vip-primary); color: #fff;">
                    <h5 class="modal-title"><i class="bi bi-plus-lg me-2"></i>Add Markup</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Series</label>
                        <select name="series_id" class="form-select" required>
                            <option value="">Select series...</option>
                            @foreach($series as $s)
                                <option value="{{ $s->id }}">{{ $s->series }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Markup Percentage (%)</label>
                        <input type="number" step="0.01" name="percentage" class="form-control" required placeholder="e.g. 15.00">
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

{{-- Edit Markup Modal --}}
<div class="modal fade" id="editMarkupModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="editMarkupForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header" style="background: var(--vip-primary); color: #fff;">
                    <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Markup: <span id="editMarkupSeries"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Markup Percentage (%)</label>
                        <input type="number" step="0.01" name="percentage" id="editMarkupPercentage" class="form-control" required>
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
    document.getElementById('editMarkupModal').addEventListener('show.bs.modal', function (event) {
        const btn = event.relatedTarget;
        document.getElementById('editMarkupForm').action = '{{ url("admin/master/prices/markup") }}/' + btn.getAttribute('data-id');
        document.getElementById('editMarkupSeries').textContent = btn.getAttribute('data-series');
        document.getElementById('editMarkupPercentage').value = btn.getAttribute('data-percentage');
    });
</script>
@endpush
@endsection
