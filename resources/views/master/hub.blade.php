@extends('layouts.app')

@section('title', 'Master Data')

@section('content')
<div class="p-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--vip-primary);">
                <i class="bi bi-database-gear me-2" style="color: var(--vip-accent);"></i>Master Data
            </h4>
            <p class="text-muted mb-0 small">Manage configurator data — series, colors, glass, profiles, pricing, and grids.</p>
        </div>
    </div>

    <div class="row g-4">

        {{-- Series --}}
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center gap-2 py-3" style="background: var(--vip-primary); color: #fff;">
                    <i class="bi bi-collection fs-5" style="color: var(--vip-accent);"></i>
                    <span class="fw-semibold">Series</span>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('admin.master.series.index') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                            <i class="bi bi-list-ul text-muted"></i> Series
                        </a>
                        <a href="{{ route('admin.master.series.configurations') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                            <i class="bi bi-sliders text-muted"></i> Configurations
                        </a>
                        <a href="{{ route('admin.master.series.window-types') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                            <i class="bi bi-window text-muted"></i> Window Types
                        </a>
                        <a href="{{ route('admin.master.series.size-limits') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                            <i class="bi bi-rulers text-muted"></i> Size Limits
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Colors --}}
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center gap-2 py-3" style="background: var(--vip-primary); color: #fff;">
                    <i class="bi bi-palette fs-5" style="color: var(--vip-accent);"></i>
                    <span class="fw-semibold">Colors</span>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('admin.master.colors.available') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                            <i class="bi bi-paint-bucket text-muted"></i> Available Colors
                        </a>
                        <a href="{{ route('admin.master.colors.configurations') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                            <i class="bi bi-sliders text-muted"></i> Color Configurations
                        </a>
                        <a href="{{ route('admin.master.colors.exterior') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                            <i class="bi bi-brush text-muted"></i> Exterior Colors
                        </a>
                        <a href="{{ route('admin.master.colors.interior') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                            <i class="bi bi-house text-muted"></i> Interior Colors
                        </a>
                        <a href="{{ route('admin.master.colors.laminate') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                            <i class="bi bi-layers text-muted"></i> Laminate Colors
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Glass --}}
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center gap-2 py-3" style="background: var(--vip-primary); color: #fff;">
                    <i class="bi bi-transparency fs-5" style="color: var(--vip-accent);"></i>
                    <span class="fw-semibold">Glass</span>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('admin.master.glass.options') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                            <i class="bi bi-diamond text-muted"></i> Glass Options
                        </a>
                        <a href="{{ route('admin.master.glass.panes') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                            <i class="bi bi-columns-gap text-muted"></i> Pane Management
                        </a>
                        <a href="{{ route('admin.master.glass.tempered') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                            <i class="bi bi-shield-check text-muted"></i> Tempered / Specialty
                        </a>
                        <a href="{{ route('admin.master.glass.thickness') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                            <i class="bi bi-border-width text-muted"></i> Thickness Manager
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Profiles --}}
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center gap-2 py-3" style="background: var(--vip-primary); color: #fff;">
                    <i class="bi bi-box-seam fs-5" style="color: var(--vip-accent);"></i>
                    <span class="fw-semibold">Profiles & Deductions</span>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('admin.master.profiles.index') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                            <i class="bi bi-diagram-3 text-muted"></i> Profile Manager
                        </a>
                        <a href="{{ route('admin.master.deductions.index') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                            <i class="bi bi-scissors text-muted"></i> Deduction Manager
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pricing --}}
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center gap-2 py-3" style="background: var(--vip-primary); color: #fff;">
                    <i class="bi bi-currency-dollar fs-5" style="color: var(--vip-accent);"></i>
                    <span class="fw-semibold">Pricing</span>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('admin.master.prices.matrix') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                            <i class="bi bi-table text-muted"></i> Price Matrix
                        </a>
                        <a href="{{ route('admin.master.prices.markup') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                            <i class="bi bi-percent text-muted"></i> Markup
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Grids --}}
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center gap-2 py-3" style="background: var(--vip-primary); color: #fff;">
                    <i class="bi bi-grid-3x3 fs-5" style="color: var(--vip-accent);"></i>
                    <span class="fw-semibold">Grids</span>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('admin.master.grids.types') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                            <i class="bi bi-grid text-muted"></i> Grid Types
                        </a>
                        <a href="{{ route('admin.master.grids.profiles') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                            <i class="bi bi-border-all text-muted"></i> Grid Profiles
                        </a>
                        <a href="{{ route('admin.master.grids.patterns') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                            <i class="bi bi-grid-3x3-gap text-muted"></i> Grid Patterns
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Frames --}}
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center gap-2 py-3" style="background: var(--vip-primary); color: #fff;">
                    <i class="bi bi-bounding-box fs-5" style="color: var(--vip-accent);"></i>
                    <span class="fw-semibold">Frames</span>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('admin.master.frames.index') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                            <i class="bi bi-aspect-ratio text-muted"></i> Frame Types
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@push('styles')
<style>
    .list-group-item-action:hover {
        background-color: rgba(201, 168, 76, 0.08);
        color: var(--vip-primary);
    }
    .list-group-item-action:hover i {
        color: var(--vip-accent) !important;
    }
</style>
@endpush
@endsection
