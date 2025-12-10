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

        // Get confirmed bookings - Group by group_code to handle multi-hall bookings
        $groupedBookings = BookedHall::whereNull('cancelled_at')
            ->get()
            ->groupBy('group_code');

        foreach ($groupedBookings as $groupCode => $bookings) {
            // Treat single bookings (no group_code) as individual groups
            $groupKey = $groupCode ?: 'single_' . $bookings->first()->id;

            // Collect all dates across all halls in this group
            $allDates = [];
            $allHallNames = [];
            $firstBooking = $bookings->first();

            foreach ($bookings as $booking) {
                $dates = $booking->event_dates ? json_decode($booking->event_dates, true) : [$booking->event_date];
                $dates = array_filter($dates);
                $allDates = array_merge($allDates, $dates);
                $allHallNames[] = $booking->hall_name;
            }

            // Remove duplicates and sort dates
            $allDates = array_unique($allDates);
            sort($allDates);
            $allHallNames = array_unique($allHallNames);

            // Create events for each date in the group
            foreach ($allDates as $date) {
                if (!empty($date)) {
                    $dateCarbon = Carbon::parse($date);

                    // Filter by date range
                    if (!$dateCarbon->between($start, $end)) {
                        continue;
                    }

                    $dateStr = $dateCarbon->format('Y-m-d');

                    // Determine display time based on the first booking of the group
                    $displayStartTime = $firstBooking->start_time ?? '00:00:00';
                    $displayEndTime = $firstBooking->end_time ?? '23:59:59';

                    // For multiple halls, show combined title
                    $title = count($allHallNames) > 1 ?
                        implode(', ', $allHallNames) . ' (' . count($allHallNames) . ' halls)' :
                        $allHallNames[0];

                    $events[] = [
                        'id' => 'booking_' . $firstBooking->id . '_' . $dateStr,
                        'title' => $title,
                        'start' => $dateStr . 'T' . $displayStartTime,
                        'end' => $dateStr . 'T' . $displayEndTime,
                        'backgroundColor' => '#28a745', // Green for confirmed bookings
                        'borderColor' => '#28a745',
                        'textColor' => '#ffffff',
                        'extendedProps' => [
                            'type' => 'booking',
                            'customer_name' => $firstBooking->customer_name,
                            'customer_phone' => $firstBooking->customer_phone,
                            'customer_email' => $firstBooking->customer_email,
                            'event_type' => $firstBooking->event_type,
                            'hall_name' => count($allHallNames) > 1 ? implode(', ', $allHallNames) :
                                         ($allHallNames[0] ?? 'N/A'),
                            'halls' => $allHallNames,
                            'group_count' => count($allHallNames),
                            'duration' => $firstBooking->duration,
                            'start_time' => $firstBooking->start_time,
                            'end_time' => $firstBooking->end_time,
                            'total_rent' => $bookings->sum('total_rent'),
                            'total_deposit' => $bookings->sum('total_deposit'),
                            'paid_amount' => $bookings->sum('paid_amount'),
                            'remaining_amount' => $bookings->sum('total_rent') - $bookings->sum('paid_amount'),
                            'status' => 'Confirmed Booking',
                            'group_code' => $groupCode
                        ]
                    ];
                }
            }
        }

        // Get hall enquiries - Group by group_code to handle multi-hall enquiries
        $groupedEnquiries = HallEnquiry::whereNull('cancelled_at')
            ->whereNotIn('group_code', BookedHall::whereNull('cancelled_at')->pluck('group_code')->filter())
            ->get()
            ->groupBy('group_code');

        foreach ($groupedEnquiries as $groupCode => $enquiries) {
            // Collect all dates across all halls in this enquiry group
            $allDates = [];
            $allHallNames = [];
            $firstEnquiry = $enquiries->first();



            foreach ($enquiries as $enquiry) {
                $dates = $enquiry->event_dates ? json_decode($enquiry->event_dates, true) : [$enquiry->event_date];
                $dates = array_filter($dates);
                $allDates = array_merge($allDates, $dates);
                // Remove prefixes like "8a ", "12a " from hall names
                $hallName = preg_replace('/^\d+a\s+/i', '', $enquiry->hall);
                $allHallNames[] = $hallName;
            }

            // Remove duplicates and sort dates
            $allDates = array_unique($allDates);
            sort($allDates);
            $allHallNames = array_unique($allHallNames);

            // Create events for each date in the enquiry group
            foreach ($allDates as $date) {
                if (!empty($date)) {
                    $dateCarbon = Carbon::parse($date);

                    // Filter by date range
                    if (!$dateCarbon->between($start, $end)) {
                        continue;
                    }

                    $dateStr = $dateCarbon->format('Y-m-d');

                    // For multiple halls in enquiry, show combined title
                    $title = count($allHallNames) > 1 ?
                        implode(', ', $allHallNames) . ' (' . count($allHallNames) . ' halls)' :
                        $allHallNames[0];

                    $events[] = [
                        'id' => 'enquiry_' . $firstEnquiry->id . '_' . $dateStr,
                        'title' => $title,
                        'start' => $dateStr . 'T00:00:00',
                        'end' => $dateStr . 'T23:59:59',
                        'backgroundColor' => '#ffc107', // Yellow for enquiries
                        'borderColor' => '#ffc107',
                        'textColor' => '#000000',
                        'extendedProps' => [
                            'type' => 'enquiry',
                            'customer_name' => $firstEnquiry->name,
                            'customer_phone' => $firstEnquiry->contact_no,
                            'customer_email' => $firstEnquiry->email,
                            'organization' => $firstEnquiry->organization,
                            'event_type' => $firstEnquiry->event_type,
                            'hall_name' => count($allHallNames) > 1 ? implode(', ', $allHallNames) :
                                         ($allHallNames[0] ?? 'N/A'),
                            'halls' => $allHallNames,
                            'group_count' => count($allHallNames),
                            'duration' => $firstEnquiry->duration,
                            'start_time' => $firstEnquiry->start_time,
                            'end_time' => $firstEnquiry->end_time,
                            'expected_audience' => $firstEnquiry->expected_audience,
                            'status' => ucfirst($firstEnquiry->status),
                            'special_note' => $firstEnquiry->special_note,
                            'group_code' => $groupCode
                        ]
                    ];
                }
            }
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
