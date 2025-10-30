@extends('website.layout.master')

@section('content')
<style>
    #signature-pad {
    touch-action: none;
    width: 100%;   /* For responsive width */
    height: 200px; /* Slightly taller for mobile fingers */
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

                                            <div class="p-2 border shadow-sm bg-white">
                                                <label class=" text-dark mb-2">Vendor Services:</label><br>

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
                                                <select name="hall" id="hall" class="form-control" required>
                                                    <option value="" selected disabled>-- Select Hall --</option>
                                                    @foreach ($halls as $hall)
                                                        <option value="{{ $hall->name }}" data-hall-id="{{ $hall->id }}"
                                                            {{ old('hall') == $hall->name ? 'selected' : '' }}>
                                                            {{ $hall->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('hall')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <!-- Hidden input field to store hall_id -->
                                            <input type="hidden" name="hall_id" id="hall_id">



                                                <script>
                                                document.getElementById("hall").addEventListener("change", function() {
                                                    var selectedOption = this.options[this.selectedIndex];
                                                    var hallName = selectedOption.value;
                                                    document.getElementById("hall_id").value = selectedOption.getAttribute("data-hall-id");
                                                    document.getElementById("hall-name").textContent = hallName;
                                                });
                                                </script>


                                            <div class="mb-3">
                                                <input type="date" name="event_date" id="event_date" class="form-control" value="{{ old('event_date') }}" required>
                                                @error('event_date')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="mb-3" style="background-color: #fff !important;">
                                                <select name="duration" id="duration" class="form-control" required>
                                                    <option value="full_day" {{ old('duration') == 'Full Day' ? 'selected' : '' }}>Full Day</option>
                                                    <option value="half_day_morning" {{ old('duration') == 'Half Day (Morning)' ? 'selected' : '' }}>Half Day (Morning)</option>
                                                    <option value="half_day_evening" {{ old('duration') == 'Half Day (Evening)' ? 'selected' : '' }}>Half Day (Evening)</option>
                                                </select>
                                                @error('duration')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="mb-3">
                                                <label for="start_time" class="form-label">Start Time *</label>
                                                <input type="time" name="start_time" id="start_time" class="form-control" value="{{ old('start_time') }}" required>
                                                @error('start_time')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="mb-3">
                                                <label for="end_time" class="form-label">End Time *</label>
                                                <input type="time" name="end_time" id="end_time" class="form-control" value="{{ old('end_time') }}" required>
                                                @error('end_time')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="mb-3">
                                                <input type="number" name="expected_audience" id="expected_audience" class="form-control"
                                                    placeholder="Expected No. of Audience *" value="{{ old('expected_audience') }}" required>
                                                @error('expected_audience')
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

                                        <!-- Agreement Checkbox -->
                                        <div class="col-md-12 text-center mt-3">
                                            <div class="form-check mb-3">
                                                <input type="checkbox" class="form-check-input" id="agreement" name="agreement" required>
                                                <label class="form-check-label" for="agreement">
                                                    I have read, understood, and agreed to the rules and regulations for <span id="hall-name">___</span> hall. Failing which booking shall be cancelled without prior notice and no refund claim will be entertained. This enquiry form is not a final booking or confirmation. Confirmation will be communicated on given contact no. or e-mail id.
                                                </label>
                                            </div>
                                            <button type="submit" id="send_message" style="background-color: #AB8965; color: white; height: 40px; width: 100px;">Submit</button>
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

            // Form submission
            form.addEventListener('submit', function(e) {
                const agreementCheckbox = document.getElementById('agreement');
                if (!agreementCheckbox.checked) {
                    alert('You must agree to the terms and conditions to submit the form.');
                    e.preventDefault();
                    return;
                }

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
                }

                if (!signatureValid && errorMessage) {
                    alert(errorMessage);
                    e.preventDefault();
                    return;
                }

                // Disable submit button to prevent double submission
                const submitButton = document.getElementById('send_message');
                submitButton.disabled = true;
                submitButton.textContent = 'Submitting...';
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
