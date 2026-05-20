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
                <div class="tab-btn {{ $status === 'converted' ? 'active' : '' }}" data-status="converted">Converted</div>
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
{{-- Edit Tech Measure Modal --}}
<div class="modal fade" id="editTmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title mb-0"><i class="bi bi-pencil me-1"></i>Edit Tech Measure</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-2">
                <div class="row g-2">
                    <div class="col-4">
                        <label class="form-label mb-0" style="font-size:.75rem; color:#888;">Customer *</label>
                        <input type="text" id="editTmCustName" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-4">
                        <label class="form-label mb-0" style="font-size:.75rem; color:#888;">Email</label>
                        <input type="email" id="editTmCustEmail" class="form-control form-control-sm">
                    </div>
                    <div class="col-4">
                        <label class="form-label mb-0" style="font-size:.75rem; color:#888;">Phone</label>
                        <input type="text" id="editTmCustPhone" class="form-control form-control-sm">
                    </div>
                    <div class="col-8">
                        <label class="form-label mb-0" style="font-size:.75rem; color:#888;">Address</label>
                        <input type="text" id="editTmAddress" class="form-control form-control-sm">
                    </div>
                    <div class="col-12">
                        <label class="form-label mb-0" style="font-size:.75rem; color:#888;">Notes</label>
                        <textarea id="editTmNotes" class="form-control form-control-sm" rows="3"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-vip btn-sm" onclick="saveEditTm()"><i class="bi bi-check-lg me-1"></i>Save Changes</button>
            </div>
        </div>
    </div>
</div>

{{-- Edit Opening Item Modal --}}
<div class="modal fade" id="editItemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title mb-0"><i class="bi bi-pencil me-1"></i>Edit Opening</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-2">
                <input type="hidden" id="editItemMeasureId">
                <input type="hidden" id="editItemId">
                <div class="row g-2">
                    <div class="col-2">
                        <label class="form-label mb-0" style="font-size:.75rem; color:#888;">Qty</label>
                        <input type="number" id="editItemQty" class="form-control form-control-sm" min="1">
                    </div>
                    <div class="col-3">
                        <label class="form-label mb-0" style="font-size:.75rem; color:#888;">Width</label>
                        <input type="text" id="editItemWidth" class="form-control form-control-sm">
                    </div>
                    <div class="col-3">
                        <label class="form-label mb-0" style="font-size:.75rem; color:#888;">Height</label>
                        <input type="text" id="editItemHeight" class="form-control form-control-sm">
                    </div>
                    <div class="col-4">
                        <label class="form-label mb-0" style="font-size:.75rem; color:#888;">Unit (Configuration)</label>
                        <select id="editItemConfig" class="form-select form-select-sm">
                            <option value="">— Select —</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label mb-0" style="font-size:.75rem; color:#888;">Reference</label>
                        <input type="text" id="editItemRoom" class="form-control form-control-sm">
                    </div>
                    <div class="col-6">
                        <label class="form-label mb-0" style="font-size:.75rem; color:#888;">Notes</label>
                        <input type="text" id="editItemNotes" class="form-control form-control-sm">
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-vip btn-sm" onclick="saveEditItem()"><i class="bi bi-check-lg me-1"></i>Save</button>
            </div>
        </div>
    </div>
</div>

{{-- Convert to Job Modal --}}
<div class="modal fade" id="convertJobModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header py-2" style="background:linear-gradient(135deg, var(--vip-accent), #a0832a); color:#fff;">
                <h6 class="modal-title mb-0"><i class="bi bi-tools me-1"></i> Convert to Job</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-3">
                {{-- Section 1: Service Line Items --}}
                <h6 style="font-size:.75rem; text-transform:uppercase; letter-spacing:.5px; color:rgba(0,0,0,.5); margin-bottom:.5rem;">
                    <i class="bi bi-list-check me-1"></i> Service Line Items
                </h6>
                <div class="card mb-3" style="border:none; box-shadow:0 1px 4px rgba(0,0,0,.06);">
                    <div class="card-body p-2">
                        <table class="table table-sm table-borderless mb-2" id="jobLineItemsTable">
                            <thead>
                                <tr style="font-size:.68rem; text-transform:uppercase; letter-spacing:.5px; color:rgba(0,0,0,.4);">
                                    <th style="width:60px;">Qty</th>
                                    <th>Service</th>
                                    <th style="width:120px;">Unit Price</th>
                                    <th style="width:120px;">Line Total</th>
                                    <th style="width:40px;"></th>
                                </tr>
                            </thead>
                            <tbody id="jobLineItemsBody"></tbody>
                        </table>
                        <button class="btn btn-sm btn-outline-secondary" onclick="addJobLineItem()">
                            <i class="bi bi-plus-lg me-1"></i> Add Line Item
                        </button>
                        <div class="text-end mt-2">
                            <strong style="font-size:.85rem;">Installation Total: $<span id="jobLineTotal">0.00</span></strong>
                        </div>
                    </div>
                </div>

                {{-- Section 2: Measurements with Price --}}
                <h6 style="font-size:.75rem; text-transform:uppercase; letter-spacing:.5px; color:rgba(0,0,0,.5); margin-bottom:.5rem;">
                    <i class="bi bi-rulers me-1"></i> Measurements
                </h6>
                <div class="card mb-3" style="border:none; box-shadow:0 1px 4px rgba(0,0,0,.06);">
                    <div class="card-body p-2">
                        <table class="table table-sm table-borderless mb-0">
                            <thead>
                                <tr style="font-size:.68rem; text-transform:uppercase; letter-spacing:.5px; color:rgba(0,0,0,.4);">
                                    <th style="width:30px;">#</th>
                                    <th style="width:40px;">Qty</th>
                                    <th>Width</th>
                                    <th>Height</th>
                                    <th>Unit (Config)</th>
                                    <th>Reference</th>
                                    <th>Notes</th>
                                    <th style="width:120px;">Price</th>
                                </tr>
                            </thead>
                            <tbody id="jobMeasurementsBody"></tbody>
                        </table>
                        <div class="text-end mt-2">
                            <strong style="font-size:.85rem;">Measurements Total: $<span id="jobMeasurementsTotal">0.00</span></strong>
                        </div>
                    </div>
                </div>

                {{-- Grand Total --}}
                <div class="text-end mb-3 pe-2">
                    <strong style="font-size:1rem; color:var(--vip-accent);">Grand Total: $<span id="jobGrandTotal">0.00</span></strong>
                </div>

                {{-- Section 3: Schedule --}}
                <h6 style="font-size:.75rem; text-transform:uppercase; letter-spacing:.5px; color:rgba(0,0,0,.5); margin-bottom:.5rem;">
                    <i class="bi bi-calendar-event me-1"></i> Schedule
                </h6>
                <div class="card mb-3" style="border:none; box-shadow:0 1px 4px rgba(0,0,0,.06);">
                    <div class="card-body p-2">
                        <div class="row g-2">
                            <div class="col-md-3">
                                <label style="font-size:.65rem; color:#999; text-transform:uppercase;">Start Date</label>
                                <input type="date" id="jobStartDate" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-3">
                                <label style="font-size:.65rem; color:#999; text-transform:uppercase;">Start Time</label>
                                <input type="time" id="jobStartTime" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-3">
                                <label style="font-size:.65rem; color:#999; text-transform:uppercase;">End Date</label>
                                <input type="date" id="jobEndDate" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-3">
                                <label style="font-size:.65rem; color:#999; text-transform:uppercase;">Est. Duration</label>
                                <input type="text" id="jobDuration" class="form-control form-control-sm" placeholder="e.g. 2 days">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Section 4: PDF Attachment --}}
                <h6 style="font-size:.75rem; text-transform:uppercase; letter-spacing:.5px; color:rgba(0,0,0,.5); margin-bottom:.5rem;">
                    <i class="bi bi-file-earmark-pdf me-1"></i> Attach PDF <span class="text-danger">*</span>
                </h6>
                <div class="card" style="border:none; box-shadow:0 1px 4px rgba(0,0,0,.06);">
                    <div class="card-body p-2">
                        <input type="file" id="jobPdfFile" accept=".pdf" class="form-control form-control-sm" required>
                        <div class="form-text">Upload the tech measure PDF or related documentation. This is required.</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-vip btn-sm" onclick="submitConvertToJob()">
                    <i class="bi bi-tools me-1"></i> Convert to Job
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Email Modal --}}
<div class="modal fade" id="emailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title mb-0"><i class="bi bi-envelope me-1"></i>Email Customer</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-2">
                <div class="mb-2">
                    <label class="form-label mb-0" style="font-size:.75rem; color:#888;">To</label>
                    <input type="text" id="emailTo" class="form-control form-control-sm" readonly>
                </div>
                <div class="mb-2">
                    <label class="form-label mb-0" style="font-size:.75rem; color:#888;">Subject</label>
                    <input type="text" id="emailSubject" class="form-control form-control-sm" placeholder="Subject...">
                </div>
                <div class="mb-2">
                    <label class="form-label mb-0" style="font-size:.75rem; color:#888;">Message</label>
                    <textarea id="emailMessage" class="form-control form-control-sm" rows="5" placeholder="Write your message..."></textarea>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-vip btn-sm" onclick="sendEmail()"><i class="bi bi-send me-1"></i>Send Email</button>
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
    .then(data => { currentMeasureData = data; renderDetail(data); })
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
    if (m.status !== 'converted') {
        actions += `<button class="btn btn-sm btn-outline-primary me-1" onclick="editMeasure(${m.id})" title="Edit"><i class="bi bi-pencil"></i></button>`;
        actions += `<button class="btn btn-sm btn-outline-primary me-1" onclick="downloadPdf(${m.id})" title="Download PDF"><i class="bi bi-download"></i></button>`;
    }
    if (m.status === 'completed') {
        actions += `<button class="btn btn-sm btn-vip" onclick="convertToQuote(${m.id})"><i class="bi bi-tools me-1"></i>Convert to Job</button>`;
    }
    toolbar.innerHTML = actions;

    let itemsHtml = '';
    if (items.length) {
        itemsHtml = `<table class="tm-items-tbl"><thead><tr>
            <th style="width:30px;">#</th><th>Qty</th><th>Width</th><th>Height</th><th>Unit (Configuration)</th><th>Reference</th><th>Notes</th><th style="width:60px;"></th>
        </tr></thead><tbody>`;
        items.forEach((item, idx) => {
            const photoHtml = item.photos?.length ? `<div class="item-photos">${item.photos.map(p => `<img src="${p.url}" class="item-photo" onclick="window.open('${p.url}','_blank')">`).join('')}</div>` : '';
            itemsHtml += `<tr>
                <td class="text-center text-muted">${idx + 1}</td>
                <td class="text-center">${item.qty || 1}</td>
                <td class="text-nowrap">${item.width || '—'}</td>
                <td class="text-nowrap">${item.height || '—'}</td>
                <td>${item.description || '—'}</td>
                <td>${item.room_label || '—'}${photoHtml}</td>
                <td style="font-size:.72rem;">${item.notes || ''}</td>
                <td class="text-center text-nowrap">
                    <button class="btn btn-sm text-primary p-0 me-1" onclick="editItemFromData(${m.id}, ${item.id})" title="Edit"><i class="bi bi-pencil" style="font-size:.75rem;"></i></button>
                    <button class="btn btn-sm text-primary p-0 me-1" onclick="uploadItemPhoto(${m.id}, ${item.id})" title="Add Photo"><i class="bi bi-camera" style="font-size:.75rem;"></i></button>
                    <button class="btn btn-sm text-danger p-0" onclick="removeItem(${m.id}, ${item.id})" title="Remove"><i class="bi bi-x-lg" style="font-size:.65rem;"></i></button>
                </td>
            </tr>`;
        });
        itemsHtml += '</tbody></table>';
    } else {
        itemsHtml = '<p class="text-muted small">No measurements recorded yet.</p>';
    }

    // Add opening form (for non-converted measures)
    let addFormHtml = '';
    if (m.status !== 'converted') {
        addFormHtml = `
        <h6 class="section-title"><i class="bi bi-plus-circle"></i> Add Opening</h6>
        <div class="card" style="border:none; box-shadow:0 1px 4px rgba(0,0,0,.06);">
            <div class="card-body py-2 px-3">
                <div class="row g-2 align-items-end">
                    <div class="col-md-1">
                        <label style="font-size:.65rem; color:#999; text-transform:uppercase;">Qty</label>
                        <input type="number" id="addQty" class="form-control form-control-sm" value="1" min="1">
                    </div>
                    <div class="col">
                        <label style="font-size:.65rem; color:#999; text-transform:uppercase;">Width</label>
                        <input type="text" id="addWidth" class="form-control form-control-sm" placeholder="36 1/2">
                    </div>
                    <div class="col">
                        <label style="font-size:.65rem; color:#999; text-transform:uppercase;">Height</label>
                        <input type="text" id="addHeight" class="form-control form-control-sm" placeholder="60 3/8">
                    </div>
                    <div class="col-md-2">
                        <label style="font-size:.65rem; color:#999; text-transform:uppercase;">Unit (Config)</label>
                        <select id="addConfig" class="form-select form-select-sm">
                            <option value="">— Select —</option>
                            ${unitOptions.map(o => '<option value="' + escHtml(o.name) + '">' + escHtml(o.name) + '</option>').join('')}
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label style="font-size:.65rem; color:#999; text-transform:uppercase;">Reference</label>
                        <input type="text" id="addRoom" class="form-control form-control-sm" placeholder="e.g. Living Room">
                    </div>
                    <div class="col-md-2">
                        <label style="font-size:.65rem; color:#999; text-transform:uppercase;">Notes</label>
                        <input type="text" id="addNotes" class="form-control form-control-sm" placeholder="Any notes...">
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button class="btn btn-sm btn-vip w-100" onclick="addItem(${m.id})" title="Add Opening"><i class="bi bi-plus-lg"></i></button>
                    </div>
                </div>
            </div>
        </div>`;
    }

    // Frame type & grid sections (editable for non-converted)
    let frameGridHtml = '';
    if (m.status !== 'converted') {
        frameGridHtml = `
        <h6 class="section-title"><i class="bi bi-columns-gap"></i> Frame Type</h6>
        <div class="card" style="border:none; box-shadow:0 1px 4px rgba(0,0,0,.06);">
            <div class="card-body py-2 px-3">
                <div class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <select id="globalFrame" class="form-select form-select-sm" onchange="updateFrameBottomOptions();">
                            <option value="">— Select Frame Type —</option>
                            ${frameTypeOptions.map(o => '<option value="' + escHtml(o.name) + '" ' + (m.frame_type === o.name ? 'selected' : '') + '>' + escHtml(o.name) + '</option>').join('')}
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
                        <button class="btn btn-sm btn-outline-success" id="btnSaveFrame" onclick="saveFrameAndGrids(${m.id}, 'frame')"><i class="bi bi-check-lg me-1"></i>Save</button>
                    </div>
                </div>
            </div>
        </div>
        <h6 class="section-title"><i class="bi bi-grid-3x3"></i> Grids</h6>
        <div class="card" style="border:none; box-shadow:0 1px 4px rgba(0,0,0,.06);">
            <div class="card-body py-2 px-3">
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
                        <button class="btn btn-sm btn-outline-success" id="btnSaveGrids" onclick="saveFrameAndGrids(${m.id}, 'grids')"><i class="bi bi-check-lg me-1"></i>Save</button>
                    </div>
                </div>
                <div id="gridFieldsWrap" style="display:${m.has_grids ? 'block' : 'none'};">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <select id="gridList" class="form-select form-select-sm">
                                <option value="">— Select —</option>
                                ${gridOptions.map(o => '<option value="' + escHtml(o.name) + '" ' + (m.grid_list === o.name ? 'selected' : '') + '>' + escHtml(o.name) + '</option>').join('')}
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select id="gridPattern" class="form-select form-select-sm">
                                <option value="">— Select —</option>
                                ${patternOptions.map(o => '<option value="' + escHtml(o.name) + '" ' + (m.grid_pattern === o.name ? 'selected' : '') + '>' + escHtml(o.name) + '</option>').join('')}
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>`;
    } else {
        // Read-only for converted — compute checkbox labels based on frame type
        let fgReadonly = '';
        let alt1Lbl = 'Retrofit 2 1/2" Frame Bottom', alt2Lbl = 'Block Frame Bottom';
        if (m.frame_type) {
            if (m.frame_type.indexOf('1 3/4') >= 0) { alt1Lbl = 'Retrofit 2 1/2" Frame Bottom'; alt2Lbl = 'Block Frame Bottom'; }
            else if (m.frame_type.indexOf('2 1/2') >= 0) { alt1Lbl = 'Retrofit 1 3/4" Frame Bottom'; alt2Lbl = 'Block Frame Bottom'; }
            else if (m.frame_type === 'Block') { alt1Lbl = 'Retrofit 1 3/4" Frame Bottom'; alt2Lbl = 'Retrofit 2 1/2" Frame Bottom'; }
            fgReadonly += `<div class="tm-info-card"><div class="label">Frame Type</div><div class="value">${escHtml(m.frame_type)}</div></div>`;
        }
        if (m.retrofit_bottom_only) {
            fgReadonly += `<div class="tm-info-card"><div class="label">${alt1Lbl}</div><div class="value">Yes</div></div>`;
        }
        if (m.block_frame_bottom) {
            fgReadonly += `<div class="tm-info-card"><div class="label">${alt2Lbl}</div><div class="value">Yes</div></div>`;
        }
        fgReadonly += `<div class="tm-info-card"><div class="label">Grids</div><div class="value">${m.has_grids ? 'Yes' : 'No'}</div></div>`;
        if (m.has_grids) {
            fgReadonly += `<div class="tm-info-card"><div class="label">Grid Type</div><div class="value">${escHtml(m.grid_list || '—')}</div></div>`;
            fgReadonly += `<div class="tm-info-card"><div class="label">Grid Pattern</div><div class="value">${escHtml(m.grid_pattern || '—')}</div></div>`;
        }
        frameGridHtml = `<h6 class="section-title"><i class="bi bi-columns-gap"></i> Frame & Grids</h6><div class="tm-info-grid">${fgReadonly}</div>`;
    }

    let photosHtml = '';
    if (photos.length) {
        photosHtml = '<div class="tm-photo-grid">' + photos.map(p => `<div class="tm-photo-card" style="position:relative;"><img src="${p.url}" onclick="window.open('${p.url}','_blank')">
            ${m.status !== 'converted' ? `<button style="position:absolute;top:4px;right:4px;background:rgba(220,53,69,.9);color:#fff;border:none;width:20px;height:20px;border-radius:50%;font-size:.6rem;display:flex;align-items:center;justify-content:center;cursor:pointer;" onclick="deletePhoto(${m.id},${p.id})"><i class="bi bi-x"></i></button>` : ''}
        </div>`).join('') + '</div>';
    } else {
        photosHtml = '<p class="text-muted small">No site photos.</p>';
    }

    // Photo upload form
    let photoUploadHtml = '';
    if (m.status !== 'converted') {
        photoUploadHtml = `<div class="mt-2">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <input type="file" id="generalPhotoFile" accept="image/*" class="form-control form-control-sm">
                </div>
                <div class="col-md-4">
                    <input type="text" id="generalPhotoCaption" class="form-control form-control-sm" placeholder="Caption (optional)">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-sm btn-vip" onclick="uploadGeneralPhoto(${m.id})"><i class="bi bi-upload me-1"></i>Upload</button>
                </div>
            </div>
        </div>`;
    }

    // Contact action buttons
    let contactBtns = '';
    if (m.customer_phone) {
        contactBtns += `<a href="tel:${m.customer_phone}" class="btn btn-sm btn-outline-primary me-1" title="Call"><i class="bi bi-telephone me-1"></i>Call</a>`;
    }
    if (m.customer_email) {
        contactBtns += `<button class="btn btn-sm btn-outline-secondary" onclick="openEmailModal()" title="Email Customer"><i class="bi bi-envelope me-1"></i>Email Customer</button>`;
    }

    body.innerHTML = `
        <div class="d-flex align-items-center gap-2 mb-3">
            <h5 class="mb-0 fw-bold">${escHtml(m.customer_name || '—')}</h5>
            ${contactBtns ? `<div class="ms-2">${contactBtns}</div>` : ''}
        </div>
        <div class="tm-info-grid">
            <div class="tm-info-card"><div class="label">Customer</div><div class="value">${m.customer_name || '—'}</div></div>
            <div class="tm-info-card"><div class="label">Phone</div><div class="value">${m.customer_phone ? `<a href="tel:${m.customer_phone}">${m.customer_phone}</a>` : '—'}</div></div>
            <div class="tm-info-card"><div class="label">Email</div><div class="value">${m.customer_email || '—'}</div></div>
            <div class="tm-info-card"><div class="label">Address</div><div class="value">${m.address || '—'}</div></div>
            <div class="tm-info-card"><div class="label">Status</div><div class="value"><span class="badge bg-${m.status === 'in_progress' ? 'primary' : m.status === 'completed' ? 'success' : m.status === 'converted' ? 'secondary' : 'warning'}">${m.status?.replace('_',' ')}</span></div></div>
            <div class="tm-info-card"><div class="label">Openings</div><div class="value">${items.length}</div></div>
            ${m.started_at ? `<div class="tm-info-card"><div class="label">Started</div><div class="value">${new Date(m.started_at).toLocaleString()}</div></div>` : ''}
            ${m.completed_at ? `<div class="tm-info-card"><div class="label">Completed</div><div class="value">${new Date(m.completed_at).toLocaleString()}</div></div>` : ''}
        </div>
        <h6 class="section-title"><i class="bi bi-rulers"></i> Measurements (${items.length})</h6>
        ${itemsHtml}
        ${addFormHtml}
        ${frameGridHtml}
        <h6 class="section-title"><i class="bi bi-image"></i> Site Photos</h6>
        ${photosHtml}
        ${photoUploadHtml}
        <h6 class="section-title"><i class="bi bi-journal-text"></i> Notes</h6>
        <div class="card" style="border:none;box-shadow:0 1px 4px rgba(0,0,0,.06);">
            <div class="card-body py-2 px-3">
                <textarea id="generalNotes" class="form-control form-control-sm" rows="3" placeholder="General notes..." ${m.status === 'converted' ? 'disabled' : ''}>${m.notes || ''}</textarea>
            </div>
        </div>
        ${m.status !== 'converted' ? `
        <div class="text-end mt-4 mb-3">
            <button class="btn btn-success px-4" onclick="saveAllMeasure(${m.id})"><i class="bi bi-check-circle me-1"></i>Save All Changes</button>
        </div>` : ''}
    `;

    // Update dynamic frame bottom labels after render
    lastFrameSelection = null;
    setTimeout(() => { updateFrameBottomOptions(); }, 10);
}

function escHtml(str) {
    if (!str) return '';
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}

function editMeasure(id) {
    if (!currentMeasureData) return;
    const m = currentMeasureData.measure;
    document.getElementById('editTmCustName').value = m.customer_name || '';
    document.getElementById('editTmCustEmail').value = m.customer_email || '';
    document.getElementById('editTmCustPhone').value = m.customer_phone || '';
    document.getElementById('editTmAddress').value = m.address || '';
    document.getElementById('editTmNotes').value = m.notes || '';
    new bootstrap.Modal(document.getElementById('editTmModal')).show();
}

function saveEditTm() {
    if (!currentMeasureId) return;
    fetch(`/admin/tech-measures/${currentMeasureId}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        body: JSON.stringify({
            customer_name: document.getElementById('editTmCustName').value,
            customer_email: document.getElementById('editTmCustEmail').value,
            customer_phone: document.getElementById('editTmCustPhone').value,
            address: document.getElementById('editTmAddress').value,
            notes: document.getElementById('editTmNotes').value,
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('editTmModal')).hide();
            loadMeasure(currentMeasureId);
        } else alert(data.error || 'Failed to save.');
    })
    .catch(() => alert('Failed to save.'));
}

function openEmailModal() {
    if (!currentMeasureData) return;
    const m = currentMeasureData.measure;
    document.getElementById('emailTo').value = m.customer_email || '';
    document.getElementById('emailSubject').value = `Regarding your tech measure - ${m.customer_name || ''}`;
    document.getElementById('emailMessage').value = '';
    new bootstrap.Modal(document.getElementById('emailModal')).show();
}

function sendEmail() {
    if (!currentMeasureId) return;
    const subject = document.getElementById('emailSubject').value.trim();
    const message = document.getElementById('emailMessage').value.trim();
    if (!subject || !message) { alert('Please fill in both subject and message.'); return; }

    fetch(`/admin/tech-measures/${currentMeasureId}/send-email`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        body: JSON.stringify({ subject, message })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('emailModal')).hide();
            alert(data.message);
        } else alert(data.error || 'Failed to send email.');
    })
    .catch(() => alert('Failed to send email.'));
}

// VIP Master options for dropdowns
const unitOptions = @json($unitOptions);
const frameTypeOptions = @json($frameTypeOptions);
const gridOptions = @json($gridOptions);
const patternOptions = @json($patternOptions);
const installationTypes = @json($installationTypes);

function addItem(measureId) {
    const qty = parseInt(document.getElementById('addQty')?.value || 1);
    const width = document.getElementById('addWidth')?.value?.trim();
    const height = document.getElementById('addHeight')?.value?.trim();
    const config = document.getElementById('addConfig')?.value || null;
    const room = document.getElementById('addRoom')?.value?.trim();
    const notes = document.getElementById('addNotes')?.value?.trim();

    fetch(`/admin/tech-measures/${measureId}/item`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        body: JSON.stringify({
            room_label: room,
            description: config || null,
            series_type: config,
            width: width || null,
            height: height || null,
            qty: qty,
            notes: notes,
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
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

    const sel = document.getElementById('editItemConfig');
    sel.innerHTML = '<option value="">— Select —</option>';
    unitOptions.forEach(o => {
        const opt = document.createElement('option');
        opt.value = o.name;
        opt.textContent = o.name;
        if (o.name === item.description) opt.selected = true;
        sel.appendChild(opt);
    });

    new bootstrap.Modal(document.getElementById('editItemModal')).show();
}

function saveEditItem() {
    const measureId = document.getElementById('editItemMeasureId').value;
    const itemId = document.getElementById('editItemId').value;
    const config = document.getElementById('editItemConfig').value;

    fetch(`/admin/tech-measures/${measureId}/item/${itemId}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        body: JSON.stringify({
            qty: parseInt(document.getElementById('editItemQty').value) || 1,
            width: document.getElementById('editItemWidth').value.trim() || null,
            height: document.getElementById('editItemHeight').value.trim() || null,
            description: config || null,
            series_type: config,
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

function removeItem(measureId, itemId) {
    if (!confirm('Remove this measurement?')) return;
    fetch(`/admin/tech-measures/${measureId}/item/${itemId}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => { if (data.success) loadMeasure(measureId); })
    .catch(() => alert('Failed to remove.'));
}

function saveFrameType(measureId) {
    const frameType = document.getElementById('globalFrame')?.value || null;
    fetch(`/admin/tech-measures/${measureId}/notes`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        body: JSON.stringify({ frame_type: frameType })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success && currentMeasureData) currentMeasureData.measure.frame_type = frameType;
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
    fetch(`/admin/tech-measures/${measureId}/notes`, {
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

function saveFrameAndGrids(measureId, section) {
    const btn = event?.target?.closest('button') || event?.target;

    if (section === 'frame') {
        const frameType = document.getElementById('globalFrame')?.value || null;
        const retrofitBottom = document.getElementById('frameAlt1')?.checked ? 1 : 0;
        const blockBottom = document.getElementById('frameAlt2')?.checked ? 1 : 0;
        fetch(`/admin/tech-measures/${measureId}/notes`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify({ frame_type: frameType, retrofit_bottom_only: retrofitBottom, block_frame_bottom: blockBottom })
        }).then(r => r.json()).then(data => {
            if (data.success && currentMeasureData) {
                currentMeasureData.measure.frame_type = frameType;
                currentMeasureData.measure.retrofit_bottom_only = retrofitBottom;
                currentMeasureData.measure.block_frame_bottom = blockBottom;
            }
            flashBtn(btn);
        }).catch(() => alert('Failed to save.'));
    } else {
        const hasGrids = document.getElementById('gridsYes')?.checked ? 1 : 0;
        const gridList = document.getElementById('gridList')?.value || null;
        const gridPattern = document.getElementById('gridPattern')?.value || null;
        fetch(`/admin/tech-measures/${measureId}/grids`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify({ has_grids: hasGrids, grid_list: gridList, grid_pattern: gridPattern })
        }).then(r => r.json()).then(data => {
            if (data.success && currentMeasureData) {
                currentMeasureData.measure.has_grids = hasGrids;
                currentMeasureData.measure.grid_list = gridList;
                currentMeasureData.measure.grid_pattern = gridPattern;
            }
            flashBtn(btn);
        }).catch(() => alert('Failed to save.'));
    }
}

function flashBtn(btn) {
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

    fetch(`/admin/tech-measures/${measureId}/grids`, {
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

function saveNotes(measureId) {
    const notes = document.getElementById('generalNotes')?.value;
    return fetch(`/admin/tech-measures/${measureId}/notes`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        body: JSON.stringify({ notes })
    }).then(r => r.json());
}

function saveAllMeasure(measureId) {
    const btn = event?.target?.closest('button') || event?.target;
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Saving...'; }

    // Gather all data
    const frameType = document.getElementById('globalFrame')?.value || null;
    const retrofitBottom = document.getElementById('frameAlt1')?.checked ? 1 : 0;
    const blockBottom = document.getElementById('frameAlt2')?.checked ? 1 : 0;
    const hasGrids = document.getElementById('gridsYes')?.checked ? 1 : 0;
    const gridList = document.getElementById('gridList')?.value || null;
    const gridPattern = document.getElementById('gridPattern')?.value || null;
    const notes = document.getElementById('generalNotes')?.value;

    // Fire all saves in parallel
    Promise.all([
        fetch(`/admin/tech-measures/${measureId}/notes`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify({ frame_type: frameType, retrofit_bottom_only: retrofitBottom, block_frame_bottom: blockBottom, notes: notes })
        }).then(r => r.json()),
        fetch(`/admin/tech-measures/${measureId}/grids`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify({ has_grids: hasGrids, grid_list: gridList, grid_pattern: gridPattern })
        }).then(r => r.json())
    ]).then(([notesRes, gridsRes]) => {
        if (currentMeasureData) {
            currentMeasureData.measure.frame_type = frameType;
            currentMeasureData.measure.retrofit_bottom_only = retrofitBottom;
            currentMeasureData.measure.block_frame_bottom = blockBottom;
            currentMeasureData.measure.notes = notes;
            currentMeasureData.measure.has_grids = hasGrids;
            currentMeasureData.measure.grid_list = gridList;
            currentMeasureData.measure.grid_pattern = gridPattern;
        }
        if (btn) {
            btn.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i>All Changes Saved!';
            btn.classList.replace('btn-success', 'btn-outline-success');
            setTimeout(() => { btn.disabled = false; btn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Save All Changes'; btn.classList.replace('btn-outline-success', 'btn-success'); }, 2000);
        }
    }).catch(() => {
        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Save All Changes'; }
        alert('Failed to save some changes.');
    });
}

function uploadItemPhoto(measureId, itemId) {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/*';
    input.onchange = function() {
        const formData = new FormData();
        formData.append('photo', this.files[0]);
        formData.append('item_id', itemId);

        fetch(`/admin/tech-measures/${measureId}/photo`, {
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

function uploadGeneralPhoto(measureId) {
    const fileInput = document.getElementById('generalPhotoFile');
    const caption = document.getElementById('generalPhotoCaption')?.value?.trim() || '';
    if (!fileInput?.files?.length) { alert('Please select a photo.'); return; }

    const formData = new FormData();
    formData.append('photo', fileInput.files[0]);
    if (caption) formData.append('caption', caption);

    fetch(`/admin/tech-measures/${measureId}/photo`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        body: formData
    })
    .then(r => r.json())
    .then(data => { if (data.success) loadMeasure(measureId); })
    .catch(() => alert('Failed to upload photo.'));
}

function deletePhoto(measureId, photoId) {
    if (!confirm('Delete this photo?')) return;
    fetch(`/admin/tech-measures/${measureId}/photo/${photoId}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => { if (data.success) loadMeasure(measureId); })
    .catch(() => alert('Failed to delete photo.'));
}

function downloadPdf(measureId) {
    window.open(`/installer/tech-measures/${measureId}/pdf`, '_blank');
}

let convertMeasureId = null;
let jobLineItemCounter = 0;

function convertToQuote(measureId) {
    if (!currentMeasureData) return;
    convertMeasureId = measureId;
    jobLineItemCounter = 0;

    // Populate measurements section
    const items = currentMeasureData.items || [];
    const tbody = document.getElementById('jobMeasurementsBody');
    tbody.innerHTML = '';
    items.forEach((item, idx) => {
        tbody.innerHTML += `<tr>
            <td class="text-center text-muted">${idx + 1}</td>
            <td class="text-center">${item.qty || 1}</td>
            <td class="text-nowrap">${escHtml(item.width) || '—'}</td>
            <td class="text-nowrap">${escHtml(item.height) || '—'}</td>
            <td>${escHtml(item.description) || '—'}</td>
            <td>${escHtml(item.room_label) || '—'}</td>
            <td style="font-size:.75rem;">${escHtml(item.notes) || ''}</td>
            <td><input type="number" class="form-control form-control-sm job-measure-price" data-item-id="${item.id}" step="0.01" min="0" placeholder="0.00" oninput="recalcJobTotals()"></td>
        </tr>`;
    });

    // Clear line items and add one default row
    document.getElementById('jobLineItemsBody').innerHTML = '';
    addJobLineItem();

    // Clear PDF and schedule fields
    document.getElementById('jobPdfFile').value = '';
    document.getElementById('jobStartDate').value = '';
    document.getElementById('jobStartTime').value = '';
    document.getElementById('jobEndDate').value = '';
    document.getElementById('jobDuration').value = '';

    recalcJobTotals();
    new bootstrap.Modal(document.getElementById('convertJobModal')).show();
}

function addJobLineItem() {
    jobLineItemCounter++;
    const id = jobLineItemCounter;
    const serviceOpts = installationTypes.map(t => `<option value="${t.id}" data-price="${t.price}">${escHtml(t.name)}</option>`).join('');

    const row = document.createElement('tr');
    row.id = 'jobLine_' + id;
    row.innerHTML = `
        <td><input type="number" class="form-control form-control-sm job-line-qty" data-line="${id}" value="1" min="1" oninput="updateLineTotal(${id})"></td>
        <td><select class="form-select form-select-sm job-line-service" data-line="${id}" onchange="onServiceChange(${id})">
            <option value="">— Select Service —</option>
            ${serviceOpts}
        </select></td>
        <td><input type="number" class="form-control form-control-sm job-line-price" data-line="${id}" step="0.01" min="0" placeholder="0.00" oninput="updateLineTotal(${id})"></td>
        <td class="text-end fw-semibold" style="font-size:.85rem; padding-top:.6rem;" id="lineTotal_${id}">$0.00</td>
        <td class="text-center"><button class="btn btn-sm text-danger p-0" onclick="removeJobLineItem(${id})"><i class="bi bi-x-lg" style="font-size:.65rem;"></i></button></td>
    `;
    document.getElementById('jobLineItemsBody').appendChild(row);
}

function removeJobLineItem(id) {
    const row = document.getElementById('jobLine_' + id);
    if (row) row.remove();
    recalcJobTotals();
}

function onServiceChange(lineId) {
    const sel = document.querySelector(`.job-line-service[data-line="${lineId}"]`);
    const opt = sel.options[sel.selectedIndex];
    const price = opt?.dataset?.price || 0;
    document.querySelector(`.job-line-price[data-line="${lineId}"]`).value = parseFloat(price).toFixed(2);
    updateLineTotal(lineId);
}

function updateLineTotal(lineId) {
    const qty = parseInt(document.querySelector(`.job-line-qty[data-line="${lineId}"]`)?.value) || 0;
    const price = parseFloat(document.querySelector(`.job-line-price[data-line="${lineId}"]`)?.value) || 0;
    const total = qty * price;
    document.getElementById('lineTotal_' + lineId).textContent = '$' + total.toFixed(2);
    recalcJobTotals();
}

function recalcJobTotals() {
    // Line items total
    let lineTotal = 0;
    document.querySelectorAll('[id^="lineTotal_"]').forEach(el => {
        lineTotal += parseFloat(el.textContent.replace('$', '')) || 0;
    });

    // Measurements total
    let measTotal = 0;
    document.querySelectorAll('.job-measure-price').forEach(inp => {
        measTotal += parseFloat(inp.value) || 0;
    });

    document.getElementById('jobLineTotal').textContent = lineTotal.toFixed(2);
    document.getElementById('jobMeasurementsTotal').textContent = measTotal.toFixed(2);
    document.getElementById('jobGrandTotal').textContent = (lineTotal + measTotal).toFixed(2);
}

function submitConvertToJob() {
    // Validate PDF
    const pdfInput = document.getElementById('jobPdfFile');
    if (!pdfInput.files.length) {
        alert('Please attach a PDF document. This is required.');
        pdfInput.focus();
        return;
    }

    // Collect line items
    const lineItems = [];
    document.querySelectorAll('#jobLineItemsBody tr').forEach(row => {
        const lineId = row.id.replace('jobLine_', '');
        const qty = parseInt(row.querySelector('.job-line-qty')?.value) || 0;
        const serviceSelect = row.querySelector('.job-line-service');
        const serviceId = serviceSelect?.value;
        const serviceName = serviceSelect?.options[serviceSelect.selectedIndex]?.text || '';
        const unitPrice = parseFloat(row.querySelector('.job-line-price')?.value) || 0;
        if (serviceId && qty > 0) {
            lineItems.push({ qty, service_id: serviceId, service_name: serviceName, unit_price: unitPrice, total: qty * unitPrice });
        }
    });

    // Collect measurement prices
    const measurementPrices = [];
    document.querySelectorAll('.job-measure-price').forEach(inp => {
        measurementPrices.push({ item_id: inp.dataset.itemId, price: parseFloat(inp.value) || 0 });
    });

    const formData = new FormData();
    formData.append('pdf', pdfInput.files[0]);
    formData.append('line_items', JSON.stringify(lineItems));
    formData.append('measurement_prices', JSON.stringify(measurementPrices));

    // Schedule fields
    const startDate = document.getElementById('jobStartDate').value;
    const startTime = document.getElementById('jobStartTime').value;
    const endDate = document.getElementById('jobEndDate').value;
    const duration = document.getElementById('jobDuration').value.trim();
    if (startDate) formData.append('scheduled_date', startDate);
    if (startTime) formData.append('scheduled_time', startTime);
    if (endDate) formData.append('end_date', endDate);
    if (duration) formData.append('estimated_duration', duration);

    const btn = document.querySelector('#convertJobModal .btn-vip');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Converting...';

    fetch(`/admin/tech-measures/${convertMeasureId}/convert-to-quote`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        body: formData
    })
    .then(r => {
        if (!r.ok) return r.text().then(t => { throw new Error(t); });
        return r.json();
    })
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('convertJobModal')).hide();
            alert(data.message || 'Job created successfully!');
            loadMeasure(convertMeasureId);
        } else {
            alert(data.error || 'Failed to convert.');
        }
    })
    .catch(err => {
        console.error('Convert error:', err);
        alert('Failed to convert: ' + (err.message || 'Unknown error'));
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-tools me-1"></i> Convert to Job';
    });
}
</script>
@endpush
