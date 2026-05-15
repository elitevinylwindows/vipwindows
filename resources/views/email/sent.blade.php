@extends('layouts.app')
@section('title', 'Sent Emails')

@section('content')
<div class="container-fluid py-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><i class="bi bi-clock-history me-2"></i>Sent Emails</h4>
        <a href="{{ route('admin.email.compose') }}" class="btn btn-vip">
            <i class="bi bi-pencil-square me-1"></i> Compose New
        </a>
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
@endsection
