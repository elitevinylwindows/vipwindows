@extends('layouts.app')
@section('title', 'Settings')

@section('content')
<div class="container-fluid py-4 px-4">
    <h4 class="fw-bold mb-4"><i class="bi bi-gear me-2"></i>Settings</h4>

    <form method="POST" action="{{ route('admin.settings.update') }}">
        @csrf

        {{-- Company Info --}}
        <div class="card mb-4">
            <div class="card-header bg-white"><h6 class="mb-0"><i class="bi bi-building me-1"></i> Company Information</h6></div>
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

        {{-- Features --}}
        <div class="card mb-4">
            <div class="card-header bg-white"><h6 class="mb-0"><i class="bi bi-toggles me-1"></i> Features</h6></div>
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

        {{-- Email / SMTP --}}
        <div class="card mb-4">
            <div class="card-header bg-white"><h6 class="mb-0"><i class="bi bi-envelope me-1"></i> Email (Zoho SMTP)</h6></div>
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

        {{-- Video Conferencing --}}
        <div class="card mb-4">
            <div class="card-header bg-white"><h6 class="mb-0"><i class="bi bi-camera-video me-1"></i> Video Conferencing</h6></div>
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

        <button type="submit" class="btn btn-vip btn-lg"><i class="bi bi-check-circle me-1"></i> Save All Settings</button>
    </form>
</div>
@endsection
