@extends('layouts.installer')
@section('title', 'Edit Quote')

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

    /* ── Main Content ───────────────────────────── */
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

    /* Settings card */
    .settings-card {
        background: #fff; border-radius: .5rem; box-shadow: 0 1px 4px rgba(0,0,0,.08);
        margin: 1rem;
    }
    .settings-card .card-header {
        background: #fff; padding: .6rem 1rem; border-bottom: 1px solid rgba(0,0,0,.06);
        font-weight: 600; font-size: .85rem; border-radius: .5rem .5rem 0 0;
    }
    .settings-card .card-body { padding: .75rem 1rem; }

    @media (max-width: 991.98px) {
        .sales-container { flex-direction: column; height: auto; }
        .sales-hub { width: 100%; min-width: 100%; max-height: 40vh; flex-direction: row; overflow-x: auto; }
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
            <a href="{{ route('installer.quotes.create') }}" class="hub-link">
                <span><span class="hub-icon"><i class="bi bi-plus-circle-fill text-danger"></i></span> New Quote</span>
            </a>
        </div>

        <div class="hub-section">
            <div class="hub-section-title">Pipeline</div>
            <a href="{{ route('installer.quotes.index') }}" class="hub-link active">
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
        @if($errors->any())
            <div class="alert alert-danger m-3 mb-0">
                @foreach($errors->all() as $e)
                    <div>{{ $e }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('installer.quotes.update', $quote->id) }}">
            @csrf
            @method('PUT')

            {{-- Quote Header Card (enterprise style) --}}
            <div class="quote-header-card">
                <div class="card-top">
                    <h5><i class="bi bi-pencil me-2"></i>Edit Quote</h5>
                    <div class="d-flex align-items-center gap-2">
                        <span class="quote-num">{{ $quote->quote_number }}</span>
                        <a href="{{ route('installer.quotes.index') }}" class="btn btn-sm btn-outline-secondary" style="font-size:.75rem; padding:2px 10px;">
                            <i class="bi bi-arrow-left me-1"></i>Back
                        </a>
                    </div>
                </div>
                <div class="card-body-compact">
                    {{-- Row 1: Customer Name | Email | Phone | Service Type | Expected Date --}}
                    <div class="row g-2 mb-2">
                        <div class="col-md-3">
                            <label class="form-label">Customer Name <span class="text-danger">*</span></label>
                            <input type="text" name="customer_name" class="form-control form-control-sm" value="{{ old('customer_name', $quote->billing_name) }}" required list="customerList">
                            <datalist id="customerList">
                                @foreach($customers as $c)
                                    <option value="{{ $c->name }}">{{ $c->email }}</option>
                                @endforeach
                            </datalist>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="customer_email" class="form-control form-control-sm" value="{{ old('customer_email', $quote->billing_email) }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Phone</label>
                            <input type="text" name="customer_phone" class="form-control form-control-sm" value="{{ old('customer_phone', $quote->billing_phone) }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Valid (days)</label>
                            <input type="number" name="valid_days" class="form-control form-control-sm" value="{{ old('valid_days', $quote->valid_until ? now()->diffInDays($quote->valid_until, false) : 30) }}" min="1" max="365">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <input type="text" class="form-control form-control-sm" value="{{ ucfirst($quote->status) }}" readonly style="background:#f5f5f5; color:#888;">
                        </div>
                    </div>

                    {{-- Row 2: Street | ZIP | City | State | Save button --}}
                    <div class="row g-2">
                        <div class="col-md-3">
                            <label class="form-label">Street</label>
                            <input type="text" name="address" class="form-control form-control-sm" value="{{ old('address', $quote->billing_address) }}">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">ZIP</label>
                            <input type="text" name="zip" class="form-control form-control-sm" value="{{ old('zip', $quote->billing_zip) }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control form-control-sm" value="{{ old('city', $quote->billing_city) }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">State</label>
                            <input type="text" name="state" class="form-control form-control-sm" value="{{ old('state', $quote->billing_state) }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Notes</label>
                            <input type="text" name="notes" class="form-control form-control-sm" value="{{ old('notes', $quote->notes) }}" placeholder="Optional notes...">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-sm w-100" style="background:var(--vip-accent); color:#fff; font-weight:600; height:30px;">
                                <i class="bi bi-check-circle me-1"></i> Save Changes
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        {{-- Quote Items Section (placeholder for future configurator) --}}
        <div class="settings-card">
            <div class="card-header">
                <i class="bi bi-list-ul me-2"></i>Quote Items
                <span class="float-end text-muted small">Coming soon - item configurator</span>
            </div>
            <div class="card-body">
                <div class="text-center py-4 text-muted" style="font-size:.85rem;">
                    <i class="bi bi-inbox d-block mb-2" style="font-size:2rem; opacity:.3;"></i>
                    Item management will be available here
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
