<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'staff_id', 'shop_id', 'tracking_number', 'garment_type_id',
        'fabric_preference', 'quantity', 'special_instructions',
        'design_reference_path', 'status', 'estimated_completion', 'total_amount',
    ];

    protected function casts(): array
    {
        return [
            'estimated_completion' => 'date',
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

    public function garmentType(): BelongsTo
    {
        return $this->belongsTo(GarmentType::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function designReferences(): HasMany
    {
        return $this->hasMany(DesignReference::class);
    }

    /**
     * Get the status index for progress tracking (0-7).
     */
    public function getStatusIndexAttribute(): int
    {
        $statuses = [
            'pending' => 0, 'measurements_verified' => 1, 'in_production' => 2,
            'fitting_scheduled' => 3, 'final_adjustment' => 4, 'ready_for_pickup' => 5,
            'completed' => 6, 'released' => 7,
        ];
        return $statuses[$this->status] ?? 0;
    }
}
