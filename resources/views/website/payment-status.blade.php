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
                        <h1>Payment Status</h1>
                        <ul class="crumb">
                            <li><a href="{{ route('Index.Page') }}">Home</a></li>
                            <li class="active">Payment Status</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="de-overlay"></div>
        </section>

        <section>
            <div class="container mt-4">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="card shadow-lg">
                            <div class="card-body text-center p-5">
                                @if($status === 'success')
                                    <div class="text-success mb-4">
                                        <i class="fas fa-check-circle" style="font-size: 4rem;"></i>
                                    </div>
                                    <h2 class="text-success mb-3">Payment Successful!</h2>
                                    <p class="lead">{{ $message }}</p>
                                    
                                    @if(isset($transaction))
                                        <div class="mt-4">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p><strong>Transaction ID:</strong> {{ $transaction->merchant_txn_no }}</p>
                                                    <p><strong>Amount Paid:</strong> ₹{{ number_format($transaction->amount, 2) }}</p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p><strong>Payment Date:</strong> {{ $transaction->payment_date ? $transaction->payment_date->format('d M Y, h:i A') : 'N/A' }}</p>
                                                    <p><strong>Gateway Ref:</strong> {{ $transaction->payphi_txn_no ?? 'N/A' }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if(isset($booking))
                                        <div class="mt-4 p-3 bg-light rounded">
                                            <h5>Booking Details</h5>
                                            <p><strong>Hall:</strong> {{ $booking->hall_name }}</p>
                                            <p><strong>Event Date:</strong> {{ $booking->event_date }}</p>
                                            <p><strong>Customer:</strong> {{ $booking->customer_name }}</p>
                                        </div>
                                    @endif

                                @elseif($status === 'failed')
                                    <div class="text-danger mb-4">
                                        <i class="fas fa-times-circle" style="font-size: 4rem;"></i>
                                    </div>
                                    <h2 class="text-danger mb-3">Payment Failed</h2>
                                    <p class="lead">{{ $message }}</p>
                                    
                                    @if(isset($transaction))
                                        <div class="mt-4">
                                            <p><strong>Transaction ID:</strong> {{ $transaction->merchant_txn_no }}</p>
                                            <p><strong>Response Code:</strong> {{ $transaction->response_code ?? 'N/A' }}</p>
                                        </div>
                                    @endif

                                @else
                                    <div class="text-warning mb-4">
                                        <i class="fas fa-exclamation-triangle" style="font-size: 4rem;"></i>
                                    </div>
                                    <h2 class="text-warning mb-3">Payment Error</h2>
                                    <p class="lead">{{ $message }}</p>
                                @endif

                                <div class="mt-4">
                                    @if($status === 'success')
                                        <a href="{{ route('Index.Page') }}" class="btn btn-success btn-lg me-3">
                                            <i class="fas fa-home"></i> Go to Home
                                        </a>
                                        <button onclick="window.print()" class="btn btn-outline-success btn-lg">
                                            <i class="fas fa-print"></i> Print Receipt
                                        </button>
                                    @else
                                        <a href="{{ route('Index.Page') }}" class="btn btn-primary btn-lg me-3">
                                            <i class="fas fa-home"></i> Go to Home
                                        </a>
                                        @if(isset($transaction) && $transaction->booked_hall_id)
                                            <a href="{{ route('payment.initiate', $transaction->booked_hall_id) }}" class="btn btn-warning btn-lg">
                                                <i class="fas fa-redo"></i> Try Again
                                            </a>
                                        @endif
                                    @endif
                                </div>

                                @if($status === 'success')
                                    <div class="mt-4 text-muted">
                                        <small>
                                            <i class="fas fa-info-circle"></i> 
                                            A confirmation message has been sent to your registered mobile number and email.
                                        </small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <!-- content close -->

    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            .card {
                border: none !important;
                box-shadow: none !important;
            }
        }
    </style>
@endsection