<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Order;

new #[Layout('components.layouts.app')] class extends Component {
    use WithPagination;
    public $search = '';
    public $status = '';

    public function with(): array
    {
        $orders = Order::with(['user', 'garmentType', 'staff'])
            ->when($this->search, fn($q) => $q->where('tracking_number', 'like', "%{$this->search}%")->orWhereHas('user', fn($q2) => $q2->where('name', 'like', "%{$this->search}%")))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->latest()->paginate(15);
        return ['orders' => $orders];
    }

    public function updatedSearch() { $this->resetPage(); }
    public function updatedStatus() { $this->resetPage(); }
}; ?>

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-primary-600 dark:text-primary-400" style="font-family: 'Poppins';">Shop Orders</h1>
            <p class="text-zinc-500 mt-1">Manage all customer orders for your shop</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="tc-card !p-4 flex flex-col sm:flex-row gap-4">
        <div class="flex-1 relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input wire:model.live.debounce.300ms="search" placeholder="Search by tracking # or customer..." class="w-full pl-10 pr-4 py-2.5 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-sm focus:ring-2 focus:ring-primary-500 transition-colors" />
        </div>
        <select wire:model.live="status" class="px-4 py-2.5 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-sm focus:ring-2 focus:ring-primary-500">
            <option value="">All Statuses</option>
            @foreach(['pending','measurements_verified','in_production','fitting_scheduled','final_adjustment','ready_for_pickup','completed','released'] as $s)
                <option value="{{ $s }}">{{ ucwords(str_replace('_',' ',$s)) }}</option>
            @endforeach
        </select>
    </div>

    <!-- Orders Table Card -->
    <div class="tc-card !p-0 overflow-hidden">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-100 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50">
                        <th class="text-left py-4 px-4 font-semibold text-zinc-600 dark:text-zinc-400">Tracking #</th>
                        <th class="text-left py-4 px-4 font-semibold text-zinc-600 dark:text-zinc-400">Customer</th>
                        <th class="text-left py-4 px-4 font-semibold text-zinc-600 dark:text-zinc-400 hidden md:table-cell">Garment</th>
                        <th class="text-left py-4 px-4 font-semibold text-zinc-600 dark:text-zinc-400 hidden lg:table-cell">Staff</th>
                        <th class="text-left py-4 px-4 font-semibold text-zinc-600 dark:text-zinc-400">Status</th>
                        <th class="text-right py-4 px-4 font-semibold text-zinc-600 dark:text-zinc-400">Amount</th>
                        <th class="text-left py-4 px-4 font-semibold text-zinc-600 dark:text-zinc-400 hidden xl:table-cell">Est. Completion</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-50 dark:divide-zinc-800">
                    @forelse($orders as $order)
                        <tr class="hover:bg-cream-50 dark:hover:bg-zinc-700/30 transition-colors duration-200 group">
                            <td class="py-4 px-4 font-mono text-xs font-semibold text-primary-600 dark:text-primary-400">{{ $order->tracking_number }}</td>
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-xs font-bold text-primary-700 dark:text-primary-300 shrink-0">
                                        {{ strtoupper(substr($order->user?->name ?? 'U', 0, 1)) }}
                                    </div>
                                    <span class="font-medium text-zinc-900 dark:text-white">{{ $order->user?->name }}</span>
                                </div>
                            </td>
                            <td class="py-4 px-4 text-zinc-600 dark:text-zinc-400 hidden md:table-cell">{{ $order->garmentType?->name ?? '-' }} <span class="text-zinc-400">×{{ $order->quantity }}</span></td>
                            <td class="py-4 px-4 hidden lg:table-cell">
                                @if($order->staff)
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-secondary-100 dark:bg-secondary-900/30 flex items-center justify-center text-[10px] font-bold text-secondary-700 dark:text-secondary-300">{{ strtoupper(substr($order->staff->name, 0, 1)) }}</div>
                                        <span class="text-zinc-600 dark:text-zinc-400 text-sm">{{ $order->staff->name }}</span>
                                    </div>
                                @else
                                    <span class="text-xs text-zinc-400 bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded-full">Unassigned</span>
                                @endif
                            </td>
                            <td class="py-4 px-4"><span class="tc-badge tc-badge-{{ $order->status }}">{{ ucwords(str_replace('_',' ',$order->status)) }}</span></td>
                            <td class="py-4 px-4 text-right font-bold text-zinc-900 dark:text-white">₱{{ number_format($order->total_amount, 2) }}</td>
                            <td class="py-4 px-4 text-zinc-500 text-sm hidden xl:table-cell">{{ $order->estimated_completion?->format('M d, Y') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-16 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <svg class="w-16 h-16 text-zinc-200 dark:text-zinc-700" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z"/></svg>
                                <p class="text-zinc-400 font-medium">No orders found</p>
                            </div>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
            <div class="px-4 py-4 border-t border-zinc-100 dark:border-zinc-700">{{ $orders->links() }}</div>
        @endif
    </div>
</div>
