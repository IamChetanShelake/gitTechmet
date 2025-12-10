@extends('website.layout.master')

@section('content')
<style>
/* Custom Button Styles */
.custom-btn {
    display: inline-block;
    padding: 12px 24px;
    font-size: 16px;
    font-weight: 600;
    text-align: center;
    text-decoration: none;
    border: none;
    cursor: pointer;
    border-radius: 8px;
    transition: all 0.3s ease;
    min-width: 120px;
    font-family: inherit;
}

.custom-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.custom-btn:active {
    transform: translateY(0);
}

.custom-btn-success {
    background-color: #28a745;
    color: white;
}

.custom-btn-success:hover {
    background-color: #218838;
}

.custom-btn-primary {
    background-color: #007bff;
    color: white;
}

.custom-btn-primary:hover {
    background-color: #0056b3;
}

.custom-btn-warning {
    background-color: #AB8965;
    color: white;
}

.custom-btn-warning:hover {
    background-color: #8b6f52;
}

.custom-btn-disabled {
    background-color: #6c757d;
    color: white;
    cursor: not-allowed;
    opacity: 0.6;
}

.custom-btn-disabled:hover {
    transform: none;
    box-shadow: none;
    background-color: #6c757d;
}

.custom-btn-large {
    padding: 16px 32px;
    font-size: 18px;
}

.custom-btn-small {
    padding: 8px 16px;
    font-size: 14px;
}
</style>
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
                @php
                    // Check if this is a multi-hall booking
                    $groupedBookings = collect();
                    if ($booking->group_code) {
                        $groupedBookings = \App\Models\BookedHall::where('group_code', $booking->group_code)->get();
                    } else {
                        $groupedBookings = collect([$booking]);
                    }
                    $isMultiHall = $groupedBookings->count() > 1;
                @endphp

                <h2 class="text-center mb-4">
                    @if($isMultiHall)
                        Multi-Hall Booking ({{ $groupedBookings->count() }} Halls)
                    @else
                        {{ $booking->enquiry->hall ?? 'No Hall Assigned' }}
                    @endif
                </h2>

                <!-- Hall Images/Details for Multi-Hall Bookings -->
                @if($isMultiHall)
                    <div class="row mb-4">
                        @foreach($groupedBookings as $index => $hallBooking)
                            @php
                                $hall = \App\Models\Hall::where('name', $hallBooking->hall_name)->first();
                            @endphp
                            <div class="col-md-6 col-lg-{{ $groupedBookings->count() == 2 ? '6' : '4' }} mb-3">
                                <div class="card border-primary">
                                    <div class="card-header bg-primary text-white text-center">
                                        <h6 class="mb-0">Hall {{ $index + 1 }}: {{ $hallBooking->hall_name }}</h6>
                                    </div>
                                    <div class="card-body text-center p-2">
                                        @if ($hall && $hall->image)
                                            <img src="{{ asset('Hall_images/'.$hall->image) }}"
                                                alt="{{ $hallBooking->hall_name }} Image"
                                                class="img-fluid rounded shadow"
                                                style="max-width: 100%; height: 150px; object-fit: cover;">
                                        @else
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 150px;">
                                                <i class="fas fa-building fa-3x text-muted"></i>
                                            </div>
                                        @endif
                                        <p class="mt-2 mb-0 small text-muted">
                                            <strong>Event Date:</strong> {{ $hallBooking->event_date ?? 'N/A' }}<br>
                                            <strong>Time:</strong> {{ $hallBooking->start_time ?? 'N/A' }} - {{ $hallBooking->end_time ?? 'N/A' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <!-- Single Hall Image -->
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
                @endif


                <!-- Hall Details Section for Multi-Hall Bookings -->
                @if($isMultiHall)
                    <div class="mb-4">
                        <h4 class="text-center mb-4">Hall Details & Pricing</h4>
                        <div class="row">
                            @foreach($groupedBookings as $index => $hallBooking)
                                @php
                                    // Calculate pricing for this specific hall (matching invoice calculation)
                                    $hallRent = $hallBooking->total_rent ?? 0;
                                    $hallDeposit = $hallBooking->total_deposit ?? 0;

                                    // Calculate total hours from start and end time
                                    $startTime = \Carbon\Carbon::createFromFormat('H:i:s', $hallBooking->start_time . ':00');
                                    $endTime = \Carbon\Carbon::createFromFormat('H:i:s', $hallBooking->end_time . ':00');
                                    $totalHours = $startTime->diffInHours($endTime, false);

                                    // Get number of days from enquiry (same as invoice)
                                    $dates = [];
                                    if ($hallBooking->enquiry) {
                                        $dates = $hallBooking->enquiry->event_dates ? json_decode($hallBooking->enquiry->event_dates, true) : [$hallBooking->enquiry->event_date];
                                        $dates = array_filter($dates);
                                    }
                                    $numberOfDays = count($dates);

                                    // Get accessories for this hall (matching invoice calculation)
                                    $hallAccessoriesAmount = 0;
                                    if ($hallBooking->enquiry && $hallBooking->enquiry->accessorie) {
                                        $accessoryIds = json_decode($hallBooking->enquiry->accessorie, true);
                                        if (is_array($accessoryIds)) {
                                            $accessories = \App\Models\Accessorie::whereIn('id', $accessoryIds)->get();
                                            $hallAccessoriesAmount = $accessories->sum(function ($accessory) use ($totalHours, $numberOfDays) {
                                                $price = (float) ($accessory->price ?? 0);
                                                $hours = (float) ($accessory->hours ?? 1);
                                                if ($price <= 0 || $hours <= 0) return 0;

                                                // Calculate blocks per day (same as invoice)
                                                $blocksPerDay = floor($totalHours / $hours);
                                                $pricePerDay = $price * max($blocksPerDay, 1);

                                                // Multiply by number of days (same as invoice)
                                                return $pricePerDay * $numberOfDays;
                                            });
                                        }
                                    }

                                    // Calculate exactly like invoice: Rent + Accessories = Subtotal, then GST on subtotal
                                    $hallSubtotal = $hallRent + $hallAccessoriesAmount;
                                    $hallGst = $hallSubtotal * 0.18; // 18% GST (CGST 9% + SGST 9%)
                                    $hallRentWithGst = $hallSubtotal + $hallGst;
                                    $hallTotal = $hallDeposit + $hallRentWithGst;
                                @endphp

                                <div class="col-lg-6 mb-4">
                                    <div class="card border-info h-100">
                                        <div class="card-header bg-info text-white">
                                            <h6 class="mb-0">
                                                <i class="fas fa-building me-2"></i>
                                                Hall {{ $index + 1 }}: {{ $hallBooking->hall_name }}
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <p class="mb-1"><strong>Event Date:</strong></p>
                                                    <p class="text-primary">{{ $hallBooking->event_date ?? 'N/A' }}</p>
                                                </div>
                                                <div class="col-sm-6">
                                                    <p class="mb-1"><strong>Duration:</strong></p>
                                                    <p class="text-primary">{{ $hallBooking->duration ?? 'N/A' }}</p>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <p class="mb-1"><strong>Start Time:</strong></p>
                                                    <p class="text-primary">{{ $hallBooking->start_time ?? 'N/A' }}</p>
                                                </div>
                                                <div class="col-sm-6">
                                                    <p class="mb-1"><strong>End Time:</strong></p>
                                                    <p class="text-primary">{{ $hallBooking->end_time ?? 'N/A' }}</p>
                                                </div>
                                            </div>
                                            <hr>
                                            <div class="pricing-breakdown">
                                                <div class="d-flex justify-content-between mb-1">
                                                    <span>Hall Rent:</span>
                                                    <span>₹{{ number_format($hallRent, 2) }}</span>
                                                </div>
                                                <div class="d-flex justify-content-between mb-1">
                                                    <span>Accessories:</span>
                                                    <span>₹{{ number_format($hallAccessoriesAmount, 2) }}</span>
                                                </div>
                                                <div class="d-flex justify-content-between mb-1">
                                                    <span>Subtotal:</span>
                                                    <span>₹{{ number_format($hallSubtotal, 2) }}</span>
                                                </div>
                                                <div class="d-flex justify-content-between mb-1">
                                                    <span>GST (18%):</span>
                                                    <span>₹{{ number_format($hallSubtotal * 0.18, 2) }}</span>
                                                </div>
                                                <div class="d-flex justify-content-between mb-1">
                                                    <span>Rent + GST:</span>
                                                    <span class="text-warning">₹{{ number_format($hallRentWithGst, 2) }}</span>
                                                </div>
                                                <div class="d-flex justify-content-between mb-1">
                                                    <span>Deposit:</span>
                                                    <span>₹{{ number_format($hallDeposit, 2) }}</span>
                                                </div>
                                                <hr class="my-2">
                                                <div class="d-flex justify-content-between">
                                                    <strong>Total for this Hall:</strong>
                                                    <strong class="text-success">₹{{ number_format($hallTotal, 2) }}</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Multi-Hall Summary -->
                        <div class="card border-success mt-4">
                            <div class="card-header bg-success text-white text-center">
                                <h6 class="mb-0">
                                    <i class="fas fa-calculator me-2"></i>
                                    Booking Summary ({{ $groupedBookings->count() }} Halls)
                                </h6>
                            </div>
                            <div class="card-body">
                                @php
                                    $totalRent = $groupedBookings->sum('total_rent');
                                    $totalDeposit = $groupedBookings->sum('total_deposit');
                                    $totalAccessories = 0;
                                    $totalRentWithGst = 0;

                                    foreach($groupedBookings as $hallBooking) {
                                        // Calculate accessories for this hall (matching invoice calculation)
                                        $startTime = \Carbon\Carbon::createFromFormat('H:i:s', $hallBooking->start_time . ':00');
                                        $endTime = \Carbon\Carbon::createFromFormat('H:i:s', $hallBooking->end_time . ':00');
                                        $totalHours = $startTime->diffInHours($endTime, false);

                                        // Get number of days from enquiry (same as invoice)
                                        $dates = [];
                                        if ($hallBooking->enquiry) {
                                            $dates = $hallBooking->enquiry->event_dates ? json_decode($hallBooking->enquiry->event_dates, true) : [$hallBooking->enquiry->event_date];
                                            $dates = array_filter($dates);
                                        }
                                        $numberOfDays = count($dates);

                                        if ($hallBooking->enquiry && $hallBooking->enquiry->accessorie) {
                                            $accessoryIds = json_decode($hallBooking->enquiry->accessorie, true);
                                            if (is_array($accessoryIds)) {
                                                $accessories = \App\Models\Accessorie::whereIn('id', $accessoryIds)->get();
                                                $hallAccessories = $accessories->sum(function ($accessory) use ($totalHours, $numberOfDays) {
                                                    $price = (float) ($accessory->price ?? 0);
                                                    $hours = (float) ($accessory->hours ?? 1);
                                                    if ($price <= 0 || $hours <= 0) return 0;

                                                    // Calculate blocks per day (same as invoice)
                                                    $blocksPerDay = floor($totalHours / $hours);
                                                    $pricePerDay = $price * max($blocksPerDay, 1);

                                                    // Multiply by number of days (same as invoice)
                                                    return $pricePerDay * $numberOfDays;
                                                });
                                                $totalAccessories += $hallAccessories;
                                            }
                                        }

                                        $hallSubtotal = ($hallBooking->total_rent ?? 0) + $hallAccessories;
                                        $totalRentWithGst += $hallSubtotal * 1.18;
                                    }

                                    $grandTotal = $totalDeposit + $totalRentWithGst;
                                @endphp

                                <div class="row text-center">
                                    <div class="col-md-3">
                                        <div class="p-3 bg-light rounded">
                                            <h5 class="text-primary mb-1">{{ $groupedBookings->count() }}</h5>
                                            <small class="text-muted">Total Halls</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3 bg-light rounded">
                                            <h5 class="text-info mb-1">₹{{ number_format($totalRent + $totalAccessories, 2) }}</h5>
                                            <small class="text-muted">Total Rent + Accessories</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3 bg-light rounded">
                                            <h5 class="text-warning mb-1">₹{{ number_format($totalDeposit, 2) }}</h5>
                                            <small class="text-muted">Total Deposit</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3 bg-light rounded">
                                            <h5 class="text-success mb-1">₹{{ number_format($grandTotal, 2) }}</h5>
                                            <small class="text-muted">Grand Total</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>
                @endif

                @if(!$isMultiHall)
                    <div class="row" style="padding: 60px;">
                        <div class="col-md-6">
                            <p><strong>Customer Name:</strong> {{ $booking->enquiry->name ?? 'N/A' }}</p>
                            <p><strong>Organization:</strong> {{  $booking->enquiry->organization ?? 'N/A' }}</p>
                            <p><strong>Email:</strong> {{  $booking->enquiry->email ?? 'N/A' }}</p>
                            <p><strong>Contact No:</strong> {{  $booking->enquiry->contact_no ?? 'N/A' }}</p>
                            <p><strong>Event Type:</strong> {{  $booking->enquiry->event_type ?? 'N/A' }}</p>
                            @if(!$isMultiHall)
                            <p><strong>Event Date:</strong> {{  $booking->enquiry->event_date ?? 'N/A' }}</p>
                            <p><strong>Duration:</strong> {{  $booking->enquiry->duration ?? 'N/A' }}</p>
                            @endif
                            <p><strong>Expected Audience:</strong> {{  $booking->enquiry->expected_audience ?? 'N/A' }}</p>
                            {{-- <p><strong>Status:</strong> {{ $booking->status ?? 'Pending' }}</p> --}}
                        </div>

                        <div class="col-md-6">
                            @if($isMultiHall)
                                <p><strong>Total Rent Amount (All Halls):</strong> ₹{{ number_format($paymentStatus['rent_amount'], 2) }}</p>
                                <p><strong>Total Deposit Amount:</strong> ₹{{ number_format($paymentStatus['deposit_amount'], 2) }}</p>
                                <p><strong>Grand Total:</strong> ₹{{ number_format($paymentStatus['total_amount'], 2) }}</p>
                            @else
                                <p><strong>Rent Amount (Including Accessories):</strong> ₹{{ number_format($paymentStatus['rent_amount'], 2) }}</p>
                            @endif
                            <p><strong>GST No:</strong> {{  $booking->enquiry->gst_no ?? 'N/A' }}</p>
                            <p><strong>Address:</strong> {{  $booking->enquiry->address ?? 'N/A' }}</p>
                            <p><strong>Referred By:</strong> {{  $booking->enquiry->referred_by ?? 'N/A' }}</p>
                            <p><strong>Special Note:</strong> {{  $booking->enquiry->special_note ?? 'N/A' }}</p>
                            <p><strong>ID Proof:</strong> {{  $booking->enquiry->Id_proof ?? 'N/A' }}</p>
                            <p><strong>Event Setup:</strong> {{  $booking->enquiry->event_setup ?? 'N/A' }}</p>
                        </div>
                    </div>
                @endif

                <div class="text-center mt-4">
                    @if(isset($paymentStatus))
                        @php
                            // For multi-hall bookings, use combined amounts
                            if($isMultiHall) {
                                $totalRent = $groupedBookings->sum('total_rent');
                                $totalDeposit = $groupedBookings->sum('total_deposit');
                                $totalAccessories = 0;
                                $totalRentWithGst = 0;

                                foreach($groupedBookings as $hallBooking) {
                                    // Calculate accessories for this hall
                                    $startTime = \Carbon\Carbon::createFromFormat('H:i:s', $hallBooking->start_time . ':00');
                                    $endTime = \Carbon\Carbon::createFromFormat('H:i:s', $hallBooking->end_time . ':00');
                                    $totalHours = $startTime->diffInHours($endTime, false);

                                    // Get number of days from enquiry
                                    $dates = [];
                                    if ($hallBooking->enquiry) {
                                        $dates = $hallBooking->enquiry->event_dates ? json_decode($hallBooking->enquiry->event_dates, true) : [$hallBooking->enquiry->event_date];
                                        $dates = array_filter($dates);
                                    }
                                    $numberOfDays = count($dates);

                                    if ($hallBooking->enquiry && $hallBooking->enquiry->accessorie) {
                                        $accessoryIds = json_decode($hallBooking->enquiry->accessorie, true);
                                        if (is_array($accessoryIds)) {
                                            $accessories = \App\Models\Accessorie::whereIn('id', $accessoryIds)->get();
                                            $hallAccessories = $accessories->sum(function ($accessory) use ($totalHours, $numberOfDays) {
                                                $price = (float) ($accessory->price ?? 0);
                                                $hours = (float) ($accessory->hours ?? 1);
                                                if ($price <= 0 || $hours <= 0) return 0;
                                                $blocksPerDay = floor($totalHours / $hours);
                                                $pricePerDay = $price * max($blocksPerDay, 1);
                                                return $pricePerDay * $numberOfDays;
                                            });
                                            $totalAccessories += $hallAccessories;
                                        }
                                    }

                                    $hallSubtotal = ($hallBooking->total_rent ?? 0) + $hallAccessories;
                                    $totalRentWithGst += $hallSubtotal * 1.18;
                                }

                                $rentAmount = $totalRent + $totalAccessories;
                                $depositAmount = $totalDeposit;
                                $rentWithGst = $totalRentWithGst;
                                $depositPlusRentWithGst = $totalDeposit + $totalRentWithGst;
                            } else {
                                $rentAmount = $paymentStatus['rent_amount'];
                                $depositAmount = $paymentStatus['deposit_amount'];
                                $rentWithGst = $paymentStatus['rent_with_gst'];
                                $depositPlusRentWithGst = $paymentStatus['total_amount'];
                            }

                            $allPaymentsCompleted = $paymentStatus['deposit_paid'] && $paymentStatus['rent_paid'];
                        @endphp

                        @if($allPaymentsCompleted)
                            <!-- All Payments Completed -->
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> All Payments Completed Successfully!
                            </div>
                        @endif

                        <!-- Payment Status Summary -->
                        @if(!empty($paymentStatus['paid_transactions']))
                            <div class="row justify-content-center mb-4">
                                <div class="col-md-10">
                                    <div class="card border-success">
                                        <div class="card-header bg-success text-white">
                                            <h5 class="mb-0"><i class="fas fa-check-circle"></i> Completed Payments</h5>
                                        </div>
                                        <div class="card-body">
                                            @foreach($paymentStatus['paid_transactions'] as $transaction)
                                                <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                                                    <div>
                                                        <strong>
                                                            @if($transaction['type'] == 'deposit')
                                                                <span class="badge bg-info">Deposit Payment</span>
                                                            @elseif($transaction['type'] == 'rent')
                                                                <span class="badge bg-warning">Rent Payment</span>
                                                            @elseif($transaction['type'] == 'full')
                                                                <span class="badge bg-success">Full Payment</span>
                                                            @elseif($transaction['type'] == 'partial')
                                                                <span class="badge bg-secondary">Partial Payment</span>
                                                            @endif
                                                        </strong>
                                                        <br>
                                                        <small class="text-muted">{{ $transaction['date'] ? $transaction['date']->format('d M Y, h:i A') : 'N/A' }}</small>
                                                        <br>
                                                        <small class="text-muted">ID: {{ $transaction['transaction_id'] }}</small>
                                                        @if(isset($transaction['original_type']) && ($transaction['original_type'] == 'CASH' || $transaction['original_type'] == 'CHECK'))
                                                            <br>
                                                            <small class="text-info">
                                                                <i class="fas fa-hand-holding-usd"></i>
                                                                {{ $transaction['original_type'] == 'CASH' ? 'Cash Payment' : 'Check Payment' }}
                                                            </small>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <h5 class="text-success mb-0">₹{{ number_format($transaction['amount'], 2) }}</h5>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if(!$allPaymentsCompleted)
                            <div class="row justify-content-center">
                                <div class="col-md-10">
                                    <h4 class="mb-4 text-center">
                                        @if(empty($paymentStatus['paid_transactions']))
                                            Choose Payment Option
                                        @else
                                            Pay Remaining Amount
                                        @endif
                                    </h4>

                                    <!-- Payment Option 1: Deposit Only -->
                                    @php
                                        // Calculate remaining deposit for multi-hall bookings
                                        $remainingDeposit = $isMultiHall ?
                                            max(0, $depositAmount - collect($paymentStatus['paid_transactions'])->where('type', 'deposit')->sum('amount')) :
                                            $paymentStatus['remaining_deposit'];
                                        $depositPaid = $remainingDeposit <= 0;
                                    @endphp
                                    <div class="payment-option mb-3 p-3 border rounded {{ $depositPaid ? 'bg-light border-success' : 'bg-white' }}">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <h5 class="mb-1">
                                                    Pay Deposit Amount
                                                    @if($depositPaid)
                                                        <span class="badge bg-success ms-2">PAID</span>
                                                    @endif
                                                </h5>
                                                <p class="mb-0 text-muted">Security deposit (No GST applicable)</p>
                                                @if($depositPaid)
                                                    <strong class="text-success">₹{{ number_format($depositAmount, 2) }} - COMPLETED</strong>
                                                @else
                                                    <strong class="text-primary">₹{{ number_format($remainingDeposit, 2) }}</strong>
                                                @endif
                                            </div>
                                            <div class="col-md-4 text-end">
                                                @if(!$depositPaid && $remainingDeposit > 0)
                                                    <form method="POST" action="{{ route('payment.initiate', $booking->id) }}" style="display: inline;">
                                                        @csrf
                                                        <input type="hidden" name="payment_type" value="deposit">
                                                        <button type="submit" class="custom-btn custom-btn-success">
                                                            Pay Deposit
                                                        </button>
                                                    </form>
                                                @else
                                                    <button class="custom-btn custom-btn-disabled" disabled>
                                                        <i class="fas fa-check"></i> Paid
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Payment Option 2: Rent Only -->
                                    @php
                                        // Calculate remaining rent for multi-hall bookings
                                        $remainingRent = $isMultiHall ?
                                            max(0, $rentWithGst - collect($paymentStatus['paid_transactions'])->whereIn('type', ['rent', 'full'])->sum('amount')) :
                                            $paymentStatus['remaining_rent'];
                                        $rentPaid = $remainingRent <= 0;
                                    @endphp
                                    <div class="payment-option mb-3 p-3 border rounded {{ $rentPaid ? 'bg-light border-success' : 'bg-white' }}">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <h5 class="mb-1">
                                                    Pay Rent Amount
                                                    @if($rentPaid)
                                                        <span class="badge bg-success ms-2">PAID</span>
                                                    @endif
                                                </h5>
                                                <p class="mb-0 text-muted">Hall rent + 18% GST</p>
                                                @if($rentPaid)
                                                    <strong class="text-success">₹{{ number_format($rentWithGst, 2) }} - COMPLETED</strong>
                                                @else
                                                    <small class="text-muted">Rent: ₹{{ number_format($rentAmount, 2) }} + GST: ₹{{ number_format($rentAmount * 0.18, 2) }}</small><br>
                                                    <strong class="text-primary">₹{{ number_format($remainingRent, 2) }}</strong>
                                                @endif
                                            </div>
                                            <div class="col-md-4 text-end">
                                                @if(!$rentPaid && $remainingRent > 0)
                                                    <form method="POST" action="{{ route('payment.initiate', $booking->id) }}" style="display: inline;">
                                                        @csrf
                                                        <input type="hidden" name="payment_type" value="rent">
                                                        <button type="submit" class="custom-btn custom-btn-primary">
                                                            Pay Rent
                                                        </button>
                                                    </form>
                                                @else
                                                    <button class="custom-btn custom-btn-disabled" disabled>
                                                        <i class="fas fa-check"></i> Paid
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Payment Option 3: Deposit + Rent (Full Payment) -->
                                    @php
                                        $partialPaymentMade = $paymentStatus['deposit_paid'] || $paymentStatus['rent_paid'];
                                        $remainingFullAmount = $paymentStatus['remaining_deposit'] + $paymentStatus['remaining_rent'];
                                    @endphp

                                    <div class="payment-option mb-3 p-3 border rounded {{ $partialPaymentMade ? 'bg-light border-secondary' : 'bg-warning bg-opacity-10' }}">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <h5 class="mb-1">
                                                    @if($partialPaymentMade)
                                                        Pay Remaining Amount
                                                    @else
                                                        Pay Deposit + Rent (Full Payment)
                                                    @endif
                                                    @if($allPaymentsCompleted)
                                                        <span class="badge bg-success ms-2">COMPLETED</span>
                                                    @elseif($partialPaymentMade)
                                                        <span class="badge bg-warning ms-2">PARTIAL</span>
                                                    @else
                                                        <span class="badge bg-success ms-2">Recommended</span>
                                                    @endif
                                                </h5>
                                                @if($partialPaymentMade)
                                                    <p class="mb-0 text-muted">Pay remaining balance</p>
                                                    <strong class="text-primary">₹{{ number_format($remainingFullAmount, 2) }}</strong>
                                                @else
                                                    <p class="mb-0 text-muted">Complete payment (Deposit + Rent + GST on rent only)</p>
                                                    <small class="text-muted">Deposit: ₹{{ number_format($depositAmount, 2) }} + Rent: ₹{{ number_format($rentAmount, 2) }} + GST: ₹{{ number_format($rentAmount * 0.18, 2) }}</small><br>
                                                    <strong class="text-success">₹{{ number_format($depositPlusRentWithGst, 2) }}</strong>
                                                @endif
                                            </div>
                                            <div class="col-md-4 text-end">
                                                @if($allPaymentsCompleted)
                                                    <button class="custom-btn custom-btn-disabled" disabled>
                                                        <i class="fas fa-check"></i> Completed
                                                    </button>
                                                @elseif($partialPaymentMade)
                                                    @if($remainingFullAmount > 0)
                                                        <form method="POST" action="{{ route('payment.initiate', $booking->id) }}" style="display: inline;">
                                                            @csrf
                                                            <input type="hidden" name="payment_type" value="remaining">
                                                            <button type="submit" class="custom-btn custom-btn-warning">
                                                                Pay Remaining
                                                            </button>
                                                        </form>
                                                    @else
                                                        <button class="custom-btn custom-btn-disabled" disabled>
                                                            <i class="fas fa-check"></i> Completed
                                                        </button>
                                                    @endif
                                                @else
                                                    <form method="POST" action="{{ route('payment.initiate', $booking->id) }}" style="display: inline;">
                                                        @csrf
                                                        <input type="hidden" name="payment_type" value="full">
                                                        <button type="submit" class="custom-btn custom-btn-warning">
                                                            Pay Full Amount
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @else
                        <!-- Fallback for when paymentStatus is not available -->
                        @if($booking && ($booking->remaining_amount === null || $booking->remaining_amount > 0))
                            @php
                                $rentAmount = $booking->total_rent ?? 0;
                                $depositAmount = $booking->total_deposit ?? 0;
                                $rentWithGst = $rentAmount * 1.18;
                                $depositPlusRentWithGst = $depositAmount + $rentWithGst;
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
                                                    <button type="submit" class="custom-btn custom-btn-success">
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
                                                    <button type="submit" class="custom-btn custom-btn-primary">
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
                                                    <button type="submit" class="custom-btn custom-btn-warning">
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
                    @endif
                </div>
            </div>
        </div>

    </div>
@endsection
