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
@endsection
