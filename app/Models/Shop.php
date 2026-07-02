<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Shop extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'address', 'barangay', 'city', 'province',
        'latitude', 'longitude', 'contact_number', 'email', 'logo_url', 'cover_photo_url',
        'operating_hours', 'specialties', 'rating', 'total_reviews',
        'is_verified', 'is_active', 'is_featured', 'commission_rate',
    ];

    protected function casts(): array
    {
        return [
            'operating_hours' => 'array',
            'specialties' => 'array',
            'is_verified' => 'boolean',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($shop) {
            if (empty($shop->slug)) {
                $shop->slug = Str::slug($shop->name);
            }
        });
    }

    // Relationships
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function staff(): HasMany
    {
        return $this->hasMany(User::class, 'shop_id')->where('role', 'tailor_staff');
    }

    public function members(): HasMany
    {
        return $this->hasMany(User::class, 'shop_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function garmentTypes(): HasMany
    {
        return $this->hasMany(GarmentType::class);
    }

    public function fabrics(): HasMany
    {
        return $this->hasMany(Fabric::class);
    }

    public function preMadeProducts(): HasMany
    {
        return $this->hasMany(PreMadeProduct::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function timeSlots(): HasMany
    {
        return $this->hasMany(AvailableTimeSlot::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ShopReview::class);
    }

    // Helpers
    public function getStarRatingAttribute(): string
    {
        $full = floor($this->rating);
        $half = ($this->rating - $full) >= 0.5 ? 1 : 0;
        $empty = 5 - $full - $half;
        return str_repeat('★', $full) . ($half ? '½' : '') . str_repeat('☆', $empty);
    }

    public function getSpecialtiesListAttribute(): string
    {
        return is_array($this->specialties) ? implode(', ', $this->specialties) : '';
    }
}
