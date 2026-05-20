@extends('layouts.app')
@section('title', 'Invoices')

@push('styles')
<style>
    .inv-container { display: flex; height: calc(100vh - 56px); overflow: hidden; }
    .inv-rail { width: 320px; min-width: 320px; background: #fff; border-right: 1px solid rgba(0,0,0,.08); display: flex; flex-direction: column; }
    .inv-rail-header { padding: 1.25rem 1rem .75rem; border-bottom: 1px solid rgba(0,0,0,.06); }
    .inv-rail-header h6 { font-size: .75rem; text-transform: uppercase; letter-spacing: 1.2px; color: rgba(0,0,0,.4); margin-bottom: .75rem; }
    .inv-rail-search input { width: 100%; padding: .4rem .75rem; font-size: .82rem; border: 1px solid rgba(0,0,0,.1); border-radius: .375rem; background: #fafaf7; }
    .inv-rail-search input:focus { outline: none; border-color: var(--vip-accent); }
    .inv-rail-tabs { display: flex; gap: 0; padding: 0; margin-top: .75rem; flex-wrap: wrap; }
    .inv-rail-tabs .tab-btn {
        flex: 1; text-align: center; padding: .4rem .25rem; font-size: .7rem;
        background: rgba(0,0,0,.03); border: 1px solid rgba(0,0,0,.08);
        color: rgba(0,0,0,.5); cursor: pointer; transition: all .15s;
    }
    .inv-rail-tabs .tab-btn:first-child { border-radius: .3rem 0 0 .3rem; }
    .inv-rail-tabs .tab-btn:last-child { border-radius: 0 .3rem .3rem 0; }
    .inv-rail-tabs .tab-btn.active { background: var(--vip-accent); color: #fff; border-color: var(--vip-accent); }
    .inv-rail-list { flex: 1; overflow-y: auto; padding: .5rem; }
    .inv-rail-footer { padding: .75rem; border-top: 1px solid rgba(0,0,0,.06); }
    .inv-card { background: #fafaf7; border: 1px solid rgba(0,0,0,.06); border-radius: .5rem; padding: .75rem 1rem; margin-bottom: .5rem; cursor: pointer; transition: all .15s; }
    .inv-card:hover { background: rgba(201,168,76,.04); border-color: rgba(201,168,76,.2); }
    .inv-card.active { background: rgba(201,168,76,.08); border-color: var(--vip-accent); }
    .inv-card .inv-name { font-weight: 600; font-size: .9rem; color: #111; }
    .inv-card .inv-number { font-size: .78rem; color: #888; margin-top: 2px; }
    .inv-card .inv-meta { display: flex; justify-content: space-between; align-items: center; margin-top: .35rem; }
    .inv-card .inv-date { font-size: .7rem; color: #aaa; }
    .inv-card .inv-amount { font-size: .85rem; font-weight: 700; color: #111; }
    .inv-card .inv-badge { font-size: .6rem; padding: 2px 6px; border-radius: 3px; font-weight: 600; text-transform: uppercase; }
    .inv-badge-draft { background: rgba(108,117,125,.15); color: #495057; }
    .inv-badge-sent { background: rgba(0,123,255,.15); color: #004085; }
    .inv-badge-paid { background: rgba(40,167,69,.15); color: #155724; }
    .inv-badge-partial { background: rgba(255,193,7,.15); color: #856404; }
    .inv-badge-overdue { background: rgba(220,53,69,.15); color: #842029; }
    .inv-badge-cancelled { background: rgba(33,37,41,.15); color: #212529; }
    .inv-main { flex: 1; overflow-y: auto; background: var(--vip-light); }
    .inv-main-toolbar { background: #fff; border-bottom: 1px solid rgba(0,0,0,.06); padding: .75rem 1.5rem; display: flex; align-items: center; justify-content: space-between; }
    .inv-main-toolbar h5 { font-size: 1rem; font-weight: 700; margin: 0; }
    .inv-detail-body { padding: 1.5rem; }
    .inv-empty-state { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 60vh; color: rgba(0,0,0,.35); }
    .inv-empty-state i { font-size: 3rem; margin-bottom: 1rem; }
    .inv-info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
    .inv-info-card { background: #fff; border-radius: .5rem; padding: 1rem; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
    .inv-info-card .label { font-size: .7rem; text-transform: uppercase; letter-spacing: .5px; color: rgba(0,0,0,.45); margin-bottom: .25rem; }
    .inv-info-card .value { font-size: .9rem; font-weight: 600; color: #111; }
    .inv-items-tbl { width: 100%; border-collapse: collapse; background: #fff; border-radius: .5rem; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
    .inv-items-tbl th { font-size: .68rem; text-transform: uppercase; letter-spacing: .5px; color: rgba(0,0,0,.4); padding: .5rem .6rem; border-bottom: 1px solid rgba(0,0,0,.08); background: #fafafa; }
    .inv-items-tbl td { padding: .45rem .6rem; font-size: .8rem; border-bottom: 1px solid rgba(0,0,0,.04); vertical-align: top; }
    .section-title { font-size: .75rem; text-transform: uppercase; letter-spacing: .5px; color: rgba(0,0,0,.5); margin-bottom: .5rem; margin-top: 1.5rem; }
    @media (max-width: 991.98px) { .inv-container { flex-direction: column; height: auto; } .inv-rail { width: 100%; min-width: 100%; max-height: 45vh; } }
</style>
@endpush

@section('content')
<div class="inv-container">
    {{-- Left Rail --}}
    <div class="inv-rail">
        <div class="inv-rail-header">
            <h6>Invoices</h6>
            <div class="inv-rail-search">
                <input type="text" id="invSearch" placeholder="Search invoices...">
            </div>
            <div class="inv-rail-tabs">
                <div class="tab-btn {{ $status === 'all' ? 'active' : '' }}" data-status="all">All</div>
                <div class="tab-btn {{ $status === 'draft' ? 'active' : '' }}" data-status="draft">Draft</div>
                <div class="tab-btn {{ $status === 'sent' ? 'active' : '' }}" data-status="sent">Sent</div>
                <div class="tab-btn {{ $status === 'paid' ? 'active' : '' }}" data-status="paid">Paid</div>
                <div class="tab-btn {{ $status === 'overdue' ? 'active' : '' }}" data-status="overdue">Overdue</div>
            </div>
        </div>
        <div class="inv-rail-list">
            @forelse($invoices as $inv)
                <div class="inv-card"
                     data-id="{{ $inv->id }}"
                     data-status="{{ $inv->status }}"
                     data-search="{{ strtolower(($inv->customer_name ?? '') . ' ' . ($inv->invoice_number ?? '') . ' ' . ($inv->customer_email ?? '')) }}">
                    <div class="inv-name">{{ $inv->customer_name ?: 'No Customer' }}</div>
                    <div class="inv-number"><i class="bi bi-receipt me-1"></i>{{ $inv->invoice_number }}</div>
                    <div class="inv-meta">
                        <span class="inv-date">{{ $inv->created_at?->format('M d, Y') }}</span>
                        <span class="inv-amount">${{ number_format($inv->total, 2) }}</span>
                    </div>
                    <div class="inv-meta" style="margin-top:.2rem;">
                        <span class="inv-badge inv-badge-{{ $inv->status }}">{{ ucfirst($inv->status) }}</span>
                        @if($inv->due_date)
                            <span class="inv-date">Due {{ $inv->due_date->format('M d') }}</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-4" style="color:rgba(0,0,0,.3);">
                    <i class="bi bi-receipt" style="font-size:2rem;"></i>
                    <p class="mt-2 mb-0 small">No invoices found</p>
                </div>
            @endforelse
        </div>
        <div class="inv-rail-footer">
            <button class="btn btn-vip btn-sm w-100" data-bs-toggle="modal" data-bs-target="#createInvoiceModal">
                <i class="bi bi-plus-circle me-1"></i> New Invoice
            </button>
        </div>
    </div>

    {{-- Right Detail Panel --}}
    <div class="inv-main">
        <div class="inv-main-toolbar">
            <h5 id="invDetailTitle">Invoice Details</h5>
            <div id="invToolbarActions"></div>
        </div>
        <div class="inv-detail-body" id="invDetailBody">
            <div class="inv-empty-state">
                <i class="bi bi-receipt"></i>
                <p>Select an invoice to view details</p>
            </div>
        </div>
    </div>
</div>

{{-- Create Invoice Modal --}}
<div class="modal fade" id="createInvoiceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.invoices.store') }}">
                @csrf
                <div class="modal-header py-2">
                    <h6 class="modal-title mb-0"><i class="bi bi-plus-circle me-1"></i>New Invoice</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-2">
                    <div class="mb-2">
                        <label class="form-label mb-0" style="font-size:.75rem; color:#888;">Create from Quote</label>
                        <select name="from_quote" class="form-select form-select-sm" id="createFromQuote">
                            <option value="">-- Blank Invoice --</option>
                            @foreach($quotes as $q)
                                <option value="{{ $q->id }}" data-name="{{ $q->billing_name }}" data-email="" data-phone="">
                                    {{ $q->quote_number }} -- {{ $q->billing_name ?: 'No Name' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <hr class="my-2">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label mb-0" style="font-size:.75rem; color:#888;">Customer Name *</label>
                            <input type="text" name="customer_name" class="form-control form-control-sm" id="createCustName" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label mb-0" style="font-size:.75rem; color:#888;">Email</label>
                            <input type="email" name="customer_email" class="form-control form-control-sm" id="createCustEmail">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label mb-0" style="font-size:.75rem; color:#888;">Phone</label>
                            <input type="text" name="customer_phone" class="form-control form-control-sm" id="createCustPhone">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label mb-0" style="font-size:.75rem; color:#888;">Due Date</label>
                            <input type="date" name="due_date" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label mb-0" style="font-size:.75rem; color:#888;">Customer Address</label>
                            <input type="text" name="customer_address" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label mb-0" style="font-size:.75rem; color:#888;">Billing Address</label>
                            <input type="text" name="billing_address" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label mb-0" style="font-size:.75rem; color:#888;">Tax Rate (%)</label>
                            <input type="number" step="0.01" name="tax_rate" class="form-control form-control-sm" value="0">
                        </div>
                        <div class="col-12">
                            <label class="form-label mb-0" style="font-size:.75rem; color:#888;">Notes</label>
                            <textarea name="notes" class="form-control form-control-sm" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-vip btn-sm"><i class="bi bi-plus-circle me-1"></i>Create Invoice</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Invoice Modal (shared, populated via JS) --}}
<div class="modal fade" id="editInvoiceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editInvoiceForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header py-2">
                    <h6 class="modal-title mb-0"><i class="bi bi-pencil me-1"></i>Edit Invoice</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-2">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label mb-0" style="font-size:.75rem; color:#888;">Customer Name *</label>
                            <input type="text" name="customer_name" id="editCustName" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label mb-0" style="font-size:.75rem; color:#888;">Email</label>
                            <input type="email" name="customer_email" id="editCustEmail" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label mb-0" style="font-size:.75rem; color:#888;">Phone</label>
                            <input type="text" name="customer_phone" id="editCustPhone" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label mb-0" style="font-size:.75rem; color:#888;">Due Date</label>
                            <input type="date" name="due_date" id="editDueDate" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label mb-0" style="font-size:.75rem; color:#888;">Customer Address</label>
                            <input type="text" name="customer_address" id="editCustAddress" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label mb-0" style="font-size:.75rem; color:#888;">Billing Address</label>
                            <input type="text" name="billing_address" id="editBillingAddress" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label mb-0" style="font-size:.75rem; color:#888;">Tax Rate (%)</label>
                            <input type="number" step="0.01" name="tax_rate" id="editTaxRate" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label mb-0" style="font-size:.75rem; color:#888;">Status</label>
                            <select name="status" id="editStatus" class="form-select form-select-sm">
                                <option value="draft">Draft</option>
                                <option value="sent">Sent</option>
                                <option value="paid">Paid</option>
                                <option value="partial">Partial</option>
                                <option value="overdue">Overdue</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label mb-0" style="font-size:.75rem; color:#888;">Notes</label>
                            <textarea name="notes" id="editNotes" class="form-control form-control-sm" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-vip btn-sm"><i class="bi bi-check-lg me-1"></i>Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Send Invoice Modal --}}
<div class="modal fade" id="sendInvoiceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title mb-0"><i class="bi bi-envelope me-1"></i>Send Invoice</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-2">
                <div class="mb-2">
                    <label class="form-label mb-0" style="font-size:.75rem; color:#888;">Customer Email</label>
                    <input type="email" id="sendInvoiceEmail" class="form-control form-control-sm" required>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-vip btn-sm" onclick="sendInvoice()"><i class="bi bi-send me-1"></i>Send Invoice</button>
            </div>
        </div>
    </div>
</div>

{{-- Record Payment Modal --}}
<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title mb-0"><i class="bi bi-cash me-1"></i>Record Payment</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-2">
                <p class="text-muted small mb-2">Balance Due: <strong id="paymentBalance">$0.00</strong></p>
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label mb-0" style="font-size:.75rem; color:#888;">Amount *</label>
                        <input type="number" step="0.01" id="paymentAmount" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label mb-0" style="font-size:.75rem; color:#888;">Payment Method</label>
                        <select id="paymentMethod" class="form-select form-select-sm">
                            <option value="cash">Cash</option>
                            <option value="check">Check</option>
                            <option value="card">Card</option>
                            <option value="transfer">Transfer</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label mb-0" style="font-size:.75rem; color:#888;">Payment Date</label>
                        <input type="date" id="paymentDate" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label mb-0" style="font-size:.75rem; color:#888;">Note</label>
                        <input type="text" id="paymentNote" class="form-control form-control-sm" placeholder="Optional note">
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-vip btn-sm" onclick="recordPayment()"><i class="bi bi-check-circle me-1"></i>Record Payment</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const csrf = '{{ csrf_token() }}';
let currentInvoiceId = null;
let currentInvoiceData = null;

document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.inv-card');

    // Status tab filter (client-side)
    document.querySelectorAll('.inv-rail-tabs .tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.inv-rail-tabs .tab-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const status = this.dataset.status;
            document.querySelectorAll('.inv-card').forEach(card => {
                if (status === 'all' || card.dataset.status === status) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });

    // Search filter (client-side)
    document.getElementById('invSearch').addEventListener('input', function() {
        const term = this.value.toLowerCase();
        document.querySelectorAll('.inv-card').forEach(card => {
            card.style.display = (!term || card.dataset.search.includes(term)) ? '' : 'none';
        });
    });

    // Card click
    cards.forEach(card => {
        card.addEventListener('click', function() {
            cards.forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            loadInvoice(this.dataset.id);
        });
    });

    // Auto-fill from quote selection
    document.getElementById('createFromQuote')?.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        if (opt.dataset.name) {
            document.getElementById('createCustName').value = opt.dataset.name;
        }
    });

    // Select first card if exists
    if (cards.length > 0) cards[0].click();
});

function loadInvoice(id) {
    currentInvoiceId = id;
    const body = document.getElementById('invDetailBody');
    body.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-secondary"></div></div>';

    fetch(`/admin/invoices/${id}`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        currentInvoiceData = data;
        renderInvoiceDetail(data);
    })
    .catch(() => {
        body.innerHTML = '<div class="alert alert-danger m-4">Failed to load invoice.</div>';
    });
}

function renderInvoiceDetail(data) {
    const inv = data.invoice;
    const items = data.items || [];
    const creator = data.creator;
    const pdfUrl = data.pdf_url;
    const job = data.job;
    const body = document.getElementById('invDetailBody');
    const title = document.getElementById('invDetailTitle');
    const toolbar = document.getElementById('invToolbarActions');

    title.textContent = 'Invoice ' + escHtml(inv.invoice_number);

    // Toolbar actions
    let actions = '';
    actions += `<button class="btn btn-sm btn-outline-primary me-1" onclick="openEditModal()" title="Edit"><i class="bi bi-pencil"></i></button>`;
    actions += `<button class="btn btn-sm btn-outline-success me-1" onclick="openSendModal()" title="Send Email"><i class="bi bi-envelope"></i></button>`;
    actions += `<button class="btn btn-sm btn-outline-warning me-1" onclick="openPaymentModal()" title="Record Payment"><i class="bi bi-cash"></i></button>`;
    if (pdfUrl) {
        actions += `<a href="${pdfUrl}" target="_blank" class="btn btn-sm btn-outline-secondary me-1" title="Download PDF"><i class="bi bi-download"></i></a>`;
    }
    actions += `<button class="btn btn-sm btn-outline-danger" onclick="deleteInvoice()" title="Delete"><i class="bi bi-trash"></i></button>`;
    toolbar.innerHTML = actions;

    // Status badge class
    const badgeClass = {
        draft: 'bg-secondary', sent: 'bg-primary', paid: 'bg-success',
        partial: 'bg-warning text-dark', overdue: 'bg-danger', cancelled: 'bg-dark'
    };

    // Linked job info
    let jobHtml = '';
    if (job) {
        jobHtml = `<div class="inv-info-card">
            <div class="label">Linked Job</div>
            <div class="value"><i class="bi bi-tools me-1"></i>${escHtml(job.job_number || '')} - ${escHtml(job.title || '')}</div>
            <div style="font-size:.7rem; color:#888; margin-top:2px;">Status: ${escHtml(job.status || '')}</div>
        </div>`;
    }

    // Line items table
    let itemsHtml = '';
    if (items.length) {
        itemsHtml = `<table class="inv-items-tbl"><thead><tr>
            <th>Description</th><th style="width:60px;">Qty</th><th style="width:100px;">Unit Price</th><th style="width:100px;">Total</th><th style="width:40px;"></th>
        </tr></thead><tbody>`;
        items.forEach(item => {
            itemsHtml += `<tr>
                <td>${escHtml(item.description)}</td>
                <td class="text-center">${item.qty}</td>
                <td class="text-end">$${parseFloat(item.unit_price).toFixed(2)}</td>
                <td class="text-end fw-semibold">$${parseFloat(item.total).toFixed(2)}</td>
                <td class="text-center">
                    <button class="btn btn-sm text-danger p-0" onclick="removeItem(${inv.id}, ${item.id})" title="Remove"><i class="bi bi-x-lg" style="font-size:.65rem;"></i></button>
                </td>
            </tr>`;
        });
        itemsHtml += '</tbody></table>';
    } else {
        itemsHtml = '<p class="text-muted small">No line items yet.</p>';
    }

    // Add item inline form
    let addItemHtml = `
    <div class="card mt-2" style="border:none; box-shadow:0 1px 4px rgba(0,0,0,.06);">
        <div class="card-body py-2 px-3">
            <div class="row g-2 align-items-end">
                <div class="col">
                    <label style="font-size:.65rem; color:#999; text-transform:uppercase;">Description</label>
                    <input type="text" id="addItemDesc" class="form-control form-control-sm" placeholder="Item description">
                </div>
                <div class="col-md-1">
                    <label style="font-size:.65rem; color:#999; text-transform:uppercase;">Qty</label>
                    <input type="number" id="addItemQty" class="form-control form-control-sm" value="1" min="1" step="0.01">
                </div>
                <div class="col-md-2">
                    <label style="font-size:.65rem; color:#999; text-transform:uppercase;">Unit Price</label>
                    <input type="number" id="addItemPrice" class="form-control form-control-sm" value="0" step="0.01" min="0">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button class="btn btn-sm btn-vip w-100" onclick="addItem(${inv.id})" title="Add Item"><i class="bi bi-plus-lg"></i></button>
                </div>
            </div>
        </div>
    </div>`;

    // PDF viewer section
    let pdfSection = '';
    if (pdfUrl) {
        pdfSection = `
        <h6 class="section-title"><i class="bi bi-file-earmark-pdf"></i> Estimate Document</h6>
        <iframe src="${pdfUrl}" style="width:100%; height:600px; border:1px solid rgba(0,0,0,.1); border-radius:.5rem;"></iframe>`;
    } else {
        pdfSection = `
        <h6 class="section-title"><i class="bi bi-file-earmark-pdf"></i> Estimate Document</h6>
        <div class="card" style="border:none; box-shadow:0 1px 4px rgba(0,0,0,.06);">
            <div class="card-body text-center py-4" style="color:rgba(0,0,0,.3);">
                <i class="bi bi-file-earmark-pdf" style="font-size:2rem;"></i>
                <p class="mb-0 small mt-2">No estimate document attached</p>
            </div>
        </div>`;
    }

    body.innerHTML = `
        {{-- Info Cards Row --}}
        <div class="inv-info-grid">
            <div class="inv-info-card">
                <div class="label">Customer</div>
                <div class="value">${escHtml(inv.customer_name) || '—'}</div>
                ${inv.customer_email ? '<div style="font-size:.75rem; color:#666; margin-top:2px;">' + escHtml(inv.customer_email) + '</div>' : ''}
                ${inv.customer_phone ? '<div style="font-size:.75rem; color:#666;">' + escHtml(inv.customer_phone) + '</div>' : ''}
            </div>
            <div class="inv-info-card">
                <div class="label">Status</div>
                <div class="value"><span class="badge ${badgeClass[inv.status] || 'bg-secondary'}">${escHtml(inv.status?.charAt(0).toUpperCase() + inv.status?.slice(1))}</span></div>
                ${inv.due_date ? '<div style="font-size:.75rem; color:#666; margin-top:4px;">Due: ' + new Date(inv.due_date).toLocaleDateString() + '</div>' : ''}
                ${inv.paid_date ? '<div style="font-size:.75rem; color:#28a745; margin-top:2px;">Paid: ' + new Date(inv.paid_date).toLocaleDateString() + '</div>' : ''}
            </div>
            <div class="inv-info-card">
                <div class="label">Totals</div>
                <div style="font-size:.8rem;">
                    <div class="d-flex justify-content-between"><span style="color:#888;">Subtotal</span><span>$${parseFloat(inv.subtotal).toFixed(2)}</span></div>
                    <div class="d-flex justify-content-between"><span style="color:#888;">Tax (${parseFloat(inv.tax_rate || 0).toFixed(2)}%)</span><span>$${parseFloat(inv.tax_amount || 0).toFixed(2)}</span></div>
                    <div class="d-flex justify-content-between fw-bold" style="border-top:1px solid rgba(0,0,0,.08); padding-top:3px; margin-top:3px;"><span>Total</span><span>$${parseFloat(inv.total).toFixed(2)}</span></div>
                    <div class="d-flex justify-content-between" style="color:#28a745;"><span>Paid</span><span>$${parseFloat(inv.amount_paid).toFixed(2)}</span></div>
                    <div class="d-flex justify-content-between fw-bold" style="color:#dc3545;"><span>Balance</span><span>$${parseFloat(inv.balance_due).toFixed(2)}</span></div>
                </div>
            </div>
            ${inv.customer_address ? '<div class="inv-info-card"><div class="label">Address</div><div class="value" style="font-size:.82rem;">' + escHtml(inv.customer_address) + '</div></div>' : ''}
            ${inv.billing_address ? '<div class="inv-info-card"><div class="label">Billing Address</div><div class="value" style="font-size:.82rem;">' + escHtml(inv.billing_address) + '</div></div>' : ''}
            ${jobHtml}
            ${creator ? '<div class="inv-info-card"><div class="label">Created By</div><div class="value">' + escHtml(creator.name || '—') + '</div><div style="font-size:.7rem; color:#888; margin-top:2px;">' + new Date(inv.created_at).toLocaleDateString() + '</div></div>' : ''}
        </div>

        {{-- PDF Viewer --}}
        ${pdfSection}

        {{-- Line Items --}}
        <h6 class="section-title"><i class="bi bi-list-check"></i> Line Items (${items.length})</h6>
        ${itemsHtml}
        ${addItemHtml}

        {{-- Notes --}}
        ${inv.notes ? '<h6 class="section-title"><i class="bi bi-journal-text"></i> Notes</h6><div class="card" style="border:none;box-shadow:0 1px 4px rgba(0,0,0,.06);"><div class="card-body py-2 px-3" style="white-space:pre-line; font-size:.85rem;">' + escHtml(inv.notes) + '</div></div>' : ''}
    `;
}

function escHtml(str) {
    if (!str) return '';
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}

// --- Edit Invoice ---
function openEditModal() {
    if (!currentInvoiceData) return;
    const inv = currentInvoiceData.invoice;
    document.getElementById('editInvoiceForm').action = `/admin/invoices/${inv.id}`;
    document.getElementById('editCustName').value = inv.customer_name || '';
    document.getElementById('editCustEmail').value = inv.customer_email || '';
    document.getElementById('editCustPhone').value = inv.customer_phone || '';
    document.getElementById('editDueDate').value = inv.due_date ? inv.due_date.substring(0, 10) : '';
    document.getElementById('editCustAddress').value = inv.customer_address || '';
    document.getElementById('editBillingAddress').value = inv.billing_address || '';
    document.getElementById('editTaxRate').value = inv.tax_rate || 0;
    document.getElementById('editStatus').value = inv.status || 'draft';
    document.getElementById('editNotes').value = inv.notes || '';
    new bootstrap.Modal(document.getElementById('editInvoiceModal')).show();
}

// --- Send Invoice ---
function openSendModal() {
    if (!currentInvoiceData) return;
    document.getElementById('sendInvoiceEmail').value = currentInvoiceData.invoice.customer_email || '';
    new bootstrap.Modal(document.getElementById('sendInvoiceModal')).show();
}

function sendInvoice() {
    if (!currentInvoiceId) return;
    const email = document.getElementById('sendInvoiceEmail').value.trim();
    if (!email) { alert('Please enter an email address.'); return; }

    const btn = document.querySelector('#sendInvoiceModal .btn-vip');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Sending...';

    fetch(`/admin/invoices/${currentInvoiceId}/send`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        body: JSON.stringify({ email })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('sendInvoiceModal')).hide();
            loadInvoice(currentInvoiceId);
            // Update card badge
            updateCardBadge(currentInvoiceId, 'sent');
        } else {
            alert(data.message || 'Failed to send invoice.');
        }
    })
    .catch(() => alert('Failed to send invoice.'))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-send me-1"></i>Send Invoice';
    });
}

// --- Record Payment ---
function openPaymentModal() {
    if (!currentInvoiceData) return;
    const inv = currentInvoiceData.invoice;
    document.getElementById('paymentBalance').textContent = '$' + parseFloat(inv.balance_due).toFixed(2);
    document.getElementById('paymentAmount').value = parseFloat(inv.balance_due).toFixed(2);
    document.getElementById('paymentDate').value = new Date().toISOString().substring(0, 10);
    document.getElementById('paymentNote').value = '';
    document.getElementById('paymentMethod').value = 'cash';
    new bootstrap.Modal(document.getElementById('paymentModal')).show();
}

function recordPayment() {
    if (!currentInvoiceId) return;
    const amount = document.getElementById('paymentAmount').value;
    const method = document.getElementById('paymentMethod').value;
    const date = document.getElementById('paymentDate').value;
    const note = document.getElementById('paymentNote').value;

    if (!amount || parseFloat(amount) <= 0) { alert('Enter a valid amount.'); return; }

    const btn = document.querySelector('#paymentModal .btn-vip');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Recording...';

    fetch(`/admin/invoices/${currentInvoiceId}/payment`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        body: JSON.stringify({ amount, payment_method: method, payment_date: date, payment_note: note })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('paymentModal')).hide();
            loadInvoice(currentInvoiceId);
        } else {
            alert(data.message || 'Failed to record payment.');
        }
    })
    .catch(() => alert('Failed to record payment.'))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Record Payment';
    });
}

// --- Add Item ---
function addItem(invoiceId) {
    const desc = document.getElementById('addItemDesc')?.value?.trim();
    const qty = document.getElementById('addItemQty')?.value;
    const price = document.getElementById('addItemPrice')?.value;

    if (!desc) { alert('Please enter a description.'); return; }

    const formData = new FormData();
    formData.append('_token', csrf);
    formData.append('description', desc);
    formData.append('qty', qty || 1);
    formData.append('unit_price', price || 0);

    fetch(`/admin/invoices/${invoiceId}/item`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success !== false) {
            loadInvoice(invoiceId);
        } else {
            alert(data.message || 'Failed to add item.');
        }
    })
    .catch(() => alert('Failed to add item.'));
}

// --- Remove Item ---
function removeItem(invoiceId, itemId) {
    if (!confirm('Remove this item?')) return;

    fetch(`/admin/invoices/${invoiceId}/item/${itemId}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) loadInvoice(invoiceId);
    })
    .catch(() => alert('Failed to remove item.'));
}

// --- Delete Invoice ---
function deleteInvoice() {
    if (!currentInvoiceId) return;
    if (!confirm('Are you sure you want to delete this invoice?')) return;

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/admin/invoices/${currentInvoiceId}`;
    form.innerHTML = `<input type="hidden" name="_token" value="${csrf}"><input type="hidden" name="_method" value="DELETE">`;
    document.body.appendChild(form);
    form.submit();
}

// --- Helper: update card badge in rail ---
function updateCardBadge(invoiceId, newStatus) {
    const card = document.querySelector(`.inv-card[data-id="${invoiceId}"]`);
    if (!card) return;
    card.dataset.status = newStatus;
    const badge = card.querySelector('.inv-badge');
    if (badge) {
        badge.className = 'inv-badge inv-badge-' + newStatus;
        badge.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
    }
}
</script>
@endpush
