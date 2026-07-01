<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AvailableTimeSlot extends Model
{
    protected $fillable = [
        'date', 'start_time', 'end_time', 'is_available', 'max_bookings', 'current_bookings',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_available' => 'boolean',
        ];
    }
}
