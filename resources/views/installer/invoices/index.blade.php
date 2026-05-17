@extends('layouts.installer')
@section('title', 'My Invoices')

@push('styles')
<style>
    .iq-container { display: flex; height: calc(100vh - 56px); overflow: hidden; }

    .iq-rail {
        width: 320px; min-width: 320px;
        background: var(--vip-primary);
        color: #fff;
        display: flex; flex-direction: column;
        border-right: 1px solid rgba(255,255,255,.06);
    }
    .iq-rail-header { padding: 1.25rem 1rem .75rem; }
    .iq-rail-header h6 { font-size: .75rem; text-transform: uppercase; letter-spacing: 1.2px; color: rgba(255,255,255,.5); margin-bottom: .75rem; }
    .iq-rail-search { display: flex; gap: .5rem; }
    .iq-rail-search input {
        flex: 1; background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.12);
        color: #fff; border-radius: .375rem; padding: .4rem .75rem; font-size: .85rem;
    }
    .iq-rail-search input::placeholder { color: rgba(255,255,255,.4); }
    .iq-rail-search input:focus { outline: none; border-color: var(--vip-accent); }

    .iq-rail-tabs { display: flex; gap: 0; padding: 0 1rem; margin-top: .75rem; flex-wrap: wrap; }
    .iq-rail-tabs .tab-btn {
        flex: 1; text-align: center; padding: .4rem .25rem; font-size: .7rem;
        background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.1);
        color: rgba(255,255,255,.6); cursor: pointer; transition: all .15s;
    }
    .iq-rail-tabs .tab-btn:first-child { border-radius: .3rem 0 0 .3rem; }
    .iq-rail-tabs .tab-btn:last-child { border-radius: 0 .3rem .3rem 0; }
    .iq-rail-tabs .tab-btn.active { background: var(--vip-accent); color: #fff; border-color: var(--vip-accent); }

    .iq-rail-list { flex: 1; overflow-y: auto; padding: .5rem; }
    .iq-card {
        background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.08);
        border-radius: .5rem; padding: .75rem 1rem; margin-bottom: .5rem;
        cursor: pointer; transition: all .15s;
    }
    .iq-card:hover { background: rgba(255,255,255,.08); border-color: rgba(201,168,76,.3); }
    .iq-card.active { background: rgba(201,168,76,.12); border-color: var(--vip-accent); }
    .iq-card .q-number { font-weight: 600; font-size: .9rem; color: #fff; }
    .iq-card .q-customer { font-size: .78rem; color: rgba(255,255,255,.55); margin-top: 2px; }
    .iq-card .q-meta { display: flex; justify-content: space-between; align-items: center; margin-top: .35rem; }
    .iq-card .q-date { font-size: .7rem; color: rgba(255,255,255,.4); }
    .iq-card .q-amount { font-size: .8rem; color: var(--vip-accent); font-weight: 600; }
    .iq-card .q-badge { font-size: .6rem; padding: 2px 6px; border-radius: 3px; font-weight: 600; text-transform: uppercase; }
    .q-badge-draft { background: rgba(108,117,125,.25); color: #adb5bd; }
    .q-badge-sent { background: rgba(0,123,255,.25); color: #5ba8ff; }
    .q-badge-paid { background: rgba(40,167,69,.25); color: #7ddf9b; }
    .q-badge-partial { background: rgba(255,193,7,.25); color: #ffc107; }
    .q-badge-overdue { background: rgba(220,53,69,.25); color: #dc3545; }
    .q-badge-cancelled { background: rgba(108,117,125,.25); color: #adb5bd; }

    .iq-rail-footer {
        padding: .75rem 1rem; border-top: 1px solid rgba(255,255,255,.08);
        font-size: .75rem; color: rgba(255,255,255,.4);
        display: flex; justify-content: space-between;
    }

    .iq-main { flex: 1; overflow-y: auto; background: var(--vip-light); }
    .iq-main-toolbar {
        background: #fff; border-bottom: 1px solid rgba(0,0,0,.06);
        padding: .75rem 1.5rem; display: flex; align-items: center; justify-content: space-between;
    }
    .iq-main-toolbar h5 { font-size: 1rem; font-weight: 700; margin: 0; }
    .iq-detail-body { padding: 1.5rem; }

    .iq-empty-state {
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        height: 60vh; color: rgba(0,0,0,.35);
    }
    .iq-empty-state i { font-size: 3rem; margin-bottom: 1rem; }

    .iq-info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
    .iq-info-card { background: #fff; border-radius: .5rem; padding: 1rem; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
    .iq-info-card .label { font-size: .7rem; text-transform: uppercase; letter-spacing: .5px; color: rgba(0,0,0,.45); margin-bottom: .25rem; }
    .iq-info-card .value { font-size: .9rem; font-weight: 600; color: #111; }

    .inv-items-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: .5rem; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
    .inv-items-table th { font-size: .7rem; text-transform: uppercase; letter-spacing: .5px; color: rgba(0,0,0,.4); padding: .6rem 1rem; border-bottom: 1px solid rgba(0,0,0,.08); background: #fafafa; }
    .inv-items-table td { padding: .6rem 1rem; font-size: .85rem; border-bottom: 1px solid rgba(0,0,0,.04); }

    @media (max-width: 991.98px) {
        .iq-container { flex-direction: column; height: auto; }
        .iq-rail { width: 100%; min-width: 100%; max-height: 45vh; }
    }
</style>
@endpush

@section('content')
<div class="iq-container">
    {{-- Left Rail --}}
    <div class="iq-rail">
        <div class="iq-rail-header">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">My Invoices</h6>
                <button class="btn btn-sm btn-vip" data-bs-toggle="modal" data-bs-target="#createInvoiceModal"><i class="bi bi-plus-lg me-1"></i>New Invoice</button>
            </div>
            <div class="iq-rail-search">
                <input type="text" id="iqSearch" placeholder="Search invoices...">
            </div>
            <div class="iq-rail-tabs">
                <div class="tab-btn {{ $status === 'all' ? 'active' : '' }}" data-status="all">All</div>
                <div class="tab-btn {{ $status === 'draft' ? 'active' : '' }}" data-status="draft">Draft</div>
                <div class="tab-btn {{ $status === 'sent' ? 'active' : '' }}" data-status="sent">Sent</div>
                <div class="tab-btn {{ $status === 'paid' ? 'active' : '' }}" data-status="paid">Paid</div>
                <div class="tab-btn {{ $status === 'overdue' ? 'active' : '' }}" data-status="overdue">Due</div>
            </div>
        </div>

        <div class="iq-rail-list">
            @forelse($invoices as $invoice)
                <div class="iq-card" data-id="{{ $invoice->id }}" data-search="{{ strtolower(($invoice->invoice_number ?? '') . ' ' . ($invoice->customer_name ?? '')) }}">
                    <div class="q-number">{{ $invoice->invoice_number }}</div>
                    <div class="q-customer"><i class="bi bi-person me-1"></i>{{ $invoice->customer_name ?: 'No customer' }}</div>
                    <div class="q-meta">
                        <span class="q-amount">${{ number_format($invoice->total, 2) }}</span>
                        <span class="q-badge q-badge-{{ $invoice->status }}">{{ ucfirst($invoice->status) }}</span>
                    </div>
                    <div class="q-date mt-1">{{ $invoice->created_at?->format('M d, Y') }}</div>
                </div>
            @empty
                <div class="text-center py-4" style="color:rgba(255,255,255,.4);">
                    <i class="bi bi-receipt" style="font-size:2rem;"></i>
                    <p class="mt-2 mb-0">No invoices yet</p>
                </div>
            @endforelse
        </div>

        <div class="iq-rail-footer">
            <span>{{ $invoices->total() }} invoice{{ $invoices->total() !== 1 ? 's' : '' }}</span>
            <span>${{ number_format($invoices->where('status', 'paid')->sum('total'), 2) }} earned</span>
        </div>
    </div>

    {{-- Main Panel --}}
    <div class="iq-main">
        <div class="iq-main-toolbar">
            <h5 id="iqDetailTitle">Invoice Details</h5>
            <div id="iqToolbarActions"></div>
        </div>
        <div class="iq-detail-body" id="iqDetailBody">
            <div class="iq-empty-state">
                <i class="bi bi-receipt"></i>
                <p>Select an invoice to view details</p>
            </div>
        </div>
    </div>
</div>
{{-- Create Invoice Modal --}}
<div class="modal fade" id="createInvoiceModal" tabindex="-1" aria-labelledby="createInvoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="background:var(--vip-primary); color:#fff; border:1px solid rgba(255,255,255,.1);">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="createInvoiceModalLabel"><i class="bi bi-receipt me-2"></i>New Invoice</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="createInvoiceForm" method="POST" action="{{ route('installer.invoices.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small text-white-50">Customer Name <span class="text-danger">*</span></label>
                            <input type="text" name="customer_name" class="form-control bg-dark text-white border-secondary" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-white-50">Customer Email</label>
                            <input type="email" name="customer_email" class="form-control bg-dark text-white border-secondary">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-white-50">Customer Phone</label>
                            <input type="text" name="customer_phone" class="form-control bg-dark text-white border-secondary">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-white-50">Due Date</label>
                            <input type="date" name="due_date" class="form-control bg-dark text-white border-secondary">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small text-white-50">Customer Address</label>
                            <input type="text" name="customer_address" class="form-control bg-dark text-white border-secondary" placeholder="Customer address">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small text-white-50">Billing Address</label>
                            <input type="text" name="billing_address" class="form-control bg-dark text-white border-secondary" placeholder="Billing address (if different)">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-white-50">Tax Rate (%)</label>
                            <input type="number" name="tax_rate" class="form-control bg-dark text-white border-secondary" value="0" step="0.01" min="0" max="100">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small text-white-50">Notes</label>
                            <input type="text" name="notes" class="form-control bg-dark text-white border-secondary" placeholder="Optional notes">
                        </div>
                    </div>
                    <p class="small text-white-50 mt-3 mb-0"><i class="bi bi-info-circle me-1"></i>You can add line items after creating the invoice.</p>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-vip btn-sm"><i class="bi bi-check-lg me-1"></i>Create Invoice</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.iq-card');
    const detailBody = document.getElementById('iqDetailBody');
    const detailTitle = document.getElementById('iqDetailTitle');
    const toolbarActions = document.getElementById('iqToolbarActions');
    const csrf = document.querySelector('meta[name=csrf-token]').content;
    let currentInvoiceId = null;

    // Tab filters
    document.querySelectorAll('.iq-rail-tabs .tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const status = this.dataset.status;
            const url = new URL(window.location);
            if (status !== 'all') url.searchParams.set('status', status);
            else url.searchParams.delete('status');
            window.location = url;
        });
    });

    // Search
    document.getElementById('iqSearch').addEventListener('input', function() {
        const term = this.value.toLowerCase();
        document.querySelectorAll('.iq-card').forEach(card => {
            card.style.display = (!term || card.dataset.search.includes(term)) ? '' : 'none';
        });
    });

    // Load detail
    cards.forEach(card => {
        card.addEventListener('click', function() {
            cards.forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            loadDetail(this.dataset.id);
        });
    });

    function loadDetail(id) {
        detailBody.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-secondary"></div></div>';

        fetch(`/installer/invoices/${id}`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }})
            .then(r => r.json())
            .then(data => {
                const inv = data.invoice;
                const items = data.items || [];
                detailTitle.textContent = inv.invoice_number;

                currentInvoiceId = inv.id;
                let tbActions = `<span class="badge ${inv.status === 'paid' ? 'bg-success' : inv.status === 'overdue' ? 'bg-danger' : inv.status === 'sent' ? 'bg-primary' : 'bg-secondary'} me-2">${inv.status ? inv.status.charAt(0).toUpperCase() + inv.status.slice(1) : 'Draft'}</span>`;
                if (inv.customer_email && inv.status !== 'paid') {
                    tbActions += `<button class="btn btn-sm btn-vip" onclick="sendInvoice(${inv.id})"><i class="bi bi-send me-1"></i>Send</button>`;
                }
                toolbarActions.innerHTML = tbActions;

                let itemsHtml = '';
                if (items.length) {
                    itemsHtml = `<table class="inv-items-table">
                        <thead><tr><th>Description</th><th class="text-end">Qty</th><th class="text-end">Rate</th><th class="text-end">Amount</th><th style="width:40px;"></th></tr></thead>
                        <tbody>${items.map(i => `<tr>
                            <td>${i.description || i.name || 'Item'}</td>
                            <td class="text-end">${i.qty || i.quantity || 1}</td>
                            <td class="text-end">$${parseFloat(i.rate || i.unit_price || 0).toFixed(2)}</td>
                            <td class="text-end fw-semibold">$${parseFloat(i.amount || i.total || 0).toFixed(2)}</td>
                            <td class="text-center"><button class="btn btn-sm text-danger p-0" onclick="removeLineItem(${inv.id}, ${i.id})" title="Remove"><i class="bi bi-x-lg"></i></button></td>
                        </tr>`).join('')}</tbody>
                    </table>`;
                } else {
                    itemsHtml = '<p class="text-muted">No line items.</p>';
                }

                detailBody.innerHTML = `
                    <div class="iq-info-grid">
                        <div class="iq-info-card"><div class="label">Customer</div><div class="value">${inv.customer_name || '—'}</div></div>
                        <div class="iq-info-card"><div class="label">Total</div><div class="value" style="color:var(--vip-accent);">$${parseFloat(inv.total || 0).toFixed(2)}</div></div>
                        <div class="iq-info-card"><div class="label">Paid</div><div class="value text-success">$${parseFloat(inv.amount_paid || 0).toFixed(2)}</div></div>
                        <div class="iq-info-card"><div class="label">Balance</div><div class="value ${parseFloat(inv.balance_due || 0) > 0 ? 'text-danger' : ''}">$${parseFloat(inv.balance_due || 0).toFixed(2)}</div></div>
                        <div class="iq-info-card"><div class="label">Created</div><div class="value">${inv.created_at || '—'}</div></div>
                        <div class="iq-info-card"><div class="label">Due Date</div><div class="value">${inv.due_date || '—'}</div></div>
                    </div>

                    ${inv.notes ? `<div class="card mb-3" style="border:none; box-shadow:0 1px 4px rgba(0,0,0,.06);"><div class="card-body py-2 px-3"><div class="small text-muted text-uppercase mb-1" style="letter-spacing:.5px;">Notes</div><p class="mb-0">${inv.notes}</p></div></div>` : ''}

                    <h6 class="mb-3" style="font-size:.8rem; text-transform:uppercase; letter-spacing:.5px; color:rgba(0,0,0,.5);"><i class="bi bi-list-ul me-1"></i>Line Items</h6>
                    ${itemsHtml}

                    <div class="card mt-3" style="border:none; box-shadow:0 1px 4px rgba(0,0,0,.06);">
                        <div class="card-body py-2 px-3">
                            <div class="small text-muted text-uppercase mb-2" style="letter-spacing:.5px;">Add Line Item</div>
                            <div class="row g-2 align-items-end">
                                <div class="col-md-5"><input type="text" id="addItemDesc" class="form-control form-control-sm" placeholder="Description"></div>
                                <div class="col-md-2"><input type="number" id="addItemQty" class="form-control form-control-sm" placeholder="Qty" value="1" min="0.01" step="0.01"></div>
                                <div class="col-md-3"><input type="number" id="addItemPrice" class="form-control form-control-sm" placeholder="Unit Price" min="0" step="0.01"></div>
                                <div class="col-md-2"><button class="btn btn-sm btn-vip w-100" onclick="addLineItem(${inv.id})"><i class="bi bi-plus"></i> Add</button></div>
                            </div>
                        </div>
                    </div>
                `;
            })
            .catch(() => {
                detailBody.innerHTML = '<div class="alert alert-danger m-4">Failed to load invoice details.</div>';
            });
    }

    // Auto-select first
    if (cards.length > 0) cards[0].click();

    // Add line item
    window.addLineItem = function(invoiceId) {
        const desc = document.getElementById('addItemDesc')?.value?.trim();
        const qty = parseFloat(document.getElementById('addItemQty')?.value);
        const price = parseFloat(document.getElementById('addItemPrice')?.value);

        if (!desc) { alert('Please enter a description.'); return; }
        if (!qty || qty <= 0) { alert('Please enter a valid quantity.'); return; }
        if (isNaN(price) || price < 0) { alert('Please enter a valid price.'); return; }

        fetch(`/installer/invoices/${invoiceId}/item`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify({ description: desc, qty: qty, unit_price: price })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) loadDetail(invoiceId);
            else alert(data.error || 'Failed to add item.');
        })
        .catch(() => alert('Failed to add item.'));
    };

    // Remove line item
    window.removeLineItem = function(invoiceId, itemId) {
        if (!confirm('Remove this line item?')) return;
        fetch(`/installer/invoices/${invoiceId}/item/${itemId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) loadDetail(invoiceId);
        })
        .catch(() => alert('Failed to remove item.'));
    };

    // Send invoice to customer
    window.sendInvoice = function(invoiceId) {
        if (!confirm('Send this invoice to the customer?')) return;
        fetch(`/installer/invoices/${invoiceId}/send`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert('Invoice sent!');
                loadDetail(invoiceId);
            } else {
                alert(data.error || 'Failed to send invoice.');
            }
        })
        .catch(() => alert('Failed to send invoice.'));
    };
});
</script>
@endpush
