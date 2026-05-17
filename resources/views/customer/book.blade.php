@extends('layouts.public')
@section('title', 'Book Installation')

@push('styles')
<style>
    .booking-wrapper { padding-top: 90px; }
    .slot-card { cursor: pointer; transition: all .15s; border: 2px solid transparent; }
    .slot-card:hover { border-color: var(--vip-accent); }
    .slot-card.selected { border-color: var(--vip-accent); background: #fef9ef; }
    .slot-card.unavailable { opacity: .4; cursor: not-allowed; pointer-events: none; }
    .installer-card { cursor: pointer; transition: all .15s; border: 2px solid transparent; }
    .installer-card:hover { border-color: var(--vip-accent); }
    .installer-card.selected { border-color: var(--vip-accent); background: #fef9ef; }
    .step-section { display: none; }
    .step-section.active { display: block; }
</style>
@endpush

@section('content')
<div class="booking-wrapper">
    <div class="container py-4" style="max-width:900px;">
        <a href="{{ route('customer.dashboard') }}" class="text-decoration-none mb-3 d-inline-block">
            <i class="bi bi-arrow-left me-1"></i> Back to My Account
        </a>

        <h4 class="fw-bold mb-4"><i class="bi bi-calendar-check me-2"></i>Book an Installation</h4>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('customer.book.confirm') }}" id="bookingForm">
            @csrf
            <input type="hidden" name="installer_id" id="installerIdInput" value="{{ $selectedInstaller }}">
            <input type="hidden" name="booking_time" id="bookingTimeInput">
            <input type="hidden" name="quote_id" value="{{ request('quote_id') }}">

            {{-- Step 1: Choose Installer --}}
            <div class="card mb-4">
                <div class="card-header bg-white fw-semibold">
                    <span class="badge bg-dark rounded-pill me-2">1</span> Choose Your Installer
                </div>
                <div class="card-body">
                    @if($quotedInstallers->count())
                        <p class="text-muted small mb-3">Installers who have quoted you:</p>
                        <div class="row g-3 mb-3">
                            @foreach($quotedInstallers as $inst)
                                <div class="col-md-6">
                                    <div class="card installer-card p-3 {{ $selectedInstaller == $inst->id ? 'selected' : '' }}"
                                         onclick="selectInstaller({{ $inst->id }}, this)">
                                        <div class="d-flex align-items-center gap-3">
                                            @if($inst->company_logo_dark)
                                                <img src="{{ asset('uploads/installer-logos/' . $inst->company_logo_dark) }}" style="height:40px; max-width:80px; object-fit:contain;">
                                            @else
                                                <div class="rounded-circle bg-dark text-white d-flex align-items-center justify-content-center" style="width:40px;height:40px;font-size:.9rem;">
                                                    {{ strtoupper(substr($inst->name, 0, 2)) }}
                                                </div>
                                            @endif
                                            <div>
                                                <div class="fw-semibold">{{ $inst->company_name ?: $inst->name }}</div>
                                                @if($inst->company_phone)<div class="text-muted small"><i class="bi bi-telephone me-1"></i>{{ $inst->company_phone }}</div>@endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if($allInstallers->count() > $quotedInstallers->count())
                        <p class="text-muted small mb-2">{{ $quotedInstallers->count() ? 'Or choose another installer:' : 'Available installers:' }}</p>
                        <select class="form-select" id="installerSelect" onchange="selectInstallerFromDropdown(this.value)">
                            <option value="">Select an installer...</option>
                            @foreach($allInstallers as $inst)
                                @if(!$quotedInstallers->contains('id', $inst->id))
                                    <option value="{{ $inst->id }}" {{ $selectedInstaller == $inst->id ? 'selected' : '' }}>
                                        {{ $inst->company_name ?: $inst->name }}
                                        @if($inst->company_city) — {{ $inst->company_city }}, {{ $inst->company_state }}@endif
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    @endif
                </div>
            </div>

            {{-- Step 2: Service & Address --}}
            <div class="card mb-4">
                <div class="card-header bg-white fw-semibold">
                    <span class="badge bg-dark rounded-pill me-2">2</span> Service Details
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Service Type</label>
                            <select name="service_type" class="form-select" required>
                                <option value="Window Installation">Window Installation</option>
                                <option value="Window Replacement">Window Replacement</option>
                                <option value="Sliding Door Installation">Sliding Door Installation</option>
                                <option value="Door Replacement">Door Replacement</option>
                                <option value="Measurement / Consultation">Measurement / Consultation</option>
                                <option value="Repair">Repair</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Installation Address</label>
                            <input type="text" name="install_address" class="form-control" required
                                   value="{{ Auth::user()->address }}" placeholder="Street address">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">City</label>
                            <input type="text" name="install_city" class="form-control" value="{{ Auth::user()->city }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">State</label>
                            <input type="text" name="install_state" class="form-control" value="{{ Auth::user()->state }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Zip</label>
                            <input type="text" name="install_zip" class="form-control" value="{{ Auth::user()->zip }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Additional Details</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Number of windows, any special requirements, access instructions..."></textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Step 3: Pick Date & Time --}}
            <div class="card mb-4">
                <div class="card-header bg-white fw-semibold">
                    <span class="badge bg-dark rounded-pill me-2">3</span> Choose Date & Time
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Preferred Date</label>
                            <input type="date" name="booking_date" id="bookingDateInput" class="form-control"
                                   value="{{ $selectedDate }}" min="{{ today()->format('Y-m-d') }}"
                                   onchange="loadSlots()">
                        </div>
                    </div>

                    <div id="slotsContainer">
                        @if(count($slots))
                            <p class="text-muted small mb-2">Available time slots:</p>
                            <div class="row g-2" id="slotsGrid">
                                @foreach($slots as $slot)
                                    <div class="col-sm-6 col-md-4">
                                        <div class="card slot-card p-3 text-center {{ !$slot['available'] ? 'unavailable' : '' }}"
                                             @if($slot['available']) onclick="selectSlot(this, '{{ $slot['time'] }}')" @endif>
                                            <div class="fw-semibold">{{ $slot['label'] }}</div>
                                            <div class="text-muted small">
                                                @if($slot['available'])
                                                    {{ $slot['remaining'] }} spot{{ $slot['remaining'] > 1 ? 's' : '' }} left
                                                @else
                                                    Fully booked
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4 text-muted" id="slotsPlaceholder">
                                <i class="bi bi-calendar3 fs-1 d-block mb-2"></i>
                                @if($selectedInstaller)
                                    No available slots for this date. Try another day.
                                @else
                                    Select an installer and date to see available time slots.
                                @endif
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
let selectedInstallerId = '{{ $selectedInstaller }}';

function selectInstaller(id, el) {
    document.querySelectorAll('.installer-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    selectedInstallerId = id;
    document.getElementById('installerIdInput').value = id;
    document.getElementById('installerSelect') && (document.getElementById('installerSelect').value = '');
    loadSlots();
}

function selectInstallerFromDropdown(id) {
    document.querySelectorAll('.installer-card').forEach(c => c.classList.remove('selected'));
    selectedInstallerId = id;
    document.getElementById('installerIdInput').value = id;
    if (id) loadSlots();
}

function selectSlot(el, time) {
    document.querySelectorAll('.slot-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('bookingTimeInput').value = time;
    document.getElementById('confirmBtn').disabled = false;
}

function loadSlots() {
    const date = document.getElementById('bookingDateInput').value;
    if (!selectedInstallerId || !date) return;

    document.getElementById('confirmBtn').disabled = true;
    document.getElementById('bookingTimeInput').value = '';

    fetch(`{{ route('customer.book.slots') }}?installer_id=${selectedInstallerId}&date=${date}`)
        .then(r => r.json())
        .then(data => {
            const container = document.getElementById('slotsContainer');
            if (!data.slots || data.slots.length === 0) {
                container.innerHTML = `<div class="text-center py-4 text-muted">
                    <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
                    No available slots for this date. Try another day.
                </div>`;
                return;
            }

            let html = '<p class="text-muted small mb-2">Available time slots:</p><div class="row g-2">';
            data.slots.forEach(slot => {
                const unavail = !slot.available ? 'unavailable' : '';
                const click = slot.available ? `onclick="selectSlot(this, '${slot.time}')"` : '';
                const spotsText = slot.available
                    ? `${slot.remaining} spot${slot.remaining > 1 ? 's' : ''} left`
                    : 'Fully booked';

                html += `<div class="col-sm-6 col-md-4">
                    <div class="card slot-card p-3 text-center ${unavail}" ${click}>
                        <div class="fw-semibold">${slot.label}</div>
                        <div class="text-muted small">${spotsText}</div>
                    </div>
                </div>`;
            });
            html += '</div>';
            container.innerHTML = html;
        })
        .catch(() => {
            document.getElementById('slotsContainer').innerHTML = `<div class="text-center py-4 text-muted">
                <i class="bi bi-exclamation-circle fs-1 d-block mb-2"></i>
                Unable to load slots. Please try again.
            </div>`;
        });
}

// Auto-load slots if installer pre-selected
document.addEventListener('DOMContentLoaded', () => {
    if (selectedInstallerId && document.getElementById('bookingDateInput').value) {
        loadSlots();
    }
});
</script>
@endpush
@endsection
