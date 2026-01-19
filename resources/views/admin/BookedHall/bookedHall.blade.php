@extends('admin.layout.masteradmin')

@section('content')
<div class="col-12">
    <div class="card my-4">
        <!-- Card Header -->
        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
            <div class="shadow-dark border-radius-lg pt-4 pb-3" style="background-color: #70533A;">
                <h6 class="text-white text-capitalize ps-3">Booked Hall Details</h6>
            </div>
        </div>

        <!-- Success / Error Messages -->
        @if(session('success'))
            <div class="alert alert-success" id="successMessage">
                {{ session('success') }}
            </div>
        @endif

        @if(session('fail'))
            <div class="alert alert-danger" id="failMessage">
                {{ session('fail') }}
            </div>
        @endif

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>
            $(document).ready(function() {
                setTimeout(function() {
                    $("#successMessage, #failMessage").fadeOut('slow');
                }, 3000); // Hide messages after 3 seconds

                // Export functionality
                $('#exportBtn').on('click', function() {
                    var dateFrom = $('#dateFrom').val();
                    var dateTo = $('#dateTo').val();

                    if (dateFrom && dateTo) {
                        var url = "{{ route('admin.booked-halls.export', 'custom') }}?date_from=" + dateFrom + "&date_to=" + dateTo;
                        window.location.href = url;
                    } else {
                        window.location.href = "{{ route('admin.booked-halls.export', 'all') }}";
                    }
                });

                // Clear filters functionality
                $('#clearFilters').on('click', function() {
                    $('#dateFrom').val('');
                    $('#dateTo').val('');
                });
            });
        </script>

        <!-- JavaScript for handling cancel booking -->
        <script>
            function cancelBooking(bookedHallId) {
                if (confirm('Are you sure you want to cancel this booking? This action cannot be undone.')) {
                    // Send AJAX request to cancel the booking
                    fetch(`/admin/cancel-booking/${bookedHallId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Booking cancelled successfully.');
                            location.reload(); // Reload the page to reflect changes
                        } else {
                            alert('Failed to cancel booking: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while cancelling the booking.');
                    });
                }
            }

            function cancelGroupBooking(groupCode) {
                if (confirm('Are you sure you want to cancel all bookings in this group? This action cannot be undone.')) {
                    // Send AJAX request to cancel the group booking
                    fetch(`/admin/cancel-group-booking/${groupCode}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Group booking cancelled successfully.');
                            location.reload(); // Reload the page to reflect changes
                        } else {
                            alert('Failed to cancel group booking: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while cancelling the group booking.');
                    });
                }
            }
        </script>

        <!-- Filter Section -->
        <div class="card-body px-3 pt-3 pb-0">
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label text-sm font-weight-bold">Date From</label>
                    <input type="date" id="dateFrom" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label text-sm font-weight-bold">Date To</label>
                    <input type="date" id="dateTo" class="form-control">
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <button id="clearFilters" class="btn btn-outline-secondary btn-sm me-2">Clear Filters</button>
                    <button id="exportBtn" type="button" class="btn btn-success btn-lg px-4 py-2">
                        <i class="fa fa-download"></i> Export Report
                    </button>
                </div>
            </div>
        </div>

        <!-- Table Section -->
        <div class="card-body px-0 pb-2">
            <div class="table-responsive p-0">
                <table class="table align-items-center mb-0">
                    <thead>
                        <tr>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Sr No.</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Customer Name</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Hall Name</th>
                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">View All Details</th>
                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Vendors</th>
                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Payment Status</th>
                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Remaining Amount</th>
                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Total Amount </br>(Rent + Deposit)</th>
                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            // Group booked halls by group_code for multi-hall bookings
                            $groupedBookings = collect();
                            $processedGroups = [];

                            foreach($bookedHalls as $bookedHall) {
                                if ($bookedHall->group_code && !in_array($bookedHall->group_code, $processedGroups)) {
                                    // This is the first booking we encounter for this group
                                    $groupBookings = $bookedHalls->where('group_code', $bookedHall->group_code);
                                    $groupedBookings->push([
                                        'is_group' => true,
                                        'group_code' => $bookedHall->group_code,
                                        'bookings' => $groupBookings,
                                        'representative' => $groupBookings->first()
                                    ]);
                                    $processedGroups[] = $bookedHall->group_code;
                                } elseif (!$bookedHall->group_code) {
                                    // Single hall booking
                                    $groupedBookings->push([
                                        'is_group' => false,
                                        'group_code' => null,
                                        'bookings' => collect([$bookedHall]),
                                        'representative' => $bookedHall
                                    ]);
                                }
                            }
                        @endphp

                        @foreach($groupedBookings as $index => $bookingGroup)
                            @php
                                $isMultiHall = $bookingGroup['is_group'];
                                $groupBookings = $bookingGroup['bookings'];
                                $representativeBooking = $bookingGroup['representative'];

                                // Total amount is the sum of committed amounts (paid_amount from booked_halls)
                                $totalAmount = $groupBookings->sum('paid_amount');

                                // Calculate remaining amount based on actual payments made
                                $totalPaid = 0;
                                foreach ($groupBookings as $bookedHall) {
                                    $paidAmount = \App\Models\PaymentTransaction::where('booked_hall_id', $bookedHall->id)
                                        ->where('status', 'SUCCESS')
                                        ->sum('amount');
                                    $totalPaid += $paidAmount;
                                }
                                $remainingAmount = max(0, $totalAmount - $totalPaid);

                                // Determine overall payment status based on payment amounts
                                $hasFailedPayment = $groupBookings->contains(function($booking) {
                                    return $booking->payment_status == 'FAILED';
                                });
                                $hasPendingPayment = $groupBookings->contains(function($booking) {
                                    return in_array($booking->payment_status, ['PENDING', 'initiated']);
                                });

                                if ($totalPaid >= $totalAmount) {
                                    $overallPaymentStatus = 'Fully Paid';
                                    $paymentDate = $groupBookings->where('payment_status', 'SUCCESS')->first()->payment_date ?? null;
                                } elseif ($totalPaid > 0) {
                                    $overallPaymentStatus = 'Partially Paid';
                                    $paymentDate = null;
                                } elseif ($hasPendingPayment) {
                                    $overallPaymentStatus = 'Pending';
                                    $paymentDate = null;
                                } elseif ($hasFailedPayment) {
                                    $overallPaymentStatus = 'Failed';
                                    $paymentDate = null;
                                } else {
                                    $overallPaymentStatus = 'Not Initiated';
                                    $paymentDate = null;
                                }
                            @endphp
                            <tr>
                                <!-- Sr No. -->
                                <td>
                                    <div class="d-flex px-2 py-1">
                                        <h6 class="mb-0 text-sm">{{ $index + 1 }}</h6>
                                    </div>
                                </td>

                                <!-- Customer Name -->
                                <td>
                                    <div class="d-flex px-2 py-1">
                                        <h6 class="mb-0 text-sm">{{ $representativeBooking->customer_name }}</h6>
                                    </div>
                                </td>

                                <!-- Hall Name -->
                                <td style="max-width: 260px;">
                                    <div class="d-flex px-2 py-1">
                                        @if($isMultiHall)
                                            <div title="{{ $groupBookings->pluck('hall_name')->join(', ') }}">
                                                <h6 class="mb-1 text-sm text-truncate" style="max-width: 180px;">
                                                    {{ $groupBookings->first()->hall_name }}
                                                    @if($groupBookings->count() > 1)
                                                        <small class="text-muted">+{{ $groupBookings->count() - 1 }} more</small>
                                                    @endif
                                                </h6>
                                                <small class="text-muted">{{ $groupBookings->count() }} halls</small>
                                            </div>
                                        @else
                                            <h6 class="mb-0 text-sm">{{ $representativeBooking->hall_name }}</h6>
                                        @endif
                                    </div>
                                </td>

                                <!-- View Details -->
                                <td class="align-middle text-center">
                                    @if($isMultiHall)
                                        <!-- Show first booking's view link for multi-hall -->
                                        <a href="{{ route('View.Booking', $representativeBooking->id) }}" class="btn btn-secondary">
                                            View Group
                                        </a>
                                    @else
                                        <a href="{{ route('View.Booking', $representativeBooking->id) }}" class="btn btn-secondary">
                                            View
                                        </a>
                                    @endif
                                </td>

                                <!-- Vendors -->
                                <td class="align-middle text-center">
                                    @php
                                        $hasConfirmedService = false;
                                        $hasApprovedService = false;

                                        foreach($groupBookings as $booking) {
                                            $confirmed = $eventServices->where('booked_hall_id', $booking->id)->where('status', 'confirmed')->isNotEmpty() ||
                                                        $cateringServices->where('booked_hall_id', $booking->id)->where('status', 'confirmed')->isNotEmpty();

                                            $approved = $eventServices->where('booked_hall_id', $booking->id)->where('status', 'approved')->isNotEmpty() ||
                                                       $cateringServices->where('booked_hall_id', $booking->id)->where('status', 'approved')->isNotEmpty();

                                            if ($confirmed) $hasConfirmedService = true;
                                            if ($approved) $hasApprovedService = true;
                                        }
                                    @endphp

                                    @if($hasConfirmedService)
                                        <a href="{{ route('View.EventCatering', $representativeBooking->id) }}" class="btn btn-info">
                                            View Event / Catering
                                        </a>
                                    @elseif ($hasApprovedService)
                                        <p>
                                           <span class="badge bg-success"> Approved by </br> admin</span>
                                        </p>
                                    @else
                                        <p>
                                            <span class="badge bg-secondary">Not Confirm </br> yet</span>
                                         </p>
                                    @endif
                                </td>

                                <!-- Payment Status -->
                                <td class="align-middle text-center">
                                    @if($overallPaymentStatus == 'Fully Paid')
                                        <span class="badge bg-success px-3 py-2">
                                            <i class="fas fa-check-circle me-1"></i>
                                            Fully Paid
                                        </span>
                                        @if($paymentDate)
                                            <br><small class="text-muted">{{ $paymentDate->format('d M Y') }}</small>
                                        @endif
                                    @elseif($overallPaymentStatus == 'Partially Paid')
                                        <span class="badge bg-warning px-3 py-2">
                                            <i class="fas fa-clock me-1"></i>
                                            Partially Paid
                                        </span>
                                    @elseif($overallPaymentStatus == 'Pending')
                                        <span class="badge bg-warning px-3 py-2">
                                            <i class="fas fa-clock me-1"></i>
                                            Pending
                                        </span>
                                    @elseif($overallPaymentStatus == 'Failed')
                                        <span class="badge bg-danger px-3 py-2">
                                            <i class="fas fa-times-circle me-1"></i>
                                            Failed
                                        </span>
                                    @else
                                        <span class="badge bg-secondary px-3 py-2">
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            Not Initiated
                                        </span>
                                    @endif
                                </td>

                                <!-- Remaining Amount -->
                                <td class="align-middle text-center">
                                    <div class="d-flex px-2 py-1 justify-content-center">
                                        <h6 class="mb-0 text-sm">₹{{ number_format($remainingAmount, 2) }}</h6>
                                    </div>
                                </td>

                                <!-- Total Amount -->
                                <td class="align-middle text-center">
                                    <div class="d-flex px-2 py-1 justify-content-center">
                                        <h6 class="mb-0 text-sm">₹{{ number_format($totalAmount, 2) }}</h6>
                                    </div>
                                </td>

                                <!-- Status -->
                                <td class="align-middle text-center">
                                    @if($isMultiHall)
                                        <!-- For multi-hall bookings, show cancel option for the group -->
                                        <button type="button" class="btn btn-danger btn-sm" onclick="cancelGroupBooking('{{ $bookingGroup['group_code'] }}')">Cancel Group</button>
                                    @else
                                        <button type="button" class="btn btn-danger btn-sm" onclick="cancelBooking({{ $representativeBooking->id }})">Cancel Booking</button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
