@extends('layouts.app')
@section('title', 'Schedule Consultation')

@section('content')
<div class="container-fluid py-4 px-4">
    <div class="mb-4">
        <a href="{{ route('admin.consultations.index') }}" class="text-muted text-decoration-none"><i class="bi bi-arrow-left me-1"></i> Back to Consultations</a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-camera-video me-2"></i>Schedule Virtual Consultation</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.consultations.store') }}">
                        @csrf

                        <h6 class="text-muted mb-3">Customer Information</h6>

                        {{-- Existing customer quick-pick --}}
                        @if($customers->count())
                            <div class="mb-3">
                                <label class="form-label">Select Existing Customer</label>
                                <select class="form-select" id="customerPick">
                                    <option value="">— Or enter manually below —</option>
                                    @foreach($customers as $c)
                                        <option data-name="{{ $c->name }}" data-email="{{ $c->email }}" data-phone="{{ $c->phone }}" data-address="{{ $c->address ? $c->address . ', ' . $c->city . ', ' . $c->state . ' ' . $c->zip : '' }}">
                                            {{ $c->name }} — {{ $c->email }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Customer Name <span class="text-danger">*</span></label>
                                <input type="text" name="customer_name" id="custName" class="form-control @error('customer_name') is-invalid @enderror" value="{{ old('customer_name') }}" required>
                                @error('customer_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Customer Email <span class="text-danger">*</span></label>
                                <input type="email" name="customer_email" id="custEmail" class="form-control @error('customer_email') is-invalid @enderror" value="{{ old('customer_email') }}" required>
                                @error('customer_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" name="customer_phone" id="custPhone" class="form-control" value="{{ old('customer_phone') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Property Address</label>
                                <input type="text" name="address" id="custAddress" class="form-control" value="{{ old('address') }}" placeholder="For on-site reference">
                            </div>
                        </div>

                        <hr class="my-3">
                        <h6 class="text-muted mb-3">Consultation Details</h6>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Date & Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="scheduled_at" class="form-control @error('scheduled_at') is-invalid @enderror" value="{{ old('scheduled_at') }}" required>
                                @error('scheduled_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Duration</label>
                                <select name="duration" class="form-select">
                                    <option value="15">15 minutes</option>
                                    <option value="30" selected>30 minutes</option>
                                    <option value="45">45 minutes</option>
                                    <option value="60">1 hour</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Platform</label>
                                <select name="platform" class="form-select">
                                    <option value="zoom">Zoom</option>
                                    <option value="teams">Microsoft Teams</option>
                                    <option value="phone">Phone Call</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Meeting Link <span class="text-muted">(optional)</span></label>
                            <input type="url" name="meeting_link" class="form-control" value="{{ old('meeting_link') }}" placeholder="https://zoom.us/j/1234567890">
                            <div class="form-text">Paste your Zoom or Teams meeting link here. The customer will receive this in their confirmation email.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Window types, special requirements, measurements needed...">{{ old('notes') }}</textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-vip"><i class="bi bi-calendar-check me-1"></i> Schedule Consultation</button>
                            <a href="{{ route('admin.consultations.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('customerPick')?.addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    if (opt.dataset.name) {
        document.getElementById('custName').value = opt.dataset.name;
        document.getElementById('custEmail').value = opt.dataset.email;
        document.getElementById('custPhone').value = opt.dataset.phone || '';
        document.getElementById('custAddress').value = opt.dataset.address || '';
    }
});
</script>
@endpush
@endsection
