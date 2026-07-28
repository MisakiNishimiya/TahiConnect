<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Measurement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_id',      // nullable — links to a specific order in the pre-made flow
        'chest', 'waist', 'hip', 'shoulder',
        'sleeve_length', 'inseam', 'neck', 'height',
        'validation_status', 'notes',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The order this measurement belongs to (order-specific measurement).
     * Null for legacy profile-style measurements.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
