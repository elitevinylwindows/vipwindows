@extends('layouts.app')
@section('title', 'Master Data')

@push('styles')
<style>
    /* ── Master Data Shell ─────────────────────────────── */
    .mh-wrapper {
        display: flex;
        height: calc(100vh - 120px);
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        overflow: hidden;
    }

    /* ── Left Rail ─────────────────────────────────────── */
    .mh-sidebar {
        width: 250px;
        min-width: 250px;
        background: var(--vip-primary);
        color: #fff;
        display: flex;
        flex-direction: column;
        border-right: 1px solid rgba(255,255,255,.08);
    }
    .mh-sidebar-header {
        padding: 16px 16px 12px;
        border-bottom: 1px solid rgba(255,255,255,.1);
        background: rgba(0,0,0,.15);
    }
    .mh-sidebar-header h6 {
        margin: 0;
        font-size: .85rem;
        font-weight: 700;
        letter-spacing: .5px;
        color: var(--vip-accent);
    }

    /* Search */
    .mh-search {
        padding: 10px 12px;
        border-bottom: 1px solid rgba(255,255,255,.08);
    }
    .mh-search input {
        width: 100%;
        background: rgba(255,255,255,.1);
        border: 1px solid rgba(255,255,255,.15);
        border-radius: 6px;
        padding: 6px 10px;
        color: #fff;
        font-size: .8rem;
    }
    .mh-search input::placeholder { color: rgba(255,255,255,.4); }
    .mh-search input:focus {
        outline: none;
        border-color: var(--vip-accent);
        background: rgba(255,255,255,.15);
    }

    /* Sidebar body */
    .mh-sidebar-body {
        flex: 1;
        overflow-y: auto;
        padding: 6px 0;
    }
    .mh-sidebar-body::-webkit-scrollbar { width: 4px; }
    .mh-sidebar-body::-webkit-scrollbar-thumb { background: rgba(255,255,255,.2); border-radius: 4px; }

    /* Groups */
    .mh-group { margin-bottom: 2px; }
    .mh-group-hdr {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        font-size: .7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.2px;
        color: rgba(255,255,255,.45);
        cursor: pointer;
        user-select: none;
    }
    .mh-group-hdr:hover { color: rgba(255,255,255,.7); }
    .mh-group-hdr .chev {
        font-size: .55rem;
        transition: transform .2s;
    }
    .mh-group.collapsed .chev { transform: rotate(-90deg); }
    .mh-group.collapsed .mh-group-items { display: none; }
    .mh-group-hdr .count {
        margin-left: auto;
        font-size: .6rem;
        opacity: .5;
    }

    /* Nav items */
    .mh-nav-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 7px 14px 7px 22px;
        color: rgba(255,255,255,.7);
        text-decoration: none;
        font-size: .82rem;
        cursor: pointer;
        border-left: 3px solid transparent;
        transition: all .15s;
    }
    .mh-nav-item:hover {
        background: rgba(255,255,255,.08);
        color: #fff;
    }
    .mh-nav-item.active {
        background: rgba(201,168,76,.12);
        border-left-color: var(--vip-accent);
        color: var(--vip-accent);
        font-weight: 600;
    }
    .mh-nav-item i { width: 18px; text-align: center; font-size: .9rem; }

    /* ── Main Panel ────────────────────────────────────── */
    .mh-main {
        flex: 1;
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    /* Toolbar */
    .mh-toolbar {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 20px;
        border-bottom: 1px solid #e9ecef;
        background: #fafafa;
        min-height: 52px;
    }
    .mh-toolbar #mhTitle {
        font-size: 1rem;
        font-weight: 700;
        color: var(--vip-primary);
        margin: 0;
    }
    .mh-toolbar #mhSub {
        font-size: .78rem;
        color: #999;
    }
    .mh-toolbar .actions { margin-left: auto; }
    .mh-toolbar .actions .btn { font-size: .78rem; }

    /* Content area */
    .mh-frame-wrap {
        flex: 1;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    /* Overview / placeholder */
    .mh-placeholder {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        text-align: center;
        padding: 40px;
        color: #999;
    }
    .mh-placeholder i { font-size: 3rem; color: #ddd; margin-bottom: 16px; }
    .mh-placeholder h5 { color: #666; margin-bottom: 8px; }

    /* KPI grid */
    .mh-kpi-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 12px;
        width: 100%;
        max-width: 600px;
        margin-bottom: 24px;
    }
    .mh-kpi {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 14px 12px;
        text-align: center;
    }
    .mh-kpi .val { font-size: 1.4rem; font-weight: 700; color: var(--vip-primary); }
    .mh-kpi .lbl { font-size: .7rem; color: #888; text-transform: uppercase; letter-spacing: .5px; margin-top: 2px; }
    .mh-kpi.gold .val { color: var(--vip-accent); }
    .mh-kpi.green .val { color: #28a745; }
    .mh-kpi.blue .val { color: #17a2b8; }

    /* Content pane */
    .mh-content-pane {
        flex: 1;
        overflow-y: auto;
        overflow-x: hidden;
        padding: 16px 20px;
        display: none;
    }

    /* Loader */
    .mh-loader {
        position: absolute;
        inset: 0;
        display: none;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: 10px;
        background: rgba(255,255,255,.9);
        z-index: 10;
    }
    .mh-loader.show { display: flex; }
    .mh-loader .spinner-border { width: 2rem; height: 2rem; color: var(--vip-accent); }

    /* Responsive */
    @media (max-width: 991.98px) {
        .mh-wrapper { flex-direction: column; height: auto; min-height: calc(100vh - 120px); }
        .mh-sidebar { width: 100%; min-width: 100%; max-height: 300px; }
        .mh-content-pane { min-height: 400px; }
    }

    /* Clean up embedded content */
    .mh-content-pane .container-fluid { padding: 0 !important; }
    .mh-content-pane .py-4 { padding-top: 0 !important; }
    .mh-content-pane > .p-4 { padding: 0 !important; }
    .mh-content-pane h4.fw-bold:first-child { display: none; }
</style>
@endpush

@section('content')
<div class="p-3">
    <div class="mh-wrapper">

        {{-- ── Left Rail ──────────────────────────────────── --}}
        <div class="mh-sidebar">
            <div class="mh-sidebar-header">
                <h6><i class="bi bi-database-gear me-1"></i> MASTER DATA</h6>
            </div>
            <div class="mh-search">
                <input type="text" id="mhSearch" placeholder="Filter sections…">
            </div>
            <div class="mh-sidebar-body">

                @php
                $groups = [
                    'Series' => [
                        ['Series', 'bi-list-ul', route('admin.master.series.index'), 'series'],
                        ['Configurations', 'bi-sliders', route('admin.master.series.configurations'), 'configurations'],
                        ['Window Types', 'bi-window', route('admin.master.series.window-types'), 'window-types'],
                        ['Size Limits', 'bi-rulers', route('admin.master.series.size-limits'), 'size-limits'],
                    ],
                    'Colors' => [
                        ['Available Colors', 'bi-paint-bucket', route('admin.master.colors.available'), 'available-colors'],
                        ['Color Configs', 'bi-sliders2', route('admin.master.colors.configurations'), 'color-configs'],
                        ['Exterior Colors', 'bi-brush', route('admin.master.colors.exterior'), 'exterior-colors'],
                        ['Interior Colors', 'bi-house', route('admin.master.colors.interior'), 'interior-colors'],
                        ['Laminate Colors', 'bi-layers', route('admin.master.colors.laminate'), 'laminate-colors'],
                    ],
                    'Glass' => [
                        ['Glass Options', 'bi-diamond', route('admin.master.glass.options'), 'glass-options'],
                        ['Pane Management', 'bi-columns-gap', route('admin.master.glass.panes'), 'pane-mgmt'],
                        ['Tempered / Specialty', 'bi-shield-check', route('admin.master.glass.tempered'), 'tempered'],
                        ['Thickness Manager', 'bi-border-width', route('admin.master.glass.thickness'), 'thickness'],
                    ],
                    'Profiles' => [
                        ['Profile Manager', 'bi-diagram-3', route('admin.master.profiles.index'), 'profile-mgr'],
                        ['Deduction Manager', 'bi-scissors', route('admin.master.deductions.index'), 'deduction-mgr'],
                    ],
                    'Pricing' => [
                        ['Price Matrix', 'bi-table', route('admin.master.prices.matrix'), 'price-matrix'],
                        ['Markup', 'bi-percent', route('admin.master.prices.markup'), 'markup'],
                    ],
                    'Grids' => [
                        ['Grid Types', 'bi-grid', route('admin.master.grids.types'), 'grid-types'],
                        ['Grid Profiles', 'bi-border-all', route('admin.master.grids.profiles'), 'grid-profiles'],
                        ['Grid Patterns', 'bi-grid-3x3-gap', route('admin.master.grids.patterns'), 'grid-patterns'],
                    ],
                    'Frames' => [
                        ['Frame Types', 'bi-aspect-ratio', route('admin.master.frames.index'), 'frame-types'],
                    ],
                ];
                @endphp

                @foreach($groups as $groupName => $items)
                    <div class="mh-group" data-group="{{ $groupName }}">
                        <div class="mh-group-hdr" onclick="mhToggleGroup(this)">
                            <i class="bi bi-chevron-down chev"></i>
                            <span>{{ $groupName }}</span>
                            <span class="count">{{ count($items) }}</span>
                        </div>
                        <div class="mh-group-items">
                            @foreach($items as [$title, $icon, $url, $slug])
                                <a class="mh-nav-item" href="#"
                                   data-url="{{ $url }}"
                                   data-title="{{ $title }}"
                                   data-group="{{ $groupName }}"
                                   data-icon="{{ $icon }}"
                                   data-slug="{{ $slug }}"
                                   onclick="mhOpen(event, this)">
                                    <i class="bi {{ $icon }}"></i> {{ $title }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach

            </div>
        </div>

        {{-- ── Main Panel ─────────────────────────────────── --}}
        <div class="mh-main">
            <div class="mh-toolbar">
                <h6 id="mhTitle"><i class="bi bi-database-gear me-1" style="color:var(--vip-accent)"></i> Overview</h6>
                <span id="mhSub"></span>
                <div class="actions">
                    <button class="btn btn-sm btn-outline-secondary" onclick="mhReloadFrame(event)" id="mhReloadBtn" style="display:none;">
                        <i class="bi bi-arrow-clockwise"></i> Reload
                    </button>
                </div>
            </div>

            <div class="mh-frame-wrap">
                {{-- Overview --}}
                <div class="mh-placeholder" id="mhOverview">
                    <div class="mh-kpi-row">
                        <div class="mh-kpi gold">
                            <div class="val">{{ \Illuminate\Support\Facades\DB::table('elitevw_master_series')->count() }}</div>
                            <div class="lbl">Series</div>
                        </div>
                        <div class="mh-kpi blue">
                            <div class="val">{{ \Illuminate\Support\Facades\DB::table('elitevw_master_series_configurations')->where('is_active', true)->count() }}</div>
                            <div class="lbl">Active Configs</div>
                        </div>
                        <div class="mh-kpi green">
                            <div class="val">{{ \Illuminate\Support\Facades\DB::table('elitevw_master_price_price_matrices')->count() }}</div>
                            <div class="lbl">Price Rows</div>
                        </div>
                        <div class="mh-kpi">
                            <div class="val">{{ \Illuminate\Support\Facades\DB::table('elitevw_profile_sets')->count() }}</div>
                            <div class="lbl">Profiles</div>
                        </div>
                    </div>
                    <i class="bi bi-arrow-left-circle"></i>
                    <h5>Select a section from the sidebar</h5>
                    <p class="small">Manage series, colors, glass, profiles, pricing, grids, and frames for the window configurator.</p>
                </div>

                {{-- Loaded content --}}
                <div class="mh-content-pane" id="mhContent"></div>

                {{-- Loader --}}
                <div class="mh-loader" id="mhLoader">
                    <div class="spinner-border" role="status"></div>
                    <span class="text-muted small">Loading…</span>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentSlug = null;

    function mhToggleGroup(hdr) {
        hdr.closest('.mh-group').classList.toggle('collapsed');
    }

    function mhOpen(e, el, skipPush) {
        e && e.preventDefault();

        // Active state
        document.querySelectorAll('.mh-nav-item.active').forEach(a => a.classList.remove('active'));
        el.classList.add('active');

        const url   = el.dataset.url;
        const title = el.dataset.title;
        const group = el.dataset.group;
        const icon  = el.dataset.icon;
        const slug  = el.dataset.slug;

        currentSlug = slug;

        // Update toolbar
        document.getElementById('mhTitle').innerHTML = `<i class="bi ${icon} me-1" style="color:var(--vip-accent)"></i> ${title}`;
        document.getElementById('mhSub').textContent = `${group} / ${title}`;
        document.getElementById('mhReloadBtn').style.display = '';

        // History
        if (!skipPush) {
            const u = new URL(window.location);
            u.searchParams.set('section', slug);
            history.pushState({ slug }, '', u);
        }

        // Load content
        const overview = document.getElementById('mhOverview');
        const content  = document.getElementById('mhContent');
        const loader   = document.getElementById('mhLoader');

        overview.style.display = 'none';
        content.style.display  = 'none';
        loader.classList.add('show');

        mhFetchInto(content, url).then(() => {
            loader.classList.remove('show');
            content.style.display = '';
        }).catch(err => {
            loader.classList.remove('show');
            content.innerHTML = `<div class="alert alert-danger m-3"><i class="bi bi-exclamation-triangle me-1"></i> Failed to load: ${err.message}</div>`;
            content.style.display = '';
        });
    }

    function mhFetchInto(target, url) {
        const sep = url.includes('?') ? '&' : '?';
        return fetch(url + sep + 'embed=1', { credentials: 'same-origin' })
            .then(r => { if (!r.ok) throw new Error(r.status); return r.text(); })
            .then(html => {
                const doc = new DOMParser().parseFromString(html, 'text/html');

                // Extract content — try known wrappers
                let content = doc.querySelector('.page-wrapper .container-fluid')
                           || doc.querySelector('.page-wrapper .p-4')
                           || doc.querySelector('.page-wrapper')
                           || doc.querySelector('main')
                           || doc.body;

                // Strip app chrome
                content.querySelectorAll('.admin-sidebar, .admin-topbar, footer, .sidebar-overlay, nav.navbar').forEach(el => el.remove());

                target.innerHTML = content.innerHTML;

                // Import stylesheets
                doc.querySelectorAll('link[rel="stylesheet"]').forEach(link => {
                    if (!document.querySelector(`link[href="${link.href}"]`)) {
                        document.head.appendChild(link.cloneNode());
                    }
                });

                // Re-execute scripts
                target.querySelectorAll('script').forEach(old => {
                    const s = document.createElement('script');
                    if (old.src) { s.src = old.src; } else { s.textContent = old.textContent; }
                    old.replaceWith(s);
                });

                // Intercept forms
                target.querySelectorAll('form').forEach(form => {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        const method = (form.querySelector('input[name="_method"]')?.value || form.method).toUpperCase();
                        const action = form.action;
                        const fd     = new FormData(form);

                        if (method === 'GET') {
                            const qs = new URLSearchParams(fd).toString();
                            mhFetchInto(target, action + '?' + qs);
                            return;
                        }

                        fetch(action, {
                            method: 'POST',
                            body: fd,
                            credentials: 'same-origin',
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        }).then(r => {
                            if (r.redirected) {
                                mhFetchInto(target, r.url);
                            } else {
                                return r.text().then(h => {
                                    const d = new DOMParser().parseFromString(h, 'text/html');
                                    let c = d.querySelector('.page-wrapper .container-fluid')
                                         || d.querySelector('.page-wrapper .p-4')
                                         || d.querySelector('.page-wrapper')
                                         || d.body;
                                    c.querySelectorAll('.admin-sidebar, .admin-topbar, footer, .sidebar-overlay').forEach(el => el.remove());
                                    target.innerHTML = c.innerHTML;
                                    target.querySelectorAll('script').forEach(old => {
                                        const s = document.createElement('script');
                                        if (old.src) s.src = old.src; else s.textContent = old.textContent;
                                        old.replaceWith(s);
                                    });
                                });
                            }
                        });
                    });
                });
            });
    }

    function mhReloadFrame(e) {
        e && e.preventDefault();
        const active = document.querySelector('.mh-nav-item.active');
        if (active) mhOpen(null, active, true);
    }

    function mhShowOverview() {
        document.getElementById('mhOverview').style.display = '';
        document.getElementById('mhContent').style.display  = 'none';
        document.getElementById('mhReloadBtn').style.display = 'none';
        document.getElementById('mhTitle').innerHTML = '<i class="bi bi-database-gear me-1" style="color:var(--vip-accent)"></i> Overview';
        document.getElementById('mhSub').textContent = '';
        document.querySelectorAll('.mh-nav-item.active').forEach(a => a.classList.remove('active'));
        currentSlug = null;
    }

    // ── Search filter ──────────────────────────────────
    document.getElementById('mhSearch').addEventListener('input', function() {
        const q = this.value.toLowerCase().trim();
        document.querySelectorAll('.mh-group').forEach(grp => {
            let anyMatch = false;
            grp.querySelectorAll('.mh-nav-item').forEach(item => {
                const match = !q || item.textContent.toLowerCase().includes(q);
                item.style.display = match ? '' : 'none';
                if (match) anyMatch = true;
            });
            grp.style.display = anyMatch ? '' : 'none';
            if (q && anyMatch) grp.classList.remove('collapsed');
        });
    });

    // ── Auto-open from URL ─────────────────────────────
    document.addEventListener('DOMContentLoaded', () => {
        const params = new URLSearchParams(window.location.search);
        const section = params.get('section');
        if (section) {
            const item = document.querySelector(`.mh-nav-item[data-slug="${section}"]`);
            if (item) {
                // Expand parent group
                const grp = item.closest('.mh-group');
                if (grp && grp.classList.contains('collapsed')) grp.classList.remove('collapsed');
                mhOpen(null, item, true);
            }
        }
    });

    // ── Browser back/forward ───────────────────────────
    window.addEventListener('popstate', function(e) {
        if (e.state && e.state.slug) {
            const item = document.querySelector(`.mh-nav-item[data-slug="${e.state.slug}"]`);
            if (item) {
                const grp = item.closest('.mh-group');
                if (grp && grp.classList.contains('collapsed')) grp.classList.remove('collapsed');
                mhOpen(null, item, true);
            }
        } else {
            mhShowOverview();
        }
    });
</script>
@endpush
