@extends('layouts.app')
@section('title', 'Service Rates')

@section('content')
<div class="container-fluid py-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('admin.settings.index') }}" class="text-decoration-none text-muted small">
                <i class="bi bi-arrow-left me-1"></i> Back to Settings
            </a>
            <h4 class="fw-bold mt-1 mb-0"><i class="bi bi-cash-stack me-2"></i>Service Rates</h4>
        </div>
        <button class="btn btn-vip" data-bs-toggle="modal" data-bs-target="#addRateModal">
            <i class="bi bi-plus-circle me-1"></i> Add Rate
        </button>
    </div>

    {{-- Category tabs --}}
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link {{ $category === 'all' ? 'active' : '' }}" href="{{ route('admin.settings.rates', ['category' => 'all']) }}">All</a>
        </li>
        @foreach($categories as $key => $label)
            <li class="nav-item">
                <a class="nav-link {{ $category === $key ? 'active' : '' }}" href="{{ route('admin.settings.rates', ['category' => $key]) }}">{{ $label }}</a>
            </li>
        @endforeach
    </ul>

    {{-- Rates table --}}
    @if($rates->isEmpty())
        <div class="card">
            <div class="card-body text-center py-5 text-muted">
                <i class="bi bi-cash-stack fs-1 d-block mb-2"></i>
                No service rates configured yet. Click "Add Rate" to create one.
            </div>
        </div>
    @else
        @foreach($grouped as $cat => $catRates)
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bi bi-tag me-1"></i>
                        {{ $categories[$cat] ?? ucwords(str_replace('_', ' ', $cat)) }}
                        <span class="badge bg-secondary ms-1">{{ $catRates->count() }}</span>
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th class="text-end">Cost Rate</th>
                                    <th class="text-end">Charge Rate</th>
                                    <th class="text-center">Unit</th>
                                    <th class="text-center">Active</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($catRates as $rate)
                                    <tr class="{{ !$rate->is_active ? 'opacity-50' : '' }}">
                                        <td class="fw-semibold">{{ $rate->name }}</td>
                                        <td class="small text-muted">{{ $rate->description ?: '—' }}</td>
                                        <td class="text-end">${{ number_format($rate->cost_rate, 2) }}</td>
                                        <td class="text-end fw-semibold">${{ number_format($rate->charge_rate, 2) }}</td>
                                        <td class="text-center">
                                            @php
                                                $unitLabels = ['per_hour' => 'Per Hour', 'per_unit' => 'Per Unit', 'flat' => 'Flat'];
                                            @endphp
                                            <span class="badge bg-light text-dark">{{ $unitLabels[$rate->unit] ?? $rate->unit }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if($rate->is_active)
                                                <i class="bi bi-check-circle-fill text-success"></i>
                                            @else
                                                <i class="bi bi-x-circle text-danger"></i>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-outline-dark" data-bs-toggle="modal" data-bs-target="#editRateModal{{ $rate->id }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteRateModal{{ $rate->id }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Edit & Delete modals for each rate --}}
            @foreach($catRates as $rate)
                {{-- Edit Modal --}}
                <div class="modal fade" id="editRateModal{{ $rate->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST" action="{{ route('admin.settings.rates.update', $rate->id) }}">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="filter_category" value="{{ $category }}">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Rate</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Category</label>
                                        <select name="category" class="form-select" required>
                                            @foreach($categories as $key => $label)
                                                <option value="{{ $key }}" {{ $rate->category === $key ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Name</label>
                                        <input type="text" name="name" class="form-control" value="{{ $rate->name }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Description</label>
                                        <input type="text" name="description" class="form-control" value="{{ $rate->description }}">
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Cost Rate ($)</label>
                                            <input type="number" name="cost_rate" class="form-control" value="{{ $rate->cost_rate }}" step="0.01" min="0" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Charge Rate ($)</label>
                                            <input type="number" name="charge_rate" class="form-control" value="{{ $rate->charge_rate }}" step="0.01" min="0" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Unit</label>
                                        <select name="unit" class="form-select" required>
                                            <option value="per_hour" {{ $rate->unit === 'per_hour' ? 'selected' : '' }}>Per Hour</option>
                                            <option value="per_unit" {{ $rate->unit === 'per_unit' ? 'selected' : '' }}>Per Unit</option>
                                            <option value="flat" {{ $rate->unit === 'flat' ? 'selected' : '' }}>Flat</option>
                                        </select>
                                    </div>
                                    <div class="form-check">
                                        <input type="hidden" name="is_active" value="0">
                                        <input type="checkbox" name="is_active" value="1" class="form-check-input" {{ $rate->is_active ? 'checked' : '' }}>
                                        <label class="form-check-label">Active</label>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-vip">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Delete Modal --}}
                <div class="modal fade" id="deleteRateModal{{ $rate->id }}" tabindex="-1">
                    <div class="modal-dialog modal-sm">
                        <div class="modal-content">
                            <form method="POST" action="{{ route('admin.settings.rates.destroy', $rate->id) }}">
                                @csrf
                                @method('DELETE')
                                <div class="modal-header">
                                    <h5 class="modal-title">Delete Rate</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Are you sure you want to delete <strong>{{ $rate->name }}</strong>?</p>
                                    <p class="text-muted small mb-0">This action cannot be undone.</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        @endforeach
    @endif
</div>

{{-- Add Rate Modal --}}
<div class="modal fade" id="addRateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.settings.rates.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Service Rate</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select" required>
                            <option value="">Select category...</option>
                            @foreach($categories as $key => $label)
                                <option value="{{ $key }}" {{ $category !== 'all' && $category === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Standard Labor" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <input type="text" name="description" class="form-control" placeholder="Optional description">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Cost Rate ($)</label>
                            <input type="number" name="cost_rate" class="form-control" step="0.01" min="0" placeholder="0.00" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Charge Rate ($)</label>
                            <input type="number" name="charge_rate" class="form-control" step="0.01" min="0" placeholder="0.00" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Unit</label>
                        <select name="unit" class="form-select" required>
                            <option value="per_hour">Per Hour</option>
                            <option value="per_unit">Per Unit</option>
                            <option value="flat">Flat</option>
                        </select>
                    </div>
                    <div class="form-check">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" class="form-check-input" checked>
                        <label class="form-check-label">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-vip">Add Rate</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
