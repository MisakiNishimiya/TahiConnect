<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    /**
     * Handle the Order "creating" event.
     */
    public function creating(Order $order): void
    {
        // Validate that the assigned user is actually a staff member
        if ($order->staff_id) {
            $staff = User::find($order->staff_id);
            if (!$staff || $staff->role !== 'tailor_staff') {
                throw new \InvalidArgumentException('The assigned user is not a tailor staff member.');
            }
        }

        // Ensure pre_made orders have the correct fields
        if ($order->order_type === 'pre_made') {
            if (!$order->pre_made_product_id) {
                throw new \InvalidArgumentException('Pre-made orders must have a product ID.');
            }
            $order->garment_type_id      = null;
            $order->fabric_preference    = null;
            $order->design_reference_path = null;
        }

        // Ensure custom orders have the correct fields
        if ($order->order_type === 'custom') {
            if (!$order->garment_type_id) {
                throw new \InvalidArgumentException('Custom orders must have a garment type.');
            }
            $order->pre_made_product_id = null;
            $order->product_size        = null;
        }
    }

    /**
     * Handle the Order "updating" event.
     */
    public function updating(Order $order): void
    {
        if ($order->isDirty('staff_id') && $order->staff_id) {
            $staff = User::find($order->staff_id);
            if (!$staff || $staff->role !== 'tailor_staff') {
                throw new \InvalidArgumentException('The assigned user is not a tailor staff member.');
            }
        }

        // Log status changes for audit trail
        if ($order->isDirty('status')) {
            Log::info('Order status changed', [
                'order_id'        => $order->id,
                'tracking_number' => $order->tracking_number,
                'old_status'      => $order->getOriginal('status'),
                'new_status'      => $order->status,
                'changed_by'      => auth()->id(),
            ]);
        }
    }
}
