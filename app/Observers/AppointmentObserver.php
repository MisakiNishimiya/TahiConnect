<?php

namespace App\Observers;

use App\Models\Appointment;
use App\Models\User;

class AppointmentObserver
{
    /**
     * Handle the Appointment "creating" event.
     */
    public function creating(Appointment $appointment): void
    {
        // Validate staff belongs to the same shop as the appointment
        if ($appointment->staff_id) {
            $staff = User::find($appointment->staff_id);
            if (!$staff || $staff->role !== 'tailor_staff' || $staff->shop_id !== $appointment->shop_id) {
                throw new \InvalidArgumentException('Staff member must belong to the same shop as the appointment.');
            }
        }
    }

    /**
     * Handle the Appointment "updating" event.
     */
    public function updating(Appointment $appointment): void
    {
        // Validate staff assignment changes
        if ($appointment->isDirty('staff_id') && $appointment->staff_id) {
            $staff = User::find($appointment->staff_id);
            if (!$staff || $staff->role !== 'tailor_staff' || $staff->shop_id !== $appointment->shop_id) {
                throw new \InvalidArgumentException('Staff member must belong to the same shop as the appointment.');
            }
        }
    }
}