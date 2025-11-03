@extends('admin.layout.masteradmin')

@section('content')
<div class="col-12">
    <div class="card my-4">
        <!-- Card Header -->
        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
            <div class="shadow-dark border-radius-lg pt-4 pb-3" style="background-color: #dc3545;">
                <h6 class="text-white text-capitalize ps-3">Cancelled Bookings</h6>
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

        <!-- Back Button -->
        <div class="d-flex justify-content-end p-3">
            <a class="btn btn-outline-primary btn-lg px-4 py-2" href="{{ route('Booked.Halls') }}">
                <i class="fas fa-arrow-left me-2"></i>Back to Booked Halls
            </a>
        </div>

        <!-- Table Section -->
        <div class="card-body px-0 pb-2">
            @if($cancelledRecords->count() > 0)
                <div class="table-responsive p-0">
                    <table class="table align-items-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Sr No.</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Type</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Customer Name</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Hall Name</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Event Date</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Cancelled Date</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Payment Status</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cancelledRecords as $index => $record)
                                <tr>
                                    <!-- Sr No. -->
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <h6 class="mb-0 text-sm">{{ $cancelledRecords->count() - $index }}</h6>
                                        </div>
                                    </td>

                                    <!-- Type -->
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            @if($record->record_type == 'Booking')
                                                <span class="badge bg-primary">Booking</span>
                                            @else
                                                <span class="badge bg-info">Enquiry</span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Customer Name -->
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <h6 class="mb-0 text-sm">{{ $record->name ?? $record->customer_name }}</h6>
                                        </div>
                                    </td>

                                    <!-- Hall Name -->
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <h6 class="mb-0 text-sm">{{ $record->hall ?? $record->hall_name }}</h6>
                                        </div>
                                    </td>

                                    <!-- Event Date -->
                                    <td class="align-middle text-center">
                                        <div class="d-flex px-2 py-1 justify-content-center">
                                            <h6 class="mb-0 text-sm">{{ $record->event_date ? \Carbon\Carbon::parse($record->event_date)->format('d M Y') : 'N/A' }}</h6>
                                        </div>
                                    </td>

                                    <!-- Cancelled Date -->
                                    <td class="align-middle text-center">
                                        <div class="d-flex px-2 py-1 justify-content-center">
                                            <h6 class="mb-0 text-sm">{{ $record->cancelled_at ? \Carbon\Carbon::parse($record->cancelled_at)->format('d M Y') : 'N/A' }}</h6>
                                            <small class="text-muted">{{ $record->cancelled_at ? \Carbon\Carbon::parse($record->cancelled_at)->format('h:i A') : '' }}</small>
                                        </div>
                                    </td>

                                    <!-- Payment Status -->
                                    <td class="align-middle text-center">
                                        @if($record->payment_status == 'SUCCESS')
                                            <span class="badge bg-success px-3 py-2">
                                                <i class="fas fa-check-circle me-1"></i>
                                                Paid (₹{{ number_format($record->payment_amount ?? 0, 2) }})
                                            </span>
                                            @if($record->payment_date)
                                                <br><small class="text-muted">{{ $record->payment_date->format('d M Y') }}</small>
                                            @endif
                                        @elseif($record->payment_status == 'FAILED')
                                            <span class="badge bg-danger px-3 py-2">
                                                <i class="fas fa-times-circle me-1"></i>
                                                Failed
                                            </span>
                                        @elseif($record->payment_status == 'PENDING' || $record->payment_status == 'initiated')
                                            <span class="badge bg-warning px-3 py-2">
                                                <i class="fas fa-clock me-1"></i>
                                                Pending
                                            </span>
                                        @else
                                            <span class="badge bg-secondary px-3 py-2">
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                {{ $record->payment_status }}
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Actions -->
                                    <td class="align-middle text-center">
                                        @if($record->record_type == 'Booking')
                                            <a href="{{ route('View.Booking', $record->id) }}" class="btn btn-info btn-sm" title="View Details">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        @else
                                            <a href="{{ route('Admin.ViewHallEnquiry', $record->id) }}" class="btn btn-info btn-sm" title="View Details">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No Cancelled Bookings Found</h4>
                    <p class="text-muted">There are currently no cancelled bookings to display.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
