<?php

use App\Models\Appointment;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
    public string $statusFilter = '';

    public function with(): array
    {
        $query = Appointment::with(['user', 'staff'])->latest('date');
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }
        return [
            'appointments' => $query->get(),
            'staffMembers' => User::where('role', 'tailor_staff')->get(),
        ];
    }
}; ?>

<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">Appointment Management</h1>
        <p class="text-zinc-500 mt-1">Manage all customer appointments and staff assignments.</p>
    </div>

    <div class="tc-card !p-4 flex flex-wrap gap-3">
        @foreach(['' => 'All', 'pending' => 'Pending', 'confirmed' => 'Confirmed', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $key => $label)
            <button wire:click="$set('statusFilter', '{{ $key }}')"
                class="px-4 py-2 text-sm rounded-lg font-medium transition-all {{ $statusFilter === $key ? 'bg-primary-500 text-white' : 'bg-white text-zinc-600 border border-zinc-200 hover:border-primary-300 dark:bg-zinc-800 dark:text-zinc-300 dark:border-zinc-700' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    <div class="tc-card !p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="border-b border-zinc-200 dark:border-zinc-700 bg-cream-50 dark:bg-zinc-800/50">
                    <th class="text-left py-3 px-4 font-medium text-zinc-500">Customer</th>
                    <th class="text-left py-3 px-4 font-medium text-zinc-500">Date</th>
                    <th class="text-left py-3 px-4 font-medium text-zinc-500 hidden sm:table-cell">Time</th>
                    <th class="text-left py-3 px-4 font-medium text-zinc-500 hidden md:table-cell">Type</th>
                    <th class="text-left py-3 px-4 font-medium text-zinc-500 hidden lg:table-cell">Staff</th>
                    <th class="text-left py-3 px-4 font-medium text-zinc-500">Status</th>
                </tr></thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse($appointments as $appt)
                        <tr class="hover:bg-cream-50 dark:hover:bg-zinc-700/30">
                            <td class="py-3 px-4 font-medium text-zinc-900 dark:text-white">{{ $appt->user?->name }}</td>
                            <td class="py-3 px-4 text-zinc-600 dark:text-zinc-400">{{ $appt->date->format('M d, Y') }}</td>
                            <td class="py-3 px-4 text-zinc-500 hidden sm:table-cell">{{ \Carbon\Carbon::parse($appt->time)->format('g:i A') }}</td>
                            <td class="py-3 px-4 hidden md:table-cell"><span class="tc-badge tc-badge-confirmed">{{ ucfirst($appt->type) }}</span></td>
                            <td class="py-3 px-4 text-zinc-500 hidden lg:table-cell">{{ $appt->staff?->name ?? '—' }}</td>
                            <td class="py-3 px-4"><span class="tc-badge tc-badge-{{ $appt->status }}">{{ ucfirst($appt->status) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-zinc-400">No appointments found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
