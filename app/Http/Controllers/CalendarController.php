<?php

namespace App\Http\Controllers;

use App\Models\BookedHall;
use App\Models\HallEnquiry;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CalendarController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the calendar view
     */
    public function index()
    {
        return view('admin.calendar.index');
    }

    /**
     * Get calendar events data
     */
    public function getEvents(Request $request)
    {
        $start = $request->start ? Carbon::parse($request->start)->startOfDay() : Carbon::now()->startOfMonth();
        $end = $request->end ? Carbon::parse($request->end)->endOfDay() : Carbon::now()->endOfMonth();

        $events = [];

        // Get confirmed bookings
        $bookedHalls = BookedHall::whereBetween('event_date', [$start, $end])
            ->whereNotNull('event_date')
            ->whereNull('cancelled_at')
            ->get();

        foreach ($bookedHalls as $booking) {
            $events[] = [
                'id' => 'booking_' . $booking->id,
                'title' => $booking->hall_name ,
                'start' => $booking->event_date . 'T' . ($booking->start_time ?? '00:00:00'),
                'end' => $booking->event_date . 'T' . ($booking->end_time ?? '23:59:59'),
                'backgroundColor' => '#28a745', // Green for confirmed bookings
                'borderColor' => '#28a745',
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'type' => 'booking',
                    'customer_name' => $booking->customer_name,
                    'customer_phone' => $booking->customer_phone,
                    'customer_email' => $booking->customer_email,
                    'event_type' => $booking->event_type,
                    'hall_name' => $booking->hall_name,
                    'duration' => $booking->duration,
                    'start_time' => $booking->start_time,
                    'end_time' => $booking->end_time,
                    'total_rent' => $booking->total_rent,
                    'total_deposit' => $booking->total_deposit,
                    'paid_amount' => $booking->paid_amount,
                    'remaining_amount' => $booking->remaining_amount,
                    'status' => 'Confirmed Booking'
                ]
            ];
        }

        // Get hall enquiries
        $enquiries = HallEnquiry::whereBetween('event_date', [$start, $end])
            ->whereNull('cancelled_at')
            ->whereNotIn('id', BookedHall::pluck('hall_enquiry_id'))
            ->get();

        foreach ($enquiries as $enquiry) {
            $events[] = [
                'id' => 'enquiry_' . $enquiry->id,
                'title' =>  ($enquiry->hall ? $enquiry->hall : '') ,
                'start' => $enquiry->event_date . 'T00:00:00',
                'end' => $enquiry->event_date . 'T23:59:59',
                'backgroundColor' => '#ffc107', // Yellow for enquiries
                'borderColor' => '#ffc107',
                'textColor' => '#000000',
                'extendedProps' => [
                    'type' => 'enquiry',
                    'customer_name' => $enquiry->name,
                    'customer_phone' => $enquiry->contact_no,
                    'customer_email' => $enquiry->email,
                    'organization' => $enquiry->organization,
                    'event_type' => $enquiry->event_type,
                    'hall_name' => $enquiry->hall,
                    'duration' => $enquiry->duration,
                    'start_time' => $enquiry->start_time,
                    'end_time' => $enquiry->end_time,
                    'expected_audience' => $enquiry->expected_audience,
                    'status' => ucfirst($enquiry->status),
                    'special_note' => $enquiry->special_note
                ]
            ];
        }

        return response()->json($events);
    }



    /**
     * Get calendar statistics
     */
    public function getStats(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));



        $stats = [
            'total_bookings' => BookedHall::where('event_date', 'like', $month . '%')
                ->whereNull('cancelled_at')->count(),
            'total_enquiries' => HallEnquiry::where('event_date', 'like', $month . '%')->count(),
            'confirmed_bookings' => BookedHall::where('event_date', 'like', $month . '%')
                ->whereNull('cancelled_at')->count(),
            'pending_enquiries' => HallEnquiry::where('event_date', 'like', $month . '%')
                ->where('status', 'pending')->count(),
            'total_revenue' => BookedHall::where('event_date', 'like', $month . '%')
                ->whereNull('cancelled_at')
                ->sum('total_rent'),
            'total_paid' => BookedHall::where('event_date', 'like', $month . '%')
                ->whereNull('cancelled_at')
                ->sum('paid_amount')
        ];

        return response()->json($stats);
    }
}
