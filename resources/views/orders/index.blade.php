@extends('layouts.app')
@section('title', 'Installation Orders')

@push('styles')
<style>
    .ord-wrapper {
        display: flex;
        height: calc(100vh - 120px);
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        overflow: hidden;
    }

    /* ── Left Rail ─────────────────────────────────────── */
    .ord-sidebar {
        width: 320px;
        min-width: 320px;
        background: var(--vip-primary);
        color: #fff;
        display: flex;
        flex-direction: column;
    }
    .ord-sidebar-header {
        padding: 14px 16px 12px;
        border-bottom: 1px solid rgba(255,255,255,.1);
        background: rgba(0,0,0,.15);
    }
    .ord-sidebar-header h6 {
        margin: 0; font-size: .85rem; font-weight: 700;
        letter-spacing: .5px; color: var(--vip-accent);
    }

    .ord-filters {
        padding: 10px 12px;
        border-bottom: 1px solid rgba(255,255,255,.08);
    }
    .ord-filters input {
        width: 100%;
        background: rgba(255,255,255,.1);
        border: 1px solid rgba(255,255,255,.15);
        border-radius: 6px;
        padding: 6px 10px;
        color: #fff;
        font-size: .8rem;
        margin-bottom: 8px;
    }
    .ord-filters input::placeholder { color: rgba(255,255,255,.4); }
    .ord-filters input:focus { outline: none; border-color: var(--vip-accent); background: rgba(255,255,255,.15); }

    .ord-status-tabs {
        display: flex; flex-wrap: wrap; gap: 4px;
    }
    .ord-status-tab {
        padding: 3px 8px;
        font-size: .64rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .4px;
        border-radius: 4px;
        cursor: pointer;
        color: rgba(255,255,255,.5);
        background: rgba(255,255,255,.06);
        border: 1px solid transparent;
        transition: all .15s;
    }
    .ord-status-tab:hover { color: rgba(255,255,255,.8); background: rgba(255,255,255,.1); }
    .ord-status-tab.active { color: var(--vip-accent); background: rgba(201,168,76,.15); border-color: rgba(201,168,76,.3); }

    .ord-sidebar-body {
        flex: 1; overflow-y: auto; padding: 4px 0;
    }
    .ord-sidebar-body::-webkit-scrollbar { width: 4px; }
    .ord-sidebar-body::-webkit-scrollbar-thumb { background: rgba(255,255,255,.2); border-radius: 4px; }

    .ord-item {
        display: block;
        padding: 10px 14px;
        color: rgba(255,255,255,.75);
        text-decoration: none;
        border-left: 3px solid transparent;
        border-bottom: 1px solid rgba(255,255,255,.05);
        cursor: pointer;
        transition: all .15s;
    }
    .ord-item:hover { background: rgba(255,255,255,.08); color: #fff; }
    .ord-item.active { background: rgba(201,168,76,.12); border-left-color: var(--vip-accent); color: #fff; }
    .ord-item .oi-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2px; }
    .ord-item .oi-id { font-weight: 700; font-size: .82rem; }
    .ord-item.active .oi-id { color: var(--vip-accent); }
    .ord-item .oi-badge {
        font-size: .58rem; padding: 2px 6px; border-radius: 3px;
        font-weight: 600; text-transform: uppercase; letter-spacing: .3px;
    }
    .oi-badge.pending { background: rgba(255,193,7,.25); color: #ffc107; }
    .oi-badge.scheduled { background: rgba(0,123,255,.25); color: #6db8ff; }
    .oi-badge.in_progress { background: rgba(13,110,253,.3); color: #6ea8fe; }
    .oi-badge.completed { background: rgba(40,167,69,.25); color: #7ddf9b; }
    .oi-badge.cancelled { background: rgba(108,117,125,.3); color: #ccc; }
    .ord-item .oi-customer { font-size: .78rem; opacity: .8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .ord-item .oi-meta { display: flex; justify-content: space-between; font-size: .68rem; opacity: .5; margin-top: 2px; }

    .ord-sidebar-footer {
        padding: 10px 14px;
        border-top: 1px solid rgba(255,255,255,.1);
        background: rgba(0,0,0,.1);
        display: flex; gap: 6px;
    }
    .ord-stat { flex: 1; text-align: center; }
    .ord-stat .val { font-size: .95rem; font-weight: 700; color: var(--vip-accent); }
    .ord-stat .lbl { font-size: .58rem; text-transform: uppercase; letter-spacing: .5px; opacity: .5; }

    /* ── Main Panel ────────────────────────────────────── */
    .ord-main { flex: 1; display: flex; flex-direction: column; min-width: 0; }
    .ord-toolbar {
        display: flex; align-items: center; gap: 12px;
        padding: 12px 20px; border-bottom: 1px solid #e9ecef;
        background: #fafafa; min-height: 52px;
    }
    .ord-toolbar .ord-title { font-size: 1rem; font-weight: 700; color: var(--vip-primary); margin: 0; }
    .ord-toolbar .ord-sub { font-size: .78rem; color: #999; }
    .ord-toolbar .actions { margin-left: auto; display: flex; gap: 6px; }
    .ord-toolbar .actions .btn { font-size: .78rem; }

    .ord-content { flex: 1; overflow-y: auto; padding: 20px; position: relative; }
    .ord-loader { position: absolute; inset: 0; display: none; align-items: center; justify-content: center; background: rgba(255,255,255,.9); z-index: 10; }
    .ord-loader.show { display: flex; }
    .ord-loader .spinner-border { width: 2rem; height: 2rem; color: var(--vip-accent); }

    .ord-placeholder { display: flex; align-items: center; justify-content: center; flex-direction: column; height: 100%; text-align: center; color: #999; }
    .ord-placeholder i { font-size: 3rem; color: #ddd; margin-bottom: 16px; }
    .ord-placeholder h5 { color: #666; margin-bottom: 8px; }

    .ord-detail-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid #e9ecef; }
    .ord-detail-header h4 { margin: 0; font-weight: 700; color: var(--vip-primary); }

    .ord-info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; margin-bottom: 24px; }
    .ord-info-card { background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 12px 14px; }
    .ord-info-card .label { font-size: .68rem; text-transform: uppercase; letter-spacing: .5px; color: #888; margin-bottom: 4px; }
    .ord-info-card .value { font-size: .9rem; font-weight: 600; color: #333; }

    .ord-items-table th { font-size: .72rem; text-transform: uppercase; letter-spacing: .5px; color: #666; }
    .ord-items-table td { font-size: .85rem; vertical-align: middle; }

    /* Status timeline */
    .ord-timeline { display: flex; gap: 0; margin-bottom: 24px; }
    .ord-timeline-step { flex: 1; text-align: center; position: relative; padding-top: 24px; }
    .ord-timeline-step::before {
        content: '';
        position: absolute;
        top: 10px;
        left: 0; right: 0;
        height: 3px;
        background: #e9ecef;
    }
    .ord-timeline-step:first-child::before { left: 50%; }
    .ord-timeline-step:last-child::before { right: 50%; }
    .ord-timeline-step .step-dot {
        width: 20px; height: 20px;
        border-radius: 50%;
        background: #e9ecef;
        border: 3px solid #fff;
        position: absolute;
        top: 2px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 2;
    }
    .ord-timeline-step.active .step-dot { background: var(--vip-accent); }
    .ord-timeline-step.done .step-dot { background: #28a745; }
    .ord-timeline-step.done::before { background: #28a745; }
    .ord-timeline-step.active::before { background: var(--vip-accent); }
    .ord-timeline-step .step-label { font-size: .68rem; font-weight: 600; color: #999; text-transform: uppercase; letter-spacing: .3px; }
    .ord-timeline-step.active .step-label { color: var(--vip-accent); }
    .ord-timeline-step.done .step-label { color: #28a745; }

    @media (max-width: 991.98px) {
        .ord-wrapper { flex-direction: column; height: auto; min-height: calc(100vh - 120px); }
        .ord-sidebar { width: 100%; min-width: 100%; max-height: 320px; }
        .ord-content { min-height: 400px; }
    }
</style>
@endpush

@section('content')
<div class="p-3">
    <div class="ord-wrapper">

        {{-- ── Left Rail ──────────────────────────────────── --}}
        <div class="ord-sidebar">
            <div class="ord-sidebar-header">
                <h6><i class="bi bi-clipboard-check me-1"></i> INSTALLATION ORDERS</h6>
            </div>

            <div class="ord-filters">
                <input type="text" id="ordSearch" placeholder="Search orders…">
                <div class="ord-status-tabs">
                    <div class="ord-status-tab {{ $status === 'all' ? 'active' : '' }}" data-status="all">All</div>
                    <div class="ord-status-tab {{ $status === 'pending' ? 'active' : '' }}" data-status="pending">Pending</div>
                    <div class="ord-status-tab {{ $status === 'scheduled' ? 'active' : '' }}" data-status="scheduled">Scheduled</div>
                    <div class="ord-status-tab {{ $status === 'in_progress' ? 'active' : '' }}" data-status="in_progress">In Progress</div>
                    <div class="ord-status-tab {{ $status === 'completed' ? 'active' : '' }}" data-status="completed">Done</div>
                </div>
            </div>

            <div class="ord-sidebar-body" id="ordList">
                @forelse($orders as $order)
                    @php
                        $tech = $technicians->firstWhere('id', $order->technician_id);
                    @endphp
                    <a class="ord-item"
                       href="#"
                       data-id="{{ $order->id }}"
                       data-status="{{ $order->status }}"
                       data-search="{{ strtolower($order->id . ' ' . ($order->customer_name ?? '') . ' ' . ($order->install_city ?? '') . ' ' . ($tech?->name ?? '')) }}"
                       onclick="ordLoad(event, this)">
                        <div class="oi-top">
                            <span class="oi-id">#{{ $order->id }}</span>
                            <span class="oi-badge {{ $order->status }}">{{ ucwords(str_replace('_', ' ', $order->status)) }}</span>
                        </div>
                        <div class="oi-customer">{{ $order->customer_name }}</div>
                        <div class="oi-meta">
                            <span>{{ $order->install_city ?? '—' }}</span>
                            <span>{{ $order->scheduled_date ? \Carbon\Carbon::parse($order->scheduled_date)->format('M d') : 'Not scheduled' }}</span>
                        </div>
                    </a>
                @empty
                    <div class="text-center py-4 opacity-50">
                        <i class="bi bi-clipboard-x d-block mb-2" style="font-size:1.5rem"></i>
                        <small>No orders found</small>
                    </div>
                @endforelse
            </div>

            <div class="ord-sidebar-footer">
                @php
                    $coll = $orders->getCollection();
                    $pendingCount = $coll->where('status', 'pending')->count();
                    $scheduledCount = $coll->where('status', 'scheduled')->count();
                    $completedCount = $coll->where('status', 'completed')->count();
                @endphp
                <div class="ord-stat"><div class="val">{{ $orders->total() }}</div><div class="lbl">Total</div></div>
                <div class="ord-stat"><div class="val">{{ $pendingCount }}</div><div class="lbl">Pending</div></div>
                <div class="ord-stat"><div class="val">{{ $scheduledCount }}</div><div class="lbl">Scheduled</div></div>
                <div class="ord-stat"><div class="val">{{ $completedCount }}</div><div class="lbl">Done</div></div>
            </div>
        </div>

        {{-- ── Main Panel ─────────────────────────────────── --}}
        <div class="ord-main">
            <div class="ord-toolbar">
                <div>
                    <h5 class="ord-title" id="ordTitle">Order Details</h5>
                    <span class="ord-sub" id="ordSub">Select an order from the list</span>
                </div>
                <div class="actions" id="ordActions" style="display:none">
                    <select class="form-select form-select-sm" id="ordStatusSelect" style="width:auto;font-size:.75rem;">
                        <option value="pending">Pending</option>
                        <option value="scheduled">Scheduled</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <button class="btn btn-sm btn-vip" id="ordSaveStatusBtn">
                        <i class="bi bi-check2 me-1"></i>Update
                    </button>
                </div>
            </div>

            <div class="ord-content" id="ordContent">
                <div class="ord-loader" id="ordLoader"><div class="spinner-border"></div></div>

                <div class="ord-placeholder" id="ordPlaceholder">
                    <i class="bi bi-clipboard-check"></i>
                    <h5>Select an Order</h5>
                    <p class="text-muted small">Click on an order from the left to view its full details, update status, and manage the installation.</p>
                </div>

                <div id="ordDetail" style="display:none"></div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    const csrf = '{{ csrf_token() }}';
    let activeOrderId = null;

    const statusSteps = ['pending', 'scheduled', 'in_progress', 'completed'];

    // ── Filter tabs ───────────────────────────────────────
    document.querySelectorAll('.ord-status-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.ord-status-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            filterOrders();
        });
    });

    document.getElementById('ordSearch').addEventListener('input', filterOrders);

    function filterOrders() {
        const search = document.getElementById('ordSearch').value.toLowerCase();
        const status = document.querySelector('.ord-status-tab.active')?.dataset.status || 'all';
        document.querySelectorAll('.ord-item').forEach(item => {
            const matchSearch = !search || item.dataset.search.includes(search);
            const matchStatus = status === 'all' || item.dataset.status === status;
            item.style.display = (matchSearch && matchStatus) ? '' : 'none';
        });
    }

    // ── Load order detail ─────────────────────────────────
    window.ordLoad = function(e, el) {
        e.preventDefault();
        const id = el.dataset.id;
        activeOrderId = id;

        document.querySelectorAll('.ord-item').forEach(i => i.classList.remove('active'));
        el.classList.add('active');

        const loader = document.getElementById('ordLoader');
        const detail = document.getElementById('ordDetail');
        const placeholder = document.getElementById('ordPlaceholder');
        loader.classList.add('show');
        placeholder.style.display = 'none';
        detail.style.display = 'none';

        fetch(`/admin/orders/${id}`, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf } })
        .then(r => r.json())
        .then(data => {
            renderOrderDetail(data);
            loader.classList.remove('show');
            detail.style.display = 'block';

            document.getElementById('ordTitle').textContent = `Order #${data.order.id}`;
            document.getElementById('ordSub').textContent = data.order.customer_name;
            document.getElementById('ordActions').style.display = 'flex';
            document.getElementById('ordStatusSelect').value = data.order.status;
        })
        .catch(err => {
            loader.classList.remove('show');
            detail.innerHTML = '<div class="alert alert-danger">Failed to load order details.</div>';
            detail.style.display = 'block';
        });
    };

    function renderOrderDetail(data) {
        const o = data.order;
        const currentStep = statusSteps.indexOf(o.status);

        // Timeline
        let timeline = '<div class="ord-timeline">';
        statusSteps.forEach((step, i) => {
            let cls = '';
            if (i < currentStep) cls = 'done';
            else if (i === currentStep) cls = 'active';
            timeline += `<div class="ord-timeline-step ${cls}">
                <div class="step-dot"></div>
                <div class="step-label">${step.replace('_', ' ')}</div>
            </div>`;
        });
        timeline += '</div>';

        const address = [o.install_address, o.install_city, o.install_state, o.install_zip].filter(Boolean).join(', ');

        let html = `
            <div class="ord-detail-header">
                <div>
                    <h4>Order #${o.id}</h4>
                    <span class="text-muted" style="font-size:.85rem">Created ${esc(o.created_at)}</span>
                </div>
                <div class="text-end">
                    <span class="badge bg-${getStatusColor(o.status)} fs-6">${o.status.replace('_', ' ')}</span>
                </div>
            </div>

            ${timeline}

            <div class="ord-info-grid">
                <div class="ord-info-card">
                    <div class="label">Customer</div>
                    <div class="value">${esc(o.customer_name)}</div>
                </div>
                <div class="ord-info-card">
                    <div class="label">Email</div>
                    <div class="value">${esc(o.customer_email || '—')}</div>
                </div>
                <div class="ord-info-card">
                    <div class="label">Phone</div>
                    <div class="value">${esc(o.customer_phone || '—')}</div>
                </div>
                <div class="ord-info-card">
                    <div class="label">Install Address</div>
                    <div class="value">${esc(address || '—')}</div>
                </div>
                <div class="ord-info-card">
                    <div class="label">Scheduled Date</div>
                    <div class="value">${esc(o.scheduled_date || 'Not scheduled')}</div>
                </div>
                <div class="ord-info-card">
                    <div class="label">Time Slot</div>
                    <div class="value">${esc(o.scheduled_slot || '—')}</div>
                </div>
                <div class="ord-info-card">
                    <div class="label">Technician</div>
                    <div class="value">${esc(o.technician_name || 'Unassigned')}</div>
                </div>
                <div class="ord-info-card">
                    <div class="label">Completed</div>
                    <div class="value">${esc(o.completed_at || '—')}</div>
                </div>
            </div>
        `;

        // Assign technician
        if (data.technicians.length > 0) {
            html += `<div class="mb-4 p-3 bg-light rounded border">
                <div class="d-flex align-items-center gap-2">
                    <label class="form-label mb-0 fw-semibold" style="font-size:.82rem;white-space:nowrap">Assign Technician:</label>
                    <select class="form-select form-select-sm" id="ordTechSelect" style="max-width:200px;">
                        <option value="">— None —</option>`;
            data.technicians.forEach(t => {
                html += `<option value="${t.id}" ${t.id == o.technician_id ? 'selected' : ''}>${esc(t.name)}</option>`;
            });
            html += `</select>
                    <button class="btn btn-sm btn-outline-primary" onclick="ordAssignTech()">Assign</button>
                </div>
            </div>`;
        }

        // Items
        if (data.items.length > 0) {
            html += `<h6 class="fw-bold mb-3"><i class="bi bi-box-seam me-2"></i>Order Items</h6>
                <div class="table-responsive"><table class="table table-sm ord-items-table">
                    <thead><tr><th>#</th><th>Description</th><th>Type</th><th>Size</th><th class="text-center">Qty</th></tr></thead><tbody>`;
            data.items.forEach((item, i) => {
                html += `<tr>
                    <td class="text-muted">${i+1}</td>
                    <td class="fw-semibold">${esc(item.description)}</td>
                    <td><span class="badge bg-light text-dark border">${esc(item.series_type || '—')}</span></td>
                    <td>${item.width}" x ${item.height}"</td>
                    <td class="text-center">${item.qty}</td>
                </tr>`;
            });
            html += `</tbody></table></div>`;
        }

        // Notes
        if (o.notes) {
            html += `<div class="mt-3 p-3 bg-light rounded border">
                <div style="font-size:.7rem;text-transform:uppercase;color:#888;letter-spacing:.5px;margin-bottom:4px">Notes</div>
                <div style="font-size:.85rem">${esc(o.notes)}</div>
            </div>`;
        }

        document.getElementById('ordDetail').innerHTML = html;
    }

    function getStatusColor(s) {
        const m = { pending: 'warning', scheduled: 'info', in_progress: 'primary', completed: 'success', cancelled: 'secondary' };
        return m[s] || 'secondary';
    }

    function esc(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // ── Update status ─────────────────────────────────────
    document.getElementById('ordSaveStatusBtn').addEventListener('click', function() {
        if (!activeOrderId) return;
        const newStatus = document.getElementById('ordStatusSelect').value;

        fetch(`/admin/orders/${activeOrderId}/status`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify({ status: newStatus })
        }).then(() => location.reload());
    });

    // ── Assign technician ─────────────────────────────────
    window.ordAssignTech = function() {
        const techId = document.getElementById('ordTechSelect').value;
        if (!activeOrderId) return;

        fetch(`/admin/orders/${activeOrderId}/assign`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify({ technician_id: techId })
        }).then(() => location.reload());
    };

})();
</script>
@endpush
