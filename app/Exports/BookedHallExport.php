<?php

namespace App\Exports;

use App\Models\BookedHall;
use App\Models\HallEnquiry;
use App\Models\Accessorie;
use App\Models\PaymentTransaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Carbon\Carbon;

class BookedHallExport implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting
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
        // Base query for booked halls
        $query = BookedHall::whereNull('cancelled_at');

        // Apply date filtering based on period
        switch ($this->period) {
            case 'weekly':
                $query->where('created_at', '>=', now()->startOfWeek())
                      ->where('created_at', '<=', now()->endOfWeek());
                break;
            case 'monthly':
                $query->where('created_at', '>=', now()->startOfMonth())
                      ->where('created_at', '<=', now()->endOfMonth());
                break;
            case 'yearly':
                $query->where('created_at', '>=', now()->startOfYear())
                      ->where('created_at', '<=', now()->endOfYear());
                break;
            case 'custom':
                if ($this->dateFrom && $this->dateTo) {
                    $query->where('created_at', '>=', $this->dateFrom)
                          ->where('created_at', '<=', $this->dateTo);
                }
                break;
            // 'all' case - no additional filtering
        }

        $bookedHalls = $query->get();

        // Group booked halls by group_code (treat single bookings as their own group)
        $grouped = $bookedHalls->groupBy(function ($booking) {
            return $booking->group_code ?: 'single_' . $booking->id;
        });

        // Create aggregated data for each group
        $aggregatedData = collect();
        foreach ($grouped as $groupCode => $groupBookings) {
            $firstBooking = $groupBookings->first();

            // Aggregate data
            $allHalls = $groupBookings->pluck('hall_name')->unique()->implode(', ');
            $allDates = [];

            // For booked halls, get dates from related enquiries
            $firstEnquiry = null;
            foreach ($groupBookings as $booking) {
                $enquiry = HallEnquiry::find($booking->hall_enquiry_id);
                if ($enquiry) {
                    $firstEnquiry = $firstEnquiry ?? $enquiry;
                    if ($enquiry->event_dates) {
                        $dates = json_decode($enquiry->event_dates, true) ?? [];
                        $allDates = array_merge($allDates, $dates);
                    } else {
                        $allDates[] = $enquiry->event_date;
                    }
                }
            }

            // Unique dates, sort
            $allDates = array_unique($allDates);
            sort($allDates);
            $programDate = implode(', ', array_map(function($date) {
                return $date ? Carbon::parse($date)->format('d-m-Y') : '';
            }, $allDates));

            $totalPaidAmount = 0;
            $allAccessories = collect();
            $allSpecialNotes = collect();

            foreach ($groupBookings as $booking) {
                // Sum paid amounts (this matches what the table shows as total)
                $totalPaidAmount += (float) ($booking->paid_amount ?? 0);

                // Get accessories from related enquiry
                $enquiry = HallEnquiry::find($booking->hall_enquiry_id);
                if ($enquiry && $enquiry->accessorie) {
                    $accs = json_decode($enquiry->accessorie, true) ?? [];
                    foreach ($accs as $accId => $qty) {
                        $allAccessories->put($accId, ($allAccessories->get($accId, 0) + $qty));
                    }
                }

                // Collect special notes from enquiry
                if ($enquiry && $enquiry->special_note) {
                    $allSpecialNotes->push($enquiry->special_note);
                }
            }

            // Calculate accessories cost
            $accessoryCosts = 0;
            $accessoryNames = [];
            foreach ($allAccessories as $accId => $qty) {
                $accessory = Accessorie::find($accId);
                if ($accessory) {
                    $accessoryNames[] = $accessory->name . ' (x' . $qty . ')';
                    $accessoryCosts += ((float) ($accessory->cost ?? 0)) * $qty;
                }
            }

            // Use the total paid amount as the final total (matches table display)
            $totalAmount = $totalPaidAmount;
            // Reverse calculate charges and GST from the total
            $charges = $totalAmount / 1.18;
            $gstAmount = $totalAmount - $charges;

            // Create aggregated object
            $aggregated = (object) [
                'group_code' => $groupCode,
                'organization' => $firstEnquiry ? ($firstEnquiry->organization ?? 'na') : 'na',
                'name' => $firstEnquiry ? ($firstEnquiry->name ?? 'na') : 'na',
                'hall' => $allHalls,
                'event_type' => $firstEnquiry ? ($firstEnquiry->event_type ?? 'na') : 'na',
                'program_date' => $programDate,
                'charges' => $charges,
                'gst_amount' => $gstAmount,
                'total_amount' => $totalAmount,
                'remarks' => $allSpecialNotes->unique()->implode('; ') ?: 'na',
                'accessories_names' => implode(', ', $accessoryNames) ?: 'na',
                'is_group' => $groupBookings->count() > 1,
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
