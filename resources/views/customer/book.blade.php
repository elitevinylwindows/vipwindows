@extends('layouts.public')
@section('title', 'Book Installation')

@push('styles')
<style>
    .booking-wrapper { padding-top: 90px; }
    .slot-card { cursor: pointer; transition: all .15s; border: 2px solid transparent; }
    .slot-card:hover { border-color: var(--vip-accent); }
    .slot-card.selected { border-color: var(--vip-accent); background: #fef9ef; }
</style>
@endpush

@section('content')
<div class="booking-wrapper">
    <div class="container py-4" style="max-width:900px;">
        <a href="{{ route('customer.dashboard') }}" class="text-decoration-none mb-3 d-inline-block">
            <i class="bi bi-arrow-left me-1"></i> Back to My Account
        </a>

        <h4 class="fw-bold mb-4"><i class="bi bi-calendar-check me-2"></i>Book Your Installation</h4>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if($pendingOrders->isEmpty())
            <div class="card p-5 text-center">
                <i class="bi bi-info-circle fs-1 text-muted d-block mb-3"></i>
                <h5 class="fw-bold">No Pending Orders</h5>
                <p class="text-muted">You don't have any orders ready for scheduling. Once your window order is processed, you'll be able to book an installation slot here.</p>
                <a href="{{ route('customer.dashboard') }}" class="btn btn-outline-vip mt-2">Back to Dashboard</a>
            </div>
        @else
            <form method="POST" action="{{ route('customer.book.confirm') }}" id="bookingForm">
                @csrf

                {{-- Step 1: Select order --}}
                <div class="card mb-4">
                    <div class="card-header bg-white fw-semibold">
                        <span class="badge bg-dark rounded-pill me-2">1</span> Select Your Order
                    </div>
                    <div class="card-body">
                        @foreach($pendingOrders as $order)
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="order_id" value="{{ $order->id }}" id="order{{ $order->id }}" {{ $loop->first ? 'checked' : '' }}>
                                <label class="form-check-label" for="order{{ $order->id }}">
                                    <strong>Order #{{ $order->id }}</strong> —
                                    {{ $order->install_address }}, {{ $order->install_city }}, {{ $order->install_state }} {{ $order->install_zip }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Step 2: Select slot --}}
                <div class="card mb-4">
                    <div class="card-header bg-white fw-semibold">
                        <span class="badge bg-dark rounded-pill me-2">2</span> Choose a Date & Time
                    </div>
                    <div class="card-body">
                        <input type="hidden" name="slot_id" id="selectedSlotId">

                        @if($slots->isEmpty())
                            <div class="text-center py-4 text-muted">
                                <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
                                No available slots at this time. Please check back later.
                            </div>
                        @else
                            @foreach($slots as $date => $dateSlots)
                                <h6 class="fw-semibold mt-3 mb-2">
                                    <i class="bi bi-calendar-event me-1"></i>
                                    {{ \Carbon\Carbon::parse($date)->format('l, F j, Y') }}
                                </h6>
                                <div class="row g-2 mb-2">
                                    @foreach($dateSlots as $slot)
                                        <div class="col-sm-6 col-md-4">
                                            <div class="card slot-card p-3 text-center" onclick="selectSlot(this, {{ $slot->id }})">
                                                <div class="fw-semibold">{{ $slot->slot_time }}</div>
                                                <div class="text-muted small">{{ $slot->bookingsRemaining() }} spot{{ $slot->bookingsRemaining() > 1 ? 's' : '' }} available</div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>

                @if($slots->isNotEmpty())
                    <button type="submit" class="btn btn-vip btn-lg w-100" id="confirmBtn" disabled>
                        <i class="bi bi-check-circle me-1"></i> Confirm Booking
                    </button>
                @endif
            </form>
        @endif
    </div>
</div>

@push('scripts')
<script>
function selectSlot(el, slotId) {
    document.querySelectorAll('.slot-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('selectedSlotId').value = slotId;
    document.getElementById('confirmBtn').disabled = false;
}
</script>
@endpush
@endsection
