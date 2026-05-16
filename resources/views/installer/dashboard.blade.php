@extends('layouts.installer')
@section('title', 'Dashboard')

@section('content')
<div class="container-fluid py-4 px-4">
    {{-- Welcome header + quick actions --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Welcome back, {{ Auth::user()->name }}</h4>
            <p class="text-muted mb-0 small">Here's your activity overview.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.quotes.create') }}" class="btn btn-vip">
                <i class="bi bi-plus-circle me-1"></i> New Quote
            </a>
            <a href="{{ route('installer.jobs.index') }}" class="btn btn-outline-dark">
                <i class="bi bi-calendar3 me-1"></i> View Schedule
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
                            <div class="text-muted small mb-1">Total Quotes</div>
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
                            <div class="text-muted small mb-1">Sent Quotes</div>
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
                            <div class="text-muted small mb-1">Active Jobs</div>
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
                            <div class="text-muted small mb-1">Completed Jobs</div>
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
                            <div class="text-muted small mb-1">Pending Invoices</div>
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
                            <div class="text-muted small mb-1">Total Earnings</div>
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

    {{-- Recent activity --}}
    <div class="row g-4">
        {{-- Recent Quotes --}}
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="fw-semibold mb-0"><i class="bi bi-calculator me-2"></i>Recent Quotes</h6>
                    <a href="{{ route('installer.quotes.index') }}" class="btn btn-sm btn-outline-dark">View All</a>
                </div>
                <div class="card-body p-0">
                    @if($recentQuotes->isEmpty())
                        <div class="text-center py-4 text-muted small">No quotes yet.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Quote #</th>
                                        <th>Customer</th>
                                        <th>Items</th>
                                        <th>Status</th>
                                        <th>Date</th>
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
                                                    <span class="badge bg-secondary">Draft</span>
                                                @elseif($quote->status === 'sent')
                                                    <span class="badge bg-success">Sent</span>
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
                    <h6 class="fw-semibold mb-0"><i class="bi bi-tools me-2"></i>Recent Jobs</h6>
                    <a href="{{ route('installer.jobs.index') }}" class="btn btn-sm btn-outline-dark">View All</a>
                </div>
                <div class="card-body p-0">
                    @if($recentJobs->isEmpty())
                        <div class="text-center py-4 text-muted small">No jobs assigned yet.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Job #</th>
                                        <th>Customer</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentJobs as $job)
                                        <tr>
                                            <td class="fw-semibold">{{ $job->job_number }}</td>
                                            <td>{{ $job->customer_name ?: '—' }}</td>
                                            <td><span class="badge badge-{{ $job->status }}">{{ ucfirst(str_replace('_', ' ', $job->status)) }}</span></td>
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
