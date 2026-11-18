<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VirtualTryon extends Model
{
    protected $fillable = [
        'user_id',
        'customer_photo_path',
        'garment_design_path',
        'preview_path',
        'status',
        'processing_status',
        'error_message',
        'processing_started_at',
        'processing_completed_at',
        'preview_data',
        'metadata',
    ];

    protected $casts = [
        'processing_started_at' => 'datetime',
        'processing_completed_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if processing is complete
     */
    public function isProcessingComplete(): bool
    {
        return $this->processing_status === 'completed';
    }

    /**
     * Check if processing failed
     */
    public function hasProcessingError(): bool
    {
        return $this->processing_status === 'failed';
    }

    /**
     * Get processing duration in seconds
     */
    public function getProcessingDurationSeconds(): ?int
    {
        if (!$this->processing_started_at || !$this->processing_completed_at) {
            return null;
        }

        return $this->processing_completed_at->diffInSeconds($this->processing_started_at);
    }
}

