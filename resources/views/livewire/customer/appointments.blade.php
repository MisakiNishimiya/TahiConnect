<?php

use App\Models\Appointment;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
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
            'upcomingAppointments' => Appointment::with('shop')
                ->where('user_id', auth()->id())
                ->where('date', '>=', now()->toDateString())
                ->whereIn('status', ['pending', 'confirmed'])
                ->orderBy('date')->orderBy('time')->get(),
            'pastAppointments' => Appointment::with('shop')
                ->where('user_id', auth()->id())
                ->where(function($q) { $q->where('date', '<', now()->toDateString())->orWhereIn('status', ['completed', 'cancelled']); })
                ->latest('date')->take(10)->get(),
        ];
    }
}; ?>

<div class="space-y-6 max-w-4xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">Appointments</h1>
            <p class="text-zinc-500 mt-1">Manage your upcoming fittings and consultations.</p>
        </div>
        <div>
            <flux:button variant="primary" icon="plus" :href="route('customer.shops')" wire:navigate>Find a Tailor to Book</flux:button>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm">{{ session('message') }}</div>
    @endif

    <div class="space-y-6">
        <!-- Upcoming Appointments -->
        <div>
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4" style="font-family: 'Poppins';">Upcoming Appointments</h2>
            <div class="grid grid-cols-1 gap-4">
                @forelse($upcomingAppointments as $appt)
                    <div class="tc-card animate-fade-in-up">
                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                            <div class="flex items-start gap-4">
                                <div class="w-14 h-14 rounded-xl bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center shrink-0">
                                    <div class="text-center">
                                        <p class="text-xs font-bold text-primary-700 dark:text-primary-300 uppercase">{{ $appt->date->format('M') }}</p>
                                        <p class="text-xl font-bold text-primary-700 dark:text-primary-300 leading-none">{{ $appt->date->format('d') }}</p>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="font-bold text-zinc-900 dark:text-white text-lg">{{ $appt->shop?->name ?? 'Unknown Shop' }}</h3>
                                    <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $appt->date->format('l, F j, Y') }} at {{ \Carbon\Carbon::parse($appt->time)->format('g:i A') }}</p>
                                    <p class="text-sm text-zinc-500">{{ ucfirst($appt->type) }}</p>
                                    
                                    @if($appt->notes)
                                        <div class="mt-3 p-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg text-sm text-zinc-600 dark:text-zinc-400">
                                            <span class="font-medium text-zinc-700 dark:text-zinc-300">Notes:</span> {{ $appt->notes }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="flex flex-col items-end justify-between gap-4">
                                <span class="tc-badge tc-badge-{{ $appt->status }}">{{ ucfirst($appt->status) }}</span>
                                
                                @if($appt->status !== 'cancelled')
                                    <button wire:click="cancelAppointment({{ $appt->id }})" wire:confirm="Are you sure you want to cancel this appointment?" class="text-sm text-red-600 hover:text-red-800 transition-colors font-medium">Cancel Booking</button>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="tc-card text-center py-12">
                        <flux:icon.calendar class="w-12 h-12 mx-auto text-zinc-300 mb-3" />
                        <h3 class="text-lg font-bold text-zinc-900 dark:text-white mb-2">No upcoming appointments</h3>
                        <p class="text-sm text-zinc-500 mb-4">You don't have any fittings or consultations scheduled.</p>
                        <flux:button variant="outline" :href="route('customer.shops')" wire:navigate>Find a Tailor</flux:button>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Past Appointments -->
        @if($pastAppointments->count() > 0)
            <div class="pt-6">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4" style="font-family: 'Poppins';">Past Appointments</h3>
                <div class="space-y-3">
                    @foreach($pastAppointments as $appt)
                        <div class="p-4 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white/50 dark:bg-zinc-800/50">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-zinc-900 dark:text-white">{{ $appt->shop?->name ?? 'Unknown Shop' }}</p>
                                    <p class="text-sm text-zinc-500">{{ $appt->date->format('M d, Y') }} at {{ \Carbon\Carbon::parse($appt->time)->format('g:i A') }}</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <p class="text-sm text-zinc-500">{{ ucfirst($appt->type) }}</p>
                                    <span class="tc-badge tc-badge-{{ $appt->status }}">{{ ucfirst($appt->status) }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
