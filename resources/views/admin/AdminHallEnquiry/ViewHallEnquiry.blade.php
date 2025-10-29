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
                                    <img src="{{ asset('sign_images/' . $hallenquirie->sign_image) }}" alt="Sign Image"
                                        style="max-width: 200px;">
                                </div>
                            @else
                                <p>No sign image uploaded.</p>
                            @endif
                        </div>

                        <!-- Print-only Footer -->
                        <div class="d-none d-print-block">
                            <div style="display: flex; justify-content: space-between; margin-top: 50px; padding: 20px 0;">
                                <div style="text-align: center; flex: 1; margin-right: 20px;">
                                    <p class="mb-0 fw-bold">Executive Officer</p>
                                    <div style="border-top: 1px solid #333; width: 200px; margin: 50px auto 10px;"></div>
                                    <p class="mt-2">Signature & Stamp</p>
                                </div>
                                <div style="text-align: center; flex: 1; margin-left: 20px;">
                                    <p class="mb-0 fw-bold">Approved by</p>
                                    <p class="mb-0">(Gurudakshina Administrative Committee)</p>
                                    <div style="border-top: 1px solid #333; width: 200px; margin: 50px auto 10px;"></div>
                                    <p class="mt-2">Signature & Stamp</p>
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

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="p-3 border rounded-3 shadow-sm bg-white">
                                        <label for="rent_amount" class="fw-bold text-dark mb-2">Rent Amount (Rs):</label>
                                        <input type="text" name="rent_amount" value="{{ old('rent_amount') }}"
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
                                        <input type="text" name="deposit" value="{{ old('deposit') }}"
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
                                            value="{{ old('event_setup') }}"
                                            class="form-control border border-secondary rounded-2 shadow-sm ps-3 @error('event_setup') is-invalid @enderror">
                                        @error('event_setup')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                            </div>

                            <div class="row">
                                <h4 class="mb-3 text-secondary"><strong>Accessories Details:</strong></h4>
                                {{-- <div class="row mb-3">
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
                                </div> --}}
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
                                                        {{ is_array(old('accessorie', json_decode($hallenquirie->accessories ?? '[]', true))) && in_array($accessorie->id, old('accessorie', json_decode($hallenquirie->accessorie ?? '[]', true))) ? 'checked' : '' }}>

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
                            <div class="col-md-6">
                                <p class="fw-bold mb-2 text-uppercase">Office Details</p>
                                <div class="border rounded p-3">
                                    {{-- <p><strong>Referred By:</strong> {{ $hallenquirie->referred_by ?? 'Not Provided' }}</p> --}}
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

        function toggleChairContainer(name, checked) {
            if (name === 'Stage Chairs') {
                document.getElementById('stage_chairs_container').style.display = checked ? 'block' : 'none';
            } else if (name === 'Hall chairs') {
                document.getElementById('hall_chairs_container').style.display = checked ? 'block' : 'none';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
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
        });
    </script>


@endsection
