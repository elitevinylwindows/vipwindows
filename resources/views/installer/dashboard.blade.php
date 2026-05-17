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
                                        <th>{{ __('installer.created') }}</th>
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
