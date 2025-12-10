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
use App\Models\Video;
use App\Models\Accessorie;
use Illuminate\Http\Request;
use Carbon\Carbon;

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
        $videos = Video::all();
        return view('website.index',compact('pages','halls','landings','facilites','testss','contacts','ourfacilities','upcomingEvents','videos'));
    }

    public function about(){
        $about = About::latest()->first();
        $teams = Team::all();
        $pages= Page::get();
        $facilites  = Facilitie::all();
        $contacts = Contact::all();
        $videos = Video::all();
        return view('website.about',compact('about','teams','pages','facilites','contacts','videos'));
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

        // Map hall names to their specific rule files with flexible matching
        $hallRulesMapping = [
            'Gurudakshina' => 'Rules and Regulations for Gurudakshina.txt',
            'Palash Hall' => 'Rules and Regulations for Palash Ha.txt',
            'Prin. T.A. Kulkarni Hall' => 'Rules and Regulations for Prin. T..txt',
            'Sharman Hall' => 'Rules and Regulations for Sharman H.txt',
            'Varanya Hall' => 'Rules and Regulations for Varanya.txt',
            'Varenya' => 'Rules and Regulations for Varanya.txt',
            'Dr. Sunadatai M. Gosavi Art Gallery' => 'DR. SUNADATAI M. GOSAVI ART GALLERY.txt',
            'Dr. Sundatai M. Gosavi Art Gallery' => 'DR. SUNADATAI M. GOSAVI ART GALLERY.txt',
            // Direct mappings for the exact database names from error feedback
            'Rules and Regulations for Varenya' => 'Rules and Regulations for Varanya.txt',
            'Rules and Regulations for Dr. Sundatai M. Gosavi Art Gallery' => 'DR. SUNADATAI M. GOSAVI ART GALLERY.txt',
            // Mappings for print-specific hall names found in enquiries table
            'Gurudakshina Auditorium Hall' => 'Rules and Regulations for Gurudakshina.txt',
            'New Prin. T.A. Kulkarni Hall' => 'Rules and Regulations for Prin. T..txt',
            'Palash' => 'Rules and Regulations for Palash Ha.txt',
            'Sharman' => 'Rules and Regulations for Sharman H.txt',
        ];

        // Function to find the best matching rule file for a hall name
        $findRuleFile = function($hallName) use ($hallRulesMapping) {
            // First try exact match (case sensitive)
            if (isset($hallRulesMapping[$hallName])) {
                $filePath = base_path($hallRulesMapping[$hallName]);
                if (file_exists($filePath)) {
                    return $hallRulesMapping[$hallName];
                }
            }

            // Try case-insensitive exact matches
            foreach ($hallRulesMapping as $key => $file) {
                if (strcasecmp($hallName, $key) === 0) {
                    $filePath = base_path($file);
                    if (file_exists($filePath)) {
                        return $file;
                    }
                }
            }

            // Try partial matches (contains)
            $hallNameLower = strtolower($hallName);
            foreach ($hallRulesMapping as $key => $file) {
                $keyLower = strtolower($key);
                if (strpos($hallNameLower, $keyLower) !== false ||
                    strpos($keyLower, $hallNameLower) !== false) {
                    $filePath = base_path($file);
                    if (file_exists($filePath)) {
                        return $file;
                    }
                }
            }

            // Try word-based matching for key terms
            $hallWords = explode(' ', $hallNameLower);
            foreach ($hallRulesMapping as $key => $file) {
                $keyWords = explode(' ', strtolower($key));
                $intersection = array_intersect($hallWords, $keyWords);
                if (count($intersection) > 0) {
                    $filePath = base_path($file);
                    if (file_exists($filePath)) {
                        return $file;
                    }
                }
            }

            return null;
        };

        // Load rules content for each hall
        $hallRules = [];
        foreach ($halls as $hall) {
            $fileName = $findRuleFile($hall->name);
            if ($fileName) {
                $filePath = base_path($fileName);
                if (file_exists($filePath)) {
                    $hallRules[$hall->name] = file_get_contents($filePath);
                } else {
                    $hallRules[$hall->name] = 'Rules and regulations file not found.';
                }
            } else {
                $hallRules[$hall->name] = 'Rules and regulations file not found.';
            }
        }

        return view('website.enquiry', compact('pages', 'contacts', 'halls', 'hallRules'));
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
        $hallRent = $booking->total_rent ?? 0;

        // Calculate total hours from start and end time
        $startTime = Carbon::createFromFormat('H:i:s', $booking->start_time . ':00');
        $endTime = Carbon::createFromFormat('H:i:s', $booking->end_time . ':00');
        $totalHours = $startTime->diffInHours($endTime, false); // false to get positive difference

        // Get accessories if any
        $accessoriesAmount = 0;
        if ($booking->enquiry && $booking->enquiry->accessorie) {
            $accessoryIds = json_decode($booking->enquiry->accessorie, true);
            if (is_array($accessoryIds)) {
                $accessories = Accessorie::whereIn('id', $accessoryIds)->get();
                // Calculate total accessories price based on blocks of hours
                $accessoriesAmount = $accessories->sum(function ($accessory) use ($totalHours) {
                    $price = (float) ($accessory->price ?? 0);
                    $hours = (float) ($accessory->hours ?? 1);
                    if ($price <= 0 || $hours <= 0) return 0;
                    $blocks = floor($totalHours / $hours);
                    return $price * max($blocks, 1); // Minimum 1 block
                });
            }
        }

        $rentAmount = $hallRent + $accessoriesAmount;
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
            // Map CASH/CHECK payments to appropriate types for display
            $displayType = $payment->transaction_type;
            if ($payment->transaction_type === 'CASH' || $payment->transaction_type === 'CHECK') {
                // For manual payments, determine type based on amount
                if ($payment->amount <= $depositAmount + 100) { // Allow some tolerance
                    $displayType = 'deposit';
                } elseif ($payment->amount >= $rentWithGst - 100) { // Allow some tolerance
                    $displayType = 'full';
                } else {
                    $displayType = 'partial';
                }
            }

            $status['paid_transactions'][] = [
                'type' => $displayType,
                'amount' => $payment->amount,
                'date' => $payment->payment_date,
                'transaction_id' => $payment->merchant_txn_no,
                'original_type' => $payment->transaction_type // Keep original type for reference
            ];

            // Handle payment logic based on display type
            switch ($displayType) {
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
                case 'partial':
                    // For partial payments, apply to remaining amounts
                    $remainingTotal = $status['remaining_deposit'] + $status['remaining_rent'];
                    if ($remainingTotal > 0) {
                        $paymentApplied = min($payment->amount, $remainingTotal);
                        $depositPortion = min($status['remaining_deposit'], $paymentApplied);
                        $rentPortion = $paymentApplied - $depositPortion;

                        $status['remaining_deposit'] = max(0, $status['remaining_deposit'] - $depositPortion);
                        $status['remaining_rent'] = max(0, $status['remaining_rent'] - $rentPortion);

                        if ($status['remaining_deposit'] == 0) $status['deposit_paid'] = true;
                        if ($status['remaining_rent'] == 0) $status['rent_paid'] = true;
                    }
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

        // Calculate total hours from start and end time
        $startTime = Carbon::createFromFormat('H:i:s', $bookedHall->start_time . ':00');
        $endTime = Carbon::createFromFormat('H:i:s', $bookedHall->end_time . ':00');
        $totalHours = $startTime->diffInHours($endTime, false); // false to get positive difference

        // Get accessories if any
        $accessoriesAmount = 0;
        if ($bookedHall->enquiry && $bookedHall->enquiry->accessorie) {
            $accessoryIds = json_decode($bookedHall->enquiry->accessorie, true);
            if (is_array($accessoryIds)) {
                $accessories = Accessorie::whereIn('id', $accessoryIds)->get();
                // Calculate total accessories price based on blocks of hours
                $accessoriesAmount = $accessories->sum(function ($accessory) use ($totalHours) {
                    $price = (float) ($accessory->price ?? 0);
                    $hours = (float) ($accessory->hours ?? 1);
                    if ($price <= 0 || $hours <= 0) return 0;
                    $blocks = floor($totalHours / $hours);
                    return $price * max($blocks, 1); // Minimum 1 block
                });
            }
        }

        $subtotal = $hallRent + $accessoriesAmount;
        $gst = $subtotal * 0.18; // 18% GST

        return $subtotal + $gst;
    }
}
