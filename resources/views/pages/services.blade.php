@extends('layouts.public')
@section('title', 'Our Services')

@section('content')
<section class="hero-section text-center" style="padding: 120px 0 60px;">
    <div class="container position-relative" style="z-index:1;">
        <h1 class="display-5 fw-bold mb-3">Our Services</h1>
        <p class="lead opacity-90">Comprehensive window installation and replacement solutions.</p>
    </div>
</section>

<section class="py-5">
    <div class="container py-4">
        <div class="row g-5">
            <div class="col-lg-6">
                <div class="card service-card p-4 h-100">
                    <div class="d-flex align-items-center mb-3">
                        <div class="service-icon me-3" style="margin:0;width:56px;height:56px;font-size:1.3rem;flex-shrink:0;"><i class="bi bi-window"></i></div>
                        <h4 class="fw-bold mb-0">New Window Installation</h4>
                    </div>
                    <p class="text-muted">Professional installation for new construction projects. We work with builders, contractors, and homeowners to deliver precision-fit windows that meet building codes and exceed expectations. From single-family homes to multi-unit developments.</p>
                    <ul class="text-muted small">
                        <li>Single-hung and double-hung windows</li>
                        <li>Casement and awning windows</li>
                        <li>Picture and bay windows</li>
                        <li>Custom shapes and sizes</li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card service-card p-4 h-100">
                    <div class="d-flex align-items-center mb-3">
                        <div class="service-icon me-3" style="margin:0;width:56px;height:56px;font-size:1.3rem;flex-shrink:0;"><i class="bi bi-arrow-repeat"></i></div>
                        <h4 class="fw-bold mb-0">Window Replacement</h4>
                    </div>
                    <p class="text-muted">Replace outdated, damaged, or inefficient windows with modern energy-efficient vinyl alternatives. Our retrofit process minimizes disruption to your home while maximizing energy savings and comfort.</p>
                    <ul class="text-muted small">
                        <li>Energy-efficient low-E glass</li>
                        <li>Argon gas filled for insulation</li>
                        <li>Multiple color and finish options</li>
                        <li>Old window removal and disposal included</li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card service-card p-4 h-100">
                    <div class="d-flex align-items-center mb-3">
                        <div class="service-icon me-3" style="margin:0;width:56px;height:56px;font-size:1.3rem;flex-shrink:0;"><i class="bi bi-door-open"></i></div>
                        <h4 class="fw-bold mb-0">Sliding Glass Doors</h4>
                    </div>
                    <p class="text-muted">Beautiful and functional sliding door installation. Enhance natural light and create seamless indoor-outdoor living spaces with our range of premium sliding door options.</p>
                    <ul class="text-muted small">
                        <li>2-panel and 3-panel options</li>
                        <li>Multi-slide and pocket doors</li>
                        <li>Impact-resistant glass available</li>
                        <li>Screen door installation included</li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card service-card p-4 h-100">
                    <div class="d-flex align-items-center mb-3">
                        <div class="service-icon me-3" style="margin:0;width:56px;height:56px;font-size:1.3rem;flex-shrink:0;"><i class="bi bi-buildings"></i></div>
                        <h4 class="fw-bold mb-0">Commercial Projects</h4>
                    </div>
                    <p class="text-muted">Window installation for commercial buildings, offices, and storefronts. We handle projects of all sizes with the same attention to detail and commitment to quality.</p>
                    <ul class="text-muted small">
                        <li>Storefront and office windows</li>
                        <li>Multi-unit residential complexes</li>
                        <li>HOA and property management projects</li>
                        <li>Bulk pricing available</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="cta-section py-5 text-center">
    <div class="container">
        <h3 class="fw-bold mb-3">Need a Window Installation?</h3>
        <p class="mb-4 opacity-90">Create your account and book an appointment today.</p>
        <a href="{{ route('register') }}" class="btn btn-dark btn-lg px-4"><i class="bi bi-calendar-check me-2"></i> Book Now</a>
    </div>
</section>
@endsection
