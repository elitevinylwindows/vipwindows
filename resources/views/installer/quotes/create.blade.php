@extends('layouts.installer')
@section('title', 'Quote')

@section('content')
{{-- ══════════════════════════════════════════════════════════════
     NFRC + Glass Pane lookup (used by Quote Preview line items)
     ══════════════════════════════════════════════════════════════ --}}
@php
    // ── Fraction label helper (must be defined before table renders) ──
    if (!function_exists('_wd2_fracLabel')) {
        function _wd2_fracLabel($dec) {
            $whole = intval($dec);
            $sixteenths = (int) round(($dec - $whole) * 16);
            if ($sixteenths <= 0) return $whole . '"';
            if ($sixteenths >= 16) return ($whole + 1) . '"';
            $num = $sixteenths;
            $den = 16;
            $a = $num; $b = $den;
            while ($b) { $t = $b; $b = $a % $b; $a = $t; }
            $gcd = $a;
            $num = $num / $gcd;
            $den = $den / $gcd;
            $frac = "{$num}/{$den}";
            return ($whole > 0 ? "{$whole} {$frac}" : $frac) . '"';
        }
    }

    $nfrcMap = [
        'CLR/CLR'  => ['u' => '0.47', 's' => '0.56', 'v' => '0.63', 'c' => '45'],
        'LE3/CLR'  => ['u' => '0.28', 's' => '0.22', 'v' => '0.52', 'c' => '62'],
        'LE3/LAM'  => ['u' => '0.28', 's' => '0.20', 'v' => '0.41', 'c' => '62'],
        'SB6/CLR'  => ['u' => '0.29', 's' => '0.27', 'v' => '0.53', 'c' => '60'],
    ];
    $glassPaneMap = [
        'CLR/CLR'  => ['f1' => '3.1MM CLR / 3.1MM CLR',  'f2' => '3.1MM CLR / 3.1MM CLR'],
        'LE3/CLR'  => ['f1' => '3.1MM LE3 / 3.1MM CLR',  'f2' => '3.1MM LE3 / 3.1MM CLR'],
        'LE3/LAM'  => ['f1' => '3.1MM LE3 / 3.1MM LAM',  'f2' => '3.1MM LE3 / 3.1MM LAM'],
        'SB6/CLR'  => ['f1' => '3.1MM SB6 / 3.1MM CLR',  'f2' => '3.1MM SB6 / 3.1MM CLR'],
    ];
    $defaultNfrc = ['u' => '0.28', 's' => '0.22', 'v' => '0.52', 'c' => '62'];
    $defaultPane = ['f1' => '3.1MM LE3 / 3.1MM CLR', 'f2' => '3.1MM LE3 / 3.1MM CLR'];

@endphp

{{-- Select2 for searchable dropdowns --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
/* Select2 compact overrides — match form-control-sm exactly (31px) */
.select2-container--default .select2-selection--single {
    height: 31px !important; min-height: 31px !important;
    padding: 0 6px !important; font-size: 12px !important;
    border: 1px solid #ced4da !important; border-radius: 0.25rem !important;
    display: flex !important; align-items: center !important;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 29px !important; padding-left: 2px !important;
    font-size: 12px !important; color: #495057 !important;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 29px !important; top: 0 !important;
}
.select2-dropdown { font-size: 12px !important; z-index: 9999 !important; }
.select2-results__option { padding: 4px 8px !important; }
.select2-search--dropdown .select2-search__field { font-size: 12px !important; padding: 4px 6px !important; }
.select2-container { width: 100% !important; }
.select2-container--default.select2-container--focus .select2-selection--single,
.select2-container--default.select2-container--open .select2-selection--single {
    border-color: var(--pc-sidebar-active-color, #cc1515) !important; box-shadow: 0 0 0 0.15rem rgba(var(--pc-sidebar-active-color-rgb, 179,2,2), 0.15) !important;
}
</style>

<style>
.sales-container { display: flex; height: calc(100vh - 56px); overflow: hidden; }
.sales-hub {
    width: 220px; min-width: 220px;
    background: #fff;
    border-right: 1px solid rgba(0,0,0,.08);
    display: flex; flex-direction: column;
    overflow-y: auto; flex-shrink: 0;
}
.hub-brand { padding: 1rem 1rem .5rem; font-size: .85rem; font-weight: 700; color: var(--vip-accent); display: flex; align-items: center; gap: .5rem; }
.hub-brand i { font-size: 1.1rem; }
.hub-section { padding: .25rem 0; }
.hub-section-title { font-size: .6rem; text-transform: uppercase; letter-spacing: 1.5px; font-weight: 600; color: rgba(0,0,0,.35); padding: .75rem 1rem .25rem; }
.hub-link {
    display: flex; align-items: center; justify-content: space-between;
    padding: .5rem 1rem; font-size: .82rem; color: #333;
    text-decoration: none; border-left: 3px solid transparent; transition: all .12s;
}
.hub-link:hover { background: rgba(201,168,76,.05); color: #111; }
.hub-link.active { background: rgba(201,168,76,.08); color: var(--vip-accent); border-left-color: var(--vip-accent); font-weight: 600; }
.hub-link .hub-icon { width: 20px; text-align: center; margin-right: .5rem; font-size: .9rem; }
.hub-link .hub-count { background: rgba(0,0,0,.06); color: #555; font-size: .7rem; font-weight: 600; padding: 1px 8px; border-radius: 10px; min-width: 24px; text-align: center; }
.hub-link.active .hub-count { background: rgba(201,168,76,.2); color: #8b6914; }
.hub-status-item { display: flex; align-items: center; justify-content: space-between; padding: .35rem 1rem .35rem 1.25rem; font-size: .78rem; color: #555; text-decoration: none; transition: background .12s; cursor: pointer; }
.hub-status-item:hover { background: rgba(0,0,0,.02); }
.hub-status-dot { width: 8px; height: 8px; border-radius: 50%; margin-right: .5rem; display: inline-block; }
.sales-main { flex: 1; overflow-y: auto; background: #f5f4f0; }
@media (max-width: 991.98px) {
    .sales-container { flex-direction: column; height: auto; }
    .sales-hub { width: 100%; min-width: 100%; max-height: none; flex-direction: row; overflow-x: auto; }
    .sales-hub .hub-section { display: flex; align-items: center; gap: .25rem; padding: .25rem .5rem; }
    .sales-hub .hub-section-title { display: none; }
}
</style>

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
            </a>
        </div>

        <div class="hub-section">
            <div class="hub-section-title">Quote Status</div>
            <a href="{{ route('installer.quotes.index', ['status' => 'draft']) }}" class="hub-status-item">
                <span><span class="hub-status-dot" style="background:#6c757d;"></span> Draft</span>
            </a>
            <a href="{{ route('installer.quotes.index', ['status' => 'sent']) }}" class="hub-status-item">
                <span><span class="hub-status-dot" style="background:#28a745;"></span> Sent</span>
            </a>
            <a href="{{ route('installer.quotes.index', ['status' => 'approved']) }}" class="hub-status-item">
                <span><span class="hub-status-dot" style="background:#007bff;"></span> Approved</span>
            </a>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="sales-main">

<div class="container-fluid pt-3">

   {{-- ROW 1: Start Quote Card (Full Width, Compact Horizontal Layout) --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-dark">{{ $quote ? __('Edit Quote') : __('Start Quote') }}</h5>
                    <span class="fw-bold text-dark" style="font-size: 1.1rem;">{{ $quote->quote_number ?? $quoteNumber ?? '' }}</span>
                </div>
                <div class="card-body py-2">
                    <form id="quoteHeaderForm" action="{{ $quote ? route('installer.quotes.update', $quote->id) : route('installer.quotes.store') }}" method="POST">
                        @csrf
                        @if($quote)
                            <input type="hidden" name="_method" value="PUT">
                        @endif

                        <input type="hidden" name="quote_number" value="{{ $quote->quote_number ?? $quoteNumber ?? '' }}">

                        @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show py-1 mb-2" style="font-size:0.8rem;">
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                            <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
                        </div>
                        @endif

                        {{-- Row 1: Customer Name | Expected Delivery --}}
                        <div class="row g-2 mb-2">
                            <input type="hidden" name="customer_number" id="customer_number" value="{{ $quote->customer_number ?? old('customer_number', '') }}">
                            <input type="hidden" name="order_type" id="order_type" value="pickup">
                            <input type="hidden" name="is_special_order" value="0">
                            <div class="col-md-6">
                                <label class="form-label small mb-0">{{ __('Customer Name') }}</label>
                                <div style="position:relative;">
                                    <input type="text" name="customer_name" id="customer_name" class="form-control form-control-sm"
                                           value="{{ $quote->customer_name ?? old('customer_name') }}" required style="padding-right:28px;">
                                    <button type="button" id="newCustomerBtn2" title="{{ __('New Customer') }}"
                                            style="position:absolute; right:4px; top:50%; transform:translateY(-50%); background:#6c757d; border:none; color:#fff; width:20px; height:20px; border-radius:50%; font-size:11px; line-height:1; padding:0; cursor:pointer; display:flex; align-items:center; justify-content:center;">
                                        <i class="fas fa-plus" style="font-size:9px;"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small mb-0">{{ __('Expected Delivery') }}</label>
                                <input type="text" name="expected_delivery" id="expected_delivery" class="form-control form-control-sm"
                                       autocomplete="off"
                                       value="{{ $quote->expected_delivery ?? \Carbon\Carbon::now()->addDays(14)->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-3 d-flex align-items-end gap-2" style="padding-bottom:3px;">
                                <button type="button" class="btn btn-sm" id="editCustomerFromHeader"
                                        style="font-size:10px; padding:2px 8px; display:none; white-space:nowrap; background:#b30202; color:#fff; border:none;"
                                        title="{{ __('Edit Customer Details') }}">
                                    <i class="fas fa-pen me-1"></i>{{ __('Edit Customer') }}
                                </button>
                            </div>
                            <input type="hidden" name="measurement_type" value="{{ $quote->measurement_type ?? 'Imperial' }}">
                        </div>

                        {{-- Row 2: Street | ZIP | City | State | Tax Rule | Resale # (conditional) --}}
                        <div class="row g-2 mb-2">
                            <div class="col-md-3">
                                <label class="form-label small mb-0">{{ __('Street') }}</label>
                                <input type="text" name="billing_address" id="billing_address" class="form-control form-control-sm"
                                       value="{{ $quote->billing_address ?? old('billing_address') }}">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label small mb-0">{{ __('ZIP') }}</label>
                                <input type="text" name="billing_zip" id="billing_zip" class="form-control form-control-sm"
                                       value="{{ $quote->billing_zip ?? old('billing_zip') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small mb-0">{{ __('City') }}</label>
                                <input type="text" name="billing_city" id="billing_city" class="form-control form-control-sm"
                                       value="{{ $quote->billing_city ?? old('billing_city') }}">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label small mb-0">{{ __('State') }}</label>
                                @php $currentState = $quote->billing_state ?? old('billing_state', ''); @endphp
                                <select name="billing_state" id="billing_state" class="form-select form-select-sm s2-searchable">
                                    <option value="">--</option>
                                    @foreach(['AL'=>'AL','AK'=>'AK','AZ'=>'AZ','AR'=>'AR','CA'=>'CA','CO'=>'CO','CT'=>'CT','DE'=>'DE','FL'=>'FL','GA'=>'GA','HI'=>'HI','ID'=>'ID','IL'=>'IL','IN'=>'IN','IA'=>'IA','KS'=>'KS','KY'=>'KY','LA'=>'LA','ME'=>'ME','MD'=>'MD','MA'=>'MA','MI'=>'MI','MN'=>'MN','MS'=>'MS','MO'=>'MO','MT'=>'MT','NE'=>'NE','NV'=>'NV','NH'=>'NH','NJ'=>'NJ','NM'=>'NM','NY'=>'NY','NC'=>'NC','ND'=>'ND','OH'=>'OH','OK'=>'OK','OR'=>'OR','PA'=>'PA','RI'=>'RI','SC'=>'SC','SD'=>'SD','TN'=>'TN','TX'=>'TX','UT'=>'UT','VT'=>'VT','VA'=>'VA','WA'=>'WA','WV'=>'WV','WI'=>'WI','WY'=>'WY'] as $abbr => $label)
                                        <option value="{{ $abbr }}" {{ strtoupper($currentState) === $abbr ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small mb-0">{{ __('Tax Rule') }}</label>
                                <select name="tax_rule_id" id="tax_rule_id" class="form-select form-select-sm s2-searchable">
                                    <option value="">{{ __('-- Select --') }}</option>
                                    @foreach(($taxRules ?? []) as $rule)
                                        <option value="{{ $rule->id }}"
                                                data-rate="{{ $rule->rate }}"
                                                data-exempt="{{ $rule->is_exempt ? '1' : '0' }}"
                                                {{ ($quote->tax_rule_id ?? old('tax_rule_id')) == $rule->id ? 'selected' : '' }}>
                                            {{ $rule->display_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2" id="resaleNumberGroup" style="display:none;">
                                <label class="form-label small mb-0">{{ __('Resale Tax #') }} <span class="text-danger">*</span></label>
                                <div style="position:relative;">
                                    <input type="text" name="resale_number" id="resale_number" class="form-control form-control-sm"
                                           value="{{ $quote->resale_number ?? old('resale_number') }}">
                                    <span id="resaleVerifiedBadge" style="display:none; position:absolute; right:6px; top:50%; transform:translateY(-50%);">
                                        <i class="fas fa-check-circle text-success" style="font-size:13px;" title="{{ __('Resale Document Verified') }}"></i>
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Row 3: Phone | Email --}}
                        <div class="row g-2 mb-2">
                            <div class="col-md-2">
                                <label class="form-label small mb-0">{{ __('Phone #') }}</label>
                                <input type="text" name="customer_phone" id="customer_phone" class="form-control form-control-sm"
                                       value="{{ $quote->customer_phone ?? old('customer_phone') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small mb-0">{{ __('Email') }}</label>
                                <input type="email" name="customer_email" id="customer_email" class="form-control form-control-sm"
                                       value="{{ $quote->customer_email ?? old('customer_email') }}">
                            </div>
                        </div>

                        {{-- Row 4: Reference (PO) | Start Button | (spacer) | Entered By | Entry Date | Valid Until --}}
                        <div class="row g-2 mb-2 align-items-end">
                            <div class="col-md-2">
                                <label class="form-label small mb-0">{{ __('Reference (PO)') }} <span class="text-danger">*</span></label>
                                <input type="text" name="reference" id="reference" class="form-control form-control-sm"
                                       value="{{ $quote->reference ?? old('reference') }}" required>
                            </div>
                            <input type="hidden" name="contact" value="{{ $quote->contact ?? old('contact') }}">
                            <input type="hidden" name="is_tax_exempt" id="is_tax_exempt" value="{{ $quote->is_tax_exempt ?? '0' }}">
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary btn-md w-100">
                                    <i class="fas fa-play me-1"></i> {{ $quote ? __('Update Quote') : __('Start Quote') }}
                                </button>
                            </div>
                            <div class="col-md-2"></div>{{-- spacer --}}
                            <div class="col-md-2">
                                <label class="form-label small mb-0">{{ __('Entered By') }}</label>
                                <input type="text" name="entered_by" class="form-control form-control-sm"
                                       value="{{ $quote->entered_by ?? auth()->user()->name ?? 'System' }}" readonly
                                       style="background:#f0f0f0; color:#666;">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small mb-0">{{ __('Entry Date') }}</label>
                                <input type="date" name="entry_date" class="form-control form-control-sm"
                                       value="{{ $quote->entry_date ?? date('Y-m-d') }}" readonly
                                       style="background:#f0f0f0; color:#666;">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small mb-0">{{ __('Valid Until') }}</label>
                                <input type="date" name="valid_until" class="form-control form-control-sm"
                                       value="{{ $quote->valid_until ?? \Carbon\Carbon::now()->addDays(30)->format('Y-m-d') }}" readonly
                                       style="background:#f0f0f0; color:#666;">
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
    @if($quote)
    {{-- ROW 2: Add Item Form (25%) | Quote Details (50%) | Configuration Preview + Product Spec (25%) --}}
    <div class="row mb-4 align-items-stretch" style="min-height:780px;">

  {{-- CARD 1: Add Item Form --}}
<div class="col-md-2 d-flex">
    <div class="card shadow w-100 d-flex flex-column">
        <form id="quoteItemForm" method="POST" action="{{ $quote ? route('installer.quotes.storeItem', $quote->id) : '#' }}" class="d-flex flex-column flex-grow-1" style="min-height:0;">
            @csrf

            {{-- Hidden Fields --}}
            <input type="hidden" name="quote_id" value="{{ $quote->id ?? '' }}">
            <input type="hidden" name="item_id" id="editing_item_id" value="">
            <input type="hidden" name="series_id" id="series_id">
            <input type="hidden" name="series_type" id="series_type">
            <input type="hidden" name="description" id="description">
            <input type="hidden" name="price" id="price" value="0">
            <input type="hidden" name="total" id="total" value="0">
            <input type="hidden" name="discount" id="discount" value="0">
            <input type="hidden" name="glass" id="glass">
            <input type="hidden" name="grid" id="grid">
            <input type="hidden" name="addon" id="addon">

            <div class="card-body flex-grow-1 overflow-auto p-3" style="min-height:0;">
                <h6 id="card1Header" class="mb-3 pb-2 border-bottom">
                    <i class="fas fa-plus-circle me-2 text-primary" id="card1HeaderIcon"></i><span id="card1HeaderText">{{ __('Add Quote Item') }}</span>
                </h6>

                {{-- PANEL DIMENSIONS HEADER (shown for compound types) --}}
                <div id="panel-dimensions-header" style="display:none;" class="mb-2">
                    <div class="d-flex align-items-center justify-content-between">
                        <label class="fw-bold mb-0" style="white-space:nowrap;">{{ __('Panel Dimensions') }}</label>
                        <button type="button" class="btn btn-link btn-sm p-0 text-muted" onclick="openDimensionModal()" title="{{ __('Configure panel dimensions') }}">
                            <i class="fas fa-cog"></i>
                        </button>
                    </div>
                    <div id="panel-dims-summary" class="small text-muted mt-1" style="font-size:10px;"></div>
                </div>

                {{-- SIZE --}}
                <div class="mb-3">
                    <label class="fw-bold">{{ __('Size') }}</label>
                    <div class="mb-2">
                        <label class="small">{{ __('Qty') }}</label>
                        <input type="number" name="qty" class="form-control form-control-sm" value="1">
                    </div>
                    <div class="mb-2">
                        <label class="small">{{ __('Width') }}</label>
                        <input type="text" name="width" class="form-control form-control-sm fraction-input" value="0" placeholder="36 1/2" autocomplete="off">
                        <input type="hidden" name="width_decimal" id="width_decimal" value="0">
                    </div>
                    <div class="mb-2">
                        <label class="small">{{ __('Height') }}</label>
                        <input type="text" name="height" class="form-control form-control-sm fraction-input" value="0" placeholder="60 3/8" autocomplete="off">
                        <input type="hidden" name="height_decimal" id="height_decimal" value="0">
                    </div>
                    {{-- Custom Dimensions checkbox --}}
                    <div id="custom-dims-checkbox-wrap" style="display:none;" class="mt-1">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="customDimsCheck" onchange="onCustomDimsToggle(this.checked)">
                            <label class="form-check-label small" for="customDimsCheck" style="color:#1e40af; cursor:pointer;">
                                <i class="fas fa-expand-arrows-alt me-1" style="font-size:10px"></i>{{ __('Custom Dimensions') }}
                            </label>
                        </div>
                    </div>
                    {{-- Hidden container for backward compat (no longer displayed inline) --}}
                    <div id="panel-dimensions-container" style="display:none;">
                        <div id="panel-dimensions-inputs"></div>
                    </div>
                    <input type="hidden" name="panel_dimensions" id="panel_dimensions_json" value="">
                </div>

                {{-- COLORS --}}
                <div class="mb-3">
                    <label class="fw-bold">{{ __('Colors') }}</label>
                    {{-- Hidden fields that store the final computed values --}}
                    <input type="hidden" name="color_config" id="colorConfigValue">
                    <input type="hidden" name="color_exterior" id="colorExteriorValue" value="WH">
                    <input type="hidden" name="color_interior" id="colorInteriorValue" value="WH">

                    {{-- Step 1: Base Window Color (dynamically populated per series) --}}
                    <select class="form-select form-select-sm mb-2" id="baseWindowColor">
                        <option value="">{{ __('Select base color...') }}</option>
                    </select>

                    {{-- Step 2: Laminated? --}}
                    <div class="form-check mb-2">
                        <input type="checkbox" class="form-check-input" id="isLaminated">
                        <label class="form-check-label small" for="isLaminated">{{ __('Laminated') }}</label>
                    </div>

                    {{-- Step 3: Laminate Side (hidden until laminated checked) --}}
                    <select class="form-select form-select-sm mb-2" id="laminateSide" style="display:none;">
                        <option value="">{{ __('Select laminate side...') }}</option>
                        <option value="exterior">{{ __('Exterior') }}</option>
                        <option value="interior">{{ __('Interior') }}</option>
                        <option value="both">{{ __('Both') }}</option>
                    </select>

                    {{-- Step 4: Laminate color dropdowns (shown based on side selection) --}}
                    <div id="lamExtBlock" style="display:none;">
                        <label class="small text-muted">{{ __('Exterior Laminate') }}</label>
                        <select class="form-select form-select-sm mb-1" id="lamExteriorSelect">
                            <option value="">{{ __('Select laminate color...') }}</option>
                            @foreach($laminateColors ?? [] as $color)
                                <option value="{{ $color->code }}" data-hex="{{ $color->hex_color ?? '' }}">{{ $color->name }}</option>
                            @endforeach
                        </select>
                        <input type="text" class="form-control form-control-sm mb-2" name="color_exterior_custom" id="colorExteriorCustom"
                               placeholder="{{ __('Enter custom color name...') }}" style="display:none;">
                    </div>

                    <div id="lamIntBlock" style="display:none;">
                        <label class="small text-muted">{{ __('Interior Laminate') }}</label>
                        <select class="form-select form-select-sm mb-1" id="lamInteriorSelect">
                            <option value="">{{ __('Select laminate color...') }}</option>
                            @foreach($laminateColors ?? [] as $color)
                                <option value="{{ $color->code }}" data-hex="{{ $color->hex_color ?? '' }}">{{ $color->name }}</option>
                            @endforeach
                        </select>
                        <input type="text" class="form-control form-control-sm mb-2" name="color_interior_custom" id="colorInteriorCustom"
                               placeholder="{{ __('Enter custom color name...') }}" style="display:none;">
                    </div>

                    {{-- Summary of selected colors --}}
                    <div id="colorSummary" class="small text-muted mt-1" style="font-size:11px;"></div>
                </div>

                {{-- FRAME --}}
                <div class="mb-3">
                    <label class="fw-bold">{{ __('Frame') }}</label>
                    <select class="form-select form-select-sm mb-2" name="frame_type" id="frame_type">
                        <option value="Retrofit 1 3/4&quot;" selected>Retrofit 1 3/4"</option>
                        <option value="Retrofit 2 1/2&quot;">Retrofit 2 1/2"</option>
                        <option value="Block">Block</option>
                        <option value="Nailon 1&quot; Setback">Nailon 1" Setback</option>
                        <option value="Nailon 1 3/8&quot; Setback">Nailon 1 3/8" Setback</option>
                    </select>
                    <input type="hidden" name="fin_type" id="fin_type" value="">

                    <script>
                    (function() {
                        // No-op for backward compat — fin_type is now part of frame_type
                        window.updateFinOptions = function() {};
                    })();
                    </script>

                    <div class="form-check mt-2">
                        <input type="hidden" name="knocked_down" value="0">
                        <input class="form-check-input" type="checkbox" name="knocked_down" id="knocked_down" value="1">
                        <label class="form-check-label small" for="knocked_down">
                            {{ __('Knocked Down') }}
                        </label>
                    </div>
                </div>

                {{-- GLASS --}}
                <div class="mb-3">
                    <label class="fw-bold">{{ __('Glass') }}</label>
                    {{-- Combined hidden field for glass_type (computed from the dropdowns) --}}
                    <input type="hidden" name="glass_type" id="glass_type" value="LE3/CLR">

                    @php
                        $goOutside = ($glassOptions['outside'] ?? collect())->toArray();
                        $goMiddle  = ($glassOptions['middle']  ?? collect())->toArray();
                        $goInside  = ($glassOptions['inside']  ?? collect())->toArray();
                        // Fallback defaults if master data is empty
                        if (empty($goOutside)) $goOutside = ['CLR', 'LE3'];
                        if (empty($goInside))  $goInside  = ['CLR', 'LAM'];
                    @endphp

                    <div class="d-flex gap-2 mb-2">
                        <div class="flex-fill">
                            <label class="small text-muted">{{ __('Outside') }}</label>
                            <select class="form-select form-select-sm" id="glassOutside">
                                @foreach($goOutside as $g)
                                    <option value="{{ $g }}" @if($g === 'LE3') selected @endif>{{ $g }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex-fill" id="glassMiddleWrap" style="display:none;">
                            <label class="small text-muted">{{ __('Middle') }}</label>
                            <select class="form-select form-select-sm" id="glassMiddle" name="glass_middle">
                                <option value="">{{ __('Select...') }}</option>
                                @foreach($goMiddle as $g)
                                    <option value="{{ $g }}">{{ $g }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex-fill">
                            <label class="small text-muted">{{ __('Inside') }}</label>
                            <select class="form-select form-select-sm" id="glassInside">
                                @foreach($goInside as $g)
                                    <option value="{{ $g }}" @if($g === 'CLR') selected @endif>{{ $g }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Three Pane checkbox — hidden when series doesn't support it --}}
                    <div class="form-check mb-2" id="threePaneWrap">
                        <input type="checkbox" class="form-check-input" id="hasThreePane" name="three_pane" value="1">
                        <label class="form-check-label small" for="hasThreePane">{{ __('Three Pane') }}</label>
                    </div>
                    {{-- Keep specialty_glass hidden field for backward compat --}}
                    <input type="hidden" name="specialty_glass" id="specialtyGlassSelect" value="">

                    {{-- Pane type availability per series --}}
                    <script>
                        var _seriesPaneTypes = @json($seriesPaneTypes ?? []);
                    </script>

                    {{-- Superspacer hidden (always Superspacer, not shown on form) --}}
                    <input type="hidden" name="spacer" value="Superspacer">

                    <select class="form-select form-select-sm mb-2" name="tempered" id="temperedOption">
                        <option value="">{{ __('Tempered Options') }}</option>
                        <option value="All">{{ __('All') }}</option>
                        <option value="Select">{{ __('Select') }}</option>
                    </select>

                    {{-- Tempered Matrix --}}
                    <div id="temperedMatrix" class="border rounded p-2 mb-2 d-none">
                        <div class="small fw-semibold mb-1">{{ __('Apply Tempered to:') }}</div>
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="small text-muted mb-1">{{ __('Glass Field 1') }}</div>
                                <div class="form-check form-check-sm">
                                    <input class="form-check-input tempered-box" type="checkbox" id="gf1_i" name="tempered_fields[]" value="gf1_i">
                                    <label class="form-check-label small" for="gf1_i">{{ __('Interior') }}</label>
                                </div>
                                <div class="form-check form-check-sm">
                                    <input class="form-check-input tempered-box" type="checkbox" id="gf1_e" name="tempered_fields[]" value="gf1_e">
                                    <label class="form-check-label small" for="gf1_e">{{ __('Exterior') }}</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="small text-muted mb-1">{{ __('Glass Field 2') }}</div>
                                <div class="form-check form-check-sm">
                                    <input class="form-check-input tempered-box" type="checkbox" id="gf2_i" name="tempered_fields[]" value="gf2_i">
                                    <label class="form-check-label small" for="gf2_i">{{ __('Interior') }}</label>
                                </div>
                                <div class="form-check form-check-sm">
                                    <input class="form-check-input tempered-box" type="checkbox" id="gf2_e" name="tempered_fields[]" value="gf2_e">
                                    <label class="form-check-label small" for="gf2_e">{{ __('Exterior') }}</label>
                                </div>
                            </div>
                        </div>
                        <div id="temperedGhosts" class="d-none"></div>
                    </div>
                </div>

                {{-- GRID --}}
                <div class="mb-3">
                    <label class="fw-bold">{{ __('Grid') }}</label>
                    <select class="form-select form-select-sm mb-2" name="grid_pattern" id="gridPatternSelect">
                        <option value="">{{ __('None') }}</option>
                        <option value="Colonial">{{ __('Colonial') }}</option>
                        <option value="Marginal-12">{{ __('Marginal-12') }}</option>
                        <option value="Marginal-18">{{ __('Marginal-18') }}</option>
                        <option value="Queen">{{ __('Queen') }}</option>
                    </select>

                    <select class="form-select form-select-sm" name="grid_profile" id="gridProfileSelect" style="display:none;">
                        <option value="" selected></option>
                        <option value="F-3/4">F-3/4</option>
                        <option value="F-5/8">F-5/8</option>
                        <option value="S-1">S-1</option>
                        <option value="S-5/8">S-5/8</option>
                    </select>
                    <input type="text" class="form-control form-control-sm mt-1" name="grid_detail" id="gridDetailInput"
                           placeholder="{{ __('Enter grid detail (e.g. 2W x 2H)...') }}" style="display:none;" required>
                </div>

                {{-- SHAPES (visible only when PW selected) --}}
                <div class="mb-3" id="shapeSection" style="display:none;">
                    <label class="fw-bold">{{ __('Shapes') }}</label>
                    <input type="hidden" name="shape_definition_id" id="shape_definition_id" value="">
                    <input type="hidden" name="shape_params" id="shape_params" value="">
                    <input type="hidden" name="shape_code" id="shape_code" value="">

                    <div class="form-check form-check-sm mb-2">
                        <input class="form-check-input" type="checkbox" id="isShapedWindow" value="1">
                        <label class="form-check-label small" for="isShapedWindow">{{ __('This is a shaped window') }}</label>
                    </div>

                    <div id="shapeControls" style="display:none;">
                        <div class="d-flex gap-1 align-items-end">
                            <div class="flex-fill">
                                <input type="text" class="form-control form-control-sm" id="shapeNameDisplay"
                                       placeholder="{{ __('No shape selected') }}" readonly
                                       style="background:#f8f9fa; font-size:11px;">
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="openShapePickerBtn"
                                    style="font-size:10px; padding:3px 8px; white-space:nowrap;"
                                    onclick="openShapePicker()">
                                <i class="fas fa-search"></i> {{ __('Browse') }}
                            </button>
                        </div>

                        {{-- Dedicated H1 / W1 shape dimension inputs --}}
                        <div id="shapeDimsContainer" class="mt-2" style="display:none;">
                            <label class="small text-muted mb-1"><i class="fas fa-ruler-combined me-1"></i>{{ __('Shape Dimensions') }}</label>
                            <div class="d-flex gap-2">
                                <div id="shapeDimH1Wrap" style="display:none; flex:1;">
                                    <label class="small" style="font-size:10px;">{{ __('H1 — Height Start') }}</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" step="0.01" min="0" class="form-control form-control-sm"
                                               id="shapeDimH1" placeholder="0" style="font-size:11px;"
                                               oninput="onShapeDimChange()">
                                        <span class="input-group-text" style="font-size:10px;">in</span>
                                    </div>
                                </div>
                                <div id="shapeDimW1Wrap" style="display:none; flex:1;">
                                    <label class="small" style="font-size:10px;">{{ __('W1 — Width Start') }}</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" step="0.01" min="0" class="form-control form-control-sm"
                                               id="shapeDimW1" placeholder="0" style="font-size:11px;"
                                               oninput="onShapeDimChange()">
                                        <span class="input-group-text" style="font-size:10px;">in</span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-muted mt-1" style="font-size:9px;" id="shapeDimHint"></div>
                        </div>

                        {{-- Shape parameters (populated dynamically after shape selection) --}}
                        <div id="shapeParamsContainer" class="mt-2" style="display:none;">
                            <label class="small text-muted">{{ __('Shape Parameters') }}</label>
                            <div id="shapeParamFields"></div>
                        </div>
                        {{-- Shape preview thumbnail --}}
                        <div id="shapePreviewThumb" class="mt-2 text-center" style="display:none;">
                            <svg id="shapePreviewSvg" viewBox="0 0 100 80" width="90" height="70"
                                 style="border:1px solid #e2e8f0; border-radius:4px; background:#f8fafc;"></svg>
                        </div>
                    </div>
                </div>

                {{-- OTHER OPTIONS --}}
                <div class="mb-3">
                    <label class="fw-bold" id="otherOptionsLabel">{{ __('Other Options') }}</label>

                    {{-- Dynamic frame bottom options (show the OTHER frame types as checkboxes) --}}
                    <div id="frameBottomOptions">
                        <div class="form-check form-check-sm" id="frameAlt1Wrap">
                            <input type="hidden" name="retrofit_bottom_only" value="0">
                            <input class="form-check-input" type="checkbox" name="retrofit_bottom_only" id="retrofit_bottom_only" value="1">
                            <label class="form-check-label small" for="retrofit_bottom_only" id="frameAlt1Label">{{ __('Retrofit 2 1/2" Frame Bottom') }}</label>
                        </div>
                        <div class="form-check form-check-sm" id="frameAlt2Wrap">
                            <input type="hidden" name="block_frame_bottom" value="0">
                            <input class="form-check-input" type="checkbox" name="block_frame_bottom" id="block_frame_bottom" value="1">
                            <label class="form-check-label small" for="block_frame_bottom" id="frameAlt2Label">{{ __('Block Frame Bottom') }}</label>
                        </div>
                    </div>

                    <div class="form-check form-check-sm">
                        <input type="hidden" name="no_logo_lock" value="0">
                        <input class="form-check-input" type="checkbox" name="no_logo_lock" id="no_logo_lock" value="1">
                        <label class="form-check-label small" for="no_logo_lock">{{ __('No Logo Lock') }}</label>
                    </div>
                    <div class="form-check form-check-sm">
                        <input type="hidden" name="double_lock" value="0">
                        <input class="form-check-input" type="checkbox" name="double_lock" id="double_lock" value="1">
                        <label class="form-check-label small" for="double_lock">{{ __('2 Locks (Each Sash)') }}</label>
                    </div>
                    <div class="form-check form-check-sm">
                        <input type="hidden" name="custom_lock_position" value="0">
                        <input class="form-check-input" type="checkbox" name="custom_lock_position" id="custom_lock_position" value="1">
                        <label class="form-check-label small" for="custom_lock_position">{{ __('Lock Position Not Standard') }}</label>
                    </div>
                    <div class="form-check form-check-sm">
                        <input type="hidden" name="custom_vent_latch" value="0">
                        <input class="form-check-input" type="checkbox" name="custom_vent_latch" id="custom_vent_latch" value="1">
                        <label class="form-check-label small" for="custom_vent_latch">{{ __('Vent Latch Position Not Standard') }}</label>
                    </div>
                </div>

                {{-- NOTES --}}
                <div class="mb-3">
                    <label class="fw-bold">{{ __('Internal Notes') }}</label>
                    <textarea name="internal_note" class="form-control form-control-sm" rows="2" placeholder="{{ __('Enter internal notes...') }}"></textarea>
                </div>

                {{-- Current Addons Display --}}
                <div class="mb-3">
                    <label class="fw-bold">{{ __('Current Addons') }}</label>
                    <div id="currentAddons" class="small text-muted border rounded p-2">
                        {{ __('None') }}
                    </div>
                </div>
            </div>

            <div class="card-footer bg-light mt-auto">
                <div class="d-flex justify-content-between mb-2">
                    <small>{{ __('Total Price:') }}</small>
                    <strong class="text-primary">$<span id="globalTotalPrice">0.00</span></strong>
                </div>
                <button type="submit" id="addToQuoteBtn" class="btn btn-primary w-100">
                    <i class="fas fa-plus" id="addToQuoteBtnIcon"></i> <span id="addToQuoteBtnText">{{ __('Add') }}</span>
                </button>
                <button type="button" id="cancelEditBtn" class="btn btn-secondary w-100 mt-2 d-none" onclick="cancelEditMode()">
                    <i class="fas fa-times"></i> {{ __('Cancel Edit') }}
                </button>
            </div>
        </form>
    </div>
</div>

        {{-- CARD 2: Quote Details (42%) --}}
<div class="col-md-6">
    <div class="card h-100 d-flex flex-column">
        {{-- Tab Navigation --}}
        <div class="card-header bg-white p-0 border-0" style="overflow:hidden;">
            <ul class="nav nav-tabs card-header-tabs mb-0 px-2 pt-1" id="quoteDetailsTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="items-tab" data-bs-toggle="tab" data-bs-target="#itemsTabPane" type="button" role="tab" aria-controls="itemsTabPane" aria-selected="true">
                        <i class="fas fa-list-alt me-1"></i> {{ __('Items') }}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="customer-tab" data-bs-toggle="tab" data-bs-target="#customerTabPane" type="button" role="tab" aria-controls="customerTabPane" aria-selected="false">
                        <i class="fas fa-user me-1"></i> {{ __('Customer Details') }}
                    </button>
                </li>
                @if(true)
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="discounts-tab" data-bs-toggle="tab" data-bs-target="#discountsTabPane" type="button" role="tab" aria-controls="discountsTabPane" aria-selected="false">
                        <i class="fas fa-percent me-1"></i> {{ __('Discounts') }}
                    </button>
                </li>
                @endif
            </ul>
        </div>

        <div class="card-body p-0 flex-grow-1 overflow-auto">
            <div class="tab-content" id="quoteDetailsTabContent">

                {{-- ═══ TAB 1: Items ═══ --}}
                <div class="tab-pane fade show active p-3" id="itemsTabPane" role="tabpanel" aria-labelledby="items-tab">
                    {{-- Visual Product Navigator --}}
                    @include('quotes.partials.product_navigator')

                    {{-- Series & Configuration Dropdowns --}}
                    <div class="row mb-3">
                        <div class="col-md-5">
                            <label>{{ __('Series') }} <span class="text-danger">*</span></label>
                            <select name="series_id" id="seriesSelect" class="form-control">
                                <option value="">{{ __('Select Series') }}</option>
                                @foreach ($seriesList as $id => $series)
                                <option value="{{ $id }}" {{ (session('selected_series') == $id || (!session('selected_series') && strtoupper(trim($series)) === 'DYNAMIC')) ? 'selected' : '' }}>
                                    {{ $series }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col">
                            <label>{{ __('Configuration') }} <span class="text-danger">*</span></label>
                            <div class="d-flex gap-2">
                                <div id="configSearchWrapper" style="position:relative; flex:1;">
                                    <input type="text" id="configSearchInput" class="form-control" placeholder="{{ __('Type to search configuration...') }}" autocomplete="off">
                                    <select name="series_type_id" id="seriesTypeSelect" style="display:none!important">
                                        <option value="">Select configuration</option>
                                    </select>
                                    <div id="configDropdown" style="display:none; position:absolute; top:100%; left:0; right:0; z-index:9999; background:#fff; border:1px solid #ccc; border-top:none; max-height:200px; overflow-y:auto; box-shadow:0 6px 12px rgba(0,0,0,.2); border-radius:0 0 4px 4px;"></div>
                                </div>
                                <button type="button" class="btn btn-primary flex-shrink-0" data-bs-toggle="modal" data-bs-target="#configLookupModal" title="{{ __('Lookup Configuration') }}" style="height: calc(1.5em + .75rem + 2px); padding: 0 .65rem;">
                                    <i class="fas fa-binoculars text-white"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Items Table --}}
                    <div class="table-responsive">
                        <table id="quoteDetailsTable" class="table table-bordered table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('Item') }}</th>
                                    <th>{{ __('Qty') }}</th>
                                    <th>{{ __('Size') }}</th>
                                    <th>{{ __('Glass') }}</th>
                                    <th>{{ __('Grid') }}</th>
                                    <th>{{ __('Price') }}</th>
                                    <th>{{ __('Total') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($quoteItems as $item)
                                <tr data-id="{{ $item->id }}" class="item-row"
                                    data-item-json="{{ json_encode([
                                        'id' => $item->id,
                                        'series_id' => $item->series_id,
                                        'series_type' => $item->series_type,
                                        'width' => $item->width,
                                        'height' => $item->height,
                                        'qty' => $item->qty,
                                        'price' => $item->price,
                                        'total' => $item->total,
                                        'glass_type' => $item->glass_type ?? 'LE3/CLR',
                                        'glass' => $item->glass,
                                        'grid' => $item->grid,
                                        'grid_pattern' => $item->grid_pattern ?? '',
                                        'grid_profile' => $item->grid_profile ?? '',
                                        'frame_type' => $item->frame_type ?? 'Retrofit',
                                        'fin_type' => $item->fin_type ?? 'Regular',
                                        'color_config' => $item->color_config ?? 'WH-WH',
                                        'color_exterior' => $item->color_exterior ?? 'WH',
                                        'color_interior' => $item->color_interior ?? 'WH',
                                        'spacer' => $item->spacer ?? 'Superspacer',
                                        'tempered' => $item->tempered ?? '',
                                        'specialty_glass' => $item->specialty_glass ?? '',
                                        'knocked_down' => $item->knocked_down ?? 0,
                                        'retrofit_bottom_only' => $item->retrofit_bottom_only ?? 0,
                                        'no_logo_lock' => $item->no_logo_lock ?? 0,
                                        'double_lock' => $item->double_lock ?? 0,
                                        'custom_lock_position' => $item->custom_lock_position ?? 0,
                                        'custom_vent_latch' => $item->custom_vent_latch ?? 0,
                                        'internal_note' => $item->internal_note ?? '',
                                        'shape_definition_id' => $item->shape_definition_id,
                                        'shape_code' => $item->shape_code ?? '',
                                        'shape_params' => $item->shape_params,
                                    ]) }}">
                                    <td class="item-description" data-series-id="{{ $item->series_id }}" data-series-type="{{ $item->series_type }}" data-width="{{ $item->width }}" data-height="{{ $item->height }}">
                                        {{ $item->description }}
                                    </td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm qty-input" 
                                               value="{{ $item->qty }}" style="width: 60px;" 
                                               data-id="{{ $item->id }}" data-price="{{ $item->price }}">
                                    </td>
                                    <td>{{ _wd2_fracLabel($item->width) }} x {{ _wd2_fracLabel($item->height) }}</td>
                                    <td>{{ $item->glass }}</td>
                                    <td>{{ $item->grid }}</td>
                                    <td class="item-price">${{ number_format($item->price, 2) }}</td>
                                    <td class="item-total" data-id="{{ $item->id }}">
                                        ${{ number_format($item->total, 2) }}
                                    </td>
                                    <td class="text-nowrap">
                                        <a href="#" class="text-primary edit-row me-1" data-id="{{ $item->id }}" title="{{ __('Edit') }}">
                                            <i data-feather="edit-2"></i>
                                        </a>
                                        <a href="#" class="text-danger remove-row" data-id="{{ $item->id }}" title="{{ __('Delete') }}">
                                            <i data-feather="trash-2"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Totals --}}
                    <div class="row mt-4">
                        <div class="col-md-6 offset-md-6">
                            <table class="table">
                                <tr>
                                    <th>{{ __('Discount:') }}</th>
                                    <td id="discount-amount">-${{ number_format($quote->discount ?? 0, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('Shipping:') }}</th>
                                    <td>
                                        <input type="number" step="0.01" class="form-control w-25" min="0" 
                                               name="shipping" id="shipping" value="{{ $quote->shipping ?? 0 }}">
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('Subtotal:') }}</th>
                                    <td id="subtotal-amount">${{ number_format($quote->sub_total ?? 0, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('Tax (10.75%):') }}</th>
                                    <td id="tax-amount">${{ number_format($quote->tax ?? 0, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('Total:') }}</th>
                                    <td><strong id="total-amount">${{ number_format($quote->total ?? 0, 2) }}</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- ═══ TAB 2: Customer Details ═══ --}}
                <div class="tab-pane fade p-3" id="customerTabPane" role="tabpanel" aria-labelledby="customer-tab">
                    <div id="customerDetailsContent">
                        @if($quote && $quote->customer)
                            @php $cust = $quote->customer; @endphp

                            {{-- Customer Header --}}
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                <div>
                                    <h5 class="mb-0">{{ $cust->customer_name ?? $quote->customer_name }}</h5>
                                    <small class="text-muted">{{ $quote->quote_number }}</small>
                                </div>
                                <span class="badge bg-primary">{{ $cust->customer_type ?? __('Dealer') }}</span>
                            </div>

                            <div class="row g-3">
                                {{-- Contact Information --}}
                                <div class="col-md-6">
                                    <div class="card bg-light border-0">
                                        <div class="card-body p-3">
                                            <h6 class="fw-bold mb-2"><i class="fas fa-phone me-1 text-primary"></i> {{ __('Contact') }}</h6>
                                            <table class="table table-sm table-borderless mb-0" style="font-size:0.85rem;">
                                                <tr>
                                                    <td class="text-muted" style="width:100px;">{{ __('Phone') }}</td>
                                                    <td class="fw-semibold">{{ $cust->billing_phone ?? '--' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">{{ __('Phone 2') }}</td>
                                                    <td class="fw-semibold">{{ $cust->delivery_phone ?? '--' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">{{ __('Fax') }}</td>
                                                    <td class="fw-semibold">{{ $cust->billing_fax ?? '--' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">{{ __('Email') }}</td>
                                                    <td class="fw-semibold">{{ $cust->email ?? '--' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">{{ __('Contact') }}</td>
                                                    <td class="fw-semibold">{{ $cust->contact_name ?? $quote->contact ?? '--' }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                {{-- Account Info --}}
                                <div class="col-md-6">
                                    <div class="card bg-light border-0">
                                        <div class="card-body p-3">
                                            <h6 class="fw-bold mb-2"><i class="fas fa-file-invoice me-1 text-primary"></i> {{ __('Account') }}</h6>
                                            <table class="table table-sm table-borderless mb-0" style="font-size:0.85rem;">
                                                <tr>
                                                    <td class="text-muted" style="width:100px;">{{ __('Status') }}</td>
                                                    <td class="fw-semibold">{{ ucfirst($cust->status ?? '--') }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">{{ __('Tier') }}</td>
                                                    <td class="fw-semibold">{{ optional($cust->tier)->name ?? '--' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">{{ __('Loyalty') }}</td>
                                                    <td class="fw-semibold">{{ $cust->loyalty_credit ?? '--' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">{{ __('Total Spent') }}</td>
                                                    <td class="fw-semibold">${{ number_format($cust->total_spent ?? 0, 2) }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">{{ __('Quote Via') }}</td>
                                                    <td class="fw-semibold">
                                                        @php
                                                            $via = $cust->receive_quote_via;
                                                            if (is_string($via)) $via = json_decode($via, true);
                                                        @endphp
                                                        {{ is_array($via) ? implode(', ', $via) : ($via ?? '--') }}
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                {{-- Billing Address --}}
                                <div class="col-md-6">
                                    <div class="card bg-light border-0">
                                        <div class="card-body p-3">
                                            <h6 class="fw-bold mb-2"><i class="fas fa-file-invoice-dollar me-1 text-primary"></i> {{ __('Billing Address') }}</h6>
                                            <div style="font-size:0.85rem;">
                                                <div class="fw-semibold">{{ $cust->billing_address ?? '--' }}</div>
                                                @if($cust->billing_address2)
                                                    <div>{{ $cust->billing_address2 }}</div>
                                                @endif
                                                <div>{{ $cust->billing_city ?? '' }}{{ $cust->billing_state ? ', '.$cust->billing_state : '' }} {{ $cust->billing_zip ?? '' }}</div>
                                                @if($cust->billing_country)
                                                    <div>{{ $cust->billing_country }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Delivery Address --}}
                                <div class="col-md-6">
                                    <div class="card bg-light border-0">
                                        <div class="card-body p-3">
                                            <h6 class="fw-bold mb-2"><i class="fas fa-truck me-1 text-primary"></i> {{ __('Delivery Address') }}</h6>
                                            <div style="font-size:0.85rem;">
                                                <div class="fw-semibold">{{ $cust->delivery_address ?? '--' }}</div>
                                                @if($cust->delivery_address2)
                                                    <div>{{ $cust->delivery_address2 }}</div>
                                                @endif
                                                <div>{{ $cust->delivery_city ?? '' }}{{ $cust->delivery_state ? ', '.$cust->delivery_state : '' }} {{ $cust->delivery_zip ?? '' }}</div>
                                                @if($cust->delivery_country)
                                                    <div>{{ $cust->delivery_country }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Notes / Special Instructions --}}
                                <div class="col-12">
                                    <div class="card bg-light border-0">
                                        <div class="card-body p-3">
                                            <h6 class="fw-bold mb-2"><i class="fas fa-sticky-note me-1 text-primary"></i> {{ __('Notes') }}</h6>
                                            <div style="font-size:0.85rem;" class="text-muted">
                                                {{ $cust->notes ?? __('No special notes for this customer.') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        @else
                            {{-- No customer loaded yet --}}
                            <div class="text-center text-muted py-5" id="noCustomerMessage">
                                <i class="fas fa-user-slash fa-3x mb-3"></i>
                                <p>{{ __('No customer loaded. Enter a Customer # in the header and press Enter.') }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- ═══ TAB 3: Discounts ═══ --}}
                @if(true)
                <div class="tab-pane fade p-3" id="discountsTabPane" role="tabpanel" aria-labelledby="discounts-tab">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0"><i class="fas fa-percent me-1 text-primary"></i> {{ __('Per-Item Discounts') }}</h6>
                        <span class="text-muted small">{{ __('Customer Tier:') }} <strong id="discountTierLabel">{{ optional(optional($quote->customer)->tier)->name ?? __('N/A') }} ({{ optional(optional($quote->customer)->tier)->percentage ?? 0 }}%)</strong></span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="discountsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('Item') }}</th>
                                    <th>{{ __('Qty') }}</th>
                                    <th>{{ __('Unit Price') }}</th>
                                    <th>{{ __('Tier Discount') }}</th>
                                    <th style="width:140px;">{{ __('Discount %') }}</th>
                                    <th>{{ __('Discount $') }}</th>
                                    <th>{{ __('Net Total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($quoteItems as $item)
                                @php
                                    $tierPct = optional(optional($quote->customer)->tier)->percentage ?? 0;
                                    $tierDisc = ($tierPct * $item->getRawOriginal('price')) / 100;
                                @endphp
                                @php
                                    $rawPrice = $item->getRawOriginal('price');
                                    $currentDisc = $item->discount ?? $tierDisc;
                                    $currentPct = $rawPrice > 0 ? ($currentDisc / $rawPrice) * 100 : 0;
                                @endphp
                                <tr data-item-id="{{ $item->id }}">
                                    <td>{{ $item->description }}</td>
                                    <td>{{ $item->qty }}</td>
                                    <td>${{ number_format($rawPrice, 2) }}</td>
                                    <td class="tier-disc">{{ number_format($tierPct, 2) }}%</td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <input type="number" step="0.01" min="0" max="100" class="form-control form-control-sm discount-override"
                                                   data-item-id="{{ $item->id }}"
                                                   data-base-price="{{ $rawPrice }}"
                                                   data-qty="{{ $item->qty }}"
                                                   data-tier-pct="{{ $tierPct }}"
                                                   value="{{ number_format($currentPct, 2) }}">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </td>
                                    <td class="final-disc">${{ number_format($currentDisc, 2) }}</td>
                                    <td class="net-total">${{ number_format(($rawPrice - $currentDisc) * $item->qty, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-light fw-bold">
                                    <td colspan="5" class="text-end">{{ __('Total Discounts:') }}</td>
                                    <td id="totalDiscountsSum">-${{ number_format($quote->discount ?? 0, 2) }}</td>
                                    <td id="totalNetSum"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="text-end mt-2">
                        <button type="button" class="btn btn-primary btn-sm" id="applyDiscountsBtn">
                            <i class="fas fa-check me-1"></i> {{ __('Apply Discounts') }}
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm ms-1" id="resetDiscountsBtn">
                            <i class="fas fa-undo me-1"></i> {{ __('Reset to Tier') }}
                        </button>
                    </div>
                </div>
                @endif

            </div>{{-- /tab-content --}}
        </div>

        {{-- ═══ Action Buttons pinned to card bottom ═══ --}}
        <div class="card-footer bg-white border-top d-flex justify-content-between py-3">
            <button type="button" class="btn btn-secondary" id="saveDraftButton">
                <i class="fas fa-save"></i> {{ __('Save Draft') }}
            </button>
            <button type="button" class="btn btn-success" id="submitQuoteButton">
                <i class="fas fa-check-circle"></i> {{ __('Submit Quote') }}
            </button>
        </div>
    </div>
</div>
        {{-- ═══ Submit Quote Modal ═══ --}}
        <div class="modal fade" id="submitQuoteModal" tabindex="-1" aria-labelledby="submitQuoteModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header" style="background: var(--company-accent, #0d6efd); color: #fff;">
                        <h5 class="modal-title" id="submitQuoteModalLabel"><i class="fas fa-paper-plane me-2"></i>{{ __('Send Quote to Customer') }}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted mb-3">{{ __('Choose how the customer would like to receive this quote:') }}</p>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">{{ __('Send Via') }}</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="sendViaEmail" value="email" checked>
                                <label class="form-check-label" for="sendViaEmail">{{ __('Email') }}</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="sendViaPhone" value="phone">
                                <label class="form-check-label" for="sendViaPhone">{{ __('Phone / Text') }}</label>
                            </div>
                        </div>

                        {{-- Email field --}}
                        <div id="submitEmailBlock" class="mb-3">
                            <label class="form-label fw-bold small">{{ __('Email Address') }}</label>
                            <input type="email" class="form-control form-control-sm" id="submitEmail"
                                   value="{{ $quote->customer->email ?? $quote->customer_email ?? '' }}">
                        </div>

                        {{-- Phone selection --}}
                        <div id="submitPhoneBlock" class="mb-3" style="display:none;">
                            <label class="form-label fw-bold small">{{ __('Phone Number') }}</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="submitPhone" id="submitPhoneBilling" value="billing" checked>
                                <label class="form-check-label" for="submitPhoneBilling">
                                    {{ __('Billing Phone:') }} <span class="fw-semibold" id="submitBillingPhoneDisplay">{{ $quote->customer->billing_phone ?? $quote->customer_phone ?? '--' }}</span>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="submitPhone" id="submitPhoneDelivery" value="delivery">
                                <label class="form-check-label" for="submitPhoneDelivery">
                                    {{ __('Shipping Phone:') }} <span class="fw-semibold" id="submitDeliveryPhoneDisplay">{{ $quote->customer->delivery_phone ?? '--' }}</span>
                                </label>
                            </div>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="radio" name="submitPhone" id="submitPhoneCustom" value="custom">
                                <label class="form-check-label" for="submitPhoneCustom">{{ __('Other number') }}</label>
                            </div>
                            <input type="text" class="form-control form-control-sm mt-1" id="submitPhoneCustomInput"
                                   placeholder="{{ __('Enter phone number...') }}" style="display:none;">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="button" class="btn btn-success" id="confirmSubmitQuoteBtn">
                            <i class="fas fa-check-circle me-1"></i> {{ __('Submit Quote') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══ Edit Customer Modal (from quote header) ═══ --}}
        <div class="modal fade" id="editCustomerModal" tabindex="-1" aria-labelledby="editCustomerModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header" style="background: var(--company-accent, #0d6efd); color: #fff;">
                        <h5 class="modal-title" id="editCustomerModalLabel"><i class="fas fa-user-edit me-2"></i>{{ __('Edit Customer Details') }}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="editCustomerModalBody">
                        {{-- Populated dynamically by JS --}}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="button" class="btn btn-success" id="saveCustomerModalBtn">
                            <i class="fas fa-save me-1"></i> {{ __('Save Changes') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- CARD 3: Configuration Preview + Product Spec (33%) --}}
        <div class="col-md-4 d-flex">
            <div class="card w-100 d-flex flex-column">
                <div class="card-body p-2 d-flex flex-column">
                    {{-- Config Preview --}}
                    <div class="d-flex align-items-center justify-content-between mb-2 px-1">
                        <h6 class="card-title mb-0">{{ __('Configuration Preview') }}</h6>
                        <div class="btn-group btn-group-sm" role="group" id="previewViewToggle" aria-label="Preview side">
                            <input type="radio" class="btn-check" name="previewView" id="previewViewExt" value="exterior" checked>
                            <label class="btn btn-outline-primary py-0" for="previewViewExt" style="font-size:11px; color:#fff !important; background:#b30202; border-color:#b30202;">{{ __('Exterior') }}</label>
                            <input type="radio" class="btn-check" name="previewView" id="previewViewInt" value="interior">
                            <label class="btn btn-outline-primary py-0" for="previewViewInt" style="font-size:11px; color:#fff !important; background:#b30202; border-color:#b30202;">{{ __('Interior') }}</label>
                        </div>
                    </div>
                    <div id="window-svg-preview" class="text-center flex-shrink-0 p-3" style="width:100%; height:280px; display:flex; align-items:center; justify-content:center; overflow:visible;">
                        @if(isset($quoteItems) && $quoteItems->count())
                            @include('components.window-diagram', [
                                'type'    => $quoteItems->first()->series_type ?? 'PW',
                                'width'   => $quoteItems->first()->width ?? 36,
                                'height'  => $quoteItems->first()->height ?? 60,
                                'maxSize' => 240,
                            ])
                        @else
                            <p class="text-muted">{{ __('Select series and configuration to see preview') }}</p>
                        @endif
                    </div>

                    {{-- Spacer --}}
                    <div class="flex-shrink-0" style="height:12px;"></div>

                    {{-- Product Spec sub-card (card in card with scroll) --}}
                    <div class="card shadow-sm" style="flex:1 1 0; min-height:0; display:flex; flex-direction:column; overflow:hidden; background:#f8f9fa; border:1px solid #dee2e6; margin-bottom:0;">
                        <div class="card-header bg-dark text-white py-2 px-3" style="flex-shrink:0; font-size:13px; font-weight:700;">
                            <i class="fas fa-clipboard-list me-1"></i> {{ __('Product Spec') }}
                        </div>
                        <div id="product-spec-section" class="card-body p-0" style="flex:1 1 0; overflow-y:auto; overflow-x:hidden; min-height:0;">
                            @include('components.product-spec-card')
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ROW 3: DETAILED QUOTE PREVIEW (Full Width) --}}
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-white d-flex justify-content-between align-items-center no-print">
                    <h4 class="mb-0">{{ __('Quote Preview') }}</h4>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-secondary text-white" id="printQuoteBtn">
                            <i class="fas fa-print"></i> {{ __('Print') }}
                        </button>
                        <button type="button" class="btn btn-sm btn-dark text-white" id="hidePricesPrintBtn">
                            <i class="fas fa-eye-slash"></i> {{ __('Hide Prices & Print') }}
                        </button>
                        <button type="button" class="btn btn-sm btn-primary text-white" id="downloadPdfBtn">
                            <i class="fas fa-download"></i> {{ __('Download PDF') }}
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="quote-preview-wrapper p-4" style="max-height: 80vh; overflow:auto; background:#f5f5f5;">
                        
                        <style>
   
/* ===== Page + Print styles are all inline ===== */
                        .detailed-quote-preview { 
                            background: #fff; 
                            max-width: 900px; 
                            margin: 0 auto; 
                            padding: 30px; 
                            font-family: Arial, sans-serif; 
                            font-size: 11px;
                            line-height: 1.4;
                        }
                        .detailed-quote-preview .header-section {
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-start;
                            margin-bottom: 20px;
                            border-bottom: 2px solid #000;
                            padding-bottom: 15px;
                        }
                        .detailed-quote-preview .header-logo { flex: 0 0 auto; }
                        .detailed-quote-preview .header-logo img { max-height: 110px; max-width: 220px; }
                        .detailed-quote-preview .company-info { flex: 1; display: flex; justify-content: flex-start; padding-left: 30px; font-size: 12px; line-height: 1.6; }
                        .detailed-quote-preview .company-info-inner { text-align: left; }
                        .detailed-quote-preview .hide-prices-header {
                            display: none;
                            justify-content: space-between;
                            margin-bottom: 20px;
                            border-bottom: 2px solid #000;
                            padding-bottom: 15px;
                        }
                        .detailed-quote-preview .hide-prices-header .hp-left { font-size: 12px; line-height: 1.6; }
                        .detailed-quote-preview .hide-prices-header .hp-left strong { font-size: 14px; }
                        .detailed-quote-preview .hide-prices-header .hp-right { text-align: right; font-size: 12px; line-height: 1.6; }
                        .detailed-quote-preview .hide-prices-header .hp-right strong { font-size: 18px; display: block; margin-bottom: 5px; }
                        .detailed-quote-preview .quote-info { text-align: right; font-size: 11px; }
                        .detailed-quote-preview .quote-info strong { font-size: 18px; display: block; margin-bottom: 5px; }
                        .detailed-quote-preview .addresses { display: flex; gap: 20px; margin: 20px 0; }
                        .detailed-quote-preview .address-box { flex: 1; }
                        .detailed-quote-preview .address-title { 
                            font-weight: bold; 
                            font-size: 13px; 
                            margin-bottom: 8px;
                            border-bottom: 1px solid #ccc;
                            padding-bottom: 4px;
                        }
                        .detailed-quote-preview .note-section {
                            background: #f9f9f9;
                            border: 1px solid #ddd;
                            padding: 10px;
                            margin: 20px 0;
                            font-style: italic;
                        }
                        .detailed-quote-preview .line-item {
                            border: 1px solid #ddd;
                            margin-bottom: 25px;
                            padding: 15px;
                            background: #fafafa;
                            page-break-inside: avoid;
                        }
                        .detailed-quote-preview .line-header {
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                            margin-bottom: 12px;
                            border-bottom: 2px solid #333;
                            padding-bottom: 8px;
                        }
                        .detailed-quote-preview .line-number {
                            font-weight: bold;
                            font-size: 14px;
                        }
                        .detailed-quote-preview .line-pricing {
                            text-align: right;
                            font-size: 12px;
                        }
                        .detailed-quote-preview .line-pricing .price { font-weight: bold; font-size: 14px; }
                        .detailed-quote-preview .spec-row {
                            margin: 6px 0;
                            display: flex;
                        }
                        .detailed-quote-preview .spec-label {
                            font-weight: 600;
                            min-width: 200px;
                            color: #333;
                        }
                        .detailed-quote-preview .spec-value {
                            color: #555;
                        }
                        .detailed-quote-preview .window-diagram {
                            float: left;
                            margin-right: 15px;
                            margin-bottom: 10px;
                        }
                        .detailed-quote-preview .glass-details {
                            background: #f0f0f0;
                            border-left: 3px solid #666;
                            padding: 8px 12px;
                            margin: 10px 0;
                            font-size: 10px;
                        }
                        .detailed-quote-preview .totals-section {
                            margin-top: 30px;
                            border-top: 2px solid #000;
                            padding-top: 15px;
                        }
                        .detailed-quote-preview .totals-table {
                            width: 100%;
                            max-width: 400px;
                            margin-left: auto;
                        }
                        .detailed-quote-preview .totals-table tr td {
                            padding: 8px 12px;
                            border: 1px solid #ddd;
                        }
                        .detailed-quote-preview .totals-table tr td:first-child {
                            font-weight: 600;
                            background: #f5f5f5;
                            width: 60%;
                        }
                        .detailed-quote-preview .totals-table tr td:last-child {
                            text-align: right;
                            font-weight: bold;
                        }
                        .detailed-quote-preview .totals-table .grand-total td {
                            background: #e9e9e9;
                            color: #000;
                            font-size: 14px;
                            font-weight: bold;
                            border-top: 2px solid #333;
                        }
                        .detailed-quote-preview .customer-ref {
                            font-style: italic;
                            color: #666;
                            margin-bottom: 10px;
                        }

/* ══════════════════════════════════════════════
   PAGE STYLES — all inline since layout has no styles stack
   ══════════════════════════════════════════════ */
.tempered-locked + label { opacity: .7; }
.addon-item {
    display: flex;
    justify-content: space-between;
    padding: 2px 0;
    font-size: 0.85rem;
}
.badge-price {
    background: #28a745;
    color: white;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 0.75rem;
}
.item-row {
    cursor: pointer;
    transition: background-color 0.2s;
}
.item-row:hover {
    background-color: #f0f8ff;
}
.item-row.selected {
    background-color: #e3f2fd;
    border-left: 3px solid #2196F3;
}
/* ── Searchable Configuration Dropdown ── */
#configSearchWrapper {
    position: relative;
}
.config-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    z-index: 1050;
    background: #fff;
    border: 1px solid #ced4da;
    border-top: none;
    border-radius: 0 0 4px 4px;
    max-height: 200px;
    overflow-y: auto;
    box-shadow: 0 6px 12px rgba(0,0,0,.15);
}
.config-dropdown .cfg-opt {
    padding: 5px 10px;
    cursor: pointer;
    font-size: 12px;
    border-bottom: 1px solid #f0f0f0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.config-dropdown .cfg-opt:last-child {
    border-bottom: none;
}
.config-dropdown .cfg-opt:hover,
.config-dropdown .cfg-opt.active {
    background: #e3f2fd;
}
.config-dropdown .cfg-opt .config-match {
    font-weight: 700;
    color: #1976d2;
}
.config-dropdown .config-no-results {
    padding: 8px 12px;
    color: #999;
    font-size: 12px;
    text-align: center;
}
/* ── Fraction Input Hint ── */
.fraction-input {
    font-family: 'Segoe UI', monospace;
}
.fraction-input:focus {
    border-color: #86b7fe;
}
/* ── Constrain SVG preview in right card ── */
#window-svg-preview svg {
    max-width: 90%;
    max-height: 240px;
    height: auto;
    width: auto;
}
/* ── Product spec section (embedded in config preview card) ── */
#product-spec-section {
    width: 100% !important;
    max-width: 100% !important;
}
#product-spec-section #productSpecCol {
    width: 100% !important;
    padding: 0 !important;
    margin: 0 !important;
}
#product-spec-section .product-spec-inner-card {
    box-shadow: none !important;
    border: none !important;
    border-radius: 0 !important;
}
/* Hide the component's own "Product Spec" header since we have our own */
#product-spec-section .product-spec-inner-card > .card-header {
    display: none !important;
}
#product-spec-section .product-spec-inner-card > .card-body {
    padding: 0 !important;
}
#product-spec-section #specBody {
    max-height: none !important;
    overflow: visible !important;
    background: #fff !important;
}
/* ── Card 2 Tabs ── */
#quoteDetailsTabs {
    border-bottom: 2px solid #dee2e6;
    padding: 0 12px;
}
#quoteDetailsTabs .nav-item {
    margin-bottom: -2px;
}
#quoteDetailsTabs .nav-link {
    font-size: 0.8rem;
    font-weight: 600;
    color: #6c757d;
    border: none;
    border-bottom: 3px solid transparent;
    padding: 10px 16px;
    background: transparent;
    border-radius: 0;
    transition: all 0.2s;
}
#quoteDetailsTabs .nav-link.active {
    color: #dc3545;
    border-bottom-color: #dc3545;
    background: transparent;
}
#quoteDetailsTabs .nav-link:hover:not(.active) {
    color: #495057;
    border-bottom-color: #adb5bd;
    background: transparent;
}
#quoteDetailsTabs .nav-link i {
    font-size: 0.75rem;
}
/* ── Size validation tooltip ── */
.size-error-tooltip {
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    background: #dc3545;
    color: #fff;
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 11px;
    white-space: nowrap;
    z-index: 1000;
    pointer-events: none;
    animation: fadeInTooltip 0.2s ease;
}
.size-error-tooltip::after {
    content: '';
    position: absolute;
    top: 100%;
    left: 50%;
    transform: translateX(-50%);
    border: 5px solid transparent;
    border-top-color: #dc3545;
}
@keyframes fadeInTooltip {
    from { opacity: 0; transform: translateX(-50%) translateY(5px); }
    to { opacity: 1; transform: translateX(-50%) translateY(0); }
}

/* ══════════════════════════════════════════════
   PRINT STYLES — Only show Quote Preview content
   ══════════════════════════════════════════════ */
@media print {

    @page {
        size: auto;
        margin: 0;
    }

    body {
        margin: 0 !important;
        padding: 0 !important;
        background: #fff !important;
    }

    /* Hide everything except the quote preview content */
    .pc-header, .pc-sidebar, .pc-mob-header,
    .loader-bg,
    .navbar, .sidebar, .sidebar-wrapper, nav, footer,
    .page-header, .breadcrumb,
    .container-fluid > .mb-4,
    .container-fluid > .row.mb-4,
    .card-header,
    .card-header.d-flex,
    .no-print,
    .btn,
    .modal {
        display: none !important;
    }

    /* Make all ancestor containers of the preview transparent/full-width */
    .container-fluid,
    .container-fluid > .row,
    .container-fluid > .row > [class*="col"],
    .container-fluid > .row > [class*="col"] > .card,
    .container-fluid > .row > [class*="col"] > .card > .card-body {
        display: block !important;
        width: 100% !important;
        max-width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
        border: none !important;
        box-shadow: none !important;
        background: #fff !important;
        overflow: visible !important;
        position: static !important;
        float: none !important;
        flex: none !important;
    }

    .quote-preview-wrapper {
        max-height: none !important;
        overflow: visible !important;
        background: #fff !important;
        padding: 0 !important;
    }

    .pc-container {
        margin-left: 0 !important;
        padding: 0 !important;
        margin-top: 0 !important;
    }

    .pc-content {
        padding: 0 !important;
        margin: 0 !important;
        margin-top: 0 !important;
    }

    .page-header,
    .container-fluid > .mb-4,
    .container-fluid > .row.mb-4 {
        height: 0 !important;
        min-height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow: hidden !important;
    }

    .card.shadow > .no-print {
        height: 0 !important;
        min-height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow: hidden !important;
    }

    .detailed-quote-preview {
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 10mm !important;
        background: #fff !important;
        font-size: 11px !important;
        box-shadow: none !important;
    }

    .line-item {
        page-break-inside: avoid !important;
    }

    .totals-section {
        page-break-inside: avoid !important;
    }

    /* ── HIDE PRICES MODE ── */
    body.print-hide-prices .line-pricing {
        display: none !important;
    }
    body.print-hide-prices .totals-section,
    body.print-hide-prices .totals-table {
        display: none !important;
    }
    body.print-hide-prices .line-header {
        border-bottom: 1px solid #999 !important;
    }
    /* Hide original header + addresses, show alternate header */
    body.print-hide-prices .header-section {
        display: none !important;
    }
    body.print-hide-prices .addresses {
        display: none !important;
    }
    body.print-hide-prices .hide-prices-header {
        display: flex !important;
    }
    body.print-hide-prices .hide-prices-signature {
        display: block !important;
    }
}
                        </style>

                        <div class="detailed-quote-preview print-area" id="detailedQuoteContent">
                            @php $branding = \App\Helper\BrandingHelper::getQuoteBranding($quote ?? null); @endphp
                            {{-- HEADER --}}
                            <div class="header-section">
                                <div class="header-logo">
                                    @if($branding->logo_path)
                                        <img src="{{ asset($branding->logo_path) }}" alt="Logo">
                                    @endif
                                </div>
                                <div class="company-info">
                                    <div class="company-info-inner">
                                        <strong style="font-size: 14px;">{{ $branding->company_name }}</strong><br>
                                        {{ $branding->address }}<br>
                                        {{ $branding->city }} {{ $branding->state }} {{ $branding->zip }}<br>
                                        Tel: {{ $branding->phone }}<br>
                                        @if($branding->fax)Fax: {{ $branding->fax }}<br>@endif
                                        {{ $branding->website }}
                                    </div>
                                </div>
                                <div class="quote-info">
                                    <strong>Quotation# {{ $quote->quote_number ?? '' }}</strong>
                                    Sales Person: {{ $quote->entered_by ?? '' }}<br>
                                    Office Ext. 111<br>
                                    Email: {{ $branding->email }}<br>
                                    Request Date: {{ $quote ? \Carbon\Carbon::parse($quote->entry_date)->format('m/d/Y') : '' }}
                                </div>
                            </div>

                            {{-- ALTERNATE HEADER (shown only in Hide Prices print mode) --}}
                            <div class="hide-prices-header">
                                <div class="hp-left">
                                    <strong>{{ $quote->customer_name ?? '' }}</strong><br>
                                    @if($quote && $quote->customer)
                                        {{ $quote->customer->billing_address ?? '' }}<br>
                                        {{ $quote->customer->billing_city ?? '' }}, {{ $quote->customer->billing_state ?? '' }} {{ $quote->customer->billing_zip ?? '' }}<br>
                                        @if($quote->customer->delivery_address)
                                            <br><span style="font-weight:600; text-decoration:underline;">Ship To:</span><br>
                                            {{ $quote->customer->delivery_address }}<br>
                                            {{ $quote->customer->delivery_city ?? '' }}, {{ $quote->customer->delivery_state ?? '' }} {{ $quote->customer->delivery_zip ?? '' }}
                                        @endif
                                    @endif
                                </div>
                                <div class="hp-right">
                                    <strong>Quotation# {{ $quote->quote_number ?? '' }}</strong>
                                    PO: {{ $quote->reference ?? '—' }}
                                </div>
                            </div>

                            {{-- ADDRESSES --}}
                            <div class="addresses">
                                <div class="address-box">
                                    <div class="address-title">Billing Address</div>
                                    <div id="preview-billing-address">
                                        <strong>{{ $quote->customer_name ?? '' }}</strong><br>
                                        @if($quote && $quote->customer)
                                            {{ $quote->customer->billing_address ?? '' }}<br>
                                            {{ $quote->customer->billing_city ?? '' }}, {{ $quote->customer->billing_state ?? '' }} {{ $quote->customer->billing_zip ?? '' }}
                                        @endif
                                    </div>
                                </div>
                                <div class="address-box">
                                    <div class="address-title">Delivery Address</div>
                                    <div id="preview-shipping-address">
                                        <strong>{{ $quote->customer_name ?? '' }}</strong><br>
                                        @if($quote && $quote->customer)
                                            {{ $quote->customer->delivery_address ?? '' }}<br>
                                            {{ $quote->customer->delivery_city ?? '' }}, {{ $quote->customer->delivery_state ?? '' }} {{ $quote->customer->delivery_zip ?? '' }}
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- NOTE --}}
                            <div class="note-section">
                                <strong>Note:</strong> {{ $quote->notes ?? 'No special notes' }}
                            </div>

                            {{-- LINE ITEMS --}}
                            <div id="detailed-line-items">
                                @foreach($quoteItems as $index => $item)
                                @php
                                    // ── Resolve NFRC + glass panes per item ──
                                    $gt = $item->glass_type ?? 'CLR/CLR';
                                    $itemNfrc = $nfrcMap[$gt] ?? $defaultNfrc;
                                    $itemPane = $glassPaneMap[$gt] ?? $defaultPane;
                                    // Format the glass type label – incorporate specialty glass if set
                                    $gtParts = explode('/', $gt);
                                    if (!empty($item->specialty_glass) && $item->specialty_glass !== 'None') {
                                        $gtParts[1] = $item->specialty_glass;
                                    }
                                    $glassLabel = implode(' / ', $gtParts);
                                    // Also adjust pane descriptions for specialty glass
                                    if (!empty($item->specialty_glass) && $item->specialty_glass !== 'None') {
                                        $sg = strtoupper($item->specialty_glass);
                                        $itemPane['f1'] = preg_replace('/\/\s*\S+$/', '/ 3.1MM ' . $sg, $itemPane['f1']);
                                        $itemPane['f2'] = preg_replace('/\/\s*\S+$/', '/ 3.1MM ' . $sg, $itemPane['f2']);
                                    }
                                @endphp
                                <div class="line-item" data-item-id="{{ $item->id }}">
                                    <div class="line-header">
                                        <div class="line-number">Line {{ $index + 1 }}</div>
                                        <div class="line-pricing price-field">
                                            <div>List: ${{ number_format($item->price, 2) }}</div>
                                            <div>Unit(Disc): ${{ number_format($item->price, 2) }}</div>
                                            <div class="preview-item-qty">Qty: {{ $item->qty }}</div>
                                            <div class="price preview-item-total">Price: ${{ number_format($item->total, 2) }}</div>
                                        </div>
                                    </div>

                                    {{-- VINYL WINDOW DIAGRAM --}}
                                    <div class="window-diagram">
                                        @include('components.window-diagram', [
                                            'type'    => $item->series_type ?? 'PW',
                                            'width'   => $item->width ?? 36,
                                            'height'  => $item->height ?? 60,
                                            'maxSize' => 120,
                                        ])
                                        <div style="text-align: center; margin-top: 3px; font-size: 9px; font-weight: bold;">
                                            Outside View
                                        </div>
                                    </div>

                                    {{-- Product Specifications --}}
                                    <div style="overflow: hidden;">
                                        <div class="spec-row">
                                            <div class="spec-label">PRODUCT:</div>
                                            <div class="spec-value">2101-VINYL DYNAMIC SLIDING WINDOW</div>
                                        </div>
                                        <div class="spec-row">
                                            <div class="spec-label">UNIT:</div>
                                            <div class="spec-value">{{ $item->series_type ?? 'IM-XO' }}, SIZE: W {{ _wd2_fracLabel($item->width) }} X H {{ _wd2_fracLabel($item->height) }}</div>
                                        </div>
                                        <div class="spec-row">
                                            <div class="spec-label">MATERIAL:</div>
                                            <div class="spec-value">MULTI-CHAMBER VINYL PROFILE</div>
                                        </div>
                                        <div class="spec-row">
                                            <div class="spec-label">FRAME:</div>
                                            <div class="spec-value">{{ strtoupper($item->frame_type ?? 'RETROFIT 1 3/4"') }}</div>
                                        </div>
                                        <div class="spec-row">
                                            <div class="spec-label">EXTERIOR/INTERIOR FINISH:</div>
                                            <div class="spec-value">
                                                @php
                                                    $extName = $item->color_exterior
                                                        ? (\App\Models\Master\Colors\ExteriorColor::where('code', $item->color_exterior)->value('name')
                                                           ?? \App\Models\Master\Colors\LaminateColor::where('code', $item->color_exterior)->value('name')
                                                           ?? strtoupper($item->color_exterior))
                                                        : 'WHITE';
                                                    $intName = $item->color_interior
                                                        ? (\App\Models\Master\Colors\InteriorColor::where('code', $item->color_interior)->value('name')
                                                           ?? \App\Models\Master\Colors\LaminateColor::where('code', $item->color_interior)->value('name')
                                                           ?? strtoupper($item->color_interior))
                                                        : 'WHITE';
                                                @endphp
                                                {{ strtoupper($extName) }} / {{ strtoupper($intName) }}
                                            </div>
                                        </div>
                                        <div class="spec-row">
                                            <div class="spec-label">GRID TYPE:</div>
                                            <div class="spec-value">
                                                @if($item->grid && $item->grid !== 'None' && $item->grid !== 'N/A')
                                                    {{ $item->grid_profile ?? 'S-1' }}, 2W by 2H Grid
                                                @else
                                                    N/A
                                                @endif
                                            </div>
                                        </div>

                                        @if($item->grid && $item->grid !== 'None' && $item->grid !== 'N/A')
                                        <div class="customer-ref">
                                            <em>Cus. Ref: No charge on grids</em>
                                        </div>
                                        @endif

                                        {{-- ═══ DYNAMIC GLASS OPTIONS ═══ --}}
                                        <div class="glass-details">
                                            <div style="font-weight: bold; margin-bottom: 5px;">*****GLASS OPTIONS*****</div>
                                            <div>GLASS TYPE: {{ $glassLabel }}</div>
                                            <div>FIELD 1: {{ $itemPane['f1'] }}, IG THICK: 3/4"</div>
                                            <div>FIELD 2: {{ $itemPane['f2'] }}, IG THICK: 3/4"</div>
                                            <div>GAS TYPE: ARGON FILLED, SPACER: {{ strtoupper($item->spacer ?? 'SUPERSPACER') }}</div>
                                            @if($item->internal_note)
                                            <div style="margin-top: 5px; font-size: 10px; color: #c00;">
                                                <strong>*****SPECIAL NOTES*****</strong><br>
                                                {{ $item->internal_note }}
                                            </div>
                                            @endif
                                        </div>

                                        {{-- ═══ DYNAMIC NFRC VALUES ═══ --}}
                                        <div style="margin-top: 8px; font-size: 10px; color: #555;">
                                            <strong>*******NFRC********</strong><br>
                                            UFACTOR: {{ $itemNfrc['u'] }}, SHGC: {{ $itemNfrc['s'] }}, VT: {{ $itemNfrc['v'] }}, CR: {{ $itemNfrc['c'] }}
                                        </div>
                                    </div>
                                </div>
                                @endforeach

                                @if($quoteItems->isEmpty())
                                <div class="text-center text-muted py-5">
                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                    <p>{{ __('No items added to this quote yet.') }}</p>
                                </div>
                                @endif
                            </div>

                            {{-- TOTALS SECTION --}}
                            @php
                                $calcSubtotal = $quoteItems->sum('total');
                                $calcTaxRate = 0.1075;
                                $calcTax = $calcSubtotal * $calcTaxRate;
                                $calcGrandTotal = $calcSubtotal + $calcTax;
                            @endphp
                            <div class="totals-section">
                                <div style="text-align: right; margin-bottom: 15px;">
                                    <strong>{{ __('TOTAL QTY:') }} <span id="preview-total-qty">{{ $quoteItems->sum('qty') }}</span></strong>
                                </div>

                                <table class="totals-table price-field">
                                    <tr>
                                        <td>{{ __('Total List Price:') }}</td>
                                        <td id="preview-list-price">${{ number_format($calcSubtotal, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Sub Total:') }}</td>
                                        <td id="preview-sub-total">${{ number_format($calcSubtotal, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Tax Rate:') }}</td>
                                        <td>10.75%</td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Tax:') }}</td>
                                        <td id="preview-tax">${{ number_format($calcTax, 2) }}</td>
                                    </tr>
                                    <tr class="grand-total">
                                        <td>{{ __('Total Sales Price:') }}</td>
                                        <td id="preview-grand-total">${{ number_format($calcGrandTotal, 2) }}</td>
                                    </tr>
                                </table>
                            </div>

                            @include('quotes._pdf_agreement')

                            {{-- Signature Block (visible only in Hide Prices print) --}}
                            <div class="hide-prices-signature" style="display:none;">
                                <div style="margin-top: 40px; padding-top: 15px; font-size: 11px;">
                                    <div style="display:flex; justify-content:space-between; gap: 40px;">
                                        <div style="flex:1;">
                                            <div style="border-bottom: 1px solid #000; margin-bottom: 4px; min-height: 30px;"></div>
                                            <strong>{{ __('Customer Signature') }}</strong>
                                        </div>
                                        <div style="width: 200px;">
                                            <div style="border-bottom: 1px solid #000; margin-bottom: 4px; min-height: 30px;"></div>
                                            <strong>{{ __('Date') }}</strong>
                                        </div>
                                    </div>
                                    <div style="display:flex; justify-content:space-between; gap: 40px; margin-top: 25px;">
                                        <div style="flex:1;">
                                            <div style="border-bottom: 1px solid #000; margin-bottom: 4px; min-height: 30px;"></div>
                                            <strong>{{ __('Printed Name') }}</strong>
                                        </div>
                                        <div style="width: 200px;">
                                            <div style="border-bottom: 1px solid #000; margin-bottom: 4px; min-height: 30px;"></div>
                                            <strong>{{ __('Date') }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>
@if($quote)
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ══════════════════════════════════════════════
    // GRID PATTERN → show/hide grid profile select
    // ══════════════════════════════════════════════
    var gridPatternEl = document.getElementById('gridPatternSelect');
    var gridProfileEl = document.getElementById('gridProfileSelect');
    var gridDetailEl = document.getElementById('gridDetailInput');
    if (gridPatternEl && gridProfileEl) {
        function toggleGridProfile() {
            if (gridPatternEl.value) {
                gridProfileEl.style.display = '';
                if (!gridProfileEl.value) gridProfileEl.value = 'F-3/4';
                // Show detail input for any grid pattern selected
                if (gridDetailEl) { gridDetailEl.style.display = ''; gridDetailEl.required = true; }
            } else {
                gridProfileEl.style.display = 'none';
                gridProfileEl.value = '';
                if (gridDetailEl) { gridDetailEl.style.display = 'none'; gridDetailEl.value = ''; gridDetailEl.required = false; }
            }
        }
        gridPatternEl.addEventListener('change', toggleGridProfile);
        toggleGridProfile(); // run on load
    }

    document.getElementById('printQuoteBtn')?.addEventListener('click', function() {
        window.print();
    });

    document.getElementById('hidePricesPrintBtn')?.addEventListener('click', function() {
        document.body.classList.add('print-hide-prices');
        window.print();
        // Remove the class after printing so screen view is unaffected
        window.addEventListener('afterprint', function onAfter() {
            document.body.classList.remove('print-hide-prices');
            window.removeEventListener('afterprint', onAfter);
        });
    });

    document.getElementById('downloadPdfBtn')?.addEventListener('click', function() {
        var btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';

        var element = document.getElementById('detailedQuoteContent');
        var wrapper = document.querySelector('.quote-preview-wrapper');

        // Temporarily remove scroll constraints so html2canvas can capture full content
        var origMaxHeight = wrapper.style.maxHeight;
        var origOverflow = wrapper.style.overflow;
        wrapper.style.maxHeight = 'none';
        wrapper.style.overflow = 'visible';

        var opt = {
            margin:       [10, 10, 10, 10],
            filename:     'Quote_{{ $quote->quote_number ?? '' }}.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2, useCORS: true, logging: false, scrollY: 0, windowHeight: element.scrollHeight },
            jsPDF:        { unit: 'mm', format: 'letter', orientation: 'portrait' },
            pagebreak:    { mode: ['avoid-all', 'css', 'legacy'] }
        };

        html2pdf().set(opt).from(element).save().then(function() {
            // Restore scroll constraints
            wrapper.style.maxHeight = origMaxHeight;
            wrapper.style.overflow = origOverflow;
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-download"></i> Download PDF';
        }).catch(function(err) {
            console.error('PDF generation error:', err);
            wrapper.style.maxHeight = origMaxHeight;
            wrapper.style.overflow = origOverflow;
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-download"></i> Download PDF';
            alert('Failed to generate PDF. Please try again.');
        });
    });
});
</script>
@endif

{{-- Configuration Lookup Modal --}}
<div class="modal fade" id="configLookupModal" tabindex="-1" aria-labelledby="configLookupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title" id="configLookupModalLabel"><i class="fas fa-binoculars me-2"></i>{{ __('Configuration Lookup') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="row g-0" style="min-height:500px;">
                    {{-- Sidebar: Product Type categories --}}
                    <div class="col-md-3 border-end" style="background:#f8f9fa;">
                        <div class="p-3">
                            <h6 class="fw-bold mb-2">{{ __('Product Types') }}</h6>
                            <input type="text" id="configLookupCategorySearch" class="form-control form-control-sm mb-2" placeholder="{{ __('Filter...') }}">
                        </div>
                        <div id="configLookupSidebar" class="nav flex-column nav-pills px-2 pb-3" style="max-height:420px; overflow-y:auto;">
                            <button class="nav-link active text-start py-2 px-3 mb-1 rounded config-category-btn" data-category="all" type="button">
                                {{ __('All Configurations') }}
                            </button>
                        </div>
                    </div>
                    {{-- Main: Configuration cards --}}
                    <div class="col-md-9">
                        <div class="p-3">
                            <input type="text" id="configLookupSearch" class="form-control form-control-sm mb-3" placeholder="{{ __('Search configurations...') }}">
                            <div id="configLookupGrid" class="row g-2" style="max-height:430px; overflow-y:auto;">
                                <div class="text-center text-muted py-5">{{ __('Select a series first to load configurations.') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-primary btn-sm" id="configLookupApplyBtn" disabled>
                    <i class="fas fa-check me-1"></i>{{ __('Apply Selection') }}
                </button>
            </div>
        </div>
    </div>
</div>

@if($showSalesNav ?? false)
    </div>{{-- /.sm-main-content --}}
</div>{{-- /.sm-shell --}}
@endif

{{-- ═══════════════════════════════════════════════════════════
     SHAPE PICKER MODAL
     ═══════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="shapePickerModal" tabindex="-1" aria-labelledby="shapePickerLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title" id="shapePickerLabel">
                    <i class="fas fa-shapes me-2 text-primary"></i>Select Window Shape
                </h6>
                <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" style="display:flex; min-height:450px; max-height:65vh;">
                {{-- Left: Detail panel (like Cantor) --}}
                <div id="shapeDetailPanel" style="width:220px; min-width:220px; border-right:1px solid #e2e8f0; padding:12px; overflow-y:auto; background:#f8fafc; display:none;">
                    <div id="shapeDetailPreview" class="text-center mb-2" style="height:130px; display:flex; align-items:center; justify-content:center; background:#fff; border:1px solid #e2e8f0; border-radius:6px;"></div>
                    <div class="small">
                        <div class="fw-bold mb-1" id="shapeDetailCode" style="color:#1B4F72;">--</div>
                        <div class="text-muted mb-2" id="shapeDetailName" style="font-size:11px;">--</div>
                        <div style="font-size:10px; color:#64748b;">
                            <div><strong>Category:</strong> <span id="shapeDetailCat">--</span></div>
                            <div><strong>Vertices:</strong> <span id="shapeDetailVerts">--</span></div>
                            <div><strong>Has Arc:</strong> <span id="shapeDetailArc">--</span></div>
                            <div><strong>Parametric:</strong> <span id="shapeDetailParam">--</span></div>
                        </div>
                    </div>
                </div>

                {{-- Right: Main content --}}
                <div style="flex:1; min-width:0; display:flex; flex-direction:column; overflow:hidden;">
                    {{-- Toolbar: view toggle + category filter --}}
                    <div class="px-3 pt-2 pb-1 border-bottom" style="background:#fff;">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-secondary active" id="viewMiniBtn" onclick="window._shapeViewMode('mini')" title="{{ __('Miniature View') }}">
                                    <i class="fas fa-th"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="viewTableBtn" onclick="window._shapeViewMode('table')" title="{{ __('Table View') }}">
                                    <i class="fas fa-list"></i>
                                </button>
                            </div>
                            <div class="flex-fill">
                                <input type="text" class="form-control form-control-sm" id="shapeSearchInput"
                                       placeholder="{{ __('Search shapes...') }}" style="font-size:11px;"
                                       oninput="window._shapeSearch(this.value)">
                            </div>
                        </div>
                        <ul class="nav nav-pills nav-fill mb-1" id="shapeCategoryTabs" style="font-size:11px;"></ul>
                    </div>

                    {{-- Shape content area --}}
                    <div style="flex:1; overflow-y:auto; padding:10px 14px;">
                        <div id="shapeGrid" class="row g-2"></div>
                        <div id="shapeTableView" style="display:none;">
                            <table class="table table-sm table-hover mb-0" style="font-size:12px;">
                                <thead style="position:sticky; top:0; background:#f1f5f9; z-index:1;">
                                    <tr>
                                        <th style="width:80px;">{{ __('Macro') }}</th>
                                        <th>{{ __('Designation') }}</th>
                                        <th style="width:90px;">{{ __('Category') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="shapeTableBody"></tbody>
                            </table>
                        </div>
                        {{-- Loading spinner --}}
                        <div id="shapeGridLoading" class="text-center py-4">
                            <div class="spinner-border spinner-border-sm text-primary"></div>
                            <div class="small text-muted mt-1">Loading shapes...</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2" id="shapePickerFooter" style="display:none;">
                <div class="d-flex align-items-center gap-2 w-100">
                    <div class="flex-fill">
                        <small class="text-muted">Selected: <strong id="pickerSelectedName">--</strong></small>
                    </div>
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="button" class="btn btn-sm btn-primary" id="confirmShapeBtn" onclick="confirmShapeSelection()">
                        <i class="fas fa-check me-1"></i>Select Shape
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Shape picker styles */
.shape-pick-card { cursor:pointer; border:2px solid #e2e8f0; border-radius:8px; padding:10px 8px;
    text-align:center; transition:all .15s; background:#fff; min-height:110px; }
.shape-pick-card:hover { border-color:#1B4F72; background:#EBF5FB; transform:translateY(-1px);
    box-shadow:0 2px 8px rgba(27,79,114,.12); }
.shape-pick-card.selected { border-color:#1B4F72; background:#D4E6F1;
    box-shadow:0 0 0 2px #1B4F72; }
.shape-pick-card .shape-icon { height:60px; display:flex; align-items:center; justify-content:center; margin-bottom:4px; }
.shape-pick-card .shape-label { font-size:10px; line-height:1.2; color:#334155; font-weight:500; }
.shape-pick-card.selected .shape-label { color:#1B4F72; font-weight:700; }
#shapeCategoryTabs .nav-link { font-size:11px; padding:4px 8px; color:#64748b; border:1px solid #e2e8f0; }
#shapeCategoryTabs .nav-link.active { background:var(--burg, #b30202); border-color:var(--burg, #b30202); color:#fff; }
.shape-group-header { font-size:11px; font-weight:700; color:#1B4F72; padding:8px 0 4px; border-bottom:1px solid #e2e8f0;
    margin-bottom:6px; text-transform:uppercase; letter-spacing:0.3px; }
#shapeTableBody tr { cursor:pointer; }
#shapeTableBody tr:hover { background:#EBF5FB !important; }
#shapeTableBody tr.selected { background:#D4E6F1 !important; font-weight:600; }
#shapeTableBody .group-header-row td { background:#f1f5f9; font-weight:700; font-size:11px; color:#1B4F72;
    text-transform:uppercase; letter-spacing:0.3px; padding:6px 10px; }
</style>

<script>
(function(){
    // ── Shape Picker State ──
    var _shapeCategories = [];
    var _shapesLoaded = false;
    var _pendingPickShape = null; // temporarily selected in modal
    var _shapeViewModeVal = 'mini'; // 'mini' or 'table'
    var _shapeSearchTerm = '';

    // ── Shape checkbox toggle ──
    var cb = document.getElementById('isShapedWindow');
    if (cb) {
        cb.addEventListener('change', function() {
            var ctrls = document.getElementById('shapeControls');
            if (ctrls) ctrls.style.display = this.checked ? '' : 'none';
            if (!this.checked) clearShapeSelection();
        });
    }

    // ── Check if shape section should be visible based on current config ──
    window.checkShapeSectionVisibility = function() {
        var shapeSection = document.getElementById('shapeSection');
        if (!shapeSection) return;

        // Determine if current config is PW-type — check multiple sources
        var configVal = (document.getElementById('seriesTypeSelect')?.value || '').toUpperCase();
        var configSearch = (document.getElementById('configSearchInput')?.value || '').toUpperCase();
        var activeWindowType = (window._activeWindowTypeCode || '').toUpperCase();
        // Also check the currentType variable (set synchronously during edit restore)
        var curType = (typeof currentType !== 'undefined' && currentType ? currentType : '').toUpperCase();

        var isPW = activeWindowType === 'PW'
                || configVal.indexOf('PW') >= 0
                || configSearch.indexOf('PW') >= 0
                || curType.indexOf('PW') >= 0;

        if (isPW) {
            shapeSection.style.display = '';
        } else {
            shapeSection.style.display = 'none';
            clearShapeSelection();
        }
    };

    // ── Global functions ──

    /**
     * Determine which dedicated shape dimensions (H1/W1) a shape code needs.
     * Returns { needsH1: bool, needsW1: bool, h1Label: str, w1Label: str, hint: str }
     */
    window.getShapeDimNeeds = function(code) {
        if (!code) return { needsH1: false, needsW1: false };
        code = code.toUpperCase();

        // RAKE shapes: need H1 (slope start height)
        if (code.indexOf('RAKE') >= 0 || code === 'S15' || code === 'S17' || code === 'S23' || code === 'S25' || code === 'S27' || code === 'S29') {
            return { needsH1: true, needsW1: false,
                     h1Label: '{{ __("H1 — Slope Start Height") }}',
                     hint: '{{ __("Height where the diagonal slope begins on the window") }}' };
        }

        // CLIPPED CORNER shapes: need both H1 and W1
        if (code.indexOf('CLIP') >= 0 || code === 'S04' || code === 'S06' || code === 'S09' || code === 'S12') {
            return { needsH1: true, needsW1: true,
                     h1Label: '{{ __("H1 — Clip Height") }}',
                     w1Label: '{{ __("W1 — Clip Width") }}',
                     hint: '{{ __("Size of the clipped corner on the window") }}' };
        }

        // PEAK shapes: need H1 (peak height)
        if (code.indexOf('PEAK') >= 0 || code === 'S31') {
            return { needsH1: true, needsW1: false,
                     h1Label: '{{ __("H1 — Peak Height") }}',
                     hint: '{{ __("Height of the peak from the top of the window") }}' };
        }

        // TRAPEZOID shapes: need W1 (top width)
        if (code.indexOf('TRAP') >= 0 || code === 'S33') {
            return { needsH1: false, needsW1: true,
                     w1Label: '{{ __("W1 — Top Width") }}',
                     hint: '{{ __("Width of the top edge of the trapezoid") }}' };
        }

        // ARCH / CAMBER / ELLIPSE shapes: need H1 (rise/curve height)
        if (code.indexOf('ARCH') >= 0 || code === 'M2' || code === 'M5') {
            return { needsH1: true, needsW1: false,
                     h1Label: '{{ __("H1 — Arc Rise Height") }}',
                     hint: '{{ __("Height of the arch curve from where it starts to the top") }}' };
        }

        // HALF ROUND extended: need H1 (height of straight portion below arc)
        if (code === 'HALF_ROUND_EXT' || code === 'HALF_ROUND_GOTHIC' || code === 'S03' || code === 'S49') {
            return { needsH1: true, needsW1: false,
                     h1Label: '{{ __("H1 — Straight Height") }}',
                     hint: '{{ __("Height of the straight portion below the arc") }}' };
        }

        // QUARTER ROUND extended: need H1 (height of straight portion)
        if (code === 'QTR_EXT_RIGHT' || code === 'QTR_EXT_LEFT' || code === 'S01' || code === 'S02') {
            return { needsH1: true, needsW1: false,
                     h1Label: '{{ __("H1 — Straight Height") }}',
                     hint: '{{ __("Height of the straight portion below the quarter round") }}' };
        }

        // OCTAGON: need both H1 and W1 (flat dimensions)
        if (code.indexOf('OCTAGON') >= 0 || code === 'S45') {
            return { needsH1: true, needsW1: true,
                     h1Label: '{{ __("H1 — Vertical Flat") }}',
                     w1Label: '{{ __("W1 — Horizontal Flat") }}',
                     hint: '{{ __("Length of the flat sides on the octagon") }}' };
        }

        // HEXAGON: need H1 (flat height)
        if (code.indexOf('HEXAGON') >= 0 || code === 'S59') {
            return { needsH1: true, needsW1: false,
                     h1Label: '{{ __("H1 — Flat Height") }}',
                     hint: '{{ __("Height of the top/bottom flat portion") }}' };
        }

        return { needsH1: false, needsW1: false };
    };

    /**
     * Show/hide the dedicated shape dimension inputs based on shape code.
     */
    window.updateShapeDimVisibility = function(shapeCode) {
        var needs = getShapeDimNeeds(shapeCode);
        var container = document.getElementById('shapeDimsContainer');
        var h1Wrap = document.getElementById('shapeDimH1Wrap');
        var w1Wrap = document.getElementById('shapeDimW1Wrap');
        var hint = document.getElementById('shapeDimHint');

        if (!needs.needsH1 && !needs.needsW1) {
            if (container) container.style.display = 'none';
            return;
        }

        if (container) container.style.display = '';
        if (h1Wrap) {
            h1Wrap.style.display = needs.needsH1 ? '' : 'none';
            if (needs.needsH1) {
                h1Wrap.querySelector('label').textContent = needs.h1Label || '{{ __("H1 — Height Start") }}';
            }
        }
        if (w1Wrap) {
            w1Wrap.style.display = needs.needsW1 ? '' : 'none';
            if (needs.needsW1) {
                w1Wrap.querySelector('label').textContent = needs.w1Label || '{{ __("W1 — Width Start") }}';
            }
        }
        if (hint) hint.textContent = needs.hint || '';
    };

    /**
     * Called when H1 or W1 dedicated inputs change — delegates to updateShapeParams which merges all sources.
     */
    window.onShapeDimChange = function() {
        updateShapeParams();
    };

    window.clearShapeSelection = function() {
        document.getElementById('shape_definition_id').value = '';
        document.getElementById('shape_params').value = '';
        document.getElementById('shape_code').value = '';
        var nameEl = document.getElementById('shapeNameDisplay');
        if (nameEl) nameEl.value = '';
        var cb = document.getElementById('isShapedWindow');
        if (cb) { cb.checked = false; }
        var ctrls = document.getElementById('shapeControls');
        if (ctrls) ctrls.style.display = 'none';
        var paramsC = document.getElementById('shapeParamsContainer');
        if (paramsC) paramsC.style.display = 'none';
        var prevThumb = document.getElementById('shapePreviewThumb');
        if (prevThumb) prevThumb.style.display = 'none';
        // Clear & hide dedicated shape dims
        var dimsC = document.getElementById('shapeDimsContainer');
        if (dimsC) dimsC.style.display = 'none';
        var dimH1 = document.getElementById('shapeDimH1');
        var dimW1 = document.getElementById('shapeDimW1');
        if (dimH1) dimH1.value = '';
        if (dimW1) dimW1.value = '';
        _pendingPickShape = null;

        // Remove shape overlay from the configuration preview
        applyShapeToPreview();
    };

    window.openShapePicker = function() {
        var modal = new bootstrap.Modal(document.getElementById('shapePickerModal'));
        modal.show();
        // Reset search
        var searchEl = document.getElementById('shapeSearchInput');
        if (searchEl) searchEl.value = '';
        _shapeSearchTerm = '';

        if (!_shapesLoaded) {
            loadShapeData();
        } else {
            // Re-render to apply any reset
            window._shapeSelectCat('all');
        }
    };

    function loadShapeData() {
        var loading = document.getElementById('shapeGridLoading');
        if (loading) loading.style.display = '';

        fetch("{{ route('installer.quotes.shapes') }}")
            .then(r => r.json())
            .then(data => {
                _shapeCategories = data;
                _shapesLoaded = true;
                if (loading) loading.style.display = 'none';
                renderCategoryTabs();
                if (data.length > 0) window._shapeSelectCat('all');
            })
            .catch(err => {
                console.error('Shape picker load error:', err);
                if (loading) loading.innerHTML = '<div class="text-danger small">Failed to load shapes</div>';
            });
    }

    function renderCategoryTabs() {
        var tabsEl = document.getElementById('shapeCategoryTabs');
        tabsEl.innerHTML = '';
        // "All" tab
        var allLi = document.createElement('li');
        allLi.className = 'nav-item';
        allLi.innerHTML = '<button class="nav-link active" data-cat-idx="all" onclick="window._shapeSelectCat(\'all\')">{{ __('All') }}</button>';
        tabsEl.appendChild(allLi);

        _shapeCategories.forEach(function(cat, i) {
            var li = document.createElement('li');
            li.className = 'nav-item';
            li.innerHTML = '<button class="nav-link" data-cat-idx="'+i+'" onclick="window._shapeSelectCat('+i+')">'+escHtml(cat.name)+'</button>';
            tabsEl.appendChild(li);
        });
    }

    // ── View mode toggle ──
    window._shapeViewMode = function(mode) {
        _shapeViewModeVal = mode;
        document.getElementById('viewMiniBtn').classList.toggle('active', mode === 'mini');
        document.getElementById('viewTableBtn').classList.toggle('active', mode === 'table');
        document.getElementById('shapeGrid').style.display = (mode === 'mini') ? '' : 'none';
        document.getElementById('shapeTableView').style.display = (mode === 'table') ? '' : 'none';
        // Re-render current view
        var activeTab = document.querySelector('#shapeCategoryTabs .nav-link.active');
        var idx = activeTab ? activeTab.dataset.catIdx : 'all';
        window._shapeSelectCat(idx);
    };

    // ── Search ──
    window._shapeSearch = function(term) {
        _shapeSearchTerm = (term || '').toLowerCase().trim();
        var activeTab = document.querySelector('#shapeCategoryTabs .nav-link.active');
        var idx = activeTab ? activeTab.dataset.catIdx : 'all';
        window._shapeSelectCat(idx);
    };

    // ── Collect defs for current filter ──
    function _getFilteredDefs(idx) {
        var grouped = []; // [{catName, defs:[]}]
        if (idx === 'all') {
            _shapeCategories.forEach(function(cat) {
                var catDefs = (cat.active_definitions || []).map(function(d) { d._catName = cat.name; return d; });
                if (catDefs.length > 0) grouped.push({ catName: cat.name, defs: catDefs });
            });
        } else {
            var cat = _shapeCategories[idx];
            var catDefs = (cat.active_definitions || []).map(function(d) { d._catName = cat.name; return d; });
            if (catDefs.length > 0) grouped.push({ catName: cat.name, defs: catDefs });
        }

        // Apply search filter
        if (_shapeSearchTerm) {
            grouped = grouped.map(function(g) {
                return {
                    catName: g.catName,
                    defs: g.defs.filter(function(d) {
                        return (d.code || '').toLowerCase().indexOf(_shapeSearchTerm) >= 0 ||
                               (d.name || '').toLowerCase().indexOf(_shapeSearchTerm) >= 0 ||
                               (d.description || '').toLowerCase().indexOf(_shapeSearchTerm) >= 0;
                    })
                };
            }).filter(function(g) { return g.defs.length > 0; });
        }
        return grouped;
    }

    window._shapeSelectCat = function(idx) {
        // Update active tab
        document.querySelectorAll('#shapeCategoryTabs .nav-link').forEach(function(btn) {
            btn.classList.toggle('active', btn.dataset.catIdx === String(idx));
        });

        var grouped = _getFilteredDefs(idx);

        if (_shapeViewModeVal === 'mini') {
            renderMiniatureView(grouped);
        } else {
            renderTableView(grouped);
        }
    };

    function renderMiniatureView(grouped) {
        var grid = document.getElementById('shapeGrid');
        grid.innerHTML = '';

        if (grouped.length === 0) {
            grid.innerHTML = '<div class="col-12 text-center text-muted py-3"><small>No shapes found</small></div>';
            return;
        }

        var showGroups = grouped.length > 1;
        grouped.forEach(function(group) {
            // Group header (like Cantor's "Group: 1 Angle Other (4 sides)")
            if (showGroups) {
                var hdr = document.createElement('div');
                hdr.className = 'col-12 shape-group-header';
                hdr.textContent = 'Group: ' + group.catName;
                grid.appendChild(hdr);
            }

            group.defs.forEach(function(def) {
                var col = document.createElement('div');
                col.className = 'col-4 col-sm-3 col-md-2';
                var svgContent = getShapeSvg(def, false, true); // with labels
                col.innerHTML =
                    '<div class="shape-pick-card" data-shape-id="'+def.id+'" data-shape-code="'+(def.code||'')+'" onclick="window._pickShape('+def.id+')">' +
                    '  <div class="shape-icon">'+svgContent+'</div>' +
                    '  <div class="shape-label">'+escHtml(def.name || def.code)+'</div>' +
                    '  <div style="font-size:9px;color:#94a3b8;">'+escHtml(def.code)+'</div>' +
                    '</div>';
                grid.appendChild(col);
            });
        });

        // Re-highlight current selection
        if (_pendingPickShape) {
            var sel = grid.querySelector('[data-shape-id="'+_pendingPickShape.id+'"]');
            if (sel) sel.classList.add('selected');
        }
    }

    function renderTableView(grouped) {
        var tbody = document.getElementById('shapeTableBody');
        tbody.innerHTML = '';

        if (grouped.length === 0) {
            tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted py-3">{{ __('No shapes found') }}</td></tr>';
            return;
        }

        var showGroups = grouped.length > 1;
        grouped.forEach(function(group) {
            if (showGroups) {
                var hdrRow = document.createElement('tr');
                hdrRow.className = 'group-header-row';
                hdrRow.innerHTML = '<td colspan="3">{{ __("Group") }}: '+escHtml(group.catName)+'</td>';
                tbody.appendChild(hdrRow);
            }
            group.defs.forEach(function(def) {
                var tr = document.createElement('tr');
                tr.dataset.shapeId = def.id;
                if (_pendingPickShape && _pendingPickShape.id === def.id) tr.className = 'selected';
                tr.innerHTML =
                    '<td><strong style="color:#5b21b6; font-family:monospace; font-size:11px;">'+escHtml(def.code)+'</strong></td>' +
                    '<td>'+escHtml(def.name || def.description || '--')+'</td>' +
                    '<td class="text-muted" style="font-size:11px;">'+escHtml(def._catName)+'</td>';
                tr.onclick = function() { window._pickShape(def.id); };
                tbody.appendChild(tr);
            });
        });
    }

    window._pickShape = function(defId) {
        // Find the definition
        var def = null;
        _shapeCategories.forEach(function(cat) {
            (cat.active_definitions || []).forEach(function(d) {
                if (d.id === defId) { def = d; def._catName = cat.name; }
            });
        });
        if (!def) return;

        _pendingPickShape = def;

        // Highlight in miniature grid
        document.querySelectorAll('.shape-pick-card').forEach(function(c) {
            c.classList.toggle('selected', parseInt(c.dataset.shapeId) === defId);
        });
        // Highlight in table view
        document.querySelectorAll('#shapeTableBody tr').forEach(function(tr) {
            tr.classList.toggle('selected', parseInt(tr.dataset.shapeId) === defId);
        });

        // Show footer
        document.getElementById('shapePickerFooter').style.display = '';
        document.getElementById('pickerSelectedName').textContent = (def.name || def.code) + ' (' + def.code + ')';

        // Update detail panel (like Cantor's left panel)
        var panel = document.getElementById('shapeDetailPanel');
        panel.style.display = '';
        document.getElementById('shapeDetailPreview').innerHTML = getShapeSvg(def, true, true);
        document.getElementById('shapeDetailCode').textContent = 'Macro: ' + (def.code || '--');
        document.getElementById('shapeDetailName').textContent = 'Designation: ' + (def.name || def.description || '--');
        document.getElementById('shapeDetailCat').textContent = def._catName || '--';
        document.getElementById('shapeDetailVerts').textContent = def.vertex_count || '--';
        document.getElementById('shapeDetailArc').textContent = def.has_arc ? 'Yes' : 'No';
        document.getElementById('shapeDetailParam').textContent = def.is_parametric ? 'Yes' : 'No';
    };

    window.confirmShapeSelection = function() {
        if (!_pendingPickShape) return;
        var def = _pendingPickShape;

        // Set hidden fields
        document.getElementById('shape_definition_id').value = def.id;
        document.getElementById('shape_code').value = def.code || '';
        document.getElementById('shapeNameDisplay').value = (def.name || def.code) + ' (' + def.code + ')';

        // Show parameter fields if shape is parametric
        var paramsContainer = document.getElementById('shapeParamsContainer');
        var paramFields = document.getElementById('shapeParamFields');
        var reqParams = def.required_params || [];
        if (typeof reqParams === 'string') {
            try { reqParams = JSON.parse(reqParams); } catch(e) { reqParams = []; }
        }

        // Normalize: required_params can be an object {"W1":"label"} or array ["W1",...]
        var paramList = [];
        if (reqParams && typeof reqParams === 'object' && !Array.isArray(reqParams)) {
            Object.keys(reqParams).forEach(function(key) {
                paramList.push({name: key, label: reqParams[key] || key});
            });
        } else if (Array.isArray(reqParams)) {
            reqParams.forEach(function(p) {
                if (typeof p === 'object') {
                    paramList.push({name: p.name || p, label: p.label || p.name || p});
                } else {
                    paramList.push({name: p, label: p});
                }
            });
        }

        // Filter out H1/W1 from dynamic params — we have dedicated inputs for those
        paramList = paramList.filter(function(p) {
            return p.name !== 'H1' && p.name !== 'W1';
        });

        if (paramList.length > 0) {
            paramFields.innerHTML = '';
            paramList.forEach(function(p) {
                var div = document.createElement('div');
                div.className = 'mb-1';
                div.innerHTML =
                    '<label class="small" style="font-size:10px;">'+escHtml(p.label)+'</label>' +
                    '<input type="text" class="form-control form-control-sm shape-param-input" '+
                    'data-param="'+escHtml(p.name)+'" placeholder="{{ __("Enter") }} '+escHtml(p.label)+'" style="font-size:11px;">';
                paramFields.appendChild(div);
            });
            paramsContainer.style.display = '';

            // Listen for param changes to update hidden field
            paramFields.querySelectorAll('.shape-param-input').forEach(function(inp) {
                inp.addEventListener('input', updateShapeParams);
            });
        } else {
            paramsContainer.style.display = 'none';
        }

        // Show/hide dedicated H1/W1 dimension inputs based on shape type
        updateShapeDimVisibility(def.code || '');
        // Clear previous dim values
        var dimH1 = document.getElementById('shapeDimH1');
        var dimW1 = document.getElementById('shapeDimW1');
        if (dimH1) dimH1.value = '';
        if (dimW1) dimW1.value = '';

        // Show shape preview thumbnail
        showShapePreview(def);

        // Update the shape_params hidden field
        updateShapeParams();

        // Close modal
        var modalEl = document.getElementById('shapePickerModal');
        var modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();

        // Apply shape contour to the configuration preview after a brief delay
        // so the modal closes and any pending preview refresh completes
        setTimeout(function(){ applyShapeToPreview(); }, 300);
    };

    function updateShapeParams() {
        var params = {};
        document.querySelectorAll('#shapeParamFields .shape-param-input').forEach(function(inp) {
            var val = inp.value.trim();
            if (val) params[inp.dataset.param] = isNaN(val) ? val : parseFloat(val);
        });

        // Merge dedicated H1/W1 dimension inputs
        var dimH1 = document.getElementById('shapeDimH1');
        var dimW1 = document.getElementById('shapeDimW1');
        if (dimH1 && dimH1.offsetParent !== null) {
            var v = parseFloat(dimH1.value);
            if (!isNaN(v) && v > 0) params.H1 = v;
        }
        if (dimW1 && dimW1.offsetParent !== null) {
            var v = parseFloat(dimW1.value);
            if (!isNaN(v) && v > 0) params.W1 = v;
        }

        document.getElementById('shape_params').value = Object.keys(params).length ? JSON.stringify(params) : '';

        // Re-apply the shape to the preview whenever params change
        applyShapeToPreview();
    }

    function showShapePreview(def) {
        var thumb = document.getElementById('shapePreviewThumb');
        var svg = document.getElementById('shapePreviewSvg');
        if (!thumb || !svg) return;
        svg.innerHTML = getShapeSvg(def, true, true);
        thumb.style.display = '';
    }

    /**
     * Apply shape contour overlay to the main window-svg-preview.
     * This clips the existing rectangular PW preview to the selected shape,
     * adds dimension labels and angle annotations like Cantor.
     */
    window.applyShapeToPreview = function() {
        var box = document.getElementById('window-svg-preview');
        if (!box) return;
        var svg = box.querySelector('svg');
        if (!svg) return;

        var shapeId = document.getElementById('shape_definition_id')?.value;
        if (!shapeId) {
            // Remove any existing shape overlay
            var existing = svg.querySelector('.shape-overlay-group');
            if (existing) existing.remove();
            var existingClip = svg.querySelector('#shapeClipPath');
            if (existingClip) existingClip.remove();
            // Remove clip from content
            svg.querySelectorAll('[clip-path="url(#shapeClipPath)"]').forEach(function(el) {
                el.removeAttribute('clip-path');
            });
            return;
        }

        // Find the def from loaded categories
        var def = null;
        _shapeCategories.forEach(function(cat) {
            (cat.active_definitions || []).forEach(function(d) {
                if (d.id == shapeId) { def = d; def._catName = cat.name; }
            });
        });
        if (!def) return;

        var svgW = parseFloat(svg.getAttribute('width') || svg.viewBox?.baseVal?.width || 240);
        var svgH = parseFloat(svg.getAttribute('height') || svg.viewBox?.baseVal?.height || 240);
        var vb = svg.getAttribute('viewBox');
        if (vb) {
            var parts = vb.split(/\s+/);
            svgW = parseFloat(parts[2]);
            svgH = parseFloat(parts[3]);
        }

        // Get shape params
        var paramsStr = document.getElementById('shape_params')?.value || '{}';
        var params = {};
        try { params = JSON.parse(paramsStr); } catch(e) {}

        // Build clip path based on shape
        var clipPath = buildShapeClipPath(def, svgW, svgH, params);
        if (!clipPath) return;

        // Remove old overlays
        var oldGroup = svg.querySelector('.shape-overlay-group');
        if (oldGroup) oldGroup.remove();
        var oldClip = svg.querySelector('#shapeClipPath');
        if (oldClip) oldClip.remove();

        // Add clip-path to defs
        var defs = svg.querySelector('defs');
        if (!defs) { defs = document.createElementNS('http://www.w3.org/2000/svg', 'defs'); svg.prepend(defs); }

        var clipEl = document.createElementNS('http://www.w3.org/2000/svg', 'clipPath');
        clipEl.id = 'shapeClipPath';
        var clipPathEl = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        clipPathEl.setAttribute('d', clipPath);
        clipEl.appendChild(clipPathEl);
        defs.appendChild(clipEl);

        // Wrap all existing content in a group with clip-path
        var children = Array.from(svg.childNodes).filter(function(n) { return n !== defs; });
        var wrapper = document.createElementNS('http://www.w3.org/2000/svg', 'g');
        wrapper.setAttribute('clip-path', 'url(#shapeClipPath)');
        children.forEach(function(c) { wrapper.appendChild(c); });
        svg.appendChild(wrapper);

        // Add shape outline overlay on top
        var overlay = document.createElementNS('http://www.w3.org/2000/svg', 'g');
        overlay.classList.add('shape-overlay-group');

        // Shape outline stroke
        var outline = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        outline.setAttribute('d', clipPath);
        outline.setAttribute('fill', 'none');
        outline.setAttribute('stroke', '#1B4F72');
        outline.setAttribute('stroke-width', '3');
        outline.setAttribute('stroke-linejoin', 'round');
        overlay.appendChild(outline);

        // Add angle annotations for parametric shapes
        var annotations = buildAngleAnnotations(def, svgW, svgH, params);
        annotations.forEach(function(ann) {
            var text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
            text.setAttribute('x', ann.x);
            text.setAttribute('y', ann.y);
            text.setAttribute('text-anchor', 'middle');
            text.setAttribute('font-size', '9');
            text.setAttribute('font-weight', 'bold');
            text.setAttribute('fill', '#b30202');
            text.setAttribute('transform', ann.rotate ? 'rotate('+ann.rotate+' '+ann.x+' '+ann.y+')' : '');
            text.textContent = ann.text;
            overlay.appendChild(text);
        });

        svg.appendChild(overlay);
    };

    function buildShapeClipPath(def, svgW, svgH, params) {
        var code = (def.code || '').toUpperCase();
        var catCode = (def._catName || '').toUpperCase().replace(/[^A-Z]/g, '');
        // Use the full SVG area with small padding
        var pad = 2;
        var x1 = pad, y1 = pad, x2 = svgW - pad, y2 = svgH - pad;
        var cx = svgW / 2;

        // H1 param as ratio of total height
        var h1Ratio = params.H1 ? (parseFloat(params.H1) / (parseFloat(document.getElementById('height_decimal')?.value) || 40)) : 0.5;
        var w1Ratio = params.W1 ? (parseFloat(params.W1) / (parseFloat(document.getElementById('width_decimal')?.value) || 40)) : 0.25;

        if (code.indexOf('RAKE_UP_LEFT') >= 0 || code === 'S15') {
            var h1y = y2 - (y2 - y1) * h1Ratio;
            return 'M'+x1+','+y2+' L'+x1+','+y1+' L'+x2+','+h1y+' L'+x2+','+y2+' Z';
        }
        if (code.indexOf('RAKE_UP_RIGHT') >= 0 || code === 'S17') {
            var h1y = y2 - (y2 - y1) * h1Ratio;
            return 'M'+x1+','+h1y+' L'+x2+','+y1+' L'+x2+','+y2+' L'+x1+','+y2+' Z';
        }
        if (code.indexOf('RAKE_DOWN_RIGHT') >= 0 || code === 'S23') {
            var h1y = y1 + (y2 - y1) * h1Ratio;
            return 'M'+x1+','+y1+' L'+x2+','+y1+' L'+x2+','+y2+' L'+x1+','+h1y+' Z';
        }
        if (code.indexOf('RAKE_DOWN_LEFT') >= 0 || code === 'S25') {
            var h1y = y1 + (y2 - y1) * h1Ratio;
            return 'M'+x1+','+y1+' L'+x2+','+y1+' L'+x2+','+h1y+' L'+x1+','+y2+' Z';
        }
        if (code.indexOf('TRI') >= 0 || catCode.indexOf('TRIANGLE') >= 0) {
            return 'M'+x1+','+y2+' L'+cx+','+y1+' L'+x2+','+y2+' Z';
        }
        if (code.indexOf('HALF_ROUND') >= 0 || code === 'M1' || code === 'S03' || code === 'S49' ||
            catCode.indexOf('HALFROUND') >= 0 || catCode.indexOf('HALF') >= 0) {
            var r = (x2 - x1) / 2;
            var archH = Math.min(r, (y2 - y1) * 0.45);
            return 'M'+x1+','+y2+' L'+x1+','+(y1 + archH)+' A'+r+','+archH+' 0 0,1 '+x2+','+(y1 + archH)+' L'+x2+','+y2+' Z';
        }
        if (code.indexOf('CLIP_LT') >= 0 || code === 'S04') {
            var cw = (x2 - x1) * w1Ratio, ch = (y2 - y1) * h1Ratio;
            return 'M'+(x1 + cw)+','+y1+' L'+x2+','+y1+' L'+x2+','+y2+' L'+x1+','+y2+' L'+x1+','+(y1 + ch)+' Z';
        }
        if (code.indexOf('CLIP_RT') >= 0 || code === 'S06') {
            var cw = (x2 - x1) * w1Ratio, ch = (y2 - y1) * h1Ratio;
            return 'M'+x1+','+y1+' L'+(x2 - cw)+','+y1+' L'+x2+','+(y1 + ch)+' L'+x2+','+y2+' L'+x1+','+y2+' Z';
        }
        if (code.indexOf('CLIP_RB') >= 0 || code === 'S09') {
            var cw = (x2 - x1) * w1Ratio, ch = (y2 - y1) * h1Ratio;
            return 'M'+x1+','+y1+' L'+x2+','+y1+' L'+x2+','+(y2 - ch)+' L'+(x2 - cw)+','+y2+' L'+x1+','+y2+' Z';
        }
        if (code.indexOf('CLIP_LB') >= 0 || code === 'S12') {
            var cw = (x2 - x1) * w1Ratio, ch = (y2 - y1) * h1Ratio;
            return 'M'+x1+','+y1+' L'+x2+','+y1+' L'+x2+','+y2+' L'+(x1 + cw)+','+y2+' L'+x1+','+(y2 - ch)+' Z';
        }
        if (code.indexOf('OCT') >= 0 || catCode.indexOf('OCTAGON') >= 0) {
            var inset = (x2 - x1) * 0.29;
            return 'M'+(x1+inset)+','+y1+' L'+(x2-inset)+','+y1+
                   ' L'+x2+','+(y1+inset)+' L'+x2+','+(y2-inset)+
                   ' L'+(x2-inset)+','+y2+' L'+(x1+inset)+','+y2+
                   ' L'+x1+','+(y2-inset)+' L'+x1+','+(y1+inset)+' Z';
        }
        if (code.indexOf('QUARTER') >= 0 || code === 'S61' || code === 'S62') {
            var r = Math.min(x2 - x1, y2 - y1);
            return 'M'+x1+','+y2+' L'+x1+','+(y2 - r)+' A'+r+','+r+' 0 0,1 '+(x1 + r)+','+y2+' Z';
        }
        if (code.indexOf('CIRCLE') >= 0 || code === 'S48') {
            var rx = (x2 - x1) / 2, ry = (y2 - y1) / 2;
            return 'M'+cx+','+y1+' A'+rx+','+ry+' 0 1,1 '+cx+','+y2+' A'+rx+','+ry+' 0 1,1 '+cx+','+y1;
        }
        if (code.indexOf('ARCH') >= 0 || code === 'M2' || code === 'M5' || catCode.indexOf('ARCH') >= 0) {
            var r = (x2 - x1) / 2;
            return 'M'+x1+','+y2+' L'+x1+','+(y1 + r)+' A'+r+','+r+' 0 0,1 '+x2+','+(y1 + r)+' L'+x2+','+y2+' Z';
        }
        if (code.indexOf('TRAP') >= 0 || catCode.indexOf('TRAPEZ') >= 0) {
            var inset = (x2 - x1) * w1Ratio;
            return 'M'+x1+','+y2+' L'+(x1 + inset)+','+y1+' L'+(x2 - inset)+','+y1+' L'+x2+','+y2+' Z';
        }
        if (code.indexOf('PEAK') >= 0 || catCode.indexOf('PEAKED') >= 0) {
            var peakH = (y2 - y1) * h1Ratio;
            return 'M'+x1+','+y2+' L'+x1+','+(y1 + peakH)+' L'+cx+','+y1+' L'+x2+','+(y1 + peakH)+' L'+x2+','+y2+' Z';
        }

        // Default: no clipping (rectangle)
        return null;
    }

    function buildAngleAnnotations(def, svgW, svgH, params) {
        var code = (def.code || '').toUpperCase();
        var annotations = [];
        var W = parseFloat(document.getElementById('width_decimal')?.value) || 40;
        var H = parseFloat(document.getElementById('height_decimal')?.value) || 40;
        var H1 = params.H1 ? parseFloat(params.H1) : null;
        var W1 = params.W1 ? parseFloat(params.W1) : null;

        if (!H1 && !W1) return annotations;

        // Calculate angles for rakes
        if (code.indexOf('RAKE') >= 0 && H1 !== null) {
            var rise = H - H1;
            var run = W;
            var slopeAngle = Math.atan2(rise, run) * 180 / Math.PI;
            var topAngle = (90 + slopeAngle).toFixed(1);
            var bottomAngle = (180 - slopeAngle).toFixed(1);

            // Place annotations near the angled corners
            annotations.push({ x: svgW * 0.15, y: svgH * 0.15, text: topAngle + '°', rotate: 0 });
            annotations.push({ x: svgW * 0.85, y: svgH * 0.75, text: bottomAngle + '°', rotate: 0 });
        }

        // Calculate angles for clipped corners
        if (code.indexOf('CLIP') >= 0 && (H1 !== null || W1 !== null)) {
            var ch = H1 || (H * 0.25);
            var cw = W1 || (W * 0.25);
            var clipAngle = Math.atan2(ch, cw) * 180 / Math.PI;
            var cornerAngle = (90 + clipAngle).toFixed(1);
            var adjacentAngle = (180 - clipAngle).toFixed(1);

            annotations.push({ x: svgW * 0.2, y: svgH * 0.2, text: cornerAngle + '°', rotate: 0 });
            annotations.push({ x: svgW * 0.2, y: svgH * 0.8, text: adjacentAngle + '°', rotate: 0 });
        }

        return annotations;
    }

    // ── SVG generators for each shape category ──
    function getShapeSvg(def, large, withLabels) {
        var w = large ? 140 : 60;
        var h = large ? 110 : 55;
        var catCode = (def._catName || '').toUpperCase().replace(/[^A-Z]/g, '');
        var code = (def.code || '').toUpperCase();
        var dispW = large ? 130 : 50;
        var dispH = large ? 100 : 48;

        // Use svg_template if available
        if (def.svg_template) {
            return '<svg viewBox="0 0 '+w+' '+h+'" width="'+dispW+'" height="'+dispH+'">'+
                   '<path d="'+def.svg_template+'" fill="#E8F4FD" stroke="#1B4F72" stroke-width="1.5"/></svg>';
        }

        var pad = large ? 18 : 6;
        var x1 = pad, y1 = pad, x2 = w-pad, y2 = h-pad;
        var cx = w/2, cy = h/2;
        var path = '';
        var labels = []; // [{x,y,text}]
        var shapeType = 'rect';

        if (code.indexOf('HALFRND') >= 0 || code.indexOf('HALFROUND') >= 0 || code.indexOf('121') >= 0 || code.indexOf('122') >= 0 ||
            catCode.indexOf('HALFROUND') >= 0 || catCode.indexOf('HALF') >= 0) {
            shapeType = 'halfround';
            var r = (x2-x1)/2;
            path = 'M'+x1+','+y2+' L'+x1+','+(y2-r*0.2)+' A'+r+','+r+' 0 0,1 '+x2+','+(y2-r*0.2)+' L'+x2+','+y2+' Z';
            labels = [{x:cx, y:y2+pad*0.7, text:'W'}, {x:x2+pad*0.6, y:cy, text:'H'}];
        } else if (code.indexOf('S15') >= 0 || (code.indexOf('RAKE') >= 0 && (code.indexOf('LEFT') >= 0 || code.indexOf('SL') >= 0))) {
            shapeType = 'rake-left';
            var slopeY = y1 + (y2-y1)*0.45;
            path = 'M'+x1+','+y2+' L'+x1+','+y1+' L'+x2+','+slopeY+' L'+x2+','+y2+' Z';
            labels = [{x:cx, y:y2+pad*0.7, text:'W'}, {x:x1-pad*0.6, y:cy, text:'H'}, {x:x2+pad*0.6, y:(slopeY+y2)/2, text:'H1'}];
        } else if (code.indexOf('S16') >= 0 || code.indexOf('S17') >= 0 || (code.indexOf('RAKE') >= 0 && code.indexOf('RIGHT') >= 0)) {
            shapeType = 'rake-right';
            var slopeY = y1 + (y2-y1)*0.45;
            path = 'M'+x1+','+slopeY+' L'+x2+','+y1+' L'+x2+','+y2+' L'+x1+','+y2+' Z';
            labels = [{x:cx, y:y2+pad*0.7, text:'W'}, {x:x2+pad*0.6, y:cy, text:'H'}, {x:x1-pad*0.6, y:(slopeY+y2)/2, text:'H1'}];
        } else if (code.indexOf('S23') >= 0 || code.indexOf('S25') >= 0 || (code.indexOf('RAKE') >= 0 && code.indexOf('BOTTOM') >= 0)) {
            shapeType = 'rake-bottom';
            var slopeY = y2 - (y2-y1)*0.35;
            path = 'M'+x1+','+y1+' L'+x2+','+y1+' L'+x2+','+y2+' L'+x1+','+slopeY+' Z';
            labels = [{x:cx, y:y2+pad*0.7, text:'W'}, {x:x2+pad*0.6, y:cy, text:'H'}, {x:x1-pad*0.6, y:(y1+slopeY)/2, text:'H1'}];
        } else if (code.indexOf('RAKE') >= 0 || catCode.indexOf('RAKE') >= 0) {
            shapeType = 'rake';
            var slopeY = y1 + (y2-y1)*0.45;
            path = 'M'+x1+','+y2+' L'+x1+','+y1+' L'+x2+','+slopeY+' L'+x2+','+y2+' Z';
            labels = [{x:cx, y:y2+pad*0.7, text:'W'}, {x:x1-pad*0.6, y:cy, text:'H'}, {x:x2+pad*0.6, y:(slopeY+y2)/2, text:'H1'}];
        } else if (code.indexOf('S04') >= 0 || code.indexOf('S06') >= 0 || code.indexOf('S09') >= 0 || code.indexOf('S12') >= 0 ||
                   code.indexOf('CLIPPED') >= 0 || code.indexOf('CLIP') >= 0) {
            shapeType = 'clipped';
            var clip = (x2-x1)*0.28;
            // Determine which corner is clipped from the code
            if (code.indexOf('LEFT TOP') >= 0 || code.indexOf('S04') >= 0) {
                path = 'M'+(x1+clip)+','+y1+' L'+x2+','+y1+' L'+x2+','+y2+' L'+x1+','+y2+' L'+x1+','+(y1+clip)+' Z';
            } else if (code.indexOf('RIGHT TOP') >= 0 || code.indexOf('S06') >= 0) {
                path = 'M'+x1+','+y1+' L'+(x2-clip)+','+y1+' L'+x2+','+(y1+clip)+' L'+x2+','+y2+' L'+x1+','+y2+' Z';
            } else if (code.indexOf('RIGHT BOTTOM') >= 0 || code.indexOf('S09') >= 0) {
                path = 'M'+x1+','+y1+' L'+x2+','+y1+' L'+x2+','+(y2-clip)+' L'+(x2-clip)+','+y2+' L'+x1+','+y2+' Z';
            } else {
                path = 'M'+x1+','+y1+' L'+x2+','+y1+' L'+x2+','+y2+' L'+(x1+clip)+','+y2+' L'+x1+','+(y2-clip)+' Z';
            }
            labels = [{x:cx, y:y2+pad*0.7, text:'W'}, {x:x2+pad*0.6, y:cy, text:'H'}, {x:x1+clip/2-2, y:y1+clip/2-2, text:'W1'}];
        } else if (code.indexOf('TRI') >= 0 || catCode.indexOf('TRIANGLE') >= 0) {
            shapeType = 'triangle';
            path = 'M'+x1+','+y2+' L'+cx+','+y1+' L'+x2+','+y2+' Z';
            labels = [{x:cx, y:y2+pad*0.7, text:'W'}, {x:cx+pad*0.3, y:cy-2, text:'H'}];
        } else if (code.indexOf('OCT') >= 0 || catCode.indexOf('OCTAGON') >= 0) {
            shapeType = 'octagon';
            var inset = (x2-x1)*0.29;
            path = 'M'+(x1+inset)+','+y1+' L'+(x2-inset)+','+y1+
                   ' L'+x2+','+(y1+inset)+' L'+x2+','+(y2-inset)+
                   ' L'+(x2-inset)+','+y2+' L'+(x1+inset)+','+y2+
                   ' L'+x1+','+(y2-inset)+' L'+x1+','+(y1+inset)+' Z';
            labels = [{x:cx, y:y2+pad*0.7, text:'W'}, {x:x2+pad*0.6, y:cy, text:'H'}];
        } else if (code.indexOf('HEX') >= 0 || catCode.indexOf('HEXAGON') >= 0) {
            var inset = (x2-x1)*0.25;
            path = 'M'+cx+','+y1+' L'+x2+','+(y1+(y2-y1)*0.25)+
                   ' L'+x2+','+(y2-(y2-y1)*0.25)+' L'+cx+','+y2+
                   ' L'+x1+','+(y2-(y2-y1)*0.25)+' L'+x1+','+(y1+(y2-y1)*0.25)+' Z';
            labels = [{x:cx, y:y2+pad*0.7, text:'W'}, {x:x2+pad*0.6, y:cy, text:'H'}];
        } else if (code.indexOf('ARCH') >= 0 || code.indexOf('GOTHIC') >= 0 || catCode.indexOf('ARCH') >= 0) {
            var r = (x2-x1)/2;
            path = 'M'+x1+','+y2+' L'+x1+','+(y1+r)+' A'+r+','+r+' 0 0,1 '+x2+','+(y1+r)+' L'+x2+','+y2+' Z';
            labels = [{x:cx, y:y2+pad*0.7, text:'W'}, {x:x2+pad*0.6, y:cy, text:'H'}];
        } else if (code.indexOf('QUARTER') >= 0 || code.indexOf('S61') >= 0 || code.indexOf('S62') >= 0 ||
                   catCode.indexOf('QUARTER') >= 0) {
            var r = Math.min(x2-x1, y2-y1);
            path = 'M'+x1+','+y2+' L'+x1+','+(y2-r)+' A'+r+','+r+' 0 0,1 '+(x1+r)+','+y2+' Z';
            labels = [{x:(x1+x1+r)/2, y:y2+pad*0.7, text:'W'}, {x:x1-pad*0.6, y:(y2-r+y2)/2, text:'H'}];
        } else if (code.indexOf('TRAP') >= 0 || catCode.indexOf('TRAPEZ') >= 0) {
            var inset = (x2-x1)*0.2;
            path = 'M'+x1+','+y2+' L'+(x1+inset)+','+y1+' L'+(x2-inset)+','+y1+' L'+x2+','+y2+' Z';
            labels = [{x:cx, y:y2+pad*0.7, text:'W'}, {x:x2+pad*0.6, y:cy, text:'H'}, {x:cx, y:y1-pad*0.3, text:'W1'}];
        } else if (code.indexOf('PENT') >= 0 || catCode.indexOf('PENTAGON') >= 0) {
            path = 'M'+cx+','+y1+' L'+x2+','+(y1+(y2-y1)*0.38)+
                   ' L'+(x2-(x2-x1)*0.19)+','+y2+' L'+(x1+(x2-x1)*0.19)+','+y2+
                   ' L'+x1+','+(y1+(y2-y1)*0.38)+' Z';
            labels = [{x:cx, y:y2+pad*0.7, text:'W'}, {x:x2+pad*0.6, y:cy, text:'H'}];
        } else {
            // Default rectangle (standard PW)
            path = 'M'+x1+','+y1+' L'+x2+','+y1+' L'+x2+','+y2+' L'+x1+','+y2+' Z';
            labels = [{x:cx, y:y2+pad*0.7, text:'W'}, {x:x2+pad*0.6, y:cy, text:'H'}];
        }

        var labelSvg = '';
        if (withLabels && labels.length > 0) {
            var fs = large ? 10 : 7;
            labels.forEach(function(l) {
                labelSvg += '<text x="'+l.x+'" y="'+l.y+'" text-anchor="middle" font-size="'+fs+'" font-weight="bold" fill="#334155">'+l.text+'</text>';
            });
        }

        return '<svg viewBox="0 0 '+w+' '+h+'" width="'+dispW+'" height="'+dispH+'">'+
               '<path d="'+path+'" fill="#E8F4FD" stroke="#1B4F72" stroke-width="'+(large?2:1.5)+'" stroke-linejoin="round"/>'+
               labelSvg+'</svg>';
    }

    function escHtml(s) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(s || ''));
        return div.innerHTML;
    }
})();
</script>

{{-- ══════════════ PANEL DIMENSIONS MODAL ══════════════ --}}
<div class="modal fade" id="panelDimsModal" tabindex="-1" aria-labelledby="panelDimsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2" style="background:#f0f4f8; border-bottom:1px solid #e2e8f0;">
                <h6 class="modal-title mb-0" id="panelDimsModalLabel">
                    <i class="fas fa-expand-arrows-alt me-2" style="color:#1B4F72;"></i>{{ __('Custom Panel Dimensions') }}
                </h6>
                <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <div id="dimModalPreviewWrap" style="position:relative; min-height:300px; display:flex; align-items:center; justify-content:center; background:#fafbfd; border:1px solid #e2e8f0; border-radius:8px; overflow:visible;">
                    {{-- SVG preview rendered here at larger size --}}
                    <div id="dimModalSvg" style="width:100%; height:100%; display:flex; align-items:center; justify-content:center;"></div>
                    {{-- Overlaid dimension inputs positioned absolutely --}}
                    <div id="dimModalOverlayInputs" style="position:absolute; top:0; left:0; width:100%; height:100%; pointer-events:none;"></div>
                </div>
                <div class="mt-2 text-muted" style="font-size:11px;">
                    <i class="fas fa-info-circle me-1"></i>{{ __('Adjust any dimension — remaining panels auto-adjust to fill the total width/height.') }}
                </div>
            </div>
            <div class="modal-footer py-2" style="border-top:1px solid #e2e8f0;">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-sm btn-primary" onclick="applyDimModalValues()" style="background:#1B4F72; border-color:#1B4F72;">
                    <i class="fas fa-check me-1"></i>{{ __('Apply') }}
                </button>
            </div>
        </div>
    </div>
</div>

<style>
#panelDimsModal .dim-overlay-input {
    position:absolute; pointer-events:auto; background:rgba(255,255,255,0.92);
    border:1px solid #1B4F72; border-radius:4px; padding:2px 5px; font-size:11px;
    font-weight:600; color:#1e293b; width:70px; text-align:center; box-shadow:0 1px 4px rgba(0,0,0,.12);
}
#panelDimsModal .dim-overlay-input:focus {
    outline:none; border-color:#f59e0b; box-shadow:0 0 0 2px rgba(245,158,11,.3);
}
#panelDimsModal .dim-overlay-label {
    position:absolute; pointer-events:none; font-size:9px; font-weight:700;
    color:#1B4F72; text-transform:uppercase; letter-spacing:.4px;
}
</style>

<script>
(function(){
    var _dimModalData = null; // { panels, rows, etc }
    var _dimModalValues = {}; // { position: {width, height} }

    window.openDimensionModal = function() {
        if (!_panelLayoutData) return;
        _dimModalData = JSON.parse(JSON.stringify(_panelLayoutData));

        var totalW = parseFraction(document.querySelector('[name="width"]')?.value) || 48;
        var totalH = parseFraction(document.querySelector('[name="height"]')?.value) || 48;

        // Load existing values from hidden field or use defaults
        var existing = null;
        var jsonVal = document.getElementById('panel_dimensions_json')?.value;
        if (jsonVal) { try { existing = JSON.parse(jsonVal); } catch(e) {} }

        var panels = _dimModalData.panels || [];
        var rows = _dimModalData.rows || ['main'];

        // Build initial values
        _dimModalValues = {};
        panels.forEach(function(p) {
            var saved = null;
            if (existing && Array.isArray(existing)) {
                saved = existing.find(function(e) { return e.position === p.position; });
            }
            _dimModalValues[p.position] = {
                width: saved ? saved.width : (p.default_width || totalW / panels.length),
                height: saved ? saved.height : (p.default_height || totalH / rows.length),
                label: p.label,
                row: p.row,
                type: p.type
            };
        });

        renderDimModal(totalW, totalH);

        var modal = new bootstrap.Modal(document.getElementById('panelDimsModal'));
        modal.show();
    };

    function renderDimModal(totalW, totalH) {
        var panels = _dimModalData.panels || [];
        var rows = _dimModalData.rows || ['main'];
        var hasMultiRows = rows.length > 1;

        // Fetch SVG at larger size for the modal
        var config = (document.getElementById('series_type')?.value || 'PW').toUpperCase();
        var previewUrl = '/installer/quotes/window-preview?type=' + encodeURIComponent(config) + '&width=' + totalW + '&height=' + totalH + '&maxSize=400';

        // Add panel widths for proportional rendering
        var mainP = [], topP = [], botP = [];
        panels.forEach(function(p) {
            var v = _dimModalValues[p.position];
            if (p.row === 'main') mainP.push(v ? v.width : p.default_width);
            else if (p.row === 'top') topP.push(v ? v.width : p.default_width);
            else if (p.row === 'bottom') botP.push(v ? v.width : p.default_width);
        });
        if (mainP.length) previewUrl += '&mainWidths=' + mainP.join(',');
        if (topP.length) previewUrl += '&topWidths=' + topP.join(',');
        if (botP.length) previewUrl += '&botWidths=' + botP.join(',');
        if (hasMultiRows) {
            var rh = {};
            rows.forEach(function(r) {
                var rp = panels.find(function(p) { return p.row === r; });
                if (rp) rh[r] = _dimModalValues[rp.position] ? _dimModalValues[rp.position].height : (rp.default_height || totalH / rows.length);
            });
            previewUrl += '&rowHeights=' + encodeURIComponent(JSON.stringify(rh));
        }

        var svgBox = document.getElementById('dimModalSvg');
        fetch(previewUrl)
            .then(function(r) { return r.text(); })
            .then(function(html) {
                svgBox.innerHTML = html;
                // Wait for SVG to render, then overlay inputs
                setTimeout(function() { positionOverlayInputs(totalW, totalH); }, 100);
            })
            .catch(function() { svgBox.innerHTML = '<span class="text-muted">Preview unavailable</span>'; });
    }

    function positionOverlayInputs(totalW, totalH) {
        var overlay = document.getElementById('dimModalOverlayInputs');
        if (!overlay) return;
        overlay.innerHTML = '';

        var svgEl = document.querySelector('#dimModalSvg svg');
        if (!svgEl) return;

        var panels = _dimModalData.panels || [];
        var rows = _dimModalData.rows || ['main'];
        var hasMultiRows = rows.length > 1;

        // Get SVG bounding box to position inputs
        var svgRect = svgEl.getBoundingClientRect();
        var wrapRect = document.getElementById('dimModalPreviewWrap').getBoundingClientRect();
        var offsetX = svgRect.left - wrapRect.left;
        var offsetY = svgRect.top - wrapRect.top;
        var svgW = svgRect.width;
        var svgH = svgRect.height;

        if (svgW < 50 || svgH < 50) return;

        // Parse viewBox to get coordinate mapping
        var vb = svgEl.getAttribute('viewBox');
        var vbParts = vb ? vb.split(/[\s,]+/).map(Number) : [0, 0, 700, 400];
        var vbW = vbParts[2] - vbParts[0];
        var vbH = vbParts[3] - vbParts[1];
        var scaleX = svgW / vbW;
        var scaleY = svgH / vbH;

        // Group panels by row
        var rowPanels = {};
        rows.forEach(function(r) { rowPanels[r] = panels.filter(function(p) { return p.row === r; }); });

        // Calculate row Y positions (approximate from SVG layout)
        var frameT = 18; // top frame thickness in viewBox units
        var usableH = vbH - frameT * 2;
        var rowCount = rows.length;
        var rowYMap = {};
        var rowHMap = {};
        rows.forEach(function(r, i) {
            rowHMap[r] = usableH / rowCount;
            rowYMap[r] = frameT + i * (usableH / rowCount);
        });

        // Place width inputs along the bottom edge of each panel
        rows.forEach(function(row) {
            var rPanels = rowPanels[row] || [];
            if (!rPanels.length) return;

            var totalRowW = 0;
            rPanels.forEach(function(p) {
                var v = _dimModalValues[p.position];
                totalRowW += v ? v.width : (p.default_width || totalW / rPanels.length);
            });

            var cx = frameT; // cumulative x in viewBox
            rPanels.forEach(function(p, pi) {
                var v = _dimModalValues[p.position];
                var pw = v ? v.width : (p.default_width || totalW / rPanels.length);
                var panelW_vb = (pw / totalRowW) * (vbW - frameT * 2);

                // Width input: centered at bottom of panel
                var inputX = offsetX + (cx + panelW_vb / 2) * scaleX - 35;
                var inputY = offsetY + (rowYMap[row] + rowHMap[row] - 5) * scaleY;

                var inp = document.createElement('input');
                inp.type = 'text';
                inp.className = 'dim-overlay-input';
                inp.value = toFraction(pw);
                inp.dataset.position = p.position;
                inp.dataset.field = 'width';
                inp.dataset.row = row;
                inp.title = p.label + ' {{ __("Width") }}';
                inp.style.left = Math.max(2, inputX) + 'px';
                inp.style.top = Math.min(inputY, svgH + offsetY - 22) + 'px';
                inp.addEventListener('change', function() { onOverlayInputChange(this, totalW, totalH); });
                overlay.appendChild(inp);

                // Label above input
                var lbl = document.createElement('div');
                lbl.className = 'dim-overlay-label';
                lbl.textContent = p.label + ' W';
                lbl.style.left = (Math.max(2, inputX) + 10) + 'px';
                lbl.style.top = (Math.min(inputY, svgH + offsetY - 22) - 14) + 'px';
                overlay.appendChild(lbl);

                cx += panelW_vb;
            });
        });

        // Place height inputs on the right side of each row
        if (hasMultiRows) {
            rows.forEach(function(row, ri) {
                var rPanels = rowPanels[row] || [];
                if (!rPanels.length) return;
                var p = rPanels[0]; // use first panel's height
                var v = _dimModalValues[p.position];
                var rh = v ? v.height : (p.default_height || totalH / rows.length);

                var inputX = offsetX + svgW + 4;
                var inputY = offsetY + (rowYMap[row] + rowHMap[row] / 2) * scaleY - 10;

                var inp = document.createElement('input');
                inp.type = 'text';
                inp.className = 'dim-overlay-input';
                inp.value = toFraction(rh);
                inp.dataset.row = row;
                inp.dataset.field = 'height';
                inp.title = (row === 'main' ? '{{ __("Main") }}' : row === 'top' ? '{{ __("Top") }}' : '{{ __("Bottom") }}') + ' {{ __("Height") }}';
                inp.style.left = Math.min(inputX, wrapRect.width - 75) + 'px';
                inp.style.top = inputY + 'px';
                inp.addEventListener('change', function() { onOverlayInputChange(this, totalW, totalH); });
                overlay.appendChild(inp);

                var lbl = document.createElement('div');
                lbl.className = 'dim-overlay-label';
                lbl.textContent = (row === 'main' ? 'H1' : row === 'top' ? 'HT' : 'HB');
                lbl.style.left = Math.min(inputX + 10, wrapRect.width - 65) + 'px';
                lbl.style.top = (inputY - 14) + 'px';
                overlay.appendChild(lbl);
            });
        } else {
            // Single row — show total height on right
            var inputX = offsetX + svgW + 4;
            var inputY = offsetY + svgH / 2 - 10;
            var inp = document.createElement('input');
            inp.type = 'text';
            inp.className = 'dim-overlay-input';
            inp.value = toFraction(totalH);
            inp.dataset.field = 'total_height';
            inp.title = '{{ __("Total Height") }}';
            inp.style.left = Math.min(inputX, wrapRect.width - 75) + 'px';
            inp.style.top = inputY + 'px';
            inp.disabled = true;
            inp.style.opacity = '0.5';
            overlay.appendChild(inp);
        }
    }

    function onOverlayInputChange(inputEl, totalW, totalH) {
        var field = inputEl.dataset.field;
        var row = inputEl.dataset.row;
        var position = inputEl.dataset.position;
        var newVal = parseFraction(inputEl.value) || 0;
        if (newVal <= 0) return;

        var panels = _dimModalData.panels || [];

        if (field === 'width' && position) {
            // Auto-adjust: distribute remaining width among other panels in same row
            var rowPanels = panels.filter(function(p) { return p.row === row; });
            var otherPanels = rowPanels.filter(function(p) { return p.position !== parseInt(position); });
            var oldVal = _dimModalValues[position] ? _dimModalValues[position].width : 0;

            // Total width for this row = sum of all panels
            var rowTotal = 0;
            rowPanels.forEach(function(p) {
                rowTotal += _dimModalValues[p.position] ? _dimModalValues[p.position].width : 0;
            });

            var remaining = rowTotal - newVal;
            if (remaining > 0 && otherPanels.length > 0) {
                var otherSum = 0;
                otherPanels.forEach(function(p) { otherSum += _dimModalValues[p.position] ? _dimModalValues[p.position].width : 0; });
                // Distribute proportionally
                otherPanels.forEach(function(p) {
                    var oldW = _dimModalValues[p.position] ? _dimModalValues[p.position].width : 0;
                    _dimModalValues[p.position].width = otherSum > 0 ? (oldW / otherSum) * remaining : remaining / otherPanels.length;
                });
            }
            _dimModalValues[position].width = newVal;

        } else if (field === 'height' && row) {
            // Set height for all panels in this row, auto-adjust other rows
            var rows = _dimModalData.rows || ['main'];
            var otherRows = rows.filter(function(r) { return r !== row; });

            // Set this row's height
            panels.forEach(function(p) {
                if (p.row === row) _dimModalValues[p.position].height = newVal;
            });

            // Auto-adjust other rows
            var remaining = totalH - newVal;
            if (remaining > 0 && otherRows.length > 0) {
                var otherTotal = 0;
                otherRows.forEach(function(r) {
                    var rp = panels.find(function(p) { return p.row === r; });
                    if (rp) otherTotal += _dimModalValues[rp.position] ? _dimModalValues[rp.position].height : 0;
                });
                otherRows.forEach(function(r) {
                    var rp = panels.find(function(p) { return p.row === r; });
                    if (rp) {
                        var oldH = _dimModalValues[rp.position] ? _dimModalValues[rp.position].height : 0;
                        var newH = otherTotal > 0 ? (oldH / otherTotal) * remaining : remaining / otherRows.length;
                        panels.forEach(function(p) {
                            if (p.row === r) _dimModalValues[p.position].height = newH;
                        });
                    }
                });
            }
        }

        // Re-render the modal with new proportions
        renderDimModal(totalW, totalH);
    }

    window.applyDimModalValues = function() {
        if (!_dimModalData) return;
        var panels = _dimModalData.panels || [];
        var result = [];

        panels.forEach(function(p) {
            var v = _dimModalValues[p.position];
            result.push({
                position: p.position,
                label: p.label,
                row: p.row,
                type: p.type,
                width: v ? Math.round(v.width * 10000) / 10000 : 0,
                height: v ? Math.round(v.height * 10000) / 10000 : 0
            });
        });

        document.getElementById('panel_dimensions_json').value = JSON.stringify(result);
        document.getElementById('customDimsCheck').checked = true;
        updateDimsSummary();
        updateWindowPreview();

        var modal = bootstrap.Modal.getInstance(document.getElementById('panelDimsModal'));
        if (modal) modal.hide();
    };
})();
</script>

    </div>{{-- /.sales-main --}}
</div>{{-- /.sales-container --}}
@endsection

{{-- styles moved inline — layout has no styles stack --}}

@push('scripts')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<style>
    /* Compact flatpickr */
    .flatpickr-calendar { font-size: 11px; width: 238px !important; padding: 2px; }
    .flatpickr-calendar .dayContainer { width: 224px !important; min-width: 224px !important; max-width: 224px !important; }
    .flatpickr-calendar .flatpickr-days { width: 224px !important; }
    .flatpickr-calendar .flatpickr-day { height: 26px; line-height: 26px; max-width: 32px; flex-basis: 32px; font-size: 11px; }
    .flatpickr-calendar .flatpickr-weekday { height: 22px; line-height: 22px; font-size: 10px; }
    .flatpickr-calendar .flatpickr-months { height: 28px; }
    .flatpickr-calendar .flatpickr-month { height: 28px; }
    .flatpickr-calendar .flatpickr-current-month { font-size: 12px; padding: 3px 0; height: 26px; }
    .flatpickr-calendar .flatpickr-monthDropdown-months { font-size: 11px; }
    .flatpickr-calendar .numInputWrapper { font-size: 11px; }
    .flatpickr-day.pref-day { color: #1b7a2e !important; font-weight: 700; }
    .flatpickr-day.pref-day:hover { background: #e7f6ea !important; }
    .flatpickr-day.pref-day-early { color: #b58900 !important; font-weight: 700; }
    .flatpickr-day.pref-day-early:hover { background: #fff5cc !important; }
</style>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
// Preferred delivery days state (updated when customer loads)
window._prefDeliveryDays = [];

// Color code → name lookup for preview
window._colorNameMap = @json(
    collect($exteriorColors ?? [])->mapWithKeys(fn($c) => [$c->code => $c->name])
    ->merge(collect($interiorColors ?? [])->mapWithKeys(fn($c) => [$c->code => $c->name]))
    ->merge(collect($laminateColors ?? [])->mapWithKeys(fn($c) => [$c->code => $c->name]))
    ->toArray()
);
window._expectedDeliveryFp = null;
window._leadDays = 14;

window._getMinDeliveryDate = function() {
    const entryEl = document.querySelector('input[name="entry_date"]');
    const base = entryEl && entryEl.value ? new Date(entryEl.value + 'T00:00:00') : new Date();
    const min = new Date(base);
    min.setDate(min.getDate() + (window._leadDays || 14));
    min.setHours(0,0,0,0);
    return min;
};

window._nextAvailableDeliveryDate = function() {
    const min = window._getMinDeliveryDate();
    const days = window._prefDeliveryDays || [];
    if (!days.length) return min;
    const d = new Date(min);
    for (let i = 0; i < 14; i++) {
        if (days.includes(d.getDay())) return d;
        d.setDate(d.getDate() + 1);
    }
    return min;
};

document.addEventListener('DOMContentLoaded', function () {
    if (typeof flatpickr !== 'undefined') {
        const el = document.getElementById('expected_delivery');
        if (el) {
            window._expectedDeliveryFp = flatpickr(el, {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'm/d/Y',
                allowInput: true,
                onDayCreate: function(dObj, dStr, fp, dayElem) {
                    const days = window._prefDeliveryDays || [];
                    const minDate = window._getMinDeliveryDate();
                    const thisDay = new Date(dayElem.dateObj); thisDay.setHours(0,0,0,0);
                    if (!days.length) return;
                    if (days.includes(thisDay.getDay())) {
                        if (thisDay.getTime() < minDate.getTime()) {
                            dayElem.classList.add('pref-day-early');
                        } else {
                            dayElem.classList.add('pref-day');
                        }
                    }
                },
                onChange: function(selectedDates, dateStr, fp) {
                    if (!selectedDates.length) return;
                    const days = window._prefDeliveryDays || [];
                    const picked = new Date(selectedDates[0]); picked.setHours(0,0,0,0);
                    const minDate = window._getMinDeliveryDate();
                    const dayNames = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

                    if (days.length && !days.includes(picked.getDay())) {
                        const allowed = days.map(d => dayNames[d]).join(', ');
                        const msg = `Cannot proceed — this customer only accepts deliveries on: ${allowed}.`;
                        (typeof toastr !== 'undefined') ? toastr.error(msg, 'Invalid Delivery Day') : alert(msg);
                        fp.setDate(window._nextAvailableDeliveryDate(), true);
                        return;
                    }
                    if (picked.getTime() < minDate.getTime()) {
                        const msg = `This date falls before the 14-day window required for production. Earliest is ${minDate.toISOString().slice(0,10)}.`;
                        (typeof toastr !== 'undefined') ? toastr.warning(msg, 'Production Lead Time') : alert(msg);
                        fp.setDate(window._nextAvailableDeliveryDate(), true);
                    }
                }
            });
        }
    }
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ══════════════════════════════════════════════
    // SELECT2: Initialize all searchable dropdowns
    // ══════════════════════════════════════════════
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('.s2-searchable').select2({
            minimumResultsForSearch: 0,
            width: '100%'
        });

        // Hook Select2 change into the tax rule handler
        $('#tax_rule_id').on('select2:select', function() {
            if (typeof handleTaxRuleChange === 'function') handleTaxRuleChange();
        });
    }

    // ══════════════════════════════════════════════
    // FRACTION PARSER — "36 1/2" → 36.5, "48 3/8" → 48.375
    // ══════════════════════════════════════════════
    function parseFraction(str) {
        if (!str) return 0;
        str = str.toString().trim();
        if (/^\d+\.?\d*$/.test(str)) return parseFloat(str);
        const mixed = str.match(/^(\d+)\s+(\d+)\/(\d+)$/);
        if (mixed) return parseInt(mixed[1]) + parseInt(mixed[2]) / parseInt(mixed[3]);
        const frac = str.match(/^(\d+)\/(\d+)$/);
        if (frac) return parseInt(frac[1]) / parseInt(frac[2]);
        const f = parseFloat(str);
        return isNaN(f) ? 0 : f;
    }

    function decToFraction(dec) {
        if (!dec && dec !== 0) return '';
        dec = parseFloat(dec);
        const whole = Math.floor(dec);
        const sixteenths = Math.round((dec - whole) * 16);
        if (sixteenths <= 0) return String(whole);
        if (sixteenths >= 16) return String(whole + 1);
        function gcd(a, b) { while (b) { [a, b] = [b, a % b]; } return a; }
        const g = gcd(sixteenths, 16);
        const num = sixteenths / g;
        const den = 16 / g;
        return whole > 0 ? `${whole} ${num}/${den}` : `${num}/${den}`;
    }

    // ══════════════════════════════════════════════
    // SIZE LIMITS: Width max 96, Height max 72
    // ══════════════════════════════════════════════
    const SIZE_LIMITS = { width: 96, height: 72 };
    let sizeValid = { width: true, height: true };

    function showSizeError(input, msg) {
        removeSizeError(input);
        input.classList.add('is-invalid');
        input.style.borderColor = '#dc3545';
        const wrapper = input.parentElement;
        wrapper.style.position = 'relative';
        const tooltip = document.createElement('div');
        tooltip.className = 'size-error-tooltip';
        tooltip.textContent = msg;
        wrapper.appendChild(tooltip);
    }

    function removeSizeError(input) {
        input.classList.remove('is-invalid');
        input.style.borderColor = '';
        const wrapper = input.parentElement;
        const existing = wrapper.querySelector('.size-error-tooltip');
        if (existing) existing.remove();
    }

    function isSizeValid() {
        return sizeValid.width && sizeValid.height;
    }

    document.querySelectorAll('.fraction-input').forEach(input => {
        const hiddenId = input.name + '_decimal';
        const hidden = document.getElementById(hiddenId);
        const fieldName = input.name; // 'width' or 'height'
        const maxVal = SIZE_LIMITS[fieldName] || Infinity;

        function sync() {
            const dec = parseFraction(input.value);
            if (hidden) hidden.value = dec;

            // Basic validation
            if (dec <= 0 && input.value.trim() !== '') {
                input.classList.add('is-invalid');
                sizeValid[fieldName] = false;
                return;
            }

            // Max size validation
            if (dec > maxVal) {
                showSizeError(input, `⚠ Max ${fieldName} is ${maxVal}"`);
                sizeValid[fieldName] = false;
            } else {
                removeSizeError(input);
                sizeValid[fieldName] = true;
            }

            // Toggle Add to Quote button
            const addBtn = document.getElementById('addToQuoteBtn');
            if (addBtn) {
                addBtn.disabled = !isSizeValid();
                addBtn.classList.toggle('btn-primary', isSizeValid());
                addBtn.classList.toggle('btn-danger', !isSizeValid());
            }
        }
        input.addEventListener('input', sync);
        input.addEventListener('change', sync);

        // Clear "0" on focus so user doesn't get "045" etc.
        input.addEventListener('focus', function() {
            if (input.value.trim() === '0') {
                input.value = '';
            }
            input.select();
        });

        // Restore "0" if left empty on blur
        input.addEventListener('blur', function() {
            if (input.value.trim() === '') {
                input.value = '0';
                sync();
            }
        });

        // Enter key navigation: Width → Height → Add
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                sync(); // ensure value is synced before moving
                if (input.name === 'width') {
                    // Move focus to height input
                    const heightInput = document.querySelector('input[name="height"].fraction-input');
                    if (heightInput) { heightInput.focus(); heightInput.select(); }
                } else if (input.name === 'height') {
                    // Click the Add button
                    const addBtn = document.getElementById('addToQuoteBtn');
                    if (addBtn && !addBtn.disabled) addBtn.click();
                }
            }
        });

        sync();
    });

    // ══════════════════════════════════════════════
    // SEARCHABLE CONFIGURATION DROPDOWN
    // ══════════════════════════════════════════════
    (function() {
        const searchInput = document.getElementById('configSearchInput');
        const hiddenSelect = document.getElementById('seriesTypeSelect');
        const dropdown = document.getElementById('configDropdown');
        if (!searchInput || !hiddenSelect || !dropdown) return;

        let allOptions = [];
        let activeIdx = -1;

        function showDropdown() { dropdown.style.display = 'block'; }
        function hideDropdown() { dropdown.style.display = 'none'; activeIdx = -1; }

        function refreshOptions() {
            allOptions = [];
            hiddenSelect.querySelectorAll('option').forEach(opt => {
                if (opt.value && !opt.disabled) allOptions.push({ value: opt.value, text: opt.textContent.trim() });
            });
        }

        function renderDropdown(filter) {
            const q = (filter || '').toLowerCase();
            const matches = q ? allOptions.filter(o => o.text.toLowerCase().includes(q)) : allOptions;
            if (matches.length === 0) {
                dropdown.innerHTML = '<div style="padding:8px 12px;color:#999;font-size:12px;text-align:center;">No matching configurations</div>';
            } else {
                dropdown.innerHTML = matches.map((o, i) => {
                    const esc = q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                    const highlighted = q ? o.text.replace(new RegExp(`(${esc})`, 'gi'), '<b style="color:#1976d2">$1</b>') : o.text;
                    return `<div class="cfg-opt" data-value="${o.value}" data-idx="${i}" style="padding:4px 10px;cursor:pointer;font-size:12px;border-bottom:1px solid #f0f0f0;">${highlighted}</div>`;
                }).join('');
            }
            showDropdown();
            activeIdx = -1;
            dropdown.querySelectorAll('.cfg-opt').forEach(el => {
                el.addEventListener('mouseenter', () => el.style.background = '#e3f2fd');
                el.addEventListener('mouseleave', () => el.style.background = '');
            });
        }

        function selectOption(value, text) {
            hiddenSelect.value = value;
            searchInput.value = text;
            hideDropdown();
            hiddenSelect.dispatchEvent(new Event('change'));
        }

        searchInput.addEventListener('focus', function() { refreshOptions(); if (allOptions.length > 0) renderDropdown(this.value); });
        searchInput.addEventListener('input', function() { refreshOptions(); renderDropdown(this.value); });
        searchInput.addEventListener('keydown', function(e) {
            const items = dropdown.querySelectorAll('.cfg-opt');
            if (e.key === 'ArrowDown') { e.preventDefault(); activeIdx = Math.min(activeIdx + 1, items.length - 1); items.forEach((el, i) => el.classList.toggle('active', i === activeIdx)); if (items[activeIdx]) items[activeIdx].scrollIntoView({ block: 'nearest' }); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); activeIdx = Math.max(activeIdx - 1, 0); items.forEach((el, i) => el.classList.toggle('active', i === activeIdx)); if (items[activeIdx]) items[activeIdx].scrollIntoView({ block: 'nearest' }); }
            else if (e.key === 'Enter') {
                e.preventDefault();
                if (activeIdx >= 0 && items[activeIdx]) {
                    selectOption(items[activeIdx].dataset.value, items[activeIdx].textContent);
                } else {
                    // No active selection — auto-select first visible match (case-insensitive)
                    const firstMatch = dropdown.querySelector('.cfg-opt');
                    if (firstMatch) {
                        selectOption(firstMatch.dataset.value, firstMatch.textContent.trim());
                    }
                }
            }
            else if (e.key === 'Escape') { hideDropdown(); searchInput.blur(); }
        });
        dropdown.addEventListener('click', function(e) { const opt = e.target.closest('.cfg-opt'); if (opt) selectOption(opt.dataset.value, opt.textContent); });
        document.addEventListener('click', function(e) { if (!e.target.closest('#configSearchWrapper')) hideDropdown(); });
    })();

    // ══════════════════════════════════════════════
    // CUSTOMER NUMBER: Enter key → fetch name → auto-submit
    // ══════════════════════════════════════════════
    const customerNumberInput = document.querySelector('input[name="customer_number"]');
    const customerNameInput = document.querySelector('input[name="customer_name"]');
    const quoteHeaderForm = document.getElementById('quoteHeaderForm');

    let _lastFetchedCustNumber = null;
    let _lastFetchFailed = false;

    function fetchCustomerAndSubmit(autoSubmit) {
        const number = customerNumberInput.value.trim();
        if (!number) { customerNameInput.value = ''; _lastFetchedCustNumber = null; _lastFetchFailed = false; return; }

        // Don't re-fetch the same number that already failed (prevents alert loop)
        if (_lastFetchFailed && number === _lastFetchedCustNumber) return;
        // Don't re-fetch the same number that already succeeded (unless auto-submitting)
        if (!autoSubmit && number === _lastFetchedCustNumber && !_lastFetchFailed && customerNameInput.value.trim()) return;

        _lastFetchedCustNumber = number;

        fetch(`/installer/customers/${number}`)
            .then(r => r.json())
            .then(d => {
                if (d && d.customer_name) {
                    _lastFetchFailed = false;
                    customerNameInput.value = d.customer_name;

                    // Populate billing address, email, phone fields
                    const setVal = (id, val) => { const el = document.getElementById(id); if (el) el.value = val || ''; };
                    setVal('billing_address', d.billing_address);
                    setVal('billing_city', d.billing_city);
                    // State: convert full name (e.g. "CALIFORNIA") to abbreviation (e.g. "CA")
                    const stateMap = {'ALABAMA':'AL','ALASKA':'AK','ARIZONA':'AZ','ARKANSAS':'AR','CALIFORNIA':'CA','COLORADO':'CO','CONNECTICUT':'CT','DELAWARE':'DE','FLORIDA':'FL','GEORGIA':'GA','HAWAII':'HI','IDAHO':'ID','ILLINOIS':'IL','INDIANA':'IN','IOWA':'IA','KANSAS':'KS','KENTUCKY':'KY','LOUISIANA':'LA','MAINE':'ME','MARYLAND':'MD','MASSACHUSETTS':'MA','MICHIGAN':'MI','MINNESOTA':'MN','MISSISSIPPI':'MS','MISSOURI':'MO','MONTANA':'MT','NEBRASKA':'NE','NEVADA':'NV','NEW HAMPSHIRE':'NH','NEW JERSEY':'NJ','NEW MEXICO':'NM','NEW YORK':'NY','NORTH CAROLINA':'NC','NORTH DAKOTA':'ND','OHIO':'OH','OKLAHOMA':'OK','OREGON':'OR','PENNSYLVANIA':'PA','RHODE ISLAND':'RI','SOUTH CAROLINA':'SC','SOUTH DAKOTA':'SD','TENNESSEE':'TN','TEXAS':'TX','UTAH':'UT','VERMONT':'VT','VIRGINIA':'VA','WASHINGTON':'WA','WEST VIRGINIA':'WV','WISCONSIN':'WI','WYOMING':'WY'};
                    let stateVal = (d.billing_state || '').toUpperCase().trim();
                    // If it's a full name, convert to abbreviation
                    if (stateVal.length > 2 && stateMap[stateVal]) stateVal = stateMap[stateVal];
                    if (typeof $ !== 'undefined' && $.fn.select2 && stateVal) {
                        $('#billing_state').val(stateVal).trigger('change');
                    } else {
                        const stEl = document.getElementById('billing_state');
                        if (stEl) stEl.value = stateVal;
                    }
                    setVal('billing_zip', d.billing_zip);
                    setVal('customer_email', d.email);
                    setVal('customer_phone', d.billing_phone);

                    // Populate tax rule from customer (Select2 aware)
                    const _taxSel = document.getElementById('tax_rule_id');
                    if (d.tax_rule_id && _taxSel) {
                        if (typeof $ !== 'undefined' && $.fn.select2) {
                            $('#tax_rule_id').val(d.tax_rule_id).trigger('change');
                        } else {
                            _taxSel.value = d.tax_rule_id;
                        }
                        if (typeof handleTaxRuleChange === 'function') handleTaxRuleChange();
                    }
                    if (d.resale_number && resaleInput) {
                        resaleInput.value = d.resale_number;
                    }
                    if (d.resale_verified && resaleVerifiedBadge) {
                        resaleVerifiedBadge.style.display = '';
                        resaleVerifiedBadge.title = 'Resale Document Verified on File';
                    } else if (resaleVerifiedBadge) {
                        resaleVerifiedBadge.style.display = 'none';
                    }

                    if (d.billing_address && document.getElementById('preview-billing-address')) {
                        document.getElementById('preview-billing-address').innerHTML = `<strong>${d.customer_name}</strong><br>${d.billing_address || ''}<br>${d.billing_city || ''}, ${d.billing_state || ''} ${d.billing_zip || ''}`;
                    }
                    if (d.delivery_address && document.getElementById('preview-shipping-address')) {
                        document.getElementById('preview-shipping-address').innerHTML = `<strong>${d.customer_name}</strong><br>${d.delivery_address || ''}<br>${d.delivery_city || ''}, ${d.delivery_state || ''} ${d.delivery_zip || ''}`;
                    }
                    // Preferred delivery days → highlight on Expected Delivery picker
                    window._prefDeliveryDays = (true) && Array.isArray(d.preferred_delivery_days)
                        ? d.preferred_delivery_days.map(n => parseInt(n, 10)).filter(n => !isNaN(n))
                        : [];
                    if (window._expectedDeliveryFp) {
                        if (typeof window._expectedDeliveryFp.redraw === 'function') window._expectedDeliveryFp.redraw();
                        // Auto-snap to next available preferred day on/after entry + 14
                        if (typeof window._nextAvailableDeliveryDate === 'function') {
                            window._expectedDeliveryFp.setDate(window._nextAvailableDeliveryDate(), true);
                        }
                    }

                    // Update Customer Details tab
                    updateCustomerTab(d, number);

                    // Grey out header fields for existing customer & show edit button
                    lockCustomerHeaderFields(true);

                    // Auto-submit ONLY if all required fields are filled (prevents blank PO)
                    if (autoSubmit && quoteHeaderForm) {
                        if (quoteHeaderForm.checkValidity()) {
                            quoteHeaderForm.submit();
                        } else {
                            // Show native validation tooltips so user sees what's missing
                            quoteHeaderForm.reportValidity();
                        }
                    }
                } else {
                    _lastFetchFailed = true;
                    customerNameInput.value = '';
                    alert('Customer not found.');
                }
            })
            .catch(() => { _lastFetchFailed = true; customerNameInput.value = ''; });
    }

    // ── Lock/unlock header fields for existing customer ──
    const _headerFieldIds = ['billing_address', 'billing_zip', 'billing_city', 'customer_phone', 'customer_email'];
    function lockCustomerHeaderFields(lock) {
        _headerFieldIds.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.readOnly = lock;
                el.style.background = lock ? '#f0f0f0' : '';
                el.style.color = lock ? '#666' : '';
            }
        });
        // billing_state is a <select> — use pointer-events to "lock" without disabling (disabled prevents form submit)
        const stEl = document.getElementById('billing_state');
        if (stEl) {
            stEl.style.pointerEvents = lock ? 'none' : '';
            stEl.style.background = lock ? '#f0f0f0' : '';
            stEl.style.color = lock ? '#666' : '';
            stEl.tabIndex = lock ? -1 : 0;
            // Also style the Select2 container if present
            const s2Container = stEl.closest('.select2') || stEl.nextElementSibling;
            if (s2Container && s2Container.classList.contains('select2-container')) {
                s2Container.style.pointerEvents = lock ? 'none' : '';
                s2Container.style.background = lock ? '#f0f0f0' : '';
                s2Container.style.opacity = lock ? '0.7' : '';
            }
            // Also try via jQuery Select2 if available
            if (typeof $ !== 'undefined' && $.fn.select2) {
                const $sel = $('#billing_state');
                if ($sel.length) {
                    const $s2 = $sel.next('.select2-container');
                    if ($s2.length) {
                        $s2.css({
                            'pointer-events': lock ? 'none' : '',
                            'background': lock ? '#f0f0f0' : '',
                            'opacity': lock ? '0.7' : ''
                        });
                    }
                }
            }
        }
        // Show/hide edit button
        const editBtn = document.getElementById('editCustomerFromHeader');
        if (editBtn) editBtn.style.display = lock ? '' : 'none';
    }

    // ── Open Edit Customer Modal from header ──
    document.getElementById('editCustomerFromHeader')?.addEventListener('click', function() {
        openCustomerEditModal();
    });

    function openCustomerEditModal() {
        const d = _currentCustomerData || {};
        const custNumber = d._custNumber || customerNumberInput?.value || '';
        const v = (k) => (d[k] || '').toString().replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;');

        const body = document.getElementById('editCustomerModalBody');
        if (!body) return;

        body.innerHTML = `
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card bg-light border-0"><div class="card-body p-3">
                        <h6 class="fw-bold mb-2"><i class="fas fa-user me-1 text-primary"></i> Customer Info</h6>
                        <div class="row g-2" style="font-size:0.85rem;">
                            <div class="col-6">
                                <label class="form-label mb-0 small text-muted">{{ __('Customer #') }}</label>
                                <input type="text" class="form-control form-control-sm" value="${custNumber}" readonly
                                       style="background:#f0f0f0; color:#666;">
                            </div>
                            <div class="col-6">
                                <label class="form-label mb-0 small text-muted">{{ __('Name') }}</label>
                                <input type="text" class="form-control form-control-sm" value="${v('customer_name')}" readonly
                                       style="background:#f0f0f0; color:#666;">
                            </div>
                        </div>
                    </div></div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-light border-0"><div class="card-body p-3">
                        <h6 class="fw-bold mb-2"><i class="fas fa-phone me-1 text-primary"></i> Contact</h6>
                        <div class="row g-2" style="font-size:0.85rem;">
                            <div class="col-6"><label class="form-label mb-0 small text-muted">{{ __('Phone') }}</label><input type="text" class="form-control form-control-sm" id="cm_billing_phone" value="${v('billing_phone')}"></div>
                            <div class="col-6"><label class="form-label mb-0 small text-muted">{{ __('Phone 2') }}</label><input type="text" class="form-control form-control-sm" id="cm_delivery_phone" value="${v('delivery_phone')}"></div>
                            <div class="col-6"><label class="form-label mb-0 small text-muted">{{ __('Fax') }}</label><input type="text" class="form-control form-control-sm" id="cm_billing_fax" value="${v('billing_fax')}"></div>
                            <div class="col-6"><label class="form-label mb-0 small text-muted">{{ __('Email') }}</label><input type="text" class="form-control form-control-sm" id="cm_email" value="${v('email')}"></div>
                            <div class="col-12"><label class="form-label mb-0 small text-muted">{{ __('Contact Person') }}</label><input type="text" class="form-control form-control-sm" id="cm_contact_name" value="${v('contact_name')}"></div>
                        </div>
                    </div></div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-light border-0"><div class="card-body p-3">
                        <h6 class="fw-bold mb-2"><i class="fas fa-file-invoice-dollar me-1 text-primary"></i> Billing Address</h6>
                        <div class="row g-2" style="font-size:0.85rem;">
                            <div class="col-12"><label class="form-label mb-0 small text-muted">{{ __('Address') }}</label><input type="text" class="form-control form-control-sm" id="cm_billing_address" value="${v('billing_address')}"></div>
                            <div class="col-12"><label class="form-label mb-0 small text-muted">{{ __('Address 2') }}</label><input type="text" class="form-control form-control-sm" id="cm_billing_address2" value="${v('billing_address2')}"></div>
                            <div class="col-5"><label class="form-label mb-0 small text-muted">{{ __('City') }}</label><input type="text" class="form-control form-control-sm" id="cm_billing_city" value="${v('billing_city')}"></div>
                            <div class="col-3"><label class="form-label mb-0 small text-muted">{{ __('State') }}</label><input type="text" class="form-control form-control-sm" id="cm_billing_state" value="${v('billing_state')}"></div>
                            <div class="col-4"><label class="form-label mb-0 small text-muted">{{ __('Zip') }}</label><input type="text" class="form-control form-control-sm" id="cm_billing_zip" value="${v('billing_zip')}"></div>
                            <div class="col-12"><label class="form-label mb-0 small text-muted">{{ __('Country') }}</label><input type="text" class="form-control form-control-sm" id="cm_billing_country" value="${v('billing_country')}"></div>
                        </div>
                    </div></div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-light border-0"><div class="card-body p-3">
                        <h6 class="fw-bold mb-2"><i class="fas fa-truck me-1 text-primary"></i> Delivery Address</h6>
                        <div class="row g-2" style="font-size:0.85rem;">
                            <div class="col-12"><label class="form-label mb-0 small text-muted">{{ __('Address') }}</label><input type="text" class="form-control form-control-sm" id="cm_delivery_address" value="${v('delivery_address')}"></div>
                            <div class="col-12"><label class="form-label mb-0 small text-muted">{{ __('Address 2') }}</label><input type="text" class="form-control form-control-sm" id="cm_delivery_address2" value="${v('delivery_address2')}"></div>
                            <div class="col-5"><label class="form-label mb-0 small text-muted">{{ __('City') }}</label><input type="text" class="form-control form-control-sm" id="cm_delivery_city" value="${v('delivery_city')}"></div>
                            <div class="col-3"><label class="form-label mb-0 small text-muted">{{ __('State') }}</label><input type="text" class="form-control form-control-sm" id="cm_delivery_state" value="${v('delivery_state')}"></div>
                            <div class="col-4"><label class="form-label mb-0 small text-muted">{{ __('Zip') }}</label><input type="text" class="form-control form-control-sm" id="cm_delivery_zip" value="${v('delivery_zip')}"></div>
                            <div class="col-12"><label class="form-label mb-0 small text-muted">{{ __('Country') }}</label><input type="text" class="form-control form-control-sm" id="cm_delivery_country" value="${v('delivery_country')}"></div>
                        </div>
                    </div></div>
                </div>
                <div class="col-12">
                    <div class="card bg-light border-0"><div class="card-body p-3">
                        <h6 class="fw-bold mb-2"><i class="fas fa-sticky-note me-1 text-primary"></i> Notes</h6>
                        <textarea class="form-control form-control-sm" id="cm_notes" rows="3" style="font-size:0.85rem;">${v('notes')}</textarea>
                    </div></div>
                </div>
            </div>`;

        var modal = new bootstrap.Modal(document.getElementById('editCustomerModal'));
        modal.show();
    }

    // ── Save from Edit Customer Modal ──
    document.getElementById('saveCustomerModalBtn')?.addEventListener('click', function() {
        const custNumber = _currentCustomerData?._custNumber || customerNumberInput?.value || '';
        if (!custNumber) return;

        const fields = [
            'billing_phone', 'delivery_phone', 'billing_fax', 'email', 'contact_name',
            'billing_address', 'billing_address2', 'billing_city', 'billing_state', 'billing_zip', 'billing_country',
            'delivery_address', 'delivery_address2', 'delivery_city', 'delivery_state', 'delivery_zip', 'delivery_country',
            'notes'
        ];
        const data = {};
        fields.forEach(f => {
            const el = document.getElementById('cm_' + f);
            if (el) data[f] = el.value;
        });

        const btn = this;
        btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        fetch(`/installer/customers/${custNumber}/update`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify(data)
        }).then(r => r.json()).then(resp => {
            if (resp.success) {
                // Update local customer data cache
                Object.assign(_currentCustomerData, data);

                // Sync billing fields back to the quote header form
                const setVal = (id, val) => { const el = document.getElementById(id); if (el) el.value = val || ''; };
                setVal('billing_address', data.billing_address);
                setVal('billing_city', data.billing_city);
                setVal('billing_zip', data.billing_zip);
                setVal('customer_email', data.email);
                setVal('customer_phone', data.billing_phone);
                // State
                const stateMap = {'ALABAMA':'AL','ALASKA':'AK','ARIZONA':'AZ','ARKANSAS':'AR','CALIFORNIA':'CA','COLORADO':'CO','CONNECTICUT':'CT','DELAWARE':'DE','FLORIDA':'FL','GEORGIA':'GA','HAWAII':'HI','IDAHO':'ID','ILLINOIS':'IL','INDIANA':'IN','IOWA':'IA','KANSAS':'KS','KENTUCKY':'KY','LOUISIANA':'LA','MAINE':'ME','MARYLAND':'MD','MASSACHUSETTS':'MA','MICHIGAN':'MI','MINNESOTA':'MN','MISSISSIPPI':'MS','MISSOURI':'MO','MONTANA':'MT','NEBRASKA':'NE','NEVADA':'NV','NEW HAMPSHIRE':'NH','NEW JERSEY':'NJ','NEW MEXICO':'NM','NEW YORK':'NY','NORTH CAROLINA':'NC','NORTH DAKOTA':'ND','OHIO':'OH','OKLAHOMA':'OK','OREGON':'OR','PENNSYLVANIA':'PA','RHODE ISLAND':'RI','SOUTH CAROLINA':'SC','SOUTH DAKOTA':'SD','TENNESSEE':'TN','TEXAS':'TX','UTAH':'UT','VERMONT':'VT','VIRGINIA':'VA','WASHINGTON':'WA','WEST VIRGINIA':'WV','WISCONSIN':'WI','WYOMING':'WY'};
                let stVal = (data.billing_state || '').toUpperCase().trim();
                if (stVal.length > 2 && stateMap[stVal]) stVal = stateMap[stVal];
                if (typeof $ !== 'undefined' && $.fn.select2) {
                    $('#billing_state').val(stVal).trigger('change');
                } else {
                    const stEl = document.getElementById('billing_state');
                    if (stEl) stEl.value = stVal;
                }

                // Update Customer Details tab too
                updateCustomerTab(_currentCustomerData, custNumber);

                // Close modal
                bootstrap.Modal.getInstance(document.getElementById('editCustomerModal'))?.hide();

                if (typeof toastr !== 'undefined') toastr.success('Customer details updated successfully!');
                else alert('Customer details updated successfully!');
            } else {
                alert('Error saving customer: ' + (resp.error || resp.message || 'Unknown error'));
                btn.disabled = false; btn.innerHTML = '<i class="fas fa-save me-1"></i> Save Changes';
            }
        }).catch(err => {
            alert('Failed to save customer details. ' + (err.message || ''));
            btn.disabled = false; btn.innerHTML = '<i class="fas fa-save me-1"></i> Save Changes';
        });
    });

    // Reset save button when modal is hidden
    document.getElementById('editCustomerModal')?.addEventListener('hidden.bs.modal', function() {
        const btn = document.getElementById('saveCustomerModalBtn');
        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-save me-1"></i> Save Changes'; }
    });

    // Store current customer data for edit mode
    let _currentCustomerData = null;
    let _isNewCustomer = false;

    // On page load: if customer already exists, lock header fields & fetch customer data
    if (customerNumberInput?.value?.trim() && customerNameInput?.value?.trim()) {
        lockCustomerHeaderFields(true);
        // Fetch full customer data so Edit Customer button works
        fetch('/installer/customers/' + encodeURIComponent(customerNumberInput.value.trim()), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(d => {
            if (d && d.customer_name) {
                updateCustomerTab(d, customerNumberInput.value.trim());
            }
        })
        .catch(() => {});
    }

    function updateCustomerTab(d, custNumber) {
        const container = document.getElementById('customerDetailsContent');
        if (!container) return;
        _currentCustomerData = Object.assign({}, d, { _custNumber: custNumber });
        const val = (v) => v || '--';
        const addr = (prefix) => {
            const a1 = d[prefix+'_address'] || '--';
            const city = d[prefix+'_city'] || '';
            const state = d[prefix+'_state'] || '';
            const zip = d[prefix+'_zip'] || '';
            const country = d[prefix+'_country'] || '';
            let html = `<div class="fw-semibold">${a1}</div>`;
            html += `<div>${city}${state ? ', '+state : ''} ${zip}</div>`;
            if (country) html += `<div>${country}</div>`;
            return html;
        };

        container.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                <div>
                    <h5 class="mb-0">${val(d.customer_name)}</h5>
                    <small class="text-muted">Customer # ${custNumber}</small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="editCustomerBtn" title="{{ __('Edit Customer Details') }}">
                        <i class="fas fa-pen me-1"></i> Edit
                    </button>
                    <span class="badge bg-primary">${val(d.customer_type || 'Dealer')}</span>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card bg-light border-0"><div class="card-body p-3">
                        <h6 class="fw-bold mb-2"><i class="fas fa-phone me-1 text-primary"></i> Contact</h6>
                        <table class="table table-sm table-borderless mb-0" style="font-size:0.85rem;">
                            <tr><td class="text-muted" style="width:100px;">{{ __('Phone') }}</td><td class="fw-semibold">${val(d.billing_phone)}</td></tr>
                            <tr><td class="text-muted">{{ __('Phone 2') }}</td><td class="fw-semibold">${val(d.delivery_phone)}</td></tr>
                            <tr><td class="text-muted">{{ __('Fax') }}</td><td class="fw-semibold">${val(d.billing_fax)}</td></tr>
                            <tr><td class="text-muted">{{ __('Email') }}</td><td class="fw-semibold">${val(d.email)}</td></tr>
                            <tr><td class="text-muted">{{ __('Contact') }}</td><td class="fw-semibold">${val(d.contact_name)}</td></tr>
                        </table>
                    </div></div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-light border-0"><div class="card-body p-3">
                        <h6 class="fw-bold mb-2"><i class="fas fa-file-invoice me-1 text-primary"></i> Account</h6>
                        <table class="table table-sm table-borderless mb-0" style="font-size:0.85rem;">
                            <tr><td class="text-muted" style="width:100px;">{{ __('Status') }}</td><td class="fw-semibold">${val(d.status)}</td></tr>
                            <tr><td class="text-muted">{{ __('Tier') }}</td><td class="fw-semibold">${val(d.tier_name)}</td></tr>
                            <tr><td class="text-muted">{{ __('Loyalty') }}</td><td class="fw-semibold">${val(d.loyalty_credit)}</td></tr>
                            <tr><td class="text-muted">{{ __('Total Spent') }}</td><td class="fw-semibold">$${parseFloat(d.total_spent || 0).toFixed(2)}</td></tr>
                            <tr><td class="text-muted">{{ __('Quote Via') }}</td><td class="fw-semibold">${val(d.receive_quote_via)}</td></tr>
                        </table>
                    </div></div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-light border-0"><div class="card-body p-3">
                        <h6 class="fw-bold mb-2"><i class="fas fa-file-invoice-dollar me-1 text-primary"></i> Billing Address</h6>
                        <div style="font-size:0.85rem;">
                            ${addr('billing')}
                            <div class="mt-1 text-muted"><small>Ph: ${val(d.billing_phone)} | Fax: ${val(d.billing_fax)}</small></div>
                        </div>
                    </div></div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-light border-0"><div class="card-body p-3">
                        <h6 class="fw-bold mb-2"><i class="fas fa-truck me-1 text-primary"></i> Delivery Address</h6>
                        <div style="font-size:0.85rem;">
                            ${addr('delivery')}
                            <div class="mt-1 text-muted"><small>Ph: ${val(d.delivery_phone)} | Fax: ${val(d.delivery_fax)}</small></div>
                        </div>
                    </div></div>
                </div>
            </div>`;

        // Attach edit button handler
        document.getElementById('editCustomerBtn')?.addEventListener('click', () => showCustomerEditForm());
    }

    function showCustomerEditForm() {
        const container = document.getElementById('customerDetailsContent');
        if (!container) return;
        const d = _currentCustomerData || {};
        const custNumber = d._custNumber || customerNumberInput?.value || '';
        const v = (k) => d[k] || '';

        container.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                <div>
                    <h5 class="mb-0">Edit Customer Details</h5>
                    <small class="text-muted">Customer # ${custNumber}</small>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-success" id="saveCustomerBtn"><i class="fas fa-save me-1"></i> Save</button>
                    <button type="button" class="btn btn-sm btn-secondary" id="cancelEditCustomerBtn"><i class="fas fa-times me-1"></i> Cancel</button>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card bg-light border-0"><div class="card-body p-3">
                        <h6 class="fw-bold mb-2"><i class="fas fa-phone me-1 text-primary"></i> Contact</h6>
                        <div class="row g-2" style="font-size:0.85rem;">
                            <div class="col-12"><label class="form-label mb-0 small text-muted">{{ __('Name') }}</label><input type="text" class="form-control form-control-sm" id="cedit_customer_name" value="${v('customer_name')}"></div>
                            <div class="col-6"><label class="form-label mb-0 small text-muted">{{ __('Phone') }}</label><input type="text" class="form-control form-control-sm" id="cedit_billing_phone" value="${v('billing_phone')}"></div>
                            <div class="col-6"><label class="form-label mb-0 small text-muted">{{ __('Phone 2') }}</label><input type="text" class="form-control form-control-sm" id="cedit_delivery_phone" value="${v('delivery_phone')}"></div>
                            <div class="col-6"><label class="form-label mb-0 small text-muted">{{ __('Fax') }}</label><input type="text" class="form-control form-control-sm" id="cedit_billing_fax" value="${v('billing_fax')}"></div>
                            <div class="col-6"><label class="form-label mb-0 small text-muted">{{ __('Email') }}</label><input type="text" class="form-control form-control-sm" id="cedit_email" value="${v('email')}"></div>
                            <div class="col-12"><label class="form-label mb-0 small text-muted">{{ __('Contact Person') }}</label><input type="text" class="form-control form-control-sm" id="cedit_contact_name" value="${v('contact_name')}"></div>
                        </div>
                    </div></div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-light border-0"><div class="card-body p-3">
                        <h6 class="fw-bold mb-2"><i class="fas fa-file-invoice-dollar me-1 text-primary"></i> Billing Address</h6>
                        <div class="row g-2" style="font-size:0.85rem;">
                            <div class="col-12"><label class="form-label mb-0 small text-muted">{{ __('Address') }}</label><input type="text" class="form-control form-control-sm" id="cedit_billing_address" value="${v('billing_address')}"></div>
                            <div class="col-12"><label class="form-label mb-0 small text-muted">{{ __('Address 2') }}</label><input type="text" class="form-control form-control-sm" id="cedit_billing_address2" value="${v('billing_address2')}"></div>
                            <div class="col-5"><label class="form-label mb-0 small text-muted">{{ __('City') }}</label><input type="text" class="form-control form-control-sm" id="cedit_billing_city" value="${v('billing_city')}"></div>
                            <div class="col-3"><label class="form-label mb-0 small text-muted">{{ __('State') }}</label><input type="text" class="form-control form-control-sm" id="cedit_billing_state" value="${v('billing_state')}"></div>
                            <div class="col-4"><label class="form-label mb-0 small text-muted">{{ __('Zip') }}</label><input type="text" class="form-control form-control-sm" id="cedit_billing_zip" value="${v('billing_zip')}"></div>
                            <div class="col-12"><label class="form-label mb-0 small text-muted">{{ __('Country') }}</label><input type="text" class="form-control form-control-sm" id="cedit_billing_country" value="${v('billing_country')}"></div>
                        </div>
                    </div></div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-light border-0"><div class="card-body p-3">
                        <h6 class="fw-bold mb-2"><i class="fas fa-truck me-1 text-primary"></i> Delivery Address</h6>
                        <div class="row g-2" style="font-size:0.85rem;">
                            <div class="col-12"><label class="form-label mb-0 small text-muted">{{ __('Address') }}</label><input type="text" class="form-control form-control-sm" id="cedit_delivery_address" value="${v('delivery_address')}"></div>
                            <div class="col-12"><label class="form-label mb-0 small text-muted">{{ __('Address 2') }}</label><input type="text" class="form-control form-control-sm" id="cedit_delivery_address2" value="${v('delivery_address2')}"></div>
                            <div class="col-5"><label class="form-label mb-0 small text-muted">{{ __('City') }}</label><input type="text" class="form-control form-control-sm" id="cedit_delivery_city" value="${v('delivery_city')}"></div>
                            <div class="col-3"><label class="form-label mb-0 small text-muted">{{ __('State') }}</label><input type="text" class="form-control form-control-sm" id="cedit_delivery_state" value="${v('delivery_state')}"></div>
                            <div class="col-4"><label class="form-label mb-0 small text-muted">{{ __('Zip') }}</label><input type="text" class="form-control form-control-sm" id="cedit_delivery_zip" value="${v('delivery_zip')}"></div>
                            <div class="col-12"><label class="form-label mb-0 small text-muted">{{ __('Country') }}</label><input type="text" class="form-control form-control-sm" id="cedit_delivery_country" value="${v('delivery_country')}"></div>
                        </div>
                    </div></div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-light border-0"><div class="card-body p-3">
                        <h6 class="fw-bold mb-2"><i class="fas fa-sticky-note me-1 text-primary"></i> Notes</h6>
                        <textarea class="form-control form-control-sm" id="cedit_notes" rows="4" style="font-size:0.85rem;">${v('notes')}</textarea>
                    </div></div>
                </div>
            </div>`;

        // Save handler
        document.getElementById('saveCustomerBtn')?.addEventListener('click', () => saveCustomerDetails(custNumber));
        // Cancel handler
        document.getElementById('cancelEditCustomerBtn')?.addEventListener('click', () => {
            if (_currentCustomerData) {
                updateCustomerTab(_currentCustomerData, custNumber);
            }
        });
    }

    function saveCustomerDetails(custNumber) {
        const fields = [
            'customer_name', 'email', 'contact_name',
            'billing_phone', 'billing_fax', 'billing_address', 'billing_address2',
            'billing_city', 'billing_state', 'billing_zip', 'billing_country',
            'delivery_phone', 'delivery_fax', 'delivery_address', 'delivery_address2',
            'delivery_city', 'delivery_state', 'delivery_zip', 'delivery_country',
            'notes'
        ];
        const data = {};
        fields.forEach(f => {
            const el = document.getElementById('cedit_' + f);
            if (el) data[f] = el.value;
        });

        const saveBtn = document.getElementById('saveCustomerBtn');
        if (saveBtn) { saveBtn.disabled = true; saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...'; }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        function doUpdate() {
            return fetch(`/installer/customers/${custNumber}/update`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify(data)
            }).then(r => r.json());
        }

        // If new customer, create first then update
        const savePromise = _isNewCustomer
            ? fetch('/installer/customers/quick-create', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ customer_number: custNumber, customer_name: data.customer_name || 'New Customer' })
              }).then(r => r.json()).then(resp => {
                  if (!resp.success) throw new Error(resp.message || 'Failed to create customer');
                  _isNewCustomer = false;
                  return doUpdate();
              })
            : doUpdate();

        savePromise.then(resp => {
            if (resp.success) {
                Object.assign(_currentCustomerData, data);
                if (data.customer_name && customerNameInput) {
                    customerNameInput.value = data.customer_name;
                    customerNameInput.readOnly = true;
                }
                updateCustomerTab(_currentCustomerData, custNumber);

                // Sync billing fields back to quote header
                const setVal = (id, val) => { const el = document.getElementById(id); if (el) el.value = val || ''; };
                setVal('billing_address', data.billing_address);
                setVal('billing_city', data.billing_city);
                setVal('billing_zip', data.billing_zip);
                setVal('customer_email', data.email);
                setVal('customer_phone', data.billing_phone);
                if (data.billing_state) {
                    const stEl = document.getElementById('billing_state');
                    if (typeof $ !== 'undefined' && $.fn.select2) {
                        let sv = (data.billing_state || '').toUpperCase().trim();
                        if (sv.length > 2) { const sm = {'ALABAMA':'AL','ALASKA':'AK','ARIZONA':'AZ','ARKANSAS':'AR','CALIFORNIA':'CA','COLORADO':'CO','CONNECTICUT':'CT','DELAWARE':'DE','FLORIDA':'FL','GEORGIA':'GA','HAWAII':'HI','IDAHO':'ID','ILLINOIS':'IL','INDIANA':'IN','IOWA':'IA','KANSAS':'KS','KENTUCKY':'KY','LOUISIANA':'LA','MAINE':'ME','MARYLAND':'MD','MASSACHUSETTS':'MA','MICHIGAN':'MI','MINNESOTA':'MN','MISSISSIPPI':'MS','MISSOURI':'MO','MONTANA':'MT','NEBRASKA':'NE','NEVADA':'NV','NEW HAMPSHIRE':'NH','NEW JERSEY':'NJ','NEW MEXICO':'NM','NEW YORK':'NY','NORTH CAROLINA':'NC','NORTH DAKOTA':'ND','OHIO':'OH','OKLAHOMA':'OK','OREGON':'OR','PENNSYLVANIA':'PA','RHODE ISLAND':'RI','SOUTH CAROLINA':'SC','SOUTH DAKOTA':'SD','TENNESSEE':'TN','TEXAS':'TX','UTAH':'UT','VERMONT':'VT','VIRGINIA':'VA','WASHINGTON':'WA','WEST VIRGINIA':'WV','WISCONSIN':'WI','WYOMING':'WY'}; sv = sm[sv] || sv; }
                        $('#billing_state').val(sv).trigger('change');
                    } else if (stEl) stEl.value = data.billing_state;
                }
                // Grey out header fields again after save
                lockCustomerHeaderFields(true);

                const container = document.getElementById('customerDetailsContent');
                if (container) {
                    const alertEl = document.createElement('div');
                    alertEl.className = 'alert alert-success alert-dismissible fade show py-1 px-2 mb-2';
                    alertEl.style.fontSize = '0.85rem';
                    alertEl.innerHTML = '<i class="fas fa-check me-1"></i> Customer details saved successfully.';
                    container.prepend(alertEl);
                    setTimeout(() => alertEl.remove(), 3000);
                }
            } else {
                alert('Error saving customer: ' + (resp.error || resp.message || 'Unknown error'));
                if (saveBtn) { saveBtn.disabled = false; saveBtn.innerHTML = '<i class="fas fa-save me-1"></i> Save'; }
            }
        })
        .catch(err => {
            alert('Failed to save customer details. ' + (err.message || ''));
            if (saveBtn) { saveBtn.disabled = false; saveBtn.innerHTML = '<i class="fas fa-save me-1"></i> Save'; }
        });
    }

    // ── New Customer "+" button handler ──
    document.getElementById('newCustomerBtn')?.addEventListener('click', function() {
        this.disabled = true;
        fetch('/installer/customers/next-number')
            .then(r => r.json())
            .then(data => {
                if (data.next_number) {
                    customerNumberInput.value = data.next_number;
                    customerNameInput.readOnly = false;
                    customerNameInput.value = '';
                    customerNameInput.focus();
                    _isNewCustomer = true;

                    // Unlock and clear billing address, email, phone for new customer entry
                    lockCustomerHeaderFields(false);
                    ['billing_address','billing_city','billing_zip','customer_email','customer_phone'].forEach(id => {
                        const el = document.getElementById(id);
                        if (el) el.value = '';
                    });
                    const stEl = document.getElementById('billing_state');
                    if (stEl) {
                        if (typeof $ !== 'undefined' && $.fn.select2) $('#billing_state').val('').trigger('change');
                        else stEl.value = '';
                    }

                    // Show empty customer tab with edit form
                    _currentCustomerData = { customer_name: '', _custNumber: data.next_number };
                    showCustomerEditForm();
                    // Switch to Customer Details tab
                    const custTab = document.getElementById('customer-tab');
                    if (custTab) new bootstrap.Tab(custTab).show();
                }
                this.disabled = false;
            })
            .catch(() => { this.disabled = false; });
    });

    // Enter key on customer number field: fetch customer then auto-submit
    customerNumberInput?.addEventListener('input', function() {
        // Reset fetch tracking when user changes the number
        _lastFetchFailed = false;
        _lastFetchedCustNumber = null;
    });

    customerNumberInput?.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            _isNewCustomer = false;
            _lastFetchFailed = false;
            _lastFetchedCustNumber = null;
            customerNameInput.readOnly = true;

            const refInput = document.getElementById('reference');
            const hasRef = refInput && refInput.value.trim();

            // Fetch customer; only auto-submit if PO is filled
            fetchCustomerAndSubmit(hasRef ? true : false);

            // If PO is empty, warn and focus the PO field
            if (!hasRef) {
                setTimeout(function() {
                    if (refInput) refInput.focus();
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Please enter a Reference (PO) number before starting the quote.', 'PO Required');
                    } else {
                        alert('Please enter a Reference (PO) number before starting the quote.');
                    }
                }, 800);
            }
        }
    });

    // Blur still fetches customer name (but does NOT auto-submit)
    customerNumberInput?.addEventListener('blur', function() {
        if (!_isNewCustomer) {
            customerNameInput.readOnly = true;
            fetchCustomerAndSubmit(false);
        }
    });

    // Intercept form submit (Start/Update button click):
    // If customer_name is empty, fetch it first then submit
    quoteHeaderForm?.addEventListener('submit', function(e) {
        const name = customerNameInput?.value?.trim();
        const number = customerNumberInput?.value?.trim();

        if (!name && number) {
            e.preventDefault();
            fetchCustomerAndSubmit(true);
        }
    });

    // ── Tax Rule dropdown logic ──
    const taxRuleSelect = document.getElementById('tax_rule_id');
    const resaleGroup = document.getElementById('resaleNumberGroup');
    const resaleInput = document.getElementById('resale_number');
    const resaleVerifiedBadge = document.getElementById('resaleVerifiedBadge');
    const isTaxExemptField = document.getElementById('is_tax_exempt');

    function handleTaxRuleChange() {
        if (!taxRuleSelect) return;
        const opt = taxRuleSelect.selectedOptions[0];
        const isExempt = opt?.dataset?.exempt === '1';

        if (isExempt) {
            resaleGroup.style.display = '';
            if (resaleInput && !resaleInput.value) resaleInput.required = true;
        } else {
            resaleGroup.style.display = 'none';
            if (resaleInput) { resaleInput.required = false; }
        }
        if (isTaxExemptField) isTaxExemptField.value = isExempt ? '1' : '0';
    }

    // Listen via jQuery for Select2 compatibility, with native fallback
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('#tax_rule_id').on('change', handleTaxRuleChange);
    } else {
        taxRuleSelect?.addEventListener('change', handleTaxRuleChange);
    }
    // Run on page load (for edit mode)
    handleTaxRuleChange();

    // Wire the second "+" button on Customer Name to the same handler
    document.getElementById('newCustomerBtn2')?.addEventListener('click', function() {
        document.getElementById('newCustomerBtn')?.click();
    });

    @if(isset($quote) && $quote)

    const seriesSelect = document.getElementById('seriesSelect');
    const seriesTypeSelect = document.getElementById('seriesTypeSelect');
    const widthInput = document.querySelector('input[name="width"]');
    const heightInput = document.querySelector('input[name="height"]');
    const qtyInput = document.querySelector('input[name="qty"]');
    const priceSpan = document.getElementById('globalTotalPrice');
    const seriesIdField = document.getElementById('series_id');
    const seriesTypeField = document.getElementById('series_type');
    const priceField = document.getElementById('price');
    const totalField = document.getElementById('total');
    const discountField = document.getElementById('discount');
    const descField = document.getElementById('description');

    let currentSeries = seriesSelect?.value || null;
    let currentType = seriesTypeSelect?.value || null;
    let frameAddons = {};
    let glassAddons = {};

    const NFRC_MAP = {
        'CLR/CLR': { u: '0.47', s: '0.56', v: '0.63', c: '45' },
        'LE3/CLR': { u: '0.28', s: '0.22', v: '0.52', c: '62' },
        'LE3/LAM': { u: '0.28', s: '0.20', v: '0.41', c: '62' },
        'SB6/CLR': { u: '0.29', s: '0.27', v: '0.53', c: '60' },
    };
    const GLASS_PANE_MAP = {
        'CLR/CLR': { f1: '3.1MM CLR / 3.1MM CLR',  f2: '3.1MM CLR / 3.1MM CLR' },
        'LE3/CLR': { f1: '3.1MM LE3 / 3.1MM CLR',  f2: '3.1MM LE3 / 3.1MM CLR' },
        'LE3/LAM': { f1: '3.1MM LE3 / 3.1MM LAM',  f2: '3.1MM LE3 / 3.1MM LAM' },
        'SB6/CLR': { f1: '3.1MM SB6 / 3.1MM CLR',  f2: '3.1MM SB6 / 3.1MM CLR' },
    };
    const DEFAULT_NFRC = { u: '0.28', s: '0.22', v: '0.52', c: '62' };
    const DEFAULT_PANE = { f1: '3.1MM LE3 / 3.1MM CLR', f2: '3.1MM LE3 / 3.1MM CLR' };

    // Live NFRC map keyed by SeriesConfiguration.series_type (e.g. "XOX-B1"),
    // populated from /rating/nfrc-master/series-map.json. Falls back to
    // glass-type NFRC_MAP if the active series has no rating row yet.
    let SERIES_NFRC_MAP = {};
    let NFRC_ACTIVE_VARIANT = 'main';
    (function loadSeriesNfrc() {
        fetch('{{ route("installer.quotes.seriesMap") }}', { credentials: 'same-origin' })
            .then(r => r.ok ? r.json() : null)
            .then(j => { if (j && j.map) { SERIES_NFRC_MAP = j.map; NFRC_ACTIVE_VARIANT = j.variant || 'main'; } })
            .catch(() => {});
    })();

    function getNfrc(glassType) {
        // Prefer the NFRC row mapped to the currently-selected series_type
        if (currentType && SERIES_NFRC_MAP[currentType]) return SERIES_NFRC_MAP[currentType];
        return NFRC_MAP[glassType] || DEFAULT_NFRC;
    }
    function getPane(glassType, specialtyGlass) {
        const pane = GLASS_PANE_MAP[glassType] || DEFAULT_PANE;
        if (specialtyGlass && specialtyGlass !== '' && specialtyGlass !== 'None') {
            const sg = specialtyGlass.toUpperCase();
            return {
                f1: pane.f1.replace(/\/\s*\S+$/, '/ 3.1MM ' + sg),
                f2: pane.f2.replace(/\/\s*\S+$/, '/ 3.1MM ' + sg)
            };
        }
        return pane;
    }
    function glassLabel(glassType, specialtyGlass) {
        const parts = (glassType || 'CLR/CLR').split('/');
        if (specialtyGlass && specialtyGlass !== '' && specialtyGlass !== 'None') {
            parts[1] = specialtyGlass;
        }
        return parts.join(' / ');
    }

    seriesSelect?.addEventListener('change', function() {
        currentSeries = this.value;
        if (seriesIdField) seriesIdField.value = currentSeries;
        const configSearch = document.getElementById('configSearchInput');
        if (!currentSeries) {
            seriesTypeSelect.innerHTML = '<option value="">Select configuration</option>';
            if (configSearch) configSearch.value = '';
            return;
        }
        // Show/hide Three Pane checkbox based on pane management
        var threePaneWrap = document.getElementById('threePaneWrap');
        var threePaneCb = document.getElementById('hasThreePane');
        if (threePaneWrap && typeof _seriesPaneTypes !== 'undefined') {
            var allowed = _seriesPaneTypes[currentSeries] || [];
            if (allowed.indexOf('three_pane') !== -1) {
                threePaneWrap.style.display = '';
            } else {
                threePaneWrap.style.display = 'none';
                if (threePaneCb) { threePaneCb.checked = false; threePaneCb.dispatchEvent(new Event('change')); }
            }
        }
        // Filter window types based on series
        if (typeof filterWindowTypesBySeries === 'function') filterWindowTypesBySeries(currentSeries);
        fetch(`/installer/quotes/series-types/${currentSeries}`)
            .then(r => r.json())
            .then(types => {
                const currentValue = seriesTypeSelect.value;
                seriesTypeSelect.innerHTML = '<option value="">Select configuration</option>';
                types.forEach(t => {
                    const opt = document.createElement('option');
                    opt.value = t; opt.textContent = t;
                    if (t === currentValue) opt.selected = true;
                    seriesTypeSelect.appendChild(opt);
                });
                if (configSearch) configSearch.value = currentValue || '';
                // Re-apply window type filter if one is active
                if (window._activeWindowTypeCode && typeof filterConfigurationsByType === 'function') {
                    filterConfigurationsByType(window._activeWindowTypeCode);
                }
            });
    });

    seriesTypeSelect?.addEventListener('change', function() {
        currentType = this.value;
        if (seriesTypeField) seriesTypeField.value = currentType;
        const configSearch = document.getElementById('configSearchInput');
        if (configSearch) configSearch.value = this.value || '';
        updateWindowPreview();
        calcPrice();
        fetchPanelLayout(currentType);
        // Show/hide shape section based on whether this is a PW config
        if (typeof checkShapeSectionVisibility === 'function') checkShapeSectionVisibility();
    });

    widthInput?.addEventListener('input', function() { updateWindowPreview(); calcPrice(); recalcPanelDefaults(); });
    heightInput?.addEventListener('input', function() { updateWindowPreview(); calcPrice(); recalcPanelDefaults(); });
    qtyInput?.addEventListener('input', calcPrice);

    // Check shape visibility on page load (in case config is already PW)
    if (typeof checkShapeSectionVisibility === 'function') checkShapeSectionVisibility();

    function calcPrice() {
        if (!currentSeries || !currentType || !widthInput?.value || !heightInput?.value) {
            if (priceSpan) priceSpan.textContent = '0.00'; return;
        }
        const w = parseFraction(widthInput.value), h = parseFraction(heightInput.value);
        if (w <= 0 || h <= 0) { if (priceSpan) priceSpan.textContent = '0.00'; return; }
        // Block if size exceeds limits
        if (!isSizeValid()) { if (priceSpan) priceSpan.textContent = '0.00'; return; }
        fetch('/installer/quotes/check-price', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ series_id: currentSeries, series_type: currentType, width: w, height: h, customer_number: document.querySelector('input[name="customer_number"]')?.value })
        })
        .then(r => r.json())
        .then(d => {
            if (d.price) {
                const qty = qtyInput?.value || 1;
                const basePrice = parseFloat(d.price);
                const addonTotal = Object.values(frameAddons).reduce((a, b) => a + b, 0) + Object.values(glassAddons).reduce((a, b) => a + b, 0);
                const total = ((basePrice + addonTotal) * parseFloat(qty)).toFixed(2);
                if (priceSpan) priceSpan.textContent = total;
                if (priceField) priceField.value = (basePrice + addonTotal).toFixed(2);
                if (totalField) totalField.value = total;
                if (discountField) discountField.value = (d.discount * qty || 0).toFixed(2);
            }
        });
    }

    function buildGlassString(form) {
        const glassType = form.querySelector('[name="glass_type"]').value;
        const tempered = form.querySelector('[name="tempered"]').value;
        const glassParts = glassType.split('/');
        let glass1 = glassParts[0] || '', glassMiddle = glassParts.length === 3 ? glassParts[1] : '', glass2 = glassParts[glassParts.length - 1] || '';
        if (tempered === 'All') { glass1 += '_TEMP'; glass2 += '_TEMP'; if (glassMiddle) glassMiddle += '_TEMP'; }
        else if (tempered === 'Select') {
            const gf1 = [], gf2 = [];
            form.querySelectorAll('input[name="tempered_fields[]"]:checked').forEach(cb => {
                if (cb.value.startsWith('gf1_')) gf1.push(cb.value.split('_')[1].toUpperCase());
                else if (cb.value.startsWith('gf2_')) gf2.push(cb.value.split('_')[1].toUpperCase());
            });
            if (gf1.length > 0) glass1 += '_TEMP';
            if (gf2.length > 0) glass2 += '_TEMP';
        }
        return glassMiddle ? `${glass1} / ${glassMiddle} / ${glass2}` : `${glass1} / ${glass2}`;
    }

    function buildGridString(form) {
        const gridPattern = form.querySelector('[name="grid_pattern"]').value;
        const gridProfile = form.querySelector('[name="grid_profile"]').value;
        const gridDetail = form.querySelector('[name="grid_detail"]')?.value || '';
        if (!gridPattern || gridPattern === '' || gridPattern === 'None') return 'N/A';
        let label = gridPattern;
        if (gridDetail) label += ' (' + gridDetail + ')';
        return label;
    }

    document.getElementById('quoteItemForm')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        if (!currentSeries || !currentType) { alert('Select Series and Configuration first'); return; }
        // Validate size limits
        if (!isSizeValid()) {
            alert(`Size exceeds limits. Max Width: ${SIZE_LIMITS.width}", Max Height: ${SIZE_LIMITS.height}". Please correct before adding.`);
            return;
        }
        const form = this;
        const w = parseFraction(widthInput?.value), h = parseFraction(heightInput?.value);
        if (!w || !h) { alert('Enter width and height'); return; }
        document.getElementById('width_decimal').value = w;
        document.getElementById('height_decimal').value = h;
        const seriesName = seriesSelect?.options[seriesSelect.selectedIndex]?.text || '';
        const frameType = form.querySelector('[name="frame_type"]').value;
        const glassType = form.querySelector('[name="glass_type"]').value;
        const wDisplay = widthInput.value.trim(), hDisplay = heightInput.value.trim();
        const description = `${seriesName}-${currentType} ${frameType} ${wDisplay}X${hDisplay} ${glassType}`;

        widthInput.value = w;
        heightInput.value = h;
        const glassString = buildGlassString(form);
        const gridString = buildGridString(form);
        if (descField) descField.value = description;
        if (seriesIdField) seriesIdField.value = currentSeries;
        if (seriesTypeField) seriesTypeField.value = currentType;
        document.getElementById('glass').value = glassString;
        document.getElementById('grid').value = gridString;

        try {
            const formData = new FormData(this);
            widthInput.value = wDisplay;
            heightInput.value = hDisplay;
            const response = await fetch(this.action, { method: 'POST', headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content}, body: formData });
            const d = await response.json();
            const editingId = document.getElementById('editing_item_id').value;
            if (d.success) {
                if (editingId) {
                    // Update mode: refresh existing row and preview
                    updateQuoteRow(d.item, glassString, gridString, description);
                    await updateDetailedPreviewItem(d.item);
                    cancelEditMode();
                } else {
                    // Add mode: append new row
                    widthInput.value = '0'; heightInput.value = '0'; qtyInput.value = '1';
                    if (priceSpan) priceSpan.textContent = '0.00';
                    appendQuoteRow(d.item, glassString, gridString, description);
                    await appendDetailedPreviewItem(d.item, document.querySelectorAll('.line-item').length + 1);
                }
                recalcTotals(); attachQtyListeners(); attachDeleteListeners(); attachItemClickListeners(); attachEditListeners();
            } else { widthInput.value = wDisplay; heightInput.value = hDisplay; alert('Error: ' + (d.message || 'Failed')); }
        } catch(err) {
            widthInput.value = wDisplay; heightInput.value = hDisplay;
            console.error(err); alert('Error - check console');
        }
    });
    
    function appendQuoteRow(item, glassString, gridString, description) {
        const tableBody = document.querySelector('#quoteDetailsTable tbody');
        const row = document.createElement('tr');
        row.setAttribute('data-id', item.id); row.classList.add('item-row');
        const itemData = buildItemData(item, glassString, gridString);
        row.setAttribute('data-item-json', JSON.stringify(itemData));
        row.innerHTML = `
            <td class="item-description" data-series-id="${item.series_id}" data-series-type="${item.series_type}" data-width="${item.width}" data-height="${item.height}">${description}</td>
            <td><input type="number" class="form-control form-control-sm qty-input" value="${item.qty}" style="width: 60px;" data-id="${item.id}" data-price="${item.price}"></td>
            <td>${decToFraction(item.width)}" x ${decToFraction(item.height)}"</td>
            <td>${glassString}</td>
            <td>${gridString}</td>
            <td class="item-price">$${parseFloat(item.price).toFixed(2)}</td>
            <td class="item-total" data-id="${item.id}">$${parseFloat(item.total).toFixed(2)}</td>
            <td class="text-nowrap"><a href="#" class="text-primary edit-row me-1" data-id="${item.id}" title="{{ __('Edit') }}"><i data-feather="edit-2"></i></a><a href="#" class="text-danger remove-row" data-id="${item.id}"><i data-feather="trash-2"></i></a></td>`;
        tableBody.appendChild(row);
        if (window.feather) feather.replace();
    }

    function buildItemData(item, glassString, gridString) {
        return {
            id: item.id,
            series_id: item.series_id || currentSeries,
            series_type: item.series_type || currentType,
            width: item.width,
            height: item.height,
            qty: item.qty,
            price: item.price,
            total: item.total,
            glass_type: item.glass_type || document.querySelector('[name="glass_type"]').value,
            glass: glassString,
            grid: gridString,
            grid_pattern: document.querySelector('[name="grid_pattern"]').value,
            grid_profile: document.querySelector('[name="grid_profile"]').value,
            grid_detail: document.querySelector('[name="grid_detail"]')?.value || '',
            frame_type: document.querySelector('[name="frame_type"]').value,
            fin_type: document.querySelector('[name="fin_type"]').value,
            color_config: document.querySelector('[name="color_config"]').value,
            color_exterior: document.querySelector('[name="color_exterior"]').value,
            color_exterior_custom: document.querySelector('[name="color_exterior_custom"]')?.value || '',
            color_interior: document.querySelector('[name="color_interior"]').value,
            color_interior_custom: document.querySelector('[name="color_interior_custom"]')?.value || '',
            spacer: document.querySelector('[name="spacer"]').value,
            tempered: document.querySelector('[name="tempered"]').value,
            specialty_glass: document.querySelector('[name="specialty_glass"]').value,
            knocked_down: document.querySelector('[name="knocked_down"][type="checkbox"]').checked ? 1 : 0,
            retrofit_bottom_only: document.querySelector('[name="retrofit_bottom_only"][type="checkbox"]').checked ? 1 : 0,
            block_frame_bottom: document.querySelector('[name="block_frame_bottom"][type="checkbox"]')?.checked ? 1 : 0,
            no_logo_lock: document.querySelector('[name="no_logo_lock"][type="checkbox"]').checked ? 1 : 0,
            double_lock: document.querySelector('[name="double_lock"][type="checkbox"]').checked ? 1 : 0,
            custom_lock_position: document.querySelector('[name="custom_lock_position"][type="checkbox"]').checked ? 1 : 0,
            custom_vent_latch: document.querySelector('[name="custom_vent_latch"][type="checkbox"]').checked ? 1 : 0,
            internal_note: document.querySelector('[name="internal_note"]').value,
            shape_definition_id: item.shape_definition_id || document.getElementById('shape_definition_id')?.value || null,
            shape_code: item.shape_code || document.getElementById('shape_code')?.value || '',
            shape_params: item.shape_params || document.getElementById('shape_params')?.value || '',
            panel_dimensions: item.panel_dimensions || document.getElementById('panel_dimensions_json')?.value || '',
        };
    }

    function updateQuoteRow(item, glassString, gridString, description) {
        const row = document.querySelector(`#quoteDetailsTable tbody tr[data-id="${item.id}"]`);
        if (!row) return;
        const itemData = buildItemData(item, glassString, gridString);
        row.setAttribute('data-item-json', JSON.stringify(itemData));
        row.innerHTML = `
            <td class="item-description" data-series-id="${item.series_id}" data-series-type="${item.series_type}" data-width="${item.width}" data-height="${item.height}">${description}</td>
            <td><input type="number" class="form-control form-control-sm qty-input" value="${item.qty}" style="width: 60px;" data-id="${item.id}" data-price="${item.price}"></td>
            <td>${decToFraction(item.width)}" x ${decToFraction(item.height)}"</td>
            <td>${glassString}</td>
            <td>${gridString}</td>
            <td class="item-price">$${parseFloat(item.price).toFixed(2)}</td>
            <td class="item-total" data-id="${item.id}">$${parseFloat(item.total).toFixed(2)}</td>
            <td class="text-nowrap"><a href="#" class="text-primary edit-row me-1" data-id="${item.id}" title="{{ __('Edit') }}"><i data-feather="edit-2"></i></a><a href="#" class="text-danger remove-row" data-id="${item.id}"><i data-feather="trash-2"></i></a></td>`;
        if (window.feather) feather.replace();
    }

    async function updateDetailedPreviewItem(item) {
        const existing = document.querySelector(`.line-item[data-item-id="${item.id}"]`);
        if (!existing) return;
        const index = Array.from(document.querySelectorAll('.line-item')).indexOf(existing) + 1;
        const _extOpt = item.color_exterior ? (document.querySelector(`#lamExteriorSelect option[value="${item.color_exterior}"]`) || document.querySelector(`#baseWindowColor option[value="${item.color_exterior}"]`)) : null;
        const diagramSVG = await generateSmallDiagramSVG(item.series_type, item.width, item.height, item.color_exterior || 'WH', _extOpt?.dataset?.hex || '');
        const gt = item.glass_type || 'CLR/CLR';
        const nfrc = getNfrc(gt);
        const pane = getPane(gt, item.specialty_glass);
        const gtLabel = glassLabel(gt, item.specialty_glass);
        existing.innerHTML = `
            <div class="line-header">
                <div class="line-number">Line ${index}</div>
                <div class="line-pricing price-field">
                    <div>List: $${parseFloat(item.price).toFixed(2)}</div>
                    <div>Unit(Disc): $${parseFloat(item.price).toFixed(2)}</div>
                    <div class="preview-item-qty">Qty: ${item.qty}</div>
                    <div class="price preview-item-total">Price: $${parseFloat(item.total).toFixed(2)}</div>
                </div>
            </div>
            <div class="window-diagram">
                ${diagramSVG || `<div style="width:120px;height:80px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;font-size:10px;">${decToFraction(item.width)}" x ${decToFraction(item.height)}"</div>`}
                <div style="text-align: center; margin-top: 3px; font-size: 9px; font-weight: bold;">Outside View</div>
            </div>
            <div style="overflow: hidden;">
                <div class="spec-row"><div class="spec-label">PRODUCT:</div><div class="spec-value">2101-VINYL DYNAMIC SLIDING WINDOW</div></div>
                <div class="spec-row"><div class="spec-label">UNIT:</div><div class="spec-value">${item.series_type || 'IM-XO'}, SIZE: W ${decToFraction(item.width)}" X H ${decToFraction(item.height)}"</div></div>
                <div class="spec-row"><div class="spec-label">MATERIAL:</div><div class="spec-value">MULTI-CHAMBER VINYL PROFILE</div></div>
                <div class="spec-row"><div class="spec-label">FRAME:</div><div class="spec-value">${(item.frame_type || 'RETROFIT 1 3/4"').toUpperCase()}</div></div>
                <div class="spec-row"><div class="spec-label">EXTERIOR/INTERIOR FINISH:</div><div class="spec-value">${((window._colorNameMap && item.color_exterior ? (window._colorNameMap[item.color_exterior] || item.color_exterior) : 'WHITE') + (item.color_exterior_custom ? ' (' + item.color_exterior_custom + ')' : '')).toUpperCase()} / ${((window._colorNameMap && item.color_interior ? (window._colorNameMap[item.color_interior] || item.color_interior) : 'WHITE') + (item.color_interior_custom ? ' (' + item.color_interior_custom + ')' : '')).toUpperCase()}</div></div>
                <div class="spec-row"><div class="spec-label">GRID TYPE:</div><div class="spec-value">${(item.grid && item.grid !== 'None' && item.grid !== 'N/A') ? (item.grid_profile || 'S-1') + ', ' + (item.grid_pattern || '') + (item.grid_detail ? ' (' + item.grid_detail + ')' : '') : 'N/A'}</div></div>
                ${(item.grid && item.grid !== 'None' && item.grid !== 'N/A') ? '<div class="customer-ref"><em>Cus. Ref: No charge on grids</em></div>' : ''}
                <div class="glass-details">
                    <div style="font-weight: bold; margin-bottom: 5px;">*****GLASS OPTIONS*****</div>
                    <div>GLASS TYPE: ${gtLabel}</div>
                    <div>FIELD 1: ${pane.f1}, IG THICK: 3/4"</div>
                    <div>FIELD 2: ${pane.f2}, IG THICK: 3/4"</div>
                    <div>GAS TYPE: ARGON FILLED, SPACER: ${(item.spacer || 'SUPERSPACER').toUpperCase()}</div>
                    ${item.internal_note ? '<div style="margin-top: 5px; font-size: 10px; color: #c00;"><strong>*****SPECIAL NOTES*****</strong><br>' + item.internal_note + '</div>' : ''}
                </div>
                <div style="margin-top: 8px; font-size: 10px; color: #555;">
                    <strong>*******NFRC********</strong><br>
                    UFACTOR: ${nfrc.u}, SHGC: ${nfrc.s}, VT: ${nfrc.v}, CR: ${nfrc.c}
                </div>
            </div>`;
        updateDetailedPreviewTotals();
    }

    async function generateSmallDiagramSVG(seriesType, w, h, colorCode, hexColor) {
        try {
            let url = `/installer/quotes/window-preview?type=${encodeURIComponent(seriesType)}&width=${w}&height=${h}&maxSize=120`;
            if (colorCode) url += `&color=${encodeURIComponent(colorCode)}`;
            if (hexColor) url += `&hexColor=${encodeURIComponent(hexColor)}`;
            const r = await fetch(url);
            return await r.text();
        } catch { return ''; }
    }

    function attachItemClickListeners() {
        document.querySelectorAll('.item-row').forEach(row => {
            row.removeEventListener('click', handleItemClick);
            row.addEventListener('click', handleItemClick);
        });
    }

    function attachEditListeners() {
        document.querySelectorAll('.edit-row').forEach(btn => {
            btn.removeEventListener('click', handleEditClick);
            btn.addEventListener('click', handleEditClick);
        });
    }

    function handleEditClick(e) {
        e.preventDefault();
        e.stopPropagation();
        const row = this.closest('.item-row');
        if (row) {
            // Simulate a row click to populate form and preview
            document.querySelectorAll('.item-row').forEach(r => r.classList.remove('selected'));
            row.classList.add('selected');
            populateFormFromRow(row, true);
        }
    }

    function handleItemClick(e) {
        if (e.target.closest('.qty-input') || e.target.closest('.remove-row') || e.target.closest('.edit-row')) return;
        document.querySelectorAll('.item-row').forEach(r => r.classList.remove('selected'));
        this.classList.add('selected');
        populateFormFromRow(this, true);
    }

    function enterEditMode(itemId) {
        document.getElementById('editing_item_id').value = itemId;
        document.getElementById('card1HeaderIcon').className = 'fas fa-edit me-2 text-warning';
        document.getElementById('card1HeaderText').textContent = 'Edit Quote Item';
        document.getElementById('addToQuoteBtnIcon').className = 'fas fa-save';
        document.getElementById('addToQuoteBtnText').textContent = 'Update';
        document.getElementById('addToQuoteBtn').classList.remove('btn-primary');
        document.getElementById('addToQuoteBtn').classList.add('btn-warning');
        document.getElementById('cancelEditBtn').classList.remove('d-none');
    }

    window.cancelEditMode = function() {
        document.getElementById('editing_item_id').value = '';
        document.getElementById('card1HeaderIcon').className = 'fas fa-plus-circle me-2 text-primary';
        document.getElementById('card1HeaderText').textContent = 'Add Quote Item';
        document.getElementById('addToQuoteBtnIcon').className = 'fas fa-plus';
        document.getElementById('addToQuoteBtnText').textContent = 'Add';
        document.getElementById('addToQuoteBtn').classList.remove('btn-warning');
        document.getElementById('addToQuoteBtn').classList.add('btn-primary');
        document.getElementById('cancelEditBtn').classList.add('d-none');
        document.querySelectorAll('.item-row').forEach(r => r.classList.remove('selected'));
        // Reset form to defaults
        const qtyIn = document.querySelector('#quoteItemForm input[name="qty"]');
        const widthIn = document.querySelector('#quoteItemForm input[name="width"]');
        const heightIn = document.querySelector('#quoteItemForm input[name="height"]');
        if (qtyIn) qtyIn.value = '1';
        if (widthIn) widthIn.value = '0';
        if (heightIn) heightIn.value = '0';
        if (document.getElementById('width_decimal')) document.getElementById('width_decimal').value = '0';
        if (document.getElementById('height_decimal')) document.getElementById('height_decimal').value = '0';
        if (priceSpan) priceSpan.textContent = '0.00';
        const noteField = document.querySelector('#quoteItemForm textarea[name="internal_note"]');
        if (noteField) noteField.value = '';
        // Clear shape selection
        if (typeof clearShapeSelection === 'function') clearShapeSelection();
    };

    function populateFormFromRow(row, setEditMode) {
        // Read item JSON early so we can get colors for the preview
        const jsonStr = row.getAttribute('data-item-json');
        let itemData = null;
        try { itemData = jsonStr ? JSON.parse(jsonStr) : null; } catch(e) {}

        // Update window preview with the item's saved color
        const td = row.querySelector('.item-description');
        if (td) {
            const type = td.dataset.seriesType;
            const w = td.dataset.width;
            const h = td.dataset.height;
            if (type) {
                const extColor = itemData?.color_exterior || 'WH';
                const extOpt = document.querySelector(`#lamExteriorSelect option[value="${extColor}"]`) || document.querySelector(`#baseWindowColor option[value="${extColor}"]`);
                const extHex = extOpt?.dataset?.hex || '';
                let url = `/installer/quotes/window-preview?type=${encodeURIComponent(type)}&width=${w}&height=${h}&maxSize=240&color=${encodeURIComponent(extColor)}`;
                if (extHex) url += `&hexColor=${encodeURIComponent(extHex)}`;
                fetch(url)
                    .then(r => r.text())
                    .then(html => {
                        const box = document.getElementById('window-svg-preview');
                        if (box) box.innerHTML = html;
                        // Re-apply shape overlay for items that have a shape
                        if (typeof applyShapeToPreview === 'function') {
                            setTimeout(function(){ applyShapeToPreview(); }, 50);
                        }
                    });
            }
        }

        // Populate the left form with this item's data
        if (!itemData) return;
        try {
            const item = itemData;

            // Enter edit mode
            if (setEditMode && item.id) {
                enterEditMode(item.id);
            }

            // Size
            const qtyInput = document.querySelector('#quoteItemForm input[name="qty"]');
            const widthInput = document.querySelector('#quoteItemForm input[name="width"]');
            const heightInput = document.querySelector('#quoteItemForm input[name="height"]');
            if (qtyInput) qtyInput.value = item.qty || 1;
            if (widthInput) widthInput.value = decToFraction(item.width) || item.width;
            if (heightInput) heightInput.value = decToFraction(item.height) || item.height;
            // Sync hidden decimal fields
            if (document.getElementById('width_decimal')) document.getElementById('width_decimal').value = item.width;
            if (document.getElementById('height_decimal')) document.getElementById('height_decimal').value = item.height;

            // Series & Config
            if (seriesSelect && item.series_id) {
                seriesSelect.value = item.series_id;
                currentSeries = item.series_id;
                if (seriesIdField) seriesIdField.value = item.series_id;
                // Fetch config options then set the right one
                fetch(`/installer/quotes/series-types/${item.series_id}`)
                    .then(r => r.json())
                    .then(types => {
                        seriesTypeSelect.innerHTML = '<option value="">Select configuration</option>';
                        types.forEach(t => {
                            const opt = document.createElement('option');
                            opt.value = t; opt.textContent = t;
                            if (t === item.series_type) opt.selected = true;
                            seriesTypeSelect.appendChild(opt);
                        });
                        currentType = item.series_type;
                        if (seriesTypeField) seriesTypeField.value = item.series_type;
                        const configSearch = document.getElementById('configSearchInput');
                        if (configSearch) configSearch.value = item.series_type || '';
                        // Recalc price now that currentType is set
                        calcPrice();
                        // Show/hide shape section based on whether this is a PW config
                        if (typeof checkShapeSectionVisibility === 'function') checkShapeSectionVisibility();
                    });
            }

            // Colors — restore using new color flow
            const setSelect = (name, val) => { const el = document.querySelector(`#quoteItemForm [name="${name}"]`); if (el && val !== undefined) el.value = val; };

            // Determine color state from saved item
            var extCode = item.color_exterior || 'WH';
            var intCode = item.color_interior || 'WH';
            var config = item.color_config || '';
            var parts = config.split('-');
            var extIsLam = parts[0] === 'LAM';
            var intIsLam = parts[1] === 'LAM';
            var isLam = extIsLam || intIsLam;

            // Set base color (the non-laminate side, or both if no laminate)
            var baseCode = extIsLam ? intCode : extCode;
            if (baseCode === 'LAM') baseCode = 'WH'; // fallback
            baseColorEl.value = baseCode;

            // Set laminated checkbox and side
            isLaminatedEl.checked = isLam;
            if (isLam) {
                if (extIsLam && intIsLam) laminateSideEl.value = 'both';
                else if (extIsLam) laminateSideEl.value = 'exterior';
                else laminateSideEl.value = 'interior';
            } else {
                laminateSideEl.value = '';
            }

            // Set laminate color selects
            if (extIsLam && lamExtSelect) lamExtSelect.value = extCode;
            if (intIsLam && lamIntSelect) lamIntSelect.value = intCode;

            // Set custom inputs
            if (extCustomInput) { extCustomInput.value = item.color_exterior_custom || ''; }
            if (intCustomInput) { intCustomInput.value = item.color_interior_custom || ''; }

            // Trigger the flow update
            updateColorFlow();

            // Repaint the SVG with the selected colors after a short delay
            setTimeout(function() {
                if (typeof repaintPreview === 'function') repaintPreview();
            }, 300);

            // Frame
            setSelect('frame_type', item.frame_type);
            // fin_type is now merged into frame_type — no separate restore needed

            // Glass — split glass_type into outside/middle/inside dropdowns
            var gtParts = (item.glass_type || 'LE3/CLR').split('/');
            var goEl = document.getElementById('glassOutside');
            var giEl = document.getElementById('glassInside');
            var gmEl = document.getElementById('glassMiddle');
            var gmWrap = document.getElementById('glassMiddleWrap');
            var threePaneEl = document.getElementById('hasThreePane');
            if (gtParts.length === 3) {
                if (goEl) goEl.value = gtParts[0] || 'LE3';
                if (gmEl) gmEl.value = gtParts[1] || '';
                if (giEl) giEl.value = gtParts[2] || 'CLR';
                if (threePaneEl) threePaneEl.checked = true;
                if (gmWrap) gmWrap.style.display = '';
            } else {
                if (goEl) goEl.value = gtParts[0] || 'LE3';
                if (giEl) giEl.value = gtParts[1] || 'CLR';
                if (gmEl) gmEl.value = '';
                if (threePaneEl) threePaneEl.checked = false;
                if (gmWrap) gmWrap.style.display = 'none';
            }
            setSelect('glass_type', item.glass_type);
            setSelect('tempered', item.tempered);
            // Trigger tempered change to update matrix
            document.getElementById('temperedOption')?.dispatchEvent(new Event('change'));

            // Grid
            setSelect('grid_pattern', item.grid_pattern);
            setSelect('grid_profile', item.grid_profile);
            // Restore grid detail
            var gdInput = document.getElementById('gridDetailInput');
            if (gdInput) { gdInput.value = item.grid_detail || ''; }
            // Toggle grid profile & detail visibility
            if (typeof toggleGridProfile === 'function') toggleGridProfile();

            // Checkboxes
            const setCheck = (name, val) => { const el = document.querySelector(`#quoteItemForm input[name="${name}"][type="checkbox"]`); if (el) el.checked = !!parseInt(val); };
            setCheck('knocked_down', item.knocked_down);
            setCheck('retrofit_bottom_only', item.retrofit_bottom_only);
            setCheck('block_frame_bottom', item.block_frame_bottom);
            setCheck('no_logo_lock', item.no_logo_lock);
            setCheck('double_lock', item.double_lock);
            setCheck('custom_lock_position', item.custom_lock_position);
            setCheck('custom_vent_latch', item.custom_vent_latch);
            updateFrameBottomOptions(item.frame_type || 'Retrofit 1 3/4"');

            // Notes
            const noteField = document.querySelector('#quoteItemForm textarea[name="internal_note"]');
            if (noteField) noteField.value = item.internal_note || '';

            // Shape — restore shape selection if present
            // The shape section visibility is handled by checkShapeSectionVisibility()
            // which runs after the config is set above. Here we just restore the data.
            if (item.shape_definition_id) {
                var shapeSection = document.getElementById('shapeSection');
                var shapeCb = document.getElementById('isShapedWindow');
                var shapeCtrls = document.getElementById('shapeControls');
                // Force-show the shape section (config fetch may still be in flight)
                if (shapeSection) shapeSection.style.display = '';
                if (shapeCb) shapeCb.checked = true;
                if (shapeCtrls) shapeCtrls.style.display = '';
                document.getElementById('shape_definition_id').value = item.shape_definition_id;
                document.getElementById('shape_code').value = item.shape_code || '';
                document.getElementById('shape_params').value = item.shape_params ? (typeof item.shape_params === 'string' ? item.shape_params : JSON.stringify(item.shape_params)) : '';
                var nameEl = document.getElementById('shapeNameDisplay');
                if (nameEl) nameEl.value = item.shape_code || ('Shape #' + item.shape_definition_id);

                // Restore dedicated H1/W1 dimension inputs
                var shCode = item.shape_code || '';
                updateShapeDimVisibility(shCode);
                var spJson = {};
                try { spJson = typeof item.shape_params === 'string' ? JSON.parse(item.shape_params || '{}') : (item.shape_params || {}); } catch(e) {}
                var dimH1 = document.getElementById('shapeDimH1');
                var dimW1 = document.getElementById('shapeDimW1');
                if (dimH1 && spJson.H1 !== undefined) dimH1.value = spJson.H1;
                if (dimW1 && spJson.W1 !== undefined) dimW1.value = spJson.W1;
            } else {
                // Clear any leftover shape data — section visibility is handled by checkShapeSectionVisibility
                document.getElementById('shape_definition_id').value = '';
                document.getElementById('shape_code').value = '';
                document.getElementById('shape_params').value = '';
                var nameEl2 = document.getElementById('shapeNameDisplay');
                if (nameEl2) nameEl2.value = '';
                var shapeCb2 = document.getElementById('isShapedWindow');
                if (shapeCb2) shapeCb2.checked = false;
                var shapeCtrls2 = document.getElementById('shapeControls');
                if (shapeCtrls2) shapeCtrls2.style.display = 'none';
            }

            // Restore panel dimensions if they exist
            if (item.panel_dimensions) {
                var pd = typeof item.panel_dimensions === 'string' ? JSON.parse(item.panel_dimensions) : item.panel_dimensions;
                document.getElementById('panel_dimensions_json').value = typeof pd === 'string' ? pd : JSON.stringify(pd);
                var chk = document.getElementById('customDimsCheck');
                if (chk) chk.checked = true;
                if (item.series_type) {
                    fetchPanelLayout(item.series_type);
                    setTimeout(function() { updateDimsSummary(); }, 400);
                }
            } else {
                document.getElementById('panel_dimensions_json').value = '';
                var chk2 = document.getElementById('customDimsCheck');
                if (chk2) chk2.checked = false;
            }

            // Show stored price immediately while async calcPrice resolves
            if (item.total && parseFloat(item.total) > 0) {
                if (priceSpan) priceSpan.textContent = parseFloat(item.total).toFixed(2);
                if (priceField) priceField.value = parseFloat(item.price || item.total).toFixed(2);
                if (totalField) totalField.value = parseFloat(item.total).toFixed(2);
            }
            calcPrice();

            // Trigger change events so Product Spec card updates
            setTimeout(() => {
                ['seriesSelect', 'seriesTypeSelect'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.dispatchEvent(new Event('change'));
                });
                ['width', 'height'].forEach(name => {
                    const el = document.querySelector(`#quoteItemForm input[name="${name}"]`);
                    if (el) el.dispatchEvent(new Event('input'));
                });
                ['glass_type', 'frame_type'].forEach(name => {
                    const el = document.querySelector(`#quoteItemForm [name="${name}"]`);
                    if (el) el.dispatchEvent(new Event('change'));
                });
                // Re-check shape visibility after all change events settle
                if (typeof checkShapeSectionVisibility === 'function') {
                    setTimeout(function(){ checkShapeSectionVisibility(); }, 600);
                }
            }, 500);

        } catch (err) {
            console.error('Error populating form from item:', err);
        }
    }

    attachQtyListeners();
    attachDeleteListeners();
    attachItemClickListeners();
    attachEditListeners();

    // ══════════════════════════════════════════════
    // Auto-populate Notes from checked options
    // ══════════════════════════════════════════════
    (function() {
        const optionCheckboxes = [
            { id: 'knocked_down',          label: 'Knocked Down' },
            { id: 'retrofit_bottom_only',  label: 'Retrofit Bottom Only' },
            { id: 'no_logo_lock',          label: 'No Logo Lock' },
            { id: 'double_lock',           label: '2 Locks (Each Sash)' },
            { id: 'custom_lock_position',  label: 'Lock Position Not Standard' },
            { id: 'custom_vent_latch',     label: 'Vent Latch Position Not Standard' },
        ];
        const internalNotesField = document.querySelector('#quoteItemForm textarea[name="internal_note"]');
        const headerNotesField = document.querySelector('#quoteHeaderForm textarea[name="notes"]');

        function syncToField(field) {
            if (!field) return;
            const autoLabels = optionCheckboxes.map(o => o.label);
            const existingLines = field.value.split('\n');
            const manualLines = existingLines.filter(line => {
                const trimmed = line.replace(/^• /, '').trim();
                return trimmed !== '' && !autoLabels.includes(trimmed);
            });
            const checkedLines = [];
            optionCheckboxes.forEach(opt => {
                const cb = document.getElementById(opt.id);
                if (cb && cb.checked) checkedLines.push('• ' + opt.label);
            });
            const parts = [...checkedLines, ...manualLines].filter(Boolean);
            field.value = parts.join('\n');
        }

        function syncOptionsToNotes() { syncToField(internalNotesField); syncToField(headerNotesField); }
        optionCheckboxes.forEach(opt => { const cb = document.getElementById(opt.id); if (cb) cb.addEventListener('change', syncOptionsToNotes); });
    })();

    async function appendDetailedPreviewItem(item, index) {
        const container = document.getElementById('detailed-line-items');
        const noItems = container.querySelector('.text-center.text-muted');
        if (noItems) noItems.remove();
        const _extOpt = item.color_exterior ? (document.querySelector(`#lamExteriorSelect option[value="${item.color_exterior}"]`) || document.querySelector(`#baseWindowColor option[value="${item.color_exterior}"]`)) : null;
        const diagramSVG = await generateSmallDiagramSVG(item.series_type, item.width, item.height, item.color_exterior || 'WH', _extOpt?.dataset?.hex || '');
        const gt = item.glass_type || 'CLR/CLR';
        const nfrc = getNfrc(gt);
        const pane = getPane(gt, item.specialty_glass);
        const gtLabel = glassLabel(gt, item.specialty_glass);
        
        const lineItem = document.createElement('div');
        lineItem.className = 'line-item';
        lineItem.setAttribute('data-item-id', item.id);
        lineItem.innerHTML = `
            <div class="line-header">
                <div class="line-number">Line ${index}</div>
                <div class="line-pricing price-field">
                    <div>List: $${parseFloat(item.price).toFixed(2)}</div>
                    <div>Unit(Disc): $${parseFloat(item.price).toFixed(2)}</div>
                    <div class="preview-item-qty">Qty: ${item.qty}</div>
                    <div class="price preview-item-total">Price: $${parseFloat(item.total).toFixed(2)}</div>
                </div>
            </div>
            <div class="window-diagram">
                ${diagramSVG || `<div style="width:120px;height:80px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;font-size:10px;">${decToFraction(item.width)}" x ${decToFraction(item.height)}"</div>`}
                <div style="text-align: center; margin-top: 3px; font-size: 9px; font-weight: bold;">Outside View</div>
            </div>
            <div style="overflow: hidden;">
                <div class="spec-row"><div class="spec-label">PRODUCT:</div><div class="spec-value">2101-VINYL DYNAMIC SLIDING WINDOW</div></div>
                <div class="spec-row"><div class="spec-label">UNIT:</div><div class="spec-value">${item.series_type || 'IM-XO'}, SIZE: W ${decToFraction(item.width)}" X H ${decToFraction(item.height)}"</div></div>
                <div class="spec-row"><div class="spec-label">MATERIAL:</div><div class="spec-value">MULTI-CHAMBER VINYL PROFILE</div></div>
                <div class="spec-row"><div class="spec-label">FRAME:</div><div class="spec-value">${(item.frame_type || 'RETROFIT 1 3/4"').toUpperCase()}</div></div>
                <div class="spec-row"><div class="spec-label">EXTERIOR/INTERIOR FINISH:</div><div class="spec-value">${((window._colorNameMap && item.color_exterior ? (window._colorNameMap[item.color_exterior] || item.color_exterior) : 'WHITE') + (item.color_exterior_custom ? ' (' + item.color_exterior_custom + ')' : '')).toUpperCase()} / ${((window._colorNameMap && item.color_interior ? (window._colorNameMap[item.color_interior] || item.color_interior) : 'WHITE') + (item.color_interior_custom ? ' (' + item.color_interior_custom + ')' : '')).toUpperCase()}</div></div>
                <div class="spec-row"><div class="spec-label">GRID TYPE:</div><div class="spec-value">${(item.grid && item.grid !== 'None' && item.grid !== 'N/A') ? (item.grid_profile || 'S-1') + ', ' + (item.grid_pattern || '') + (item.grid_detail ? ' (' + item.grid_detail + ')' : '') : 'N/A'}</div></div>
                ${(item.grid && item.grid !== 'None' && item.grid !== 'N/A') ? '<div class="customer-ref"><em>Cus. Ref: No charge on grids</em></div>' : ''}
                <div class="glass-details">
                    <div style="font-weight: bold; margin-bottom: 5px;">*****GLASS OPTIONS*****</div>
                    <div>GLASS TYPE: ${gtLabel}</div>
                    <div>FIELD 1: ${pane.f1}, IG THICK: 3/4"</div>
                    <div>FIELD 2: ${pane.f2}, IG THICK: 3/4"</div>
                    <div>GAS TYPE: ARGON FILLED, SPACER: ${(item.spacer || 'SUPERSPACER').toUpperCase()}</div>
                    ${item.internal_note ? '<div style="margin-top: 5px; font-size: 10px; color: #c00;"><strong>*****SPECIAL NOTES*****</strong><br>' + item.internal_note + '</div>' : ''}
                </div>
                <div style="margin-top: 8px; font-size: 10px; color: #555;">
                    <strong>*******NFRC********</strong><br>
                    UFACTOR: ${nfrc.u}, SHGC: ${nfrc.s}, VT: ${nfrc.v}, CR: ${nfrc.c}
                </div>
            </div>`;
        container.appendChild(lineItem);
        updateDetailedPreviewTotals();
    }

    function updateDetailedPreviewTotals() {
        let totalQty = 0, subtotal = 0;
        document.querySelectorAll('.line-item').forEach(item => {
            const qtyText = item.querySelector('.preview-item-qty')?.textContent || 'Qty: 0';
            const qty = parseInt(qtyText.replace('Qty: ', '')) || 0;
            const totalText = item.querySelector('.preview-item-total')?.textContent || 'Price: $0.00';
            const total = parseFloat(totalText.replace(/[^0-9.]/g, '')) || 0;
            totalQty += qty; subtotal += total;
        });
        const taxRate = 0.1075, tax = subtotal * taxRate, grandTotal = subtotal + tax;
        if (document.getElementById('preview-total-qty')) document.getElementById('preview-total-qty').textContent = totalQty;
        if (document.getElementById('preview-list-price')) document.getElementById('preview-list-price').textContent = '$' + subtotal.toFixed(2);
        if (document.getElementById('preview-sub-total')) document.getElementById('preview-sub-total').textContent = '$' + subtotal.toFixed(2);
        if (document.getElementById('preview-tax')) document.getElementById('preview-tax').textContent = '$' + tax.toFixed(2);
        if (document.getElementById('preview-grand-total')) document.getElementById('preview-grand-total').textContent = '$' + grandTotal.toFixed(2);
    }

    function recalcTotals() {
        let subtotal = 0;
        document.querySelectorAll('.item-total').forEach(el => { subtotal += parseFloat(el.textContent.replace('$','').replace(',','')) || 0; });
        const taxRate = 0.1075;
        const shipping = parseFloat(document.getElementById('shipping')?.value || 0);
        const discount = parseFloat(document.getElementById('discount-amount')?.textContent.replace(/[$,-]/g, '') || 0);
        const tax = subtotal * taxRate, total = subtotal - discount + shipping + tax;
        document.getElementById('subtotal-amount').textContent = '$' + subtotal.toFixed(2);
        document.getElementById('tax-amount').textContent = '$' + tax.toFixed(2);
        document.getElementById('total-amount').textContent = '$' + total.toFixed(2);
        updateDetailedPreviewTotals();
    }

    function updateDisplay() {
        calcPrice();
        const allAddons = {...frameAddons, ...glassAddons};
        const addonsDiv = document.getElementById('currentAddons');
        if (Object.keys(allAddons).length === 0) {
            addonsDiv.innerHTML = '<div class="text-center text-muted small">No addons selected</div>';
        } else {
            let html = '';
            for (const [type, price] of Object.entries(allAddons)) { html += `<div class="addon-item"><span>${type}:</span> <span class="badge-price">+$${price.toFixed(2)}</span></div>`; }
            addonsDiv.innerHTML = html;
        }
        document.getElementById('addon').value = JSON.stringify(allAddons);
    }

    document.getElementById('frame_type')?.addEventListener('change', function() {
        const selected = this.value, previous = this.dataset.previousValue || null;
        if (previous && frameAddons[previous]) delete frameAddons[previous];
        this.dataset.previousValue = selected;
        fetch('/installer/quotes/schema/price', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify({ dropdown_value: selected, series_type: currentType, series: currentSeries }) })
        .then(r => r.json()).then(d => { if (d.price > 0) frameAddons[selected] = d.price; updateDisplay(); });

        // Update frame bottom options based on selected frame type
        updateFrameBottomOptions(selected);
    });

    function updateFrameBottomOptions(selected) {
        var alt1Label = document.getElementById('frameAlt1Label');
        var alt2Label = document.getElementById('frameAlt2Label');
        var alt1Cb = document.getElementById('retrofit_bottom_only');
        var alt2Cb = document.getElementById('block_frame_bottom');
        if (!alt1Label || !alt2Label) return;
        if (alt1Cb) alt1Cb.checked = false;
        if (alt2Cb) alt2Cb.checked = false;

        if (selected && selected.indexOf('1 3/4') >= 0) {
            alt1Label.textContent = 'Retrofit 2 1/2" Frame Bottom';
            alt2Label.textContent = 'Block Frame Bottom';
        } else if (selected && selected.indexOf('2 1/2') >= 0) {
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
    updateFrameBottomOptions(document.getElementById('frame_type')?.value || 'Retrofit 1 3/4"');

    // ══════════════════════════════════════════════
    // GLASS FLOW: Outside + Middle (Three Pane) + Inside → glass_type hidden field
    // ══════════════════════════════════════════════
    (function(){
        var glassOutEl = document.getElementById('glassOutside');
        var glassInEl = document.getElementById('glassInside');
        var glassMiddleEl = document.getElementById('glassMiddle');
        var glassMiddleWrap = document.getElementById('glassMiddleWrap');
        var glassTypeEl = document.getElementById('glass_type');
        var threePaneEl = document.getElementById('hasThreePane');

        function updateGlassType() {
            if (glassOutEl && glassInEl && glassTypeEl) {
                if (threePaneEl && threePaneEl.checked && glassMiddleEl && glassMiddleEl.value) {
                    glassTypeEl.value = glassOutEl.value + '/' + glassMiddleEl.value + '/' + glassInEl.value;
                } else {
                    glassTypeEl.value = glassOutEl.value + '/' + glassInEl.value;
                }
                glassTypeEl.dispatchEvent(new Event('change'));
            }
        }

        function toggleThreePane() {
            if (!threePaneEl || !glassMiddleWrap) return;
            glassMiddleWrap.style.display = threePaneEl.checked ? '' : 'none';
            if (!threePaneEl.checked && glassMiddleEl) glassMiddleEl.value = '';
            updateGlassType();
        }

        if (glassOutEl) glassOutEl.addEventListener('change', updateGlassType);
        if (glassInEl) glassInEl.addEventListener('change', updateGlassType);
        if (glassMiddleEl) glassMiddleEl.addEventListener('change', updateGlassType);
        if (threePaneEl) threePaneEl.addEventListener('change', toggleThreePane);
        toggleThreePane();
    })();

    document.getElementById('glass_type')?.addEventListener('change', function() {
        const selected = this.value, previous = this.dataset.previousValue || null;
        if (previous && glassAddons[previous]) delete glassAddons[previous];
        this.dataset.previousValue = selected;
        fetch('/installer/quotes/schema/price', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify({ dropdown_value: selected, series_type: currentType, series: currentSeries }) })
        .then(r => r.json()).then(d => { if (d.price > 0) glassAddons[selected] = d.price; updateDisplay(); });
    });

    // ══════════════════════════════════════════════
    // NEW COLOR FLOW: Base Color → Laminated? → Side → Laminate Color
    // ══════════════════════════════════════════════
    var _seriesAvailableColors = @json($seriesAvailableColors ?? []);

    var baseColorEl     = document.getElementById('baseWindowColor');
    var isLaminatedEl   = document.getElementById('isLaminated');
    var laminateSideEl  = document.getElementById('laminateSide');
    var lamExtBlock     = document.getElementById('lamExtBlock');
    var lamIntBlock     = document.getElementById('lamIntBlock');
    var lamExtSelect    = document.getElementById('lamExteriorSelect');
    var lamIntSelect    = document.getElementById('lamInteriorSelect');
    var extCustomInput  = document.getElementById('colorExteriorCustom');
    var intCustomInput  = document.getElementById('colorInteriorCustom');
    var colorSummary    = document.getElementById('colorSummary');
    var extValueEl      = document.getElementById('colorExteriorValue');
    var intValueEl      = document.getElementById('colorInteriorValue');
    var configValueEl   = document.getElementById('colorConfigValue');

    function updateColorFlow() {
        var base = baseColorEl.value;
        var laminated = isLaminatedEl.checked;
        var side = laminateSideEl.value;

        // Show/hide laminate side selector
        laminateSideEl.style.display = laminated ? '' : 'none';
        if (!laminated) {
            laminateSideEl.value = '';
            side = '';
        }

        // Show/hide laminate color blocks
        var showExt = laminated && (side === 'exterior' || side === 'both');
        var showInt = laminated && (side === 'interior' || side === 'both');
        lamExtBlock.style.display = showExt ? '' : 'none';
        lamIntBlock.style.display = showInt ? '' : 'none';

        // Reset hidden laminate selects
        if (!showExt) { lamExtSelect.value = ''; extCustomInput.style.display = 'none'; extCustomInput.value = ''; }
        if (!showInt) { lamIntSelect.value = ''; intCustomInput.style.display = 'none'; intCustomInput.value = ''; }

        // Compute final exterior/interior values
        var extCode = showExt && lamExtSelect.value ? lamExtSelect.value : base;
        var intCode = showInt && lamIntSelect.value ? lamIntSelect.value : base;
        extValueEl.value = extCode;
        intValueEl.value = intCode;

        // Build color_config code
        var extPart = showExt && lamExtSelect.value ? 'LAM' : base;
        var intPart = showInt && lamIntSelect.value ? 'LAM' : base;
        configValueEl.value = extPart + '-' + intPart;

        // Summary text
        var extName = showExt && lamExtSelect.value ? (lamExtSelect.options[lamExtSelect.selectedIndex]?.text || lamExtSelect.value) : baseColorEl.options[baseColorEl.selectedIndex].text;
        var intName = showInt && lamIntSelect.value ? (lamIntSelect.options[lamIntSelect.selectedIndex]?.text || lamIntSelect.value) : baseColorEl.options[baseColorEl.selectedIndex].text;
        if (extCustomInput.value) extName += ' (' + extCustomInput.value + ')';
        if (intCustomInput.value) intName += ' (' + intCustomInput.value + ')';
        colorSummary.textContent = 'Ext: ' + extName + ' / Int: ' + intName;

        // Trigger preview update
        if (typeof repaintPreview === 'function') repaintPreview();
        if (typeof updateWindowPreview === 'function') updateWindowPreview();
    }

    function toggleLamCustom(selectEl, inputEl) {
        if (!selectEl || !inputEl) return;
        var opt = selectEl.options[selectEl.selectedIndex];
        var isOther = opt && opt.textContent.trim().toLowerCase() === 'other';
        inputEl.style.display = isOther ? '' : 'none';
        if (!isOther) inputEl.value = '';
    }

    // Populate base color dropdown based on selected series
    function filterBaseColorsBySeries(seriesId) {
        var colors = _seriesAvailableColors[seriesId];
        var currentVal = baseColorEl.value;
        // Clear existing options
        baseColorEl.innerHTML = '<option value="">Select base color...</option>';
        if (!colors || !colors.length) {
            // No colors defined for this series
            updateColorFlow();
            return;
        }
        var foundCurrent = false;
        colors.forEach(function(c) {
            var opt = document.createElement('option');
            opt.value = c.code;
            opt.textContent = c.name;
            if (c.code === currentVal) { opt.selected = true; foundCurrent = true; }
            baseColorEl.appendChild(opt);
        });
        // Auto-select first if previous selection not available
        if (!foundCurrent && colors.length > 0) {
            baseColorEl.value = colors[0].code;
        }
        updateColorFlow();
    }

    // Event listeners
    baseColorEl.addEventListener('change', updateColorFlow);
    isLaminatedEl.addEventListener('change', updateColorFlow);
    laminateSideEl.addEventListener('change', updateColorFlow);
    lamExtSelect.addEventListener('change', function() { toggleLamCustom(lamExtSelect, extCustomInput); updateColorFlow(); });
    lamIntSelect.addEventListener('change', function() { toggleLamCustom(lamIntSelect, intCustomInput); updateColorFlow(); });
    extCustomInput.addEventListener('input', updateColorFlow);
    intCustomInput.addEventListener('input', updateColorFlow);

    // Hook into series change to filter base colors, window types, and pane visibility
    var _origSeriesSelect = document.getElementById('seriesSelect');
    if (_origSeriesSelect) {
        _origSeriesSelect.addEventListener('change', function() {
            filterBaseColorsBySeries(this.value);
            if (typeof filterWindowTypesBySeries === 'function') filterWindowTypesBySeries(this.value);
            // Show/hide Three Pane checkbox based on pane management
            var threePaneWrap = document.getElementById('threePaneWrap');
            var threePaneCb = document.getElementById('hasThreePane');
            if (threePaneWrap && typeof _seriesPaneTypes !== 'undefined') {
                var allowed = _seriesPaneTypes[this.value] || [];
                if (allowed.indexOf('three_pane') !== -1) {
                    threePaneWrap.style.display = '';
                } else {
                    threePaneWrap.style.display = 'none';
                    if (threePaneCb) { threePaneCb.checked = false; threePaneCb.dispatchEvent(new Event('change')); }
                }
            }
        });
        filterBaseColorsBySeries(_origSeriesSelect.value); // run on load
        if (typeof filterWindowTypesBySeries === 'function') filterWindowTypesBySeries(_origSeriesSelect.value);
        // Also run pane visibility on initial load
        (function() {
            var threePaneWrap = document.getElementById('threePaneWrap');
            if (threePaneWrap && typeof _seriesPaneTypes !== 'undefined' && _origSeriesSelect.value) {
                var allowed = _seriesPaneTypes[_origSeriesSelect.value] || [];
                if (allowed.indexOf('three_pane') === -1) {
                    threePaneWrap.style.display = 'none';
                }
            }
        })();
    }

    // Initialize
    updateColorFlow();

    (function(){
        const sel = document.getElementById('temperedOption'), block = document.getElementById('temperedMatrix'), ghosts = document.getElementById('temperedGhosts');
        if (!sel || !block || !ghosts) return;
        const checks = block.querySelectorAll('.tempered-box');
        function clearGhosts(){ ghosts.innerHTML = ''; }
        function ensureGhosts(){ clearGhosts(); checks.forEach(cb => { const h = document.createElement('input'); h.type = 'hidden'; h.name = 'tempered_fields[]'; h.value = cb.value; ghosts.appendChild(h); }); }
        function lockAll(){ checks.forEach(cb => { cb.checked = true; cb.disabled = true; cb.classList.add('tempered-locked'); }); ensureGhosts(); }
        function unlockForSelect(){ clearGhosts(); checks.forEach(cb => { cb.disabled = false; cb.classList.remove('tempered-locked'); cb.checked = false; }); }
        function apply(val){ if (!val) { block.classList.add('d-none'); clearGhosts(); return; } block.classList.remove('d-none'); if (val === 'All') lockAll(); else unlockForSelect(); }
        apply(sel.value || '');
        sel.addEventListener('change', function(){ apply(this.value || ''); });
    })();

    function attachQtyListeners() { document.querySelectorAll('.qty-input').forEach(input => { input.removeEventListener('input', handleQtyChange); input.addEventListener('input', handleQtyChange); }); }

    function handleQtyChange(e) {
        const input = e.target, itemId = input.dataset.id, qty = parseInt(input.value) || 0, price = parseFloat(input.dataset.price) || 0;
        const quoteId = "{{ $quote->id ?? '' }}";
        const newTotal = (qty * price).toFixed(2);
        const rowTotal = document.querySelector(`.item-total[data-id="${itemId}"]`);
        if (rowTotal) rowTotal.textContent = '$' + newTotal;
        const previewItem = document.querySelector(`.line-item[data-item-id="${itemId}"]`);
        if (previewItem) {
            const qtyElem = previewItem.querySelector('.preview-item-qty');
            const totalElem = previewItem.querySelector('.preview-item-total');
            if (qtyElem) qtyElem.textContent = 'Qty: ' + qty;
            if (totalElem) totalElem.textContent = 'Price: $' + newTotal;
        }
        recalcTotals();
        fetch(`/installer/quotes/${quoteId}/items/${itemId}/qty`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify({ qty: qty }) })
        .then(r => r.json()).then(d => { if (d.success) console.log('Qty updated successfully'); }).catch(err => console.error('Error updating qty:', err));
    }
    
    function attachDeleteListeners() { document.querySelectorAll('.remove-row').forEach(btn => { btn.removeEventListener('click', handleDelete); btn.addEventListener('click', handleDelete); }); }
    
    function handleDelete(e) {
        e.preventDefault();
        if (!confirm('Are you sure you want to delete this item?')) return;
        const itemId = this.dataset.id, quoteId = "{{ $quote->id ?? '' }}";
        fetch(`/installer/quotes/${quoteId}/item/${itemId}`, { method: 'DELETE', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                const row = document.querySelector(`#quoteDetailsTable tbody tr[data-id="${itemId}"]`);
                if (row) row.remove();
                const previewItem = document.querySelector(`.line-item[data-item-id="${itemId}"]`);
                if (previewItem) previewItem.remove();
                const container = document.getElementById('detailed-line-items');
                if (container.children.length === 0) container.innerHTML = '<div class="text-center text-muted py-5"><i class="fas fa-inbox fa-3x mb-3"></i><p>No items added to this quote yet.</p></div>';
                recalcTotals();
            } else { alert('Failed to delete item'); }
        }).catch(err => { console.error('Error deleting item:', err); alert('Error deleting item'); });
    }
    
    // ══════════════════════════════════════════════════════════════
    // Panel Dimensions — sub-panel width/height inputs
    // ══════════════════════════════════════════════════════════════
    var _panelLayoutData = null; // cached layout from API

    function fetchPanelLayout(seriesType) {
        const header = document.getElementById('panel-dimensions-header');
        const checkWrap = document.getElementById('custom-dims-checkbox-wrap');

        if (!seriesType) {
            if (header) header.style.display = 'none';
            if (checkWrap) checkWrap.style.display = 'none';
            _panelLayoutData = null;
            return;
        }

        const W = parseFraction(widthInput?.value) || 0;
        const H = parseFraction(heightInput?.value) || 0;
        let url = `/installer/quotes/panel-layout?series_type=${encodeURIComponent(seriesType)}`;
        if (W > 0) url += `&width=${W}`;
        if (H > 0) url += `&height=${H}`;

        fetch(url)
            .then(r => r.json())
            .then(data => {
                _panelLayoutData = data;
                const totalPanels = (data.panels || []).length;
                if (totalPanels <= 1) {
                    if (header) header.style.display = 'none';
                    if (checkWrap) checkWrap.style.display = 'none';
                    document.getElementById('panel_dimensions_json').value = '';
                    _panelLayoutData = null;
                    return;
                }
                // Show header + checkbox
                if (header) header.style.display = 'block';
                if (checkWrap) checkWrap.style.display = 'block';
                updateDimsSummary();
            })
            .catch(err => {
                console.error('Panel layout error:', err);
                if (header) header.style.display = 'none';
                if (checkWrap) checkWrap.style.display = 'none';
            });
    }

    // Show a compact summary of current panel dims in the header
    function updateDimsSummary() {
        var el = document.getElementById('panel-dims-summary');
        if (!el || !_panelLayoutData) { if (el) el.textContent = ''; return; }
        var panels = _panelLayoutData.panels || [];
        var jsonVal = document.getElementById('panel_dimensions_json').value;
        if (jsonVal) {
            try {
                var saved = JSON.parse(jsonVal);
                if (Array.isArray(saved) && saved.length) {
                    el.innerHTML = saved.map(function(s) { return '<div>' + s.label + ': ' + toFraction(s.width) + '&Prime; &times; ' + toFraction(s.height) + '&Prime;</div>'; }).join('');
                    return;
                }
            } catch(e) {}
        }
        // Default: show equal split
        el.innerHTML = panels.map(function(p) {
            var w = p.default_width ? toFraction(p.default_width) + '&Prime;' : '—';
            var h = p.default_height ? toFraction(p.default_height) + '&Prime;' : '—';
            return '<div>' + p.label + ': ' + w + ' &times; ' + h + '</div>';
        }).join('');
    }

    // Toggle handler for Custom Dimensions checkbox
    window.onCustomDimsToggle = function(checked) {
        if (checked && _panelLayoutData) {
            openDimensionModal();
        }
    };

    function renderPanelInputs(data, savedDims) {
        const inputsDiv = document.getElementById('panel-dimensions-inputs');
        if (!inputsDiv) return;

        const rows = data.rows || ['main'];
        const panels = data.panels || [];
        const rowNames = { main: '{{ __("Main") }}', top: '{{ __("Top") }}', bottom: '{{ __("Bottom") }}' };

        // Build saved dims lookup by position
        var savedMap = {};
        if (savedDims && Array.isArray(savedDims)) {
            savedDims.forEach(function(sd) { savedMap[sd.position] = sd; });
        }

        var html = '';
        var hasMultipleRows = rows.length > 1;

        // For multi-row, show row heights first
        if (hasMultipleRows) {
            html += '<div class="mb-2 p-2" style="background:#f0f9ff;border-radius:4px;">';
            html += '<div class="small fw-semibold text-secondary mb-1">{{ __("Row Heights") }}</div>';
            html += '<div class="row g-1">';
            rows.forEach(function(row) {
                var defH = 0;
                var panelsInRow = panels.filter(function(p) { return p.row === row; });
                if (panelsInRow.length > 0 && panelsInRow[0].default_height) {
                    defH = panelsInRow[0].default_height;
                }
                // Check saved
                var savedH = '';
                if (savedDims) {
                    var sp = panelsInRow[0];
                    if (sp && savedMap[sp.position]) savedH = savedMap[sp.position].height || '';
                }
                html += '<div class="col">';
                html += '<label class="small">' + (rowNames[row] || row) + ' H</label>';
                html += '<input type="text" class="form-control form-control-sm fraction-input panel-row-h" data-row="' + row + '" value="' + (savedH || (defH ? toFraction(defH) : '')) + '" placeholder="' + (defH ? toFraction(defH) : '') + '">';
                html += '</div>';
            });
            html += '</div></div>';
        }

        // Panel widths grouped by row
        rows.forEach(function(row) {
            var panelsInRow = panels.filter(function(p) { return p.row === row; });
            if (panelsInRow.length <= 0) return;

            html += '<div class="mb-1 p-2" style="background:#f8fafc;border-radius:4px;">';
            if (hasMultipleRows) {
                html += '<div class="small fw-semibold text-secondary mb-1">' + (rowNames[row] || row) + ' {{ __("Panels") }}</div>';
            }
            html += '<div class="row g-1">';
            panelsInRow.forEach(function(panel) {
                var defW = panel.default_width || 0;
                var savedW = savedMap[panel.position] ? (savedMap[panel.position].width || '') : '';
                html += '<div class="col">';
                html += '<label class="small" title="Field ' + panel.position + '">' + panel.label + ' W</label>';
                html += '<input type="text" class="form-control form-control-sm fraction-input panel-dim-w" data-position="' + panel.position + '" data-row="' + panel.row + '" value="' + (savedW || (defW ? toFraction(defW) : '')) + '" placeholder="' + (defW ? toFraction(defW) : '') + '">';
                html += '</div>';
            });
            html += '</div></div>';
        });

        inputsDiv.innerHTML = html;

        // Attach change listeners
        inputsDiv.querySelectorAll('.panel-dim-w, .panel-row-h').forEach(function(inp) {
            inp.addEventListener('input', function() {
                collectPanelDimensions();
                updateWindowPreview();
            });
        });

        collectPanelDimensions();
    }

    function collectPanelDimensions() {
        if (!_panelLayoutData) return;
        var panels = _panelLayoutData.panels || [];
        var result = [];

        // Collect row heights
        var rowHeights = {};
        document.querySelectorAll('.panel-row-h').forEach(function(inp) {
            var val = parseFraction(inp.value);
            if (val > 0) rowHeights[inp.dataset.row] = val;
        });

        // Collect per-panel widths
        panels.forEach(function(panel) {
            var widthInput = document.querySelector('.panel-dim-w[data-position="' + panel.position + '"]');
            var w = widthInput ? parseFraction(widthInput.value) : (panel.default_width || 0);
            var h = rowHeights[panel.row] || panel.default_height || 0;

            result.push({
                position: panel.position,
                label: panel.label,
                row: panel.row,
                type: panel.type,
                width: w,
                height: h
            });
        });

        document.getElementById('panel_dimensions_json').value = JSON.stringify(result);
    }

    function recalcPanelDefaults() {
        if (!_panelLayoutData || !_panelLayoutData.panels || _panelLayoutData.panels.length <= 1) return;
        // Re-fetch with new width/height to get updated defaults
        fetchPanelLayout(currentType);
    }

    // Helper: convert decimal to fraction string for display
    function toFraction(dec) {
        if (!dec || dec <= 0) return '';
        var whole = Math.floor(dec);
        var frac = dec - whole;
        if (frac < 0.01) return '' + whole;
        // Common fractions
        var fracs = [[1,8],[1,4],[3,8],[1,2],[5,8],[3,4],[7,8],[1,16],[3,16],[5,16],[7,16],[9,16],[11,16],[13,16],[15,16],[1,3],[2,3]];
        var best = '', bestDiff = 999;
        fracs.forEach(function(f) {
            var val = f[0]/f[1];
            var diff = Math.abs(frac - val);
            if (diff < bestDiff) { bestDiff = diff; best = f[0]+'/'+f[1]; }
        });
        if (bestDiff > 0.02) return dec.toFixed(3);
        return whole > 0 ? whole + ' ' + best : best;
    }

    function updateWindowPreview() {
        const config = (currentType || '').toUpperCase();
        const W = parseFraction(widthInput?.value) || 48;
        const H = parseFraction(heightInput?.value) || 48;
        const box = document.getElementById('window-svg-preview');
        if (!box || !config) return;

        // Get the currently selected exterior color code
        const extSelect = document.querySelector('[name="color_exterior"]');
        const extColor = extSelect?.value || 'WH';
        const extHex = extSelect?.selectedOptions?.[0]?.dataset?.hex || '';

        let url = `/installer/quotes/window-preview?type=${encodeURIComponent(config)}&width=${W}&height=${H}&maxSize=240&color=${encodeURIComponent(extColor)}`;
        if (extHex) url += `&hexColor=${encodeURIComponent(extHex)}`;

        // Append panel dimensions for proportional rendering
        var panelDimsJson = document.getElementById('panel_dimensions_json')?.value;
        if (panelDimsJson) {
            try {
                var dims = JSON.parse(panelDimsJson);
                if (dims && dims.length > 1) {
                    // Group by row, send widths and heights
                    var mainWidths = dims.filter(function(d){return d.row==='main';}).map(function(d){return d.width;});
                    var topWidths = dims.filter(function(d){return d.row==='top';}).map(function(d){return d.width;});
                    var botWidths = dims.filter(function(d){return d.row==='bottom';}).map(function(d){return d.width;});
                    if (mainWidths.length) url += '&mainWidths=' + mainWidths.join(',');
                    if (topWidths.length) url += '&topWidths=' + topWidths.join(',');
                    if (botWidths.length) url += '&botWidths=' + botWidths.join(',');
                    // Pass panel labels for the configuration preview
                    var mainLabels = dims.filter(function(d){return d.row==='main';}).map(function(d){return d.label;});
                    var topLabels = dims.filter(function(d){return d.row==='top';}).map(function(d){return d.label;});
                    var botLabels = dims.filter(function(d){return d.row==='bottom';}).map(function(d){return d.label;});
                    if (mainLabels.length) url += '&mainLabels=' + encodeURIComponent(mainLabels.join(','));
                    if (topLabels.length) url += '&topLabels=' + encodeURIComponent(topLabels.join(','));
                    if (botLabels.length) url += '&botLabels=' + encodeURIComponent(botLabels.join(','));
                    // Row heights
                    var rowH = {};
                    dims.forEach(function(d){ if (!rowH[d.row]) rowH[d.row] = d.height; });
                    if (Object.keys(rowH).length > 1) {
                        url += '&rowHeights=' + encodeURIComponent(JSON.stringify(rowH));
                    }
                }
            } catch(e) {}
        }

        fetch(url)
            .then(r => r.text())
            .then(html => {
                box.innerHTML = html;
                // Re-apply shape overlay if a shape is selected
                if (typeof applyShapeToPreview === 'function') {
                    setTimeout(function(){ applyShapeToPreview(); }, 50);
                }
            })
            .catch(err => { console.error('Preview fetch error:', err); });
    }

    document.getElementById('saveDraftButton')?.addEventListener('click', function() {
        const quoteId = "{{ $quote->id ?? '' }}", btn = this, originalText = btn.innerHTML;
        btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        const data = { quote_id: quoteId, status: 'draft', discount: parseFloat(document.getElementById('discount-amount')?.textContent.replace(/[$,-]/g, '') || 0), shipping: parseFloat(document.getElementById('shipping')?.value || 0), subtotal: parseFloat(document.getElementById('subtotal-amount')?.textContent.replace(/[$,]/g, '') || 0), tax: parseFloat(document.getElementById('tax-amount')?.textContent.replace(/[$,]/g, '') || 0), total: parseFloat(document.getElementById('total-amount')?.textContent.replace(/[$,]/g, '') || 0) };
        fetch(`/installer/quotes/${quoteId}/save-draft`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify(data) })
        .then(r => r.json())
        .then(d => {
            if (d.success) { if (typeof toastr !== 'undefined') toastr.success('Quote saved as draft successfully!'); else alert('Quote saved as draft successfully!'); setTimeout(() => { window.location.href = "{{ route('installer.quotes.index') }}"; }, 1000); }
            else { alert('Failed to save draft: ' + (d.message || 'Unknown error')); btn.disabled = false; btn.innerHTML = originalText; }
        }).catch(err => { console.error('Error saving draft:', err); alert('Error saving draft'); btn.disabled = false; btn.innerHTML = originalText; });
    });

    // ══════════════════════════════════════════════
    // SUBMIT QUOTE: Show modal for contact method selection
    // ══════════════════════════════════════════════
    (function() {
        const sendViaEmail = document.getElementById('sendViaEmail');
        const sendViaPhone = document.getElementById('sendViaPhone');
        const submitEmailBlock = document.getElementById('submitEmailBlock');
        const submitPhoneBlock = document.getElementById('submitPhoneBlock');
        const submitPhoneCustom = document.getElementById('submitPhoneCustom');
        const submitPhoneCustomInput = document.getElementById('submitPhoneCustomInput');

        // Toggle email/phone blocks based on checkboxes
        sendViaEmail?.addEventListener('change', function() {
            submitEmailBlock.style.display = this.checked ? '' : 'none';
        });
        sendViaPhone?.addEventListener('change', function() {
            submitPhoneBlock.style.display = this.checked ? '' : 'none';
        });

        // Show custom phone input when "Other number" radio selected
        document.querySelectorAll('input[name="submitPhone"]').forEach(r => {
            r.addEventListener('change', function() {
                submitPhoneCustomInput.style.display = this.value === 'custom' ? '' : 'none';
            });
        });

        // Open the modal when Submit Quote button is clicked
        document.getElementById('submitQuoteButton')?.addEventListener('click', function() {
            // Refresh displayed contact info from the form/customer data
            const emailField = document.getElementById('customer_email');
            if (emailField && emailField.value) {
                document.getElementById('submitEmail').value = emailField.value;
            }
            const phoneField = document.getElementById('customer_phone');
            if (phoneField && phoneField.value) {
                document.getElementById('submitBillingPhoneDisplay').textContent = phoneField.value;
            }

            var submitModal = new bootstrap.Modal(document.getElementById('submitQuoteModal'));
            submitModal.show();
        });

        // Confirm submit from modal
        document.getElementById('confirmSubmitQuoteBtn')?.addEventListener('click', function() {
            const quoteId = "{{ $quote->id ?? '' }}";
            const btn = this, originalText = btn.innerHTML;
            btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';

            // Gather contact method selections
            const sendMethods = [];
            if (sendViaEmail?.checked) sendMethods.push('email');
            if (sendViaPhone?.checked) sendMethods.push('phone');

            let contactEmail = document.getElementById('submitEmail')?.value || '';
            let contactPhone = '';
            if (sendViaPhone?.checked) {
                const selectedRadio = document.querySelector('input[name="submitPhone"]:checked');
                if (selectedRadio) {
                    if (selectedRadio.value === 'billing') contactPhone = document.getElementById('submitBillingPhoneDisplay')?.textContent?.trim() || '';
                    else if (selectedRadio.value === 'delivery') contactPhone = document.getElementById('submitDeliveryPhoneDisplay')?.textContent?.trim() || '';
                    else if (selectedRadio.value === 'custom') contactPhone = document.getElementById('submitPhoneCustomInput')?.value || '';
                }
            }

            // Validate at least one method selected
            if (sendMethods.length === 0) {
                alert('Please select at least one contact method.');
                btn.disabled = false; btn.innerHTML = originalText;
                return;
            }
            if (sendMethods.includes('email') && !contactEmail) {
                alert('Please enter an email address.');
                btn.disabled = false; btn.innerHTML = originalText;
                return;
            }
            if (sendMethods.includes('phone') && !contactPhone) {
                alert('Please select or enter a phone number.');
                btn.disabled = false; btn.innerHTML = originalText;
                return;
            }

            const data = {
                quote_id: quoteId,
                status: 'Quote Submitted',
                send_via: sendMethods,
                contact_email: contactEmail,
                contact_phone: contactPhone,
                discount: parseFloat(document.getElementById('discount-amount')?.textContent.replace(/[$,-]/g, '') || 0),
                shipping: parseFloat(document.getElementById('shipping')?.value || 0),
                subtotal: parseFloat(document.getElementById('subtotal-amount')?.textContent.replace(/[$,]/g, '') || 0),
                tax: parseFloat(document.getElementById('tax-amount')?.textContent.replace(/[$,]/g, '') || 0),
                total: parseFloat(document.getElementById('total-amount')?.textContent.replace(/[$,]/g, '') || 0)
            };

            fetch(`/installer/quotes/${quoteId}/save-draft`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify(data) })
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    bootstrap.Modal.getInstance(document.getElementById('submitQuoteModal'))?.hide();
                    var msg = d.message || 'Quote submitted successfully!';
                    if (d.email_warning) { if (typeof toastr !== 'undefined') toastr.warning(d.email_warning); else alert('Warning: ' + d.email_warning); }
                    if (typeof toastr !== 'undefined') toastr.success(msg); else alert(msg);
                    setTimeout(() => { window.location.href = "{{ route('installer.quotes.index') }}"; }, 1500);
                }
                else { alert('Failed to submit quote: ' + (d.message || 'Unknown error')); btn.disabled = false; btn.innerHTML = originalText; }
            }).catch(err => { console.error('Error submitting quote:', err); alert('Error submitting quote'); btn.disabled = false; btn.innerHTML = originalText; });
        });
    })();

    attachQtyListeners();
    attachDeleteListeners();
    attachItemClickListeners();
    attachEditListeners();

    // ══════════════════════════════════════════════
    // AUTO-LOAD: If series is pre-selected (DYNAMIC), fetch configs and default to XO
    // ══════════════════════════════════════════════
    if (seriesSelect?.value && !seriesTypeSelect?.value) {
        currentSeries = seriesSelect.value;
        if (seriesIdField) seriesIdField.value = currentSeries;
        fetch(`/installer/quotes/series-types/${currentSeries}`)
            .then(r => r.json())
            .then(types => {
                seriesTypeSelect.innerHTML = '<option value="">Select configuration</option>';
                let defaultFound = false;
                types.forEach(t => {
                    const opt = document.createElement('option');
                    opt.value = t; opt.textContent = t;
                    // Default to XO (case-insensitive)
                    if (!defaultFound && t.toUpperCase().includes('XO')) {
                        opt.selected = true;
                        defaultFound = true;
                    }
                    seriesTypeSelect.appendChild(opt);
                });
                if (defaultFound) {
                    currentType = seriesTypeSelect.value;
                    if (seriesTypeField) seriesTypeField.value = currentType;
                    const configSearch = document.getElementById('configSearchInput');
                    if (configSearch) configSearch.value = currentType;
                    updateWindowPreview();
                    calcPrice();
                }
            });
    }

    // Print & Download handlers are in the inline script block above

    // ══════════════════════════════════════════════
    // CONFIGURATION LOOKUP MODAL
    // ══════════════════════════════════════════════
    (function() {
        const allConfigurations = @json($allConfigurations ?? []);
        const productAreas = @json($productAreas ?? []);
        const modal = document.getElementById('configLookupModal');
        const grid = document.getElementById('configLookupGrid');
        const sidebar = document.getElementById('configLookupSidebar');
        const applyBtn = document.getElementById('configLookupApplyBtn');
        const searchInput = document.getElementById('configLookupSearch');
        const categorySearch = document.getElementById('configLookupCategorySearch');
        if (!modal || !grid || !sidebar || !applyBtn) return;

        let selectedConfig = null;
        let currentConfigs = [];
        let activeArea = 'all';
        const svgCache = {};

        function getConfigsForSeries() {
            const seriesId = document.getElementById('seriesSelect')?.value;
            if (!seriesId || !allConfigurations[seriesId]) return [];
            return allConfigurations[seriesId];
        }

        // Fetch SVG preview from the window-diagram endpoint (same as config preview)
        async function loadSvgPreview(configName, container) {
            if (svgCache[configName]) {
                container.innerHTML = svgCache[configName];
                return;
            }
            try {
                const r = await fetch(`/installer/quotes/window-preview?type=${encodeURIComponent(configName)}&width=36&height=60&maxSize=90&noDimensions=1`);
                const svg = await r.text();
                if (svg && svg.includes('<svg')) {
                    svgCache[configName] = svg;
                    container.innerHTML = svg;
                } else {
                    container.innerHTML = '<i class="fas fa-window-maximize fa-2x text-muted"></i>';
                }
            } catch {
                container.innerHTML = '<i class="fas fa-window-maximize fa-2x text-muted"></i>';
            }
        }

        // Get the selected series name (e.g. "DYNAMIC", "VIP")
        function getSelectedSeriesName() {
            const sel = document.getElementById('seriesSelect');
            return (sel?.selectedOptions[0]?.textContent || '').trim().toUpperCase();
        }

        // Get product_categories for this area matching current series
        function getAreaCategories(area) {
            const seriesName = getSelectedSeriesName();
            return (area.series_categories && area.series_categories[seriesName]) || [];
        }

        function renderSidebar(configs) {
            const filter = (categorySearch?.value || '').toLowerCase();
            // Build sidebar from product areas filtered by selected series
            let html = `<button class="nav-link ${activeArea === 'all' ? 'active' : ''} text-start py-2 px-3 mb-1 rounded config-category-btn" data-area="all" type="button"><i class="fas fa-th-large me-1"></i> All (${configs.length})</button>`;
            productAreas.forEach(area => {
                const desc = area.description || area.product_area || '';
                if (filter && !desc.toLowerCase().includes(filter)) return;
                const cats = getAreaCategories(area);
                if (cats.length === 0) return;
                const count = configs.filter(c => c.product_category && cats.includes(c.product_category)).length;
                if (count === 0) return;
                const key = area.product_area || desc;
                html += `<button class="nav-link ${activeArea === key ? 'active' : ''} text-start py-2 px-3 mb-1 rounded config-category-btn" data-area="${key}" type="button"><i class="fas fa-window-maximize me-1"></i> ${desc} (${count})</button>`;
            });
            sidebar.innerHTML = html;
            sidebar.querySelectorAll('.config-category-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    activeArea = this.dataset.area;
                    sidebar.querySelectorAll('.config-category-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    renderGrid(configs);
                });
            });
        }

        function renderGrid(configs) {
            const search = (searchInput?.value || '').toLowerCase();
            let filtered = configs;

            // Filter by selected product area (using product_category)
            if (activeArea !== 'all') {
                const area = productAreas.find(a => (a.product_area || a.description) === activeArea);
                if (area) {
                    const cats = getAreaCategories(area);
                    filtered = filtered.filter(c => c.product_category && cats.includes(c.product_category));
                }
            }
            if (search) filtered = filtered.filter(c => (c.name || '').toLowerCase().includes(search));

            if (filtered.length === 0) {
                grid.innerHTML = '<div class="col-12 text-center text-muted py-5">No configurations found.</div>';
                return;
            }

            grid.innerHTML = filtered.map(c => {
                const isSelected = selectedConfig === c.name;
                return `
                    <div class="col-6 col-md-3">
                        <div class="card config-lookup-card ${isSelected ? 'border-primary shadow-sm' : ''}" data-config="${c.name}" style="cursor:pointer; transition: all 0.15s; overflow:visible;">
                            <div class="card-body p-2 text-center position-relative pt-3">
                                <div class="position-absolute" style="top:8px; right:8px; z-index:2;">
                                    <input class="form-check-input config-radio" type="radio" name="configLookupRadio" value="${c.name}" ${isSelected ? 'checked' : ''} style="cursor:pointer; width:16px; height:16px;">
                                </div>
                                <div class="config-svg-box" data-config-name="${c.name}" style="width:100%; height:100px; display:flex; align-items:center; justify-content:center; background:#fff; border:1px solid #eee; border-radius:4px;">
                                    <div class="spinner-border spinner-border-sm text-muted" role="status"><span class="visually-hidden">Loading...</span></div>
                                </div>
                                <div class="mt-1 small fw-semibold text-truncate" title="${c.name}">${c.name}</div>
                            </div>
                        </div>
                    </div>`;
            }).join('');

            // Load SVG previews for each card
            grid.querySelectorAll('.config-svg-box').forEach(box => {
                loadSvgPreview(box.dataset.configName, box);
            });

            grid.querySelectorAll('.config-lookup-card').forEach(card => {
                card.addEventListener('click', function() {
                    selectedConfig = this.dataset.config;
                    grid.querySelectorAll('.config-lookup-card').forEach(c => c.classList.remove('border-primary', 'shadow-sm'));
                    this.classList.add('border-primary', 'shadow-sm');
                    grid.querySelectorAll('.config-radio').forEach(r => r.checked = false);
                    this.querySelector('.config-radio').checked = true;
                    applyBtn.disabled = false;
                });
            });
        }

        modal.addEventListener('show.bs.modal', function() {
            selectedConfig = null;
            applyBtn.disabled = true;
            if (searchInput) searchInput.value = '';
            if (categorySearch) categorySearch.value = '';
            activeArea = 'all';
            currentConfigs = getConfigsForSeries();
            if (currentConfigs.length === 0) {
                sidebar.innerHTML = '<div class="text-muted small p-3">Select a series first.</div>';
                grid.innerHTML = '<div class="col-12 text-center text-muted py-5">Select a series first to load configurations.</div>';
                return;
            }
            renderSidebar(currentConfigs);
            renderGrid(currentConfigs);
        });

        searchInput?.addEventListener('input', function() { renderGrid(currentConfigs); });
        categorySearch?.addEventListener('input', function() { renderSidebar(currentConfigs); });

        applyBtn.addEventListener('click', function() {
            if (!selectedConfig) return;
            const hiddenSelect = document.getElementById('seriesTypeSelect');
            const configSearch = document.getElementById('configSearchInput');
            let found = false;
            hiddenSelect.querySelectorAll('option').forEach(opt => { if (opt.value === selectedConfig) found = true; });
            if (!found) {
                const opt = document.createElement('option');
                opt.value = selectedConfig; opt.textContent = selectedConfig;
                hiddenSelect.appendChild(opt);
            }
            hiddenSelect.value = selectedConfig;
            if (configSearch) configSearch.value = selectedConfig;
            hiddenSelect.dispatchEvent(new Event('change'));
            bootstrap.Modal.getInstance(modal).hide();
        });
    })();

    @endif

    // ══════════════════════════════════════════════
    // DISCOUNTS TAB
    // ══════════════════════════════════════════════
    (function() {
        function recalcDiscountsTable() {
            let totalDisc = 0, totalNet = 0;
            document.querySelectorAll('#discountsTable tbody tr').forEach(row => {
                const input = row.querySelector('.discount-override');
                if (!input) return;
                const basePrice = parseFloat(input.dataset.basePrice) || 0;
                const qty = parseInt(input.dataset.qty) || 1;
                const pct = Math.min(100, Math.max(0, parseFloat(input.value) || 0));
                const disc = (pct / 100) * basePrice;
                const net = (basePrice - disc) * qty;
                row.querySelector('.final-disc').textContent = '$' + disc.toFixed(2);
                row.querySelector('.net-total').textContent = '$' + net.toFixed(2);
                totalDisc += disc;
                totalNet += net;
            });
            document.getElementById('totalDiscountsSum').textContent = '-$' + totalDisc.toFixed(2);
            document.getElementById('totalNetSum').textContent = '$' + totalNet.toFixed(2);
        }

        document.querySelectorAll('.discount-override').forEach(input => {
            input.addEventListener('input', recalcDiscountsTable);
        });

        // Apply Discounts button
        document.getElementById('applyDiscountsBtn')?.addEventListener('click', function() {
            const btn = this;
            const discounts = [];
            document.querySelectorAll('.discount-override').forEach(input => {
                const basePrice = parseFloat(input.dataset.basePrice) || 0;
                const pct = Math.min(100, Math.max(0, parseFloat(input.value) || 0));
                const disc = (pct / 100) * basePrice;
                discounts.push({ item_id: input.dataset.itemId, discount: disc });
            });

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Applying...';

            fetch('{{ $quote ? route("installer.quotes.applyDiscounts", $quote->id) : "#" }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ discounts })
            })
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    // Update the discount display on the Items tab
                    document.getElementById('discount-amount').textContent = '-$' + parseFloat(d.total_discount).toFixed(2);
                    document.getElementById('discount').value = d.total_discount;
                    recalcTotals();
                    if (window.toastr) toastr.success(d.message);
                } else {
                    if (window.toastr) toastr.error(d.message || 'Failed to apply discounts');
                }
            })
            .catch(() => { if (window.toastr) toastr.error('Failed to apply discounts'); })
            .finally(() => { btn.disabled = false; btn.innerHTML = '<i class="fas fa-check me-1"></i> Apply Discounts'; });
        });

        // Reset to Tier button
        document.getElementById('resetDiscountsBtn')?.addEventListener('click', function() {
            document.querySelectorAll('.discount-override').forEach(input => {
                input.value = parseFloat(input.dataset.tierPct).toFixed(2);
            });
            recalcDiscountsTable();
        });

        // Initial calc
        recalcDiscountsTable();

        // Sync discounts table when items tab changes (new items added, qty changes, etc.)
        const discountsTab = document.getElementById('discounts-tab');
        if (discountsTab) {
            discountsTab.addEventListener('shown.bs.tab', function() {
                syncDiscountsFromItems();
                recalcDiscountsTable();
            });
        }

        function syncDiscountsFromItems() {
            const tbody = document.querySelector('#discountsTable tbody');
            const existingIds = new Set();
            // Gather current item rows from Items tab
            document.querySelectorAll('#quoteDetailsTable tbody .item-row').forEach(row => {
                const id = row.dataset.id;
                existingIds.add(id);
                let discRow = tbody.querySelector(`tr[data-item-id="${id}"]`);
                const itemJson = JSON.parse(row.dataset.itemJson || '{}');
                const desc = row.querySelector('.item-description')?.textContent?.trim() || '';
                const qty = parseInt(row.querySelector('.qty-input')?.value) || 1;
                const price = parseFloat(row.querySelector('.qty-input')?.dataset?.price) || 0;
                const tierPct = parseFloat(document.querySelector('.discount-override')?.dataset?.tierPct) || 0;
                const tierDisc = (tierPct / 100) * price;

                if (!discRow) {
                    // New item — add row
                    discRow = document.createElement('tr');
                    discRow.setAttribute('data-item-id', id);
                    discRow.innerHTML = `
                        <td>${desc}</td>
                        <td>${qty}</td>
                        <td>$${price.toFixed(2)}</td>
                        <td class="tier-disc">${tierPct.toFixed(2)}%</td>
                        <td><div class="input-group input-group-sm">
                            <input type="number" step="0.01" min="0" max="100" class="form-control form-control-sm discount-override"
                                   data-item-id="${id}" data-base-price="${price}" data-qty="${qty}" data-tier-pct="${tierPct}"
                                   value="${tierPct.toFixed(2)}">
                            <span class="input-group-text">%</span>
                        </div></td>
                        <td class="final-disc">$${tierDisc.toFixed(2)}</td>
                        <td class="net-total">$${((price - tierDisc) * qty).toFixed(2)}</td>`;
                    tbody.appendChild(discRow);
                    discRow.querySelector('.discount-override').addEventListener('input', recalcDiscountsTable);
                } else {
                    // Update qty and price
                    const input = discRow.querySelector('.discount-override');
                    input.dataset.qty = qty;
                    input.dataset.basePrice = price;
                    discRow.children[1].textContent = qty;
                    discRow.children[2].textContent = '$' + price.toFixed(2);
                }
            });
            // Remove rows for deleted items
            tbody.querySelectorAll('tr[data-item-id]').forEach(row => {
                if (!existingIds.has(row.dataset.itemId)) row.remove();
            });
        }
    })();

// ─── LIVE COLOR TINT: repaint the preview SVG when exterior/interior changes ───
(function(){
    const LAM_HEX = '#5aa9ee';

    function hexToRgb(h){
        if (!h) return null;
        h = h.replace('#','').trim();
        if (h.length === 3) h = h.split('').map(c=>c+c).join('');
        if (h.length !== 6) return null;
        return { r: parseInt(h.slice(0,2),16), g: parseInt(h.slice(2,4),16), b: parseInt(h.slice(4,6),16) };
    }
    function rgbToHex(r,g,b){ return '#' + [r,g,b].map(v => Math.max(0,Math.min(255,Math.round(v))).toString(16).padStart(2,'0')).join(''); }
    function shade(hex, amt){
        const rgb = hexToRgb(hex); if (!rgb) return hex;
        const t = amt < 0 ? 0 : 255, p = Math.abs(amt);
        return rgbToHex(rgb.r + (t-rgb.r)*p, rgb.g + (t-rgb.g)*p, rgb.b + (t-rgb.b)*p);
    }
    function gradientStops(hex){
        return {
            frame: [ shade(hex, 0.15), shade(hex, 0.05), shade(hex,-0.10), shade(hex,-0.22) ],
            sash:  [ shade(hex, 0.08), shade(hex,-0.02), shade(hex,-0.20) ]
        };
    }

    function selectedHex(dropdown){
        if (!dropdown) return null;
        const opt = dropdown.selectedOptions[0];
        if (!opt || !opt.value) return null;
        if (opt.dataset.hex) return opt.dataset.hex;
        if ((opt.value || '').toUpperCase() === 'LAM') return LAM_HEX;
        return null;
    }

    function repaintPreview(){
        // Get hex from the resolved color values
        var extCode = document.getElementById('colorExteriorValue')?.value || 'WH';
        var intCode = document.getElementById('colorInteriorValue')?.value || 'WH';
        // Try to find hex from laminate selects first, then base color
        var extOpt = document.querySelector('#lamExteriorSelect option[value="'+extCode+'"]') || document.querySelector('#baseWindowColor option[value="'+extCode+'"]');
        var intOpt = document.querySelector('#lamInteriorSelect option[value="'+intCode+'"]') || document.querySelector('#baseWindowColor option[value="'+intCode+'"]');
        var extHex = extOpt?.dataset?.hex || null;
        var intHex = intOpt?.dataset?.hex || null;
        // Map base colors to hex
        if (!extHex) extHex = {'WH':'#ffffff','AL':'#d2b48c','BK':'#222222'}[extCode] || '#ffffff';
        if (!intHex) intHex = {'WH':'#ffffff','AL':'#d2b48c','BK':'#222222'}[intCode] || '#ffffff';

        const view = document.querySelector('input[name="previewView"]:checked')?.value || 'exterior';
        const activeHex = (view === 'interior' ? intHex : extHex) || '#ffffff';
        const stops = gradientStops(activeHex);

        document.querySelectorAll('#window-svg-preview svg').forEach(svg => {
            svg.querySelectorAll('linearGradient').forEach(g => {
                const id = g.id || '';
                const ss = g.querySelectorAll('stop');
                if (!ss.length) return;
                if (id.endsWith('-vH') || id.endsWith('-vHR') || id.endsWith('-vV') || id.endsWith('-vVR')) {
                    stops.frame.forEach((c,i) => { if (ss[i]) ss[i].setAttribute('stop-color', c); });
                } else if (id.endsWith('-sH') || id.endsWith('-sV')) {
                    stops.sash.forEach((c,i) => { if (ss[i]) ss[i].setAttribute('stop-color', c); });
                }
            });
        });
    }

    document.querySelectorAll('input[name="previewView"]').forEach(r => r.addEventListener('change', repaintPreview));
    // initial paint
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', repaintPreview);
    else repaintPreview();
})();

});
</script>
@endpush