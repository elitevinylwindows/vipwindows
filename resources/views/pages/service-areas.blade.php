@extends('layouts.public')
@section('title', 'Service Areas')

@section('content')
<section class="hero-section text-center" style="padding: 120px 0 60px;">
    <div class="container position-relative" style="z-index:1;">
        <h1 class="display-5 fw-bold mb-3">Service Areas</h1>
        <p class="lead opacity-90">We proudly serve these locations and surrounding communities.</p>
    </div>
</section>

<section class="py-5">
    <div class="container py-4">
        <div class="text-center mb-5">
            <p class="text-muted" style="max-width:600px; margin:0 auto;">
                VIP Windows provides professional window installation services across the following areas.
                Not sure if we cover your location? Contact us and we'll let you know.
            </p>
        </div>

        @if($areas->isEmpty())
            <div class="text-center py-4">
                <i class="bi bi-geo-alt fs-1 text-muted"></i>
                <h5 class="mt-3 text-muted">Service areas coming soon.</h5>
            </div>
        @else
            <div class="row g-4 justify-content-center">
                @foreach($areas as $area)
                    <div class="col-md-4">
                        <div class="card service-card p-4 text-center h-100">
                            <i class="bi bi-geo-alt-fill fs-1 mb-2" style="color:var(--vip-primary);"></i>
                            <h5 class="fw-bold">{{ $area->name }}</h5>
                            <p class="text-muted small mb-1">{{ $area->description ?: 'And surrounding neighborhoods' }}</p>
                            <span class="badge bg-dark mt-auto" style="width:fit-content; margin:0 auto;">{{ $area->state }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="text-center mt-5">
            <div class="card p-4 d-inline-block" style="border:2px dashed var(--vip-accent); background:transparent; box-shadow:none;">
                <h5 class="fw-bold mb-2">Don't see your area?</h5>
                <p class="text-muted small mb-3">We may still be able to help. Reach out and we'll check availability for your location.</p>
                <a href="{{ route('contact') }}" class="btn btn-vip"><i class="bi bi-telephone me-1"></i> Contact Us</a>
            </div>
        </div>
    </div>
</section>
@endsection
