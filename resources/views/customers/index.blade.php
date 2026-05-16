@extends('layouts.app')
@section('title', 'Customers')

@section('content')
<div class="container-fluid py-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><i class="bi bi-people me-2"></i>Customers</h4>
        <button class="btn btn-vip" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
            <i class="bi bi-person-plus me-1"></i> Add Customer
        </button>
    </div>

    {{-- Search --}}
    <div class="card mb-4">
        <div class="card-body py-2">
            <form method="GET" class="d-flex gap-2">
                <input type="text" name="search" class="form-control" placeholder="Search by name, email, or phone..." value="{{ request('search') }}">
                <button class="btn btn-vip"><i class="bi bi-search"></i></button>
                @if(request('search'))
                    <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">Clear</a>
                @endif
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            @if($customers->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-people fs-1 d-block mb-2"></i>
                    No customers found.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>City</th>
                                <th>Joined</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customers as $customer)
                                <tr>
                                    <td class="fw-semibold">{{ $customer->name }}</td>
                                    <td>
                                        @if($customer->customer_type === 'business')
                                            <span class="badge bg-primary"><i class="bi bi-building me-1"></i>Business</span>
                                        @else
                                            <span class="badge bg-secondary"><i class="bi bi-house-door me-1"></i>Homeowner</span>
                                        @endif
                                    </td>
                                    <td>{{ $customer->email }}</td>
                                    <td>{{ $customer->phone ?: '—' }}</td>
                                    <td>{{ $customer->city ? $customer->city . ', ' . $customer->state : '—' }}</td>
                                    <td class="text-muted small">{{ $customer->created_at->format('M d, Y') }}</td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-secondary view-customer-btn" data-customer-id="{{ $customer->id }}" title="View">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editCustomer{{ $customer->id }}" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <a href="{{ route('admin.email.compose', ['to' => $customer->email]) }}" class="btn btn-sm btn-outline-success" title="Email">
                                            <i class="bi bi-envelope"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.customers.destroy', $customer->id) }}" class="d-inline" onsubmit="return confirm('Remove this customer?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>

                                {{-- Edit modal --}}
                                <div class="modal fade" id="editCustomer{{ $customer->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('admin.customers.update', $customer->id) }}">
                                                @csrf @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title"><i class="bi bi-pencil me-1"></i> Edit Customer</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                                            <input type="text" name="name" class="form-control" value="{{ $customer->name }}" required>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Email <span class="text-danger">*</span></label>
                                                            <input type="email" name="email" class="form-control" value="{{ $customer->email }}" required>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Phone</label>
                                                            <input type="text" name="phone" class="form-control" value="{{ $customer->phone }}">
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Customer Type</label>
                                                            <select name="customer_type" class="form-select">
                                                                <option value="homeowner" {{ $customer->customer_type === 'homeowner' ? 'selected' : '' }}>Homeowner</option>
                                                                <option value="business" {{ $customer->customer_type === 'business' ? 'selected' : '' }}>Business</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <hr class="my-2">
                                                    <div class="mb-3">
                                                        <label class="form-label">Street Address</label>
                                                        <input type="text" name="address" class="form-control" value="{{ $customer->address }}">
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-5 mb-3">
                                                            <label class="form-label">City</label>
                                                            <input type="text" name="city" class="form-control" value="{{ $customer->city }}">
                                                        </div>
                                                        <div class="col-md-4 mb-3">
                                                            <label class="form-label">State</label>
                                                            <input type="text" name="state" class="form-control" value="{{ $customer->state }}">
                                                        </div>
                                                        <div class="col-md-3 mb-3">
                                                            <label class="form-label">ZIP</label>
                                                            <input type="text" name="zip" class="form-control" value="{{ $customer->zip }}">
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Notes</label>
                                                        <textarea name="notes" class="form-control" rows="2">{{ $customer->notes }}</textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" class="btn btn-vip">Save Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3">{{ $customers->appends(request()->query())->links() }}</div>
            @endif
        </div>
    </div>
</div>

{{-- Add Customer modal --}}
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

{{-- View Customer Detail modal (AJAX-loaded) --}}
<div class="modal fade" id="viewCustomerModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person me-1"></i> <span id="vcName">Customer</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                {{-- Customer info bar --}}
                <div class="p-3 bg-light border-bottom">
                    <div class="row g-3">
                        <div class="col-md-3"><span class="text-muted small d-block">Email</span><span id="vcEmail" class="fw-semibold">—</span></div>
                        <div class="col-md-2"><span class="text-muted small d-block">Phone</span><span id="vcPhone" class="fw-semibold">—</span></div>
                        <div class="col-md-3"><span class="text-muted small d-block">Address</span><span id="vcAddress" class="fw-semibold">—</span></div>
                        <div class="col-md-2"><span class="text-muted small d-block">Type</span><span id="vcType" class="fw-semibold">—</span></div>
                        <div class="col-md-2"><span class="text-muted small d-block">Since</span><span id="vcSince" class="fw-semibold">—</span></div>
                    </div>
                </div>

                {{-- Tabs --}}
                <ul class="nav nav-tabs px-3 pt-3" id="vcTabs" role="tablist">
                    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#vcJobs"><i class="bi bi-tools me-1"></i> Jobs <span class="badge bg-secondary ms-1" id="vcJobsCount">0</span></a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#vcQuotes"><i class="bi bi-calculator me-1"></i> Quotes <span class="badge bg-secondary ms-1" id="vcQuotesCount">0</span></a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#vcInvoices"><i class="bi bi-receipt me-1"></i> Invoices <span class="badge bg-secondary ms-1" id="vcInvoicesCount">0</span></a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#vcOrders"><i class="bi bi-clipboard-check me-1"></i> Orders <span class="badge bg-secondary ms-1" id="vcOrdersCount">0</span></a></li>
                </ul>

                <div class="tab-content p-3">
                    {{-- Jobs tab --}}
                    <div class="tab-pane fade show active" id="vcJobs">
                        <div id="vcJobsEmpty" class="text-center py-4 text-muted" style="display:none;">
                            <i class="bi bi-tools fs-2 d-block mb-2"></i> No jobs for this customer.
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0" id="vcJobsTable" style="display:none;">
                                <thead class="table-light">
                                    <tr><th>Job #</th><th>Status</th><th>Priority</th><th>Address</th><th>Assigned To</th><th>Scheduled</th><th>Created</th></tr>
                                </thead>
                                <tbody id="vcJobsBody"></tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Quotes tab --}}
                    <div class="tab-pane fade" id="vcQuotes">
                        <div id="vcQuotesEmpty" class="text-center py-4 text-muted" style="display:none;">
                            <i class="bi bi-calculator fs-2 d-block mb-2"></i> No quotes for this customer.
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0" id="vcQuotesTable" style="display:none;">
                                <thead class="table-light">
                                    <tr><th>Quote #</th><th>Status</th><th>Items</th><th>Total</th><th>Date</th></tr>
                                </thead>
                                <tbody id="vcQuotesBody"></tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Invoices tab --}}
                    <div class="tab-pane fade" id="vcInvoices">
                        <div id="vcInvoicesEmpty" class="text-center py-4 text-muted" style="display:none;">
                            <i class="bi bi-receipt fs-2 d-block mb-2"></i> No invoices for this customer.
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0" id="vcInvoicesTable" style="display:none;">
                                <thead class="table-light">
                                    <tr><th>Invoice #</th><th>Status</th><th>Total</th><th>Balance</th><th>Due Date</th><th>Date</th></tr>
                                </thead>
                                <tbody id="vcInvoicesBody"></tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Orders tab --}}
                    <div class="tab-pane fade" id="vcOrders">
                        <div id="vcOrdersEmpty" class="text-center py-4 text-muted" style="display:none;">
                            <i class="bi bi-clipboard-check fs-2 d-block mb-2"></i> No installation orders for this customer.
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0" id="vcOrdersTable" style="display:none;">
                                <thead class="table-light">
                                    <tr><th>Order #</th><th>Status</th><th>Address</th><th>Scheduled</th><th>Created</th></tr>
                                </thead>
                                <tbody id="vcOrdersBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Loading overlay --}}
                <div id="vcLoader" class="text-center py-5" style="display:none;">
                    <div class="spinner-border text-secondary" role="status"></div>
                    <div class="text-muted mt-2 small">Loading customer data…</div>
                </div>
            </div>
            <div class="modal-footer">
                <a href="#" id="vcEmailBtn" class="btn btn-outline-success btn-sm"><i class="bi bi-envelope me-1"></i> Email</a>
                <a href="#" id="vcCreateJobBtn" class="btn btn-outline-primary btn-sm"><i class="bi bi-tools me-1"></i> Create Job</a>
                <a href="#" id="vcCreateInvoiceBtn" class="btn btn-outline-warning btn-sm"><i class="bi bi-receipt me-1"></i> Create Invoice</a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const statusColors = {
    draft: 'secondary', sent: 'primary', paid: 'success', partial: 'warning', overdue: 'danger', cancelled: 'dark',
    pending: 'warning', scheduled: 'info', in_progress: 'primary', completed: 'success'
};
const priorityColors = { low: 'secondary', normal: 'primary', high: 'warning', urgent: 'danger' };

document.querySelectorAll('.view-customer-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.customerId;
        const modal = new bootstrap.Modal(document.getElementById('viewCustomerModal'));

        // Show loader, hide content
        document.getElementById('vcLoader').style.display = '';
        document.querySelectorAll('#vcTabs, .tab-content').forEach(el => el.style.display = 'none');

        modal.show();

        fetch(`/admin/customers/${id}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            const c = data.customer;
            document.getElementById('vcName').textContent = c.name;
            document.getElementById('vcEmail').textContent = c.email;
            document.getElementById('vcPhone').textContent = c.phone || '—';
            document.getElementById('vcAddress').textContent = c.address ? `${c.address}, ${c.city}, ${c.state} ${c.zip}` : '—';
            document.getElementById('vcType').innerHTML = c.customer_type === 'business'
                ? '<span class="badge bg-primary">Business</span>'
                : '<span class="badge bg-secondary">Homeowner</span>';
            document.getElementById('vcSince').textContent = new Date(c.created_at).toLocaleDateString('en-US', { year:'numeric', month:'short', day:'numeric' });

            // Footer action links
            document.getElementById('vcEmailBtn').href = `/admin/email/sent?to=${encodeURIComponent(c.email)}`;

            // Jobs
            fillTable('vcJobs', data.jobs, ['job_number', 'status', 'priority', 'install_address', 'assigned_to', 'scheduled_date', 'created_at'], row => {
                const statusBadge = `<span class="badge bg-${statusColors[row.status] || 'secondary'}">${row.status.replace('_', ' ')}</span>`;
                const priorBadge = `<span class="badge bg-${priorityColors[row.priority] || 'secondary'}">${row.priority}</span>`;
                return [row.job_number, statusBadge, priorBadge, row.install_address || '—', row.assigned_to, row.scheduled_date || '—', row.created_at];
            });

            // Quotes
            fillTable('vcQuotes', data.quotes, ['quote_number', 'status', 'items_count', 'total', 'created_at'], row => {
                const badge = `<span class="badge bg-${statusColors[row.status] || 'secondary'}">${row.status}</span>`;
                return [row.quote_number, badge, row.items_count + ' item(s)', '$' + row.total, row.created_at];
            });

            // Invoices
            fillTable('vcInvoices', data.invoices, ['invoice_number', 'status', 'total', 'balance_due', 'due_date', 'created_at'], row => {
                const badge = `<span class="badge bg-${statusColors[row.status] || 'secondary'}">${row.status}</span>`;
                return [row.invoice_number, badge, '$' + row.total, '$' + row.balance_due, row.due_date || '—', row.created_at];
            });

            // Orders
            fillTable('vcOrders', data.orders, ['id', 'status', 'install_address', 'scheduled_date', 'created_at'], row => {
                const badge = `<span class="badge bg-${statusColors[row.status] || 'secondary'}">${row.status.replace('_', ' ')}</span>`;
                return ['#' + row.id, badge, row.install_address, row.scheduled_date || '—', row.created_at];
            });

            document.getElementById('vcLoader').style.display = 'none';
            document.querySelectorAll('#vcTabs, .tab-content').forEach(el => el.style.display = '');
        });
    });
});

function fillTable(prefix, rows, cols, mapper) {
    const tbody = document.getElementById(prefix + 'Body');
    const table = document.getElementById(prefix + 'Table');
    const empty = document.getElementById(prefix + 'Empty');
    const count = document.getElementById(prefix + 'Count');

    count.textContent = rows.length;
    tbody.innerHTML = '';

    if (rows.length === 0) {
        table.style.display = 'none';
        empty.style.display = '';
    } else {
        table.style.display = '';
        empty.style.display = 'none';
        rows.forEach(row => {
            const cells = mapper(row);
            const tr = document.createElement('tr');
            cells.forEach(cell => {
                const td = document.createElement('td');
                td.innerHTML = cell;
                tr.appendChild(td);
            });
            tbody.appendChild(tr);
        });
    }
}
</script>
@endpush
@endsection
