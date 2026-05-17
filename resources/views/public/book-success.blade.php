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
            @if($booking)
                <p class="text-muted">
                    Your booking request <strong>{{ $booking['number'] }}</strong> has been sent to
                    <strong>{{ $booking['installer'] }}</strong>.
                </p>
                <p>
                    <i class="bi bi-calendar me-1"></i> {{ $booking['date'] }}<br>
                    <i class="bi bi-clock me-1"></i> {{ $booking['time'] }}
                </p>
                <p class="text-muted small">You'll receive a confirmation once the installer reviews your request.</p>
            @else
                <p class="text-muted">Your booking has been submitted. You'll hear back soon.</p>
            @endif
            <a href="{{ route('home') }}" class="btn btn-vip mt-3">Back to Home</a>
        </div>
    </div>
</div>
@endsection
