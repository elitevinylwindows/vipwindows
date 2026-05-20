@extends('layouts.app')
@section('title', 'Installers')

@push('styles')
<style>
    .ins-wrapper {
        display: flex;
        height: calc(100vh - 120px);
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        overflow: hidden;
    }

    /* ── Left Rail ─────────────────────────────────────── */
    .ins-sidebar {
        width: 300px;
        min-width: 300px;
        background: var(--vip-primary);
        color: #fff;
        display: flex;
        flex-direction: column;
    }
    .ins-sidebar-header {
        padding: 14px 16px 12px;
        border-bottom: 1px solid rgba(255,255,255,.1);
        background: rgba(0,0,0,.15);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .ins-sidebar-header h6 {
        margin: 0; font-size: .85rem; font-weight: 700;
        letter-spacing: .5px; color: var(--vip-accent);
    }
    .ins-sidebar-header .btn-new {
        background: var(--vip-accent);
        color: var(--vip-primary);
        border: none;
        font-size: .72rem;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 5px;
        cursor: pointer;
    }
    .ins-sidebar-header .btn-new:hover { background: #d4b35a; color: #000; }

    .ins-filters {
        padding: 10px 12px;
        border-bottom: 1px solid rgba(255,255,255,.08);
    }
    .ins-filters input {
        width: 100%;
        background: rgba(255,255,255,.1);
        border: 1px solid rgba(255,255,255,.15);
        border-radius: 6px;
        padding: 6px 10px;
        color: #fff;
        font-size: .8rem;
    }
    .ins-filters input::placeholder { color: rgba(255,255,255,.4); }
    .ins-filters input:focus { outline: none; border-color: var(--vip-accent); background: rgba(255,255,255,.15); }

    .ins-sidebar-body { flex: 1; overflow-y: auto; padding: 4px 0; }
    .ins-sidebar-body::-webkit-scrollbar { width: 4px; }
    .ins-sidebar-body::-webkit-scrollbar-thumb { background: rgba(255,255,255,.2); border-radius: 4px; }

    .ins-item {
        display: block;
        padding: 10px 14px;
        color: rgba(255,255,255,.75);
        text-decoration: none;
        border-left: 3px solid transparent;
        border-bottom: 1px solid rgba(255,255,255,.05);
        cursor: pointer;
        transition: all .15s;
    }
    .ins-item:hover { background: rgba(255,255,255,.08); color: #fff; }
    .ins-item.active { background: rgba(201,168,76,.12); border-left-color: var(--vip-accent); color: #fff; }
    .ins-item .ins-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2px; }
    .ins-item .ins-name { font-weight: 700; font-size: .82rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 180px; }
    .ins-item.active .ins-name { color: var(--vip-accent); }
    .ins-item .ins-company { font-size: .75rem; opacity: .65; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .ins-item .ins-meta { font-size: .68rem; opacity: .45; margin-top: 2px; }
    .ins-item .ins-badge {
        font-size: .58rem; padding: 2px 6px; border-radius: 3px;
        font-weight: 600; text-transform: uppercase;
        background: rgba(40,167,69,.25); color: #7ddf9b;
    }
    .ins-item .ins-badge.suspended { background: rgba(220,53,69,.25); color: #f5a0a8; }

    .ins-sidebar-footer {
        padding: 10px 14px;
        border-top: 1px solid rgba(255,255,255,.1);
        background: rgba(0,0,0,.1);
        text-align: center;
    }
    .ins-sidebar-footer .val { font-size: 1.1rem; font-weight: 700; color: var(--vip-accent); }
    .ins-sidebar-footer .lbl { font-size: .6rem; text-transform: uppercase; letter-spacing: .5px; opacity: .5; }

    /* ── Main Panel ────────────────────────────────────── */
    .ins-main { flex: 1; display: flex; flex-direction: column; min-width: 0; }
    .ins-toolbar {
        display: flex; align-items: center; gap: 12px;
        padding: 12px 20px; border-bottom: 1px solid #e9ecef;
        background: #fafafa; min-height: 52px;
    }
    .ins-toolbar .ins-title { font-size: 1rem; font-weight: 700; color: var(--vip-primary); margin: 0; }
    .ins-toolbar .ins-sub { font-size: .78rem; color: #999; }
    .ins-toolbar .actions { margin-left: auto; display: flex; gap: 6px; }
    .ins-toolbar .actions .btn { font-size: .78rem; }

    .ins-content { flex: 1; overflow-y: auto; padding: 20px; position: relative; }
    .ins-loader { position: absolute; inset: 0; display: none; align-items: center; justify-content: center; background: rgba(255,255,255,.9); z-index: 10; }
    .ins-loader.show { display: flex; }
    .ins-loader .spinner-border { width: 2rem; height: 2rem; color: var(--vip-accent); }

    .ins-placeholder { display: flex; align-items: center; justify-content: center; flex-direction: column; height: 100%; text-align: center; color: #999; }
    .ins-placeholder i { font-size: 3rem; color: #ddd; margin-bottom: 16px; }
    .ins-placeholder h5 { color: #666; margin-bottom: 8px; }

    .ins-detail-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid #e9ecef; }
    .ins-detail-header h4 { margin: 0; font-weight: 700; color: var(--vip-primary); }

    .ins-info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin-bottom: 24px; }
    .ins-info-card { background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 12px 14px; }
    .ins-info-card .label { font-size: .68rem; text-transform: uppercase; letter-spacing: .5px; color: #888; margin-bottom: 4px; }
    .ins-info-card .value { font-size: .9rem; font-weight: 600; color: #333; }

    .ins-stat-cards { display: flex; gap: 16px; margin-bottom: 24px; }
    .ins-stat-card {
        flex: 1; text-align: center;
        background: #f8f9fa; border: 1px solid #e9ecef;
        border-radius: 8px; padding: 16px;
    }
    .ins-stat-card .sv { font-size: 1.5rem; font-weight: 700; color: var(--vip-primary); }
    .ins-stat-card .sl { font-size: .7rem; text-transform: uppercase; letter-spacing: .5px; color: #888; margin-top: 4px; }
    .ins-stat-card.gold .sv { color: var(--vip-accent); }

    @media (max-width: 991.98px) {
        .ins-wrapper { flex-direction: column; height: auto; min-height: calc(100vh - 120px); }
        .ins-sidebar { width: 100%; min-width: 100%; max-height: 320px; }
        .ins-content { min-height: 400px; }
    }
</style>
@endpush

@section('content')
<div class="p-3">
    <div class="ins-wrapper">

        {{-- ── Left Rail ──────────────────────────────────── --}}
        <div class="ins-sidebar">
            <div class="ins-sidebar-header">
                <h6><i class="bi bi-person-badge me-1"></i> INSTALLERS</h6>
                <button class="btn-new" onclick="new bootstrap.Modal(document.getElementById('addInstallerModal')).show()">
                    <i class="bi bi-plus me-1"></i>Add New
                </button>
            </div>

            <div class="ins-filters">
                <input type="text" id="insSearch" placeholder="Search installers…" oninput="filterInstallers()">
            </div>

            <div class="ins-sidebar-body" id="insList">
                @forelse($installers as $installer)
                    <a class="ins-item"
                       href="#"
                       data-id="{{ $installer->id }}"
                       data-search="{{ strtolower($installer->name . ' ' . $installer->email . ' ' . ($installer->company_name ?? '') . ' ' . ($installer->phone ?? '')) }}"
                       onclick="insLoad(event, this)">
                        <div class="ins-top">
                            <span class="ins-name">{{ $installer->name }}</span>
                            <span class="ins-badge {{ ($installer->status ?? 'active') === 'suspended' ? 'suspended' : '' }}">
                                {{ ucfirst($installer->status ?? 'active') }}
                            </span>
                        </div>
                        <div class="ins-company">
                            <i class="bi bi-building me-1"></i>{{ $installer->company_name ?: 'No company' }}
                        </div>
                        <div class="ins-meta">{{ $installer->email }}</div>
                    </a>
                @empty
                    <div class="text-center py-4 opacity-50">
                        <i class="bi bi-person-badge d-block mb-2" style="font-size:1.5rem"></i>
                        <small>No installers yet</small>
                    </div>
                @endforelse
            </div>

            <div class="ins-sidebar-footer">
                <div class="val">{{ $installers->total() }}</div>
                <div class="lbl">Total Installers</div>
            </div>
        </div>

        {{-- ── Main Panel ─────────────────────────────────── --}}
        <div class="ins-main">
            <div class="ins-toolbar">
                <div>
                    <h5 class="ins-title" id="insTitle">Installer Profile</h5>
                    <span class="ins-sub" id="insSub">Select an installer from the list</span>
                </div>
                <div class="actions" id="insActions" style="display:none">
                    <button class="btn btn-sm btn-outline-primary" id="insEditBtn">
                        <i class="bi bi-pencil me-1"></i>Edit
                    </button>
                    <a href="#" class="btn btn-sm btn-outline-success" id="insEmailBtn">
                        <i class="bi bi-envelope me-1"></i>Email
                    </a>
                    <button class="btn btn-sm btn-outline-danger" id="insDeleteBtn">
                        <i class="bi bi-trash me-1"></i>Remove
                    </button>
                </div>
            </div>

            <div class="ins-content" id="insContent">
                <div class="ins-loader" id="insLoader"><div class="spinner-border"></div></div>

                <div class="ins-placeholder" id="insPlaceholder">
                    <i class="bi bi-person-badge"></i>
                    <h5>Select an Installer</h5>
                    <p class="text-muted small">Click on an installer from the left to view their profile, company details, and activity stats.</p>
                </div>

                <div id="insDetail" style="display:none"></div>
            </div>
        </div>

    </div>
</div>

{{-- Add Installer Modal --}}
<div class="modal fade" id="addInstallerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.installers.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-person-badge me-1"></i> Add New Installer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h6 class="text-muted small fw-bold mb-2">Personal Info</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Full Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" required></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Email <span class="text-danger">*</span></label><input type="email" name="email" class="form-control" required></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control"></div>
                    </div>
                    <hr class="my-2">
                    <h6 class="text-muted small fw-bold mb-2">Company Info</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Company Name</label><input type="text" name="company_name" class="form-control"></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Company Phone</label><input type="text" name="company_phone" class="form-control"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Company Email</label><input type="email" name="company_email" class="form-control"></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Website</label><input type="text" name="company_website" class="form-control" placeholder="https://"></div>
                    </div>
                    <hr class="my-2">
                    <h6 class="text-muted small fw-bold mb-2">Address</h6>
                    <div class="mb-3"><input type="text" name="address" class="form-control" placeholder="Street Address" data-address-autocomplete></div>
                    <div class="row">
                        <div class="col-md-5 mb-3"><input type="text" name="city" class="form-control" placeholder="City"></div>
                        <div class="col-md-4 mb-3"><input type="text" name="state" class="form-control" placeholder="State" value="CA"></div>
                        <div class="col-md-3 mb-3"><input type="text" name="zip" class="form-control" placeholder="ZIP"></div>
                    </div>
                    <div class="mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
                    @if(isset($services) && $services->count())
                    <hr class="my-2">
                    <h6 class="text-muted small fw-bold mb-2">Services</h6>
                    <div class="row">
                        @foreach($services as $svc)
                        <div class="col-md-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="services[]" value="{{ $svc->id }}" id="addSvc{{ $svc->id }}">
                                <label class="form-check-label" for="addSvc{{ $svc->id }}">{{ $svc->name }} <small class="text-muted">({{ $svc->code }})</small></label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-vip"><i class="bi bi-person-badge me-1"></i> Add Installer</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Installer Modal --}}
<div class="modal fade" id="editInstallerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editInstallerForm" method="POST">
                @csrf
                <input type="hidden" name="_method" value="PUT">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil me-1"></i> Edit Installer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Full Name <span class="text-danger">*</span></label><input type="text" name="name" id="eName" class="form-control" required></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Email <span class="text-danger">*</span></label><input type="email" name="email" id="eEmail" class="form-control" required></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Phone</label><input type="text" name="phone" id="ePhone" class="form-control"></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Status</label>
                            <select name="status" id="eStatus" class="form-select">
                                <option value="active">Active</option>
                                <option value="suspended">Suspended</option>
                            </select>
                        </div>
                    </div>
                    <hr class="my-2">
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Company Name</label><input type="text" name="company_name" id="eCompany" class="form-control"></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Company Phone</label><input type="text" name="company_phone" id="eCompanyPhone" class="form-control"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Company Email</label><input type="email" name="company_email" id="eCompanyEmail" class="form-control"></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Website</label><input type="text" name="company_website" id="eWebsite" class="form-control"></div>
                    </div>
                    <hr class="my-2">
                    <div class="mb-3"><input type="text" name="address" id="eAddress" class="form-control" placeholder="Street Address" data-address-autocomplete></div>
                    <div class="row">
                        <div class="col-md-5 mb-3"><input type="text" name="city" id="eCity" class="form-control" placeholder="City"></div>
                        <div class="col-md-4 mb-3"><input type="text" name="state" id="eState" class="form-control" placeholder="State"></div>
                        <div class="col-md-3 mb-3"><input type="text" name="zip" id="eZip" class="form-control" placeholder="ZIP"></div>
                    </div>
                    <div class="mb-3"><label class="form-label">Notes</label><textarea name="notes" id="eNotes" class="form-control" rows="2"></textarea></div>
                    @if(isset($services) && $services->count())
                    <hr class="my-2">
                    <h6 class="text-muted small fw-bold mb-2">Services</h6>
                    <div class="row" id="editServiceChecks">
                        @foreach($services as $svc)
                        <div class="col-md-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input edit-svc-check" type="checkbox" name="services[]" value="{{ $svc->id }}" id="editSvc{{ $svc->id }}">
                                <label class="form-check-label" for="editSvc{{ $svc->id }}">{{ $svc->name }} <small class="text-muted">({{ $svc->code }})</small></label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-vip">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    const csrf = '{{ csrf_token() }}';
    let activeInstallerId = null;
    let activeInstallerData = null;
    let activeInstallerServices = [];

    // ── Search filter ─────────────────────────────────────
    window.filterInstallers = function() {
        const search = document.getElementById('insSearch').value.toLowerCase();
        document.querySelectorAll('.ins-item').forEach(item => {
            item.style.display = (!search || item.dataset.search.includes(search)) ? '' : 'none';
        });
    };

    // ── Load installer detail ─────────────────────────────
    window.insLoad = function(e, el) {
        e.preventDefault();
        const id = el.dataset.id;
        activeInstallerId = id;

        document.querySelectorAll('.ins-item').forEach(i => i.classList.remove('active'));
        el.classList.add('active');

        const loader = document.getElementById('insLoader');
        const detail = document.getElementById('insDetail');
        const placeholder = document.getElementById('insPlaceholder');
        loader.classList.add('show');
        placeholder.style.display = 'none';
        detail.style.display = 'none';

        fetch(`/admin/installers/${id}`, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf } })
        .then(r => r.json())
        .then(data => {
            activeInstallerData = data.installer;
            activeInstallerServices = data.services || [];
            renderInstallerDetail(data);
            loader.classList.remove('show');
            detail.style.display = 'block';

            document.getElementById('insTitle').textContent = data.installer.name;
            document.getElementById('insSub').textContent = data.installer.company_name || data.installer.email;
            document.getElementById('insActions').style.display = 'flex';
            document.getElementById('insEmailBtn').href = `/admin/email/compose?to=${encodeURIComponent(data.installer.email)}`;
        })
        .catch(err => {
            loader.classList.remove('show');
            detail.innerHTML = '<div class="alert alert-danger">Failed to load installer details.</div>';
            detail.style.display = 'block';
        });
    };

    function renderInstallerDetail(data) {
        const i = data.installer;
        const s = data.stats;
        const services = data.services || [];
        const pay = data.pay || {};
        const since = i.created_at ? new Date(i.created_at).toLocaleDateString('en-US', { year:'numeric', month:'short', day:'numeric' }) : '—';
        const address = [i.address, i.city, i.state, i.zip].filter(Boolean).join(', ') || '—';
        const statusBadge = (i.status || 'active') === 'active'
            ? '<span class="badge bg-success">Active</span>'
            : '<span class="badge bg-danger">Suspended</span>';

        const fmtHM = (mins) => { const h = Math.floor(mins/60), m = mins%60; return h+'h '+m+'m'; };

        let html = `
            <div class="ins-detail-header">
                <div>
                    <h4>${esc(i.name)} ${statusBadge}</h4>
                    <div class="text-muted" style="font-size:.85rem">Installer since ${since}</div>
                </div>
                ${(i.company_logo_dark || i.company_logo_light) ? `<img src="/uploads/installer-logos/${i.company_logo_dark || i.company_logo_light}" style="height:40px;border-radius:6px;">` : ''}
            </div>

            <div class="ins-stat-cards">
                <div class="ins-stat-card gold"><div class="sv">${s.quotes}</div><div class="sl">Quotes Created</div></div>
                <div class="ins-stat-card"><div class="sv">${s.jobs}</div><div class="sl">Jobs Assigned</div></div>
                <div class="ins-stat-card"><div class="sv">${s.invoices}</div><div class="sl">Invoices Created</div></div>
            </div>
        `;

        // ─── Pay Overview (always show) ───
        html += `
            <div style="font-size:.7rem;text-transform:uppercase;color:#888;letter-spacing:.5px;margin-bottom:8px;margin-top:4px;">
                <i class="bi bi-wallet2 me-1"></i> Pay Overview
            </div>
            <div class="ins-stat-cards" style="margin-bottom:1rem;">
                <div class="ins-stat-card" style="background:linear-gradient(135deg,#198754,#157347);color:#fff;">
                    <div class="sv">$${Number(pay.this_month || 0).toFixed(2)}</div>
                    <div class="sl" style="color:rgba(255,255,255,.7);">This Month · ${fmtHM(pay.this_month_minutes || 0)}</div>
                </div>
                <div class="ins-stat-card" style="background:linear-gradient(135deg,#6f42c1,#59359a);color:#fff;">
                    <div class="sv">$${Number(pay.all_time || 0).toFixed(2)}</div>
                    <div class="sl" style="color:rgba(255,255,255,.7);">All Time · ${fmtHM(pay.all_time_minutes || 0)}</div>
                </div>
            </div>
        `;

        // ─── Monthly Breakdown ───
        if (pay.monthly && pay.monthly.length > 0) {
            html += `<div style="font-size:.7rem;text-transform:uppercase;color:#888;letter-spacing:.5px;margin-bottom:8px;">
                <i class="bi bi-bar-chart me-1"></i> Monthly Breakdown
            </div>
            <div class="p-3 bg-light rounded border mb-3">
                <table style="width:100%;border-collapse:collapse;font-size:.85rem;">
                    <thead><tr style="border-bottom:2px solid rgba(0,0,0,.08);">
                        <th style="text-align:left;padding:4px 8px;font-size:.7rem;text-transform:uppercase;color:#888;">Month</th>
                        <th style="text-align:center;padding:4px 8px;font-size:.7rem;text-transform:uppercase;color:#888;">Jobs</th>
                        <th style="text-align:center;padding:4px 8px;font-size:.7rem;text-transform:uppercase;color:#888;">Hours</th>
                        <th style="text-align:right;padding:4px 8px;font-size:.7rem;text-transform:uppercase;color:#888;">Earnings</th>
                    </tr></thead><tbody>`;
            pay.monthly.forEach(m => {
                const monthName = new Date(m.month + '-01').toLocaleDateString('en-US', {year:'numeric', month:'short'});
                html += `<tr style="border-bottom:1px solid rgba(0,0,0,.04);">
                    <td style="padding:6px 8px;font-weight:600;">${monthName}</td>
                    <td style="padding:6px 8px;text-align:center;">${m.total_jobs}</td>
                    <td style="padding:6px 8px;text-align:center;">${fmtHM(m.total_minutes)}</td>
                    <td style="padding:6px 8px;text-align:right;font-weight:700;color:#198754;">$${Number(m.total_earnings).toFixed(2)}</td>
                </tr>`;
            });
            html += `</tbody></table></div>`;
        }

        // ─── Recent Time Logs ───
        if (pay.recent_logs && pay.recent_logs.length > 0) {
            html += `<div style="font-size:.7rem;text-transform:uppercase;color:#888;letter-spacing:.5px;margin-bottom:8px;">
                <i class="bi bi-clock-history me-1"></i> Recent Time Logs
            </div>
            <div class="p-3 bg-light rounded border mb-3">
                <table style="width:100%;border-collapse:collapse;font-size:.82rem;">
                    <thead><tr style="border-bottom:2px solid rgba(0,0,0,.08);">
                        <th style="text-align:left;padding:4px 6px;font-size:.65rem;text-transform:uppercase;color:#888;">Date</th>
                        <th style="text-align:left;padding:4px 6px;font-size:.65rem;text-transform:uppercase;color:#888;">Job</th>
                        <th style="text-align:left;padding:4px 6px;font-size:.65rem;text-transform:uppercase;color:#888;">Service</th>
                        <th style="text-align:center;padding:4px 6px;font-size:.65rem;text-transform:uppercase;color:#888;">Duration</th>
                        <th style="text-align:right;padding:4px 6px;font-size:.65rem;text-transform:uppercase;color:#888;">Pay</th>
                    </tr></thead><tbody>`;
            pay.recent_logs.forEach(l => {
                html += `<tr style="border-bottom:1px solid rgba(0,0,0,.04);">
                    <td style="padding:5px 6px;">${esc(l.date)}</td>
                    <td style="padding:5px 6px;font-weight:600;">${esc(l.job_number)}</td>
                    <td style="padding:5px 6px;"><span class="badge" style="background:${l.service_color};font-size:.6rem;">${esc(l.service_name)}</span></td>
                    <td style="padding:5px 6px;text-align:center;font-weight:600;">${fmtHM(l.total_minutes)}</td>
                    <td style="padding:5px 6px;text-align:right;font-weight:700;color:#198754;">${l.earnings > 0 ? '$'+Number(l.earnings).toFixed(2) : '<span style="color:#aaa;">$0.00</span>'}</td>
                </tr>`;
            });
            html += `</tbody></table></div>`;
        }

        // ─── Contact Info ───
        html += `
            <div class="ins-info-grid">
                <div class="ins-info-card"><div class="label">Email</div><div class="value">${esc(i.email)}</div></div>
                <div class="ins-info-card"><div class="label">Phone</div><div class="value">${esc(i.phone || '—')}</div></div>
                <div class="ins-info-card"><div class="label">Address</div><div class="value">${esc(address)}</div></div>
                <div class="ins-info-card"><div class="label">Company Name</div><div class="value">${esc(i.company_name || '—')}</div></div>
                <div class="ins-info-card"><div class="label">Company Phone</div><div class="value">${esc(i.company_phone || '—')}</div></div>
                <div class="ins-info-card"><div class="label">Company Email</div><div class="value">${esc(i.company_email || '—')}</div></div>
                <div class="ins-info-card"><div class="label">Website</div><div class="value">${i.company_website ? `<a href="${esc(i.company_website)}" target="_blank">${esc(i.company_website)}</a>` : '—'}</div></div>
            </div>
        `;

        // Services section
        if (services.length > 0) {
            html += `<div class="p-3 bg-light rounded border mb-3">
                <div style="font-size:.7rem;text-transform:uppercase;color:#888;letter-spacing:.5px;margin-bottom:8px"><i class="bi bi-wrench me-1"></i>Assigned Services</div>
                <div class="d-flex flex-wrap gap-2">
                    ${services.map(svc => `<span class="badge" style="background:rgba(201,168,76,.15);color:#8b6914;font-size:.78rem;padding:5px 10px;border-radius:4px;">
                        ${esc(svc.name)} ${svc.custom_price ? `<small>($${parseFloat(svc.custom_price).toFixed(2)})</small>` : `<small>($${parseFloat(svc.base_price).toFixed(2)})</small>`}
                    </span>`).join('')}
                </div>
            </div>`;
        }

        if (i.notes) {
            html += `<div class="p-3 bg-light rounded border">
                <div style="font-size:.7rem;text-transform:uppercase;color:#888;letter-spacing:.5px;margin-bottom:4px">Notes</div>
                <div style="font-size:.85rem">${esc(i.notes)}</div>
            </div>`;
        }

        document.getElementById('insDetail').innerHTML = html;
    }

    function esc(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // ── Edit installer ────────────────────────────────────
    document.getElementById('insEditBtn').addEventListener('click', function() {
        if (!activeInstallerData) return;
        const i = activeInstallerData;
        document.getElementById('editInstallerForm').action = `/admin/installers/${i.id}`;
        document.getElementById('eName').value = i.name || '';
        document.getElementById('eEmail').value = i.email || '';
        document.getElementById('ePhone').value = i.phone || '';
        document.getElementById('eStatus').value = i.status || 'active';
        document.getElementById('eCompany').value = i.company_name || '';
        document.getElementById('eCompanyPhone').value = i.company_phone || '';
        document.getElementById('eCompanyEmail').value = i.company_email || '';
        document.getElementById('eWebsite').value = i.company_website || '';
        document.getElementById('eAddress').value = i.address || '';
        document.getElementById('eCity').value = i.city || '';
        document.getElementById('eState').value = i.state || '';
        document.getElementById('eZip').value = i.zip || '';
        document.getElementById('eNotes').value = i.notes || '';

        // Pre-check assigned services
        const svcIds = activeInstallerServices.map(s => String(s.id));
        document.querySelectorAll('.edit-svc-check').forEach(cb => {
            cb.checked = svcIds.includes(cb.value);
        });

        new bootstrap.Modal(document.getElementById('editInstallerModal')).show();
    });

    // ── Delete installer ──────────────────────────────────
    document.getElementById('insDeleteBtn').addEventListener('click', function() {
        if (!activeInstallerId || !confirm('Remove this installer? This cannot be undone.')) return;
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/installers/${activeInstallerId}`;
        form.innerHTML = `<input type="hidden" name="_token" value="${csrf}"><input type="hidden" name="_method" value="DELETE">`;
        document.body.appendChild(form);
        form.submit();
    });

})();
</script>
@endpush
