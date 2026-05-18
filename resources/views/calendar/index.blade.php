@extends('layouts.app')
@section('title', 'Calendar Management')

@push('styles')
<style>
    .cal-wrapper {
        display: flex;
        height: calc(100vh - 120px);
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        overflow: hidden;
    }

    /* ── Left Rail ─────────────────────────────────────── */
    .cal-sidebar {
        width: 300px;
        min-width: 300px;
        background: var(--vip-primary);
        color: #fff;
        display: flex;
        flex-direction: column;
    }
    .cal-sidebar-header {
        padding: 14px 16px 12px;
        border-bottom: 1px solid rgba(255,255,255,.1);
        background: rgba(0,0,0,.15);
    }
    .cal-sidebar-header h6 {
        margin: 0;
        font-size: .85rem;
        font-weight: 700;
        letter-spacing: .5px;
        color: var(--vip-accent);
    }

    /* Add slot form */
    .cal-add-form {
        padding: 14px;
        border-bottom: 1px solid rgba(255,255,255,.08);
    }
    .cal-add-form label {
        font-size: .72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: rgba(255,255,255,.6);
        margin-bottom: 4px;
        display: block;
    }
    .cal-add-form .form-control,
    .cal-add-form .form-select {
        background: rgba(255,255,255,.1);
        border: 1px solid rgba(255,255,255,.15);
        color: #fff;
        font-size: .82rem;
        border-radius: 6px;
    }
    .cal-add-form .form-control:focus,
    .cal-add-form .form-select:focus {
        background: rgba(255,255,255,.15);
        border-color: var(--vip-accent);
        color: #fff;
        box-shadow: none;
    }
    .cal-add-form .form-select option { color: #333; background: #fff; }
    .cal-add-form .btn-add-slot {
        background: var(--vip-accent);
        color: var(--vip-primary);
        border: none;
        font-size: .78rem;
        font-weight: 700;
        padding: 8px 16px;
        border-radius: 6px;
        width: 100%;
    }
    .cal-add-form .btn-add-slot:hover { background: #d4b35a; }

    /* Upcoming scheduled */
    .cal-upcoming-header {
        padding: 10px 14px 6px;
        font-size: .7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: rgba(255,255,255,.4);
    }
    .cal-sidebar-body {
        flex: 1;
        overflow-y: auto;
        padding: 0 0 8px;
    }
    .cal-sidebar-body::-webkit-scrollbar { width: 4px; }
    .cal-sidebar-body::-webkit-scrollbar-thumb { background: rgba(255,255,255,.2); border-radius: 4px; }
    .cal-order-card {
        padding: 10px 14px;
        border-bottom: 1px solid rgba(255,255,255,.05);
        border-left: 3px solid transparent;
    }
    .cal-order-card:hover { background: rgba(255,255,255,.05); }
    .cal-order-card .oc-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2px;
    }
    .cal-order-card .oc-name { font-weight: 600; font-size: .8rem; }
    .cal-order-card .oc-badge {
        font-size: .58rem;
        padding: 2px 6px;
        border-radius: 3px;
        font-weight: 600;
        text-transform: uppercase;
        background: rgba(0,123,255,.25);
        color: #6db8ff;
    }
    .cal-order-card .oc-meta {
        font-size: .7rem;
        opacity: .6;
    }

    .cal-sidebar-footer {
        padding: 10px 14px;
        border-top: 1px solid rgba(255,255,255,.1);
        background: rgba(0,0,0,.1);
        display: flex;
        gap: 8px;
    }
    .cal-stat { flex: 1; text-align: center; }
    .cal-stat .val { font-size: 1rem; font-weight: 700; color: var(--vip-accent); }
    .cal-stat .lbl { font-size: .6rem; text-transform: uppercase; letter-spacing: .5px; opacity: .5; }

    /* ── Main Panel — Calendar Grid ────────────────────── */
    .cal-main {
        flex: 1;
        display: flex;
        flex-direction: column;
        min-width: 0;
    }
    .cal-toolbar {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 20px;
        border-bottom: 1px solid #e9ecef;
        background: #fafafa;
        min-height: 52px;
    }
    .cal-toolbar .cal-month {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--vip-primary);
        margin: 0;
    }
    .cal-toolbar .cal-nav { margin-left: auto; display: flex; gap: 6px; }
    .cal-toolbar .cal-nav .btn {
        width: 32px; height: 32px;
        display: flex; align-items: center; justify-content: center;
        border-radius: 6px; font-size: .9rem;
    }

    .cal-grid-wrap {
        flex: 1;
        overflow-y: auto;
        padding: 16px;
    }

    /* Calendar table */
    .cal-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 4px;
    }
    .cal-table th {
        text-align: center;
        font-size: .72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #888;
        padding: 8px 4px;
    }
    .cal-table td {
        vertical-align: top;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 6px;
        min-height: 90px;
        height: 90px;
        width: 14.28%;
        background: #fff;
        transition: all .15s;
    }
    .cal-table td:hover { border-color: var(--vip-accent); box-shadow: 0 2px 8px rgba(201,168,76,.1); }
    .cal-table td.empty { background: #f8f9fa; border-color: transparent; }
    .cal-table td.today { background: #fffdf3; border-color: var(--vip-accent); }
    .cal-day-num {
        font-size: .78rem;
        font-weight: 700;
        color: #333;
        margin-bottom: 4px;
    }
    .cal-table td.today .cal-day-num { color: var(--vip-accent); }

    /* Slot pill */
    .cal-slot {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 3px 6px;
        border-radius: 4px;
        margin-bottom: 3px;
        font-size: .65rem;
        font-weight: 600;
    }
    .cal-slot.available { background: #d4edda; color: #155724; }
    .cal-slot.full { background: #f8d7da; color: #721c24; }
    .cal-slot .slot-remove {
        background: none;
        border: none;
        color: inherit;
        opacity: .5;
        font-size: .6rem;
        cursor: pointer;
        padding: 0 2px;
    }
    .cal-slot .slot-remove:hover { opacity: 1; color: #dc3545; }

    /* Order chip */
    .cal-order-chip {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        padding: 2px 6px;
        border-radius: 4px;
        background: rgba(0,123,255,.1);
        color: #0d6efd;
        font-size: .6rem;
        font-weight: 600;
        margin-bottom: 2px;
    }

    /* Availability modal */
    .avail-day-row {
        display: flex; align-items: center; gap: 8px;
        padding: 8px 0; border-bottom: 1px solid #f0f0f0;
    }
    .avail-day-row:last-child { border-bottom: none; }
    .avail-day-name { width: 80px; font-weight: 600; font-size: .85rem; }
    .avail-day-row .form-control, .avail-day-row .form-select {
        font-size: .82rem; padding: 4px 8px;
    }
    .avail-day-row .form-check-input { width: 18px; height: 18px; }
    .avail-toggle-off .avail-times { opacity: .3; pointer-events: none; }

    .override-card {
        display: flex; align-items: center; justify-content: space-between;
        background: #f8f9fa; border-radius: 6px; padding: 8px 12px;
        margin-bottom: 6px; font-size: .82rem;
    }
    .override-card .badge-off { background: #f8d7da; color: #721c24; padding: 2px 8px; border-radius: 3px; font-size: .7rem; }
    .override-card .badge-on { background: #d4edda; color: #155724; padding: 2px 8px; border-radius: 3px; font-size: .7rem; }

    .btn-manage-avail {
        background: rgba(255,255,255,.1);
        border: 1px solid rgba(255,255,255,.2);
        color: #fff;
        font-size: .75rem;
        font-weight: 600;
        padding: 6px 14px;
        border-radius: 6px;
        width: 100%;
        margin-top: 8px;
    }
    .btn-manage-avail:hover { background: rgba(255,255,255,.15); color: var(--vip-accent); }

    @media (max-width: 991.98px) {
        .cal-wrapper { flex-direction: column; height: auto; min-height: calc(100vh - 120px); }
        .cal-sidebar { width: 100%; min-width: 100%; max-height: 350px; }
        .cal-grid-wrap { min-height: 400px; }
    }
</style>
@endpush

@section('content')
@php
    $prev = $startOfMonth->copy()->subMonth()->format('Y-m');
    $next = $startOfMonth->copy()->addMonth()->format('Y-m');
    $daysInMonth = $startOfMonth->daysInMonth;
    $firstDow = $startOfMonth->dayOfWeek;

    // Flatten all orders this month for sidebar
    $allMonthOrders = collect();
    foreach ($scheduledOrders as $dayOrders) {
        $allMonthOrders = $allMonthOrders->merge($dayOrders);
    }
    $totalSlots = $slots->flatten()->count();
    $availableSlots = $slots->flatten()->filter(fn($s) => $s->isAvailable())->count();
@endphp

<div class="p-3">
    <div class="cal-wrapper">

        {{-- ── Left Rail ──────────────────────────────────── --}}
        <div class="cal-sidebar">
            <div class="cal-sidebar-header">
                <h6><i class="bi bi-calendar3 me-1"></i> CALENDAR & AVAILABILITY</h6>
            </div>

            {{-- Add slot form --}}
            <div class="cal-add-form">
                <form method="POST" action="{{ route('admin.calendar.storeSlot') }}">
                    @csrf
                    <label>Date</label>
                    <input type="date" name="slot_date" class="form-control mb-2" required min="{{ today()->format('Y-m-d') }}">
                    <label>Time Slot</label>
                    <select name="slot_time" class="form-select mb-2" required>
                        <option value="Morning (8am-12pm)">Morning (8am-12pm)</option>
                        <option value="Afternoon (12pm-4pm)">Afternoon (12pm-4pm)</option>
                        <option value="Full Day (8am-4pm)">Full Day (8am-4pm)</option>
                    </select>
                    <label>Max Bookings</label>
                    <input type="number" name="max_bookings" class="form-control mb-3" value="2" min="1" max="20" required>
                    <button type="submit" class="btn-add-slot"><i class="bi bi-plus-circle me-1"></i> Add Slot</button>
                </form>
            </div>

            {{-- Manage Availability --}}
            <div style="padding: 0 14px;">
                <button class="btn-manage-avail" onclick="openAvailabilityModal()">
                    <i class="bi bi-clock-history me-1"></i> Manage Weekly Availability
                </button>
            </div>

            {{-- Upcoming installations this month --}}
            <div class="cal-upcoming-header">Scheduled This Month ({{ $allMonthOrders->count() }})</div>
            <div class="cal-sidebar-body">
                @forelse($allMonthOrders->sortBy('scheduled_date') as $ord)
                    @php $sideColor = $serviceColors[$ord->service_type] ?? '#0d6efd'; @endphp
                    <div class="cal-order-card" style="border-left-color:{{ $sideColor }};">
                        <div class="oc-top">
                            <span class="oc-name">{{ Str::limit($ord->customer_name, 20) }}</span>
                            <span class="oc-badge">{{ $ord->scheduled_slot ?? 'TBD' }}</span>
                        </div>
                        <div class="oc-meta">
                            <i class="bi bi-calendar-event me-1"></i>{{ \Carbon\Carbon::parse($ord->scheduled_date)->format('M d') }}
                            &middot; {{ Str::limit($ord->install_city ?? '', 15) }}
                        </div>
                    </div>
                @empty
                    <div class="text-center py-3 opacity-50">
                        <i class="bi bi-calendar-x d-block mb-1" style="font-size:1.3rem"></i>
                        <small>No installations scheduled</small>
                    </div>
                @endforelse
            </div>

            <div class="cal-sidebar-footer">
                <div class="cal-stat">
                    <div class="val">{{ $totalSlots }}</div>
                    <div class="lbl">Total Slots</div>
                </div>
                <div class="cal-stat">
                    <div class="val">{{ $availableSlots }}</div>
                    <div class="lbl">Available</div>
                </div>
                <div class="cal-stat">
                    <div class="val">{{ $allMonthOrders->count() }}</div>
                    <div class="lbl">Booked</div>
                </div>
            </div>
        </div>

        {{-- ── Main Panel — Calendar Grid ─────────────────── --}}
        <div class="cal-main">
            <div class="cal-toolbar">
                <h5 class="cal-month">{{ $startOfMonth->format('F Y') }}</h5>
                <div class="cal-nav">
                    <a href="{{ route('admin.calendar.index', ['month' => $prev]) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                    <a href="{{ route('admin.calendar.index', ['month' => now()->format('Y-m')]) }}" class="btn btn-outline-dark" style="width:auto;font-size:.72rem;font-weight:600;padding:4px 10px;">
                        Today
                    </a>
                    <a href="{{ route('admin.calendar.index', ['month' => $next]) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </div>
            </div>

            <div class="cal-grid-wrap">
                <table class="cal-table">
                    <thead>
                        <tr>
                            @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $d)
                                <th>{{ $d }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @php $day = 1; @endphp
                        @for($w = 0; $w < 6 && $day <= $daysInMonth; $w++)
                            <tr>
                                @for($dow = 0; $dow < 7; $dow++)
                                    @if(($w === 0 && $dow < $firstDow) || $day > $daysInMonth)
                                        <td class="empty"></td>
                                    @else
                                        @php
                                            $dateKey = $startOfMonth->copy()->day($day)->format('Y-m-d');
                                            $daySlots = $slots[$dateKey] ?? collect();
                                            $dayOrders = $scheduledOrders[$dateKey] ?? collect();
                                            $isToday = $dateKey === today()->format('Y-m-d');
                                        @endphp
                                        <td class="{{ $isToday ? 'today' : '' }}">
                                            <div class="cal-day-num">{{ $day }}</div>
                                            @foreach($daySlots as $slot)
                                                <div class="cal-slot {{ $slot->isAvailable() ? 'available' : 'full' }}">
                                                    <span>{{ Str::limit($slot->slot_time, 12) }} ({{ $slot->bookingsRemaining() }}/{{ $slot->max_bookings }})</span>
                                                    <form method="POST" action="{{ route('admin.calendar.deleteSlot', $slot->id) }}" class="d-inline" onsubmit="return confirm('Remove?')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="slot-remove"><i class="bi bi-x"></i></button>
                                                    </form>
                                                </div>
                                            @endforeach
                                            @foreach($dayOrders as $ord)
                                                @php
                                                    $chipColor = $serviceColors[$ord->service_type] ?? '#0d6efd';
                                                @endphp
                                                <div class="cal-order-chip" style="background:{{ $chipColor }}20; color:{{ $chipColor }};" title="{{ $ord->service_type ?? 'Installation' }}">
                                                    <i class="bi bi-tools"></i> {{ Str::limit($ord->customer_name, 8) }}
                                                </div>
                                            @endforeach
                                        </td>
                                        @php $day++; @endphp
                                    @endif
                                @endfor
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

{{-- Manage Availability Modal --}}
<div class="modal fade" id="availabilityModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-clock me-2"></i>Manage Weekly Availability</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs mb-3" id="availTabs">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#weeklyTab">Weekly Schedule</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#overridesTab">Date Overrides</a>
                    </li>
                </ul>

                <div class="tab-content">
                    {{-- Weekly Schedule Tab --}}
                    <div class="tab-pane fade show active" id="weeklyTab">
                        <p class="text-muted" style="font-size:.82rem;">Set your default weekly working hours. These apply to all weeks unless overridden for specific dates.</p>
                        <div id="weeklyDays">
                            <!-- Populated by JS -->
                        </div>
                        <button class="btn btn-vip mt-3" onclick="saveWeeklyAvailability()">
                            <i class="bi bi-check-lg me-1"></i> Save Weekly Schedule
                        </button>
                    </div>

                    {{-- Date Overrides Tab --}}
                    <div class="tab-pane fade" id="overridesTab">
                        <p class="text-muted" style="font-size:.82rem;">Add exceptions for specific dates — mark days off, holidays, or custom hours.</p>
                        <div class="row g-2 mb-3 align-items-end">
                            <div class="col-3">
                                <label class="form-label" style="font-size:.75rem;">Date</label>
                                <input type="date" id="overrideDate" class="form-control form-control-sm" min="{{ today()->format('Y-m-d') }}">
                            </div>
                            <div class="col-2">
                                <label class="form-label" style="font-size:.75rem;">Available?</label>
                                <select id="overrideAvailable" class="form-select form-select-sm" onchange="toggleOverrideTimes()">
                                    <option value="0">Day Off</option>
                                    <option value="1">Custom Hours</option>
                                </select>
                            </div>
                            <div class="col-2 override-time-fields" style="display:none;">
                                <label class="form-label" style="font-size:.75rem;">Start</label>
                                <input type="time" id="overrideStart" class="form-control form-control-sm" value="08:00">
                            </div>
                            <div class="col-2 override-time-fields" style="display:none;">
                                <label class="form-label" style="font-size:.75rem;">End</label>
                                <input type="time" id="overrideEnd" class="form-control form-control-sm" value="17:00">
                            </div>
                            <div class="col-2">
                                <label class="form-label" style="font-size:.75rem;">Reason</label>
                                <input type="text" id="overrideReason" class="form-control form-control-sm" placeholder="e.g. Holiday">
                            </div>
                            <div class="col-1">
                                <button class="btn btn-sm btn-vip w-100" onclick="addOverride()"><i class="bi bi-plus"></i></button>
                            </div>
                        </div>
                        <div id="overridesList">
                            <!-- Populated by JS -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const dayNames = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
let currentAvailability = {};
let currentOverrides = [];

function openAvailabilityModal() {
    // Fetch current availability
    fetch('{{ route("admin.calendar.availability") }}', {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        currentAvailability = data.availability || {};
        currentOverrides = data.overrides || [];
        renderWeeklyDays();
        renderOverrides();
        new bootstrap.Modal(document.getElementById('availabilityModal')).show();
    })
    .catch(() => alert('Failed to load availability data.'));
}

function renderWeeklyDays() {
    const container = document.getElementById('weeklyDays');
    let html = '';
    for (let d = 0; d < 7; d++) {
        const avail = currentAvailability[d] || {};
        const isAvail = avail.is_available !== undefined ? avail.is_available : (d >= 1 && d <= 5);
        const start = avail.start_time ? avail.start_time.substring(0,5) : '08:00';
        const end = avail.end_time ? avail.end_time.substring(0,5) : '17:00';
        const duration = avail.slot_duration || 60;
        const maxBook = avail.max_bookings_per_slot || 5;

        html += `
        <div class="avail-day-row ${isAvail ? '' : 'avail-toggle-off'}" id="dayRow${d}">
            <div class="avail-day-name">${dayNames[d]}</div>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="dayAvail${d}" ${isAvail ? 'checked' : ''} onchange="toggleDayRow(${d})">
            </div>
            <div class="avail-times d-flex gap-2 flex-grow-1">
                <input type="time" class="form-control" id="dayStart${d}" value="${start}" style="max-width:120px;">
                <span class="align-self-center" style="font-size:.8rem;color:#999;">to</span>
                <input type="time" class="form-control" id="dayEnd${d}" value="${end}" style="max-width:120px;">
                <select class="form-select" id="dayDuration${d}" style="max-width:100px;">
                    <option value="30" ${duration==30?'selected':''}>30 min</option>
                    <option value="60" ${duration==60?'selected':''}>1 hr</option>
                    <option value="90" ${duration==90?'selected':''}>1.5 hr</option>
                    <option value="120" ${duration==120?'selected':''}>2 hr</option>
                    <option value="240" ${duration==240?'selected':''}>4 hr</option>
                    <option value="480" ${duration==480?'selected':''}>8 hr</option>
                </select>
                <input type="number" class="form-control" id="dayMax${d}" value="${maxBook}" min="1" max="50" style="max-width:70px;" title="Max bookings per slot">
            </div>
        </div>`;
    }
    container.innerHTML = html;
}

function toggleDayRow(d) {
    const checked = document.getElementById('dayAvail' + d).checked;
    const row = document.getElementById('dayRow' + d);
    if (checked) row.classList.remove('avail-toggle-off');
    else row.classList.add('avail-toggle-off');
}

function saveWeeklyAvailability() {
    const days = [];
    for (let d = 0; d < 7; d++) {
        days.push({
            day_of_week: d,
            is_available: document.getElementById('dayAvail' + d).checked ? 1 : 0,
            start_time: document.getElementById('dayStart' + d).value,
            end_time: document.getElementById('dayEnd' + d).value,
            slot_duration: parseInt(document.getElementById('dayDuration' + d).value),
            max_bookings_per_slot: parseInt(document.getElementById('dayMax' + d).value),
        });
    }

    fetch('{{ route("admin.calendar.availability.saveWeekly") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ days })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('Weekly availability saved!');
        } else {
            alert('Error: ' + (data.message || 'Unknown'));
        }
    })
    .catch(() => alert('Failed to save.'));
}

function toggleOverrideTimes() {
    const show = document.getElementById('overrideAvailable').value === '1';
    document.querySelectorAll('.override-time-fields').forEach(el => {
        el.style.display = show ? '' : 'none';
    });
}

function addOverride() {
    const date = document.getElementById('overrideDate').value;
    if (!date) { alert('Please select a date.'); return; }

    const isAvail = document.getElementById('overrideAvailable').value === '1';
    const payload = {
        override_date: date,
        is_available: isAvail ? 1 : 0,
        reason: document.getElementById('overrideReason').value || null,
    };
    if (isAvail) {
        payload.start_time = document.getElementById('overrideStart').value;
        payload.end_time = document.getElementById('overrideEnd').value;
    }

    fetch('{{ route("admin.calendar.availability.addOverride") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Add to list and re-render
            const existing = currentOverrides.findIndex(o => o.override_date === date || (o.override_date && o.override_date.startsWith && o.override_date.startsWith(date)));
            if (existing >= 0) currentOverrides[existing] = data.override;
            else currentOverrides.push(data.override);
            renderOverrides();
            document.getElementById('overrideDate').value = '';
            document.getElementById('overrideReason').value = '';
        } else {
            alert('Error saving override.');
        }
    })
    .catch(() => alert('Failed to save override.'));
}

function removeOverride(id) {
    if (!confirm('Remove this date override?')) return;

    fetch(`/admin/calendar/availability/override/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            currentOverrides = currentOverrides.filter(o => o.id !== id);
            renderOverrides();
        }
    })
    .catch(() => alert('Failed to remove override.'));
}

function renderOverrides() {
    const container = document.getElementById('overridesList');
    if (!currentOverrides.length) {
        container.innerHTML = '<p class="text-muted" style="font-size:.82rem;">No date overrides set.</p>';
        return;
    }
    container.innerHTML = currentOverrides.map(o => {
        const dateStr = o.override_date ? (typeof o.override_date === 'string' ? o.override_date.substring(0,10) : o.override_date) : '';
        const isOff = !o.is_available;
        return `
        <div class="override-card">
            <div>
                <strong>${dateStr}</strong>
                <span class="${isOff ? 'badge-off' : 'badge-on'} ms-2">${isOff ? 'DAY OFF' : 'Custom Hours'}</span>
                ${!isOff && o.start_time ? `<span class="ms-2" style="font-size:.78rem;color:#666;">${o.start_time.substring(0,5)} – ${o.end_time.substring(0,5)}</span>` : ''}
                ${o.reason ? `<span class="ms-2 text-muted" style="font-size:.78rem;">— ${o.reason}</span>` : ''}
            </div>
            <button class="btn btn-sm btn-outline-danger" onclick="removeOverride(${o.id})" style="font-size:.7rem;padding:2px 8px;">
                <i class="bi bi-x"></i>
            </button>
        </div>`;
    }).join('');
}
</script>
@endpush
