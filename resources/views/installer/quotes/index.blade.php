@extends('layouts.installer')
@section('title', 'My Quotes')

@push('styles')
<style>
    .iq-container { display: flex; height: calc(100vh - 56px); overflow: hidden; }

    /* ── Left Rail ─────────────────────────────── */
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

    .iq-rail-tabs { display: flex; gap: 0; padding: 0 1rem; margin-top: .75rem; }
    .iq-rail-tabs .tab-btn {
        flex: 1; text-align: center; padding: .4rem .5rem; font-size: .75rem;
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
    .iq-card .q-top { display: flex; justify-content: space-between; align-items: flex-start; }
    .iq-card .q-actions { display: none; gap: .25rem; }
    .iq-card:hover .q-actions { display: flex; }
    .iq-card .q-actions .btn-icon {
        width: 22px; height: 22px; display: flex; align-items: center; justify-content: center;
        border-radius: 3px; border: none; cursor: pointer; font-size: .7rem;
        transition: all .15s;
    }
    .iq-card .q-actions .btn-edit { background: rgba(201,168,76,.2); color: var(--vip-accent); }
    .iq-card .q-actions .btn-edit:hover { background: rgba(201,168,76,.4); }
    .iq-card .q-actions .btn-delete { background: rgba(220,53,69,.15); color: #dc3545; }
    .iq-card .q-actions .btn-delete:hover { background: rgba(220,53,69,.3); }
    .iq-card .q-meta { display: flex; justify-content: space-between; align-items: center; margin-top: .35rem; }
    .iq-card .q-date { font-size: .7rem; color: rgba(255,255,255,.4); }
    .iq-card .q-badge { font-size: .6rem; padding: 2px 6px; border-radius: 3px; font-weight: 600; text-transform: uppercase; }
    .q-badge-draft { background: rgba(108,117,125,.25); color: #adb5bd; }
    .q-badge-sent { background: rgba(40,167,69,.25); color: #7ddf9b; }

    .iq-rail-footer {
        padding: .75rem 1rem; border-top: 1px solid rgba(255,255,255,.08);
        font-size: .75rem; color: rgba(255,255,255,.4);
        display: flex; justify-content: space-between;
    }

    /* ── Main Panel ────────────────────────────── */
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

    .iq-info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
    .iq-info-card { background: #fff; border-radius: .5rem; padding: 1.25rem; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
    .iq-info-card .label { font-size: .7rem; text-transform: uppercase; letter-spacing: .5px; color: rgba(0,0,0,.45); margin-bottom: .25rem; }
    .iq-info-card .value { font-size: 1rem; font-weight: 600; color: #111; }

    .iq-items-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: .5rem; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
    .iq-items-table th { font-size: .7rem; text-transform: uppercase; letter-spacing: .5px; color: rgba(0,0,0,.4); padding: .6rem 1rem; border-bottom: 1px solid rgba(0,0,0,.08); background: #fafafa; }
    .iq-items-table td { padding: .6rem 1rem; font-size: .85rem; border-bottom: 1px solid rgba(0,0,0,.04); }

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
            <h6>My Quotes</h6>
            <div class="iq-rail-search">
                <input type="text" id="iqSearch" placeholder="Search quotes...">
                <a href="{{ route('installer.quotes.create') }}" class="btn btn-sm btn-vip" title="New Quote">
                    <i class="bi bi-plus-lg"></i>
                </a>
            </div>
            <div class="iq-rail-tabs">
                <div class="tab-btn {{ $status === 'all' ? 'active' : '' }}" data-status="all">All</div>
                <div class="tab-btn {{ $status === 'draft' ? 'active' : '' }}" data-status="draft">Draft</div>
                <div class="tab-btn {{ $status === 'sent' ? 'active' : '' }}" data-status="sent">Sent</div>
            </div>
        </div>

        <div class="iq-rail-list">
            @forelse($quotes as $quote)
                <div class="iq-card" data-id="{{ $quote->id }}" data-search="{{ strtolower($quote->quote_number . ' ' . ($quote->billing_name ?? '') . ' ' . ($quote->customer_number ?? '')) }}">
                    <div class="q-top">
                        <div class="q-number">{{ $quote->quote_number }}</div>
                        <div class="q-actions">
                            <button class="btn-icon btn-edit" title="Edit" onclick="event.stopPropagation(); editQuote({{ $quote->id }})"><i class="bi bi-pencil"></i></button>
                            <button class="btn-icon btn-delete" title="Delete" onclick="event.stopPropagation(); deleteQuote({{ $quote->id }}, '{{ $quote->quote_number }}')"><i class="bi bi-trash"></i></button>
                        </div>
                    </div>
                    <div class="q-customer"><i class="bi bi-person me-1"></i>{{ $quote->billing_name ?: $quote->customer_number ?: 'No customer' }}</div>
                    <div class="q-meta">
                        <span class="q-date">{{ $quote->created_at?->format('M d, Y') }}</span>
                        <span class="q-badge {{ $quote->status === 'sent' ? 'q-badge-sent' : 'q-badge-draft' }}">{{ ucfirst($quote->status) }}</span>
                    </div>
                </div>
            @empty
                <div class="text-center py-4" style="color:rgba(255,255,255,.4);">
                    <i class="bi bi-calculator" style="font-size:2rem;"></i>
                    <p class="mt-2 mb-0">No quotes yet</p>
                </div>
            @endforelse
        </div>

        <div class="iq-rail-footer">
            <span>{{ $quotes->total() }} quote{{ $quotes->total() !== 1 ? 's' : '' }}</span>
            <span>{{ $quotes->where('status', 'draft')->count() }} drafts</span>
        </div>
    </div>

    {{-- Main Panel --}}
    <div class="iq-main">
        <div class="iq-main-toolbar">
            <h5 id="iqDetailTitle">Quote Details</h5>
            <div id="iqToolbarActions"></div>
        </div>
        <div class="iq-detail-body" id="iqDetailBody">
            <div class="iq-empty-state">
                <i class="bi bi-calculator"></i>
                <p>Select a quote to view details</p>
                <p class="small text-muted mt-2">Select a quote from the list</p>
            </div>
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

        fetch(`/installer/quotes/${id}`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }})
            .then(r => r.json())
            .then(data => {
                const q = data.quote;
                const items = data.items || [];
                const summary = data.summary || {};
                detailTitle.textContent = q.quote_number;

                toolbarActions.innerHTML = `
                    <span class="badge ${q.status === 'sent' ? 'bg-success' : 'bg-secondary'}">${q.status ? q.status.charAt(0).toUpperCase() + q.status.slice(1) : 'Draft'}</span>
                `;

                let itemsHtml = '';
                if (items.length) {
                    itemsHtml = `<table class="iq-items-table">
                        <thead><tr><th>Item</th><th>Size</th><th>Qty</th><th>Price</th></tr></thead>
                        <tbody>${items.map(i => `<tr>
                            <td><strong>${i.series_type || 'Window'}</strong><br><small class="text-muted">${i.description || ''}</small></td>
                            <td>${i.width || ''}x${i.height || ''}</td>
                            <td>${i.qty || 1}</td>
                            <td>$${parseFloat(i.total || 0).toFixed(2)}</td>
                        </tr>`).join('')}</tbody>
                    </table>`;
                } else {
                    itemsHtml = '<p class="text-muted">No items added yet.</p>';
                }

                detailBody.innerHTML = `
                    <div class="iq-info-grid">
                        <div class="iq-info-card"><div class="label">Customer</div><div class="value">${q.billing_name || q.customer_number || '—'}</div></div>
                        <div class="iq-info-card"><div class="label">Status</div><div class="value"><span class="badge ${q.status === 'sent' ? 'bg-success' : 'bg-secondary'}">${q.status ? q.status.charAt(0).toUpperCase() + q.status.slice(1) : 'Draft'}</span></div></div>
                        <div class="iq-info-card"><div class="label">Items</div><div class="value">${summary.items_count || 0}</div></div>
                        <div class="iq-info-card"><div class="label">Subtotal</div><div class="value" style="color:var(--vip-accent);">$${parseFloat(summary.subtotal || 0).toFixed(2)}</div></div>
                        <div class="iq-info-card"><div class="label">Created</div><div class="value">${q.created_at || '—'}</div></div>
                        ${q.valid_until ? `<div class="iq-info-card"><div class="label">Valid Until</div><div class="value">${q.valid_until}</div></div>` : ''}
                    </div>
                    <h6 class="mb-3" style="font-size:.8rem; text-transform:uppercase; letter-spacing:.5px; color:rgba(0,0,0,.5);"><i class="bi bi-list-ul me-1"></i>Line Items</h6>
                    ${itemsHtml}
                `;
            })
            .catch(() => {
                detailBody.innerHTML = '<div class="alert alert-danger m-4">Failed to load quote details.</div>';
            });
    }

    // Edit quote - navigate to edit page
    window.editQuote = function(id) {
        window.location.href = `/installer/quotes/${id}/edit`;
    };

    // Delete quote
    window.deleteQuote = function(id, number) {
        if (!confirm(`Delete quote ${number}? This cannot be undone.`)) return;
        fetch(`/installer/quotes/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
        })
        .then(r => {
            if (r.ok) location.reload();
            else alert('Failed to delete quote.');
        });
    };

    // Auto-select first
    if (cards.length > 0) cards[0].click();
});
</script>
@endpush
