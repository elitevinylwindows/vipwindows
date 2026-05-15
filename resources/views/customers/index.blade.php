@extends('layouts.app')
@section('title', 'Customers')

@section('content')
<div class="container-fluid py-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><i class="bi bi-people me-2"></i>Customers</h4>
        <button class="btn btn-vip" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
            <i class="bi bi-person-plus me-1"></i> Add Customer
        </button>
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
                                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#viewCustomer{{ $customer->id }}" title="View">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editCustomer{{ $customer->id }}" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <a href="{{ route('admin.email.compose', ['to' => $customer->email]) }}" class="btn btn-sm btn-outline-success" title="Email">
                                            <i class="bi bi-envelope"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.customers.destroy', $customer->id) }}" class="d-inline" onsubmit="return confirm('Remove this customer?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>

                                {{-- View modal --}}
                                <div class="modal fade" id="viewCustomer{{ $customer->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title"><i class="bi bi-person me-1"></i> {{ $customer->name }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <div class="text-muted small">Email</div>
                                                        <div class="fw-semibold">{{ $customer->email }}</div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="text-muted small">Phone</div>
                                                        <div class="fw-semibold">{{ $customer->phone ?: '—' }}</div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="text-muted small">Address</div>
                                                        <div class="fw-semibold">
                                                            @if($customer->address)
                                                                {{ $customer->address }}<br>
                                                                {{ $customer->city }}, {{ $customer->state }} {{ $customer->zip }}
                                                            @else
                                                                —
                                                            @endif
                                                        </div>
                                                    </div>
                                                    @if($customer->notes)
                                                        <div class="col-12">
                                                            <div class="text-muted small">Notes</div>
                                                            <div>{{ $customer->notes }}</div>
                                                        </div>
                                                    @endif
                                                    <div class="col-md-6">
                                                        <div class="text-muted small">Customer Since</div>
                                                        <div class="fw-semibold">{{ $customer->created_at->format('F j, Y') }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <a href="{{ route('admin.email.compose', ['to' => $customer->email]) }}" class="btn btn-outline-success btn-sm"><i class="bi bi-envelope me-1"></i> Email</a>
                                                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-dismiss="modal" onclick="setTimeout(()=>new bootstrap.Modal(document.getElementById('editCustomer{{ $customer->id }}')).show(), 300)">
                                                    <i class="bi bi-pencil me-1"></i> Edit
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Edit modal --}}
                                <div class="modal fade" id="editCustomer{{ $customer->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('admin.customers.update', $customer->id) }}">
                                                @csrf @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title"><i class="bi bi-pencil me-1"></i> Edit Customer</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                                            <input type="text" name="name" class="form-control" value="{{ $customer->name }}" required>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Email <span class="text-danger">*</span></label>
                                                            <input type="email" name="email" class="form-control" value="{{ $customer->email }}" required>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Phone</label>
                                                        <input type="text" name="phone" class="form-control" value="{{ $customer->phone }}">
                                                    </div>
                                                    <hr class="my-2">
                                                    <div class="mb-3">
                                                        <label class="form-label">Street Address</label>
                                                        <input type="text" name="address" class="form-control" value="{{ $customer->address }}">
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-5 mb-3">
                                                            <label class="form-label">City</label>
                                                            <input type="text" name="city" class="form-control" value="{{ $customer->city }}">
                                                        </div>
                                                        <div class="col-md-4 mb-3">
                                                            <label class="form-label">State</label>
                                                            <input type="text" name="state" class="form-control" value="{{ $customer->state }}">
                                                        </div>
                                                        <div class="col-md-3 mb-3">
                                                            <label class="form-label">ZIP</label>
                                                            <input type="text" name="zip" class="form-control" value="{{ $customer->zip }}">
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Notes</label>
                                                        <textarea name="notes" class="form-control" rows="2">{{ $customer->notes }}</textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" class="btn btn-vip">Save Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
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

{{-- Add Customer modal --}}
<div class="modal fade" id="addCustomerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.customers.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-person-plus me-1"></i> Add New Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" placeholder="(555) 123-4567">
                    </div>
                    <hr class="my-2">
                    <h6 class="text-muted small mb-2">Address</h6>
                    <div class="mb-3">
                        <label class="form-label">Street Address</label>
                        <input type="text" name="address" class="form-control">
                    </div>
                    <div class="row">
                        <div class="col-md-5 mb-3">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">State</label>
                            <input type="text" name="state" class="form-control" value="CA">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">ZIP</label>
                            <input type="text" name="zip" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Any notes about this customer..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-vip"><i class="bi bi-person-plus me-1"></i> Add Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
