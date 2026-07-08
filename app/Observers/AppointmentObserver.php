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
        // Validate that the assigned user is actually a staff member
        if ($appointment->staff_id) {
            $staff = User::find($appointment->staff_id);
            if (!$staff || $staff->role !== 'tailor_staff') {
                throw new \InvalidArgumentException('The assigned user is not a tailor staff member.');
            }
        }
    }

    /**
     * Handle the Appointment "updating" event.
     */
    public function updating(Appointment $appointment): void
    {
        if ($appointment->isDirty('staff_id') && $appointment->staff_id) {
            $staff = User::find($appointment->staff_id);
            if (!$staff || $staff->role !== 'tailor_staff') {
                throw new \InvalidArgumentException('The assigned user is not a tailor staff member.');
            }
        }
    }
}
