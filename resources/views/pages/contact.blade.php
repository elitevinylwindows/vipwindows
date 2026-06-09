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
                            <p class="text-muted mb-0">info@vipwindowsinc.com</p>
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

{{-- Virtual Consultation Section --}}
<section class="py-5" style="background:#f8f8f8;">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center mb-4">
                <h3 class="fw-bold" style="color:var(--vip-primary);">
                    <i class="bi bi-camera-video me-2" style="color:var(--vip-accent);"></i>
                    Request a Virtual Consultation
                </h3>
                <p class="text-muted">Can't visit in person? Schedule a virtual consultation via Zoom, Teams, or phone. We'll measure, assess, and verify everything remotely.</p>
            </div>
            <div class="col-lg-6">
                <div class="card p-4" style="border:none; box-shadow:0 2px 12px rgba(0,0,0,.08); border-radius:.75rem;">
                    @if(session('consultation_success'))
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle me-1"></i> {{ session('consultation_success') }}
                        </div>
                    @endif
                    <form method="POST" action="{{ route('consultation.request') }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required placeholder="Your name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone <span class="text-danger">*</span></label>
                                <input type="text" name="phone" class="form-control" required placeholder="Your phone number">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" required placeholder="Your email address">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <input type="text" name="address" class="form-control" placeholder="Installation address (optional)" data-address-autocomplete>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Preferred Platform</label>
                                <select name="platform" class="form-select">
                                    <option value="zoom">Zoom</option>
                                    <option value="teams">Microsoft Teams</option>
                                    <option value="phone">Phone Call</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Preferred Time</label>
                                <select name="preferred_time" class="form-select">
                                    <option value="morning">Morning (8-12)</option>
                                    <option value="afternoon">Afternoon (12-5)</option>
                                    <option value="flexible">Flexible</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="3" placeholder="Tell us about your project — window type, quantity, any concerns..."></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-vip btn-lg w-100">
                                    <i class="bi bi-camera-video me-2"></i> Request Consultation
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
