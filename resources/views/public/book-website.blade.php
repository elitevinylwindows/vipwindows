@extends('layouts.public')
@section('title', 'Book Installation — VIP Windows')

@push('styles')
<style>
    .booking-wrapper { padding-top: 90px; }
    .slot-card { cursor: pointer; transition: all .15s; border: 2px solid transparent; }
    .slot-card:hover { border-color: var(--vip-accent); }
    .slot-card.selected { border-color: var(--vip-accent); background: #fef9ef; }
    .slot-card.unavailable { opacity: .4; cursor: not-allowed; pointer-events: none; }
    .vip-hero { background: linear-gradient(135deg, #0a0a0a, #1a1a1a); border-radius: .75rem; padding: 2rem; margin-bottom: 1.5rem; color: #fff; text-align: center; }
    .vip-hero h4 { font-weight: 700; margin-bottom: .25rem; }
    .vip-hero .accent { color: var(--vip-accent); }
</style>
@endpush

@section('content')
<div class="booking-wrapper">
    <div class="container py-4" style="max-width:900px;">

        {{-- VIP Windows Header --}}
        <div class="vip-hero">
            <img src="/images/logo.png" alt="VIP Windows" style="height:60px;" class="mb-2">
            <h4>Book a <span class="accent">Professional</span> Installation</h4>
            <p class="text-white-50 small mb-0">Schedule your window installation with our certified technicians</p>
        </div>

        <h5 class="fw-bold mb-3"><i class="bi bi-calendar-check me-2"></i>Schedule Your Installation</h5>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('public.book.website.confirm') }}" id="bookingForm">
            @csrf

            {{-- Service Type --}}
            <div class="card mb-3">
                <div class="card-header bg-white fw-semibold">
                    <span class="badge bg-dark rounded-pill me-2">1</span> Service Type
                </div>
                <div class="card-body">
                    <select name="service_type" class="form-select form-select-sm" required>
                        <option value="">Select a service...</option>
                        <option value="Window Installation" {{ old('service_type') == 'Window Installation' ? 'selected' : '' }}>Window Installation</option>
                        <option value="Window Replacement" {{ old('service_type') == 'Window Replacement' ? 'selected' : '' }}>Window Replacement</option>
                        <option value="Door Installation" {{ old('service_type') == 'Door Installation' ? 'selected' : '' }}>Door Installation</option>
                        <option value="Door Replacement" {{ old('service_type') == 'Door Replacement' ? 'selected' : '' }}>Door Replacement</option>
                        <option value="Consultation" {{ old('service_type') == 'Consultation' ? 'selected' : '' }}>Free Consultation</option>
                        <option value="Other" {{ old('service_type') == 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
            </div>

            {{-- Customer Info --}}
            <div class="card mb-3">
                <div class="card-header bg-white fw-semibold">
                    <span class="badge bg-dark rounded-pill me-2">2</span> Your Information
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Full Name *</label>
                            <input type="text" name="customer_name" class="form-control form-control-sm" required value="{{ old('customer_name') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Email *</label>
                            <input type="email" name="customer_email" class="form-control form-control-sm" required value="{{ old('customer_email') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Phone *</label>
                            <input type="text" name="customer_phone" class="form-control form-control-sm" required value="{{ old('customer_phone') }}" placeholder="(555) 123-4567">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Installation Address *</label>
                            <input type="text" name="install_address" class="form-control form-control-sm" required value="{{ old('install_address') }}" data-address-autocomplete>
                        </div>
                        <div class="col-4">
                            <label class="form-label small">City</label>
                            <input type="text" name="install_city" class="form-control form-control-sm" value="{{ old('install_city') }}">
                        </div>
                        <div class="col-4">
                            <label class="form-label small">State</label>
                            <input type="text" name="install_state" class="form-control form-control-sm" value="{{ old('install_state') }}">
                        </div>
                        <div class="col-4">
                            <label class="form-label small">Zip</label>
                            <input type="text" name="install_zip" class="form-control form-control-sm" value="{{ old('install_zip') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label small">Additional Details</label>
                            <textarea name="description" class="form-control form-control-sm" rows="2" placeholder="Number of windows, special requirements...">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Date & Time --}}
            <div class="card mb-3">
                <div class="card-header bg-white fw-semibold">
                    <span class="badge bg-dark rounded-pill me-2">3</span> Choose Date & Time
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Preferred Date *</label>
                            <input type="date" name="booking_date" class="form-control form-control-sm"
                                   value="{{ $selectedDate }}" min="{{ today()->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Preferred Time *</label>
                            <input type="time" name="booking_time" class="form-control form-control-sm" value="{{ old('booking_time', '09:00') }}" required>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-vip btn-lg w-100">
                <i class="bi bi-check-circle me-1"></i> Submit Booking Request
            </button>
        </form>
    </div>
</div>

@endsection
