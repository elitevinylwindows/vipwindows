@extends('layouts.app')
@section('title', 'Service Areas')

@section('content')
<div class="container-fluid py-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><i class="bi bi-geo-alt me-2"></i>Service Areas</h4>
        <button class="btn btn-vip" data-bs-toggle="modal" data-bs-target="#addAreaModal">
            <i class="bi bi-plus-circle me-1"></i> Add Area
        </button>
    </div>

    <div class="card">
        <div class="card-body p-0">
            @if($areas->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-geo-alt fs-1 d-block mb-2"></i>
                    No service areas added yet. Add your first one to display on the website.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Description</th>
                                <th>State</th>
                                <th>Order</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($areas as $area)
                                <tr>
                                    <td class="fw-semibold">{{ $area->name }}</td>
                                    <td class="text-muted small">{{ $area->description ?: '—' }}</td>
                                    <td>{{ $area->state }}</td>
                                    <td>{{ $area->sort_order }}</td>
                                    <td>
                                        @if($area->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Hidden</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editArea{{ $area->id }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form method="POST" action="{{ route('admin.service-areas.destroy', $area->id) }}" class="d-inline" onsubmit="return confirm('Remove this area?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>

                                {{-- Edit modal --}}
                                <div class="modal fade" id="editArea{{ $area->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('admin.service-areas.update', $area->id) }}">
                                                @csrf @method('PUT')
                                                <div class="modal-header"><h5 class="modal-title">Edit Service Area</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Name</label>
                                                        <input type="text" name="name" class="form-control" value="{{ $area->name }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Description</label>
                                                        <input type="text" name="description" class="form-control" value="{{ $area->description }}" placeholder="e.g. And surrounding neighborhoods">
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">State</label>
                                                            <input type="text" name="state" class="form-control" value="{{ $area->state }}" required>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Sort Order</label>
                                                            <input type="number" name="sort_order" class="form-control" value="{{ $area->sort_order }}">
                                                        </div>
                                                    </div>
                                                    <div class="form-check">
                                                        <input type="checkbox" name="is_active" value="1" class="form-check-input" {{ $area->is_active ? 'checked' : '' }}>
                                                        <label class="form-check-label">Visible on website</label>
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
            @endif
        </div>
    </div>
</div>

{{-- Add modal --}}
<div class="modal fade" id="addAreaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.service-areas.store') }}">
                @csrf
                <div class="modal-header"><h5 class="modal-title"><i class="bi bi-geo-alt me-1"></i> Add Service Area</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Long Beach" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description <span class="text-muted">(optional)</span></label>
                        <input type="text" name="description" class="form-control" placeholder="e.g. Long Beach and surrounding areas">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">State</label>
                            <input type="text" name="state" class="form-control" value="CA" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" value="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-vip"><i class="bi bi-plus-circle me-1"></i> Add Area</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
