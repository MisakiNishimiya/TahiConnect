<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\User;

class StaffBelongsToShop implements ValidationRule
{
    protected $shopId;

    public function __construct($shopId)
    {
        $this->shopId = $shopId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value) {
            return; // Allow null staff_id
        }

        $staff = User::find($value);
        
        if (!$staff) {
            $fail('The selected staff member does not exist.');
            return;
        }

        if ($staff->role !== 'tailor_staff') {
            $fail('The selected user is not a staff member.');
            return;
        }

        if ($staff->shop_id != $this->shopId) {
            $fail('The selected staff member does not belong to this shop.');
            return;
        }
    }
}