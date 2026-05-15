@extends('layouts.app')
@section('title', 'Installation Orders')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><i class="bi bi-clipboard-check me-2"></i>Installation Orders</h4>
    </div>

    {{-- Status filter tabs --}}
    <ul class="nav nav-pills mb-3">
        @foreach(['all' => 'All', 'pending' => 'Pending', 'scheduled' => 'Scheduled', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $key => $label)
            <li class="nav-item">
                <a class="nav-link {{ $status === $key ? 'active' : '' }}" href="{{ route('orders.index', ['status' => $key]) }}">
                    {{ $label }}
                </a>
            </li>
        @endforeach
    </ul>

    <div class="card">
        <div class="card-body p-0">
            @if($orders->isEmpty())
                <div class="text-muted text-center py-5">No orders found.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Phone</th>
                                <th>Address</th>
                                <th>Status</th>
                                <th>Scheduled</th>
                                <th>Technician</th>
                                <th>Created</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                <tr>
                                    <td><strong>#{{ $order->id }}</strong></td>
                                    <td>{{ $order->customer_name }}</td>
                                    <td>{{ $order->customer_phone ?: '—' }}</td>
                                    <td class="small">{{ $order->install_address }}, {{ $order->install_city }}</td>
                                    <td><span class="badge badge-{{ $order->status }}">{{ ucwords(str_replace('_', ' ', $order->status)) }}</span></td>
                                    <td>
                                        @if($order->scheduled_date)
                                            {{ \Carbon\Carbon::parse($order->scheduled_date)->format('M d, Y') }}
                                            <br><small class="text-muted">{{ $order->scheduled_slot }}</small>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $tech = $technicians->firstWhere('id', $order->technician_id);
                                        @endphp
                                        {{ $tech ? $tech->name : '—' }}
                                    </td>
                                    <td class="small">{{ $order->created_at->format('M d') }}</td>
                                    <td>
                                        <a href="{{ route('orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="mt-3">{{ $orders->appends(['status' => $status])->links() }}</div>
</div>
@endsection
