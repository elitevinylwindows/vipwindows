@extends('layouts.installer')
@section('title', __('installer.calendar'))

@push('styles')
<style>
    .cal-container { display: flex; height: calc(100vh - 56px); overflow: hidden; }

    /* ── Sidebar Stats ──────────────────────── */
    .cal-sidebar {
        width: 280px; min-width: 280px;
        background: #fff; border-right: 1px solid rgba(0,0,0,.08);
        display: flex; flex-direction: column; overflow-y: auto;
    }
    .cal-sidebar-header {
        padding: 1.25rem 1.25rem .5rem;
        border-bottom: 1px solid rgba(0,0,0,.06);
    }
    .cal-sidebar-header h6 { font-size: .75rem; text-transform: uppercase; letter-spacing: 1.2px; color: rgba(0,0,0,.4); margin-bottom: .5rem; }
    .cal-stat { display: flex; justify-content: space-between; align-items: center; padding: .4rem 0; font-size: .85rem; }
    .cal-stat .dot { width: 10px; height: 10px; border-radius: 50%; margin-right: .5rem; }
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
    .cal-cell .cell-more { font-size: .6rem; color: var(--vip-accent); font-weight: 600; margin-top: 1px; }

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
@endphp

<div class="cal-container">
    {{-- Sidebar --}}
    <div class="cal-sidebar">
        <div class="cal-sidebar-header">
            <h6>{{ $monthLabel }} {{ __('installer.overview') }}</h6>
            <div class="cal-stat">
                <span><span class="dot" style="background:#333; display:inline-block;"></span> {{ __('installer.total_jobs') }}</span>
                <span class="count">{{ $totalMonth }}</span>
            </div>
            <div class="cal-stat">
                <span><span class="dot" style="background:#ffc107; display:inline-block;"></span> {{ __('installer.pending') }}</span>
                <span class="count" style="color:#ffc107;">{{ $pending }}</span>
            </div>
            <div class="cal-stat">
                <span><span class="dot" style="background:#17a2b8; display:inline-block;"></span> {{ __('installer.scheduled') }}</span>
                <span class="count" style="color:#17a2b8;">{{ $scheduled }}</span>
            </div>
            <div class="cal-stat">
                <span><span class="dot" style="background:#007bff; display:inline-block;"></span> {{ __('installer.in_progress') }}</span>
                <span class="count" style="color:#007bff;">{{ $inProgress }}</span>
            </div>
            <div class="cal-stat">
                <span><span class="dot" style="background:#28a745; display:inline-block;"></span> {{ __('installer.completed') }}</span>
                <span class="count" style="color:#28a745;">{{ $completed }}</span>
            </div>
        </div>

        <div class="cal-day-list">
            @php
                $upcoming = $jobs->where('scheduled_date', '>=', $today)->where('status', '!=', 'completed')->sortBy('scheduled_date')->take(15);
            @endphp

            @if($upcoming->count())
                <div class="cal-day-section">
                    <div class="day-header">{{ __('installer.upcoming_jobs') }}</div>
                    @foreach($upcoming as $uj)
                        <a href="{{ route('installer.jobs.index') }}?highlight={{ $uj->id }}" class="cal-job-card status-{{ $uj->status }}" style="text-decoration:none; display:block;">
                            <div class="jc-title">{{ $uj->job_number }}</div>
                            <div class="jc-meta">
                                <i class="bi bi-person me-1"></i>{{ $uj->customer_name ?? '—' }}
                                &middot; {{ $uj->scheduled_date->format('M d') }}
                                @if($uj->scheduled_time) @ {{ $uj->scheduled_time }} @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
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
                {{-- Day headers --}}
                @foreach([__('installer.sun'),__('installer.mon'),__('installer.tue'),__('installer.wed'),__('installer.thu'),__('installer.fri'),__('installer.sat')] as $dn)
                    <div class="day-name">{{ $dn }}</div>
                @endforeach

                {{-- Calendar cells --}}
                @php $current = $monthStart->copy(); @endphp
                @while($current <= $monthEnd)
                    @php
                        $dateKey = $current->format('Y-m-d');
                        $isToday = $current->isSameDay($today);
                        $isOtherMonth = $current->month !== (int)$month;
                        $dayJobs = $jobsByDate->get($dateKey, collect());
                        $maxShow = 3;
                    @endphp
                    <div class="cal-cell {{ $isToday ? 'today' : '' }} {{ $isOtherMonth ? 'other-month' : '' }}"
                         @if($dayJobs->count()) onclick="window.location='{{ route('installer.jobs.index') }}?status=all'" @endif>
                        <span class="cell-date">{{ $current->day }}</span>
                        <div class="cell-jobs">
                            @foreach($dayJobs->take($maxShow) as $dj)
                                <span class="cell-job s-{{ $dj->status }}">{{ $dj->customer_name ? substr($dj->customer_name, 0, 12) : $dj->job_number }}</span>
                            @endforeach
                            @if($dayJobs->count() > $maxShow)
                                <span class="cell-more">+{{ $dayJobs->count() - $maxShow }} {{ __('installer.more') }}</span>
                            @endif
                        </div>
                    </div>
                    @php $current->addDay(); @endphp
                @endwhile
            </div>
        </div>
    </div>
</div>
@endsection
