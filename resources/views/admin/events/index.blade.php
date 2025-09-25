@extends('admin.layout.masteradmin')
@section('content')
    <div class="col-12">
        <div class="card my-4">
        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
            <div class="shadow-dark border-radius-lg pt-4 pb-3"
                        style="background-color: #70533A;">
                <h6 class="text-white text-capitalize ps-3">Upcoming Events Management</h6>
            </div>
        </div>

        <div class="d-flex justify-content-end p-3">
            <a class="btn btn-outline-primary btn-lg px-4 py-2" href="{{ route('admin.events.create') }}">Add New Event</a>
        </div>

        <div class="card-body px-0 pb-2">
            {{-- @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mx-3" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif --}}

            <div class="table-responsive p-0">
            <table class="table align-items-center mb-0">
                <thead>
                <tr>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Event Title</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Date & Time</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Location</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Status</th>
                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($events as $event)
                <tr>
                    <td>
                    <div class="d-flex px-2 py-1">
                        @if($event->image)
                        <img src="{{ asset('Event_images/' . $event->image) }}" class="avatar avatar-sm me-3 border-radius-lg" alt="event image">
                        @endif
                        <div class="d-flex flex-column justify-content-center">
                        <h6 class="mb-0 text-sm">{{ $event->title }}</h6>
                        <p class="text-xs text-secondary mb-0">{{ Str::limit($event->description, 50) }}</p>
                        </div>
                    </div>
                    </td>
                    <td>
                        <div class="d-flex flex-column">
                            <span class="text-sm font-weight-bold">{{ $event->formatted_date }}</span>
                            @if($event->formatted_time)
                                <span class="text-xs text-secondary">{{ $event->formatted_time }}</span>
                            @endif
                        </div>
                    </td>
                    <td>
                        <span class="text-sm">{{ $event->location ?? 'Not specified' }}</span>
                        @if($event->hall_info)
                            <br><small class="text-muted">Cap: {{ $event->hall_info->capacity }} | {{ $event->hall_info->area }} sqft</small>
                        @endif
                    </td>
                    <td>
                        @if($event->is_active)
                            <span class="badge badge-sm bg-gradient-success">Active</span>
                        @else
                            <span class="badge badge-sm bg-gradient-secondary">Inactive</span>
                        @endif
                    </td>
                    <td class="align-middle text-center">
                        <a href="{{ route('admin.events.show', $event->id) }}" class="btn btn-info btn-sm me-1">
                            <i class="fas fa-eye"></i> View
                        </a>
                        <a href="{{ route('admin.events.edit', $event->id) }}" class="btn btn-success btn-sm me-1">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <form action="{{ route('admin.events.destroy', $event->id) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Are you sure you want to delete this event?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-4">
                        <div class="text-muted">
                            <i class="fas fa-calendar-times fa-3x mb-3"></i>
                            <h5>No Events Found</h5>
                            <p>Start by adding your first upcoming event.</p>
                            <a href="{{ route('admin.events.create') }}" class="btn btn-primary">Add First Event</a>
                        </div>
                    </td>
                </tr>
                @endforelse
                </tbody>
            </table>
            </div>

            @if($events->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $events->links() }}
            </div>
            @endif
        </div>
        </div>
    </div>
@endsection
