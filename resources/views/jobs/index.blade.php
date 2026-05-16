@extends('layouts.app')
@section('title', 'Jobs')

@section('content')
<div class="container-fluid py-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><i class="bi bi-tools me-2"></i>Jobs</h4>
        <button class="btn btn-vip" data-bs-toggle="modal" data-bs-target="#createJobModal">
            <i class="bi bi-plus-circle me-1"></i> New Job
        </button>
    </div>

    {{-- Stats cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body py-3">
                    <div class="text-muted small">Today's Jobs</div>
                    <div class="fs-4 fw-bold">{{ $todayJobs }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body py-3">
                    <div class="text-muted small">Upcoming This Week</div>
                    <div class="fs-4 fw-bold">{{ $weekJobs }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body py-3">
                    <div class="text-muted small">In Progress</div>
                    <div class="fs-4 fw-bold text-primary">{{ $inProgress }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body py-3">
                    <div class="text-muted small">Completed This Month</div>
                    <div class="fs-4 fw-bold text-success">{{ $completedMonth }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Status filter --}}
    <div class="mb-4">
        <div class="btn-group btn-group-sm" role="group">
            <a href="{{ route('admin.jobs.index') }}" class="btn {{ $status === 'all' ? 'btn-dark' : 'btn-outline-dark' }}">All</a>
            <a href="{{ route('admin.jobs.index', ['status' => 'pending']) }}" class="btn {{ $status === 'pending' ? 'btn-dark' : 'btn-outline-dark' }}">Pending</a>
            <a href="{{ route('admin.jobs.index', ['status' => 'scheduled']) }}" class="btn {{ $status === 'scheduled' ? 'btn-dark' : 'btn-outline-dark' }}">Scheduled</a>
            <a href="{{ route('admin.jobs.index', ['status' => 'in_progress']) }}" class="btn {{ $status === 'in_progress' ? 'btn-dark' : 'btn-outline-dark' }}">In Progress</a>
            <a href="{{ route('admin.jobs.index', ['status' => 'completed']) }}" class="btn {{ $status === 'completed' ? 'btn-dark' : 'btn-outline-dark' }}">Completed</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            @if($jobs->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-tools fs-1 d-block mb-2"></i>
                    No jobs yet. Create your first one.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Job #</th>
                                <th>Customer</th>
                                <th>Address</th>
                                <th>Assigned To</th>
                                <th>Date</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($jobs as $job)
                                <tr>
                                    <td class="fw-semibold">{{ $job->job_number }}</td>
                                    <td>{{ $job->customer_name }}</td>
                                    <td class="small">
                                        @if($job->install_address)
                                            {{ $job->install_address }}<br>
                                            <span class="text-muted">{{ $job->install_city }}{{ $job->install_state ? ', '.$job->install_state : '' }} {{ $job->install_zip }}</span>
                                        @else
                                            —
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
                                                <span class="badge bg-secondary">Low</span>
                                                @break
                                            @case('normal')
                                                <span class="badge bg-primary">Normal</span>
                                                @break
                                            @case('high')
                                                <span class="badge bg-warning text-dark">High</span>
                                                @break
                                            @case('urgent')
                                                <span class="badge bg-danger">Urgent</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td>
                                        @switch($job->status)
                                            @case('pending')
                                                <span class="badge bg-warning text-dark">Pending</span>
                                                @break
                                            @case('scheduled')
                                                <span class="badge bg-info">Scheduled</span>
                                                @break
                                            @case('in_progress')
                                                <span class="badge bg-primary">In Progress</span>
                                                @break
                                            @case('completed')
                                                <span class="badge bg-success">Completed</span>
                                                @break
                                            @case('cancelled')
                                                <span class="badge bg-dark">Cancelled</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-secondary view-job-btn" data-job-id="{{ $job->id }}" title="View">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editJob{{ $job->id }}" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#assignJob{{ $job->id }}" title="Assign">
                                            <i class="bi bi-person-check"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#statusJob{{ $job->id }}" title="Update Status">
                                            <i class="bi bi-arrow-repeat"></i>
                                        </button>
                                        <form method="POST" action="{{ route('admin.jobs.destroy', $job->id) }}" class="d-inline" onsubmit="return confirm('Delete this job?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
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
                                                    <h5 class="modal-title"><i class="bi bi-pencil me-1"></i> Edit Job {{ $job->job_number }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Customer Name <span class="text-danger">*</span></label>
                                                            <input type="text" name="customer_name" class="form-control" value="{{ $job->customer_name }}" required>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Email</label>
                                                            <input type="email" name="customer_email" class="form-control" value="{{ $job->customer_email }}">
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Phone</label>
                                                        <input type="text" name="customer_phone" class="form-control" value="{{ $job->customer_phone }}">
                                                    </div>
                                                    <hr class="my-2">
                                                    <h6 class="text-muted small mb-2">Install Address</h6>
                                                    <div class="mb-3">
                                                        <input type="text" name="install_address" class="form-control" placeholder="Street Address" value="{{ $job->install_address }}">
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-5 mb-3">
                                                            <input type="text" name="install_city" class="form-control" placeholder="City" value="{{ $job->install_city }}">
                                                        </div>
                                                        <div class="col-md-4 mb-3">
                                                            <input type="text" name="install_state" class="form-control" placeholder="State" value="{{ $job->install_state }}">
                                                        </div>
                                                        <div class="col-md-3 mb-3">
                                                            <input type="text" name="install_zip" class="form-control" placeholder="ZIP" value="{{ $job->install_zip }}">
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Description</label>
                                                        <textarea name="description" class="form-control" rows="2">{{ $job->description }}</textarea>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-4 mb-3">
                                                            <label class="form-label">Priority</label>
                                                            <select name="priority" class="form-select">
                                                                <option value="low" {{ $job->priority === 'low' ? 'selected' : '' }}>Low</option>
                                                                <option value="normal" {{ $job->priority === 'normal' ? 'selected' : '' }}>Normal</option>
                                                                <option value="high" {{ $job->priority === 'high' ? 'selected' : '' }}>High</option>
                                                                <option value="urgent" {{ $job->priority === 'urgent' ? 'selected' : '' }}>Urgent</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4 mb-3">
                                                            <label class="form-label">Scheduled Date</label>
                                                            <input type="date" name="scheduled_date" class="form-control" value="{{ $job->scheduled_date?->format('Y-m-d') }}">
                                                        </div>
                                                        <div class="col-md-4 mb-3">
                                                            <label class="form-label">Scheduled Time</label>
                                                            <input type="text" name="scheduled_time" class="form-control" placeholder="e.g. 9:00 AM" value="{{ $job->scheduled_time }}">
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Estimated Duration</label>
                                                        <input type="text" name="estimated_duration" class="form-control" placeholder="e.g. 4 hours" value="{{ $job->estimated_duration }}">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Notes</label>
                                                        <textarea name="notes" class="form-control" rows="2">{{ $job->notes }}</textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" class="btn btn-vip">Save Changes</button>
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
                                                <h5 class="modal-title"><i class="bi bi-person-check me-1"></i> Assign Job {{ $job->job_number }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Assign To <span class="text-danger">*</span></label>
                                                    <select class="form-select assign-to-select">
                                                        <option value="">— Select —</option>
                                                        @foreach($technicians as $tech)
                                                            <option value="{{ $tech->id }}" {{ $job->assigned_to == $tech->id ? 'selected' : '' }}>
                                                                {{ $tech->name }} ({{ ucfirst($tech->role) }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Scheduled Date</label>
                                                    <input type="date" class="form-control assign-date" value="{{ $job->scheduled_date?->format('Y-m-d') }}">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Scheduled Time</label>
                                                    <input type="text" class="form-control assign-time" placeholder="e.g. 9:00 AM" value="{{ $job->scheduled_time }}">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-vip assign-job-btn" data-job-id="{{ $job->id }}">
                                                    <i class="bi bi-check-circle me-1"></i> Assign
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
                                                <h5 class="modal-title"><i class="bi bi-arrow-repeat me-1"></i> Update Status — {{ $job->job_number }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Status <span class="text-danger">*</span></label>
                                                    <select class="form-select status-select">
                                                        <option value="pending" {{ $job->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                                        <option value="scheduled" {{ $job->status === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                                        <option value="in_progress" {{ $job->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                                        <option value="completed" {{ $job->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                                        <option value="cancelled" {{ $job->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3 completion-notes-field" style="display:none;">
                                                    <label class="form-label">Completion Notes</label>
                                                    <textarea class="form-control completion-notes" rows="3" placeholder="Notes about the completed job..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-vip update-status-btn" data-job-id="{{ $job->id }}">
                                                    <i class="bi bi-check-circle me-1"></i> Update Status
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
            <div class="modal-footer">
                <div class="w-100">
                    <h6 class="fw-semibold mb-2">Add Note</h6>
                    <div class="input-group">
                        <input type="text" class="form-control" id="newJobNote" placeholder="Type a note...">
                        <button class="btn btn-vip" id="addJobNoteBtn"><i class="bi bi-plus"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Create Job modal --}}
<div class="modal fade" id="createJobModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.jobs.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-1"></i> New Job</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Create from Quote</label>
                            <select name="from_quote" class="form-select" id="jobFromQuote">
                                <option value="">— None —</option>
                                @foreach($quotes as $q)
                                    <option value="{{ $q->id }}" data-name="{{ $q->billing_name }}">
                                        {{ $q->quote_number }} — {{ $q->billing_name ?: 'No Name' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Create from Invoice</label>
                            <select name="from_invoice" class="form-select" id="jobFromInvoice">
                                <option value="">— None —</option>
                                @foreach($invoices as $inv)
                                    <option value="{{ $inv->id }}" data-name="{{ $inv->customer_name }}" data-email="{{ $inv->customer_email }}" data-phone="{{ $inv->customer_phone }}" data-address="{{ $inv->customer_address }}">
                                        {{ $inv->invoice_number }} — {{ $inv->customer_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Customer Name <span class="text-danger">*</span></label>
                            <input type="text" name="customer_name" class="form-control" id="jobCustName" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="customer_email" class="form-control" id="jobCustEmail">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="customer_phone" class="form-control" id="jobCustPhone">
                    </div>
                    <hr class="my-2">
                    <h6 class="text-muted small mb-2">Install Address</h6>
                    <div class="mb-3">
                        <input type="text" name="install_address" class="form-control" placeholder="Street Address" id="jobInstallAddr">
                    </div>
                    <div class="row">
                        <div class="col-md-5 mb-3">
                            <input type="text" name="install_city" class="form-control" placeholder="City" id="jobInstallCity">
                        </div>
                        <div class="col-md-4 mb-3">
                            <input type="text" name="install_state" class="form-control" placeholder="State" value="CA" id="jobInstallState">
                        </div>
                        <div class="col-md-3 mb-3">
                            <input type="text" name="install_zip" class="form-control" placeholder="ZIP" id="jobInstallZip">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Job description..."></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Priority</label>
                            <select name="priority" class="form-select">
                                <option value="low">Low</option>
                                <option value="normal" selected>Normal</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Assigned To</label>
                            <select name="assigned_to" class="form-select">
                                <option value="">— Unassigned —</option>
                                @foreach($technicians as $tech)
                                    <option value="{{ $tech->id }}">{{ $tech->name }} ({{ ucfirst($tech->role) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Estimated Duration</label>
                            <input type="text" name="estimated_duration" class="form-control" placeholder="e.g. 4 hours">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Scheduled Date</label>
                            <input type="date" name="scheduled_date" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Scheduled Time</label>
                            <input type="text" name="scheduled_time" class="form-control" placeholder="e.g. 9:00 AM">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-vip"><i class="bi bi-plus-circle me-1"></i> Create Job</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
const csrfToken = '{{ csrf_token() }}';
let currentViewJobId = null;

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
        document.getElementById('viewJobTitle').textContent = 'Loading...';
        document.getElementById('viewJobBody').innerHTML = '<div class="text-center py-4"><div class="spinner-border text-muted" role="status"></div></div>';
        new bootstrap.Modal(document.getElementById('viewJobModal')).show();

        fetch(`/admin/jobs/${id}`)
            .then(r => r.json())
            .then(data => {
                const job = data.job;
                document.getElementById('viewJobTitle').textContent = 'Job ' + job.job_number;

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
                    notesHtml = '<h6 class="fw-semibold mt-3">Notes</h6><div class="list-group">';
                    data.notes.forEach(n => {
                        const date = new Date(n.created_at).toLocaleDateString();
                        notesHtml += `<div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <small class="fw-semibold">${n.author ? n.author.name : 'Unknown'}</small>
                                <small class="text-muted">${date}</small>
                            </div>
                            <div>${n.note}</div>
                        </div>`;
                    });
                    notesHtml += '</div>';
                }

                // Timeline
                let timeline = '<h6 class="fw-semibold mt-3">Timeline</h6><ul class="list-unstyled">';
                timeline += `<li><i class="bi bi-circle-fill text-muted me-1" style="font-size:.5rem;vertical-align:middle"></i> Created: ${new Date(job.created_at).toLocaleDateString()}</li>`;
                if (job.scheduled_date) timeline += `<li><i class="bi bi-circle-fill text-info me-1" style="font-size:.5rem;vertical-align:middle"></i> Scheduled: ${new Date(job.scheduled_date).toLocaleDateString()}${job.scheduled_time ? ' at ' + job.scheduled_time : ''}</li>`;
                if (job.actual_start) timeline += `<li><i class="bi bi-circle-fill text-primary me-1" style="font-size:.5rem;vertical-align:middle"></i> Started: ${new Date(job.actual_start).toLocaleString()}</li>`;
                if (job.actual_end) timeline += `<li><i class="bi bi-circle-fill text-success me-1" style="font-size:.5rem;vertical-align:middle"></i> Completed: ${new Date(job.actual_end).toLocaleString()}</li>`;
                timeline += '</ul>';

                document.getElementById('viewJobBody').innerHTML = `
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <div class="text-muted small">Customer</div>
                            <div class="fw-semibold">${job.customer_name}</div>
                            ${job.customer_email ? '<div class="small">' + job.customer_email + '</div>' : ''}
                            ${job.customer_phone ? '<div class="small">' + job.customer_phone + '</div>' : ''}
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Install Address</div>
                            <div>${job.install_address || '—'}</div>
                            ${job.install_city ? '<div class="small text-muted">' + job.install_city + (job.install_state ? ', ' + job.install_state : '') + ' ' + (job.install_zip || '') + '</div>' : ''}
                        </div>
                        <div class="col-md-2">
                            <div class="text-muted small">Status</div>
                            <span class="badge ${statusBadge[job.status] || 'bg-secondary'}">${job.status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</span>
                        </div>
                        <div class="col-md-2">
                            <div class="text-muted small">Priority</div>
                            <span class="badge ${priorityBadge[job.priority] || 'bg-secondary'}">${job.priority.charAt(0).toUpperCase() + job.priority.slice(1)}</span>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <div class="text-muted small">Assigned To</div>
                            <div class="fw-semibold">${data.assignee ? data.assignee.name : 'Unassigned'}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Estimated Duration</div>
                            <div>${job.estimated_duration || '—'}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Created By</div>
                            <div>${data.creator ? data.creator.name : '—'}</div>
                        </div>
                    </div>
                    ${job.description ? '<div class="mb-3"><div class="text-muted small">Description</div><div>' + job.description + '</div></div>' : ''}
                    ${job.completion_notes ? '<div class="mb-3"><div class="text-muted small">Completion Notes</div><div>' + job.completion_notes + '</div></div>' : ''}
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

// Assign job
document.querySelectorAll('.assign-job-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const modal = this.closest('.modal');
        const jobId = this.dataset.jobId;
        const assignedTo = modal.querySelector('.assign-to-select').value;
        const scheduledDate = modal.querySelector('.assign-date').value;
        const scheduledTime = modal.querySelector('.assign-time').value;

        if (!assignedTo) { alert('Select a person to assign'); return; }

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
                alert(data.message || 'Failed');
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
                alert(data.message || 'Failed');
                this.disabled = false;
            }
        });
    });
});
</script>
@endpush
@endsection
