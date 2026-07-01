<?php

use App\Models\Appointment;
use App\Models\Measurement;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
    public function markCompleted(int $id): void
    {
        Appointment::where('id', $id)->where('staff_id', auth()->id())->update(['status' => 'completed']);
        session()->flash('message', 'Appointment marked as completed.');
    }

    public function confirmAppointment(int $id): void
    {
        Appointment::where('id', $id)->where('staff_id', auth()->id())->update(['status' => 'confirmed']);
        session()->flash('message', 'Appointment confirmed.');
    }

    public function with(): array
    {
        $staffId = auth()->id();
        return [
            'todayAppointments' => Appointment::where('staff_id', $staffId)->whereDate('date', today())->with('user')->orderBy('time')->get(),
            'upcomingAppointments' => Appointment::where('staff_id', $staffId)->where('date', '>', today())->whereIn('status', ['pending', 'confirmed'])->with('user')->orderBy('date')->orderBy('time')->take(10)->get(),
            'pastAppointments' => Appointment::where('staff_id', $staffId)->where(function($q) { $q->where('date', '<', today())->orWhere('status', 'completed'); })->with('user')->latest('date')->take(5)->get(),
        ];
    }
}; ?>

<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">My Appointments</h1>
        <p class="text-zinc-500 mt-1">Manage your scheduled fittings, consultations, and pickups.</p>
    </div>

    @if (session()->has('message'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm">{{ session('message') }}</div>
    @endif

    <!-- Today's Appointments -->
    <div>
        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white mb-3 flex items-center gap-2" style="font-family: 'Poppins';">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
            Today — {{ now()->format('l, F d') }}
        </h2>
        @if($todayAppointments->count())
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($todayAppointments as $appt)
                    <div class="tc-card border-l-4 border-l-primary-500 animate-fade-in-up">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-14 h-14 rounded-xl bg-primary-100 dark:bg-primary-900/30 flex flex-col items-center justify-center">
                                    <p class="text-lg font-bold text-primary-700 dark:text-primary-300 leading-none">{{ \Carbon\Carbon::parse($appt->time)->format('g:i') }}</p>
                                    <p class="text-[10px] text-primary-500">{{ \Carbon\Carbon::parse($appt->time)->format('A') }}</p>
                                </div>
                                <div>
                                    <p class="font-semibold text-zinc-900 dark:text-white">{{ $appt->user?->name }}</p>
                                    <p class="text-xs text-zinc-500">{{ ucfirst($appt->type) }}</p>
                                    @if($appt->notes)
                                        <p class="text-xs text-zinc-400 mt-1">{{ $appt->notes }}</p>
                                    @endif
                                </div>
                            </div>
                            <span class="tc-badge tc-badge-{{ $appt->status }}">{{ ucfirst($appt->status) }}</span>
                        </div>
                        <div class="flex gap-2 mt-3 pt-3 border-t border-zinc-100 dark:border-zinc-700">
                            @if($appt->status === 'pending')
                                <flux:button wire:click="confirmAppointment({{ $appt->id }})" variant="primary" size="sm" class="!bg-primary-500 hover:!bg-primary-600">Confirm</flux:button>
                            @endif
                            @if($appt->status !== 'completed' && $appt->status !== 'cancelled')
                                <flux:button wire:click="markCompleted({{ $appt->id }})" variant="ghost" size="sm">Mark Complete</flux:button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="tc-card text-center py-8">
                <p class="text-sm text-zinc-400">No appointments scheduled for today. Enjoy your free time! ☕</p>
            </div>
        @endif
    </div>

    <!-- Upcoming -->
    <div>
        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white mb-3" style="font-family: 'Poppins';">Upcoming</h2>
        <div class="space-y-3">
            @forelse($upcomingAppointments as $appt)
                <div class="tc-card !p-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-center">
                            <div>
                                <p class="text-[10px] font-bold text-blue-700 dark:text-blue-300">{{ $appt->date->format('M') }}</p>
                                <p class="text-sm font-bold text-blue-700 dark:text-blue-300 leading-none">{{ $appt->date->format('d') }}</p>
                            </div>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-zinc-900 dark:text-white">{{ $appt->user?->name }}</p>
                            <p class="text-xs text-zinc-500">{{ \Carbon\Carbon::parse($appt->time)->format('g:i A') }} · {{ ucfirst($appt->type) }}</p>
                        </div>
                    </div>
                    <span class="tc-badge tc-badge-{{ $appt->status }}">{{ ucfirst($appt->status) }}</span>
                </div>
            @empty
                <p class="text-sm text-zinc-400 text-center py-4">No upcoming appointments.</p>
            @endforelse
        </div>
    </div>

    <!-- Past -->
    @if($pastAppointments->count())
        <div>
            <h2 class="text-lg font-semibold text-zinc-700 dark:text-zinc-300 mb-3" style="font-family: 'Poppins';">Past</h2>
            <div class="space-y-2">
                @foreach($pastAppointments as $appt)
                    <div class="tc-card !p-3 flex items-center justify-between opacity-60">
                        <div>
                            <p class="text-sm text-zinc-700 dark:text-zinc-300">{{ $appt->user?->name }} — {{ $appt->date->format('M d') }}</p>
                            <p class="text-xs text-zinc-500">{{ ucfirst($appt->type) }}</p>
                        </div>
                        <span class="tc-badge tc-badge-{{ $appt->status }}">{{ ucfirst($appt->status) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
