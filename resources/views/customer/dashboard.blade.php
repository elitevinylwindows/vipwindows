@extends('layouts.public')
@section('title', 'My Account')

@push('styles')
<style>
    .account-wrapper { padding-top: 90px; }
</style>
@endpush

@section('content')
<div class="account-wrapper">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0"><i class="bi bi-person-circle me-2"></i>Welcome, {{ Auth::user()->name }}</h4>
            <a href="{{ route('customer.book') }}" class="btn btn-vip">
                <i class="bi bi-calendar-check me-1"></i> Book Installation
            </a>
        </div>

        {{-- Stats --}}
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card p-3 text-center">
                    <div class="fs-3 fw-bold" style="color:var(--vip-accent);">{{ $orders->count() }}</div>
                    <div class="text-muted small">Total Orders</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3 text-center">
                    <div class="fs-3 fw-bold text-info">{{ $upcoming->count() }}</div>
                    <div class="text-muted small">Upcoming Installations</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3 text-center">
                    <div class="fs-3 fw-bold text-success">{{ $completed->count() }}</div>
                    <div class="text-muted small">Completed</div>
                </div>
            </div>
        </div>

        {{-- Upcoming installations --}}
        @if($upcoming->count())
            <div class="card mb-4">
                <div class="card-header bg-white fw-semibold">
                    <i class="bi bi-calendar-event me-1"></i> Upcoming Installations
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr><th>Order</th><th>Date</th><th>Time</th><th>Address</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                                @foreach($upcoming as $o)
                                    <tr>
                                        <td>#{{ $o->id }}</td>
                                        <td>{{ $o->scheduled_date ? $o->scheduled_date->format('M d, Y') : '—' }}</td>
                                        <td>{{ $o->scheduled_slot ?: '—' }}</td>
                                        <td class="small">{{ $o->install_address }}, {{ $o->install_city }}</td>
                                        <td><span class="badge" style="background:var(--vip-accent);color:#fff;">{{ ucwords(str_replace('_', ' ', $o->status)) }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        {{-- All orders --}}
        <div class="card">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-clock-history me-1"></i> All Orders
            </div>
            <div class="card-body p-0">
                @if($orders->isEmpty())
                    <div class="text-muted text-center py-5">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        No orders yet. Once your windows are ordered, they'll appear here.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr><th>Order</th><th>Date</th><th>Address</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $o)
                                    <tr>
                                        <td>#{{ $o->id }}</td>
                                        <td>{{ $o->created_at->format('M d, Y') }}</td>
                                        <td class="small">{{ $o->install_address }}, {{ $o->install_city }}, {{ $o->install_state }}</td>
                                        <td>
                                            @php
                                                $colors = ['pending'=>'warning','scheduled'=>'info','in_progress'=>'primary','completed'=>'success','cancelled'=>'danger'];
                                            @endphp
                                            <span class="badge bg-{{ $colors[$o->status] ?? 'secondary' }}">{{ ucwords(str_replace('_', ' ', $o->status)) }}</span>
                                        </td>
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
@endsection
