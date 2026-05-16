@extends('layouts.app')
@section('title', 'Customers')

@push('styles')
<style>
    /* ── Customer Hub Shell ───────────────────────────── */
    .ch-wrapper {
        display: flex;
        height: calc(100vh - 120px);
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        overflow: hidden;
    }

    /* ── Left Rail ─────────────────────────────────────── */
    .ch-sidebar {
        width: 300px;
        min-width: 300px;
        background: var(--vip-primary);
        color: #fff;
        display: flex;
        flex-direction: column;
        border-right: 1px solid rgba(255,255,255,.08);
    }
    .ch-sidebar-header {
        padding: 14px 16px 12px;
        border-bottom: 1px solid rgba(255,255,255,.1);
        background: rgba(0,0,0,.15);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .ch-sidebar-header h6 {
        margin: 0;
        font-size: .85rem;
        font-weight: 700;
        letter-spacing: .5px;
        color: var(--vip-accent);
    }
    .ch-sidebar-header .btn-new {
        background: var(--vip-accent);
        color: var(--vip-primary);
        border: none;
        font-size: .72rem;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 5px;
        cursor: pointer;
    }
    .ch-sidebar-header .btn-new:hover { background: #d4b35a; color: #000; }

    /* Search + Filters */
    .ch-filters {
        padding: 10px 12px;
        border-bottom: 1px solid rgba(255,255,255,.08);
    }
    .ch-filters input {
        width: 100%;
        background: rgba(255,255,255,.1);
        border: 1px solid rgba(255,255,255,.15);
        border-radius: 6px;
        padding: 6px 10px;
        color: #fff;
        font-size: .8rem;
        margin-bottom: 8px;
    }
    .ch-filters input::placeholder { color: rgba(255,255,255,.4); }
    .ch-filters input:focus {
        outline: none;
        border-color: var(--vip-accent);
        background: rgba(255,255,255,.15);
    }
    .ch-type-tabs {
        display: flex;
        gap: 4px;
    }
    .ch-type-tab {
        flex: 1;
        text-align: center;
        padding: 4px 6px;
        font-size: .68rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .5px;
        border-radius: 4px;
        cursor: pointer;
        color: rgba(255,255,255,.5);
        background: rgba(255,255,255,.06);
        border: 1px solid transparent;
        transition: all .15s;
    }
    .ch-type-tab:hover {
        color: rgba(255,255,255,.8);
        background: rgba(255,255,255,.1);
    }
    .ch-type-tab.active {
        color: var(--vip-accent);
        background: rgba(201,168,76,.15);
        border-color: rgba(201,168,76,.3);
    }

    /* Sidebar body — customer list */
    .ch-sidebar-body {
        flex: 1;
        overflow-y: auto;
        padding: 4px 0;
    }
    .ch-sidebar-body::-webkit-scrollbar { width: 4px; }
    .ch-sidebar-body::-webkit-scrollbar-thumb { background: rgba(255,255,255,.2); border-radius: 4px; }

    /* Customer cards in sidebar */
    .ch-cust-item {
        display: block;
        padding: 10px 14px;
        color: rgba(255,255,255,.75);
        text-decoration: none;
        border-left: 3px solid transparent;
        border-bottom: 1px solid rgba(255,255,255,.05);
        cursor: pointer;
        transition: all .15s;
    }
    .ch-cust-item:hover {
        background: rgba(255,255,255,.08);
        color: #fff;
    }
    .ch-cust-item.active {
        background: rgba(201,168,76,.12);
        border-left-color: var(--vip-accent);
        color: #fff;
    }
    .ch-cust-item .ch-c-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2px;
    }
    .ch-cust-item .ch-c-name {
        font-weight: 700;
        font-size: .82rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 180px;
    }
    .ch-cust-item.active .ch-c-name { color: var(--vip-accent); }
    .ch-cust-item .ch-c-badge {
        font-size: .58rem;
        padding: 2px 6px;
        border-radius: 3px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .3px;
    }
    .ch-c-badge.homeowner { background: rgba(108,117,125,.3); color: #ccc; }
    .ch-c-badge.business { background: rgba(0,123,255,.25); color: #6db8ff; }
    .ch-cust-item .ch-c-email {
        font-size: .75rem;
        opacity: .65;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .ch-cust-item .ch-c-meta {
        display: flex;
        justify-content: space-between;
        font-size: .68rem;
        opacity: .45;
        margin-top: 2px;
    }

    /* Sidebar stats */
    .ch-sidebar-footer {
        padding: 10px 14px;
        border-top: 1px solid rgba(255,255,255,.1);
        background: rgba(0,0,0,.1);
        display: flex;
        gap: 8px;
    }
    .ch-stat {
        flex: 1;
        text-align: center;
    }
    .ch-stat .val {
        font-size: 1rem;
        font-weight: 700;
        color: var(--vip-accent);
    }
    .ch-stat .lbl {
        font-size: .6rem;
        text-transform: uppercase;
        letter-spacing: .5px;
        opacity: .5;
    }

    /* ── Main Panel ────────────────────────────────────── */
    .ch-main {
        flex: 1;
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    /* Toolbar */
    .ch-toolbar {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 20px;
        border-bottom: 1px solid #e9ecef;
        background: #fafafa;
        min-height: 52px;
    }
    .ch-toolbar .ch-title {
        font-size: 1rem;
        font-weight: 700;
        color: var(--vip-primary);
        margin: 0;
    }
    .ch-toolbar .ch-sub { font-size: .78rem; color: #999; }
    .ch-toolbar .actions { margin-left: auto; display: flex; gap: 6px; }
    .ch-toolbar .actions .btn { font-size: .78rem; }

    /* Content area */
    .ch-content {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
        position: relative;
    }

    /* Placeholder */
    .ch-placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        height: 100%;
        text-align: center;
        color: #999;
    }
    .ch-placeholder i { font-size: 3rem; color: #ddd; margin-bottom: 16px; }
    .ch-placeholder h5 { color: #666; margin-bottom: 8px; }

    /* Loader */
    .ch-loader {
        position: absolute;
        inset: 0;
        display: none;
        align-items: center;
        justify-content: center;
        background: rgba(255,255,255,.9);
        z-index: 10;
    }
    .ch-loader.show { display: flex; }
    .ch-loader .spinner-border { width: 2rem; height: 2rem; color: var(--vip-accent); }

    /* Detail cards */
    .ch-detail-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 1px solid #e9ecef;
    }
    .ch-detail-header .ch-dh-left h4 { margin: 0; font-weight: 700; color: var(--vip-primary); }
    .ch-detail-header .ch-dh-left .text-muted { font-size: .85rem; }

    .ch-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 12px;
        margin-bottom: 24px;
    }
    .ch-info-card {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 12px 14px;
    }
    .ch-info-card .label {
        font-size: .68rem;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #888;
        margin-bottom: 4px;
    }
    .ch-info-card .value {
        font-size: .9rem;
        font-weight: 600;
        color: #333;
    }

    /* Tabs inside detail */
    .ch-detail-tabs .nav-link {
        font-size: .82rem;
        color: #666;
        padding: 8px 14px;
    }
    .ch-detail-tabs .nav-link.active {
        color: var(--vip-primary);
        font-weight: 600;
        border-bottom-color: var(--vip-accent);
    }
    .ch-tab-table th {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #666;
        border-bottom: 2px solid #e9ecef;
    }
    .ch-tab-table td { font-size: .85rem; vertical-align: middle; }

    /* Responsive */
    @media (max-width: 991.98px) {
        .ch-wrapper { flex-direction: column; height: auto; min-height: calc(100vh - 120px); }
        .ch-sidebar { width: 100%; min-width: 100%; max-height: 320px; }
        .ch-content { min-height: 400px; }
    }
</style>
@endpush

@section('content')
<div class="p-3">
    <div class="ch-wrapper">

        {{-- ── Left Rail ──────────────────────────────────── --}}
        <div class="ch-sidebar">
            <div class="ch-sidebar-header">
                <h6><i class="bi bi-people me-1"></i> CUSTOMERS</h6>
                <button class="btn-new" onclick="document.getElementById('addCustomerModal') && new bootstrap.Modal(document.getElementById('addCustomerModal')).show()">
                    <i class="bi bi-plus me-1"></i>Add New
                </button>
            </div>

            <div class="ch-filters">
                <input type="text" id="chSearch" placeholder="Search customers…">
                <div class="ch-type-tabs">
                    <div class="ch-type-tab active" data-type="all">All</div>
                    <div class="ch-type-tab" data-type="homeowner"><i class="bi bi-house-door me-1"></i>Homeowner</div>
                    <div class="ch-type-tab" data-type="business"><i class="bi bi-building me-1"></i>Business</div>
                </div>
            </div>

            <div class="ch-sidebar-body" id="chCustomerList">
                @forelse($customers as $customer)
                    <a class="ch-cust-item"
                       href="#"
                       data-id="{{ $customer->id }}"
                       data-type="{{ $customer->customer_type ?? 'homeowner' }}"
                       data-search="{{ strtolower($customer->name . ' ' . $customer->email . ' ' . ($customer->phone ?? '') . ' ' . ($customer->city ?? '')) }}"
                       onclick="chLoadCustomer(event, this)">
                        <div class="ch-c-top">
                            <span class="ch-c-name">{{ $customer->name }}</span>
                            <span class="ch-c-badge {{ $customer->customer_type ?? 'homeowner' }}">
                                {{ ($customer->customer_type ?? 'homeowner') === 'business' ? 'Business' : 'Homeowner' }}
                            </span>
                        </div>
                        <div class="ch-c-email">{{ $customer->email }}</div>
                        <div class="ch-c-meta">
                            <span>{{ $customer->phone ?: 'No phone' }}</span>
                            <span>{{ $customer->city ? $customer->city . ', ' . $customer->state : '—' }}</span>
                        </div>
                    </a>
                @empty
                    <div class="text-center py-4 opacity-50">
                        <i class="bi bi-people d-block mb-2" style="font-size:1.5rem"></i>
                        <small>No customers yet</small>
                    </div>
                @endforelse
            </div>

            <div class="ch-sidebar-footer">
                @php
                    $totalCustomers = $customers->total();
                    $homeCount = $customers->getCollection()->where('customer_type', 'homeowner')->count() + $customers->getCollection()->whereNull('customer_type')->count();
                    $bizCount = $customers->getCollection()->where('customer_type', 'business')->count();
                @endphp
                <div class="ch-stat">
                    <div class="val">{{ $totalCustomers }}</div>
                    <div class="lbl">Total</div>
                </div>
                <div class="ch-stat">
                    <div class="val">{{ $homeCount }}</div>
                    <div class="lbl">Homeowner</div>
                </div>
                <div class="ch-stat">
                    <div class="val">{{ $bizCount }}</div>
                    <div class="lbl">Business</div>
                </div>
            </div>
        </div>

        {{-- ── Main Panel ─────────────────────────────────── --}}
        <div class="ch-main">
            <div class="ch-toolbar">
                <div>
                    <h5 class="ch-title" id="chTitle">Customer Details</h5>
                    <span class="ch-sub" id="chSub">Select a customer from the list</span>
                </div>
                <div class="actions" id="chActions" style="display:none">
                    <button class="btn btn-sm btn-outline-primary" id="chEditBtn">
                        <i class="bi bi-pencil me-1"></i>Edit
                    </button>
                    <a href="#" class="btn btn-sm btn-outline-success" id="chEmailBtn">
                        <i class="bi bi-envelope me-1"></i>Email
                    </a>
                    <button class="btn btn-sm btn-outline-danger" id="chDeleteBtn">
                        <i class="bi bi-trash me-1"></i>Delete
                    </button>
                </div>
            </div>

            <div class="ch-content" id="chContent">
                <div class="ch-loader" id="chLoader">
                    <div class="spinner-border"></div>
                </div>

                <div class="ch-placeholder" id="chPlaceholder">
                    <i class="bi bi-people"></i>
                    <h5>Select a Customer</h5>
                    <p class="text-muted small">Click on a customer from the left to view their profile, jobs, quotes, invoices, and orders.</p>
                </div>

                <div id="chDetail" style="display:none"></div>
            </div>
        </div>

    </div>
</div>

{{-- Add Customer Modal --}}
<div class="modal fade" id="addCustomerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.customers.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-person-plus me-1"></i> Add New Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" placeholder="(555) 123-4567">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Customer Type</label>
                            <select name="customer_type" class="form-select">
                                <option value="homeowner">Homeowner</option>
                                <option value="business">Business</option>
                            </select>
                        </div>
                    </div>
                    <hr class="my-2">
                    <h6 class="text-muted small mb-2">Address</h6>
                    <div class="mb-3">
                        <label class="form-label">Street Address</label>
                        <input type="text" name="address" class="form-control">
                    </div>
                    <div class="row">
                        <div class="col-md-5 mb-3"><label class="form-label">City</label><input type="text" name="city" class="form-control"></div>
                        <div class="col-md-4 mb-3"><label class="form-label">State</label><input type="text" name="state" class="form-control" value="CA"></div>
                        <div class="col-md-3 mb-3"><label class="form-label">ZIP</label><input type="text" name="zip" class="form-control"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Any notes about this customer..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-vip"><i class="bi bi-person-plus me-1"></i> Add Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Customer Modal (dynamic) --}}
<div class="modal fade" id="editCustomerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editCustomerForm" method="POST">
                @csrf
                <input type="hidden" name="_method" value="PUT">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil me-1"></i> Edit Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="editName" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="editEmail" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" id="editPhone" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Customer Type</label>
                            <select name="customer_type" id="editType" class="form-select">
                                <option value="homeowner">Homeowner</option>
                                <option value="business">Business</option>
                            </select>
                        </div>
                    </div>
                    <hr class="my-2">
                    <div class="mb-3">
                        <label class="form-label">Street Address</label>
                        <input type="text" name="address" id="editAddress" class="form-control">
                    </div>
                    <div class="row">
                        <div class="col-md-5 mb-3"><label class="form-label">City</label><input type="text" name="city" id="editCity" class="form-control"></div>
                        <div class="col-md-4 mb-3"><label class="form-label">State</label><input type="text" name="state" id="editState" class="form-control"></div>
                        <div class="col-md-3 mb-3"><label class="form-label">ZIP</label><input type="text" name="zip" id="editZip" class="form-control"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" id="editNotes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-vip">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    const csrf = '{{ csrf_token() }}';
    let activeCustomerId = null;
    let activeCustomerData = null;

    const statusColors = {
        draft: 'secondary', sent: 'primary', paid: 'success', partial: 'warning',
        overdue: 'danger', cancelled: 'dark', pending: 'warning', scheduled: 'info',
        in_progress: 'primary', completed: 'success', accepted: 'success'
    };
    const priorityColors = { low: 'secondary', normal: 'primary', high: 'warning', urgent: 'danger' };

    // ── Type tab filtering ────────────────────────────────
    document.querySelectorAll('.ch-type-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.ch-type-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            filterCustomers();
        });
    });

    // ── Search filtering ──────────────────────────────────
    document.getElementById('chSearch').addEventListener('input', filterCustomers);

    function filterCustomers() {
        const search = document.getElementById('chSearch').value.toLowerCase();
        const type = document.querySelector('.ch-type-tab.active')?.dataset.type || 'all';

        document.querySelectorAll('.ch-cust-item').forEach(item => {
            const matchSearch = !search || item.dataset.search.includes(search);
            const itemType = item.dataset.type || 'homeowner';
            const matchType = type === 'all' || itemType === type;
            item.style.display = (matchSearch && matchType) ? '' : 'none';
        });
    }

    // ── Load customer detail ──────────────────────────────
    window.chLoadCustomer = function(e, el) {
        e.preventDefault();
        const id = el.dataset.id;
        activeCustomerId = id;

        // Highlight
        document.querySelectorAll('.ch-cust-item').forEach(i => i.classList.remove('active'));
        el.classList.add('active');

        // Show loader
        const loader = document.getElementById('chLoader');
        const detail = document.getElementById('chDetail');
        const placeholder = document.getElementById('chPlaceholder');
        loader.classList.add('show');
        placeholder.style.display = 'none';
        detail.style.display = 'none';

        fetch(`/admin/customers/${id}`, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
        })
        .then(r => r.json())
        .then(data => {
            activeCustomerData = data.customer;
            renderCustomerDetail(data);
            loader.classList.remove('show');
            detail.style.display = 'block';

            // Update toolbar
            document.getElementById('chTitle').textContent = data.customer.name;
            document.getElementById('chSub').textContent = data.customer.email;
            document.getElementById('chActions').style.display = 'flex';
            document.getElementById('chEmailBtn').href = `/admin/email/compose?to=${encodeURIComponent(data.customer.email)}`;
        })
        .catch(err => {
            loader.classList.remove('show');
            detail.innerHTML = '<div class="alert alert-danger">Failed to load customer details.</div>';
            detail.style.display = 'block';
        });
    };

    function renderCustomerDetail(data) {
        const c = data.customer;
        const typeBadge = (c.customer_type || 'homeowner') === 'business'
            ? '<span class="badge bg-primary"><i class="bi bi-building me-1"></i>Business</span>'
            : '<span class="badge bg-secondary"><i class="bi bi-house-door me-1"></i>Homeowner</span>';
        const since = c.created_at ? new Date(c.created_at).toLocaleDateString('en-US', { year:'numeric', month:'short', day:'numeric' }) : '—';
        const address = [c.address, c.city, c.state, c.zip].filter(Boolean).join(', ') || '—';

        let html = `
            <div class="ch-detail-header">
                <div class="ch-dh-left">
                    <h4>${esc(c.name)} ${typeBadge}</h4>
                    <div class="text-muted">Customer since ${since}</div>
                </div>
            </div>

            <div class="ch-info-grid">
                <div class="ch-info-card">
                    <div class="label">Email</div>
                    <div class="value">${esc(c.email)}</div>
                </div>
                <div class="ch-info-card">
                    <div class="label">Phone</div>
                    <div class="value">${esc(c.phone || '—')}</div>
                </div>
                <div class="ch-info-card">
                    <div class="label">Address</div>
                    <div class="value">${esc(address)}</div>
                </div>
                <div class="ch-info-card">
                    <div class="label">Notes</div>
                    <div class="value">${esc(c.notes || 'No notes')}</div>
                </div>
            </div>

            <ul class="nav nav-tabs ch-detail-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#chTabJobs">
                        <i class="bi bi-tools me-1"></i>Jobs <span class="badge bg-secondary ms-1">${data.jobs.length}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#chTabQuotes">
                        <i class="bi bi-calculator me-1"></i>Quotes <span class="badge bg-secondary ms-1">${data.quotes.length}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#chTabInvoices">
                        <i class="bi bi-receipt me-1"></i>Invoices <span class="badge bg-secondary ms-1">${data.invoices.length}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#chTabOrders">
                        <i class="bi bi-clipboard-check me-1"></i>Orders <span class="badge bg-secondary ms-1">${data.orders.length}</span>
                    </a>
                </li>
            </ul>

            <div class="tab-content pt-3">
        `;

        // Jobs tab
        html += `<div class="tab-pane fade show active" id="chTabJobs">`;
        if (data.jobs.length === 0) {
            html += emptyState('bi-tools', 'No jobs for this customer');
        } else {
            html += `<div class="table-responsive"><table class="table table-sm ch-tab-table">
                <thead><tr><th>Job #</th><th>Status</th><th>Priority</th><th>Address</th><th>Assigned To</th><th>Scheduled</th><th>Created</th></tr></thead><tbody>`;
            data.jobs.forEach(j => {
                html += `<tr>
                    <td class="fw-semibold">${esc(j.job_number)}</td>
                    <td>${badge(j.status)}</td>
                    <td><span class="badge bg-${priorityColors[j.priority] || 'secondary'}">${esc(j.priority)}</span></td>
                    <td>${esc(j.install_address || '—')}</td>
                    <td>${esc(j.assigned_to || '—')}</td>
                    <td>${esc(j.scheduled_date || '—')}</td>
                    <td class="text-muted">${esc(j.created_at)}</td>
                </tr>`;
            });
            html += `</tbody></table></div>`;
        }
        html += `</div>`;

        // Quotes tab
        html += `<div class="tab-pane fade" id="chTabQuotes">`;
        if (data.quotes.length === 0) {
            html += emptyState('bi-calculator', 'No quotes for this customer');
        } else {
            html += `<div class="table-responsive"><table class="table table-sm ch-tab-table">
                <thead><tr><th>Quote #</th><th>Status</th><th>Items</th><th>Total</th><th>Date</th></tr></thead><tbody>`;
            data.quotes.forEach(q => {
                html += `<tr>
                    <td class="fw-semibold">${esc(q.quote_number)}</td>
                    <td>${badge(q.status)}</td>
                    <td>${q.items_count} item${q.items_count !== 1 ? 's' : ''}</td>
                    <td class="fw-semibold">$${esc(q.total)}</td>
                    <td class="text-muted">${esc(q.created_at)}</td>
                </tr>`;
            });
            html += `</tbody></table></div>`;
        }
        html += `</div>`;

        // Invoices tab
        html += `<div class="tab-pane fade" id="chTabInvoices">`;
        if (data.invoices.length === 0) {
            html += emptyState('bi-receipt', 'No invoices for this customer');
        } else {
            html += `<div class="table-responsive"><table class="table table-sm ch-tab-table">
                <thead><tr><th>Invoice #</th><th>Status</th><th>Total</th><th>Balance</th><th>Due Date</th><th>Created</th></tr></thead><tbody>`;
            data.invoices.forEach(inv => {
                html += `<tr>
                    <td class="fw-semibold">${esc(inv.invoice_number)}</td>
                    <td>${badge(inv.status)}</td>
                    <td>$${esc(inv.total)}</td>
                    <td class="fw-semibold">$${esc(inv.balance_due)}</td>
                    <td>${esc(inv.due_date || '—')}</td>
                    <td class="text-muted">${esc(inv.created_at)}</td>
                </tr>`;
            });
            html += `</tbody></table></div>`;
        }
        html += `</div>`;

        // Orders tab
        html += `<div class="tab-pane fade" id="chTabOrders">`;
        if (data.orders.length === 0) {
            html += emptyState('bi-clipboard-check', 'No installation orders for this customer');
        } else {
            html += `<div class="table-responsive"><table class="table table-sm ch-tab-table">
                <thead><tr><th>Order #</th><th>Status</th><th>Address</th><th>Scheduled</th><th>Created</th></tr></thead><tbody>`;
            data.orders.forEach(o => {
                html += `<tr>
                    <td class="fw-semibold">#${o.id}</td>
                    <td>${badge(o.status)}</td>
                    <td>${esc(o.install_address || '—')}</td>
                    <td>${esc(o.scheduled_date || '—')}</td>
                    <td class="text-muted">${esc(o.created_at)}</td>
                </tr>`;
            });
            html += `</tbody></table></div>`;
        }
        html += `</div></div>`;

        document.getElementById('chDetail').innerHTML = html;
    }

    function badge(status) {
        const s = (status || '').replace(/_/g, ' ');
        return `<span class="badge bg-${statusColors[status] || 'secondary'}">${s}</span>`;
    }

    function emptyState(icon, text) {
        return `<div class="text-center py-4 text-muted"><i class="bi ${icon} d-block mb-2" style="font-size:2rem"></i><p>${text}</p></div>`;
    }

    function esc(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // ── Edit customer ─────────────────────────────────────
    document.getElementById('chEditBtn').addEventListener('click', function() {
        if (!activeCustomerData) return;
        const c = activeCustomerData;
        document.getElementById('editCustomerForm').action = `/admin/customers/${c.id}`;
        document.getElementById('editName').value = c.name || '';
        document.getElementById('editEmail').value = c.email || '';
        document.getElementById('editPhone').value = c.phone || '';
        document.getElementById('editType').value = c.customer_type || 'homeowner';
        document.getElementById('editAddress').value = c.address || '';
        document.getElementById('editCity').value = c.city || '';
        document.getElementById('editState').value = c.state || '';
        document.getElementById('editZip').value = c.zip || '';
        document.getElementById('editNotes').value = c.notes || '';
        new bootstrap.Modal(document.getElementById('editCustomerModal')).show();
    });

    // ── Delete customer ───────────────────────────────────
    document.getElementById('chDeleteBtn').addEventListener('click', function() {
        if (!activeCustomerId || !confirm('Remove this customer? This cannot be undone.')) return;

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/customers/${activeCustomerId}`;
        form.innerHTML = `<input type="hidden" name="_token" value="${csrf}"><input type="hidden" name="_method" value="DELETE">`;
        document.body.appendChild(form);
        form.submit();
    });

})();
</script>
@endpush
