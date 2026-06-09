@extends('layouts.public')
@section('title', 'Book Installation — VIP Windows')

@push('styles')
<style>
    .booking-wrapper { padding-top: 90px; }
    .vip-hero { background: linear-gradient(135deg, #0a0a0a, #1a1a1a); border-radius: .75rem; padding: 2rem; margin-bottom: 1.5rem; color: #fff; text-align: center; }
    .vip-hero h4 { font-weight: 700; margin-bottom: .25rem; }
    .vip-hero .accent { color: var(--vip-accent); }

    /* ── Calendly-style calendar ── */
    .cal-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px; text-align: center; }
    .cal-hdr { font-size: .7rem; font-weight: 600; color: #888; padding: 6px 0; text-transform: uppercase; }
    .cal-day {
        padding: 8px 4px; border-radius: 50%; cursor: pointer; font-size: .85rem;
        transition: all .15s; aspect-ratio: 1; display: flex; align-items: center; justify-content: center;
        margin: 2px auto; width: 38px; height: 38px;
    }
    .cal-day:hover:not(.disabled):not(.selected) { background: #f0f0f0; }
    .cal-day.today { font-weight: 700; color: var(--vip-accent, #c8973b); }
    .cal-day.selected { background: #1a1a1a; color: #fff; font-weight: 700; }
    .cal-day.disabled { color: #ccc; cursor: not-allowed; }
    .cal-day.has-slots { position: relative; }
    .cal-day.has-slots::after { content: ''; position: absolute; bottom: 2px; left: 50%; transform: translateX(-50%); width: 5px; height: 5px; border-radius: 50%; background: var(--vip-accent, #c8973b); }
    .cal-day.empty { visibility: hidden; }
    .cal-nav { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
    .cal-nav h6 { margin: 0; font-weight: 700; font-size: .95rem; }
    .cal-nav button { background: none; border: 1px solid #ddd; border-radius: 50%; width: 32px; height: 32px; cursor: pointer; display: flex; align-items: center; justify-content: center; }
    .cal-nav button:hover { background: #f5f5f5; }

    /* ── Time slots ── */
    .time-slots { max-height: 360px; overflow-y: auto; }
    .time-slot {
        display: block; width: 100%; padding: 10px 14px; margin-bottom: 6px;
        border: 2px solid #e5e5e5; border-radius: 8px; background: #fff;
        cursor: pointer; font-size: .9rem; font-weight: 500; text-align: center;
        transition: all .15s;
    }
    .time-slot:hover { border-color: var(--vip-accent, #c8973b); background: #fef9ef; }
    .time-slot.selected { border-color: #1a1a1a; background: #1a1a1a; color: #fff; }
    .time-slot.unavailable { opacity: .35; cursor: not-allowed; pointer-events: none; text-decoration: line-through; }
    .time-slot .slot-info { font-size: .7rem; font-weight: 400; opacity: .7; }

    .date-display { font-size: .85rem; color: #666; margin-bottom: 10px; }
    .no-slots { text-align: center; color: #999; padding: 2rem 1rem; font-size: .85rem; }
    .no-slots i { font-size: 2rem; display: block; margin-bottom: .5rem; }
</style>
@endpush

@section('content')
<div class="booking-wrapper">
    <div class="container py-4" style="max-width:900px;">

        <div class="vip-hero">
            <img src="/images/logo.png" alt="VIP Windows" style="height:60px;" class="mb-2">
            <h4>Book a <span class="accent">Professional</span> Installation</h4>
            <p class="text-white-50 small mb-0">Schedule your window installation with our certified technicians</p>
        </div>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('public.book.website.confirm') }}" id="bookingForm">
            @csrf
            <input type="hidden" name="booking_date" id="hiddenDate">
            <input type="hidden" name="booking_time" id="hiddenTime">

            {{-- Step 1: Service --}}
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

            {{-- Step 2: Customer Info --}}
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

            {{-- Step 3: Calendly-style Date & Time --}}
            <div class="card mb-3">
                <div class="card-header bg-white fw-semibold">
                    <span class="badge bg-dark rounded-pill me-2">3</span> Choose Date & Time
                </div>
                <div class="card-body">
                    <div class="row">
                        {{-- Calendar (left) --}}
                        <div class="col-md-7 border-end pe-3">
                            <div class="cal-nav">
                                <button type="button" onclick="calNav(-1)"><i class="bi bi-chevron-left"></i></button>
                                <h6 id="calMonthLabel"></h6>
                                <button type="button" onclick="calNav(1)"><i class="bi bi-chevron-right"></i></button>
                            </div>
                            <div class="cal-grid">
                                <div class="cal-hdr">Sun</div><div class="cal-hdr">Mon</div><div class="cal-hdr">Tue</div>
                                <div class="cal-hdr">Wed</div><div class="cal-hdr">Thu</div><div class="cal-hdr">Fri</div><div class="cal-hdr">Sat</div>
                            </div>
                            <div class="cal-grid" id="calDays"></div>
                        </div>

                        {{-- Time slots (right) --}}
                        <div class="col-md-5 ps-3">
                            <div id="timeSlotsHeader" class="date-display fw-semibold">Select a date</div>
                            <div class="time-slots" id="timeSlotsContainer">
                                <div class="no-slots">
                                    <i class="bi bi-calendar-event"></i>
                                    Pick a date to see available times
                                </div>
                            </div>
                        </div>
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
const slotsUrl = '{{ route("public.book.website.slots") }}';
const todayStr = '{{ today()->format("Y-m-d") }}';
const todayDate = new Date(todayStr + 'T00:00:00');

let calYear, calMonth, selectedDate = null, selectedTime = null;
// Slot cache: { 'YYYY-MM-DD': [...slots] }
let slotCache = {};

// Start calendar on today's month
(function init() {
    const d = new Date();
    calYear = d.getFullYear();
    calMonth = d.getMonth();
    renderCal();
})();

function calNav(dir) {
    calMonth += dir;
    if (calMonth > 11) { calMonth = 0; calYear++; }
    if (calMonth < 0)  { calMonth = 11; calYear--; }
    renderCal();
}

function renderCal() {
    const months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    document.getElementById('calMonthLabel').textContent = months[calMonth] + ' ' + calYear;

    const firstDay = new Date(calYear, calMonth, 1).getDay();
    const daysInMonth = new Date(calYear, calMonth + 1, 0).getDate();
    const container = document.getElementById('calDays');
    container.innerHTML = '';

    // Empty cells before first day
    for (let i = 0; i < firstDay; i++) {
        container.innerHTML += '<div class="cal-day empty"></div>';
    }

    // Day cells
    for (let d = 1; d <= daysInMonth; d++) {
        const dateStr = `${calYear}-${String(calMonth + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
        const dateObj = new Date(dateStr + 'T00:00:00');
        const isPast = dateObj < todayDate;
        const isToday = dateStr === todayStr;
        const isSel = dateStr === selectedDate;

        let cls = 'cal-day';
        if (isPast) cls += ' disabled';
        if (isToday) cls += ' today';
        if (isSel) cls += ' selected';

        const click = isPast ? '' : `onclick="selectDate('${dateStr}')"`;
        container.innerHTML += `<div class="${cls}" ${click}>${d}</div>`;
    }

    // Pre-fetch slots for visible dates
    prefetchMonth();
}

function prefetchMonth() {
    const daysInMonth = new Date(calYear, calMonth + 1, 0).getDate();
    for (let d = 1; d <= daysInMonth; d++) {
        const dateStr = `${calYear}-${String(calMonth + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
        const dateObj = new Date(dateStr + 'T00:00:00');
        if (dateObj < todayDate) continue;
        if (slotCache[dateStr] !== undefined) {
            markDayHasSlots(dateStr, slotCache[dateStr]);
            continue;
        }
        // Fetch in background
        fetch(`${slotsUrl}?date=${dateStr}`, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                slotCache[dateStr] = data.slots || [];
                markDayHasSlots(dateStr, slotCache[dateStr]);
            })
            .catch(() => {});
    }
}

function markDayHasSlots(dateStr, slots) {
    const hasAvailable = slots.some(s => s.available);
    // Find the day cell by matching text
    const allDays = document.querySelectorAll('#calDays .cal-day:not(.empty):not(.disabled)');
    const day = parseInt(dateStr.split('-')[2]);
    allDays.forEach(el => {
        if (parseInt(el.textContent) === day) {
            const elDate = `${calYear}-${String(calMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            if (elDate === dateStr && hasAvailable) {
                el.classList.add('has-slots');
            }
        }
    });
}

function selectDate(dateStr) {
    selectedDate = dateStr;
    selectedTime = null;
    document.getElementById('hiddenDate').value = dateStr;
    document.getElementById('hiddenTime').value = '';
    document.getElementById('confirmBtn').disabled = true;

    // Update calendar highlight
    document.querySelectorAll('#calDays .cal-day').forEach(el => el.classList.remove('selected'));
    const day = parseInt(dateStr.split('-')[2]);
    const allDays = document.querySelectorAll('#calDays .cal-day:not(.empty)');
    allDays.forEach(el => {
        if (parseInt(el.textContent) === day) {
            const elDate = `${calYear}-${String(calMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            if (elDate === dateStr) el.classList.add('selected');
        }
    });

    // Show date label
    const d = new Date(dateStr + 'T00:00:00');
    const opts = { weekday: 'long', month: 'long', day: 'numeric' };
    document.getElementById('timeSlotsHeader').textContent = d.toLocaleDateString('en-US', opts);

    // Load slots
    const container = document.getElementById('timeSlotsContainer');
    container.innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-secondary"></div></div>';

    if (slotCache[dateStr] !== undefined) {
        renderSlots(slotCache[dateStr]);
        return;
    }

    fetch(`${slotsUrl}?date=${dateStr}`, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            slotCache[dateStr] = data.slots || [];
            renderSlots(slotCache[dateStr]);
        })
        .catch(() => {
            container.innerHTML = '<div class="no-slots"><i class="bi bi-exclamation-circle"></i>Failed to load slots.</div>';
        });
}

function renderSlots(slots) {
    const container = document.getElementById('timeSlotsContainer');

    if (!slots.length) {
        container.innerHTML = '<div class="no-slots"><i class="bi bi-calendar-x"></i>Not available this day.<br><span style="font-size:.75rem;">Try another date.</span></div>';
        return;
    }

    let html = '';
    slots.forEach(s => {
        const cls = s.available ? '' : 'unavailable';
        const click = s.available ? `onclick="selectTime('${s.time}', this)"` : '';
        const info = s.available ? `${s.remaining} spot${s.remaining > 1 ? 's' : ''} left` : 'Fully booked';
        html += `<div class="time-slot ${cls}" ${click}>
            ${s.display}
            <div class="slot-info">${info}</div>
        </div>`;
    });
    container.innerHTML = html;
}

function selectTime(time, el) {
    selectedTime = time;
    document.getElementById('hiddenTime').value = time;
    document.getElementById('confirmBtn').disabled = false;

    // Highlight selected
    document.querySelectorAll('.time-slot').forEach(s => s.classList.remove('selected'));
    el.classList.add('selected');
}
</script>
@endpush
@endsection
