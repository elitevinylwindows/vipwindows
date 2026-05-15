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
        @if($images->isEmpty())
            <div class="text-center py-5">
                <i class="bi bi-image fs-1 text-muted"></i>
                <h5 class="mt-3 text-muted">Gallery Coming Soon</h5>
                <p class="text-muted">We're adding project photos. Check back shortly!</p>
            </div>
        @else
            {{-- Category filter --}}
            @if($categories->count() > 1)
                <div class="text-center mb-4">
                    <div class="btn-group flex-wrap" role="group" id="galleryFilter">
                        <button type="button" class="btn btn-outline-dark active" data-filter="all">All</button>
                        @foreach($categories as $cat)
                            <button type="button" class="btn btn-outline-dark" data-filter="{{ $cat }}">{{ ucwords(str_replace('_', ' ', $cat)) }}</button>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="row g-4" id="galleryGrid">
                @foreach($images as $img)
                    <div class="col-md-4 gallery-item" data-category="{{ $img->category }}">
                        <div class="card service-card overflow-hidden h-100">
                            <a href="{{ asset($img->image_path) }}" target="_blank">
                                <img src="{{ asset($img->image_path) }}" class="card-img-top" style="height:250px; object-fit:cover;" alt="{{ $img->title }}">
                            </a>
                            <div class="card-body">
                                <h6 class="fw-semibold mb-1">{{ $img->title ?: 'Project Photo' }}</h6>
                                @if($img->description)
                                    <p class="text-muted small mb-1">{{ $img->description }}</p>
                                @endif
                                <span class="badge" style="background:var(--vip-accent); color:#fff;">{{ ucwords(str_replace('_', ' ', $img->category)) }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="text-center mt-5">
            <p class="text-muted">Want to see your project featured here? <a href="{{ route('contact') }}" style="color:var(--vip-accent);">Get a free quote today.</a></p>
        </div>
    </div>
</section>

@push('scripts')
<script>
document.querySelectorAll('#galleryFilter button').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('#galleryFilter button').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        const filter = this.dataset.filter;
        document.querySelectorAll('.gallery-item').forEach(item => {
            item.style.display = (filter === 'all' || item.dataset.category === filter) ? '' : 'none';
        });
    });
});
</script>
@endpush
@endsection
