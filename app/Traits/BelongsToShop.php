<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * BelongsToShop Trait (Single-Shop Edition)
 *
 * In the single-shop system, every operational record belongs to the one shop.
 * Super Admin has full read access for support/troubleshooting.
 * Shop Owner has full operational access.
 * Customers can only access their own records.
 */
trait BelongsToShop
{
    /**
     * Scope: return all records.
     * In a single-shop deployment all records belong to this instance.
     * Kept for backward compatibility — calls to forCurrentUserShop() still work.
     */
    public function scopeForCurrentUserShop(Builder $query): Builder
    {
        return $query;
    }

    /**
     * Scope: filter by a specific shop_id (kept for explicit queries).
     */
    public function scopeForShop(Builder $query, $shopId): Builder
    {
        return $query->where('shop_id', $shopId);
    }

    /**
     * Check if the authenticated user can access this record.
     *
     * - Super Admin: full access (support & troubleshooting)
     * - Shop Owner:  full access (manages all business operations)
     * - Tailor Staff: full access (processes orders & appointments)
     * - Customer:    own records only
     */
    public function canBeAccessedByCurrentUser(): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        // Super Admin and Shop Owner have full access
        if ($user->isSuperAdmin() || $user->isShopOwner() || $user->isStaff()) {
            return true;
        }

        // Customers can only access their own records
        return isset($this->user_id) && $this->user_id === $user->id;
    }
}
