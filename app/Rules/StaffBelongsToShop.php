<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\User;

/**
 * StaffBelongsToShop (Single-Shop Edition)
 *
 * In a single-shop system, any tailor_staff user is valid staff.
 * The shop_id check is removed since all staff belong to the one shop.
 */
class StaffBelongsToShop implements ValidationRule
{
    // $shopId kept in constructor for backward compatibility but no longer used.
    public function __construct(protected mixed $shopId = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value) {
            return; // Null staff_id is allowed
        }

        $staff = User::find($value);

        if (!$staff) {
            $fail('The selected staff member does not exist.');
            return;
        }

        if ($staff->role !== 'tailor_staff') {
            $fail('The selected user is not a staff member.');
        }
    }
}
