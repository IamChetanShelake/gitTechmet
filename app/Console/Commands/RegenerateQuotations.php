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

        $this->info("Found {$enquiries->count()} enquiries to regenerate.");

        $progressBar = $this->output->createProgressBar($enquiries->count());
        $progressBar->start();

        foreach ($enquiries as $enquiry) {
            // Fetch accessories
            $accessoryIds = json_decode($enquiry->accessorie, true) ?? [];
            $accessories = Accessorie::whereIn('id', $accessoryIds)->get();

            // Calculate total hours from start and end time
            $startTime = Carbon::createFromFormat('H:i:s', $enquiry->start_time . ':00');
            $endTime = Carbon::createFromFormat('H:i:s', $enquiry->end_time . ':00');
            $totalHours = $startTime->diffInHours($endTime, false); // false to get positive difference

            // Calculate total accessories price based on blocks of hours
            $totalAccessoriesPrice = $accessories->sum(function ($accessory) use ($totalHours) {
                $price = (float) ($accessory->price ?? 0);
                $hours = (float) ($accessory->hours ?? 1);
                if ($price <= 0 || $hours <= 0) return 0;
                $blocks = floor($totalHours / $hours);
                return $price * max($blocks, 1); // Minimum 1 block
            });
            $totalAmount = ($enquiry->rent_amount ?? 0) + $totalAccessoriesPrice;
            $gst = $totalAmount * 0.18;
            $finalAmount = $totalAmount + $gst;

            // Generate PDF
            $pdf = app(PDF::class);
            $pdf = $pdf->loadView('bill.invoice', compact('enquiry', 'accessories', 'totalAccessoriesPrice', 'totalAmount', 'gst', 'finalAmount', 'totalHours'));

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
