@extends('admin.layout.masteradmin')

@section('content')
<div class="col-12">
    <div class="card my-4">
        <!-- Card Header -->
        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
            <div class="shadow-dark border-radius-lg pt-4 pb-3" style="background-color: #70533A;">
                <h6 class="text-white text-capitalize ps-3">Hall Enquiry Details</h6>
            </div>
        </div>

        <!-- Success / Error Messages -->
        {{-- @if(session('success'))
            <div class="alert alert-success" id="successMessage">
                {{ session('success') }}
            </div>
        @endif

        @if(session('fail'))
            <div class="alert alert-danger" id="failMessage">
                {{ session('fail') }}
            </div>
        @endif --}}

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>
            $(document).ready(function() {
                setTimeout(function() {
                    $("#successMessage, #failMessage").fadeOut('slow');
                }, 3000); // Hide messages after 3 seconds

                // Filter functionality
                function filterTable() {
                    var hallFilter = $('#hallFilter').val().toLowerCase();
                    var customerSearch = $('#customerSearch').val().toLowerCase();
                    var statusFilter = $('#statusFilter').val().toLowerCase();

                    $('tbody tr').each(function() {
                        var row = $(this);
                        var customerName = row.find('td:nth-child(2) h6').text().toLowerCase();
                        var hallName = row.find('td:nth-child(3) h6').text().toLowerCase();
                        var status = '';

                        // Get status from badge
                        var statusBadge = row.find('td:nth-child(6) .badge');
                        if (statusBadge.hasClass('bg-warning')) {
                            status = 'pending';
                        } else if (statusBadge.hasClass('bg-success')) {
                            status = 'viewed';
                        } else if (statusBadge.hasClass('bg-danger')) {
                            status = 'rejected';
                        }

                        var showRow = true;

                        // Apply hall filter
                        if (hallFilter && hallName.indexOf(hallFilter) === -1) {
                            showRow = false;
                        }

                        // Apply customer search
                        if (customerSearch && customerName.indexOf(customerSearch) === -1) {
                            showRow = false;
                        }

                        // Apply status filter
                        if (statusFilter && status !== statusFilter) {
                            showRow = false;
                        }

                        if (showRow) {
                            row.show();
                        } else {
                            row.hide();
                        }
                    });

                    // Update serial numbers for visible rows
                    updateSerialNumbers();
                }

                function updateSerialNumbers() {
                    var visibleIndex = 1;
                    $('tbody tr:visible').each(function() {
                        $(this).find('td:first-child h6').text(visibleIndex);
                        visibleIndex++;
                    });
                }

                // Event listeners for filters
                $('#hallFilter, #statusFilter').on('change', filterTable);
                $('#customerSearch').on('keyup', filterTable);

                // Clear filters functionality
                $('#clearFilters').on('click', function() {
                    $('#hallFilter').val('');
                    $('#customerSearch').val('');
                    $('#statusFilter').val('');
                    $('tbody tr').show();
                    updateSerialNumbers();
                });
            });
        </script>

        <!-- Filter Section -->
        <div class="card-body px-3 pt-3 pb-0">
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="hallFilter" class="form-label text-sm font-weight-bold">Filter by Hall Name</label>
                    <select id="hallFilter" class="form-select">
                        <option value="">All Halls</option>
                        @foreach($hallenquiries->unique('hall') as $enquiry)
                            <option value="{{ $enquiry->hall }}">{{ $enquiry->hall }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="customerSearch" class="form-label text-sm font-weight-bold">Search by Customer Name</label>
                    <input type="text" id="customerSearch" class="form-control" placeholder="Enter customer name...">
                </div>
                <div class="col-md-4">
                    <label for="statusFilter" class="form-label text-sm font-weight-bold">Filter by Status</label>
                    <select id="statusFilter" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="Viewed">Viewed</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-12">
                    <button id="clearFilters" class="btn btn-outline-secondary btn-sm">Clear All Filters</button>
                </div>
            </div>
        </div>

        <!-- Add Button -->
        <div class="d-flex justify-content-end px-3">
            {{-- <a class="btn btn-outline-primary btn-lg px-4 py-2" href="{{ route('admin.hall-enquiry.create') }}">Add</a> --}}
        </div>

        <!-- Table Section -->
        <div class="card-body px-0 pb-2 pt-0">
            <div class="table-responsive p-0">
                <table class="table align-items-center mb-0">
                    <thead>
                        <tr>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Sr No.</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Customer Name</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Hall Name</th>
                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">View All Details</th>
                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Action</th>
                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Confirm Bokking</th>

                        </tr>
                    </thead>
                    <tbody>
                        @foreach($hallenquiries as $index => $hallenquirie)
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
                                        <h6 class="mb-0 text-sm">{{ $hallenquirie->name }}</h6>
                                    </div>
                                </td>

                                <!-- Hall Name -->
                                <td>
                                    <div class="d-flex px-2 py-1">
                                        <h6 class="mb-0 text-sm">{{ $hallenquirie->hall }}</h6>
                                    </div>
                                </td>

                                <!-- View Details -->
                                <td class="align-middle text-center">
                                    <a href="{{ route('Admin.ViewHallEnquiry', $hallenquirie->id) }}" class="btn btn-secondary">
                                        View
                                    </a>
                                </td>

                                <!-- Action (Delete) -->
                                <td class="align-middle text-center">
                                    <form action="{{ route('admin.hall-enquiry.destroy', $hallenquirie->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this hall enquiry?')">
                                            Delete
                                        </button>
                                    </form>
                                </td>

                                {{-- <td class="align-middle text-center">
                                    <a href="{{route('Generate.Bill',$hallenquirie->id)}}">
                                        Generate Bill
                                    </a>
                                </td> --}}
                                {{-- <td class="align-middle text-center">
                                    <a href="{{ route('Generate.Bill', $hallenquirie->id) }}"
                                       class="btn btn-sm shadow-sm text-white d-flex align-items-center justify-content-center gap-2"
                                       style="background-color: #007bff; border-color: #007bff;">
                                        <span class="material-symbols-outlined" style="font-size: 18px;">request_quote</span>
                                        <span>Generate Bill</span>
                                    </a>
                                </td> --}}

                                <!--<td class="align-middle text-center">-->
                                <!--    @if($hallenquirie->status == 'Viewed')-->
                                <!--        {{-- <a href="{{ route('Generate.Bill', $hallenquirie->id) }}"-->
                                <!--           class="btn btn-sm shadow-sm text-white d-flex align-items-center justify-content-center gap-2"-->
                                <!--           style="background-color: #007bff; border-color: #007bff;">-->
                                <!--            <span class="material-symbols-outlined" style="font-size: 18px;">request_quote</span>-->
                                <!--            <span>Quotation</span> --}}
                                <!--            @if($hallenquirie->quotation_file)-->
                                <!--                <a href="{{ asset('quotation/' .$hallenquirie->quotation_file) }}" target="_blank" class="btn btn-sm shadow-sm text-white d-flex align-items-center justify-content-center gap-2"-->
                                <!--                    style="background-color: #007bff; border-color: #007bff;">-->
                                <!--                    View Quotation-->
                                <!--                </a>-->
                                <!--            @endif-->

                                <!--        </a>-->
                                <!--    @endif-->
                                <!--</td>-->
                                <td class="align-middle text-center">
                                    @if($hallenquirie->status == 'Viewed')
                                            @if($hallenquirie->quotation_file)
                                                <a href="{{ route('admin.quotation.stream', $hallenquirie->id) }}" target="_blank" class="btn btn-sm shadow-sm text-white d-flex align-items-center justify-content-center gap-2"
                                                    style="background-color: #007bff; border-color: #007bff;">
                                                    View Quotation
                                                </a>
                                            @endif
                                    @endif
                                </td>






                                <!-- Action (Edit/Delete) -->
                                {{-- <td class="align-middle text-center">
                                    <a href="{{ route('admin.hall-enquiry.edit', $hallenquirie->id) }}" class="btn btn-success">
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.hall-enquiry.destroy', $hallenquirie->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">
                                            Delete
                                        </button>
                                    </form>
                                </td> --}}

                                <!-- Status (You can add dynamic status if required) -->
                                <td class="align-middle text-center">
                                    @if($hallenquirie->status == 'pending')
                                        <span class="badge bg-warning">Pending</span>
                                    @elseif($hallenquirie->status == 'Viewed')
                                        <span class="badge bg-success">Viewed</span>
                                    @else
                                        <span class="badge bg-danger">Rejected</span>
                                    @endif
                                </td>

                                <td class="align-middle text-center">
                                    @if($hallenquirie->status == 'Viewed')
                                        <form action="{{ route('Confirm.Booking', $hallenquirie->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-secondary">Confirm Booking</button>
                                        </form>
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
