@extends('admin.layout.masteradmin')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
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
                    var statusFilter = $('#statusFilter').val();

                    $('tbody tr').each(function() {
                        var row = $(this);
                        var customerName = row.find('td:nth-child(2) h6').text().toLowerCase();
                        var hallName = row.find('td:nth-child(3) h6').text().toLowerCase();
                        var status = '';

                        // Get status from badge text (7th column)
                        var statusBadge = row.find('td:nth-child(7) .badge');
                        status = statusBadge.text().trim();

                        var showRow = true;

                        // Apply hall filter
                        if (hallFilter && hallName.indexOf(hallFilter) === -1) {
                            showRow = false;
                        }

                        // Apply customer search
                        if (customerSearch && customerName.indexOf(customerSearch) === -1) {
                            showRow = false;
                        }

                        // Apply status filter (case-insensitive)
                        if (statusFilter && status.toLowerCase() !== statusFilter.toLowerCase()) {
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
                        @foreach($groupedEnquiries->unique('hall') as $enquiry)
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

        <!-- Export Section -->
        <div class="d-flex justify-content-end px-3 mb-3">
            <div class="btn-group" role="group">
                <button id="exportDropdown" type="button" class="btn btn-success btn-lg px-4 py-2 dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa fa-download"></i> Export Reports
                </button>
                <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                    <li><a class="dropdown-item" href="{{ route('admin.hall-enquiries.export', 'all') }}">
                        <i class="fas fa-file-excel"></i> All Time Report
                    </a></li>
                    <li><a class="dropdown-item" href="{{ route('admin.hall-enquiries.export', 'weekly') }}">
                        <i class="fas fa-calendar-week"></i> Weekly Report
                    </a></li>
                    <li><a class="dropdown-item" href="{{ route('admin.hall-enquiries.export', 'monthly') }}">
                        <i class="fas fa-calendar-alt"></i> Monthly Report
                    </a></li>
                    <li><a class="dropdown-item" href="{{ route('admin.hall-enquiries.export', 'yearly') }}">
                        <i class="fas fa-calendar"></i> Yearly Report
                    </a></li>
                </ul>
            </div>
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
                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Quotation</th>
                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Confirm Booking</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($groupedEnquiries as $index => $hallenquirie)
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
                                <td style="max-width: 220px;">
                                    <div class="d-flex px-2 py-1">
                                        @if($hallenquirie->is_group)
                                            <div title="{{ implode(', ', $hallenquirie->group_halls) }}">
                                                <h6 class="mb-1 text-sm text-truncate" style="max-width: 180px;">
                                                    {{ $hallenquirie->group_halls[0] }}
                                                    @if($hallenquirie->group_count > 1)
                                                        <small class="text-muted">+{{ $hallenquirie->group_count - 1 }} more</small>
                                                    @endif
                                                </h6>
                                                <small class="text-muted">{{ $hallenquirie->group_count }} halls</small>
                                            </div>
                                        @else
                                            <h6 class="mb-0 text-sm">{{ $hallenquirie->hall }}</h6>
                                        @endif
                                    </div>
                                </td>

                                <!-- View Details -->
                                <td class="align-middle text-center">
                                    <a href="{{ route('Admin.ViewHallEnquiry', $hallenquirie->id) }}" class="btn btn-secondary">
                                        View
                                    </a>
                                </td>

                                <!-- Action (Edit/Cancel Enquiry) -->
                                <td class="align-middle text-center">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('Admin.EditHallEnquiry', $hallenquirie->id) }}" class="btn btn-info btn-sm">
                                            <i class="fa fa-edit"></i> Edit
                                        </a>
                                        <form action="{{ route('admin.hall-enquiry.cancel', $hallenquirie->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" style="background-color: red; border-color: red;border-bottom-left-radius: 0px;border-top-left-radius: 0px;height: 36px;" class="btn btn-sm btn-sm text-white" onclick="return confirm('Are you sure you want to cancel this hall enquiry?')">
                                                <i class="fa fa-ban"></i> Cancel
                                            </button>
                                        </form>
                                    </div>
                                </td>


                                <td class="align-middle text-center">
                                    @if($hallenquirie->status == 'Viewed')
                                            @if($hallenquirie->quotation_file)
                                                <div class="btn-group-vertical" role="group">
                                                    <div class="btn-group mb-1" role="group">
                                                        <a href="{{ route('admin.quotation.stream', $hallenquirie->id) }}" target="_blank" class="btn btn-sm shadow-sm text-white"
                                                            style="background-color: #007bff; border-color: #007bff;">
                                                            <i class="fa fa-eye"></i> View Quotation
                                                        </a>
                                                        <a href="{{ route('admin.quotation.edit', $hallenquirie->id) }}" class="btn btn-warning shadow-sm text-white">
                                                            <i class="fa fa-edit"></i> Edit
                                                        </a>
                                                        <form action="{{ route('admin.send.quotation', $hallenquirie->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm shadow-sm text-white"
                                                            style="background-color: #16af58; border-color: #16af58;border-bottom-left-radius: 0px;border-top-left-radius: 0px;height: 36px;">
                                                            <i class="fa fa-whatsapp" style="font-size: 20px;"></i>
                                                        </button>
                                                    </form>
                                                    </div>

                                                </div>
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
