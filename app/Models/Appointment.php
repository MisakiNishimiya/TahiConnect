<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BelongsToShop;

class Appointment extends Model
{
    use HasFactory, BelongsToShop;

    protected $fillable = [
        'user_id', 'staff_id', 'shop_id', 'date', 'time', 'type', 'status', 'notes',
    ];

    /**
     * Auto-assign the single shop on creation if not provided.
     */
    protected static function booted(): void
    {
        static::creating(function ($appointment) {
            if (empty($appointment->shop_id)) {
                $appointment->shop_id = \App\Models\Shop::instance()->id;
            }
        });
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function getTypeLabelAttribute(): string
    {
        $typeLabels = [
            'initial_measurement' => 'Initial Measurement & Consultation',
            'fabric_selection' => 'Fabric Selection & Touch-and-Feel',
            'baste_fitting' => 'Baste Fitting',
            'final_pickup' => 'Final Adjustments & Pickup',
            'consultation' => 'Initial Consultation',
            'fitting' => 'Fitting',
            'pickup' => 'Pickup'
        ];
        return $typeLabels[$this->type] ?? ucfirst(str_replace('_', ' ', $this->type));
    }
}
