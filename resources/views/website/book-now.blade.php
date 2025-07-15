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
                        @php
                            $rentAmount = $booking->total_rent ?? 0;
                            $depositAmount = $booking->total_deposit ?? 0;
                            $rentWithGst = $rentAmount * 1.18; // Add 18% GST to rent
                            $depositPlusRentWithGst = $depositAmount + $rentWithGst; // No GST on deposit, GST only on rent
                        @endphp
                        
                        <div class="row justify-content-center">
                            <div class="col-md-10">
                                <h4 class="mb-4 text-center">Choose Payment Option</h4>
                                
                                <!-- Payment Option 1: Deposit Only -->
                                <div class="payment-option mb-3 p-3 border rounded" style="background-color: #f8f9fa;">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <h5 class="mb-1">Pay Deposit Amount</h5>
                                            <p class="mb-0 text-muted">Security deposit (No GST applicable)</p>
                                            <strong class="text-primary">₹{{ number_format($depositAmount, 2) }}</strong>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <form method="POST" action="{{ route('payment.initiate', $booking->id) }}" style="display: inline;">
                                                @csrf
                                                <input type="hidden" name="payment_type" value="deposit">
                                                <button type="submit" class="btn text-white px-4 py-2" style="background-color: #28a745; border-radius: 8px; transition: 0.3s; border: none;">
                                                    Pay Deposit
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Payment Option 2: Rent Only -->
                                <div class="payment-option mb-3 p-3 border rounded" style="background-color: #f8f9fa;">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <h5 class="mb-1">Pay Rent Amount</h5>
                                            <p class="mb-0 text-muted">Hall rent + 18% GST</p>
                                            <small class="text-muted">Rent: ₹{{ number_format($rentAmount, 2) }} + GST: ₹{{ number_format($rentAmount * 0.18, 2) }}</small><br>
                                            <strong class="text-primary">₹{{ number_format($rentWithGst, 2) }}</strong>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <form method="POST" action="{{ route('payment.initiate', $booking->id) }}" style="display: inline;">
                                                @csrf
                                                <input type="hidden" name="payment_type" value="rent">
                                                <button type="submit" class="btn text-white px-4 py-2" style="background-color: #007bff; border-radius: 8px; transition: 0.3s; border: none;">
                                                    Pay Rent
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Payment Option 3: Deposit + Rent -->
                                <div class="payment-option mb-3 p-3 border rounded" style="background-color: #fff3cd;">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <h5 class="mb-1">Pay Deposit + Rent</h5>
                                            <p class="mb-0 text-muted">Complete payment (Deposit + Rent + GST on rent only)</p>
                                            <small class="text-muted">Deposit: ₹{{ number_format($depositAmount, 2) }} + Rent: ₹{{ number_format($rentAmount, 2) }} + GST: ₹{{ number_format($rentAmount * 0.18, 2) }}</small><br>
                                            <strong class="text-success">₹{{ number_format($depositPlusRentWithGst, 2) }}</strong>
                                            <span class="badge bg-success ms-2">Recommended</span>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <form method="POST" action="{{ route('payment.initiate', $booking->id) }}" style="display: inline;">
                                                @csrf
                                                <input type="hidden" name="payment_type" value="full">
                                                <button type="submit" class="btn text-white px-4 py-2" style="background-color: #AB8965; border-radius: 8px; transition: 0.3s; border: none;">
                                                    Pay Full Amount
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
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