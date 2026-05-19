@extends('layouts.installer')
@section('title', 'Attendance')

@push('styles')
<style>
    .att-hero {
        background: linear-gradient(135deg, var(--vip-primary), #1a1a2e);
        border-radius: .75rem; padding: 2rem; color: #fff;
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 1.5rem;
    }
    .att-hero .clock-display { font-size: 2.5rem; font-weight: 700; letter-spacing: 2px; }
    .att-hero .clock-date { font-size: .85rem; color: rgba(255,255,255,.5); }
    .att-hero .clock-status { margin-top: .5rem; }
    .att-hero .badge-active { background: #28a745; font-size: .8rem; padding: .35rem .75rem; }
    .att-hero .badge-off { background: rgba(255,255,255,.15); font-size: .8rem; padding: .35rem .75rem; }

    .att-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
    .att-stat-card {
        background: #fff; border-radius: .5rem; padding: 1rem 1.25rem;
        box-shadow: 0 1px 4px rgba(0,0,0,.06);
    }
    .att-stat-card .stat-label { font-size: .68rem; text-transform: uppercase; letter-spacing: .5px; color: rgba(0,0,0,.4); }
    .att-stat-card .stat-value { font-size: 1.4rem; font-weight: 700; color: #111; margin-top: .15rem; }

    .service-breakdown { background: #fff; border-radius: .5rem; box-shadow: 0 1px 4px rgba(0,0,0,.06); padding: 1rem 1.25rem; margin-bottom: 1.5rem; }
    .service-bar { height: 8px; border-radius: 4px; background: rgba(0,0,0,.06); overflow: hidden; margin-top: .25rem; }
    .service-bar-fill { height: 100%; border-radius: 4px; }

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
    $totalH = intdiv($totalMinutes, 60);
    $totalM = $totalMinutes % 60;
    $avgH = intdiv($avgMinutes, 60);
    $avgM = $avgMinutes % 60;
    $prevMonth = $start->copy()->subMonth()->format('Y-m');
    $nextMonth = $start->copy()->addMonth()->format('Y-m');
@endphp

<div class="container-fluid py-3">
    {{-- Clock Status Hero --}}
    <div class="att-hero">
        <div>
            <div class="clock-display" id="liveClock">{{ now()->format('g:i:s A') }}</div>
            <div class="clock-date">{{ now()->format('l, F j, Y') }}</div>
            <div class="clock-status">
                @if($active)
                    <span class="badge badge-active">
                        <i class="bi bi-circle-fill me-1" style="font-size:.5rem;"></i>
                        Active on {{ $active->job?->job_number ?? 'Job' }}
                        ({{ $active->job?->service?->name ?? '' }})
                        since {{ $active->clock_in->format('g:i A') }}
                    </span>
                @else
                    <span class="badge badge-off"><i class="bi bi-circle me-1" style="font-size:.5rem;"></i> No Active Job</span>
                @endif
            </div>
        </div>
        <div class="text-end">
            <div class="small" style="color:rgba(255,255,255,.4);">Time tracked from jobs</div>
            <a href="{{ route('installer.calendar') }}" class="btn btn-sm btn-outline-light mt-2">
                <i class="bi bi-calendar me-1"></i> Go to Calendar
            </a>
        </div>
    </div>

    {{-- Month Navigation --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('installer.attendance.index', ['month' => $prevMonth]) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-chevron-left"></i></a>
            <h6 class="mb-0">{{ $start->format('F Y') }}</h6>
            <a href="{{ route('installer.attendance.index', ['month' => $nextMonth]) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-chevron-right"></i></a>
        </div>
        <a href="{{ route('installer.attendance.index') }}" class="btn btn-sm btn-outline-primary">This Month</a>
    </div>

    {{-- Stats --}}
    <div class="att-stats">
        <div class="att-stat-card">
            <div class="stat-label">Total Hours</div>
            <div class="stat-value">{{ $totalH }}h {{ $totalM }}m</div>
        </div>
        <div class="att-stat-card">
            <div class="stat-label">Days Worked</div>
            <div class="stat-value">{{ $totalDays }}</div>
        </div>
        <div class="att-stat-card">
            <div class="stat-label">Jobs Completed</div>
            <div class="stat-value">{{ $totalJobs }}</div>
        </div>
        <div class="att-stat-card">
            <div class="stat-label">Avg per Day</div>
            <div class="stat-value">{{ $avgH }}h {{ $avgM }}m</div>
        </div>
    </div>

    {{-- Service Breakdown --}}
    @if($serviceBreakdown->count())
    <div class="service-breakdown">
        <h6 class="fw-bold mb-3" style="font-size:.85rem;"><i class="bi bi-bar-chart me-1"></i> Time by Service</h6>
        @php $maxMin = $serviceBreakdown->max('minutes') ?: 1; @endphp
        @foreach($serviceBreakdown as $svcName => $data)
            @php
                $sh = intdiv($data['minutes'], 60);
                $sm = $data['minutes'] % 60;
                $pct = round(($data['minutes'] / $maxMin) * 100);
                $colors = ['Tech Measure' => '#6f42c1', 'Installation' => '#0d6efd', 'Repair' => '#dc3545', 'Service' => '#198754'];
                $barColor = $colors[$svcName] ?? '#c9a84c';
            @endphp
            <div class="mb-2">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="small fw-semibold">{{ $svcName }}</span>
                    <span class="small text-muted">{{ $data['count'] }} jobs &middot; {{ $sh }}h {{ $sm }}m</span>
                </div>
                <div class="service-bar">
                    <div class="service-bar-fill" style="width:{{ $pct }}%; background:{{ $barColor }};"></div>
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
                        <td class="fw-semibold">{{ $log->clock_in->format('M d, Y') }}</td>
                        <td>
                            <span class="fw-semibold">{{ $log->job?->job_number ?? '—' }}</span>
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
                    <tr><td colspan="7" class="text-center text-muted py-4">
                        <i class="bi bi-clock-history d-block mb-2" style="font-size:1.5rem; opacity:.3;"></i>
                        No time tracked this month. Start a route from the calendar to begin tracking.
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
setInterval(() => {
    const el = document.getElementById('liveClock');
    if (el) el.textContent = new Date().toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', second: '2-digit', hour12: true });
}, 1000);
</script>
@endpush
