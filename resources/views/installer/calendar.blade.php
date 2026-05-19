@extends('layouts.installer')
@section('title', __('installer.calendar'))

@push('styles')
<style>
    .cal-container { display: flex; height: calc(100vh - 56px); overflow: hidden; }

    /* ── Sidebar Stats ──────────────────────── */
    .cal-sidebar {
        width: 300px; min-width: 300px;
        background: #fff; border-right: 1px solid rgba(0,0,0,.08);
        display: flex; flex-direction: column; overflow-y: auto;
    }
    .cal-sidebar-header {
        padding: 1.25rem 1.25rem .5rem;
        border-bottom: 1px solid rgba(0,0,0,.06);
    }
    .cal-sidebar-header h6 { font-size: .75rem; text-transform: uppercase; letter-spacing: 1.2px; color: rgba(0,0,0,.4); margin-bottom: .5rem; }
    .cal-stat { display: flex; justify-content: space-between; align-items: center; padding: .4rem 0; font-size: .85rem; }
    .cal-stat .dot { width: 10px; height: 10px; border-radius: 50%; margin-right: .5rem; display: inline-block; }
    .cal-stat .count { font-weight: 700; font-size: .9rem; }

    .cal-day-list { flex: 1; overflow-y: auto; padding: 1rem; }
    .cal-day-section { margin-bottom: 1.25rem; }
    .cal-day-section .day-header {
        font-size: .7rem; text-transform: uppercase; letter-spacing: 1px;
        color: rgba(0,0,0,.4); font-weight: 600; margin-bottom: .5rem;
        padding-bottom: .25rem; border-bottom: 1px solid rgba(0,0,0,.06);
    }
    .cal-job-card {
        background: #f9f8f5; border-radius: .375rem; padding: .5rem .75rem;
        margin-bottom: .35rem; cursor: pointer; transition: all .12s;
        border-left: 3px solid transparent;
    }
    .cal-job-card:hover { background: rgba(201,168,76,.06); }
    .cal-job-card .jc-title { font-size: .82rem; font-weight: 600; color: #111; }
    .cal-job-card .jc-meta { font-size: .72rem; color: #888; margin-top: 1px; }
    .cal-job-card.status-pending { border-left-color: #ffc107; }
    .cal-job-card.status-scheduled { border-left-color: #17a2b8; }
    .cal-job-card.status-in_progress { border-left-color: #007bff; }
    .cal-job-card.status-completed { border-left-color: #28a745; }
    .cal-job-card.booking-card { border-left-color: #6f42c1; }
    .cal-job-card.booking-confirmed { border-left-color: #17a2b8; }

    /* ── Calendar Grid ──────────────────────── */
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
        cursor: pointer; transition: background .1s;
        position: relative;
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
    .cal-cell .cell-jobs { margin-top: .25rem; }
    .cal-cell .cell-job {
        font-size: .65rem; padding: 1px 4px; border-radius: 2px; margin-bottom: 2px;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        display: block;
    }
    .cell-job.s-pending { background: rgba(255,193,7,.2); color: #856404; }
    .cell-job.s-scheduled { background: rgba(23,162,184,.15); color: #0c5460; }
    .cell-job.s-in_progress { background: rgba(0,123,255,.15); color: #004085; }
    .cell-job.s-completed { background: rgba(40,167,69,.15); color: #155724; }
    .cell-job.s-booking { background: rgba(111,66,193,.15); color: #4a1d96; }
    .cell-job.s-booking-confirmed { background: rgba(23,162,184,.15); color: #0c5460; }
    .cal-cell .cell-more { font-size: .6rem; color: var(--vip-accent); font-weight: 600; margin-top: 1px; }
    .cell-job.s-event { font-size: .65rem; }

    /* Availability modal */
    .avail-day-row { display: flex; align-items: center; gap: .75rem; padding: .5rem 0; border-bottom: 1px solid rgba(0,0,0,.05); }
    .avail-day-row .day-label { width: 100px; font-weight: 600; font-size: .85rem; }
    .avail-day-row .form-control, .avail-day-row .form-select { font-size: .82rem; padding: .3rem .5rem; }

    @media (max-width: 991.98px) {
        .cal-container { flex-direction: column; height: auto; }
        .cal-sidebar { width: 100%; min-width: 100%; max-height: 35vh; }
        .cal-cell { min-height: 60px; }
    }
</style>
@endpush

@section('content')
@php
    $today = \Carbon\Carbon::today();
    $monthStart = $startOfMonth->copy()->startOfWeek(\Carbon\Carbon::SUNDAY);
    $monthEnd = $endOfMonth->copy()->endOfWeek(\Carbon\Carbon::SATURDAY);
    $prevMonth = $startOfMonth->copy()->subMonth();
    $nextMonth = $startOfMonth->copy()->addMonth();
    $monthLabel = $startOfMonth->format('F Y');
    $dayNames = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
@endphp

<div class="cal-container">
    {{-- Sidebar --}}
    <div class="cal-sidebar">
        <div class="cal-sidebar-header">
            <h6>{{ $monthLabel }} {{ __('installer.overview') }}</h6>
            <div class="cal-stat">
                <span><span class="dot" style="background:#333;"></span> {{ __('installer.total_jobs') }}</span>
                <span class="count">{{ $totalMonth }}</span>
            </div>
            @if($pendingBookings)
            <div class="cal-stat">
                <span><span class="dot" style="background:#6f42c1;"></span> Pending Bookings</span>
                <span class="count" style="color:#6f42c1;">{{ $pendingBookings }}</span>
            </div>
            @endif
            <div class="cal-stat">
                <span><span class="dot" style="background:#ffc107;"></span> {{ __('installer.pending') }}</span>
                <span class="count" style="color:#ffc107;">{{ $pending }}</span>
            </div>
            <div class="cal-stat">
                <span><span class="dot" style="background:#17a2b8;"></span> {{ __('installer.scheduled') }}</span>
                <span class="count" style="color:#17a2b8;">{{ $scheduled }}</span>
            </div>
            <div class="cal-stat">
                <span><span class="dot" style="background:#007bff;"></span> {{ __('installer.in_progress') }}</span>
                <span class="count" style="color:#007bff;">{{ $inProgress }}</span>
            </div>
            <div class="cal-stat">
                <span><span class="dot" style="background:#28a745;"></span> {{ __('installer.completed') }}</span>
                <span class="count" style="color:#28a745;">{{ $completed }}</span>
            </div>

            <div class="mt-3">
                <button class="btn btn-sm btn-outline-dark w-100" data-bs-toggle="modal" data-bs-target="#availabilityModal">
                    <i class="bi bi-gear me-1"></i> Manage Availability
                </button>
            </div>
        </div>

        <div class="cal-day-list">
            {{-- Pending Bookings --}}
            @if($bookings->where('status', 'pending')->count())
                <div class="cal-day-section">
                    <div class="day-header"><i class="bi bi-bell me-1"></i> Pending Booking Requests</div>
                    @foreach($bookings->where('status', 'pending') as $bk)
                        <div class="cal-job-card booking-card" onclick="showBooking({{ $bk->id }})" data-booking='@json($bk)'>
                            <div class="jc-title">{{ $bk->customer_name }}</div>
                            <div class="jc-meta">
                                <i class="bi bi-calendar me-1"></i>{{ $bk->booking_date->format('M d') }}
                                @ {{ date('g:i A', strtotime($bk->booking_time)) }}
                                &middot; {{ $bk->service_type }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Upcoming Jobs --}}
            @php
                $upcomingJobs = $jobs->where('scheduled_date', '>=', $today)->where('status', '!=', 'completed')->sortBy('scheduled_date')->take(10);
            @endphp
            @if($upcomingJobs->count())
                <div class="cal-day-section">
                    <div class="day-header">{{ __('installer.upcoming_jobs') }}</div>
                    @foreach($upcomingJobs as $uj)
                        <div class="cal-job-card status-{{ $uj->status }}" onclick="showJobPopup({{ $uj->id }})">
                            <div class="jc-title">{{ $uj->job_number }}</div>
                            <div class="jc-meta">
                                <i class="bi bi-person me-1"></i>{{ $uj->customer_name ?? '—' }}
                                &middot; {{ $uj->scheduled_date->format('M d') }}
                                @if($uj->scheduled_time) @ {{ $uj->scheduled_time }} @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Upcoming Events (admin-scheduled) --}}
            @php
                $upcomingEvents = $calendarEvents->where('event_date', '>=', $today)->sortBy('event_date')->take(10);
            @endphp
            @if($upcomingEvents->count())
                <div class="cal-day-section">
                    <div class="day-header"><i class="bi bi-calendar-event me-1"></i> Upcoming Events</div>
                    @foreach($upcomingEvents as $ue)
                        <div class="cal-job-card" style="border-left-color: {{ $ue->color ?? '#c9a84c' }};" onclick="showEventPopup({{ $ue->id }})">
                            <div class="jc-title">{{ $ue->title }}</div>
                            <div class="jc-meta">
                                <i class="bi bi-person me-1"></i>{{ $ue->customer_name ?? '—' }}
                                &middot; {{ $ue->event_date->format('M d') }}
                                @if($ue->event_time) @ {{ $ue->event_time }} @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            @if(!$upcomingJobs->count() && !$upcomingEvents->count() && !$bookings->where('status', 'pending')->count())
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-calendar-check" style="font-size:2rem; opacity:.3;"></i>
                    <p class="mt-2 small">{{ __('installer.no_upcoming_jobs') }}</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Main Calendar --}}
    <div class="cal-main">
        <div class="cal-toolbar">
            <div class="nav-btns">
                <a href="{{ route('installer.calendar', ['month' => $prevMonth->month, 'year' => $prevMonth->year]) }}" title="Previous"><i class="bi bi-chevron-left"></i></a>
                <a href="{{ route('installer.calendar') }}" title="{{ __('installer.today') }}" style="width:auto; padding:0 10px; font-size:.75rem; font-weight:600;">{{ __('installer.today') }}</a>
                <a href="{{ route('installer.calendar', ['month' => $nextMonth->month, 'year' => $nextMonth->year]) }}" title="Next"><i class="bi bi-chevron-right"></i></a>
            </div>
            <h5>{{ $monthLabel }}</h5>
            <a href="{{ route('installer.jobs.index') }}" class="btn btn-sm btn-vip"><i class="bi bi-list-ul me-1"></i>{{ __('installer.all_jobs') }}</a>
        </div>

        <div class="cal-grid-wrap">
            <div class="cal-grid">
                @foreach([__('installer.sun'),__('installer.mon'),__('installer.tue'),__('installer.wed'),__('installer.thu'),__('installer.fri'),__('installer.sat')] as $dn)
                    <div class="day-name">{{ $dn }}</div>
                @endforeach

                @php $current = $monthStart->copy(); @endphp
                @while($current <= $monthEnd)
                    @php
                        $dateKey = $current->format('Y-m-d');
                        $isToday = $current->isSameDay($today);
                        $isOtherMonth = $current->month !== (int)$month;
                        $dayJobs = $jobsByDate->get($dateKey, collect());
                        $dayEvents = $eventsByDate->get($dateKey, collect());
                        $dayBookings = $bookingsByDate->get($dateKey, collect());
                        $maxShow = 3;
                        $allItems = collect();
                        foreach($dayJobs as $dj) { $allItems->push(['type' => 'job', 'item' => $dj]); }
                        foreach($dayEvents as $de) { $allItems->push(['type' => 'event', 'item' => $de]); }
                        foreach($dayBookings as $db) { $allItems->push(['type' => 'booking', 'item' => $db]); }
                    @endphp
                    <div class="cal-cell {{ $isToday ? 'today' : '' }} {{ $isOtherMonth ? 'other-month' : '' }}">
                        <span class="cell-date">{{ $current->day }}</span>
                        <div class="cell-jobs">
                            @foreach($allItems->take($maxShow) as $entry)
                                @if($entry['type'] === 'job')
                                    <span class="cell-job s-{{ $entry['item']->status }}" onclick="event.stopPropagation(); showJobPopup({{ $entry['item']->id }})" style="cursor:pointer;">
                                        @if($entry['item']->status === 'completed')
                                            <i class="bi bi-check-circle-fill text-success" style="font-size:.55rem;"></i>
                                        @elseif($entry['item']->rescheduled_at)
                                            <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size:.55rem;"></i>
                                        @endif
                                        {{ $entry['item']->customer_name ? substr($entry['item']->customer_name, 0, 12) : $entry['item']->job_number }}
                                    </span>
                                @elseif($entry['type'] === 'event')
                                    <span class="cell-job s-event" onclick="event.stopPropagation(); showEventPopup({{ $entry['item']->id }})" style="cursor:pointer; background:{{ $entry['item']->color ?? '#c9a84c' }}22; color:{{ $entry['item']->color ?? '#c9a84c' }}; border-left: 2px solid {{ $entry['item']->color ?? '#c9a84c' }};">
                                        @if(($entry['item']->event_status ?? '') === 'completed')
                                            <i class="bi bi-check-circle-fill text-success" style="font-size:.55rem;"></i>
                                        @elseif($entry['item']->rescheduled_at)
                                            <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size:.55rem;"></i>
                                        @else
                                            <i class="bi bi-calendar-event" style="font-size:.55rem;"></i>
                                        @endif
                                        {{ substr($entry['item']->title, 0, 10) }}
                                    </span>
                                @else
                                    <span class="cell-job s-booking{{ $entry['item']->status === 'confirmed' ? '-confirmed' : '' }}">
                                        <i class="bi bi-bookmark-fill" style="font-size:.55rem;"></i> {{ substr($entry['item']->customer_name, 0, 10) }}
                                    </span>
                                @endif
                            @endforeach
                            @if($allItems->count() > $maxShow)
                                <span class="cell-more">+{{ $allItems->count() - $maxShow }} {{ __('installer.more') }}</span>
                            @endif
                        </div>
                    </div>
                    @php $current->addDay(); @endphp
                @endwhile
            </div>
        </div>
    </div>
</div>

{{-- Availability Settings Modal --}}
<div class="modal fade" id="availabilityModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-gear me-2"></i>Availability Settings</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">Set your weekly schedule. Customers will only be able to book during your available hours. If you don't set availability, you're assumed open Mon–Fri 8 AM–5 PM with up to 5 bookings per hour slot.</p>

                <form id="availabilityForm">
                    @foreach($dayNames as $idx => $dayName)
                        @php
                            $dayAvail = $availability->get($idx);
                            $isOn = $dayAvail ? $dayAvail->is_available : ($idx >= 1 && $idx <= 5); // default Mon-Fri on
                            $startT = $dayAvail ? substr($dayAvail->start_time, 0, 5) : '08:00';
                            $endT = $dayAvail ? substr($dayAvail->end_time, 0, 5) : '17:00';
                            $slotDur = $dayAvail ? $dayAvail->slot_duration : 60;
                            $maxBook = $dayAvail ? $dayAvail->max_bookings_per_slot : 5;
                        @endphp
                        <div class="avail-day-row">
                            <div class="day-label">{{ $dayName }}</div>
                            <div class="form-check form-switch">
                                <input class="form-check-input avail-toggle" type="checkbox" id="dayOn{{ $idx }}"
                                       data-day="{{ $idx }}" {{ $isOn ? 'checked' : '' }}
                                       onchange="toggleDayFields({{ $idx }})">
                            </div>
                            <div class="day-fields day-fields-{{ $idx }}" style="{{ !$isOn ? 'opacity:.3;pointer-events:none;' : '' }}">
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
                                        <input type="number" class="form-control" id="dayMax{{ $idx }}" value="{{ $maxBook }}" min="1" max="50" title="Max bookings per slot">
                                        <span class="input-group-text" style="font-size:.7rem;">max</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-vip" onclick="saveAvailability()">
                    <i class="bi bi-check2 me-1"></i> Save Availability
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Job Detail Popup --}}
<div class="modal fade" id="jobPopupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title mb-0" id="jobPopupTitle"><i class="bi bi-tools me-1"></i> Job</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="jobPopupBody">
                <div class="text-center py-4"><div class="spinner-border spinner-border-sm text-muted"></div></div>
            </div>
            <div class="modal-footer py-2" id="jobPopupFooter"></div>
        </div>
    </div>
</div>

{{-- Event Detail Popup --}}
<div class="modal fade" id="eventPopupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title mb-0" id="eventPopupTitle"><i class="bi bi-calendar-event me-1"></i> Event</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="eventPopupBody">
                <div class="text-center py-4"><div class="spinner-border spinner-border-sm text-muted"></div></div>
            </div>
            <div class="modal-footer py-2" id="eventPopupFooter"></div>
        </div>
    </div>
</div>

{{-- Reschedule Modal --}}
<div class="modal fade" id="rescheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title mb-0"><i class="bi bi-calendar2-week me-1"></i> Reschedule</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="rescheduleType" value="">
                <input type="hidden" id="rescheduleId" value="">
                <p class="text-muted small mb-3">Submit a reschedule request with a reason. The admin will assign a new date and time.</p>
                <div class="mb-0">
                    <label class="form-label small fw-semibold">Reason for Rescheduling <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="rescheduleReason" rows="3" placeholder="e.g. Customer not available, weather delay, materials not ready..."></textarea>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-vip btn-sm" onclick="submitReschedule()">
                    <i class="bi bi-check2 me-1"></i> Confirm Reschedule
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Booking Detail Modal --}}
<div class="modal fade" id="bookingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-bookmark me-2"></i>Booking Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="bookingModalBody"></div>
            <div class="modal-footer" id="bookingModalFooter"></div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// ── Job data for popup ──
@php
    $jobsJson = $jobs->map(function($j) {
        return [
            'id' => $j->id,
            'job_number' => $j->job_number,
            'customer_name' => $j->customer_name,
            'customer_phone' => $j->customer_phone,
            'customer_email' => $j->customer_email,
            'install_address' => $j->install_address,
            'install_city' => $j->install_city,
            'install_state' => $j->install_state,
            'install_zip' => $j->install_zip,
            'scheduled_date' => $j->scheduled_date?->format('l, M d, Y'),
            'scheduled_date_raw' => $j->scheduled_date?->format('Y-m-d'),
            'scheduled_time' => $j->scheduled_time,
            'status' => $j->status,
            'service_name' => $j->service?->name ?? '—',
            'description' => $j->description,
            'is_rescheduled' => (bool) $j->rescheduled_at,
            'reschedule_reason' => $j->reschedule_reason,
        ];
    })->keyBy('id');

    $eventsJson = $calendarEvents->map(function($e) {
        return [
            'id' => $e->id,
            'title' => $e->title,
            'description' => $e->description,
            'customer_name' => $e->customer_name,
            'customer_email' => $e->customer_email,
            'customer_phone' => $e->customer_phone,
            'address' => $e->address,
            'event_date' => $e->event_date?->format('l, M d, Y'),
            'event_date_raw' => $e->event_date?->format('Y-m-d'),
            'event_time' => $e->event_time,
            'end_time' => $e->end_time,
            'color' => $e->color ?? '#c9a84c',
            'crew_name' => $e->crew?->name ?? '—',
            'service_name' => $e->service?->name ?? null,
            'event_status' => $e->event_status ?? 'scheduled',
            'is_rescheduled' => (bool) $e->rescheduled_at,
            'reschedule_reason' => $e->reschedule_reason,
        ];
    })->keyBy('id');
@endphp
const jobsData = @json($jobsJson);
const eventsData = @json($eventsJson);

function showJobPopup(jobId) {
    const job = jobsData[jobId];
    if (!job) return;

    document.getElementById('jobPopupTitle').innerHTML = `<i class="bi bi-tools me-1"></i> ${job.job_number}`;

    const statusColors = {
        pending: 'bg-warning text-dark', scheduled: 'bg-info',
        in_progress: 'bg-primary', completed: 'bg-success', cancelled: 'bg-dark'
    };
    const statusBadge = statusColors[job.status] || 'bg-secondary';
    const statusLabel = job.status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());

    // Build full address for Google Maps (use comgooglemaps:// on iOS, fallback to web)
    const addressParts = [job.install_address, job.install_city, job.install_state, job.install_zip].filter(Boolean);
    const fullAddress = addressParts.join(', ');
    const encodedAddr = encodeURIComponent(fullAddress);
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
    const isAndroid = /Android/.test(navigator.userAgent);
    let mapsUrl = '';
    if (fullAddress) {
        if (isIOS) {
            mapsUrl = `comgooglemaps://?daddr=${encodedAddr}&directionsmode=driving`;
        } else if (isAndroid) {
            mapsUrl = `google.navigation:q=${encodedAddr}`;
        } else {
            mapsUrl = `https://www.google.com/maps/dir/?api=1&destination=${encodedAddr}`;
        }
    }
    // Fallback URL for when the app isn't installed
    const mapsWebUrl = fullAddress ? `https://www.google.com/maps/dir/?api=1&destination=${encodedAddr}` : '';

    // Determine if it's a tech measure (by service name)
    const isTechMeasure = (job.service_name || '').toLowerCase().includes('measure');
    const detailUrl = isTechMeasure
        ? `/installer/tech-measures?job=${jobId}`
        : `/installer/jobs/${jobId}`;
    const detailLabel = isTechMeasure ? 'Tech Measure' : 'Job Details';

    document.getElementById('jobPopupBody').innerHTML = `
        ${job.is_rescheduled ? `<div class="alert alert-warning py-2 px-3 mb-3" style="font-size:.82rem;"><i class="bi bi-send-check me-1"></i> <strong>Reschedule Request Sent to Admin</strong>${job.reschedule_reason ? ': ' + job.reschedule_reason : ''}</div>` : ''}
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <h6 class="fw-bold mb-1">${job.customer_name || 'No Customer'}</h6>
                ${job.customer_phone ? `<div class="small text-muted"><i class="bi bi-telephone me-1"></i><a href="tel:${job.customer_phone}">${job.customer_phone}</a></div>` : ''}
                ${job.customer_email ? `<div class="small text-muted"><i class="bi bi-envelope me-1"></i>${job.customer_email}</div>` : ''}
            </div>
            <span class="badge ${statusBadge}">${statusLabel}</span>
        </div>

        <div class="row g-2 mb-3">
            <div class="col-6">
                <div class="small text-muted">Service</div>
                <div class="fw-semibold">${job.service_name}</div>
            </div>
            <div class="col-6">
                <div class="small text-muted">Date & Time</div>
                <div class="fw-semibold">${job.scheduled_date || '—'}${job.scheduled_time ? ' @ ' + job.scheduled_time : ''}</div>
            </div>
        </div>

        ${fullAddress ? `
        <div class="mb-3">
            <div class="small text-muted">Address</div>
            <div class="fw-semibold">${fullAddress}</div>
        </div>` : ''}

        ${job.description ? `
        <div class="mb-3">
            <div class="small text-muted">Description</div>
            <div class="small">${job.description}</div>
        </div>` : ''}
    `;

    // Footer buttons — progressive flow: Start Route → Arrived → Start Job
    let footerHtml = '';
    let routeRow = '';
    let actionRow = '';

    const jobStatus = job.status;

    if (jobStatus === 'in_progress') {
        // Already started — show Start Job / View Job button + Arrived
        routeRow = `
            <a href="${detailUrl}" class="btn btn-success flex-fill">
                <i class="bi bi-play-circle me-1"></i> ${detailLabel}
            </a>
        `;
    } else if (jobStatus === 'completed') {
        routeRow = `
            <a href="${detailUrl}" class="btn btn-outline-success flex-fill">
                <i class="bi bi-check-circle me-1"></i> View Completed Job
            </a>
        `;
    } else {
        // Pending or scheduled — show all 3 buttons, toggle visibility via state
        const routeState = (jobsData[jobId] && jobsData[jobId]._routeState) ? jobsData[jobId]._routeState : 'idle';

        // Store mapsUrl on the data object so onclick can read it without quoting issues
        if (jobsData[jobId]) {
            jobsData[jobId]._mapsUrl = mapsUrl;
            jobsData[jobId]._mapsWebUrl = mapsWebUrl;
        }

        routeRow += `
            <button class="btn btn-vip flex-fill" id="startRouteBtn_${jobId}"
                    style="${routeState !== 'idle' || !fullAddress ? 'display:none;' : ''}"
                    onclick="onRouteStarted(${jobId})">
                <i class="bi bi-geo-alt-fill me-1"></i> Start Route
            </button>
        `;
        routeRow += `
            <button class="btn btn-danger flex-fill" id="arrivedBtn_${jobId}"
                    style="${routeState === 'routed' ? '' : 'display:none;'}"
                    onclick="onArrivedAtLocation(${jobId})">
                <i class="bi bi-pin-map-fill me-1"></i> Arrived at Location
            </button>
        `;
        routeRow += `
            <button class="btn btn-success flex-fill" id="startJobBtn_${jobId}"
                    style="${routeState === 'arrived' ? '' : 'display:none;'}"
                    onclick="onStartJob(${jobId}, ${isTechMeasure ? 'true' : 'false'})">
                <i class="bi bi-play-circle me-1"></i> Start Job
            </button>
        `;
    }

    // Action buttons row: Call, Send Reminder, Reschedule
    if (job.customer_phone) {
        actionRow += `<a href="tel:${job.customer_phone}" class="btn btn-outline-secondary btn-sm flex-fill"><i class="bi bi-telephone me-1"></i>Call</a>`;
    }
    if (job.customer_email) {
        actionRow += `<button class="btn btn-outline-warning btn-sm flex-fill" onclick="sendJobReminder(${jobId})"><i class="bi bi-bell me-1"></i>Reminder</button>`;
    }
    actionRow += `<button class="btn btn-outline-info btn-sm flex-fill" onclick="openReschedule('job', ${jobId})"><i class="bi bi-calendar2-week me-1"></i>Reschedule</button>`;

    document.getElementById('jobPopupFooter').innerHTML = `
        <div class="w-100">
            <div class="d-flex gap-2 mb-2">${routeRow}</div>
            <div class="d-flex gap-2">${actionRow}</div>
        </div>
    `;

    new bootstrap.Modal(document.getElementById('jobPopupModal')).show();
}

function onRouteStarted(jobId) {
    // Track state FIRST so the buttons swap immediately
    jobsData[jobId]._routeState = 'routed';
    const routeBtn = document.getElementById('startRouteBtn_' + jobId);
    const arrivedBtn = document.getElementById('arrivedBtn_' + jobId);
    if (routeBtn) routeBtn.style.display = 'none';
    if (arrivedBtn) arrivedBtn.style.display = '';

    // Try to open Google Maps app, fall back to web
    const appUrl = jobsData[jobId]._mapsUrl;
    const webUrl = jobsData[jobId]._mapsWebUrl;

    if (appUrl && appUrl.startsWith('http')) {
        // Desktop — just open in new tab
        window.open(appUrl, '_blank');
    } else if (appUrl) {
        // iOS/Android — try app scheme, fall back to web after timeout
        window.location.href = appUrl;
        setTimeout(() => {
            // If we're still here after 1.5s, the app didn't open — use web fallback
            if (webUrl) window.open(webUrl, '_blank');
        }, 1500);
    } else if (webUrl) {
        window.open(webUrl, '_blank');
    }
}

function onArrivedAtLocation(jobId) {
    // Track state and swap buttons
    jobsData[jobId]._routeState = 'arrived';
    const arrivedBtn = document.getElementById('arrivedBtn_' + jobId);
    const startJobBtn = document.getElementById('startJobBtn_' + jobId);
    if (arrivedBtn) arrivedBtn.style.display = 'none';
    if (startJobBtn) startJobBtn.style.display = '';
}

function onStartJob(jobId, isTechMeasure) {
    // Clock in to the job (starts attendance tracking for crew)
    fetch(`/installer/jobs/${jobId}/clock-in`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
    })
    .then(r => r.json())
    .then(data => {
        // Update local data so reopening popup shows correct state
        if (jobsData[jobId]) jobsData[jobId].status = 'in_progress';

        // Redirect to job detail page
        if (isTechMeasure) {
            window.location.href = '/installer/tech-measures?job=' + jobId;
        } else {
            window.location.href = '/installer/jobs/' + jobId;
        }
    })
    .catch(() => {
        // Still redirect even if clock-in fails
        if (isTechMeasure) {
            window.location.href = '/installer/tech-measures?job=' + jobId;
        } else {
            window.location.href = '/installer/jobs/' + jobId;
        }
    });
}

function openEventRoute(eventId) {
    const appUrl = eventsData[eventId]._mapsUrl;
    const webUrl = eventsData[eventId]._mapsWebUrl;

    if (appUrl && appUrl.startsWith('http')) {
        window.open(appUrl, '_blank');
    } else if (appUrl) {
        window.location.href = appUrl;
        setTimeout(() => {
            if (webUrl) window.open(webUrl, '_blank');
        }, 1500);
    } else if (webUrl) {
        window.open(webUrl, '_blank');
    }
}

// ── Event Popup ──
function showEventPopup(eventId) {
    const ev = eventsData[eventId];
    if (!ev) return;

    document.getElementById('eventPopupTitle').innerHTML = `<i class="bi bi-calendar-event me-1" style="color:${ev.color}"></i> ${ev.title}`;

    let bodyHtml = `
        ${ev.is_rescheduled ? `<div class="alert alert-warning py-2 px-3 mb-3" style="font-size:.82rem;"><i class="bi bi-send-check me-1"></i> <strong>Reschedule Request Sent to Admin</strong>${ev.reschedule_reason ? ': ' + ev.reschedule_reason : ''}</div>` : ''}
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <h6 class="fw-bold mb-1">${ev.customer_name || 'No Customer'}</h6>
                ${ev.customer_phone ? `<div class="small text-muted"><i class="bi bi-telephone me-1"></i><a href="tel:${ev.customer_phone}">${ev.customer_phone}</a></div>` : ''}
                ${ev.customer_email ? `<div class="small text-muted"><i class="bi bi-envelope me-1"></i>${ev.customer_email}</div>` : ''}
            </div>
            ${ev.service_name ? `<span class="badge" style="background:${ev.color}; color:#fff;">${ev.service_name}</span>` : ''}
        </div>

        <div class="row g-2 mb-3">
            <div class="col-6">
                <div class="small text-muted">Date</div>
                <div class="fw-semibold">${ev.event_date || '—'}</div>
            </div>
            <div class="col-6">
                <div class="small text-muted">Time</div>
                <div class="fw-semibold">${ev.event_time || '—'}${ev.end_time ? ' – ' + ev.end_time : ''}</div>
            </div>
        </div>

        ${ev.address ? `<div class="mb-3"><div class="small text-muted">Address</div><div class="fw-semibold">${ev.address}</div></div>` : ''}
        ${ev.crew_name && ev.crew_name !== '—' ? `<div class="mb-3"><div class="small text-muted">Crew</div><div class="fw-semibold">${ev.crew_name}</div></div>` : ''}
        ${ev.description ? `<div class="mb-3"><div class="small text-muted">Description</div><div class="small">${ev.description}</div></div>` : ''}
    `;

    document.getElementById('eventPopupBody').innerHTML = bodyHtml;

    // Footer: Start Route + Call, Send Reminder, Reschedule
    let routeRow = '';
    let actionRow = '';

    if (ev.address) {
        const evEncodedAddr = encodeURIComponent(ev.address);
        const evIsIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
        const evIsAndroid = /Android/.test(navigator.userAgent);
        let evMapsUrl;
        if (evIsIOS) {
            evMapsUrl = `comgooglemaps://?daddr=${evEncodedAddr}&directionsmode=driving`;
        } else if (evIsAndroid) {
            evMapsUrl = `google.navigation:q=${evEncodedAddr}`;
        } else {
            evMapsUrl = `https://www.google.com/maps/dir/?api=1&destination=${evEncodedAddr}`;
        }
        const evWebUrl = `https://www.google.com/maps/dir/?api=1&destination=${evEncodedAddr}`;

        // Store on event data for onclick
        eventsData[eventId]._mapsUrl = evMapsUrl;
        eventsData[eventId]._mapsWebUrl = evWebUrl;

        routeRow = `<button class="btn btn-vip flex-fill" onclick="openEventRoute(${eventId})"><i class="bi bi-geo-alt-fill me-1"></i> Start Route</button>`;
    }

    if (ev.customer_phone) {
        actionRow += `<a href="tel:${ev.customer_phone}" class="btn btn-outline-secondary btn-sm flex-fill"><i class="bi bi-telephone me-1"></i>Call</a>`;
    }
    if (ev.customer_email) {
        actionRow += `<button class="btn btn-outline-warning btn-sm flex-fill" onclick="sendEventReminder(${eventId})"><i class="bi bi-bell me-1"></i>Reminder</button>`;
    }
    actionRow += `<button class="btn btn-outline-info btn-sm flex-fill" onclick="openReschedule('event', ${eventId})"><i class="bi bi-calendar2-week me-1"></i>Reschedule</button>`;

    document.getElementById('eventPopupFooter').innerHTML = `
        <div class="w-100">
            ${routeRow ? `<div class="d-flex gap-2 mb-2">${routeRow}</div>` : ''}
            <div class="d-flex gap-2">${actionRow}</div>
        </div>
    `;

    new bootstrap.Modal(document.getElementById('eventPopupModal')).show();
}

// ── Send Reminder (Job) ──
function sendJobReminder(jobId) {
    const job = jobsData[jobId];
    if (!job || !job.customer_email) { alert('No customer email on this job.'); return; }
    if (!confirm('Send a reminder email to ' + job.customer_email + '?')) return;

    const btn = event.target.closest('button');
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Sending...'; }

    fetch(`/installer/jobs/${jobId}/send-reminder`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' },
    })
    .then(r => r.json())
    .then(data => {
        if (btn) { btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Sent!'; btn.classList.replace('btn-outline-warning', 'btn-success'); }
        setTimeout(() => { if (btn) { btn.innerHTML = '<i class="bi bi-bell me-1"></i>Reminder'; btn.classList.replace('btn-success', 'btn-outline-warning'); btn.disabled = false; } }, 2000);
    })
    .catch(() => { alert('Failed to send reminder.'); if (btn) { btn.innerHTML = '<i class="bi bi-bell me-1"></i>Reminder'; btn.disabled = false; } });
}

// ── Send Reminder (Event) ──
function sendEventReminder(eventId) {
    const ev = eventsData[eventId];
    if (!ev || !ev.customer_email) { alert('No customer email on this event.'); return; }
    if (!confirm('Send a reminder email to ' + ev.customer_email + '?')) return;

    const btn = event.target.closest('button');
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Sending...'; }

    fetch(`/admin/calendar/event/${eventId}/reminder`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            if (btn) { btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Sent!'; btn.classList.replace('btn-outline-warning', 'btn-success'); }
            setTimeout(() => { if (btn) { btn.innerHTML = '<i class="bi bi-bell me-1"></i>Reminder'; btn.classList.replace('btn-success', 'btn-outline-warning'); btn.disabled = false; } }, 2000);
        } else {
            alert(data.error || 'Failed to send.');
            if (btn) { btn.innerHTML = '<i class="bi bi-bell me-1"></i>Reminder'; btn.disabled = false; }
        }
    })
    .catch(() => { alert('Failed to send reminder.'); if (btn) { btn.innerHTML = '<i class="bi bi-bell me-1"></i>Reminder'; btn.disabled = false; } });
}

// ── Reschedule ──
function openReschedule(type, id) {
    // Close the current popup
    bootstrap.Modal.getInstance(document.getElementById(type === 'event' ? 'eventPopupModal' : 'jobPopupModal'))?.hide();

    document.getElementById('rescheduleType').value = type;
    document.getElementById('rescheduleId').value = id;
    document.getElementById('rescheduleReason').value = '';

    setTimeout(() => {
        new bootstrap.Modal(document.getElementById('rescheduleModal')).show();
    }, 300);
}

function submitReschedule() {
    const type = document.getElementById('rescheduleType').value;
    const id = document.getElementById('rescheduleId').value;
    const reason = document.getElementById('rescheduleReason').value.trim();

    if (!reason) { alert('Please provide a reason for rescheduling.'); return; }

    const url = type === 'event'
        ? `/installer/calendar-events/${id}/reschedule`
        : `/installer/jobs/${id}/reschedule`;

    fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({ reason: reason }),
    })
    .then(r => {
        if (!r.ok) return r.json().then(d => { throw new Error(d.error || d.message || `HTTP ${r.status}`); });
        return r.json();
    })
    .then(data => {
        bootstrap.Modal.getInstance(document.getElementById('rescheduleModal'))?.hide();
        // Show confirmation then reload
        alert('Reschedule request sent to admin.');
        location.reload();
    })
    .catch(e => alert('Failed to reschedule: ' + e.message));
}

function toggleDayFields(day) {
    const isOn = document.getElementById('dayOn' + day).checked;
    const fields = document.querySelector('.day-fields-' + day);
    fields.style.opacity = isOn ? '1' : '.3';
    fields.style.pointerEvents = isOn ? 'auto' : 'none';
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

    fetch('{{ route("installer.availability.weekly") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ days })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('availabilityModal')).hide();
            location.reload();
        }
    })
    .catch(e => alert('Error saving availability.'));
}

function showBooking(id) {
    const card = document.querySelector(`[data-booking]`);
    const allCards = document.querySelectorAll('[data-booking]');
    let booking = null;

    allCards.forEach(c => {
        const b = JSON.parse(c.dataset.booking);
        if (b.id === id) booking = b;
    });

    if (!booking) return;

    const body = document.getElementById('bookingModalBody');
    const footer = document.getElementById('bookingModalFooter');
    const dateStr = new Date(booking.booking_date).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });

    body.innerHTML = `
        <div class="mb-3">
            <strong class="d-block">${booking.customer_name}</strong>
            ${booking.customer_email ? `<small class="text-muted"><i class="bi bi-envelope me-1"></i>${booking.customer_email}</small><br>` : ''}
            ${booking.customer_phone ? `<small class="text-muted"><i class="bi bi-telephone me-1"></i>${booking.customer_phone}</small>` : ''}
        </div>
        <div class="row g-2 mb-3">
            <div class="col-6">
                <div class="small text-muted">Date</div>
                <div class="fw-semibold">${dateStr}</div>
            </div>
            <div class="col-6">
                <div class="small text-muted">Time</div>
                <div class="fw-semibold">${booking.booking_time}</div>
            </div>
        </div>
        <div class="mb-3">
            <div class="small text-muted">Service</div>
            <div class="fw-semibold">${booking.service_type || '—'}</div>
        </div>
        ${booking.install_address ? `
        <div class="mb-3">
            <div class="small text-muted">Address</div>
            <div>${booking.install_address}${booking.install_city ? ', ' + booking.install_city : ''}${booking.install_state ? ' ' + booking.install_state : ''} ${booking.install_zip || ''}</div>
        </div>` : ''}
        ${booking.description ? `
        <div class="mb-3">
            <div class="small text-muted">Details</div>
            <div>${booking.description}</div>
        </div>` : ''}
    `;

    footer.innerHTML = booking.status === 'pending' ? `
        <button class="btn btn-danger btn-sm" onclick="updateBookingStatus(${booking.id}, 'cancelled')">
            <i class="bi bi-x-circle me-1"></i> Decline
        </button>
        <button class="btn btn-vip" onclick="updateBookingStatus(${booking.id}, 'confirmed')">
            <i class="bi bi-check-circle me-1"></i> Confirm Booking
        </button>
    ` : `
        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    `;

    new bootstrap.Modal(document.getElementById('bookingModal')).show();
}

function updateBookingStatus(id, status) {
    fetch(`/installer/bookings/${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ status })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('bookingModal')).hide();
            location.reload();
        }
    });
}
</script>
@endpush
@endsection
