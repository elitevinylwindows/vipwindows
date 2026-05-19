@extends('layouts.installer')
@section('title', 'Services')

@push('styles')
<style>
    .sj-container { display: flex; height: calc(100vh - 56px); overflow: hidden; }

    /* ── Left Rail ── */
    .sj-rail {
        width: 320px; min-width: 320px;
        background: var(--vip-primary); color: #fff;
        display: flex; flex-direction: column;
        border-right: 1px solid rgba(255,255,255,.06);
    }
    .sj-rail-header { padding: 1.25rem 1rem .75rem; }
    .sj-rail-header h6 { font-size: .75rem; text-transform: uppercase; letter-spacing: 1.2px; color: rgba(255,255,255,.5); margin-bottom: .75rem; }
    .sj-rail-search { display: flex; gap: .5rem; }
    .sj-rail-search input {
        flex: 1; background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.12);
        color: #fff; border-radius: .375rem; padding: .4rem .75rem; font-size: .85rem;
    }
    .sj-rail-search input::placeholder { color: rgba(255,255,255,.4); }
    .sj-rail-search input:focus { outline: none; border-color: var(--vip-accent); }

    .sj-rail-tabs { display: flex; gap: 0; padding: 0 1rem; margin-top: .75rem; flex-wrap: wrap; }
    .sj-rail-tabs .tab-btn {
        flex: 1; text-align: center; padding: .4rem .25rem; font-size: .7rem;
        background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.1);
        color: rgba(255,255,255,.6); cursor: pointer; transition: all .15s;
    }
    .sj-rail-tabs .tab-btn:first-child { border-radius: .3rem 0 0 .3rem; }
    .sj-rail-tabs .tab-btn:last-child { border-radius: 0 .3rem .3rem 0; }
    .sj-rail-tabs .tab-btn.active { background: var(--vip-accent); color: #fff; border-color: var(--vip-accent); }

    .sj-rail-list { flex: 1; overflow-y: auto; padding: .5rem; }
    .sj-card {
        background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.08);
        border-radius: .5rem; padding: .75rem 1rem; margin-bottom: .5rem;
        cursor: pointer; transition: all .15s;
    }
    .sj-card:hover { background: rgba(255,255,255,.08); border-color: rgba(201,168,76,.3); }
    .sj-card.active { background: rgba(201,168,76,.12); border-color: var(--vip-accent); }
    .sj-card .sj-name { font-weight: 600; font-size: .9rem; color: #fff; }
    .sj-card .sj-addr { font-size: .78rem; color: rgba(255,255,255,.55); margin-top: 2px; }
    .sj-card .sj-meta { display: flex; justify-content: space-between; align-items: center; margin-top: .35rem; }
    .sj-card .sj-date { font-size: .7rem; color: rgba(255,255,255,.4); }
    .sj-card .sj-badge { font-size: .6rem; padding: 2px 6px; border-radius: 3px; font-weight: 600; text-transform: uppercase; }
    .sj-badge-scheduled { background: rgba(0,123,255,.25); color: #5ba8ff; }
    .sj-badge-completed { background: rgba(40,167,69,.25); color: #7ddf9b; }
    .sj-badge-rescheduled { background: rgba(108,117,125,.25); color: #adb5bd; }

    .sj-rail-footer {
        padding: .75rem 1rem; border-top: 1px solid rgba(255,255,255,.08);
        font-size: .75rem; color: rgba(255,255,255,.4);
    }

    /* ── Main Panel ── */
    .sj-main { flex: 1; overflow-y: auto; background: var(--vip-light); }
    .sj-main-toolbar {
        background: #fff; border-bottom: 1px solid rgba(0,0,0,.06);
        padding: .75rem 1.5rem; display: flex; align-items: center; justify-content: space-between;
    }
    .sj-main-toolbar h5 { font-size: 1rem; font-weight: 700; margin: 0; }
    .sj-detail-body { padding: 1.5rem; }

    .sj-empty-state {
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        height: 60vh; color: rgba(0,0,0,.35);
    }
    .sj-empty-state i { font-size: 3rem; margin-bottom: 1rem; }

    .sj-info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
    .sj-info-card { background: #fff; border-radius: .5rem; padding: 1rem; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
    .sj-info-card .label { font-size: .7rem; text-transform: uppercase; letter-spacing: .5px; color: rgba(0,0,0,.45); margin-bottom: .25rem; }
    .sj-info-card .value { font-size: .9rem; font-weight: 600; color: #111; }

    .section-title { font-size: .85rem; font-weight: 700; margin: 1.25rem 0 .75rem; color: var(--vip-primary); }
    .section-title i { color: var(--vip-accent); }

    .sj-checklist { background: #fff; border-radius: .5rem; padding: 1rem; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
    .sj-checklist-item { display: flex; align-items: center; gap: .5rem; padding: .4rem 0; border-bottom: 1px solid #f0f0f0; font-size: .85rem; }
    .sj-checklist-item:last-child { border-bottom: none; }

    @media (max-width: 991.98px) {
        .sj-container { flex-direction: column; height: auto; min-height: calc(100vh - 56px); }
        .sj-rail { width: 100%; min-width: 100%; max-height: 280px; }
    }
</style>
@endpush

@section('content')
<div class="sj-container">
    {{-- Left Rail --}}
    <div class="sj-rail">
        <div class="sj-rail-header">
            <h6><i class="bi bi-wrench me-1"></i> Services</h6>
            <div class="sj-rail-search">
                <input type="text" id="sjSearch" placeholder="Search services..." oninput="filterCards()">
            </div>
            <div class="sj-rail-tabs">
                <div class="tab-btn {{ $status === 'all' ? 'active' : '' }}" onclick="filterStatus('all')">All</div>
                <div class="tab-btn {{ $status === 'today' ? 'active' : '' }}" onclick="filterStatus('today')">Today</div>
                <div class="tab-btn {{ $status === 'upcoming' ? 'active' : '' }}" onclick="filterStatus('upcoming')">Upcoming</div>
                <div class="tab-btn {{ $status === 'completed' ? 'active' : '' }}" onclick="filterStatus('completed')">Completed</div>
            </div>
        </div>

        <div class="sj-rail-list" id="sjRailList">
            @forelse($events as $e)
                <div class="sj-card" data-id="{{ $e->id }}" onclick="loadEvent({{ $e->id }}, this)">
                    <div class="d-flex align-items-center gap-2">
                        <span style="width:4px; height:28px; border-radius:2px; background:{{ $e->service?->color ?? $e->color ?? '#c9a84c' }};"></span>
                        <div>
                            <div class="sj-name">{{ $e->customer_name ?: $e->title }}</div>
                            <div class="sj-addr">{{ $e->service?->name ?? $e->title }}</div>
                        </div>
                    </div>
                    <div class="sj-meta">
                        <span class="sj-date"><i class="bi bi-calendar3 me-1"></i>{{ $e->event_date->format('M d, Y') }}{{ $e->event_time ? ' @ ' . $e->event_time : '' }}</span>
                        @php $es = $e->event_status ?? 'scheduled'; @endphp
                        <span class="sj-badge sj-badge-{{ $es }}">{{ ucfirst($es) }}</span>
                    </div>
                </div>
            @empty
                <div class="text-center py-5 text-white-50">
                    <i class="bi bi-wrench" style="font-size:2rem; opacity:.3;"></i>
                    <p class="small mt-2">No services found.</p>
                </div>
            @endforelse
        </div>

        <div class="sj-rail-footer">
            {{ $events->total() }} service{{ $events->total() !== 1 ? 's' : '' }}
        </div>
    </div>

    {{-- Main Panel --}}
    <div class="sj-main">
        <div class="sj-main-toolbar">
            <h5 id="sjDetailTitle"><i class="bi bi-wrench me-1" style="color:var(--vip-accent);"></i> Services</h5>
            <div id="sjToolbarActions"></div>
        </div>
        <div class="sj-detail-body" id="sjDetailBody">
            <div class="sj-empty-state">
                <i class="bi bi-arrow-left-circle"></i>
                <h5 style="color:#666;">Select a service</h5>
                <p class="small">Choose a service from the list to view its details.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const csrf = document.querySelector('meta[name=csrf-token]').content;
let currentEventId = null;

function filterStatus(status) {
    const url = new URL(window.location);
    url.searchParams.set('status', status);
    window.location = url;
}

function filterCards() {
    const q = document.getElementById('sjSearch').value.toLowerCase();
    document.querySelectorAll('.sj-card').forEach(card => {
        const text = card.textContent.toLowerCase();
        card.style.display = text.includes(q) ? '' : 'none';
    });
}

function loadEvent(id, el) {
    document.querySelectorAll('.sj-card').forEach(c => c.classList.remove('active'));
    if (el) el.classList.add('active');
    currentEventId = id;

    const body = document.getElementById('sjDetailBody');
    body.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-secondary"></div></div>';

    fetch(`/installer/service-jobs/${id}`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => renderDetail(data))
    .catch(() => {
        body.innerHTML = '<div class="alert alert-danger m-4">Failed to load service details.</div>';
    });
}

function renderDetail(data) {
    const e = data.event;
    const job = data.job;
    const body = document.getElementById('sjDetailBody');
    const title = document.getElementById('sjDetailTitle');
    const toolbar = document.getElementById('sjToolbarActions');

    title.innerHTML = `<span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:${e.service_color}; margin-right:6px;"></span> ${escHtml(e.customer_name || e.title)}`;

    // Toolbar actions — clock in/out + complete
    let actions = '';
    if (e.is_clocked_in) {
        actions += `<span class="badge bg-primary me-2" style="font-size:.75rem;"><i class="bi bi-clock me-1"></i><span id="elapsedTime">--:--</span></span>`;
        actions += `<button class="btn btn-sm btn-warning" onclick="clockOut(${e.id})"><i class="bi bi-stop-circle me-1"></i>Clock Out</button> `;
    } else if (e.event_status !== 'completed') {
        actions += `<button class="btn btn-sm btn-info text-white" onclick="clockIn(${e.id})"><i class="bi bi-play-circle me-1"></i>Clock In</button> `;
    }
    if (e.event_status !== 'completed') {
        actions += `<button class="btn btn-sm btn-success" onclick="completeService(${e.id})"><i class="bi bi-check-lg me-1"></i>Complete</button> `;
    }
    if (e.event_status === 'completed' && e.total_time_minutes > 0) {
        const hrs = Math.floor(e.total_time_minutes / 60), mins = e.total_time_minutes % 60;
        actions = `<span class="badge bg-success me-2" style="font-size:.75rem;"><i class="bi bi-check-circle me-1"></i>Total: ${hrs}h ${mins}m</span>` + actions;
    }
    toolbar.innerHTML = actions;

    // Start elapsed timer if clocked in
    if (e.is_clocked_in && e.active_since) {
        startElapsedTimer(new Date(e.active_since));
    }

    let html = `
        <div class="sj-info-grid">
            <div class="sj-info-card"><div class="label">Customer</div><div class="value">${escHtml(e.customer_name || '—')}</div></div>
            <div class="sj-info-card"><div class="label">Phone</div><div class="value">${e.customer_phone ? `<a href="tel:${e.customer_phone}">${escHtml(e.customer_phone)}</a>` : '—'}</div></div>
            <div class="sj-info-card"><div class="label">Address</div><div class="value">${escHtml(e.address || '—')}</div></div>
            <div class="sj-info-card"><div class="label">Service</div><div class="value"><span style="color:${e.service_color}; font-weight:700;">${escHtml(e.service_name)}</span></div></div>
            <div class="sj-info-card"><div class="label">Date</div><div class="value">${escHtml(e.event_date || '—')}${e.event_time ? ' @ ' + escHtml(e.event_time) : ''}</div></div>
            <div class="sj-info-card"><div class="label">Crew</div><div class="value">${escHtml(e.crew_name)}</div></div>
            <div class="sj-info-card"><div class="label">Status</div><div class="value"><span class="badge bg-${e.event_status === 'completed' ? 'success' : e.event_status === 'rescheduled' ? 'secondary' : 'primary'}">${escHtml(e.event_status?.replace('_',' ') || 'scheduled')}</span></div></div>
            ${e.total_time_minutes > 0 ? `<div class="sj-info-card"><div class="label">Time Logged</div><div class="value">${Math.floor(e.total_time_minutes / 60)}h ${e.total_time_minutes % 60}m</div></div>` : ''}
        </div>
    `;

    // Installation types
    if (e.installation_types && e.installation_types.length) {
        html += `<h6 class="section-title"><i class="bi bi-list-check"></i> Installation Types</h6>
            <div class="sj-checklist">`;
        e.installation_types.forEach(t => {
            html += `<div class="sj-checklist-item"><i class="bi bi-check-circle-fill text-success"></i> ${escHtml(t)}</div>`;
        });
        html += `</div>`;
    }

    // Description
    if (e.description) {
        html += `<h6 class="section-title"><i class="bi bi-journal-text"></i> Description</h6>
            <div class="sj-checklist"><p class="mb-0" style="font-size:.85rem; white-space:pre-wrap;">${escHtml(e.description)}</p></div>`;
    }

    // Linked Job info
    if (job) {
        html += `<h6 class="section-title"><i class="bi bi-tools"></i> Linked Job: ${escHtml(job.job_number)}</h6>
            <div class="sj-checklist">
                <div class="sj-checklist-item"><strong>Status:</strong>&nbsp;<span class="badge bg-${job.status === 'completed' ? 'success' : job.status === 'in_progress' ? 'primary' : 'warning'}">${escHtml(job.status?.replace('_',' '))}</span></div>
                ${job.notes ? `<div class="sj-checklist-item" style="flex-direction:column; align-items:flex-start;"><strong>Notes:</strong><p class="mb-0 small text-muted mt-1">${escHtml(job.notes)}</p></div>` : ''}
            </div>`;

        if (job.items && job.items.length) {
            html += `<h6 class="section-title"><i class="bi bi-card-checklist"></i> Checklist</h6>
                <div class="sj-checklist">`;
            job.items.forEach(item => {
                html += `<div class="sj-checklist-item">
                    <i class="bi bi-${item.is_completed ? 'check-circle-fill text-success' : 'circle text-muted'}"></i>
                    <span style="${item.is_completed ? 'text-decoration:line-through; opacity:.6;' : ''}">${escHtml(item.description)}</span>
                </div>`;
            });
            html += `</div>`;
        }
    }

    body.innerHTML = html;
}

function escHtml(str) {
    if (!str) return '';
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}

function clockIn(eventId) {
    fetch(`/installer/service-jobs/${eventId}/clock-in`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) loadEvent(eventId);
        else alert(data.error || 'Failed to clock in.');
    })
    .catch(() => alert('Failed to clock in.'));
}

function clockOut(eventId) {
    fetch(`/installer/service-jobs/${eventId}/clock-out`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            loadEvent(eventId);
        }
        else alert(data.error || 'Failed to clock out.');
    })
    .catch(() => alert('Failed to clock out.'));
}

function completeService(eventId) {
    if (!confirm('Mark this service as complete? Any active timers will be stopped.')) return;
    fetch(`/installer/service-jobs/${eventId}/complete`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) loadEvent(eventId);
        else alert(data.error || 'Failed to complete.');
    })
    .catch(() => alert('Failed to complete.'));
}

// Elapsed timer for active clock-in
let elapsedInterval = null;
function startElapsedTimer(startDate) {
    if (elapsedInterval) clearInterval(elapsedInterval);
    function update() {
        const now = new Date();
        const diff = Math.floor((now - startDate) / 1000);
        const hrs = Math.floor(diff / 3600);
        const mins = Math.floor((diff % 3600) / 60);
        const secs = diff % 60;
        const el = document.getElementById('elapsedTime');
        if (el) el.textContent = `${hrs}h ${String(mins).padStart(2,'0')}m ${String(secs).padStart(2,'0')}s`;
    }
    update();
    elapsedInterval = setInterval(update, 1000);
}

// Auto-open first card
document.addEventListener('DOMContentLoaded', () => {
    const first = document.querySelector('.sj-card');
    if (first) loadEvent(parseInt(first.dataset.id), first);
});
</script>
@endpush
