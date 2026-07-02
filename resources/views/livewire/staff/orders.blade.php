<?php

use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
    public string $statusFilter = '';
    public bool $showDetailModal = false;
    public ?Order $selectedOrder = null;

    public function updateStatus(int $orderId, string $newStatus): void
    {
        $order = Order::where('id', $orderId)->where('staff_id', auth()->id())->first();
        if ($order) {
            $order->update(['status' => $newStatus]);
            \App\Models\OrderStatusHistory::create([
                'order_id' => $order->id, 'status' => $newStatus,
                'changed_by' => auth()->id(), 'notes' => 'Status updated by staff', 'created_at' => now(),
            ]);
            if ($this->selectedOrder?->id === $orderId) $this->selectedOrder = $order->fresh();
            session()->flash('message', "Order {$order->tracking_number} → " . ucwords(str_replace('_', ' ', $newStatus)));
        }
    }

    public function openDetail(int $orderId): void
    {
        $this->selectedOrder = Order::where('id', $orderId)->where('staff_id', auth()->id())->with(['user', 'garmentType'])->first();
        $this->showDetailModal = true;
    }

    public function with(): array
    {
        $query = Order::where('staff_id', auth()->id())->with(['user', 'garmentType'])->latest();
        if ($this->statusFilter) $query->where('status', $this->statusFilter);
        return [
            'orders' => $query->get(),
            'allStatuses' => ['pending','measurements_verified','in_production','fitting_scheduled','final_adjustment','ready_for_pickup','completed','released'],
            'statusCounts' => [
                'all' => Order::where('staff_id', auth()->id())->count(),
                'in_production' => Order::where('staff_id', auth()->id())->where('status', 'in_production')->count(),
                'fitting_scheduled' => Order::where('staff_id', auth()->id())->where('status', 'fitting_scheduled')->count(),
                'final_adjustment' => Order::where('staff_id', auth()->id())->where('status', 'final_adjustment')->count(),
            ],
        ];
    }
}; ?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-primary-600 dark:text-primary-400" style="font-family: 'Poppins';">My Assigned Orders</h1>
            <p class="text-zinc-500 mt-1">Track and update order statuses for your assigned work.</p>
        </div>
        <div class="flex items-center gap-2 text-sm text-zinc-500">
            <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
            <span>{{ $statusCounts['all'] }} total orders</span>
        </div>
    </div>

    @if (session()->has('message'))
        <x-notification-toast type="success" title="Status Updated!" message="{{ session('message') }}" :dismissible="true" />
    @endif

    <!-- Filter Tabs -->
    <div class="tc-card !p-0 overflow-hidden">
        <div class="flex overflow-x-auto custom-scrollbar border-b border-zinc-100 dark:border-zinc-700">
            @php
                $filters = [
                    ['key' => '', 'label' => 'All Orders', 'icon' => 'M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z'],
                    ['key' => 'in_production', 'label' => 'In Production', 'icon' => 'M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z'],
                    ['key' => 'fitting_scheduled', 'label' => 'Fitting', 'icon' => 'M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3'],
                    ['key' => 'final_adjustment', 'label' => 'Adjustment', 'icon' => 'M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 011.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.56.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.893.149c-.425.07-.765.383-.93.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 01-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.397.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 01-.12-1.45l.527-.737c.25-.35.273-.806.108-1.204-.165-.397-.505-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.107-1.204l-.527-.738a1.125 1.125 0 01.12-1.45l.773-.773a1.125 1.125 0 011.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894z'],
                ];
            @endphp
            @foreach($filters as $f)
                <button wire:click="$set('statusFilter', '{{ $f['key'] }}')"
                    class="flex-shrink-0 px-6 py-4 text-sm font-medium transition-all duration-300 relative whitespace-nowrap group {{ $statusFilter === $f['key'] ? 'text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20' : 'text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800/50' }}">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $f['icon'] }}"/></svg>
                        {{ $f['label'] }}
                        @php $countKey = $f['key'] ?: 'all'; @endphp
                        @if(($statusCounts[$countKey] ?? 0) > 0)
                            <span class="px-2 py-0.5 text-xs rounded-full {{ $statusFilter === $f['key'] ? 'bg-primary-200 dark:bg-primary-800 text-primary-800 dark:text-primary-200' : 'bg-zinc-100 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-400' }}">{{ $statusCounts[$countKey] }}</span>
                        @endif
                    </span>
                    @if($statusFilter === $f['key'])
                        <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-primary-500"></div>
                    @endif
                </button>
            @endforeach
        </div>
    </div>

    <!-- Orders List -->
    <div class="space-y-4">
        @forelse($orders as $order)
            <div class="tc-card hover-lift interactive-card group animate-fade-in-up cursor-pointer" style="--stagger-index: {{ $loop->index }}"
                 wire:click="openDetail({{ $order->id }})">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-primary-100 to-primary-200 dark:from-primary-900/30 dark:to-primary-800/30 flex items-center justify-center group-hover:scale-110 transition-transform duration-300 border border-primary-200 dark:border-primary-800">
                            <svg class="w-7 h-7 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z"/></svg>
                        </div>
                        <div>
                            <p class="font-mono text-sm font-semibold text-primary-600 dark:text-primary-400">{{ $order->tracking_number }}</p>
                            <p class="text-sm font-medium text-zinc-900 dark:text-white">{{ $order->garmentType?->name }}</p>
                            <p class="text-xs text-zinc-500">{{ $order->user?->name }} · Qty: {{ $order->quantity }}</p>
                        </div>
                    </div>
                    <div class="flex flex-col items-end gap-2">
                        <span class="tc-badge tc-badge-{{ $order->status }}">{{ ucwords(str_replace('_', ' ', $order->status)) }}</span>
                        <p class="text-lg font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">₱{{ number_format($order->total_amount, 0) }}</p>
                    </div>
                </div>

                @if($order->special_instructions)
                    <div class="mt-4 p-3 rounded-xl bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-800 text-sm text-amber-700 dark:text-amber-300">
                        <span class="font-semibold">📝 Note:</span> {{ $order->special_instructions }}
                    </div>
                @endif

                <!-- Progress bar -->
                <div class="flex gap-1.5 mt-4 mb-3">
                    @for($i = 0; $i < 7; $i++)
                        <div class="h-1.5 flex-1 rounded-full transition-all duration-500 {{ $i <= $order->status_index ? 'bg-primary-500' : 'bg-zinc-200 dark:bg-zinc-700' }}"></div>
                    @endfor
                </div>

                @if(!in_array($order->status, ['completed', 'released']))
                    <div class="flex flex-wrap gap-2 mt-2" wire:click.stop>
                        @php $nextIdx = $order->status_index + 1; @endphp
                        @if($nextIdx < count($allStatuses))
                            <button wire:click="updateStatus({{ $order->id }}, '{{ $allStatuses[$nextIdx] }}')"
                                class="px-4 py-2 text-sm font-medium bg-gradient-to-r from-primary-500 to-primary-600 text-white rounded-xl hover:from-primary-600 hover:to-primary-700 transition-all duration-300 shadow-sm hover:shadow-md click-feedback flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                Advance to {{ ucwords(str_replace('_', ' ', $allStatuses[$nextIdx])) }}
                            </button>
                        @endif
                    </div>
                @else
                    <div class="flex items-center gap-2 mt-2">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="text-sm font-medium text-emerald-600 dark:text-emerald-400">Order completed</span>
                    </div>
                @endif
            </div>
        @empty
            <x-enhanced-empty-state icon="orders" title="No orders found"
                description="No assigned orders match the current filter."
                :actions="[['type' => 'secondary', 'label' => 'Clear Filter', 'onclick' => '\$wire.set(\'statusFilter\', \'\')' ]]" />
        @endforelse
    </div>

    <!-- Order Detail Modal -->
    @if($showDetailModal && $selectedOrder)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
            <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl w-full max-w-lg" @click.stop>
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-semibold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">Order Details</h3>
                        <button wire:click="$set('showDetailModal', false)" class="p-2 text-zinc-400 hover:text-zinc-600 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-full transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="p-4 bg-zinc-50 dark:bg-zinc-800 rounded-xl">
                                <p class="text-xs text-zinc-500 mb-1">Tracking #</p>
                                <p class="font-mono font-semibold text-primary-600 dark:text-primary-400">{{ $selectedOrder->tracking_number }}</p>
                            </div>
                            <div class="p-4 bg-zinc-50 dark:bg-zinc-800 rounded-xl">
                                <p class="text-xs text-zinc-500 mb-1">Status</p>
                                <span class="tc-badge tc-badge-{{ $selectedOrder->status }}">{{ ucwords(str_replace('_', ' ', $selectedOrder->status)) }}</span>
                            </div>
                            <div class="p-4 bg-zinc-50 dark:bg-zinc-800 rounded-xl">
                                <p class="text-xs text-zinc-500 mb-1">Customer</p>
                                <p class="font-medium text-zinc-900 dark:text-white">{{ $selectedOrder->user?->name }}</p>
                            </div>
                            <div class="p-4 bg-zinc-50 dark:bg-zinc-800 rounded-xl">
                                <p class="text-xs text-zinc-500 mb-1">Amount</p>
                                <p class="text-lg font-bold text-zinc-900 dark:text-white">₱{{ number_format($selectedOrder->total_amount, 2) }}</p>
                            </div>
                        </div>
                        @if($selectedOrder->special_instructions)
                            <div class="p-4 bg-amber-50 dark:bg-amber-900/20 rounded-xl border border-amber-200 dark:border-amber-800">
                                <p class="text-xs text-amber-600 dark:text-amber-400 font-medium mb-1">Special Instructions</p>
                                <p class="text-sm text-amber-800 dark:text-amber-200">{{ $selectedOrder->special_instructions }}</p>
                            </div>
                        @endif
                    </div>
                    <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-zinc-200 dark:border-zinc-700">
                        <button wire:click="$set('showDetailModal', false)" class="px-5 py-2.5 text-sm font-medium text-zinc-600 bg-zinc-100 dark:bg-zinc-800 dark:text-zinc-300 rounded-xl hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors click-feedback">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
