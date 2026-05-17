@extends('layouts.installer')
@section('title', __('installer.my_profile'))

@push('styles')
<style>
    .profile-container { display: flex; height: calc(100vh - 56px); overflow: hidden; }

    /* ── Left Rail Menu ─────────────────────────── */
    .profile-rail {
        width: 240px; min-width: 240px;
        background: var(--vip-primary);
        color: #fff;
        display: flex; flex-direction: column;
        border-right: 1px solid rgba(255,255,255,.06);
    }
    .profile-rail-header {
        padding: 1.5rem 1.25rem 1rem;
        border-bottom: 1px solid rgba(255,255,255,.08);
    }
    .profile-rail-header h6 { font-size: .7rem; text-transform: uppercase; letter-spacing: 1.5px; color: rgba(255,255,255,.4); margin: 0; }
    .profile-rail-nav { padding: .75rem 0; flex: 1; }
    .profile-rail-nav a {
        display: flex; align-items: center; gap: .6rem;
        padding: .7rem 1.25rem;
        color: rgba(255,255,255,.65); text-decoration: none;
        font-size: .85rem; border-left: 3px solid transparent;
        transition: all .15s;
    }
    .profile-rail-nav a:hover { background: rgba(255,255,255,.05); color: #fff; }
    .profile-rail-nav a.active { background: rgba(201,168,76,.1); color: var(--vip-accent); border-left-color: var(--vip-accent); }
    .profile-rail-nav a i { font-size: 1rem; width: 18px; text-align: center; }

    /* ── Main Panel ────────────────────────────── */
    .profile-main { flex: 1; overflow-y: auto; background: var(--vip-light); }
    .profile-section { display: none; padding: 2rem; max-width: 800px; }
    .profile-section.active { display: block; }
    .profile-section .section-title { font-size: 1.1rem; font-weight: 700; margin-bottom: 1.5rem; }

    .form-card { background: #fff; border-radius: .5rem; padding: 1.5rem; box-shadow: 0 1px 4px rgba(0,0,0,.06); margin-bottom: 1.5rem; }
    .form-card h6 { font-size: .75rem; text-transform: uppercase; letter-spacing: .5px; color: rgba(0,0,0,.45); margin-bottom: 1rem; }

    .logo-preview { height: 80px; max-width: 250px; object-fit: contain; border: 1px solid #eee; border-radius: .5rem; padding: .5rem; }

    @media (max-width: 991.98px) {
        .profile-container { flex-direction: column; height: auto; }
        .profile-rail { width: 100%; min-width: 100%; flex-direction: row; overflow-x: auto; }
        .profile-rail-header { display: none; }
        .profile-rail-nav { display: flex; padding: .5rem; gap: .25rem; }
        .profile-rail-nav a { padding: .5rem .75rem; border-left: none; border-bottom: 2px solid transparent; white-space: nowrap; font-size: .8rem; }
        .profile-rail-nav a.active { border-bottom-color: var(--vip-accent); border-left-color: transparent; }
    }
</style>
@endpush

@section('content')
<div class="profile-container">
    {{-- Left Rail Menu --}}
    <div class="profile-rail">
        <div class="profile-rail-header">
            <h6>{{ __('installer.account') }}</h6>
        </div>
        <nav class="profile-rail-nav">
            <a href="#" class="profile-tab active" data-section="personal"><i class="bi bi-person"></i> {{ __('installer.my_profile') }}</a>
            <a href="#" class="profile-tab" data-section="company"><i class="bi bi-building"></i> {{ __('installer.company_info') }}</a>
            <a href="#" class="profile-tab" data-section="branding"><i class="bi bi-palette"></i> {{ __('installer.company_logo') }}</a>
            <a href="#" class="profile-tab" data-section="pricing"><i class="bi bi-currency-dollar"></i> {{ __('installer.pricing') }}</a>
            <a href="#" class="profile-tab" data-section="security"><i class="bi bi-shield-lock"></i> {{ __('installer.security') }}</a>
        </nav>
    </div>

    {{-- Main Panel --}}
    <div class="profile-main">
        {{-- Personal Info Section --}}
        <div class="profile-section active" id="section-personal">
            <div class="section-title"><i class="bi bi-person me-2"></i>{{ __('installer.my_profile') }}</div>
            <form method="POST" action="{{ route('installer.profile.update') }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="_section" value="personal">
                <div class="form-card">
                    <h6>{{ __('installer.personal_info') }}</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('installer.name') }}</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('installer.email') }}</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('installer.phone') }}</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('installer.address') }}</label>
                            <input type="text" name="address" class="form-control" value="{{ old('address', $user->address) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('installer.city') }}</label>
                            <input type="text" name="city" class="form-control" value="{{ old('city', $user->city) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('installer.state') }}</label>
                            <input type="text" name="state" class="form-control" value="{{ old('state', $user->state) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('installer.zip') }}</label>
                            <input type="text" name="zip" class="form-control" value="{{ old('zip', $user->zip) }}">
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-vip"><i class="bi bi-check-circle me-1"></i> {{ __('installer.update_profile') }}</button>
            </form>
        </div>

        {{-- Company Info Section --}}
        <div class="profile-section" id="section-company">
            <div class="section-title"><i class="bi bi-building me-2"></i>{{ __('installer.company_info') }}</div>
            <form method="POST" action="{{ route('installer.profile.update') }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="_section" value="company">
                <div class="form-card">
                    <h6>{{ __('installer.company_info') }}</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('installer.company_name') }}</label>
                            <input type="text" name="company_name" class="form-control" value="{{ old('company_name', $user->company_name) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('installer.company_phone') }}</label>
                            <input type="text" name="company_phone" class="form-control" value="{{ old('company_phone', $user->company_phone) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('installer.email') }}</label>
                            <input type="email" name="company_email" class="form-control" value="{{ old('company_email', $user->company_email) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Website</label>
                            <input type="text" name="company_website" class="form-control" value="{{ old('company_website', $user->company_website) }}" placeholder="https://...">
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-vip"><i class="bi bi-check-circle me-1"></i> {{ __('installer.save') }}</button>
            </form>
        </div>

        {{-- Branding Section --}}
        <div class="profile-section" id="section-branding">
            <div class="section-title"><i class="bi bi-palette me-2"></i>{{ __('installer.company_logo') }}</div>
            <div class="form-card">
                <h6>{{ __('installer.company_logo') }}</h6>
                @if($user->company_logo)
                    <div class="mb-3">
                        <label class="form-label text-muted small">Current Logo</label>
                        <div>
                            <img src="{{ asset('uploads/installer-logos/' . $user->company_logo) }}" alt="Company Logo" class="logo-preview">
                        </div>
                    </div>
                @endif
                <form method="POST" action="{{ route('installer.profile.uploadLogo') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row align-items-end g-3">
                        <div class="col-md-8">
                            <label class="form-label">Upload New Logo</label>
                            <input type="file" name="company_logo" class="form-control @error('company_logo') is-invalid @enderror" accept="image/*" required>
                            @error('company_logo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text">JPEG, PNG, GIF, SVG, WebP. Max 2MB.</div>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-outline-dark w-100"><i class="bi bi-upload me-1"></i> Upload</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="form-card">
                <h6>Branding Preview</h6>
                <p class="text-muted small">Your logo appears in the sidebar and on customer-facing documents (quotes, invoices). Recommended size: 300×100px or similar landscape ratio.</p>
            </div>
        </div>

        {{-- Pricing Section --}}
        <div class="profile-section" id="section-pricing">
            <div class="section-title"><i class="bi bi-currency-dollar me-2"></i>{{ __('installer.pricing') }}</div>
            <form method="POST" action="{{ route('installer.profile.update') }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="_section" value="pricing">
                <div class="form-card">
                    <h6>{{ __('installer.markup_percentage') }}</h6>
                    <p class="text-muted small mb-3">Set your markup on top of the admin base price. You can use a percentage, a flat amount, or both. The final price your customers see will be: <strong>Admin Price + (Admin Price &times; Markup %) + Flat Add-on</strong>.</p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('installer.markup_percentage') }} (%)</label>
                            <div class="input-group">
                                <input type="number" name="price_markup_pct" class="form-control @error('price_markup_pct') is-invalid @enderror"
                                       value="{{ old('price_markup_pct', $user->price_markup_pct ?? 0) }}" step="0.01" min="0" max="500">
                                <span class="input-group-text">%</span>
                                @error('price_markup_pct') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <small class="text-muted">e.g. 20 means +20% on admin price</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('installer.markup_flat') }} ($)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="price_markup_flat" class="form-control @error('price_markup_flat') is-invalid @enderror"
                                       value="{{ old('price_markup_flat', $user->price_markup_flat ?? 0) }}" step="0.01" min="0">
                                @error('price_markup_flat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <small class="text-muted">Fixed dollar amount added per window/item</small>
                        </div>
                    </div>
                </div>
                <div class="form-card">
                    <h6>Example</h6>
                    <p class="text-muted small mb-0" id="pricingExample">If admin price is $100, with <strong>{{ $user->price_markup_pct ?? 0 }}%</strong> markup and <strong>${{ number_format($user->price_markup_flat ?? 0, 2) }}</strong> flat add-on, your price = <strong>${{ number_format(100 * (1 + ($user->price_markup_pct ?? 0) / 100) + ($user->price_markup_flat ?? 0), 2) }}</strong></p>
                </div>
                <button type="submit" class="btn btn-vip"><i class="bi bi-check-circle me-1"></i> {{ __('installer.save') }}</button>
            </form>
        </div>

        {{-- Security Section --}}
        <div class="profile-section" id="section-security">
            <div class="section-title"><i class="bi bi-shield-lock me-2"></i>{{ __('installer.security') }}</div>
            <form method="POST" action="{{ route('installer.profile.update') }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="_section" value="security">
                <div class="form-card">
                    <h6>{{ __('installer.change_password') }}</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('installer.new_password') }}</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Min 8 characters">
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('installer.confirm_password') }}</label>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm new password">
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-vip"><i class="bi bi-check-circle me-1"></i> {{ __('installer.change_password') }}</button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.profile-tab');
    const sections = document.querySelectorAll('.profile-section');

    tabs.forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            const target = this.dataset.section;

            tabs.forEach(t => t.classList.remove('active'));
            sections.forEach(s => s.classList.remove('active'));

            this.classList.add('active');
            document.getElementById('section-' + target).classList.add('active');
        });
    });
});
</script>
@endpush
