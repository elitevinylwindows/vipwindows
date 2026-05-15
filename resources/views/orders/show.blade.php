@extends('layouts.app')
@section('title', 'Order #' . $order->id)

@section('content')
<div class="container py-4">
    <a href="{{ route('admin.orders.index') }}" class="text-decoration-none mb-3 d-inline-block">
        <i class="bi bi-arrow-left me-1"></i> Back to Orders
    </a>

    <div class="row g-4">
        {{-- Order details --}}
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">Order #{{ $order->id }}</h5>
                    <span class="badge badge-{{ $order->status }} fs-6">{{ ucwords(str_replace('_', ' ', $order->status)) }}</span>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <div class="text-muted small">Customer</div>
                            <div class="fw-semibold">{{ $order->customer_name }}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="text-muted small">Email</div>
                            <div>{{ $order->customer_email ?: '—' }}</div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <div class="text-muted small">Phone</div>
                            <div>{{ $order->customer_phone ?: '—' }}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="text-muted small">Quote ID</div>
                            <div>{{ $order->quote_id ? '#' . $order->quote_id : '—' }}</div>
                        </div>
                    </div>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="text-muted small">Installation Address</div>
                            <div>
                                {{ $order->install_address }}
                                @if($order->install_address2)<br>{{ $order->install_address2 }}@endif
                                <br>{{ $order->install_city }}, {{ $order->install_state }} {{ $order->install_zip }}
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <div class="text-muted small">Scheduled Date</div>
                            <div>
                                @if($order->scheduled_date)
                                    {{ \Carbon\Carbon::parse($order->scheduled_date)->format('l, M d, Y') }}
                                @else
                                    <span class="text-muted">Not yet scheduled</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="text-muted small">Time Slot</div>
                            <div>{{ $order->scheduled_slot ?: '—' }}</div>
                        </div>
                    </div>
                    @if($order->notes)
                        <hr>
                        <div class="text-muted small">Notes</div>
                        <div>{{ $order->notes }}</div>
                    @endif
                </div>
            </div>

            {{-- Quote items --}}
            @if($order->quoteItems && $order->quoteItems->count())
                <div class="card">
                    <div class="card-header bg-white fw-semibold">
                        <i class="bi bi-list-check me-1"></i> Quote Items
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Description</th>
                                        <th>Width</th>
                                        <th>Height</th>
                                        <th>Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($order->quoteItems as $i => $item)
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ $item->series_name ?? '' }} {{ $item->configuration_name ?? '' }}</td>
                                            <td>{{ $item->width ?? '—' }}</td>
                                            <td>{{ $item->height ?? '—' }}</td>
                                            <td>{{ $item->quantity ?? 1 }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Sidebar actions --}}
        <div class="col-lg-4">
            {{-- Update status --}}
            <div class="card mb-3">
                <div class="card-header bg-white fw-semibold">Update Status</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.orders.updateStatus', $order->id) }}">
                        @csrf @method('PUT')
                        <div class="mb-3">
                            <select name="status" class="form-select">
                                @foreach(['pending','scheduled','in_progress','completed','cancelled'] as $s)
                                    <option value="{{ $s }}" {{ $order->status === $s ? 'selected' : '' }}>
                                        {{ ucwords(str_replace('_', ' ', $s)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-muted">Notes</label>
                            <textarea name="notes" class="form-control" rows="3">{{ $order->notes }}</textarea>
                        </div>
                        <button class="btn btn-vip w-100">Update</button>
                    </form>
                </div>
            </div>

            {{-- Assign technician --}}
            <div class="card mb-3">
                <div class="card-header bg-white fw-semibold">Assign Technician</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.orders.assign', $order->id) }}">
                        @csrf @method('PUT')
                        <div class="mb-3">
                            <select name="technician_id" class="form-select">
                                <option value="">— Select —</option>
                                @foreach($technicians as $t)
                                    <option value="{{ $t->id }}" {{ $order->technician_id == $t->id ? 'selected' : '' }}>
                                        {{ $t->name }} ({{ ucfirst($t->role) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button class="btn btn-outline-primary w-100">Assign</button>
                    </form>
                </div>
            </div>

            {{-- Booking link --}}
            <div class="card">
                <div class="card-header bg-white fw-semibold">Customer Booking Link</div>
                <div class="card-body">
                    <p class="small text-muted mb-2">Share this link with the customer so they can book their installation slot:</p>
                    <div class="input-group">
                        <input type="text" class="form-control form-control-sm" id="bookingLink" value="{{ route('booking.show', $order->id) }}" readonly>
                        <button class="btn btn-outline-secondary btn-sm" onclick="navigator.clipboard.writeText(document.getElementById('bookingLink').value)">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
