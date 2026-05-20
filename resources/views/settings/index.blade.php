@extends('layouts.app')
@section('title', 'Settings')

@push('styles')
<style>
    .settings-layout { display: flex; gap: 0; min-height: calc(100vh - 130px); }
    .settings-rail {
        width: 240px; flex-shrink: 0;
        background: #fff; border-right: 1px solid rgba(0,0,0,.08);
        padding: 1rem 0;
    }
    .settings-rail .rail-label {
        font-size: .65rem; text-transform: uppercase; letter-spacing: 1.5px;
        color: rgba(0,0,0,.35); padding: .5rem 1.25rem .25rem; font-weight: 600;
    }
    .settings-rail a {
        display: flex; align-items: center; gap: .6rem;
        padding: .6rem 1.25rem; color: rgba(0,0,0,.6);
        text-decoration: none; font-size: .88rem;
        border-left: 3px solid transparent; transition: all .15s;
    }
    .settings-rail a:hover { background: rgba(0,0,0,.03); color: #111; }
    .settings-rail a.active {
        background: rgba(201,168,76,.08); color: var(--vip-accent);
        border-left-color: var(--vip-accent); font-weight: 600;
    }
    .settings-rail a i { font-size: 1rem; width: 20px; text-align: center; }
    .settings-content { flex: 1; padding: 1.5rem 2rem; overflow-y: auto; }
    .settings-section { display: none; }
    .settings-section.active { display: block; }
    @media (max-width: 767.98px) {
        .settings-layout { flex-direction: column; }
        .settings-rail { width: 100%; border-right: none; border-bottom: 1px solid rgba(0,0,0,.08); display: flex; overflow-x: auto; padding: .5rem; gap: .25rem; }
        .settings-rail .rail-label { display: none; }
        .settings-rail a { padding: .5rem .75rem; border-left: none; border-bottom: 2px solid transparent; white-space: nowrap; font-size: .8rem; }
        .settings-rail a.active { border-left: none; border-bottom-color: var(--vip-accent); }
        .settings-content { padding: 1rem; }
    }
</style>
@endpush

@section('content')
<div class="settings-layout">
    {{-- Left Rail --}}
    <div class="settings-rail">
        <div class="rail-label">General</div>
        <a href="#" class="settings-tab active" data-section="company">
            <i class="bi bi-building"></i> Company Info
        </a>
        <a href="#" class="settings-tab" data-section="features">
            <i class="bi bi-toggles"></i> Features
        </a>
        <a href="#" class="settings-tab" data-section="rates">
            <i class="bi bi-cash-stack"></i> Service Rates
        </a>

        <div class="rail-label mt-3">Integrations</div>
        <a href="#" class="settings-tab" data-section="email-smtp">
            <i class="bi bi-envelope"></i> Email / SMTP
        </a>
        <a href="#" class="settings-tab" data-section="video">
            <i class="bi bi-camera-video"></i> Video Conferencing
        </a>
        <a href="#" class="settings-tab" data-section="quickbooks">
            <i class="bi bi-receipt"></i> QuickBooks
        </a>

        <div class="rail-label mt-3">Communication</div>
        <a href="#" class="settings-tab" data-section="email-templates">
            <i class="bi bi-envelope-paper"></i> Email Templates
        </a>

        <div class="rail-label mt-3">Danger Zone</div>
        <a href="#" class="settings-tab" data-section="truncate-data">
            <i class="bi bi-trash3"></i> Truncate Data
        </a>
    </div>

    {{-- Main Content --}}
    <div class="settings-content">

        {{-- ═══════════ COMPANY INFO ═══════════ --}}
        <div class="settings-section active" id="section-company">
            <h5 class="fw-bold mb-3"><i class="bi bi-building me-2"></i>Company Information</h5>
            <form method="POST" action="{{ route('admin.settings.update') }}">
                @csrf
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Company Name</label>
                                <input type="text" name="company_name" class="form-control" value="{{ $settings['company_name'] }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="text" name="company_phone" class="form-control" value="{{ $settings['company_phone'] }}">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="company_email" class="form-control" value="{{ $settings['company_email'] }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Business Hours</label>
                                <input type="text" name="business_hours" class="form-control" value="{{ $settings['business_hours'] }}">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <input type="text" name="company_address" class="form-control" value="{{ $settings['company_address'] }}" data-address-autocomplete>
                        </div>
                    </div>
                </div>

                <h6 class="fw-semibold mb-3"><i class="bi bi-file-earmark-text me-1"></i> Estimate Settings</h6>
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Contractor License #</label>
                                <input type="text" name="license_number" class="form-control" value="{{ $settings['license_number'] }}" placeholder="e.g. Lic#1008123">
                                <div class="form-text">Shown on all estimates sent to customers.</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Sales Tax Rate (%)</label>
                                <input type="text" name="sales_tax_rate" class="form-control" value="{{ $settings['sales_tax_rate'] }}" placeholder="10.75">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Default Estimate Terms</label>
                                <input type="text" name="estimate_terms" class="form-control" value="{{ $settings['estimate_terms'] }}" placeholder="Due on receipt">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Visa/MC Fee (%)</label>
                                <input type="text" name="cc_fee_visa" class="form-control" value="{{ $settings['cc_fee_visa'] }}" placeholder="2">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Amex/Discover Fee (%)</label>
                                <input type="text" name="cc_fee_amex" class="form-control" value="{{ $settings['cc_fee_amex'] }}" placeholder="2.5">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Estimate Footer Text</label>
                            <textarea name="estimate_footer" class="form-control" rows="3">{{ $settings['estimate_footer'] }}</textarea>
                        </div>
                    </div>
                </div>

                <h6 class="fw-semibold mb-3"><i class="bi bi-geo-alt me-1"></i> Google Maps / Address Autocomplete</h6>
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="mb-2">
                            <label class="form-label">Google Maps API Key</label>
                            <input type="text" name="google_maps_key" class="form-control" value="{{ $settings['google_maps_key'] }}" placeholder="AIzaSy...">
                            <div class="form-text">Required for address autocomplete on all forms. Enable the <strong>Places API</strong> in your <a href="https://console.cloud.google.com/apis/library/places-backend.googleapis.com" target="_blank">Google Cloud Console</a>.</div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-vip"><i class="bi bi-check-circle me-1"></i> Save Company Settings</button>
            </form>
        </div>

        {{-- ═══════════ FEATURES ═══════════ --}}
        <div class="settings-section" id="section-features">
            <h5 class="fw-bold mb-3"><i class="bi bi-toggles me-2"></i>Features</h5>
            <form method="POST" action="{{ route('admin.settings.update') }}">
                @csrf
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="form-check mb-3">
                            <input type="hidden" name="booking_enabled" value="0">
                            <input type="checkbox" name="booking_enabled" value="1" class="form-check-input" {{ $settings['booking_enabled'] ? 'checked' : '' }}>
                            <label class="form-check-label">Enable Online Installation Booking</label>
                            <div class="form-text">Allow customers to book installation time slots from the website.</div>
                        </div>
                        <div class="form-check">
                            <input type="hidden" name="consultation_enabled" value="0">
                            <input type="checkbox" name="consultation_enabled" value="1" class="form-check-input" {{ $settings['consultation_enabled'] ? 'checked' : '' }}>
                            <label class="form-check-label">Enable Virtual Consultations</label>
                            <div class="form-text">Allow scheduling Zoom/Teams virtual consultations with customers.</div>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-vip"><i class="bi bi-check-circle me-1"></i> Save Features</button>
            </form>
        </div>

        {{-- ═══════════ SERVICE RATES ═══════════ --}}
        <div class="settings-section" id="section-rates">
            <h5 class="fw-bold mb-3"><i class="bi bi-cash-stack me-2"></i>Service Rates</h5>
            <p class="text-muted small mb-3">Hourly pay rates for Tech Measure, Service, and Repair. Installation is billed per unit (managed in Service Types).</p>

            <div class="card mb-4">
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:40px;"></th>
                                <th>Service</th>
                                <th class="text-end" style="width:160px;">Pay Rate ($/hr)</th>
                                <th class="text-center" style="width:80px;">Status</th>
                                <th style="width:80px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($hourlyServices as $svc)
                                <tr id="rateRow{{ $svc->id }}">
                                    <td><span style="width:10px; height:10px; border-radius:50%; background:{{ $svc->color ?? '#c9a84c' }}; display:inline-block;"></span></td>
                                    <td>
                                        <div class="fw-semibold">{{ $svc->name }}</div>
                                        @if($svc->description)
                                            <div class="text-muted small">{{ $svc->description }}</div>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="input-group input-group-sm" style="width:140px; margin-left:auto;">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control text-end rate-input" id="rate_{{ $svc->id }}"
                                                   value="{{ number_format($svc->installer_pay, 2, '.', '') }}" step="0.01" min="0">
                                            <span class="input-group-text">/hr</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @if($svc->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-dark" onclick="saveRate({{ $svc->id }})">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                            @if($hourlyServices->isEmpty())
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        No hourly services found. Add services in Service Types first.
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="alert alert-light border small">
                <i class="bi bi-info-circle me-1"></i>
                Installation rates are per-unit and managed through <a href="{{ route('admin.services.index') }}">Service Types</a> &rarr; Installation Types.
            </div>
        </div>

        {{-- ═══════════ EMAIL / SMTP ═══════════ --}}
        <div class="settings-section" id="section-email-smtp">
            <h5 class="fw-bold mb-3"><i class="bi bi-envelope me-2"></i>Email (Zoho SMTP)</h5>
            <form method="POST" action="{{ route('admin.settings.update') }}">
                @csrf
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="alert alert-info small mb-3">
                            <i class="bi bi-info-circle me-1"></i>
                            Configure your Zoho Mail SMTP to send emails from the application. Use your Zoho email credentials below.
                            Make sure SMTP access is enabled in your Zoho Mail settings.
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">From Name</label>
                                <input type="text" name="mail_from_name" class="form-control" value="{{ $settings['mail_from_name'] }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">From Email Address</label>
                                <input type="email" name="mail_from_address" class="form-control" value="{{ $settings['mail_from_address'] }}">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">SMTP Host</label>
                                <input type="text" name="smtp_host" class="form-control" value="{{ $settings['smtp_host'] }}" placeholder="smtp.zoho.com">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">SMTP Port</label>
                                <input type="text" name="smtp_port" class="form-control" value="{{ $settings['smtp_port'] }}" placeholder="465">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Encryption</label>
                                <select name="smtp_encryption" class="form-select">
                                    <option value="ssl" {{ $settings['smtp_encryption'] === 'ssl' ? 'selected' : '' }}>SSL</option>
                                    <option value="tls" {{ $settings['smtp_encryption'] === 'tls' ? 'selected' : '' }}>TLS</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">SMTP Username</label>
                                <input type="text" name="smtp_username" class="form-control" value="{{ $settings['smtp_username'] }}" placeholder="your@zohomail.com">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">SMTP Password</label>
                                <input type="password" name="smtp_password" class="form-control" value="{{ $settings['smtp_password'] }}" placeholder="App password or SMTP password">
                            </div>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-vip"><i class="bi bi-check-circle me-1"></i> Save Email Settings</button>
            </form>
        </div>

        {{-- ═══════════ VIDEO CONFERENCING ═══════════ --}}
        <div class="settings-section" id="section-video">
            <h5 class="fw-bold mb-3"><i class="bi bi-camera-video me-2"></i>Video Conferencing</h5>
            <form method="POST" action="{{ route('admin.settings.update') }}">
                @csrf
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="alert alert-info small mb-3">
                            <i class="bi bi-info-circle me-1"></i>
                            Optional: Configure API keys to auto-generate meeting links when scheduling consultations.
                            Without API keys, you can still manually paste Zoom/Teams links.
                        </div>
                        <h6 class="text-muted mb-2">Zoom</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Zoom API Key</label>
                                <input type="text" name="zoom_api_key" class="form-control" value="{{ $settings['zoom_api_key'] }}" placeholder="Your Zoom JWT API Key">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Zoom API Secret</label>
                                <input type="password" name="zoom_api_secret" class="form-control" value="{{ $settings['zoom_api_secret'] }}">
                            </div>
                        </div>
                        <hr>
                        <h6 class="text-muted mb-2">Microsoft Teams</h6>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Tenant ID</label>
                                <input type="text" name="teams_tenant_id" class="form-control" value="{{ $settings['teams_tenant_id'] }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Client ID</label>
                                <input type="text" name="teams_client_id" class="form-control" value="{{ $settings['teams_client_id'] }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Client Secret</label>
                                <input type="password" name="teams_client_secret" class="form-control" value="{{ $settings['teams_client_secret'] }}">
                            </div>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-vip"><i class="bi bi-check-circle me-1"></i> Save Video Settings</button>
            </form>
        </div>

        {{-- ═══════════ QUICKBOOKS DESKTOP ═══════════ --}}
        <div class="settings-section" id="section-quickbooks">
            <h5 class="fw-bold mb-3"><i class="bi bi-receipt me-2"></i>QuickBooks Desktop Integration</h5>
            <form method="POST" action="{{ route('admin.settings.update') }}">
                @csrf
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="alert alert-info small mb-3">
                            <i class="bi bi-info-circle me-1"></i>
                            Connect your QuickBooks Desktop to sync invoices, customers, and payments.
                            You'll need to configure the QuickBooks Web Connector with the credentials below.
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">QB Username</label>
                                <input type="text" name="qb_username" class="form-control" value="{{ $settings['qb_username'] ?? '' }}" placeholder="QuickBooks Web Connector username">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">QB Password</label>
                                <input type="password" name="qb_password" class="form-control" value="{{ $settings['qb_password'] ?? '' }}" placeholder="QuickBooks Web Connector password">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Company File Path</label>
                                <input type="text" name="qb_company_file" class="form-control" value="{{ $settings['qb_company_file'] ?? '' }}" placeholder="C:\Users\Public\Documents\Intuit\QuickBooks\Company.qbw">
                                <div class="form-text">Full path to your QuickBooks company file on the server.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Web Connector URL</label>
                                <input type="text" name="qb_wc_url" class="form-control" value="{{ $settings['qb_wc_url'] ?? url('/admin/quickbooks/wc') }}" readonly>
                                <div class="form-text">Point the QuickBooks Web Connector to this URL.</div>
                            </div>
                        </div>
                        <div class="form-check mb-3">
                            <input type="hidden" name="qb_sync_invoices" value="0">
                            <input type="checkbox" name="qb_sync_invoices" value="1" class="form-check-input" {{ ($settings['qb_sync_invoices'] ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label">Auto-sync invoices to QuickBooks</label>
                            <div class="form-text">Automatically create/update invoices in QuickBooks when invoices are created or modified.</div>
                        </div>
                        <div class="form-check mb-3">
                            <input type="hidden" name="qb_sync_customers" value="0">
                            <input type="checkbox" name="qb_sync_customers" value="1" class="form-check-input" {{ ($settings['qb_sync_customers'] ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label">Auto-sync customers to QuickBooks</label>
                            <div class="form-text">Automatically create customers in QuickBooks when new customers are added.</div>
                        </div>
                        <div class="form-check mb-3">
                            <input type="hidden" name="qb_sync_payments" value="0">
                            <input type="checkbox" name="qb_sync_payments" value="1" class="form-check-input" {{ ($settings['qb_sync_payments'] ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label">Auto-sync payments to QuickBooks</label>
                            <div class="form-text">Automatically record payments in QuickBooks when payments are received.</div>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-vip"><i class="bi bi-check-circle me-1"></i> Save QuickBooks Settings</button>
            </form>
        </div>

        {{-- ═══════════ EMAIL TEMPLATES ═══════════ --}}
        <div class="settings-section" id="section-email-templates">
            <h5 class="fw-bold mb-3"><i class="bi bi-envelope-paper me-2"></i>Email Templates</h5>
            <p class="text-muted small mb-3">Manage notification emails sent to customers for jobs. Use placeholders to personalize each message.</p>

            {{-- Placeholder reference --}}
            <div class="card mb-4">
                <div class="card-body py-3">
                    <h6 class="fw-semibold mb-2"><i class="bi bi-braces me-1"></i> Available Placeholders</h6>
                    <div class="row g-2">
                        @foreach($placeholders as $token => $desc)
                            <div class="col-md-4 col-sm-6">
                                <div class="d-flex align-items-start gap-2">
                                    <code class="text-nowrap" style="font-size:.8rem; background:#f0ede5; padding:2px 6px; border-radius:4px; color:#8b6914;">{{ $token }}</code>
                                    <span class="small text-muted">{{ $desc }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Template cards --}}
            @foreach($templates as $template)
                <div class="card mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                        <div>
                            <h6 class="mb-0 fw-semibold">
                                @switch($template->slug)
                                    @case('job-scheduled')
                                        <i class="bi bi-calendar-check text-info me-1"></i>
                                        @break
                                    @case('day-before-reminder')
                                        <i class="bi bi-bell text-warning me-1"></i>
                                        @break
                                    @case('follow-up')
                                        <i class="bi bi-chat-heart text-success me-1"></i>
                                        @break
                                    @case('payment-received')
                                        <i class="bi bi-credit-card text-primary me-1"></i>
                                        @break
                                    @default
                                        <i class="bi bi-envelope me-1"></i>
                                @endswitch
                                {{ $template->name }}
                            </h6>
                            <span class="small text-muted">Slug: {{ $template->slug }}</span>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="active{{ $template->id }}"
                                   form="templateForm{{ $template->id }}" name="is_active" value="1"
                                   {{ $template->is_active ? 'checked' : '' }}>
                            <label class="form-check-label small" for="active{{ $template->id }}">Active</label>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.email-templates.update', $template->id) }}" id="templateForm{{ $template->id }}">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Template Name</label>
                                <input type="text" name="name" class="form-control form-control-sm" value="{{ $template->name }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Subject Line</label>
                                <input type="text" name="subject" class="form-control form-control-sm" value="{{ $template->subject }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Body</label>
                                <textarea name="body" class="form-control" rows="6" style="font-size:.85rem;" required>{{ $template->body }}</textarea>
                                <div class="form-text">Use placeholders above. Line breaks are preserved in the email.</div>
                            </div>
                            <button type="submit" class="btn btn-sm btn-vip">
                                <i class="bi bi-check-circle me-1"></i> Save Template
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach

            @if($templates->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-envelope-paper fs-1 d-block mb-2"></i>
                    No email templates found. Run the SQL seed to create the default templates.
                </div>
            @endif
        </div>

        {{-- ═══════════ TRUNCATE DATA ═══════════ --}}
        <div class="settings-section" id="section-truncate-data">
            <h5 class="fw-bold mb-1"><i class="bi bi-trash3 me-2 text-danger"></i>Truncate Data</h5>
            <p class="text-muted small mb-4">Permanently delete records within a date range. This action <strong>cannot be undone</strong>.</p>

            <div class="card border-danger">
                <div class="card-body">
                    {{-- Date Range --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Start Date *</label>
                            <input type="date" id="truncateStartDate" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">End Date *</label>
                            <input type="date" id="truncateEndDate" class="form-control" required>
                        </div>
                    </div>

                    {{-- Category Checkboxes --}}
                    <label class="form-label fw-semibold mb-2">Select categories to truncate:</label>
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="form-check p-3 border rounded" style="background:#fafaf7;">
                                <input class="form-check-input truncate-cat" type="checkbox" value="tech_measures" id="trCatTM">
                                <label class="form-check-label fw-semibold" for="trCatTM">
                                    <i class="bi bi-rulers me-1"></i> Tech Measures
                                </label>
                                <div class="text-muted" style="font-size:.72rem;">Tech measures, items, photos &amp; linked calendar events</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check p-3 border rounded" style="background:#fafaf7;">
                                <input class="form-check-input truncate-cat" type="checkbox" value="installations" id="trCatInst">
                                <label class="form-check-label fw-semibold" for="trCatInst">
                                    <i class="bi bi-tools me-1"></i> Installations
                                </label>
                                <div class="text-muted" style="font-size:.72rem;">Jobs, job items, time logs, notes &amp; invoices</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check p-3 border rounded" style="background:#fafaf7;">
                                <input class="form-check-input truncate-cat" type="checkbox" value="services" id="trCatSvc">
                                <label class="form-check-label fw-semibold" for="trCatSvc">
                                    <i class="bi bi-gear me-1"></i> Service Jobs
                                </label>
                                <div class="text-muted" style="font-size:.72rem;">Service calendar events &amp; related records</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check p-3 border rounded" style="background:#fafaf7;">
                                <input class="form-check-input truncate-cat" type="checkbox" value="repairs" id="trCatRep">
                                <label class="form-check-label fw-semibold" for="trCatRep">
                                    <i class="bi bi-wrench me-1"></i> Repairs
                                </label>
                                <div class="text-muted" style="font-size:.72rem;">Repair calendar events &amp; related records</div>
                            </div>
                        </div>
                    </div>

                    {{-- Preview --}}
                    <div id="truncatePreview" class="d-none mb-4">
                        <div class="alert alert-warning mb-0">
                            <h6 class="alert-heading mb-2"><i class="bi bi-exclamation-triangle me-1"></i> Records to be deleted:</h6>
                            <div id="truncatePreviewBody" class="small"></div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-warning" onclick="previewTruncate()">
                            <i class="bi bi-eye me-1"></i> Preview
                        </button>
                        <button type="button" class="btn btn-danger" id="truncateBtn" onclick="executeTruncate()" disabled>
                            <i class="bi bi-trash3 me-1"></i> Delete Records
                        </button>
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
    const tabs = document.querySelectorAll('.settings-tab');
    const sections = document.querySelectorAll('.settings-section');

    // Check URL hash for initial tab
    const hash = window.location.hash.replace('#', '');
    if (hash) {
        const targetTab = document.querySelector(`.settings-tab[data-section="${hash}"]`);
        if (targetTab) {
            tabs.forEach(t => t.classList.remove('active'));
            sections.forEach(s => s.classList.remove('active'));
            targetTab.classList.add('active');
            document.getElementById('section-' + hash)?.classList.add('active');
        }
    }

    tabs.forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            const section = this.dataset.section;

            tabs.forEach(t => t.classList.remove('active'));
            sections.forEach(s => s.classList.remove('active'));

            this.classList.add('active');
            document.getElementById('section-' + section)?.classList.add('active');

            // Update URL hash without scrolling
            history.replaceState(null, null, '#' + section);
        });
    });
});

// Save hourly rate via AJAX
function saveRate(serviceId) {
    const input = document.getElementById('rate_' + serviceId);
    const rate = parseFloat(input.value);
    if (isNaN(rate) || rate < 0) { alert('Please enter a valid rate.'); return; }

    const csrf = document.querySelector('meta[name=csrf-token]').content;
    const btn = input.closest('tr').querySelector('button');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

    fetch(`/admin/settings/rate/${serviceId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ installer_pay: rate }),
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        if (data.success) {
            btn.innerHTML = '<i class="bi bi-check-lg text-success"></i>';
            setTimeout(() => { btn.innerHTML = '<i class="bi bi-check-lg"></i>'; }, 1500);
        } else {
            btn.innerHTML = '<i class="bi bi-x-lg text-danger"></i>';
            alert(data.error || 'Failed to save.');
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-x-lg text-danger"></i>';
        alert('Network error.');
    });
}

// ── Truncate Data ──
function getTruncatePayload() {
    const startDate = document.getElementById('truncateStartDate').value;
    const endDate = document.getElementById('truncateEndDate').value;
    const categories = [...document.querySelectorAll('.truncate-cat:checked')].map(c => c.value);
    return { start_date: startDate, end_date: endDate, categories };
}

function previewTruncate() {
    const { start_date, end_date, categories } = getTruncatePayload();
    if (!start_date || !end_date) { alert('Please select both start and end dates.'); return; }
    if (categories.length === 0) { alert('Please select at least one category.'); return; }
    if (start_date > end_date) { alert('Start date must be before end date.'); return; }

    const csrf = document.querySelector('meta[name=csrf-token]').content;
    fetch('/admin/settings/truncate-preview', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        body: JSON.stringify({ start_date, end_date, categories }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.error) { alert(data.error); return; }
        const preview = document.getElementById('truncatePreview');
        const body = document.getElementById('truncatePreviewBody');
        let html = '<ul class="mb-0">';
        let totalCount = 0;
        data.counts.forEach(c => {
            html += `<li><strong>${c.label}:</strong> ${c.count} record${c.count !== 1 ? 's' : ''}</li>`;
            totalCount += c.count;
        });
        html += '</ul>';
        html += `<div class="mt-2 fw-bold">Total: ${totalCount} records</div>`;
        body.innerHTML = html;
        preview.classList.remove('d-none');
        document.getElementById('truncateBtn').disabled = totalCount === 0;
    })
    .catch(() => alert('Failed to preview. Please try again.'));
}

function executeTruncate() {
    const { start_date, end_date, categories } = getTruncatePayload();
    if (!start_date || !end_date || categories.length === 0) return;

    const confirmText = `Are you sure you want to permanently delete all selected records from ${start_date} to ${end_date}?\n\nThis CANNOT be undone.`;
    if (!confirm(confirmText)) return;

    const doubleConfirm = prompt('Type DELETE to confirm:');
    if (doubleConfirm !== 'DELETE') { alert('Truncation cancelled.'); return; }

    const csrf = document.querySelector('meta[name=csrf-token]').content;
    const btn = document.getElementById('truncateBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Deleting...';

    fetch('/admin/settings/truncate', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        body: JSON.stringify({ start_date, end_date, categories }),
    })
    .then(r => r.json())
    .then(data => {
        btn.innerHTML = '<i class="bi bi-trash3 me-1"></i> Delete Records';
        btn.disabled = false;
        if (data.error) { alert(data.error); return; }
        document.getElementById('truncatePreview').classList.add('d-none');
        alert(data.message || 'Records deleted successfully.');
    })
    .catch(() => {
        btn.innerHTML = '<i class="bi bi-trash3 me-1"></i> Delete Records';
        btn.disabled = false;
        alert('Failed to delete. Please try again.');
    });
}
</script>
@endpush
