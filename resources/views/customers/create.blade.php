@extends('layouts.app')
@section('title', 'Add Customer')

@section('content')
<div class="container-fluid py-4 px-4">
    <div class="mb-4">
        <a href="{{ route('admin.customers.index') }}" class="text-muted text-decoration-none"><i class="bi bi-arrow-left me-1"></i> Back to Customers</a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-person-plus me-2"></i>Add New Customer</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.customers.store') }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="(555) 123-4567">
                            </div>
                        </div>

                        <hr class="my-3">
                        <h6 class="text-muted mb-3">Address</h6>

                        <div class="mb-3">
                            <label class="form-label">Street Address</label>
                            <input type="text" name="address" class="form-control" value="{{ old('address') }}">
                        </div>
                        <div class="row">
                            <div class="col-md-5 mb-3">
                                <label class="form-label">City</label>
                                <input type="text" name="city" class="form-control" value="{{ old('city') }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">State</label>
                                <input type="text" name="state" class="form-control" value="{{ old('state', 'CA') }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">ZIP</label>
                                <input type="text" name="zip" class="form-control" value="{{ old('zip') }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Any notes about this customer...">{{ old('notes') }}</textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-vip"><i class="bi bi-person-plus me-1"></i> Add Customer</button>
                            <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
