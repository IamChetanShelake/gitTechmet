<?php

namespace App\Console\Commands;

use App\Models\Accessorie;
use App\Models\HallEnquiry;
use Barryvdh\DomPDF\PDF;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class RegenerateQuotations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quotations:regenerate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerate all existing quotation PDFs with the updated template';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $enquiries = HallEnquiry::whereNotNull('quotation_file')->get();

        if ($enquiries->isEmpty()) {
            $this->info('No enquiries with quotations found.');
            return Command::SUCCESS;
        }

        // Group enquiries by their representative enquiry (first in group or single enquiries)
        $groupedEnquiries = collect();
        $processedGroups = [];

        foreach ($enquiries as $enquiry) {
            if ($enquiry->group_code && !in_array($enquiry->group_code, $processedGroups)) {
                // This is the first enquiry we encounter for this group
                $groupedEnquiries->push($enquiry);
                $processedGroups[] = $enquiry->group_code;
            } elseif (!$enquiry->group_code) {
                // Single enquiry
                $groupedEnquiries->push($enquiry);
            }
        }

        $this->info("Found {$groupedEnquiries->count()} enquiries/groups to regenerate.");

        $progressBar = $this->output->createProgressBar($groupedEnquiries->count());
        $progressBar->start();

        foreach ($groupedEnquiries as $enquiry) {
            // Check if this is a multi-hall enquiry
            $enquiriesCollection = collect();
            if ($enquiry->group_code) {
                // Fetch all enquiries in the same group
                $enquiriesCollection = HallEnquiry::where('group_code', $enquiry->group_code)
                                           ->orderBy('hall')
                                           ->get();
            } else {
                // Single enquiry, add it to the collection
                $enquiriesCollection->push($enquiry);
            }

            $isMultiHall = $enquiriesCollection->count() > 1;

            // Calculate totals for all halls
            $totalDeposit = 0;
            $totalRent = 0;
            $totalAccessoriesPrice = 0;
            $allAccessories = collect();

            foreach ($enquiriesCollection as $hallEnquiry) {
                // Calculate hours per day from start and end time
                $startTime = Carbon::createFromFormat('H:i:s', $hallEnquiry->start_time . ':00');
                $endTime = Carbon::createFromFormat('H:i:s', $hallEnquiry->end_time . ':00');
                $hoursPerDay = $startTime->diffInHours($endTime, false); // false to get positive difference

                // Get number of days for this hall
                $dates = $hallEnquiry->event_dates ? json_decode($hallEnquiry->event_dates, true) : [$hallEnquiry->event_date];
                $dates = array_filter($dates);
                $numberOfDays = count($dates);

                // Add to totals
                $totalDeposit += ($hallEnquiry->deposit ?? 0);
                $totalRent += ($hallEnquiry->rent_amount ?? 0);

                // Fetch accessories using stored IDs for this hall
                $accessoryIds = json_decode($hallEnquiry->accessorie, true); // Convert JSON string to array
                if (!empty($accessoryIds)) {
                    $hallAccessories = Accessorie::whereIn('id', $accessoryIds)->get();

                    // Calculate accessories price for this hall (per day × number of days)
                    $hallAccessoriesPrice = $hallAccessories->sum(function ($accessory) use ($hoursPerDay, $numberOfDays) {
                        $price = (float) ($accessory->price ?? 0);
                        $hours = (float) ($accessory->hours ?? 1);
                        if ($price <= 0 || $hours <= 0) return 0;

                        // Calculate blocks per day
                        $blocksPerDay = floor($hoursPerDay / $hours);
                        $pricePerDay = $price * max($blocksPerDay, 1); // Minimum 1 block per day

                        // Multiply by number of days
                        return $pricePerDay * $numberOfDays;
                    });

                    $totalAccessoriesPrice += $hallAccessoriesPrice;

                    // Add hall-specific accessories with hall info
                    foreach ($hallAccessories as $accessory) {
                        $price = (float) ($accessory->price ?? 0);
                        $hours = (float) ($accessory->hours ?? 1);
                        if ($price > 0 && $hours > 0) {
                            // Calculate blocks per day
                            $blocksPerDay = floor($hoursPerDay / $hours);
                            $pricePerDay = $price * max($blocksPerDay, 1); // Minimum 1 block per day

                            // Multiply by number of days
                            $totalPrice = $pricePerDay * $numberOfDays;

                            $accessory->calculated_price = $totalPrice;
                            $accessory->hall_name = $hallEnquiry->hall;
                            $allAccessories->push($accessory);
                        }
                    }
                }
            }

            // Calculate total amount (Hall Rent + Paid Accessories)
            $totalAmount = $totalRent + $totalAccessoriesPrice;
            $gst = $totalAmount * 0.18;  // Assuming 18% GST
            $finalAmount = $totalAmount + $gst;

            // Generate PDF with cache buster
            $pdf = app(PDF::class);
            $pdf = $pdf->loadView('bill.invoice', [
                'enquiry' => $enquiry,
                'groupedEnquiries' => $enquiriesCollection,
                'isMultiHall' => $isMultiHall,
                'allAccessories' => $allAccessories,
                'totalAccessoriesPrice' => $totalAccessoriesPrice,
                'totalAmount' => $totalAmount,
                'totalDeposit' => $totalDeposit,
                'totalRent' => $totalRent,
                'gst' => $gst,
                'finalAmount' => $finalAmount,
                'cache_buster' => time(), // Add cache buster
            ]);

            $fileName = 'quotation_' . $enquiry->id . '.pdf';
            $filePath = public_path('quotation/' . $fileName);
            $directory = public_path('quotation');

            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0755, true, true);
            }

            $pdf->save($filePath);

            $this->info("Regenerated: {$fileName}");
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
        $this->info('All quotations regenerated successfully.');

        return Command::SUCCESS;
    }
}
