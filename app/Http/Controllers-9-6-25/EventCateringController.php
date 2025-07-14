<?php

namespace App\Http\Controllers;

use App\Models\BookedHall;
use App\Models\Cateringitem;
use App\Models\EventService;
use Illuminate\Http\Request;
use App\Models\CateringService;
use App\Models\Eventitem;
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
        $eventBookings = BookedHall::where('event_flag', 1)
        ->where(function ($query) use ($vendorId) {
            $query->whereNull('event_booked_by')  // Show unbooked halls
                  ->orWhere('event_booked_by', $vendorId); // Show only vendor's own bookings
        })
        ->with('eventServices')
        ->get();

        return view('Event.VendorEventCrud.EventTable', compact('eventBookings'));
    }

    public function viewEventBooking($id){
        $eventBooking = BookedHall::with('eventServices')->findOrFail($id);
         // ✅ Update `booked_by` field in `booked_halls` table
         foreach ($eventBooking->eventServices as $service) {
            $itemIds = json_decode($service->Item_id, true); // Decode stored JSON array

            // Fetch item names based on IDs and store in a new attribute
            $service->item_names = Eventitem::whereIn('id', $itemIds)->pluck('item_name')->toArray();
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
        return redirect('/event-booked-halls')->with('success', 'Event Price Added Successfully');
    }

    // Confirm Event Booking
    public function confirmEvent($eventId)
    {
        // return $eventId;
        $eventBooking = EventService::where('booked_hall_id',$eventId)->first();
        // if ($eventBooking) {
            $eventBooking->status = 'confirmed' ;

            $eventBooking->save();


        return redirect()->back()->with('success', 'Event booking confirmed successfully!');
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
        // - `event_flag = 1` (for event-related bookings)
        // - `booked_by` is NULL (unbooked halls)
        // - OR `booked_by` matches the logged-in vendor (vendor sees only their own bookings)
        $cateringBookings = BookedHall::where('catering_flag', 1)
        ->where(function ($query) use ($vendorId) {
            $query->whereNull('catering_booked_by')  // Show unbooked halls
                  ->orWhere('catering_booked_by', $vendorId); // Show only vendor's own bookings
        })
        ->with('cateringServices')
        ->get();

        return view('Catering.CateringTable', compact('cateringBookings'));
    }




    public function viewCateringBooking($id){
        $cateringBooking = BookedHall::findOrFail($id);

         // ✅ Update `booked_by` field in `booked_halls` table
         foreach ($cateringBooking->cateringServices as $service) {
            $itemIds = json_decode($service->Item_id, true); // Decode stored JSON array

            // Fetch item names based on IDs and store in a new attribute
            $service->item_names = Cateringitem::whereIn('id', $itemIds)->pluck('item_name')->toArray();
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
        return redirect('/catering-booked-halls')->with('success', 'Catering Service Added Successfully');
    }
    public function confirmCatering($cateringId)
    {

        $cateringBooking = CateringService::where('booked_hall_id',$cateringId)->first();
        if ($cateringBooking) {
            $cateringBooking->status = 'confirmed';
            $cateringBooking->save();
        }

        return redirect()->back()->with('success', 'Catering booking confirmed successfully!');
    }


}
