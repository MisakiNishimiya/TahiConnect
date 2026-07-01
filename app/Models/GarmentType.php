<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GarmentType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'base_price', 'image_url'];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
