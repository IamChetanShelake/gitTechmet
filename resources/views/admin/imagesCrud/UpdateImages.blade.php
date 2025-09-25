@extends('admin.layout.masteradmin')

@section('content')
<body>
    <div class="container mt-5 d-flex justify-content-center">
        <div class="card shadow-lg p-4 w-70">
            <h2 class="text-center mb-4 text-primary">Update Image</h2>
            <form action="{{route('Update.Image', $image->id)}}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label for="image" class="form-label fw-bold">Upload Image</label>
                    <div class="col-1">
                        <img height="80" width="80" style=" margin-left:10px; border-radius:5px;"
                            src="{{ asset('Gallery/' . $image->image) }}" alt="">
                    </div>
                    <input type="file" name="image" class="form-control border rounded-3 shadow-sm ps-3" accept="image/*">
                    @error('image')
                    <span class="invalid-feedback d-block">
                        {{$message}}
                    </span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="order" class="form-label fw-bold">Display Order</label>
                    <input type="number" name="order" class="form-control border rounded-3 shadow-sm ps-3" value="{{ $image->order }}" min="0" required>
                    @error('order')
                    <span class="invalid-feedback d-block">
                        {{$message}}
                    </span>
                    @enderror
                    <small class="form-text text-muted">Lower numbers appear first. Pinned images always appear before unpinned ones.</small>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input type="checkbox" name="is_pinned" class="form-check-input" id="is_pinned" value="1" {{ $image->is_pinned ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold" for="is_pinned">
                            Pin this image to the top
                        </label>
                    </div>
                    <small class="form-text text-muted">Pinned images will appear at the top of the gallery.</small>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <button type="submit" class="btn btn-primary px-4">Update Image</button>
                    <a href="/image" class="btn btn-outline-secondary px-4">Back</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
@endsection
