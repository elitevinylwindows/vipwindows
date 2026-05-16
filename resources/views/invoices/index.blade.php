@extends('layouts.app')
@section('title', 'Invoices')

@section('content')
<div class="container-fluid py-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><i class="bi bi-receipt me-2"></i>Invoices</h4>
        <button class="btn btn-vip" data-bs-toggle="modal" data-bs-target="#createInvoiceModal">
            <i class="bi bi-plus-circle me-1"></i> New Invoice
        </button>
    </div>

    {{-- Status filter --}}
    <div class="mb-4">
        <div class="btn-group btn-group-sm" role="group">
            <a href="{{ route('admin.invoices.index') }}" class="btn {{ $status === 'all' ? 'btn-dark' : 'btn-outline-dark' }}">All</a>
            <a href="{{ route('admin.invoices.index', ['status' => 'draft']) }}" class="btn {{ $status === 'draft' ? 'btn-dark' : 'btn-outline-dark' }}">Draft</a>
            <a href="{{ route('admin.invoices.index', ['status' => 'sent']) }}" class="btn {{ $status === 'sent' ? 'btn-dark' : 'btn-outline-dark' }}">Sent</a>
            <a href="{{ route('admin.invoices.index', ['status' => 'paid']) }}" class="btn {{ $status === 'paid' ? 'btn-dark' : 'btn-outline-dark' }}">Paid</a>
            <a href="{{ route('admin.invoices.index', ['status' => 'overdue']) }}" class="btn {{ $status === 'overdue' ? 'btn-dark' : 'btn-outline-dark' }}">Overdue</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            @if($invoices->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-receipt fs-1 d-block mb-2"></i>
                    No invoices yet. Create your first one.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Invoice #</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Due Date</th>
                                <th class="text-end">Total</th>
                                <th class="text-end">Paid</th>
                                <th class="text-end">Balance</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoices as $invoice)
                                <tr>
                                    <td class="fw-semibold">{{ $invoice->invoice_number }}</td>
                                    <td>{{ $invoice->customer_name }}</td>
                                    <td class="text-muted small">{{ $invoice->created_at?->format('M d, Y') }}</td>
                                    <td class="text-muted small">{{ $invoice->due_date?->format('M d, Y') ?? '—' }}</td>
                                    <td class="text-end">${{ number_format($invoice->total, 2) }}</td>
                                    <td class="text-end">${{ number_format($invoice->amount_paid, 2) }}</td>
                                    <td class="text-end fw-semibold">${{ number_format($invoice->balance_due, 2) }}</td>
                                    <td>
                                        @switch($invoice->status)
                                            @case('draft')
                                                <span class="badge bg-secondary">Draft</span>
                                                @break
                                            @case('sent')
                                                <span class="badge bg-primary">Sent</span>
                                                @break
                                            @case('paid')
                                                <span class="badge bg-success">Paid</span>
                                                @break
                                            @case('partial')
                                                <span class="badge bg-warning text-dark">Partial</span>
                                                @break
                                            @case('overdue')
                                                <span class="badge bg-danger">Overdue</span>
                                                @break
                                            @case('cancelled')
                                                <span class="badge bg-dark">Cancelled</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-secondary view-invoice-btn" data-invoice-id="{{ $invoice->id }}" title="View">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editInvoice{{ $invoice->id }}" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#sendInvoice{{ $invoice->id }}" title="Send">
                                            <i class="bi bi-envelope"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#paymentInvoice{{ $invoice->id }}" title="Record Payment">
                                            <i class="bi bi-cash"></i>
                                        </button>
                                        <form method="POST" action="{{ route('admin.invoices.destroy', $invoice->id) }}" class="d-inline" onsubmit="return confirm('Delete this invoice?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>

                                {{-- Edit modal --}}
                                <div class="modal fade" id="editInvoice{{ $invoice->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('admin.invoices.update', $invoice->id) }}">
                                                @csrf @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title"><i class="bi bi-pencil me-1"></i> Edit Invoice {{ $invoice->invoice_number }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Customer Name <span class="text-danger">*</span></label>
                                                            <input type="text" name="customer_name" class="form-control" value="{{ $invoice->customer_name }}" required>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Email</label>
                                                            <input type="email" name="customer_email" class="form-control" value="{{ $invoice->customer_email }}">
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Phone</label>
                                                            <input type="text" name="customer_phone" class="form-control" value="{{ $invoice->customer_phone }}">
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Due Date</label>
                                                            <input type="date" name="due_date" class="form-control" value="{{ $invoice->due_date?->format('Y-m-d') }}">
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Customer Address</label>
                                                        <input type="text" name="customer_address" class="form-control" value="{{ $invoice->customer_address }}">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Billing Address</label>
                                                        <input type="text" name="billing_address" class="form-control" value="{{ $invoice->billing_address }}">
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Tax Rate (%)</label>
                                                            <input type="number" step="0.01" name="tax_rate" class="form-control" value="{{ $invoice->tax_rate }}">
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Status</label>
                                                            <select name="status" class="form-select">
                                                                <option value="draft" {{ $invoice->status === 'draft' ? 'selected' : '' }}>Draft</option>
                                                                <option value="sent" {{ $invoice->status === 'sent' ? 'selected' : '' }}>Sent</option>
                                                                <option value="paid" {{ $invoice->status === 'paid' ? 'selected' : '' }}>Paid</option>
                                                                <option value="partial" {{ $invoice->status === 'partial' ? 'selected' : '' }}>Partial</option>
                                                                <option value="overdue" {{ $invoice->status === 'overdue' ? 'selected' : '' }}>Overdue</option>
                                                                <option value="cancelled" {{ $invoice->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Notes</label>
                                                        <textarea name="notes" class="form-control" rows="2">{{ $invoice->notes }}</textarea>
                                                    </div>

                                                    {{-- Line items --}}
                                                    <hr>
                                                    <h6 class="fw-semibold mb-3">Line Items</h6>
                                                    @if($invoice->items->count())
                                                        <table class="table table-sm mb-3">
                                                            <thead>
                                                                <tr>
                                                                    <th>Description</th>
                                                                    <th class="text-center">Qty</th>
                                                                    <th class="text-end">Unit Price</th>
                                                                    <th class="text-end">Total</th>
                                                                    <th></th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($invoice->items as $item)
                                                                    <tr>
                                                                        <td>{{ $item->description }}</td>
                                                                        <td class="text-center">{{ $item->qty }}</td>
                                                                        <td class="text-end">${{ number_format($item->unit_price, 2) }}</td>
                                                                        <td class="text-end">${{ number_format($item->total, 2) }}</td>
                                                                        <td class="text-end">
                                                                            <button type="button" class="btn btn-sm btn-outline-danger remove-item-btn" data-invoice-id="{{ $invoice->id }}" data-item-id="{{ $item->id }}">
                                                                                <i class="bi bi-x"></i>
                                                                            </button>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    @else
                                                        <p class="text-muted small">No items yet.</p>
                                                    @endif
                                                    <button type="button" class="btn btn-sm btn-outline-dark" data-bs-toggle="modal" data-bs-target="#addItemInvoice{{ $invoice->id }}" onclick="bootstrap.Modal.getInstance(document.getElementById('editInvoice{{ $invoice->id }}')).hide()">
                                                        <i class="bi bi-plus me-1"></i> Add Item
                                                    </button>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" class="btn btn-vip">Save Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                {{-- Add Item modal --}}
                                <div class="modal fade" id="addItemInvoice{{ $invoice->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('admin.invoices.addItem', $invoice->id) }}">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title"><i class="bi bi-plus-circle me-1"></i> Add Item to {{ $invoice->invoice_number }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Description <span class="text-danger">*</span></label>
                                                        <input type="text" name="description" class="form-control" required>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-4 mb-3">
                                                            <label class="form-label">Qty <span class="text-danger">*</span></label>
                                                            <input type="number" step="0.01" name="qty" class="form-control item-qty" value="1" required>
                                                        </div>
                                                        <div class="col-md-4 mb-3">
                                                            <label class="form-label">Unit Price <span class="text-danger">*</span></label>
                                                            <input type="number" step="0.01" name="unit_price" class="form-control item-unit-price" value="0" required>
                                                        </div>
                                                        <div class="col-md-4 mb-3">
                                                            <label class="form-label">Total</label>
                                                            <input type="text" class="form-control item-total-display" readonly value="$0.00">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" class="btn btn-vip"><i class="bi bi-plus me-1"></i> Add Item</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                {{-- Send Invoice modal --}}
                                <div class="modal fade" id="sendInvoice{{ $invoice->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title"><i class="bi bi-envelope me-1"></i> Send Invoice {{ $invoice->invoice_number }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Customer Email</label>
                                                    <input type="email" class="form-control send-invoice-email" value="{{ $invoice->customer_email }}" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-vip send-invoice-btn" data-invoice-id="{{ $invoice->id }}">
                                                    <i class="bi bi-send me-1"></i> Send Invoice
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Record Payment modal --}}
                                <div class="modal fade" id="paymentInvoice{{ $invoice->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title"><i class="bi bi-cash me-1"></i> Record Payment — {{ $invoice->invoice_number }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p class="text-muted small mb-3">Balance Due: <strong>${{ number_format($invoice->balance_due, 2) }}</strong></p>
                                                <div class="mb-3">
                                                    <label class="form-label">Amount <span class="text-danger">*</span></label>
                                                    <input type="number" step="0.01" class="form-control payment-amount" value="{{ number_format($invoice->balance_due, 2, '.', '') }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Payment Method</label>
                                                    <select class="form-select payment-method">
                                                        <option value="cash">Cash</option>
                                                        <option value="check">Check</option>
                                                        <option value="card">Card</option>
                                                        <option value="transfer">Transfer</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Payment Date</label>
                                                    <input type="date" class="form-control payment-date" value="{{ date('Y-m-d') }}">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Note</label>
                                                    <input type="text" class="form-control payment-note" placeholder="Optional payment note">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-vip record-payment-btn" data-invoice-id="{{ $invoice->id }}">
                                                    <i class="bi bi-check-circle me-1"></i> Record Payment
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3">{{ $invoices->appends(request()->query())->links() }}</div>
            @endif
        </div>
    </div>
</div>

{{-- View Invoice modal (shared, populated via JS) --}}
<div class="modal fade" id="viewInvoiceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-receipt me-1"></i> <span id="viewInvoiceTitle"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewInvoiceBody">
                <div class="text-center py-4"><div class="spinner-border text-muted" role="status"></div></div>
            </div>
        </div>
    </div>
</div>

{{-- Create Invoice modal --}}
<div class="modal fade" id="createInvoiceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.invoices.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-1"></i> New Invoice</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Create from Quote</label>
                        <select name="from_quote" class="form-select" id="createFromQuote">
                            <option value="">— Blank Invoice —</option>
                            @foreach($quotes as $q)
                                <option value="{{ $q->id }}" data-name="{{ $q->billing_name }}" data-email="" data-phone="">
                                    {{ $q->quote_number }} — {{ $q->billing_name ?: 'No Name' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Customer Name <span class="text-danger">*</span></label>
                            <input type="text" name="customer_name" class="form-control" id="createCustName" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="customer_email" class="form-control" id="createCustEmail">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="customer_phone" class="form-control" id="createCustPhone">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Due Date</label>
                            <input type="date" name="due_date" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Customer Address</label>
                        <input type="text" name="customer_address" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Billing Address</label>
                        <input type="text" name="billing_address" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tax Rate (%)</label>
                        <input type="number" step="0.01" name="tax_rate" class="form-control" value="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-vip"><i class="bi bi-plus-circle me-1"></i> Create Invoice</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
const csrfToken = '{{ csrf_token() }}';

// Auto-fill customer name from quote selection
document.getElementById('createFromQuote')?.addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    if (opt.dataset.name) {
        document.getElementById('createCustName').value = opt.dataset.name;
    }
});

// View invoice
document.querySelectorAll('.view-invoice-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.invoiceId;
        document.getElementById('viewInvoiceTitle').textContent = 'Loading...';
        document.getElementById('viewInvoiceBody').innerHTML = '<div class="text-center py-4"><div class="spinner-border text-muted" role="status"></div></div>';
        new bootstrap.Modal(document.getElementById('viewInvoiceModal')).show();

        fetch(`/admin/invoices/${id}`)
            .then(r => r.json())
            .then(data => {
                const inv = data.invoice;
                document.getElementById('viewInvoiceTitle').textContent = 'Invoice ' + inv.invoice_number;

                let itemsHtml = '';
                if (data.items && data.items.length) {
                    itemsHtml = `<table class="table table-sm">
                        <thead><tr><th>Description</th><th class="text-center">Qty</th><th class="text-end">Unit Price</th><th class="text-end">Total</th></tr></thead>
                        <tbody>`;
                    data.items.forEach(item => {
                        itemsHtml += `<tr>
                            <td>${item.description}</td>
                            <td class="text-center">${item.qty}</td>
                            <td class="text-end">$${parseFloat(item.unit_price).toFixed(2)}</td>
                            <td class="text-end">$${parseFloat(item.total).toFixed(2)}</td>
                        </tr>`;
                    });
                    itemsHtml += '</tbody></table>';
                } else {
                    itemsHtml = '<p class="text-muted">No line items.</p>';
                }

                const statusBadge = {
                    draft: 'bg-secondary', sent: 'bg-primary', paid: 'bg-success',
                    partial: 'bg-warning text-dark', overdue: 'bg-danger', cancelled: 'bg-dark'
                };

                document.getElementById('viewInvoiceBody').innerHTML = `
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="text-muted small">Customer</div>
                            <div class="fw-semibold">${inv.customer_name}</div>
                            ${inv.customer_email ? '<div class="small">' + inv.customer_email + '</div>' : ''}
                            ${inv.customer_phone ? '<div class="small">' + inv.customer_phone + '</div>' : ''}
                        </div>
                        <div class="col-md-3">
                            <div class="text-muted small">Status</div>
                            <span class="badge ${statusBadge[inv.status] || 'bg-secondary'}">${inv.status.charAt(0).toUpperCase() + inv.status.slice(1)}</span>
                        </div>
                        <div class="col-md-3">
                            <div class="text-muted small">Due Date</div>
                            <div class="fw-semibold">${inv.due_date ? new Date(inv.due_date).toLocaleDateString() : '—'}</div>
                        </div>
                        ${inv.customer_address ? '<div class="col-md-6"><div class="text-muted small">Address</div><div>' + inv.customer_address + '</div></div>' : ''}
                        ${inv.billing_address ? '<div class="col-md-6"><div class="text-muted small">Billing Address</div><div>' + inv.billing_address + '</div></div>' : ''}
                    </div>
                    <h6 class="fw-semibold">Items</h6>
                    ${itemsHtml}
                    <hr>
                    <div class="row">
                        <div class="col-md-6"></div>
                        <div class="col-md-6">
                            <table class="table table-sm mb-0">
                                <tr><td>Subtotal</td><td class="text-end">$${parseFloat(inv.subtotal).toFixed(2)}</td></tr>
                                <tr><td>Tax (${parseFloat(inv.tax_rate).toFixed(2)}%)</td><td class="text-end">$${parseFloat(inv.tax_amount).toFixed(2)}</td></tr>
                                <tr class="fw-bold"><td>Total</td><td class="text-end">$${parseFloat(inv.total).toFixed(2)}</td></tr>
                                <tr><td>Amount Paid</td><td class="text-end text-success">$${parseFloat(inv.amount_paid).toFixed(2)}</td></tr>
                                <tr class="fw-bold"><td>Balance Due</td><td class="text-end text-danger">$${parseFloat(inv.balance_due).toFixed(2)}</td></tr>
                            </table>
                        </div>
                    </div>
                    ${inv.notes ? '<hr><div class="text-muted small">Notes</div><div style="white-space:pre-line">' + inv.notes + '</div>' : ''}
                `;
            });
    });
});

// Send invoice
document.querySelectorAll('.send-invoice-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const modal = this.closest('.modal');
        const email = modal.querySelector('.send-invoice-email').value;
        if (!email) return;

        const invoiceId = this.dataset.invoiceId;
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Sending...';

        fetch(`/admin/invoices/${invoiceId}/send`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ email })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(modal).hide();
                location.reload();
            } else {
                alert(data.message || 'Failed to send');
                this.disabled = false;
                this.innerHTML = '<i class="bi bi-send me-1"></i> Send Invoice';
            }
        });
    });
});

// Record payment
document.querySelectorAll('.record-payment-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const modal = this.closest('.modal');
        const invoiceId = this.dataset.invoiceId;
        const amount = modal.querySelector('.payment-amount').value;
        const method = modal.querySelector('.payment-method').value;
        const date = modal.querySelector('.payment-date').value;
        const note = modal.querySelector('.payment-note').value;

        if (!amount || parseFloat(amount) <= 0) { alert('Enter a valid amount'); return; }

        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Recording...';

        fetch(`/admin/invoices/${invoiceId}/payment`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ amount, payment_method: method, payment_date: date, payment_note: note })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(modal).hide();
                location.reload();
            } else {
                alert(data.message || 'Failed');
                this.disabled = false;
                this.innerHTML = '<i class="bi bi-check-circle me-1"></i> Record Payment';
            }
        });
    });
});

// Remove item
document.querySelectorAll('.remove-item-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        if (!confirm('Remove this item?')) return;

        const invoiceId = this.dataset.invoiceId;
        const itemId = this.dataset.itemId;

        fetch(`/admin/invoices/${invoiceId}/item/${itemId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) location.reload();
        });
    });
});

// Auto-calc item total
document.querySelectorAll('.item-qty, .item-unit-price').forEach(input => {
    input.addEventListener('input', function() {
        const parent = this.closest('.modal-body');
        const qty = parseFloat(parent.querySelector('.item-qty').value) || 0;
        const price = parseFloat(parent.querySelector('.item-unit-price').value) || 0;
        parent.querySelector('.item-total-display').value = '$' + (qty * price).toFixed(2);
    });
});
</script>
@endpush
@endsection
