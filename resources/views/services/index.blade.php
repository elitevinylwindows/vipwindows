@extends('layouts.app')
@section('title', 'Services')

@push('styles')
<style>
    .svc-container { display: flex; height: calc(100vh - 56px); overflow: hidden; }

    /* ── Left Rail ─────────────────────────────── */
    .svc-rail {
        width: 320px; min-width: 320px;
        background: var(--vip-primary);
        color: #fff;
        display: flex; flex-direction: column;
        border-right: 1px solid rgba(255,255,255,.06);
    }
    .svc-rail-header { padding: 1.25rem 1rem .75rem; }
    .svc-rail-header h6 { font-size: .75rem; text-transform: uppercase; letter-spacing: 1.2px; color: rgba(255,255,255,.5); margin-bottom: .75rem; }
    .svc-rail-search {
        display: flex; gap: .5rem;
    }
    .svc-rail-search input {
        flex: 1; background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.12);
        color: #fff; border-radius: .375rem; padding: .4rem .75rem; font-size: .85rem;
    }
    .svc-rail-search input::placeholder { color: rgba(255,255,255,.4); }
    .svc-rail-search input:focus { outline: none; border-color: var(--vip-accent); }

    .svc-rail-tabs {
        display: flex; gap: 0; padding: 0 1rem; margin-top: .75rem;
    }
    .svc-rail-tabs .tab-btn {
        flex: 1; text-align: center; padding: .4rem .5rem; font-size: .75rem;
        background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.1);
        color: rgba(255,255,255,.6); cursor: pointer; transition: all .15s;
    }
    .svc-rail-tabs .tab-btn:first-child { border-radius: .3rem 0 0 .3rem; }
    .svc-rail-tabs .tab-btn:last-child { border-radius: 0 .3rem .3rem 0; }
    .svc-rail-tabs .tab-btn.active {
        background: var(--vip-accent); color: #fff; border-color: var(--vip-accent);
    }

    .svc-rail-list { flex: 1; overflow-y: auto; padding: .5rem; }
    .svc-card {
        background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.08);
        border-radius: .5rem; padding: .75rem 1rem; margin-bottom: .5rem;
        cursor: pointer; transition: all .15s;
    }
    .svc-card:hover { background: rgba(255,255,255,.08); border-color: rgba(201,168,76,.3); }
    .svc-card.active { background: rgba(201,168,76,.12); border-color: var(--vip-accent); }
    .svc-card .svc-name { font-weight: 600; font-size: .9rem; color: #fff; }
    .svc-card .svc-code { font-size: .7rem; color: rgba(255,255,255,.4); text-transform: uppercase; letter-spacing: .5px; }
    .svc-card .svc-price { font-size: .85rem; color: var(--vip-accent); font-weight: 600; margin-top: .25rem; }
    .svc-card .svc-unit { font-size: .7rem; color: rgba(255,255,255,.45); }
    .svc-card .svc-badge {
        display: inline-block; font-size: .65rem; padding: .1rem .4rem;
        border-radius: .2rem; margin-top: .25rem;
    }
    .svc-badge-active { background: rgba(40,167,69,.2); color: #28a745; }
    .svc-badge-inactive { background: rgba(220,53,69,.2); color: #dc3545; }

    .svc-rail-footer {
        padding: .75rem 1rem; border-top: 1px solid rgba(255,255,255,.08);
        font-size: .75rem; color: rgba(255,255,255,.4);
        display: flex; justify-content: space-between; align-items: center;
    }

    /* ── Main Panel ────────────────────────────── */
    .svc-main { flex: 1; overflow-y: auto; background: var(--vip-light); }
    .svc-main-toolbar {
        background: #fff; border-bottom: 1px solid rgba(0,0,0,.06);
        padding: .75rem 1.5rem; display: flex; align-items: center; justify-content: space-between;
    }
    .svc-main-toolbar h5 { font-size: 1rem; font-weight: 700; margin: 0; }
    .svc-detail-body { padding: 1.5rem; }

    .svc-empty-state {
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        height: 60vh; color: rgba(0,0,0,.35);
    }
    .svc-empty-state i { font-size: 3rem; margin-bottom: 1rem; }

    /* Info cards grid */
    .svc-info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
    .svc-info-card {
        background: #fff; border-radius: .5rem; padding: 1.25rem;
        box-shadow: 0 1px 4px rgba(0,0,0,.06);
    }
    .svc-info-card .label { font-size: .7rem; text-transform: uppercase; letter-spacing: .5px; color: rgba(0,0,0,.45); margin-bottom: .25rem; }
    .svc-info-card .value { font-size: 1rem; font-weight: 600; color: #111; word-break: break-all; }

    /* Installers table */
    .svc-installers-card {
        background: #fff; border-radius: .5rem; padding: 1.25rem;
        box-shadow: 0 1px 4px rgba(0,0,0,.06);
    }
    .svc-installers-card h6 { font-size: .8rem; text-transform: uppercase; letter-spacing: .5px; color: rgba(0,0,0,.5); margin-bottom: .75rem; }
    .svc-inst-table { width: 100%; border-collapse: collapse; }
    .svc-inst-table th { font-size: .7rem; text-transform: uppercase; letter-spacing: .5px; color: rgba(0,0,0,.4); padding: .5rem .75rem; border-bottom: 1px solid rgba(0,0,0,.08); }
    .svc-inst-table td { padding: .6rem .75rem; font-size: .85rem; border-bottom: 1px solid rgba(0,0,0,.04); }

    @media (max-width: 991.98px) {
        .svc-container { flex-direction: column; height: auto; }
        .svc-rail { width: 100%; min-width: 100%; max-height: 45vh; }
    }
</style>
@endpush

@section('content')
<div class="svc-container">
    {{-- Left Rail --}}
    <div class="svc-rail">
        <div class="svc-rail-header">
            <h6>Services</h6>
            <div class="svc-rail-search">
                <input type="text" id="svcSearch" placeholder="Search services..." value="{{ request('search') }}">
                <button class="btn btn-sm btn-vip" data-bs-toggle="modal" data-bs-target="#addServiceModal" title="Add Service">
                    <i class="bi bi-plus-lg"></i>
                </button>
            </div>
            <div class="svc-rail-tabs">
                <div class="tab-btn {{ !request('filter') ? 'active' : '' }}" data-filter="">All</div>
                <div class="tab-btn {{ request('filter') === 'active' ? 'active' : '' }}" data-filter="active">Active</div>
                <div class="tab-btn {{ request('filter') === 'inactive' ? 'active' : '' }}" data-filter="inactive">Inactive</div>
            </div>
        </div>

        <div class="svc-rail-list">
            @forelse($services as $service)
                <div class="svc-card" data-id="{{ $service->id }}">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="d-flex align-items-center gap-2">
                            <span style="width:10px;height:10px;border-radius:50%;background:{{ $service->color ?? '#0d6efd' }};flex-shrink:0;"></span>
                            <div>
                                <div class="svc-name">{{ $service->name }}</div>
                            </div>
                        </div>
                        <span class="svc-badge {{ $service->is_active ? 'svc-badge-active' : 'svc-badge-inactive' }}">
                            {{ $service->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between align-items-end mt-1">
                        <div class="svc-price">${{ number_format($service->base_price, 2) }}</div>
                        <div class="svc-unit">{{ str_replace('_', ' ', $service->unit) }}</div>
                    </div>
                </div>
            @empty
                <div class="text-center py-4" style="color:rgba(255,255,255,.4);">
                    <i class="bi bi-wrench" style="font-size:2rem;"></i>
                    <p class="mt-2 mb-0">No services found</p>
                </div>
            @endforelse
        </div>

        <div class="svc-rail-footer">
            <span>{{ $services->total() }} service{{ $services->total() !== 1 ? 's' : '' }}</span>
            <span>Active: {{ $services->where('is_active', true)->count() }}</span>
        </div>
    </div>

    {{-- Main Panel --}}
    <div class="svc-main">
        <div class="svc-main-toolbar">
            <h5 id="svcDetailTitle">Service Details</h5>
            <div id="svcToolbarActions"></div>
        </div>
        <div class="svc-detail-body" id="svcDetailBody">
            <div class="svc-empty-state">
                <i class="bi bi-wrench"></i>
                <p>Select a service to view details</p>
            </div>
        </div>
    </div>
</div>

{{-- Add Service Modal --}}
<div class="modal fade" id="addServiceModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.services.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Add Service</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-8"><label class="form-label">Service Name *</label><input type="text" name="name" class="form-control" required></div>
                        <div class="col-4"><label class="form-label">Code *</label><input type="text" name="code" class="form-control" required placeholder="e.g. WIN_INSTALL"></div>
                        <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
                        <div class="col-4"><label class="form-label">Base Price *</label><input type="number" name="base_price" class="form-control" step="0.01" min="0" required></div>
                        <div class="col-4"><label class="form-label">Cost Price</label><input type="number" name="cost_price" class="form-control" step="0.01" min="0" value="0"></div>
                        <div class="col-4"><label class="form-label">Unit *</label>
                            <select name="unit" class="form-select">
                                <option value="per_job">Per Job</option>
                                <option value="per_hour">Per Hour</option>
                                <option value="per_unit">Per Unit</option>
                            </select>
                        </div>
                        <div class="col-12"><hr class="my-1"><label class="form-label fw-bold text-muted" style="font-size:.75rem;">INSTALLER PAY</label></div>
                        <div class="col-6"><label class="form-label">Installer Pay Rate</label><input type="number" name="installer_pay" class="form-control" step="0.01" min="0" placeholder="e.g. 60.00"></div>
                        <div class="col-6"><label class="form-label">Pay Type</label>
                            <select name="installer_pay_type" class="form-select">
                                <option value="per_unit">Per Unit</option>
                                <option value="per_job">Per Job</option>
                                <option value="per_hour">Per Hour</option>
                                <option value="percentage">% of Base Price</option>
                            </select>
                        </div>
                        <div class="col-4"><label class="form-label">Min Price</label><input type="number" name="min_price" class="form-control" step="0.01" min="0"></div>
                        <div class="col-4"><label class="form-label">Max Price</label><input type="number" name="max_price" class="form-control" step="0.01" min="0"></div>
                        <div class="col-4"><label class="form-label">Sort Order</label><input type="number" name="sort_order" class="form-control" value="0"></div>
                        <div class="col-4">
                            <label class="form-label">Calendar Color</label>
                            <input type="color" name="color" class="form-control form-control-color" value="#0d6efd" title="Color shown on calendar">
                        </div>
                        <div class="col-8 d-flex align-items-end">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" value="1" class="form-check-input" id="addSvcActive" checked>
                                <label class="form-check-label" for="addSvcActive">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-vip">Create Service</button></div>
            </div>
        </form>
    </div>
</div>

{{-- Edit Service Modal --}}
<div class="modal fade" id="editServiceModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="editServiceForm">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Edit Service</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-8"><label class="form-label">Service Name *</label><input type="text" name="name" id="editSvcName" class="form-control" required></div>
                        <div class="col-4"><label class="form-label">Code *</label><input type="text" name="code" id="editSvcCode" class="form-control" required></div>
                        <div class="col-12"><label class="form-label">Description</label><textarea name="description" id="editSvcDesc" class="form-control" rows="2"></textarea></div>
                        <div class="col-4"><label class="form-label">Base Price *</label><input type="number" name="base_price" id="editSvcBasePrice" class="form-control" step="0.01" min="0" required></div>
                        <div class="col-4"><label class="form-label">Cost Price</label><input type="number" name="cost_price" id="editSvcCostPrice" class="form-control" step="0.01" min="0"></div>
                        <div class="col-4"><label class="form-label">Unit *</label>
                            <select name="unit" id="editSvcUnit" class="form-select">
                                <option value="per_job">Per Job</option>
                                <option value="per_hour">Per Hour</option>
                                <option value="per_unit">Per Unit</option>
                            </select>
                        </div>
                        <div class="col-12"><hr class="my-1"><label class="form-label fw-bold text-muted" style="font-size:.75rem;">INSTALLER PAY</label></div>
                        <div class="col-6"><label class="form-label">Installer Pay Rate</label><input type="number" name="installer_pay" id="editSvcInstallerPay" class="form-control" step="0.01" min="0"></div>
                        <div class="col-6"><label class="form-label">Pay Type</label>
                            <select name="installer_pay_type" id="editSvcInstallerPayType" class="form-select">
                                <option value="per_unit">Per Unit</option>
                                <option value="per_job">Per Job</option>
                                <option value="per_hour">Per Hour</option>
                                <option value="percentage">% of Base Price</option>
                            </select>
                        </div>
                        <div class="col-4"><label class="form-label">Min Price</label><input type="number" name="min_price" id="editSvcMinPrice" class="form-control" step="0.01" min="0"></div>
                        <div class="col-4"><label class="form-label">Max Price</label><input type="number" name="max_price" id="editSvcMaxPrice" class="form-control" step="0.01" min="0"></div>
                        <div class="col-4"><label class="form-label">Sort Order</label><input type="number" name="sort_order" id="editSvcSortOrder" class="form-control"></div>
                        <div class="col-4">
                            <label class="form-label">Calendar Color</label>
                            <input type="color" name="color" id="editSvcColor" class="form-control form-control-color" value="#0d6efd" title="Color shown on calendar">
                        </div>
                        <div class="col-8 d-flex align-items-end">
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
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.svc-card');
    const detailBody = document.getElementById('svcDetailBody');
    const detailTitle = document.getElementById('svcDetailTitle');
    const toolbarActions = document.getElementById('svcToolbarActions');
    let activeId = null;

    // Tab filters
    document.querySelectorAll('.svc-rail-tabs .tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const filter = this.dataset.filter;
            const url = new URL(window.location);
            if (filter) url.searchParams.set('filter', filter);
            else url.searchParams.delete('filter');
            window.location = url;
        });
    });

    // Search
    let searchTimer;
    document.getElementById('svcSearch').addEventListener('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            const url = new URL(window.location);
            if (this.value) url.searchParams.set('search', this.value);
            else url.searchParams.delete('search');
            window.location = url;
        }, 500);
    });

    // Load detail
    cards.forEach(card => {
        card.addEventListener('click', function() {
            cards.forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            activeId = this.dataset.id;
            loadDetail(activeId);
        });
    });

    function loadDetail(id) {
        detailBody.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-secondary"></div></div>';

        fetch(`/admin/services/${id}`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }})
            .then(r => r.json())
            .then(data => {
                const svc = data.service;
                const installers = data.installers;
                detailTitle.textContent = svc.name;

                const unitLabel = svc.unit.replace(/_/g, ' ');
                const margin = svc.base_price > 0 ? (((svc.base_price - svc.cost_price) / svc.base_price) * 100).toFixed(1) : '0.0';
                const instPay = parseFloat(svc.installer_pay || 0);
                const instPayType = (svc.installer_pay_type || 'per_unit').replace(/_/g, ' ');
                const instPayDisplay = svc.installer_pay_type === 'percentage' ? instPay.toFixed(1) + '%' : '$' + instPay.toFixed(2);
                const profitPerUnit = svc.installer_pay_type === 'percentage'
                    ? svc.base_price - (svc.base_price * instPay / 100)
                    : svc.base_price - instPay;

                toolbarActions.innerHTML = `
                    <button class="btn btn-sm btn-outline-secondary me-2" onclick="editService(${svc.id})"><i class="bi bi-pencil"></i> Edit</button>
                    <form method="POST" action="/admin/services/${svc.id}" style="display:inline" onsubmit="return confirm('Delete this service?')">
                        <input type="hidden" name="_token" value="${document.querySelector('meta[name=csrf-token]').content}">
                        <input type="hidden" name="_method" value="DELETE">
                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                `;

                let instHtml = '';
                if (installers.length > 0) {
                    instHtml = `<table class="svc-inst-table">
                        <thead><tr><th>Installer</th><th>Company</th><th>Custom Price</th></tr></thead>
                        <tbody>${installers.map(i => `<tr>
                            <td>${i.name}</td>
                            <td>${i.company_name || '—'}</td>
                            <td>${i.custom_price ? '$' + parseFloat(i.custom_price).toFixed(2) : '<span style="color:rgba(0,0,0,.35)">Uses base price</span>'}</td>
                        </tr>`).join('')}</tbody>
                    </table>`;
                } else {
                    instHtml = '<p style="color:rgba(0,0,0,.4); font-size:.85rem;">No installers assigned to this service yet.</p>';
                }

                detailBody.innerHTML = `
                    <div class="svc-info-grid">
                        <div class="svc-info-card">
                            <div class="label">Base Price (Customer Charge)</div>
                            <div class="value" style="color:var(--vip-accent);">$${parseFloat(svc.base_price).toFixed(2)} <small style="font-weight:400;color:rgba(0,0,0,.4)">${unitLabel}</small></div>
                        </div>
                        <div class="svc-info-card">
                            <div class="label">Installer Pay</div>
                            <div class="value" style="color:#dc3545;">${instPayDisplay} <small style="font-weight:400;color:rgba(0,0,0,.4)">${instPayType}</small></div>
                        </div>
                        <div class="svc-info-card">
                            <div class="label">Profit per ${unitLabel}</div>
                            <div class="value" style="color:#198754;">$${profitPerUnit.toFixed(2)}</div>
                        </div>
                        <div class="svc-info-card">
                            <div class="label">Cost Price</div>
                            <div class="value">$${parseFloat(svc.cost_price).toFixed(2)}</div>
                        </div>
                        <div class="svc-info-card">
                            <div class="label">Margin</div>
                            <div class="value">${margin}%</div>
                        </div>
                        <div class="svc-info-card">
                            <div class="label">Calendar Color</div>
                            <div class="value d-flex align-items-center gap-2"><span style="display:inline-block;width:18px;height:18px;border-radius:4px;background:${svc.color || '#0d6efd'};"></span> ${svc.color || '#0d6efd'}</div>
                        </div>
                        ${svc.min_price ? `<div class="svc-info-card"><div class="label">Min Price</div><div class="value">$${parseFloat(svc.min_price).toFixed(2)}</div></div>` : ''}
                        ${svc.max_price ? `<div class="svc-info-card"><div class="label">Max Price</div><div class="value">$${parseFloat(svc.max_price).toFixed(2)}</div></div>` : ''}
                    </div>
                    ${svc.description ? `<div class="svc-info-card mb-4"><div class="label">Description</div><div class="value" style="font-weight:400; font-size:.9rem;">${svc.description}</div></div>` : ''}
                    <div class="svc-installers-card">
                        <h6><i class="bi bi-person-badge me-1"></i> Assigned Installers (${installers.length})</h6>
                        ${instHtml}
                    </div>
                `;
            })
            .catch(() => {
                detailBody.innerHTML = '<div class="alert alert-danger m-4">Failed to load service details.</div>';
            });
    }

    // Edit service
    window.editService = function(id) {
        fetch(`/admin/services/${id}`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }})
            .then(r => r.json())
            .then(data => {
                const svc = data.service;
                document.getElementById('editServiceForm').action = `/admin/services/${svc.id}`;
                document.getElementById('editSvcName').value = svc.name;
                document.getElementById('editSvcCode').value = svc.code;
                document.getElementById('editSvcDesc').value = svc.description || '';
                document.getElementById('editSvcBasePrice').value = svc.base_price;
                document.getElementById('editSvcCostPrice').value = svc.cost_price;
                document.getElementById('editSvcUnit').value = svc.unit;
                document.getElementById('editSvcMinPrice').value = svc.min_price || '';
                document.getElementById('editSvcMaxPrice').value = svc.max_price || '';
                document.getElementById('editSvcSortOrder').value = svc.sort_order || 0;
                document.getElementById('editSvcColor').value = svc.color || '#0d6efd';
                document.getElementById('editSvcInstallerPay').value = svc.installer_pay || '';
                document.getElementById('editSvcInstallerPayType').value = svc.installer_pay_type || 'per_unit';
                document.getElementById('editSvcActive').checked = svc.is_active;
                new bootstrap.Modal(document.getElementById('editServiceModal')).show();
            });
    };

    // Auto-select first
    if (cards.length > 0) cards[0].click();
});
</script>
@endpush
