@extends('admin.layout.masteradmin')

@section('content')

<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card shadow-lg">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="mb-0">Event Details</h3>
                            <a href="{{ route('admin.events.index') }}" class="btn btn-light btn-sm">
                                <i class="fas fa-arrow-left"></i> Back to Events
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        @if($event->image)
                            <div class="text-center mb-4">
                                <img src="{{ asset('Event_images/' . $event->image) }}" alt="{{ $event->title }}" class="img-fluid rounded shadow" style="max-height: 300px;">
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-8">
                                <h4 class="text-primary mb-3">{{ $event->title }}</h4>

                                @if($event->description)
                                    <div class="mb-4">
                                        <h6 class="text-muted mb-2">Description</h6>
                                        <p class="text-justify">{{ $event->description }}</p>
                                    </div>
                                @endif

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <h6 class="text-muted mb-1"><i class="fas fa-calendar text-primary"></i> Event Date</h6>
                                            <p class="mb-0 fw-bold">{{ $event->formatted_date }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <h6 class="text-muted mb-1"><i class="fas fa-clock text-primary"></i> Event Time</h6>
                                            <p class="mb-0 fw-bold">{{ $event->formatted_time ?? 'Not specified' }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <h6 class="text-muted mb-1"><i class="fas fa-map-marker-alt text-primary"></i> Location</h6>
                                    <p class="mb-0 fw-bold">{{ $event->location ?? 'Not specified' }}</p>
                                    @if($event->hall_info)
                                        <small class="text-muted">
                                            Capacity: {{ $event->hall_info->capacity }} | Area: {{ $event->hall_info->area }} sqft
                                        </small>
                                    @endif
                                </div>

                                <div class="mb-3">
                                    <h6 class="text-muted mb-1"><i class="fas fa-info-circle text-primary"></i> Status</h6>
                                    @if($event->is_active)
                                        <span class="badge bg-success fs-6">Active</span>
                                    @else
                                        <span class="badge bg-secondary fs-6">Inactive</span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card border">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Quick Actions</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-grid gap-2">
                                            <a href="{{ route('admin.events.edit', $event->id) }}" class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit"></i> Edit Event
                                            </a>
                                            <a href="{{ route('event.detail', $event->id) }}" target="_blank" class="btn btn-info btn-sm">
                                                <i class="fas fa-external-link-alt"></i> View on Website
                                            </a>
                                            <form action="{{ route('admin.events.destroy', $event->id) }}" method="POST" class="mt-2"
                                                  onsubmit="return confirm('Are you sure you want to delete this event?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm w-100">
                                                    <i class="fas fa-trash"></i> Delete Event
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <div class="card border mt-3">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Event Statistics</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row text-center">
                                            <div class="col-6">
                                                <div class="border-end">
                                                    <h4 class="text-primary mb-0">{{ $event->event_date->isPast() ? 'Past' : 'Upcoming' }}</h4>
                                                    <small class="text-muted">Status</small>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <h4 class="text-success mb-0">{{ $event->created_at->diffForHumans() }}</h4>
                                                <small class="text-muted">Created</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <strong>Created:</strong> {{ $event->created_at->format('M d, Y \a\t h:i A') }}
                                </small>
                            </div>
                            <div class="col-md-6 text-end">
                                <small class="text-muted">
                                    <strong>Last Updated:</strong> {{ $event->updated_at->format('M d, Y \a\t h:i A') }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

@endsection
