<?php

namespace App\Http\Controllers;

use App\Models\Key;
use App\Models\Hall;
use App\Models\Page;
use App\Models\Team;
use App\Models\Test;
use App\Models\About;
use App\Models\Ideal;
use App\Models\Image;
use App\Models\Contact;
use App\Models\Landing;
use App\Models\HallPage;
use App\Models\Facilitie;
use App\Models\BookedHall;
use App\Models\Hall_Image;
use App\Models\HallEnquiry;
use App\Models\OurFacilite;
use App\Models\PaymentTransaction;
use App\Models\Event;
use Illuminate\Http\Request;

class WebsiteController extends Controller
{
    public function index(){
        $pages= Page::get();
        $halls = Hall::all();
        $landings = Landing::all();
        $facilites  = Facilitie::all();
        $testss = Test::all();
        $contacts = Contact::all();
        $ourfacilities = OurFacilite::all();
        $upcomingEvents = Event::upcoming()->limit(6)->get();
        return view('website.index',compact('pages','halls','landings','facilites','testss','contacts','ourfacilities','upcomingEvents'));
    }

    public function about(){
        $about = About::latest()->first();
        $teams = Team::all();
        $pages= Page::get();
        $facilites  = Facilitie::all();
        $contacts = Contact::all();
        return view('website.about',compact('about','teams','pages','facilites','contacts'));
    }

    public function facilities(){
        return view('website.facilities');
    }

    public function contact(){
        $contacts = Contact::all();
        $pages= Page::get();
        return view('website.contact',compact('pages','contacts'));
    }

    public function gallery(){
       // return 'Hello world!';
       $images = Image::orderBy('is_pinned', 'desc')
                      ->orderBy('order', 'asc')
                      ->orderBy('created_at', 'desc')
                      ->get();
       $pages= Page::get();
       $contacts = Contact::all();

        return view('website.gallery',compact('images','pages','contacts'));
    }

    public function hall(){
        $pages= Page::get();
        $halls = Hall::all();
        $contacts = Contact::all();

        return view('website.hall',compact('pages','halls','contacts'));
    }
    public function hallDetails($id){
        $pages= Page::get();
        $hall = Hall::with(['keys', 'ideals', 'images'])->findOrFail($id); // Fetch keys and ideals
        $hall_images=Hall_Image::where('hall_id',$id)->get();
        $halls = Hall::all();
        $contacts = Contact::all();


        return view('website.hall-detail',compact('pages','hall','hall_images','halls','contacts'));
    }

    public function enquiry(){
        $pages= Page::get();
        $contacts = Contact::all();
        $halls = Hall::all();
        return view('website.enquiry',compact('pages','contacts','halls'));
    }

    public function legal($id){
        $pages= Page::get();
        $page = Page::find($id);
        $contacts = Contact::all();
        return view('website.page-details',compact('pages','page','contacts'));
    }

    public function eventDetail($id){
        $pages = Page::get();
        $event = Event::findOrFail($id);
        $contacts = Contact::all();
        $upcomingEvents = Event::upcoming()->where('id', '!=', $id)->limit(3)->get();

        return view('website.event-detail', compact('pages', 'event', 'contacts', 'upcomingEvents'));
    }

    public function privacyPolicy($id)
    {
        $hall = Hall::findOrFail($id); // Fetch the hall details
        $hallpage = HallPage::where('hall_id', $id)->firstOrFail(); // Fetch the hall's privacy policy
        $pages= Page::get();
        $contacts = Contact::all();

        return view('website.PrivacyPolicyPage', compact('hall', 'hallpage','pages','contacts'));
    }

    public function checkBookingPin(Request $request)
    {
        $pin = $request->input('pin');
        $contacts = Contact::all();
        $pages = Page::get();

        // Fetch the booked hall along with its hall enquiry
        $booking = BookedHall::with(['enquiry', 'paymentTransactions'])->where('booking_code', $pin)->first();

        if ($booking && $booking->enquiry) {
            // Find the hall based on the `hall_name` in `booked_halls`
            $hall = Hall::where('name', $booking->hall_name)->first(); // Fetch the hall details

            // Get successful payment transactions
            $successfulPayments = PaymentTransaction::where('booked_hall_id', $booking->id)
                ->where('status', 'SUCCESS')
                ->get();

            // Calculate payment status
            $paymentStatus = $this->calculatePaymentStatus($booking, $successfulPayments);

            // Calculate total amount for payment
            $totalAmount = $this->calculateTotalAmount($booking);

            return view('website.book-now', compact('contacts', 'pages', 'booking', 'hall', 'totalAmount', 'paymentStatus', 'successfulPayments'));
        } else {
            return back()->with('error', 'Invalid PIN, please contact the admin.');
        }
    }

    /**
     * Calculate payment status for a booking
     */
    private function calculatePaymentStatus($booking, $successfulPayments)
    {
        $rentAmount = $booking->total_rent ?? 0;
        $depositAmount = $booking->total_deposit ?? 0;
        $rentWithGst = $rentAmount * 1.18;

        $status = [
            'deposit_paid' => false,
            'rent_paid' => false,
            'full_paid' => false,
            'deposit_amount' => $depositAmount,
            'rent_amount' => $rentAmount,
            'rent_with_gst' => $rentWithGst,
            'total_amount' => $depositAmount + $rentWithGst,
            'paid_transactions' => [],
            'remaining_deposit' => $depositAmount,
            'remaining_rent' => $rentWithGst,
        ];

        foreach ($successfulPayments as $payment) {
            $status['paid_transactions'][] = [
                'type' => $payment->transaction_type,
                'amount' => $payment->amount,
                'date' => $payment->payment_date,
                'transaction_id' => $payment->merchant_txn_no
            ];

            switch ($payment->transaction_type) {
                case 'deposit':
                    $status['deposit_paid'] = true;
                    $status['remaining_deposit'] = max(0, $status['remaining_deposit'] - $payment->amount);
                    break;
                case 'rent':
                    $status['rent_paid'] = true;
                    $status['remaining_rent'] = max(0, $status['remaining_rent'] - $payment->amount);
                    break;
                case 'full':
                    $status['deposit_paid'] = true;
                    $status['rent_paid'] = true;
                    $status['full_paid'] = true;
                    $status['remaining_deposit'] = 0;
                    $status['remaining_rent'] = 0;
                    break;
            }
        }

        return $status;
    }

    /**
     * Calculate total amount for booking including hall rent, accessories, and GST
     */
    private function calculateTotalAmount($bookedHall)
    {
        $hallRent = $bookedHall->total_rent ?? 0;

        // Get accessories if any
        $accessoriesAmount = 0;
        if ($bookedHall->enquiry && $bookedHall->enquiry->accessorie) {
            $accessoryIds = json_decode($bookedHall->enquiry->accessorie, true);
            if (is_array($accessoryIds)) {
                $accessories = \App\Models\Accessorie::whereIn('id', $accessoryIds)->get();
                $accessoriesAmount = $accessories->sum('price');
            }
        }

        $subtotal = $hallRent + $accessoriesAmount;
        $gst = $subtotal * 0.18; // 18% GST

        return $subtotal + $gst;
    }
}
