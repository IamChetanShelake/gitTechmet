<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HallEnquiry;
use App\Models\BookedHall;
use Carbon\Carbon;

class TestReminderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Calculate the target date (8 days from now)
        $targetDate = Carbon::now()->addDays(8)->toDateString();

        // Create a test HallEnquiry
        $enquiry = HallEnquiry::create([
            'group_code' => null, // Single hall booking
            'name' => 'Test Customer',
            'organization' => 'Test Organization',
            'email' => 'test@example.com',
            'contact_no' => '9876543210',
            'address' => 'Test Address',
            'event_type' => 'Wedding',
            'hall' => 'Main Hall',
            'event_date' => $targetDate,
            'duration' => '4 hours',
            'start_time' => '10:00',
            'end_time' => '14:00',
            'expected_audience' => '200',
            'rent_amount' => 50000,
            'deposit' => 10000,
            'accessorie' => json_encode([1, 2]), // Assuming some accessory IDs
        ]);

        // Create a BookedHall linked to the enquiry
        BookedHall::create([
            'group_code' => null,
            'hall_enquiry_id' => $enquiry->id,
            'customer_name' => $enquiry->name,
            'customer_phone' => $enquiry->contact_no,
            'customer_email' => $enquiry->email,
            'event_date' => $targetDate,
            'event_time' => $enquiry->start_time,
            'event_type' => $enquiry->event_type,
            'hall_name' => $enquiry->hall,
            'duration' => $enquiry->duration,
            'start_time' => $enquiry->start_time,
            'end_time' => $enquiry->end_time,
            'total_rent' => $enquiry->rent_amount,
            'total_deposit' => $enquiry->deposit,
            'paid_amount' => 0, // Assuming no payment made yet
            'remaining_amount' => $enquiry->rent_amount + $enquiry->deposit, // Full amount remaining
            'catering_flag' => 0,
            'event_flag' => 0,
            'booking_code' => 'TEST' . time(),
        ]);

        $this->command->info('Test reminder data seeded successfully for date: ' . $targetDate);
    }
}
