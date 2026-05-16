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
            <div class="col-md-3">
                <div class="card p-3 text-center">
                    <div class="fs-3 fw-bold" style="color:var(--vip-accent);">{{ $quotes->count() }}</div>
                    <div class="text-muted small">Quotes Received</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3 text-center">
                    <div class="fs-3 fw-bold" style="color:var(--vip-accent);">{{ $orders->count() }}</div>
                    <div class="text-muted small">Total Orders</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3 text-center">
                    <div class="fs-3 fw-bold text-info">{{ $upcoming->count() }}</div>
                    <div class="text-muted small">Upcoming</div>
                </div>
            </div>
            <div class="col-md-3">
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

        {{-- Quotes received --}}
        <div class="card mb-4">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-calculator me-1"></i> Quotes Received
            </div>
            <div class="card-body p-0">
                @if($quotes->isEmpty())
                    <div class="text-muted text-center py-5">
                        <i class="bi bi-calculator fs-1 d-block mb-2"></i>
                        No quotes yet. When your installer sends you a quote, it'll appear here.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr><th>Quote #</th><th>Date</th><th>Items</th><th>Total</th><th></th></tr>
                            </thead>
                            <tbody>
                                @foreach($quotes as $quote)
                                    <tr>
                                        <td class="fw-semibold">{{ $quote->quote_number }}</td>
                                        <td>{{ $quote->created_at?->format('M d, Y') }}</td>
                                        <td>{{ $quote->items->count() }} item(s)</td>
                                        <td class="fw-semibold">${{ number_format($quote->items->sum(fn($i) => $i->getRawOriginal('total')), 2) }}</td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-outline-dark" data-bs-toggle="modal" data-bs-target="#viewQuote{{ $quote->id }}">
                                                <i class="bi bi-eye me-1"></i> View
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- Quote detail modals --}}
        @foreach($quotes as $quote)
            <div class="modal fade" id="viewQuote{{ $quote->id }}" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Quote #{{ $quote->quote_number }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Date:</strong> {{ $quote->created_at?->format('F j, Y') }}</p>
                                    @if($quote->valid_until)
                                        <p class="mb-1"><strong>Valid Until:</strong> {{ $quote->valid_until }}</p>
                                    @endif
                                </div>
                            </div>
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Description</th>
                                        <th class="text-center">Size</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($quote->items as $i => $item)
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ $item->description }}<br><small class="text-muted">{{ $item->series_type }}</small></td>
                                            <td class="text-center">{{ $item->width }}" x {{ $item->height }}"</td>
                                            <td class="text-center">{{ $item->qty }}</td>
                                            <td class="text-end">${{ number_format($item->getRawOriginal('total'), 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="fw-bold">
                                        <td colspan="4" class="text-end">Total</td>
                                        <td class="text-end">${{ number_format($quote->items->sum(fn($i) => $i->getRawOriginal('total')), 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        {{-- All orders --}}
        <div class="card">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-clock-history me-1"></i> Installation History
            </div>
            <div class="card-body p-0">
                @if($orders->isEmpty())
                    <div class="text-muted text-center py-5">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        No installations yet. Once your windows are ordered, they'll appear here.
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
