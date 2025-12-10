@extends('admin.layout.masteradmin')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card shadow-lg border-0">
                <!-- Card Header -->
                <div class="card-header bg-gradient-dark text-white text-center py-3">
                    <h4 class="mb-0" style="color: white;">Edit Hall Enquiry</h4>
                </div>

                <!-- Card Body -->
                <div class="card-body px-5 py-4">
                    <!-- Validation Errors -->
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Please fix the following errors:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('Admin.UpdateHallEnquiry', $hallenquirie->id) }}" method="POST" id="editEnquiryForm">
                        @csrf
                        @method('PUT')

                        <!-- Personal Details Section -->
                        <div class="row g-4 mb-4">
                            <div class="col-12">
                                <h5 class="text-primary border-bottom pb-2">Personal Details</h5>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                           id="name" name="name" placeholder="Full Name"
                                           value="{{ old('name', $hallenquirie->name) }}" required>
                                    <label for="name">Full Name *</label>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="organization" name="organization"
                                           placeholder="Organization" value="{{ old('organization', $hallenquirie->organization) }}">
                                    <label for="organization">Organization</label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="gst_no" name="gst_no"
                                           placeholder="GST Number" value="{{ old('gst_no', $hallenquirie->gst_no) }}">
                                    <label for="gst_no">GST Number</label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                           id="email" name="email" placeholder="Email Address"
                                           value="{{ old('email', $hallenquirie->email) }}" required>
                                    <label for="email">Email Address *</label>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control @error('contact_no') is-invalid @enderror"
                                           id="contact_no" name="contact_no" placeholder="Contact Number"
                                           value="{{ old('contact_no', $hallenquirie->contact_no) }}" required
                                           pattern="[6-9][0-9]{9}" maxlength="10">
                                    <label for="contact_no">Contact Number *</label>
                                    @error('contact_no')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="referred_by" name="referred_by"
                                           placeholder="Referred By" value="{{ old('referred_by', $hallenquirie->referred_by) }}">
                                    <label for="referred_by">Referred By</label>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-floating">
                                    <textarea class="form-control" id="address" name="address"
                                              placeholder="Address" style="height: 80px;">{{ old('address', $hallenquirie->address) }}</textarea>
                                    <label for="address">Address</label>
                                </div>
                            </div>
                        </div>

                        <!-- Event Details Section -->
                        <div class="row g-4 mb-4">
                            <div class="col-12">
                                <h5 class="text-primary border-bottom pb-2">Event Details</h5>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control @error('event_type') is-invalid @enderror"
                                           id="event_type" name="event_type" placeholder="Event Type"
                                           value="{{ old('event_type', $hallenquirie->event_type) }}" required>
                                    <label for="event_type">Event Type *</label>
                                    @error('event_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Hall Selection Section -->
                        <div class="row g-4 mb-4">
                            <div class="col-12">
                                <h5 class="text-primary border-bottom pb-2">Hall Selection</h5>
                                <p class="text-muted small">Select the halls you want to book. You can select multiple halls.</p>
                            </div>

                            <div class="col-12">
                                <div class="row g-3" id="hallSelection">
                                    @foreach($halls as $hall)
                                        <div class="col-md-6 col-lg-4">
                                            <div class="hall-card border rounded-3 p-3 h-100">
                                                <div class="form-check d-flex align-items-start">
                                                    <input class="form-check-input hall-checkbox me-2 mt-1" type="checkbox"
                                                           name="selected_halls[]" value="{{ $hall->id }}"
                                                           id="hall_{{ $hall->id }}"
                                                           {{ in_array($hall->id, old('selected_halls', $groupedEnquiries->pluck('hall_id')->toArray())) ? 'checked' : '' }}>
                                                    <label class="form-check-label w-100" for="hall_{{ $hall->id }}">
                                                        <div class="d-flex align-items-center">
                                                            <strong class="text-primary">{{ $hall->name }}</strong>
                                                        </div>
                                                        <small class="text-muted d-block mt-1">
                                                            Capacity: {{ $hall->capacity }} people<br>
                                                            Location: {{ $hall->location }}
                                                        </small>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @error('selected_halls')
                                    <div class="text-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Hall Details Table -->
                        <div class="row g-4 mb-4">
                            <div class="col-12">
                                <h5 class="text-primary border-bottom pb-2">Hall Details</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="hall-details-table" style="display: none;">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Hall Name</th>
                                                <th>Event Dates</th>
                                                <th>Duration/Session</th>
                                                <th>Expected Audience</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="hall-table-body">
                                            <!-- Hall rows will be added here dynamically -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Template for hall table row (hidden) -->
                            <table id="hall-row-template" style="display: none;">
                                <tr class="hall-table-row" data-hall-id="{hall_id}">
                                    <td class="hall-name-cell">
                                        <strong class="hall-name-display"></strong>
                                        <input type="hidden" name="hall_details[{hall_id}][hall_name]" class="hall-name-input">
                                    </td>
                                    <td class="dates-cell">
                                        <div class="date-selection-container">
                                            <div class="date-input-group mb-1">
                                                <input type="date" name="hall_details[{hall_id}][event_dates][]" class="form-control form-control-sm hall-event-date">
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-primary add-date" style="font-size: 12px; padding: 2px 8px;">+ Add Date</button>
                                    </td>
                                    <td class="duration-cell">
                                        <!-- Duration selection based on hall type -->
                                        <div class="hall-duration-section">
                                            <select name="hall_details[{hall_id}][duration]" class="form-control form-control-sm hall-duration">
                                                <!-- Options will be populated based on hall type -->
                                            </select>
                                        </div>
                                        <!-- Session selection for Gurudakshina hall -->
                                        <div class="hall-session-section" style="display: none;">
                                            <select name="hall_details[{hall_id}][session]" class="form-control form-control-sm hall-session">
                                                <option value="">-- Select Session --</option>
                                                <option value="morning">Morning Session (8:00 AM - 2:00 PM)</option>
                                                <option value="evening">Evening Session (4:00 PM - 9:00 PM)</option>
                                                <option value="full_day">Full Day</option>
                                            </select>
                                        </div>
                                        <!-- Time inputs for Art Gallery -->
                                        <div class="hall-time-section mt-1" style="display: none;">
                                            <input type="time" name="hall_details[{hall_id}][start_time]" class="form-control form-control-sm hall-start-time mb-1" placeholder="Start Time">
                                            <input type="time" name="hall_details[{hall_id}][end_time]" class="form-control form-control-sm hall-end-time" placeholder="End Time">
                                        </div>
                                    </td>
                                    <td class="audience-cell">
                                        <input type="number" name="hall_details[{hall_id}][expected_audience]" class="form-control form-control-sm hall-expected-audience" placeholder="Audience *">
                                    </td>
                                    <td class="actions-cell">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-hall">Remove</button>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- Vendor Services Section -->
                        <div class="row g-4 mb-4">
                            <div class="col-12">
                                <h5 class="text-primary border-bottom pb-2">Vendor Services</h5>
                                <p class="text-muted small">Select the vendor services you need.</p>
                            </div>

                            <div class="col-12">
                                <div class="row">
                                    @php
                                        $selectedVendors = old('vendor', json_decode($hallenquirie->vendor, true) ?? []);
                                    @endphp
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="vendor[]" value="event"
                                                   id="vendor_event"
                                                   {{ in_array('event', $selectedVendors) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="vendor_event">
                                                Event Services
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="vendor[]" value="catering"
                                                   id="vendor_catering"
                                                   {{ in_array('catering', $selectedVendors) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="vendor_catering">
                                                Catering Services
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="vendor[]" value="photography"
                                                   id="vendor_photography"
                                                   {{ in_array('photography', $selectedVendors) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="vendor_photography">
                                                Photography
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                @error('vendor')
                                    <div class="text-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="row">
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-primary btn-lg px-5 py-3">
                                    <i class="fas fa-save me-2"></i>Update Enquiry
                                </button>
                                <a href="{{ route('AdminHallEnquiry') }}" class="btn btn-secondary btn-lg px-5 py-3 ms-3">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.hall-card {
    transition: all 0.3s ease;
    cursor: pointer;
    border: 2px solid #e9ecef;
}

.hall-card:hover {
    box-shadow: 0 6px 12px rgba(0,0,0,0.15);
    transform: translateY(-3px);
    border-color: #007bff;
}

.hall-card.selected {
    border-color: #007bff !important;
    background-color: #f8f9ff;
    box-shadow: 0 4px 8px rgba(0,123,255,0.2);
}

.form-check-input:checked ~ .form-check-label .text-primary {
    color: #0056b3 !important;
    font-weight: 600;
}

.btn-lg {
    min-width: 160px;
    font-weight: 600;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.btn-lg:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.card {
    border-radius: 12px;
    overflow: hidden;
}

.card-header {
    border-radius: 12px 12px 0 0 !important;
}

.form-floating > .form-control,
.form-floating > .form-select {
    border-radius: 8px;
    border: 1px solid #ced4da;
    transition: all 0.3s ease;
    padding-left: 16px;
}

.form-floating > .form-control:focus,
.form-floating > .form-select:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

/* Fix checkbox visibility */
.form-check-input {
    border: 1px solid #ced4da;
    background-color: #fff;
    border-radius: 0.25rem;
    width: 1.2em;
    height: 1.2em;
    margin-top: 0.25em;
}

.form-check-input:checked {
    background-color: #007bff;
    border-color: #007bff;
}

.form-check-input:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.table {
    border-radius: 8px;
    overflow: hidden;
}

.alert {
    border-radius: 8px;
    border: none;
}

.h5 {
    font-weight: 600;
    color: #495057;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {

    // Phone number validation
    const contactInput = document.getElementById('contact_no');
    contactInput.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '');
        if (this.value.length > 10) {
            this.value = this.value.slice(0, 10);
        }
    });

    // Hall selection functionality
    const hallCheckboxes = document.querySelectorAll('.hall-checkbox');
    const hallDetailsTable = document.getElementById('hall-details-table');
    const hallTableBody = document.getElementById('hall-table-body');
    const hallRowTemplate = document.getElementById('hall-row-template');
    const hallsData = @json($halls->pluck('name', 'id'));

    // Function to determine hall type
    function getHallType(hallName) {
        const name = hallName.toLowerCase();
        if (name.includes('gurudakshina')) {
            return 'gurudakshina';
        } else if (name.includes('art gallery') || name.includes('art') && name.includes('gallery')) {
            return 'art_gallery';
        } else {
            return 'other';
        }
    }

    // Function to setup hall-specific options for a table row
    function setupHallOptionsForRow(row, hallName) {
        const hallType = getHallType(hallName);
        const durationSelect = row.querySelector('.hall-duration');
        const sessionSection = row.querySelector('.hall-session-section');
        const timeSection = row.querySelector('.hall-time-section');
        const sessionSelect = row.querySelector('.hall-session');

        // Clear existing options
        durationSelect.innerHTML = '';

        if (hallType === 'gurudakshina') {
            // For Gurudakshina: hide duration and time, show session
            const durationSection = row.querySelector('.hall-duration-section');
            durationSection.style.display = 'none';
            sessionSection.style.display = 'block';
            timeSection.style.display = 'none';

            // Only set required for visible session select
            sessionSelect.required = true;
            // Make sure duration select is not required when hidden
            durationSelect.required = false;

            // Add event listener for session change to show/hide time inputs for full day
            sessionSelect.addEventListener('change', function() {
                if (this.value === 'full_day') {
                    timeSection.style.display = 'block';
                    const startTimeInput = timeSection.querySelector('.hall-start-time');
                    const endTimeInput = timeSection.querySelector('.hall-end-time');
                    startTimeInput.required = true;
                    endTimeInput.required = true;
                } else {
                    timeSection.style.display = 'none';
                    const startTimeInput = timeSection.querySelector('.hall-start-time');
                    const endTimeInput = timeSection.querySelector('.hall-end-time');
                    startTimeInput.required = false;
                    endTimeInput.required = false;
                }
            });
        } else if (hallType === 'art_gallery') {
            // For Art Gallery: show only full day option
            const durationSection = row.querySelector('.hall-duration-section');
            durationSection.style.display = 'block';
            sessionSection.style.display = 'none';
            timeSection.style.display = 'block';

            durationSelect.innerHTML = '<option value="full_day">Full Day</option>';
            durationSelect.value = 'full_day';
            durationSelect.required = true;
            // Make sure session select is not required when hidden
            sessionSelect.required = false;
        } else {
            // For other halls: show morning, afternoon, evening with custom times
            const durationSection = row.querySelector('.hall-duration-section');
            durationSection.style.display = 'block';
            sessionSection.style.display = 'none';
            timeSection.style.display = 'block'; // Show time inputs for custom times

            const startTimeInput = row.querySelector('.hall-start-time');
            const endTimeInput = row.querySelector('.hall-end-time');

            durationSelect.innerHTML = `
                <option value="">-- Select Duration --</option>
                <option value="half_day_morning">Morning</option>
                <option value="half_day_afternoon">Afternoon</option>
                <option value="half_day_evening">Evening</option>
            `;
            durationSelect.required = true;
            // Make sure session select is not required when hidden
            sessionSelect.required = false;
        }
    }

    // Function to create hall table row
    function createHallTableRow(hallId, hallName) {
        console.log('Creating hall table row for hall ID:', hallId, 'Name:', hallName);

        const templateRow = hallRowTemplate.querySelector('tr').cloneNode(true);
        templateRow.setAttribute('data-hall-id', hallId);

        // Update hall name display
        templateRow.querySelector('.hall-name-display').textContent = hallName;

        // Update form field names and IDs
        const inputs = templateRow.querySelectorAll('input, select');
        inputs.forEach(input => {
            if (input.name && input.name.includes('{hall_id}')) {
                input.name = input.name.replace('{hall_id}', hallId);
            }
            if (input.id && input.id.includes('{hall_id}')) {
                input.id = input.id.replace('{hall_id}', hallId);
            }

            // Add required attribute for common fields
            if (input.classList.contains('hall-event-date') ||
                input.classList.contains('hall-expected-audience')) {
                input.required = true;
            }

            // Handle hidden hall name input
            if (input.classList.contains('hall-name-input')) {
                input.value = hallName;
            }
        });

        // Update label 'for' attributes
        const labels = templateRow.querySelectorAll('label');
        labels.forEach(label => {
            if (label.htmlFor && label.htmlFor.includes('{hall_id}')) {
                label.htmlFor = label.htmlFor.replace('{hall_id}', hallId);
            }
        });

        hallTableBody.appendChild(templateRow);

        // Show the table if it's hidden
        hallDetailsTable.style.display = 'table';

        // Setup hall-specific options (this will show/hide sections and set required attributes)
        setupHallOptionsForRow(templateRow, hallName);

        console.log('Hall table row created successfully');
        return templateRow;
    }

    // Function to remove hall table row
    function removeHallTableRow(hallId) {
        const row = hallTableBody.querySelector(`tr[data-hall-id="${hallId}"]`);
        if (row) {
            row.remove();

            // Hide table if no rows left
            if (hallTableBody.children.length === 0) {
                hallDetailsTable.style.display = 'none';
            }
        }
    }

    // Handle checkbox changes
    hallCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const hallId = this.value;
            const hallName = hallsData[hallId];

            if (this.checked) {
                createHallTableRow(hallId, hallName);
            } else {
                removeHallTableRow(hallId);
            }
        });
    });

    // Handle remove hall button clicks
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-hall')) {
            e.preventDefault();
            const row = e.target.closest('tr');
            const hallId = row.getAttribute('data-hall-id');

            // Uncheck the corresponding checkbox
            const checkbox = document.getElementById(`hall_${hallId}`);
            if (checkbox) {
                checkbox.checked = false;
            }

            // Remove the row
            removeHallTableRow(hallId);
        }
    });

    // Handle dynamic date addition
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('add-date')) {
            e.preventDefault();
            const row = e.target.closest('tr');
            const hallId = row.getAttribute('data-hall-id');
            const dateCell = e.target.closest('.dates-cell');
            const dateContainer = dateCell.querySelector('.date-selection-container');

            const dateInputGroup = document.createElement('div');
            dateInputGroup.className = 'date-input-group mb-1';
            dateInputGroup.innerHTML = `
                <input type="date" name="hall_details[${hallId}][event_dates][]" class="form-control form-control-sm hall-event-date">
                <button type="button" class="btn btn-sm btn-outline-danger remove-date-btn" style="font-size: 12px; padding: 1px 5px;">×</button>
            `;

            dateContainer.appendChild(dateInputGroup);
        }

        if (e.target.classList.contains('remove-date-btn')) {
            e.preventDefault();
            const dateInputGroup = e.target.closest('.date-input-group');
            const dateContainer = dateInputGroup.parentElement;
            const remainingGroups = dateContainer.querySelectorAll('.date-input-group');

            // Only remove if there's more than one date input
            if (remainingGroups.length > 1) {
                dateInputGroup.remove();
            }
        }
    });

    // Initialize existing selections (for form validation errors or existing data)
    const selectedHalls = @json(old('selected_halls', []));
    const oldHallDetails = @json(old('hall_details', []));
    const existingHallDetails = @json($existingHallDetails ?? []);

    // Combine existing hall details with old input data (old input takes precedence for validation errors)
    const hallDetailsData = { ...existingHallDetails, ...oldHallDetails };

    // Get selected hall IDs from either old input or existing enquiries
    const hallIdsToInitialize = selectedHalls.length > 0 ? selectedHalls : Object.keys(hallDetailsData);

    hallIdsToInitialize.forEach(hallId => {
        const checkbox = document.getElementById(`hall_${hallId}`);
        const hallName = hallsData[hallId];
        if (checkbox && hallName) {
            // Check the checkbox
            checkbox.checked = true;

            // Create the table row
            const row = createHallTableRow(hallId, hallName);

            // Populate hall details if available
            if (hallDetailsData[hallId]) {
                const hallData = hallDetailsData[hallId];

                // Set audience first
                const audienceInput = row.querySelector('.hall-expected-audience');
                if (audienceInput && hallData.expected_audience) {
                    audienceInput.value = hallData.expected_audience;
                }

                // Handle event dates - populate existing date inputs
                if (hallData.event_dates && Array.isArray(hallData.event_dates) && hallData.event_dates.length > 0) {
                    const dateContainer = row.querySelector('.date-selection-container');
                    const existingDateInputs = dateContainer.querySelectorAll('.hall-event-date');

                    // Filter out empty dates
                    const validDates = hallData.event_dates.filter(date => date && date.trim() !== '');

                    if (validDates.length > 0) {
                        // Set the first date input
                        if (existingDateInputs[0]) {
                            existingDateInputs[0].value = validDates[0];
                        }

                        // Add additional date inputs for remaining dates
                        for (let i = 1; i < validDates.length; i++) {
                            const dateInputGroup = document.createElement('div');
                            dateInputGroup.className = 'date-input-group mb-1';
                            dateInputGroup.innerHTML = `
                                <input type="date" name="hall_details[${hallId}][event_dates][]" class="form-control form-control-sm hall-event-date" value="${validDates[i]}">
                                <button type="button" class="btn btn-sm btn-outline-danger remove-date-btn" style="font-size: 12px; padding: 1px 5px;">×</button>
                            `;
                            dateContainer.appendChild(dateInputGroup);
                        }
                    }
                }

                // Get hall type for this row
                const hallType = getHallType(hallName);

                // Set duration/session and times based on hall type
                const durationSelect = row.querySelector('.hall-duration');
                const sessionSelect = row.querySelector('.hall-session');
                const startTimeInput = row.querySelector('.hall-start-time');
                const endTimeInput = row.querySelector('.hall-end-time');

                // Set duration/session and times based on hall type
                if (hallType === 'gurudakshina') {
                    // Gurudakshina uses session field (morning, evening, full_day)
                    const savedSession = hallData.session || (hallData.duration === 'full_day' ? 'full_day' : '');
                    if (sessionSelect && savedSession) {
                        sessionSelect.value = savedSession;
                        // If full_day, show time inputs and set values
                        if (savedSession === 'full_day') {
                            const timeSection = row.querySelector('.hall-time-section');
                            timeSection.style.display = 'block';
                            const startTimeInput = timeSection.querySelector('.hall-start-time');
                            const endTimeInput = timeSection.querySelector('.hall-end-time');
                            startTimeInput.required = true;
                            endTimeInput.required = true;
                            if (hallData.start_time) startTimeInput.value = hallData.start_time;
                            if (hallData.end_time) endTimeInput.value = hallData.end_time;
                        }
                        // Trigger change event to ensure proper setup
                        sessionSelect.dispatchEvent(new Event('change'));
                    }
                } else {
                    // All other halls use duration
                    if (durationSelect && hallData.duration) {
                        durationSelect.value = hallData.duration;
                    }

                    // Always set start/end time if available (for Art Gallery & others)
                    if (startTimeInput && hallData.start_time) {
                        startTimeInput.value = hallData.start_time;
                    }
                    if (endTimeInput && hallData.end_time) {
                        endTimeInput.value = hallData.end_time;
                    }
                }
            }
        }
    });

    // Hall selection visual feedback
    const hallCards = document.querySelectorAll('.hall-card');
    hallCards.forEach(card => {
        const checkbox = card.querySelector('input[type="checkbox"]');
        checkbox.addEventListener('change', function() {
            if (this.checked) {
                card.classList.add('selected');
            } else {
                card.classList.remove('selected');
            }
        });

        // Initialize selected state
        if (checkbox.checked) {
            card.classList.add('selected');
        }
    });

    // Form validation
    const form = document.getElementById('editEnquiryForm');
    form.addEventListener('submit', function(e) {
        console.log('Form submission started');

        // Check if at least one hall is selected
        const selectedHallsCheck = document.querySelectorAll('input[name="selected_halls[]"]:checked');
        if (selectedHallsCheck.length === 0) {
            alert('Please select at least one hall.');
            e.preventDefault();
            return false;
        }
        console.log('Hall selection passed:', selectedHallsCheck.length, 'halls selected');

        // Validate that selected halls have details filled
        let hasValidHallDetails = true;
        selectedHallsCheck.forEach(hallCheckbox => {
            const hallId = hallCheckbox.value;
            const hallRow = hallTableBody.querySelector(`tr[data-hall-id="${hallId}"]`);
            if (hallRow) {
                const dateInputs = hallRow.querySelectorAll('.hall-event-date');
                const audienceInput = hallRow.querySelector('.hall-expected-audience');
                const hallName = hallsData[hallId];
                const hallType = getHallType(hallName);

                // Check if at least one date is selected and filled
                let hasValidDate = false;
                let filledDateCount = 0;
                dateInputs.forEach(dateInput => {
                    if (dateInput.value.trim() !== '') {
                        hasValidDate = true;
                        filledDateCount++;
                    }
                });

                if (!hasValidDate) {
                    alert(`Please select at least one event date for ${hallName}.`);
                    hasValidHallDetails = false;
                    e.preventDefault();
                    return;
                }

                // Validate based on hall type
                if (hallType === 'gurudakshina') {
                    const sessionSelect = hallRow.querySelector('.hall-session');
                    if (!sessionSelect.value) {
                        alert(`Please select a session for ${hallName}.`);
                        hasValidHallDetails = false;
                        e.preventDefault();
                        return;
                    }
                } else {
                    const durationSelect = hallRow.querySelector('.hall-duration');

                    if (!durationSelect.value) {
                        alert(`Please select a duration for ${hallName}.`);
                        hasValidHallDetails = false;
                        e.preventDefault();
                        return;
                    }

                    // For Art Gallery and other halls, check start/end times
                    if (hallType === 'art_gallery' || hallType === 'other') {
                        const startTimeInput = hallRow.querySelector('.hall-start-time');
                        const endTimeInput = hallRow.querySelector('.hall-end-time');
                        if (!startTimeInput.value || !endTimeInput.value) {
                            alert(`Please fill in start and end times for ${hallName}.`);
                            hasValidHallDetails = false;
                            e.preventDefault();
                            return;
                        }
                    }
                }

                if (!audienceInput.value) {
                    alert(`Please enter expected audience for ${hallName}.`);
                    hasValidHallDetails = false;
                    e.preventDefault();
                    return;
                }

                console.log(`Hall ${hallName}: ${filledDateCount} dates filled`);
            }
        });

        if (!hasValidHallDetails) {
            return;
        }
        console.log('Hall details validation passed');
    });
});
</script>
@endsection
