@extends('layouts.app')
@section('title', 'Quotes')

@push('styles')
<style>
    /* ── Quote Hub Shell ─────────────────────────────── */
    .qh-wrapper {
        display: flex;
        height: calc(100vh - 120px);
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        overflow: hidden;
    }

    /* ── Left Rail ─────────────────────────────────────── */
    .qh-sidebar {
        width: 300px;
        min-width: 300px;
        background: var(--vip-primary);
        color: #fff;
        display: flex;
        flex-direction: column;
        border-right: 1px solid rgba(255,255,255,.08);
    }
    .qh-sidebar-header {
        padding: 14px 16px 12px;
        border-bottom: 1px solid rgba(255,255,255,.1);
        background: rgba(0,0,0,.15);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .qh-sidebar-header h6 {
        margin: 0;
        font-size: .85rem;
        font-weight: 700;
        letter-spacing: .5px;
        color: var(--vip-accent);
    }
    .qh-sidebar-header .btn-new-quote {
        background: var(--vip-accent);
        color: var(--vip-primary);
        border: none;
        font-size: .72rem;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 5px;
        text-decoration: none;
    }
    .qh-sidebar-header .btn-new-quote:hover {
        background: #d4b35a;
        color: #000;
    }

    /* Search + Filters */
    .qh-filters {
        padding: 10px 12px;
        border-bottom: 1px solid rgba(255,255,255,.08);
    }
    .qh-filters input {
        width: 100%;
        background: rgba(255,255,255,.1);
        border: 1px solid rgba(255,255,255,.15);
        border-radius: 6px;
        padding: 6px 10px;
        color: #fff;
        font-size: .8rem;
        margin-bottom: 8px;
    }
    .qh-filters input::placeholder { color: rgba(255,255,255,.4); }
    .qh-filters input:focus {
        outline: none;
        border-color: var(--vip-accent);
        background: rgba(255,255,255,.15);
    }
    .qh-status-tabs {
        display: flex;
        gap: 4px;
    }
    .qh-status-tab {
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
    .qh-status-tab:hover {
        color: rgba(255,255,255,.8);
        background: rgba(255,255,255,.1);
    }
    .qh-status-tab.active {
        color: var(--vip-accent);
        background: rgba(201,168,76,.15);
        border-color: rgba(201,168,76,.3);
    }

    /* Sidebar body — quote list */
    .qh-sidebar-body {
        flex: 1;
        overflow-y: auto;
        padding: 4px 0;
    }
    .qh-sidebar-body::-webkit-scrollbar { width: 4px; }
    .qh-sidebar-body::-webkit-scrollbar-thumb { background: rgba(255,255,255,.2); border-radius: 4px; }

    /* Quote cards in sidebar */
    .qh-quote-item {
        display: block;
        padding: 10px 14px;
        color: rgba(255,255,255,.75);
        text-decoration: none;
        border-left: 3px solid transparent;
        border-bottom: 1px solid rgba(255,255,255,.05);
        cursor: pointer;
        transition: all .15s;
    }
    .qh-quote-item:hover {
        background: rgba(255,255,255,.08);
        color: #fff;
    }
    .qh-quote-item.active {
        background: rgba(201,168,76,.12);
        border-left-color: var(--vip-accent);
        color: #fff;
    }
    .qh-quote-item .qh-q-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 3px;
    }
    .qh-quote-item .qh-q-num {
        font-weight: 700;
        font-size: .82rem;
    }
    .qh-quote-item.active .qh-q-num { color: var(--vip-accent); }
    .qh-quote-item .qh-q-badge {
        font-size: .6rem;
        padding: 2px 6px;
        border-radius: 3px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .3px;
    }
    .qh-q-badge.draft { background: rgba(108,117,125,.3); color: #ccc; }
    .qh-q-badge.sent { background: rgba(40,167,69,.25); color: #7ddf9b; }
    .qh-q-badge.accepted { background: rgba(0,123,255,.25); color: #6db8ff; }
    .qh-quote-item .qh-q-customer {
        font-size: .78rem;
        opacity: .8;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .qh-quote-item .qh-q-meta {
        display: flex;
        justify-content: space-between;
        font-size: .68rem;
        opacity: .5;
        margin-top: 2px;
    }

    /* Sidebar stats */
    .qh-sidebar-footer {
        padding: 10px 14px;
        border-top: 1px solid rgba(255,255,255,.1);
        background: rgba(0,0,0,.1);
        display: flex;
        gap: 8px;
    }
    .qh-stat {
        flex: 1;
        text-align: center;
    }
    .qh-stat .val {
        font-size: 1rem;
        font-weight: 700;
        color: var(--vip-accent);
    }
    .qh-stat .lbl {
        font-size: .6rem;
        text-transform: uppercase;
        letter-spacing: .5px;
        opacity: .5;
    }

    /* ── Main Panel ────────────────────────────────────── */
    .qh-main {
        flex: 1;
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    /* Toolbar */
    .qh-toolbar {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 20px;
        border-bottom: 1px solid #e9ecef;
        background: #fafafa;
        min-height: 52px;
    }
    .qh-toolbar .qh-title {
        font-size: 1rem;
        font-weight: 700;
        color: var(--vip-primary);
        margin: 0;
    }
    .qh-toolbar .qh-sub {
        font-size: .78rem;
        color: #999;
    }
    .qh-toolbar .actions { margin-left: auto; display: flex; gap: 6px; }
    .qh-toolbar .actions .btn { font-size: .78rem; }

    /* Content area */
    .qh-content {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
    }

    /* Placeholder */
    .qh-placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        height: 100%;
        text-align: center;
        color: #999;
    }
    .qh-placeholder i { font-size: 3rem; color: #ddd; margin-bottom: 16px; }
    .qh-placeholder h5 { color: #666; margin-bottom: 8px; }

    /* Loader */
    .qh-loader {
        position: absolute;
        inset: 0;
        display: none;
        align-items: center;
        justify-content: center;
        background: rgba(255,255,255,.9);
        z-index: 10;
    }
    .qh-loader.show { display: flex; }
    .qh-loader .spinner-border { width: 2rem; height: 2rem; color: var(--vip-accent); }

    /* Detail layout */
    .qh-detail-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 1px solid #e9ecef;
    }
    .qh-detail-header .qh-dh-left h4 { margin: 0; font-weight: 700; color: var(--vip-primary); }
    .qh-detail-header .qh-dh-left .text-muted { font-size: .85rem; }
    .qh-detail-header .qh-dh-right { text-align: right; }

    .qh-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 12px;
        margin-bottom: 20px;
    }
    .qh-info-card {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 12px 14px;
    }
    .qh-info-card .label {
        font-size: .68rem;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #888;
        margin-bottom: 4px;
    }
    .qh-info-card .value {
        font-size: .9rem;
        font-weight: 600;
        color: #333;
    }

    .qh-items-table th {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #666;
        border-bottom: 2px solid #e9ecef;
    }
    .qh-items-table td {
        font-size: .85rem;
        vertical-align: middle;
    }

    .qh-summary-bar {
        display: flex;
        justify-content: flex-end;
        gap: 24px;
        padding: 14px 0;
        margin-top: 12px;
        border-top: 2px solid var(--vip-primary);
    }
    .qh-summary-bar .sum-item .sum-label {
        font-size: .7rem;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #888;
    }
    .qh-summary-bar .sum-item .sum-value {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--vip-primary);
    }

    /* Responsive */
    @media (max-width: 991.98px) {
        .qh-wrapper { flex-direction: column; height: auto; min-height: calc(100vh - 120px); }
        .qh-sidebar { width: 100%; min-width: 100%; max-height: 320px; }
        .qh-content { min-height: 400px; }
    }
</style>
@endpush

@section('content')
<div class="p-3">
    <div class="qh-wrapper">

        {{-- ── Left Rail ──────────────────────────────────── --}}
        <div class="qh-sidebar">
            <div class="qh-sidebar-header">
                <h6><i class="bi bi-calculator me-1"></i> QUOTES</h6>
                <a href="{{ route('admin.quotes.create') }}" class="btn-new-quote">
                    <i class="bi bi-plus me-1"></i>New Quote
                </a>
            </div>

            <div class="qh-filters">
                <input type="text" id="qhSearch" placeholder="Search quotes…">
                <div class="qh-status-tabs">
                    <div class="qh-status-tab active" data-status="all">All</div>
                    <div class="qh-status-tab" data-status="draft">Draft</div>
                    <div class="qh-status-tab" data-status="sent">Sent</div>
                </div>
            </div>

            <div class="qh-sidebar-body" id="qhQuoteList">
                @forelse($quotes as $quote)
                    <a class="qh-quote-item"
                       href="#"
                       data-id="{{ $quote->id }}"
                       data-status="{{ $quote->status }}"
                       data-search="{{ strtolower($quote->quote_number . ' ' . ($quote->billing_name ?? '') . ' ' . ($quote->customer_number ?? '')) }}"
                       onclick="qhLoadQuote(event, this)">
                        <div class="qh-q-top">
                            <span class="qh-q-num">{{ $quote->quote_number }}</span>
                            <span class="qh-q-badge {{ $quote->status }}">{{ ucfirst($quote->status) }}</span>
                        </div>
                        <div class="qh-q-customer">
                            {{ $quote->billing_name ?: $quote->customer_number ?: 'No customer' }}
                        </div>
                        <div class="qh-q-meta">
                            <span>{{ $quote->items->count() }} item{{ $quote->items->count() !== 1 ? 's' : '' }}</span>
                            <span>${{ number_format($quote->items->sum(fn($i) => $i->getRawOriginal('total')), 2) }}</span>
                            <span>{{ $quote->created_at?->format('M d, Y') }}</span>
                        </div>
                    </a>
                @empty
                    <div class="text-center py-4 opacity-50">
                        <i class="bi bi-calculator d-block mb-2" style="font-size:1.5rem"></i>
                        <small>No quotes yet</small>
                    </div>
                @endforelse
            </div>

            <div class="qh-sidebar-footer">
                @php
                    $totalQuotes = $quotes->total();
                    $draftCount = $quotes->getCollection()->where('status', 'draft')->count();
                    $sentCount = $quotes->getCollection()->where('status', 'sent')->count();
                @endphp
                <div class="qh-stat">
                    <div class="val">{{ $totalQuotes }}</div>
                    <div class="lbl">Total</div>
                </div>
                <div class="qh-stat">
                    <div class="val">{{ $draftCount }}</div>
                    <div class="lbl">Draft</div>
                </div>
                <div class="qh-stat">
                    <div class="val">{{ $sentCount }}</div>
                    <div class="lbl">Sent</div>
                </div>
            </div>
        </div>

        {{-- ── Main Panel ─────────────────────────────────── --}}
        <div class="qh-main">
            <div class="qh-toolbar">
                <div>
                    <h5 class="qh-title" id="qhTitle">Quote Details</h5>
                    <span class="qh-sub" id="qhSub">Select a quote from the list</span>
                </div>
                <div class="actions" id="qhActions" style="display:none">
                    <a href="#" id="qhEditBtn" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-pencil me-1"></i>Edit
                    </a>
                    <button class="btn btn-sm btn-outline-success" id="qhSendBtn">
                        <i class="bi bi-send me-1"></i>Send
                    </button>
                    <button class="btn btn-sm btn-outline-danger" id="qhDeleteBtn">
                        <i class="bi bi-trash me-1"></i>Delete
                    </button>
                </div>
            </div>

            <div class="qh-content" id="qhContent" style="position:relative">
                <div class="qh-loader" id="qhLoader">
                    <div class="spinner-border"></div>
                </div>

                <div class="qh-placeholder" id="qhPlaceholder">
                    <i class="bi bi-calculator"></i>
                    <h5>Select a Quote</h5>
                    <p class="text-muted small">Click on a quote from the left to view its details, or create a new one.</p>
                </div>

                <div id="qhDetail" style="display:none"></div>
            </div>
        </div>

    </div>
</div>

{{-- Send Modal --}}
<div class="modal fade" id="sendQuoteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Send Quote</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label fw-semibold">Customer Email</label>
                <input type="email" class="form-control" id="sendEmailInput" placeholder="customer@email.com" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-vip btn-sm" id="confirmSendBtn">
                    <i class="bi bi-send me-1"></i>Send Quote
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    const csrf = '{{ csrf_token() }}';
    let activeQuoteId = null;

    // ── Status tab filtering ──────────────────────────────
    document.querySelectorAll('.qh-status-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.qh-status-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            filterQuotes();
        });
    });

    // ── Search filtering ──────────────────────────────────
    document.getElementById('qhSearch').addEventListener('input', filterQuotes);

    function filterQuotes() {
        const search = document.getElementById('qhSearch').value.toLowerCase();
        const status = document.querySelector('.qh-status-tab.active')?.dataset.status || 'all';

        document.querySelectorAll('.qh-quote-item').forEach(item => {
            const matchSearch = !search || item.dataset.search.includes(search);
            const matchStatus = status === 'all' || item.dataset.status === status;
            item.style.display = (matchSearch && matchStatus) ? '' : 'none';
        });
    }

    // ── Load quote detail ─────────────────────────────────
    window.qhLoadQuote = function(e, el) {
        e.preventDefault();
        const id = el.dataset.id;
        activeQuoteId = id;

        // Highlight active
        document.querySelectorAll('.qh-quote-item').forEach(i => i.classList.remove('active'));
        el.classList.add('active');

        // Show loader
        const loader = document.getElementById('qhLoader');
        const detail = document.getElementById('qhDetail');
        const placeholder = document.getElementById('qhPlaceholder');
        loader.classList.add('show');
        placeholder.style.display = 'none';
        detail.style.display = 'none';

        fetch(`/admin/quotes/${id}`, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
        })
        .then(r => r.json())
        .then(data => {
            renderQuoteDetail(data);
            loader.classList.remove('show');
            detail.style.display = 'block';

            // Update toolbar
            document.getElementById('qhTitle').textContent = data.quote.quote_number;
            document.getElementById('qhSub').textContent = data.quote.billing_name || data.quote.customer_number || 'No customer';
            document.getElementById('qhActions').style.display = 'flex';
            document.getElementById('qhEditBtn').href = `/admin/quotes/${id}/edit`;
        })
        .catch(err => {
            loader.classList.remove('show');
            detail.innerHTML = '<div class="alert alert-danger">Failed to load quote details.</div>';
            detail.style.display = 'block';
        });
    };

    function renderQuoteDetail(data) {
        const q = data.quote;
        const items = data.items;
        const summary = data.summary;

        const statusBadge = q.status === 'sent'
            ? '<span class="badge bg-success">Sent</span>'
            : q.status === 'draft'
                ? '<span class="badge bg-secondary">Draft</span>'
                : `<span class="badge bg-info">${q.status.charAt(0).toUpperCase() + q.status.slice(1)}</span>`;

        let html = `
            <div class="qh-detail-header">
                <div class="qh-dh-left">
                    <h4>${q.quote_number} ${statusBadge}</h4>
                    <div class="text-muted">Created ${q.created_at} by ${q.entered_by || '—'}</div>
                </div>
                <div class="qh-dh-right">
                    <div style="font-size:.7rem;text-transform:uppercase;color:#888;letter-spacing:.5px">Total</div>
                    <div style="font-size:1.4rem;font-weight:700;color:var(--vip-primary)">$${summary.subtotal}</div>
                    <div style="font-size:.78rem;color:#666">${summary.items_count} item${summary.items_count !== 1 ? 's' : ''}</div>
                </div>
            </div>

            <div class="qh-info-grid">
                <div class="qh-info-card">
                    <div class="label">Customer</div>
                    <div class="value">${q.billing_name || q.customer_number || '—'}</div>
                </div>
                <div class="qh-info-card">
                    <div class="label">Email</div>
                    <div class="value">${q.billing_email || '—'}</div>
                </div>
                <div class="qh-info-card">
                    <div class="label">Address</div>
                    <div class="value">${[q.billing_address, q.billing_city, q.billing_state, q.billing_zip].filter(Boolean).join(', ') || '—'}</div>
                </div>
                <div class="qh-info-card">
                    <div class="label">Entry Date</div>
                    <div class="value">${q.entry_date || '—'}</div>
                </div>
                <div class="qh-info-card">
                    <div class="label">Expected Delivery</div>
                    <div class="value">${q.expected_delivery || '—'}</div>
                </div>
                <div class="qh-info-card">
                    <div class="label">Valid Until</div>
                    <div class="value">${q.valid_until || '—'}</div>
                </div>
            </div>
        `;

        // Items table
        if (items.length > 0) {
            html += `
                <h6 class="fw-bold mb-3"><i class="bi bi-list-check me-2"></i>Line Items</h6>
                <div class="table-responsive">
                    <table class="table table-sm qh-items-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Description</th>
                                <th>Type</th>
                                <th>Size</th>
                                <th>Color</th>
                                <th>Glass</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Price</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            items.forEach((item, idx) => {
                html += `
                    <tr>
                        <td class="text-muted">${idx + 1}</td>
                        <td class="fw-semibold">${item.description || '—'}</td>
                        <td><span class="badge bg-light text-dark border">${item.series_type || '—'}</span></td>
                        <td>${item.width}" x ${item.height}"</td>
                        <td>${item.color_config || '—'}</td>
                        <td>${item.glass || '—'}</td>
                        <td class="text-center">${item.qty}</td>
                        <td class="text-end">$${item.price}</td>
                        <td class="text-end fw-semibold">$${item.total}</td>
                    </tr>
                `;
            });

            html += `
                        </tbody>
                    </table>
                </div>
                <div class="qh-summary-bar">
                    <div class="sum-item">
                        <div class="sum-label">Items</div>
                        <div class="sum-value">${summary.items_count}</div>
                    </div>
                    <div class="sum-item">
                        <div class="sum-label">Subtotal</div>
                        <div class="sum-value">$${summary.subtotal}</div>
                    </div>
                </div>
            `;
        } else {
            html += `
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-inbox d-block mb-2" style="font-size:2rem"></i>
                    <p>No line items yet. <a href="/admin/quotes/${q.id}/edit">Edit this quote</a> to add items.</p>
                </div>
            `;
        }

        // Notes
        if (q.notes) {
            html += `
                <div class="mt-3 p-3 bg-light rounded border">
                    <div style="font-size:.7rem;text-transform:uppercase;color:#888;letter-spacing:.5px;margin-bottom:4px">Notes</div>
                    <div style="font-size:.85rem">${q.notes}</div>
                </div>
            `;
        }

        document.getElementById('qhDetail').innerHTML = html;
    }

    // ── Send quote ────────────────────────────────────────
    document.getElementById('qhSendBtn').addEventListener('click', function() {
        if (!activeQuoteId) return;
        document.getElementById('sendEmailInput').value = '';
        new bootstrap.Modal(document.getElementById('sendQuoteModal')).show();
    });

    document.getElementById('confirmSendBtn').addEventListener('click', function() {
        const email = document.getElementById('sendEmailInput').value;
        if (!email || !activeQuoteId) return;

        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Sending…';

        fetch(`/admin/quotes/${activeQuoteId}/send`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ email })
        })
        .then(r => r.json())
        .then(data => {
            bootstrap.Modal.getInstance(document.getElementById('sendQuoteModal')).hide();
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to send');
            }
        })
        .finally(() => {
            this.disabled = false;
            this.innerHTML = '<i class="bi bi-send me-1"></i>Send Quote';
        });
    });

    // ── Delete quote ──────────────────────────────────────
    document.getElementById('qhDeleteBtn').addEventListener('click', function() {
        if (!activeQuoteId || !confirm('Delete this quote? This cannot be undone.')) return;

        fetch(`/admin/quotes/${activeQuoteId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrf }
        }).then(() => location.reload());
    });

})();
</script>
@endpush
