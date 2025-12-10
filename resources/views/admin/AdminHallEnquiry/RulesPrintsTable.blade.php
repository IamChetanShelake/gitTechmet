@extends('admin.layout.masteradmin')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<div class="col-12">
    <div class="card my-4">
        <!-- Card Header -->
        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
            <div class="shadow-dark border-radius-lg pt-4 pb-3" style="background-color: #70533A;">
                <h6 class="text-white text-capitalize ps-3">Rules & Regulations Print Management</h6>
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

                // Filter functionality for card listing
                function filterCards() {
                    var hallFilter = $('#hallFilter').val().toLowerCase();
                    var customerSearch = $('#customerSearch').val().toLowerCase();

                    $('.col-12.mb-3').each(function() {
                        var card = $(this);
                        var customerName = card.find('h6.mb-1').text().toLowerCase();
                        var hallName = card.find('.col-md-3 .text-sm').text().toLowerCase();

                        var showCard = true;

                        // Apply hall filter
                        if (hallFilter && hallName.indexOf(hallFilter) === -1) {
                            showCard = false;
                        }

                        // Apply customer search
                        if (customerSearch && customerName.indexOf(customerSearch) === -1) {
                            showCard = false;
                        }

                        if (showCard) {
                            card.show();
                        } else {
                            card.hide();
                        }
                    });

                    // Update serial numbers for visible cards
                    updateSerialNumbers();
                }

                function updateSerialNumbers() {
                    var visibleIndex = 1;
                    $('.col-12.mb-3:visible .bg-primary').each(function() {
                        $(this).text(visibleIndex);
                        visibleIndex++;
                    });
                }

                // Event listeners for filters
                $('#hallFilter').on('change', filterCards);
                $('#customerSearch').on('keyup', filterCards);

                // Clear filters functionality
                $('#clearFilters').on('click', function() {
                    $('#hallFilter').val('');
                    $('#customerSearch').val('');
                    $('.col-12.mb-3').show();
                    updateSerialNumbers();
                });
            });
        </script>

        <!-- Filter Section -->
        <div class="card-body px-3 pt-3 pb-0">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="hallFilter" class="form-label text-sm font-weight-bold">Filter by Hall Name</label>
                    <select id="hallFilter" class="form-select">
                        <option value="">All Halls</option>
                        @foreach($groupedEnquiries->unique('group_halls') as $enquiry)
                            <option value="{{ $enquiry->group_halls }}">{{ $enquiry->group_halls }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="customerSearch" class="form-label text-sm font-weight-bold">Search by Customer Name</label>
                    <input type="text" id="customerSearch" class="form-control" placeholder="Enter customer name...">
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-12">
                    <button id="clearFilters" class="btn btn-outline-secondary btn-sm">Clear All Filters</button>
                </div>
            </div>
        </div>

        <!-- Listing Section -->
        <div class="card-body px-3 pb-2 pt-0">
            <div class="row">
                @foreach($groupedEnquiries as $index => $enquiry)
                    <div class="col-12 mb-3">
                        <div class="card border shadow-sm">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <!-- Serial Number -->
                                    <div class="col-auto">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-weight: bold;">
                                            {{ $index + 1 }}
                                        </div>
                                    </div>

                                    <!-- Customer Details -->
                                    <div class="col-md-3">
                                        <h6 class="mb-1 text-dark font-weight-bold">{{ $enquiry->name }}</h6>
                                        @if($enquiry->organization)
                                            <small class="text-muted">{{ $enquiry->organization }}</small>
                                        @endif
                                        <div class="mt-1">
                                            <small class="text-muted">
                                                <i class="fa fa-phone"></i> {{ $enquiry->contact_no }}
                                                @if($enquiry->email)
                                                    <br><i class="fa fa-envelope"></i> {{ $enquiry->email }}
                                                @endif
                                            </small>
                                        </div>
                                    </div>

                                    <!-- Hall Details -->
                                    <div class="col-md-3">
                                        <div class="d-flex align-items-center">
                                            <i class="material-symbols-rounded text-primary me-2">meeting_room</i>
                                            @if($enquiry->is_group)
                                                <div>
                                                    <h6 class="mb-0 text-sm">{{ $enquiry->group_halls }}</h6>
                                                    <small class="text-muted">{{ $enquiry->group_count }} halls</small>
                                                </div>
                                            @else
                                                <h6 class="mb-0 text-sm">{{ $enquiry->group_halls }}</h6>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Event Date -->
                                    <div class="col-md-2">
                                        <div class="d-flex align-items-center">
                                            <i class="material-symbols-rounded text-success me-2">event</i>
                                            @php
                                                $dates = $enquiry->event_dates ? json_decode($enquiry->event_dates, true) : [$enquiry->event_date];
                                                $dates = array_filter($dates);
                                                sort($dates);
                                            @endphp
                                            <div>
                                                <small class="text-dark font-weight-bold">
                                                    @if(count($dates) > 1)
                                                        {{ $dates[0] }} to {{ end($dates) }}
                                                    @else
                                                        {{ $dates[0] ?? 'N/A' }}
                                                    @endif
                                                </small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Generated Date -->
                                    <div class="col-md-2">
                                        <div class="d-flex align-items-center">
                                            <i class="material-symbols-rounded text-info me-2">schedule</i>
                                            <small class="text-muted">
                                                {{ $enquiry->updated_at ? $enquiry->updated_at->format('d-m-Y') : $enquiry->created_at->format('d-m-Y') }}
                                                <br>
                                                {{ $enquiry->updated_at ? $enquiry->updated_at->format('H:i') : $enquiry->created_at->format('H:i') }}
                                            </small>
                                        </div>
                                    </div>

                                    <!-- Action Button -->
                                    <div class="col-md-2 text-end">
                                        @if($enquiry->rules_print_file)
                                            <a href="{{ asset($enquiry->rules_print_file) }}" target="_blank" class="btn btn-primary btn-sm">
                                                <i class="fa fa-eye"></i> View Rules Print
                                            </a>
                                        @else
                                            <span class="badge bg-warning text-dark">PDF Not Generated</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                @if($groupedEnquiries->isEmpty())
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="material-symbols-rounded text-muted" style="font-size: 48px;">description</i>
                            <h5 class="text-muted mt-3">No Rules & Regulations Prints Found</h5>
                            <p class="text-muted">Rules prints will appear here once customers submit enquiries.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
