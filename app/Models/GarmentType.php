<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GarmentType extends Model
{
    use HasFactory;

    protected $fillable = ['shop_id', 'name', 'description', 'base_price', 'image_url'];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->shop_id)) {
                $model->shop_id = \App\Models\Shop::instance()->id;
            }
        });
    }

    public function shop(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
