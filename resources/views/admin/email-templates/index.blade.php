@extends('layouts.app')
@section('title', 'Email Templates')

@section('content')
<div class="container-fluid py-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-envelope-paper me-2"></i>Email Templates</h4>
            <p class="text-muted small mb-0">Manage notification emails sent to customers for jobs. Use placeholders to personalize each message.</p>
        </div>
    </div>

    {{-- Placeholder reference --}}
    <div class="card mb-4">
        <div class="card-body py-3">
            <h6 class="fw-semibold mb-2"><i class="bi bi-braces me-1"></i> Available Placeholders</h6>
            <div class="row g-2">
                @foreach($placeholders as $token => $desc)
                    <div class="col-md-3 col-sm-6">
                        <div class="d-flex align-items-start gap-2">
                            <code class="text-nowrap" style="font-size:.8rem; background:#f0ede5; padding:2px 6px; border-radius:4px; color:#8b6914;">{{ $token }}</code>
                            <span class="small text-muted">{{ $desc }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Template cards --}}
    <div class="row g-4">
        @foreach($templates as $template)
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                        <div>
                            <h6 class="mb-0 fw-semibold">
                                @switch($template->slug)
                                    @case('job-scheduled')
                                        <i class="bi bi-calendar-check text-info me-1"></i>
                                        @break
                                    @case('day-before-reminder')
                                        <i class="bi bi-bell text-warning me-1"></i>
                                        @break
                                    @case('follow-up')
                                        <i class="bi bi-chat-heart text-success me-1"></i>
                                        @break
                                    @case('payment-received')
                                        <i class="bi bi-credit-card text-primary me-1"></i>
                                        @break
                                @endswitch
                                {{ $template->name }}
                            </h6>
                            <span class="small text-muted">Slug: {{ $template->slug }}</span>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="active{{ $template->id }}"
                                   form="templateForm{{ $template->id }}" name="is_active" value="1"
                                   {{ $template->is_active ? 'checked' : '' }}>
                            <label class="form-check-label small" for="active{{ $template->id }}">Active</label>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.email-templates.update', $template->id) }}" id="templateForm{{ $template->id }}">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Template Name</label>
                                <input type="text" name="name" class="form-control form-control-sm" value="{{ $template->name }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Subject Line</label>
                                <input type="text" name="subject" class="form-control form-control-sm" value="{{ $template->subject }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Body</label>
                                <textarea name="body" class="form-control" rows="8" style="font-size:.85rem;" required>{{ $template->body }}</textarea>
                                <div class="form-text">Use placeholders above. Line breaks are preserved in the email.</div>
                            </div>
                            <button type="submit" class="btn btn-sm btn-vip">
                                <i class="bi bi-check-circle me-1"></i> Save Template
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if($templates->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="bi bi-envelope-paper fs-1 d-block mb-2"></i>
            No email templates found. Run the SQL seed to create the default templates.
        </div>
    @endif
</div>
@endsection
