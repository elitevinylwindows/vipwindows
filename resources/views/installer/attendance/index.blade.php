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

    .att-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
    .att-stat-card {
        background: #fff; border-radius: .5rem; padding: 1rem 1.25rem;
        box-shadow: 0 1px 4px rgba(0,0,0,.06);
    }
    .att-stat-card .stat-label { font-size: .68rem; text-transform: uppercase; letter-spacing: .5px; color: rgba(0,0,0,.4); }
    .att-stat-card .stat-value { font-size: 1.4rem; font-weight: 700; color: #111; margin-top: .15rem; }

    .att-table { background: #fff; border-radius: .5rem; box-shadow: 0 1px 4px rgba(0,0,0,.06); overflow: hidden; }
    .att-table table { width: 100%; border-collapse: collapse; }
    .att-table th { font-size: .68rem; text-transform: uppercase; letter-spacing: .5px; color: rgba(0,0,0,.4); padding: .6rem 1rem; background: #fafaf7; border-bottom: 1px solid rgba(0,0,0,.06); }
    .att-table td { padding: .6rem 1rem; font-size: .85rem; border-bottom: 1px solid rgba(0,0,0,.04); }
    .att-table .active-row { background: rgba(40,167,69,.06); }
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
    {{-- Clock In/Out Hero --}}
    <div class="att-hero">
        <div>
            <div class="clock-display" id="liveClock">{{ now()->format('g:i:s A') }}</div>
            <div class="clock-date">{{ now()->format('l, F j, Y') }}</div>
            <div class="clock-status">
                @if($active)
                    <span class="badge badge-active"><i class="bi bi-circle-fill me-1" style="font-size:.5rem;"></i> Clocked In since {{ $active->clock_in->format('g:i A') }}</span>
                @else
                    <span class="badge badge-off"><i class="bi bi-circle me-1" style="font-size:.5rem;"></i> Not Clocked In</span>
                @endif
            </div>
        </div>
        <div>
            @if($active)
                <form method="POST" action="{{ route('installer.attendance.clockOut') }}">
                    @csrf
                    <div class="mb-2">
                        <input type="text" name="notes" class="form-control form-control-sm" placeholder="Shift notes (optional)"
                               style="background:rgba(255,255,255,.1); border:1px solid rgba(255,255,255,.2); color:#fff; width:250px;">
                    </div>
                    <button class="btn btn-warning btn-lg w-100"><i class="bi bi-stop-circle me-2"></i>Clock Out</button>
                </form>
            @else
                <form method="POST" action="{{ route('installer.attendance.clockIn') }}">
                    @csrf
                    <button class="btn btn-success btn-lg"><i class="bi bi-play-circle me-2"></i>Clock In</button>
                </form>
            @endif
        </div>
    </div>

    {{-- Month Stats --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('installer.attendance.index', ['month' => $prevMonth]) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-chevron-left"></i></a>
            <h6 class="mb-0">{{ $start->format('F Y') }}</h6>
            <a href="{{ route('installer.attendance.index', ['month' => $nextMonth]) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-chevron-right"></i></a>
        </div>
        <a href="{{ route('installer.attendance.index') }}" class="btn btn-sm btn-outline-primary">This Month</a>
    </div>

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
            <div class="stat-label">Avg per Day</div>
            <div class="stat-value">{{ $avgH }}h {{ $avgM }}m</div>
        </div>
    </div>

    {{-- Log Table --}}
    <div class="att-table">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Clock In</th>
                    <th>Clock Out</th>
                    <th>Duration</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr class="{{ $log->isActive() ? 'active-row' : '' }}">
                        <td class="fw-semibold">{{ $log->date->format('M d, Y') }}</td>
                        <td>{{ $log->clock_in->format('g:i A') }}</td>
                        <td>
                            @if($log->clock_out)
                                {{ $log->clock_out->format('g:i A') }}
                            @else
                                <span class="badge bg-success" style="font-size:.65rem;">Active</span>
                            @endif
                        </td>
                        <td>{{ $log->durationFormatted() }}</td>
                        <td class="text-muted">{{ $log->notes ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No attendance records for this month.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Live clock
setInterval(() => {
    const el = document.getElementById('liveClock');
    if (el) el.textContent = new Date().toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', second: '2-digit', hour12: true });
}, 1000);
</script>
@endpush
