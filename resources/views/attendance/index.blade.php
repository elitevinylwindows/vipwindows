@extends('layouts.app')
@section('title', 'Attendance')

@push('styles')
<style>
    .att-toolbar {
        background: #fff; border-radius: .5rem; padding: 1rem 1.25rem;
        box-shadow: 0 1px 4px rgba(0,0,0,.06); margin-bottom: 1.25rem;
        display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: .75rem;
    }
    .att-active-bar {
        background: linear-gradient(135deg, #198754, #157347);
        border-radius: .5rem; padding: 1rem 1.25rem; color: #fff;
        margin-bottom: 1.25rem;
    }
    .att-active-bar .badge { background: rgba(255,255,255,.2); font-size: .7rem; }
    .att-summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; margin-bottom: 1.25rem; }
    .att-sum-card {
        background: #fff; border-radius: .5rem; padding: 1rem 1.25rem;
        box-shadow: 0 1px 4px rgba(0,0,0,.06); display: flex; align-items: center; gap: 1rem;
    }
    .att-sum-card .avatar { width: 40px; height: 40px; border-radius: 50%; background: var(--vip-accent); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: .85rem; }
    .att-sum-card .sum-name { font-weight: 600; font-size: .85rem; }
    .att-sum-card .sum-meta { font-size: .72rem; color: #888; }

    .att-table { background: #fff; border-radius: .5rem; box-shadow: 0 1px 4px rgba(0,0,0,.06); overflow: hidden; }
    .att-table table { width: 100%; border-collapse: collapse; }
    .att-table th { font-size: .68rem; text-transform: uppercase; letter-spacing: .5px; color: rgba(0,0,0,.4); padding: .6rem 1rem; background: #fafaf7; border-bottom: 1px solid rgba(0,0,0,.06); }
    .att-table td { padding: .6rem 1rem; font-size: .85rem; border-bottom: 1px solid rgba(0,0,0,.04); }
    .att-table .active-row { background: rgba(40,167,69,.06); }
    .bg-purple { background: #6f42c1 !important; }
</style>
@endpush

@section('content')
@php
    $prevMonth = $start->copy()->subMonth()->format('Y-m');
    $nextMonth = $start->copy()->addMonth()->format('Y-m');
@endphp

<div class="container-fluid py-3">
    <h5 class="fw-bold mb-3"><i class="bi bi-clock-history me-2"></i>Attendance</h5>

    {{-- Currently Active on Jobs --}}
    @if($activeNow->count())
        <div class="att-active-bar">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <strong><i class="bi bi-circle-fill me-1" style="font-size:.5rem;"></i> {{ $activeNow->count() }} Currently on a Job</strong>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-3 mt-2">
                @foreach($activeNow as $al)
                    <div>
                        <span class="fw-semibold">{{ $al->user?->name ?? 'Unknown' }}</span>
                        <span class="badge ms-1">{{ $al->job?->job_number ?? 'Job' }} · {{ $al->job?->service?->name ?? '' }} · since {{ $al->clock_in->format('g:i A') }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Toolbar --}}
    <div class="att-toolbar">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.attendance.index', ['month' => $prevMonth, 'user_id' => $selectedUser]) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-chevron-left"></i></a>
            <h6 class="mb-0">{{ $start->format('F Y') }}</h6>
            <a href="{{ route('admin.attendance.index', ['month' => $nextMonth, 'user_id' => $selectedUser]) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-chevron-right"></i></a>
        </div>
        <div class="d-flex align-items-center gap-2">
            <form method="GET" action="{{ route('admin.attendance.index') }}" class="d-flex align-items-center gap-2">
                <input type="hidden" name="month" value="{{ $month }}">
                <select name="user_id" class="form-select form-select-sm" style="width:200px;" onchange="this.form.submit()">
                    <option value="">All Staff</option>
                    @foreach($staff as $s)
                        <option value="{{ $s->id }}" {{ $selectedUser == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('admin.attendance.index') }}" class="btn btn-sm btn-outline-primary">This Month</a>
        </div>
    </div>

    {{-- Summary Cards --}}
    @if($summary->count())
        <div class="att-summary">
            @foreach($summary as $s)
                @php
                    $h = intdiv($s['total_minutes'], 60);
                    $m = $s['total_minutes'] % 60;
                    $ah = intdiv($s['avg_minutes'], 60);
                    $am = $s['avg_minutes'] % 60;
                    $initials = collect(explode(' ', $s['user']->name ?? 'U'))->map(fn($w) => strtoupper(substr($w,0,1)))->join('');
                @endphp
                <div class="att-sum-card">
                    <div class="avatar">{{ $initials }}</div>
                    <div>
                        <div class="sum-name">{{ $s['user']->name ?? 'Unknown' }}</div>
                        <div class="sum-meta">{{ $s['total_jobs'] }} jobs &middot; {{ $s['total_days'] }} days &middot; {{ $h }}h {{ $m }}m total &middot; avg {{ $ah }}h {{ $am }}m/day</div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Log Table --}}
    <div class="att-table">
        <table>
            <thead>
                <tr>
                    <th>Staff</th>
                    <th>Date</th>
                    <th>Job</th>
                    <th>Service</th>
                    <th>Customer</th>
                    <th>Started</th>
                    <th>Ended</th>
                    <th>Duration</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    @php
                        $isActive = !$log->clock_out;
                        $dur = $log->total_minutes;
                        if ($isActive) $dur = $log->clock_in->diffInMinutes(now());
                        $dh = intdiv($dur, 60);
                        $dm = $dur % 60;
                    @endphp
                    <tr class="{{ $isActive ? 'active-row' : '' }}">
                        <td class="fw-semibold">{{ $log->user?->name ?? 'Unknown' }}</td>
                        <td>{{ $log->clock_in->format('M d, Y') }}</td>
                        <td>
                            <a href="{{ route('admin.jobs.show', $log->job_id) }}" class="text-decoration-none fw-semibold">
                                {{ $log->job?->job_number ?? '—' }}
                            </a>
                        </td>
                        <td>
                            @php
                                $svcName = $log->job?->service?->name ?? '—';
                                $svcBadge = match(true) {
                                    str_contains(strtolower($svcName), 'measure') => 'bg-purple text-white',
                                    str_contains(strtolower($svcName), 'install') => 'bg-primary',
                                    str_contains(strtolower($svcName), 'repair')  => 'bg-danger',
                                    str_contains(strtolower($svcName), 'service') => 'bg-success',
                                    default => 'bg-secondary',
                                };
                            @endphp
                            <span class="badge {{ $svcBadge }}" style="font-size:.65rem;">{{ $svcName }}</span>
                        </td>
                        <td class="text-muted">{{ $log->job?->customer_name ?? '—' }}</td>
                        <td>{{ $log->clock_in->format('g:i A') }}</td>
                        <td>
                            @if($log->clock_out)
                                {{ $log->clock_out->format('g:i A') }}
                            @else
                                <span class="badge bg-success" style="font-size:.65rem;">Active</span>
                            @endif
                        </td>
                        <td class="fw-semibold">{{ $dh }}h {{ $dm }}m</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No job time records for this period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
