@extends('layouts.public')
@section('title', 'Professional Window Installation')

@section('content')
{{-- Hero --}}
<section class="hero-section text-center" style="padding: 140px 0 80px;">
    <div class="container position-relative" style="z-index:1;">
        <h1 class="display-4 fw-bold mb-3">Expert Window Installation<br>You Can Trust</h1>
        <p class="lead opacity-90 mb-4" style="max-width:650px; margin:0 auto;">
            Professional vinyl window and door installation, replacement, and upgrade services.
            Licensed, insured, and backed by 25 years of experience.
        </p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="{{ route('public.book.website') }}" class="btn btn-vip btn-lg px-4">
                <i class="bi bi-calendar-check me-2"></i> Book Installation
            </a>
            <a href="{{ route('services') }}" class="btn btn-outline-light btn-lg px-4">
                Our Services
            </a>
        </div>
    </div>
</section>

{{-- Stats bar --}}
<section class="stats-bar py-4">
    <div class="container">
        <div class="row text-center g-4">
            <div class="col-6 col-md-3">
                <div class="stat-number">500+</div>
                <div class="small opacity-75">Installations Completed</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-number">10+</div>
                <div class="small opacity-75">Years Experience</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-number">100%</div>
                <div class="small opacity-75">Licensed & Insured</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-number">5.0</div>
                <div class="small opacity-75">Customer Rating</div>
            </div>
        </div>
    </div>
</section>

{{-- Services overview --}}
<section class="py-5">
    <div class="container py-4">
        <div class="text-center mb-5">
            <h2 class="section-heading">Our Services</h2>
            <p class="section-subtext">From single window replacements to full-home upgrades, we handle it all with precision and care.</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card service-card p-4 text-center h-100">
                    <div class="service-icon"><i class="bi bi-window"></i></div>
                    <h5 class="fw-bold">Window Installation</h5>
                    <p class="text-muted small">New construction and retrofit window installation for residential and commercial properties. Precision-fit every time.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card service-card p-4 text-center h-100">
                    <div class="service-icon"><i class="bi bi-arrow-repeat"></i></div>
                    <h5 class="fw-bold">Window Replacement</h5>
                    <p class="text-muted small">Upgrade old, drafty windows with energy-efficient vinyl windows. Improve comfort and reduce energy costs.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card service-card p-4 text-center h-100">
                    <div class="service-icon"><i class="bi bi-door-open"></i></div>
                    <h5 class="fw-bold">Sliding Doors</h5>
                    <p class="text-muted small">Beautiful sliding glass door installation that brings the outdoors in. Available in multiple styles and finishes.</p>
                </div>
            </div>
        </div>
        <div class="text-center mt-4">
            <a href="{{ route('services') }}" class="btn btn-outline-vip">View All Services <i class="bi bi-arrow-right ms-1"></i></a>
        </div>
    </div>
</section>

{{-- Why choose us --}}
<section class="py-5" style="background: var(--vip-light);">
    <div class="container py-4">
        <div class="text-center mb-5">
            <h2 class="section-heading">Why Choose VIP Windows?</h2>
        </div>
        <div class="row g-4">
            <div class="col-md-6 col-lg-3 text-center">
                <div class="mb-3"><i class="bi bi-shield-check fs-1" style="color:var(--vip-primary);"></i></div>
                <h6 class="fw-bold">Licensed & Insured</h6>
                <p class="text-muted small">Fully licensed and insured for your peace of mind. We stand behind our work.</p>
            </div>
            <div class="col-md-6 col-lg-3 text-center">
                <div class="mb-3"><i class="bi bi-clock-history fs-1" style="color:var(--vip-primary);"></i></div>
                <h6 class="fw-bold">On-Time Service</h6>
                <p class="text-muted small">We respect your schedule. Arrive on time, work efficiently, clean up when done.</p>
            </div>
            <div class="col-md-6 col-lg-3 text-center">
                <div class="mb-3"><i class="bi bi-award fs-1" style="color:var(--vip-primary);"></i></div>
                <h6 class="fw-bold">Quality Materials</h6>
                <p class="text-muted small">We install top-quality vinyl windows built to last and backed by manufacturer warranties.</p>
            </div>
            <div class="col-md-6 col-lg-3 text-center">
                <div class="mb-3"><i class="bi bi-people fs-1" style="color:var(--vip-primary);"></i></div>
                <h6 class="fw-bold">Expert Team</h6>
                <p class="text-muted small">Our experienced technicians handle every project with care, precision, and professionalism.</p>
            </div>
        </div>
    </div>
</section>

{{-- Testimonials --}}
<section class="py-5">
    <div class="container py-4">
        <div class="text-center mb-5">
            <h2 class="section-heading">What Our Customers Say</h2>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card testimonial-card p-4 h-100">
                    <div class="mb-2"><i class="bi bi-star-fill text-warning"></i><i class="bi bi-star-fill text-warning"></i><i class="bi bi-star-fill text-warning"></i><i class="bi bi-star-fill text-warning"></i><i class="bi bi-star-fill text-warning"></i></div>
                    <p class="text-muted small">"The team was professional, on time, and did a fantastic job. Our new windows look amazing and the house is noticeably quieter."</p>
                    <div class="fw-semibold small mt-auto">— Sarah M.</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card testimonial-card p-4 h-100">
                    <div class="mb-2"><i class="bi bi-star-fill text-warning"></i><i class="bi bi-star-fill text-warning"></i><i class="bi bi-star-fill text-warning"></i><i class="bi bi-star-fill text-warning"></i><i class="bi bi-star-fill text-warning"></i></div>
                    <p class="text-muted small">"Great experience from start to finish. The booking process was easy and the installers were very careful with our home."</p>
                    <div class="fw-semibold small mt-auto">— James R.</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card testimonial-card p-4 h-100">
                    <div class="mb-2"><i class="bi bi-star-fill text-warning"></i><i class="bi bi-star-fill text-warning"></i><i class="bi bi-star-fill text-warning"></i><i class="bi bi-star-fill text-warning"></i><i class="bi bi-star-fill text-warning"></i></div>
                    <p class="text-muted small">"We replaced all 12 windows in our home. The difference in energy efficiency is incredible. Highly recommend VIP Windows!"</p>
                    <div class="fw-semibold small mt-auto">— Linda & Tom K.</div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="cta-section py-5 text-center">
    <div class="container py-3">
        <h2 class="fw-bold mb-3">Ready to Upgrade Your Windows?</h2>
        <p class="mb-4 opacity-90">Book your installation online or get in touch with our team.</p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="{{ route('public.book.website') }}" class="btn btn-dark btn-lg px-4">
                <i class="bi bi-calendar-check me-2"></i> Book Now
            </a>
            <a href="{{ route('contact') }}" class="btn btn-outline-dark btn-lg px-4">
                Contact Us
            </a>
        </div>
    </div>
</section>
@endsection
