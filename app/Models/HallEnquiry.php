<?php

namespace App\Models;

use App\Models\Hall;
use App\Models\Accessorie;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HallEnquiry extends Model
{
    use HasFactory;

    protected $table = 'hallenquirys';

    protected $fillable = [
        'group_code',
        'name',
        'organization',
        'gst_no',
        'email',
        'contact_no',
        'address',
        'referred_by',
        'event_type',
        'hall',
        'event_date',
        'event_dates',
        'duration',
        'start_time',
        'end_time',
        'expected_audience',
        'stage_chairs_count',
        'hall_chairs_count',
        'rent_amount',
        'deposit',
        'id_proof',
        'event_setup',
        'special_note',
        'accessorie',
        'vendor',
        'quotation_file',
        'rules_print_file',
        'sign_image',
        'typed_signature',
        'cancelled_at',
    ];

    // Relationship with Hall
    public function halll()
    {
        return $this->belongsTo(Hall::class, 'hall_id', 'id');
    }

    // A Hallenquiry can have multiple Accessories
    public function accessories()
    {
        return $this->hasMany(Accessorie::class, 'enquiry_id');
        // Make sure 'enquiry_id' exists in the 'accessoris' table
    }
    public function bookedhall()
    {
        return $this->hasOne(Hall::class, 'hall_enquiry_id', 'id');
    }

}
