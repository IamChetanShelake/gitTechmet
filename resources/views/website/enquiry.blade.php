@extends('website.layout.master')

@section('content')
<style>
    #signature-pad {
    touch-action: none;
    width: 100%;   /* For responsive width */
    height: 200px; /* Slightly taller for mobile fingers */
}

    /* Custom button styles */
    .custom-btn {
        padding: 6px 12px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        text-decoration: none;
        display: inline-block;
        transition: all 0.2s ease;
        background-color: #fff;
        color: #495057;
        margin: 2px;
    }

    .custom-btn:hover {
        text-decoration: none;
        color: #495057;
    }

    .custom-btn-primary {
        border-color: #007bff;
        color: #007bff;
    }

    .custom-btn-primary:hover {
        background-color: #007bff;
        color: white;
    }

    .custom-btn-danger {
        border-color: #dc3545;
        color: #dc3545;
    }

    .custom-btn-danger:hover {
        background-color: #dc3545;
        color: white;
    }

    /* Modal styles */
    .modal {
        z-index: 1055 !important;
    }

    .modal-dialog {
        z-index: 1060 !important;
    }

    .modal-content {
        z-index: 1065 !important;
        position: relative;
    }

    /* Custom backdrop for modal */
    .modal-backdrop-custom {
        position: fixed;
           top: -81px;
    left: 0;
    width: 100%;
    height: 167vh;
        background-color: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(2px);
        z-index: 1040;
        display: none;
    }

    .modal-backdrop-custom.show {
        display: block;
    }

    /* Ensure modal content is always on top */
    #rulesModal.show .modal-content {
        z-index: 1070 !important;
    }
</style>
    <!-- content begin -->
    <div class="no-bottom no-top" id="content">

        <div id="top"></div>

        <section id="subheader" class="relative jarallax text-light">
            <img src="{{ asset('website/assets/images/background/Background.jpg') }}" class="jarallax-img" alt="">
            <div class="container relative z-index-1000">
                <div class="row justify-content-center">
                    <div class="col-lg-6 text-center">
                        <h1>Enquiry Now</h1>
                        <p class="mt-3 lead">Ready to host an unforgettable event? Book your hall now and step into a world of elegance and sophistication. Your perfect venue is just a click away!</p>
                        <ul class="crumb">
                            <li><a href="{{ route('Index.Page') }}">Home</a></li>
                            <li class="active">Enquiry Now</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="de-overlay"></div>
        </section>



        <div class="p-3">
                    @if(session('success'))
                        <div class="alert alert-success" id="successMessage">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <h5>Please fix the following errors:</h5>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

                <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                <script>
                    $(document).ready(function() {
                        setTimeout(function() {
                            $("#successMessage").fadeOut('slow');
                        }, 3000);
                    });
                </script>

        <section id="section_form" class="relative lines-deco">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8 offset-lg-2">
                        <div id="success_message" class="text-center">
                            <h2>Your reservation has been sent successfully.</h2>
                            <div class="col-lg-8 offset-lg-2">
                                <p>We will contact you shortly. Refresh this page if you want to make another reservation.
                                </p>

                                <img src="{{ asset('website/assets/images/misc/2.webp') }}" class="w-100 rounded-up-100"
                                    alt="">
                            </div>
                        </div>

                        {{-- <div id="booking_form_wrap">
                            <div class="container card" style="background-color: #fff5ed">
                            <form name="contactForm" id='booking_form' class="form-border" method="post"
                                action="booking.php">

                                    {{-- <div class="card mt-4" style="border: 2px solid #a88465;">
                                        <div style="padding: 50px;">
                                            <h2>Hall Enquiry Form</h2>

                                            <div id="step-2" class="row g-4">
                                                <h4>Enter your details</h4>
                                                <form method="POST" action="{{ url('/hall-enquiry') }}">
                                                    @csrf
                                                    <div class="col-md-6">
                                                        <div>
                                                            <input type='text' name='name' id='name'
                                                                class="form-control" placeholder="Name of Person" required>
                                                        </div>
                                                        <div>
                                                            <input type='text' name='organization' id='organization'
                                                                class="form-control" placeholder="Name of Organization">
                                                        </div>
                                                        <div>
                                                            <input type='text' name='gst_no' id='gst_no'
                                                                class="form-control" placeholder="GST No.">
                                                        </div>
                                                        <div>
                                                            <input type='email' name='email' id='email'
                                                                class="form-control" placeholder="Email Address" required>
                                                        </div>
                                                        <div>
                                                            <input type='text' name='contact_no' id='contact_no'
                                                                class="form-control" placeholder="Contact No." required>
                                                        </div>
                                                        <div>
                                                            <input type='text' name='address' id='address'
                                                                class="form-control" placeholder="Address" required>
                                                        </div>
                                                        <div>
                                                            <input type='text' name='referred_by' id='referred_by'
                                                                class="form-control" placeholder="Referred By">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h4>Event Details</h4>
                                                        <div>
                                                            <input type='text' name='event_type' id='event_type'
                                                                class="form-control" placeholder="Type of Event" required>
                                                        </div>
                                                        <div>
                                                            <label>Select Type</label>
                                                            <select name='hall' id='hall' class="form-control" required>
                                                                <option value="">-- Select Type --</option>
                                                                @foreach ($halls as $hall)
                                                                <option value="{{ $hall->id }}">{{ $hall->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div>
                                                            <input type='date' name='event_date' id='event_date'
                                                                class="form-control" required>
                                                        </div>
                                                        <div>
                                                            <select name='duration' id='duration' class="form-control"
                                                                required>
                                                                <option value="full_day">Full Day</option>
                                                                <option value="half_day_morning">Half Day (Morning)</option>
                                                                <option value="half_day_evening">Half Day (Evening)</option>
                                                            </select>

                                                        </div>
                                                        <div>
                                                            <label for="start_time">Start Time</label>
                                                            <input type="time" name="start_time" id="start_time" class="form-control" required>
                                                        </div>
                                                        <div>
                                                            <label for="end_time">End Time</label>
                                                            <input type="time" name="end_time" id="end_time" class="form-control" required>
                                                        </div>
                                                        <div>
                                                            <input type='number' name='expected_audience'
                                                                id='expected_audience' class="form-control"
                                                                placeholder="Expected No. of Audience" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <p id='submit'>
                                                            <input type='submit' id='send_message' value='Submit Form'
                                                                class="btn-main">
                                                        </p>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                </form>
                            </div>
                            <div id='error_message' class='error'>Sorry, error occured this time sending your message.
                            </div>
                        </div> --}}




                        <div id="booking_form_wrap">
                            <div class="container card p-4 shadow-sm" style="background-color: #fff5ed; border-radius: 10px;">
                                <h2 class="text-center mb-4">Hall Enquiry Form</h2>

                                <form name="contactForm" class="form-border" method="POST" action="{{ route('enquiry.store') }}" enctype="multipart/form-data">
                                    @csrf

                                    <div class="row g-4">
                                        <!-- Left Column -->
                                        <div class="col-md-6">
                                            <h4>Enter Your Details</h4>
                                            <div class="mb-3">
                                                <input type="text" name="name" id="name" class="form-control" placeholder="Name of Person *" value="{{ old('name') }}" required>
                                                @error('name')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <input type="text" name="organization" id="organization" class="form-control" placeholder="Name of Organization *" value="{{ old('organization') }}">
                                                @error('organization')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <input type="text" name="gst_no" id="gst_no" class="form-control" placeholder="GST No." value="{{ old('gst_no') }}">
                                            </div>

                                            <div class="mb-3">
                                                <input type="email" name="email" id="email" class="form-control" placeholder="Email Address *" value="{{ old('email') }}" required>
                                                @error('email')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <input type="text" name="contact_no" id="contact_no" class="form-control" placeholder="Contact No. *" value="{{ old('contact_no') }}" required>
                                                @error('contact_no')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <input type="text" name="address" id="address" class="form-control" placeholder="Address" value="{{ old('address') }}">
                                            </div>

                                            <div class="mb-3">
                                                <input type="text" name="referred_by" id="referred_by" class="form-control" placeholder="Referred By" value="{{ old('referred_by') }}">
                                            </div>

                                            <!-- Vendor Services Section -->
                                            <div>
                                                <h5>Our Vendor Services:</h5>
                                                    <ul class="list-unstyled" style="font-size: 18px; line-height: 2;padding-left: 10px;margin-bottom: 0px;">
                                                        <li><i class="fa fa-calendar-check-o text-primary me-2"></i>Event Services</li>
                                                        <li><i class="fa fa-utensils text-primary me-2"></i>Catering Services</li>
                                                        <li><i class="fa fa-camera text-primary me-2"></i>Photography</li>
                                                    </ul>
                                            </div>



                                        </div>

                                        <!-- Right Column -->
                                        <div class="col-md-6">
                                            <h4>Event Details *</h4>
                                            <div class="mb-3">
                                                <input type="text" name="event_type" id="event_type" class="form-control" placeholder="Type of Event *" value="{{ old('event_type') }}" required>
                                                @error('event_type')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>



                                            <div class="mb-3" style="background-color: #fff !important;">
                                                <label class="form-label">Select Halls *</label>
                                                <div id="hall-selection" style="padding-left: 20px;">
                                                    @foreach ($halls as $hall)
                                                        <div class="form-check">
                                                            <input class="form-check-input hall-checkbox" type="checkbox"
                                                                   name="selected_halls[]" value="{{ $hall->id }}"
                                                                   id="hall_{{ $hall->id }}"
                                                                   {{ in_array($hall->id, old('selected_halls', [])) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="hall_{{ $hall->id }}">
                                                                {{ $hall->name }}
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                @error('selected_halls')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>



                                            <div class="mb-3">
                                                <label for="signature_type" class="form-label">Select Signature Type *</label>
                                                <select name="signature_type" id="signature_type" class="form-select" required>
                                                    <option value="">-- Select --</option>
                                                    <option value="image">Upload Signature Image</option>
                                                    <option value="text">Type Signature (Name)</option>
                                                    <option value="draw">Draw Digital Signature</option>
                                                </select>
                                            </div>

                                            <div class="mb-3" id="upload-signature" style="display: none;">
                                                <label for="sign_image" class="form-label">Upload Signature Image *</label>
                                                <input type="file" name="sign_image" id="sign_image" class="form-control" accept="image/*">
                                                @error('sign_image')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3" id="text-signature" style="display: none;">
                                                <label for="typed_signature" class="form-label">Type Signature (Your Name) *</label>
                                                <input type="text" name="typed_signature" id="typed_signature" class="form-control">
                                                @error('typed_signature')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3" id="draw-signature" style="display: none;">
                                                <label class="form-label">Draw Signature *</label>
                                                <div style="border: 1px solid #ced4da; border-radius: 0.25rem; height: 150px; background-color: #fff;">
                                                    <canvas id="signature-pad" style="width: 100%; height: 100%; touch-action: none;"></canvas>
                                                </div>
                                                <div id="draw-signature-error" class="text-danger" style="display: none;">Please draw your signature.</div>
                                                <input type="hidden" name="digital_signature" id="digital-signature">
                                                <button type="button" style="background-color: gray; color: white;font-size:14px;" id="clear-signature">Clear Signature</button>
                                            </div>




                                        </div>



                                        <!-- Hall Details Table -->
                                        <div class="col-mb-12">
                                            <div id="hall-details-container" class="mt-4">
                                                <h5>Hall Details</h5>
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
                                                        <button type="button" style="background-color: white; border: 1px solid #007bff; color: #007bff; padding: 2px 8px; font-size: 12px; border-radius: 3px; cursor: pointer;" class="add-date">+ Add Date</button>
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
                                                                <option value="full_day">Full Day </option>
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
                                                        <button type="button" style="background-color: white; border: 1px solid #dc3545; color: #dc3545; padding: 4px 12px; font-size: 12px; border-radius: 3px; cursor: pointer;" class="remove-hall">Remove</button>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>

                                        <!-- Agreement Checkbox -->
                                        <div class="col-md-12 text-center mt-3">
                                            <div class="form-check mb-3">
                                                <input type="checkbox" class="form-check-input" id="agreement" name="agreement">
                                                <label class="form-check-label" for="agreement">
                                                    I have read, understood, and agreed to the <a href="#" id="rules-link" style="color: #007bff; text-decoration: underline;">rules and regulations</a> for <span id="hall-name">___</span> hall. Failing which booking shall be cancelled without prior notice and no refund claim will be entertained. This enquiry form is not a final booking or confirmation. Confirmation will be communicated on given contact no. or e-mail id.
                                                </label>
                                            </div>
                                            <button type="submit" id="send_message" style="background-color: #AB8965; color: white; height: 40px; width: 100px;">Submit</button>
                                        </div>

                                        <!-- Custom Backdrop -->
                                        <div class="modal-backdrop-custom" id="customBackdrop"></div>

                                        <!-- Rules and Regulations Modal -->
                                        <div class="modal fade" id="rulesModal" tabindex="-1" role="dialog" aria-labelledby="rulesModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-lg" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="rulesModalLabel">Rules and Regulations</h5>
                                                        <button type="button" class="close" id="closeModalBtn" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
                                                        <h6>General Rules and Regulations for Hall Booking:</h6>
                                                        <ol>
                                                            <li>All bookings are subject to availability and confirmation by the management.</li>
                                                            <li>Full payment must be made at the time of booking confirmation.</li>
                                                            <li>Cancellation charges will apply as per the policy mentioned in the booking confirmation.</li>
                                                            <li>The hall must be used only for the purpose specified in the booking.</li>
                                                            <li>Any damage to the hall property or equipment will be charged to the customer.</li>
                                                            <li>Decorations must be approved by the management in advance.</li>
                                                            <li>Noise levels must be maintained within permissible limits.</li>
                                                            <li>Smoking and consumption of alcohol is strictly prohibited inside the premises.</li>
                                                            <li>The customer is responsible for the behavior of all guests and attendees.</li>
                                                            <li>Management reserves the right to cancel any booking without prior notice in case of emergencies.</li>
                                                            <li>Parking is available on a first-come, first-served basis.</li>
                                                            <li>Outside catering is allowed only with prior permission from the management.</li>
                                                            <li>The hall should be vacated by the agreed time; late checkout may incur additional charges.</li>
                                                            <li>All waste must be disposed of properly; cleaning charges may apply if the hall is left in an unacceptable condition.</li>
                                                            <li>The management is not responsible for any loss of personal belongings.</li>
                                                        </ol>

                                                        <h6>Additional Terms:</h6>
                                                        <ul>
                                                            <li>This enquiry form does not constitute a confirmed booking.</li>
                                                            <li>Final confirmation will be communicated via phone or email.</li>
                                                            <li>All terms and conditions are subject to change without notice.</li>
                                                            <li>By agreeing to these rules, you acknowledge that you have read and understood all terms.</li>
                                                        </ul>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" style="background-color: gray; color: white; height: 40px; width: 100px;" id="closeModalBtn2">Close</button>
                                                        <button type="button" style="background-color: #AB8965; color: white; height: 40px; width: 100px;" id="agreeBtn">I Agree</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>

                            </div>

                            <!-- Error Message -->
                            <div id="error_message" class="text-danger text-center mt-3" style="display: none;">
                                Sorry, an error occurred while sending your message.
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </section>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Hall selection functionality
            const hallCheckboxes = document.querySelectorAll('.hall-checkbox');
            const hallDetailsTable = document.getElementById('hall-details-table');
            const hallTableBody = document.getElementById('hall-table-body');
            const hallRowTemplate = document.getElementById('hall-row-template');
            const hallsData = @json($halls->pluck('name', 'id'));
            const hallRulesData = @json($hallRules ?? []);

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
                    // For other halls: show morning, afternoon, evening with custom start/end times
                    const durationSection = row.querySelector('.hall-duration-section');
                    durationSection.style.display = 'block';
                    sessionSection.style.display = 'none';
                    timeSection.style.display = 'block'; // Show time inputs for custom slots

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

            // Function to update agreement hall names
            function updateAgreementHallNames() {
                const selectedHalls = document.querySelectorAll('.hall-checkbox:checked');
                const hallNameSpan = document.getElementById('hall-name');

                if (selectedHalls.length === 0) {
                    hallNameSpan.textContent = '___';
                } else if (selectedHalls.length === 1) {
                    const hallId = selectedHalls[0].value;
                    hallNameSpan.textContent = hallsData[hallId];
                } else {
                    // Multiple halls selected
                    const hallNames = Array.from(selectedHalls).map(checkbox => hallsData[checkbox.value]);
                    hallNameSpan.textContent = hallNames.join(', ');
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

                    // Update agreement hall names
                    updateAgreementHallNames();
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
                        <input type="date" name="hall_details[${hallId}][event_dates][]" class="form-control form-control-sm hall-event-date" required>
                        <button type="button" style="background-color: white; border: 1px solid #dc3545; color: #dc3545; padding: 1px 5px; font-size: 12px; border-radius: 3px; cursor: pointer; margin-left: 2px;" class="remove-date-btn">×</button>
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

            // Initialize existing selections (for form validation errors)
            const selectedHalls = @json(old('selected_halls', []));
            const oldHallDetails = @json(old('hall_details', []));

            selectedHalls.forEach(hallId => {
                const checkbox = document.getElementById(`hall_${hallId}`);
                const hallName = hallsData[hallId];
                if (checkbox && !checkbox.checked) {
                    checkbox.checked = true;
                    const row = createHallTableRow(hallId, hallName);

                    // Repopulate existing hall details if available
                    if (oldHallDetails[hallId]) {
                        const hallData = oldHallDetails[hallId];

                        // Set duration/session
                        const durationSelect = row.querySelector('.hall-duration');
                        const sessionSelect = row.querySelector('.hall-session');
                        if (durationSelect && hallData.duration) {
                            durationSelect.value = hallData.duration;
                        }
                        if (sessionSelect && hallData.session) {
                            sessionSelect.value = hallData.session;
                        }

                        // Set times
                        const startTimeInput = row.querySelector('.hall-start-time');
                        const endTimeInput = row.querySelector('.hall-end-time');
                        if (startTimeInput && hallData.start_time) {
                            startTimeInput.value = hallData.start_time;
                        }
                        if (endTimeInput && hallData.end_time) {
                            endTimeInput.value = hallData.end_time;
                        }

                        // Set audience
                        const audienceInput = row.querySelector('.hall-expected-audience');
                        if (audienceInput && hallData.expected_audience) {
                            audienceInput.value = hallData.expected_audience;
                        }

                        // Vendor services now handled by admin only

                        // Handle event dates - add extra date inputs if there are multiple dates
                        if (hallData.event_dates && Array.isArray(hallData.event_dates)) {
                            const dateContainer = row.querySelector('.date-selection-container');
                            const existingDateInputs = dateContainer.querySelectorAll('.hall-event-date');

                            // Fill existing date inputs first
                            hallData.event_dates.forEach((date, index) => {
                                if (date && date.trim() !== '') {
                                    if (existingDateInputs[index]) {
                                        existingDateInputs[index].value = date;
                                    } else {
                                        // Add new date input if needed
                                        const dateInputGroup = document.createElement('div');
                                        dateInputGroup.className = 'date-input-group mb-1';
                                        dateInputGroup.innerHTML = `
                                            <input type="date" name="hall_details[${hallId}][event_dates][]" class="form-control form-control-sm hall-event-date" value="${date}" required>
                                            <button type="button" style="background-color: white; border: 1px solid #dc3545; color: #dc3545; padding: 1px 5px; font-size: 12px; border-radius: 3px; cursor: pointer; margin-left: 2px;" class="remove-date-btn">×</button>
                                        `;
                                        dateContainer.appendChild(dateInputGroup);
                                    }
                                }
                            });
                        }
                    }
                }
            });

            const signatureType = document.getElementById('signature_type');
            const uploadSignature = document.getElementById('upload-signature');
            const textSignature = document.getElementById('text-signature');
            const drawSignature = document.getElementById('draw-signature');
            const canvas = document.getElementById('signature-pad');
            const ctx = canvas.getContext('2d');
            const clearButton = document.getElementById('clear-signature');
            const form = document.querySelector('form');
            const digitalSignatureInput = document.getElementById('digital-signature');

            let drawing = false;
            let lastPos = { x: 0, y: 0 };

            // Set canvas dimensions
            function resizeCanvas() {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                ctx.scale(ratio, ratio);
            }

            window.addEventListener('resize', resizeCanvas);
            resizeCanvas();

            // Handle signature type change
            signatureType.addEventListener('change', function() {
                uploadSignature.style.display = 'none';
                textSignature.style.display = 'none';
                drawSignature.style.display = 'none';

                // Hide errors
                document.getElementById('draw-signature-error').style.display = 'none';

                if (this.value === 'image') {
                    uploadSignature.style.display = 'block';
                } else if (this.value === 'text') {
                    textSignature.style.display = 'block';
                } else if (this.value === 'draw') {
                    drawSignature.style.display = 'block';
                    resizeCanvas(); // Ensure canvas is properly sized
                }
            });

            // Drawing functions
            function getEventPosition(event) {
                const rect = canvas.getBoundingClientRect();
                let clientX, clientY;
                if (event.touches && event.touches.length > 0) {
                    clientX = event.touches[0].clientX;
                    clientY = event.touches[0].clientY;
                } else {
                    clientX = event.clientX;
                    clientY = event.clientY;
                }
                return { x: clientX - rect.left, y: clientY - rect.top };
            }

            function startDrawing(event) {
                drawing = true;
                lastPos = getEventPosition(event);
                ctx.beginPath();
                ctx.moveTo(lastPos.x, lastPos.y);
                // Draw a small dot for single taps
                ctx.lineTo(lastPos.x + 0.0001, lastPos.y);
                ctx.stroke();
                event.preventDefault();
            }

            function draw(event) {
                if (!drawing) return;
                const pos = getEventPosition(event);
                ctx.beginPath();
                ctx.moveTo(lastPos.x, lastPos.y);
                ctx.lineTo(pos.x, pos.y);
                ctx.strokeStyle = '#000';
                ctx.lineWidth = 2;
                ctx.lineCap = 'round';
                ctx.stroke();
                lastPos = pos;
                event.preventDefault();
            }

            function stopDrawing(event) {
                drawing = false;
                event.preventDefault();
            }

            // Event listeners for drawing
            canvas.addEventListener('mousedown', startDrawing);
            canvas.addEventListener('mousemove', draw);
            canvas.addEventListener('mouseup', stopDrawing);
            canvas.addEventListener('mouseleave', stopDrawing);

            canvas.addEventListener('touchstart', startDrawing);
            canvas.addEventListener('touchmove', draw);
            canvas.addEventListener('touchend', stopDrawing);

            // Clear signature
            clearButton.addEventListener('click', (e) => {
                e.preventDefault();
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                digitalSignatureInput.value = '';
            });

            // Rules and Regulations Modal functionality
            const rulesLink = document.getElementById('rules-link');
            const rulesModal = document.getElementById('rulesModal');
            const customBackdrop = document.getElementById('customBackdrop');
            const agreeBtn = document.getElementById('agreeBtn');
            const closeModalBtn = document.getElementById('closeModalBtn');
            const closeModalBtn2 = document.getElementById('closeModalBtn2');
            const agreementCheckbox = document.getElementById('agreement');

            // Function to generate rules content for selected halls
            function getSelectedHallsRules() {
                const selectedHalls = document.querySelectorAll('.hall-checkbox:checked');
                if (selectedHalls.length === 0) return 'Please select at least one hall to view rules.';

                let content = '';

                selectedHalls.forEach(hallCheckbox => {
                    const hallId = hallCheckbox.value;
                    const hallName = hallsData[hallId];

                    // Find the corresponding rules in hallRulesData
                    const hallRules = hallRulesData[hallName];
                    if (hallRules) {
                        content += `<h6>Rules and Regulations for ${hallName}:</h6>`;
                        content += `<div class="hall-rules-content">${hallRules.replace(/\n/g, '<br>')}</div>`;
                        content += '<hr>';
                    } else {
                        content += `<h6>Rules and Regulations for ${hallName}:</h6>`;
                        content += '<p>Rules and regulations file not found.</p>';
                        content += '<hr>';
                    }
                });

                // Add general terms at the end
                content += `
                    <h6>Additional General Terms:</h6>
                    <ul>
                        <li>This enquiry form does not constitute a confirmed booking.</li>
                        <li>Final confirmation will be communicated via phone or email.</li>
                        <li>All terms and conditions are subject to change without notice.</li>
                        <li>By agreeing to these rules, you acknowledge that you have read and understood all terms for the selected halls.</li>
                    </ul>
                `;

                return content;
            }

            // Function to show modal
            function showModal() {
                const rulesContent = getSelectedHallsRules();
                document.querySelector('.modal-body').innerHTML = rulesContent;

                rulesModal.style.display = 'block';
                rulesModal.classList.add('show');
                customBackdrop.classList.add('show');
                document.body.classList.add('modal-open');
            }

            // Function to hide modal
            function hideModal() {
                rulesModal.style.display = 'none';
                rulesModal.classList.remove('show');
                customBackdrop.classList.remove('show');
                document.body.classList.remove('modal-open');
            }

            // Show modal when rules link is clicked
            rulesLink.addEventListener('click', function(e) {
                e.preventDefault();
                showModal();
            });

            // Show modal when agreement checkbox is clicked
            agreementCheckbox.addEventListener('click', function(e) {
                if (this.checked) {
                    showModal();
                }
            });

            // Handle close button clicks
            closeModalBtn.addEventListener('click', function(e) {
                e.preventDefault();
                hideModal();
            });

            closeModalBtn2.addEventListener('click', function(e) {
                e.preventDefault();
                hideModal();
            });

            // Handle agreement button click
            agreeBtn.addEventListener('click', function(e) {
                e.preventDefault();
                agreementCheckbox.checked = true;
                hideModal();
            });

            // Close modal when clicking on backdrop
            rulesModal.addEventListener('click', function(e) {
                if (e.target === rulesModal) {
                    hideModal();
                }
            });

            // Form submission
            form.addEventListener('submit', function(e) {
                console.log('Form submission started');

                if (!agreementCheckbox.checked) {
                    alert('You must agree to the terms and conditions to submit the form.');
                    e.preventDefault();
                    return;
                }
                console.log('Agreement checkbox passed');

                // Check if at least one hall is selected
                const selectedHalls = document.querySelectorAll('.hall-checkbox:checked');
                if (selectedHalls.length === 0) {
                    alert('Please select at least one hall.');
                    e.preventDefault();
                    return;
                }
                console.log('Hall selection passed:', selectedHalls.length, 'halls selected');

                // Validate that selected halls have details filled
                let hasValidHallDetails = true;
                selectedHalls.forEach(hallCheckbox => {
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

                                // For Art Gallery and other halls, also check start/end times
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

                // Validate signature
                let signatureValid = false;
                let errorMessage = '';

                if (signatureType.value === 'image') {
                    const signImage = document.getElementById('sign_image');
                    if (signImage.files.length === 0) {
                        errorMessage = 'Please upload a signature image.';
                    } else {
                        signatureValid = true;
                    }
                } else if (signatureType.value === 'text') {
                    const typedSignature = document.getElementById('typed_signature');
                    if (typedSignature.value.trim() === '') {
                        errorMessage = 'Please enter your signature (name).';
                    } else {
                        signatureValid = true;
                    }
                } else if (signatureType.value === 'draw') {
                    if (isCanvasEmpty()) {
                        document.getElementById('draw-signature-error').style.display = 'block';
                        e.preventDefault();
                        return;
                    } else {
                        digitalSignatureInput.value = canvas.toDataURL('image/png');
                        signatureValid = true;
                    }
                } else {
                    // No signature type selected
                    errorMessage = 'Please select a signature type.';
                }

                if (!signatureValid && errorMessage) {
                    alert(errorMessage);
                    e.preventDefault();
                    return;
                }
                console.log('Signature validation passed');

                // Disable submit button to prevent double submission
                const submitButton = document.getElementById('send_message');
                submitButton.disabled = true;
                submitButton.textContent = 'Submitting...';
                console.log('Form submission allowed, proceeding...');
            });

            function isCanvasEmpty() {
                const pixelBuffer = new Uint32Array(
                    ctx.getImageData(0, 0, canvas.width, canvas.height).data.buffer
                );
                return !pixelBuffer.some(pixel => pixel !== 0);
            }
        });
    </script>
    <!-- content close -->
@endsection
