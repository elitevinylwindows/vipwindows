@extends('layouts.app')

@section('title', 'Price Matrix')

@section('content')
<div class="p-4">
    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('admin.master.hub') }}" class="text-decoration-none" style="color: var(--vip-accent);">Master Data</a></li>
            <li class="breadcrumb-item"><a href="#" class="text-decoration-none" style="color: var(--vip-accent);">Pricing</a></li>
            <li class="breadcrumb-item active">Price Matrix</li>
        </ol>
    </nav>

    <div class="d-flex align-items-center justify-content-between mb-4">
        <h4 class="fw-bold mb-0" style="color: var(--vip-primary);">
            <i class="bi bi-table me-2" style="color: var(--vip-accent);"></i>Price Matrix
        </h4>
        <button class="btn btn-vip" data-bs-toggle="modal" data-bs-target="#createPriceModal">
            <i class="bi bi-plus-lg me-1"></i> Add Price
        </button>
    </div>

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-body py-3">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Series</label>
                    <select id="filterSeries" class="form-select form-select-sm">
                        <option value="">All Series</option>
                        @foreach($series as $s)
                            <option value="{{ $s->id }}">{{ $s->series }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Window Type</label>
                    <select id="filterType" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        @foreach($seriesTypes as $st)
                            <option value="{{ $st->id }}">{{ $st->series_type }} ({{ optional($series->firstWhere('id', $st->series_id))->series }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-sm btn-outline-dark w-100" onclick="loadPrices()">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Price Table --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="priceTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Series</th>
                            <th>Type</th>
                            <th class="text-end">Width</th>
                            <th class="text-end">Height</th>
                            <th class="text-end">Price ($)</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="priceBody">
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="bi bi-funnel fs-1 d-block mb-2 opacity-25"></i>
                                Select a series and/or type, then click <strong>Filter</strong> to load prices.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Create Price Modal --}}
<div class="modal fade" id="createPriceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--vip-primary); color: #fff;">
                <h5 class="modal-title"><i class="bi bi-plus-lg me-2"></i>Add Price Entry</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createPriceForm">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Series</label>
                            <select name="series_id" class="form-select" required>
                                <option value="">Select...</option>
                                @foreach($series as $s)
                                    <option value="{{ $s->id }}">{{ $s->series }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Window Type</label>
                            <select name="series_type_id" class="form-select" required>
                                <option value="">Select...</option>
                                @foreach($seriesTypes as $st)
                                    <option value="{{ $st->id }}">{{ $st->series_type }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-4">
                            <label class="form-label fw-semibold">Width</label>
                            <input type="number" step="0.01" name="width" class="form-control" required placeholder="e.g. 36">
                        </div>
                        <div class="col-4">
                            <label class="form-label fw-semibold">Height</label>
                            <input type="number" step="0.01" name="height" class="form-control" required placeholder="e.g. 60">
                        </div>
                        <div class="col-4">
                            <label class="form-label fw-semibold">Price ($)</label>
                            <input type="number" step="0.01" name="price" class="form-control" required placeholder="e.g. 250.00">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-vip" onclick="createPrice()"><i class="bi bi-check-lg me-1"></i> Create</button>
            </div>
        </div>
    </div>
</div>

{{-- Edit Price Modal --}}
<div class="modal fade" id="editPriceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--vip-primary); color: #fff;">
                <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Price</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editPriceForm">
                    <input type="hidden" name="id" id="editPriceId">
                    <div class="row g-3">
                        <div class="col-4">
                            <label class="form-label fw-semibold">Width</label>
                            <input type="number" step="0.01" name="width" id="editPriceWidth" class="form-control">
                        </div>
                        <div class="col-4">
                            <label class="form-label fw-semibold">Height</label>
                            <input type="number" step="0.01" name="height" id="editPriceHeight" class="form-control">
                        </div>
                        <div class="col-4">
                            <label class="form-label fw-semibold">Price ($)</label>
                            <input type="number" step="0.01" name="price" id="editPriceValue" class="form-control" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-vip" onclick="updatePrice()"><i class="bi bi-check-lg me-1"></i> Update</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const priceDataUrl = '{{ route("admin.master.prices.matrix.data") }}';
    const priceStoreUrl = '{{ route("admin.master.prices.matrix.store") }}';
    const priceBaseUrl = '{{ url("admin/master/prices/matrix") }}';
    const csrfToken = '{{ csrf_token() }}';

    function loadPrices() {
        const seriesId = document.getElementById('filterSeries').value;
        const typeId = document.getElementById('filterType').value;
        const params = new URLSearchParams();
        if (seriesId) params.set('series_id', seriesId);
        if (typeId) params.set('series_type_id', typeId);

        fetch(priceDataUrl + '?' + params.toString())
            .then(r => r.json())
            .then(prices => {
                const tbody = document.getElementById('priceBody');
                if (!prices.length) {
                    tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-5"><i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>No prices found for this filter.</td></tr>';
                    return;
                }
                tbody.innerHTML = prices.map((p, i) => `
                    <tr>
                        <td class="ps-4 text-muted">${i + 1}</td>
                        <td class="fw-semibold">${p.series_name || ''}</td>
                        <td>${p.series_type || ''}</td>
                        <td class="text-end">${p.width}</td>
                        <td class="text-end">${p.height}</td>
                        <td class="text-end fw-semibold">$${parseFloat(p.price).toFixed(2)}</td>
                        <td class="text-end pe-4">
                            <button class="btn btn-sm btn-outline-dark me-1" onclick="openEditPrice(${p.id}, ${p.width}, ${p.height}, ${p.price})">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deletePrice(${p.id})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `).join('');
            });
    }

    function createPrice() {
        const form = document.getElementById('createPriceForm');
        const data = new FormData(form);
        const body = {};
        data.forEach((v, k) => body[k] = v);

        fetch(priceStoreUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify(body)
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                bootstrap.Modal.getInstance(document.getElementById('createPriceModal')).hide();
                form.reset();
                loadPrices();
                showToast('Price entry created.');
            }
        });
    }

    function openEditPrice(id, width, height, price) {
        document.getElementById('editPriceId').value = id;
        document.getElementById('editPriceWidth').value = width;
        document.getElementById('editPriceHeight').value = height;
        document.getElementById('editPriceValue').value = price;
        new bootstrap.Modal(document.getElementById('editPriceModal')).show();
    }

    function updatePrice() {
        const id = document.getElementById('editPriceId').value;
        const body = {
            width: document.getElementById('editPriceWidth').value,
            height: document.getElementById('editPriceHeight').value,
            price: document.getElementById('editPriceValue').value,
        };

        fetch(priceBaseUrl + '/' + id, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify(body)
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                bootstrap.Modal.getInstance(document.getElementById('editPriceModal')).hide();
                loadPrices();
                showToast('Price updated.');
            }
        });
    }

    function deletePrice(id) {
        if (!confirm('Delete this price entry?')) return;
        fetch(priceBaseUrl + '/' + id, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken }
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                loadPrices();
                showToast('Price entry deleted.');
            }
        });
    }

    function showToast(msg) {
        const existing = document.getElementById('vipToast');
        if (existing) existing.remove();
        document.body.insertAdjacentHTML('beforeend', `
            <div id="vipToast" class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
                <div class="toast show align-items-center text-white border-0" style="background: var(--vip-primary);">
                    <div class="d-flex">
                        <div class="toast-body"><i class="bi bi-check-circle me-1"></i> ${msg}</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" onclick="this.closest('#vipToast').remove()"></button>
                    </div>
                </div>
            </div>
        `);
        setTimeout(() => document.getElementById('vipToast')?.remove(), 3000);
    }
</script>
@endpush
@endsection
