@extends('layouts.installer')
@section('title', 'My Jobs')

@section('content')
<div class="container-fluid py-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><i class="bi bi-tools me-2"></i>My Jobs</h4>
    </div>

    {{-- Stats row --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">Today</div>
                            <h4 class="fw-bold mb-0">{{ $todayJobs }}</h4>
                        </div>
                        <i class="bi bi-calendar-day text-primary fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">This Week</div>
                            <h4 class="fw-bold mb-0">{{ $weekJobs }}</h4>
                        </div>
                        <i class="bi bi-calendar-week text-info fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">In Progress</div>
                            <h4 class="fw-bold mb-0">{{ $inProgress }}</h4>
                        </div>
                        <i class="bi bi-arrow-repeat text-warning fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Status filter --}}
    <div class="mb-4">
        <div class="btn-group btn-group-sm" role="group">
            <a href="{{ route('installer.jobs.index') }}" class="btn {{ $status === 'all' ? 'btn-dark' : 'btn-outline-dark' }}">All</a>
            <a href="{{ route('installer.jobs.index', ['status' => 'pending']) }}" class="btn {{ $status === 'pending' ? 'btn-dark' : 'btn-outline-dark' }}">Pending</a>
            <a href="{{ route('installer.jobs.index', ['status' => 'scheduled']) }}" class="btn {{ $status === 'scheduled' ? 'btn-dark' : 'btn-outline-dark' }}">Scheduled</a>
            <a href="{{ route('installer.jobs.index', ['status' => 'in_progress']) }}" class="btn {{ $status === 'in_progress' ? 'btn-dark' : 'btn-outline-dark' }}">In Progress</a>
            <a href="{{ route('installer.jobs.index', ['status' => 'completed']) }}" class="btn {{ $status === 'completed' ? 'btn-dark' : 'btn-outline-dark' }}">Completed</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            @if($jobs->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-tools fs-1 d-block mb-2"></i>
                    No jobs assigned to you yet.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Job #</th>
                                <th>Customer</th>
                                <th>Location</th>
                                <th>Scheduled</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($jobs as $job)
                                <tr>
                                    <td class="fw-semibold">{{ $job->job_number }}</td>
                                    <td>
                                        {{ $job->customer_name ?: '—' }}
                                        @if($job->customer_phone)
                                            <div class="text-muted small">{{ $job->customer_phone }}</div>
                                        @endif
                                    </td>
                                    <td class="small text-muted">
                                        @if($job->install_address)
                                            {{ $job->install_address }}<br>
                                            {{ $job->install_city }}, {{ $job->install_state }} {{ $job->install_zip }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>
                                        @if($job->scheduled_date)
                                            <span class="fw-semibold">{{ $job->scheduled_date->format('M d, Y') }}</span>
                                            @if($job->scheduled_time)
                                                <div class="text-muted small">{{ $job->scheduled_time }}</div>
                                            @endif
                                        @else
                                            <span class="text-muted">Not scheduled</span>
                                        @endif
                                    </td>
                                    <td>
                                        @switch($job->priority)
                                            @case('urgent')
                                                <span class="badge bg-danger">Urgent</span>
                                                @break
                                            @case('high')
                                                <span class="badge bg-warning text-dark">High</span>
                                                @break
                                            @case('normal')
                                                <span class="badge bg-secondary">Normal</span>
                                                @break
                                            @case('low')
                                                <span class="badge bg-light text-dark">Low</span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary">Normal</span>
                                        @endswitch
                                    </td>
                                    <td><span class="badge badge-{{ $job->status }}">{{ ucfirst(str_replace('_', ' ', $job->status)) }}</span></td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#jobDetail{{ $job->id }}" title="Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        @if(in_array($job->status, ['pending', 'scheduled']))
                                            <form method="POST" action="{{ route('installer.jobs.updateStatus', $job->id) }}" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="status" value="in_progress">
                                                <button class="btn btn-sm btn-outline-primary" title="Start Job"><i class="bi bi-play-fill"></i></button>
                                            </form>
                                        @elseif($job->status === 'in_progress')
                                            <form method="POST" action="{{ route('installer.jobs.updateStatus', $job->id) }}" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="status" value="completed">
                                                <button class="btn btn-sm btn-outline-success" title="Complete Job"><i class="bi bi-check-lg"></i></button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>

                                {{-- Job detail modal --}}
                                <div class="modal fade" id="jobDetail{{ $job->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">{{ $job->job_number }} — {{ $job->customer_name }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row mb-3">
                                                    <div class="col-md-6">
                                                        <strong>Customer:</strong> {{ $job->customer_name }}<br>
                                                        @if($job->customer_email)<strong>Email:</strong> {{ $job->customer_email }}<br>@endif
                                                        @if($job->customer_phone)<strong>Phone:</strong> {{ $job->customer_phone }}<br>@endif
                                                    </div>
                                                    <div class="col-md-6">
                                                        @if($job->install_address)
                                                            <strong>Install Address:</strong><br>
                                                            {{ $job->install_address }}<br>
                                                            {{ $job->install_city }}, {{ $job->install_state }} {{ $job->install_zip }}
                                                        @endif
                                                    </div>
                                                </div>

                                                @if($job->description)
                                                    <div class="mb-3">
                                                        <strong>Description:</strong>
                                                        <p class="mb-0">{{ $job->description }}</p>
                                                    </div>
                                                @endif

                                                @if($job->notes)
                                                    <div class="mb-3">
                                                        <strong>Notes:</strong>
                                                        <p class="mb-0">{{ $job->notes }}</p>
                                                    </div>
                                                @endif

                                                {{-- Job notes --}}
                                                <hr>
                                                <h6 class="fw-semibold mb-3">Activity Notes</h6>

                                                @if($job->jobNotes->count())
                                                    @foreach($job->jobNotes as $note)
                                                        <div class="border rounded p-2 mb-2">
                                                            <div class="d-flex justify-content-between">
                                                                <strong class="small">{{ $note->author?->name ?? 'System' }}</strong>
                                                                <span class="text-muted small">{{ $note->created_at->format('M d, Y g:ia') }}</span>
                                                            </div>
                                                            <p class="mb-0 small mt-1">{{ $note->note }}</p>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <p class="text-muted small">No notes yet.</p>
                                                @endif

                                                {{-- Add note form --}}
                                                <form method="POST" action="{{ route('installer.jobs.addNote', $job->id) }}" class="mt-3">
                                                    @csrf
                                                    <div class="input-group">
                                                        <input type="text" name="note" class="form-control" placeholder="Add a note..." required>
                                                        <button class="btn btn-vip" type="submit"><i class="bi bi-plus"></i> Add</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3">{{ $jobs->appends(request()->query())->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
