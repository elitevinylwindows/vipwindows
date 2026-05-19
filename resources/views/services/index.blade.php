@extends('layouts.app')
@section('title', 'Services')

@push('styles')
<style>
    .svc-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.25rem; }
    .svc-card {
        background: #fff; border-radius: .75rem; overflow: hidden;
        box-shadow: 0 1px 6px rgba(0,0,0,.06); transition: transform .15s;
    }
    .svc-card:hover { transform: translateY(-2px); }
    .svc-card-header {
        padding: 1rem 1.25rem; display: flex; align-items: center; gap: .75rem;
        border-bottom: 1px solid rgba(0,0,0,.04);
    }
    .svc-dot { width: 12px; height: 12px; border-radius: 50%; flex-shrink: 0; }
    .svc-card-header h6 { margin: 0; font-size: 1rem; font-weight: 700; }
    .svc-card-header .badge { font-size: .65rem; }
    .svc-card-body { padding: 1.25rem; }
    .svc-field { margin-bottom: .75rem; }
    .svc-field label { font-size: .7rem; text-transform: uppercase; letter-spacing: .5px; color: rgba(0,0,0,.45); display: block; margin-bottom: 2px; }
    .svc-field .val { font-size: 1rem; font-weight: 600; color: #111; }
    .svc-field .val.gold { color: var(--vip-accent); }
    .svc-field .val.red { color: #dc3545; }
    .svc-field .val.green { color: #198754; }

    /* Install types table */
    .install-types-section { margin-top: 2rem; }
    .it-table { width: 100%; border-collapse: separate; border-spacing: 0; }
    .it-table thead th {
        font-size: .7rem; text-transform: uppercase; letter-spacing: .5px;
        color: rgba(0,0,0,.5); padding: .6rem 1rem; border-bottom: 2px solid rgba(0,0,0,.08);
        white-space: nowrap;
    }
    .it-table tbody td {
        padding: .75rem 1rem; font-size: .9rem; border-bottom: 1px solid rgba(0,0,0,.04);
        vertical-align: middle;
    }
    .it-table tbody tr:hover { background: rgba(201,168,76,.03); }
    .profit-bar {
        height: 6px; border-radius: 3px; background: #eee; overflow: hidden; width: 60px; display: inline-block; vertical-align: middle; margin-left: .5rem;
    }
    .profit-bar-fill { height: 100%; background: #198754; border-radius: 3px; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-wrench me-2"></i>Services</h4>
            <p class="text-muted small mb-0">4 core services. Tech Measure, Service, and Repair are billed hourly. Installation is billed per unit with defined install types below.</p>
        </div>
    </div>

    {{-- Service Cards Grid --}}
    <div class="svc-grid mb-4">
        @foreach($services as $service)
            <div class="svc-card">
                <div class="svc-card-header">
                    <span class="svc-dot" style="background:{{ $service->color ?? '#0d6efd' }}"></span>
                    <h6>{{ $service->name }}</h6>
                    <span class="badge {{ $service->is_active ? 'bg-success' : 'bg-secondary' }} ms-auto">
                        {{ $service->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <div class="svc-card-body">
                    @if($service->description)
                        <p class="text-muted small mb-3">{{ $service->description }}</p>
                    @endif

                    <div class="row g-3">
                        <div class="col-6">
                            <div class="svc-field">
                                <label>Billing</label>
                                <div class="val">{{ ucwords(str_replace('_', ' ', $service->unit)) }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="svc-field">
                                <label>Hourly Rate</label>
                                <div class="val gold">${{ number_format($service->base_price, 2) }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="svc-field">
                                <label>Installer Pay</label>
                                <div class="val red">
                                    @if($service->installer_pay_type === 'percentage')
                                        {{ number_format($service->installer_pay, 1) }}%
                                    @else
                                        ${{ number_format($service->installer_pay, 2) }}
                                    @endif
                                    <span class="text-muted" style="font-size:.7rem; font-weight:400;">{{ str_replace('_', ' ', $service->installer_pay_type) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="svc-field">
                                <label>Profit / hr</label>
                                @php
                                    $profit = $service->installer_pay_type === 'percentage'
                                        ? $service->base_price - ($service->base_price * $service->installer_pay / 100)
                                        : $service->base_price - $service->installer_pay;
                                @endphp
                                <div class="val green">${{ number_format($profit, 2) }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="text-end mt-2">
                        <button class="btn btn-sm btn-outline-secondary" onclick="editService({{ $service->id }})">
                            <i class="bi bi-pencil me-1"></i> Edit
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Installation Types Section --}}
    <div class="install-types-section">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <div>
                    <h5 class="mb-0 fw-bold"><i class="bi bi-grid-3x3-gap me-2 text-success"></i>Installation Types</h5>
                    <p class="text-muted small mb-0 mt-1">Define each type of installation, what you charge per unit, what the installer gets paid, and your profit margin.</p>
                </div>
                <button class="btn btn-vip" data-bs-toggle="modal" data-bs-target="#addInstallTypeModal">
                    <i class="bi bi-plus-circle me-1"></i> Add Type
                </button>
            </div>
            <div class="card-body p-0">
                @if($installTypes->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-grid-3x3-gap fs-1 d-block mb-2"></i>
                        No installation types defined yet. Add your first one.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="it-table">
                            <thead>
                                <tr>
                                    <th>Install Type</th>
                                    <th>Description</th>
                                    <th class="text-end">Price / Unit</th>
                                    <th class="text-end">Installer Pay / Unit</th>
                                    <th class="text-end">Profit / Unit</th>
                                    <th class="text-end">Margin</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($installTypes as $type)
                                    @php
                                        $typeProfit = $type->profit();
                                        $typeMargin = $type->marginPercent();
                                    @endphp
                                    <tr>
                                        <td class="fw-semibold">{{ $type->name }}</td>
                                        <td class="text-muted small">{{ $type->description ?? '—' }}</td>
                                        <td class="text-end fw-semibold" style="color:var(--vip-accent);">${{ number_format($type->price, 2) }}</td>
                                        <td class="text-end fw-semibold text-danger">${{ number_format($type->installer_pay, 2) }}</td>
                                        <td class="text-end fw-semibold text-success">${{ number_format($typeProfit, 2) }}</td>
                                        <td class="text-end">
                                            <span class="fw-semibold {{ $typeMargin >= 30 ? 'text-success' : ($typeMargin >= 15 ? 'text-warning' : 'text-danger') }}">
                                                {{ $typeMargin }}%
                                            </span>
                                            <div class="profit-bar">
                                                <div class="profit-bar-fill" style="width:{{ min($typeMargin, 100) }}%;background:{{ $typeMargin >= 30 ? '#198754' : ($typeMargin >= 15 ? '#ffc107' : '#dc3545') }};"></div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $type->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $type->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-outline-secondary" onclick="editInstallType({{ $type->id }}, '{{ addslashes($type->name) }}', '{{ addslashes($type->description) }}', {{ $type->price }}, {{ $type->installer_pay }}, {{ $type->is_active ? 'true' : 'false' }}, {{ $type->sort_order }})">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form method="POST" action="{{ route('admin.services.installTypes.destroy', $type->id) }}" class="d-inline" onsubmit="return confirm('Delete this install type?')">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-light">
                                    <td colspan="2" class="fw-bold small text-muted">AVERAGES ({{ $installTypes->count() }} types)</td>
                                    <td class="text-end fw-bold" style="color:var(--vip-accent);">${{ number_format($installTypes->avg('price'), 2) }}</td>
                                    <td class="text-end fw-bold text-danger">${{ number_format($installTypes->avg('installer_pay'), 2) }}</td>
                                    <td class="text-end fw-bold text-success">${{ number_format($installTypes->avg('price') - $installTypes->avg('installer_pay'), 2) }}</td>
                                    <td colspan="3"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Add Installation Type Modal --}}
<div class="modal fade" id="addInstallTypeModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.services.installTypes.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-1"></i> Add Installation Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Type Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. Window Install, Door Install, Screen Install">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Optional description..."></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Price per Unit (Customer) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="price" class="form-control" step="0.01" min="0" required id="addItPrice" oninput="calcAddProfit()">
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Installer Pay per Unit <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="installer_pay" class="form-control" step="0.01" min="0" required id="addItPay" oninput="calcAddProfit()">
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 p-3 rounded" style="background:#f0f9f0; border:1px solid rgba(25,135,84,.15);" id="addItProfitPreview">
                        <div class="d-flex justify-content-between">
                            <span class="small text-muted">Profit per unit:</span>
                            <span class="fw-bold text-success" id="addItProfitVal">$0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <span class="small text-muted">Margin:</span>
                            <span class="fw-bold" id="addItMarginVal">0%</span>
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-vip"><i class="bi bi-plus-circle me-1"></i> Add Type</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Edit Installation Type Modal --}}
<div class="modal fade" id="editInstallTypeModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="editItForm">
            @csrf @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil me-1"></i> Edit Installation Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Type Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editItName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="editItDesc" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Price per Unit (Customer) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="price" id="editItPrice" class="form-control" step="0.01" min="0" required oninput="calcEditProfit()">
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Installer Pay per Unit <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="installer_pay" id="editItPay" class="form-control" step="0.01" min="0" required oninput="calcEditProfit()">
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 p-3 rounded" style="background:#f0f9f0; border:1px solid rgba(25,135,84,.15);">
                        <div class="d-flex justify-content-between">
                            <span class="small text-muted">Profit per unit:</span>
                            <span class="fw-bold text-success" id="editItProfitVal">$0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <span class="small text-muted">Margin:</span>
                            <span class="fw-bold" id="editItMarginVal">0%</span>
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-6">
                            <label class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" id="editItSort" class="form-control">
                        </div>
                        <div class="col-6 d-flex align-items-end">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" value="1" class="form-check-input" id="editItActive">
                                <label class="form-check-label" for="editItActive">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-vip">Save Changes</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Edit Service Modal (for the 4 core services) --}}
<div class="modal fade" id="editServiceModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="editServiceForm">
            @csrf @method('PUT')
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Edit Service</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-8"><label class="form-label">Service Name</label><input type="text" name="name" id="editSvcName" class="form-control" required></div>
                        <div class="col-4">
                            <label class="form-label">Color</label>
                            <input type="color" name="color" id="editSvcColor" class="form-control form-control-color" value="#0d6efd">
                        </div>
                        <div class="col-12"><label class="form-label">Description</label><textarea name="description" id="editSvcDesc" class="form-control" rows="2"></textarea></div>
                        <div class="col-4"><label class="form-label">Base Price / Hr *</label><input type="number" name="base_price" id="editSvcBasePrice" class="form-control" step="0.01" min="0" required></div>
                        <div class="col-4"><label class="form-label">Installer Pay / Hr</label><input type="number" name="installer_pay" id="editSvcInstallerPay" class="form-control" step="0.01" min="0"></div>
                        <div class="col-4">
                            <label class="form-label">Unit</label>
                            <select name="unit" id="editSvcUnit" class="form-select">
                                <option value="per_hour">Per Hour</option>
                                <option value="per_job">Per Job</option>
                                <option value="per_unit">Per Unit</option>
                            </select>
                        </div>
                        <input type="hidden" name="installer_pay_type" id="editSvcInstallerPayType" value="per_hour">
                        <input type="hidden" name="cost_price" value="0">
                        <input type="hidden" name="sort_order" id="editSvcSortOrder" value="0">
                        <div class="col-12 d-flex align-items-end">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" value="1" class="form-check-input" id="editSvcActive">
                                <label class="form-check-label" for="editSvcActive">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-vip">Save Changes</button></div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Edit service (core 4)
function editService(id) {
    fetch(`/admin/services/${id}`, { headers: { 'Accept': 'application/json' }})
        .then(r => r.json())
        .then(data => {
            const svc = data.service;
            document.getElementById('editServiceForm').action = `/admin/services/${svc.id}`;
            document.getElementById('editSvcName').value = svc.name;
            document.getElementById('editSvcDesc').value = svc.description || '';
            document.getElementById('editSvcBasePrice').value = svc.base_price;
            document.getElementById('editSvcUnit').value = svc.unit;
            document.getElementById('editSvcColor').value = svc.color || '#0d6efd';
            document.getElementById('editSvcInstallerPay').value = svc.installer_pay || '';
            document.getElementById('editSvcInstallerPayType').value = svc.installer_pay_type || 'per_hour';
            document.getElementById('editSvcSortOrder').value = svc.sort_order || 0;
            document.getElementById('editSvcActive').checked = svc.is_active;
            new bootstrap.Modal(document.getElementById('editServiceModal')).show();
        });
}

// Edit installation type
function editInstallType(id, name, desc, price, pay, active, sort) {
    document.getElementById('editItForm').action = `/admin/services/install-types/${id}`;
    document.getElementById('editItName').value = name;
    document.getElementById('editItDesc').value = desc || '';
    document.getElementById('editItPrice').value = price;
    document.getElementById('editItPay').value = pay;
    document.getElementById('editItActive').checked = active;
    document.getElementById('editItSort').value = sort;
    calcEditProfit();
    new bootstrap.Modal(document.getElementById('editInstallTypeModal')).show();
}

// Profit calculators
function calcAddProfit() {
    const price = parseFloat(document.getElementById('addItPrice').value) || 0;
    const pay = parseFloat(document.getElementById('addItPay').value) || 0;
    const profit = price - pay;
    const margin = price > 0 ? ((profit / price) * 100).toFixed(1) : '0.0';
    document.getElementById('addItProfitVal').textContent = '$' + profit.toFixed(2);
    const mEl = document.getElementById('addItMarginVal');
    mEl.textContent = margin + '%';
    mEl.className = 'fw-bold ' + (margin >= 30 ? 'text-success' : margin >= 15 ? 'text-warning' : 'text-danger');
}

function calcEditProfit() {
    const price = parseFloat(document.getElementById('editItPrice').value) || 0;
    const pay = parseFloat(document.getElementById('editItPay').value) || 0;
    const profit = price - pay;
    const margin = price > 0 ? ((profit / price) * 100).toFixed(1) : '0.0';
    document.getElementById('editItProfitVal').textContent = '$' + profit.toFixed(2);
    const mEl = document.getElementById('editItMarginVal');
    mEl.textContent = margin + '%';
    mEl.className = 'fw-bold ' + (margin >= 30 ? 'text-success' : margin >= 15 ? 'text-warning' : 'text-danger');
}
</script>
@endpush
