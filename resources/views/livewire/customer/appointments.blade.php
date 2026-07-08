<?php

use App\Models\Appointment;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
    public string $activeTab = 'upcoming';
    public bool $showCancelModal = false;
    public ?Appointment $appointmentToCancel = null;

    public function cancelAppointment($id): void
    {
        $appt = Appointment::where('id', $id)->where('user_id', auth()->id())->first();
        if ($appt) {
            $appt->update(['status' => 'cancelled']);
            session()->flash('message', 'Appointment cancelled successfully.');
            $this->showCancelModal = false;
            $this->appointmentToCancel = null;
        }
    }

    public function confirmCancel($id): void
    {
        $this->appointmentToCancel = Appointment::where('id', $id)->where('user_id', auth()->id())->first();
        $this->showCancelModal = true;
    }

    public function closeCancelModal(): void
    {
        $this->showCancelModal = false;
        $this->appointmentToCancel = null;
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
                ->where(function($q) { 
                    $q->where('date', '<', now()->toDateString())
                      ->orWhereIn('status', ['completed', 'cancelled']); 
                })
                ->latest('date')->take(10)->get(),
            'appointmentCounts' => [
                'upcoming' => Appointment::where('user_id', auth()->id())
                    ->where('date', '>=', now()->toDateString())
                    ->whereIn('status', ['pending', 'confirmed'])
                    ->count(),
                'past' => Appointment::where('user_id', auth()->id())
                    ->where(function($q) { 
                        $q->where('date', '<', now()->toDateString())
                          ->orWhereIn('status', ['completed', 'cancelled']); 
                    })
                    ->count(),
            ]
        ];
    }
}; ?>

<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-primary-600 dark:text-primary-400" style="font-family: 'Poppins';">My Appointments</h1>
            <p class="text-zinc-500 mt-1">Manage your fittings, consultations, and measurements sessions.</p>
        </div>
        <div class="flex gap-3">
            <button 
                onclick="window.location.href='{{ route('customer.book') }}'"
                class="px-6 py-3 bg-gradient-to-r from-primary-500 to-primary-600 text-white rounded-xl hover:from-primary-600 hover:to-primary-700 transition-all duration-300 font-medium shadow-lg hover:shadow-xl hover:shadow-primary-500/25 click-feedback flex items-center gap-2"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Book New Appointment
            </button>
        </div>
    </div>

    <!-- Success Message -->
    @if (session()->has('message'))
        <x-notification-toast 
            type="success" 
            title="Success!" 
            message="{{ session('message') }}"
            :dismissible="true"
        />
    @endif

    <!-- Tab Navigation -->
    <div class="tc-card !p-0 overflow-hidden">
        <div class="flex border-b border-zinc-100 dark:border-zinc-700">
            <button 
                wire:click="$set('activeTab', 'upcoming')"
                class="flex-1 px-6 py-4 text-sm font-medium transition-all duration-300 relative {{ $activeTab === 'upcoming' ? 'text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20' : 'text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800/50' }}"
            >
                <span class="flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Upcoming
                    <span class="ml-1 px-2 py-0.5 text-xs rounded-full {{ $activeTab === 'upcoming' ? 'bg-primary-200 dark:bg-primary-800 text-primary-800 dark:text-primary-200' : 'bg-zinc-100 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-400' }}">
                        {{ $appointmentCounts['upcoming'] }}
                    </span>
                </span>
                @if($activeTab === 'upcoming')
                    <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-primary-500"></div>
                @endif
            </button>
            
            <button 
                wire:click="$set('activeTab', 'past')"
                class="flex-1 px-6 py-4 text-sm font-medium transition-all duration-300 relative {{ $activeTab === 'past' ? 'text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20' : 'text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800/50' }}"
            >
                <span class="flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    History
                    <span class="ml-1 px-2 py-0.5 text-xs rounded-full {{ $activeTab === 'past' ? 'bg-primary-200 dark:bg-primary-800 text-primary-800 dark:text-primary-200' : 'bg-zinc-100 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-400' }}">
                        {{ $appointmentCounts['past'] }}
                    </span>
                </span>
                @if($activeTab === 'past')
                    <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-primary-500"></div>
                @endif
            </button>
        </div>
    </div>

    <!-- Upcoming Appointments -->
    @if($activeTab === 'upcoming')
        <div class="space-y-4">
            @forelse($upcomingAppointments as $appt)
                <div class="tc-card hover-lift interactive-card group animate-fade-in-up" style="--stagger-index: {{ $loop->index }}">
                    <!-- Mobile-First Layout -->
                    <div class="flex flex-col lg:flex-row lg:items-center gap-4">
                        <!-- Date Badge & Shop Info -->
                        <div class="flex items-start gap-4 flex-1">
                            <!-- Enhanced Date Badge -->
                            <div class="relative">
                                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary-100 to-primary-200 dark:from-primary-900/30 dark:to-primary-800/30 flex flex-col items-center justify-center border border-primary-200 dark:border-primary-800 shadow-sm">
                                    <span class="text-xs font-bold text-primary-600 dark:text-primary-400 uppercase tracking-wide">{{ $appt->date->format('M') }}</span>
                                    <span class="text-xl font-bold text-primary-700 dark:text-primary-300 leading-none">{{ $appt->date->format('d') }}</span>
                                </div>
                                @if($appt->date->isToday())
                                    <div class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full animate-pulse"></div>
                                @elseif($appt->date->isTomorrow())
                                    <div class="absolute -top-1 -right-1 w-3 h-3 bg-amber-500 rounded-full animate-pulse"></div>
                                @endif
                            </div>

                            <!-- Appointment Details -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between mb-2">
                                    <h3 class="text-lg font-bold text-zinc-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors" style="font-family: 'Poppins';">
                                        {{ $appt->shop?->name ?? 'Unknown Shop' }}
                                    </h3>
                                    <span class="tc-badge tc-badge-{{ $appt->status }} ml-2 shrink-0">
                                        {{ ucfirst($appt->status) }}
                                    </span>
                                </div>
                                
                                <!-- DateTime & Type -->
                                <div class="space-y-2 mb-3">
                                    <div class="flex items-center gap-2 text-sm">
                                        <svg class="w-4 h-4 text-zinc-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span class="font-medium text-zinc-700 dark:text-zinc-300">
                                            {{ $appt->date->format('l, F j, Y') }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2 text-sm">
                                        <svg class="w-4 h-4 text-zinc-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span class="font-medium text-zinc-700 dark:text-zinc-300">
                                            {{ \Carbon\Carbon::parse($appt->time)->format('g:i A') }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2 text-sm">
                                        <svg class="w-4 h-4 text-zinc-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v11a2 2 0 002 2h2m0-13h10a2 2 0 012 2v11a2 2 0 01-2 2H9m0-13v13"/>
                                        </svg>
                                        <span class="text-zinc-600 dark:text-zinc-400">
                                            {{ $appt->type_label }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Time Until Appointment -->
                                @php
                                    $appointmentDateTime = \Carbon\Carbon::parse($appt->date->format('Y-m-d') . ' ' . $appt->time);
                                    $timeUntil = now()->diffForHumans($appointmentDateTime);
                                @endphp
                                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-medium {{ $appt->date->isToday() ? 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400' : ($appt->date->isTomorrow() ? 'bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400' : 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400') }}">
                                    <div class="w-2 h-2 rounded-full {{ $appt->date->isToday() ? 'bg-red-400' : ($appt->date->isTomorrow() ? 'bg-amber-400' : 'bg-blue-400') }} {{ $appt->date->isToday() || $appt->date->isTomorrow() ? 'animate-pulse' : '' }}"></div>
                                    @if($appointmentDateTime->isPast())
                                        Overdue
                                    @else
                                        {{ $timeUntil }}
                                    @endif
                                </div>

                                <!-- Notes -->
                                @if($appt->notes)
                                    <div class="mt-4 p-3 bg-gradient-to-r from-zinc-50 to-zinc-100 dark:from-zinc-800/50 dark:to-zinc-700/50 rounded-xl border border-zinc-200 dark:border-zinc-700">
                                        <div class="flex items-start gap-2">
                                            <svg class="w-4 h-4 text-zinc-400 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                                            </svg>
                                            <div>
                                                <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wide">Notes:</span>
                                                <p class="text-sm text-zinc-600 dark:text-zinc-300 mt-1">{{ $appt->notes }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex flex-col sm:flex-row lg:flex-col gap-2 sm:self-start lg:self-center">
                            @if($appt->status !== 'cancelled')
                                <button 
                                    wire:click="confirmCancel({{ $appt->id }})"
                                    class="px-4 py-2 text-sm font-medium text-red-600 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors click-feedback flex items-center justify-center gap-2"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    Cancel
                                </button>
                            @endif
                            
                            <button class="px-4 py-2 text-sm font-medium text-primary-600 bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800 rounded-lg hover:bg-primary-100 dark:hover:bg-primary-900/30 transition-colors click-feedback flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                Reschedule
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <x-enhanced-empty-state
                    icon="calendar"
                    title="No upcoming appointments"
                    description="You don't have any fittings or consultations scheduled. Book your first appointment!"
                    :actions="[
                        ['type' => 'primary', 'label' => 'Book Appointment', 'onclick' => 'window.location.href=\"' . route('customer.book') . '\"'],
                        ['type' => 'secondary', 'label' => 'View Catalog', 'onclick' => 'window.location.href=\"' . route('customer.catalog') . '\"']
                    ]"
                />
            @endforelse
        </div>
    @endif

    <!-- Past Appointments -->
    @if($activeTab === 'past')
        <div class="space-y-3">
            @forelse($pastAppointments as $appt)
                <div class="tc-card !p-4 hover-lift animate-fade-in-up opacity-75 hover:opacity-100 transition-all duration-300" style="--stagger-index: {{ $loop->index }}">
                    <div class="flex items-center gap-4">
                        <!-- Compact Date -->
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-zinc-100 to-zinc-200 dark:from-zinc-700 dark:to-zinc-600 flex flex-col items-center justify-center border border-zinc-200 dark:border-zinc-600 shrink-0">
                            <span class="text-xs font-bold text-zinc-600 dark:text-zinc-400 uppercase">{{ $appt->date->format('M') }}</span>
                            <span class="text-sm font-bold text-zinc-700 dark:text-zinc-300 leading-none">{{ $appt->date->format('d') }}</span>
                        </div>

                        <!-- Appointment Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <h4 class="font-semibold text-zinc-900 dark:text-white truncate">
                                    {{ $appt->shop?->name ?? 'Unknown Shop' }}
                                </h4>
                                <span class="tc-badge tc-badge-{{ $appt->status }} ml-2 shrink-0">
                                    {{ ucfirst($appt->status) }}
                                </span>
                            </div>
                            <div class="flex items-center gap-4 mt-1 text-sm text-zinc-500">
                                <span>{{ $appt->date->format('M d, Y') }}</span>
                                <span>{{ \Carbon\Carbon::parse($appt->time)->format('g:i A') }}</span>
                                <span>{{ $appt->type_label }}</span>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="flex items-center gap-2">
                            @if($appt->status === 'completed')
                                <button class="p-2 text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 rounded-lg transition-colors click-feedback" title="Book Again">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/>
                                    </svg>
                                </button>
                            @endif
                            <button class="p-2 text-zinc-400 hover:text-zinc-600 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg transition-colors click-feedback" title="View Details">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <x-enhanced-empty-state
                    icon="calendar"
                    title="No appointment history"
                    description="You haven't had any appointments yet. Your completed and cancelled appointments will appear here."
                    :actions="[
                        ['type' => 'primary', 'label' => 'Book First Appointment', 'onclick' => 'window.location.href=\"' . route('customer.book') . '\"']
                    ]"
                />
            @endforelse
        </div>
    @endif

    <!-- Floating Action Button -->
    <x-floating-action-button 
        icon="plus" 
        tooltip="Book New Appointment"
        onclick="window.location.href='{{ route('customer.book') }}'"
    />

    <!-- Cancel Confirmation Modal -->
    @if($showCancelModal && $appointmentToCancel)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
            <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl w-full max-w-md" @click.stop>
                <div class="p-6">
                    <!-- Header -->
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Cancel Appointment</h3>
                            <p class="text-sm text-zinc-500">This action cannot be undone</p>
                        </div>
                    </div>

                    <!-- Appointment Details -->
                    <div class="bg-zinc-50 dark:bg-zinc-800/50 rounded-xl p-4 mb-6">
                        <p class="font-medium text-zinc-900 dark:text-white">{{ $appointmentToCancel->shop?->name }}</p>
                        <p class="text-sm text-zinc-500 mt-1">
                            {{ $appointmentToCancel->date->format('l, F j, Y') }} at {{ \Carbon\Carbon::parse($appointmentToCancel->time)->format('g:i A') }}
                        </p>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-3">
                        <button 
                            wire:click="closeCancelModal"
                            class="flex-1 px-4 py-3 text-sm font-medium text-zinc-600 bg-zinc-100 dark:bg-zinc-800 dark:text-zinc-300 rounded-xl hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors click-feedback"
                        >
                            Keep Appointment
                        </button>
                        <button 
                            wire:click="cancelAppointment({{ $appointmentToCancel->id }})"
                            class="flex-1 px-4 py-3 text-sm font-medium text-white bg-red-600 rounded-xl hover:bg-red-700 transition-colors click-feedback"
                        >
                            Yes, Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
