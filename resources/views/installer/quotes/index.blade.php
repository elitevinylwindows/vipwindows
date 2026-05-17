@extends('layouts.installer')
@section('title', 'My Quotes')

@push('styles')
<style>
    .sales-container { display: flex; height: calc(100vh - 56px); overflow: hidden; }

    /* ── Sales Hub Left Rail ─────────────────────── */
    .sales-hub {
        width: 260px; min-width: 260px;
        background: #fff;
        border-right: 1px solid rgba(0,0,0,.08);
        display: flex; flex-direction: column;
        overflow-y: auto;
    }
    .hub-brand { padding: 1rem 1.25rem .5rem; font-size: .85rem; font-weight: 700; color: var(--vip-accent); display: flex; align-items: center; gap: .5rem; }
    .hub-brand i { font-size: 1.1rem; }

    .hub-section { padding: .25rem 0; }
    .hub-section-title {
        font-size: .6rem; text-transform: uppercase; letter-spacing: 1.5px; font-weight: 600;
        color: rgba(0,0,0,.35); padding: .75rem 1.25rem .25rem;
    }
    .hub-link {
        display: flex; align-items: center; justify-content: space-between;
        padding: .5rem 1.25rem; font-size: .85rem; color: #333;
        text-decoration: none; border-left: 3px solid transparent; transition: all .12s;
    }
    .hub-link:hover { background: rgba(201,168,76,.05); color: #111; }
    .hub-link.active { background: rgba(201,168,76,.08); color: var(--vip-accent); border-left-color: var(--vip-accent); font-weight: 600; }
    .hub-link .hub-icon { width: 20px; text-align: center; margin-right: .5rem; font-size: .9rem; }
    .hub-link .hub-count {
        background: rgba(0,0,0,.06); color: #555; font-size: .7rem; font-weight: 600;
        padding: 1px 8px; border-radius: 10px; min-width: 24px; text-align: center;
    }
    .hub-link.active .hub-count { background: rgba(201,168,76,.2); color: #8b6914; }

    .hub-status-item {
        display: flex; align-items: center; justify-content: space-between;
        padding: .35rem 1.25rem .35rem 1.5rem; font-size: .8rem; color: #555;
        text-decoration: none; transition: background .12s; cursor: pointer;
    }
    .hub-status-item:hover { background: rgba(0,0,0,.02); }
    .hub-status-item.active-filter { font-weight: 600; color: #111; }
    .hub-status-dot { width: 8px; height: 8px; border-radius: 50%; margin-right: .5rem; }
    .hub-status-item .hub-count { font-size: .7rem; }

    /* ── Main Content ───────────────────────────── */
    .sales-main { flex: 1; overflow-y: auto; background: #f5f4f0; display: flex; flex-direction: column; }

    /* Top breadcrumb bar */
    .sales-topbar {
        background: #fff; border-bottom: 1px solid rgba(0,0,0,.06);
        padding: .6rem 1.5rem; display: flex; align-items: center; justify-content: space-between;
    }
    .sales-topbar .crumb { font-size: .85rem; color: #888; }
    .sales-topbar .crumb a { color: var(--vip-accent); text-decoration: none; }
    .sales-topbar .crumb a:hover { text-decoration: underline; }

    /* Stats bar */
    .stats-bar { display: flex; gap: 1rem; padding: 1rem 1.5rem .5rem; }
    .stat-box {
        flex: 1; background: #fff; border-radius: .5rem; padding: .75rem 1rem;
        text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,.06);
    }
    .stat-box .stat-value { font-size: 1.3rem; font-weight: 700; color: #111; }
    .stat-box .stat-label { font-size: .65rem; text-transform: uppercase; letter-spacing: .5px; color: #888; margin-top: 2px; }
    .stat-box.accent .stat-value { color: var(--vip-accent); }

    /* Tab bar + search */
    .tab-bar {
        display: flex; align-items: center; gap: .5rem; padding: .5rem 1.5rem;
        flex-wrap: wrap;
    }
    .tab-bar .tab-btn {
        padding: .35rem .85rem; font-size: .78rem; font-weight: 600; border-radius: .3rem;
        border: 1px solid rgba(0,0,0,.1); background: #fff; color: #555; cursor: pointer;
        text-decoration: none; transition: all .12s; display: inline-flex; align-items: center; gap: .3rem;
    }
    .tab-bar .tab-btn:hover { border-color: var(--vip-accent); color: var(--vip-accent); }
    .tab-bar .tab-btn.active { background: var(--vip-primary); color: #fff; border-color: var(--vip-primary); }
    .tab-bar .search-box { margin-left: auto; position: relative; }
    .tab-bar .search-box input {
        border: 1px solid rgba(0,0,0,.1); border-radius: .3rem; padding: .35rem .75rem .35rem 2rem;
        font-size: .8rem; width: 200px; background: #fff;
    }
    .tab-bar .search-box input:focus { outline: none; border-color: var(--vip-accent); }
    .tab-bar .search-box i { position: absolute; left: .6rem; top: 50%; transform: translateY(-50%); color: #aaa; font-size: .8rem; }
    .tab-bar .btn-new {
        background: var(--vip-accent); color: #fff; border: none; padding: .35rem .85rem;
        font-size: .78rem; font-weight: 600; border-radius: .3rem; cursor: pointer;
        display: inline-flex; align-items: center; gap: .3rem; text-decoration: none;
    }
    .tab-bar .btn-new:hover { background: #b8973f; color: #fff; }

    /* Quote table */
    .quotes-table-wrap { flex: 1; padding: 0 1.5rem 1rem; display: flex; gap: 1rem; }
    .quotes-table-card { flex: 1; background: #fff; border-radius: .5rem; box-shadow: 0 1px 3px rgba(0,0,0,.06); overflow: hidden; display: flex; flex-direction: column; }
    .q-tbl { width: 100%; border-collapse: collapse; }
    .q-tbl th {
        font-size: .68rem; text-transform: uppercase; letter-spacing: .5px; color: #888;
        padding: .6rem 1rem; border-bottom: 1px solid rgba(0,0,0,.08); background: #fafaf7;
        text-align: left; position: sticky; top: 0;
    }
    .q-tbl td { padding: .55rem 1rem; font-size: .82rem; border-bottom: 1px solid rgba(0,0,0,.04); cursor: pointer; }
    .q-tbl tr:hover td { background: rgba(201,168,76,.04); }
    .q-tbl tr.selected td { background: rgba(201,168,76,.1); }
    .q-tbl .q-num { font-weight: 600; color: #111; }
    .q-tbl .q-total { color: var(--vip-accent); font-weight: 600; }
    .q-tbl .badge-status { font-size: .6rem; padding: 2px 8px; border-radius: 3px; font-weight: 600; text-transform: uppercase; }
    .badge-draft { background: #eee; color: #666; }
    .badge-sent { background: #d4edda; color: #155724; }
    .badge-approved { background: #cce5ff; color: #004085; }

    /* Detail panel */
    .detail-panel {
        width: 380px; min-width: 380px; background: #fff;
        border-radius: .5rem; box-shadow: 0 1px 3px rgba(0,0,0,.06);
        display: flex; flex-direction: column; overflow: hidden;
    }
    .detail-panel .dp-empty {
        flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center;
        color: rgba(0,0,0,.3); padding: 2rem;
    }
    .detail-panel .dp-empty i { font-size: 2.5rem; margin-bottom: .75rem; opacity: .3; }
    .detail-panel .dp-header {
        padding: .75rem 1rem; border-bottom: 1px solid rgba(0,0,0,.06);
        display: flex; justify-content: space-between; align-items: center;
    }
    .detail-panel .dp-header h6 { margin: 0; font-weight: 700; font-size: .95rem; }
    .detail-panel .dp-body { flex: 1; overflow-y: auto; padding: 1rem; }
    .dp-field { margin-bottom: .6rem; }
    .dp-field .dp-label { font-size: .65rem; text-transform: uppercase; letter-spacing: .5px; color: #888; }
    .dp-field .dp-value { font-size: .85rem; font-weight: 500; color: #111; }

    .dp-items-tbl { width: 100%; border-collapse: collapse; margin-top: .5rem; }
    .dp-items-tbl th { font-size: .6rem; text-transform: uppercase; color: #888; padding: .35rem .5rem; border-bottom: 1px solid rgba(0,0,0,.08); }
    .dp-items-tbl td { font-size: .78rem; padding: .35rem .5rem; border-bottom: 1px solid rgba(0,0,0,.04); }

    @media (max-width: 1199.98px) {
        .detail-panel { display: none; }
    }
    @media (max-width: 991.98px) {
        .sales-container { flex-direction: column; height: auto; }
        .sales-hub { width: 100%; min-width: 100%; max-height: 40vh; flex-direction: row; overflow-x: auto; }
    }
</style>
@endpush

@section('content')
@php
    $totalQuotes = $quotes->total();
    $draftCount = \App\Models\Quote::where('entered_by', Auth::user()->name)->where('status', 'draft')->count();
    $sentCount = \App\Models\Quote::where('entered_by', Auth::user()->name)->where('status', 'sent')->count();
    $approvedCount = \App\Models\Quote::where('entered_by', Auth::user()->name)->where('status', 'approved')->count();
@endphp

<div class="sales-container">
    {{-- Sales Hub Left Rail --}}
    <div class="sales-hub">
        <div class="hub-brand"><i class="bi bi-bar-chart-line"></i> SALES HUB</div>

        <div class="hub-section">
            <a href="{{ route('installer.dashboard') }}" class="hub-link">
                <span><span class="hub-icon"><i class="bi bi-speedometer2"></i></span> Dashboard</span>
            </a>
        </div>

        <div class="hub-section">
            <div class="hub-section-title">Quick Actions</div>
            <a href="{{ route('installer.quotes.create') }}" class="hub-link {{ request()->routeIs('installer.quotes.create') ? 'active' : '' }}">
                <span><span class="hub-icon"><i class="bi bi-plus-circle-fill text-danger"></i></span> New Quote</span>
            </a>
        </div>

        <div class="hub-section">
            <div class="hub-section-title">Pipeline</div>
            <a href="{{ route('installer.quotes.index') }}" class="hub-link {{ request()->routeIs('installer.quotes.index') && !request('status') ? 'active' : '' }}">
                <span><span class="hub-icon"><i class="bi bi-file-earmark-text"></i></span> Quotes</span>
                <span class="hub-count">{{ $totalQuotes }}</span>
            </a>
            <a href="{{ route('installer.jobs.index') }}" class="hub-link {{ request()->routeIs('installer.jobs.*') ? 'active' : '' }}">
                <span><span class="hub-icon"><i class="bi bi-tools"></i></span> Jobs</span>
            </a>
            <a href="{{ route('installer.invoices.index') }}" class="hub-link {{ request()->routeIs('installer.invoices.*') ? 'active' : '' }}">
                <span><span class="hub-icon"><i class="bi bi-receipt"></i></span> Invoices</span>
            </a>
        </div>

        <div class="hub-section">
            <div class="hub-section-title">Quote Status</div>
            <a href="{{ route('installer.quotes.index', ['status' => 'draft']) }}" class="hub-status-item {{ $status === 'draft' ? 'active-filter' : '' }}">
                <span><span class="hub-status-dot" style="background:#6c757d; display:inline-block;"></span> Draft</span>
                <span class="hub-count">{{ $draftCount }}</span>
            </a>
            <a href="{{ route('installer.quotes.index', ['status' => 'sent']) }}" class="hub-status-item {{ $status === 'sent' ? 'active-filter' : '' }}">
                <span><span class="hub-status-dot" style="background:#28a745; display:inline-block;"></span> Sent</span>
                <span class="hub-count">{{ $sentCount }}</span>
            </a>
            <a href="{{ route('installer.quotes.index', ['status' => 'approved']) }}" class="hub-status-item {{ $status === 'approved' ? 'active-filter' : '' }}">
                <span><span class="hub-status-dot" style="background:#007bff; display:inline-block;"></span> Approved</span>
                <span class="hub-count">{{ $approvedCount }}</span>
            </a>
        </div>

        <div class="hub-section">
            <div class="hub-section-title">Customers</div>
            <a href="{{ route('installer.customers.index') }}" class="hub-link {{ request()->routeIs('installer.customers.*') ? 'active' : '' }}">
                <span><span class="hub-icon"><i class="bi bi-people"></i></span> My Customers</span>
            </a>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="sales-main">
        {{-- Breadcrumb --}}
        <div class="sales-topbar">
            <div class="crumb">
                <a href="{{ route('installer.dashboard') }}">Dashboard</a> &rsaquo;
                <strong>Quotes{{ $status !== 'all' ? ' / ' . ucfirst($status) : '' }}</strong>
            </div>
        </div>

        {{-- Stats --}}
        <div class="stats-bar">
            <div class="stat-box"><div class="stat-value">{{ $totalQuotes }}</div><div class="stat-label">Quotes</div></div>
            <div class="stat-box"><div class="stat-value">{{ $draftCount }}</div><div class="stat-label">Drafts</div></div>
            <div class="stat-box"><div class="stat-value">{{ $sentCount }}</div><div class="stat-label">Sent</div></div>
            <div class="stat-box accent"><div class="stat-value">{{ $approvedCount }}</div><div class="stat-label">Approved</div></div>
        </div>

        {{-- Tab bar --}}
        <div class="tab-bar">
            <a href="{{ route('installer.quotes.index') }}" class="tab-btn {{ $status === 'all' ? 'active' : '' }}"><i class="bi bi-file-earmark-text"></i> Quotes</a>
            <a href="{{ route('installer.jobs.index') }}" class="tab-btn"><i class="bi bi-tools"></i> Jobs</a>
            <a href="{{ route('installer.invoices.index') }}" class="tab-btn"><i class="bi bi-receipt"></i> Invoices</a>
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="iqSearch" placeholder="Search...">
            </div>
            <a href="{{ route('installer.quotes.create') }}" class="btn-new"><i class="bi bi-plus-lg"></i> New Quote</a>
        </div>

        {{-- Table + Detail --}}
        <div class="quotes-table-wrap">
            <div class="quotes-table-card">
                <div style="flex:1; overflow-y:auto;">
                    <table class="q-tbl">
                        <thead>
                            <tr>
                                <th>Quote #</th>
                                <th>Customer</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Expires</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($quotes as $quote)
                                <tr class="q-row" data-id="{{ $quote->id }}" data-search="{{ strtolower($quote->quote_number . ' ' . ($quote->billing_name ?? '')) }}">
                                    <td class="q-num">{{ $quote->quote_number }}</td>
                                    <td>{{ $quote->billing_name ?: '—' }}</td>
                                    <td>{{ $quote->items_count ?? 0 }}</td>
                                    <td class="q-total">${{ number_format($quote->total ?? 0, 2) }}</td>
                                    <td><span class="badge-status badge-{{ $quote->status }}">{{ ucfirst($quote->status) }}</span></td>
                                    <td class="text-muted small">{{ $quote->created_at?->format('Y-m-d') }}</td>
                                    <td class="text-muted small">{{ $quote->valid_until ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No quotes found. <a href="{{ route('installer.quotes.create') }}">Create your first quote</a>.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($quotes->hasPages())
                    <div class="px-3 py-2 border-top" style="font-size:.8rem;">{{ $quotes->appends(request()->query())->links() }}</div>
                @endif
            </div>

            {{-- Detail Panel --}}
            <div class="detail-panel" id="detailPanel">
                <div class="dp-empty" id="dpEmpty">
                    <i class="bi bi-bar-chart-line"></i>
                    <p class="small">Select a quote to view details</p>
                </div>
                <div id="dpContent" style="display:none; flex:1; display:flex; flex-direction:column;">
                    <div class="dp-header">
                        <h6 id="dpTitle">—</h6>
                        <div id="dpActions"></div>
                    </div>
                    <div class="dp-body" id="dpBody"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const rows = document.querySelectorAll('.q-row');
    const csrf = document.querySelector('meta[name=csrf-token]').content;

    // Search
    document.getElementById('iqSearch').addEventListener('input', function() {
        const term = this.value.toLowerCase();
        rows.forEach(row => {
            row.style.display = (!term || row.dataset.search.includes(term)) ? '' : 'none';
        });
    });

    // Row click → load detail
    rows.forEach(row => {
        row.addEventListener('click', function() {
            rows.forEach(r => r.classList.remove('selected'));
            this.classList.add('selected');
            loadDetail(this.dataset.id);
        });
    });

    function loadDetail(id) {
        document.getElementById('dpEmpty').style.display = 'none';
        const dpContent = document.getElementById('dpContent');
        dpContent.style.display = 'flex';
        document.getElementById('dpBody').innerHTML = '<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-secondary"></div></div>';

        fetch(`/installer/quotes/${id}`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }})
            .then(r => r.json())
            .then(data => {
                const q = data.quote;
                const items = data.items || [];
                const summary = data.summary || {};

                document.getElementById('dpTitle').textContent = q.quote_number;
                document.getElementById('dpActions').innerHTML = `
                    <a href="/installer/quotes/${q.id}/edit" class="btn btn-sm btn-outline-primary" style="font-size:.7rem; padding:2px 8px;"><i class="bi bi-pencil"></i></a>
                    <button class="btn btn-sm btn-outline-danger ms-1" style="font-size:.7rem; padding:2px 8px;" onclick="deleteQuote(${q.id}, '${q.quote_number}')"><i class="bi bi-trash"></i></button>
                `;

                let itemsHtml = '';
                if (items.length) {
                    itemsHtml = `<table class="dp-items-tbl">
                        <thead><tr><th>Item</th><th>Qty</th><th class="text-end">Total</th></tr></thead>
                        <tbody>${items.map(i => `<tr><td>${i.series_type || i.description || 'Item'}</td><td>${i.qty || 1}</td><td class="text-end" style="color:var(--vip-accent); font-weight:600;">$${parseFloat(i.total || 0).toFixed(2)}</td></tr>`).join('')}</tbody>
                    </table>`;
                }

                document.getElementById('dpBody').innerHTML = `
                    <div class="dp-field"><div class="dp-label">Customer</div><div class="dp-value">${q.billing_name || '—'}</div></div>
                    <div class="row">
                        <div class="col-6 dp-field"><div class="dp-label">Status</div><div class="dp-value"><span class="badge-status badge-${q.status}">${q.status ? q.status.charAt(0).toUpperCase() + q.status.slice(1) : 'Draft'}</span></div></div>
                        <div class="col-6 dp-field"><div class="dp-label">Items</div><div class="dp-value">${summary.items_count || 0}</div></div>
                    </div>
                    <div class="row">
                        <div class="col-6 dp-field"><div class="dp-label">Subtotal</div><div class="dp-value" style="color:var(--vip-accent);">$${parseFloat(summary.subtotal || 0).toFixed(2)}</div></div>
                        <div class="col-6 dp-field"><div class="dp-label">Created</div><div class="dp-value">${q.created_at || '—'}</div></div>
                    </div>
                    ${q.billing_email ? `<div class="dp-field"><div class="dp-label">Email</div><div class="dp-value">${q.billing_email}</div></div>` : ''}
                    ${q.billing_phone ? `<div class="dp-field"><div class="dp-label">Phone</div><div class="dp-value">${q.billing_phone}</div></div>` : ''}
                    ${q.valid_until ? `<div class="dp-field"><div class="dp-label">Valid Until</div><div class="dp-value">${q.valid_until}</div></div>` : ''}
                    ${items.length ? `<div class="mt-3"><div class="dp-label mb-1">Line Items</div>${itemsHtml}</div>` : ''}
                    ${q.notes ? `<div class="dp-field mt-3"><div class="dp-label">Notes</div><div class="dp-value small">${q.notes}</div></div>` : ''}
                `;
            })
            .catch(() => {
                document.getElementById('dpBody').innerHTML = '<div class="alert alert-danger m-3" style="font-size:.8rem;">Failed to load details.</div>';
            });
    }

    window.deleteQuote = function(id, number) {
        if (!confirm('Delete quote ' + number + '?')) return;
        fetch('/installer/quotes/' + id, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
        }).then(r => { if (r.ok) location.reload(); else alert('Failed.'); });
    };

    // Auto-select first row
    if (rows.length > 0) rows[0].click();
});
</script>
@endpush
