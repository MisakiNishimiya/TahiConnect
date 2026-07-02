<?php

use App\Models\Appointment;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
    public string $statusFilter = '';
    public string $search = '';

    public function with(): array
    {
        $query = Appointment::with(['user', 'staff'])->latest('date');
        if ($this->statusFilter) $query->where('status', $this->statusFilter);
        if ($this->search) $query->whereHas('user', fn($q) => $q->where('name', 'like', "%{$this->search}%"));
        return [
            'appointments' => $query->get(),
            'counts' => [
                '' => Appointment::count(),
                'pending' => Appointment::where('status','pending')->count(),
                'confirmed' => Appointment::where('status','confirmed')->count(),
                'completed' => Appointment::where('status','completed')->count(),
                'cancelled' => Appointment::where('status','cancelled')->count(),
            ],
            'todayCount' => Appointment::whereDate('date', today())->count(),
        ];
    }
}; ?>

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-primary-600 dark:text-primary-400" style="font-family: 'Poppins';">Appointment Management</h1>
            <p class="text-zinc-500 mt-1">Manage all customer appointments and staff assignments.</p>
        </div>
        <div class="flex items-center gap-2 px-4 py-2 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl text-sm">
            <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
            <span class="font-medium text-emerald-700 dark:text-emerald-300">{{ $todayCount }} today</span>
        </div>
    </div>

    <!-- Status Filter Tabs -->
    <div class="tc-card !p-0 overflow-hidden">
        <div class="flex overflow-x-auto custom-scrollbar border-b border-zinc-100 dark:border-zinc-700">
            @foreach(['' => 'All', 'pending' => 'Pending', 'confirmed' => 'Confirmed', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $key => $label)
                <button wire:click="$set('statusFilter', '{{ $key }}')"
                    class="flex-shrink-0 px-6 py-4 text-sm font-medium transition-all duration-300 relative whitespace-nowrap {{ $statusFilter === $key ? 'text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20' : 'text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800/50' }}">
                    <span class="flex items-center gap-2">
                        {{ $label }}
                        @if(($counts[$key] ?? 0) > 0)
                            <span class="px-2 py-0.5 text-xs rounded-full {{ $statusFilter === $key ? 'bg-primary-200 dark:bg-primary-800 text-primary-800 dark:text-primary-200' : 'bg-zinc-100 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-400' }}">{{ $counts[$key] }}</span>
                        @endif
                    </span>
                    @if($statusFilter === $key)<div class="absolute bottom-0 left-0 right-0 h-0.5 bg-primary-500"></div>@endif
                </button>
            @endforeach
        </div>
    </div>

    <!-- Search -->
    <div class="relative">
        <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <input wire:model.live.debounce.300ms="search" placeholder="Search by customer name..."
            class="w-full pl-11 pr-4 py-3 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-sm focus:ring-2 focus:ring-primary-500 transition-colors" />
    </div>

    <!-- Table -->
    <div class="tc-card !p-0 overflow-hidden">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-sm">
                <thead><tr class="border-b border-zinc-100 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50">
                    <th class="text-left py-4 px-4 font-semibold text-zinc-500">Customer</th>
                    <th class="text-left py-4 px-4 font-semibold text-zinc-500">Date & Time</th>
                    <th class="text-left py-4 px-4 font-semibold text-zinc-500 hidden md:table-cell">Type</th>
                    <th class="text-left py-4 px-4 font-semibold text-zinc-500 hidden lg:table-cell">Assigned Staff</th>
                    <th class="text-left py-4 px-4 font-semibold text-zinc-500">Status</th>
                </tr></thead>
                <tbody class="divide-y divide-zinc-50 dark:divide-zinc-800">
                    @forelse($appointments as $appt)
                        <tr class="hover:bg-cream-50 dark:hover:bg-zinc-700/30 transition-colors duration-200 group">
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-primary-500 to-secondary-500 flex items-center justify-center text-xs font-bold text-white group-hover:scale-110 transition-transform duration-300">
                                        {{ strtoupper(substr($appt->user?->name ?? 'U', 0, 1)) }}
                                    </div>
                                    <span class="font-semibold text-zinc-900 dark:text-white">{{ $appt->user?->name }}</span>
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <p class="font-medium text-zinc-900 dark:text-white">{{ $appt->date->format('M d, Y') }}</p>
                                <p class="text-xs text-zinc-500">{{ \Carbon\Carbon::parse($appt->time)->format('g:i A') }}</p>
                            </td>
                            <td class="py-4 px-4 hidden md:table-cell">
                                <span class="px-2.5 py-1 text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-full">
                                    {{ ucwords(str_replace('_', ' ', $appt->type)) }}
                                </span>
                            </td>
                            <td class="py-4 px-4 hidden lg:table-cell">
                                @if($appt->staff)
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-secondary-100 dark:bg-secondary-900/30 flex items-center justify-center text-[11px] font-bold text-secondary-700 dark:text-secondary-300">{{ strtoupper(substr($appt->staff->name, 0, 1)) }}</div>
                                        <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $appt->staff->name }}</span>
                                    </div>
                                @else
                                    <span class="text-xs text-zinc-400 bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded-full">Unassigned</span>
                                @endif
                            </td>
                            <td class="py-4 px-4"><span class="tc-badge tc-badge-{{ $appt->status }}">{{ ucfirst($appt->status) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-16 text-center">
                            <svg class="w-16 h-16 mx-auto text-zinc-200 dark:text-zinc-700 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25"/></svg>
                            <p class="text-zinc-400 font-medium">No appointments found</p>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
