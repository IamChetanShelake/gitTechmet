<?php

namespace App\Http\Controllers;

use App\Models\Eventitem;
use App\Models\BookedHall;
use App\Models\Cateringitem;
use App\Models\EventService;
use Illuminate\Http\Request;
use App\Models\CateringService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class EventCateringController extends Controller
{

    // public function eventBookedHalls()
    // {

    //     $eventBookings = BookedHall::where('event_flag', 1)
    //     ->with('eventServices')
    //     ->get();


    //     return view('Event.EventTable', compact('eventBookings'));
    // }
    public function eventBookedHalls()
    {
        // Ensure the user is logged in
        if (!Auth::check()) {
            return redirect()->back()->with('error', 'You must be logged in to view bookings.');
        }

        // Get the logged-in vendor's ID
        $vendorId = Auth::id();

        // Fetch bookings where:
        // - `event_flag = 1` (for event-related bookings)
        // - `booked_by` is NULL (unbooked halls)
        // - OR `booked_by` matches the logged-in vendor (vendor sees only their own bookings)
        // - Include cancelled bookings so vendors know the booking was cancelled
        $eventBookingsQuery = BookedHall::where('event_flag', 1)
        ->where(function ($query) use ($vendorId) {
            $query->whereNull('event_booked_by')  // Show unbooked halls
                  ->orWhere('event_booked_by', $vendorId); // Show only vendor's own bookings
        })
        ->orderBy('created_at', 'desc')
        ->with('eventServices');

        $eventBookingsRaw = $eventBookingsQuery->get();

        // Group bookings by group_code for display
        $groupedBookings = collect();
        $processedGroups = [];

        foreach ($eventBookingsRaw as $booking) {
            if ($booking->group_code && !in_array($booking->group_code, $processedGroups)) {
                // This is a multi-hall booking group
                $groupBookings = BookedHall::where('group_code', $booking->group_code)
                                           ->where('event_flag', 1)
                                           ->where(function ($query) use ($vendorId) {
                                               $query->whereNull('event_booked_by')
                                                     ->orWhere('event_booked_by', $vendorId);
                                           })
                                           ->with('eventServices')
                                           ->get();

                // Create a representative booking object for the group
                $groupRep = $groupBookings->first();
                $groupRep->is_group = true;

                // Combine hall names
                $hallNames = $groupBookings->pluck('hall_name')->implode(', ');
                $groupRep->hall_name = $hallNames;
                $groupRep->group_count = $groupBookings->count();

                // Combine event services from all halls in the group
                $allEventServices = collect();
                foreach ($groupBookings as $groupBooking) {
                    $allEventServices = $allEventServices->merge($groupBooking->eventServices);
                }
                $groupRep->eventServices = $allEventServices;

                $groupedBookings->push($groupRep);
                $processedGroups[] = $booking->group_code;
            } elseif (!$booking->group_code) {
                // This is a single hall booking
                $booking->is_group = false;
                $groupedBookings->push($booking);
            }
        }

        return view('Event.VendorEventCrud.EventTable', ['eventBookings' => $groupedBookings]);
    }

    public function viewEventBooking($id){
        $eventBooking = BookedHall::with('eventServices')->findOrFail($id);

        // Check if this is a multi-hall booking
        $groupedEnquiries = collect();
        if ($eventBooking->group_code) {
            $groupedEnquiries = BookedHall::where('group_code', $eventBooking->group_code)
                                         ->with('eventServices')
                                         ->orderBy('hall_name')
                                         ->get();
        } else {
            $groupedEnquiries->push($eventBooking);
        }

        $isMultiHall = $groupedEnquiries->count() > 1;

        // If multi-hall, create a combined representation
        if ($isMultiHall) {
            // Create a merged object with combined information
            $combinedBooking = clone $groupedEnquiries->first();

            // Combine hall names
            $hallNames = $groupedEnquiries->pluck('hall_name')->toArray();
            $combinedBooking->hall_name = implode(', ', $hallNames);
            $combinedBooking->group_count = $groupedEnquiries->count();

            // Combine all event services
            $allEventServices = collect();
            foreach ($groupedEnquiries as $booking) {
                $allEventServices = $allEventServices->merge($booking->eventServices);
            }
            $combinedBooking->eventServices = $allEventServices;

            // Collect all event dates for display
            $allDates = $groupedEnquiries->pluck('event_date')->unique()->filter()->values()->toArray();

            // Group bookings by hall name and collect all timing info per hall
            $hallTimingInfo = [];
            $groupedByHall = $groupedEnquiries->groupBy('hall_name');

            foreach ($groupedByHall as $hallName => $hallBookings) {
                $hallInfo = [
                    'hall_name' => $hallName,
                    'dates_times' => []
                ];

                foreach ($hallBookings as $booking) {
                    $hallInfo['dates_times'][] = [
                        'event_date' => $booking->event_date,
                        'start_time' => $booking->start_time,
                        'end_time' => $booking->end_time,
                        'duration' => $booking->duration
                    ];
                }

                $hallTimingInfo[] = $hallInfo;
            }

            // Format dates and hall timing info for display
            $combinedBooking->all_event_dates = $allDates;
            $combinedBooking->hall_timing_info = $hallTimingInfo;

            $eventBooking = $combinedBooking;
        }

        // ✅ Update `booked_by` field in `booked_halls` table for each service
        foreach ($eventBooking->eventServices as $service) {
            if (isset($service->Item_id)) {
                $itemIds = json_decode($service->Item_id, true); // Decode stored JSON array
                // Fetch item names based on IDs and store in a new attribute
                $service->item_names = Eventitem::whereIn('id', $itemIds)->pluck('item_name')->toArray();
            } else {
                $service->item_names = [];
            }
        }

        // Pass data to the view
        return view('Event.VendorEventCrud.EventViewBooking', compact('eventBooking'));
    }

    public function CreateEventService($booked_hall_id){
        $eventitems = Eventitem::all();
        return view('Event.VendorEventCrud.AddEventService', compact('booked_hall_id','eventitems'));
    }
    public function AddEventService(Request $request,$booked_hall_id){

        // $validated = $request->validate([
        //     'booked_hall_id' => 'required|exists:booked_halls,id',
        //     'service_name' => 'required|string|max:255',
        //     'service_price' => 'required|numeric',
        //     'quantity' => 'required|integer|min:1',
        //     'total_price' => 'required|numeric'
        // ]);

        if (!Auth::check()) {
            return redirect()->back()->with('error', 'You must be logged in to book an event.');
        }

           // Get the logged-in vendor's ID (or use name if needed)
            $vendorId = Auth::id(); // Get vendor ID
            $vendorName = Auth::user()->name; // Get vendor name

        // Retrieve booked_hall_id from the URL
        $booked_hall_id = request('booked_hall_id');

        // Ensure booked_hall_id is not null
        if (!$booked_hall_id) {
            return redirect()->back()->with('error', 'Booked Hall ID is required.');
        }
         // ✅ Update `booked_by` field in `booked_halls` table
         BookedHall::where('id', $booked_hall_id)->update([
            'event_booked_by' => $vendorId, // or use $vendorName if you want the name
        ]);

        $eventBooking = new EventService();
        $eventBooking->booked_hall_id = $booked_hall_id; // Assign booked_hall_id
        $eventBooking->Item_id = json_encode($request->Item_id ?? []);
        $eventBooking->total_price = $request->total_price;
        $eventBooking->status = 'viewed';


        // ✅ Update `booked_by` field in `booked_halls` table
        BookedHall::where('id', $booked_hall_id)->update([
            'event_booked_by' => $vendorId, // or use $vendorName if you want the name
        ]);


        $eventBooking->save();
        $this->sendEventWhatsAppMessage($booked_hall_id, $vendorName);
        return redirect('/event-booked-halls')->with('success', 'Event Price Added Successfully');
    }

    private function sendEventWhatsAppMessage($bookedHallId, $vendorName)
    {
        $bookedHall = \App\Models\BookedHall::find($bookedHallId);

        if (!$bookedHall) {
            Log::error("❌ Booked hall not found for ID: {$bookedHallId}");
            return;
        }

        // Check if this is a multi-hall booking
        $groupedEnquiries = collect();
        if ($bookedHall->group_code) {
            $groupedEnquiries = BookedHall::where('group_code', $bookedHall->group_code)->orderBy('hall_name')->get();
        } else {
            $groupedEnquiries->push($bookedHall);
        }

        $isMultiHall = $groupedEnquiries->count() > 1;

        // Build hall information
        if ($isMultiHall) {
            $hallNames = $groupedEnquiries->pluck('hall_name')->toArray();
            $hallInfo = implode(', ', $hallNames) . ' (' . count($hallNames) . ' halls)';
        } else {
            $hallInfo = $bookedHall->hall_name ?? 'Event Hall';
        }

        $apiUrl = config('oneclick.api_url') . "/" . config('oneclick.api_version') . "/" . config('oneclick.phone_id') . "/messages";
        $token = trim(config('oneclick.api_token'));

        $contactNumber =  $bookedHall->customer_phone;
        $customerName = $bookedHall->customer_name ?? 'Customer';

        $payloadArray = [
            "to" => $contactNumber,
            "recipient_type" => "individual",
            "type" => "template",
            "template" => [
                "name" => "template6",
                "language" => [
                    "policy" => "deterministic",
                    "code" => "en"
                ],
                "components" => [
                    [
                        "type" => "body",
                        "parameters" => [
                            ["type" => "text", "text" => $customerName], // {{1}}
                            ["type" => "text", "text" => $vendorName],   // {{2}}
                            ["type" => "text", "text" => $hallInfo],     // {{3}}
                        ]
                    ]
                ]
            ]
        ];

        $payload = json_encode($payloadArray);

        // 🛜 Send via cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . $token,
            "Content-Type: application/json"
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // 🧾 Log everything
        Log::info("📤 Event WhatsApp Message Log", [
            'contact' => $contactNumber,
            'http_code' => $httpCode,
            'payload' => $payloadArray,
            'response' => $response,
            'error' => $curlError
        ]);

        if ($httpCode == 200) {
            Log::info("✅ WhatsApp event message sent to {$contactNumber}.");
        } else {
            Log::error("❌ WhatsApp event message failed for {$contactNumber}. HTTP {$httpCode} - {$response}");
        }
    }


    // Confirm Event Booking
    // public function confirmEvent($eventId)
    // {
    //     // return $eventId;
    //     $eventBooking = EventService::where('booked_hall_id',$eventId)->first();
    //     // if ($eventBooking) {
    //         $eventBooking->status = 'confirmed' ;

    //         $eventBooking->save();


    //     return redirect()->back()->with('success', 'Event booking confirmed successfully!');
    // }

    public function confirmEvent($eventId)
        {
            $bookedHall = BookedHall::find($eventId);
            if (!$bookedHall) {
                return redirect()->back()->with('error', 'Booking not found.');
            }

            // If this is a multi-hall booking, confirm all event services in the group
            if ($bookedHall->group_code) {
                $groupBookings = BookedHall::where('group_code', $bookedHall->group_code)
                                          ->where('event_flag', 1)
                                          ->get();

                foreach ($groupBookings as $groupBooking) {
                    $eventService = EventService::where('booked_hall_id', $groupBooking->id)->first();
                    if ($eventService) {
                        $eventService->status = 'confirmed';
                        $eventService->save();
                    }
                }

                $vendorName = Auth::user()->name ?? 'Vendor';
                $this->sendBookingConfirmedMessage($eventId, $vendorName, 'event');
                $this->sendBookingConfirmedMessageToAdmin($eventId, $vendorName);
            } else {
                // Single hall booking
                $eventBooking = EventService::where('booked_hall_id', $eventId)->first();
                if ($eventBooking) {
                    $eventBooking->status = 'confirmed';
                    $eventBooking->save();

                    $vendorName = Auth::user()->name ?? 'Vendor';
                    $this->sendBookingConfirmedMessage($eventId, $vendorName, 'event');
                    $this->sendBookingConfirmedMessageToAdmin($eventId, $vendorName);
                }
            }

            return redirect()->back()->with('success', 'Event booking confirmed successfully!');
        }

        private function sendBookingConfirmedMessageToAdmin($bookedHallId, $vendorName)
            {
                // Fetch booked hall data
                $bookedHall = \App\Models\BookedHall::find($bookedHallId);

                if (!$bookedHall) {
                    Log::error("❌ Booked hall not found for ID: {$bookedHallId}");
                    return;
                }

                // Check if this is a multi-hall booking
                $groupedEnquiries = collect();
                if ($bookedHall->group_code) {
                    $groupedEnquiries = BookedHall::where('group_code', $bookedHall->group_code)->orderBy('hall_name')->get();
                } else {
                    $groupedEnquiries->push($bookedHall);
                }

                $isMultiHall = $groupedEnquiries->count() > 1;

                // Build hall information
                if ($isMultiHall) {
                    $hallNames = $groupedEnquiries->pluck('hall_name')->toArray();
                    $hallName = implode(', ', $hallNames) . ' (' . count($hallNames) . ' halls)';
                } else {
                    $hallName = $bookedHall->hall_name ?? 'Event Hall';
                }

                // Build date information
                $allDates = [];
                foreach ($groupedEnquiries as $booking) {
                    $allDates[] = $booking->event_date;
                }
                $allDates = array_unique($allDates);
                sort($allDates);

                $eventDate = '';
                if (count($allDates) > 1) {
                    $eventDate = implode(', ', $allDates) . ' (' . count($allDates) . ' dates)';
                } elseif (count($allDates) == 1) {
                    $eventDate = $allDates[0];
                }

                // Fetch admin user (role = admin)
                $admin = \App\Models\User::where('role', 'admin')->first();

                if (!$admin || !$admin->mobile) {
                    Log::error("❌ Admin user or mobile number not found.");
                    return;
                }

                // Prepare values for message
                $adminContact = $admin->mobile;
                $customerName = $bookedHall->customer_name ?? 'Customer';

                // WhatsApp API details from config
                $apiUrl = config('oneclick.api_url') . "/" . config('oneclick.api_version') . "/" . config('oneclick.phone_id') . "/messages";
                $token = trim(config('oneclick.api_token'));

                // Create the payload
                $payloadArray = [
                    "to" => $adminContact,
                    "recipient_type" => "individual",
                    "type" => "template",
                    "template" => [
                        "name" => "template8", // your admin template name
                        "language" => [
                            "policy" => "deterministic",
                            "code" => "en"
                        ],
                        "components" => [
                            [
                                "type" => "body",
                                "parameters" => [
                                    ["type" => "text", "text" => $vendorName],    // {{1}} Vendor Name
                                    ["type" => "text", "text" => $customerName],  // {{2}} Customer Name
                                    ["type" => "text", "text" => $eventDate],     // {{3}} Event Date(s)
                                    ["type" => "text", "text" => $hallName],      // {{4}} Hall Name(s)
                                ]
                            ]
                        ]
                    ]
                ];

                $payload = json_encode($payloadArray);

                // Send using cURL
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $apiUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Authorization: Bearer " . $token,
                    "Content-Type: application/json"
                ]);

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlError = curl_error($ch);
                curl_close($ch);

                // Log response for debugging
                Log::info("📤 Admin WhatsApp Booking Message Log", [
                    'admin_contact' => $adminContact,
                    'http_code' => $httpCode,
                    'payload' => $payloadArray,
                    'response' => $response,
                    'error' => $curlError
                ]);

                // Handle success/failure
                if ($httpCode == 200) {
                    Log::info("✅ Booking confirmation message sent to admin: {$adminContact}.");
                } else {
                    Log::error("❌ Failed to send booking message to admin. HTTP {$httpCode} - {$response}");
                }
            }

























    // public function cateringBookedHalls()
    // {
    //     // Fetch records where catering_flag = 1
    //     $cateringBookings = BookedHall::where('catering_flag', 1)->get();

    //     // Pass data to the view
    //     return view('Catering.CateringTable', compact('cateringBookings'));
    // }

    public function cateringBookedHalls()
    {
        // Ensure the user is logged in
        if (!Auth::check()) {
            return redirect()->back()->with('error', 'You must be logged in to view bookings.');
        }

        // Get the logged-in vendor's ID
        $vendorId = Auth::id();

        // Fetch bookings where:
        // - `catering_flag = 1` (for catering-related bookings)
        // - `booked_by` is NULL (unbooked halls)
        // - OR `booked_by` matches the logged-in vendor (vendor sees only their own bookings)
        // - Include cancelled bookings so vendors know the booking was cancelled
        $cateringBookingsQuery = BookedHall::where('catering_flag', 1)
        ->where(function ($query) use ($vendorId) {
            $query->whereNull('catering_booked_by')  // Show unbooked halls
                  ->orWhere('catering_booked_by', $vendorId); // Show only vendor's own bookings
        })
        ->orderBy('created_at', 'desc')
        ->with('cateringServices');

        $cateringBookingsRaw = $cateringBookingsQuery->get();

        // Group bookings by group_code for display
        $groupedBookings = collect();
        $processedGroups = [];

        foreach ($cateringBookingsRaw as $booking) {
            if ($booking->group_code && !in_array($booking->group_code, $processedGroups)) {
                // This is a multi-hall booking group
                $groupBookings = BookedHall::where('group_code', $booking->group_code)
                                           ->where('catering_flag', 1)
                                           ->where(function ($query) use ($vendorId) {
                                               $query->whereNull('catering_booked_by')
                                                     ->orWhere('catering_booked_by', $vendorId);
                                           })
                                           ->with('cateringServices')
                                           ->get();

                // Create a representative booking object for the group
                $groupRep = $groupBookings->first();
                $groupRep->is_group = true;

                // Combine hall names
                $hallNames = $groupBookings->pluck('hall_name')->implode(', ');
                $groupRep->hall_name = $hallNames;
                $groupRep->group_count = $groupBookings->count();

                // Combine catering services from all halls in the group
                $allCateringServices = collect();
                foreach ($groupBookings as $groupBooking) {
                    $allCateringServices = $allCateringServices->merge($groupBooking->cateringServices);
                }
                $groupRep->cateringServices = $allCateringServices;

                $groupedBookings->push($groupRep);
                $processedGroups[] = $booking->group_code;
            } elseif (!$booking->group_code) {
                // This is a single hall booking
                $booking->is_group = false;
                $groupedBookings->push($booking);
            }
        }

        return view('Catering.CateringTable', ['cateringBookings' => $groupedBookings]);
    }




    public function viewCateringBooking($id){
        $cateringBooking = BookedHall::with('cateringServices')->findOrFail($id);

        // Check if this is a multi-hall booking
        $groupedEnquiries = collect();
        if ($cateringBooking->group_code) {
            $groupedEnquiries = BookedHall::where('group_code', $cateringBooking->group_code)
                                         ->with('cateringServices')
                                         ->orderBy('hall_name')
                                         ->get();
        } else {
            $groupedEnquiries->push($cateringBooking);
        }

        $isMultiHall = $groupedEnquiries->count() > 1;

        // If multi-hall, create a combined representation
        if ($isMultiHall) {
            // Create a merged object with combined information
            $combinedBooking = clone $groupedEnquiries->first();

            // Combine hall names
            $hallNames = $groupedEnquiries->pluck('hall_name')->toArray();
            $combinedBooking->hall_name = implode(', ', $hallNames);
            $combinedBooking->group_count = $groupedEnquiries->count();

            // Combine all catering services
            $allCateringServices = collect();
            foreach ($groupedEnquiries as $booking) {
                $allCateringServices = $allCateringServices->merge($booking->cateringServices);
            }
            $combinedBooking->cateringServices = $allCateringServices;

            // Collect all event dates for display
            $allDates = $groupedEnquiries->pluck('event_date')->unique()->filter()->values()->toArray();

            // Group bookings by hall name and collect all timing info per hall
            $hallTimingInfo = [];
            $groupedByHall = $groupedEnquiries->groupBy('hall_name');

            foreach ($groupedByHall as $hallName => $hallBookings) {
                $hallInfo = [
                    'hall_name' => $hallName,
                    'dates_times' => []
                ];

                foreach ($hallBookings as $booking) {
                    $hallInfo['dates_times'][] = [
                        'event_date' => $booking->event_date,
                        'start_time' => $booking->start_time,
                        'end_time' => $booking->end_time,
                        'duration' => $booking->duration
                    ];
                }

                $hallTimingInfo[] = $hallInfo;
            }

            // Format dates and hall timing info for display
            $combinedBooking->all_event_dates = $allDates;
            $combinedBooking->hall_timing_info = $hallTimingInfo;

            $cateringBooking = $combinedBooking;
        }

        // ✅ Update `booked_by` field for each service
        foreach ($cateringBooking->cateringServices as $service) {
            if (isset($service->Item_id)) {
                $itemIds = json_decode($service->Item_id, true); // Decode stored JSON array
                // Fetch item names based on IDs and store in a new attribute
                $service->item_names = Cateringitem::whereIn('id', $itemIds)->pluck('item_name')->toArray();
            } else {
                $service->item_names = [];
            }
        }

        // Pass data to the view
        return view('Catering.CateringViewBooking', compact('cateringBooking'));
    }


    public function CreateCateringService($booked_hall_id){
        $cateringitems = Cateringitem::all();
        return view('Catering.AddCateringService', compact('booked_hall_id','cateringitems'));
    }


    public function AddCateringService(Request $request,$booked_hall_id){

        // $validated = $request->validate([
        //     'booked_hall_id' => 'required|exists:booked_halls,id',
        //     'service_name' => 'required|string|max:255',
        //     'service_price' => 'required|numeric',
        //     'quantity' => 'required|integer|min:1',
        //     'total_price' => 'required|numeric'
        // ]);

        if (!Auth::check()) {
            return redirect()->back()->with('error', 'You must be logged in to book an event.');
        }

        // Get the logged-in vendor's ID (or use name if needed)
        $vendorId = Auth::id(); // Get vendor ID
        $vendorName = Auth::user()->name; // Get vendor name



        // Retrieve booked_hall_id from the URL
        $booked_hall_id = request('booked_hall_id');

        // Ensure booked_hall_id is not null
        if (!$booked_hall_id) {
            return redirect()->back()->with('error', 'Booked Hall ID is required.');
        }

        $cateringBooking = new CateringService();
        $cateringBooking->booked_hall_id = $booked_hall_id; // Assign booked_hall_id
        $cateringBooking->Item_id = json_encode($request->Item_id ?? []);
        $cateringBooking->total_price = $request->total_price;
        $cateringBooking->status = 'viewed';

        // ✅ Update `booked_by` field in `booked_halls` table
        BookedHall::where('id', $booked_hall_id)->update([
            'catering_booked_by' => $vendorId, // or use $vendorName if you want the name
        ]);



        $cateringBooking->save();

        $this->sendCateringWhatsAppMessage($booked_hall_id, $vendorName);
        return redirect('/catering-booked-halls')->with('success', 'Catering Service Added Successfully');
    }



        private function sendCateringWhatsAppMessage($bookedHallId, $vendorName)
            {
                $bookedHall = \App\Models\BookedHall::find($bookedHallId);

                if (!$bookedHall) {
                    Log::error("❌ Booked hall not found for ID: {$bookedHallId}");
                    return;
                }

                // Check if this is a multi-hall booking
                $groupedEnquiries = collect();
                if ($bookedHall->group_code) {
                    $groupedEnquiries = BookedHall::where('group_code', $bookedHall->group_code)->orderBy('hall_name')->get();
                } else {
                    $groupedEnquiries->push($bookedHall);
                }

                $isMultiHall = $groupedEnquiries->count() > 1;

                // Build hall information
                if ($isMultiHall) {
                    $hallNames = $groupedEnquiries->pluck('hall_name')->toArray();
                    $hallInfo = implode(', ', $hallNames) . ' (' . count($hallNames) . ' halls)';
                } else {
                    $hallInfo = $bookedHall->hall_name ?? 'Event Hall';
                }

                $apiUrl = config('oneclick.api_url') . "/" . config('oneclick.api_version') . "/" . config('oneclick.phone_id') . "/messages";
                $token = trim(config('oneclick.api_token'));

                $contactNumber =  $bookedHall->customer_phone;
                $customerName = $bookedHall->customer_name ?? 'Customer';

                $payloadArray = [
                    "to" => $contactNumber,
                    "recipient_type" => "individual",
                    "type" => "template",
                    "template" => [
                        "name" => "template6",
                        "language" => [
                            "policy" => "deterministic",
                            "code" => "en"
                        ],
                        "components" => [
                            [
                                "type" => "body",
                                "parameters" => [
                                    ["type" => "text", "text" => $customerName], // {{1}}
                                    ["type" => "text", "text" => $vendorName],   // {{2}}
                                    ["type" => "text", "text" => $hallInfo],     // {{3}}
                                ]
                            ]
                        ]
                    ]
                ];

                $payload = json_encode($payloadArray);

                // 🛜 Send via cURL
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $apiUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Authorization: Bearer " . $token,
                    "Content-Type: application/json"
                ]);

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlError = curl_error($ch);
                curl_close($ch);

                // 🧾 Log everything
                Log::info("📤 Catering WhatsApp Message Log", [
                    'contact' => $contactNumber,
                    'http_code' => $httpCode,
                    'payload' => $payloadArray,
                    'response' => $response,
                    'error' => $curlError
                ]);

                if ($httpCode == 200) {
                    Log::info("✅ WhatsApp catering message sent to {$contactNumber}.");
                } else {
                    Log::error("❌ WhatsApp catering message failed for {$contactNumber}. HTTP {$httpCode} - {$response}");
                }
            }







    // public function confirmCatering($cateringId)
    // {

    //     $cateringBooking = CateringService::where('booked_hall_id',$cateringId)->first();
    //     if ($cateringBooking) {
    //         $cateringBooking->status = 'confirmed';
    //         $cateringBooking->save();
    //     }

    //     return redirect()->back()->with('success', 'Catering booking confirmed successfully!');
    // }

    public function confirmCatering($cateringId)
        {
            $bookedHall = BookedHall::find($cateringId);
            if (!$bookedHall) {
                return redirect()->back()->with('error', 'Booking not found.');
            }

            // If this is a multi-hall booking, confirm all catering services in the group
            if ($bookedHall->group_code) {
                $groupBookings = BookedHall::where('group_code', $bookedHall->group_code)
                                          ->where('catering_flag', 1)
                                          ->get();

                foreach ($groupBookings as $groupBooking) {
                    $cateringService = CateringService::where('booked_hall_id', $groupBooking->id)->first();
                    if ($cateringService) {
                        $cateringService->status = 'confirmed';
                        $cateringService->save();
                    }
                }

                $vendorName = Auth::user()->name ?? 'Vendor';
                $this->sendBookingConfirmedMessage($cateringId, $vendorName, 'catering');
                $this->sendBookingConfirmedMessageToAdminForCatering($cateringId, $vendorName);
            } else {
                // Single hall booking
                $cateringBooking = CateringService::where('booked_hall_id', $cateringId)->first();
                if ($cateringBooking) {
                    $cateringBooking->status = 'confirmed';
                    $cateringBooking->save();

                    $vendorName = Auth::user()->name ?? 'Vendor';
                    $this->sendBookingConfirmedMessage($cateringId, $vendorName, 'catering');
                    $this->sendBookingConfirmedMessageToAdminForCatering($cateringId, $vendorName);
                }
            }

            return redirect()->back()->with('success', 'Catering booking confirmed successfully!');
        }

        private function sendBookingConfirmedMessageToAdminForCatering($bookedHallId, $vendorName)
            {
                // Fetch booked hall data
                $bookedHall = \App\Models\BookedHall::find($bookedHallId);

                if (!$bookedHall) {
                    Log::error("❌ Booked hall not found for ID: {$bookedHallId}");
                    return;
                }

                // Check if this is a multi-hall booking
                $groupedEnquiries = collect();
                if ($bookedHall->group_code) {
                    $groupedEnquiries = BookedHall::where('group_code', $bookedHall->group_code)->orderBy('hall_name')->get();
                } else {
                    $groupedEnquiries->push($bookedHall);
                }

                $isMultiHall = $groupedEnquiries->count() > 1;

                // Build hall information
                if ($isMultiHall) {
                    $hallNames = $groupedEnquiries->pluck('hall_name')->toArray();
                    $hallName = implode(', ', $hallNames) . ' (' . count($hallNames) . ' halls)';
                } else {
                    $hallName = $bookedHall->hall_name ?? 'Catering Hall';
                }

                // Build date information
                $allDates = [];
                foreach ($groupedEnquiries as $booking) {
                    $allDates[] = $booking->event_date;
                }
                $allDates = array_unique($allDates);
                sort($allDates);

                $eventDate = '';
                if (count($allDates) > 1) {
                    $eventDate = implode(', ', $allDates) . ' (' . count($allDates) . ' dates)';
                } elseif (count($allDates) == 1) {
                    $eventDate = $allDates[0];
                }

                // Fetch admin user (role = admin)
                $admin = \App\Models\User::where('role', 'admin')->first();

                if (!$admin || !$admin->mobile) {
                    Log::error("❌ Admin user or mobile number not found.");
                    return;
                }

                // Prepare values for message
                $adminContact = $admin->mobile;
                $customerName = $bookedHall->customer_name ?? 'Customer';

                // WhatsApp API details from config
                $apiUrl = config('oneclick.api_url') . "/" . config('oneclick.api_version') . "/" . config('oneclick.phone_id') . "/messages";
                $token = trim(config('oneclick.api_token'));

                // Create the payload
                $payloadArray = [
                    "to" => $adminContact,
                    "recipient_type" => "individual",
                    "type" => "template",
                    "template" => [
                        "name" => "template8", // admin message template name
                        "language" => [
                            "policy" => "deterministic",
                            "code" => "en"
                        ],
                        "components" => [
                            [
                                "type" => "body",
                                "parameters" => [
                                    ["type" => "text", "text" => $vendorName],    // {{1}} Vendor Name
                                    ["type" => "text", "text" => $customerName],  // {{2}} Customer Name
                                    ["type" => "text", "text" => $eventDate],     // {{3}} Event Date(s)
                                    ["type" => "text", "text" => $hallName],      // {{4}} Hall Name(s)
                                ]
                            ]
                        ]
                    ]
                ];

                $payload = json_encode($payloadArray);

                // Send using cURL
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $apiUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Authorization: Bearer " . $token,
                    "Content-Type: application/json"
                ]);

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlError = curl_error($ch);
                curl_close($ch);

                // Log response for debugging
                Log::info("📤 Admin WhatsApp Catering Message Log", [
                    'admin_contact' => $adminContact,
                    'http_code' => $httpCode,
                    'payload' => $payloadArray,
                    'response' => $response,
                    'error' => $curlError
                ]);

                // Handle success/failure
                if ($httpCode == 200) {
                    Log::info("✅ Catering confirmation message sent to admin: {$adminContact}.");
                } else {
                    Log::error("❌ Failed to send catering message to admin. HTTP {$httpCode} - {$response}");
                }
            }








    private function sendBookingConfirmedMessage($bookedHallId, $vendorName, $type = 'event')
            {
                $bookedHall = \App\Models\BookedHall::find($bookedHallId);

                if (!$bookedHall) {
                    Log::error("❌ Booked hall not found for ID: {$bookedHallId}");
                    return;
                }

                // Check if this is a multi-hall booking
                $groupedEnquiries = collect();
                if ($bookedHall->group_code) {
                    $groupedEnquiries = BookedHall::where('group_code', $bookedHall->group_code)->orderBy('hall_name')->get();
                } else {
                    $groupedEnquiries->push($bookedHall);
                }

                $isMultiHall = $groupedEnquiries->count() > 1;

                // Build hall information
                if ($isMultiHall) {
                    $hallNames = $groupedEnquiries->pluck('hall_name')->toArray();
                    $hallName = implode(', ', $hallNames) . ' (' . count($hallNames) . ' halls)';
                } else {
                    $hallName = $bookedHall->hall_name ?? 'Event Hall';
                }

                // Build date information
                $allDates = [];
                foreach ($groupedEnquiries as $booking) {
                    $allDates[] = $booking->event_date;
                }
                $allDates = array_unique($allDates);
                sort($allDates);

                $eventDate = '';
                if (count($allDates) > 1) {
                    $eventDate = implode(', ', $allDates) . ' (' . count($allDates) . ' dates)';
                } elseif (count($allDates) == 1) {
                    $eventDate = $allDates[0];
                }

                $apiUrl = config('oneclick.api_url') . "/" . config('oneclick.api_version') . "/" . config('oneclick.phone_id') . "/messages";
                $token = trim(config('oneclick.api_token'));

                $contactNumber = $bookedHall->customer_phone;
                $customerName = $bookedHall->customer_name ?? 'Customer';

                $payloadArray = [
                    "to" => $contactNumber,
                    "recipient_type" => "individual",
                    "type" => "template",
                    "template" => [
                        "name" => "template7",
                        "language" => [
                            "policy" => "deterministic",
                            "code" => "en"
                        ],
                        "components" => [
                            [
                                "type" => "body",
                                "parameters" => [
                                    ["type" => "text", "text" => $customerName],   // {{1}}
                                    ["type" => "text", "text" => $hallName],       // {{2}}
                                    ["type" => "text", "text" => $eventDate],      // {{3}}
                                    ["type" => "text", "text" => $vendorName],     // {{4}}
                                ]
                            ]
                        ]
                    ]
                ];

                $payload = json_encode($payloadArray);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $apiUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Authorization: Bearer " . $token,
                    "Content-Type: application/json"
                ]);

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlError = curl_error($ch);
                curl_close($ch);

                Log::info("📤 Vendor Booking Confirmed WhatsApp Message Log", [
                    'contact' => $contactNumber,
                    'http_code' => $httpCode,
                    'payload' => $payloadArray,
                    'response' => $response,
                    'error' => $curlError
                ]);

                if ($httpCode == 200) {
                    Log::info("✅ Vendor booking confirmation message sent to {$contactNumber}.");
                } else {
                    Log::error("❌ Vendor booking confirmation failed for {$contactNumber}. HTTP {$httpCode} - {$response}");
                }
            }



}
