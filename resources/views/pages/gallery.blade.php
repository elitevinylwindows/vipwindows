@extends('layouts.public')
@section('title', 'Gallery')

@section('content')
<section class="hero-section text-center" style="padding: 120px 0 60px;">
    <div class="container position-relative" style="z-index:1;">
        <h1 class="display-5 fw-bold mb-3">Our Work</h1>
        <p class="lead opacity-90">Browse our portfolio of completed installations.</p>
    </div>
</section>

<section class="py-5">
    <div class="container py-4">
        {{-- Placeholder gallery grid --}}
        <div class="row g-4">
            @for($i = 1; $i <= 9; $i++)
                <div class="col-md-4">
                    <div class="card service-card overflow-hidden">
                        <div class="bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center" style="height:250px;">
                            <div class="text-center text-muted">
                                <i class="bi bi-image fs-1"></i>
                                <div class="small mt-2">Project Photo {{ $i }}</div>
                            </div>
                        </div>
                        <div class="card-body">
                            <h6 class="fw-semibold mb-1">Installation Project {{ $i }}</h6>
                            <p class="text-muted small mb-0">Window installation — Residential</p>
                        </div>
                    </div>
                </div>
            @endfor
        </div>

        <div class="text-center mt-5">
            <p class="text-muted">More photos coming soon. Follow us on social media for the latest project updates.</p>
        </div>
    </div>
</section>
@endsection
