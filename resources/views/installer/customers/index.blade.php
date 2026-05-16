@extends('layouts.installer')
@section('title', 'My Customers')

@section('content')
<div class="container-fluid py-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><i class="bi bi-people me-2"></i>My Customers</h4>
    </div>

    <div class="card">
        <div class="card-body p-0">
            @if($customers->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-people fs-1 d-block mb-2"></i>
                    No customers yet. Customers will appear here as you create quotes and jobs.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Address</th>
                                <th class="text-center">Quotes</th>
                                <th class="text-center">Jobs</th>
                                <th class="text-center">Invoices</th>
                                <th>Last Activity</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customers as $customer)
                                <tr>
                                    <td class="fw-semibold">{{ $customer['name'] }}</td>
                                    <td>{{ $customer['email'] ?: '—' }}</td>
                                    <td>{{ $customer['phone'] ?: '—' }}</td>
                                    <td class="small text-muted">{{ $customer['address'] ?: '—' }}</td>
                                    <td class="text-center">
                                        @if($customer['quotes'] > 0)
                                            <span class="badge bg-secondary">{{ $customer['quotes'] }}</span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($customer['jobs'] > 0)
                                            <span class="badge bg-primary">{{ $customer['jobs'] }}</span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($customer['invoices'] > 0)
                                            <span class="badge bg-success">{{ $customer['invoices'] }}</span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="text-muted small">
                                        {{ $customer['last_activity'] ? \Carbon\Carbon::parse($customer['last_activity'])->format('M d, Y') : '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
