@extends('layouts.app')
@section('title', 'Calendar Management')

@section('content')
<div class="container py-4">
    <h4 class="fw-bold mb-4"><i class="bi bi-calendar3 me-2"></i>Calendar &amp; Availability</h4>

    {{-- Month navigation --}}
    @php
        $prev = $startOfMonth->copy()->subMonth()->format('Y-m');
        $next = $startOfMonth->copy()->addMonth()->format('Y-m');
    @endphp
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.calendar.index', ['month' => $prev]) }}" class="btn btn-outline-secondary btn-sm me-2">
            <i class="bi bi-chevron-left"></i>
        </a>
        <h5 class="mb-0 fw-semibold">{{ $startOfMonth->format('F Y') }}</h5>
        <a href="{{ route('admin.calendar.index', ['month' => $next]) }}" class="btn btn-outline-secondary btn-sm ms-2">
            <i class="bi bi-chevron-right"></i>
        </a>
    </div>

    <div class="row g-4">
        {{-- Add slot form --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-white fw-semibold">
                    <i class="bi bi-plus-circle me-1"></i> Add Availability Slot
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.calendar.storeSlot') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" name="slot_date" class="form-control" required min="{{ today()->format('Y-m-d') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Time Slot</label>
                            <select name="slot_time" class="form-select" required>
                                <option value="Morning (8am-12pm)">Morning (8am-12pm)</option>
                                <option value="Afternoon (12pm-4pm)">Afternoon (12pm-4pm)</option>
                                <option value="Full Day (8am-4pm)">Full Day (8am-4pm)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Max Bookings</label>
                            <input type="number" name="max_bookings" class="form-control" value="2" min="1" max="20" required>
                        </div>
                        <button class="btn btn-vip w-100">Add Slot</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Calendar grid --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-white fw-semibold">Availability for {{ $startOfMonth->format('F Y') }}</div>
                <div class="card-body p-0">
                    @php
                        $daysInMonth = $startOfMonth->daysInMonth;
                        $firstDow = $startOfMonth->dayOfWeek; // 0=Sun
                    @endphp
                    <table class="table table-bordered mb-0" style="table-layout:fixed;">
                        <thead class="table-light">
                            <tr>
                                @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $d)
                                    <th class="text-center small py-2">{{ $d }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @php $day = 1; @endphp
                            @for($w = 0; $w < 6 && $day <= $daysInMonth; $w++)
                                <tr>
                                    @for($dow = 0; $dow < 7; $dow++)
                                        @if(($w === 0 && $dow < $firstDow) || $day > $daysInMonth)
                                            <td class="bg-light" style="min-height:80px;">&nbsp;</td>
                                        @else
                                            @php
                                                $dateKey = $startOfMonth->copy()->day($day)->format('Y-m-d');
                                                $daySlots = $slots[$dateKey] ?? collect();
                                                $dayOrders = $scheduledOrders[$dateKey] ?? collect();
                                                $isToday = $dateKey === today()->format('Y-m-d');
                                            @endphp
                                            <td class="{{ $isToday ? 'bg-warning bg-opacity-10' : '' }}" style="vertical-align:top; min-height:80px; padding:4px;">
                                                <div class="fw-bold small {{ $isToday ? 'text-warning' : '' }}">{{ $day }}</div>
                                                @foreach($daySlots as $slot)
                                                    <div class="small mt-1 p-1 rounded {{ $slot->isAvailable() ? 'bg-success bg-opacity-10' : 'bg-danger bg-opacity-10' }}">
                                                        <div class="fw-semibold" style="font-size:0.7rem;">{{ $slot->slot_time }}</div>
                                                        <div style="font-size:0.65rem;">{{ $slot->bookingsRemaining() }}/{{ $slot->max_bookings }} avail</div>
                                                        <div class="mt-1">
                                                            <form method="POST" action="{{ route('admin.calendar.deleteSlot', $slot->id) }}" class="d-inline" onsubmit="return confirm('Remove this slot?')">
                                                                @csrf @method('DELETE')
                                                                <button class="btn btn-link btn-sm text-danger p-0" style="font-size:0.65rem;">remove</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                @endforeach
                                                @foreach($dayOrders as $ord)
                                                    <div class="small mt-1 p-1 rounded bg-primary bg-opacity-10" style="font-size:0.65rem;">
                                                        <i class="bi bi-tools"></i> #{{ $ord->id }} {{ Str::limit($ord->customer_name, 10) }}
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
</div>
@endsection
