<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'booked_hall_id',
        'merchant_txn_no',
        'payphi_txn_no',
        'amount',
        'currency_code',
        'customer_email',
        'customer_mobile',
        'status',
        'response_code',
        'transaction_type',
        'full_response',
        'payment_date'
    ];

    protected $casts = [
        'full_response' => 'array',
        'payment_date' => 'datetime'
    ];

    // Relationship with BookedHall
    public function bookedHall()
    {
        return $this->belongsTo(BookedHall::class, 'booked_hall_id');
    }

    // Check if payment is successful
    public function isSuccessful()
    {
        return $this->status === 'SUCCESS';
    }

    // Check if payment is pending
    public function isPending()
    {
        return in_array($this->status, ['initiated', 'PENDING']);
    }

    // Check if payment failed
    public function isFailed()
    {
        return in_array($this->status, ['FAILED', 'CANCELLED']);
    }
}
