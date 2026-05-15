@extends('layouts.app')
@section('title', 'Virtual Consultations')

@section('content')
<div class="container-fluid py-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><i class="bi bi-camera-video me-2"></i>Virtual Consultations</h4>
        <a href="{{ route('admin.consultations.create') }}" class="btn btn-vip">
            <i class="bi bi-plus-circle me-1"></i> Schedule Consultation
        </a>
    </div>

    {{-- Stats cards --}}
    @php
        $upcoming = $consultations->where('status', 'scheduled')->where('scheduled_at', '>=', now());
        $today = $consultations->where('status', 'scheduled')->whereBetween('scheduled_at', [now()->startOfDay(), now()->endOfDay()]);
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card stat-card p-3">
                <div class="text-muted small">Today</div>
                <div class="fs-4 fw-bold" style="color:var(--vip-accent);">{{ $today->count() }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card p-3">
                <div class="text-muted small">Upcoming</div>
                <div class="fs-4 fw-bold">{{ $upcoming->count() }}</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            @if($consultations->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-camera-video fs-1 d-block mb-2"></i>
                    No consultations scheduled yet.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Customer</th>
                                <th>Date & Time</th>
                                <th>Duration</th>
                                <th>Platform</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($consultations as $c)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $c->customer_name }}</div>
                                        <div class="text-muted small">{{ $c->customer_email }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $c->scheduled_at->format('M d, Y') }}</div>
                                        <div class="text-muted small">{{ $c->scheduled_at->format('g:i A') }}</div>
                                    </td>
                                    <td>{{ $c->duration }} min</td>
                                    <td>
                                        @if($c->platform === 'zoom')
                                            <span class="badge bg-primary"><i class="bi bi-camera-video me-1"></i>Zoom</span>
                                        @elseif($c->platform === 'teams')
                                            <span class="badge bg-info"><i class="bi bi-microsoft-teams me-1"></i>Teams</span>
                                        @else
                                            <span class="badge bg-secondary"><i class="bi bi-telephone me-1"></i>Phone</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($c->status === 'scheduled')
                                            <span class="badge bg-primary">Scheduled</span>
                                        @elseif($c->status === 'completed')
                                            <span class="badge bg-success">Completed</span>
                                        @elseif($c->status === 'cancelled')
                                            <span class="badge bg-danger">Cancelled</span>
                                        @else
                                            <span class="badge bg-warning text-dark">No Show</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($c->meeting_link && $c->status === 'scheduled')
                                            <a href="{{ $c->meeting_link }}" target="_blank" class="btn btn-sm btn-success" title="Join">
                                                <i class="bi bi-box-arrow-up-right"></i>
                                            </a>
                                        @endif
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editConsult{{ $c->id }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    </td>
                                </tr>

                                {{-- Edit/Update modal --}}
                                <div class="modal fade" id="editConsult{{ $c->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('admin.consultations.update', $c->id) }}">
                                                @csrf @method('PUT')
                                                <div class="modal-header"><h5 class="modal-title">Update Consultation</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Customer</label>
                                                        <div class="fw-semibold">{{ $c->customer_name }} ({{ $c->customer_email }})</div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Status</label>
                                                        <select name="status" class="form-select">
                                                            <option value="scheduled" {{ $c->status === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                                            <option value="completed" {{ $c->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                                            <option value="cancelled" {{ $c->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                                            <option value="no_show" {{ $c->status === 'no_show' ? 'selected' : '' }}>No Show</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Meeting Link</label>
                                                        <input type="url" name="meeting_link" class="form-control" value="{{ $c->meeting_link }}" placeholder="https://zoom.us/j/...">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Notes</label>
                                                        <textarea name="notes" class="form-control" rows="3">{{ $c->notes }}</textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" class="btn btn-vip">Save Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3">{{ $consultations->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
