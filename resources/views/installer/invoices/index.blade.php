@extends('layouts.installer')
@section('title', 'My Invoices')

@section('content')
<div class="container-fluid py-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><i class="bi bi-receipt me-2"></i>My Invoices</h4>
    </div>

    {{-- Status filter --}}
    <div class="mb-4">
        <div class="btn-group btn-group-sm" role="group">
            <a href="{{ route('installer.invoices.index') }}" class="btn {{ $status === 'all' ? 'btn-dark' : 'btn-outline-dark' }}">All</a>
            <a href="{{ route('installer.invoices.index', ['status' => 'draft']) }}" class="btn {{ $status === 'draft' ? 'btn-dark' : 'btn-outline-dark' }}">Draft</a>
            <a href="{{ route('installer.invoices.index', ['status' => 'sent']) }}" class="btn {{ $status === 'sent' ? 'btn-dark' : 'btn-outline-dark' }}">Sent</a>
            <a href="{{ route('installer.invoices.index', ['status' => 'paid']) }}" class="btn {{ $status === 'paid' ? 'btn-dark' : 'btn-outline-dark' }}">Paid</a>
            <a href="{{ route('installer.invoices.index', ['status' => 'partial']) }}" class="btn {{ $status === 'partial' ? 'btn-dark' : 'btn-outline-dark' }}">Partial</a>
            <a href="{{ route('installer.invoices.index', ['status' => 'overdue']) }}" class="btn {{ $status === 'overdue' ? 'btn-dark' : 'btn-outline-dark' }}">Overdue</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            @if($invoices->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-receipt fs-1 d-block mb-2"></i>
                    No invoices found.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Invoice #</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Due Date</th>
                                <th class="text-end">Total</th>
                                <th class="text-end">Paid</th>
                                <th class="text-end">Balance</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoices as $invoice)
                                <tr>
                                    <td class="fw-semibold">{{ $invoice->invoice_number }}</td>
                                    <td>{{ $invoice->customer_name }}</td>
                                    <td class="text-muted small">{{ $invoice->created_at?->format('M d, Y') }}</td>
                                    <td class="text-muted small">{{ $invoice->due_date?->format('M d, Y') ?? '—' }}</td>
                                    <td class="text-end">${{ number_format($invoice->total, 2) }}</td>
                                    <td class="text-end">${{ number_format($invoice->amount_paid, 2) }}</td>
                                    <td class="text-end fw-semibold">${{ number_format($invoice->balance_due, 2) }}</td>
                                    <td>
                                        @switch($invoice->status)
                                            @case('draft')
                                                <span class="badge bg-secondary">Draft</span>
                                                @break
                                            @case('sent')
                                                <span class="badge bg-primary">Sent</span>
                                                @break
                                            @case('paid')
                                                <span class="badge bg-success">Paid</span>
                                                @break
                                            @case('partial')
                                                <span class="badge bg-warning text-dark">Partial</span>
                                                @break
                                            @case('overdue')
                                                <span class="badge bg-danger">Overdue</span>
                                                @break
                                            @case('cancelled')
                                                <span class="badge bg-dark">Cancelled</span>
                                                @break
                                        @endswitch
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3">{{ $invoices->appends(request()->query())->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
