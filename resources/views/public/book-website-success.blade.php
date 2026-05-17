@extends('layouts.public')
@section('title', 'Booking Confirmed')

@push('styles')
<style>.success-wrapper { padding-top: 120px; }</style>
@endpush

@section('content')
<div class="success-wrapper">
    <div class="container py-5" style="max-width:600px;">
        <div class="card p-5 text-center">
            <div class="mb-3"><i class="bi bi-check-circle-fill text-success" style="font-size:4rem;"></i></div>
            <h4 class="fw-bold mb-3">Booking Request Submitted!</h4>
            @if(session('booking'))
                <p class="text-muted">
                    Your installation booking has been sent to <strong>VIP Windows</strong>.
                </p>
                <p>
                    <i class="bi bi-calendar me-1"></i> {{ session('booking.date') }}<br>
                    <i class="bi bi-clock me-1"></i> {{ session('booking.time') }}
                </p>
                <p class="text-muted small">A member of our team will contact you to confirm the details.</p>
            @else
                <p class="text-muted">Your booking has been submitted. We'll be in touch soon.</p>
            @endif
            <a href="{{ route('home') }}" class="btn btn-vip mt-3">Back to Home</a>
        </div>
    </div>
</div>
@endsection
