<?php

namespace App\Exports;

use App\Models\HallEnquiry;
use App\Models\BookedHall;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Carbon\Carbon;

class HallEnquiryExport implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting
{
    protected $period;

    public function __construct($period = 'all')
    {
        $this->period = $period;
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
            // 'all' case - no additional filtering
        }

        $enquiries = $query->get();

        // Get corresponding booked halls data
        $bookedHalls = BookedHall::whereNull('cancelled_at')
            ->whereIn('hall_enquiry_id', $enquiries->pluck('id'))
            ->get()
            ->keyBy('hall_enquiry_id');

        // Combine the data
        return $enquiries->map(function ($enquiry) use ($bookedHalls) {
            $enquiry->booked_hall = $bookedHalls->get($enquiry->id);
            return $enquiry;
        });
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Sr. No.',
            'Customer Name',
            'Date',
            'Organization Name',
            'Event Type',
            'Mobile No.',
            'Venue',
            'Event Manager Inhouse',
            'Event Manager Outside',
            'Caterer Inhouse',
            'Caterer Outside'
        ];
    }

    /**
     * @param mixed $enquiry
     * @return array
     */
    public function map($enquiry): array
    {
        static $serialNumber = 0;
        $serialNumber++; // Increment serial number for each row

        $srNo = $serialNumber; // Sequential Sr. No. starting from 1
        $customerName = $enquiry->name ?? '';
        $date = $enquiry->event_date ? Carbon::parse($enquiry->event_date)->format('d-m-Y') : '';
        $organization = $enquiry->organization ?? '';
        $eventType = $enquiry->event_type ?? '';
        $mobileNo = $enquiry->contact_no ? "'" . $enquiry->contact_no : ''; // Prefix with single quote to force text format
        $venue = $enquiry->hall ?? '';

        // Determine Event Manager and Caterer values based on booked hall data
        $eventManagerInhouse = '';
        $eventManagerOutside = '';
        $catererInhouse = '';
        $catererOutside = '';

        if ($enquiry->booked_hall) {
            // If event_flag is 1, it's inhouse, else outside
            if ($enquiry->booked_hall->event_flag == '1') {
                $eventManagerInhouse = 'Yes';
                $eventManagerOutside = 'No';
            } else {
                $eventManagerInhouse = 'No';
                $eventManagerOutside = 'Yes';
            }

            // If catering_flag is 1, it's inhouse, else outside
            if ($enquiry->booked_hall->catering_flag == '1') {
                $catererInhouse = 'Yes';
                $catererOutside = 'No';
            } else {
                $catererInhouse = 'No';
                $catererOutside = 'Yes';
            }
        } else {
            // If no booked hall, check vendor services from enquiry
            $vendorServices = json_decode($enquiry->vendor, true) ?? [];

            if (in_array('event', $vendorServices)) {
                $eventManagerInhouse = 'Yes';
                $eventManagerOutside = 'No';
            } else {
                $eventManagerInhouse = 'No';
                $eventManagerOutside = 'Yes';
            }

            if (in_array('catering', $vendorServices)) {
                $catererInhouse = 'Yes';
                $catererOutside = 'No';
            } else {
                $catererInhouse = 'No';
                $catererOutside = 'Yes';
            }
        }

        return [
            $srNo,
            $customerName,
            $date,
            $organization,
            $eventType,
            $mobileNo,
            $venue,
            $eventManagerInhouse,
            $eventManagerOutside,
            $catererInhouse,
            $catererOutside
        ];
    }

    /**
     * @return array
     */
    public function columnFormats(): array
    {
        return [
            'F' => NumberFormat::FORMAT_TEXT, // Mobile No. column (6th column, 0-indexed as 5, Excel column F)
        ];
    }
}
