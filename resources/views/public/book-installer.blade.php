@extends('layouts.public')
@section('title', 'Book Installation — ' . ($installer->company_name ?: $installer->name))

@push('styles')
<style>
    .booking-wrapper { padding-top: 90px; }
    .slot-card { cursor: pointer; transition: all .15s; border: 2px solid transparent; }
    .slot-card:hover { border-color: var(--vip-accent); }
    .slot-card.selected { border-color: var(--vip-accent); background: #fef9ef; }
    .slot-card.unavailable { opacity: .4; cursor: not-allowed; pointer-events: none; }
    .installer-hero { background: #f9f8f5; border-radius: .75rem; padding: 1.5rem; margin-bottom: 1.5rem; }
    .service-option { cursor: pointer; transition: all .15s; border: 2px solid transparent; }
    .service-option:hover { border-color: var(--vip-accent); }
    .service-option.selected { border-color: var(--vip-accent); background: #fef9ef; }
</style>
@endpush

@section('content')
<div class="booking-wrapper">
    <div class="container py-4" style="max-width:900px;">

        {{-- Installer Header --}}
        <div class="installer-hero d-flex align-items-center gap-3">
            @if($installer->company_logo_dark)
                <img src="{{ asset('uploads/installer-logos/' . $installer->company_logo_dark) }}" style="height:60px; max-width:150px; object-fit:contain;">
            @else
                <div class="rounded-circle bg-dark text-white d-flex align-items-center justify-content-center" style="width:60px;height:60px;font-size:1.2rem;font-weight:700;">
                    {{ strtoupper(substr($installer->name, 0, 2)) }}
                </div>
            @endif
            <div>
                <h4 class="fw-bold mb-0">{{ $installer->company_name ?: $installer->name }}</h4>
                <div class="text-muted small">
                    @if($installer->company_phone)<i class="bi bi-telephone me-1"></i>{{ $installer->company_phone }} @endif
                    @if($installer->company_email)<span class="ms-2"><i class="bi bi-envelope me-1"></i>{{ $installer->company_email }}</span>@endif
                </div>
                @if($installer->company_city)
                    <div class="text-muted small"><i class="bi bi-geo-alt me-1"></i>{{ $installer->company_city }}, {{ $installer->company_state }}</div>
                @endif
            </div>
        </div>

        <h5 class="fw-bold mb-3"><i class="bi bi-calendar-check me-2"></i>Book an Installation</h5>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('public.book.confirm', $installer->booking_slug) }}" id="bookingForm">
            @csrf
            <input type="hidden" name="booking_time" id="bookingTimeInput">

            {{-- Service Selection --}}
            @if($services->count())
                <div class="card mb-3">
                    <div class="card-header bg-white fw-semibold">
                        <span class="badge bg-dark rounded-pill me-2">1</span> Select Service
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            @foreach($services as $svc)
                                <div class="col-md-6">
                                    <div class="card service-option p-3" onclick="selectService(this, '{{ $svc->name }}')">
                                        <div class="d-flex justify-content-between">
                                            <div class="fw-semibold">{{ $svc->name }}</div>
                                            <div class="fw-bold" style="color:var(--vip-accent);">{{ $svc->priceLabel() }}</div>
                                        </div>
                                        @if($svc->description)
                                            <div class="text-muted small mt-1">{{ $svc->description }}</div>
                                        @endif
                                        @if($svc->estimated_duration)
                                            <div class="text-muted small"><i class="bi bi-clock me-1"></i>~{{ $svc->estimated_duration }} min</div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <input type="hidden" name="service_type" id="serviceTypeInput" required>
                    </div>
                </div>
            @else
                <input type="hidden" name="service_type" value="Window Installation">
            @endif

            {{-- Customer Info --}}
            <div class="card mb-3">
                <div class="card-header bg-white fw-semibold">
                    <span class="badge bg-dark rounded-pill me-2">{{ $services->count() ? '2' : '1' }}</span> Your Information
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Full Name</label>
                            <input type="text" name="customer_name" class="form-control form-control-sm" required value="{{ old('customer_name') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Email</label>
                            <input type="email" name="customer_email" class="form-control form-control-sm" required value="{{ old('customer_email') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Phone</label>
                            <input type="text" name="customer_phone" class="form-control form-control-sm" value="{{ old('customer_phone') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Installation Address</label>
                            <input type="text" name="install_address" class="form-control form-control-sm" required value="{{ old('install_address') }}">
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
                    <span class="badge bg-dark rounded-pill me-2">{{ $services->count() ? '3' : '2' }}</span> Choose Date & Time
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Preferred Date</label>
                        <input type="date" name="booking_date" id="bookingDateInput" class="form-control form-control-sm" style="max-width:220px;"
                               value="{{ $selectedDate }}" min="{{ today()->format('Y-m-d') }}" onchange="loadSlots()">
                    </div>

                    <div id="slotsContainer">
                        @if(count($slots))
                            <p class="text-muted small mb-2">Available time slots:</p>
                            <div class="row g-2">
                                @foreach($slots as $slot)
                                    <div class="col-sm-6 col-md-4">
                                        <div class="card slot-card p-2 text-center {{ !$slot['available'] ? 'unavailable' : '' }}"
                                             @if($slot['available']) onclick="selectSlot(this, '{{ $slot['time'] }}')" @endif>
                                            <div class="fw-semibold small">{{ $slot['label'] }}</div>
                                            <div class="text-muted" style="font-size:.7rem;">
                                                {{ $slot['available'] ? $slot['remaining'] . ' spot' . ($slot['remaining'] > 1 ? 's' : '') . ' left' : 'Fully booked' }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-3 text-muted small">
                                <i class="bi bi-calendar-x fs-3 d-block mb-1"></i>
                                No available slots for this date. Try another day.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-vip btn-lg w-100" id="confirmBtn" disabled>
                <i class="bi bi-check-circle me-1"></i> Submit Booking Request
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
function selectService(el, name) {
    document.querySelectorAll('.service-option').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('serviceTypeInput').value = name;
}

function selectSlot(el, time) {
    document.querySelectorAll('.slot-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('bookingTimeInput').value = time;
    document.getElementById('confirmBtn').disabled = false;
}

function loadSlots() {
    const date = document.getElementById('bookingDateInput').value;
    if (!date) return;
    document.getElementById('confirmBtn').disabled = true;
    document.getElementById('bookingTimeInput').value = '';

    fetch(`{{ route('public.book.slots', $installer->booking_slug) }}?date=${date}`)
        .then(r => r.json())
        .then(data => {
            const c = document.getElementById('slotsContainer');
            if (!data.slots || !data.slots.length) {
                c.innerHTML = '<div class="text-center py-3 text-muted small"><i class="bi bi-calendar-x fs-3 d-block mb-1"></i>No available slots for this date.</div>';
                return;
            }
            let h = '<p class="text-muted small mb-2">Available time slots:</p><div class="row g-2">';
            data.slots.forEach(s => {
                const u = !s.available ? 'unavailable' : '';
                const cl = s.available ? `onclick="selectSlot(this,'${s.time}')"` : '';
                h += `<div class="col-sm-6 col-md-4"><div class="card slot-card p-2 text-center ${u}" ${cl}><div class="fw-semibold small">${s.label}</div><div class="text-muted" style="font-size:.7rem;">${s.available ? s.remaining + ' spot' + (s.remaining > 1 ? 's' : '') + ' left' : 'Fully booked'}</div></div></div>`;
            });
            h += '</div>';
            c.innerHTML = h;
        });
}
</script>
@endpush
@endsection
