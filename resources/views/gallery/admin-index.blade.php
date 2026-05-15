@extends('layouts.app')
@section('title', 'Gallery Management')

@section('content')
<div class="container-fluid py-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><i class="bi bi-images me-2"></i>Gallery</h4>
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
@endsection
