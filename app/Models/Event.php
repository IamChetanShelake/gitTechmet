<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'event_date',
        'event_time',
        'location',
        'image',
        'is_active'
    ];

    protected $casts = [
        'event_date' => 'date',
        'event_time' => 'datetime:H:i',
        'is_active' => 'boolean'
    ];

    /**
     * Scope for upcoming events
     */
    public function scopeUpcoming($query)
    {
        return $query->where('event_date', '>=', now()->toDateString())
                    ->where('is_active', true)
                    ->orderBy('event_date', 'asc')
                    ->orderBy('event_time', 'asc');
    }

    /**
     * Get the formatted event date
     */
    public function getFormattedDateAttribute()
    {
        return $this->event_date->format('M d, Y');
    }

    /**
     * Get the formatted event time
     */
    public function getFormattedTimeAttribute()
    {
        return $this->event_time ? $this->event_time->format('h:i A') : null;
    }

    /**
     * Get hall information based on location (hall name)
     */
    public function getHallInfoAttribute()
    {
        if ($this->location) {
            $hall = \App\Models\Hall::where('name', $this->location)->first();
            return $hall;
        }
        return null;
    }
}
