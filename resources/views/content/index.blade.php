@extends('layouts.app')
@section('title', 'Content')

@push('styles')
<style>
    .content-layout { display: flex; gap: 0; min-height: calc(100vh - 130px); }
    .content-rail {
        width: 220px; flex-shrink: 0;
        background: #fff; border-right: 1px solid rgba(0,0,0,.08);
        padding: 1rem 0;
    }
    .content-rail a {
        display: flex; align-items: center; gap: .6rem;
        padding: .65rem 1.25rem; color: rgba(0,0,0,.6);
        text-decoration: none; font-size: .88rem;
        border-left: 3px solid transparent; transition: all .15s;
    }
    .content-rail a:hover { background: rgba(0,0,0,.03); color: #111; }
    .content-rail a.active {
        background: rgba(201,168,76,.08); color: var(--vip-accent);
        border-left-color: var(--vip-accent); font-weight: 600;
    }
    .content-rail a i { font-size: 1rem; width: 20px; text-align: center; }
    .content-main { flex: 1; padding: 1.5rem 2rem; overflow-y: auto; }
    .content-section { display: none; }
    .content-section.active { display: block; }
    @media (max-width: 767.98px) {
        .content-layout { flex-direction: column; }
        .content-rail { width: 100%; border-right: none; border-bottom: 1px solid rgba(0,0,0,.08); display: flex; overflow-x: auto; padding: .5rem; gap: .25rem; }
        .content-rail a { padding: .5rem .75rem; border-left: none; border-bottom: 2px solid transparent; white-space: nowrap; font-size: .8rem; }
        .content-rail a.active { border-left: none; border-bottom-color: var(--vip-accent); }
        .content-main { padding: 1rem; }
    }
</style>
@endpush

@section('content')
<div class="content-layout">
    {{-- Left Rail --}}
    <div class="content-rail">
        <a href="#" class="content-tab active" data-section="gallery">
            <i class="bi bi-images"></i> Gallery
        </a>
        <a href="#" class="content-tab" data-section="service-areas">
            <i class="bi bi-geo-alt"></i> Service Areas
        </a>
    </div>

    {{-- Main Content --}}
    <div class="content-main">

        {{-- ═══════════ GALLERY ═══════════ --}}
        <div class="content-section active" id="section-gallery">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0"><i class="bi bi-images me-2"></i>Gallery</h5>
                <button class="btn btn-vip" data-bs-toggle="modal" data-bs-target="#uploadModal">
                    <i class="bi bi-cloud-upload me-1"></i> Upload Images
                </button>
            </div>

            @if($images->isEmpty())
                <div class="card p-5 text-center">
                    <i class="bi bi-image fs-1 text-muted d-block mb-3"></i>
                    <h5>No gallery images yet</h5>
                    <p class="text-muted">Upload your first project photos to showcase on the website.</p>
                </div>
            @else
                <div class="row g-3">
                    @foreach($images as $img)
                        <div class="col-sm-6 col-md-4 col-lg-3">
                            <div class="card h-100">
                                <img src="{{ asset($img->image_path) }}" class="card-img-top" style="height:200px; object-fit:cover;" alt="{{ $img->title }}">
                                <div class="card-body p-3">
                                    <h6 class="fw-semibold mb-1 small">{{ $img->title ?: 'Untitled' }}</h6>
                                    <span class="badge bg-secondary mb-2">{{ ucwords(str_replace('_', ' ', $img->category)) }}</span>
                                    @if(!$img->is_active)
                                        <span class="badge bg-warning text-dark">Hidden</span>
                                    @endif
                                </div>
                                <div class="card-footer bg-white border-top d-flex gap-1 p-2">
                                    <button class="btn btn-sm btn-outline-primary flex-fill" data-bs-toggle="modal" data-bs-target="#editModal{{ $img->id }}">
                                        <i class="bi bi-pencil"></i> Edit
                                    </button>
                                    <form method="POST" action="{{ route('admin.gallery.destroy', $img->id) }}" onsubmit="return confirm('Delete this image?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </div>

                            {{-- Edit modal --}}
                            <div class="modal fade" id="editModal{{ $img->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('admin.gallery.update', $img->id) }}">
                                            @csrf @method('PUT')
                                            <div class="modal-header"><h5 class="modal-title">Edit Image</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Title</label>
                                                    <input type="text" name="title" class="form-control" value="{{ $img->title }}">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Description</label>
                                                    <input type="text" name="description" class="form-control" value="{{ $img->description }}">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Category</label>
                                                    <select name="category" class="form-select">
                                                        @foreach(['installation','replacement','sliding_door','commercial','other'] as $cat)
                                                            <option value="{{ $cat }}" {{ $img->category === $cat ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $cat)) }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Sort Order</label>
                                                    <input type="number" name="sort_order" class="form-control" value="{{ $img->sort_order }}">
                                                </div>
                                                <div class="form-check">
                                                    <input type="checkbox" name="is_active" value="1" class="form-check-input" {{ $img->is_active ? 'checked' : '' }}>
                                                    <label class="form-check-label">Visible on website</label>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-vip">Save Changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ═══════════ SERVICE AREAS ═══════════ --}}
        <div class="content-section" id="section-service-areas">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0"><i class="bi bi-geo-alt me-2"></i>Service Areas</h5>
                <button class="btn btn-vip" data-bs-toggle="modal" data-bs-target="#addAreaModal">
                    <i class="bi bi-plus-circle me-1"></i> Add Area
                </button>
            </div>

            <div class="card">
                <div class="card-body p-0">
                    @if($areas->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-geo-alt fs-1 d-block mb-2"></i>
                            No service areas added yet. Add your first one to display on the website.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>State</th>
                                        <th>Order</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($areas as $area)
                                        <tr>
                                            <td class="fw-semibold">{{ $area->name }}</td>
                                            <td class="text-muted small">{{ $area->description ?: '—' }}</td>
                                            <td>{{ $area->state }}</td>
                                            <td>{{ $area->sort_order }}</td>
                                            <td>
                                                @if($area->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-warning text-dark">Hidden</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editArea{{ $area->id }}">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <form method="POST" action="{{ route('admin.service-areas.destroy', $area->id) }}" class="d-inline" onsubmit="return confirm('Remove this area?')">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>

                                        {{-- Edit modal --}}
                                        <div class="modal fade" id="editArea{{ $area->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST" action="{{ route('admin.service-areas.update', $area->id) }}">
                                                        @csrf @method('PUT')
                                                        <div class="modal-header"><h5 class="modal-title">Edit Service Area</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label">Name</label>
                                                                <input type="text" name="name" class="form-control" value="{{ $area->name }}" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Description</label>
                                                                <input type="text" name="description" class="form-control" value="{{ $area->description }}" placeholder="e.g. And surrounding neighborhoods">
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-6 mb-3">
                                                                    <label class="form-label">State</label>
                                                                    <input type="text" name="state" class="form-control" value="{{ $area->state }}" required>
                                                                </div>
                                                                <div class="col-md-6 mb-3">
                                                                    <label class="form-label">Sort Order</label>
                                                                    <input type="number" name="sort_order" class="form-control" value="{{ $area->sort_order }}">
                                                                </div>
                                                            </div>
                                                            <div class="form-check">
                                                                <input type="checkbox" name="is_active" value="1" class="form-check-input" {{ $area->is_active ? 'checked' : '' }}>
                                                                <label class="form-check-label">Visible on website</label>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="submit" class="btn btn-vip">Save Changes</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Upload modal --}}
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.gallery.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header"><h5 class="modal-title"><i class="bi bi-cloud-upload me-1"></i> Upload Images</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Images</label>
                        <input type="file" name="images[]" class="form-control" multiple accept="image/*" required>
                        <div class="form-text">Max 5MB each. JPEG, PNG, or WebP.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Title <span class="text-muted">(optional)</span></label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Kitchen Window Replacement">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description <span class="text-muted">(optional)</span></label>
                        <input type="text" name="description" class="form-control" placeholder="Brief description">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select">
                            <option value="installation">Installation</option>
                            <option value="replacement">Replacement</option>
                            <option value="sliding_door">Sliding Door</option>
                            <option value="commercial">Commercial</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-vip"><i class="bi bi-cloud-upload me-1"></i> Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Add Service Area modal --}}
<div class="modal fade" id="addAreaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.service-areas.store') }}">
                @csrf
                <div class="modal-header"><h5 class="modal-title"><i class="bi bi-geo-alt me-1"></i> Add Service Area</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Long Beach" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description <span class="text-muted">(optional)</span></label>
                        <input type="text" name="description" class="form-control" placeholder="e.g. Long Beach and surrounding areas">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">State</label>
                            <input type="text" name="state" class="form-control" value="CA" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" value="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-vip"><i class="bi bi-plus-circle me-1"></i> Add Area</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.content-tab');
    const sections = document.querySelectorAll('.content-section');

    // Check URL hash for initial tab
    const hash = window.location.hash.replace('#', '');
    if (hash) {
        const targetTab = document.querySelector(`.content-tab[data-section="${hash}"]`);
        if (targetTab) {
            tabs.forEach(t => t.classList.remove('active'));
            sections.forEach(s => s.classList.remove('active'));
            targetTab.classList.add('active');
            document.getElementById('section-' + hash)?.classList.add('active');
        }
    }

    tabs.forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            const section = this.dataset.section;

            tabs.forEach(t => t.classList.remove('active'));
            sections.forEach(s => s.classList.remove('active'));

            this.classList.add('active');
            document.getElementById('section-' + section)?.classList.add('active');

            history.replaceState(null, null, '#' + section);
        });
    });
});
</script>
@endpush
