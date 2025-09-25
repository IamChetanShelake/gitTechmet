@extends('admin.layout.masteradmin')

@section('content')

<body>
    <div class="container mt-5 d-flex justify-content-center">
        <div class="card shadow-lg p-5 w-75">
            <h2 class="text-center mb-4 text-primary">Add New Event</h2>
            <form action="{{ route('admin.events.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label for="title" class="form-label fw-bold">Event Title</label>
                    <input type="text" class="form-control border rounded-3 shadow-sm ps-3 @error('title') is-invalid @enderror"
                           id="title" name="title" placeholder="Enter event title..." required>
                    @error('title')
                    <span class="invalid-feedback d-block">
                        {{$message}}
                    </span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label fw-bold">Event Description</label>
                    <textarea type="text" class="form-control border rounded-3 shadow-sm ps-3"
                              name="description" id="description" placeholder="Write a detailed description of the event..." rows="6"></textarea>
                    @error('description')
                    <span class="invalid-feedback d-block">
                        {{$message}}
                    </span>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="event_date" class="form-label fw-bold">Event Date</label>
                        <input type="date" class="form-control border rounded-3 shadow-sm ps-3 @error('event_date') is-invalid @enderror"
                               id="event_date" name="event_date" required>
                        @error('event_date')
                        <span class="invalid-feedback d-block">
                            {{$message}}
                        </span>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="event_time" class="form-label fw-bold">Event Time (Optional)</label>
                        <input type="time" class="form-control border rounded-3 shadow-sm ps-3 @error('event_time') is-invalid @enderror"
                               id="event_time" name="event_time">
                        @error('event_time')
                        <span class="invalid-feedback d-block">
                            {{$message}}
                        </span>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="location" class="form-label fw-bold">Select Hall/Venue</label>
                    <select class="form-control border rounded-3 shadow-sm ps-3 @error('location') is-invalid @enderror"
                            id="location" name="location">
                        <option value="">Choose a hall for the event...</option>
                        @foreach($halls as $hall)
                            <option value="{{ $hall->name }}" {{ old('location') == $hall->name ? 'selected' : '' }}>
                                {{ $hall->name }} (Capacity: {{ $hall->capacity }}, Area: {{ $hall->area }} sqft)
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Select the hall where this event will be held</small>
                    @error('location')
                    <span class="invalid-feedback d-block">
                        {{$message}}
                    </span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label fw-bold">Event Image (Optional)</label>
                    <input type="file" name="image" class="form-control border rounded-3 shadow-sm ps-3" accept="image/*">
                    <small class="text-muted">Accepted formats: JPEG, PNG, JPG, GIF. Max size: 2MB</small>
                    @error('image')
                    <span class="invalid-feedback d-block">
                        {{$message}}
                    </span>
                    @enderror
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input type="hidden" name="is_active" value="0">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                        <label class="form-check-label fw-bold" for="is_active">
                            Active Event
                        </label>
                        <small class="text-muted d-block">Uncheck to hide this event from the website</small>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <button type="submit" class="btn btn-primary px-4">Create Event</button>
                    <a href="{{ route('admin.events.index') }}" class="btn btn-outline-secondary px-4">Back to Events</a>
                </div>
            </form>
        </div>
    </div>
</body>

@endsection
