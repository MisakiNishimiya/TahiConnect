<?php

use App\Models\Appointment;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
    public string $activeTab = 'today';

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
            'counts' => [
                'today' => Appointment::where('staff_id', $staffId)->whereDate('date', today())->count(),
                'upcoming' => Appointment::where('staff_id', $staffId)->where('date', '>', today())->whereIn('status', ['pending','confirmed'])->count(),
                'past' => Appointment::where('staff_id', $staffId)->where(function($q) { $q->where('date', '<', today())->orWhere('status','completed'); })->count(),
            ],
        ];
    }
}; ?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-primary-600 dark:text-primary-400" style="font-family: 'Poppins';">My Appointments</h1>
            <p class="text-zinc-500 mt-1">Manage your scheduled fittings, consultations, and pickups.</p>
        </div>
        <div class="flex items-center gap-2 text-sm font-medium text-zinc-600 dark:text-zinc-400">
            <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
            Today: {{ now()->format('l, F d') }}
        </div>
    </div>

    @if (session()->has('message'))
        <x-notification-toast type="success" title="Updated!" message="{{ session('message') }}" :dismissible="true" />
    @endif

    <!-- Tabs -->
    <div class="tc-card !p-0 overflow-hidden">
        <div class="flex overflow-x-auto custom-scrollbar border-b border-zinc-100 dark:border-zinc-700">
            @foreach([['today','Today','M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5'],['upcoming','Upcoming','M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z'],['past','Past','M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z']] as [$key,$label,$icon])
                <button wire:click="$set('activeTab', '{{ $key }}')"
                    class="flex-shrink-0 px-6 py-4 text-sm font-medium transition-all duration-300 relative whitespace-nowrap {{ $activeTab === $key ? 'text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20' : 'text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800/50' }}">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                        {{ $label }}
                        @if(($counts[$key] ?? 0) > 0)
                            <span class="px-2 py-0.5 text-xs rounded-full {{ $activeTab === $key ? 'bg-primary-200 dark:bg-primary-800 text-primary-800 dark:text-primary-200' : 'bg-zinc-100 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-400' }}">{{ $counts[$key] }}</span>
                        @endif
                    </span>
                    @if($activeTab === $key)<div class="absolute bottom-0 left-0 right-0 h-0.5 bg-primary-500"></div>@endif
                </button>
            @endforeach
        </div>
    </div>

    <!-- Today's Appointments -->
    @if($activeTab === 'today')
        @if($todayAppointments->count())
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($todayAppointments as $appt)
                    <div class="tc-card hover-lift interactive-card group animate-fade-in-up border-l-4 border-l-primary-500" style="--stagger-index: {{ $loop->index }}">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center gap-4">
                                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary-100 to-primary-200 dark:from-primary-900/30 dark:to-primary-800/30 flex flex-col items-center justify-center border border-primary-200 dark:border-primary-800 group-hover:scale-105 transition-transform duration-300">
                                    <p class="text-xl font-bold text-primary-700 dark:text-primary-300 leading-none">{{ \Carbon\Carbon::parse($appt->time)->format('g:i') }}</p>
                                    <p class="text-xs text-primary-500 font-medium">{{ \Carbon\Carbon::parse($appt->time)->format('A') }}</p>
                                </div>
                                <div>
                                    <p class="font-semibold text-zinc-900 dark:text-white">{{ $appt->user?->name }}</p>
                                    <p class="text-sm text-zinc-500">{{ ucwords(str_replace('_', ' ', $appt->type)) }}</p>
                                    @if($appt->notes)
                                        <p class="text-xs text-zinc-400 mt-1 italic">{{ Str::limit($appt->notes, 60) }}</p>
                                    @endif
                                </div>
                            </div>
                            <span class="tc-badge tc-badge-{{ $appt->status }} shrink-0">{{ ucfirst($appt->status) }}</span>
                        </div>
                        <div class="flex gap-2 pt-4 border-t border-zinc-100 dark:border-zinc-700">
                            @if($appt->status === 'pending')
                                <button wire:click="confirmAppointment({{ $appt->id }})"
                                    class="flex-1 py-2 px-4 text-sm font-medium bg-gradient-to-r from-primary-500 to-primary-600 text-white rounded-xl hover:from-primary-600 hover:to-primary-700 transition-all duration-300 click-feedback">
                                    ✓ Confirm
                                </button>
                            @endif
                            @if(!in_array($appt->status, ['completed','cancelled']))
                                <button wire:click="markCompleted({{ $appt->id }})"
                                    class="flex-1 py-2 px-4 text-sm font-medium bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 rounded-xl hover:bg-emerald-100 dark:hover:bg-emerald-900/30 transition-colors click-feedback">
                                    Mark Complete
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <x-enhanced-empty-state icon="calendar" title="No appointments today"
                description="Your schedule is clear today. Enjoy your free time!"
                :actions="[]" />
        @endif
    @endif

    <!-- Upcoming -->
    @if($activeTab === 'upcoming')
        <div class="space-y-3">
            @forelse($upcomingAppointments as $appt)
                <div class="tc-card hover-lift interactive-card group animate-fade-in-up" style="--stagger-index: {{ $loop->index }}">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-100 to-blue-200 dark:from-blue-900/30 dark:to-blue-800/30 flex flex-col items-center justify-center border border-blue-200 dark:border-blue-800 group-hover:scale-105 transition-transform duration-300 shrink-0">
                            <p class="text-xs font-bold text-blue-700 dark:text-blue-300 uppercase">{{ $appt->date->format('M') }}</p>
                            <p class="text-xl font-bold text-blue-700 dark:text-blue-300 leading-none">{{ $appt->date->format('d') }}</p>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-zinc-900 dark:text-white">{{ $appt->user?->name }}</p>
                            <p class="text-sm text-zinc-500">{{ \Carbon\Carbon::parse($appt->time)->format('g:i A') }} · {{ ucwords(str_replace('_', ' ', $appt->type)) }}</p>
                            <p class="text-xs text-zinc-400 mt-1">{{ $appt->date->diffForHumans() }}</p>
                        </div>
                        <span class="tc-badge tc-badge-{{ $appt->status }} shrink-0">{{ ucfirst($appt->status) }}</span>
                    </div>
                </div>
            @empty
                <x-enhanced-empty-state icon="calendar" title="No upcoming appointments"
                    description="You have no upcoming appointments scheduled."
                    :actions="[]" />
            @endforelse
        </div>
    @endif

    <!-- Past -->
    @if($activeTab === 'past')
        <div class="space-y-3">
            @forelse($pastAppointments as $appt)
                <div class="tc-card opacity-70 hover:opacity-100 transition-opacity duration-300">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-zinc-100 dark:bg-zinc-700 flex items-center justify-center">
                                <svg class="w-5 h-5 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $appt->user?->name }}</p>
                                <p class="text-xs text-zinc-500">{{ $appt->date->format('M d, Y') }} · {{ ucwords(str_replace('_', ' ', $appt->type)) }}</p>
                            </div>
                        </div>
                        <span class="tc-badge tc-badge-{{ $appt->status }}">{{ ucfirst($appt->status) }}</span>
                    </div>
                </div>
            @empty
                <x-enhanced-empty-state icon="calendar" title="No past appointments" description="Your completed appointments will appear here." :actions="[]" />
            @endforelse
        </div>
    @endif
</div>
