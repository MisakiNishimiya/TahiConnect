<?php

use App\Models\Appointment;
use App\Models\AvailableTimeSlot;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Carbon\Carbon;

new #[Layout('components.layouts.app')] class extends Component {
    public string $date = '';
    public string $time = '';
    public string $type = 'consultation';
    public string $appointmentNotes = '';

    public function bookAppointment(): void
    {
        $this->validate([
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required',
            'type' => 'required|in:fitting,consultation,pickup',
        ]);

        Appointment::create([
            'user_id' => auth()->id(),
            'date' => $this->date,
            'time' => $this->time,
            'type' => $this->type,
            'notes' => $this->appointmentNotes,
            'status' => 'pending',
        ]);

        $this->reset(['date', 'time', 'type', 'appointmentNotes']);
        session()->flash('message', 'Appointment booked successfully!');
    }

    public function cancelAppointment($id): void
    {
        $appt = Appointment::where('id', $id)->where('user_id', auth()->id())->first();
        if ($appt) {
            $appt->update(['status' => 'cancelled']);
            session()->flash('message', 'Appointment cancelled.');
        }
    }

    public function with(): array
    {
        return [
            'upcomingAppointments' => Appointment::where('user_id', auth()->id())
                ->where('date', '>=', now()->toDateString())
                ->whereIn('status', ['pending', 'confirmed'])
                ->orderBy('date')->orderBy('time')->get(),
            'pastAppointments' => Appointment::where('user_id', auth()->id())
                ->where(function($q) { $q->where('date', '<', now()->toDateString())->orWhereIn('status', ['completed', 'cancelled']); })
                ->latest('date')->take(10)->get(),
            'timeSlots' => ['09:00', '10:00', '11:00', '13:00', '14:00', '15:00', '16:00'],
        ];
    }
}; ?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">Appointments</h1>
            <p class="text-zinc-500 mt-1">Schedule and manage your tailoring appointments.</p>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm">{{ session('message') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Booking Form -->
        <div class="tc-card">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white mb-6" style="font-family: 'Poppins';">Book Appointment</h2>
            <form wire:submit="bookAppointment" class="space-y-5">
                <flux:input wire:model="date" label="Date" type="date" min="{{ now()->toDateString() }}" required />

                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Available Time Slots</label>
                    <div class="grid grid-cols-3 sm:grid-cols-4 gap-2">
                        @foreach($timeSlots as $slot)
                            <button type="button" wire:click="$set('time', '{{ $slot }}')"
                                class="px-3 py-2 text-sm rounded-lg border transition-all {{ $time === $slot ? 'bg-primary-500 text-white border-primary-500' : 'border-zinc-200 text-zinc-700 hover:border-primary-300 dark:border-zinc-600 dark:text-zinc-300' }}">
                                {{ \Carbon\Carbon::parse($slot)->format('g:i A') }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Appointment Type</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach(['consultation' => 'Consultation', 'fitting' => 'Fitting', 'pickup' => 'Pickup'] as $val => $label)
                            <button type="button" wire:click="$set('type', '{{ $val }}')"
                                class="px-4 py-2 text-sm rounded-lg border transition-all {{ $type === $val ? 'bg-primary-500 text-white border-primary-500' : 'border-zinc-200 text-zinc-700 hover:border-primary-300 dark:border-zinc-600 dark:text-zinc-300' }}">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <flux:textarea wire:model="appointmentNotes" label="Notes (optional)" placeholder="Any special requests..." rows="2" />

                <flux:button type="submit" variant="primary" class="w-full !bg-primary-500 hover:!bg-primary-600">Book Appointment</flux:button>
            </form>
        </div>

        <!-- Upcoming Appointments -->
        <div class="space-y-4">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">Upcoming Appointments</h2>
            @forelse($upcomingAppointments as $appt)
                <div class="tc-card animate-fade-in-up">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                <div class="text-center">
                                    <p class="text-xs font-bold text-blue-700 dark:text-blue-300">{{ $appt->date->format('M') }}</p>
                                    <p class="text-lg font-bold text-blue-700 dark:text-blue-300 leading-none">{{ $appt->date->format('d') }}</p>
                                </div>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-zinc-900 dark:text-white">{{ $appt->date->format('l, M d, Y') }}</p>
                                <p class="text-xs text-zinc-500">{{ \Carbon\Carbon::parse($appt->time)->format('g:i A') }} · {{ ucfirst($appt->type) }}</p>
                            </div>
                        </div>
                        <span class="tc-badge tc-badge-{{ $appt->status }}">{{ ucfirst($appt->status) }}</span>
                    </div>
                    @if($appt->notes)
                        <p class="text-xs text-zinc-500 mt-2 ml-15">{{ $appt->notes }}</p>
                    @endif
                    @if($appt->status !== 'cancelled')
                        <div class="flex gap-2 mt-3">
                            <button wire:click="cancelAppointment({{ $appt->id }})" class="text-xs text-red-600 hover:text-red-800 transition-colors">Cancel</button>
                        </div>
                    @endif
                </div>
            @empty
                <div class="tc-card text-center py-8">
                    <svg class="w-12 h-12 mx-auto text-zinc-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                    <p class="text-sm text-zinc-400">No upcoming appointments</p>
                </div>
            @endforelse

            @if($pastAppointments->count() > 0)
                <h3 class="text-md font-semibold text-zinc-700 dark:text-zinc-300 mt-6">Past Appointments</h3>
                @foreach($pastAppointments->take(3) as $appt)
                    <div class="tc-card opacity-60">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-zinc-700 dark:text-zinc-300">{{ $appt->date->format('M d, Y') }} · {{ \Carbon\Carbon::parse($appt->time)->format('g:i A') }}</p>
                                <p class="text-xs text-zinc-500">{{ ucfirst($appt->type) }}</p>
                            </div>
                            <span class="tc-badge tc-badge-{{ $appt->status }}">{{ ucfirst($appt->status) }}</span>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>
