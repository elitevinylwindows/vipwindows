@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="container py-4">
    <h4 class="fw-bold mb-4"><i class="bi bi-speedometer2 me-2"></i>Dashboard</h4>

    {{-- Stat cards --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card stat-card p-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width:48px;height:48px;background:#fff3cd;">
                        <i class="bi bi-hourglass-split fs-4 text-warning"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Pending</div>
                        <div class="fs-4 fw-bold">{{ $stats['pending'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card stat-card p-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width:48px;height:48px;background:#d1ecf1;">
                        <i class="bi bi-calendar-check fs-4 text-info"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Scheduled</div>
                        <div class="fs-4 fw-bold">{{ $stats['scheduled'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card stat-card p-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width:48px;height:48px;background:#cce5ff;">
                        <i class="bi bi-tools fs-4 text-primary"></i>
                    </div>
                    <div>
                        <div class="text-muted small">In Progress</div>
                        <div class="fs-4 fw-bold">{{ $stats['in_progress'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card stat-card p-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width:48px;height:48px;background:#d4edda;">
                        <i class="bi bi-check-circle fs-4 text-success"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Completed</div>
                        <div class="fs-4 fw-bold">{{ $stats['completed'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Today's installations --}}
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-white fw-semibold">
                    <i class="bi bi-calendar-day me-1"></i> Today's Installations
                </div>
                <div class="card-body p-0">
                    @if($todayInstalls->isEmpty())
                        <div class="text-muted text-center py-4">No installations scheduled for today.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Order</th>
                                        <th>Customer</th>
                                        <th>Slot</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($todayInstalls as $o)
                                        <tr>
                                            <td><a href="{{ route('orders.show', $o->id) }}">#{{ $o->id }}</a></td>
                                            <td>{{ $o->customer_name }}</td>
                                            <td>{{ $o->scheduled_slot }}</td>
                                            <td><span class="badge badge-{{ $o->status }}">{{ ucwords(str_replace('_', ' ', $o->status)) }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Recent orders --}}
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-clock-history me-1"></i> Recent Orders</span>
                    <a href="{{ route('orders.index') }}" class="btn btn-sm btn-outline-secondary">View All</a>
                </div>
                <div class="card-body p-0">
                    @if($recentOrders->isEmpty())
                        <div class="text-muted text-center py-4">No orders yet.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Order</th>
                                        <th>Customer</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentOrders as $o)
                                        <tr>
                                            <td><a href="{{ route('orders.show', $o->id) }}">#{{ $o->id }}</a></td>
                                            <td>{{ $o->customer_name }}</td>
                                            <td><span class="badge badge-{{ $o->status }}">{{ ucwords(str_replace('_', ' ', $o->status)) }}</span></td>
                                            <td>{{ $o->created_at->format('M d, Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
