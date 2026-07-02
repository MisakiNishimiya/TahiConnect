<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToShop
{
    /**
     * Scope a query to only include records from the authenticated user's shop.
     */
    public function scopeForCurrentUserShop(Builder $query): Builder
    {
        $user = auth()->user();
        
        if (!$user) {
            return $query->whereRaw('1 = 0'); // No results if not authenticated
        }
        
        if ($user->isAdmin()) {
            return $query; // Admins can see all
        }
        
        if ($user->isShopOwner() || $user->isStaff()) {
            return $query->where('shop_id', $user->shop_id);
        }
        
        return $query;
    }
    
    /**
     * Scope a query to only include records from a specific shop.
     */
    public function scopeForShop(Builder $query, $shopId): Builder
    {
        return $query->where('shop_id', $shopId);
    }
    
    /**
     * Check if the current user can access this record.
     */
    public function canBeAccessedByCurrentUser(): bool
    {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }
        
        if ($user->isAdmin()) {
            return true;
        }
        
        if ($user->isCustomer()) {
            // Customers can only access their own orders/appointments
            return $this->user_id === $user->id;
        }
        
        if ($user->isShopOwner() || $user->isStaff()) {
            return $this->shop_id === $user->shop_id;
        }
        
        return false;
    }
}