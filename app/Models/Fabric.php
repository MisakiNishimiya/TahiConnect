<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fabric extends Model
{
    protected $fillable = [
        'shop_id', 'name', 'material', 'color', 'price_per_meter', 'image_url', 'in_stock',
    ];

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

    protected function casts(): array
    {
        return [
            'in_stock' => 'boolean',
        ];
    }
}
