<?php

use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
    public string $search = '';
    public string $statusFilter = '';

    public function with(): array
    {
        $query = Order::with(['user', 'garmentType', 'staff'])->latest();
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('tracking_number', 'like', "%{$this->search}%")
                  ->orWhereHas('user', fn($q2) => $q2->where('name', 'like', "%{$this->search}%"));
            });
        }
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }
        return [
            'orders' => $query->get(),
        ];
    }
}; ?>

<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">Order Management</h1>
        <p class="text-zinc-500 mt-1">View and manage all customer orders.</p>
    </div>

    <!-- Filters -->
    <div class="tc-card !p-4 flex flex-wrap gap-4 items-center">
        <div class="flex-1 min-w-[200px]">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Search by tracking # or customer..." />
        </div>
        <select wire:model.live="statusFilter" class="rounded-lg border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700 text-sm">
            <option value="">All Statuses</option>
            @foreach(['pending','measurements_verified','in_production','fitting_scheduled','final_adjustment','ready_for_pickup','completed','released'] as $s)
                <option value="{{ $s }}">{{ ucwords(str_replace('_', ' ', $s)) }}</option>
            @endforeach
        </select>
    </div>

    <div class="tc-card !p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="border-b border-zinc-200 dark:border-zinc-700 bg-cream-50 dark:bg-zinc-800/50">
                    <th class="text-left py-3 px-4 font-medium text-zinc-500">Tracking #</th>
                    <th class="text-left py-3 px-4 font-medium text-zinc-500">Customer</th>
                    <th class="text-left py-3 px-4 font-medium text-zinc-500 hidden md:table-cell">Garment</th>
                    <th class="text-left py-3 px-4 font-medium text-zinc-500 hidden lg:table-cell">Staff</th>
                    <th class="text-left py-3 px-4 font-medium text-zinc-500">Status</th>
                    <th class="text-right py-3 px-4 font-medium text-zinc-500">Amount</th>
                </tr></thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse($orders as $order)
                        <tr class="hover:bg-cream-50 dark:hover:bg-zinc-700/30">
                            <td class="py-3 px-4 font-mono text-xs text-primary-600 dark:text-primary-400">{{ $order->tracking_number }}</td>
                            <td class="py-3 px-4 font-medium text-zinc-900 dark:text-white">{{ $order->user?->name }}</td>
                            <td class="py-3 px-4 text-zinc-500 hidden md:table-cell">{{ $order->garmentType?->name ?? '-' }}</td>
                            <td class="py-3 px-4 text-zinc-500 hidden lg:table-cell">{{ $order->staff?->name ?? '—' }}</td>
                            <td class="py-3 px-4"><span class="tc-badge tc-badge-{{ $order->status }}">{{ ucwords(str_replace('_', ' ', $order->status)) }}</span></td>
                            <td class="py-3 px-4 text-right font-semibold">₱{{ number_format($order->total_amount, 0) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-zinc-400">No orders found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
