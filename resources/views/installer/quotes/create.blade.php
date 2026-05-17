@extends('layouts.installer')
@section('title', 'New Quote')

@push('styles')
<style>
    .form-card { border: none; box-shadow: 0 1px 4px rgba(0,0,0,.08); border-radius: .5rem; }
    .form-card .card-header { background: #fff; border-bottom: 1px solid rgba(0,0,0,.06); font-weight: 600; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0"><i class="bi bi-plus-circle me-2"></i>New Quote</h4>
            <p class="text-muted small mb-0">Create a new quote for your customer</p>
        </div>
        <a href="{{ route('installer.quotes.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Quotes
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $e)
                <div>{{ $e }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('installer.quotes.store') }}">
        @csrf
        <div class="row g-4">
            {{-- Customer Info --}}
            <div class="col-lg-8">
                <div class="card form-card">
                    <div class="card-header py-3"><i class="bi bi-person me-2"></i>Customer Information</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Customer Name <span class="text-danger">*</span></label>
                                <input type="text" name="customer_name" class="form-control" value="{{ old('customer_name') }}" required list="customerList">
                                <datalist id="customerList">
                                    @foreach($customers as $c)
                                        <option value="{{ $c->name }}">{{ $c->email }}</option>
                                    @endforeach
                                </datalist>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="customer_email" class="form-control" value="{{ old('customer_email') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="text" name="customer_phone" class="form-control" value="{{ old('customer_phone') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Address</label>
                                <input type="text" name="address" class="form-control" value="{{ old('address') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">City</label>
                                <input type="text" name="city" class="form-control" value="{{ old('city') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">State</label>
                                <input type="text" name="state" class="form-control" value="{{ old('state') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">ZIP</label>
                                <input type="text" name="zip" class="form-control" value="{{ old('zip') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quote Settings --}}
            <div class="col-lg-4">
                <div class="card form-card">
                    <div class="card-header py-3"><i class="bi bi-gear me-2"></i>Quote Settings</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Valid For (days)</label>
                            <input type="number" name="valid_days" class="form-control" value="{{ old('valid_days', 30) }}" min="1" max="365">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="5" placeholder="Internal notes or special instructions...">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-vip w-100 py-2 fw-semibold">
                        <i class="bi bi-check-circle me-1"></i> Create Quote
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
