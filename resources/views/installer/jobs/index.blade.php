@extends('layouts.installer')
@section('title', __('installer.my_jobs'))

@push('styles')
<style>
    .iq-container { display: flex; height: calc(100vh - 56px); overflow: hidden; }

    /* ── Left Rail ─────────────────────────────── */
    .iq-rail {
        width: 320px; min-width: 320px;
        background: var(--vip-primary);
        color: #fff;
        display: flex; flex-direction: column;
        border-right: 1px solid rgba(255,255,255,.06);
    }
    .iq-rail-header { padding: 1.25rem 1rem .75rem; }
    .iq-rail-header h6 { font-size: .75rem; text-transform: uppercase; letter-spacing: 1.2px; color: rgba(255,255,255,.5); margin-bottom: .75rem; }
    .iq-rail-search { display: flex; gap: .5rem; }
    .iq-rail-search input {
        flex: 1; background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.12);
        color: #fff; border-radius: .375rem; padding: .4rem .75rem; font-size: .85rem;
    }
    .iq-rail-search input::placeholder { color: rgba(255,255,255,.4); }
    .iq-rail-search input:focus { outline: none; border-color: var(--vip-accent); }

    .iq-rail-tabs { display: flex; gap: 0; padding: 0 1rem; margin-top: .75rem; flex-wrap: wrap; }
    .iq-rail-tabs .tab-btn {
        flex: 1; text-align: center; padding: .4rem .25rem; font-size: .7rem;
        background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.1);
        color: rgba(255,255,255,.6); cursor: pointer; transition: all .15s;
    }
    .iq-rail-tabs .tab-btn:first-child { border-radius: .3rem 0 0 .3rem; }
    .iq-rail-tabs .tab-btn:last-child { border-radius: 0 .3rem .3rem 0; }
    .iq-rail-tabs .tab-btn.active { background: var(--vip-accent); color: #fff; border-color: var(--vip-accent); }

    .iq-rail-list { flex: 1; overflow-y: auto; padding: .5rem; }
    .iq-card {
        background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.08);
        border-radius: .5rem; padding: .75rem 1rem; margin-bottom: .5rem;
        cursor: pointer; transition: all .15s;
    }
    .iq-card:hover { background: rgba(255,255,255,.08); border-color: rgba(201,168,76,.3); }
    .iq-card.active { background: rgba(201,168,76,.12); border-color: var(--vip-accent); }
    .iq-card .q-number { font-weight: 600; font-size: .9rem; color: #fff; }
    .iq-card .q-customer { font-size: .78rem; color: rgba(255,255,255,.55); margin-top: 2px; }
    .iq-card .q-meta { display: flex; justify-content: space-between; align-items: center; margin-top: .35rem; }
    .iq-card .q-date { font-size: .7rem; color: rgba(255,255,255,.4); }
    .iq-card .q-badge { font-size: .6rem; padding: 2px 6px; border-radius: 3px; font-weight: 600; text-transform: uppercase; }
    .q-badge-pending { background: rgba(255,193,7,.25); color: #ffc107; }
    .q-badge-scheduled { background: rgba(23,162,184,.25); color: #17a2b8; }
    .q-badge-in_progress { background: rgba(0,123,255,.25); color: #5ba8ff; }
    .q-badge-completed { background: rgba(40,167,69,.25); color: #7ddf9b; }
    .q-badge-cancelled { background: rgba(220,53,69,.25); color: #dc3545; }

    .iq-rail-footer {
        padding: .75rem 1rem; border-top: 1px solid rgba(255,255,255,.08);
        font-size: .75rem; color: rgba(255,255,255,.4);
        display: flex; justify-content: space-between;
    }

    /* ── Main Panel ────────────────────────────── */
    .iq-main { flex: 1; overflow-y: auto; background: var(--vip-light); }
    .iq-main-toolbar {
        background: #fff; border-bottom: 1px solid rgba(0,0,0,.06);
        padding: .75rem 1.5rem; display: flex; align-items: center; justify-content: space-between;
    }
    .iq-main-toolbar h5 { font-size: 1rem; font-weight: 700; margin: 0; }
    .iq-detail-body { padding: 1.5rem; }

    .iq-empty-state {
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        height: 60vh; color: rgba(0,0,0,.35);
    }
    .iq-empty-state i { font-size: 3rem; margin-bottom: 1rem; }

    .iq-info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
    .iq-info-card { background: #fff; border-radius: .5rem; padding: 1rem; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
    .iq-info-card .label { font-size: .7rem; text-transform: uppercase; letter-spacing: .5px; color: rgba(0,0,0,.45); margin-bottom: .25rem; }
    .iq-info-card .value { font-size: .9rem; font-weight: 600; color: #111; }

    .note-card { background: #fff; border-radius: .375rem; padding: .6rem .75rem; margin-bottom: .5rem; box-shadow: 0 1px 3px rgba(0,0,0,.04); }

    .job-items-tbl { width: 100%; border-collapse: collapse; background: #fff; border-radius: .5rem; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
    .job-items-tbl th { font-size: .7rem; text-transform: uppercase; letter-spacing: .5px; color: rgba(0,0,0,.4); padding: .5rem .75rem; border-bottom: 1px solid rgba(0,0,0,.08); background: #fafafa; }
    .job-items-tbl td { padding: .5rem .75rem; font-size: .82rem; border-bottom: 1px solid rgba(0,0,0,.04); }
    .job-items-tbl .item-done td { text-decoration: line-through; opacity: .5; }
    .item-check { cursor: pointer; }

    @media (max-width: 991.98px) {
        .iq-container { flex-direction: column; height: auto; }
        .iq-rail { width: 100%; min-width: 100%; max-height: 45vh; }
    }
</style>
@endpush

@section('content')
<div class="iq-container">
    {{-- Left Rail --}}
    <div class="iq-rail">
        <div class="iq-rail-header">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">{{ __('installer.my_jobs') }}</h6>
                <button class="btn btn-sm btn-vip" data-bs-toggle="modal" data-bs-target="#createJobModal"><i class="bi bi-plus-lg me-1"></i>{{ __('installer.new_job') }}</button>
            </div>
            <div class="iq-rail-search">
                <input type="text" id="iqSearch" placeholder="Search jobs...">
            </div>
            <div class="iq-rail-tabs">
                <div class="tab-btn {{ $status === 'all' ? 'active' : '' }}" data-status="all">{{ __('installer.all') }}</div>
                <div class="tab-btn {{ $status === 'pending' ? 'active' : '' }}" data-status="pending">{{ __('installer.pending') }}</div>
                <div class="tab-btn {{ $status === 'scheduled' ? 'active' : '' }}" data-status="scheduled">{{ __('installer.scheduled') }}</div>
                <div class="tab-btn {{ $status === 'in_progress' ? 'active' : '' }}" data-status="in_progress">{{ __('installer.in_progress') }}</div>
                <div class="tab-btn {{ $status === 'completed' ? 'active' : '' }}" data-status="completed">{{ __('installer.completed') }}</div>
            </div>
        </div>

        <div class="iq-rail-list">
            @forelse($jobs as $job)
                <div class="iq-card" data-id="{{ $job->id }}" data-search="{{ strtolower(($job->job_number ?? '') . ' ' . ($job->customer_name ?? '') . ' ' . ($job->install_city ?? '')) }}">
                    <div class="q-number">{{ $job->job_number ?? 'JOB-' . $job->id }}</div>
                    <div class="q-customer"><i class="bi bi-person me-1"></i>{{ $job->customer_name ?: 'No customer' }}</div>
                    <div class="q-meta">
                        <span class="q-date">{{ $job->scheduled_date?->format('M d, Y') ?? 'Not scheduled' }}</span>
                        <span class="q-badge q-badge-{{ $job->status }}">{{ ucfirst(str_replace('_', ' ', $job->status)) }}</span>
                    </div>
                </div>
            @empty
                <div class="text-center py-4" style="color:rgba(255,255,255,.4);">
                    <i class="bi bi-tools" style="font-size:2rem;"></i>
                    <p class="mt-2 mb-0">{{ __('installer.no_jobs_found') }}</p>
                </div>
            @endforelse
        </div>

        <div class="iq-rail-footer">
            <span>{{ $jobs->total() }} job{{ $jobs->total() !== 1 ? 's' : '' }}</span>
            <span>{{ $jobs->where('status', 'in_progress')->count() }} active</span>
        </div>
    </div>

    {{-- Main Panel --}}
    <div class="iq-main">
        <div class="iq-main-toolbar">
            <h5 id="iqDetailTitle">{{ __('installer.job_details') }}</h5>
            <div id="iqToolbarActions"></div>
        </div>
        <div class="iq-detail-body" id="iqDetailBody">
            <div class="iq-empty-state">
                <i class="bi bi-tools"></i>
                <p>{{ __('installer.select_job') }}</p>
            </div>
        </div>
    </div>
</div>
{{-- Create Job Modal (compact) --}}
<div class="modal fade" id="createJobModal" tabindex="-1" aria-labelledby="createJobModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="background:var(--vip-primary); color:#fff; border:1px solid rgba(255,255,255,.1);">
            <div class="modal-header border-0 py-2">
                <h6 class="modal-title mb-0" id="createJobModalLabel"><i class="bi bi-tools me-1"></i>New Job</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="createJobForm" method="POST" action="{{ route('installer.jobs.store') }}">
                @csrf
                <div class="modal-body py-2">
                    <div class="row g-2">
                        <div class="col-4">
                            <label class="form-label mb-0" style="font-size:.68rem;color:rgba(255,255,255,.4);">Customer *</label>
                            <input type="text" name="customer_name" class="form-control form-control-sm bg-dark text-white border-secondary" required>
                        </div>
                        <div class="col-4">
                            <label class="form-label mb-0" style="font-size:.68rem;color:rgba(255,255,255,.4);">Email</label>
                            <input type="email" name="customer_email" class="form-control form-control-sm bg-dark text-white border-secondary">
                        </div>
                        <div class="col-4">
                            <label class="form-label mb-0" style="font-size:.68rem;color:rgba(255,255,255,.4);">Phone</label>
                            <input type="text" name="customer_phone" class="form-control form-control-sm bg-dark text-white border-secondary">
                        </div>

                        {{-- Assignment: Crew or Installer toggle --}}
                        <div class="col-12 mt-2">
                            <label class="form-label mb-1" style="font-size:.68rem;color:rgba(255,255,255,.4);">Assign To</label>
                            <div class="d-flex gap-2 mb-1">
                                <div class="btn-group btn-group-sm w-100" role="group">
                                    <input type="radio" class="btn-check" name="assignment_type" value="crew" id="createAssignCrew" checked>
                                    <label class="btn btn-outline-light" for="createAssignCrew"><i class="bi bi-people-fill me-1"></i>Crew</label>
                                    <input type="radio" class="btn-check" name="assignment_type" value="installer" id="createAssignInstaller">
                                    <label class="btn btn-outline-light" for="createAssignInstaller"><i class="bi bi-person-badge me-1"></i>Installer</label>
                                </div>
                            </div>
                            <div id="createCrewSelect">
                                <select name="crew_id" class="form-select form-select-sm bg-dark text-white border-secondary">
                                    <option value="">Select crew...</option>
                                    @foreach($crews as $crew)
                                        <option value="{{ $crew->id }}">{{ $crew->name }} ({{ $crew->members->count() }} members)</option>
                                    @endforeach
                                </select>
                            </div>
                            <div id="createInstallerSelect" style="display:none;">
                                <select name="assigned_to" class="form-select form-select-sm bg-dark text-white border-secondary">
                                    <option value="">Select installer...</option>
                                    @foreach($installers as $inst)
                                        <option value="{{ $inst->id }}">{{ $inst->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-12 mt-1"><hr class="border-secondary my-1"></div>
                        <div class="col-8">
                            <label class="form-label mb-0" style="font-size:.68rem;color:rgba(255,255,255,.4);">Address</label>
                            <input type="text" name="install_address" class="form-control form-control-sm bg-dark text-white border-secondary" placeholder="Street" data-address-autocomplete>
                        </div>
                        <div class="col-4">
                            <label class="form-label mb-0" style="font-size:.68rem;color:rgba(255,255,255,.4);">Priority</label>
                            <select name="priority" class="form-select form-select-sm bg-dark text-white border-secondary">
                                <option value="normal" selected>Normal</option>
                                <option value="low">Low</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div class="col-5">
                            <input type="text" name="install_city" class="form-control form-control-sm bg-dark text-white border-secondary" placeholder="City">
                        </div>
                        <div class="col-4">
                            <input type="text" name="install_state" class="form-control form-control-sm bg-dark text-white border-secondary" placeholder="State">
                        </div>
                        <div class="col-3">
                            <input type="text" name="install_zip" class="form-control form-control-sm bg-dark text-white border-secondary" placeholder="Zip">
                        </div>

                        <div class="col-12 mt-1"><hr class="border-secondary my-1"></div>
                        <div class="col-3">
                            <label class="form-label mb-0" style="font-size:.68rem;color:rgba(255,255,255,.4);">Start Date</label>
                            <input type="date" name="scheduled_date" class="form-control form-control-sm bg-dark text-white border-secondary">
                        </div>
                        <div class="col-3">
                            <label class="form-label mb-0" style="font-size:.68rem;color:rgba(255,255,255,.4);">End Date</label>
                            <input type="date" name="end_date" class="form-control form-control-sm bg-dark text-white border-secondary">
                        </div>
                        <div class="col-3">
                            <label class="form-label mb-0" style="font-size:.68rem;color:rgba(255,255,255,.4);">Time</label>
                            <input type="time" name="scheduled_time" class="form-control form-control-sm bg-dark text-white border-secondary">
                        </div>
                        <div class="col-3">
                            <label class="form-label mb-0" style="font-size:.68rem;color:rgba(255,255,255,.4);">Est. Duration</label>
                            <input type="text" name="estimated_duration" class="form-control form-control-sm bg-dark text-white border-secondary" placeholder="e.g. 2 days">
                        </div>
                        <div class="col-12">
                            <label class="form-label mb-0" style="font-size:.68rem;color:rgba(255,255,255,.4);">Description</label>
                            <textarea name="description" rows="2" class="form-control form-control-sm bg-dark text-white border-secondary" placeholder="Job details..."></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label mb-0" style="font-size:.68rem;color:rgba(255,255,255,.4);">Notes</label>
                            <textarea name="notes" rows="1" class="form-control form-control-sm bg-dark text-white border-secondary" placeholder="Internal notes..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 py-2">
                    <button type="button" class="btn btn-outline-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-vip btn-sm"><i class="bi bi-check-lg me-1"></i>Create Job</button>
                </div>
            </form>
        </div>
    </div>
</div>
{{-- Edit Job Modal (compact) --}}
<div class="modal fade" id="editJobModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="background:var(--vip-primary); color:#fff; border:1px solid rgba(255,255,255,.1);">
            <div class="modal-header border-0 py-2">
                <h6 class="modal-title mb-0"><i class="bi bi-pencil me-1"></i>Edit Job</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-2">
                <div class="row g-2">
                    <div class="col-4">
                        <label class="form-label mb-0" style="font-size:.68rem;color:rgba(255,255,255,.4);">Customer *</label>
                        <input type="text" name="customer_name" class="form-control form-control-sm bg-dark text-white border-secondary" required>
                    </div>
                    <div class="col-4">
                        <label class="form-label mb-0" style="font-size:.68rem;color:rgba(255,255,255,.4);">Email</label>
                        <input type="email" name="customer_email" class="form-control form-control-sm bg-dark text-white border-secondary">
                    </div>
                    <div class="col-4">
                        <label class="form-label mb-0" style="font-size:.68rem;color:rgba(255,255,255,.4);">Phone</label>
                        <input type="text" name="customer_phone" class="form-control form-control-sm bg-dark text-white border-secondary">
                    </div>

                    {{-- Assignment toggle --}}
                    <div class="col-12 mt-2">
                        <label class="form-label mb-1" style="font-size:.68rem;color:rgba(255,255,255,.4);">Assign To</label>
                        <div class="d-flex gap-2 mb-1">
                            <div class="btn-group btn-group-sm w-100" role="group">
                                <input type="radio" class="btn-check" name="assignment_type" value="crew" id="editAssignCrew" checked>
                                <label class="btn btn-outline-light" for="editAssignCrew"><i class="bi bi-people-fill me-1"></i>Crew</label>
                                <input type="radio" class="btn-check" name="assignment_type" value="installer" id="editAssignInstaller">
                                <label class="btn btn-outline-light" for="editAssignInstaller"><i class="bi bi-person-badge me-1"></i>Installer</label>
                            </div>
                        </div>
                        <div id="editCrewSelect">
                            <select name="crew_id" class="form-select form-select-sm bg-dark text-white border-secondary">
                                <option value="">Select crew...</option>
                                @foreach($crews as $crew)
                                    <option value="{{ $crew->id }}">{{ $crew->name }} ({{ $crew->members->count() }} members)</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="editInstallerSelect" style="display:none;">
                            <select name="assigned_to" class="form-select form-select-sm bg-dark text-white border-secondary">
                                <option value="">Select installer...</option>
                                @foreach($installers as $inst)
                                    <option value="{{ $inst->id }}">{{ $inst->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-12 mt-1"><hr class="border-secondary my-1"></div>
                    <div class="col-8">
                        <label class="form-label mb-0" style="font-size:.68rem;color:rgba(255,255,255,.4);">Address</label>
                        <input type="text" name="install_address" class="form-control form-control-sm bg-dark text-white border-secondary" data-address-autocomplete>
                    </div>
                    <div class="col-4">
                        <label class="form-label mb-0" style="font-size:.68rem;color:rgba(255,255,255,.4);">Priority</label>
                        <select name="priority" class="form-select form-select-sm bg-dark text-white border-secondary">
                            <option value="normal">Normal</option>
                            <option value="low">Low</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                    <div class="col-5">
                        <input type="text" name="install_city" class="form-control form-control-sm bg-dark text-white border-secondary" placeholder="City">
                    </div>
                    <div class="col-4">
                        <input type="text" name="install_state" class="form-control form-control-sm bg-dark text-white border-secondary" placeholder="State">
                    </div>
                    <div class="col-3">
                        <input type="text" name="install_zip" class="form-control form-control-sm bg-dark text-white border-secondary" placeholder="Zip">
                    </div>
                    <div class="col-12 mt-1"><hr class="border-secondary my-1"></div>
                    <div class="col-3">
                        <label class="form-label mb-0" style="font-size:.68rem;color:rgba(255,255,255,.4);">Start Date</label>
                        <input type="date" name="scheduled_date" class="form-control form-control-sm bg-dark text-white border-secondary">
                    </div>
                    <div class="col-3">
                        <label class="form-label mb-0" style="font-size:.68rem;color:rgba(255,255,255,.4);">End Date</label>
                        <input type="date" name="end_date" class="form-control form-control-sm bg-dark text-white border-secondary">
                    </div>
                    <div class="col-3">
                        <label class="form-label mb-0" style="font-size:.68rem;color:rgba(255,255,255,.4);">Time</label>
                        <input type="time" name="scheduled_time" class="form-control form-control-sm bg-dark text-white border-secondary">
                    </div>
                    <div class="col-3">
                        <label class="form-label mb-0" style="font-size:.68rem;color:rgba(255,255,255,.4);">Est. Duration</label>
                        <input type="text" name="estimated_duration" class="form-control form-control-sm bg-dark text-white border-secondary">
                    </div>
                    <div class="col-12">
                        <label class="form-label mb-0" style="font-size:.68rem;color:rgba(255,255,255,.4);">Description</label>
                        <textarea name="description" rows="2" class="form-control form-control-sm bg-dark text-white border-secondary"></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label mb-0" style="font-size:.68rem;color:rgba(255,255,255,.4);">Notes</label>
                        <textarea name="notes" rows="1" class="form-control form-control-sm bg-dark text-white border-secondary"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 py-2">
                <button type="button" class="btn btn-outline-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-vip btn-sm" onclick="saveEditJob()"><i class="bi bi-check-lg me-1"></i>Save Changes</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Services data for item service dropdown
@php
$servicesJson = $services->map(function($s) {
    return ['id' => $s->id, 'name' => $s->name, 'installer_pay' => $s->installer_pay, 'installer_pay_type' => $s->installer_pay_type, 'base_price' => $s->base_price];
});
@endphp
const servicesData = @json($servicesJson);

document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.iq-card');
    const detailBody = document.getElementById('iqDetailBody');
    const detailTitle = document.getElementById('iqDetailTitle');
    const toolbarActions = document.getElementById('iqToolbarActions');
    const csrf = document.querySelector('meta[name=csrf-token]').content;
    let currentJobId = null;
    let currentJobData = null;

    // Assignment type toggle for create modal
    document.querySelectorAll('#createJobModal input[name="assignment_type"]').forEach(r => {
        r.addEventListener('change', function() {
            document.getElementById('createCrewSelect').style.display = this.value === 'crew' ? '' : 'none';
            document.getElementById('createInstallerSelect').style.display = this.value === 'installer' ? '' : 'none';
        });
    });
    // Assignment type toggle for edit modal
    document.querySelectorAll('#editJobModal input[name="assignment_type"]').forEach(r => {
        r.addEventListener('change', function() {
            document.getElementById('editCrewSelect').style.display = this.value === 'crew' ? '' : 'none';
            document.getElementById('editInstallerSelect').style.display = this.value === 'installer' ? '' : 'none';
        });
    });

    // Tab filters
    document.querySelectorAll('.iq-rail-tabs .tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const status = this.dataset.status;
            const url = new URL(window.location);
            if (status !== 'all') url.searchParams.set('status', status);
            else url.searchParams.delete('status');
            window.location = url;
        });
    });

    // Search
    document.getElementById('iqSearch').addEventListener('input', function() {
        const term = this.value.toLowerCase();
        document.querySelectorAll('.iq-card').forEach(card => {
            card.style.display = (!term || card.dataset.search.includes(term)) ? '' : 'none';
        });
    });

    // Load detail
    cards.forEach(card => {
        card.addEventListener('click', function() {
            cards.forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            loadDetail(this.dataset.id);
        });
    });

    function loadDetail(id) {
        detailBody.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-secondary"></div></div>';

        fetch(`/installer/jobs/${id}`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }})
            .then(r => r.json())
            .then(data => {
                const j = data.job;
                const notes = data.notes || [];
                const items = data.items || [];
                const totalPay = parseFloat(data.total_pay || 0);
                detailTitle.textContent = j.job_number || ('JOB-' + j.id);

                currentJobId = j.id;
                currentJobData = j;
                const isClockedIn = data.is_clocked_in;
                const activeSince = data.active_since;
                const timeLogs = data.time_logs || [];
                const totalTimeMins = data.total_time_minutes || 0;
                const imageUrl = data.image_url;

                let actions = '';
                // Clock in/out buttons
                if (isClockedIn) {
                    actions += `<button class="btn btn-sm btn-warning" onclick="clockOut(${j.id})"><i class="bi bi-stop-circle me-1"></i>Clock Out</button> `;
                } else if (j.status !== 'completed' && j.status !== 'cancelled') {
                    actions += `<button class="btn btn-sm btn-info text-white" onclick="clockIn(${j.id})"><i class="bi bi-play-circle me-1"></i>Clock In</button> `;
                }
                if (j.status === 'pending' || j.status === 'scheduled') {
                    actions += `<form method="POST" action="/installer/jobs/${j.id}/status" class="d-inline"><input type="hidden" name="_token" value="${csrf}"><input type="hidden" name="status" value="in_progress"><button class="btn btn-sm btn-primary"><i class="bi bi-play-fill me-1"></i>Start</button></form> `;
                } else if (j.status === 'in_progress') {
                    actions += `<form method="POST" action="/installer/jobs/${j.id}/status" class="d-inline"><input type="hidden" name="_token" value="${csrf}"><input type="hidden" name="status" value="completed"><button class="btn btn-sm btn-success"><i class="bi bi-check-lg me-1"></i>Complete</button></form> `;
                }
                actions += `<button class="btn btn-sm btn-outline-primary" onclick="openEditJob()" title="Edit"><i class="bi bi-pencil"></i></button> `;
                actions += `<button class="btn btn-sm btn-outline-danger" onclick="deleteJob(${j.id}, '${j.job_number || 'JOB-' + j.id}')" title="Delete"><i class="bi bi-trash"></i></button>`;
                toolbarActions.innerHTML = actions;

                // Date display
                let dateDisplay = j.scheduled_date || 'Not set';
                if (j.end_date && j.end_date !== j.scheduled_date) {
                    dateDisplay = (j.scheduled_date || '?') + ' → ' + j.end_date;
                }
                if (j.scheduled_time) dateDisplay += ' @ ' + j.scheduled_time;

                // Notes HTML
                let notesHtml = notes.length
                    ? notes.map(n => `<div class="note-card"><div class="d-flex justify-content-between"><strong class="small">${n.author || 'System'}</strong><span class="text-muted small">${n.created_at || ''}</span></div><p class="mb-0 small mt-1">${n.note}</p></div>`).join('')
                    : '<p class="text-muted small">No notes yet.</p>';

                // Service dropdown options
                let svcOptions = '<option value="">— No service —</option>' + servicesData.map(s => `<option value="${s.id}">${s.name} ($${parseFloat(s.installer_pay).toFixed(2)}/${s.installer_pay_type?.replace(/_/g,' ') || 'unit'})</option>`).join('');

                // Items table with pay columns
                let itemsHtml = '';
                if (items.length) {
                    itemsHtml = `<table class="job-items-tbl mb-2">
                        <thead><tr><th style="width:28px;"></th><th>Item</th><th>Service</th><th class="text-center">Qty</th><th class="text-end">Unit Pay</th><th class="text-end">Total Pay</th><th style="width:32px;"></th></tr></thead>
                        <tbody>${items.map(i => `<tr class="${i.completed ? 'item-done' : ''}">
                            <td class="text-center"><input type="checkbox" class="item-check" ${i.completed ? 'checked' : ''} onchange="toggleJobItem(${j.id}, ${i.id})"></td>
                            <td>${i.description}${i.notes ? '<br><small class="text-muted">' + i.notes + '</small>' : ''}</td>
                            <td><span class="badge bg-light text-dark" style="font-size:.6rem;">${i.service_name || i.item_type || '—'}</span></td>
                            <td class="text-center">${parseFloat(i.qty)}</td>
                            <td class="text-end text-muted">$${parseFloat(i.unit_pay || 0).toFixed(2)}</td>
                            <td class="text-end fw-semibold text-success">$${parseFloat(i.total_pay || 0).toFixed(2)}</td>
                            <td class="text-center"><button class="btn btn-sm text-danger p-0" onclick="removeJobItem(${j.id}, ${i.id})" title="Remove"><i class="bi bi-x-lg" style="font-size:.65rem;"></i></button></td>
                        </tr>`).join('')}
                        <tr style="background:#f0fdf4;"><td colspan="5" class="text-end fw-bold small" style="padding-right:8px;">TOTAL PAY</td><td class="text-end fw-bold text-success">$${totalPay.toFixed(2)}</td><td></td></tr>
                        </tbody>
                    </table>`;
                } else {
                    itemsHtml = '<p class="text-muted small">No items added yet.</p>';
                }

                // Time log display
                const totalHrs = Math.floor(totalTimeMins / 60);
                const totalMins = totalTimeMins % 60;
                let clockStatusHtml = '';
                if (isClockedIn) {
                    clockStatusHtml = `<div class="iq-info-card" style="background:linear-gradient(135deg,#0d6efd,#0a58ca);color:#fff;"><div class="label" style="color:rgba(255,255,255,.6);"><i class="bi bi-clock-fill me-1"></i>Clocked In</div><div class="value" style="color:#fff;">Since ${activeSince}</div></div>`;
                } else {
                    clockStatusHtml = `<div class="iq-info-card"><div class="label"><i class="bi bi-clock me-1"></i>Time Logged</div><div class="value">${totalHrs}h ${totalMins}m</div></div>`;
                }

                detailBody.innerHTML = `
                    <div class="iq-info-grid">
                        <div class="iq-info-card"><div class="label">Customer</div><div class="value">${j.customer_name || '—'}</div></div>
                        <div class="iq-info-card"><div class="label">Phone</div><div class="value">${j.customer_phone || '—'}</div></div>
                        <div class="iq-info-card"><div class="label">Status</div><div class="value"><span class="badge badge-${j.status}">${j.status ? j.status.replace('_',' ').replace(/^./,c=>c.toUpperCase()) : '—'}</span></div></div>
                        <div class="iq-info-card"><div class="label">Schedule</div><div class="value">${dateDisplay}</div></div>
                        ${clockStatusHtml}
                        <div class="iq-info-card" style="background:linear-gradient(135deg,#198754,#157347);color:#fff;"><div class="label" style="color:rgba(255,255,255,.6);">My Pay</div><div class="value" style="color:#fff;font-size:1.1rem;">$${totalPay.toFixed(2)}</div></div>
                    </div>

                    ${j.install_address ? `
                    <div class="card mb-3" style="border:none; box-shadow:0 1px 4px rgba(0,0,0,.06);">
                        <div class="card-body py-2 px-3">
                            <div class="small text-muted text-uppercase mb-1" style="letter-spacing:.5px;">Install Address</div>
                            <div>${j.install_address}<br>${j.install_city || ''}, ${j.install_state || ''} ${j.install_zip || ''}</div>
                        </div>
                    </div>` : ''}

                    ${j.description ? `<div class="card mb-3" style="border:none;box-shadow:0 1px 4px rgba(0,0,0,.06);"><div class="card-body py-2 px-3"><div class="small text-muted text-uppercase mb-1" style="letter-spacing:.5px;">Description</div><p class="mb-0 small">${j.description}</p></div></div>` : ''}

                    <h6 class="mb-2 mt-3" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.5px;color:rgba(0,0,0,.5);"><i class="bi bi-list-check me-1"></i>Line Items & Pay</h6>
                    ${itemsHtml}

                    <div class="card mb-3" style="border:none; box-shadow:0 1px 4px rgba(0,0,0,.06);">
                        <div class="card-body py-2 px-3">
                            <div class="row g-2 align-items-end">
                                <div class="col-md-3">
                                    <label style="font-size:.65rem;color:#999;">Service</label>
                                    <select id="addJobItemService" class="form-select form-select-sm" onchange="onServiceSelect()">${svcOptions}</select>
                                </div>
                                <div class="col-md-3"><label style="font-size:.65rem;color:#999;">Description</label><input type="text" id="addJobItemDesc" class="form-control form-control-sm" placeholder="e.g. Double Hung 36x48"></div>
                                <div class="col-md-2">
                                    <label style="font-size:.65rem;color:#999;">Type</label>
                                    <select id="addJobItemType" class="form-select form-select-sm">
                                        <option value="window">Window</option>
                                        <option value="door">Door</option>
                                        <option value="service">Service</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-2"><label style="font-size:.65rem;color:#999;">Qty</label><input type="number" id="addJobItemQty" class="form-control form-control-sm" value="1" min="1" step="1"></div>
                                <div class="col-md-2"><button class="btn btn-sm btn-vip w-100" onclick="addJobItem(${j.id})"><i class="bi bi-plus me-1"></i>Add</button></div>
                            </div>
                        </div>
                    </div>

                    <h6 class="mb-2 mt-3" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.5px;color:rgba(0,0,0,.5);"><i class="bi bi-image me-1"></i>Job Photo</h6>
                    <div class="card mb-3" style="border:none; box-shadow:0 1px 4px rgba(0,0,0,.06);">
                        <div class="card-body py-2 px-3">
                            ${imageUrl ? `<img src="${imageUrl}" class="img-fluid rounded mb-2" style="max-height:200px; object-fit:cover;">` : '<p class="text-muted small mb-2">No photo uploaded yet.</p>'}
                            <form id="imageUploadForm" onsubmit="uploadJobImage(event, ${j.id})" enctype="multipart/form-data">
                                <div class="input-group input-group-sm">
                                    <input type="file" name="image" accept="image/*" class="form-control" required>
                                    <button class="btn btn-vip" type="submit"><i class="bi bi-upload me-1"></i>Upload</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    ${timeLogs.length ? `
                    <h6 class="mb-2 mt-3" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.5px;color:rgba(0,0,0,.5);"><i class="bi bi-clock-history me-1"></i>Time Log</h6>
                    <div class="card mb-3" style="border:none; box-shadow:0 1px 4px rgba(0,0,0,.06);">
                        <div class="card-body py-2 px-3">
                            <table class="table table-sm table-borderless mb-0" style="font-size:.78rem;">
                                <thead><tr><th>In</th><th>Out</th><th class="text-end">Duration</th></tr></thead>
                                <tbody>${timeLogs.map(l => {
                                    const mins = l.total_minutes || 0;
                                    const dur = l.is_active ? '<span class="badge bg-primary">Active</span>' : (Math.floor(mins/60) + 'h ' + (mins%60) + 'm');
                                    return '<tr><td>' + (l.clock_in || '—') + '</td><td>' + (l.clock_out || '—') + '</td><td class="text-end">' + dur + '</td></tr>';
                                }).join('')}
                                <tr style="border-top:1px solid rgba(0,0,0,.1);"><td colspan="2" class="text-end fw-bold">Total</td><td class="text-end fw-bold">${totalHrs}h ${totalMins}m</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>` : ''}

                    <h6 class="mb-2 mt-3" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.5px;color:rgba(0,0,0,.5);"><i class="bi bi-chat-left-text me-1"></i>Notes</h6>
                    ${notesHtml}
                    <form method="POST" action="/installer/jobs/${j.id}/note" class="mt-2">
                        <input type="hidden" name="_token" value="${csrf}">
                        <div class="input-group input-group-sm">
                            <input type="text" name="note" class="form-control" placeholder="Add a note..." required>
                            <button class="btn btn-vip" type="submit"><i class="bi bi-plus"></i></button>
                        </div>
                    </form>
                `;
            })
            .catch(() => {
                detailBody.innerHTML = '<div class="alert alert-danger m-4">Failed to load job details.</div>';
            });
    }

    // When a service is selected, auto-fill description
    window.onServiceSelect = function() {
        const sel = document.getElementById('addJobItemService');
        const svc = servicesData.find(s => s.id == sel.value);
        if (svc) {
            const descEl = document.getElementById('addJobItemDesc');
            if (!descEl.value) descEl.value = svc.name;
        }
    };

    // Job items management
    window.addJobItem = function(jobId) {
        const desc = document.getElementById('addJobItemDesc')?.value?.trim();
        const itemType = document.getElementById('addJobItemType')?.value;
        const qty = parseFloat(document.getElementById('addJobItemQty')?.value || 1);
        const serviceId = document.getElementById('addJobItemService')?.value || null;

        if (!desc) { alert('Please enter an item description.'); return; }

        fetch(`/installer/jobs/${jobId}/item`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify({ description: desc, item_type: itemType, qty: qty, service_id: serviceId || null, notes: null })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) loadDetail(jobId);
            else alert(data.error || 'Failed to add item.');
        })
        .catch(() => alert('Failed to add item.'));
    };

    window.removeJobItem = function(jobId, itemId) {
        if (!confirm('Remove this item?')) return;
        fetch(`/installer/jobs/${jobId}/item/${itemId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => { if (data.success) loadDetail(jobId); })
        .catch(() => alert('Failed to remove item.'));
    };

    window.toggleJobItem = function(jobId, itemId) {
        fetch(`/installer/jobs/${jobId}/item/${itemId}/toggle`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => { if (data.success) loadDetail(jobId); })
        .catch(() => alert('Failed to toggle item.'));
    };

    // Edit job
    window.openEditJob = function() {
        if (!currentJobData) return;
        const j = currentJobData;
        const modal = document.getElementById('editJobModal');
        modal.querySelector('[name="customer_name"]').value = j.customer_name || '';
        modal.querySelector('[name="customer_email"]').value = j.customer_email || '';
        modal.querySelector('[name="customer_phone"]').value = j.customer_phone || '';
        modal.querySelector('[name="install_address"]').value = j.install_address || '';
        modal.querySelector('[name="install_city"]').value = j.install_city || '';
        modal.querySelector('[name="install_state"]').value = j.install_state || '';
        modal.querySelector('[name="install_zip"]').value = j.install_zip || '';
        modal.querySelector('[name="priority"]').value = j.priority || 'normal';
        modal.querySelector('[name="scheduled_date"]').value = j.scheduled_date ? j.scheduled_date.substring(0,10) : '';
        modal.querySelector('[name="end_date"]').value = j.end_date ? j.end_date.substring(0,10) : '';
        modal.querySelector('[name="scheduled_time"]').value = j.scheduled_time || '';
        modal.querySelector('[name="estimated_duration"]').value = j.estimated_duration || '';
        modal.querySelector('[name="description"]').value = j.description || '';
        modal.querySelector('[name="notes"]').value = j.notes || '';

        // Set assignment type
        const aType = j.assignment_type || 'crew';
        modal.querySelector(`input[name="assignment_type"][value="${aType}"]`).checked = true;
        document.getElementById('editCrewSelect').style.display = aType === 'crew' ? '' : 'none';
        document.getElementById('editInstallerSelect').style.display = aType === 'installer' ? '' : 'none';
        if (j.crew_id) modal.querySelector('#editCrewSelect select').value = j.crew_id;
        if (j.assigned_to) modal.querySelector('#editInstallerSelect select').value = j.assigned_to;

        new bootstrap.Modal(modal).show();
    };

    window.saveEditJob = function() {
        if (!currentJobId) return;
        const modal = document.getElementById('editJobModal');
        const formData = {};
        modal.querySelectorAll('input[name]:not([type="radio"]), select[name], textarea[name]').forEach(el => {
            formData[el.name] = el.value;
        });
        // Get checked radio
        const checkedRadio = modal.querySelector('input[name="assignment_type"]:checked');
        if (checkedRadio) formData.assignment_type = checkedRadio.value;

        fetch(`/installer/jobs/${currentJobId}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify(formData)
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(modal).hide();
                loadDetail(currentJobId);
                const card = document.querySelector(`.iq-card[data-id="${currentJobId}"]`);
                if (card) card.querySelector('.q-customer').innerHTML = '<i class="bi bi-person me-1"></i>' + (formData.customer_name || 'No customer');
            } else alert('Failed to update job.');
        })
        .catch(() => alert('Failed to update job.'));
    };

    // Delete job
    window.deleteJob = function(jobId, jobNumber) {
        if (!confirm(`Delete job ${jobNumber}? This cannot be undone.`)) return;
        fetch(`/installer/jobs/${jobId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const card = document.querySelector(`.iq-card[data-id="${jobId}"]`);
                if (card) card.remove();
                detailBody.innerHTML = '<div class="iq-empty-state"><i class="bi bi-tools"></i><p>Job deleted. Select another job.</p></div>';
                toolbarActions.innerHTML = '';
                detailTitle.textContent = 'Job Details';
            } else alert('Failed to delete job.');
        })
        .catch(() => alert('Failed to delete job.'));
    };

    // Clock in/out
    window.clockIn = function(jobId) {
        fetch(`/installer/jobs/${jobId}/clock-in`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) { loadDetail(jobId); }
            else alert(data.error || 'Failed to clock in.');
        })
        .catch(() => alert('Failed to clock in.'));
    };

    window.clockOut = function(jobId) {
        fetch(`/installer/jobs/${jobId}/clock-out`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                loadDetail(jobId);
            } else alert(data.error || 'Failed to clock out.');
        })
        .catch(() => alert('Failed to clock out.'));
    };

    // Image upload
    window.uploadJobImage = function(e, jobId) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);

        fetch(`/installer/jobs/${jobId}/upload-image`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) loadDetail(jobId);
            else alert('Failed to upload image.');
        })
        .catch(() => alert('Failed to upload image.'));
    };

    // Auto-select: highlighted job from URL param, or first card
    const urlParams = new URLSearchParams(window.location.search);
    const highlightId = urlParams.get('highlight');
    let autoSelected = false;
    if (highlightId) {
        cards.forEach(card => {
            if (card.dataset.id === highlightId) {
                card.click();
                card.scrollIntoView({ block: 'center' });
                autoSelected = true;
            }
        });
    }
    if (!autoSelected && cards.length > 0) cards[0].click();
});
</script>
@endpush
