@extends('admin.layout.masteradmin')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-lg-flex">
                        <div>
                            <h5 class="mb-0">📅 Booking & Enquiry Calendar</h5>
                            <p class="text-sm mb-0">View all confirmed bookings and enquiries in one place</p>
                        </div>
                        {{-- <div class="ms-auto my-auto mt-lg-0 mt-4">
                            <div class="ms-auto my-auto">
                                <a href="{{ route('admin.calendar.stats') }}" class="btn btn-outline-primary btn-sm mb-0">
                                    <i class="material-symbols-rounded opacity-5">analytics</i>
                                    View Statistics
                                </a>
                            </div>
                        </div> --}}
                    </div>
                </div>
                <div class="card-body p-3">
                    <!-- Calendar Legend -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="d-flex align-items-center mb-3">
                                <h6 class="mb-0 me-3">Legend:</h6>
                                <div class="d-flex align-items-center me-4">
                                    <div class="badge bg-success me-2" style="width: 20px; height: 20px; border-radius: 50%;"></div>
                                    <span class="text-sm">Confirmed Bookings</span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="badge bg-warning me-2" style="width: 20px; height: 20px; border-radius: 50%;"></div>
                                    <span class="text-sm">Enquiries</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Calendar Container -->
                    <div id="calendar" class="calendar-container"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Event Details Modal -->
<div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventModalLabel">Event Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="eventModalBody">
                <!-- Event details will be populated here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="viewFullDetailsBtn">View Full Details</button>
            </div>
        </div>
    </div>
</div>

<!-- FullCalendar CSS -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' rel='stylesheet' />

<!-- FullCalendar JS -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>

<style>
    .calendar-container {
        max-height: 80vh;
        overflow: hidden;
    }

    #calendar {
        font-family: 'Inter', sans-serif;
    }

    .fc {
        --fc-border-color: #e9ecef;
        --fc-button-text-color: #67748e;
        --fc-button-bg-color: #fff;
        --fc-button-border-color: #e9ecef;
        --fc-button-hover-bg-color: #f8f9fa;
        --fc-button-hover-border-color: #e9ecef;
        --fc-button-active-bg-color: #5a73a8;
        --fc-button-active-border-color: #5a73a8;
        --fc-today-bg-color: rgba(90, 115, 168, 0.1);
        --fc-event-bg-color: #28a745;
        --fc-event-border-color: #28a745;
        --fc-event-text-color: #fff;
    }

    .fc .fc-button {
        font-size: 0.875rem;
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        text-transform: none;
        font-weight: 500;
    }

    .fc .fc-button:not(:disabled):active,
    .fc .fc-button:not(:disabled).fc-button-active {
        box-shadow: 0 0 0 0.2rem rgba(90, 115, 168, 0.25);
    }

    .fc .fc-toolbar-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #344767;
    }

    .fc .fc-col-header-cell {
        font-size: 0.875rem;
        font-weight: 600;
        color: #67748e;
        background-color: #f8f9fa;
        padding: 1rem 0.5rem;
    }

    .fc .fc-daygrid-day-number {
        font-size: 0.875rem;
        font-weight: 500;
        color: #344767;
        padding: 0.5rem;
    }

    .fc .fc-daygrid-day-top {
        justify-content: center;
    }

    .fc .fc-daygrid-day.fc-day-today {
        background-color: rgba(90, 115, 168, 0.05);
    }

    .fc .fc-event {
        border-radius: 0.375rem;
        border: none;
        font-size: 0.75rem;
        font-weight: 500;
        padding: 0.25rem 0.5rem;
        cursor: pointer;
        transition: all 0.15s ease-in-out;
    }

    .fc .fc-event:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .fc .fc-event.event-enquiry {
        background-color: #ffc107;
        border-color: #ffc107;
        color: #000;
    }

    .fc .fc-event.event-booking {
        background-color: #28a745;
        border-color: #28a745;
        color: #fff;
    }

    .fc .fc-popover {
        border: none;
        border-radius: 0.5rem;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        background: #fff;
    }

    .fc .fc-popover-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
        padding: 0.75rem 1rem;
        font-weight: 600;
        color: #344767;
    }

    .fc .fc-popover-body {
        padding: 0.75rem 1rem;
    }

    .fc .fc-list-event {
        border-radius: 0.375rem;
        margin-bottom: 0.5rem;
        border: none;
    }

    .fc .fc-list-event-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
    }

    .fc .fc-list-event.event-enquiry .fc-list-event-dot {
        background-color: #ffc107;
    }

    .fc .fc-list-event.event-booking .fc-list-event-dot {
        background-color: #28a745;
    }

    /* Custom event content styling */
    .event-title {
        font-weight: 600;
        font-size: 0.875rem;
        margin-bottom: 0.25rem;
    }

    .event-detail {
        font-size: 0.75rem;
        opacity: 0.9;
        margin-bottom: 0.125rem;
    }

    .event-status {
        font-size: 0.75rem;
        font-weight: 500;
        padding: 0.125rem 0.5rem;
        border-radius: 0.25rem;
        display: inline-block;
    }

    .status-confirmed {
        background-color: rgba(40, 167, 69, 0.1);
        color: #28a745;
    }

    .status-pending {
        background-color: rgba(255, 193, 7, 0.1);
        color: #856404;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .fc .fc-toolbar-title {
            font-size: 1rem;
        }

        .fc .fc-button {
            font-size: 0.75rem;
            padding: 0.375rem 0.75rem;
        }

        .fc .fc-col-header-cell {
            font-size: 0.75rem;
            padding: 0.75rem 0.25rem;
        }

        .fc .fc-daygrid-day-number {
            font-size: 0.75rem;
            padding: 0.25rem;
        }
    }

    /* Loading state */
    .fc-view-harness {
        position: relative;
    }

    .calendar-loading {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 1000;
        background: rgba(255, 255, 255, 0.9);
        padding: 1rem;
        border-radius: 0.5rem;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar;

    calendar = new FullCalendar.Calendar(calendarEl, {
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
        },
        initialView: 'dayGridMonth',
        height: 'auto',
        aspectRatio: 1.35,
        nowIndicator: true,
        editable: false,
        selectable: false,
        dayMaxEvents: 3,
        moreLinkClick: 'popover',
        displayEventTime: false,

        // Events function
        events: function(fetchInfo, successCallback, failureCallback) {
            fetch('{{ route("admin.calendar.events") }}?start=' + fetchInfo.start.toISOString().split('T')[0] + '&end=' + fetchInfo.end.toISOString().split('T')[0], {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                successCallback(data);
            })
            .catch(error => {
                console.error('Error fetching events:', error);
                failureCallback(error);
            });
        },

        // Event display customization
        eventDidMount: function(info) {
            // Add custom classes based on event type
            if (info.event.extendedProps && info.event.extendedProps.type === 'enquiry') {
                info.el.classList.add('event-enquiry');
            } else if (info.event.extendedProps && info.event.extendedProps.type === 'booking') {
                info.el.classList.add('event-booking');
            }

            // Add tooltip with event details
            if (info.event.extendedProps) {
                var tooltip = document.createElement('div');
                tooltip.className = 'event-tooltip';
                var statusClass = info.event.extendedProps.status ? info.event.extendedProps.status.toLowerCase().replace(' ', '-') : 'unknown';
                tooltip.innerHTML = `
                    <div class="event-title">${info.event.title}</div>
                    <div class="event-detail"><strong>Customer:</strong> ${info.event.extendedProps.customer_name || 'N/A'}</div>
                    <div class="event-detail"><strong>Phone:</strong> ${info.event.extendedProps.customer_phone || 'N/A'}</div>
                    <div class="event-detail"><strong>Email:</strong> ${info.event.extendedProps.customer_email || 'N/A'}</div>
                    <div class="event-detail"><strong>Type:</strong> ${info.event.extendedProps.event_type || 'N/A'}</div>
                    <div class="event-detail"><strong>Status:</strong> <span class="event-status status-${statusClass}">${info.event.extendedProps.status || 'Unknown'}</span></div>
                    ${info.event.extendedProps.halls && info.event.extendedProps.halls.length > 1 ?
                        `<div class="event-detail"><strong>Halls (${info.event.extendedProps.halls.length}):</strong><br>${info.event.extendedProps.halls.join('<br>')}</div>` :
                        (info.event.extendedProps.hall_name ? `<div class="event-detail"><strong>Hall:</strong> ${info.event.extendedProps.hall_name}</div>` : '')}
                    ${info.event.extendedProps.total_rent ? `<div class="event-detail"><strong>Total Amount:</strong> ₹${info.event.extendedProps.total_rent}</div>` : ''}
                    ${info.event.extendedProps.total_deposit ? `<div class="event-detail"><strong>Total Deposit:</strong> ₹${info.event.extendedProps.total_deposit}</div>` : ''}
                    ${info.event.extendedProps.special_note ? `<div class="event-detail"><strong>Note:</strong> ${info.event.extendedProps.special_note}</div>` : ''}
                `;

                // Style the tooltip
                tooltip.style.cssText = `
                    position: absolute;
                    background: white;
                    border: 1px solid #e9ecef;
                    border-radius: 0.5rem;
                    padding: 1rem;
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                    z-index: 1000;
                    min-width: 280px;
                    max-width: 500px;
                    font-size: 0.875rem;
                    line-height: 1.4;
                    color: #000;
                    display: none;
                    pointer-events: none;
                    word-wrap: break-word;
                    overflow-wrap: break-word;
                    white-space: normal;
                `;

                info.el.style.position = 'relative';
                info.el.appendChild(tooltip);

                // Show/hide tooltip on hover
                info.el.addEventListener('mouseenter', function(e) {
                    tooltip.style.display = 'block';
                    tooltip.style.top = '-10px';
                    tooltip.style.left = '100%';
                    tooltip.style.right = 'auto';
                    tooltip.style.transform = 'translateX(10px)';

                    // Force reflow to get accurate dimensions
                    tooltip.offsetHeight;

                    // Check if tooltip goes off-screen and adjust position
                    var rect = tooltip.getBoundingClientRect();
                    var calendarRect = calendarEl.getBoundingClientRect();

                    if (rect.right > calendarRect.right) {
                        // Move tooltip to the left if it goes off the right edge
                        tooltip.style.left = 'auto';
                        tooltip.style.right = '100%';
                        tooltip.style.transform = 'translateX(-10px)';
                    }

                    if (rect.top < calendarRect.top) {
                        // Move tooltip down if it goes off the top edge
                        tooltip.style.top = '100%';
                        tooltip.style.transform = 'translateY(10px)';
                    }
                });

                info.el.addEventListener('mouseleave', function() {
                    tooltip.style.display = 'none';
                });
            }
        },

        // Event click handler
        eventClick: function(info) {
            // Prevent default behavior
            info.jsEvent.preventDefault();

            // Show custom modal with event details
            showEventModal(info.event);

            console.log('Event clicked:', info.event);
            return false; // Prevent any default popover
        },

        // Loading states
        loading: function(bool) {
            if (bool) {
                // Show loading indicator
                if (!document.querySelector('.calendar-loading')) {
                    var loadingEl = document.createElement('div');
                    loadingEl.className = 'calendar-loading';
                    loadingEl.innerHTML = '<div class="d-flex align-items-center"><div class="spinner-border spinner-border-sm me-2" role="status"></div>Loading events...</div>';
                    calendarEl.appendChild(loadingEl);
                }
            } else {
                // Hide loading indicator
                var loadingEl = document.querySelector('.calendar-loading');
                if (loadingEl) {
                    loadingEl.remove();
                }
            }
        },

        // View change handler to reload events
        datesSet: function(dateInfo) {
            // Events will be automatically refetched due to eventSources configuration
        }
    });

    calendar.render();

    // Refresh calendar data every 5 minutes
    setInterval(function() {
        calendar.refetchEvents();
    }, 300000);

    // Function to show event modal
    function showEventModal(event) {
        var modal = new bootstrap.Modal(document.getElementById('eventModal'));
        var modalBody = document.getElementById('eventModalBody');
        var viewFullDetailsBtn = document.getElementById('viewFullDetailsBtn');

        // Clear previous content
        modalBody.innerHTML = '';

        // Build modal content based on event type
        var content = '<div class="row">';

        if (event.extendedProps) {
            var props = event.extendedProps;

            // Left column - Basic info
            content += '<div class="col-md-6">';
            content += '<h6 class="text-primary mb-3"><i class="fas fa-info-circle me-2"></i>Event Information</h6>';
            content += '<div class="mb-2"><strong>Title:</strong> ' + event.title + '</div>';
            content += '<div class="mb-2"><strong>Type:</strong> ' + (props.event_type || 'N/A') + '</div>';
            content += '<div class="mb-2"><strong>Status:</strong> <span class="badge ' + (props.type === 'booking' ? 'bg-success' : 'bg-warning') + '">' + (props.status || 'Unknown') + '</span></div>';
            if (props.hall_name) {
                content += '<div class="mb-2"><strong>Hall:</strong> ' + props.hall_name + '</div>';
            }
            if (props.duration) {
                content += '<div class="mb-2"><strong>Duration:</strong> ' + props.duration + '</div>';
            }
            if (props.start_time) {
                content += '<div class="mb-2"><strong>Start Time:</strong> ' + props.start_time + '</div>';
            }
            if (props.end_time) {
                content += '<div class="mb-2"><strong>End Time:</strong> ' + props.end_time + '</div>';
            }
            content += '</div>';

            // Right column - Customer info
            content += '<div class="col-md-6">';
            content += '<h6 class="text-primary mb-3"><i class="fas fa-user me-2"></i>Customer Details</h6>';
            content += '<div class="mb-2"><strong>Name:</strong> ' + (props.customer_name || 'N/A') + '</div>';
            content += '<div class="mb-2"><strong>Phone:</strong> ' + (props.customer_phone || 'N/A') + '</div>';
            content += '<div class="mb-2"><strong>Email:</strong> ' + (props.customer_email || 'N/A') + '</div>';
            if (props.organization) {
                content += '<div class="mb-2"><strong>Organization:</strong> ' + props.organization + '</div>';
            }
            content += '</div>';
        }

        content += '</div>';

        // Additional details section
        if (event.extendedProps) {
            var props = event.extendedProps;
            content += '<hr><div class="row"><div class="col-12">';

            if (props.total_rent) {
                content += '<div class="mb-2"><strong>Total Rent Amount:</strong> ₹' + props.total_rent + '</div>';
            }
            if (props.total_deposit) {
                content += '<div class="mb-2"><strong>Total Deposit Amount:</strong> ₹' + props.total_deposit + '</div>';
            }
            if (props.paid_amount) {
                content += '<div class="mb-2"><strong>Paid Amount(with GST):</strong> ₹' + props.paid_amount + '</div>';
            }
            if (props.remaining_amount) {
                content += '<div class="mb-2"><strong>Remaining Amount:</strong> ₹' + props.remaining_amount + '</div>';
            }

            if (props.expected_audience) {
                content += '<div class="mb-2"><strong>Expected Audience:</strong> ' + props.expected_audience + '</div>';
            }
            if (props.special_note) {
                content += '<div class="mb-2"><strong>Special Note:</strong> ' + props.special_note + '</div>';
            }

            content += '</div></div>';
        }

        modalBody.innerHTML = content;

        // Set up the "View Full Details" button
        if (props.type === 'booking') {
            viewFullDetailsBtn.style.display = 'inline-block';
            viewFullDetailsBtn.onclick = function() {
                // Extract booking ID from event ID (handles multi-hall bookings with group codes and dates)
                var bookingId = event.id.replace('booking_', '').split('_')[0];
                window.location.href = '{{ url("/booked-halls") }}/' + bookingId;
            };
        } else if (props.type === 'enquiry') {
            viewFullDetailsBtn.style.display = 'inline-block';
            viewFullDetailsBtn.onclick = function() {
                // Extract enquiry ID from event ID (handles multi-hall enquiries with group codes and dates)
                var enquiryId = event.id.replace('enquiry_', '').split('_')[0];
                window.location.href = '{{ url("/ViewHallEnquiry") }}/' + enquiryId;
            };
        } else {
            viewFullDetailsBtn.style.display = 'none';
        }

        // Show the modal
        modal.show();
    }
});
</script>
@endsection
