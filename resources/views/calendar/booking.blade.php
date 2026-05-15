<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Book Installation — VIP Windows</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root { --vip-primary: #1a3a5c; --vip-accent: #e8a838; }
        body { background: #f4f6f9; font-family: 'Segoe UI', system-ui, sans-serif; }
        .brand-bar { background: linear-gradient(135deg, var(--vip-primary), #244e78); color: #fff; }
        .brand-bar h4 span { color: var(--vip-accent); }
        .btn-vip { background: var(--vip-accent); color: #fff; border: none; }
        .btn-vip:hover { background: #d49530; color: #fff; }
        .slot-card { cursor: pointer; transition: all .15s; border: 2px solid transparent; }
        .slot-card:hover { border-color: var(--vip-accent); }
        .slot-card.selected { border-color: var(--vip-accent); background: #fef9ef; }
    </style>
</head>
<body>
    <div class="brand-bar py-3">
        <div class="container">
            <h4 class="mb-0"><i class="bi bi-building me-1"></i> <span>VIP</span> Windows — Book Your Installation</h4>
        </div>
    </div>

    <div class="container py-4" style="max-width:800px;">
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="card mb-4">
            <div class="card-body">
                <h5 class="fw-bold">Order #{{ $order->id }}</h5>
                <p class="mb-1"><strong>{{ $order->customer_name }}</strong></p>
                <p class="text-muted mb-0">
                    {{ $order->install_address }}, {{ $order->install_city }}, {{ $order->install_state }} {{ $order->install_zip }}
                </p>
            </div>
        </div>

        <h5 class="fw-semibold mb-3">Select an Available Date &amp; Time</h5>

        @if($slots->isEmpty())
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-1"></i> No installation slots are currently available. Please check back later or contact us.
            </div>
        @else
            <form method="POST" action="{{ route('booking.confirm', $order->id) }}" id="bookingForm">
                @csrf
                <input type="hidden" name="slot_id" id="selectedSlotId">

                @foreach($slots as $date => $dateSlots)
                    <h6 class="fw-semibold mt-4 mb-2">
                        <i class="bi bi-calendar-event me-1"></i>
                        {{ \Carbon\Carbon::parse($date)->format('l, F j, Y') }}
                    </h6>
                    <div class="row g-2 mb-2">
                        @foreach($dateSlots as $slot)
                            <div class="col-sm-6 col-md-4">
                                <div class="card slot-card p-3 text-center" data-slot-id="{{ $slot->id }}" onclick="selectSlot(this, {{ $slot->id }})">
                                    <div class="fw-semibold">{{ $slot->slot_time }}</div>
                                    <div class="text-muted small">{{ $slot->bookingsRemaining() }} spot{{ $slot->bookingsRemaining() > 1 ? 's' : '' }} left</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach

                <div class="mt-4">
                    <button type="submit" class="btn btn-vip btn-lg px-5" id="confirmBtn" disabled>
                        <i class="bi bi-check-circle me-1"></i> Confirm Booking
                    </button>
                </div>
            </form>
        @endif
    </div>

    <script>
        function selectSlot(el, slotId) {
            document.querySelectorAll('.slot-card').forEach(c => c.classList.remove('selected'));
            el.classList.add('selected');
            document.getElementById('selectedSlotId').value = slotId;
            document.getElementById('confirmBtn').disabled = false;
        }
    </script>
</body>
</html>
