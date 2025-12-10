@extends('admin.layout.masteradmin')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card shadow-lg border-0">
                <!-- Card Header -->
                <div class="card-header bg-gradient-dark text-white text-center py-3">
                    <h4 class="mb-0" style="color: white;">Edit Quotation</h4>
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

                    @php
                        // Check if this is a multi-hall enquiry
                        $groupedEnquiries = collect();
                        if ($enquiry->group_code) {
                            // Fetch all enquiries in the same group
                            $groupedEnquiries = \App\Models\HallEnquiry::where('group_code', $enquiry->group_code)
                                                                     ->orderBy('hall')
                                                                     ->get();
                        } else {
                            // Single enquiry, add it to the collection
                            $groupedEnquiries->push($enquiry);
                        }
                        $isMultiHall = $groupedEnquiries->count() > 1;
                    @endphp

                    <form action="{{ route('admin.quotation.update', $enquiry->id) }}" method="POST" id="editQuotationForm">
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
                                           value="{{ old('name', $enquiry->name) }}" readonly>
                                    <label for="name">Full Name *</label>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="organization" name="organization"
                                           placeholder="Organization" value="{{ old('organization', $enquiry->organization) }}" readonly>
                                    <label for="organization">Organization</label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="gst_no" name="gst_no"
                                           placeholder="GST Number" value="{{ old('gst_no', $enquiry->gst_no) }}" readonly>
                                    <label for="gst_no">GST Number</label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                           id="email" name="email" placeholder="Email Address"
                                           value="{{ old('email', $enquiry->email) }}" readonly>
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
                                           value="{{ old('contact_no', $enquiry->contact_no) }}" readonly
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
                                           placeholder="Referred By" value="{{ old('referred_by', $enquiry->referred_by) }}" readonly>
                                    <label for="referred_by">Referred By</label>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-floating">
                                    <textarea class="form-control" id="address" name="address"
                                              placeholder="Address" style="height: 80px;" readonly>{{ old('address', $enquiry->address) }}</textarea>
                                    <label for="address">Address</label>
                                </div>
                            </div>
                        </div>

                        <!-- Event Details Section -->
                        <div class="row g-4 mb-4 {{ $isMultiHall ? 'd-none' : '' }}">
                            <div class="col-12">
                                <h5 class="text-primary border-bottom pb-2">Event Details</h5>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control @error('event_type') is-invalid @enderror"
                                           id="event_type" name="event_type" placeholder="Event Type"
                                           value="{{ old('event_type', $enquiry->event_type) }}" readonly>
                                    <label for="event_type">Event Type *</label>
                                    @error('event_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control @error('hall') is-invalid @enderror"
                                           id="hall" name="hall" placeholder="Hall Name"
                                           value="{{ old('hall', $enquiry->hall) }}" readonly>
                                    <label for="hall">Hall Name *</label>
                                    @error('hall')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="date" class="form-control @error('event_date') is-invalid @enderror"
                                           id="event_date" name="event_date"
                                           value="{{ old('event_date', $enquiry->event_date) }}" readonly>
                                    <label for="event_date">Event Date *</label>
                                    @error('event_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="duration" name="duration"
                                           placeholder="Duration" value="{{ old('duration', $enquiry->duration) }}" readonly>
                                    <label for="duration">Duration</label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="time" class="form-control @error('start_time') is-invalid @enderror"
                                           id="start_time" name="start_time"
                                           value="{{ old('start_time', $enquiry->start_time) }}" readonly>
                                    <label for="start_time">Start Time *</label>
                                    @error('start_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="time" class="form-control @error('end_time') is-invalid @enderror"
                                           id="end_time" name="end_time"
                                           value="{{ old('end_time', $enquiry->end_time) }}" readonly>
                                    <label for="end_time">End Time *</label>
                                    @error('end_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-floating">
                                    <input type="number" class="form-control" id="expected_audience" name="expected_audience"
                                           placeholder="Expected Audience" value="{{ old('expected_audience', $enquiry->expected_audience) }}" readonly>
                                    <label for="expected_audience">Expected Audience</label>
                                </div>
                            </div>
                        </div>


                        <!-- Office Details Section -->
                        @if($isMultiHall)
                            <!-- Multi-Hall Office Details -->
                            <div class="row g-4 mb-4">
                                <div class="col-12">
                                    <h5 class="text-primary border-bottom pb-2">Office Details - Multi Hall Booking</h5>
                                    <p class="text-muted small">Configure details for each hall separately.</p>
                                </div>

                                @foreach($groupedEnquiries->groupBy('hall') as $hallName => $hallEnquiries)
                                    <div class="hall-section mb-4 p-3 border rounded">
                                        <h6 class="text-primary">{{ $hallName }}</h6>
                                        <input type="hidden" name="hall_ids[]" value="{{ $hallEnquiries->first()->id }}">

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <div class="form-floating">
                                                    <input type="text" name="rent_amount[{{ $hallEnquiries->first()->id }}]"
                                                           value="{{ old('rent_amount.' . $hallEnquiries->first()->id, $hallEnquiries->first()->rent_amount) }}"
                                                           placeholder="Enter Amount"
                                                           class="form-control border border-secondary rounded-2 shadow-sm ps-3"
                                                           required>
                                                    <label>Rent Amount (Rs) for {{ $hallName }} *</label>
                                                </div>
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <div class="form-floating">
                                                    <input type="text" name="deposit[{{ $hallEnquiries->first()->id }}]"
                                                           value="{{ old('deposit.' . $hallEnquiries->first()->id, $hallEnquiries->first()->deposit) }}"
                                                           placeholder="Enter Deposit Amount"
                                                           class="form-control border border-secondary rounded-2 shadow-sm ps-3"
                                                           required>
                                                    <label>Deposit Amount (Rs) for {{ $hallName }} *</label>
                                                </div>
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <div class="form-floating">
                                                    <textarea name="special_note[{{ $hallEnquiries->first()->id }}]"
                                                              placeholder="Enter Special Note"
                                                              class="form-control border border-secondary rounded-2 shadow-sm ps-3">{{ old('special_note.' . $hallEnquiries->first()->id, $hallEnquiries->first()->special_note) }}</textarea>
                                                    <label>Special Note for {{ $hallName }}</label>
                                                </div>
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <div class="form-floating">
                                                    <input type="text" name="event_setup[{{ $hallEnquiries->first()->id }}]"
                                                           value="{{ old('event_setup.' . $hallEnquiries->first()->id, $hallEnquiries->first()->event_setup) }}"
                                                           placeholder="Enter Event Setup"
                                                           class="form-control border border-secondary rounded-2 shadow-sm ps-3">
                                                    <label>Event Setup for {{ $hallName }}</label>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Per-Hall Accessories -->
                                        <div class="mt-3">
                                            <h6 class="text-secondary">Accessories for {{ $hallName }}:</h6>
                                            <ul class="list-group w-100">
                                                @foreach ($accessories as $accessorie)
                                                    @if (!empty($accessorie->name) || !empty($accessorie->quantity) || !empty($accessorie->price) || !empty($accessorie->hours))
                                                        <li class="list-group-item d-flex align-items-center">
                                                            <div class="d-flex align-items-center">
                                                                <input class="form-check-input custom-checkbox me-2" type="checkbox"
                                                                       name="accessorie[{{ $hallEnquiries->first()->id }}][]" value="{{ $accessorie->id }}"
                                                                       onchange="toggleChairContainerForHall('{{ $hallEnquiries->first()->id }}', '{{ $accessorie->name }}', this.checked)"
                                                                       {{ is_array(old('accessorie.' . $hallEnquiries->first()->id, json_decode($hallEnquiries->first()->accessorie ?? '[]', true))) && in_array($accessorie->id, old('accessorie.' . $hallEnquiries->first()->id, json_decode($hallEnquiries->first()->accessorie ?? '[]', true))) ? 'checked' : '' }}>

                                                                <label class="mb-0 fw-bold">{{ $accessorie->name ?? 'Unknown Accessory' }}</label>
                                                            </div>

                                                            <div style="margin-left: 20px;">
                                                                @if (!empty($accessorie->quantity))
                                                                    <span class="badge bg-primary">Qty: {{ $accessorie->quantity }}</span>
                                                                @endif
                                                                @if (!empty($accessorie->price))
                                                                    <span class="badge bg-success">Price: ₹{{ $accessorie->price }}</span>
                                                                @endif
                                                                @if (!empty($accessorie->hours))
                                                                    <span class="badge bg-warning">Hours: {{ $accessorie->hours }}</span>
                                                                @endif
                                                            </div>
                                                        </li>
                                                    @endif
                                                @endforeach
                                            </ul>

                                            <div class="row mb-3 mt-2">
                                                <div id="stage_chairs_container_{{ $hallEnquiries->first()->id }}" style="display:none;" class="col-md-6">
                                                    <div class="form-floating">
                                                        <input type="number" name="stage_chairs_count[{{ $hallEnquiries->first()->id }}]" value="{{ old('stage_chairs_count.' . $hallEnquiries->first()->id, $hallEnquiries->first()->stage_chairs_count) }}" placeholder="Enter Stage Chairs Count" class="form-control border border-secondary rounded-2 shadow-sm ps-3">
                                                        <label>Stage Chairs Count for {{ $hallName }}</label>
                                                    </div>
                                                </div>
                                                <div id="hall_chairs_container_{{ $hallEnquiries->first()->id }}" style="display:none;" class="col-md-6">
                                                    <div class="form-floating">
                                                        <input type="number" name="hall_chairs_count[{{ $hallEnquiries->first()->id }}]" value="{{ old('hall_chairs_count.' . $hallEnquiries->first()->id, $hallEnquiries->first()->hall_chairs_count) }}" placeholder="Enter Hall Chairs Count" class="form-control border border-secondary rounded-2 shadow-sm ps-3">
                                                        <label>Hall Chairs Count for {{ $hallName }}</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                <!-- Common fields for all halls -->
                                {{-- <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <div class="form-floating">
                                            <select name="id_proof" class="form-control border border-secondary rounded-2 shadow-sm ps-3 @error('id_proof') is-invalid @enderror" required>
                                                <option value="" disabled {{ old('id_proof') == '' ? 'selected' : '' }}>-- Select ID Proof --</option>
                                                <option value="Aadhar Card" {{ old('id_proof') == 'Aadhar Card' ? 'selected' : '' }}>Aadhar Card</option>
                                                <option value="Driving Licence" {{ old('id_proof') == 'Driving Licence' ? 'selected' : '' }}>Driving Licence</option>
                                            </select>
                                            <label>ID Proof *</label>
                                            @error('id_proof')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div> --}}
                            </div>
                        @else
                            <!-- Single Hall Office Details -->
                            <div class="row g-4 mb-4">
                                <div class="col-12">
                                    <h5 class="text-primary border-bottom pb-2">Office Details</h5>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control @error('rent_amount') is-invalid @enderror"
                                               id="rent_amount" name="rent_amount" placeholder="Rent Amount"
                                               value="{{ old('rent_amount', $enquiry->rent_amount) }}">
                                        <label for="rent_amount">Rent Amount (Rs)</label>
                                        @error('rent_amount')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control @error('deposit') is-invalid @enderror"
                                               id="deposit" name="deposit" placeholder="Deposit Amount"
                                               value="{{ old('deposit', $enquiry->deposit) }}">
                                        <label for="deposit">Deposit Amount (Rs)</label>
                                        @error('deposit')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <select class="form-control @error('id_proof') is-invalid @enderror"
                                                id="id_proof" name="id_proof">
                                            <option value="">-- Select ID Proof --</option>
                                            <option value="Aadhar Card" {{ old('id_proof', $enquiry->id_proof) == 'Aadhar Card' ? 'selected' : '' }}>Aadhar Card</option>
                                            <option value="Driving Licence" {{ old('id_proof', $enquiry->id_proof) == 'Driving Licence' ? 'selected' : '' }}>Driving Licence</option>
                                        </select>
                                        <label for="id_proof">ID Proof</label>
                                        @error('id_proof')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="event_setup" name="event_setup"
                                               placeholder="Event Setup" value="{{ old('event_setup', $enquiry->event_setup) }}">
                                        <label for="event_setup">Event Setup</label>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-floating">
                                        <textarea class="form-control" id="special_note" name="special_note"
                                                  placeholder="Special Note" style="height: 80px;">{{ old('special_note', $enquiry->special_note) }}</textarea>
                                        <label for="special_note">Special Note</label>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if(!$isMultiHall)
                        <!-- Accessories Section (Single Hall Only) -->
                        <div class="row g-4 mb-4">
                            <div class="col-12">
                                <h5 class="text-primary border-bottom pb-2">Accessories</h5>
                                <p class="text-muted small">Select the accessories you need for your event.</p>
                            </div>

                            <div class="col-12">
                                <div class="row g-3" id="accessoriesSelection">
                                    @php
                                        $selectedAccessories = old('accessorie', json_decode($enquiry->accessorie, true) ?? []);
                                    @endphp
                                    @foreach($accessories ?? [] as $accessory)
                                        <div class="col-md-6 col-lg-4">
                                            <div class="accessory-card border rounded-3 p-3 h-100">
                                                <div class="form-check d-flex align-items-start">
                                                    <input class="form-check-input accessory-checkbox me-2 mt-1" type="checkbox"
                                                           name="accessorie[]" value="{{ $accessory->id }}"
                                                           id="accessory_{{ $accessory->id }}"
                                                           onchange="toggleChairContainer('{{ $accessory->name }}', this.checked)"
                                                           {{ in_array($accessory->id, $selectedAccessories) ? 'checked' : '' }}>
                                                    <label class="form-check-label w-100" for="accessory_{{ $accessory->id }}">
                                                        <div class="d-flex align-items-center">
                                                            <strong class="text-primary">{{ $accessory->name }}</strong>
                                                        </div>
                                                        <small class="text-muted d-block mt-1">
                                                            Price: ₹{{ number_format($accessory->price, 2) }}<br>
                                                            Hours: {{ $accessory->hours }}
                                                        </small>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @error('accessorie')
                                    <div class="text-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Chair Count Containers -->
                            <div class="row mb-3 mt-2">
                                <div id="stage_chairs_container" style="display:none;" class="col-md-6">
                                    <div class="form-floating">
                                        <input type="number" class="form-control" id="stage_chairs_count" name="stage_chairs_count"
                                               placeholder="Stage Chairs Count" value="{{ old('stage_chairs_count', $enquiry->stage_chairs_count) }}">
                                        <label for="stage_chairs_count">Stage Chairs Count</label>
                                    </div>
                                </div>
                                <div id="hall_chairs_container" style="display:none;" class="col-md-6">
                                    <div class="form-floating">
                                        <input type="number" class="form-control" id="hall_chairs_count" name="hall_chairs_count"
                                               placeholder="Hall Chairs Count" value="{{ old('hall_chairs_count', $enquiry->hall_chairs_count) }}">
                                        <label for="hall_chairs_count">Hall Chairs Count</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif



                        <!-- Submit Button -->
                        <div class="row">
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-primary btn-lg px-5 py-3">
                                    <i class="fas fa-save me-2"></i>Update Quotation
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
.accessory-card {
    transition: all 0.3s ease;
    cursor: pointer;
    border: 2px solid #e9ecef;
}

.accessory-card:hover {
    box-shadow: 0 6px 12px rgba(0,0,0,0.15);
    transform: translateY(-3px);
    border-color: #007bff;
}

.accessory-card.selected {
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

    // Accessory selection visual feedback
    const accessoryCards = document.querySelectorAll('.accessory-card');
    accessoryCards.forEach(card => {
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

    // Initialize chair containers based on selected accessories for single hall
    const checkboxes = document.querySelectorAll('input[name="accessorie[]"]');
    checkboxes.forEach(function(cb) {
        const li = cb.closest('.accessory-card');
        const label = li ? li.querySelector('label') : null;
        if (label) {
            const name = label.textContent.trim();
            if (cb.checked && (name === 'Stage Chairs' || name === 'Hall chairs')) {
                toggleChairContainer(name, true);
            }
        }
    });

    // Initialize chair containers for multi-hall accessories
    const multiHallCheckboxes = document.querySelectorAll('input[name^="accessorie["]');
    multiHallCheckboxes.forEach(function(cb) {
        if (cb.name.includes('[') && cb.name.includes(']')) {
            const li = cb.closest('li');
            const label = li ? li.querySelector('label') : null;
            if (label) {
                const name = label.textContent.trim();
                const hallId = cb.name.match(/accessorie\[(\d+)\]/)[1];
                if (cb.checked && (name === 'Stage Chairs' || name === 'Hall chairs')) {
                    toggleChairContainerForHall(hallId, name, true);
                }
            }
        }
    });
});

function toggleChairContainer(name, checked) {
    if (name === 'Stage Chairs') {
        document.getElementById('stage_chairs_container').style.display = checked ? 'block' : 'none';
    } else if (name === 'Hall chairs') {
        document.getElementById('hall_chairs_container').style.display = checked ? 'block' : 'none';
    }
}

function toggleChairContainerForHall(hallId, name, checked) {
    if (name === 'Stage Chairs') {
        document.getElementById('stage_chairs_container_' + hallId).style.display = checked ? 'block' : 'none';
    } else if (name === 'Hall chairs') {
        document.getElementById('hall_chairs_container_' + hallId).style.display = checked ? 'block' : 'none';
    }
}
</script>
@endsection
