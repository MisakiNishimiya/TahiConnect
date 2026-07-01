<?php

use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
    public string $statusFilter = '';

    public function updateStatus(int $orderId, string $newStatus): void
    {
        $order = Order::where('id', $orderId)->where('staff_id', auth()->id())->first();
        if ($order) {
            $order->update(['status' => $newStatus]);
            \App\Models\OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => $newStatus,
                'changed_by' => auth()->id(),
                'notes' => 'Status updated by staff',
                'created_at' => now(),
            ]);
            session()->flash('message', "Order {$order->tracking_number} updated to " . ucwords(str_replace('_', ' ', $newStatus)));
        }
    }

    public function with(): array
    {
        $query = Order::where('staff_id', auth()->id())->with(['user', 'garmentType'])->latest();
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }
        return [
            'orders' => $query->get(),
            'allStatuses' => ['pending','measurements_verified','in_production','fitting_scheduled','final_adjustment','ready_for_pickup','completed','released'],
        ];
    }
}; ?>

<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">My Assigned Orders</h1>
        <p class="text-zinc-500 mt-1">Track and update order statuses for your assigned work.</p>
    </div>

    @if (session()->has('message'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm">{{ session('message') }}</div>
    @endif

    <div class="tc-card !p-4 flex flex-wrap gap-2">
        <button wire:click="$set('statusFilter', '')" class="px-4 py-2 text-sm rounded-lg font-medium transition-all {{ !$statusFilter ? 'bg-primary-500 text-white' : 'bg-white text-zinc-600 border border-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:border-zinc-700' }}">All</button>
        @foreach(['in_production' => 'Production', 'fitting_scheduled' => 'Fitting', 'final_adjustment' => 'Adjustment'] as $key => $label)
            <button wire:click="$set('statusFilter', '{{ $key }}')" class="px-4 py-2 text-sm rounded-lg font-medium transition-all {{ $statusFilter === $key ? 'bg-primary-500 text-white' : 'bg-white text-zinc-600 border border-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:border-zinc-700' }}">{{ $label }}</button>
        @endforeach
    </div>

    <div class="space-y-4">
        @forelse($orders as $order)
            <div class="tc-card animate-fade-in-up">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
                            <svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                        </div>
                        <div>
                            <p class="font-mono text-sm font-semibold text-primary-600 dark:text-primary-400">{{ $order->tracking_number }}</p>
                            <p class="text-sm text-zinc-900 dark:text-white font-medium">{{ $order->garmentType?->name }}</p>
                            <p class="text-xs text-zinc-500">Customer: {{ $order->user?->name }} · Qty: {{ $order->quantity }}</p>
                        </div>
                    </div>
                    <div class="flex flex-col items-end gap-2">
                        <span class="tc-badge tc-badge-{{ $order->status }}">{{ ucwords(str_replace('_', ' ', $order->status)) }}</span>
                        <p class="text-sm font-bold" style="font-family: 'Poppins';">₱{{ number_format($order->total_amount, 0) }}</p>
                    </div>
                </div>

                @if($order->special_instructions)
                    <div class="mt-3 p-3 rounded-lg bg-amber-50 dark:bg-amber-900/10 text-sm text-amber-700 dark:text-amber-300">
                        <span class="font-semibold">Note:</span> {{ $order->special_instructions }}
                    </div>
                @endif

                <!-- Mini progress + status update -->
                <div class="flex gap-1 mt-4 mb-3">
                    @for($i = 0; $i < 7; $i++)
                        <div class="h-1.5 flex-1 rounded-full {{ $i <= $order->status_index ? 'bg-primary-500' : 'bg-zinc-200 dark:bg-zinc-700' }}"></div>
                    @endfor
                </div>

                @if(!in_array($order->status, ['completed', 'released']))
                    <div class="flex flex-wrap gap-2">
                        @php $nextIdx = $order->status_index + 1; @endphp
                        @if($nextIdx < count($allStatuses))
                            <flux:button wire:click="updateStatus({{ $order->id }}, '{{ $allStatuses[$nextIdx] }}')" variant="primary" size="sm" class="!bg-primary-500 hover:!bg-primary-600">
                                Advance → {{ ucwords(str_replace('_', ' ', $allStatuses[$nextIdx])) }}
                            </flux:button>
                        @endif
                    </div>
                @endif
            </div>
        @empty
            <div class="tc-card text-center py-12">
                <svg class="w-16 h-16 mx-auto text-zinc-200 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                <p class="text-zinc-400">No orders match your filter.</p>
            </div>
        @endforelse
    </div>
</div>
