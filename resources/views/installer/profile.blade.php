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
            {{-- Company info, services, booking link, pricing hidden for now
            <a href="#" class="profile-tab" data-section="company"><i class="bi bi-building"></i> {{ __('installer.company_info') }}</a>
            <a href="#" class="profile-tab" data-section="services"><i class="bi bi-tools"></i> My Services</a>
            <a href="#" class="profile-tab" data-section="booking-link"><i class="bi bi-link-45deg"></i> Booking Link</a>
            <a href="#" class="profile-tab" data-section="pricing"><i class="bi bi-currency-dollar"></i> {{ __('installer.pricing') }}</a>
            --}}
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

        {{-- Company Info, Branding, Services, Booking Link, Pricing — commented out for now
        <div class="profile-section" id="section-company">
            <div class="section-title"><i class="bi bi-building me-2"></i>Company Info</div>
        </div>
        <div class="profile-section" id="section-services">
            <div class="section-title"><i class="bi bi-tools me-2"></i>My Services</div>
        </div>
        <div class="profile-section" id="section-booking-link">
            <div class="section-title"><i class="bi bi-link-45deg me-2"></i>Booking Link</div>
        </div>
        <div class="profile-section" id="section-pricing">
            <div class="section-title"><i class="bi bi-currency-dollar me-2"></i>Pricing</div>
        </div>
        --}}

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

{{-- Service Modal — commented out for now, services moved to dedicated page
--}}

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
