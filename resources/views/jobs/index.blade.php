@extends('layouts.app')
@section('title', __('admin.jobs'))

@section('content')
<div class="container-fluid py-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><i class="bi bi-tools me-2"></i>{{ __('admin.jobs') }}</h4>
        <button class="btn btn-vip" data-bs-toggle="modal" data-bs-target="#createJobModal">
            <i class="bi bi-plus-circle me-1"></i> {{ __('admin.new_job') }}
        </button>
    </div>

    {{-- Stats cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body py-3">
                    <div class="text-muted small">{{ __('admin.todays_jobs') }}</div>
                    <div class="fs-4 fw-bold">{{ $todayJobs }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body py-3">
                    <div class="text-muted small">{{ __('admin.upcoming_this_week') }}</div>
                    <div class="fs-4 fw-bold">{{ $weekJobs }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body py-3">
                    <div class="text-muted small">{{ __('admin.in_progress') }}</div>
                    <div class="fs-4 fw-bold text-primary">{{ $inProgress }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body py-3">
                    <div class="text-muted small">{{ __('admin.completed_this_month') }}</div>
                    <div class="fs-4 fw-bold text-success">{{ $completedMonth }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Status filter --}}
    <div class="mb-4">
        <div class="btn-group btn-group-sm" role="group">
            <a href="{{ route('admin.jobs.index') }}" class="btn {{ $status === 'all' ? 'btn-dark' : 'btn-outline-dark' }}">{{ __('admin.all') }}</a>
            <a href="{{ route('admin.jobs.index', ['status' => 'pending']) }}" class="btn {{ $status === 'pending' ? 'btn-dark' : 'btn-outline-dark' }}">{{ __('admin.pending') }}</a>
            <a href="{{ route('admin.jobs.index', ['status' => 'scheduled']) }}" class="btn {{ $status === 'scheduled' ? 'btn-dark' : 'btn-outline-dark' }}">{{ __('admin.scheduled') }}</a>
            <a href="{{ route('admin.jobs.index', ['status' => 'in_progress']) }}" class="btn {{ $status === 'in_progress' ? 'btn-dark' : 'btn-outline-dark' }}">{{ __('admin.in_progress') }}</a>
            <a href="{{ route('admin.jobs.index', ['status' => 'completed']) }}" class="btn {{ $status === 'completed' ? 'btn-dark' : 'btn-outline-dark' }}">{{ __('admin.completed') }}</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            @if($jobs->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-tools fs-1 d-block mb-2"></i>
                    {{ __('admin.no_jobs_yet') }}
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('admin.job_number') }}</th>
                                <th>{{ __('admin.customer') }}</th>
                                <th>{{ __('admin.service') }}</th>
                                <th>{{ __('admin.assigned_to') }}</th>
                                <th>{{ __('admin.date') }}</th>
                                <th>{{ __('admin.priority') }}</th>
                                <th>{{ __('admin.status') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($jobs as $job)
                                <tr>
                                    <td class="fw-semibold">{{ $job->job_number }}</td>
                                    <td>{{ $job->customer_name }}</td>
                                    <td class="small">
                                        @if($job->service)
                                            <span class="d-inline-flex align-items-center gap-1">
                                                <span style="width:8px;height:8px;border-radius:50%;background:{{ $job->service->color ?? '#6c757d' }};flex-shrink:0;"></span>
                                                {{ $job->service->name }}
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>{{ $job->assignee?->name ?? '—' }}</td>
                                    <td class="text-muted small">
                                        {{ $job->scheduled_date?->format('M d, Y') ?? '—' }}
                                        @if($job->scheduled_time)
                                            <br>{{ $job->scheduled_time }}
                                        @endif
                                    </td>
                                    <td>
                                        @switch($job->priority)
                                            @case('low')
                                                <span class="badge bg-secondary">{{ __('admin.low') }}</span>
                                                @break
                                            @case('normal')
                                                <span class="badge bg-primary">{{ __('admin.normal') }}</span>
                                                @break
                                            @case('high')
                                                <span class="badge bg-warning text-dark">{{ __('admin.high') }}</span>
                                                @break
                                            @case('urgent')
                                                <span class="badge bg-danger">{{ __('admin.urgent') }}</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td>
                                        @switch($job->status)
                                            @case('pending')
                                                <span class="badge bg-warning text-dark">{{ __('admin.pending') }}</span>
                                                @break
                                            @case('scheduled')
                                                <span class="badge bg-info">{{ __('admin.scheduled') }}</span>
                                                @break
                                            @case('in_progress')
                                                <span class="badge bg-primary">{{ __('admin.in_progress') }}</span>
                                                @break
                                            @case('completed')
                                                <span class="badge bg-success">{{ __('admin.completed') }}</span>
                                                @break
                                            @case('cancelled')
                                                <span class="badge bg-dark">{{ __('admin.cancelled') }}</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-secondary view-job-btn" data-job-id="{{ $job->id }}" title="{{ __('admin.view') }}">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editJob{{ $job->id }}" title="{{ __('admin.edit') }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#assignJob{{ $job->id }}" title="{{ __('admin.assign') }}">
                                            <i class="bi bi-person-check"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#statusJob{{ $job->id }}" title="{{ __('admin.update_status') }}">
                                            <i class="bi bi-arrow-repeat"></i>
                                        </button>
                                        <form method="POST" action="{{ route('admin.jobs.destroy', $job->id) }}" class="d-inline" onsubmit="return confirm('{{ __('admin.delete_this_job') }}')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" title="{{ __('admin.delete') }}"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>

                                {{-- Edit modal --}}
                                <div class="modal fade" id="editJob{{ $job->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('admin.jobs.update', $job->id) }}">
                                                @csrf @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title"><i class="bi bi-pencil me-1"></i> {{ __('admin.edit_job') }} {{ $job->job_number }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">{{ __('admin.customer_name') }} <span class="text-danger">*</span></label>
                                                            <input type="text" name="customer_name" class="form-control" value="{{ $job->customer_name }}" required>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">{{ __('admin.email') }}</label>
                                                            <input type="email" name="customer_email" class="form-control" value="{{ $job->customer_email }}">
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">{{ __('admin.phone') }}</label>
                                                        <input type="text" name="customer_phone" class="form-control" value="{{ $job->customer_phone }}">
                                                    </div>
                                                    <hr class="my-2">
                                                    <h6 class="text-muted small mb-2">{{ __('admin.install_address') }}</h6>
                                                    <div class="mb-3">
                                                        <input type="text" name="install_address" class="form-control" placeholder="{{ __('admin.street_address') }}" value="{{ $job->install_address }}" data-address-autocomplete>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-5 mb-3">
                                                            <input type="text" name="install_city" class="form-control" placeholder="{{ __('admin.city') }}" value="{{ $job->install_city }}">
                                                        </div>
                                                        <div class="col-md-4 mb-3">
                                                            <input type="text" name="install_state" class="form-control" placeholder="{{ __('admin.state') }}" value="{{ $job->install_state }}">
                                                        </div>
                                                        <div class="col-md-3 mb-3">
                                                            <input type="text" name="install_zip" class="form-control" placeholder="{{ __('admin.zip') }}" value="{{ $job->install_zip }}">
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">{{ __('admin.description') }}</label>
                                                        <textarea name="description" class="form-control" rows="2">{{ $job->description }}</textarea>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-4 mb-3">
                                                            <label class="form-label">{{ __('admin.priority') }}</label>
                                                            <select name="priority" class="form-select">
                                                                <option value="low" {{ $job->priority === 'low' ? 'selected' : '' }}>{{ __('admin.low') }}</option>
                                                                <option value="normal" {{ $job->priority === 'normal' ? 'selected' : '' }}>{{ __('admin.normal') }}</option>
                                                                <option value="high" {{ $job->priority === 'high' ? 'selected' : '' }}>{{ __('admin.high') }}</option>
                                                                <option value="urgent" {{ $job->priority === 'urgent' ? 'selected' : '' }}>{{ __('admin.urgent') }}</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4 mb-3">
                                                            <label class="form-label">{{ __('admin.scheduled_date') }}</label>
                                                            <input type="date" name="scheduled_date" class="form-control" value="{{ $job->scheduled_date?->format('Y-m-d') }}">
                                                        </div>
                                                        <div class="col-md-4 mb-3">
                                                            <label class="form-label">{{ __('admin.scheduled_time') }}</label>
                                                            <input type="text" name="scheduled_time" class="form-control" placeholder="{{ __('admin.time_placeholder') }}" value="{{ $job->scheduled_time }}">
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">{{ __('admin.estimated_duration') }}</label>
                                                        <input type="text" name="estimated_duration" class="form-control" placeholder="{{ __('admin.duration_placeholder') }}" value="{{ $job->estimated_duration }}">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">{{ __('admin.notes') }}</label>
                                                        <textarea name="notes" class="form-control" rows="2">{{ $job->notes }}</textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" class="btn btn-vip">{{ __('admin.save_changes') }}</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                {{-- Assign modal --}}
                                <div class="modal fade" id="assignJob{{ $job->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title"><i class="bi bi-person-check me-1"></i> {{ __('admin.assign_job') }} {{ $job->job_number }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">{{ __('admin.assign_to') }} <span class="text-danger">*</span></label>
                                                    <select class="form-select assign-to-select">
                                                        <option value="">— {{ __('admin.select') }} —</option>
                                                        @foreach($technicians as $tech)
                                                            <option value="{{ $tech->id }}" {{ $job->assigned_to == $tech->id ? 'selected' : '' }}>
                                                                {{ $tech->name }} ({{ ucfirst($tech->role) }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">{{ __('admin.scheduled_date') }}</label>
                                                    <input type="date" class="form-control assign-date" value="{{ $job->scheduled_date?->format('Y-m-d') }}">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">{{ __('admin.scheduled_time') }}</label>
                                                    <input type="text" class="form-control assign-time" placeholder="{{ __('admin.time_placeholder') }}" value="{{ $job->scheduled_time }}">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-vip assign-job-btn" data-job-id="{{ $job->id }}">
                                                    <i class="bi bi-check-circle me-1"></i> {{ __('admin.assign') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Update Status modal --}}
                                <div class="modal fade" id="statusJob{{ $job->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title"><i class="bi bi-arrow-repeat me-1"></i> {{ __('admin.update_status') }} — {{ $job->job_number }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">{{ __('admin.status') }} <span class="text-danger">*</span></label>
                                                    <select class="form-select status-select">
                                                        <option value="pending" {{ $job->status === 'pending' ? 'selected' : '' }}>{{ __('admin.pending') }}</option>
                                                        <option value="scheduled" {{ $job->status === 'scheduled' ? 'selected' : '' }}>{{ __('admin.scheduled') }}</option>
                                                        <option value="in_progress" {{ $job->status === 'in_progress' ? 'selected' : '' }}>{{ __('admin.in_progress') }}</option>
                                                        <option value="completed" {{ $job->status === 'completed' ? 'selected' : '' }}>{{ __('admin.completed') }}</option>
                                                        <option value="cancelled" {{ $job->status === 'cancelled' ? 'selected' : '' }}>{{ __('admin.cancelled') }}</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3 completion-notes-field" style="display:none;">
                                                    <label class="form-label">{{ __('admin.completion_notes') }}</label>
                                                    <textarea class="form-control completion-notes" rows="3" placeholder="{{ __('admin.completion_notes_placeholder') }}"></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-vip update-status-btn" data-job-id="{{ $job->id }}">
                                                    <i class="bi bi-check-circle me-1"></i> {{ __('admin.update_status') }}
                                                </button>
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

{{-- View Job modal (shared, populated via JS) --}}
<div class="modal fade" id="viewJobModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-tools me-1"></i> <span id="viewJobTitle"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewJobBody">
                <div class="text-center py-4"><div class="spinner-border text-muted" role="status"></div></div>
            </div>
            <div class="modal-footer d-block">
                {{-- Email Actions --}}
                <div class="mb-3 pb-3 border-bottom">
                    <h6 class="fw-semibold mb-2"><i class="bi bi-envelope me-1"></i> {{ __('admin.send_email_to_customer') }}</h6>
                    <div class="d-flex flex-wrap gap-2" id="jobEmailActions">
                        <button class="btn btn-sm btn-outline-info send-job-email-btn" data-slug="job-scheduled" title="{{ __('admin.send_scheduling_confirmation') }}">
                            <i class="bi bi-calendar-check me-1"></i> {{ __('admin.job_scheduled') }}
                        </button>
                        <button class="btn btn-sm btn-outline-warning send-job-email-btn" data-slug="day-before-reminder" title="{{ __('admin.send_day_before_reminder') }}">
                            <i class="bi bi-bell me-1"></i> {{ __('admin.day_before_reminder') }}
                        </button>
                        <button class="btn btn-sm btn-outline-success send-job-email-btn" data-slug="follow-up" title="{{ __('admin.send_follow_up') }}">
                            <i class="bi bi-chat-heart me-1"></i> {{ __('admin.follow_up') }}
                        </button>
                        <button class="btn btn-sm btn-outline-primary send-job-email-btn" data-slug="payment-received" title="{{ __('admin.send_payment_confirmation') }}">
                            <i class="bi bi-credit-card me-1"></i> {{ __('admin.payment_received') }}
                        </button>
                        <button class="btn btn-sm btn-outline-danger" id="sendEstimateBtn" title="{{ __('admin.send_estimate_to_customer') }}" onclick="sendEstimate()">
                            <i class="bi bi-file-earmark-pdf me-1"></i> {{ __('admin.send_estimate') }}
                        </button>
                    </div>
                    <div id="emailSendResult" class="small mt-2" style="display:none;"></div>
                </div>
                {{-- Add Note --}}
                <div>
                    <h6 class="fw-semibold mb-2">{{ __('admin.add_note') }}</h6>
                    <div class="input-group">
                        <input type="text" class="form-control" id="newJobNote" placeholder="{{ __('admin.type_a_note') }}">
                        <button class="btn btn-vip" id="addJobNoteBtn"><i class="bi bi-plus"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Create Job modal (compact) --}}
<div class="modal fade" id="createJobModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.jobs.store') }}">
                @csrf
                <div class="modal-header py-2">
                    <h6 class="modal-title mb-0"><i class="bi bi-plus-circle me-1"></i> {{ __('admin.new_job') }}</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-2">
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label mb-0 small text-muted">{{ __('admin.from_quote') }}</label>
                            <select name="from_quote" class="form-select form-select-sm" id="jobFromQuote">
                                <option value="">— {{ __('admin.none') }} —</option>
                                @foreach($quotes as $q)
                                    <option value="{{ $q->id }}" data-name="{{ $q->billing_name }}">{{ $q->quote_number }} — {{ $q->billing_name ?: __('admin.no_name') }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label mb-0 small text-muted">{{ __('admin.from_invoice') }}</label>
                            <select name="from_invoice" class="form-select form-select-sm" id="jobFromInvoice">
                                <option value="">— {{ __('admin.none') }} —</option>
                                @foreach($invoices as $inv)
                                    <option value="{{ $inv->id }}" data-name="{{ $inv->customer_name }}" data-email="{{ $inv->customer_email }}" data-phone="{{ $inv->customer_phone }}" data-address="{{ $inv->customer_address }}">{{ $inv->invoice_number }} — {{ $inv->customer_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    {{-- Service Line Items --}}
                    <div class="mt-2">
                        <label class="form-label mb-1 small text-muted fw-bold">{{ strtoupper(__('admin.service_line_items')) }}</label>
                        <div id="jobServiceLines">
                            {{-- Dynamic rows added by JS --}}
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary mt-1" onclick="addServiceLine()">
                            <i class="bi bi-plus me-1"></i> {{ __('admin.add_service') }}
                        </button>
                    </div>
                    <hr class="my-2">
                    <div class="row g-2">
                        <div class="col-4">
                            <label class="form-label mb-0 small text-muted">{{ __('admin.customer_name') }} *</label>
                            <input type="text" name="customer_name" class="form-control form-control-sm" id="jobCustName" required>
                        </div>
                        <div class="col-4">
                            <label class="form-label mb-0 small text-muted">{{ __('admin.email') }}</label>
                            <input type="email" name="customer_email" class="form-control form-control-sm" id="jobCustEmail">
                        </div>
                        <div class="col-4">
                            <label class="form-label mb-0 small text-muted">{{ __('admin.phone') }}</label>
                            <input type="text" name="customer_phone" class="form-control form-control-sm" id="jobCustPhone">
                        </div>
                    </div>

                    {{-- Assign To: Crew / Installer toggle --}}
                    <div class="mt-2">
                        <label class="form-label mb-1 small text-muted">{{ __('admin.assign_to') }}</label>
                        <div class="btn-group btn-group-sm w-100 mb-2" role="group">
                            <input type="radio" class="btn-check" name="assignment_type" value="crew" id="createAssignCrew" checked>
                            <label class="btn btn-outline-dark" for="createAssignCrew"><i class="bi bi-people-fill me-1"></i>{{ __('admin.crew') }}</label>
                            <input type="radio" class="btn-check" name="assignment_type" value="installer" id="createAssignInstaller">
                            <label class="btn btn-outline-dark" for="createAssignInstaller"><i class="bi bi-person-badge me-1"></i>{{ __('admin.installer') }}</label>
                        </div>
                        <div id="adminCreateCrewSelect">
                            <select name="crew_id" class="form-select form-select-sm">
                                <option value="">— {{ __('admin.select_crew') }} —</option>
                                @foreach($crews as $crew)
                                    <option value="{{ $crew->id }}">{{ $crew->name }} ({{ $crew->members->count() }} {{ __('admin.members') }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="adminCreateInstallerSelect" style="display:none;">
                            <select name="assigned_to" class="form-select form-select-sm">
                                <option value="">— {{ __('admin.select_installer') }} —</option>
                                @foreach($technicians as $tech)
                                    <option value="{{ $tech->id }}">{{ $tech->name }} ({{ ucfirst($tech->role) }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <hr class="my-2">
                    <div class="row g-2">
                        <div class="col-8">
                            <label class="form-label mb-0 small text-muted">{{ __('admin.address') }}</label>
                            <input type="text" name="install_address" class="form-control form-control-sm" placeholder="{{ __('admin.street') }}" id="jobInstallAddr" data-address-autocomplete>
                        </div>
                        <div class="col-4">
                            <label class="form-label mb-0 small text-muted">{{ __('admin.priority') }}</label>
                            <select name="priority" class="form-select form-select-sm">
                                <option value="low">{{ __('admin.low') }}</option>
                                <option value="normal" selected>{{ __('admin.normal') }}</option>
                                <option value="high">{{ __('admin.high') }}</option>
                                <option value="urgent">{{ __('admin.urgent') }}</option>
                            </select>
                        </div>
                        <div class="col-5">
                            <input type="text" name="install_city" class="form-control form-control-sm" placeholder="{{ __('admin.city') }}" id="jobInstallCity">
                        </div>
                        <div class="col-4">
                            <input type="text" name="install_state" class="form-control form-control-sm" placeholder="{{ __('admin.state') }}" value="CA" id="jobInstallState">
                        </div>
                        <div class="col-3">
                            <input type="text" name="install_zip" class="form-control form-control-sm" placeholder="{{ __('admin.zip') }}" id="jobInstallZip">
                        </div>
                    </div>

                    <hr class="my-2">
                    <div class="row g-2">
                        <div class="col-12">
                            <label class="form-label mb-0 small text-muted">{{ __('admin.description') }}</label>
                            <textarea name="description" class="form-control form-control-sm" rows="2" placeholder="{{ __('admin.job_description_placeholder') }}"></textarea>
                        </div>
                    </div>

                    <hr class="my-2">
                    <div class="row g-2">
                        <div class="col-3">
                            <label class="form-label mb-0 small text-muted">{{ __('admin.start_date') }}</label>
                            <input type="date" name="scheduled_date" class="form-control form-control-sm">
                        </div>
                        <div class="col-3">
                            <label class="form-label mb-0 small text-muted">{{ __('admin.end_date') }}</label>
                            <input type="date" name="end_date" class="form-control form-control-sm">
                        </div>
                        <div class="col-3">
                            <label class="form-label mb-0 small text-muted">{{ __('admin.time') }}</label>
                            <input type="text" name="scheduled_time" class="form-control form-control-sm" placeholder="{{ __('admin.time_placeholder') }}">
                        </div>
                        <div class="col-3">
                            <label class="form-label mb-0 small text-muted">{{ __('admin.est_duration') }}</label>
                            <input type="text" name="estimated_duration" class="form-control form-control-sm" placeholder="{{ __('admin.duration_days_placeholder') }}">
                        </div>
                    </div>

                    <div class="row g-2 mt-1">
                        <div class="col-12">
                            <label class="form-label mb-0 small text-muted">{{ __('admin.notes') }}</label>
                            <textarea name="notes" class="form-control form-control-sm" rows="1"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">{{ __('admin.cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-vip"><i class="bi bi-plus-circle me-1"></i> {{ __('admin.create_job') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
const csrfToken = '{{ csrf_token() }}';
const __t = {
    loading: '{{ __('admin.loading') }}',
    notes: '{{ __('admin.notes') }}',
    unknown: '{{ __('admin.unknown') }}',
    timeline: '{{ __('admin.timeline') }}',
    created: '{{ __('admin.created') }}',
    scheduled_label: '{{ __('admin.scheduled') }}',
    started_label: '{{ __('admin.started') }}',
    completed_label: '{{ __('admin.completed') }}',
    at: '{{ __('admin.at') }}',
    customer: '{{ __('admin.customer') }}',
    install_address: '{{ __('admin.install_address') }}',
    status: '{{ __('admin.status') }}',
    priority: '{{ __('admin.priority') }}',
    assigned_to: '{{ __('admin.assigned_to') }}',
    unassigned: '{{ __('admin.unassigned') }}',
    estimated_duration: '{{ __('admin.estimated_duration') }}',
    created_by: '{{ __('admin.created_by') }}',
    description: '{{ __('admin.description') }}',
    completion_notes: '{{ __('admin.completion_notes') }}',
    job: '{{ __('admin.job') }}',
    select: '{{ __('admin.select') }}',
    select_person: '{{ __('admin.select_person_to_assign') }}',
    failed: '{{ __('admin.failed') }}',
    network_error: '{{ __('admin.network_error') }}',
    send_confirm: '{{ __('admin.send_email_confirm') }}',
    send_estimate_confirm: '{{ __('admin.send_estimate_confirm') }}',
    failed_to_send: '{{ __('admin.failed_to_send') }}',
};
let currentViewJobId = null;
let serviceLineIdx = 0;

// Services data for dynamic line items
@php
    $svcJson = $services->map(function($s) {
        return [
            'id' => $s->id,
            'name' => $s->name,
            'base_price' => $s->base_price,
            'installer_pay' => $s->installer_pay ?? 0,
            'installer_pay_type' => $s->installer_pay_type ?? 'per_unit',
            'unit' => $s->unit,
            'color' => $s->color ?? '#0d6efd',
        ];
    });
@endphp
const servicesData = @json($svcJson);

function addServiceLine(svcId = '', qty = 1) {
    const idx = serviceLineIdx++;
    const container = document.getElementById('jobServiceLines');
    const row = document.createElement('div');
    row.className = 'row g-1 align-items-center mb-1';
    row.id = 'svcLine' + idx;

    let options = `<option value="">— ${__t.select} —</option>`;
    servicesData.forEach(s => {
        const sel = s.id == svcId ? 'selected' : '';
        options += `<option value="${s.id}" ${sel}>${s.name} — $${parseFloat(s.base_price).toFixed(2)}</option>`;
    });

    row.innerHTML = `
        <div class="col-6">
            <select name="items[${idx}][service_id]" class="form-select form-select-sm" onchange="updateLineTotal(${idx})">
                ${options}
            </select>
        </div>
        <div class="col-2">
            <input type="number" name="items[${idx}][qty]" class="form-control form-control-sm" value="${qty}" min="1" placeholder="Qty" onchange="updateLineTotal(${idx})">
        </div>
        <div class="col-3">
            <span class="small text-muted" id="lineTotal${idx}">—</span>
        </div>
        <div class="col-1 text-end">
            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1" onclick="document.getElementById('svcLine${idx}').remove()" style="font-size:.75rem;">
                <i class="bi bi-x"></i>
            </button>
        </div>
    `;
    container.appendChild(row);
    updateLineTotal(idx);
}

function updateLineTotal(idx) {
    const row = document.getElementById('svcLine' + idx);
    if (!row) return;
    const sel = row.querySelector('select');
    const qtyInput = row.querySelector('input[type="number"]');
    const totalSpan = document.getElementById('lineTotal' + idx);
    const svcId = sel.value;
    const qty = parseInt(qtyInput.value) || 0;

    if (!svcId || !qty) { totalSpan.textContent = '—'; return; }

    const svc = servicesData.find(s => s.id == svcId);
    if (!svc) { totalSpan.textContent = '—'; return; }

    const total = svc.base_price * qty;
    const pay = svc.installer_pay_type === 'percentage'
        ? (svc.base_price * svc.installer_pay / 100) * qty
        : svc.installer_pay * qty;
    const profit = total - pay;

    totalSpan.innerHTML = `<span style="color:var(--vip-accent);font-weight:600;">$${total.toFixed(2)}</span> <span style="font-size:.65rem;color:#666;">(pay $${pay.toFixed(2)})</span>`;
}

// Add one blank row by default
document.addEventListener('DOMContentLoaded', function() {
    addServiceLine();
});

// Crew/Installer toggle for create modal
document.querySelectorAll('#createJobModal input[name="assignment_type"]').forEach(r => {
    r.addEventListener('change', function() {
        document.getElementById('adminCreateCrewSelect').style.display = this.value === 'crew' ? '' : 'none';
        document.getElementById('adminCreateInstallerSelect').style.display = this.value === 'installer' ? '' : 'none';
    });
});

// Auto-fill from quote selection
document.getElementById('jobFromQuote')?.addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    if (opt.dataset.name) {
        document.getElementById('jobCustName').value = opt.dataset.name;
    }
});

// Auto-fill from invoice selection
document.getElementById('jobFromInvoice')?.addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    if (opt.value) {
        document.getElementById('jobCustName').value = opt.dataset.name || '';
        document.getElementById('jobCustEmail').value = opt.dataset.email || '';
        document.getElementById('jobCustPhone').value = opt.dataset.phone || '';
        if (opt.dataset.address) {
            document.getElementById('jobInstallAddr').value = opt.dataset.address;
        }
    }
});

// View job
document.querySelectorAll('.view-job-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.jobId;
        currentViewJobId = id;
        document.getElementById('viewJobTitle').textContent = __t.loading;
        document.getElementById('viewJobBody').innerHTML = '<div class="text-center py-4"><div class="spinner-border text-muted" role="status"></div></div>';
        new bootstrap.Modal(document.getElementById('viewJobModal')).show();

        fetch(`/admin/jobs/${id}`)
            .then(r => r.json())
            .then(data => {
                const job = data.job;
                document.getElementById('viewJobTitle').textContent = __t.job + ' ' + job.job_number;

                const statusBadge = {
                    pending: 'bg-warning text-dark', scheduled: 'bg-info',
                    in_progress: 'bg-primary', completed: 'bg-success', cancelled: 'bg-dark'
                };
                const priorityBadge = {
                    low: 'bg-secondary', normal: 'bg-primary',
                    high: 'bg-warning text-dark', urgent: 'bg-danger'
                };

                let notesHtml = '';
                if (data.notes && data.notes.length) {
                    notesHtml = `<h6 class="fw-semibold mt-3">${__t.notes}</h6><div class="list-group">`;
                    data.notes.forEach(n => {
                        const date = new Date(n.created_at).toLocaleDateString();
                        notesHtml += `<div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <small class="fw-semibold">${n.author ? n.author.name : __t.unknown}</small>
                                <small class="text-muted">${date}</small>
                            </div>
                            <div>${n.note}</div>
                        </div>`;
                    });
                    notesHtml += '</div>';
                }

                // Timeline
                let timeline = `<h6 class="fw-semibold mt-3">${__t.timeline}</h6><ul class="list-unstyled">`;
                timeline += `<li><i class="bi bi-circle-fill text-muted me-1" style="font-size:.5rem;vertical-align:middle"></i> ${__t.created}: ${new Date(job.created_at).toLocaleDateString()}</li>`;
                if (job.scheduled_date) timeline += `<li><i class="bi bi-circle-fill text-info me-1" style="font-size:.5rem;vertical-align:middle"></i> ${__t.scheduled_label}: ${new Date(job.scheduled_date).toLocaleDateString()}${job.scheduled_time ? ' ' + __t.at + ' ' + job.scheduled_time : ''}</li>`;
                if (job.actual_start) timeline += `<li><i class="bi bi-circle-fill text-primary me-1" style="font-size:.5rem;vertical-align:middle"></i> ${__t.started_label}: ${new Date(job.actual_start).toLocaleString()}</li>`;
                if (job.actual_end) timeline += `<li><i class="bi bi-circle-fill text-success me-1" style="font-size:.5rem;vertical-align:middle"></i> ${__t.completed_label}: ${new Date(job.actual_end).toLocaleString()}</li>`;
                timeline += '</ul>';

                document.getElementById('viewJobBody').innerHTML = `
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <div class="text-muted small">${__t.customer}</div>
                            <div class="fw-semibold">${job.customer_name}</div>
                            ${job.customer_email ? '<div class="small">' + job.customer_email + '</div>' : ''}
                            ${job.customer_phone ? '<div class="small">' + job.customer_phone + '</div>' : ''}
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">${__t.install_address}</div>
                            <div>${job.install_address || '—'}</div>
                            ${job.install_city ? '<div class="small text-muted">' + job.install_city + (job.install_state ? ', ' + job.install_state : '') + ' ' + (job.install_zip || '') + '</div>' : ''}
                        </div>
                        <div class="col-md-2">
                            <div class="text-muted small">${__t.status}</div>
                            <span class="badge ${statusBadge[job.status] || 'bg-secondary'}">${job.status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</span>
                        </div>
                        <div class="col-md-2">
                            <div class="text-muted small">${__t.priority}</div>
                            <span class="badge ${priorityBadge[job.priority] || 'bg-secondary'}">${job.priority.charAt(0).toUpperCase() + job.priority.slice(1)}</span>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <div class="text-muted small">${__t.assigned_to}</div>
                            <div class="fw-semibold">${data.assignee ? data.assignee.name : __t.unassigned}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">${__t.estimated_duration}</div>
                            <div>${job.estimated_duration || '—'}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">${__t.created_by}</div>
                            <div>${data.creator ? data.creator.name : '—'}</div>
                        </div>
                    </div>
                    ${job.description ? '<div class="mb-3"><div class="text-muted small">' + __t.description + '</div><div>' + job.description + '</div></div>' : ''}
                    ${job.completion_notes ? '<div class="mb-3"><div class="text-muted small">' + __t.completion_notes + '</div><div>' + job.completion_notes + '</div></div>' : ''}
                    ${timeline}
                    ${notesHtml}
                `;
            });
    });
});

// Add note
document.getElementById('addJobNoteBtn')?.addEventListener('click', function() {
    if (!currentViewJobId) return;
    const noteInput = document.getElementById('newJobNote');
    const note = noteInput.value.trim();
    if (!note) return;

    fetch(`/admin/jobs/${currentViewJobId}/note`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ note })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            noteInput.value = '';
            // Re-trigger view to refresh
            document.querySelector(`.view-job-btn[data-job-id="${currentViewJobId}"]`)?.click();
        }
    });
});

// Send job email
document.querySelectorAll('.send-job-email-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        if (!currentViewJobId) return;
        const slug = this.dataset.slug;
        const resultDiv = document.getElementById('emailSendResult');

        if (!confirm(__t.send_confirm.replace(':type', this.textContent.trim()))) return;

        this.disabled = true;
        const origHTML = this.innerHTML;
        this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        fetch(`/admin/jobs/${currentViewJobId}/send-email`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ template_slug: slug })
        })
        .then(r => r.json())
        .then(data => {
            this.innerHTML = origHTML;
            this.disabled = false;
            resultDiv.style.display = 'block';
            if (data.success) {
                resultDiv.className = 'small mt-2 text-success';
                resultDiv.innerHTML = '<i class="bi bi-check-circle me-1"></i>' + data.message;
            } else {
                resultDiv.className = 'small mt-2 text-danger';
                resultDiv.innerHTML = '<i class="bi bi-exclamation-circle me-1"></i>' + (data.error || __t.failed_to_send);
            }
            setTimeout(() => { resultDiv.style.display = 'none'; }, 5000);
        })
        .catch(() => {
            this.innerHTML = origHTML;
            this.disabled = false;
            resultDiv.style.display = 'block';
            resultDiv.className = 'small mt-2 text-danger';
            resultDiv.textContent = __t.network_error;
        });
    });
});

// Send Estimate (PDF only)
function sendEstimate() {
    if (!currentViewJobId) return;
    const resultDiv = document.getElementById('emailSendResult');
    const btn = document.getElementById('sendEstimateBtn');

    if (!confirm(__t.send_estimate_confirm)) return;

    btn.disabled = true;
    const origHTML = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

    fetch(`/admin/jobs/${currentViewJobId}/send-estimate`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
    })
    .then(r => r.json())
    .then(data => {
        btn.innerHTML = origHTML;
        btn.disabled = false;
        resultDiv.style.display = 'block';
        if (data.success) {
            resultDiv.className = 'small mt-2 text-success';
            resultDiv.innerHTML = '<i class="bi bi-check-circle me-1"></i>' + data.message;
        } else {
            resultDiv.className = 'small mt-2 text-danger';
            resultDiv.innerHTML = '<i class="bi bi-exclamation-circle me-1"></i>' + (data.error || __t.failed_to_send);
        }
        setTimeout(() => { resultDiv.style.display = 'none'; }, 5000);
    })
    .catch(() => {
        btn.innerHTML = origHTML;
        btn.disabled = false;
        resultDiv.style.display = 'block';
        resultDiv.className = 'small mt-2 text-danger';
        resultDiv.textContent = __t.network_error;
    });
}

// Assign job
document.querySelectorAll('.assign-job-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const modal = this.closest('.modal');
        const jobId = this.dataset.jobId;
        const assignedTo = modal.querySelector('.assign-to-select').value;
        const scheduledDate = modal.querySelector('.assign-date').value;
        const scheduledTime = modal.querySelector('.assign-time').value;

        if (!assignedTo) { alert(__t.select_person); return; }

        this.disabled = true;
        fetch(`/admin/jobs/${jobId}/assign`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ assigned_to: assignedTo, scheduled_date: scheduledDate, scheduled_time: scheduledTime })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(modal).hide();
                location.reload();
            } else {
                alert(data.message || __t.failed);
                this.disabled = false;
            }
        });
    });
});

// Update status
document.querySelectorAll('.status-select').forEach(sel => {
    sel.addEventListener('change', function() {
        const field = this.closest('.modal-body').querySelector('.completion-notes-field');
        field.style.display = this.value === 'completed' ? 'block' : 'none';
    });
});

document.querySelectorAll('.update-status-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const modal = this.closest('.modal');
        const jobId = this.dataset.jobId;
        const status = modal.querySelector('.status-select').value;
        const completionNotes = modal.querySelector('.completion-notes').value;

        this.disabled = true;
        fetch(`/admin/jobs/${jobId}/status`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ status, completion_notes: completionNotes })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(modal).hide();
                location.reload();
            } else {
                alert(data.message || __t.failed);
                this.disabled = false;
            }
        });
    });
});
</script>
@endpush
@endsection
