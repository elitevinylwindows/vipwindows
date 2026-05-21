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

{{-- Edit Tech Measure Modal --}}
<div class="modal fade" id="editTmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="background:var(--vip-primary); color:#fff; border:1px solid rgba(255,255,255,.1);">
            <div class="modal-header border-0 py-2">
                <h6 class="modal-title mb-0"><i class="bi bi-pencil me-1"></i>Edit Tech Measure</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-2">
                <div class="row g-2">
                    <div class="col-4">
                        <label class="form-label mb-0" style="font-size:.68rem;color:rgba(255,255,255,.4);">Customer *</label>
                        <input type="text" id="editTmCustName" class="form-control form-control-sm bg-dark text-white border-secondary" required>
                    </div>
                    <div class="col-4">
                        <label class="form-label mb-0" style="font-size:.68rem;color:rgba(255,255,255,.4);">Email</label>
                        <input type="email" id="editTmCustEmail" class="form-control form-control-sm bg-dark text-white border-secondary">
                    </div>
                    <div class="col-4">
                        <label class="form-label mb-0" style="font-size:.68rem;color:rgba(255,255,255,.4);">Phone</label>
                        <input type="text" id="editTmCustPhone" class="form-control form-control-sm bg-dark text-white border-secondary">
                    </div>
                    <div class="col-12 mt-1"><hr class="border-secondary my-1"></div>
                    <div class="col-6">
                        <label class="form-label mb-0" style="font-size:.68rem;color:rgba(255,255,255,.4);">Address</label>
                        <input type="text" id="editTmAddress" class="form-control form-control-sm bg-dark text-white border-secondary">
                    </div>
                    <div class="col-3">
                        <label class="form-label mb-0" style="font-size:.68rem;color:rgba(255,255,255,.4);">City</label>
                        <input type="text" id="editTmCity" class="form-control form-control-sm bg-dark text-white border-secondary">
                    </div>
                    <div class="col-1">
                        <label class="form-label mb-0" style="font-size:.68rem;color:rgba(255,255,255,.4);">State</label>
                        <input type="text" id="editTmState" class="form-control form-control-sm bg-dark text-white border-secondary">
                    </div>
                    <div class="col-2">
                        <label class="form-label mb-0" style="font-size:.68rem;color:rgba(255,255,255,.4);">Zip</label>
                        <input type="text" id="editTmZip" class="form-control form-control-sm bg-dark text-white border-secondary">
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 py-2">
                <button type="button" class="btn btn-outline-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-vip btn-sm" onclick="saveEditTm()"><i class="bi bi-check-lg me-1"></i>Save Changes</button>
            </div>
        </div>
    </div>
</div>

{{-- Edit Opening Item Modal --}}
<div class="modal fade" id="editItemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="background:var(--vip-primary); color:#fff; border:1px solid rgba(255,255,255,.1);">
            <div class="modal-header border-0 py-2">
                <h6 class="modal-title mb-0"><i class="bi bi-pencil me-1"></i>Edit Opening</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-2">
                <input type="hidden" id="editItemMeasureId">
                <input type="hidden" id="editItemId">
                <div class="row g-2">
                    <div class="col-2">
                        <label class="form-label mb-0" style="font-size:.68rem;color:rgba(255,255,255,.4);">Qty</label>
                        <input type="number" id="editItemQty" class="form-control form-control-sm bg-dark text-white border-secondary" min="1">
                    </div>
                    <div class="col-3">
                        <label class="form-label mb-0" style="font-size:.68rem;color:rgba(255,255,255,.4);">Width</label>
                        <input type="text" id="editItemWidth" class="form-control form-control-sm bg-dark text-white border-secondary">
                    </div>
                    <div class="col-3">
                        <label class="form-label mb-0" style="font-size:.68rem;color:rgba(255,255,255,.4);">Height</label>
                        <input type="text" id="editItemHeight" class="form-control form-control-sm bg-dark text-white border-secondary">
                    </div>
                    <div class="col-4">
                        <label class="form-label mb-0" style="font-size:.68rem;color:rgba(255,255,255,.4);">Unit (Configuration)</label>
                        <select id="editItemConfig" class="form-select form-select-sm bg-dark text-white border-secondary">
                            <option value="">— Select —</option>
                        </select>
                    </div>
                    <div class="col-3">
                        <label class="form-label mb-0" style="font-size:.68rem;color:rgba(255,255,255,.4);">Type</label>
                        <select id="editItemOpeningType" class="form-select form-select-sm bg-dark text-white border-secondary">
                            <option value="">—</option>
                            <option value="Window">Window</option>
                            <option value="Door">Door</option>
                        </select>
                    </div>
                    <div class="col-4">
                        <label class="form-label mb-0" style="font-size:.68rem;color:rgba(255,255,255,.4);">Reference</label>
                        <input type="text" id="editItemRoom" class="form-control form-control-sm bg-dark text-white border-secondary">
                    </div>
                    <div class="col-5">
                        <label class="form-label mb-0" style="font-size:.68rem;color:rgba(255,255,255,.4);">Notes</label>
                        <input type="text" id="editItemNotes" class="form-control form-control-sm bg-dark text-white border-secondary">
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 py-2">
                <button type="button" class="btn btn-outline-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-vip btn-sm" onclick="saveEditItem()"><i class="bi bi-check-lg me-1"></i>Save</button>
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

// VIP Master options for dropdowns
const unitOptions = @json($unitOptions);
const frameTypeOptions = @json($frameTypeOptions);
const gridOptions = @json($gridOptions);
const patternOptions = @json($patternOptions);

function escHtml(str) {
    if (!str) return '';
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}

document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.tm-card');
    const urlParams = new URLSearchParams(window.location.search);
    const focusId = urlParams.get('focus');
    const fromCalendar = urlParams.get('from') === 'calendar';

    // If coming from calendar, hide the left rail for full-width detail view
    if (fromCalendar) {
        const rail = document.querySelector('.tm-rail');
        if (rail) rail.style.display = 'none';
        // Add a "Back to Calendar" button in the toolbar
        const toolbar = document.querySelector('.tm-main-toolbar');
        if (toolbar) {
            const backBtn = document.createElement('a');
            backBtn.href = '{{ route("installer.calendar") }}';
            backBtn.className = 'btn btn-sm btn-outline-secondary me-2';
            backBtn.innerHTML = '<i class="bi bi-arrow-left me-1"></i>Back to Calendar';
            toolbar.insertBefore(backBtn, toolbar.firstChild);
        }
    }

    // Tab filters
    document.querySelectorAll('.tm-rail-tabs .tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const status = this.dataset.status;
            const url = new URL(window.location);
            if (status !== 'all') url.searchParams.set('status', status);
            else url.searchParams.delete('status');
            // Remove calendar params when navigating tabs
            url.searchParams.delete('focus');
            url.searchParams.delete('from');
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

    // If a specific measure is focused (from calendar or direct link), load it
    if (focusId) {
        const targetCard = document.querySelector(`.tm-card[data-id="${focusId}"]`);
        if (targetCard) {
            targetCard.click();
        } else {
            // Measure not in current page — load directly via AJAX
            loadMeasure(focusId);
        }
    } else if (cards.length > 0) {
        cards[0].click();
    }
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

    // Toolbar actions — mirror Jobs pattern
    let actions = '';

    // Show elapsed time if currently clocked in
    if (m.is_clocked_in) {
        actions += `<span class="badge bg-primary me-2" id="elapsedBadge" style="font-size:.75rem;"><i class="bi bi-clock me-1"></i><span id="elapsedTime">--:--</span></span>`;
    }

    // Show total time tracked (completed sessions)
    if (m.total_time_minutes > 0 && !m.is_clocked_in) {
        const th = Math.floor(m.total_time_minutes / 60), tm = m.total_time_minutes % 60;
        actions += `<span class="badge bg-secondary me-2" style="font-size:.75rem;"><i class="bi bi-clock-history me-1"></i>${th}h ${tm}m total</span>`;
    }

    // Status transitions with Clock In/Out
    if (m.status === 'pending') {
        actions += `<button class="btn btn-sm btn-info text-white" onclick="clockInMeasure(${m.id})"><i class="bi bi-play-circle me-1"></i>Clock In</button> `;
    } else if (m.status === 'in_progress') {
        if (m.is_clocked_in) {
            actions += `<button class="btn btn-sm btn-warning" onclick="clockOutMeasure(${m.id})"><i class="bi bi-stop-circle me-1"></i>Clock Out</button> `;
        } else {
            actions += `<button class="btn btn-sm btn-info text-white" onclick="clockInMeasure(${m.id})"><i class="bi bi-play-circle me-1"></i>Clock In</button> `;
        }
        actions += `<button class="btn btn-sm btn-success" onclick="completeMeasure(${m.id})"><i class="bi bi-check-lg me-1"></i>Complete</button> `;
    }

    // Edit & Delete (like Jobs)
    if (m.status !== 'converted') {
        actions += `<button class="btn btn-sm btn-outline-primary" onclick="downloadPdf(${m.id})" title="Download PDF"><i class="bi bi-download"></i></button> `;
        actions += `<button class="btn btn-sm btn-outline-danger" onclick="deleteMeasure(${m.id})" title="Delete"><i class="bi bi-trash"></i></button>`;
    }

    // Completion badge
    if (m.status === 'completed') {
        const totalMins = m.total_time_minutes || 0;
        const hrs = Math.floor(totalMins / 60), mins = totalMins % 60;
        if (totalMins > 0) {
            actions = `<span class="badge bg-success me-2" style="font-size:.75rem;"><i class="bi bi-check-circle me-1"></i>Total: ${hrs}h ${mins}m</span>` + actions;
        }
    }

    toolbar.innerHTML = actions;

    // Start elapsed timer if clocked in
    if (m.is_clocked_in && m.active_since) {
        startElapsedTimer(new Date(m.active_since));
    }

    // Items table
    let itemsHtml = '';
    if (items.length) {
        itemsHtml = `<table class="tm-items-tbl">
            <thead><tr>
                <th style="width:30px;" class="text-center">#</th>
                <th class="text-center">Qty</th>
                <th>Width</th>
                <th>Height</th>
                <th>Unit (Configuration)</th>
                <th>Type</th>
                <th>Reference</th>
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
                <td class="text-center text-nowrap">
                    <button class="btn btn-sm p-0 text-muted" onclick="changeQty(${m.id}, ${item.id}, -1)" style="font-size:.7rem; line-height:1;"><i class="bi bi-dash-circle"></i></button>
                    <span class="mx-1 fw-semibold" id="qty_${item.id}">${item.qty || 1}</span>
                    <button class="btn btn-sm p-0 text-muted" onclick="changeQty(${m.id}, ${item.id}, 1)" style="font-size:.7rem; line-height:1;"><i class="bi bi-plus-circle"></i></button>
                </td>
                <td class="text-nowrap">${item.width || '—'}</td>
                <td class="text-nowrap">${item.height || '—'}</td>
                <td>${item.description || '—'}</td>
                <td>${item.opening_type || '—'}</td>
                <td>${item.room_label || '—'}${photoHtml}</td>
                <td style="font-size:.72rem;">${item.notes || ''}</td>
                <td class="text-center text-nowrap">
                    <button class="btn btn-sm text-primary p-0 me-1" onclick="editItemFromData(${m.id}, ${item.id})" title="Edit"><i class="bi bi-pencil" style="font-size:.75rem;"></i></button>
                    <button class="btn btn-sm text-primary p-0" onclick="uploadItemPhoto(${m.id}, ${item.id})" title="Add Photo"><i class="bi bi-camera" style="font-size:.75rem;"></i></button>
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
            <div class="row g-2 align-items-end">
                <div class="col-md-1">
                    <label>Qty</label>
                    <input type="number" id="addQty" class="form-control form-control-sm" value="1" min="1">
                </div>
                <div class="col">
                    <label>Width</label>
                    <input type="text" id="addWidth" class="form-control form-control-sm" placeholder='36 1/2'>
                </div>
                <div class="col">
                    <label>Height</label>
                    <input type="text" id="addHeight" class="form-control form-control-sm" placeholder='60 3/8'>
                </div>
                <div class="col-md-2">
                    <label>Unit (Configuration)</label>
                    <select id="addConfig" class="form-select form-select-sm">
                        <option value="">— Select —</option>
                        ${unitOptions.map(o => `<option value="${escHtml(o.name)}">${escHtml(o.name)}</option>`).join('')}
                    </select>
                </div>
                <div class="col-md-1">
                    <label>Type</label>
                    <select id="addOpeningType" class="form-select form-select-sm">
                        <option value="">—</option>
                        <option value="Window">Window</option>
                        <option value="Door">Door</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Reference</label>
                    <input type="text" id="addRoom" class="form-control form-control-sm" placeholder="e.g. Living Room">
                </div>
                <div class="col-md-3">
                    <label>Notes</label>
                    <input type="text" id="addNotes" class="form-control form-control-sm" placeholder="Any notes...">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button class="btn btn-sm btn-vip w-100" onclick="addItem(${m.id})" title="Add Opening"><i class="bi bi-plus-lg"></i></button>
                </div>
            </div>
        </div>

        {{-- Frame Type — applies to all openings --}}
        <h6 class="section-title"><i class="bi bi-columns-gap"></i> Frame Type</h6>
        <div class="tm-add-form mb-3">
            <div class="row g-2 align-items-center">
                <div class="col-md-4">
                    <select id="globalFrame" class="form-select form-select-sm" onchange="updateFrameBottomOptions();">
                        <option value="">— Select Frame Type —</option>
                        ${frameTypeOptions.map(o => `<option value="${escHtml(o.name)}" ${m.frame_type === o.name ? 'selected' : ''}>${escHtml(o.name)}</option>`).join('')}
                    </select>
                </div>
                <div class="col-md-6 d-flex gap-3">
                    <div class="form-check form-check-sm mb-0">
                        <input class="form-check-input" type="checkbox" id="frameAlt1" ${m.retrofit_bottom_only ? 'checked' : ''}>
                        <label class="form-check-label small" for="frameAlt1" id="frameAlt1Label">Retrofit 2 1/2" Frame Bottom</label>
                    </div>
                    <div class="form-check form-check-sm mb-0">
                        <input class="form-check-input" type="checkbox" id="frameAlt2" ${m.block_frame_bottom ? 'checked' : ''}>
                        <label class="form-check-label small" for="frameAlt2" id="frameAlt2Label">Block Frame Bottom</label>
                    </div>
                </div>
                <div class="col-md-2 text-end">
                    <button class="btn btn-sm btn-outline-success" onclick="saveInstallerFrameAndGrids(${m.id}, 'frame')"><i class="bi bi-check-lg me-1"></i>Save</button>
                </div>
            </div>
        </div>

        {{-- Grid Toggle Section --}}
        <h6 class="section-title"><i class="bi bi-grid-3x3"></i> Grids</h6>
        <div class="tm-add-form">
            <div class="d-flex align-items-center gap-3 mb-2">
                <label class="fw-semibold" style="font-size:.85rem;">Does this project have grids?</label>
                <div class="form-check form-check-inline mb-0">
                    <input class="form-check-input" type="radio" name="hasGrids" id="gridsYes" value="yes" ${m.has_grids ? 'checked' : ''} onchange="toggleGridFields()">
                    <label class="form-check-label" for="gridsYes" style="font-size:.85rem;">Yes</label>
                </div>
                <div class="form-check form-check-inline mb-0">
                    <input class="form-check-input" type="radio" name="hasGrids" id="gridsNo" value="no" ${!m.has_grids ? 'checked' : ''} onchange="toggleGridFields()">
                    <label class="form-check-label" for="gridsNo" style="font-size:.85rem;">No</label>
                </div>
                <div class="ms-auto">
                    <button class="btn btn-sm btn-outline-success" onclick="saveInstallerFrameAndGrids(${m.id}, 'grids')"><i class="bi bi-check-lg me-1"></i>Save</button>
                </div>
            </div>
            <div id="gridFieldsWrap" style="display:${m.has_grids ? 'block' : 'none'};">
                <div class="row g-2">
                    <div class="col-md-4">
                        <label>Grid Type</label>
                        <select id="gridList" class="form-select form-select-sm">
                            <option value="">— Select —</option>
                            ${gridOptions.map(o => `<option value="${escHtml(o.name)}" ${m.grid_list === o.name ? 'selected' : ''}>${escHtml(o.name)}</option>`).join('')}
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Grid Pattern</label>
                        <select id="gridPattern" class="form-select form-select-sm">
                            <option value="">— Select —</option>
                            ${patternOptions.map(o => `<option value="${escHtml(o.name)}" ${m.grid_pattern === o.name ? 'selected' : ''}>${escHtml(o.name)}</option>`).join('')}
                        </select>
                    </div>
                </div>
            </div>
        </div>
        ` : `
        {{-- Read-only frame & grids for converted --}}
        ${m.frame_type ? `
        <h6 class="section-title"><i class="bi bi-columns-gap"></i> Frame Type</h6>
        <div class="tm-info-grid">
            <div class="tm-info-card"><div class="label">Frame Type</div><div class="value">${escHtml(m.frame_type)}</div></div>
            ${m.retrofit_bottom_only ? `<div class="tm-info-card"><div class="label">${
                m.frame_type.indexOf('1 3/4') >= 0 ? 'Retrofit 2 1/2" Frame Bottom' :
                m.frame_type.indexOf('2 1/2') >= 0 ? 'Retrofit 1 3/4" Frame Bottom' :
                m.frame_type === 'Block' ? 'Retrofit 1 3/4" Frame Bottom' :
                'Retrofit 2 1/2" Frame Bottom'
            }</div><div class="value">Yes</div></div>` : ''}
            ${m.block_frame_bottom ? `<div class="tm-info-card"><div class="label">${
                m.frame_type === 'Block' ? 'Retrofit 2 1/2" Frame Bottom' : 'Block Frame Bottom'
            }</div><div class="value">Yes</div></div>` : ''}
        </div>` : ''}
        <h6 class="section-title"><i class="bi bi-grid-3x3"></i> Grids</h6>
        <div class="tm-info-grid">
            <div class="tm-info-card"><div class="label">Grids</div><div class="value">${m.has_grids ? 'Yes' : 'No'}</div></div>
            ${m.has_grids ? `
            <div class="tm-info-card"><div class="label">Grid Type</div><div class="value">${escHtml(m.grid_list || '—')}</div></div>
            <div class="tm-info-card"><div class="label">Grid Pattern</div><div class="value">${escHtml(m.grid_pattern || '—')}</div></div>
            ` : ''}
        </div>
        `}

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

    // Initialize frame bottom checkbox labels after render
    lastFrameSelection = null;
    setTimeout(() => { updateFrameBottomOptions(); }, 10);
}


function addItem(measureId) {
    const qty = parseInt(document.getElementById('addQty')?.value || 1);
    const width = document.getElementById('addWidth')?.value?.trim();
    const height = document.getElementById('addHeight')?.value?.trim();
    const config = document.getElementById('addConfig')?.value || null;
    const openingType = document.getElementById('addOpeningType')?.value || null;
    const room = document.getElementById('addRoom')?.value?.trim();
    const notes = document.getElementById('addNotes')?.value?.trim();

    fetch(`/installer/tech-measures/${measureId}/item`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        body: JSON.stringify({
            room_label: room,
            description: config || null,
            series_type: config,
            width: width || null,
            height: height || null,
            qty: qty,
            opening_type: openingType,
            notes: notes,
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Clear inputs for next entry (keep Unit and Type selections)
            document.getElementById('addQty').value = 1;
            document.getElementById('addWidth').value = '';
            document.getElementById('addHeight').value = '';
            document.getElementById('addRoom').value = '';
            document.getElementById('addNotes').value = '';
            loadMeasure(measureId);
        }
        else alert(data.error || 'Failed to add item.');
    })
    .catch(() => alert('Failed to add item.'));
}

function changeQty(measureId, itemId, delta) {
    const span = document.getElementById('qty_' + itemId);
    if (!span) return;
    let qty = parseInt(span.textContent) + delta;
    if (qty < 1) qty = 1;
    span.textContent = qty;

    fetch(`/installer/tech-measures/${measureId}/item/${itemId}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        body: JSON.stringify({ qty })
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) { alert('Failed to update qty.'); loadMeasure(measureId); }
    })
    .catch(() => { alert('Failed to update qty.'); loadMeasure(measureId); });
}

function editItemFromData(measureId, itemId) {
    if (!currentMeasureData) return;
    const item = (currentMeasureData.items || []).find(i => i.id === itemId);
    if (!item) return;
    editItem(measureId, itemId, item);
}

function editItem(measureId, itemId, item) {
    document.getElementById('editItemMeasureId').value = measureId;
    document.getElementById('editItemId').value = itemId;
    document.getElementById('editItemQty').value = item.qty || 1;
    document.getElementById('editItemWidth').value = item.width || '';
    document.getElementById('editItemHeight').value = item.height || '';
    document.getElementById('editItemRoom').value = item.room_label || '';
    document.getElementById('editItemNotes').value = item.notes || '';

    // Populate unit dropdown with options
    const sel = document.getElementById('editItemConfig');
    sel.innerHTML = '<option value="">— Select —</option>';
    unitOptions.forEach(o => {
        const opt = document.createElement('option');
        opt.value = o.name;
        opt.textContent = o.name;
        if (o.name === item.description) opt.selected = true;
        sel.appendChild(opt);
    });

    // Set opening type
    document.getElementById('editItemOpeningType').value = item.opening_type || '';

    new bootstrap.Modal(document.getElementById('editItemModal')).show();
}

function saveEditItem() {
    const measureId = document.getElementById('editItemMeasureId').value;
    const itemId = document.getElementById('editItemId').value;
    const config = document.getElementById('editItemConfig').value;

    fetch(`/installer/tech-measures/${measureId}/item/${itemId}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        body: JSON.stringify({
            qty: parseInt(document.getElementById('editItemQty').value) || 1,
            width: document.getElementById('editItemWidth').value.trim() || null,
            height: document.getElementById('editItemHeight').value.trim() || null,
            description: config || null,
            series_type: config,
            opening_type: document.getElementById('editItemOpeningType').value || null,
            room_label: document.getElementById('editItemRoom').value.trim(),
            notes: document.getElementById('editItemNotes').value.trim(),
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('editItemModal')).hide();
            loadMeasure(measureId);
        } else alert(data.error || 'Failed to update.');
    })
    .catch(() => alert('Failed to update.'));
}

function saveFrameType(measureId) {
    const frameType = document.getElementById('globalFrame')?.value || null;
    fetch(`/installer/tech-measures/${measureId}/notes`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        body: JSON.stringify({ frame_type: frameType })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success && currentMeasureData) {
            currentMeasureData.measure.frame_type = frameType;
        }
    })
    .catch(() => {});
}

let lastFrameSelection = null;

function updateFrameBottomOptions() {
    const selected = document.getElementById('globalFrame')?.value || '';
    const alt1Label = document.getElementById('frameAlt1Label');
    const alt2Label = document.getElementById('frameAlt2Label');
    const alt1Cb = document.getElementById('frameAlt1');
    const alt2Cb = document.getElementById('frameAlt2');
    if (!alt1Label || !alt2Label) return;

    // Only uncheck when user actively changes the dropdown, not on initial load
    if (lastFrameSelection !== null && lastFrameSelection !== selected) {
        if (alt1Cb) alt1Cb.checked = false;
        if (alt2Cb) alt2Cb.checked = false;
    }
    lastFrameSelection = selected;

    if (selected.indexOf('1 3/4') >= 0) {
        alt1Label.textContent = 'Retrofit 2 1/2" Frame Bottom';
        alt2Label.textContent = 'Block Frame Bottom';
    } else if (selected.indexOf('2 1/2') >= 0) {
        alt1Label.textContent = 'Retrofit 1 3/4" Frame Bottom';
        alt2Label.textContent = 'Block Frame Bottom';
    } else if (selected === 'Block') {
        alt1Label.textContent = 'Retrofit 1 3/4" Frame Bottom';
        alt2Label.textContent = 'Retrofit 2 1/2" Frame Bottom';
    } else {
        alt1Label.textContent = 'Retrofit 2 1/2" Frame Bottom';
        alt2Label.textContent = 'Block Frame Bottom';
    }
}

function saveFrameOptions(measureId) {
    const retrofitBottom = document.getElementById('frameAlt1')?.checked ? 1 : 0;
    const blockBottom = document.getElementById('frameAlt2')?.checked ? 1 : 0;
    fetch(`/installer/tech-measures/${measureId}/notes`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        body: JSON.stringify({ retrofit_bottom_only: retrofitBottom, block_frame_bottom: blockBottom })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success && currentMeasureData) {
            currentMeasureData.measure.retrofit_bottom_only = retrofitBottom;
            currentMeasureData.measure.block_frame_bottom = blockBottom;
        }
    })
    .catch(() => {});
}

function toggleGridFields() {
    const isYes = document.getElementById('gridsYes')?.checked;
    const wrap = document.getElementById('gridFieldsWrap');
    if (wrap) wrap.style.display = isYes ? 'block' : 'none';
}

function saveInstallerFrameAndGrids(measureId, section) {
    const btn = event?.target?.closest('button') || event?.target;
    if (section === 'frame') {
        const frameType = document.getElementById('globalFrame')?.value || null;
        const retrofitBottom = document.getElementById('frameAlt1')?.checked ? 1 : 0;
        const blockBottom = document.getElementById('frameAlt2')?.checked ? 1 : 0;
        fetch(`/installer/tech-measures/${measureId}/notes`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify({ frame_type: frameType, retrofit_bottom_only: retrofitBottom, block_frame_bottom: blockBottom })
        }).then(r => r.json()).then(data => {
            if (data.success && currentMeasureData) {
                currentMeasureData.measure.frame_type = frameType;
                currentMeasureData.measure.retrofit_bottom_only = retrofitBottom;
                currentMeasureData.measure.block_frame_bottom = blockBottom;
            }
            flashInstallerBtn(btn);
        }).catch(() => alert('Failed to save.'));
    } else {
        const hasGrids = document.getElementById('gridsYes')?.checked ? 1 : 0;
        const gridList = document.getElementById('gridList')?.value || null;
        const gridPattern = document.getElementById('gridPattern')?.value || null;
        fetch(`/installer/tech-measures/${measureId}/grids`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify({ has_grids: hasGrids, grid_list: gridList, grid_pattern: gridPattern })
        }).then(r => r.json()).then(data => {
            if (data.success && currentMeasureData) {
                currentMeasureData.measure.has_grids = hasGrids;
                currentMeasureData.measure.grid_list = gridList;
                currentMeasureData.measure.grid_pattern = gridPattern;
            }
            flashInstallerBtn(btn);
        }).catch(() => alert('Failed to save.'));
    }
}

function flashInstallerBtn(btn) {
    if (!btn) return;
    const orig = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Saved!';
    btn.classList.replace('btn-outline-success', 'btn-success');
    setTimeout(() => { btn.innerHTML = orig; btn.classList.replace('btn-success', 'btn-outline-success'); }, 1500);
}

function saveGridSettings(measureId) {
    const hasGrids = document.getElementById('gridsYes')?.checked ? 1 : 0;
    const gridList = document.getElementById('gridList')?.value || null;
    const gridPattern = document.getElementById('gridPattern')?.value || null;

    fetch(`/installer/tech-measures/${measureId}/grids`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        body: JSON.stringify({ has_grids: hasGrids, grid_list: gridList, grid_pattern: gridPattern })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success && currentMeasureData) {
            currentMeasureData.measure.has_grids = hasGrids;
            currentMeasureData.measure.grid_list = gridList;
            currentMeasureData.measure.grid_pattern = gridPattern;
        }
    })
    .catch(() => {});
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

function clockInMeasure(measureId) {
    fetch(`/installer/tech-measures/${measureId}/clock-in`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            loadMeasure(measureId);
        } else {
            alert(data.error || 'Failed to clock in.');
        }
    })
    .catch(() => alert('Failed to clock in.'));
}

function clockOutMeasure(measureId) {
    fetch(`/installer/tech-measures/${measureId}/clock-out`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert(data.message || 'Clocked out.');
            loadMeasure(measureId);
        } else {
            alert(data.error || 'Failed to clock out.');
        }
    })
    .catch(() => alert('Failed to clock out.'));
}

function completeMeasure(measureId) {
    // Check how many openings exist and total qty
    const items = currentMeasureData?.items || [];
    const totalQty = items.reduce((sum, item) => sum + (parseInt(item.qty) || 1), 0);

    if (items.length === 0) {
        alert('No openings have been entered. Please add at least one opening before completing this tech measure.');
        return;
    }

    const label = totalQty === 1 ? '1 opening' : totalQty + ' openings';
    if (!confirm(`Complete this tech measure with ${label}?`)) return;

    // Gather all current frame & grid data to save with completion
    const frameType = document.getElementById('globalFrame')?.value || null;
    const retrofitBottom = document.getElementById('frameAlt1')?.checked ? 1 : 0;
    const blockBottom = document.getElementById('frameAlt2')?.checked ? 1 : 0;
    const hasGrids = document.getElementById('gridsYes')?.checked ? 1 : 0;
    const gridList = document.getElementById('gridList')?.value || null;
    const gridPattern = document.getElementById('gridPattern')?.value || null;

    fetch(`/installer/tech-measures/${measureId}/complete`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        body: JSON.stringify({
            frame_type: frameType,
            retrofit_bottom_only: retrofitBottom,
            block_frame_bottom: blockBottom,
            has_grids: hasGrids,
            grid_list: gridList,
            grid_pattern: gridPattern,
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('from') === 'calendar') {
                window.location.href = '{{ route("installer.calendar") }}';
            } else {
                loadMeasure(measureId);
            }
        }
    })
    .catch(() => alert('Failed to complete.'));
}

function downloadPdf(measureId) {
    window.open(`/installer/tech-measures/${measureId}/pdf`, '_blank');
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

// Elapsed timer for active measures
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
</script>
@endpush
