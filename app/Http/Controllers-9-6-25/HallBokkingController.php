<?php

namespace App\Http\Controllers;

use App\Models\Eventitem;
use App\Models\BookedHall;
use App\Models\HallEnquiry;
use App\Models\Cateringitem;
use App\Models\EventService;
use Illuminate\Http\Request;
use App\Models\CateringService;

class HallBokkingController extends Controller
{
    public function index(){
        // Fetch all booked halls
        $bookedHalls = BookedHall::all();
        $eventServices = EventService::all();
        $cateringServices = CateringService::all();
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



    // Extract vendor data (convert JSON string to array)
    $vendorServices = json_decode($hallenquiry->vendor, true) ?? [];

    // Initialize event and catering flags
    $eventFlag = in_array('event', $vendorServices) ? '1' : '0';
    $cateringFlag = in_array('catering', $vendorServices) ? '1' : '0';

    // Fetch total rent from hallenquirys table
    $totalRent = $hallenquiry->rent_amount;
    $totalDeposit = $hallenquiry->deposit;

    // Store payment details as NULL for now
    $paidAmount = null;
    $remainingAmount = null;

    // Generate a unique 6-digit booking code
    do {
        $bookingCode = mt_rand(100000, 999999);
    } while (BookedHall::where('booking_code', $bookingCode)->exists());

    // Create a new booked hall entry
    $bookedHall = new BookedHall();
    $bookedHall->hall_enquiry_id = $hallenquiry->id;
    $bookedHall->customer_name = $hallenquiry->name;
    $bookedHall->customer_phone = $hallenquiry->contact_no;
    $bookedHall->customer_email = $hallenquiry->email;
    $bookedHall->event_date = $hallenquiry->event_date;
    // $bookedHall->event_time = $hallenquiry->event_time;
    $bookedHall->event_type = $hallenquiry->event_type;
    $bookedHall->hall_name = $hallenquiry->hall;
    $bookedHall->duration = $hallenquiry->duration;
    $bookedHall->start_time = $hallenquiry->start_time;
    $bookedHall->end_time = $hallenquiry->end_time;
    $bookedHall->catering_flag = $cateringFlag;
    $bookedHall->event_flag = $eventFlag;
    $bookedHall->total_rent = $totalRent;
    $bookedHall->total_deposit = $totalDeposit;
    $bookedHall->paid_amount = $paidAmount; // NULL for now
    $bookedHall->remaining_amount = $remainingAmount; // NULL for now
    $bookedHall->booking_code = $bookingCode; // Store the unique booking code
    $bookedHall->save();  // Save the data

    // Update the status of hall enquiry to "confirmed"
    $hallenquiry->status = 'confirmed';
    $hallenquiry->save();

    return redirect('/AdminHallEnquiry')->with('success', 'Booking Confirmed Successfully');
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
            return back()->with('success', 'Status updated successfully.');
        }

        return back()->with('fail', 'Service not found.');
    }

        public function ViewEventCatering($id)
        {
            $eventServices = EventService::where('booked_hall_id', $id)->get();
            $cateringServices = CateringService::where('booked_hall_id', $id)->get();

            // foreach ($eventBooking->eventServices as $service) {
            //     $itemIds = json_decode($service->Item_id, true); // Decode stored JSON array

            //     // Fetch item names based on IDs and store in a new attribute
            //     $service->item_names = Eventitem::whereIn('id', $itemIds)->pluck('item_name')->toArray();
            // }
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





}
