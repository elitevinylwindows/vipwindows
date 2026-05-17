@extends('layouts.installer')
@section('title', 'New Quote')

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

    /* ── Main Panel ────────────────────────────── */
    .sales-main { flex: 1; overflow-y: auto; background: #f5f4f0; }

    /* Header card - enterprise style */
    .quote-header-card {
        background: #fff; border-radius: .5rem; box-shadow: 0 1px 4px rgba(0,0,0,.08);
        margin: 1rem; margin-bottom: 0;
    }
    .quote-header-card .card-top {
        background: #fff; padding: .5rem 1rem; border-bottom: 1px solid rgba(0,0,0,.06);
        display: flex; justify-content: space-between; align-items: center;
        border-radius: .5rem .5rem 0 0;
    }
    .quote-header-card .card-top h5 { margin: 0; font-size: .95rem; font-weight: 700; color: #111; }
    .quote-header-card .card-top .quote-num { font-weight: 700; font-size: 1rem; color: var(--vip-accent); }
    .quote-header-card .card-body-compact { padding: .5rem 1rem .75rem; }
    .quote-header-card .form-label { font-size: .75rem; margin-bottom: 0; color: #555; }
    .quote-header-card .form-control-sm, .quote-header-card .form-select-sm { font-size: .8rem; height: 30px; }

    /* Items area */
    .quote-items-area { margin: 1rem; }
    .items-card {
        background: #fff; border-radius: .5rem; box-shadow: 0 1px 4px rgba(0,0,0,.08);
        display: flex; min-height: 400px;
    }

    /* Add Item sidebar */
    .add-item-panel {
        width: 220px; min-width: 220px; border-right: 1px solid rgba(0,0,0,.06);
        display: flex; flex-direction: column;
    }
    .add-item-panel .panel-header {
        padding: .6rem .75rem; border-bottom: 1px solid rgba(0,0,0,.06);
        font-size: .8rem; font-weight: 600;
    }
    .add-item-panel .panel-body { padding: .6rem .75rem; flex: 1; overflow-y: auto; }
    .add-item-panel .form-label { font-size: .7rem; margin-bottom: 2px; color: #666; }
    .add-item-panel .form-control-sm, .add-item-panel .form-select-sm { font-size: .78rem; height: 28px; }
    .add-item-panel textarea.form-control-sm { height: auto; }

    /* Items table */
    .items-table-panel { flex: 1; display: flex; flex-direction: column; }
    .items-table-panel .panel-header {
        padding: .6rem 1rem; border-bottom: 1px solid rgba(0,0,0,.06);
        display: flex; justify-content: space-between; align-items: center;
        font-size: .8rem; font-weight: 600;
    }
    .items-table-panel .panel-body { flex: 1; overflow-y: auto; padding: 0; }

    .items-tbl { width: 100%; border-collapse: collapse; }
    .items-tbl th { font-size: .68rem; text-transform: uppercase; letter-spacing: .5px; color: #888; padding: .5rem .75rem; border-bottom: 1px solid rgba(0,0,0,.08); background: #fafaf7; text-align: left; }
    .items-tbl td { padding: .5rem .75rem; font-size: .82rem; border-bottom: 1px solid rgba(0,0,0,.04); }
    .items-tbl tr:hover { background: rgba(201,168,76,.04); }
    .items-tbl .text-end { text-align: right; }
    .items-tbl .item-actions { opacity: 0; transition: opacity .15s; }
    .items-tbl tr:hover .item-actions { opacity: 1; }

    /* Summary panel */
    .summary-panel {
        width: 200px; min-width: 200px; border-left: 1px solid rgba(0,0,0,.06);
        display: flex; flex-direction: column;
    }
    .summary-panel .panel-header {
        padding: .6rem .75rem; border-bottom: 1px solid rgba(0,0,0,.06);
        font-size: .8rem; font-weight: 600;
    }
    .summary-panel .panel-body { padding: .75rem; flex: 1; }
    .summary-row { display: flex; justify-content: space-between; padding: .3rem 0; font-size: .8rem; }
    .summary-row.total { border-top: 2px solid #111; font-weight: 700; font-size: .95rem; padding-top: .5rem; margin-top: .5rem; }

    @media (max-width: 991.98px) {
        .sales-container { flex-direction: column; height: auto; }
        .sales-hub { width: 100%; min-width: 100%; max-height: 40vh; flex-direction: row; overflow-x: auto; }
        .items-card { flex-direction: column; }
        .add-item-panel, .summary-panel { width: 100%; min-width: 100%; border: none; border-bottom: 1px solid rgba(0,0,0,.06); }
    }
</style>
@endpush

@section('content')
@php
    $totalQuotes = $quotes->count();
    $draftCount = $quotes->where('status', 'draft')->count();
    $sentCount = $quotes->where('status', 'sent')->count();
    $approvedCount = $quotes->where('status', 'approved')->count();
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
            <a href="{{ route('installer.quotes.create') }}" class="hub-link active">
                <span><span class="hub-icon"><i class="bi bi-plus-circle-fill text-danger"></i></span> New Quote</span>
            </a>
        </div>

        <div class="hub-section">
            <div class="hub-section-title">Pipeline</div>
            <a href="{{ route('installer.quotes.index') }}" class="hub-link">
                <span><span class="hub-icon"><i class="bi bi-file-earmark-text"></i></span> Quotes</span>
                <span class="hub-count">{{ $totalQuotes }}</span>
            </a>
            <a href="{{ route('installer.jobs.index') }}" class="hub-link">
                <span><span class="hub-icon"><i class="bi bi-tools"></i></span> Jobs</span>
            </a>
            <a href="{{ route('installer.invoices.index') }}" class="hub-link">
                <span><span class="hub-icon"><i class="bi bi-receipt"></i></span> Invoices</span>
            </a>
        </div>

        <div class="hub-section">
            <div class="hub-section-title">Quote Status</div>
            <a href="{{ route('installer.quotes.index', ['status' => 'draft']) }}" class="hub-status-item">
                <span><span class="hub-status-dot" style="background:#6c757d; display:inline-block;"></span> Draft</span>
                <span class="hub-count">{{ $draftCount }}</span>
            </a>
            <a href="{{ route('installer.quotes.index', ['status' => 'sent']) }}" class="hub-status-item">
                <span><span class="hub-status-dot" style="background:#28a745; display:inline-block;"></span> Sent</span>
                <span class="hub-count">{{ $sentCount }}</span>
            </a>
            <a href="{{ route('installer.quotes.index', ['status' => 'approved']) }}" class="hub-status-item">
                <span><span class="hub-status-dot" style="background:#007bff; display:inline-block;"></span> Approved</span>
                <span class="hub-count">{{ $approvedCount }}</span>
            </a>
        </div>

        <div class="hub-section">
            <div class="hub-section-title">Customers</div>
            <a href="{{ route('installer.customers.index') }}" class="hub-link">
                <span><span class="hub-icon"><i class="bi bi-people"></i></span> My Customers</span>
            </a>
        </div>
    </div>

    {{-- Main Panel --}}
    <div class="sales-main">
        <form method="POST" action="{{ route('installer.quotes.store') }}" id="quoteForm">
            @csrf

            {{-- Quote Header Card (enterprise style) --}}
            <div class="quote-header-card">
                <div class="card-top">
                    <h5><i class="bi bi-plus-circle me-2"></i>Start Quote</h5>
                    <div class="d-flex align-items-center gap-2">
                        <span class="small text-muted">Entered by: <strong>{{ Auth::user()->name }}</strong></span>
                        <a href="{{ route('installer.quotes.index') }}" class="btn btn-sm btn-outline-secondary" style="font-size:.75rem; padding:2px 10px;">
                            <i class="bi bi-arrow-left me-1"></i>Back
                        </a>
                    </div>
                </div>
                <div class="card-body-compact">
                    @if($errors->any())
                        <div class="alert alert-danger py-1 mb-2" style="font-size:.78rem;">
                            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
                        </div>
                    @endif

                    {{-- Row 1: Customer Name | Email | Phone | Order Type --}}
                    <div class="row g-2 mb-2">
                        <div class="col-md-3">
                            <label class="form-label">Customer Name <span class="text-danger">*</span></label>
                            <input type="text" name="customer_name" class="form-control form-control-sm" value="{{ old('customer_name') }}" required list="customerList">
                            <datalist id="customerList">
                                @foreach($customers as $c)
                                    <option value="{{ $c->name }}" data-email="{{ $c->email }}" data-phone="{{ $c->phone }}" data-address="{{ $c->address }}" data-city="{{ $c->city }}" data-state="{{ $c->state }}" data-zip="{{ $c->zip }}">{{ $c->email }}</option>
                                @endforeach
                            </datalist>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="customer_email" id="customer_email" class="form-control form-control-sm" value="{{ old('customer_email') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Phone</label>
                            <input type="text" name="customer_phone" id="customer_phone" class="form-control form-control-sm" value="{{ old('customer_phone') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Service Type</label>
                            <select name="order_type" class="form-select form-select-sm">
                                <option value="installation">Installation</option>
                                <option value="replacement">Replacement</option>
                                <option value="repair">Repair</option>
                                <option value="consultation">Consultation</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Expected Date</label>
                            <input type="date" name="expected_delivery" class="form-control form-control-sm" value="{{ now()->addDays(14)->format('Y-m-d') }}">
                        </div>
                    </div>

                    {{-- Row 2: Street | ZIP | City | State | Valid Until --}}
                    <div class="row g-2 mb-2">
                        <div class="col-md-3">
                            <label class="form-label">Street</label>
                            <input type="text" name="address" id="address" class="form-control form-control-sm" value="{{ old('address') }}">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">ZIP</label>
                            <input type="text" name="zip" id="zip" class="form-control form-control-sm" value="{{ old('zip') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">City</label>
                            <input type="text" name="city" id="city" class="form-control form-control-sm" value="{{ old('city') }}">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">State</label>
                            <select name="state" id="state" class="form-select form-select-sm">
                                <option value="">--</option>
                                @foreach(['AL','AK','AZ','AR','CA','CO','CT','DE','FL','GA','HI','ID','IL','IN','IA','KS','KY','LA','ME','MD','MA','MI','MN','MS','MO','MT','NE','NV','NH','NJ','NM','NY','NC','ND','OH','OK','OR','PA','RI','SC','SD','TN','TX','UT','VT','VA','WA','WV','WI','WY'] as $st)
                                    <option value="{{ $st }}" {{ old('state') === $st ? 'selected' : '' }}>{{ $st }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Valid (days)</label>
                            <input type="number" name="valid_days" class="form-control form-control-sm" value="{{ old('valid_days', 30) }}" min="1" max="365">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">Entry Date</label>
                            <input type="date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" readonly style="background:#f5f5f5; color:#888;">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-sm w-100" style="background:var(--vip-accent); color:#fff; font-weight:600; height:30px;">
                                <i class="bi bi-play-fill me-1"></i> Start Quote
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Notes (collapsible) --}}
            <input type="hidden" name="notes" id="notesValue" value="{{ old('notes') }}">
        </form>

        {{-- Items Card (3-column like enterprise) --}}
        <div class="quote-items-area">
            <div class="items-card">
                {{-- Add Item Panel (left) --}}
                <div class="add-item-panel">
                    <div class="panel-header">
                        <i class="bi bi-plus-circle me-1 text-primary"></i> Add Line Item
                    </div>
                    <div class="panel-body">
                        <div class="mb-2">
                            <label class="form-label">Service</label>
                            <select id="itemService" class="form-select form-select-sm">
                                <option value="">Select service...</option>
                                @foreach($services ?? [] as $svc)
                                    <option value="{{ $svc->name }}" data-price="{{ $svc->base_price }}">{{ $svc->name }}</option>
                                @endforeach
                                <option value="custom">Custom Item</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Description</label>
                            <input type="text" id="itemDesc" class="form-control form-control-sm" placeholder="Item description">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Size / Specs</label>
                            <input type="text" id="itemSpecs" class="form-control form-control-sm" placeholder="e.g. 36x60, 2-panel">
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label">Qty</label>
                                <input type="number" id="itemQty" class="form-control form-control-sm" value="1" min="1">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Unit Price</label>
                                <input type="number" id="itemPrice" class="form-control form-control-sm" value="0" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Notes</label>
                            <textarea id="itemNotes" class="form-control form-control-sm" rows="2" placeholder="Optional notes..."></textarea>
                        </div>
                        <button type="button" class="btn btn-sm w-100 mt-2" style="background:var(--vip-accent); color:#fff; font-weight:600;" onclick="addItem()">
                            <i class="bi bi-plus-lg me-1"></i> Add Item
                        </button>
                    </div>
                </div>

                {{-- Items Table (center) --}}
                <div class="items-table-panel">
                    <div class="panel-header">
                        <span><i class="bi bi-list-ul me-1"></i> Quote Items</span>
                        <span class="text-muted small" id="itemCountLabel">0 items</span>
                    </div>
                    <div class="panel-body">
                        <table class="items-tbl">
                            <thead>
                                <tr>
                                    <th style="width:30px;">#</th>
                                    <th>Service / Description</th>
                                    <th>Specs</th>
                                    <th class="text-end">Qty</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-end">Total</th>
                                    <th style="width:50px;"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                <tr id="emptyRow">
                                    <td colspan="7" class="text-center py-5 text-muted" style="font-size:.85rem;">
                                        <i class="bi bi-inbox d-block mb-2" style="font-size:2rem; opacity:.3;"></i>
                                        Start by adding line items on the left
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Summary Panel (right) --}}
                <div class="summary-panel">
                    <div class="panel-header">
                        <i class="bi bi-calculator me-1"></i> Summary
                    </div>
                    <div class="panel-body">
                        <div class="summary-row"><span>Subtotal</span><span id="sumSubtotal">$0.00</span></div>
                        <div class="summary-row"><span>Tax</span><span id="sumTax">$0.00</span></div>
                        <div class="summary-row"><span>Discount</span><span id="sumDiscount">$0.00</span></div>
                        <div class="summary-row total"><span>Total</span><span id="sumTotal" style="color:var(--vip-accent);">$0.00</span></div>

                        <div class="mt-4 pt-3" style="border-top:1px solid rgba(0,0,0,.06);">
                            <div class="small text-muted mb-2">Quick Notes</div>
                            <textarea class="form-control form-control-sm" rows="3" placeholder="Quote notes..." style="font-size:.75rem;" oninput="document.getElementById('notesValue').value = this.value"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-fill customer fields from datalist
    const nameInput = document.querySelector('input[name="customer_name"]');
    if (nameInput) {
        nameInput.addEventListener('change', function() {
            const opts = document.querySelectorAll('#customerList option');
            for (const opt of opts) {
                if (opt.value === this.value) {
                    document.getElementById('customer_email').value = opt.dataset.email || '';
                    document.getElementById('customer_phone').value = opt.dataset.phone || '';
                    document.getElementById('address').value = opt.dataset.address || '';
                    document.getElementById('city').value = opt.dataset.city || '';
                    document.getElementById('zip').value = opt.dataset.zip || '';
                    const stateSelect = document.getElementById('state');
                    if (opt.dataset.state) {
                        stateSelect.value = opt.dataset.state.toUpperCase();
                    }
                    break;
                }
            }
        });
    }

    // Service selection auto-fills price
    document.getElementById('itemService').addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        if (opt.dataset.price) {
            document.getElementById('itemPrice').value = parseFloat(opt.dataset.price).toFixed(2);
        }
        if (this.value && this.value !== 'custom') {
            document.getElementById('itemDesc').value = this.value;
        }
    });
});

let items = [];
let itemCounter = 0;

function addItem() {
    const service = document.getElementById('itemService').value;
    const desc = document.getElementById('itemDesc').value || service || 'Item';
    const specs = document.getElementById('itemSpecs').value;
    const qty = parseInt(document.getElementById('itemQty').value) || 1;
    const price = parseFloat(document.getElementById('itemPrice').value) || 0;
    const notes = document.getElementById('itemNotes').value;
    const total = qty * price;

    if (!desc || price <= 0) {
        alert('Please enter a description and price.');
        return;
    }

    itemCounter++;
    items.push({ id: itemCounter, desc, specs, qty, price, total, notes });

    renderItems();
    clearItemForm();
}

function removeItem(id) {
    items = items.filter(i => i.id !== id);
    renderItems();
}

function renderItems() {
    const tbody = document.getElementById('itemsBody');
    const emptyRow = document.getElementById('emptyRow');

    if (items.length === 0) {
        emptyRow.style.display = '';
        document.getElementById('itemCountLabel').textContent = '0 items';
    } else {
        emptyRow.style.display = 'none';
        document.getElementById('itemCountLabel').textContent = items.length + ' item' + (items.length !== 1 ? 's' : '');
    }

    // Remove old item rows
    tbody.querySelectorAll('.item-row').forEach(r => r.remove());

    items.forEach((item, idx) => {
        const tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.innerHTML = `
            <td class="text-muted">${idx + 1}</td>
            <td><strong>${escHtml(item.desc)}</strong>${item.notes ? '<br><small class="text-muted">' + escHtml(item.notes) + '</small>' : ''}</td>
            <td class="text-muted small">${escHtml(item.specs)}</td>
            <td class="text-end">${item.qty}</td>
            <td class="text-end">$${item.price.toFixed(2)}</td>
            <td class="text-end fw-semibold">$${item.total.toFixed(2)}</td>
            <td class="text-end"><button class="btn btn-sm text-danger item-actions" onclick="removeItem(${item.id})" style="padding:0 4px;"><i class="bi bi-trash"></i></button></td>
        `;
        tbody.appendChild(tr);
    });

    // Update summary
    const subtotal = items.reduce((sum, i) => sum + i.total, 0);
    document.getElementById('sumSubtotal').textContent = '$' + subtotal.toFixed(2);
    document.getElementById('sumTotal').textContent = '$' + subtotal.toFixed(2);
}

function clearItemForm() {
    document.getElementById('itemService').value = '';
    document.getElementById('itemDesc').value = '';
    document.getElementById('itemSpecs').value = '';
    document.getElementById('itemQty').value = '1';
    document.getElementById('itemPrice').value = '0';
    document.getElementById('itemNotes').value = '';
}

function escHtml(s) {
    const d = document.createElement('div');
    d.textContent = s || '';
    return d.innerHTML;
}
</script>
@endpush
