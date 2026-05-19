@extends('layouts.app')
@section('title', 'Calendar Management')

@push('styles')
<style>
    .cal-container { display: flex; height: calc(100vh - 56px); overflow: hidden; }

    /* ── Sidebar ──────────────────────── */
    .cal-sidebar {
        width: 300px; min-width: 300px;
        background: #fff; border-right: 1px solid rgba(0,0,0,.08);
        display: flex; flex-direction: column; overflow-y: auto;
    }
    .cal-sidebar-header {
        padding: 1.25rem 1.25rem .75rem;
        border-bottom: 1px solid rgba(0,0,0,.06);
    }
    .cal-sidebar-header h6 { font-size: .75rem; text-transform: uppercase; letter-spacing: 1.2px; color: rgba(0,0,0,.4); margin-bottom: .5rem; }
    .cal-stat { display: flex; justify-content: space-between; align-items: center; padding: .35rem 0; font-size: .85rem; }
    .cal-stat .dot { width: 10px; height: 10px; border-radius: 50%; margin-right: .5rem; display: inline-block; }
    .cal-stat .count { font-weight: 700; font-size: .9rem; }

    .cal-actions { padding: .75rem 1.25rem; border-bottom: 1px solid rgba(0,0,0,.06); }
    .cal-actions .btn { font-size: .78rem; }

    .cal-day-list { flex: 1; overflow-y: auto; padding: .75rem 1rem; }
    .cal-day-section { margin-bottom: 1rem; }
    .cal-day-section .day-header {
        font-size: .68rem; text-transform: uppercase; letter-spacing: 1px;
        color: rgba(0,0,0,.4); font-weight: 600; margin-bottom: .4rem;
        padding-bottom: .25rem; border-bottom: 1px solid rgba(0,0,0,.06);
    }
    .cal-item-card {
        background: #f9f8f5; border-radius: .375rem; padding: .5rem .75rem;
        margin-bottom: .3rem; cursor: pointer; transition: all .12s;
        border-left: 3px solid transparent;
    }
    .cal-item-card:hover { background: rgba(201,168,76,.06); }
    .cal-item-card .ic-title { font-size: .82rem; font-weight: 600; color: #111; }
    .cal-item-card .ic-meta { font-size: .72rem; color: #888; margin-top: 1px; }
    .cal-item-card.type-job { border-left-color: #17a2b8; }
    .cal-item-card.type-order { border-left-color: #007bff; }
    .cal-item-card.type-event { border-left-color: #c9a84c; }

    /* ── Calendar Grid ──────────────────── */
    .cal-main { flex: 1; overflow-y: auto; background: var(--vip-light); display: flex; flex-direction: column; }
    .cal-toolbar {
        background: #fff; border-bottom: 1px solid rgba(0,0,0,.06);
        padding: .75rem 1.5rem; display: flex; align-items: center; justify-content: space-between;
    }
    .cal-toolbar h5 { font-size: 1.1rem; font-weight: 700; margin: 0; }
    .cal-toolbar .nav-btns { display: flex; gap: .35rem; }
    .cal-toolbar .nav-btns a {
        display: inline-flex; align-items: center; justify-content: center;
        width: 32px; height: 32px; border-radius: .3rem;
        border: 1px solid rgba(0,0,0,.1); background: #fff; color: #333;
        text-decoration: none; transition: all .12s;
    }
    .cal-toolbar .nav-btns a:hover { border-color: var(--vip-accent); color: var(--vip-accent); }

    .cal-grid-wrap { flex: 1; padding: 1rem 1.5rem; }
    .cal-grid {
        display: grid; grid-template-columns: repeat(7, 1fr);
        background: #fff; border-radius: .5rem; box-shadow: 0 1px 4px rgba(0,0,0,.06);
        overflow: hidden;
    }
    .cal-grid .day-name {
        text-align: center; padding: .5rem; font-size: .65rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: 1px; color: rgba(0,0,0,.4);
        background: #fafaf7; border-bottom: 1px solid rgba(0,0,0,.06);
    }
    .cal-cell {
        min-height: 90px; padding: .35rem .5rem;
        border-right: 1px solid rgba(0,0,0,.04);
        border-bottom: 1px solid rgba(0,0,0,.04);
        transition: background .1s; position: relative;
    }
    .cal-cell:hover { background: rgba(201,168,76,.03); }
    .cal-cell.today { background: rgba(201,168,76,.08); }
    .cal-cell.other-month { opacity: .35; }
    .cal-cell .cell-date {
        font-size: .75rem; font-weight: 600; color: #333;
        display: inline-flex; align-items: center; justify-content: center;
        width: 24px; height: 24px; border-radius: 50%;
    }
    .cal-cell.today .cell-date { background: var(--vip-accent); color: #fff; }
    .cal-cell .cell-items { margin-top: .25rem; }
    .cal-cell .cell-chip {
        font-size: .6rem; padding: 1px 4px; border-radius: 2px; margin-bottom: 2px;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        display: block; font-weight: 600;
    }
    .chip-job { background: rgba(23,162,184,.15); color: #0c5460; }
    .chip-order { background: rgba(0,123,255,.15); color: #004085; }
    .chip-event { background: rgba(201,168,76,.15); color: #7a6523; }
    .cal-cell .cell-more { font-size: .58rem; color: var(--vip-accent); font-weight: 600; margin-top: 1px; }

    /* Availability modal */
    .avail-day-row { display: flex; align-items: center; gap: .75rem; padding: .5rem 0; border-bottom: 1px solid rgba(0,0,0,.05); }
    .avail-day-row .day-label { width: 100px; font-weight: 600; font-size: .85rem; }
    .avail-day-row .form-control, .avail-day-row .form-select { font-size: .82rem; padding: .3rem .5rem; }
    .avail-day-fields { transition: opacity .15s; }
    .avail-day-fields.off { opacity: .3; pointer-events: none; }

    @media (max-width: 991.98px) {
        .cal-container { flex-direction: column; height: auto; }
        .cal-sidebar { width: 100%; min-width: 100%; max-height: 35vh; }
        .cal-cell { min-height: 60px; }
    }

    /* Google Places autocomplete dropdown above Bootstrap modals */
    .pac-container { z-index: 1060 !important; }
</style>
@endpush

@section('content')
@php
    $today = \Carbon\Carbon::today();
    $prevMonth = $startOfMonth->copy()->subMonth();
    $nextMonth = $startOfMonth->copy()->addMonth();
    $monthLabel = $startOfMonth->format('F Y');
    $dayNames = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

    // Upcoming items for sidebar
    $upcomingJobs = \App\Models\Job::with('service')
        ->whereNotNull('scheduled_date')
        ->where('scheduled_date', '>=', $today)
        ->whereIn('status', ['pending', 'scheduled', 'in_progress'])
        ->orderBy('scheduled_date')
        ->take(8)->get();
@endphp

<div class="cal-container">
    {{-- ── Sidebar ──────────────────────── --}}
    <div class="cal-sidebar">
        <div class="cal-sidebar-header">
            <h6>{{ $monthLabel }} Overview</h6>
            <div class="cal-stat">
                <span><span class="dot" style="background:#333;"></span> Total Jobs</span>
                <span class="count">{{ $totalJobs }}</span>
            </div>
            <div class="cal-stat">
                <span><span class="dot" style="background:#ffc107;"></span> Pending</span>
                <span class="count" style="color:#ffc107;">{{ $pendingJobs }}</span>
            </div>
            <div class="cal-stat">
                <span><span class="dot" style="background:#17a2b8;"></span> Scheduled</span>
                <span class="count" style="color:#17a2b8;">{{ $scheduledCount }}</span>
            </div>
            <div class="cal-stat">
                <span><span class="dot" style="background:#007bff;"></span> In Progress</span>
                <span class="count" style="color:#007bff;">{{ $inProgressCount }}</span>
            </div>
            <div class="cal-stat">
                <span><span class="dot" style="background:#28a745;"></span> Completed</span>
                <span class="count" style="color:#28a745;">{{ $completedCount }}</span>
            </div>
            <div class="cal-stat">
                <span><span class="dot" style="background:#6f42c1;"></span> Orders</span>
                <span class="count" style="color:#6f42c1;">{{ $totalOrders }}</span>
            </div>
            <div class="cal-stat">
                <span><span class="dot" style="background:#c9a84c;"></span> Events</span>
                <span class="count" style="color:#c9a84c;">{{ $totalEvents }}</span>
            </div>
        </div>

        <div class="cal-actions">
            <button class="btn btn-sm btn-outline-dark w-100 mb-2" data-bs-toggle="modal" data-bs-target="#availabilityModal">
                <i class="bi bi-gear me-1"></i> Manage Availability
            </button>
            <button class="btn btn-sm btn-vip w-100" data-bs-toggle="modal" data-bs-target="#addEventModal">
                <i class="bi bi-plus-circle me-1"></i> Add to Schedule
            </button>
        </div>

        <div class="cal-day-list">
            @if($upcomingJobs->count())
                <div class="cal-day-section">
                    <div class="day-header"><i class="bi bi-clock me-1"></i> Upcoming Jobs</div>
                    @foreach($upcomingJobs as $uj)
                        <div class="cal-item-card type-job">
                            <div class="ic-title">{{ $uj->job_number }} — {{ Str::limit($uj->customer_name, 18) }}</div>
                            <div class="ic-meta">
                                <i class="bi bi-calendar me-1"></i>{{ $uj->scheduled_date->format('M d') }}
                                @if($uj->scheduled_time) @ {{ $uj->scheduled_time }} @endif
                                @if($uj->service)
                                    &middot; <span style="color:{{ $uj->service->color ?? '#666' }};">{{ $uj->service->name }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-calendar-check" style="font-size:2rem; opacity:.3;"></i>
                    <p class="mt-2 small">No upcoming jobs this month</p>
                </div>
            @endif
        </div>
    </div>

    {{-- ── Main Calendar Grid ──────────────────── --}}
    <div class="cal-main">
        <div class="cal-toolbar">
            <div class="nav-btns">
                <a href="{{ route('admin.calendar.index', ['month' => $prevMonth->format('Y-m')]) }}" title="Previous"><i class="bi bi-chevron-left"></i></a>
                <a href="{{ route('admin.calendar.index') }}" title="Today" style="width:auto; padding:0 10px; font-size:.75rem; font-weight:600;">Today</a>
                <a href="{{ route('admin.calendar.index', ['month' => $nextMonth->format('Y-m')]) }}" title="Next"><i class="bi bi-chevron-right"></i></a>
            </div>
            <h5>{{ $monthLabel }}</h5>
            <a href="{{ route('admin.jobs.index') }}" class="btn btn-sm btn-vip"><i class="bi bi-list-ul me-1"></i> All Jobs</a>
        </div>

        <div class="cal-grid-wrap">
            <div class="cal-grid">
                @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $dn)
                    <div class="day-name">{{ $dn }}</div>
                @endforeach

                @php $current = $gridStart->copy(); @endphp
                @while($current <= $gridEnd)
                    @php
                        $dateKey = $current->format('Y-m-d');
                        $isToday = $current->isSameDay($today);
                        $isOther = $current->month !== $startOfMonth->month;
                        $dayJobList = $scheduledJobs[$dateKey] ?? collect();
                        $dayOrderList = $scheduledOrders[$dateKey] ?? collect();
                        $dayEventList = $calendarEvents[$dateKey] ?? collect();
                        $daySlotList = $slots[$dateKey] ?? collect();
                        $maxShow = 3;

                        $allItems = collect();
                        foreach($dayJobList as $j) { $allItems->push(['type' => 'job', 'id' => $j->id, 'label' => Str::limit($j->customer_name ?: $j->job_number, 10), 'full_label' => ($j->job_number ?? '') . ' — ' . ($j->customer_name ?? ''), 'time' => $j->scheduled_time, 'color' => ($j->service ? ($serviceColorById[$j->service_id] ?? '#17a2b8') : '#17a2b8'), 'address' => trim(($j->install_address ?? '') . ', ' . ($j->install_city ?? '') . ' ' . ($j->install_state ?? ''), ', '), 'status' => $j->status, 'service_name' => $j->service?->name]); }
                        foreach($dayOrderList as $o) { $allItems->push(['type' => 'order', 'id' => $o->id, 'label' => Str::limit($o->customer_name, 10), 'full_label' => $o->customer_name, 'time' => null, 'color' => ($serviceColors[$o->service_type] ?? '#007bff'), 'address' => '', 'status' => $o->status, 'service_name' => $o->service_type]); }
                        foreach($dayEventList as $ev) {
                            // Always use live service color when a service is assigned; fallback to stored color or gold
                            $evColor = ($ev->service && $ev->service->color) ? $ev->service->color : ($ev->color ?: '#c9a84c');
                            $allItems->push(['type' => 'event', 'id' => $ev->id, 'label' => Str::limit($ev->title, 10), 'full_label' => $ev->title, 'time' => $ev->event_time, 'end_time' => $ev->end_time, 'color' => $evColor, 'address' => $ev->address, 'description' => $ev->description, 'service_id' => $ev->service_id, 'service_name' => $ev->service?->name, 'crew_id' => $ev->crew_id, 'crew_name' => $ev->crew?->name, 'end_date' => $ev->end_date?->format('Y-m-d'), 'customer_name' => $ev->customer_name, 'customer_email' => $ev->customer_email, 'customer_phone' => $ev->customer_phone, 'installation_types' => $ev->installation_types]);
                        }
                    @endphp
                    <div class="cal-cell {{ $isToday ? 'today' : '' }} {{ $isOther ? 'other-month' : '' }}">
                        <span class="cell-date">{{ $current->day }}</span>
                        <div class="cell-items">
                            @foreach($allItems->take($maxShow) as $idx => $item)
                                <span class="cell-chip" style="background:{{ $item['color'] }}20; color:{{ $item['color'] }}; cursor:pointer;"
                                      onclick='openCalItem(@json($item))'>
                                    @if($item['type'] === 'job')<i class="bi bi-wrench" style="font-size:.5rem;"></i>
                                    @elseif($item['type'] === 'order')<i class="bi bi-tools" style="font-size:.5rem;"></i>
                                    @else<i class="bi bi-calendar-event" style="font-size:.5rem;"></i>
                                    @endif
                                    {{ $item['label'] }}
                                </span>
                            @endforeach
                            @if($allItems->count() > $maxShow)
                                <span class="cell-more" style="cursor:pointer;" onclick='openDaySummary(@json($allItems), "{{ $dateKey }}")'>+{{ $allItems->count() - $maxShow }} more</span>
                            @endif
                        </div>
                    </div>
                    @php $current->addDay(); @endphp
                @endwhile
            </div>
        </div>
    </div>
</div>

{{-- ── Add to Schedule Modal ──────────────────── --}}
<div class="modal fade" id="addEventModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.calendar.storeEvent') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h6 class="modal-title mb-0"><i class="bi bi-calendar-plus me-1"></i> Add to Schedule</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Title *</label>
                        <input type="text" name="title" class="form-control form-control-sm" required placeholder="e.g. Site Visit, Team Meeting, Holiday...">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-4">
                            <label class="form-label">Client Name</label>
                            <input type="text" name="customer_name" class="form-control form-control-sm" placeholder="Customer name">
                        </div>
                        <div class="col-4">
                            <label class="form-label">Client Email <small class="text-muted">(notified)</small></label>
                            <input type="email" name="customer_email" class="form-control form-control-sm" placeholder="client@email.com">
                        </div>
                        <div class="col-4">
                            <label class="form-label">Client Phone</label>
                            <input type="text" name="customer_phone" class="form-control form-control-sm" placeholder="(555) 123-4567">
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-3">
                            <label class="form-label">Date *</label>
                            <input type="date" name="event_date" class="form-control form-control-sm" required value="{{ today()->format('Y-m-d') }}">
                        </div>
                        <div class="col-3">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-control form-control-sm">
                        </div>
                        <div class="col-3">
                            <label class="form-label">Start Time</label>
                            <input type="time" name="event_time" class="form-control form-control-sm">
                        </div>
                        <div class="col-3">
                            <label class="form-label">End Time</label>
                            <input type="time" name="end_time" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <input type="text" name="address" id="evAddress" class="form-control form-control-sm" placeholder="Start typing an address..." autocomplete="off">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">Service</label>
                            <select name="service_id" id="createEvService" class="form-select form-select-sm" onchange="toggleInstallTypes('create')">
                                <option value="">— None —</option>
                                @foreach($services as $svc)
                                    <option value="{{ $svc->id }}" data-name="{{ strtolower($svc->name) }}">{{ $svc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Assign Crew</label>
                            <select name="crew_id" class="form-select form-select-sm">
                                <option value="">— None —</option>
                                @foreach($crews as $crew)
                                    <option value="{{ $crew->id }}">{{ $crew->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3" id="createInstallTypesWrap" style="display:none;">
                        <label class="form-label">Installation Types</label>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($services as $svc)
                                <div class="form-check form-check-inline" style="font-size:.85rem;">
                                    <input class="form-check-input" type="checkbox" name="installation_types[]" value="{{ $svc->name }}" id="createIT_{{ $svc->id }}">
                                    <label class="form-check-label" for="createIT_{{ $svc->id }}">{{ $svc->name }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control form-control-sm" rows="2" placeholder="Optional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-vip"><i class="bi bi-plus-circle me-1"></i> Add Event</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ── Manage Availability Modal ──────────────────── --}}
<div class="modal fade" id="availabilityModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-gear me-2"></i>Availability Settings</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs mb-3">
                    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#weeklyTab">Weekly Schedule</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#overridesTab">Date Overrides</a></li>
                </ul>

                <div class="tab-content">
                    {{-- Weekly Schedule --}}
                    <div class="tab-pane fade show active" id="weeklyTab">
                        <p class="text-muted small mb-3">Set your default weekly working hours. Customers can only book during available times.</p>
                        <form id="availabilityForm">
                            @foreach($dayNames as $idx => $dayName)
                                @php
                                    $dayAvail = $availability->get($idx);
                                    $isOn = $dayAvail ? $dayAvail->is_available : ($idx >= 1 && $idx <= 5);
                                    $startT = $dayAvail ? substr($dayAvail->start_time, 0, 5) : '08:00';
                                    $endT = $dayAvail ? substr($dayAvail->end_time, 0, 5) : '17:00';
                                    $slotDur = $dayAvail ? $dayAvail->slot_duration : 60;
                                    $maxBook = $dayAvail ? $dayAvail->max_bookings_per_slot : 5;
                                @endphp
                                <div class="avail-day-row">
                                    <div class="day-label">{{ $dayName }}</div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="dayOn{{ $idx }}" {{ $isOn ? 'checked' : '' }}
                                               onchange="toggleDayFields({{ $idx }})">
                                    </div>
                                    <div class="avail-day-fields {{ !$isOn ? 'off' : '' }}" id="dayFields{{ $idx }}">
                                        <div class="d-flex align-items-center gap-2">
                                            <input type="time" class="form-control" id="dayStart{{ $idx }}" value="{{ $startT }}" style="width:110px;">
                                            <span class="text-muted small">to</span>
                                            <input type="time" class="form-control" id="dayEnd{{ $idx }}" value="{{ $endT }}" style="width:110px;">
                                            <select class="form-select" id="dayDuration{{ $idx }}" style="width:85px;" title="Slot duration">
                                                <option value="30" {{ $slotDur == 30 ? 'selected' : '' }}>30m</option>
                                                <option value="60" {{ $slotDur == 60 ? 'selected' : '' }}>1hr</option>
                                                <option value="90" {{ $slotDur == 90 ? 'selected' : '' }}>1.5hr</option>
                                                <option value="120" {{ $slotDur == 120 ? 'selected' : '' }}>2hr</option>
                                            </select>
                                            <div class="input-group" style="width:100px;">
                                                <input type="number" class="form-control" id="dayMax{{ $idx }}" value="{{ $maxBook }}" min="1" max="50">
                                                <span class="input-group-text" style="font-size:.7rem;">max</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </form>
                        <button class="btn btn-vip mt-3" onclick="saveAvailability()">
                            <i class="bi bi-check2 me-1"></i> Save Availability
                        </button>
                    </div>

                    {{-- Date Overrides --}}
                    <div class="tab-pane fade" id="overridesTab">
                        <p class="text-muted small mb-3">Add exceptions for specific dates — mark days off, holidays, or custom hours.</p>
                        <div class="row g-2 mb-3 align-items-end">
                            <div class="col-3">
                                <label class="form-label" style="font-size:.75rem;">Date</label>
                                <input type="date" id="overrideDate" class="form-control form-control-sm" min="{{ today()->format('Y-m-d') }}">
                            </div>
                            <div class="col-2">
                                <label class="form-label" style="font-size:.75rem;">Type</label>
                                <select id="overrideAvailable" class="form-select form-select-sm" onchange="toggleOverrideTimes()">
                                    <option value="0">Day Off</option>
                                    <option value="1">Custom</option>
                                </select>
                            </div>
                            <div class="col-2 override-times" style="display:none;">
                                <label class="form-label" style="font-size:.75rem;">Start</label>
                                <input type="time" id="overrideStart" class="form-control form-control-sm" value="08:00">
                            </div>
                            <div class="col-2 override-times" style="display:none;">
                                <label class="form-label" style="font-size:.75rem;">End</label>
                                <input type="time" id="overrideEnd" class="form-control form-control-sm" value="17:00">
                            </div>
                            <div class="col-2">
                                <label class="form-label" style="font-size:.75rem;">Reason</label>
                                <input type="text" id="overrideReason" class="form-control form-control-sm" placeholder="Holiday">
                            </div>
                            <div class="col-1">
                                <button class="btn btn-sm btn-vip w-100" onclick="addOverride()"><i class="bi bi-plus"></i></button>
                            </div>
                        </div>
                        <div id="overridesList"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- ── View/Edit Calendar Item Modal ──────────────────── --}}
<div class="modal fade" id="viewItemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title mb-0" id="viewItemTitle"></h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewItemBody"></div>
            <div class="modal-footer py-2" id="viewItemFooter"></div>
        </div>
    </div>
</div>

{{-- ── Day Summary Modal (shows all items for a day) ──────────────────── --}}
<div class="modal fade" id="daySummaryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title mb-0" id="daySummaryTitle"></h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="daySummaryBody"></div>
        </div>
    </div>
</div>

{{-- ── Admin Reschedule Modal ──────────────────── --}}
<div class="modal fade" id="adminRescheduleModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title mb-0"><i class="bi bi-calendar2-week me-1"></i> Reschedule Event</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="adminRescheduleId" value="">
                <div class="mb-3">
                    <label class="form-label small fw-semibold">New Date</label>
                    <input type="date" class="form-control" id="adminRescheduleDate">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">New Time</label>
                    <input type="time" class="form-control" id="adminRescheduleTime">
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-vip btn-sm" onclick="submitAdminReschedule()">
                    <i class="bi bi-check2 me-1"></i> Confirm
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── Edit Event Modal ──────────────────── --}}
<div class="modal fade" id="editEventModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="editEventForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h6 class="modal-title mb-0"><i class="bi bi-pencil me-1"></i> Edit Event</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Title *</label>
                        <input type="text" name="title" id="editEvTitle" class="form-control form-control-sm" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-3">
                            <label class="form-label">Date *</label>
                            <input type="date" name="event_date" id="editEvDate" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-3">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" id="editEvEndDate" class="form-control form-control-sm">
                        </div>
                        <div class="col-3">
                            <label class="form-label">Start Time</label>
                            <input type="time" name="event_time" id="editEvTime" class="form-control form-control-sm">
                        </div>
                        <div class="col-3">
                            <label class="form-label">End Time</label>
                            <input type="time" name="end_time" id="editEvEndTime" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-4">
                            <label class="form-label">Client Name</label>
                            <input type="text" name="customer_name" id="editEvCustName" class="form-control form-control-sm">
                        </div>
                        <div class="col-4">
                            <label class="form-label">Client Email</label>
                            <input type="email" name="customer_email" id="editEvCustEmail" class="form-control form-control-sm">
                        </div>
                        <div class="col-4">
                            <label class="form-label">Client Phone</label>
                            <input type="text" name="customer_phone" id="editEvCustPhone" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <input type="text" name="address" id="editEvAddress" class="form-control form-control-sm" placeholder="Start typing an address..." autocomplete="off">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">Service</label>
                            <select name="service_id" id="editEvService" class="form-select form-select-sm" onchange="toggleInstallTypes('edit')">
                                <option value="">— None —</option>
                                @foreach($services as $svc)
                                    <option value="{{ $svc->id }}" data-name="{{ strtolower($svc->name) }}">{{ $svc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Crew</label>
                            <select name="crew_id" id="editEvCrew" class="form-select form-select-sm">
                                <option value="">— None —</option>
                                @foreach($crews as $crew)
                                    <option value="{{ $crew->id }}">{{ $crew->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3" id="editInstallTypesWrap" style="display:none;">
                        <label class="form-label">Installation Types</label>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($services as $svc)
                                <div class="form-check form-check-inline" style="font-size:.85rem;">
                                    <input class="form-check-input" type="checkbox" name="installation_types[]" value="{{ $svc->name }}" id="editIT_{{ $svc->id }}">
                                    <label class="form-check-label" for="editIT_{{ $svc->id }}">{{ $svc->name }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="editEvDesc" class="form-control form-control-sm" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-vip"><i class="bi bi-check-lg me-1"></i> Save</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

// Show/hide installation types checkboxes based on service selection
function toggleInstallTypes(prefix) {
    const select = document.getElementById(prefix === 'create' ? 'createEvService' : 'editEvService');
    const wrap = document.getElementById(prefix === 'create' ? 'createInstallTypesWrap' : 'editInstallTypesWrap');
    const selectedOpt = select.options[select.selectedIndex];
    const svcName = (selectedOpt?.dataset?.name || '').toLowerCase();
    // Show checkboxes when service name contains "install"
    wrap.style.display = svcName.includes('install') ? 'block' : 'none';
}

// ── Availability ──
function toggleDayFields(day) {
    const isOn = document.getElementById('dayOn' + day).checked;
    const fields = document.getElementById('dayFields' + day);
    if (isOn) fields.classList.remove('off');
    else fields.classList.add('off');
}

function saveAvailability() {
    const days = [];
    for (let i = 0; i < 7; i++) {
        days.push({
            day_of_week: i,
            is_available: document.getElementById('dayOn' + i).checked ? 1 : 0,
            start_time: document.getElementById('dayStart' + i).value,
            end_time: document.getElementById('dayEnd' + i).value,
            slot_duration: parseInt(document.getElementById('dayDuration' + i).value),
            max_bookings_per_slot: parseInt(document.getElementById('dayMax' + i).value) || 5,
        });
    }

    fetch('{{ route("admin.calendar.availability.saveWeekly") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ days })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('availabilityModal')).hide();
            alert('Availability saved!');
        }
    })
    .catch(() => alert('Error saving availability.'));
}

// ── Overrides ──
let currentOverrides = [];

function toggleOverrideTimes() {
    const show = document.getElementById('overrideAvailable').value === '1';
    document.querySelectorAll('.override-times').forEach(el => el.style.display = show ? '' : 'none');
}

function loadOverrides() {
    fetch('{{ route("admin.calendar.availability") }}', {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        currentOverrides = data.overrides || [];
        renderOverrides();
    });
}

function addOverride() {
    const date = document.getElementById('overrideDate').value;
    if (!date) { alert('Select a date.'); return; }

    const payload = {
        override_date: date,
        is_available: document.getElementById('overrideAvailable').value === '1' ? 1 : 0,
        reason: document.getElementById('overrideReason').value || null,
    };
    if (payload.is_available) {
        payload.start_time = document.getElementById('overrideStart').value;
        payload.end_time = document.getElementById('overrideEnd').value;
    }

    fetch('{{ route("admin.calendar.availability.addOverride") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            loadOverrides();
            document.getElementById('overrideDate').value = '';
            document.getElementById('overrideReason').value = '';
        }
    });
}

function removeOverride(id) {
    if (!confirm('Remove this override?')) return;
    fetch(`/admin/calendar/availability/override/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
    }).then(r => r.json()).then(data => { if (data.success) loadOverrides(); });
}

function renderOverrides() {
    const el = document.getElementById('overridesList');
    if (!currentOverrides.length) { el.innerHTML = '<p class="text-muted small">No overrides set.</p>'; return; }
    el.innerHTML = currentOverrides.map(o => {
        const d = typeof o.override_date === 'string' ? o.override_date.substring(0,10) : '';
        const off = !o.is_available;
        return `<div class="d-flex justify-content-between align-items-center p-2 mb-1" style="background:#f8f9fa; border-radius:6px; font-size:.82rem;">
            <div><strong>${d}</strong>
                <span class="ms-2 badge ${off ? 'bg-danger' : 'bg-success'}" style="font-size:.65rem;">${off ? 'DAY OFF' : 'Custom'}</span>
                ${!off && o.start_time ? `<span class="ms-1 text-muted" style="font-size:.75rem;">${o.start_time.substring(0,5)}–${o.end_time.substring(0,5)}</span>` : ''}
                ${o.reason ? `<span class="ms-1 text-muted" style="font-size:.75rem;">— ${o.reason}</span>` : ''}
            </div>
            <button class="btn btn-sm btn-outline-danger py-0 px-1" onclick="removeOverride(${o.id})" style="font-size:.7rem;"><i class="bi bi-x"></i></button>
        </div>`;
    }).join('');
}

// Load overrides when tab is shown
document.querySelector('a[href="#overridesTab"]')?.addEventListener('shown.bs.tab', loadOverrides);

// ── Calendar Item Popups ──
function openCalItem(item) {
    const modal = document.getElementById('viewItemModal');
    const title = document.getElementById('viewItemTitle');
    const body = document.getElementById('viewItemBody');
    const footer = document.getElementById('viewItemFooter');

    if (item.type === 'event') {
        title.innerHTML = `<i class="bi bi-calendar-event me-1" style="color:${item.color}"></i> ${item.full_label}`;
        let details = '';
        // Service badge
        if (item.service_name) details += `<p class="mb-1"><span class="badge" style="background:${item.color}; color:#fff; font-size:.75rem;"><i class="bi bi-tag me-1"></i>${item.service_name}</span></p>`;
        // Contact info
        if (item.customer_name) details += `<p class="mb-1 small"><i class="bi bi-person me-1"></i><strong>${item.customer_name}</strong></p>`;
        if (item.customer_phone) details += `<p class="mb-1 small"><i class="bi bi-telephone me-1"></i><a href="tel:${item.customer_phone}">${item.customer_phone}</a></p>`;
        if (item.customer_email) details += `<p class="mb-1 small"><i class="bi bi-envelope me-1"></i><a href="mailto:${item.customer_email}">${item.customer_email}</a></p>`;
        if (item.customer_name || item.customer_phone || item.customer_email) details += '<hr class="my-2">';
        // Schedule details
        if (item.time) details += `<p class="mb-1 small"><i class="bi bi-clock me-1"></i><strong>Time:</strong> ${item.time}${item.end_time ? ' – ' + item.end_time : ''}</p>`;
        if (item.address) details += `<p class="mb-1 small"><i class="bi bi-geo-alt me-1"></i><strong>Address:</strong> ${item.address}</p>`;
        if (item.crew_name) details += `<p class="mb-1 small"><i class="bi bi-people me-1"></i><strong>Crew:</strong> ${item.crew_name}</p>`;
        if (item.installation_types && item.installation_types.length) {
            const typeBadges = item.installation_types.map(t => `<span class="badge bg-secondary me-1" style="font-size:.7rem;">${t}</span>`).join('');
            details += `<p class="mb-1 small"><i class="bi bi-list-check me-1"></i><strong>Types:</strong> ${typeBadges}</p>`;
        }
        if (item.description) details += `<p class="mb-1 small"><i class="bi bi-card-text me-1"></i>${item.description}</p>`;
        if (item.end_date) details += `<p class="mb-1 small"><i class="bi bi-calendar-range me-1"></i><strong>Until:</strong> ${item.end_date}</p>`;
        if (!details) details = '<p class="text-muted small mb-0">No additional details.</p>';
        body.innerHTML = details;
        let footerHtml = '';
        if (item.customer_email) {
            footerHtml += `<button class="btn btn-sm btn-outline-warning" onclick="sendReminder(${item.id})"><i class="bi bi-bell me-1"></i>Send Reminder</button> `;
            footerHtml += `<button class="btn btn-sm btn-outline-info" onclick="sendEmail(${item.id})"><i class="bi bi-envelope me-1"></i>Email</button> `;
        }
        if (item.customer_phone) {
            footerHtml += `<a href="tel:${item.customer_phone}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-telephone me-1"></i>Call</a> `;
        }
        footerHtml += `<button class="btn btn-sm btn-outline-info" onclick="openAdminReschedule(${item.id}, '', '${item.time || ''}')"><i class="bi bi-calendar2-week me-1"></i>Reschedule</button> `;
        footerHtml += `<button class="btn btn-sm btn-outline-primary" onclick="openEditEvent(${item.id})"><i class="bi bi-pencil me-1"></i>Edit</button> `;
        footerHtml += `<form method="POST" action="/admin/calendar/event/${item.id}" class="d-inline" onsubmit="return confirm('Delete this event?')">
                <input type="hidden" name="_token" value="${csrfToken}">
                <input type="hidden" name="_method" value="DELETE">
                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash me-1"></i>Delete</button>
            </form>`;
        footer.innerHTML = footerHtml;
    } else if (item.type === 'job') {
        title.innerHTML = `<i class="bi bi-wrench me-1" style="color:${item.color}"></i> ${item.full_label}`;
        let details = '';
        if (item.service_name) details += `<p class="mb-1"><span class="badge" style="background:${item.color}; color:#fff; font-size:.75rem;"><i class="bi bi-tag me-1"></i>${item.service_name}</span></p>`;
        if (item.time) details += `<p class="mb-1 small"><i class="bi bi-clock me-1"></i><strong>Time:</strong> ${item.time}</p>`;
        if (item.address) details += `<p class="mb-1 small"><i class="bi bi-geo-alt me-1"></i><strong>Address:</strong> ${item.address}</p>`;
        if (item.status) details += `<p class="mb-1 small"><i class="bi bi-flag me-1"></i><strong>Status:</strong> ${item.status.replace('_',' ')}</p>`;
        if (!details) details = '<p class="text-muted small mb-0">No additional details.</p>';
        body.innerHTML = details;
        footer.innerHTML = `<a href="/admin/jobs?highlight=${item.id}" class="btn btn-sm btn-outline-primary"><i class="bi bi-box-arrow-up-right me-1"></i>View Job</a>`;
    } else {
        title.innerHTML = `<i class="bi bi-tools me-1" style="color:${item.color}"></i> ${item.full_label}`;
        let orderDetails = '';
        if (item.service_name) orderDetails += `<p class="mb-1"><span class="badge" style="background:${item.color}; color:#fff; font-size:.75rem;"><i class="bi bi-tag me-1"></i>${item.service_name}</span></p>`;
        orderDetails += '<p class="text-muted small mb-0">Installation order.</p>';
        body.innerHTML = orderDetails;
        footer.innerHTML = '';
    }

    new bootstrap.Modal(modal).show();
}

function openDaySummary(items, dateKey) {
    const modal = document.getElementById('daySummaryModal');
    document.getElementById('daySummaryTitle').textContent = dateKey;
    let html = '';
    items.forEach(item => {
        const icon = item.type === 'job' ? 'wrench' : (item.type === 'order' ? 'tools' : 'calendar-event');
        html += `<div class="d-flex align-items-center p-2 mb-1 rounded" style="background:${item.color}10; cursor:pointer;" onclick="bootstrap.Modal.getInstance(document.getElementById('daySummaryModal')).hide(); setTimeout(() => openCalItem(${JSON.stringify(item).replace(/"/g,'&quot;')}), 300);">
            <i class="bi bi-${icon} me-2" style="color:${item.color};"></i>
            <div>
                <div class="small fw-semibold">${item.full_label}</div>
                <div style="font-size:.7rem; color:#888;">${item.service_name ? item.service_name + ' · ' : ''}${item.type}${item.time ? ' · ' + item.time : ''}</div>
            </div>
        </div>`;
    });
    document.getElementById('daySummaryBody').innerHTML = html;
    new bootstrap.Modal(modal).show();
}

function sendAppEmail(eventId, btn, label, icon) {
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Sending...'; }
    fetch(`/admin/calendar/event/${eventId}/reminder`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            if (btn) { btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Sent!'; btn.classList.add('btn-success'); }
            setTimeout(() => { if (btn) { btn.innerHTML = `<i class="bi bi-${icon} me-1"></i>${label}`; btn.classList.remove('btn-success'); btn.disabled = false; } }, 2000);
        } else {
            alert(data.error || 'Failed to send.');
            if (btn) { btn.innerHTML = `<i class="bi bi-${icon} me-1"></i>${label}`; btn.disabled = false; }
        }
    })
    .catch(() => { alert('Failed to send.'); if (btn) { btn.innerHTML = `<i class="bi bi-${icon} me-1"></i>${label}`; btn.disabled = false; } });
}

function sendReminder(eventId) {
    if (!confirm('Send a reminder email to the client?')) return;
    sendAppEmail(eventId, event.target.closest('button'), 'Send Reminder', 'bell');
}

function sendEmail(eventId) {
    if (!confirm('Send the schedule confirmation email to the client?')) return;
    sendAppEmail(eventId, event.target.closest('button'), 'Email', 'envelope');
}

function openEditEvent(eventId) {
    // Close view modal
    bootstrap.Modal.getInstance(document.getElementById('viewItemModal'))?.hide();

    // Fetch event data and populate edit form
    fetch(`/admin/calendar/event/${eventId}`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(ev => {
        document.getElementById('editEvTitle').value = ev.title || '';
        document.getElementById('editEvDate').value = ev.event_date ? ev.event_date.substring(0,10) : '';
        document.getElementById('editEvEndDate').value = ev.end_date ? ev.end_date.substring(0,10) : '';
        document.getElementById('editEvTime').value = ev.event_time || '';
        document.getElementById('editEvEndTime').value = ev.end_time || '';
        document.getElementById('editEvAddress').value = ev.address || '';
        document.getElementById('editEvCustName').value = ev.customer_name || '';
        document.getElementById('editEvCustEmail').value = ev.customer_email || '';
        document.getElementById('editEvCustPhone').value = ev.customer_phone || '';
        document.getElementById('editEvService').value = ev.service_id || '';
        document.getElementById('editEvCrew').value = ev.crew_id || '';
        document.getElementById('editEvDesc').value = ev.description || '';
        document.getElementById('editEventForm').action = `/admin/calendar/event/${eventId}`;

        // Populate installation types checkboxes
        const types = ev.installation_types || [];
        document.querySelectorAll('#editInstallTypesWrap input[type="checkbox"]').forEach(cb => {
            cb.checked = types.includes(cb.value);
        });
        toggleInstallTypes('edit');

        setTimeout(() => new bootstrap.Modal(document.getElementById('editEventModal')).show(), 300);
    })
    .catch(() => alert('Failed to load event details.'));
}

// ── Admin Reschedule ──
function openAdminReschedule(eventId, currentDate, currentTime) {
    bootstrap.Modal.getInstance(document.getElementById('viewItemModal'))?.hide();
    document.getElementById('adminRescheduleId').value = eventId;
    document.getElementById('adminRescheduleDate').value = currentDate || '';
    document.getElementById('adminRescheduleTime').value = currentTime || '';
    setTimeout(() => new bootstrap.Modal(document.getElementById('adminRescheduleModal')).show(), 300);
}

function submitAdminReschedule() {
    const eventId = document.getElementById('adminRescheduleId').value;
    const newDate = document.getElementById('adminRescheduleDate').value;
    const newTime = document.getElementById('adminRescheduleTime').value;

    if (!newDate) { alert('Please select a date.'); return; }

    // Fetch current event, then update with new date/time
    fetch(`/admin/calendar/event/${eventId}`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(ev => {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/calendar/event/${eventId}`;
        form.style.display = 'none';

        const fields = {
            '_token': csrfToken,
            '_method': 'PUT',
            'title': ev.title || '',
            'event_date': newDate,
            'event_time': newTime || ev.event_time || '',
            'end_time': ev.end_time || '',
            'end_date': ev.end_date ? ev.end_date.substring(0,10) : '',
            'address': ev.address || '',
            'customer_name': ev.customer_name || '',
            'customer_email': ev.customer_email || '',
            'customer_phone': ev.customer_phone || '',
            'service_id': ev.service_id || '',
            'crew_id': ev.crew_id || '',
            'description': ev.description || '',
        };

        Object.entries(fields).forEach(([k, v]) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = k;
            input.value = v;
            form.appendChild(input);
        });

        // Installation types
        if (ev.installation_types && ev.installation_types.length) {
            ev.installation_types.forEach(t => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'installation_types[]';
                input.value = t;
                form.appendChild(input);
            });
        }

        document.body.appendChild(form);
        form.submit();
    })
    .catch(() => alert('Failed to reschedule.'));
}
</script>

{{-- Google Places Autocomplete --}}
@if(config('services.google.maps_key'))
<script>
function initGooglePlaces() {
    const options = {
        types: ['address'],
        componentRestrictions: { country: 'us' }
    };

    // Create modal address field
    const createAddr = document.getElementById('evAddress');
    if (createAddr) {
        const ac1 = new google.maps.places.Autocomplete(createAddr, options);
        ac1.addListener('place_changed', function() {
            const place = ac1.getPlace();
            if (place.formatted_address) {
                createAddr.value = place.formatted_address;
            }
        });
        // Prevent form submit on Enter when selecting from dropdown
        createAddr.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && document.querySelector('.pac-container:not([style*="display: none"])')) {
                e.preventDefault();
            }
        });
    }

    // Edit modal address field
    const editAddr = document.getElementById('editEvAddress');
    if (editAddr) {
        const ac2 = new google.maps.places.Autocomplete(editAddr, options);
        ac2.addListener('place_changed', function() {
            const place = ac2.getPlace();
            if (place.formatted_address) {
                editAddr.value = place.formatted_address;
            }
        });
        editAddr.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && document.querySelector('.pac-container:not([style*="display: none"])')) {
                e.preventDefault();
            }
        });
    }
}
</script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_key') }}&libraries=places&callback=initGooglePlaces" async defer></script>
@endif
@endpush
