@extends('layouts.installer')
@section('title', 'My Profile')

@section('content')
<div class="container-fluid py-4 px-4">
    <h4 class="fw-bold mb-4"><i class="bi bi-person-gear me-2"></i>My Profile</h4>

    <form method="POST" action="{{ route('installer.profile.update') }}">
        @csrf
        @method('PUT')

        {{-- Personal Information --}}
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-person me-1"></i> Personal Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}">
                        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Address</label>
                        <input type="text" name="address" class="form-control" value="{{ old('address', $user->address) }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">City</label>
                        <input type="text" name="city" class="form-control" value="{{ old('city', $user->city) }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">State</label>
                        <input type="text" name="state" class="form-control" value="{{ old('state', $user->state) }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Zip Code</label>
                        <input type="text" name="zip" class="form-control" value="{{ old('zip', $user->zip) }}">
                    </div>
                </div>
                <hr>
                <h6 class="text-muted mb-3">Change Password <small class="text-muted">(leave blank to keep current)</small></h6>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Min 8 characters">
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm new password">
                    </div>
                </div>
            </div>
        </div>

        {{-- Company Branding --}}
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-building me-1"></i> Company Branding</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Company Name</label>
                        <input type="text" name="company_name" class="form-control" value="{{ old('company_name', $user->company_name) }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Company Phone</label>
                        <input type="text" name="company_phone" class="form-control" value="{{ old('company_phone', $user->company_phone) }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Company Email</label>
                        <input type="email" name="company_email" class="form-control" value="{{ old('company_email', $user->company_email) }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Website</label>
                        <input type="text" name="company_website" class="form-control" value="{{ old('company_website', $user->company_website) }}" placeholder="https://...">
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-vip btn-lg"><i class="bi bi-check-circle me-1"></i> Save Profile</button>
    </form>

    {{-- Logo Upload (separate form) --}}
    <div class="card mt-4">
        <div class="card-header bg-white">
            <h6 class="mb-0"><i class="bi bi-image me-1"></i> Company Logo</h6>
        </div>
        <div class="card-body">
            @if($user->company_logo)
                <div class="mb-3">
                    <label class="form-label text-muted">Current Logo</label>
                    <div>
                        <img src="{{ asset('uploads/installer-logos/' . $user->company_logo) }}" alt="Company Logo" style="height:80px; max-width:250px; object-fit:contain; border:1px solid #eee; border-radius:.5rem; padding:.5rem;">
                    </div>
                </div>
            @endif
            <form method="POST" action="{{ route('installer.profile.uploadLogo') }}" enctype="multipart/form-data">
                @csrf
                <div class="row align-items-end">
                    <div class="col-md-8 mb-3">
                        <label class="form-label">Upload New Logo</label>
                        <input type="file" name="company_logo" class="form-control @error('company_logo') is-invalid @enderror" accept="image/*" required>
                        @error('company_logo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text">Accepted formats: JPEG, PNG, GIF, SVG, WebP. Max 2MB.</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <button type="submit" class="btn btn-outline-dark w-100"><i class="bi bi-upload me-1"></i> Upload Logo</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
