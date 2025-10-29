@extends('admin.layout.masteradmin')

@section('content')
<body>
    <div class="container mt-5 d-flex justify-content-center">
        <div class="card shadow-lg p-4 w-70">
            <h2 class="text-center mb-4 text-primary">Add Video</h2>
            <form action="{{route('videos.store')}}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label for="title" class="form-label fw-bold">Video Title (Optional)</label>
                    <input type="text" class="form-control border rounded-3 shadow-sm ps-3 @error('title') is-invalid @enderror"
                           id="title" name="title" placeholder="Enter video title">
                    @error('title')
                    <span class="invalid-feedback d-block">
                        {{$message}}
                    </span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="youtube_url" class="form-label fw-bold">YouTube URL</label>
                    <input type="url" class="form-control border rounded-3 shadow-sm ps-3 @error('youtube_url') is-invalid @enderror"
                           id="youtube_url" name="youtube_url" placeholder="https://youtu.be/VIDEO_ID or https://www.youtube.com/watch?v=VIDEO_ID" required>
                    @error('youtube_url')
                    <span class="invalid-feedback d-block">
                        {{$message}}
                    </span>
                    @enderror
                    <small class="form-text text-muted">Enter the full YouTube URL (youtu.be or youtube.com)</small>
                </div>

                <div class="mb-3">
                    <label for="thumbnail" class="form-label fw-bold">Video Thumbnail</label>
                    <input type="file" name="thumbnail" class="form-control border rounded-3 shadow-sm ps-3" accept="image/*" required>
                    @error('thumbnail')
                    <span class="invalid-feedback d-block">
                        {{$message}}
                    </span>
                    @enderror
                    <small class="form-text text-muted">Upload a thumbnail image for the video (JPEG, PNG, GIF)</small>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <button type="submit" class="btn btn-primary px-4">Add Video</button>
                    <a href="/videos" class="btn btn-outline-secondary px-4">Back</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
@endsection
