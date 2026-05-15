@extends('layouts.app')
@section('title', 'Email')

@section('content')
<div class="container-fluid py-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><i class="bi bi-envelope me-2"></i>Email</h4>
        <button class="btn btn-vip" data-bs-toggle="modal" data-bs-target="#composeModal">
            <i class="bi bi-pencil-square me-1"></i> Compose Email
        </button>
    </div>

    <div class="card">
        <div class="card-body p-0">
            @if($emails->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-envelope fs-1 d-block mb-2"></i>
                    No emails sent yet.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>To</th>
                                <th>Subject</th>
                                <th>Sent</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($emails as $email)
                                <tr>
                                    <td class="fw-semibold">{{ $email->to }}</td>
                                    <td>{{ $email->subject }}</td>
                                    <td class="text-muted small">{{ $email->created_at->format('M d, Y g:i A') }}</td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#viewEmail{{ $email->id }}">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </td>
                                </tr>

                                {{-- View modal --}}
                                <div class="modal fade" id="viewEmail{{ $email->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">{{ $email->subject }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-2 small"><strong>To:</strong> {{ $email->to }}</div>
                                                @if($email->cc)<div class="mb-2 small"><strong>CC:</strong> {{ $email->cc }}</div>@endif
                                                <div class="mb-2 small text-muted">{{ $email->created_at->format('F j, Y g:i A') }}</div>
                                                <hr>
                                                <div>{!! nl2br(e($email->body)) !!}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3">{{ $emails->links() }}</div>
            @endif
        </div>
    </div>
</div>

{{-- Compose Email modal --}}
<div class="modal fade" id="composeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.email.send') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-1"></i> Compose Email</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">To <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="email" name="to" class="form-control" placeholder="recipient@example.com" required>
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
                    </div>

                    <div class="mb-3">
                        <label class="form-label">CC <span class="text-muted">(optional, comma-separated)</span></label>
                        <input type="text" name="cc" class="form-control" placeholder="cc1@example.com, cc2@example.com">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Subject <span class="text-danger">*</span></label>
                        <input type="text" name="subject" class="form-control" placeholder="Email subject" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Message <span class="text-danger">*</span></label>
                        <textarea name="body" class="form-control" rows="8" placeholder="Type your message here..."></textarea>
                        <div class="form-text">HTML formatting is supported.</div>
                    </div>

                    {{-- Quick templates --}}
                    <div>
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
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-vip"><i class="bi bi-send me-1"></i> Send Email</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('.customer-pick').forEach(el => {
    el.addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelector('#composeModal input[name="to"]').value = this.dataset.email;
    });
});
document.querySelectorAll('.template-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelector('#composeModal input[name="subject"]').value = this.dataset.subject;
        document.querySelector('#composeModal textarea[name="body"]').value = this.dataset.body;
    });
});

// Auto-open compose modal if ?to= is in the URL
@if(request('to'))
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelector('#composeModal input[name="to"]').value = '{{ request("to") }}';
        new bootstrap.Modal(document.getElementById('composeModal')).show();
    });
@endif
</script>
@endpush
@endsection
