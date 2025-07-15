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
                    @if(isset($paymentStatus))
                        @php
                            $rentAmount = $paymentStatus['rent_amount'];
                            $depositAmount = $paymentStatus['deposit_amount'];
                            $rentWithGst = $paymentStatus['rent_with_gst'];
                            $depositPlusRentWithGst = $paymentStatus['total_amount'];
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
                                                            @endif
                                                        </strong>
                                                        <br>
                                                        <small class="text-muted">{{ $transaction['date'] ? $transaction['date']->format('d M Y, h:i A') : 'N/A' }}</small>
                                                        <br>
                                                        <small class="text-muted">ID: {{ $transaction['transaction_id'] }}</small>
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
                                    <div class="payment-option mb-3 p-3 border rounded {{ $paymentStatus['deposit_paid'] ? 'bg-light border-success' : 'bg-white' }}">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <h5 class="mb-1">
                                                    Pay Deposit Amount
                                                    @if($paymentStatus['deposit_paid'])
                                                        <span class="badge bg-success ms-2">PAID</span>
                                                    @endif
                                                </h5>
                                                <p class="mb-0 text-muted">Security deposit (No GST applicable)</p>
                                                @if($paymentStatus['deposit_paid'])
                                                    <strong class="text-success">₹{{ number_format($depositAmount, 2) }} - COMPLETED</strong>
                                                @else
                                                    <strong class="text-primary">₹{{ number_format($paymentStatus['remaining_deposit'], 2) }}</strong>
                                                @endif
                                            </div>
                                            <div class="col-md-4 text-end">
                                                @if(!$paymentStatus['deposit_paid'] && $paymentStatus['remaining_deposit'] > 0)
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
                                    <div class="payment-option mb-3 p-3 border rounded {{ $paymentStatus['rent_paid'] ? 'bg-light border-success' : 'bg-white' }}">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <h5 class="mb-1">
                                                    Pay Rent Amount
                                                    @if($paymentStatus['rent_paid'])
                                                        <span class="badge bg-success ms-2">PAID</span>
                                                    @endif
                                                </h5>
                                                <p class="mb-0 text-muted">Hall rent + 18% GST</p>
                                                @if($paymentStatus['rent_paid'])
                                                    <strong class="text-success">₹{{ number_format($rentWithGst, 2) }} - COMPLETED</strong>
                                                @else
                                                    <small class="text-muted">Rent: ₹{{ number_format($rentAmount, 2) }} + GST: ₹{{ number_format($rentAmount * 0.18, 2) }}</small><br>
                                                    <strong class="text-primary">₹{{ number_format($paymentStatus['remaining_rent'], 2) }}</strong>
                                                @endif
                                            </div>
                                            <div class="col-md-4 text-end">
                                                @if(!$paymentStatus['rent_paid'] && $paymentStatus['remaining_rent'] > 0)
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
