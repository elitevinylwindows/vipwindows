@extends('layouts.public')
@section('title', 'About Us')

@section('content')
<section class="hero-section text-center" style="padding: 120px 0 60px;">
    <div class="container position-relative" style="z-index:1;">
        <h1 class="display-5 fw-bold mb-3">About VIP Windows</h1>
        <p class="lead opacity-90">Your trusted partner for professional window installation.</p>
    </div>
</section>

<section class="py-5">
    <div class="container py-4" style="max-width:800px;">
        {{-- Placeholder — replace with actual company details --}}
        <div class="card p-5 mb-4" style="border:none; box-shadow:0 2px 12px rgba(0,0,0,.08); border-radius:.75rem;">
            <h3 class="fw-bold mb-3" style="color:var(--vip-primary);">Our Story</h3>
            <p class="text-muted">
                VIP Windows is a professional window installation company dedicated to delivering
                top-quality craftsmanship on every project. With years of industry experience, our team
                of skilled technicians handles everything from single window replacements to complete
                home and commercial window upgrades.
            </p>
            <p class="text-muted">
                We pride ourselves on punctuality, clean workmanship, and customer satisfaction. Every
                installation is performed by trained professionals using premium vinyl windows backed by
                manufacturer warranties.
            </p>

            <hr class="my-4">

            <h4 class="fw-bold mb-3" style="color:var(--vip-primary);">Our Values</h4>
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="d-flex">
                        <i class="bi bi-check-circle-fill me-2 mt-1" style="color:var(--vip-accent);"></i>
                        <div>
                            <strong>Quality First</strong>
                            <p class="text-muted small mb-0">We never cut corners. Every window is installed to the highest standards.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex">
                        <i class="bi bi-check-circle-fill me-2 mt-1" style="color:var(--vip-accent);"></i>
                        <div>
                            <strong>Customer Focused</strong>
                            <p class="text-muted small mb-0">Your satisfaction is our top priority, from first contact to final walkthrough.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex">
                        <i class="bi bi-check-circle-fill me-2 mt-1" style="color:var(--vip-accent);"></i>
                        <div>
                            <strong>Transparent Pricing</strong>
                            <p class="text-muted small mb-0">No hidden fees or surprises. You know the cost before we start.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex">
                        <i class="bi bi-check-circle-fill me-2 mt-1" style="color:var(--vip-accent);"></i>
                        <div>
                            <strong>Reliability</strong>
                            <p class="text-muted small mb-0">We show up on time, every time. Your schedule matters to us.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
