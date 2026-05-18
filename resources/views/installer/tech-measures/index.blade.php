@extends('layouts.installer')
@section('title', 'Tech Measures')

@push('styles')
<style>
    .tm-container { display: flex; height: calc(100vh - 56px); overflow: hidden; }

    /* ── Left Rail ── */
    .tm-rail {
        width: 320px; min-width: 320px;
        background: var(--vip-primary); color: #fff;
        display: flex; flex-direction: column;
        border-right: 1px solid rgba(255,255,255,.06);
    }
    .tm-rail-header { padding: 1.25rem 1rem .75rem; }
    .tm-rail-header h6 { font-size: .75rem; text-transform: uppercase; letter-spacing: 1.2px; color: rgba(255,255,255,.5); margin-bottom: .75rem; }
    .tm-rail-search { display: flex; gap: .5rem; }
    .tm-rail-search input {
        flex: 1; background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.12);
        color: #fff; border-radius: .375rem; padding: .4rem .75rem; font-size: .85rem;
    }
    .tm-rail-search input::placeholder { color: rgba(255,255,255,.4); }
    .tm-rail-search input:focus { outline: none; border-color: var(--vip-accent); }

    .tm-rail-tabs { display: flex; gap: 0; padding: 0 1rem; margin-top: .75rem; flex-wrap: wrap; }
    .tm-rail-tabs .tab-btn {
        flex: 1; text-align: center; padding: .4rem .25rem; font-size: .7rem;
        background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.1);
        color: rgba(255,255,255,.6); cursor: pointer; transition: all .15s;
    }
    .tm-rail-tabs .tab-btn:first-child { border-radius: .3rem 0 0 .3rem; }
    .tm-rail-tabs .tab-btn:last-child { border-radius: 0 .3rem .3rem 0; }
    .tm-rail-tabs .tab-btn.active { background: var(--vip-accent); color: #fff; border-color: var(--vip-accent); }

    .tm-rail-list { flex: 1; overflow-y: auto; padding: .5rem; }
    .tm-card {
        background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.08);
        border-radius: .5rem; padding: .75rem 1rem; margin-bottom: .5rem;
        cursor: pointer; transition: all .15s;
    }
    .tm-card:hover { background: rgba(255,255,255,.08); border-color: rgba(201,168,76,.3); }
    .tm-card.active { background: rgba(201,168,76,.12); border-color: var(--vip-accent); }
    .tm-card .tm-name { font-weight: 600; font-size: .9rem; color: #fff; }
    .tm-card .tm-addr { font-size: .78rem; color: rgba(255,255,255,.55); margin-top: 2px; }
    .tm-card .tm-meta { display: flex; justify-content: space-between; align-items: center; margin-top: .35rem; }
    .tm-card .tm-date { font-size: .7rem; color: rgba(255,255,255,.4); }
    .tm-card .tm-badge { font-size: .6rem; padding: 2px 6px; border-radius: 3px; font-weight: 600; text-transform: uppercase; }
    .tm-badge-pending { background: rgba(255,193,7,.25); color: #ffc107; }
    .tm-badge-in_progress { background: rgba(0,123,255,.25); color: #5ba8ff; }
    .tm-badge-completed { background: rgba(40,167,69,.25); color: #7ddf9b; }
    .tm-badge-converted { background: rgba(108,117,125,.25); color: #adb5bd; }

    .tm-rail-footer {
        padding: .75rem 1rem; border-top: 1px solid rgba(255,255,255,.08);
        font-size: .75rem; color: rgba(255,255,255,.4);
    }

    /* ── Main Panel ── */
    .tm-main { flex: 1; overflow-y: auto; background: var(--vip-light); }
    .tm-main-toolbar {
        background: #fff; border-bottom: 1px solid rgba(0,0,0,.06);
        padding: .75rem 1.5rem; display: flex; align-items: center; justify-content: space-between;
    }
    .tm-main-toolbar h5 { font-size: 1rem; font-weight: 700; margin: 0; }
    .tm-detail-body { padding: 1.5rem; }

    .tm-empty-state {
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        height: 60vh; color: rgba(0,0,0,.35);
    }
    .tm-empty-state i { font-size: 3rem; margin-bottom: 1rem; }

    .tm-info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
    .tm-info-card { background: #fff; border-radius: .5rem; padding: 1rem; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
    .tm-info-card .label { font-size: .7rem; text-transform: uppercase; letter-spacing: .5px; color: rgba(0,0,0,.45); margin-bottom: .25rem; }
    .tm-info-card .value { font-size: .9rem; font-weight: 600; color: #111; }

    /* Measurement items table */
    .tm-items-tbl { width: 100%; border-collapse: collapse; background: #fff; border-radius: .5rem; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
    .tm-items-tbl th { font-size: .68rem; text-transform: uppercase; letter-spacing: .5px; color: rgba(0,0,0,.4); padding: .5rem .6rem; border-bottom: 1px solid rgba(0,0,0,.08); background: #fafafa; }
    .tm-items-tbl td { padding: .45rem .6rem; font-size: .8rem; border-bottom: 1px solid rgba(0,0,0,.04); vertical-align: top; }
    .tm-items-tbl .item-photos { display: flex; gap: 4px; flex-wrap: wrap; margin-top: 4px; }
    .tm-items-tbl .item-photo { width: 40px; height: 40px; border-radius: 4px; object-fit: cover; cursor: pointer; }

    /* Add item form */
    .tm-add-form { background: #fff; border-radius: .5rem; padding: 1rem; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
    .tm-add-form label { font-size: .65rem; color: #999; text-transform: uppercase; letter-spacing: .5px; }

    /* Photo gallery */
    .tm-photo-grid { display: flex; flex-wrap: wrap; gap: .5rem; }
    .tm-photo-card { position: relative; width: 120px; height: 120px; border-radius: .5rem; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.1); }
    .tm-photo-card img { width: 100%; height: 100%; object-fit: cover; }
    .tm-photo-card .photo-del {
        position: absolute; top: 4px; right: 4px;
        background: rgba(220,53,69,.9); color: #fff; border: none;
        width: 20px; height: 20px; border-radius: 50%; font-size: .6rem;
        display: flex; align-items: center; justify-content: center; cursor: pointer;
    }
    .tm-photo-card .photo-caption { position: absolute; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,.6); color: #fff; font-size: .6rem; padding: 2px 6px; }

    .section-title {
        font-size: .75rem; text-transform: uppercase; letter-spacing: .5px;
        color: rgba(0,0,0,.5); margin-bottom: .5rem; margin-top: 1.5rem;
    }
    .section-title i { margin-right: .25rem; }

    @media (max-width: 991.98px) {
        .tm-container { flex-direction: column; height: auto; }
        .tm-rail { width: 100%; min-width: 100%; max-height: 45vh; }
    }
</style>
@endpush

@section('content')
<div class="tm-container">
    {{-- Left Rail --}}
    <div class="tm-rail">
        <div class="tm-rail-header">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Tech Measures</h6>
            </div>
            <div class="tm-rail-search">
                <input type="text" id="tmSearch" placeholder="Search measures...">
            </div>
            <div class="tm-rail-tabs">
                <div class="tab-btn {{ $status === 'all' ? 'active' : '' }}" data-status="all">All</div>
                <div class="tab-btn {{ $status === 'pending' ? 'active' : '' }}" data-status="pending">Pending</div>
                <div class="tab-btn {{ $status === 'in_progress' ? 'active' : '' }}" data-status="in_progress">Active</div>
                <div class="tab-btn {{ $status === 'completed' ? 'active' : '' }}" data-status="completed">Done</div>
            </div>
        </div>

        <div class="tm-rail-list">
            @forelse($measures as $m)
                <div class="tm-card" data-id="{{ $m->id }}" data-search="{{ strtolower(($m->customer_name ?? '') . ' ' . ($m->address ?? '')) }}">
                    <div class="tm-name"><i class="bi bi-rulers me-1"></i>{{ $m->customer_name ?: 'No customer' }}</div>
                    <div class="tm-addr"><i class="bi bi-geo-alt me-1"></i>{{ Str::limit($m->address ?: 'No address', 35) }}</div>
                    <div class="tm-meta">
                        <span class="tm-date">{{ $m->created_at->format('M d, Y') }}</span>
                        <span class="tm-badge tm-badge-{{ $m->status }}">{{ ucfirst(str_replace('_', ' ', $m->status)) }}</span>
                    </div>
                </div>
            @empty
                <div class="text-center py-4" style="color:rgba(255,255,255,.4);">
                    <i class="bi bi-rulers" style="font-size:2rem;"></i>
                    <p class="mt-2 mb-0">No tech measures assigned</p>
                </div>
            @endforelse
        </div>

        <div class="tm-rail-footer">
            <span>{{ $measures->total() }} measure{{ $measures->total() !== 1 ? 's' : '' }}</span>
        </div>
    </div>

    {{-- Main Panel --}}
    <div class="tm-main">
        <div class="tm-main-toolbar">
            <h5 id="tmDetailTitle">Tech Measure Details</h5>
            <div id="tmToolbarActions"></div>
        </div>
        <div class="tm-detail-body" id="tmDetailBody">
            <div class="tm-empty-state">
                <i class="bi bi-rulers"></i>
                <p>Select a tech measure to view details</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const csrf = document.querySelector('meta[name=csrf-token]').content;
let currentMeasureId = null;
let currentMeasureData = null;
const seriesData = @json($seriesList);

document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.tm-card');

    // Tab filters
    document.querySelectorAll('.tm-rail-tabs .tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const status = this.dataset.status;
            const url = new URL(window.location);
            if (status !== 'all') url.searchParams.set('status', status);
            else url.searchParams.delete('status');
            window.location = url;
        });
    });

    // Search
    document.getElementById('tmSearch').addEventListener('input', function() {
        const term = this.value.toLowerCase();
        document.querySelectorAll('.tm-card').forEach(card => {
            card.style.display = (!term || card.dataset.search.includes(term)) ? '' : 'none';
        });
    });

    // Card click
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

    fetch(`/installer/tech-measures/${id}`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        currentMeasureData = data;
        renderMeasureDetail(data);
    })
    .catch(() => {
        body.innerHTML = '<div class="alert alert-danger m-4">Failed to load measure.</div>';
    });
}

function renderMeasureDetail(data) {
    const m = data.measure;
    const items = data.items || [];
    const photos = data.photos || [];
    const body = document.getElementById('tmDetailBody');
    const title = document.getElementById('tmDetailTitle');
    const toolbar = document.getElementById('tmToolbarActions');

    title.textContent = m.customer_name || 'Tech Measure';

    // Toolbar actions
    let actions = '';
    if (m.status === 'pending') {
        actions += `<button class="btn btn-sm btn-info text-white" onclick="startMeasure(${m.id})"><i class="bi bi-play-circle me-1"></i>Start Job</button> `;
    } else if (m.status === 'in_progress') {
        actions += `<button class="btn btn-sm btn-success" onclick="completeMeasure(${m.id})"><i class="bi bi-check-lg me-1"></i>Complete</button> `;
    }
    toolbar.innerHTML = actions;

    // Series options for add form
    let seriesOpts = '<option value="">— Select Series —</option>' + seriesData.map(s => `<option value="${s.id}">${s.series}</option>`).join('');

    // Items table
    let itemsHtml = '';
    if (items.length) {
        itemsHtml = `<table class="tm-items-tbl">
            <thead><tr>
                <th style="width:30px;">#</th>
                <th>Room / Location</th>
                <th>Type</th>
                <th>Series / Config</th>
                <th>W × H</th>
                <th>Qty</th>
                <th>Frame</th>
                <th>Glass</th>
                <th>Grid</th>
                <th>Notes</th>
                <th style="width:60px;"></th>
            </tr></thead>
            <tbody>`;
        items.forEach((item, idx) => {
            const photoHtml = item.photos?.length
                ? `<div class="item-photos">${item.photos.map(p => `<img src="${p.url}" class="item-photo" onclick="window.open('${p.url}','_blank')" title="${p.caption || ''}">`).join('')}</div>`
                : '';
            itemsHtml += `<tr>
                <td class="text-center text-muted">${idx + 1}</td>
                <td><strong>${item.room_label || '—'}</strong><br><span class="text-muted" style="font-size:.7rem;">${item.existing_condition || ''}</span>${photoHtml}</td>
                <td>${item.opening_type || '—'}</td>
                <td>${item.description || '—'}<br><span class="text-muted" style="font-size:.7rem;">${item.series_type || ''}</span></td>
                <td class="text-nowrap">${item.width || '—'} × ${item.height || '—'}</td>
                <td class="text-center">${item.qty || 1}</td>
                <td>${item.frame_type || '—'}</td>
                <td>${item.glass_type || '—'}${item.tempered ? ' <span class="badge bg-warning text-dark" style="font-size:.55rem;">T</span>' : ''}</td>
                <td>${item.grid_pattern || '—'}</td>
                <td style="font-size:.72rem;">${item.notes || ''}</td>
                <td class="text-center">
                    <button class="btn btn-sm text-primary p-0 me-1" onclick="uploadItemPhoto(${m.id}, ${item.id})" title="Add Photo"><i class="bi bi-camera" style="font-size:.75rem;"></i></button>
                    <button class="btn btn-sm text-danger p-0" onclick="removeItem(${m.id}, ${item.id})" title="Remove"><i class="bi bi-x-lg" style="font-size:.65rem;"></i></button>
                </td>
            </tr>`;
        });
        itemsHtml += '</tbody></table>';
    } else {
        itemsHtml = '<p class="text-muted small">No measurements added yet. Use the form below to add openings.</p>';
    }

    // Photo gallery (general photos)
    let photosHtml = '';
    if (photos.length) {
        photosHtml = '<div class="tm-photo-grid">';
        photos.forEach(p => {
            photosHtml += `<div class="tm-photo-card">
                <img src="${p.url}" onclick="window.open('${p.url}','_blank')">
                <button class="photo-del" onclick="deletePhoto(${m.id}, ${p.id})"><i class="bi bi-x"></i></button>
                ${p.caption ? `<div class="photo-caption">${p.caption}</div>` : ''}
            </div>`;
        });
        photosHtml += '</div>';
    }

    body.innerHTML = `
        <div class="tm-info-grid">
            <div class="tm-info-card"><div class="label">Customer</div><div class="value">${m.customer_name || '—'}</div></div>
            <div class="tm-info-card"><div class="label">Phone</div><div class="value">${m.customer_phone ? `<a href="tel:${m.customer_phone}">${m.customer_phone}</a>` : '—'}</div></div>
            <div class="tm-info-card"><div class="label">Address</div><div class="value">${m.address || '—'}</div></div>
            <div class="tm-info-card"><div class="label">Status</div><div class="value"><span class="badge bg-${m.status === 'in_progress' ? 'primary' : m.status === 'completed' ? 'success' : m.status === 'converted' ? 'secondary' : 'warning'}">${m.status?.replace('_',' ')}</span></div></div>
            ${m.started_at ? `<div class="tm-info-card"><div class="label">Started</div><div class="value">${new Date(m.started_at).toLocaleString()}</div></div>` : ''}
            <div class="tm-info-card"><div class="label">Items</div><div class="value">${items.length} opening${items.length !== 1 ? 's' : ''}</div></div>
        </div>

        <h6 class="section-title"><i class="bi bi-rulers"></i> Measurements (${items.length})</h6>
        ${itemsHtml}

        ${m.status === 'in_progress' || m.status === 'pending' ? `
        <h6 class="section-title"><i class="bi bi-plus-circle"></i> Add Opening</h6>
        <div class="tm-add-form">
            <div class="row g-2">
                <div class="col-md-2">
                    <label>Room / Location</label>
                    <input type="text" id="addRoom" class="form-control form-control-sm" placeholder="e.g. Living Room">
                </div>
                <div class="col-md-2">
                    <label>Opening Type</label>
                    <select id="addOpeningType" class="form-select form-select-sm">
                        <option value="window">Window</option>
                        <option value="door">Door</option>
                        <option value="sliding_door">Sliding Door</option>
                        <option value="picture">Picture Window</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Series</label>
                    <select id="addSeries" class="form-select form-select-sm" onchange="loadConfigs(this.value)">${seriesOpts}</select>
                </div>
                <div class="col-md-2">
                    <label>Configuration</label>
                    <select id="addConfig" class="form-select form-select-sm">
                        <option value="">— Select —</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <label>Width</label>
                    <input type="text" id="addWidth" class="form-control form-control-sm" placeholder='36 1/2'>
                </div>
                <div class="col-md-1">
                    <label>Height</label>
                    <input type="text" id="addHeight" class="form-control form-control-sm" placeholder='60 3/8'>
                </div>
            </div>
            <div class="row g-2 mt-1">
                <div class="col-md-2">
                    <label>Frame</label>
                    <select id="addFrame" class="form-select form-select-sm">
                        <option value="">— Select —</option>
                        <option value='Retrofit 1 3/4"'>Retrofit 1 3/4"</option>
                        <option value='Retrofit 2 1/2"'>Retrofit 2 1/2"</option>
                        <option value="Block">Block</option>
                        <option value='Nailon 1" Setback'>Nailon 1" Setback</option>
                        <option value='Nailon 1 3/8" Setback'>Nailon 1 3/8" Setback</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Glass</label>
                    <select id="addGlass" class="form-select form-select-sm">
                        <option value="">— Select —</option>
                        <option value="LE3/CLR">LE3/CLR</option>
                        <option value="CLR/CLR">CLR/CLR</option>
                        <option value="LE3/LAM">LE3/LAM</option>
                        <option value="CLR/LAM">CLR/LAM</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Tempered</label>
                    <select id="addTempered" class="form-select form-select-sm">
                        <option value="">None</option>
                        <option value="All">All</option>
                        <option value="Select">Select</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Grid Pattern</label>
                    <select id="addGridPattern" class="form-select form-select-sm">
                        <option value="">None</option>
                        <option value="Colonial">Colonial</option>
                        <option value="Marginal-12">Marginal-12</option>
                        <option value="Marginal-18">Marginal-18</option>
                        <option value="Queen">Queen</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <label>Qty</label>
                    <input type="number" id="addQty" class="form-control form-control-sm" value="1" min="1">
                </div>
                <div class="col-md-3">
                    <label>Existing Condition</label>
                    <input type="text" id="addCondition" class="form-control form-control-sm" placeholder="e.g. Aluminum single pane, rotted">
                </div>
            </div>
            <div class="row g-2 mt-1">
                <div class="col-md-9">
                    <label>Notes</label>
                    <input type="text" id="addNotes" class="form-control form-control-sm" placeholder="Any notes about this opening...">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button class="btn btn-sm btn-vip w-100" onclick="addItem(${m.id})"><i class="bi bi-plus-lg me-1"></i>Add Opening</button>
                </div>
            </div>
        </div>
        ` : ''}

        <h6 class="section-title"><i class="bi bi-image"></i> Site Photos</h6>
        ${photosHtml}
        ${m.status !== 'converted' ? `
        <form id="generalPhotoForm" class="mt-2" onsubmit="uploadGeneralPhoto(event, ${m.id})">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <input type="file" name="photo" accept="image/*" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-4">
                    <input type="text" name="caption" class="form-control form-control-sm" placeholder="Caption (optional)">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-sm btn-vip" type="submit"><i class="bi bi-upload me-1"></i>Upload</button>
                </div>
            </div>
        </form>
        ` : ''}

        <h6 class="section-title"><i class="bi bi-journal-text"></i> General Notes</h6>
        <div class="tm-add-form">
            <textarea id="generalNotes" class="form-control form-control-sm" rows="3" placeholder="General notes about this tech measure..." ${m.status === 'converted' ? 'disabled' : ''}>${m.notes || ''}</textarea>
            ${m.status !== 'converted' ? `<button class="btn btn-sm btn-vip mt-2" onclick="saveNotes(${m.id})"><i class="bi bi-check me-1"></i>Save Notes</button>` : ''}
        </div>
    `;
}

function loadConfigs(seriesId) {
    const configSelect = document.getElementById('addConfig');
    configSelect.innerHTML = '<option value="">Loading...</option>';
    if (!seriesId) { configSelect.innerHTML = '<option value="">— Select —</option>'; return; }

    fetch(`/installer/quotes/series-types/${seriesId}`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(types => {
        let html = '<option value="">— Select —</option>';
        types.forEach(t => { html += `<option value="${t.series_type}">${t.series_type}</option>`; });
        configSelect.innerHTML = html;
    })
    .catch(() => { configSelect.innerHTML = '<option value="">— Error —</option>'; });
}

function addItem(measureId) {
    const room = document.getElementById('addRoom')?.value?.trim();
    const openingType = document.getElementById('addOpeningType')?.value;
    const seriesId = document.getElementById('addSeries')?.value || null;
    const seriesType = document.getElementById('addConfig')?.value || null;
    const width = document.getElementById('addWidth')?.value?.trim();
    const height = document.getElementById('addHeight')?.value?.trim();
    const frame = document.getElementById('addFrame')?.value;
    const glass = document.getElementById('addGlass')?.value;
    const tempered = document.getElementById('addTempered')?.value;
    const gridPattern = document.getElementById('addGridPattern')?.value;
    const qty = parseInt(document.getElementById('addQty')?.value || 1);
    const condition = document.getElementById('addCondition')?.value?.trim();
    const notes = document.getElementById('addNotes')?.value?.trim();

    // Build description from series + config
    const seriesEl = document.getElementById('addSeries');
    const seriesName = seriesEl?.options[seriesEl.selectedIndex]?.text || '';
    const desc = [seriesName, seriesType].filter(Boolean).join(' — ') || openingType;

    fetch(`/installer/tech-measures/${measureId}/item`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        body: JSON.stringify({
            room_label: room,
            opening_type: openingType,
            description: desc,
            series_id: seriesId,
            series_type: seriesType,
            width: parseFloat(width) || null,
            height: parseFloat(height) || null,
            qty: qty,
            frame_type: frame,
            glass_type: glass,
            tempered: tempered,
            grid_pattern: gridPattern,
            existing_condition: condition,
            notes: notes,
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) loadMeasure(measureId);
        else alert(data.error || 'Failed to add item.');
    })
    .catch(() => alert('Failed to add item.'));
}

function removeItem(measureId, itemId) {
    if (!confirm('Remove this measurement?')) return;
    fetch(`/installer/tech-measures/${measureId}/item/${itemId}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => { if (data.success) loadMeasure(measureId); })
    .catch(() => alert('Failed to remove.'));
}

function startMeasure(measureId) {
    fetch(`/installer/tech-measures/${measureId}/start`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => { if (data.success) loadMeasure(measureId); })
    .catch(() => alert('Failed to start.'));
}

function completeMeasure(measureId) {
    if (!confirm('Mark this tech measure as complete?')) return;
    fetch(`/installer/tech-measures/${measureId}/complete`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => { if (data.success) loadMeasure(measureId); })
    .catch(() => alert('Failed to complete.'));
}

function uploadItemPhoto(measureId, itemId) {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/*';
    input.onchange = function() {
        const formData = new FormData();
        formData.append('photo', this.files[0]);
        formData.append('item_id', itemId);

        fetch(`/installer/tech-measures/${measureId}/photo`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: formData
        })
        .then(r => r.json())
        .then(data => { if (data.success) loadMeasure(measureId); })
        .catch(() => alert('Failed to upload photo.'));
    };
    input.click();
}

function uploadGeneralPhoto(e, measureId) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);

    fetch(`/installer/tech-measures/${measureId}/photo`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) { form.reset(); loadMeasure(measureId); }
    })
    .catch(() => alert('Failed to upload photo.'));
}

function deletePhoto(measureId, photoId) {
    if (!confirm('Delete this photo?')) return;
    fetch(`/installer/tech-measures/${measureId}/photo/${photoId}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => { if (data.success) loadMeasure(measureId); })
    .catch(() => alert('Failed to delete photo.'));
}

function saveNotes(measureId) {
    const notes = document.getElementById('generalNotes')?.value;
    fetch(`/installer/tech-measures/${measureId}/notes`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        body: JSON.stringify({ notes })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) alert('Notes saved.');
    })
    .catch(() => alert('Failed to save notes.'));
}
</script>
@endpush
