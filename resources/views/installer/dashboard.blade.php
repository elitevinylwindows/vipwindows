@extends('layouts.installer')
@section('title', __('installer.dashboard'))

@section('content')
<div class="container-fluid py-4 px-4">
    {{-- Welcome header + quick actions --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">{{ __('installer.welcome_back') }}, {{ Auth::user()->name }}</h4>
            <p class="text-muted mb-0 small">{{ __('installer.recent_activity') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.quotes.create') }}" class="btn btn-vip">
                <i class="bi bi-plus-circle me-1"></i> {{ __('installer.new_quote') }}
            </a>
            <a href="{{ route('installer.jobs.index') }}" class="btn btn-outline-dark">
                <i class="bi bi-calendar3 me-1"></i> {{ __('installer.view_calendar') }}
            </a>
        </div>
    </div>

    {{-- Stats cards --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-4 col-md-6">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small mb-1">{{ __('installer.total_quotes') }}</div>
                            <h3 class="fw-bold mb-0">{{ $totalQuotes }}</h3>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:44px;height:44px;background:rgba(201,168,76,.15);">
                            <i class="bi bi-calculator" style="color:var(--vip-accent);font-size:1.2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small mb-1">{{ __('installer.sent') }} {{ __('installer.quotes') }}</div>
                            <h3 class="fw-bold mb-0">{{ $sentQuotes }}</h3>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:44px;height:44px;background:rgba(0,0,0,.06);">
                            <i class="bi bi-send" style="font-size:1.2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small mb-1">{{ __('installer.in_progress') }}</div>
                            <h3 class="fw-bold mb-0">{{ $activeJobs }}</h3>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:44px;height:44px;background:rgba(0,123,255,.12);">
                            <i class="bi bi-tools text-primary" style="font-size:1.2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small mb-1">{{ __('installer.completed') }}</div>
                            <h3 class="fw-bold mb-0">{{ $completedJobs }}</h3>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:44px;height:44px;background:rgba(40,167,69,.12);">
                            <i class="bi bi-check-circle text-success" style="font-size:1.2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small mb-1">{{ __('installer.pending_invoices') }}</div>
                            <h3 class="fw-bold mb-0">{{ $pendingInvoices }}</h3>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:44px;height:44px;background:rgba(255,193,7,.15);">
                            <i class="bi bi-receipt text-warning" style="font-size:1.2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small mb-1">{{ __('installer.total') }}</div>
                            <h3 class="fw-bold mb-0">${{ number_format($totalEarnings, 2) }}</h3>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:44px;height:44px;background:rgba(40,167,69,.12);">
                            <i class="bi bi-currency-dollar text-success" style="font-size:1.2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Earnings Overview --}}
    <div class="row g-3 mb-4">
        <div class="col-12">
            <h6 class="text-muted fw-semibold mb-0"><i class="bi bi-wallet2 me-1"></i> My Earnings</h6>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 h-100" style="background: linear-gradient(135deg, #198754 0%, #157347 100%); color: #fff;">
                <div class="card-body">
                    <div class="small opacity-75 mb-1">Completed Job Pay</div>
                    <h3 class="fw-bold mb-0">${{ number_format($completedJobPay, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 h-100" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); color: #fff;">
                <div class="card-body">
                    <div class="small opacity-75 mb-1">Upcoming Job Pay</div>
                    <h3 class="fw-bold mb-0">${{ number_format($pendingJobPay, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 h-100" style="background: linear-gradient(135deg, #6f42c1 0%, #59359a 100%); color: #fff;">
                <div class="card-body">
                    <div class="small opacity-75 mb-1">Total All Jobs</div>
                    <h3 class="fw-bold mb-0">${{ number_format($totalJobPay, 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Time-Based Pay (from clock in/out) --}}
    <div class="row g-3 mb-4">
        <div class="col-12">
            <h6 class="text-muted fw-semibold mb-0"><i class="bi bi-clock-history me-1"></i> Time-Based Pay</h6>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">This Month</div>
                    <h3 class="fw-bold mb-0 text-success">${{ number_format($thisMonthTimePay, 2) }}</h3>
                    <div class="small text-muted mt-1">
                        {{ $thisMonthTimeJobs }} job{{ $thisMonthTimeJobs !== 1 ? 's' : '' }} &middot;
                        {{ intdiv($thisMonthTimeMinutes, 60) }}h {{ $thisMonthTimeMinutes % 60 }}m
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">All Time</div>
                    <h3 class="fw-bold mb-0" style="color:var(--vip-accent);">${{ number_format($allTimeTimePay, 2) }}</h3>
                </div>
            </div>
        </div>
        @foreach($monthlyTimePay->take(2) as $mp)
            <div class="col-lg-3 col-md-6">
                <div class="card stat-card h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-1">{{ \Carbon\Carbon::parse($mp->month . '-01')->format('F Y') }}</div>
                        <h4 class="fw-bold mb-0">${{ number_format($mp->total_earnings, 2) }}</h4>
                        <div class="small text-muted mt-1">
                            {{ $mp->total_jobs }} job{{ $mp->total_jobs !== 1 ? 's' : '' }} &middot;
                            {{ intdiv($mp->total_minutes, 60) }}h {{ $mp->total_minutes % 60 }}m
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Recent Time Logs --}}
    @if($recentTimeLogs->isNotEmpty())
    <div class="card mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="fw-semibold mb-0"><i class="bi bi-clock me-2"></i> Recent Time Logs</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Job</th>
                            <th>Service</th>
                            <th>Clock In</th>
                            <th>Clock Out</th>
                            <th>Duration</th>
                            <th class="text-end">Pay</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentTimeLogs as $tl)
                            @php
                                $th = intdiv($tl->total_minutes, 60);
                                $tm = $tl->total_minutes % 60;
                            @endphp
                            <tr>
                                <td>{{ $tl->clock_in->format('M d, Y') }}</td>
                                <td class="fw-semibold">{{ $tl->job?->job_number ?? '—' }}</td>
                                <td>
                                    @if($tl->job?->service)
                                        <span class="badge" style="background:{{ $tl->job->service->color ?? '#6c757d' }}; font-size:.65rem;">{{ $tl->job->service->name }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>{{ $tl->clock_in->format('g:i A') }}</td>
                                <td>{{ $tl->clock_out->format('g:i A') }}</td>
                                <td class="fw-semibold">{{ $th }}h {{ $tm }}m</td>
                                <td class="text-end fw-bold text-success">
                                    @if(($tl->earnings ?? 0) > 0)
                                        ${{ number_format($tl->earnings, 2) }}
                                    @else
                                        <span class="text-muted">$0.00</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- Monthly History --}}
    @if($monthlyTimePay->count() > 2)
    <div class="card mb-4">
        <div class="card-header bg-white">
            <h6 class="fw-semibold mb-0"><i class="bi bi-bar-chart me-2"></i> Monthly Pay History</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Month</th>
                            <th>Jobs</th>
                            <th>Hours</th>
                            <th class="text-end">Earnings</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($monthlyTimePay as $mp)
                            <tr>
                                <td class="fw-semibold">{{ \Carbon\Carbon::parse($mp->month . '-01')->format('F Y') }}</td>
                                <td>{{ $mp->total_jobs }}</td>
                                <td>{{ intdiv($mp->total_minutes, 60) }}h {{ $mp->total_minutes % 60 }}m</td>
                                <td class="text-end fw-bold text-success">${{ number_format($mp->total_earnings, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- Earnings Breakdown by Job --}}
    @if($recentPaidJobs->isNotEmpty())
    <div class="card mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="fw-semibold mb-0"><i class="bi bi-cash-stack me-2"></i> Completed Job Pay Breakdown</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Job #</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Pay Breakdown</th>
                            <th class="text-end">Total Pay</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentPaidJobs as $pj)
                            @php $jobTotal = $pj->jobItems->sum('total_pay'); @endphp
                            <tr>
                                <td class="fw-semibold">{{ $pj->job_number }}</td>
                                <td>{{ $pj->customer_name ?: '—' }}</td>
                                <td>{{ $pj->jobItems->count() }}</td>
                                <td>
                                    @foreach($pj->jobItems as $ji)
                                        <div class="small">
                                            <span class="fw-semibold">{{ $ji->description }}</span>
                                            <span class="text-muted">× {{ intval($ji->qty) }}</span>
                                            @if($ji->unit_pay > 0)
                                                <span class="text-muted">@ ${{ number_format($ji->unit_pay, 2) }}</span>
                                            @endif
                                            <span class="text-success fw-semibold">= ${{ number_format($ji->total_pay, 2) }}</span>
                                        </div>
                                    @endforeach
                                </td>
                                <td class="text-end fw-bold text-success">${{ number_format($jobTotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- Recent activity --}}
    <div class="row g-4">
        {{-- Recent Quotes --}}
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="fw-semibold mb-0"><i class="bi bi-calculator me-2"></i>{{ __('installer.my_quotes') }}</h6>
                    <a href="{{ route('installer.quotes.index') }}" class="btn btn-sm btn-outline-dark">{{ __('installer.all') }}</a>
                </div>
                <div class="card-body p-0">
                    @if($recentQuotes->isEmpty())
                        <div class="text-center py-4 text-muted small">{{ __('installer.no_quotes_found') }}</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('installer.quote_number') }}</th>
                                        <th>{{ __('installer.customer_name') }}</th>
                                        <th>{{ __('installer.quantity') }}</th>
                                        <th>{{ __('installer.status') }}</th>
                                        <th>{{ __('installer.created') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentQuotes as $quote)
                                        <tr>
                                            <td class="fw-semibold">{{ $quote->quote_number }}</td>
                                            <td>{{ $quote->billing_name ?: $quote->customer_number ?: '—' }}</td>
                                            <td>{{ $quote->items->count() }}</td>
                                            <td>
                                                @if($quote->status === 'draft')
                                                    <span class="badge bg-secondary">{{ __('installer.draft') }}</span>
                                                @elseif($quote->status === 'sent')
                                                    <span class="badge bg-success">{{ __('installer.sent') }}</span>
                                                @else
                                                    <span class="badge bg-info">{{ ucfirst($quote->status) }}</span>
                                                @endif
                                            </td>
                                            <td class="text-muted small">{{ $quote->created_at?->format('M d') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Recent Jobs --}}
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="fw-semibold mb-0"><i class="bi bi-tools me-2"></i>{{ __('installer.my_jobs') }}</h6>
                    <a href="{{ route('installer.jobs.index') }}" class="btn btn-sm btn-outline-dark">{{ __('installer.all') }}</a>
                </div>
                <div class="card-body p-0">
                    @if($recentJobs->isEmpty())
                        <div class="text-center py-4 text-muted small">{{ __('installer.no_jobs_found') }}</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('installer.job_number') }}</th>
                                        <th>{{ __('installer.customer_name') }}</th>
                                        <th>{{ __('installer.status') }}</th>
                                        <th>Pay</th>
                                        <th>{{ __('installer.created') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentJobs as $job)
                                        @php $jobPay = $job->jobItems->sum('total_pay'); @endphp
                                        <tr>
                                            <td class="fw-semibold">{{ $job->job_number }}</td>
                                            <td>{{ $job->customer_name ?: '—' }}</td>
                                            <td><span class="badge badge-{{ $job->status }}">{{ ucfirst(str_replace('_', ' ', $job->status)) }}</span></td>
                                            <td class="fw-semibold {{ $jobPay > 0 ? 'text-success' : 'text-muted' }}">{{ $jobPay > 0 ? '$' . number_format($jobPay, 2) : '—' }}</td>
                                            <td class="text-muted small">{{ $job->scheduled_date?->format('M d') ?: $job->created_at?->format('M d') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
