<?php

use App\Models\Appointment;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('components.layouts.app')] class extends Component {
    use WithPagination;

    public string $search = '';
    public string $status = '';

    public function with(): array
    {
        return [
            'appointments' => Appointment::with(['user', 'staff'])
                ->when($this->search, fn($q) => $q->whereHas('user', fn($q2) => $q2->where('name', 'like', "%{$this->search}%")))
                ->when($this->status, fn($q) => $q->where('status', $this->status))
                ->orderBy('date', 'desc')->orderBy('time', 'desc')
                ->paginate(15),
            'counts' => [
                'pending'   => Appointment::where('status', 'pending')->count(),
                'confirmed' => Appointment::where('status', 'confirmed')->count(),
                'completed' => Appointment::where('status', 'completed')->count(),
                'cancelled' => Appointment::where('status', 'cancelled')->count(),
            ],
        ];
    }

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedStatus(): void { $this->resetPage(); }
}; ?>

<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-primary-600 dark:text-primary-400" style="font-family: 'Poppins';">Appointments</h1>
        <p class="text-zinc-500 mt-1">Manage all customer appointments and fitting schedules</p>
    </div>

    <!-- Summary -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach([
            ['Pending', $counts['pending'], 'bg-amber-100 dark:bg-amber-900/30', 'text-amber-700 dark:text-amber-300'],
            ['Confirmed', $counts['confirmed'], 'bg-blue-100 dark:bg-blue-900/30', 'text-blue-700 dark:text-blue-300'],
            ['Completed', $counts['completed'], 'bg-emerald-100 dark:bg-emerald-900/30', 'text-emerald-700 dark:text-emerald-300'],
            ['Cancelled', $counts['cancelled'], 'bg-red-100 dark:bg-red-900/30', 'text-red-700 dark:text-red-300'],
        ] as [$label, $count, $bg, $color])
        <div class="tc-card {{ $bg }} border-0">
            <p class="text-2xl font-bold {{ $color }}" style="font-family:'Poppins'">{{ $count }}</p>
            <p class="text-sm {{ $color }} font-medium mt-1">{{ $label }}</p>
        </div>
        @endforeach
    </div>

    <!-- Filters -->
    <div class="tc-card !p-4 flex flex-col sm:flex-row gap-4">
        <div class="flex-1 relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input wire:model.live.debounce.300ms="search" placeholder="Search by customer name..."
                class="w-full pl-10 pr-4 py-2.5 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-sm focus:ring-2 focus:ring-primary-500" />
        </div>
        <select wire:model.live="status" class="px-4 py-2.5 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-sm focus:ring-2 focus:ring-primary-500">
            <option value="">All Statuses</option>
            <option value="pending">Pending</option>
            <option value="confirmed">Confirmed</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
        </select>
    </div>

    <!-- Table -->
    <div class="tc-card !p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-100 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50">
                        <th class="text-left py-4 px-4 font-semibold text-zinc-500">Customer</th>
                        <th class="text-left py-4 px-4 font-semibold text-zinc-500">Date & Time</th>
                        <th class="text-left py-4 px-4 font-semibold text-zinc-500 hidden md:table-cell">Type</th>
                        <th class="text-left py-4 px-4 font-semibold text-zinc-500 hidden lg:table-cell">Staff</th>
                        <th class="text-left py-4 px-4 font-semibold text-zinc-500">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-50 dark:divide-zinc-800">
                    @forelse($appointments as $appt)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/30 transition-colors">
                            <td class="py-4 px-4 font-medium text-zinc-900 dark:text-white">{{ $appt->user?->name }}</td>
                            <td class="py-4 px-4 text-zinc-600 dark:text-zinc-400">
                                {{ $appt->date->format('M d, Y') }}<br>
                                <span class="text-xs text-zinc-400">{{ \Carbon\Carbon::parse($appt->time)->format('g:i A') }}</span>
                            </td>
                            <td class="py-4 px-4 text-zinc-500 hidden md:table-cell">{{ ucwords(str_replace('_', ' ', $appt->type)) }}</td>
                            <td class="py-4 px-4 text-zinc-500 hidden lg:table-cell">{{ $appt->staff?->name ?? '—' }}</td>
                            <td class="py-4 px-4"><span class="tc-badge tc-badge-{{ $appt->status }}">{{ ucfirst($appt->status) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-16 text-center text-zinc-400">No appointments found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($appointments->hasPages())
            <div class="px-4 py-4 border-t border-zinc-100 dark:border-zinc-700">{{ $appointments->links() }}</div>
        @endif
    </div>
</div>
