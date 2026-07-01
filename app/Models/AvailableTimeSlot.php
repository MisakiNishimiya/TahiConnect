<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AvailableTimeSlot extends Model
{
    protected $fillable = [
        'shop_id', 'date', 'start_time', 'end_time', 'is_available', 'max_bookings', 'current_bookings',
    ];

    public function shop(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_available' => 'boolean',
        ];
    }
}
