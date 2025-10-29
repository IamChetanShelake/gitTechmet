<?php

namespace App\Models;

use App\Models\HallEnquiry;
use App\Models\EventService;
use App\Models\CateringService;
use App\Models\PaymentTransaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BookedHall extends Model
{
    use HasFactory;

    protected $fillable = [
        'hall_enquiry_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'event_date',
        'event_time',
        'event_type',
        'hall_name',
        'duration',
        'start_time',
        'end_time',
        'catering_flag',
        'event_flag',
        'total_rent',
        'total_deposit',
        'paid_amount',
        'remaining_amount',
        'booking_code',
        'cancelled_at'
    ];

    public function enquiry()
    {
        return $this->belongsTo(HallEnquiry::class, 'hall_enquiry_id');
    }

    // Function to check if payment is completed
    public function isFullyPaid()
    {
        return $this->remaining_amount === 0 && $this->remaining_amount !== null;
    }

     // Relationship with Event Services
     public function eventServices() {
        return $this->hasMany(EventService::class, 'booked_hall_id');
    }

    //Relationship with Catering Services
    public function cateringServices() {
        return $this->hasMany(CateringService::class, 'booked_hall_id');
    }

    // Relationship with Payment Transactions
    public function paymentTransactions()
    {
        return $this->hasMany(PaymentTransaction::class, 'booked_hall_id');
    }

    // Get successful payment transactions
    public function successfulPayments()
    {
        return $this->paymentTransactions()->where('status', 'SUCCESS');
    }

    // Get total paid amount from successful transactions
    public function getTotalPaidAmount()
    {
        return $this->successfulPayments()->sum('amount');
    }

    // Check if booking has any successful payment
    public function hasSuccessfulPayment()
    {
        return $this->successfulPayments()->exists();
    }
}
