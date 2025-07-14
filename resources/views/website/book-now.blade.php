@extends('website.layout.master')

@section('content')
    <!-- content begin -->
    <div class="no-bottom no-top" id="content">
        <div id="top"></div>
        <section id="subheader" class="relative jarallax text-light">
            <img src="{{ asset('website/assets/images/background/Background.jpg') }}" class="jarallax-img" alt="">
            <div class="container relative z-index-1000">
                <div class="row justify-content-center">
                    <div class="col-lg-6 text-center">
                        <h1>Book Now</h1>

                        <ul class="crumb">
                            <li><a href="{{ route('Index.Page') }}">Home</a></li>
                            <li class="active">Book Now</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="de-overlay"></div>
        </section>

        <div class="container mt-4">
            <div class="card shadow-lg p-4">
                <h2 class="text-center mb-4">{{ $booking->enquiry->hall ?? 'No Hall Assigned' }}</h2>

                <!-- Hall Image -->
                @if ($hall && $hall->image)
                    <div class="text-center my-3">
                        <img src="{{ asset('Hall_images/'.$hall->image) }}"
                            alt="Hall Image"
                            class="img-fluid rounded shadow w-100"
                            style="max-width: 500px; height: auto; object-fit: cover;">
                    </div>
                @else
                    <p class="text-muted text-center">No Image Available</p>
                @endif


                <hr>

                <div class="row" style="padding: 60px;">
                    <div class="col-md-6">
                        <p><strong>Customer Name:</strong> {{ $booking->enquiry->name ?? 'N/A' }}</p>
                        <p><strong>Organization:</strong> {{  $booking->enquiry->organization ?? 'N/A' }}</p>
                        <p><strong>Email:</strong> {{  $booking->enquiry->email ?? 'N/A' }}</p>
                        <p><strong>Contact No:</strong> {{  $booking->enquiry->contact_no ?? 'N/A' }}</p>
                        <p><strong>Event Type:</strong> {{  $booking->enquiry->event_type ?? 'N/A' }}</p>
                        <p><strong>Event Date:</strong> {{  $booking->enquiry->event_date ?? 'N/A' }}</p>
                        <p><strong>Duration:</strong> {{  $booking->enquiry->duration ?? 'N/A' }}</p>
                        <p><strong>Expected Audience:</strong> {{  $booking->enquiry->expected_audience ?? 'N/A' }}</p>
                        {{-- <p><strong>Status:</strong> {{ $booking->status ?? 'Pending' }}</p> --}}
                    </div>

                    <div class="col-md-6">
                        <p><strong>Rent Amount:</strong> ₹{{ number_format($booking->enquiry->rent_amount, 2) }}</p>
                        <p><strong>GST No:</strong> {{  $booking->enquiry->gst_no ?? 'N/A' }}</p>
                        <p><strong>Address:</strong> {{  $booking->enquiry->address ?? 'N/A' }}</p>
                        <p><strong>Referred By:</strong> {{  $booking->enquiry->referred_by ?? 'N/A' }}</p>
                        <p><strong>Special Note:</strong> {{  $booking->enquiry->special_note ?? 'N/A' }}</p>
                        <p><strong>ID Proof:</strong> {{  $booking->enquiry->Id_proof ?? 'N/A' }}</p>
                        <p><strong>Event Setup:</strong> {{  $booking->enquiry->event_setup ?? 'N/A' }}</p>

                        {{-- <p><strong>Accessories:</strong></p>
                        @php
                            $selected_accessories = json_decode( $booking->enquiry->accessorie ?? '[]', true);
                            $accessory_names = \App\Models\Accessorie::whereIn('id', $selected_accessories)->pluck('name')->toArray();
                        @endphp
                        <ul>
                            @if (!empty($accessory_names))
                                @foreach ($accessory_names as $accessory)
                                    <li>{{ $accessory }}</li>
                                @endforeach
                            @else
                                <li>N/A</li>
                            @endif
                        </ul> --}}
                    </div>
                </div>

                <div class="text-center mt-4">
                    @if($booking && ($booking->remaining_amount === null || $booking->remaining_amount > 0))
                        <form method="POST" action="{{ route('payment.initiate', $booking->id) }}" style="display: inline;">
                            @csrf
                            <button type="submit" class="text-white px-5 py-2" style="background-color: #AB8965; border-radius: 8px; transition: 0.3s; border: none; cursor: pointer;">
                                Pay Now - ₹{{ number_format($totalAmount ?? 0, 2) }}
                            </button>
                        </form>
                    @else
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> Payment Completed Successfully!
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
@endsection