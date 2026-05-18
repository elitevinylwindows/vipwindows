@extends('layouts.app')
@section('title', 'Tech Measures')

@push('styles')
<style>
    .tm-container { display: flex; height: calc(100vh - 56px); overflow: hidden; }
    .tm-rail { width: 320px; min-width: 320px; background: #fff; border-right: 1px solid rgba(0,0,0,.08); display: flex; flex-direction: column; }
    .tm-rail-header { padding: 1.25rem 1rem .75rem; border-bottom: 1px solid rgba(0,0,0,.06); }
    .tm-rail-header h6 { font-size: .75rem; text-transform: uppercase; letter-spacing: 1.2px; color: rgba(0,0,0,.4); margin-bottom: .75rem; }
    .tm-rail-search input { width: 100%; padding: .4rem .75rem; font-size: .82rem; border: 1px solid rgba(0,0,0,.1); border-radius: .375rem; background: #fafaf7; }
    .tm-rail-search input:focus { outline: none; border-color: var(--vip-accent); }
    .tm-rail-tabs { display: flex; gap: 0; padding: 0; margin-top: .75rem; flex-wrap: wrap; }
    .tm-rail-tabs .tab-btn {
        flex: 1; text-align: center; padding: .4rem .25rem; font-size: .7rem;
        background: rgba(0,0,0,.03); border: 1px solid rgba(0,0,0,.08);
        color: rgba(0,0,0,.5); cursor: pointer; transition: all .15s;
    }
    .tm-rail-tabs .tab-btn:first-child { border-radius: .3rem 0 0 .3rem; }
    .tm-rail-tabs .tab-btn:last-child { border-radius: 0 .3rem .3rem 0; }
    .tm-rail-tabs .tab-btn.active { background: var(--vip-accent); color: #fff; border-color: var(--vip-accent); }
    .tm-rail-list { flex: 1; overflow-y: auto; padding: .5rem; }
    .tm-card { background: #fafaf7; border: 1px solid rgba(0,0,0,.06); border-radius: .5rem; padding: .75rem 1rem; margin-bottom: .5rem; cursor: pointer; transition: all .15s; }
    .tm-card:hover { background: rgba(201,168,76,.04); border-color: rgba(201,168,76,.2); }
    .tm-card.active { background: rgba(201,168,76,.08); border-color: var(--vip-accent); }
    .tm-card .tm-name { font-weight: 600; font-size: .9rem; color: #111; }
    .tm-card .tm-addr { font-size: .78rem; color: #888; margin-top: 2px; }
    .tm-card .tm-meta { display: flex; justify-content: space-between; align-items: center; margin-top: .35rem; }
    .tm-card .tm-date { font-size: .7rem; color: #aaa; }
    .tm-card .tm-badge { font-size: .6rem; padding: 2px 6px; border-radius: 3px; font-weight: 600; text-transform: uppercase; }
    .tm-badge-pending { background: rgba(255,193,7,.15); color: #856404; }
    .tm-badge-in_progress { background: rgba(0,123,255,.15); color: #004085; }
    .tm-badge-completed { background: rgba(40,167,69,.15); color: #155724; }
    .tm-badge-converted { background: rgba(108,117,125,.15); color: #495057; }
    .tm-main { flex: 1; overflow-y: auto; background: var(--vip-light); }
    .tm-main-toolbar { background: #fff; border-bottom: 1px solid rgba(0,0,0,.06); padding: .75rem 1.5rem; display: flex; align-items: center; justify-content: space-between; }
    .tm-main-toolbar h5 { font-size: 1rem; font-weight: 700; margin: 0; }
    .tm-detail-body { padding: 1.5rem; }
    .tm-empty-state { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 60vh; color: rgba(0,0,0,.35); }
    .tm-empty-state i { font-size: 3rem; margin-bottom: 1rem; }
    .tm-info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
    .tm-info-card { background: #fff; border-radius: .5rem; padding: 1rem; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
    .tm-info-card .label { font-size: .7rem; text-transform: uppercase; letter-spacing: .5px; color: rgba(0,0,0,.45); margin-bottom: .25rem; }
    .tm-info-card .value { font-size: .9rem; font-weight: 600; color: #111; }
    .tm-items-tbl { width: 100%; border-collapse: collapse; background: #fff; border-radius: .5rem; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
    .tm-items-tbl th { font-size: .68rem; text-transform: uppercase; letter-spacing: .5px; color: rgba(0,0,0,.4); padding: .5rem .6rem; border-bottom: 1px solid rgba(0,0,0,.08); background: #fafafa; }
    .tm-items-tbl td { padding: .45rem .6rem; font-size: .8rem; border-bottom: 1px solid rgba(0,0,0,.04); vertical-align: top; }
    .tm-photo-grid { display: flex; flex-wrap: wrap; gap: .5rem; }
    .tm-photo-card { width: 120px; height: 120px; border-radius: .5rem; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.1); cursor: pointer; }
    .tm-photo-card img { width: 100%; height: 100%; object-fit: cover; }
    .section-title { font-size: .75rem; text-transform: uppercase; letter-spacing: .5px; color: rgba(0,0,0,.5); margin-bottom: .5rem; margin-top: 1.5rem; }
    .item-photos { display: flex; gap: 4px; flex-wrap: wrap; margin-top: 4px; }
    .item-photo { width: 40px; height: 40px; border-radius: 4px; object-fit: cover; cursor: pointer; }
    @media (max-width: 991.98px) { .tm-container { flex-direction: column; height: auto; } .tm-rail { width: 100%; min-width: 100%; max-height: 45vh; } }
</style>
@endpush

@section('content')
<div class="tm-container">
    <div class="tm-rail">
        <div class="tm-rail-header">
            <h6>Tech Measures</h6>
            <div class="tm-rail-search">
                <input type="text" id="tmSearch" placeholder="Search...">
            </div>
            <div class="tm-rail-tabs">
                <div class="tab-btn {{ $status === 'all' ? 'active' : '' }}" data-status="all">All</div>
                <div class="tab-btn {{ $status === 'pending' ? 'active' : '' }}" data-status="pending">Pending</div>
                <div class="tab-btn {{ $status === 'in_progress' ? 'active' : '' }}" data-status="in_progress">Active</div>
                <div class="tab-btn {{ $status === 'completed' ? 'active' : '' }}" data-status="completed">Done</div>
                <div class="tab-btn {{ $status === 'converted' ? 'active' : '' }}" data-status="converted">Quoted</div>
            </div>
        </div>
        <div class="tm-rail-list">
            @forelse($measures as $m)
                <div class="tm-card" data-id="{{ $m->id }}" data-search="{{ strtolower(($m->customer_name ?? '') . ' ' . ($m->address ?? '') . ' ' . ($m->assignee?->name ?? '')) }}">
                    <div class="tm-name"><i class="bi bi-rulers me-1"></i>{{ $m->customer_name ?: 'No customer' }}</div>
                    <div class="tm-addr"><i class="bi bi-geo-alt me-1"></i>{{ Str::limit($m->address ?: 'No address', 35) }}</div>
                    <div class="tm-meta">
                        <span class="tm-date">{{ $m->assignee?->name ?? 'Unassigned' }} · {{ $m->created_at->format('M d') }}</span>
                        <span class="tm-badge tm-badge-{{ $m->status }}">{{ ucfirst(str_replace('_', ' ', $m->status)) }}</span>
                    </div>
                </div>
            @empty
                <div class="text-center py-4" style="color:rgba(0,0,0,.3);">
                    <i class="bi bi-rulers" style="font-size:2rem;"></i>
                    <p class="mt-2 mb-0 small">No tech measures</p>
                </div>
            @endforelse
        </div>
    </div>
    <div class="tm-main">
        <div class="tm-main-toolbar">
            <h5 id="tmDetailTitle">Tech Measure Details</h5>
            <div id="tmToolbarActions"></div>
        </div>
        <div class="tm-detail-body" id="tmDetailBody">
            <div class="tm-empty-state"><i class="bi bi-rulers"></i><p>Select a tech measure to review</p></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const csrf = document.querySelector('meta[name=csrf-token]').content;
let currentMeasureId = null;

document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.tm-card');

    document.querySelectorAll('.tm-rail-tabs .tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const status = this.dataset.status;
            const url = new URL(window.location);
            if (status !== 'all') url.searchParams.set('status', status);
            else url.searchParams.delete('status');
            window.location = url;
        });
    });

    document.getElementById('tmSearch').addEventListener('input', function() {
        const term = this.value.toLowerCase();
        document.querySelectorAll('.tm-card').forEach(card => {
            card.style.display = (!term || card.dataset.search.includes(term)) ? '' : 'none';
        });
    });

    cards.forEach(card => {
        card.addEventListener('click', function() {
            cards.forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            loadMeasure(this.dataset.id);
        });
    });

    if (cards.length > 0) cards[0].click();
});

function loadMeasure(id) {
    currentMeasureId = id;
    const body = document.getElementById('tmDetailBody');
    body.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-secondary"></div></div>';

    fetch(`/admin/tech-measures/${id}`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
    .then(r => r.json())
    .then(data => renderDetail(data))
    .catch(() => { body.innerHTML = '<div class="alert alert-danger m-4">Failed to load.</div>'; });
}

function renderDetail(data) {
    const m = data.measure;
    const items = data.items || [];
    const photos = data.photos || [];
    const body = document.getElementById('tmDetailBody');
    const title = document.getElementById('tmDetailTitle');
    const toolbar = document.getElementById('tmToolbarActions');

    title.textContent = m.customer_name || 'Tech Measure';

    let actions = '';
    if (m.status === 'completed') {
        actions += `<button class="btn btn-sm btn-vip" onclick="convertToQuote(${m.id})"><i class="bi bi-calculator me-1"></i>Convert to Quote</button>`;
    }
    toolbar.innerHTML = actions;

    let itemsHtml = '';
    if (items.length) {
        itemsHtml = `<table class="tm-items-tbl"><thead><tr>
            <th>#</th><th>Room</th><th>Type</th><th>Description</th><th>W × H</th><th>Qty</th><th>Frame</th><th>Glass</th><th>Grid</th><th>Condition</th><th>Notes</th>
        </tr></thead><tbody>`;
        items.forEach((item, idx) => {
            const photoHtml = item.photos?.length ? `<div class="item-photos">${item.photos.map(p => `<img src="${p.url}" class="item-photo" onclick="window.open('${p.url}','_blank')">`).join('')}</div>` : '';
            itemsHtml += `<tr>
                <td class="text-center text-muted">${idx + 1}</td>
                <td><strong>${item.room_label || '—'}</strong>${photoHtml}</td>
                <td>${item.opening_type || '—'}</td>
                <td>${item.description || '—'}<br><span class="text-muted" style="font-size:.7rem;">${item.series_type || ''}</span></td>
                <td class="text-nowrap">${item.width || '—'} × ${item.height || '—'}</td>
                <td class="text-center">${item.qty || 1}</td>
                <td>${item.frame_type || '—'}</td>
                <td>${item.glass_type || '—'}${item.tempered ? ' <span class="badge bg-warning text-dark" style="font-size:.55rem;">T</span>' : ''}</td>
                <td>${item.grid_pattern || '—'}</td>
                <td style="font-size:.72rem;">${item.existing_condition || '—'}</td>
                <td style="font-size:.72rem;">${item.notes || ''}</td>
            </tr>`;
        });
        itemsHtml += '</tbody></table>';
    } else {
        itemsHtml = '<p class="text-muted small">No measurements recorded yet.</p>';
    }

    let photosHtml = '';
    if (photos.length) {
        photosHtml = '<div class="tm-photo-grid">' + photos.map(p => `<div class="tm-photo-card"><img src="${p.url}" onclick="window.open('${p.url}','_blank')"></div>`).join('') + '</div>';
    } else {
        photosHtml = '<p class="text-muted small">No site photos.</p>';
    }

    body.innerHTML = `
        <div class="tm-info-grid">
            <div class="tm-info-card"><div class="label">Customer</div><div class="value">${m.customer_name || '—'}</div></div>
            <div class="tm-info-card"><div class="label">Phone</div><div class="value">${m.customer_phone || '—'}</div></div>
            <div class="tm-info-card"><div class="label">Email</div><div class="value">${m.customer_email || '—'}</div></div>
            <div class="tm-info-card"><div class="label">Address</div><div class="value">${m.address || '—'}</div></div>
            <div class="tm-info-card"><div class="label">Status</div><div class="value"><span class="badge bg-${m.status === 'in_progress' ? 'primary' : m.status === 'completed' ? 'success' : m.status === 'converted' ? 'secondary' : 'warning'}">${m.status?.replace('_',' ')}</span></div></div>
            <div class="tm-info-card"><div class="label">Openings</div><div class="value">${items.length}</div></div>
            ${m.started_at ? `<div class="tm-info-card"><div class="label">Started</div><div class="value">${new Date(m.started_at).toLocaleString()}</div></div>` : ''}
            ${m.completed_at ? `<div class="tm-info-card"><div class="label">Completed</div><div class="value">${new Date(m.completed_at).toLocaleString()}</div></div>` : ''}
        </div>
        <h6 class="section-title"><i class="bi bi-rulers"></i> Measurements (${items.length})</h6>
        ${itemsHtml}
        <h6 class="section-title"><i class="bi bi-image"></i> Site Photos</h6>
        ${photosHtml}
        ${m.notes ? `<h6 class="section-title"><i class="bi bi-journal-text"></i> Notes</h6><div class="card" style="border:none;box-shadow:0 1px 4px rgba(0,0,0,.06);"><div class="card-body py-2 px-3"><p class="small mb-0">${m.notes.replace(/\n/g, '<br>')}</p></div></div>` : ''}
    `;
}

function convertToQuote(measureId) {
    if (!confirm('Convert this tech measure into a quote? Items will be copied.')) return;
    fetch(`/admin/tech-measures/${measureId}/convert-to-quote`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('Quote created! Redirecting...');
            window.location.href = `/admin/quotes/${data.quote_id}`;
        } else {
            alert(data.error || 'Failed to convert.');
        }
    })
    .catch(() => alert('Failed to convert.'));
}
</script>
@endpush
