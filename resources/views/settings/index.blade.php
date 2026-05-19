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
                            <input type="text" name="company_address" class="form-control" value="{{ $settings['company_address'] }}">
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
            <p class="text-muted small mb-3">Manage service pricing. This section redirects to the full rates manager.</p>
            <a href="{{ route('admin.settings.rates') }}" class="btn btn-vip">
                <i class="bi bi-cash-stack me-1"></i> Open Service Rates Manager
            </a>
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
            <p class="text-muted small mb-3">Manage notification emails sent to customers. This opens the full template editor.</p>
            <a href="{{ route('admin.email-templates.index') }}" class="btn btn-vip">
                <i class="bi bi-envelope-paper me-1"></i> Open Email Templates Editor
            </a>
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
</script>
@endpush
