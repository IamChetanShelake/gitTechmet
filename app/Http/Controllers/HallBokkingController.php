<?php

namespace App\Http\Controllers;

use App\Models\Eventitem;
use App\Models\BookedHall;
use App\Models\HallEnquiry;
use App\Models\Cateringitem;
use App\Models\EventService;
use Illuminate\Http\Request;
use App\Models\CateringService;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\Log;

class HallBokkingController extends Controller
{
    public function index(){
        // Fetch all booked halls with payment transaction data (excluding cancelled ones)
        $bookedHalls = BookedHall::with('paymentTransactions')->whereNull('cancelled_at')->orderBy('created_at', 'desc')->get();
        $eventServices = EventService::all();
        $cateringServices = CateringService::all();

        // Add payment status to each booked hall
        foreach ($bookedHalls as $bookedHall) {
            $latestTransaction = $bookedHall->paymentTransactions()
                ->orderBy('created_at', 'desc')
                ->first();

            if ($latestTransaction) {
                $bookedHall->payment_status = $latestTransaction->status;
                $bookedHall->payment_amount = $latestTransaction->amount;
                $bookedHall->payment_date = $latestTransaction->payment_date;
            } else {
                $bookedHall->payment_status = 'Not Initiated';
                $bookedHall->payment_amount = 0;
                $bookedHall->payment_date = null;
            }
        }

        return view('admin.BookedHall.bookedHall', compact('bookedHalls','eventServices','cateringServices'));
    }

    public function view($id){
        // Fetch the booked hall record by ID
        $bookedHall = BookedHall::findOrFail($id);

        // Return the view with the booked hall data
        return view('admin.BookedHall.ViewBooked', compact('bookedHall'));

    }


    public function confirmBooking($id, Request $request)
    {
        // Fetch hall enquiry record
        $hallenquiry = HallEnquiry::findOrFail($id);

        // If this enquiry has a group_code, confirm all enquiries in the group
        if ($hallenquiry->group_code) {
            $groupEnquiries = HallEnquiry::where('group_code', $hallenquiry->group_code)->get();

            foreach ($groupEnquiries as $groupEnquiry) {
                $this->createBookedHallRecord($groupEnquiry);
            }

            // Send WhatsApp for the representative enquiry
            $bookingCode = BookedHall::where('group_code', $hallenquiry->group_code)->first()->booking_code;
            $this->sendWhatsappMessage($hallenquiry, $bookingCode);
            $this->sendWhatsappToVendors($hallenquiry);
        } else {
            // Single hall booking (existing logic)
            $this->createBookedHallRecord($hallenquiry);
            $bookingCode = BookedHall::where('hall_enquiry_id', $hallenquiry->id)->first()->booking_code ?? null;
            $this->sendWhatsappMessage($hallenquiry, $bookingCode);
            $this->sendWhatsappToVendors($hallenquiry);
        }

        return redirect('/AdminHallEnquiry')->with('success', 'Booking Confirmed Successfully');
    }

    private function createBookedHallRecord($hallenquiry)
    {
        // Extract vendor data (convert JSON string to array)
        $vendorServices = json_decode($hallenquiry->vendor, true) ?? [];

        // Initialize event and catering flags
        $eventFlag = in_array('event', $vendorServices) ? '1' : '0';
        $cateringFlag = in_array('catering', $vendorServices) ? '1' : '0';

        // Fetch total rent from hallenquirys table
        $totalRent = $hallenquiry->rent_amount;
        $totalDeposit = $hallenquiry->deposit;

        // Calculate accessories amount
        $accessoriesAmount = 0;
        if ($hallenquiry->start_time && $hallenquiry->end_time) {
            $startTime = \Carbon\Carbon::createFromFormat('H:i:s', $hallenquiry->start_time . ':00');
            $endTime = \Carbon\Carbon::createFromFormat('H:i:s', $hallenquiry->end_time . ':00');
            $totalHours = $startTime->diffInHours($endTime, false);

            // Get number of days
            $dates = $hallenquiry->event_dates ? json_decode($hallenquiry->event_dates, true) : [$hallenquiry->event_date];
            $dates = array_filter($dates);
            $numberOfDays = count($dates);

            if ($hallenquiry->accessorie) {
                $accessoryIds = json_decode($hallenquiry->accessorie, true);
                if (is_array($accessoryIds)) {
                    $accessories = \App\Models\Accessorie::whereIn('id', $accessoryIds)->get();
                    $accessoriesAmount = $accessories->sum(function ($accessory) use ($totalHours, $numberOfDays) {
                        $price = (float) ($accessory->price ?? 0);
                        $hours = (float) ($accessory->hours ?? 1);
                        if ($price <= 0 || $hours <= 0) return 0;

                        $blocksPerDay = floor($totalHours / $hours);
                        $pricePerDay = $price * max($blocksPerDay, 1);
                        return $pricePerDay * $numberOfDays;
                    });
                }
            }
        }

        // Calculate final amount (Rent + Accessories + GST + Deposit)
        $subtotal = $totalRent + $accessoriesAmount;
        $gst = $subtotal * 0.18; // 18% GST
        $finalAmount = $subtotal + $gst + $totalDeposit;

        // Set initial payment details
        $paidAmount = $finalAmount; // Store final amount as paid amount (considered committed)
        $remainingAmount = 0; // No remaining amount initially

        // Generate a unique 6-digit booking code
        do {
            $bookingCode = mt_rand(100000, 999999);
        } while (BookedHall::where('booking_code', $bookingCode)->exists());

        // Create a new booked hall entry
        $bookedHall = new BookedHall();
        $bookedHall->group_code = $hallenquiry->group_code; // Include group_code
        $bookedHall->hall_enquiry_id = $hallenquiry->id;
        $bookedHall->customer_name = $hallenquiry->name;
        $bookedHall->customer_phone = $hallenquiry->contact_no;
        $bookedHall->customer_email = $hallenquiry->email;
        $bookedHall->event_date = $hallenquiry->event_date;
        $bookedHall->event_type = $hallenquiry->event_type;
        $bookedHall->hall_name = $hallenquiry->hall;
        $bookedHall->duration = $hallenquiry->duration;
        $bookedHall->start_time = $hallenquiry->start_time;
        $bookedHall->end_time = $hallenquiry->end_time;
        $bookedHall->catering_flag = $cateringFlag;
        $bookedHall->event_flag = $eventFlag;
        $bookedHall->total_rent = $totalRent;
        $bookedHall->total_deposit = $totalDeposit;
        $bookedHall->paid_amount = $paidAmount;
        $bookedHall->remaining_amount = $remainingAmount;
        $bookedHall->booking_code = $bookingCode;
        $bookedHall->save();

        // Update the status of hall enquiry to "confirmed"
        $hallenquiry->status = 'confirmed';
        $hallenquiry->save();

        return $bookedHall;
    }

    private function sendWhatsappMessage($hallenquiry, $bookingCode)
    {
        // Check if this is a multi-hall enquiry
        $groupedEnquiries = collect();
        if ($hallenquiry->group_code) {
            $groupedEnquiries = HallEnquiry::where('group_code', $hallenquiry->group_code)->orderBy('hall')->get();
        } else {
            $groupedEnquiries->push($hallenquiry);
        }

        $isMultiHall = $groupedEnquiries->count() > 1;

        // Build hall information
        if ($isMultiHall) {
            $hallNames = $groupedEnquiries->pluck('hall')->toArray();
            $hallInfo = implode(', ', $hallNames) . ' (' . count($hallNames) . ' halls)';
        } else {
            $hallInfo = $hallenquiry->hall ?? 'N/A';
        }

        $apiUrl = config('oneclick.api_url') . "/" . config('oneclick.api_version') . "/" . config('oneclick.phone_id') . "/messages";
        $token = trim(config('oneclick.api_token'));

        // Format contact number
        $contactNumber = "+91" . $hallenquiry->contact_no;

        // Fetch admin mobile number
        $admin = \App\Models\User::where('role', 'admin')->first();
        $adminMobile = $admin->mobile ?? 'Not Provided';

        // Build payload for the WhatsApp API
        $payloadArray = [
            "to" => $contactNumber,
            "recipient_type" => "individual",
            "type" => "template",
            "template" => [
                "name" => "test11",
                "language" => [
                    "policy" => "deterministic",
                    "code" => "en"
                ],
                "components" => [
                    [
                        "type" => "body",
                        "parameters" => [
                            ["type" => "text", "text" => $hallenquiry->name],               // {{1}} - Customer Name
                            ["type" => "text", "text" => $hallInfo],                        // {{2}} - Hall Name(s)
                            ["type" => "text", "text" => $adminMobile],                    // {{3}} - Admin Mobile
                            ["type" => "text", "text" => $bookingCode]                     // {{4}} - Booking Code
                        ]
                    ]
                ]
            ]
        ];

        $payload = json_encode($payloadArray);

        // Send request via cURL
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

        // 📝 Log everything
        Log::info("📤 WhatsApp Message Sent", [
            'contact' => $contactNumber,
            'code' => $httpCode,
            'response' => $response,
            'payload' => $payloadArray,
            'curl_error' => $curlError
        ]);

        if ($httpCode == 200) {
            Log::info("✅ WhatsApp confirmation sent to {$contactNumber}.");
        } else {
            Log::error("❌ WhatsApp message failed for {$contactNumber}. HTTP {$httpCode} - {$response}");
        }
    }

    private function sendWhatsappToVendors($hallenquiry)
    {
        // Check if this is a multi-hall enquiry
        $groupedEnquiries = collect();
        if ($hallenquiry->group_code) {
            $groupedEnquiries = HallEnquiry::where('group_code', $hallenquiry->group_code)->orderBy('hall')->get();
        } else {
            $groupedEnquiries->push($hallenquiry);
        }

        $isMultiHall = $groupedEnquiries->count() > 1;

        // Build hall information
        if ($isMultiHall) {
            $hallNames = $groupedEnquiries->pluck('hall')->toArray();
            $eventType = implode(', ', $hallNames) . ' (' . count($hallNames) . ' halls)';
        } else {
            $eventType = $hallenquiry->hall ?? 'N/A';
        }

        $vendorsSelected = json_decode($hallenquiry->vendor, true) ?? [];

        if (empty($vendorsSelected)) {
            Log::info("🚫 No vendors selected for enquiry ID {$hallenquiry->id}");
            return;
        }

        foreach ($vendorsSelected as $vendorType) {
            $vendors = \App\Models\User::where('role', $vendorType)->get();

            foreach ($vendors as $vendor) {
                $vendorName = $vendor->name;
                $customerName = $hallenquiry->name;

                $payload = [
                    "to" => "+91" . $vendor->mobile,
                    "recipient_type" => "individual",
                    "type" => "template",
                    "template" => [
                        "language" => [
                            "policy" => "deterministic",
                            "code" => "en"
                        ],
                        "name" => "template5",
                        "components" => [
                            [
                                "type" => "body",
                                "parameters" => [
                                    ["type" => "text", "text" => $vendorName],     // {{1}} Vendor Name
                                    ["type" => "text", "text" => $customerName],   // {{2}} Customer Name
                                    ["type" => "text", "text" => $eventType]       // {{3}} Event Type (Hall(s))
                                ]
                            ]
                        ]
                    ]
                ];

                // Send cURL request
                $this->sendWhatsAppCurlRequest($payload);
            }
        }
    }

    private function sendWhatsAppCurlRequest($payloadArray)
    {
        $apiUrl = config('oneclick.api_url') . "/" . config('oneclick.api_version') . "/" . config('oneclick.phone_id') . "/messages";
        $token = trim(config('oneclick.api_token'));

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

        Log::info("📩 Vendor WhatsApp Message", [
            'payload' => $payloadArray,
            'response' => $response,
            'http_code' => $httpCode,
            'curl_error' => $curlError
        ]);

        if ($httpCode == 200) {
            Log::info("✅ Message sent to vendor successfully.");
        } else {
            Log::error("❌ Failed to send message to vendor. HTTP {$httpCode} - {$response}");
        }
    }

    public function updateStatus(Request $request)
    {
        $serviceType = $request->service_type;
        $serviceId = $request->service_id;
        $status = $request->status;

        if ($serviceType === 'event') {
            $service = EventService::find($serviceId);
        } else {
            $service = CateringService::find($serviceId);
        }

        if ($service) {
            $service->status = $status;
            $service->save();
            // Call sendWhatsappMessageToVendors and sendWhatsappMessageToUser when the status is approved
            if ($status === 'approved') {
                $this->sendWhatsappMessageToVendors($service);
                $this->sendWhatsappMessageToUser($service);
            }
            return back()->with('success', 'Status updated successfully.');
        }

        return back()->with('fail', 'Service not found.');
    }

    private function sendWhatsappMessageToVendors($service)
    {
        // Determine the service type and fetch related booked hall
        $bookedHall = BookedHall::find($service->booked_hall_id);
        if (!$bookedHall) {
            Log::error("❌ No booked hall found for service ID {$service->id}");
            return;
        }

        // Fetch hall enquiry to get hall name and event date
        $hallEnquiry = HallEnquiry::find($bookedHall->hall_enquiry_id);
        if (!$hallEnquiry) {
            Log::error("❌ No hall enquiry found for booked hall ID {$bookedHall->id}");
            return;
        }

        // Check if this is a multi-hall booking
        $groupedEnquiries = collect();
        if ($hallEnquiry->group_code) {
            $groupedEnquiries = HallEnquiry::where('group_code', $hallEnquiry->group_code)->orderBy('hall')->get();
        } else {
            $groupedEnquiries->push($hallEnquiry);
        }

        $isMultiHall = $groupedEnquiries->count() > 1;

        // Build hall information
        if ($isMultiHall) {
            $hallNames = $groupedEnquiries->pluck('hall')->toArray();
            $hallName = implode(', ', $hallNames) . ' (' . count($hallNames) . ' halls)';
        } else {
            $hallName = $hallEnquiry->hall ?? 'N/A';
        }

        // Build date information
        $allDates = [];
        foreach ($groupedEnquiries as $enquiry) {
            $dates = $enquiry->event_dates ? json_decode($enquiry->event_dates, true) : [$enquiry->event_date];
            $dates = array_filter($dates);
            $allDates = array_merge($allDates, $dates);
        }
        $allDates = array_unique($allDates);
        sort($allDates);

        $eventDate = '';
        if (count($allDates) > 1) {
            $eventDate = implode(', ', $allDates) . ' (' . count($allDates) . ' dates)';
        } elseif (count($allDates) == 1) {
            $eventDate = $allDates[0];
        }

        // Determine vendor role based on service type
        $vendorRole = $service instanceof EventService ? 'event' : 'catering';

        // Fetch vendors with the specified role
        $vendors = \App\Models\User::where('role', $vendorRole)->get();

        if ($vendors->isEmpty()) {
            Log::info("🚫 No vendors found for role {$vendorRole} for service ID {$service->id}");
            return;
        }

        foreach ($vendors as $vendor) {
            $vendorName = $vendor->name;

            // Build payload for WhatsApp API
            $payload = [
                "to" => "+91" . $vendor->mobile,
                "recipient_type" => "individual",
                "type" => "template",
                "template" => [
                    "language" => [
                        "policy" => "deterministic",
                        "code" => "en"
                    ],
                    "name" => "template9",
                    "components" => [
                        [
                            "type" => "body",
                            "parameters" => [
                                ["type" => "text", "text" => $vendorName],    // {{1}} Vendor Name
                                ["type" => "text", "text" => $hallName],      // {{2}} Hall Name(s)
                                ["type" => "text", "text" => $eventDate]      // {{3}} Event Date(s)
                            ]
                        ]
                    ]
                ]
            ];

            // Send cURL request using existing method
            $this->sendWhatsAppCurlRequest($payload);
        }
    }

    private function sendWhatsappMessageToUser($service)
    {
        // Fetch related booked hall
        $bookedHall = BookedHall::find($service->booked_hall_id);
        if (!$bookedHall) {
            Log::error("❌ No booked hall found for service ID {$service->id}");
            return;
        }

        // Fetch hall enquiry to get customer details, hall name, and event date
        $hallEnquiry = HallEnquiry::find($bookedHall->hall_enquiry_id);
        if (!$hallEnquiry) {
            Log::error("❌ No hall enquiry found for booked hall ID {$bookedHall->id}");
            return;
        }

        // Check if this is a multi-hall booking
        $groupedEnquiries = collect();
        if ($hallEnquiry->group_code) {
            $groupedEnquiries = HallEnquiry::where('group_code', $hallEnquiry->group_code)->orderBy('hall')->get();
        } else {
            $groupedEnquiries->push($hallEnquiry);
        }

        $isMultiHall = $groupedEnquiries->count() > 1;

        // Build hall information
        if ($isMultiHall) {
            $hallNames = $groupedEnquiries->pluck('hall')->toArray();
            $hallName = implode(', ', $hallNames) . ' (' . count($hallNames) . ' halls)';
        } else {
            $hallName = $hallEnquiry->hall ?? 'N/A';
        }

        // Build date information
        $allDates = [];
        foreach ($groupedEnquiries as $enquiry) {
            $dates = $enquiry->event_dates ? json_decode($enquiry->event_dates, true) : [$enquiry->event_date];
            $dates = array_filter($dates);
            $allDates = array_merge($allDates, $dates);
        }
        $allDates = array_unique($allDates);
        sort($allDates);

        $eventDate = '';
        if (count($allDates) > 1) {
            $eventDate = implode(', ', $allDates) . ' (' . count($allDates) . ' dates)';
        } elseif (count($allDates) == 1) {
            $eventDate = $allDates[0];
        }

        // Format contact number
        $contactNumber = "+91" . $hallEnquiry->contact_no;
        $customerName = $hallEnquiry->name;

        // Build payload for WhatsApp API
        $payload = [
            "to" => $contactNumber,
            "recipient_type" => "individual",
            "type" => "template",
            "template" => [
                "language" => [
                    "policy" => "deterministic",
                    "code" => "en"
                ],
                "name" => "template10",
                "components" => [
                    [
                        "type" => "body",
                        "parameters" => [
                            ["type" => "text", "text" => $customerName],    // {{1}} Customer Name
                            ["type" => "text", "text" => $hallName],       // {{2}} Hall Name(s)
                            ["type" => "text", "text" => $eventDate]       // {{3}} Event Date(s)
                        ]
                    ]
                ]
            ]
        ];

        // Send cURL request using existing method
        $this->sendWhatsAppCurlRequest($payload);
    }

    public function cancelledBookings()
    {
        // Fetch cancelled bookings
        $cancelledBookings = BookedHall::with('paymentTransactions')->whereNotNull('cancelled_at')->get();

        // Add payment status and type to cancelled bookings
        foreach ($cancelledBookings as $booking) {
            $latestTransaction = $booking->paymentTransactions()
                ->orderBy('created_at', 'desc')
                ->first();

            if ($latestTransaction) {
                $booking->payment_status = $latestTransaction->status;
                $booking->payment_amount = $latestTransaction->amount;
                $booking->payment_date = $latestTransaction->payment_date;
            } else {
                $booking->payment_status = 'Not Initiated';
                $booking->payment_amount = 0;
                $booking->payment_date = null;
            }
            $booking->record_type = 'Booking';
        }

        // Fetch cancelled enquiries
        $cancelledEnquiries = HallEnquiry::whereNotNull('cancelled_at')->get();

        // Add type to cancelled enquiries
        foreach ($cancelledEnquiries as $enquiry) {
            $enquiry->record_type = 'Enquiry';
            $enquiry->payment_status = 'N/A';
            $enquiry->payment_amount = 0;
            $enquiry->payment_date = null;
        }

        // Combine both collections
        $allCancelledRecords = $cancelledBookings->concat($cancelledEnquiries);

        // Group records by group_code for multi-hall display
        $groupedCancelledRecords = collect();
        $processedGroups = [];

        foreach ($allCancelledRecords as $record) {
            $groupCode = $record->group_code ?? null;

            if ($groupCode && !in_array($groupCode, $processedGroups)) {
                // This is a multi-hall group - create a representative record
                $groupRecords = $allCancelledRecords->where('group_code', $groupCode);

                // Create a representative record for the group
                $groupRep = $groupRecords->first();
                $groupRep->is_group = true;

                // Combine hall names
                $hallNames = $groupRecords->pluck($groupRep->record_type == 'Booking' ? 'hall_name' : 'hall')->unique()->implode(', ');
                if ($groupRep->record_type == 'Booking') {
                    $groupRep->hall_name = $hallNames;
                } else {
                    $groupRep->hall = $hallNames;
                }
                $groupRep->group_count = $groupRecords->count();

                // Get the earliest cancelled_at date from the group
                $groupRep->cancelled_at = $groupRecords->min('cancelled_at');

                $groupedCancelledRecords->push($groupRep);
                $processedGroups[] = $groupCode;
            } elseif (!$groupCode) {
                // This is a single hall record
                $record->is_group = false;
                $record->group_count = 1;
                $groupedCancelledRecords->push($record);
            }
        }

        // Sort by cancelled_at date (most recent first)
        $cancelledRecords = $groupedCancelledRecords->sortByDesc('cancelled_at');

        return view('admin.BookedHall.cancelledBookings', compact('cancelledRecords'));
    }

    public function ViewEventCatering($id)
    {
        $bookedHall = BookedHall::find($id);
        if (!$bookedHall) {
            return redirect()->back()->with('error', 'Booking not found.');
        }

        // If this is a multi-hall booking, get services from all halls in the group
        if ($bookedHall->group_code) {
            $groupBookings = BookedHall::where('group_code', $bookedHall->group_code)->get();
            $bookedHallIds = $groupBookings->pluck('id')->toArray();

            $eventServices = EventService::with('bookedHall')->whereIn('booked_hall_id', $bookedHallIds)->get();
            $cateringServices = CateringService::with('bookedHall')->whereIn('booked_hall_id', $bookedHallIds)->get();
        } else {
            // Single hall booking
            $eventServices = EventService::with('bookedHall')->where('booked_hall_id', $id)->get();
            $cateringServices = CateringService::with('bookedHall')->where('booked_hall_id', $id)->get();
        }

        foreach ($eventServices as $serviceEvent) {
            $itemIds = json_decode($serviceEvent->Item_id, true); // Decode stored JSON array

            // Fetch item names based on IDs and store in a new attribute
            $serviceEvent->item_names = Eventitem::whereIn('id', $itemIds)->pluck('item_name')->toArray();
        }

        foreach ($cateringServices as $serviceCatering) {
            $itemIds = json_decode($serviceCatering->Item_id, true); // Decode stored JSON array

            // Fetch item names based on IDs and store in a new attribute
            $serviceCatering->item_names = Cateringitem::whereIn('id', $itemIds)->pluck('item_name')->toArray();
        }

        return view('admin.BookedHall.ViewEventCatering', compact('eventServices', 'cateringServices'));
    }

    public function cancelBooking($id)
    {
        try {
            $bookedHall = BookedHall::findOrFail($id);

            // Check if this is part of a multi-hall booking
            if ($bookedHall->group_code) {
                // Cancel the entire group booking
                return $this->cancelGroupBooking($bookedHall->group_code);
            } else {
                // Cancel single hall booking
                // Update the related hall enquiry status to cancelled (this removes it from calendar)
                $hallEnquiry = HallEnquiry::find($bookedHall->hall_enquiry_id);
                if ($hallEnquiry) {
                    $hallEnquiry->status = 'cancelled';
                    $hallEnquiry->save();
                }

                // Mark the booked hall as cancelled instead of deleting it
                // This allows event and catering panels to see that the booking was cancelled
                $bookedHall->cancelled_at = now();
                $bookedHall->save();

                return response()->json(['success' => true, 'message' => 'Booking cancelled successfully.']);
            }
        } catch (\Exception $e) {
            Log::error('Error cancelling booking: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to cancel booking. Please try again.']);
        }
    }

    public function cancelGroupBooking($groupCode)
    {
        try {
            // Find all bookings in the group
            $bookedHalls = BookedHall::where('group_code', $groupCode)->get();

            if ($bookedHalls->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'No bookings found in this group.']);
            }

            foreach ($bookedHalls as $bookedHall) {
                // Update the related hall enquiry status to cancelled (this removes it from calendar)
                $hallEnquiry = HallEnquiry::find($bookedHall->hall_enquiry_id);
                if ($hallEnquiry) {
                    $hallEnquiry->status = 'cancelled';
                    $hallEnquiry->save();
                }

                // Mark the booked hall as cancelled instead of deleting it
                // This allows event and catering panels to see that the booking was cancelled
                $bookedHall->cancelled_at = now();
                $bookedHall->save();
            }

            return response()->json(['success' => true, 'message' => 'Group booking cancelled successfully.']);
        } catch (\Exception $e) {
            Log::error('Error cancelling group booking: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to cancel group booking. Please try again.']);
        }
    }
}
