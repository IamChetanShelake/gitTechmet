<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BookedHall;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SendPaymentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send payment reminders 8 days before event date';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Log::info('🔄 Starting payment reminders check...');

        // Calculate the date 8 days from now
        $targetDate = Carbon::now()->addDays(8)->toDateString();

        Log::info('📅 Looking for bookings on date: ' . $targetDate);

        // Find all booked halls with event date 8 days from now
        $bookings = BookedHall::where('event_date', $targetDate)
            ->whereNull('cancelled_at')
            ->with('enquiry')
            ->get();

        Log::info('📊 Found ' . $bookings->count() . ' bookings for payment reminders');

        $sentCount = 0;
        $errorCount = 0;

        // Group bookings by group_code for multi-hall handling
        $groupedBookings = $bookings->groupBy('group_code');

        // Track sent group codes to avoid duplicate reminders
        $sentGroupCodes = [];

        foreach ($groupedBookings as $groupCode => $groupBookings) {
            try {
                if ($groupCode && !in_array($groupCode, $sentGroupCodes)) {
                    // Multi-hall booking - get ALL bookings for this group code across all dates
                    $allGroupBookings = BookedHall::where('group_code', $groupCode)
                        ->whereNull('cancelled_at')
                        ->with('enquiry')
                        ->get();

                    // Only send if group has payments pending
                    if ($this->calculateRemainingAmount($allGroupBookings) > 0) {
                        $this->sendMultiHallReminder($allGroupBookings);
                        $sentGroupCodes[] = $groupCode;
                    }
                } elseif (!$groupCode && $this->calculateRemainingAmount($groupBookings) > 0) {
                    // Single hall booking
                    $this->sendSingleHallReminder($groupBookings->first());
                }

                if (!in_array($groupCode, $sentGroupCodes)) {
                    $sentCount++;
                }
            } catch (\Exception $e) {
                Log::error('💥 Error sending reminder for group ' . ($groupCode ?? 'single') . ': ' . $e->getMessage());
                $errorCount++;
            }
        }

        Log::info('✅ Payment reminders completed:', [
            'total_groups' => $groupedBookings->count(),
            'sent' => $sentCount,
            'errors' => $errorCount
        ]);

        $this->info("Sent {$sentCount} payment reminders with {$errorCount} errors");

        return Command::SUCCESS;
    }

    /**
     * Send reminder for single hall booking
     */
    private function sendSingleHallReminder($bookedHall)
    {
        $this->sendWhatsAppReminder($bookedHall, [$bookedHall]);
    }

    /**
     * Send reminder for multi-hall booking
     */
    private function sendMultiHallReminder($groupBookings)
    {
        // Use the first booking as representative
        $representativeBooking = $groupBookings->first();
        $this->sendWhatsAppReminder($representativeBooking, $groupBookings);
    }

    /**
     * Send WhatsApp reminder using the template
     */
    private function sendWhatsAppReminder($representativeBooking, $allBookings)
    {
        try {
            $apiUrl = config('oneclick.api_url') . "/" . config('oneclick.api_version') . "/" . config('oneclick.phone_id') . "/messages";
            $token = trim(config('oneclick.api_token'));

            // Format contact number
            $contactNumber = "+91" . $representativeBooking->customer_phone;

            // Prepare template variables
            $customerName = $representativeBooking->customer_name;

            // Build hall details string
            $hallDetails = $this->buildHallDetailsString($allBookings);

            // Calculate remaining amount
            $remainingAmount = $this->calculateRemainingAmount($allBookings);

            // Build payload using the provided template format
            $payloadArray = [
                "to" => $contactNumber,
                "recipient_type" => "individual",
                "type" => "template",
                "template" => [
                    "language" => [
                        "policy" => "deterministic",
                        "code" => "en"
                    ],
                    "name" => "8_days_before",
                    "components" => [
                        [
                            "type" => "body",
                            "parameters" => [
                                [
                                    "type" => "text",
                                    "text" => $customerName
                                ],
                                [
                                    "type" => "text",
                                    "text" => $hallDetails
                                ],
                                [
                                    "type" => "text",
                                    "text" => "₹" . number_format($remainingAmount, 2)
                                ]
                            ]
                        ]
                    ]
                ]
            ];

            $payload = json_encode($payloadArray);

            // Send cURL request
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
            curl_close($ch);

            // Log the WhatsApp notification attempt
            Log::info("📤 Payment Reminder WhatsApp Sent", [
                'contact' => $contactNumber,
                'customer_name' => $customerName,
                'hall_details' => $hallDetails,
                'remaining_amount' => $remainingAmount,
                'event_date' => $representativeBooking->event_date,
                'http_code' => $httpCode,
                'response' => $response,
                'payload' => $payloadArray
            ]);

            if ($httpCode == 200) {
                Log::info("✅ Payment reminder WhatsApp sent to {$contactNumber}");
            } else {
                Log::error("❌ Payment reminder WhatsApp failed for {$contactNumber}. HTTP {$httpCode} - {$response}");
                throw new \Exception("WhatsApp API returned HTTP {$httpCode}");
            }

        } catch (\Exception $e) {
            Log::error('❌ Failed to send payment reminder WhatsApp', [
                'error' => $e->getMessage(),
                'booking_id' => $representativeBooking->id,
                'customer_phone' => $representativeBooking->customer_phone,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Build hall details string for single or multiple halls
     */
    private function buildHallDetailsString($bookings)
    {
        // Ensure $bookings is a collection
        if (is_array($bookings)) {
            $bookings = collect($bookings);
        }

        if ($bookings->count() == 1) {
            $booking = $bookings->first();
            // Check if this booking has multiple dates
            $enquiry = $booking->enquiry;
            if ($enquiry && $enquiry->event_dates) {
                $dates = json_decode($enquiry->event_dates, true);
                $dates = array_filter($dates);
                if (count($dates) > 1) {
                    sort($dates);
                    return $booking->hall_name . " on " . implode(', ', $dates) . " (" . count($dates) . " dates)";
                }
            }
            return $booking->hall_name . " on " . $booking->event_date;
        } else {
            // Multi-hall booking
            $hallNames = $bookings->pluck('hall_name')->unique()->toArray();
            $hallInfo = implode(', ', $hallNames) . ' (' . count($hallNames) . ' halls)';

            // Collect all dates from all enquiries
            $allDates = [];
            foreach ($bookings as $booking) {
                $enquiry = $booking->enquiry;
                if ($enquiry && $enquiry->event_dates) {
                    $dates = json_decode($enquiry->event_dates, true);
                    $dates = array_filter($dates);
                    $allDates = array_merge($allDates, $dates);
                } else {
                    $allDates[] = $booking->event_date;
                }
            }
            $allDates = array_unique($allDates);
            sort($allDates);

            $dateInfo = '';
            if (count($allDates) > 1) {
                $dateInfo = implode(', ', $allDates) . ' (' . count($allDates) . ' dates)';
            } else {
                $dateInfo = $allDates[0];
            }

            return $hallInfo . " on " . $dateInfo;
        }
    }

    /**
     * Calculate remaining amount for the booking(s)
     * Fixed to match BillController calculation logic
     */
    private function calculateRemainingAmount($bookings)
    {
        $totalRemaining = 0;

        // Group bookings by group_code to calculate total consistently
        $groupCode = $bookings->first()->group_code;
        if ($groupCode) {
            // For group bookings, calculate total once using enquiry data
            $representativeEnquiry = $bookings->first()->enquiry;
            if ($representativeEnquiry) {
                return $this->calculateTotalFromEnquiry($representativeEnquiry, $bookings);
            }
        }

        // Fallback: individual booking calculation
        foreach ($bookings as $booking) {
            // Calculate total amount (rent + deposit + accessories + GST) - Matches BillController
            $totalRent = $booking->total_rent ?? 0;
            $totalDeposit = $booking->total_deposit ?? 0;

            // Calculate accessories if enquiry exists
            $accessoriesAmount = 0;
            if ($booking->enquiry && $booking->enquiry->accessorie) {
                $accessoryIds = json_decode($booking->enquiry->accessorie, true);
                if (is_array($accessoryIds)) {
                    $accessories = \App\Models\Accessorie::whereIn('id', $accessoryIds)->get();
                    // Calculate accessories based on duration
                    $startTime = \Carbon\Carbon::createFromFormat('H:i:s', $booking->start_time . ':00');
                    $endTime = \Carbon\Carbon::createFromFormat('H:i:s', $booking->end_time . ':00');
                    $totalHours = $startTime->diffInHours($endTime, false);

                    $numberOfDays = 1; // Default
                    if ($booking->enquiry->event_dates) {
                        $dates = json_decode($booking->enquiry->event_dates, true);
                        $numberOfDays = count(array_filter($dates));
                    }

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

            // Match BillController calculation: GST only on (rent + accessories), then + deposit
            $subtotal = $totalRent + $accessoriesAmount;
            $gst = $subtotal * 0.18;
            $totalAmount = $subtotal + $gst + $totalDeposit;

            // Get paid amount from successful transactions
            $paidAmount = $booking->successfulPayments()->sum('amount');

            $remaining = max(0, $totalAmount - $paidAmount);
            $totalRemaining += $remaining;
        }

        return $totalRemaining;
    }

    /**
     * Calculate total from enquiry data (matches BillController exactly)
     */
    private function calculateTotalFromEnquiry($enquiry, $bookings)
    {
        // Get all grouped enquiries
        $groupedEnquiries = collect();
        if ($enquiry->group_code) {
            $groupedEnquiries = \App\Models\HallEnquiry::where('group_code', $enquiry->group_code)->get();
        } else {
            $groupedEnquiries->push($enquiry);
        }

        $totalDeposit = 0;
        $totalRent = 0;
        $totalAccessoriesPrice = 0;

        foreach ($groupedEnquiries as $hallEnquiry) {
            // Calculate hours per day
            $startTime = \Carbon\Carbon::createFromFormat('H:i:s', $hallEnquiry->start_time . ':00');
            $endTime = \Carbon\Carbon::createFromFormat('H:i:s', $hallEnquiry->end_time . ':00');
            $hoursPerDay = $startTime->diffInHours($endTime, false);

            // Get number of days
            $dates = $hallEnquiry->event_dates ? json_decode($hallEnquiry->event_dates, true) : [$hallEnquiry->event_date];
            $dates = array_filter($dates);
            $numberOfDays = count($dates);

            // Sum up totals
            $totalDeposit += ($hallEnquiry->deposit ?? 0);
            $totalRent += ($hallEnquiry->rent_amount ?? 0);

            // Calculate accessories
            $accessoryIds = json_decode($hallEnquiry->accessorie, true);
            if (!empty($accessoryIds)) {
                $hallAccessories = \App\Models\Accessorie::whereIn('id', $accessoryIds)->get();
                $hallAccessoriesPrice = $hallAccessories->sum(function ($accessory) use ($hoursPerDay, $numberOfDays) {
                    $price = (float) ($accessory->price ?? 0);
                    $hours = (float) ($accessory->hours ?? 1);
                    if ($price <= 0 || $hours <= 0) return 0;
                    $blocksPerDay = floor($hoursPerDay / $hours);
                    $pricePerDay = $price * max($blocksPerDay, 1);
                    return $pricePerDay * $numberOfDays;
                });
                $totalAccessoriesPrice += $hallAccessoriesPrice;
            }
        }

        // Match BillController calculation exactly
        $totalAmount = $totalRent + $totalAccessoriesPrice; // Rent + accessories
        $gst = $totalAmount * 0.18; // GST on (rent + accessories)
        $finalAmount = $totalAmount + $gst + $totalDeposit; // Add deposit after GST

        // Get total paid amount across all bookings in the group
        $totalPaid = $bookings->sum(function($booking) {
            return $booking->successfulPayments()->sum('amount');
        });

        return max(0, $finalAmount - $totalPaid);
    }
}
