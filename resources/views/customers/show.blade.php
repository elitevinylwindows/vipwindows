@extends('layouts.app')
@section('title', $customer->name)

@section('content')
<div class="container-fluid py-4 px-4">
    <div class="mb-4">
        <a href="{{ route('admin.customers.index') }}" class="text-muted text-decoration-none"><i class="bi bi-arrow-left me-1"></i> Back to Customers</a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-person me-2"></i>{{ $customer->name }}</h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.email.compose', ['to' => $customer->email]) }}" class="btn btn-sm btn-outline-success"><i class="bi bi-envelope me-1"></i> Email</a>
                        <a href="{{ route('admin.customers.edit', $customer->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil me-1"></i> Edit</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="text-muted small">Email</div>
                            <div class="fw-semibold">{{ $customer->email }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Phone</div>
                            <div class="fw-semibold">{{ $customer->phone ?: '—' }}</div>
                        </div>
                        <div class="col-md-12">
                            <div class="text-muted small">Address</div>
                            <div class="fw-semibold">
                                @if($customer->address)
                                    {{ $customer->address }}<br>
                                    {{ $customer->city }}, {{ $customer->state }} {{ $customer->zip }}
                                @else
                                    —
                                @endif
                            </div>
                        </div>
                        @if($customer->notes)
                            <div class="col-md-12">
                                <div class="text-muted small">Notes</div>
                                <div>{{ $customer->notes }}</div>
                            </div>
                        @endif
                        <div class="col-md-6">
                            <div class="text-muted small">Customer Since</div>
                            <div class="fw-semibold">{{ $customer->created_at->format('F j, Y') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
