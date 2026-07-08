<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToShop;

class Order extends Model
{
    use HasFactory, BelongsToShop;

    protected $fillable = [
        'user_id', 'staff_id', 'shop_id', 'tracking_number', 'garment_type_id',
        'fabric_preference', 'quantity', 'special_instructions',
        'design_reference_path', 'status', 'estimated_completion', 'total_amount',
        'order_type', 'pre_made_product_id', 'product_size',
    ];

    protected function casts(): array
    {
        return [
            'estimated_completion' => 'date',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────────────────

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

    public function preMadeProduct(): BelongsTo
    {
        return $this->belongsTo(PreMadeProduct::class, 'pre_made_product_id');
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

    public function reviews(): HasMany
    {
        return $this->hasMany(ShopReview::class);
    }

    // ── Status helpers ────────────────────────────────────────────────────────

    /**
     * Get the status index for progress tracking.
     */
    public function getStatusIndexAttribute(): int
    {
        if ($this->order_type === 'pre_made') {
            $statuses = [
                'pending'          => 0,
                'in_production'    => 1,
                'ready_for_pickup' => 2,
                'completed'        => 3,
                'released'         => 4,
            ];
        } else {
            $statuses = [
                'pending'               => 0,
                'measurements_verified' => 1,
                'in_production'         => 2,
                'fitting_scheduled'     => 3,
                'final_adjustment'      => 4,
                'ready_for_pickup'      => 5,
                'completed'             => 6,
                'released'              => 7,
            ];
        }
        return $statuses[$this->status] ?? 0;
    }

    /**
     * Get the maximum status index for the order type.
     */
    public function getMaxStatusIndexAttribute(): int
    {
        return $this->order_type === 'pre_made' ? 4 : 7;
    }

    public function requiresMeasurements(): bool
    {
        return $this->order_type === 'custom';
    }

    public function requiresFittings(): bool
    {
        return $this->order_type === 'custom';
    }

    /**
     * Get valid next statuses for this order.
     */
    public function getValidNextStatuses(): array
    {
        if ($this->order_type === 'pre_made') {
            return match($this->status) {
                'pending'          => ['in_production'],
                'in_production'    => ['ready_for_pickup'],
                'ready_for_pickup' => ['completed'],
                'completed'        => ['released'],
                default            => []
            };
        } else {
            return match($this->status) {
                'pending'               => ['measurements_verified'],
                'measurements_verified' => ['in_production'],
                'in_production'         => ['fitting_scheduled'],
                'fitting_scheduled'     => ['final_adjustment'],
                'final_adjustment'      => ['ready_for_pickup'],
                'ready_for_pickup'      => ['completed'],
                'completed'             => ['released'],
                default                 => []
            };
        }
    }
}
