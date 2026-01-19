<?php

namespace App\Exports;

use App\Models\HallEnquiry;
use App\Models\BookedHall;
use App\Models\Accessorie;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Carbon\Carbon;

class HallEnquiryExport implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting
{
    protected $period;
    protected $dateFrom;
    protected $dateTo;

    public function __construct($period = 'all', $dateFrom = null, $dateTo = null)
    {
        $this->period = $period;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Base query for hall enquiries
        $query = HallEnquiry::whereNull('cancelled_at')
            ->whereIn('status', ['Viewed', 'pending', 'confirmed']);

        // Apply date filtering based on period
        switch ($this->period) {
            case 'weekly':
                $query->where('event_date', '>=', now()->startOfWeek())
                      ->where('event_date', '<=', now()->endOfWeek());
                break;
            case 'monthly':
                $query->where('event_date', '>=', now()->startOfMonth())
                      ->where('event_date', '<=', now()->endOfMonth());
                break;
            case 'yearly':
                $query->where('event_date', '>=', now()->startOfYear())
                      ->where('event_date', '<=', now()->endOfYear());
                break;
            case 'custom':
                if ($this->dateFrom && $this->dateTo) {
                    $query->where('event_date', '>=', $this->dateFrom)
                          ->where('event_date', '<=', $this->dateTo);
                }
                break;
            // 'all' case - no additional filtering
        }

        $enquiries = $query->get();

        // Group enquiries by group_code (treat single enquiries as their own group)
        $grouped = $enquiries->groupBy(function ($enquiry) {
            return $enquiry->group_code ?: 'single_' . $enquiry->id;
        });

        // Create aggregated data for each group
        $aggregatedData = collect();
        foreach ($grouped as $groupCode => $groupEnquiries) {
            $firstEnquiry = $groupEnquiries->first();

            // Aggregate data
            $allHalls = $groupEnquiries->pluck('hall')->unique()->implode(', ');
            $allDates = [];
            $totalRent = 0;
            $totalDeposit = 0;
            $totalAccessoriesPrice = 0;
            $allAccessories = collect();
            $allSpecialNotes = collect();

            foreach ($groupEnquiries as $enquiry) {
                // Collect dates
                if ($enquiry->event_dates) {
                    $dates = json_decode($enquiry->event_dates, true) ?? [];
                    $allDates = array_merge($allDates, $dates);
                } else {
                    $allDates[] = $enquiry->event_date;
                }

                // Sum amounts
                $totalRent += (float) ($enquiry->rent_amount ?? 0);
                $totalDeposit += (float) ($enquiry->deposit ?? 0);

                // Calculate hours per day from start and end time
                $startTime = Carbon::createFromFormat('H:i:s', $enquiry->start_time . ':00');
                $endTime = Carbon::createFromFormat('H:i:s', $enquiry->end_time . ':00');
                $hoursPerDay = $startTime->diffInHours($endTime, false); // false to get positive difference

                // Get number of days for this enquiry
                $dates = $enquiry->event_dates ? json_decode($enquiry->event_dates, true) : [$enquiry->event_date];
                $dates = array_filter($dates);
                $numberOfDays = count($dates);

                // Collect accessories with proper cost calculation
                if ($enquiry->accessorie) {
                    $accessoryIds = json_decode($enquiry->accessorie, true) ?? [];
                    if (!empty($accessoryIds)) {
                        $accessoryCounts = array_count_values($accessoryIds); // Count quantities
                        foreach ($accessoryCounts as $accId => $qty) {
                            $allAccessories->put($accId, ($allAccessories->get($accId, 0) + $qty));
                        }

                        // Calculate accessory costs using same logic as bill controller
                        $hallAccessories = Accessorie::whereIn('id', array_keys($accessoryCounts))->get();
                        $hallAccessoriesPrice = $hallAccessories->sum(function ($accessory) use ($hoursPerDay, $numberOfDays, $accessoryCounts) {
                            $price = (float) ($accessory->price ?? 0); // Use 'price' field as in bill controller
                            $hours = (float) ($accessory->hours ?? 1);
                            if ($price <= 0 || $hours <= 0) return 0;

                            $blocksPerDay = floor($hoursPerDay / $hours);
                            $pricePerDay = $price * max($blocksPerDay, 1); // Minimum 1 block per day

                            // Multiply by number of days and quantity
                            $qty = $accessoryCounts[$accessory->id] ?? 1;
                            return $pricePerDay * $numberOfDays * $qty;
                        });

                        $totalAccessoriesPrice += $hallAccessoriesPrice;
                    }
                }

                // Collect special notes
                if ($enquiry->special_note) {
                    $allSpecialNotes->push($enquiry->special_note);
                }
            }

            // Unique dates, sort
            $allDates = array_unique($allDates);
            sort($allDates);
            $programDate = implode(', ', array_map(function($date) {
                return $date ? Carbon::parse($date)->format('d-m-Y') : '';
            }, $allDates));

            // Calculate accessories cost and names
            $accessoryCosts = $totalAccessoriesPrice; // Use the calculated price from above
            $accessoryNames = [];
            foreach ($allAccessories as $accId => $qty) {
                $accessory = Accessorie::find($accId);
                if ($accessory) {
                    $accessoryNames[] = $accessory->name . ' (x' . $qty . ')';
                }
            }

            // GST is only on service charges (rent + accessories), not on deposit
            $serviceCharges = $totalRent + $accessoryCosts;
            $gstAmount = $serviceCharges * 0.18;
            $charges = $serviceCharges + $totalDeposit; // Total charges including deposit
            $totalAmount = $charges + $gstAmount;

            // Create aggregated object
            $aggregated = (object) [
                'group_code' => $groupCode,
                'organization' => $firstEnquiry->organization,
                'name' => $firstEnquiry->name,
                'hall' => $allHalls,
                'event_type' => $firstEnquiry->event_type,
                'program_date' => $programDate,
                'charges' => $charges,
                'gst_amount' => $gstAmount,
                'total_amount' => $totalAmount,
                'remarks' => $allSpecialNotes->unique()->implode('; '),
                'accessories_names' => implode(', ', $accessoryNames),
                'is_group' => $groupEnquiries->count() > 1,
            ];

            $aggregatedData->push($aggregated);
        }

        return $aggregatedData;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Sr No',
            'Organization Name',
            'Customer Name',
            'Hall Name',
            'Event Type',
            'Program Date',
            'Charges',
            'GST Amount',
            'Total Amount',
            'Remarks',
            'Accessories Names'
        ];
    }

    /**
     * @param mixed $data
     * @return array
     */
    public function map($data): array
    {
        static $serialNumber = 0;
        $serialNumber++; // Increment serial number for each row

        $srNo = $serialNumber;
        $organization = $data->organization ?? 'na';
        $customerName = $data->name ?? 'na';
        $hallName = $data->hall ?? 'na';
        $eventType = $data->event_type ?? 'na';
        $programDate = $data->program_date ?? 'na';
        $charges = $data->charges ?? 0;
        $gstAmount = $data->gst_amount ?? 0;
        $totalAmount = $data->total_amount ?? 0;
        $remarks = $data->remarks ?: 'na';
        $accessoriesNames = $data->accessories_names ?: 'na';

        return [
            $srNo,
            $organization,
            $customerName,
            $hallName,
            $eventType,
            $programDate,
            number_format($charges, 2),
            number_format($gstAmount, 2),
            number_format($totalAmount, 2),
            $remarks,
            $accessoriesNames
        ];
    }

    /**
     * @return array
     */
    public function columnFormats(): array
    {
        return [
            'G' => NumberFormat::FORMAT_NUMBER_00, // Charges column (7th column, 0-indexed as 6, Excel column G)
            'H' => NumberFormat::FORMAT_NUMBER_00, // GST Amount column (8th, Excel H)
            'I' => NumberFormat::FORMAT_NUMBER_00, // Total Amount column (9th, Excel I)
        ];
    }
}
