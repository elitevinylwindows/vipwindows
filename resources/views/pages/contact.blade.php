@extends('layouts.public')
@section('title', 'Contact Us')

@section('content')
<section class="hero-section text-center" style="padding: 120px 0 60px;">
    <div class="container position-relative" style="z-index:1;">
        <h1 class="display-5 fw-bold mb-3">Contact Us</h1>
        <p class="lead opacity-90">Get in touch — we'd love to hear from you.</p>
    </div>
</section>

<section class="py-5">
    <div class="container py-4">
        <div class="row g-5 justify-content-center">
            <div class="col-lg-5">
                <div class="card p-4 h-100" style="border:none; box-shadow:0 2px 12px rgba(0,0,0,.08); border-radius:.75rem;">
                    <h4 class="fw-bold mb-4" style="color:var(--vip-primary);">Get In Touch</h4>

                    <div class="d-flex mb-4">
                        <div class="service-icon me-3" style="margin:0;width:48px;height:48px;font-size:1.1rem;flex-shrink:0;">
                            <i class="bi bi-telephone"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Phone</h6>
                            <p class="text-muted mb-0">(562) 368-0313</p>
                        </div>
                    </div>

                    <div class="d-flex mb-4">
                        <div class="service-icon me-3" style="margin:0;width:48px;height:48px;font-size:1.1rem;flex-shrink:0;">
                            <i class="bi bi-envelope"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Email</h6>
                            <p class="text-muted mb-0">info@vipwindows.net</p>
                        </div>
                    </div>

                    <div class="d-flex mb-4">
                        <div class="service-icon me-3" style="margin:0;width:48px;height:48px;font-size:1.1rem;flex-shrink:0;">
                            <i class="bi bi-clock"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Business Hours</h6>
                            <p class="text-muted mb-0">Monday - Friday: 8:00 AM - 5:00 PM<br>Saturday: By appointment<br>Sunday: Closed</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card p-4" style="border:none; box-shadow:0 2px 12px rgba(0,0,0,.08); border-radius:.75rem;">
                    <h4 class="fw-bold mb-4" style="color:var(--vip-primary);">Send Us a Message</h4>
                    <form>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Name</label>
                                <input type="text" class="form-control" placeholder="Your name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="text" class="form-control" placeholder="Your phone number">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" placeholder="Your email address">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Message</label>
                                <textarea class="form-control" rows="5" placeholder="How can we help you?"></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-vip btn-lg w-100">
                                    <i class="bi bi-send me-2"></i> Send Message
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
