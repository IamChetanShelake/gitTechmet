@extends('admin.layout.masteradmin')

@section('content')
<body>
    <div class="container mt-5 d-flex justify-content-center">
        <div class="card shadow-lg p-4 w-70">
            <h2 class="text-center mb-4 text-primary">Video Details</h2>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Video Title:</label>
                        <p class="form-control-plaintext">{{ $video->title ?? 'No Title' }}</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">YouTube URL:</label>
                        <p class="form-control-plaintext">
                            <a href="{{ $video->youtube_url }}" target="_blank" class="text-primary">
                                {{ $video->youtube_url }}
                                <i class="fas fa-external-link-alt ms-1"></i>
                            </a>
                        </p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Created At:</label>
                        <p class="form-control-plaintext">{{ $video->created_at->format('d M Y, H:i') }}</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Updated At:</label>
                        <p class="form-control-plaintext">{{ $video->updated_at->format('d M Y, H:i') }}</p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Video Thumbnail:</label>
                        <div class="mt-2">
                            @if($video->thumbnail)
                                <img src="{{ asset('video_thumbnails/' . $video->thumbnail) }}" class="img-fluid rounded shadow" alt="Video Thumbnail" style="max-width: 100%; height: auto;">
                            @else
                                <p class="text-muted">No thumbnail available</p>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Video Preview:</label>
                        <div class="mt-2">
                            @php
                                // Extract YouTube video ID
                                $videoId = '';
                                if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $video->youtube_url, $matches)) {
                                    $videoId = $matches[1];
                                }
                            @endphp
                            @if($videoId)
                                <div class="ratio ratio-16x9">
                                    <iframe src="https://www.youtube.com/embed/{{ $videoId }}" title="YouTube video player" allowfullscreen></iframe>
                                </div>
                            @else
                                <p class="text-muted">Invalid YouTube URL</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="{{route('videos.edit',$video->id)}}" class="btn btn-success px-4">Edit Video</a>
                <a href="/videos" class="btn btn-outline-secondary px-4">Back to Videos</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
@endsection
