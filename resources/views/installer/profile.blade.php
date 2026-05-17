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
            <a href="#" class="profile-tab" data-section="services"><i class="bi bi-tools"></i> My Services</a>
            <a href="#" class="profile-tab" data-section="booking-link"><i class="bi bi-link-45deg"></i> Booking Link</a>
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
                            <label class="form-label">Fax</label>
                            <input type="text" name="company_fax" class="form-control" value="{{ old('company_fax', $user->company_fax) }}" placeholder="Optional">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('installer.email') }}</label>
                            <input type="email" name="company_email" class="form-control" value="{{ old('company_email', $user->company_email) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Website</label>
                            <input type="text" name="company_website" class="form-control" value="{{ old('company_website', $user->company_website) }}" placeholder="https://...">
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('installer.company_address') }}</label>
                            <input type="text" name="company_address" class="form-control" value="{{ old('company_address', $user->company_address) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('installer.city') }}</label>
                            <input type="text" name="company_city" class="form-control" value="{{ old('company_city', $user->company_city) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('installer.state') }}</label>
                            <input type="text" name="company_state" class="form-control" value="{{ old('company_state', $user->company_state) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('installer.zip') }}</label>
                            <input type="text" name="company_zip" class="form-control" value="{{ old('company_zip', $user->company_zip) }}">
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-vip"><i class="bi bi-check-circle me-1"></i> {{ __('installer.save') }}</button>
            </form>
        </div>

        {{-- Branding Section --}}
        <div class="profile-section" id="section-branding">
            <div class="section-title"><i class="bi bi-palette me-2"></i>{{ __('installer.company_logo') }}</div>

            {{-- Light Logo (for dark sidebar) --}}
            <div class="form-card">
                <h6><i class="bi bi-moon-stars me-1"></i> Light Logo <span class="fw-normal text-muted">— for sidebar (dark background)</span></h6>
                <p class="text-muted small mb-3">Upload a white or light-colored version of your logo. This will appear in the sidebar navigation panel.</p>
                @if($user->company_logo_light)
                    <div class="mb-3">
                        <label class="form-label text-muted small">Current Light Logo</label>
                        <div style="background: #111; border-radius: .5rem; padding: .75rem; display: inline-block;">
                            <img src="{{ asset('uploads/installer-logos/' . $user->company_logo_light) }}" alt="Light Logo" class="logo-preview" style="border: none;">
                        </div>
                    </div>
                @endif
                <form method="POST" action="{{ route('installer.profile.uploadLogo') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="logo_type" value="light">
                    <div class="row align-items-end g-3">
                        <div class="col-md-8">
                            <label class="form-label">Upload Light Logo</label>
                            <input type="file" name="company_logo" class="form-control @error('company_logo') is-invalid @enderror" accept="image/*" required>
                            @error('company_logo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text">White/light logo for dark backgrounds. JPEG, PNG, SVG, WebP. Max 2MB.</div>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-outline-dark w-100"><i class="bi bi-upload me-1"></i> Upload</button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Dark Logo (for quotes/invoices) --}}
            <div class="form-card">
                <h6><i class="bi bi-sun me-1"></i> Dark Logo <span class="fw-normal text-muted">— for quotes & invoices (white background)</span></h6>
                <p class="text-muted small mb-3">Upload a dark-colored version of your logo. This will appear on customer-facing documents like quotes, invoices, and PDFs.</p>
                @if($user->company_logo_dark)
                    <div class="mb-3">
                        <label class="form-label text-muted small">Current Dark Logo</label>
                        <div>
                            <img src="{{ asset('uploads/installer-logos/' . $user->company_logo_dark) }}" alt="Dark Logo" class="logo-preview">
                        </div>
                    </div>
                @endif
                <form method="POST" action="{{ route('installer.profile.uploadLogo') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="logo_type" value="dark">
                    <div class="row align-items-end g-3">
                        <div class="col-md-8">
                            <label class="form-label">Upload Dark Logo</label>
                            <input type="file" name="company_logo" class="form-control @error('company_logo') is-invalid @enderror" accept="image/*" required>
                            @error('company_logo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text">Dark/colored logo for white backgrounds. JPEG, PNG, SVG, WebP. Max 2MB.</div>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-outline-dark w-100"><i class="bi bi-upload me-1"></i> Upload</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="form-card">
                <h6>Recommended</h6>
                <p class="text-muted small mb-0">300×100px or similar landscape ratio. The light logo goes in your sidebar, and the dark logo goes on quotes, invoices, and other documents your customers will see.</p>
            </div>
        </div>

        {{-- Services Section --}}
        <div class="profile-section" id="section-services">
            <div class="section-title"><i class="bi bi-tools me-2"></i>My Services</div>
            <p class="text-muted small mb-3">Define the services you offer. These will appear on your public booking page for customers to choose from.</p>

            <div class="form-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Services</h6>
                    <button class="btn btn-sm btn-vip" onclick="openServiceModal()"><i class="bi bi-plus me-1"></i> Add Service</button>
                </div>
                <div id="servicesListContainer">
                    <div class="text-center py-3 text-muted small"><i class="bi bi-hourglass-split me-1"></i> Loading services...</div>
                </div>
            </div>
        </div>

        {{-- Booking Link Section --}}
        <div class="profile-section" id="section-booking-link">
            <div class="section-title"><i class="bi bi-link-45deg me-2"></i>Public Booking Link</div>
            <div class="form-card">
                <h6>Your Shareable Booking Page</h6>
                <p class="text-muted small mb-3">Share this link with your customers so they can book installations based on your availability and services.</p>

                @if($user->booking_slug)
                    <div class="input-group mb-2">
                        <span class="input-group-text"><i class="bi bi-link-45deg"></i></span>
                        <input type="text" class="form-control" id="bookingLinkInput" readonly
                               value="{{ url('/book/installer/' . $user->booking_slug) }}">
                        <button class="btn btn-outline-dark" onclick="copyBookingLink()">
                            <i class="bi bi-clipboard me-1"></i> Copy
                        </button>
                    </div>
                    <div id="copyFeedback" class="text-success small" style="display:none;"><i class="bi bi-check-circle me-1"></i>Copied to clipboard!</div>
                    <div class="mt-3">
                        <a href="{{ url('/book/installer/' . $user->booking_slug) }}" target="_blank" class="btn btn-sm btn-outline-dark">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Preview Booking Page
                        </a>
                    </div>
                @else
                    <div class="alert alert-warning small mb-0">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Your booking link will be generated automatically once you save your company name in the <strong>Company Info</strong> section.
                    </div>
                @endif
            </div>

            @if($user->booking_slug)
            <div class="form-card">
                <h6>How It Works</h6>
                <ul class="text-muted small mb-0" style="padding-left: 1.2rem;">
                    <li class="mb-1">Customers visit your booking link — no login required</li>
                    <li class="mb-1">They select from the services you've set up</li>
                    <li class="mb-1">They pick an available time slot from your calendar</li>
                    <li class="mb-1">You receive the booking request on your Calendar page</li>
                </ul>
            </div>
            @endif
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

{{-- Service Modal --}}
<div class="modal fade" id="serviceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold" id="serviceModalTitle">Add Service</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="serviceEditId">
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Service Name</label>
                    <input type="text" id="serviceName" class="form-control form-control-sm" placeholder="e.g. Window Installation" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small">Description</label>
                    <textarea id="serviceDesc" class="form-control form-control-sm" rows="2" placeholder="Brief description of the service"></textarea>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Price ($)</label>
                        <input type="number" id="servicePrice" class="form-control form-control-sm" step="0.01" min="0" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Price Type</label>
                        <select id="servicePriceType" class="form-select form-select-sm">
                            <option value="flat">Flat Rate</option>
                            <option value="per_unit">Per Unit</option>
                            <option value="per_hour">Per Hour</option>
                            <option value="per_sqft">Per Sq Ft</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small">Estimated Duration (minutes)</label>
                    <input type="number" id="serviceDuration" class="form-control form-control-sm" min="0" placeholder="e.g. 60">
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="serviceActive" checked>
                    <label class="form-check-label small" for="serviceActive">Active (visible on booking page)</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-vip" onclick="saveService()"><i class="bi bi-check me-1"></i> Save Service</button>
            </div>
        </div>
    </div>
</div>

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

            if (target === 'services') loadServices();
        });
    });
});

const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

// ── Services CRUD ──────────────────────────
function loadServices() {
    fetch('{{ route("installer.services.index") }}', { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            const c = document.getElementById('servicesListContainer');
            if (!data.services || !data.services.length) {
                c.innerHTML = '<div class="text-center py-4 text-muted small"><i class="bi bi-tools fs-3 d-block mb-1"></i>No services yet. Add your first service above.</div>';
                return;
            }
            let h = '<div class="table-responsive"><table class="table table-sm table-hover mb-0"><thead><tr><th>Service</th><th>Price</th><th>Duration</th><th>Status</th><th></th></tr></thead><tbody>';
            const typeLabels = { flat: 'Flat', per_unit: '/unit', per_hour: '/hr', per_sqft: '/sqft' };
            data.services.forEach(s => {
                h += `<tr>
                    <td><div class="fw-semibold small">${s.name}</div>${s.description ? '<div class="text-muted" style="font-size:.75rem;">' + s.description + '</div>' : ''}</td>
                    <td class="small">$${parseFloat(s.price).toFixed(2)} <span class="text-muted">${typeLabels[s.price_type] || s.price_type}</span></td>
                    <td class="small text-muted">${s.estimated_duration ? s.estimated_duration + ' min' : '—'}</td>
                    <td><span class="badge ${s.is_active ? 'bg-success' : 'bg-secondary'}">${s.is_active ? 'Active' : 'Inactive'}</span></td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-dark py-0 px-1" onclick='editService(${JSON.stringify(s)})'><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-outline-danger py-0 px-1" onclick="deleteService(${s.id})"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>`;
            });
            h += '</tbody></table></div>';
            c.innerHTML = h;
        });
}

function openServiceModal() {
    document.getElementById('serviceEditId').value = '';
    document.getElementById('serviceName').value = '';
    document.getElementById('serviceDesc').value = '';
    document.getElementById('servicePrice').value = '';
    document.getElementById('servicePriceType').value = 'flat';
    document.getElementById('serviceDuration').value = '';
    document.getElementById('serviceActive').checked = true;
    document.getElementById('serviceModalTitle').textContent = 'Add Service';
    new bootstrap.Modal(document.getElementById('serviceModal')).show();
}

function editService(svc) {
    document.getElementById('serviceEditId').value = svc.id;
    document.getElementById('serviceName').value = svc.name;
    document.getElementById('serviceDesc').value = svc.description || '';
    document.getElementById('servicePrice').value = svc.price;
    document.getElementById('servicePriceType').value = svc.price_type;
    document.getElementById('serviceDuration').value = svc.estimated_duration || '';
    document.getElementById('serviceActive').checked = !!svc.is_active;
    document.getElementById('serviceModalTitle').textContent = 'Edit Service';
    new bootstrap.Modal(document.getElementById('serviceModal')).show();
}

function saveService() {
    const id = document.getElementById('serviceEditId').value;
    const payload = {
        name: document.getElementById('serviceName').value,
        description: document.getElementById('serviceDesc').value,
        price: document.getElementById('servicePrice').value,
        price_type: document.getElementById('servicePriceType').value,
        estimated_duration: document.getElementById('serviceDuration').value || null,
        is_active: document.getElementById('serviceActive').checked ? 1 : 0,
    };

    const url = id ? `{{ url('installer/services') }}/${id}` : '{{ route("installer.services.store") }}';
    const method = id ? 'PUT' : 'POST';

    fetch(url, {
        method, headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('serviceModal')).hide();
            loadServices();
        }
    });
}

function deleteService(id) {
    if (!confirm('Delete this service?')) return;
    fetch(`{{ url('installer/services') }}/${id}`, {
        method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => { if (data.success) loadServices(); });
}

// ── Booking Link Copy ──────────────────────
function copyBookingLink() {
    const input = document.getElementById('bookingLinkInput');
    if (!input) return;
    navigator.clipboard.writeText(input.value).then(() => {
        const fb = document.getElementById('copyFeedback');
        fb.style.display = 'block';
        setTimeout(() => fb.style.display = 'none', 2000);
    });
}
</script>
@endpush
