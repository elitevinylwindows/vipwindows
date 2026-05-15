@extends('layouts.app')
@section('title', 'Customers')

@section('content')
<div class="container-fluid py-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><i class="bi bi-people me-2"></i>Customers</h4>
        <a href="{{ route('admin.customers.create') }}" class="btn btn-vip">
            <i class="bi bi-person-plus me-1"></i> Add Customer
        </a>
    </div>

    {{-- Search --}}
    <div class="card mb-4">
        <div class="card-body py-2">
            <form method="GET" class="d-flex gap-2">
                <input type="text" name="search" class="form-control" placeholder="Search by name, email, or phone..." value="{{ request('search') }}">
                <button class="btn btn-vip"><i class="bi bi-search"></i></button>
                @if(request('search'))
                    <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">Clear</a>
                @endif
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            @if($customers->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-people fs-1 d-block mb-2"></i>
                    No customers found.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>City</th>
                                <th>Joined</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customers as $customer)
                                <tr>
                                    <td class="fw-semibold">{{ $customer->name }}</td>
                                    <td>{{ $customer->email }}</td>
                                    <td>{{ $customer->phone ?: '—' }}</td>
                                    <td>{{ $customer->city ? $customer->city . ', ' . $customer->state : '—' }}</td>
                                    <td class="text-muted small">{{ $customer->created_at->format('M d, Y') }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.customers.show', $customer->id) }}" class="btn btn-sm btn-outline-secondary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.customers.edit', $customer->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="{{ route('admin.email.compose', ['to' => $customer->email]) }}" class="btn btn-sm btn-outline-success" title="Email">
                                            <i class="bi bi-envelope"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3">
                    {{ $customers->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
