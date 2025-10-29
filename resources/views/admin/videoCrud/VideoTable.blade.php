@extends('admin.layout.masteradmin')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card my-4">
                    <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                        <div class="shadow-dark border-radius-lg pt-4 pb-3"
                            style="background-color: #70533A;">
                            <h6 class="text-white text-capitalize ps-3">Video Management</h6>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end p-3">
                        <a class="btn btn-outline-primary btn-lg px-4 py-2" href="{{route('videos.add')}}">Add Video</a>
                    </div>

                    <div class="card-body px-3 pb-3">
                        <div class="table-responsive">
                            <table class="table table-hover text-center">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder">Title</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder">Thumbnail</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder">YouTube URL</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder">View</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($videos as $video)
                                        <tr>
                                            <td>
                                                <h6 class="mb-0 text-sm">{{ $video->title ?? 'No Title' }}</h6>
                                            </td>
                                            <td>
                                                @if(isset($video) && $video->thumbnail)
                                                    <img src="{{ asset('video_thumbnails/' . $video->thumbnail) }}" class="img-fluid rounded" style="width: 50px; height: 50px;" alt="Video Thumbnail">
                                                @else
                                                    <p>No Thumbnail</p>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ $video->youtube_url }}" target="_blank" class="text-primary">
                                                    <i class="fab fa-youtube"></i> View on YouTube
                                                </a>
                                            </td>
                                            <td>
                                                <a class="btn btn-info" href="{{route('videos.view',$video->id)}}">View</a>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-2">
                                                    <a href="{{route('videos.edit',$video->id)}}" class="btn btn-success">Edit</a>
                                                    <form action="{{route('videos.delete',$video->id)}}" method="post">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this video?')">Delete</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
