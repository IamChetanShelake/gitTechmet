@extends('admin.layout.masteradmin')

@section('content')
    <div class="container-fluid py-4">
        <div class="col-lg-10 mx-auto">
            <div class="card shadow-lg border-0">
                <!-- Card Header -->
                <div class="card-header bg-gradient-dark text-white text-center py-3">
                    <h4 class="mb-0" style="color: #fff;display:inline">Hall Enquiry Details</h4>
                    <button style="float: right;" onclick="printDiv('printableArea')">Print</button>
                </div>


                <!-- Card Body -->
                <div class="card-body px-5 py-4">
                    <div id="printableArea">
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
                                            href="tel:{{ $hallenquirie->contact_no }}">{{ $hallenquirie->contact_no }}</a></p>
                                    <p><strong>Address:</strong> {{ $hallenquirie->address ?? 'Not Provided' }}</p>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-6">
                                <p class="fw-bold mb-2 text-uppercase">Event Details</p>
                                <div class="border rounded p-3">
                                    <p><strong>Referred By:</strong> {{ $hallenquirie->referred_by ?? 'Not Provided' }}</p>
                                    <p><strong>Event Type:</strong> {{ $hallenquirie->event_type }}</p>
                                    <p><strong>Event Date:</strong> {{ $hallenquirie->event_date }}</p>
                                    <p><strong>Hall:</strong> {{ $hallenquirie->hall }}</p>
                                    <p><strong>Duration:</strong> {{ $hallenquirie->duration }}</p>
                                    <p><strong>Expected Audience:</strong> {{ $hallenquirie->expected_audience }}</p>
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
                        <div>
                            <p class="fw-bold mb-2 text-uppercase">Sign Image</p>
                                        @if ($hallenquirie->sign_image)
                                            <div class="border rounded p-3">
                                                <img src="{{ asset('sign_images/' . $hallenquirie->sign_image) }}" alt="Sign Image" style="max-width: 200px;">
                                            </div>
                                        @else
                                            <p>No sign image uploaded.</p>
                                        @endif
                        </div>
                    </div>
                    <hr>



                    @if ($hallenquirie->status == 'pending')
                        <form action="{{ route('Admin.StoreOffice', $hallenquirie->id) }}" method="POST"
                            class="p-4 border rounded-3 shadow-sm bg-light">
                            @csrf

                            <h4 class="mb-3 text-primary"><strong>For Office Use Only</strong></h4>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="p-3 border rounded-3 shadow-sm bg-white">
                                        <label for="rent_amount" class="fw-bold text-dark mb-2">Rent Amount (Rs):</label>
                                        <input type="text" name="rent_amount" value="{{ old('rent_amount') }}"
                                            placeholder="Enter Amount"
                                            class="form-control border border-secondary rounded-2 shadow-sm ps-3 @error('rent') is-invalid @enderror"
                                            required>
                                        @error('rent_amount')
                                            <div class="invalid-feedback">{{$message}}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <div class="p-3 border rounded-3 shadow-sm bg-white">
                                        <label for="deposit" class="fw-bold text-dark mb-2">Deposit Amount (Rs):</label>
                                        <input type="text" name="deposit" value="{{ old('deposit') }}"
                                            placeholder="Enter Deposit Amount"
                                            class="form-control border border-secondary rounded-2 shadow-sm ps-3 @error('deposit') is-invalid @enderror"
                                            required>
                                        @error('deposit')
                                            <div class="invalid-feedback">{{$message}}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <div class="p-3 border rounded-3 shadow-sm bg-white">
                                        <label for="special_note" class="fw-bold text-dark mb-2">Special Note:</label>
                                        <textarea name="special_note" placeholder="Enter Special Note"
                                            class="form-control border border-secondary rounded-2 shadow-sm ps-3 @error('special_note') is-invalid @enderror">{{ old('special_note') }}</textarea>
                                        @error('special_note')
                                            <div class="invalid-feedback">{{$message}}</div>
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
                                            <div class="invalid-feedback">{{$message}}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                {{-- <div class="col-md-6 mb-3">
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
                                </div> --}}
                                <div class="col-md-6 mb-3" style="text-align: center; margin-left: 235px;">
                                    <div class="p-3 border rounded-3 shadow-sm bg-white" >
                                        <label for="event_setup" class="fw-bold text-dark mb-2">Event Setup:</label>
                                        <input type="text" placeholder="Enter Event Setup" name="event_setup"
                                            value="{{ old('event_setup') }}"
                                            class="form-control border border-secondary rounded-2 shadow-sm ps-3 @error('event_setup') is-invalid @enderror">
                                        @error('event_setup')
                                            <div class="invalid-feedback">{{$message}}</div>
                                        @enderror
                                    </div>
                                </div>
                                {{-- <div class="col-md-6 mb-3">
                                    <div class="p-3 border rounded-3 shadow-sm bg-white">
                                        <label class="fw-bold text-dark mb-2">Vendor Services:</label><br>

                                        <div class="form-check form-check-inline mt-2">
                                            <input class="form-check-input" type="checkbox" name="vendor[]"
                                                value="event"
                                                {{ is_array(old('vendor', json_decode($hallEnquiry->vendor ?? '[]', true))) && in_array('event', old('vendor', json_decode($hallEnquiry->vendor ?? '[]', true))) ? 'checked' : '' }}>
                                            <label class="form-check-label">Event</label>
                                        </div>

                                        <div class="form-check form-check-inline mt-2">
                                            <input class="form-check-input" type="checkbox" name="vendor[]"
                                                value="catering"
                                                {{ is_array(old('vendor', json_decode($hallEnquiry->vendor ?? '[]', true))) && in_array('catering', old('vendor', json_decode($hallEnquiry->vendor ?? '[]', true))) ? 'checked' : '' }}>
                                            <label class="form-check-label">Catering</label>
                                        </div>
                                    </div>
                                </div> --}}
                            </div>

                            {{-- <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="p-3 border rounded-3 shadow-sm bg-white">
                                        <label class="fw-bold text-dark mb-2">Vendor Services:</label><br>

                                        <div class="form-check form-check-inline mt-2">
                                            <input class="form-check-input" type="checkbox" name="vendor[]"
                                                value="event"
                                                {{ is_array(old('vendor', json_decode($hallEnquiry->vendor ?? '[]', true))) && in_array('event', old('vendor', json_decode($hallEnquiry->vendor ?? '[]', true))) ? 'checked' : '' }}>
                                            <label class="form-check-label">Event</label>
                                        </div>

                                        <div class="form-check form-check-inline mt-2">
                                            <input class="form-check-input" type="checkbox" name="vendor[]"
                                                value="catering"
                                                {{ is_array(old('vendor', json_decode($hallEnquiry->vendor ?? '[]', true))) && in_array('catering', old('vendor', json_decode($hallEnquiry->vendor ?? '[]', true))) ? 'checked' : '' }}>
                                            <label class="form-check-label">Catering</label>
                                        </div>
                                    </div>
                                </div>
                            </div> --}}

                            <!-- ✅ New Checkboxes for Name, Quantity, Price, and Hours -->
                            {{-- <div class="row">
                                <h4 class="mb-3 text-secondary"><strong>Accessories Details:</strong></h4>
                                @foreach ($accessories as $accessorie)
                                    @if (
                                        !empty($accessorie->name) ||
                                            !empty($accessorie->quantity) ||
                                            !empty($accessorie->price) ||
                                            !empty($accessorie->hours))
                                        <div class="col-md-4">
                                            <div class="card shadow-sm border-0 rounded-lg mb-3">
                                                <div class="card-body">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="accessorie[]"
                                                            value="{{ $accessorie->id }}"
                                                            {{ is_array(old('accessorie', json_decode($hallenquirie->accessories ?? '[]', true))) && in_array($accessorie->id, old('accessorie', json_decode($hallenquirie->accessorie ?? '[]', true))) ? 'checked' : '' }}>

                                                        <label class="form-check-label font-weight-bold">
                                                            {{ $accessorie->name ?? 'Unknown Accessory' }}
                                                        </label>
                                                    </div>
                                                    <ul class="list-unstyled mt-2">
                                                        @if (!empty($accessorie->quantity))
                                                            <li><strong>Qty:</strong> {{ $accessorie->quantity }}</li>
                                                        @endif
                                                        @if (!empty($accessorie->price))
                                                            <li><strong>Price:</strong> ₹{{ $accessorie->price }}</li>
                                                        @endif
                                                        @if (!empty($accessorie->hours))
                                                            <li><strong>Hours:</strong> {{ $accessorie->hours }}</li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div> --}}
                            <div class="row">
                                <h4 class="mb-3 text-secondary"><strong>Accessories Details:</strong></h4>
                                <ul class="list-group w-100">
                                    @foreach ($accessories as $accessorie)
                                        @if (!empty($accessorie->name) || !empty($accessorie->quantity) || !empty($accessorie->price) || !empty($accessorie->hours))
                                            <li class="list-group-item d-flex align-items-center">
                                                <div class="d-flex align-items-center">
                                                    <input class="form-check-input custom-checkbox me-2" type="checkbox" name="accessorie[]"
                                                        value="{{ $accessorie->id }}"
                                                        {{ is_array(old('accessorie', json_decode($hallenquirie->accessories ?? '[]', true))) && in_array($accessorie->id, old('accessorie', json_decode($hallenquirie->accessorie ?? '[]', true))) ? 'checked' : '' }}>

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
                            </div>



                            <!-- Add this CSS for better checkbox visibility -->
                            <style>
                                .custom-checkbox {
                                    border: 2px solid #333;
                                }
                            </style>

                            <div class="d-flex justify-content-center mt-4">
                                {{-- <button type="submit" class="btn btn-primary px-4 py-2">Save</button> --}}
                                <button type="submit" class="btn btn-primary px-4 py-2">Save & Generate Quotation</button>
                                {{-- <a href="{{ route('AdminHallEnquiry') }}" class="btn btn-secondary ms-2 px-4 py-2">Back</a> --}}
                            </div>
                        </form>

                    @else
                        <!-- Right Column -->
                        <div class="row">
                        <div class="col-md-6">
                            <p class="fw-bold mb-2 text-uppercase">Office Details</p>
                            <div class="border rounded p-3">
                                {{-- <p><strong>Referred By:</strong> {{ $hallenquirie->referred_by ?? 'Not Provided' }}</p> --}}
                                <p><strong>Rent Amount:</strong> {{ $hallenquirie->rent_amount }}</p>
                                <p><strong>Total Deposit:</strong> {{ $hallenquirie->deposit }}</p>
                                <p><strong>Special Note:</strong> {{ $hallenquirie->special_note }}</p>
                                <p><strong>ID Proof:</strong> {{ $hallenquirie->Id_proof }}</p>
                                <p><strong>Event Setup:</strong> {{ $hallenquirie->event_setup }}</p>
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
                                        {{-- {{ $accessory }}  --}}
                                        <strong>{{ $accessory }}</strong><br>
                                    @endforeach
                                @else
                                    N/A
                                @endif
                            </div>

                        </div>
                    </div>
                </div>
                @endif




                    <!-- FOR OFFICE USE ONLY -->
                    {{-- <form action="{{ route('Admin.StoreOffice', $hallenquirie->id) }}" method="POST" class="p-4 border rounded-3 shadow-sm bg-light">
                    @csrf

                    <h4 class="mb-3 text-primary"><strong>For Office Use Only</strong></h4>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="p-3 border rounded-3 shadow-sm bg-white">
                                <label for="rent" class="fw-bold text-dark mb-2">Rent Amount (Rs):</label>
                                <input type="text" name="rent" value="{{ old('rent') }}" placeholder="Enter Amount"
                                    class="form-control border border-secondary rounded-2 shadow-sm ps-3 @error('rent') is-invalid @enderror" required>
                                @error('rent')
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
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="p-3 border rounded-3 shadow-sm bg-white">
                                <label for="id_proof" class="fw-bold text-dark mb-2">ID Proof:</label>
                                <select name="id_proof"
                                    class="form-control border border-secondary rounded-2 shadow-sm ps-3 @error('id_proof') is-invalid @enderror" required>
                                    <option value="" disabled {{ old('id_proof') == '' ? 'selected' : '' }}>-- Select ID Proof --</option>
                                    <option value="Aadhar Card" {{ old('id_proof') == 'Aadhar Card' ? 'selected' : '' }}>Aadhar Card</option>
                                    <option value="Driving Licence" {{ old('id_proof') == 'Driving Licence' ? 'selected' : '' }}>Driving License</option>
                                </select>
                                @error('id_proof')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="p-3 border rounded-3 shadow-sm bg-white">
                                <label for="event_setup" class="fw-bold text-dark mb-2">Event Setup:</label>
                                <input type="text" placeholder="Enter Event Setup" name="event_setup"
                                    value="{{ old('event_setup') }}"
                                    class="form-control border border-secondary rounded-2 shadow-sm ps-3 @error('event_setup') is-invalid @enderror">
                                @error('event_setup')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="p-3 border rounded-3 shadow-sm bg-white">
                                <label class="fw-bold text-dark mb-2">Catering Services:</label><br>
                                <div class="form-check form-check-inline mt-2">
                                    <input class="form-check-input" type="checkbox" name="tea_snacks" value="1"
                                        {{ old('tea_snacks') ? 'checked' : '' }}>
                                    <label class="form-check-label">Tea/Snacks</label>
                                </div>
                                <div class="form-check form-check-inline mt-2">
                                    <input class="form-check-input" type="checkbox" name="lunch_dinner" value="1"
                                        {{ old('lunch_dinner') ? 'checked' : '' }}>
                                    <label class="form-check-label">Lunch/Dinner</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-center mt-4">
                        <button type="submit" class="btn btn-primary px-4 py-2">Save</button>
                        <a href="{{ route('AdminHallEnquiry') }}" class="btn btn-secondary ms-2 px-4 py-2">Back</a>
                    </div>
                </form> --}}




                <!-- Action Buttons -->
                {{-- <div class="text-center mt-4">
                    <a href="{{ route('AdminHallEnquiry') }}" class="btn btn-secondary px-4 me-2">Back</a>
                    <a href="{{ route('admin.hall-enquiry.edit', $hallenquirie->id) }}" class="btn btn-success px-4 me-2">Edit</a>
                    <form action="{{ route('admin.hall-enquiry.destroy', $hallenquirie->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger px-4" onclick="return confirm('Are you sure you want to delete this enquiry?')">
                            Delete
                        </button>
                    </form>
                </div> --}}
            </div>
            <div style="text-align: center;">
                <a href="{{ route('AdminHallEnquiry') }}" class="btn btn-secondary" style="width: 100px;">Back</a>
            </div>
        </div>
    </div>

{{-- <script>
    function printDiv(divId) {
    var div = document.getElementById(divId).cloneNode(true);
    var imgs = div.querySelectorAll('img');
    var promises = [];

    // Convert all image src to base64 or absolute URLs
    imgs.forEach(function(img) {
        if (img.src) {
            // Convert to absolute URL
            var absUrl = new URL(img.src, window.location.origin).href;
            img.src = absUrl;

            // Create a promise for fetching and converting the image to base64
            var promise = fetch(absUrl)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.blob();
                })
                .then(blob => {
                    return new Promise((resolve) => {
                        var reader = new FileReader();
                        reader.onloadend = function() {
                            img.src = reader.result; // Set base64 data URL
                            resolve();
                        };
                        reader.readAsDataURL(blob);
                    });
                })
                .catch(error => {
                    console.error('Error fetching image for print:', error);
                    resolve(); // Continue even if one image fails
                });

            promises.push(promise);
        }
    });

    // Wait for all image conversions to complete
    Promise.all(promises).then(() => {
        var content = div.innerHTML;
        var myWindow = window.open('', '', 'height=800,width=800');
        myWindow.document.write('<html><head><title>Print Preview</title>');
        myWindow.document.write(`
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 20px;
                    color: #333;
                }
                .border {
                    border: 1px solid #ccc;
                    padding: 10px;
                    margin-bottom: 15px;
                    border-radius: 4px;
                }
                p {
                    margin: 5px 0;
                    font-size: 14px;
                }
                .fw-bold {
                    font-weight: bold;
                    text-transform: uppercase;
                }
                .p-3 {
                    padding: 1rem;
                }
                .badge {
                    padding: 5px 10px;
                    border-radius: 4px;
                    font-size: 12px;
                    color: #fff;
                    display: inline-block;
                }
                .bg-warning { background-color: #f0ad4e; }
                .bg-success { background-color: #5cb85c; }
                .bg-danger { background-color: #d9534f; }
                img {
                    width: auto;
                    height: auto;
                    max-width: 100%;
                    vertical-align: top;
                }
            </style>
        `);
        myWindow.document.write('</head><body>');
        myWindow.document.write(content);
        myWindow.document.write('</body></html>');
        myWindow.document.close();
        myWindow.focus();
        myWindow.print();
        myWindow.close();
    });
}
</script> --}}


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
                body {
                    font-family: Arial, sans-serif;
                    margin: 20px;
                    color: #333;
                }
                .border {
                    border: 1px solid #ccc;
                    padding: 10px;
                    margin-bottom: 15px;
                    border-radius: 4px;
                }
                p {
                    margin: 5px 0;
                    font-size: 14px;
                }
                .fw-bold {
                    font-weight: bold;
                    text-transform: uppercase;
                }
                .p-3 {
                    padding: 1rem;
                }
                .badge {
                    padding: 5px 10px;
                    border-radius: 4px;
                    font-size: 12px;
                    color: #fff;
                    display: inline-block;
                }
                .bg-warning { background-color: #f0ad4e; }
                .bg-success { background-color: #5cb85c; }
                .bg-danger { background-color: #d9534f; }
                img {
                    width: auto;
                    height: auto;
                    max-width: 100%;
                    vertical-align: top;
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
</script>


@endsection
