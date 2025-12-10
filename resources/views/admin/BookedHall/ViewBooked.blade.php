@extends('admin.layout.masteradmin')

@section('content')
<div class="col-12">
    <div class="card my-4">
        <!-- Card Header -->
        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
            <div class="shadow-dark border-radius-lg pt-4 pb-3" style="background-color: #70533A;">
                <h6 class="text-white text-capitalize ps-3">
                    @if($bookedHall->group_code)
                        Multi-Hall Booking Details
                    @else
                        Booked Hall Details
                    @endif
                </h6>
            </div>
        </div>

        <!-- Card Body -->
        <div class="card-body">
            @php
                // Check if this is a multi-hall booking
                $groupedBookings = collect();
                if ($bookedHall->group_code) {
                    $groupedBookings = \App\Models\BookedHall::where('group_code', $bookedHall->group_code)->get();
                } else {
                    $groupedBookings = collect([$bookedHall]);
                }
                $isMultiHall = $groupedBookings->count() > 1;
            @endphp

            <!-- Customer Information (Common for all halls) -->
            <h5 class="mb-3">Customer Information</h5>
            <table class="table table-bordered mb-4">
                <tbody>
                    <tr>
                        <th>Customer Name</th>
                        <td>{{ $bookedHall->customer_name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Customer Phone</th>
                        <td>{{ $bookedHall->customer_phone ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Customer Email</th>
                        <td>{{ $bookedHall->customer_email ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Event Type</th>
                        <td>{{ $bookedHall->event_type ?? 'N/A' }}</td>
                    </tr>
                    @if($isMultiHall)
                    <tr>
                        <th>Booking Code</th>
                        <td>{{ $bookedHall->booking_code ?? 'N/A' }} (Group Booking)</td>
                    </tr>
                    @endif
                </tbody>
            </table>

            <!-- Hall Details -->
            <h5 class="mb-3">
                @if($isMultiHall)
                    Hall Details ({{ $groupedBookings->count() }} Halls)
                @else
                    Hall Details
                @endif
            </h5>

            @foreach($groupedBookings as $index => $booking)
                @if($isMultiHall)
                    <h6 class="mt-3 mb-2">Hall {{ $index + 1 }}: {{ $booking->hall_name }}</h6>
                @endif

                <table class="table table-bordered @if($isMultiHall) mb-3 @endif">
                    <tbody>
                        <tr>
                            <th>Hall Name</th>
                            <td>{{ $booking->hall_name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Event Date</th>
                            <td>{{ $booking->event_date ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Event Time</th>
                            <td>{{ $booking->event_time ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Duration</th>
                            <td>{{ $booking->duration ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Start Time</th>
                            <td>{{ $booking->start_time ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>End Time</th>
                            <td>{{ $booking->end_time ?? 'N/A' }}</td>
                        </tr>
                        @if(!$isMultiHall)
                        <tr>
                            <th>Booking Code</th>
                            <td>{{ $booking->booking_code ?? 'N/A' }}</td>
                        </tr>
                        @endif

                        <!-- Financial Details -->
                        @php
                            // Use the stored paid_amount as total committed amount
                            $totalAmount = $booking->paid_amount ?? 0;

                            // Calculate accessories amount for display purposes
                            $accessoriesAmount = 0;
                            if ($booking->start_time && $booking->end_time) {
                                $startTime = \Carbon\Carbon::createFromFormat('H:i:s', $booking->start_time . ':00');
                                $endTime = \Carbon\Carbon::createFromFormat('H:i:s', $booking->end_time . ':00');
                                $totalHours = $startTime->diffInHours($endTime, false);

                                // Get number of days from enquiry (same as quotation generation)
                                $dates = [];
                                if ($booking->enquiry) {
                                    $dates = $booking->enquiry->event_dates ? json_decode($booking->enquiry->event_dates, true) : [$booking->enquiry->event_date];
                                    $dates = array_filter($dates);
                                }
                                $numberOfDays = count($dates);

                                if ($booking->enquiry && $booking->enquiry->accessorie) {
                                    $accessoryIds = json_decode($booking->enquiry->accessorie, true);
                                    if (is_array($accessoryIds)) {
                                        $accessories = \App\Models\Accessorie::whereIn('id', $accessoryIds)->get();
                                        $accessoriesAmount = $accessories->sum(function ($accessory) use ($totalHours, $numberOfDays) {
                                            $price = (float) ($accessory->price ?? 0);
                                            $hours = (float) ($accessory->hours ?? 1);
                                            if ($price <= 0 || $hours <= 0) return 0;

                                            // Calculate blocks per day
                                            $blocksPerDay = floor($totalHours / $hours);
                                            $pricePerDay = $price * max($blocksPerDay, 1); // Minimum 1 block per day

                                            // Multiply by number of days (same as quotation)
                                            return $pricePerDay * $numberOfDays;
                                        });
                                    }
                                }
                            }
                        @endphp
                        <tr>
                            <th>Total Rent</th>
                            <td>₹{{ number_format($booking->total_rent ?? 0, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Total Deposit</th>
                            <td>₹{{ number_format($booking->total_deposit ?? 0, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Accessories Amount</th>
                            <td>₹{{ number_format($accessoriesAmount, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Total Amount</th>
                            <td>₹{{ number_format($totalAmount, 2) }}</td>
                        </tr>

                        <!-- Payment Information -->
                        @php
                            $paidAmount = \App\Models\PaymentTransaction::where('booked_hall_id', $booking->id)
                                ->where('status', 'SUCCESS')
                                ->sum('amount');
                            $remainingAmount = $totalAmount - $paidAmount;
                        @endphp
                        <tr>
                            <th>Paid Amount</th>
                            <td>₹{{ number_format($paidAmount, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Remaining Amount</th>
                            <td>₹{{ number_format(max(0, $remainingAmount), 2) }}</td>
                        </tr>

                        <!-- Payment Status -->
                        @php
                            $latestTransaction = \App\Models\PaymentTransaction::where('booked_hall_id', $booking->id)
                                ->orderBy('created_at', 'desc')
                                ->first();
                        @endphp
                        <tr>
                            <th>Payment Status</th>
                            <td>
                                @if($latestTransaction)
                                    @if($latestTransaction->status == 'SUCCESS')
                                        <span class="badge bg-success">Paid</span>
                                        @if($latestTransaction->payment_date)
                                            <br><small class="text-muted">{{ $latestTransaction->payment_date->format('d M Y') }}</small>
                                        @endif
                                    @elseif($latestTransaction->status == 'FAILED')
                                        <span class="badge bg-danger">Failed</span>
                                    @elseif($latestTransaction->status == 'PENDING' || $latestTransaction->status == 'initiated')
                                        <span class="badge bg-warning">Pending</span>
                                    @else
                                        <span class="badge bg-secondary">Not Initiated</span>
                                    @endif
                                @else
                                    <span class="badge bg-secondary">Not Initiated</span>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            @endforeach

            <!-- Summary for Multi-Hall Bookings -->
            @if($isMultiHall)
                <h5 class="mb-3">Booking Summary</h5>
                <table class="table table-bordered">
                    <tbody>
                        @php
                            $totalRent = $groupedBookings->sum('total_rent');
                            $totalDeposit = $groupedBookings->sum('total_deposit');
                            $totalAccessories = 0;
                            $totalPaid = 0;

                            foreach($groupedBookings as $booking) {
                                // Calculate accessories for this booking
                                $bookingAccessories = 0;
                                if ($booking->start_time && $booking->end_time) {
                                    $startTime = \Carbon\Carbon::createFromFormat('H:i:s', $booking->start_time . ':00');
                                    $endTime = \Carbon\Carbon::createFromFormat('H:i:s', $booking->end_time . ':00');
                                    $totalHours = $startTime->diffInHours($endTime, false);

                                    // Get number of days from enquiry (same as quotation generation)
                                    $dates = [];
                                    if ($booking->enquiry) {
                                        $dates = $booking->enquiry->event_dates ? json_decode($booking->enquiry->event_dates, true) : [$booking->enquiry->event_date];
                                        $dates = array_filter($dates);
                                    }
                                    $numberOfDays = count($dates);

                                    if ($booking->enquiry && $booking->enquiry->accessorie) {
                                        $accessoryIds = json_decode($booking->enquiry->accessorie, true);
                                        if (is_array($accessoryIds)) {
                                            $accessories = \App\Models\Accessorie::whereIn('id', $accessoryIds)->get();
                                            $bookingAccessories = $accessories->sum(function ($accessory) use ($totalHours, $numberOfDays) {
                                                $price = (float) ($accessory->price ?? 0);
                                                $hours = (float) ($accessory->hours ?? 1);
                                                if ($price <= 0 || $hours <= 0) return 0;

                                                // Calculate blocks per day
                                                $blocksPerDay = floor($totalHours / $hours);
                                                $pricePerDay = $price * max($blocksPerDay, 1); // Minimum 1 block per day

                                                // Multiply by number of days (same as quotation)
                                                return $pricePerDay * $numberOfDays;
                                            });
                                        }
                                    }
                                }
                                $totalAccessories += $bookingAccessories;

                                // Calculate paid amount
                                $paid = \App\Models\PaymentTransaction::where('booked_hall_id', $booking->id)
                                    ->where('status', 'SUCCESS')
                                    ->sum('amount');
                                $totalPaid += $paid;
                            }

                            // Use the sum of paid_amount from all halls as the grand total
                            $grandTotal = $groupedBookings->sum('paid_amount');
                            $totalRemaining = $grandTotal - $totalPaid;
                        @endphp
                        <tr>
                            <th>Total Halls</th>
                            <td>{{ $groupedBookings->count() }}</td>
                        </tr>
                        <tr>
                            <th>Total Rent (All Halls)</th>
                            <td>₹{{ number_format($totalRent, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Total Deposit (All Halls)</th>
                            <td>₹{{ number_format($totalDeposit, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Total Accessories (All Halls)</th>
                            <td>₹{{ number_format($totalAccessories, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Grand Total</th>
                            <td>₹{{ number_format($grandTotal, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Total Paid</th>
                            <td>₹{{ number_format($totalPaid, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Total Remaining</th>
                            <td>₹{{ number_format(max(0, $totalRemaining), 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            @endif

            <!-- Payment History -->
            <div class="mt-4">
                <h5 class="mb-3">Payment History</h5>
                @php
                    // Get all payment transactions for this booking (all halls in group)
                    $allTransactions = collect();
                    foreach($groupedBookings as $booking) {
                        $transactions = \App\Models\PaymentTransaction::where('booked_hall_id', $booking->id)
                            ->where('status', 'SUCCESS')
                            ->orderBy('payment_date', 'desc')
                            ->get();
                        $allTransactions = $allTransactions->merge($transactions);
                    }
                    $allTransactions = $allTransactions->sortByDesc('payment_date');
                @endphp

                @if($allTransactions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Transaction ID</th>
                                    <th>Payment Type</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($allTransactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->payment_date ? $transaction->payment_date->format('d M Y H:i') : 'N/A' }}</td>
                                    <td>
                                        @if($transaction->transaction_type == 'CASH' || $transaction->transaction_type == 'CHECK')
                                            <span class="badge bg-info">{{ $transaction->merchant_txn_no }}</span>
                                        @else
                                            <code class="small">{{ $transaction->merchant_txn_no }}</code>
                                        @endif
                                    </td>
                                    <td>
                                        @if($transaction->transaction_type == 'CASH')
                                            <span class="badge bg-success">Cash</span>
                                        @elseif($transaction->transaction_type == 'CHECK')
                                            <span class="badge bg-warning">Check</span>
                                        @else
                                            <span class="badge bg-primary">Online</span>
                                        @endif
                                    </td>
                                    <td><strong>₹{{ number_format($transaction->amount, 2) }}</strong></td>
                                    <td>
                                        @if($transaction->transaction_type == 'CASH' || $transaction->transaction_type == 'CHECK')
                                            Manual Entry
                                        @else
                                            Gateway Payment
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $responseData = $transaction->full_response;
                                            $notes = '';
                                            if (is_array($responseData) && isset($responseData['notes'])) {
                                                $notes = $responseData['notes'];
                                            } elseif (is_array($responseData) && isset($responseData['recorded_by'])) {
                                                $notes = 'Recorded by: ' . $responseData['recorded_by'];
                                            }
                                        @endphp
                                        {{ $notes ?: '-' }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="3" class="text-end">Total Paid:</th>
                                    <th colspan="3"><strong>₹{{ number_format($allTransactions->sum('amount'), 2) }}</strong></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> No payments recorded yet.
                    </div>
                @endif
            </div>

            <!-- Cash/Check Payment Recording -->
            @if($groupedBookings->sum('paid_amount') > $groupedBookings->sum(function($booking) {
                return \App\Models\PaymentTransaction::where('booked_hall_id', $booking->id)->where('status', 'SUCCESS')->sum('amount');
            }))
            <div class="mt-4">
                <h5 class="mb-3">Record Payment</h5>
                <form action="{{ route('payment.record.cash', $bookedHall->id) }}" method="POST" class="card p-3">
                    @csrf
                    <div class="row">
                        <div class="col-md-3">
                            <label for="payment_type" class="form-label">Payment Type</label>
                            <select name="payment_type" id="payment_type" class="form-select" required>
                                <option value="cash">Cash</option>
                                <option value="check">Check</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="amount" class="form-label">Amount (₹)</label>
                            <input type="number" name="amount" id="amount" class="form-control"
                                   step="0.01" min="0" placeholder="Enter amount"
                                   value="{{ $groupedBookings->sum('paid_amount') - $groupedBookings->sum(function($booking) {
                                       return \App\Models\PaymentTransaction::where('booked_hall_id', $booking->id)->where('status', 'SUCCESS')->sum('amount');
                                   }) }}">
                        </div>
                        <div class="col-md-4">
                            <label for="notes" class="form-label">Notes (Optional)</label>
                            <input type="text" name="notes" id="notes" class="form-control"
                                   placeholder="Payment reference, check number, etc.">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-plus-circle me-1"></i> Record Payment
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            @endif

            <div class="mt-4">
                <a href="{{ route('Booked.Halls') }}" class="btn btn-secondary">Back to Booked Halls</a>
            </div>
        </div>
    </div>
</div>
@endsection
