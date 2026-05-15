@extends('layouts.app')
@section('title', 'Compose Email')

@section('content')
<div class="container-fluid py-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><i class="bi bi-envelope me-2"></i>Compose Email</h4>
        <a href="{{ route('admin.email.sent') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-clock-history me-1"></i> Sent History
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.email.send') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">To <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="email" name="to" class="form-control @error('to') is-invalid @enderror"
                                       value="{{ old('to', $prefillEmail) }}" placeholder="recipient@example.com" required>
                                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-person-lines-fill"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" style="max-height:300px; overflow-y:auto;">
                                    @foreach($customers as $c)
                                        <li><a class="dropdown-item customer-pick" href="#" data-email="{{ $c->email }}">{{ $c->name }} — {{ $c->email }}</a></li>
                                    @endforeach
                                    @if($customers->isEmpty())
                                        <li><span class="dropdown-item text-muted">No customers yet</span></li>
                                    @endif
                                </ul>
                            </div>
                            @error('to')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">CC <span class="text-muted">(optional, comma-separated)</span></label>
                            <input type="text" name="cc" class="form-control" value="{{ old('cc') }}" placeholder="cc1@example.com, cc2@example.com">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Subject <span class="text-danger">*</span></label>
                            <input type="text" name="subject" class="form-control @error('subject') is-invalid @enderror"
                                   value="{{ old('subject') }}" placeholder="Email subject" required>
                            @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Message <span class="text-danger">*</span></label>
                            <textarea name="body" class="form-control @error('body') is-invalid @enderror" rows="12"
                                      placeholder="Type your message here...">{{ old('body') }}</textarea>
                            @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">HTML formatting is supported.</div>
                        </div>

                        {{-- Quick templates --}}
                        <div class="mb-3">
                            <label class="form-label text-muted small">Quick Templates</label>
                            <div class="d-flex gap-2 flex-wrap">
                                <button type="button" class="btn btn-sm btn-outline-secondary template-btn"
                                        data-subject="Your VIP Windows Quote"
                                        data-body="Dear Customer,<br><br>Thank you for your interest in VIP Windows. Please find your quote details below.<br><br>If you have any questions, feel free to reach out to us at (562) 368-0313.<br><br>Best regards,<br>VIP Windows Team">
                                    <i class="bi bi-file-text me-1"></i> Quote Follow-up
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary template-btn"
                                        data-subject="Installation Scheduling — VIP Windows"
                                        data-body="Dear Customer,<br><br>We'd like to schedule your window installation. Please let us know your preferred dates and times, and we'll do our best to accommodate.<br><br>You can also book directly through our website or call us at (562) 368-0313.<br><br>Thank you,<br>VIP Windows Team">
                                    <i class="bi bi-calendar me-1"></i> Schedule Installation
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary template-btn"
                                        data-subject="Virtual Consultation Invitation — VIP Windows"
                                        data-body="Dear Customer,<br><br>We'd like to invite you to a virtual consultation where we can review your windows, take measurements, and provide a detailed estimate — all from the comfort of your home.<br><br>Please reply with your preferred date and time, and we'll send you a meeting link.<br><br>Best regards,<br>VIP Windows Team">
                                    <i class="bi bi-camera-video me-1"></i> Consultation Invite
                                </button>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-vip"><i class="bi bi-send me-1"></i> Send Email</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('.customer-pick').forEach(el => {
    el.addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelector('input[name="to"]').value = this.dataset.email;
    });
});
document.querySelectorAll('.template-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelector('input[name="subject"]').value = this.dataset.subject;
        document.querySelector('textarea[name="body"]').value = this.dataset.body;
    });
});
</script>
@endpush
@endsection
