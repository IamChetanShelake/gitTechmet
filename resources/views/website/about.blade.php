@extends('website.layout.master')
@section('content')





        <!-- content begin -->
        <div class="no-bottom no-top" id="content">

            <div id="top"></div>

            <section id="subheader" class="relative jarallax text-light">
                <img src="{{asset('website/assets/images/background/Background.jpg')}}" class="jarallax-img" alt="">
                <div class="container relative z-index-1000">
                    <div class="row justify-content-center">
                        <div class="col-lg-12 text-center">
                            <h1>About Us</h1>
                            <ul class="crumb">
                                <li><a href="{{route('Index.Page')}}">Home</a></li>
                                <li class="active">About Us</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="de-overlay"></div>
            </section>

            <section class="relative lines-deco">
                <div class="container">

                    <div class="row gx-5 align-items-center">
                        <div class="col-lg-6">
                            <div class="subtitle wow fadeInUp mb-3">Welcome</div>
                            <h2 class="wow fadeInUp" data-wow-delay=".2s">{{$about->title}}</h2>
                            <p>{!! $about->description !!}</p>
                        </div>

                        <div class="col-lg-6">



                            <div class="row g-4">
                                @if($about && $about->image)
                                <img src="{{asset('About_images/'.$about->image)}}" class="img-fluid mb-4 wow zoomIn" alt="">
                                @else
                                <p>No Image Available</p>
                                @endif

                                {{-- <div class="col-6">
                                    <img src="{{asset('website/assets/images/misc/7.webp')}}" class="img-fluid mb-4 wow zoomIn" alt="">
                                    <div class="col-12 text-center">
                                        <div class="bg-color-2 text-light p-4">
                                            <div class="de_count wow fadeInUp">
                                                <h2 class="mb-0"><span class="timer" data-to="120" data-speed="3000"></span>+</h2>
                                                <span>Rooms Available</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="spacer-single sm-hide"></div>
                                    <div class="col-12 text-center">
                                        <div class="bg-color text-light p-4">
                                            <div class="de_count wow fadeInUp">
                                                <h2 class="mb-0"><span class="timer" data-to="105" data-speed="3000"></span>%</h2>
                                                <span>Menu Selection</span>
                                            </div>
                                        </div>
                                    </div>
                                    <img src="{{asset('website/assets/images/misc/8.webp')}}" class="img-fluid mt-4 wow zoomIn" alt="">
                                </div> --}}
                            </div>

                        </div>

                    </div>

                </div>
            </section>

            <section class="bg-light lines-deco">
                <div class="container">
                    <div class="row g-4">
                        <div class="col-lg-12 text-center">
                            <div class="subtitle wow fadeInUp mb-3">Behind the Scene</div>
                            <h2 class="wow fadeInUp mb-0" data-wow-delay=".2s">Our Team</h2>
                        </div>
                        <div class=" row justify-content-center text-center">
                            @foreach ($teams as $team)
                                <div class="col-lg-3 col-md-4 col-sm-6 d-flex justify-content-center"
                                    style="display: flex; flex-direction: column; align-items: center; text-align: center;">

                                    <div class="team-member" style="width: 100%;">
                                        <img src="{{ asset('Team_images/' . $team->image) }}" class="img-fluid" alt=""
                                            style="max-width: 100%; height: auto; display: block; margin: 0 auto;">

                                        <div class="p-3">
                                            <h4 class="mb-0">{{ $team->name }}</h4>
                                            <p class="mb-2">{{ $team->designation }} </p>

                                            <div class="social-icons" style="display: flex; justify-content: center; gap: 10px;">
                                                <a href="#"><i class="bg-white bg-hover-2 text-hover-white fa-brands fa-facebook-f"></i></a>
                                                <a href="#"><i class="bg-white bg-hover-2 text-hover-white fa-brands fa-x-twitter"></i></a>
                                                <a href="#"><i class="bg-white bg-hover-2 text-hover-white fa-brands fa-instagram"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>



                        {{-- <div class="col-lg-3">
                            <img src="{{asset('website/assets/images/team/2.webp')}}" class="img-fluid" alt="">
                            <div class="p-3 text-center
                            ">
                                <h4 class="mb-0">Sophia Jenkins</h4>
                                <p class="mb-2">Founder &amp;  CEO</p>
                                <div class="social-icons">
                                    <a href="#"><i class="bg-white id-color bg-hover-2 text-hover-white fa-brands fa-facebook-f"></i></a>
                                    <a href="#"><i class="bg-white id-color bg-hover-2 text-hover-white fa-brands fa-x-twitter"></i></a>
                                    <a href="#"><i class="bg-white id-color bg-hover-2 text-hover-white fa-brands fa-instagram"></i></a>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3">
                            <img src="{{asset('website/assets/images/team/3.webp')}}" class="img-fluid" alt="">
                            <div class="p-3 text-center
                            ">
                                <h4 class="mb-0">Ethan Reynolds</h4>
                                <p class="mb-2">Founder &amp;  CEO</p>
                                <div class="social-icons">
                                    <a href="#"><i class="bg-white id-color bg-hover-2 text-hover-white fa-brands fa-facebook-f"></i></a>
                                    <a href="#"><i class="bg-white id-color bg-hover-2 text-hover-white fa-brands fa-x-twitter"></i></a>
                                    <a href="#"><i class="bg-white id-color bg-hover-2 text-hover-white fa-brands fa-instagram"></i></a>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3">
                            <img src="{{asset('website/assets/images/team/4.webp')}}" class="img-fluid" alt="">
                            <div class="p-3 text-center
                            ">
                                <h4 class="mb-0">Noah Anderson</h4>
                                <p class="mb-2">Founder &amp;  CEO</p>
                                <div class="social-icons">
                                    <a href="#"><i class="bg-white id-color bg-hover-2 text-hover-white fa-brands fa-facebook-f"></i></a>
                                    <a href="#"><i class="bg-white id-color bg-hover-2 text-hover-white fa-brands fa-x-twitter"></i></a>
                                    <a href="#"><i class="bg-white id-color bg-hover-2 text-hover-white fa-brands fa-instagram"></i></a>
                                </div>
                            </div>
                        </div> --}}
                    </div>
                </div>
            </section>

            <section class="relative lines-deco">
                <div class="container relative z-2">
                    <div class="row g-4">

                        <div class="col-lg-6">
                            <div class="ms-4">
                                <div class="subtitle wow fadeInUp mb-3">Halls &amp; Suites</div>
                                <h2 class="wow fadeInUp mb-5">Hall Facilities</h2>

                                <div class="row g-3">
                                    @foreach ($facilites as $facilite)


                                    <div class="col-lg-6">
                                        <div class="mb-4 relative wow fadeInRight" data-wow-delay=".3s">
                                            <img src="{{asset('Facilitie_images/'.$facilite->image)}}" class="w-50px absolute id-color icofont-thunder-light" alt="">
                                            <div class="pl-70">
                                                <h5>{{$facilite->title}}</h5>
                                                <p class="mb-0">{{$facilite->description}}</p>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach

                                    {{-- <div class="col-lg-6">
                                        <div class="mb-4 relative wow fadeInRight" data-wow-delay=".3s">
                                            <img src="{{asset('website/assets/images/icons/tv.png')}}" class="w-50px absolute id-color icofont-thunder-light" alt="">
                                            <div class="pl-70">
                                                <h5>Audio-Visual Setup</h5>
                                                <p class="mb-0">Equipped with projectors, microphones, and high-quality sound systems.</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="mb-4 relative wow fadeInRight" data-wow-delay=".3s">
                                            <img src="{{asset('website/assets/images/icons/desk.png')}}" class="w-50px absolute id-color icofont-thunder-light" alt="">
                                            <div class="pl-70">
                                                <h5>Comfortable Seating Arrangements</h5>
                                                <p class="mb-0">Flexible layouts for seminars, training sessions, and conferences.</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="mb-4 relative wow fadeInRight" data-wow-delay=".3s">
                                            <img src="{{asset('website/assets/images/icons/wifi.png')}}" class="w-50px absolute id-color icofont-thunder-light" alt="">
                                            <div class="pl-70">
                                                <h5>High-Speed WiFi & IT Support</h5>
                                                <p class="mb-0">Ensuring seamless online connectivity for virtual and hybrid events.</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="mb-4 relative wow fadeInRight" data-wow-delay=".3s">
                                            <img src="{{asset('website/assets/images/icons/balcony.png')}}" class="w-50px absolute id-color icofont-thunder-light" alt="">
                                            <div class="pl-70">
                                                <h5>Refreshments & Catering</h5>
                                                <p class="mb-0">Customizable options for coffee breaks and meals.</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="mb-4 relative wow fadeInRight" data-wow-delay=".3s">
                                            <img src="{{asset('website/assets/images/icons/guests.png')}}" class="w-50px absolute id-color icofont-thunder-light" alt="">
                                            <div class="pl-70">
                                                <h5>Dedicated Event Support</h5>
                                                <p class="mb-0">Professional assistance for smooth event execution.</p>
                                            </div>
                                        </div>
                                    </div> --}}
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="relative">
                                        <img src="{{asset('website/assets/images/misc/2 - Copy (2).png')}}" class="img-fluid wow fadeInUp" alt="">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="spacer-single sm-hide"></div>
                                    <div class="relative">
                                        <img src="{{asset('website/assets/images/misc/Audi 3.jpg')}}" class="img-fluid wow fadeInUp" alt="">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="relative">
                                        <img src="{{asset('website/assets/images/misc/Audi gr - Copy (2).jpg')}}" class="img-fluid wow fadeInUp" alt="">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="spacer-single sm-hide"></div>
                                    <div class="relative">
                                        <img src="{{asset('website/assets/images/misc/Audi VIP - Copy (2).jpg')}}" class="img-fluid wow fadeInUp" alt="">
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>

            </section>




        <!-- content close -->
            <section class="bg-light relative pt50 no-bottom">
            <div class="container relative z-2">
                <div class="row g-4">
                    <div class="col-lg-8 offset-lg-2 mb-4 text-center">
                        <div class="subtitle wow fadeInUp mb-3">Our Youtube</div>
                        <h2 class="wow fadeInUp">Watch Our Videos</h2>
                    </div>
                </div>
            </div>

            <div class="container-fluid">
                @if($videos->count() > 0)
                 @php
                                // Calculate dynamic width for perfect fit
                                $videoCount = $videos->count();
                                if ($videoCount >=  8) {
                                    // For 8 videos, calculate width to fit perfectly
                                    // $containerWidth = 100; // 100% of container
                                    // $videoWidth = $containerWidth / 8; // Each video takes 12.5% of width
                                    $flexStyle = "justify-content: flex-start;";
                                } else {
                                    // Default fixed width for other counts
                                    $flexStyle = "justify-content: center;";
                                }
                            @endphp
                    <div class="d-flex overflow-auto pb-3 modern-scroll" style="flex-wrap: nowrap; {{ $flexStyle }}">
                        @foreach($videos as $video)
                            @php
                                // Extract YouTube video ID from URL
                                $videoId = '';
                                $url = trim($video->youtube_url);

                                // If it's already just the video ID (11 characters)
                                if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $url)) {
                                    $videoId = $url;
                                }
                                // Try to extract from various YouTube URL formats
                                elseif (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches)) {
                                    $videoId = $matches[1];
                                }
                                // Fallback: try to find any 11-character sequence that looks like a video ID
                                elseif (preg_match('/([a-zA-Z0-9_-]{11})/', $url, $matches)) {
                                    $videoId = $matches[1];
                                }
                            @endphp



                            <div class="me-2" style="width:200px; flex: 0 0 auto;">
                                <a href="#" class="d-block hover relative overflow-hidden text-light video-link rounded-3"
                                    data-video-id="{{ $videoId }}" data-url="{{ $video->youtube_url }}" style="width:200px; height: 180px;">
                                    <img src="{{ asset('video_thumbnails/' . $video->thumbnail) }}"
                                        class="w-100 h-100 object-fit-cover hover-scale-1-1 rounded-3" alt="{{ $video->title ?? 'Video' }}">
                                    <div class="abs abs-centered fs-24 text-white hover-op-0">
                                        <i class="fa-brands fa-youtube" style="color: red; font-size: 2rem;"></i>
                                    </div>
                                    {{-- @if($video->title)
                                        <div class="abs bottom-0 w-100 bg-dark bg-opacity-75 text-white p-2 rounded-bottom-3">
                                            <h6 class="mb-0 fs-12 text-center">{{ $video->title }}</h6>
                                        </div>
                                    @endif --}}
                                </a>
                            </div>
                        @endforeach
                    </div>

                    <style>
                        /* Thin Scrollbar Styling */
                        .modern-scroll {
                            scrollbar-width: thin; /* Firefox */
                            scrollbar-color: rgba(112, 83, 58, 0.3) transparent; /* Firefox */
                        }

                        .modern-scroll::-webkit-scrollbar {
                            height: 2px; /* Very thin scrollbar */
                        }

                        .modern-scroll::-webkit-scrollbar-track {
                            background: transparent; /* Transparent track */
                        }

                        .modern-scroll::-webkit-scrollbar-thumb {
                            background: rgba(112, 83, 58, 0.3); /* Semi-transparent brand color */
                            border-radius: 10px;
                        }

                        .modern-scroll::-webkit-scrollbar-thumb:hover {
                            background: rgba(112, 83, 58, 0.5); /* Slightly more visible on hover */
                        }
                        @media (max-width: 576px) {
                            .modern-scroll {
                                justify-content:flex-start !important; /* Slightly thicker scrollbar on mobile for easier touch */
                            }
                        }
                    </style>
                @else
                    <div class="text-center py-5">
                        <p class="text-muted">No videos available at the moment.</p>
                    </div>
                @endif
            </div>
        </section>
        <!-- YouTube Video Modal -->
        <div id="videoModal" class="video-modal-overlay">
            <div class="video-modal-content">
                <button id="closeModal" class="video-modal-close">&times;</button>
                <div class="video-modal-wrapper">
                    <iframe id="videoIframe" src="" title="YouTube video" allowfullscreen></iframe>
                </div>
            </div>
        </div>

        <style>
            .video-modal-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.9);
                z-index: 99999;
                justify-content: center;
                align-items: center;
                opacity: 0;
                transition: opacity 0.3s ease;
            }

            .video-modal-overlay.show {
                display: flex !important;
                opacity: 1;
            }

            .video-modal-content {
                background: #fff;
                padding: 0;
                max-width: 90vw;
                max-height: 90vh;
                width: 800px;
                position: relative;
                border-radius: 8px;
                overflow: hidden;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
                transform: scale(0.7);
                transition: transform 0.3s ease;
            }

            .video-modal-overlay.show .video-modal-content {
                transform: scale(1);
            }

            .video-modal-close {
                position: absolute;
                top: -40px;
                right: 0;
                background: rgba(255, 255, 255, 0.9);
                border: none;
                font-size: 28px;
                cursor: pointer;
                color: #333;
                width: 40px;
                height: 40px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 100000;
                transition: background 0.2s ease;
            }

            .video-modal-close:hover {
                background: rgba(255, 255, 255, 1);
                transform: scale(1.1);
            }

            .video-modal-wrapper {
                position: relative;
                padding-top: 56.25%; /* 16:9 aspect ratio */
                height: 0;
            }

            .video-modal-wrapper iframe {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                border: none;
            }

            @media (max-width: 768px) {
                .video-modal-content {
                    max-width: 95vw;
                    width: 95vw;
                }

                .video-modal-close {
                    top: -35px;
                    font-size: 24px;
                    width: 35px;
                    height: 35px;
                }
            }
        </style>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                var videoModal = document.getElementById("videoModal");
                var videoIframe = document.getElementById("videoIframe");
                var closeModal = document.getElementById("closeModal");

                // Function to open modal
                function openModal(videoId) {
                    if (videoId && videoId.trim() !== '') {
                        console.log('Opening video with ID:', videoId); // Debug log
                        videoIframe.src = "https://www.youtube.com/embed/" + videoId + "?autoplay=1&rel=0&modestbranding=1";
                        videoModal.classList.add('show');
                        document.body.style.overflow = 'hidden'; // Prevent background scrolling
                    } else {
                        console.error('Invalid video ID:', videoId);
                    }
                }

                // Function to close modal
                function closeModalFunction() {
                    videoModal.classList.remove('show');
                    document.body.style.overflow = ''; // Restore scrolling
                    // Delay clearing the iframe to allow for smooth transition
                    setTimeout(function() {
                        videoIframe.src = "";
                    }, 300);
                }

                // Add click event to all video links
                document.querySelectorAll(".video-link").forEach(function(link) {
                    link.addEventListener("click", function(event) {
                        event.preventDefault();
                        event.stopPropagation();

                        var videoId = link.getAttribute("data-video-id");
                        var videoUrl = link.getAttribute("data-url");
                        console.log('Video link clicked, ID:', videoId, 'URL:', videoUrl); // Debug log
                        openModal(videoId);
                    });
                });

                // Close modal when close button is clicked
                if (closeModal) {
                    closeModal.addEventListener("click", function(event) {
                        event.preventDefault();
                        event.stopPropagation();
                        closeModalFunction();
                    });
                }

                // Close modal when clicking outside the content
                videoModal.addEventListener("click", function(event) {
                    if (event.target === videoModal) {
                        closeModalFunction();
                    }
                });

                // Close modal with Escape key
                document.addEventListener("keydown", function(event) {
                    if (event.key === "Escape" && videoModal.classList.contains('show')) {
                        closeModalFunction();
                    }
                });

                // Handle scrolling to upcoming events section
                function scrollToUpcomingEvents() {
                    var element = document.getElementById('upcoming-events');
                    if (element) {
                        element.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                }

                if (window.location.hash === '#upcoming-events') {
                    // Try scrolling immediately
                    scrollToUpcomingEvents();

                    // Also try after DOM is fully loaded
                    window.addEventListener('load', function() {
                        setTimeout(scrollToUpcomingEvents, 100);
                    });

                    // Fallback with longer delay for slow loading content
                    setTimeout(scrollToUpcomingEvents, 1000);
                }

                // Debug: Log all video links found
                console.log('Found video links:', document.querySelectorAll(".video-link").length);
            });
        </script>





@endsection
