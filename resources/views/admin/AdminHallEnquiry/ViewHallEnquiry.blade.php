@extends('admin.layout.masteradmin')

@section('content')
    <div class="container-fluid py-4">
        <div class="col-lg-10 mx-auto">
            <div class="card shadow-lg border-0">
                <!-- Card Header -->
                <div class="card-header bg-gradient-dark text-white text-center py-3">
                    <h4 class="mb-0" style="color: #fff;display:inline">Hall Enquiry Details</h4>
                    <a href="{{ route('admin.pre.show.stream', $hallenquirie->id) }}" target="_blank" class="btn btn-success" style="float: right; margin-right: 10px;">Stream Pre Show PDF</a>
                    <button style="float: right;" onclick="printDiv('printableArea')">Print</button>
                </div>


                <!-- Card Body -->
                <div class="card-body px-5 py-4">
                    <div id="printableArea">
                        <!-- Print-only header -->
                        <div class="d-none d-print-block">
                            <div
                                style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
                                <img src="{{ asset('website/assets/Gokhale-logo.png') }}" alt="Gokhale Logo"
                                    style="height: 80px; width: auto;">
                                <div style="text-align: center; flex: 1; margin: 0 20px;">
                                    <h3 style="color: #333; margin: 5px 0; font-size: 20px;">Gokhle Education Society</h3>
                                    <h4 style="color: #555; margin: 5px 0; font-size: 18px;">Gurudakshina Project</h4>
                                    <h5 style="color: #666; margin: 5px 0; font-size: 16px;">Enquiry Form</h5>
                                </div>
                                <img src="{{ asset('website/assets/100gokhale-logo-2.png') }}" alt="100 Years Logo"
                                    style="height: 80px; width: auto;">
                            </div>



                            {{-- </div> --}}
                            <hr class="mb-4">
                        </div>

                        <div class="row g-4">
                            <!-- Left Column -->
                            <div class="col-md-6">
                                <p class="fw-bold mb-2 text-uppercase">Personal Details</p>
                                <div class="border rounded p-3">
                                    <p><strong>Name:</strong> {{ $hallenquirie->name }}</p>
                                    <p><strong>Organization:</strong> {{ $hallenquirie->organization }}</p>
                                    <p><strong>GST No:</strong> {{ $hallenquirie->gst_no ?? 'Not Provided' }}</p>
                                    <p><strong>Email:</strong> <a
                                            href="mailto:{{ $hallenquirie->email }}">{{ $hallenquirie->email }}</a></p>
                                    <p><strong>Contact No:</strong> <a
                                            href="tel:{{ $hallenquirie->contact_no }}">{{ $hallenquirie->contact_no }}</a>
                                    </p>
                                    <p><strong>Address:</strong> {{ $hallenquirie->address ?? 'Not Provided' }}</p>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-6">
                                <p class="fw-bold mb-2 text-uppercase">Event Details</p>
                                <div class="border rounded p-3">
                                    <p><strong>Referred By:</strong> {{ $hallenquirie->referred_by ?? 'Not Provided' }}</p>
                                    <p><strong>Event Type:</strong> {{ $hallenquirie->event_type }}</p>

                                    @if($groupedEnquiries->count() > 1)
                                        <p><strong>Multi-Hall Enquiry:</strong> {{ $groupedEnquiries->groupBy('hall')->count() }} halls, {{ $groupedEnquiries->count() }} total bookings</p>
                                        <div class="mt-3">
                                            @foreach($groupedEnquiries->groupBy('hall') as $hallName => $hallEnquiries)
                                                <div class="mb-3 p-2 border rounded">
                                                    <strong>{{ $hallName }}</strong><br>
                                                    <small class="text-muted">
                                                        @php
                                                            $dates = $hallEnquiries->first()->event_dates ? json_decode($hallEnquiries->first()->event_dates, true) : [$hallEnquiries->first()->event_date];
                                                            $dates = array_filter($dates); // Remove empty values
                                                            sort($dates);
                                                        @endphp
                                                        <strong>Dates:</strong> {{ implode(', ', $dates) }} ({{ count($dates) }} date{{ count($dates) > 1 ? 's' : '' }})<br>
                                                        <strong>Time:</strong> {{ $hallEnquiries->first()->start_time }} - {{ $hallEnquiries->first()->end_time }}<br>
                                                        <strong>Duration:</strong> {{ $hallEnquiries->first()->duration }}<br>
                                                        <strong>Expected Audience:</strong> {{ $hallEnquiries->first()->expected_audience }}
                                                    </small>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        @php
                                            $dates = $hallenquirie->event_dates ? json_decode($hallenquirie->event_dates, true) : [$hallenquirie->event_date];
                                            $dates = array_filter($dates); // Remove empty values
                                            sort($dates);
                                        @endphp
                                        <p><strong>Event Date{{ count($dates) > 1 ? 's' : '' }}:</strong> {{ implode(', ', $dates) }}</p>
                                        <p><strong>Start Time:</strong> {{ $hallenquirie->start_time }}</p>
                                        <p><strong>End Time:</strong> {{ $hallenquirie->end_time }}</p>
                                        <p><strong>Hall:</strong> {{ $hallenquirie->hall }}</p>
                                        <p><strong>Duration:</strong> {{ $hallenquirie->duration }}</p>
                                        <p><strong>Expected Audience:</strong> {{ $hallenquirie->expected_audience }}</p>
                                    @endif

                                    <p><strong>Status:</strong>
                                        @if ($hallenquirie->status == 'pending')
                                            <span class="badge bg-warning">Pending</span>
                                        @elseif($hallenquirie->status == 'Viewed')
                                            <span class="badge bg-success">Viewed</span>
                                        @else
                                            <span class="badge bg-danger">Rejected</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Agreement Paragraph -->
                        <div class="border rounded p-3 mt-3">
                            <p><strong>Agreement:</strong></p>
                            <p>I have read, understood, and agreed to the rules and regulations for
                                @if($groupedEnquiries->count() > 1)
                                    <strong>{{ $groupedEnquiries->pluck('hall')->unique()->implode(', ') }}</strong> halls
                                @else
                                    <strong>{{ $hallenquirie->hall }}</strong> hall
                                @endif
                                . Failing which booking shall be cancelled without prior notice and no refund claim will be entertained. This enquiry form is not a final booking or confirmation. Confirmation will be communicated on given contact no. or e-mail id.</p>
                        </div>

                        <div>
                            <p class="fw-bold mb-2 text-uppercase">Sign Image</p>
                            @if ($hallenquirie->sign_image)
                                <div class="border rounded p-3">
                                    <img src="{{ asset('sign_images/' . $hallenquirie->sign_image) }}" alt="Sign Image"
                                        style="max-width: 200px;">
                                </div>
                            @elseif($hallenquirie->typed_signature)
                                <p>{{ $hallenquirie->typed_signature }}</p>
                            @else
                                <p>No sign image uploaded.</p>
                            @endif
                        </div>

                        <!-- Office Details and Accessories for Print -->
                        @if ($hallenquirie->status != 'pending')
                            @if($groupedEnquiries->count() > 1)
                                <!-- Multi-Hall Office Details -->
                                <div class="mt-4">
                                    <h5 class="fw-bold mb-3 text-uppercase">Per-Hall Office Details</h5>
                                    @foreach($groupedEnquiries->groupBy('hall') as $hallName => $hallEnquiries)
                                        <div class="row g-4 mb-4">
                                            <div class="col-12">
                                                <h6 class="text-primary">{{ $hallName }}</h6>
                                            </div>
                                            <!-- Office Details - Left Column -->
                                            <div class="col-md-6">
                                                <div class="border rounded p-3">
                                                    @php
                                                        $firstEnquiry = $hallEnquiries->first();
                                                    @endphp
                                                    <p><strong>Rent Amount:</strong> {{ !empty($firstEnquiry->rent_amount) ? $firstEnquiry->rent_amount : 'Not Set' }}</p>
                                                    <p><strong>Total Deposit:</strong> {{ !empty($firstEnquiry->deposit) ? $firstEnquiry->deposit : 'Not Set' }}</p>
                                                    <p><strong>Special Note:</strong> {{ !empty($firstEnquiry->special_note) ? $firstEnquiry->special_note : 'Not Set' }}</p>
                                                    <p><strong>ID Proof:</strong> {{ !empty($firstEnquiry->id_proof) ? $firstEnquiry->id_proof : 'Not Set' }}</p>
                                                    <p><strong>Event Setup:</strong> {{ !empty($firstEnquiry->event_setup) ? $firstEnquiry->event_setup : 'Not Set' }}</p>
                                                    <p><strong>Stage Chairs Count:</strong> {{ $firstEnquiry->stage_chairs_count ?? 'N/A' }}</p>
                                                    <p><strong>Hall Chairs Count:</strong> {{ $firstEnquiry->hall_chairs_count ?? 'N/A' }}</p>
                                                </div>
                                            </div>

                                            <!-- Accessories - Right Column -->
                                            <div class="col-md-6">
                                                <div class="border rounded p-3">
                                                    @php
                                                        $selected_accessories = json_decode($firstEnquiry->accessorie, true) ?? [];
                                                        $accessory_names = [];
                                                        if (!empty($selected_accessories)) {
                                                            $accessory_names = \App\Models\Accessorie::whereIn('id', $selected_accessories)
                                                                ->pluck('name')
                                                                ->toArray();
                                                        }
                                                    @endphp

                                                    @if (!empty($accessory_names))
                                                        @foreach ($accessory_names as $accessory)
                                                            <strong>{{ $accessory }}</strong><br>
                                                        @endforeach
                                                    @else
                                                        <strong>No accessories selected</strong>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <!-- Single Hall Office Details -->
                                <div class="row g-4 mt-4">
                                    <!-- Office Details - Left Column -->
                                    <div class="col-md-6">
                                        <p class="fw-bold mb-2 text-uppercase">Office Details</p>
                                        <div class="border rounded p-3">
                                                    <p><strong>Rent Amount:</strong> {{ !empty($hallenquirie->rent_amount) ? $hallenquirie->rent_amount : 'Not Set' }}</p>
                                                    <p><strong>Total Deposit:</strong> {{ !empty($hallenquirie->deposit) ? $hallenquirie->deposit : 'Not Set' }}</p>
                                                    <p><strong>Special Note:</strong> {{ !empty($hallenquirie->special_note) ? $hallenquirie->special_note : 'Not Set' }}</p>
                                                    <p><strong>ID Proof:</strong> {{ !empty($hallenquirie->id_proof) ? $hallenquirie->id_proof : 'Not Set' }}</p>
                                                    <p><strong>Event Setup:</strong> {{ !empty($hallenquirie->event_setup) ? $hallenquirie->event_setup : 'Not Set' }}</p>
                                            <p><strong>Stage Chairs Count:</strong> {{ $hallenquirie->stage_chairs_count ?? 'N/A' }}</p>
                                            <p><strong>Hall Chairs Count:</strong> {{ $hallenquirie->hall_chairs_count ?? 'N/A' }}</p>
                                            @php
                                                $vendor_services = json_decode($hallenquirie->vendor, true) ?? [];
                                            @endphp
                                            <p><strong>Vendor Services:</strong>
                                                {{ $hallenquirie->vendor ? implode(', ', json_decode($hallenquirie->vendor, true)) : 'N/A' }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Overall Vendor Services Section -->
                                    <div class="col-md-6">
                                        <p class="fw-bold mb-2 text-uppercase">Vendor Services</p>
                                        <div class="border rounded p-3">
                                            @if($groupedEnquiries->count() > 1)
                                                @php
                                                    $overall_vendor_services = [];
                                                    foreach($groupedEnquiries as $enquiry) {
                                                        $vendor_services = json_decode($enquiry->vendor, true) ?? [];
                                                        $overall_vendor_services = array_merge($overall_vendor_services, $vendor_services);
                                                    }
                                                    $overall_vendor_services = array_unique($overall_vendor_services);
                                                @endphp
                                                <p><strong>Overall Vendor Services:</strong>
                                                    {{ !empty($overall_vendor_services) ? implode(', ', $overall_vendor_services) : 'N/A' }}
                                                </p>
                                            @else
                                                @php
                                                    $vendor_services = json_decode($hallenquirie->vendor, true) ?? [];
                                                @endphp
                                                <p><strong>Vendor Services:</strong>
                                                    {{ $hallenquirie->vendor ? implode(', ', json_decode($hallenquirie->vendor, true)) : 'N/A' }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Accessories - Right Column -->
                                    <div class="col-md-6">
                                        <p class="fw-bold mb-2 text-uppercase">Accessories</p>
                                        <div class="border rounded p-3">
                                            @php
                                                $selected_accessories = json_decode($hallenquirie->accessorie, true) ?? [];
                                                $accessory_names = [];
                                                if (!empty($selected_accessories)) {
                                                    $accessory_names = \App\Models\Accessorie::whereIn('id', $selected_accessories)
                                                        ->pluck('name')
                                                        ->toArray();
                                                }
                                            @endphp

                                            @if (!empty($accessory_names))
                                                @foreach ($accessory_names as $accessory)
                                                    <strong>{{ $accessory }}</strong><br>
                                                @endforeach
                                            @else
                                                <strong>No accessories selected</strong>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if($groupedEnquiries->count() > 1)
                                @php
                                    $overall_vendor_services = [];
                                    foreach($groupedEnquiries as $enquiry) {
                                        $vendor_services = json_decode($enquiry->vendor, true) ?? [];
                                        $overall_vendor_services = array_merge($overall_vendor_services, $vendor_services);
                                    }
                                    $overall_vendor_services = array_unique($overall_vendor_services);
                                @endphp

                                <!-- Overall Vendor Services Section for Multi-Hall -->
                                <div class="row g-4 mt-4">
                                    <div class="col-md-6">
                                        <p class="fw-bold mb-2 text-uppercase">Overall Vendor Services</p>
                                        <div class="border rounded p-3">
                                            <p><strong>Vendor Services:</strong>
                                                {{ !empty($overall_vendor_services) ? implode(', ', $overall_vendor_services) : 'N/A' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif

                        <div class="mt-4 d-none d-print-block">
                            <p>Enquiry Date:- {{ $hallenquirie->created_at }}</p>
                            <p>Attended By :- _______________</p>
                        </div>

                        <!-- Print-only Footer -->
                        <div class="d-none d-print-block">
                            <div style="display: flex; justify-content: space-between; margin-top: 50px; padding: 20px 0;">
                                <div style="text-align: center; flex: 1; margin-right: 20px;">
                                    <p class="mb-0 fw-bold">Executive Officer</p>
                                    <div style="border-top: 1px solid #333; width: 200px; margin: 50px auto 10px;"></div>
                                    <p class="mt-2">Signature</p>
                                </div>
                                <div style="text-align: center; flex: 1; margin-left: 20px;">
                                    <p class="mb-0 fw-bold">Approved by</p>
                                    <p class="mb-0">(Gurudakshina Administrative Committee)</p>
                                    <div style="border-top: 1px solid #333; width: 200px; margin: 50px auto 10px;"></div>
                                    <p class="mt-2">Signature</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>



                    @if ($hallenquirie->status == 'pending')
                        <form action="{{ route('Admin.StoreOffice', $hallenquirie->id) }}" method="POST"
                            class="p-4 border rounded-3 shadow-sm bg-light">
                            @csrf

                            <h4 class="mb-3 text-primary"><strong>For Office Use Only</strong></h4>

                            @if($groupedEnquiries->count() > 1)
                                <!-- Multi-Hall Form -->
                                <div class="mb-4">
                                    <h5 class="text-secondary">Configure Details for Each Hall</h5>
                                    @foreach($groupedEnquiries->groupBy('hall') as $hallName => $hallEnquiries)
                                        <div class="hall-section mb-4 p-3 border rounded">
                                            <h6 class="text-primary">{{ $hallName }}</h6>
                                            <input type="hidden" name="hall_ids[]" value="{{ $hallEnquiries->first()->id }}">

                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="fw-bold text-dark mb-2">Rent Amount (Rs) for {{ $hallName }}:</label>
                                                    <input type="text" name="rent_amount[{{ $hallEnquiries->first()->id }}]"
                                                        value="{{ old('rent_amount.' . $hallEnquiries->first()->id) }}"
                                                        placeholder="Enter Amount"
                                                        class="form-control border border-secondary rounded-2 shadow-sm ps-3"
                                                        required>
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label class="fw-bold text-dark mb-2">Deposit Amount (Rs) for {{ $hallName }}:</label>
                                                    <input type="text" name="deposit[{{ $hallEnquiries->first()->id }}]"
                                                        value="{{ old('deposit.' . $hallEnquiries->first()->id) }}"
                                                        placeholder="Enter Deposit Amount"
                                                        class="form-control border border-secondary rounded-2 shadow-sm ps-3"
                                                        required>
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label class="fw-bold text-dark mb-2">Special Note for {{ $hallName }}:</label>
                                                    <textarea name="special_note[{{ $hallEnquiries->first()->id }}]"
                                                        placeholder="Enter Special Note"
                                                        class="form-control border border-secondary rounded-2 shadow-sm ps-3">{{ old('special_note.' . $hallEnquiries->first()->id) }}</textarea>
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label class="fw-bold text-dark mb-2">Event Setup for {{ $hallName }}:</label>
                                                    <input type="text" name="event_setup[{{ $hallEnquiries->first()->id }}]"
                                                        value="{{ old('event_setup.' . $hallEnquiries->first()->id) }}"
                                                        placeholder="Enter Event Setup"
                                                        class="form-control border border-secondary rounded-2 shadow-sm ps-3">
                                                </div>
                                            </div>

                                            <!-- Per-Hall Accessories -->
                                            <div class="mt-3">
                                                <h6 class="text-secondary">Accessories for {{ $hallName }}:</h6>
                                                <ul class="list-group w-100">
                                                    @foreach ($accessories as $accessorie)
                                                        @if (
                                                            !empty($accessorie->name) ||
                                                                !empty($accessorie->quantity) ||
                                                                !empty($accessorie->price) ||
                                                                !empty($accessorie->hours))
                                                            <li class="list-group-item d-flex align-items-center">
                                                                <div class="d-flex align-items-center">
                                                                    <input class="form-check-input custom-checkbox me-2" type="checkbox"
                                                                        name="accessorie[{{ $hallEnquiries->first()->id }}][]" value="{{ $accessorie->id }}"
                                                                        onchange="toggleChairContainerForHall('{{ $hallEnquiries->first()->id }}', '{{ $accessorie->name }}', this.checked)"
                                                                        {{ is_array(old('accessorie.' . $hallEnquiries->first()->id, json_decode($hallEnquiries->first()->accessorie ?? '[]', true))) && in_array($accessorie->id, old('accessorie.' . $hallEnquiries->first()->id, json_decode($hallEnquiries->first()->accessorie ?? '[]', true))) || $accessorie->name == 'Stage Chairs' || $accessorie->name == 'Hall chairs' ? 'checked' : '' }}>

                                                                    <label
                                                                        class="mb-0 fw-bold">{{ $accessorie->name ?? 'Unknown Accessory' }}</label>
                                                                </div>

                                                                <div style="margin-left: 20px;">
                                                                    @if (!empty($accessorie->quantity))
                                                                        <span class="badge bg-primary">Qty:
                                                                            {{ $accessorie->quantity }}</span>
                                                                    @endif
                                                                    @if (!empty($accessorie->price))
                                                                        <span class="badge bg-success">Price:
                                                                            ₹{{ $accessorie->price }}</span>
                                                                    @endif
                                                                    @if (!empty($accessorie->hours))
                                                                        <span class="badge bg-warning">Hours:
                                                                            {{ $accessorie->hours }}</span>
                                                                    @endif
                                                                </div>
                                                            </li>
                                                        @endif
                                                    @endforeach
                                                </ul>

                                                <div class="row mb-3 mt-2">
                                                    <div id="stage_chairs_container_{{ $hallEnquiries->first()->id }}" style="display:none;" class="col-md-6">
                                                        <div class="p-3 border rounded-3 shadow-sm bg-white">
                                                            <label for="stage_chairs_count_{{ $hallEnquiries->first()->id }}" class="fw-bold text-dark mb-2">Stage Chairs Count for {{ $hallName }}:</label>
                                                            <input type="number" name="stage_chairs_count[{{ $hallEnquiries->first()->id }}]" value="{{ old('stage_chairs_count.' . $hallEnquiries->first()->id) }}" placeholder="Enter Stage Chairs Count" class="form-control border border-secondary rounded-2 shadow-sm ps-3">
                                                        </div>
                                                    </div>
                                                    <div id="hall_chairs_container_{{ $hallEnquiries->first()->id }}" style="display:none;" class="col-md-6">
                                                        <div class="p-3 border rounded-3 shadow-sm bg-white">
                                                            <label for="hall_chairs_count_{{ $hallEnquiries->first()->id }}" class="fw-bold text-dark mb-2">Hall Chairs Count for {{ $hallName }}:</label>
                                                            <input type="number" name="hall_chairs_count[{{ $hallEnquiries->first()->id }}]" value="{{ old('hall_chairs_count.' . $hallEnquiries->first()->id) }}" placeholder="Enter Hall Chairs Count" class="form-control border border-secondary rounded-2 shadow-sm ps-3">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                        </div>
                                    @endforeach
                                </div>

                                <!-- Common fields for all halls -->
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="p-3 border rounded-3 shadow-sm bg-white">
                                            <label for="id_proof" class="fw-bold text-dark mb-2">ID Proof:</label>
                                            <select name="id_proof"
                                                class="form-control border border-secondary rounded-2 shadow-sm ps-3 @error('id_proof') is-invalid @enderror"
                                                required>
                                                <option value="" disabled {{ old('id_proof') == '' ? 'selected' : '' }}>
                                                    -- Select ID Proof --</option>
                                                <option value="Aadhar Card"
                                                    {{ old('id_proof') == 'Aadhar Card' ? 'selected' : '' }}>Aadhar Card
                                                </option>
                                                <option value="Driving Licence"
                                                    {{ old('id_proof') == 'Driving Licence' ? 'selected' : '' }}>Driving
                                                    License</option>
                                            </select>
                                            @error('id_proof')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <div class="p-3 border rounded-3 shadow-sm bg-white">
                                            <label class="fw-bold text-dark mb-2">Vendor Services:</label>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="vendor[]" value="event"
                                                               id="event_common"
                                                               {{ is_array(old('vendor', [])) && in_array('event', old('vendor', [])) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="event_common">
                                                            Event Services
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="vendor[]" value="catering"
                                                               id="catering_common"
                                                               {{ is_array(old('vendor', [])) && in_array('catering', old('vendor', [])) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="catering_common">
                                                            Catering Services
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="vendor[]" value="photography"
                                                               id="photography_common"
                                                               {{ is_array(old('vendor', [])) && in_array('photography', old('vendor', [])) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="photography_common">
                                                            Photography
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <!-- Single Hall Form -->
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="p-3 border rounded-3 shadow-sm bg-white">
                                            <label for="rent_amount" class="fw-bold text-dark mb-2">Rent Amount (Rs):</label>
                                            <input type="text" name="rent_amount"
                                                placeholder="Enter Amount"
                                                class="form-control border border-secondary rounded-2 shadow-sm ps-3 @error('rent') is-invalid @enderror"
                                                required>
                                            @error('rent_amount')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <div class="p-3 border rounded-3 shadow-sm bg-white">
                                            <label for="deposit" class="fw-bold text-dark mb-2">Deposit Amount (Rs):</label>
                                            <input type="text" name="deposit"
                                                placeholder="Enter Deposit Amount"
                                                class="form-control border border-secondary rounded-2 shadow-sm ps-3 @error('deposit') is-invalid @enderror"
                                                required>
                                            @error('deposit')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <div class="p-3 border rounded-3 shadow-sm bg-white">
                                            <label for="special_note" class="fw-bold text-dark mb-2">Special Note:</label>
                                            <textarea name="special_note" placeholder="Enter Special Note"
                                                class="form-control border border-secondary rounded-2 shadow-sm ps-3 @error('special_note') is-invalid @enderror">{{ old('special_note') }}</textarea>
                                            @error('special_note')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="p-3 border rounded-3 shadow-sm bg-white">
                                            <label for="id_proof" class="fw-bold text-dark mb-2">ID Proof:</label>
                                            <select name="id_proof"
                                                class="form-control border border-secondary rounded-2 shadow-sm ps-3 @error('id_proof') is-invalid @enderror"
                                                required>
                                                <option value="" disabled {{ old('id_proof') == '' ? 'selected' : '' }}>
                                                    -- Select ID Proof --</option>
                                                <option value="Aadhar Card"
                                                    {{ old('id_proof') == 'Aadhar Card' ? 'selected' : '' }}>Aadhar Card
                                                </option>
                                                <option value="Driving Licence"
                                                    {{ old('id_proof') == 'Driving Licence' ? 'selected' : '' }}>Driving
                                                    License</option>
                                            </select>
                                            @error('id_proof')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3" style="text-align: center; margin-left: 235px;">
                                        <div class="p-3 border rounded-3 shadow-sm bg-white">
                                            <label for="event_setup" class="fw-bold text-dark mb-2">Event Setup:</label>
                                            <input type="text" placeholder="Enter Event Setup" name="event_setup"

                                                class="form-control border border-secondary rounded-2 shadow-sm ps-3 @error('event_setup') is-invalid @enderror">
                                            @error('event_setup')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <h4 class="mb-3 text-secondary"><strong>Accessories Details:</strong></h4>
                                    <ul class="list-group w-100">
                                        @foreach ($accessories as $accessorie)
                                            @if (
                                                !empty($accessorie->name) ||
                                                    !empty($accessorie->quantity) ||
                                                    !empty($accessorie->price) ||
                                                    !empty($accessorie->hours))
                                                <li class="list-group-item d-flex align-items-center">
                                                    <div class="d-flex align-items-center">
                                                        <input class="form-check-input custom-checkbox me-2" type="checkbox"
                                                            name="accessorie[]" value="{{ $accessorie->id }}"
                                                            onchange="toggleChairContainer('{{ $accessorie->name }}', this.checked)"
                                                            {{ is_array(old('accessorie', json_decode($hallenquirie->accessories ?? '[]', true))) && in_array($accessorie->id, old('accessorie', json_decode($hallenquirie->accessorie ?? '[]', true))) || $accessorie->name == 'Stage Chairs' || $accessorie->name == 'Hall chairs' ? 'checked' : '' }}>

                                                        <label
                                                            class="mb-0 fw-bold">{{ $accessorie->name ?? 'Unknown Accessory' }}</label>
                                                    </div>

                                                    <div style="margin-left: 20px;">
                                                        @if (!empty($accessorie->quantity))
                                                            <span class="badge bg-primary">Qty:
                                                                {{ $accessorie->quantity }}</span>
                                                        @endif
                                                        @if (!empty($accessorie->price))
                                                            <span class="badge bg-success">Price:
                                                                ₹{{ $accessorie->price }}</span>
                                                        @endif
                                                        @if (!empty($accessorie->hours))
                                                            <span class="badge bg-warning">Hours:
                                                                {{ $accessorie->hours }}</span>
                                                        @endif
                                                    </div>
                                                </li>
                                            @endif
                                        @endforeach
                                    </ul>

                                    <div class="row mb-3 mt-2">
                                        <div id="stage_chairs_container" style="display:none;" class="col-md-6">
                                            <div class="p-3 border rounded-3 shadow-sm bg-white">
                                                <label for="stage_chairs_count" class="fw-bold text-dark mb-2">Stage Chairs Count:</label>
                                                <input type="number" name="stage_chairs_count" value="{{ old('stage_chairs_count') }}" placeholder="Enter Stage Chairs Count" class="form-control border border-secondary rounded-2 shadow-sm ps-3">
                                            </div>
                                        </div>
                                        <div id="hall_chairs_container" style="display:none;" class="col-md-6">
                                            <div class="p-3 border rounded-3 shadow-sm bg-white">
                                                <label for="hall_chairs_count" class="fw-bold text-dark mb-2">Hall Chairs Count:</label>
                                                <input type="number" name="hall_chairs_count" value="{{ old('hall_chairs_count') }}" placeholder="Enter Hall Chairs Count" class="form-control border border-secondary rounded-2 shadow-sm ps-3">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Vendor Services for single hall -->
                                <div class="row">
                                    <h4 class="mb-3 text-secondary"><strong>Vendor Services:</strong></h4>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="vendor[]" value="event"
                                                   id="event_single"
                                                   {{ is_array(old('vendor', json_decode($hallenquirie->vendor ?? '[]', true))) && in_array('event', old('vendor', json_decode($hallenquirie->vendor ?? '[]', true))) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="event_single">
                                                Event Services
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="vendor[]" value="catering"
                                                   id="catering_single"
                                                   {{ is_array(old('vendor', json_decode($hallenquirie->vendor ?? '[]', true))) && in_array('catering', old('vendor', json_decode($hallenquirie->vendor ?? '[]', true))) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="catering_single">
                                                Catering Services
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="vendor[]" value="photography"
                                                   id="photography_single"
                                                   {{ is_array(old('vendor', json_decode($hallenquirie->vendor ?? '[]', true))) && in_array('photography', old('vendor', json_decode($hallenquirie->vendor ?? '[]', true))) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="photography_single">
                                                Photography
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Add this CSS for better checkbox visibility -->
                            <style>
                                .custom-checkbox {
                                    border: 2px solid #333;
                                }
                            </style>

                            <div class="d-flex justify-content-center mt-4">
                                {{-- <button type="submit" class="btn btn-primary px-4 py-2">Save</button> --}}
                                <button type="submit" class="btn btn-primary px-4 py-2">Save & Generate
                                    Quotation</button>
                                {{-- <a href="{{ route('AdminHallEnquiry') }}" class="btn btn-secondary ms-2 px-4 py-2">Back</a> --}}
                            </div>
                        </form>
                    @else
                        <!-- Right Column -->
                        <div class="row">
                            {{-- <div class="col-md-6">
                                <p class="fw-bold mb-2 text-uppercase">Office Details</p>
                                <div class="border rounded p-3">
                                    <p><strong>Rent Amount:</strong> {{ $hallenquirie->rent_amount }}</p>
                                    <p><strong>Total Deposit:</strong> {{ $hallenquirie->deposit }}</p>
                                    <p><strong>Special Note:</strong> {{ $hallenquirie->special_note }}</p>
                                    <p><strong>ID Proof:</strong> {{ $hallenquirie->Id_proof }}</p>
                                    <p><strong>Event Setup:</strong> {{ $hallenquirie->event_setup }}</p>
                                    <p><strong>Stage Chairs Count:</strong> {{ $hallenquirie->stage_chairs_count ?? 'N/A' }}</p>
                                    <p><strong>Hall Chairs Count:</strong> {{ $hallenquirie->hall_chairs_count ?? 'N/A' }}</p>
                                    @php
                                        $vendor_services = json_decode($hallenquirie->vendor, true) ?? [];
                                    @endphp
                                    <p><strong>Vendor Services :-</strong>
                                        {{ $hallenquirie->vendor ? implode(', ', json_decode($hallenquirie->vendor, true)) : 'N/A' }}
                                    </p>
                                </div>
                            </div>


                            <div class="col-md-6 ">
                                <p class="fw-bold mb-2 text-uppercase"><strong>Accessories:</strong><br></p>
                                <div class="card border rounded p-3">

                                    @php
                                        $selected_accessories = json_decode($hallenquirie->accessorie, true) ?? [];
                                    @endphp
                                    @php
                                        $selected_accessories = json_decode($hallenquirie->accessorie, true) ?? [];
                                        $accessory_names = \App\Models\Accessorie::whereIn('id', $selected_accessories)
                                            ->pluck('name')
                                            ->toArray();
                                    @endphp

                                    @if (!empty($accessory_names))
                                        @foreach ($accessory_names as $accessory)

                                            <strong>{{ $accessory }}</strong><br>
                                        @endforeach
                                    @else
                                        N/A
                                    @endif
                                </div>

                            </div> --}}
                        </div>
                </div>
                @endif
            </div>
            <div style="text-align: center;">
                <a href="{{ route('AdminHallEnquiry') }}" class="btn btn-secondary" style="width: 100px;">Back</a>
            </div>
        </div>
    </div>



    <script>
        function printDiv(divId) {
            var div = document.getElementById(divId).cloneNode(true);
            var imgs = div.querySelectorAll('img');
            var promises = [];

            // Convert all image src to absolute URLs and preload them
            imgs.forEach(function(img) {
                if (img.src) {
                    // Convert to absolute URL
                    var absUrl = new URL(img.src, window.location.origin).href;

                    // Create a promise to preload the image
                    var promise = new Promise((resolve, reject) => {
                        var tempImg = new Image();
                        tempImg.src = absUrl;

                        // When the image is fully loaded
                        tempImg.onload = function() {
                            img.src = absUrl; // Set the absolute URL
                            resolve();
                        };

                        // Handle image loading errors
                        tempImg.onerror = function() {
                            console.error('Error loading image:', absUrl);
                            img.src = ''; // Optionally set a placeholder or remove the image
                            resolve(); // Continue even if the image fails to load
                        };
                    });

                    promises.push(promise);
                }
            });

            // Wait for all images to load
            Promise.all(promises).then(() => {
                var content = div.innerHTML;
                var myWindow = window.open('', '', 'height=800,width=800');
                myWindow.document.write('<html><head><title>Print Preview</title>');
                myWindow.document.write(`
            <style>
                @page {
                    size: A4;
                    margin: 0.5in;
                }
                body {
                    font-family: 'Times New Roman', serif;
                    margin: 0;
                    padding: 0;
                    color: #000;
                    font-size: 12px;
                    line-height: 1.4;
                }
                #printableArea {
                    max-width: none;
                    margin: 0;
                    padding: 0;
                }
                .border {
                    border: 1px solid #000;
                    padding: 10px;
                    margin-bottom: 15px;
                    border-radius: 0;
                    page-break-inside: avoid;
                }
                p {
                    margin: 6px 0;
                    font-size: 12px;
                }
                .fw-bold {
                    font-weight: bold;
                    text-transform: uppercase;
                    font-size: 13px;
                }
                .p-3 {
                    padding: 10px;
                }
                .badge {
                    padding: 4px 8px;
                    border-radius: 3px;
                    font-size: 11px;
                    color: #fff;
                    display: inline-block;
                    font-weight: bold;
                }
                .bg-warning { background-color: #f39c12; }
                .bg-success { background-color: #27ae60; }
                .bg-danger { background-color: #e74c3c; }
                img {
                    max-width: 80px;
                    height: auto;
                    vertical-align: top;
                }
                .row {
                    display: table;
                    width: 100%;
                    margin-bottom: 15px;
                }
                .col-md-6 {
                    display: table-cell;
                    width: 50%;
                    vertical-align: top;
                    padding: 0 10px;
                }
                h3 {
                    font-size: 18px;
                    margin: 10px 0;
                    text-align: center;
                }
                h4 {
                    font-size: 16px;
                    margin: 8px 0;
                    text-align: center;
                }
                h5 {
                    font-size: 14px;
                    margin: 6px 0;
                    text-align: center;
                }
                .text-center {
                    text-align: center;
                }
                .text-uppercase {
                    text-transform: uppercase;
                }
                .mb-2 {
                    margin-bottom: 8px;
                }
                .mb-3 {
                    margin-bottom: 12px;
                }
                .mb-4 {
                    margin-bottom: 16px;
                }
                .mt-3 {
                    margin-top: 12px;
                }
                .mt-4 {
                    margin-top: 16px;
                }
                .d-none {
                    display: none !important;
                }
                .d-print-block {
                    display: block !important;
                }
                hr {
                    border: none;
                    border-top: 1px solid #000;
                    margin: 20px 0;
                }
                .page-break {
                    page-break-before: always;
                }
                .no-break {
                    page-break-inside: avoid;
                }
                strong {
                    font-weight: bold;
                }
                .flex {
                    display: flex;
                }
                .justify-content-between {
                    justify-content: space-between;
                }
                .align-items-center {
                    align-items: center;
                }
                .flex-1 {
                    flex: 1;
                }
                .margin-right-20 {
                    margin-right: 20px;
                }
                .margin-left-20 {
                    margin-left: 20px;
                }
                .margin-0-20 {
                    margin: 0 20px;
                }
                .border-top {
                    border-top: 1px solid #000;
                }
                .width-200 {
                    width: 200px;
                }
                .margin-50px-auto {
                    margin: 50px auto 10px;
                }
                .margin-20px-0 {
                    margin: 20px 0;
                }
                .padding-20px-0 {
                    padding: 20px 0;
                }
            </style>
        `);
                myWindow.document.write('</head><body>');
                myWindow.document.write(content);
                myWindow.document.write('</body></html>');
                myWindow.document.close();

                // Wait for the DOM to fully render before printing
                setTimeout(() => {
                    myWindow.focus();
                    myWindow.print();
                    myWindow.close();
                }, 500); // 500ms delay to ensure rendering
            }).catch(error => {
                console.error('Error processing images for print:', error);
                // Optionally, open the print window without images or show an alert
                alert('Some images could not be loaded. Printing without images.');
                var content = div.innerHTML;
                var myWindow = window.open('', '', 'height=800,width=800');
                myWindow.document.write('<html><head><title>Print Preview</title></head><body>');
                myWindow.document.write(content);
                myWindow.document.write('</body></html>');
                myWindow.document.close();
                setTimeout(() => {
                    myWindow.focus();
                    myWindow.print();
                    myWindow.close();
                }, 500);
            });
        }

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

        document.addEventListener('DOMContentLoaded', function() {
            // Handle single hall accessories
            const checkboxes = document.querySelectorAll('input[name="accessorie[]"]');
            checkboxes.forEach(function(cb) {
                const li = cb.closest('li');
                const label = li ? li.querySelector('label') : null;
                if (label) {
                    const name = label.textContent.trim();
                    if (cb.checked && (name === 'Stage Chairs' || name === 'Hall chairs')) {
                        toggleChairContainer(name, true);
                    }
                }
            });

            // Handle multi-hall accessories
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
    </script>


@endsection
