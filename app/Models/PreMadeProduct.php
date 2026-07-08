<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToShop;

class PreMadeProduct extends Model
{
    use HasFactory, BelongsToShop;

    protected $fillable = [
        'shop_id',
        'name',
        'description',
        'price',
        'image_url',
        'available_sizes',
        'is_active',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->shop_id)) {
                $model->shop_id = \App\Models\Shop::instance()->id;
            }
        });
    }

    protected function casts(): array
    {
        return [
            'available_sizes' => 'array',
            'is_active' => 'boolean',
            'price' => 'decimal:2',
        ];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'pre_made_product_id');
    }
}
