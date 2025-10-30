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
        </script>

        <!-- Add Button -->
        <div class="d-flex justify-content-end p-3">
            {{-- <a class="btn btn-outline-primary btn-lg px-4 py-2" href="{{ route('admin.hall-enquiry.create') }}">Add</a> --}}
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
                        @foreach($bookedHalls as $index => $bookedHall)
                            @php
                                // Calculate total amount first (Hall + Accessories + Deposit)
                                $totalAmount = ($bookedHall->total_rent ?? 0) + ($bookedHall->total_deposit ?? 0);
                                if ($bookedHall->start_time && $bookedHall->end_time) {
                                    $startTime = \Carbon\Carbon::createFromFormat('H:i:s', $bookedHall->start_time . ':00');
                                    $endTime = \Carbon\Carbon::createFromFormat('H:i:s', $bookedHall->end_time . ':00');
                                    $totalHours = $startTime->diffInHours($endTime, false);

                                    // Get accessories if any from enquiry
                                    $accessoriesAmount = 0;
                                    if ($bookedHall->enquiry && $bookedHall->enquiry->accessorie) {
                                        $accessoryIds = json_decode($bookedHall->enquiry->accessorie, true);
                                        if (is_array($accessoryIds)) {
                                            $accessories = \App\Models\Accessorie::whereIn('id', $accessoryIds)->get();
                                            // Calculate total accessories price based on blocks of hours
                                            $accessoriesAmount = $accessories->sum(function ($accessory) use ($totalHours) {
                                                $price = (float) ($accessory->price ?? 0);
                                                $hours = (float) ($accessory->hours ?? 1);
                                                if ($price <= 0 || $hours <= 0) return 0;
                                                $blocks = floor($totalHours / $hours);
                                                return $price * max($blocks, 1); // Minimum 1 block
                                            });
                                        }
                                    }

                                    $totalAmount = ($bookedHall->total_rent ?? 0) + $accessoriesAmount + ($bookedHall->total_deposit ?? 0);
                                }

                                // Calculate remaining amount
                                $paidAmount = \App\Models\PaymentTransaction::where('booked_hall_id', $bookedHall->id)
                                    ->where('status', 'SUCCESS')
                                    ->sum('amount');
                                $remainingAmount = $totalAmount - $paidAmount;
                                $remainingAmount = max(0, $remainingAmount);
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
                                        <h6 class="mb-0 text-sm">{{ $bookedHall->customer_name }}</h6>
                                    </div>
                                </td>

                                <!-- Hall Name -->
                                <td>
                                    <div class="d-flex px-2 py-1">
                                        <h6 class="mb-0 text-sm">{{ $bookedHall->hall_name }}</h6>
                                    </div>
                                </td>

                                <!-- View Details -->
                                <td class="align-middle text-center">
                                    <a href="{{ route('View.Booking', $bookedHall->id) }}" class="btn btn-secondary">
                                        View
                                    </a>
                                </td>

                                <!-- Vendors -->
                                <td class="align-middle text-center">
                                    @php
                                        $hasConfirmedService = $eventServices->where('booked_hall_id', $bookedHall->id)->where('status', 'confirmed')->isNotEmpty() ||
                                                               $cateringServices->where('booked_hall_id', $bookedHall->id)->where('status', 'confirmed')->isNotEmpty();

                                        $hasApprovedService = $eventServices->where('booked_hall_id', $bookedHall->id)->where('status', 'approved')->isNotEmpty() ||
                                                               $cateringServices->where('booked_hall_id', $bookedHall->id)->where('status', 'approved')->isNotEmpty();
                                    @endphp

                                    @if($hasConfirmedService)
                                        <a href="{{ route('View.EventCatering', $bookedHall->id) }}" class="btn btn-info">
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
                                    @if($bookedHall->payment_status == 'SUCCESS')
                                        <span class="badge bg-success px-3 py-2">
                                            <i class="fas fa-check-circle me-1"></i>
                                            Paid
                                        </span>
                                        @if($bookedHall->payment_date)
                                            <br><small class="text-muted">{{ $bookedHall->payment_date->format('d M Y') }}</small>
                                        @endif
                                    @elseif($bookedHall->payment_status == 'FAILED')
                                        <span class="badge bg-danger px-3 py-2">
                                            <i class="fas fa-times-circle me-1"></i>
                                            Failed
                                        </span>
                                    @elseif($bookedHall->payment_status == 'PENDING' || $bookedHall->payment_status == 'initiated')
                                        <span class="badge bg-warning px-3 py-2">
                                            <i class="fas fa-clock me-1"></i>
                                            Pending
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
                                    <button type="button" class="btn btn-danger btn-sm" onclick="cancelBooking({{ $bookedHall->id }})">Cancel Booking</button>
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
