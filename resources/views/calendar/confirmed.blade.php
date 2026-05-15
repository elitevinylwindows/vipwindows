<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Booking Confirmed — VIP Windows</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root { --vip-primary: #1a3a5c; --vip-accent: #e8a838; }
        body { background: #f4f6f9; font-family: 'Segoe UI', system-ui, sans-serif; }
        .brand-bar { background: linear-gradient(135deg, var(--vip-primary), #244e78); color: #fff; }
        .brand-bar h4 span { color: var(--vip-accent); }
        .success-icon { font-size: 4rem; color: #28a745; }
    </style>
</head>
<body>
    <div class="brand-bar py-3">
        <div class="container">
            <h4 class="mb-0"><i class="bi bi-building me-1"></i> <span>VIP</span> Windows</h4>
        </div>
    </div>

    <div class="container py-5" style="max-width:600px;">
        <div class="card text-center p-5">
            <div class="success-icon mb-3">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <h3 class="fw-bold mb-3">Installation Booked!</h3>
            <p class="text-muted mb-4">Your installation has been scheduled. Here are the details:</p>

            <div class="bg-light rounded p-4 text-start mb-4">
                <div class="row mb-2">
                    <div class="col-5 text-muted">Order</div>
                    <div class="col-7 fw-semibold">#{{ $order->id }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-5 text-muted">Date</div>
                    <div class="col-7 fw-semibold">{{ $slot->slot_date->format('l, F j, Y') }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-5 text-muted">Time</div>
                    <div class="col-7 fw-semibold">{{ $slot->slot_time }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-5 text-muted">Address</div>
                    <div class="col-7">{{ $order->install_address }}, {{ $order->install_city }}, {{ $order->install_state }} {{ $order->install_zip }}</div>
                </div>
            </div>

            <p class="text-muted small">
                A team member from VIP Windows will arrive during the scheduled time window.
                If you need to reschedule, please contact us.
            </p>
        </div>
    </div>
</body>
</html>
