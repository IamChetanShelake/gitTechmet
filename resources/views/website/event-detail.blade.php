@extends('website.layout.master')

@section('content')
    <!-- content begin -->
    <div class="no-bottom no-top" id="content">
        <div id="top"></div>


        <section id="subheader" class="relative jarallax text-light">
                <img src="{{asset('website/assets/images/background/Background.jpg')}}" class="jarallax-img" alt="">
                <div class="container relative z-index-1000">
                    <div class="row justify-content-center">
                        <div class="col-lg-12 text-center">
                            <h1>Event Details</h1>
                            <ul class="crumb">
                                <li><a href="{{route('Index.Page')}}">Home</a></li>
                                <li class="active">Event Details</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="de-overlay"></div>
            </section>

        <!-- Event Detail Section -->
        <section class="relative lines-deco">
            <div class="container relative z-2">
                <div class="row g-4">
                    <div class="col-lg-8 offset-lg-2 text-center">
                        {{-- <div class="subtitle wow fadeInUp mb-3">Event Details</div> --}}
                        <h2 class="wow fadeInUp">{{ $event->title }}</h2>
                    </div>
                </div>

                <div class="row g-4 mt-4">
                    <div class="col-lg-8">
                        <div class="relative">
                            @if($event->image)
                                <img src="{{ asset('Event_images/' . $event->image) }}" class="w-100 rounded-up-100 mb-4" alt="{{ $event->title }}" style="height: 400px; object-fit: cover;">
                            @endif

                            <div class="event-info bg-white p-4 rounded-1 shadow-sm">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="event-date-time mb-3">
                                            <h4 class="mb-3"><i class="icofont-calendar text-primary"></i> Date & Time</h4>
                                            <div class="bg-light p-3 rounded-1">
                                                <div class="d-flex align-items-center mb-2">
                                                    <i class="icofont-calendar fs-20 text-primary me-2"></i>
                                                    <span class="fw-bold">{{ $event->formatted_date }}</span>

                                                </div>
                                                @if($event->formatted_time)
                                                    <div class="d-flex align-items-center">
                                                        <i class="icofont-clock-time fs-20 text-primary me-2"></i>
                                                        <span class="fw-bold">{{ $event->formatted_time }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>



                                    <div class="col-md-6">
                                        <div class="event-location mb-3">
                                            <h4 class="mb-3"><i class="icofont-location-pin text-primary"></i> Location</h4>
                                            <div class="bg-light p-3 rounded-1">
                                                <div class="d-flex align-items-start">
                                                    <i class="icofont-location-pin fs-20 text-primary me-2 mt-1"></i>
                                                    <span>{{ $event->location ?? 'Venue to be announced' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="event-description mt-4">
                                    <h4 class="mb-3">About This Event</h4>
                                    <div class="text-muted">
                                        {!! nl2br(e($event->description)) !!}
                                    </div>
                                </div>

                                <div class="event-actions mt-4">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <a href="{{ route('Index.Page') }}" class="btn-main w-100">
                                                <i class="icofont-home"></i> Back to Home
                                            </a>
                                        </div>
                                        <div class="col-md-6">
                                            <a href="{{ route('Enquiry.Page') }}" class="btn-line w-100">
                                                <i class="icofont-envelope"></i> Contact Us
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <!-- Upcoming Events Sidebar -->
                        @if($upcomingEvents->count() > 0)
                        <div class="bg-white p-4 rounded-1 shadow-sm">
                            <h4 class="mb-3">Other Upcoming Events</h4>
                            @foreach($upcomingEvents as $upcomingEvent)
                            <div class="upcoming-event-item mb-3 pb-3 border-bottom">
                                <h6 class="mb-2">
                                    <a href="{{ route('event.detail', $upcomingEvent->id) }}" class="text-decoration-none text-dark">
                                        {{ $upcomingEvent->title }}
                                    </a>
                                </h6>
                                <div class="small text-muted mb-2">
                                    <i class="icofont-calendar"></i> {{ $upcomingEvent->formatted_date }}
                                    @if($upcomingEvent->formatted_time)
                                        <br><i class="icofont-clock-time"></i> {{ $upcomingEvent->formatted_time }}
                                    @endif
                                </div>
                                @if($upcomingEvent->location)
                                    <div class="small text-muted">
                                        <i class="icofont-location-pin"></i> {{ Str::limit($upcomingEvent->location, 30) }}
                                    </div>
                                @endif
                            </div>
                            @endforeach

                            <div class="text-center mt-3">
                                <a href="{{ route('Index.Page') }}#upcoming-events" class="btn-line btn-sm">
                                    View All Events
                                </a>
                            </div>
                        </div>
                        @endif

                        <!-- Contact Information -->
                        <div class="bg-color text-light p-4 rounded-1 mt-4">
                            <h4 class="mb-3">Need More Information?</h4>
                            <p class="mb-3">Contact us for more details about this event or to inquire about booking.</p>
                            <div class="contact-info">
                                @if($contacts->count() > 0)
                                    @php $contact = $contacts->where('title','Phone details')->first(); @endphp
                                    <div>
                                        {!!$contact->description!!}
                                    </div>
                                @endif
                            </div>
                            <div class="mt-3">
                                <a href="{{ route('Enquiry.Page') }}" class="btn-main btn-sm">
                                    <i class="icofont-envelope"></i> Send Inquiry
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
