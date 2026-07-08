<?php

use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
    public string $activeTab    = 'available';   // 'available' | 'mine'
    public string $statusFilter = '';
    public bool   $showDetailModal = false;
    public ?Order $selectedOrder   = null;

    // ── Take Order (UI-only — backend will assign staff_id) ───────────────────
    public function takeOrder(int $orderId): void
    {
        // UI-only placeholder — backend will do: Order::findOrFail($orderId)->update(['staff_id' => auth()->id()])
        session()->flash('taken', 'Order taken! It now appears in your Assigned Orders.');
        $this->activeTab = 'mine';
    }

    // ── Advance order status ──────────────────────────────────────────────────
    public function updateStatus(int $orderId, string $newStatus): void
    {
        $order = Order::where('id', $orderId)->where('staff_id', auth()->id())->first();
        if ($order) {
            $order->update(['status' => $newStatus]);
            \App\Models\OrderStatusHistory::create([
                'order_id'   => $order->id,
                'status'     => $newStatus,
                'changed_by' => auth()->id(),
                'notes'      => 'Status updated by staff',
                'created_at' => now(),
            ]);
            if ($this->selectedOrder?->id === $orderId) {
                $this->selectedOrder = $order->fresh();
            }
            session()->flash('message', "Order {$order->tracking_number} → " . ucwords(str_replace('_', ' ', $newStatus)));
        }
    }

    public function openDetail(int $orderId): void
    {
        $this->selectedOrder = Order::where('id', $orderId)
            ->where('staff_id', auth()->id())
            ->with(['user', 'garmentType'])
            ->first();
        $this->showDetailModal = true;
    }

    public function with(): array
    {
        $allStatuses = ['pending','measurements_verified','in_production','fitting_scheduled','final_adjustment','ready_for_pickup','completed','released'];

        // Available = unassigned orders (no staff yet)
        $availableOrders = Order::whereNull('staff_id')
            ->with(['user','garmentType','preMadeProduct'])
            ->latest()
            ->get();

        // Mine = orders assigned to this staff
        $mineQuery = Order::where('staff_id', auth()->id())
            ->with(['user','garmentType','preMadeProduct'])
            ->latest();
        if ($this->statusFilter) {
            $mineQuery->where('status', $this->statusFilter);
        }

        return [
            'availableOrders' => $availableOrders,
            'availableCount'  => $availableOrders->count(),
            'myOrders'        => $mineQuery->get(),
            'allStatuses'     => $allStatuses,
            'statusCounts'    => [
                'all'               => Order::where('staff_id', auth()->id())->count(),
                'in_production'     => Order::where('staff_id', auth()->id())->where('status', 'in_production')->count(),
                'fitting_scheduled' => Order::where('staff_id', auth()->id())->where('status', 'fitting_scheduled')->count(),
                'final_adjustment'  => Order::where('staff_id', auth()->id())->where('status', 'final_adjustment')->count(),
            ],
        ];
    }
}; ?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-primary-600" style="font-family: 'Poppins';">Orders</h1>
            <p class="text-zinc-500 mt-1">Browse available orders or manage your assigned work.</p>
        </div>
        @if($availableCount > 0)
            <div class="flex items-center gap-2 px-3 py-1.5 bg-amber-50 border border-amber-200 rounded-xl">
                <div class="w-2 h-2 bg-amber-500 rounded-full animate-pulse"></div>
                <span class="text-xs font-semibold text-amber-700">{{ $availableCount }} {{ Str::plural('order', $availableCount) }} waiting for staff</span>
            </div>
        @endif
    </div>

    @if(session()->has('message'))
        <x-notification-toast type="success" title="Status Updated!" message="{{ session('message') }}" :dismissible="true" />
    @endif
    @if(session()->has('taken'))
        <x-notification-toast type="success" title="Order Taken!" message="{{ session('taken') }}" :dismissible="true" />
    @endif

    <!-- Main Tabs: Available vs Mine -->
    <div class="tc-card !p-0 overflow-hidden">
        <div class="flex border-b border-zinc-100">
            <button wire:click="$set('activeTab','available')"
                class="flex-1 px-6 py-4 text-sm font-medium transition-all relative {{ $activeTab === 'available' ? 'text-primary-600 bg-primary-50' : 'text-zinc-500 hover:text-zinc-700 hover:bg-zinc-50' }}">
                <span class="flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z"/></svg>
                    Available Orders
                    @if($availableCount > 0)
                        <span class="px-2 py-0.5 text-xs rounded-full font-bold {{ $activeTab === 'available' ? 'bg-amber-200 text-amber-800' : 'bg-amber-100 text-amber-700' }}">{{ $availableCount }}</span>
                    @endif
                </span>
                @if($activeTab === 'available')<div class="absolute bottom-0 left-0 right-0 h-0.5 bg-primary-500"></div>@endif
            </button>
            <button wire:click="$set('activeTab','mine')"
                class="flex-1 px-6 py-4 text-sm font-medium transition-all relative {{ $activeTab === 'mine' ? 'text-primary-600 bg-primary-50' : 'text-zinc-500 hover:text-zinc-700 hover:bg-zinc-50' }}">
                <span class="flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z"/></svg>
                    My Assigned
                    @if($statusCounts['all'] > 0)
                        <span class="px-2 py-0.5 text-xs rounded-full {{ $activeTab === 'mine' ? 'bg-primary-200 text-primary-800' : 'bg-zinc-100 text-zinc-600' }}">{{ $statusCounts['all'] }}</span>
                    @endif
                </span>
                @if($activeTab === 'mine')<div class="absolute bottom-0 left-0 right-0 h-0.5 bg-primary-500"></div>@endif
            </button>
        </div>
    </div>

    {{-- ── AVAILABLE ORDERS TAB ──────────────────────────────────────────── --}}
    @if($activeTab === 'available')
        <div class="p-4 bg-blue-50 border border-blue-100 rounded-xl flex items-start gap-3">
            <svg class="w-5 h-5 text-blue-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-sm text-blue-700">These are all <strong>unassigned</strong> orders. Click <strong>Take Order</strong> on any order to assign it to yourself — it will move to your <strong>My Assigned</strong> tab.</p>
        </div>

        <div class="space-y-4">
            @forelse($availableOrders as $order)
                <div class="tc-card border border-zinc-100 hover:border-primary-200 hover:shadow-md transition-all duration-200 group">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                <svg class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z"/></svg>
                            </div>
                            <div>
                                <div class="flex items-center gap-2 mb-0.5">
                                    <p class="font-mono text-sm font-bold text-primary-600">{{ $order->tracking_number }}</p>
                                    <span class="px-2 py-0.5 text-[10px] font-bold bg-amber-100 text-amber-700 rounded-full uppercase tracking-wide">Unassigned</span>
                                </div>
                                <p class="text-sm font-semibold text-zinc-900">{{ $order->garmentType?->name ?? $order->preMadeProduct?->name ?? 'Custom Order' }}</p>
                                <p class="text-xs text-zinc-500">Customer: {{ $order->user?->name }} · Qty: {{ $order->quantity }}</p>
                                <p class="text-xs text-zinc-400 mt-0.5">Placed {{ $order->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        <div class="flex flex-col items-end gap-2 shrink-0">
                            <p class="text-lg font-bold text-zinc-900">₱{{ number_format($order->total_amount, 0) }}</p>
                            <span class="tc-badge tc-badge-{{ $order->status }}">{{ ucwords(str_replace('_', ' ', $order->status)) }}</span>
                        </div>
                    </div>

                    @if($order->special_instructions)
                        <div class="mt-3 p-3 rounded-xl bg-amber-50 border border-amber-200 text-sm text-amber-700">
                            <span class="font-semibold">📝 Note:</span> {{ $order->special_instructions }}
                        </div>
                    @endif

                    <div class="mt-4 pt-4 border-t border-zinc-100 flex items-center justify-between">
                        <p class="text-xs text-zinc-400">Created {{ $order->created_at->format('M d, Y') }}</p>
                        <button wire:click="takeOrder({{ $order->id }})"
                            wire:confirm="Take this order? It will be assigned to you and appear in My Assigned Orders."
                            class="flex items-center gap-2 px-5 py-2 text-sm font-semibold bg-gradient-to-r from-primary-500 to-primary-600 text-white rounded-xl hover:from-primary-600 hover:to-primary-700 transition-all shadow-sm hover:shadow-md">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008z"/></svg>
                            Take Order
                        </button>
                    </div>
                </div>
            @empty
                <div class="tc-card py-16 text-center">
                    <div class="w-14 h-14 mx-auto mb-4 rounded-2xl bg-zinc-100 flex items-center justify-center">
                        <svg class="w-7 h-7 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <p class="text-sm font-semibold text-zinc-600">No available orders right now</p>
                    <p class="text-xs text-zinc-400 mt-1">New incoming orders will appear here for you to take.</p>
                </div>
            @endforelse
        </div>
    @endif

    {{-- ── MY ASSIGNED ORDERS TAB ────────────────────────────────────────── --}}
    @if($activeTab === 'mine')
        <!-- Status sub-filters -->
        <div class="tc-card !p-0 overflow-hidden">
            <div class="flex overflow-x-auto border-b border-zinc-100">
                @php
                    $filters = [
                        ['key' => '',                  'label' => 'All',        'count' => $statusCounts['all']],
                        ['key' => 'in_production',     'label' => 'In Prod',    'count' => $statusCounts['in_production']],
                        ['key' => 'fitting_scheduled', 'label' => 'Fitting',    'count' => $statusCounts['fitting_scheduled']],
                        ['key' => 'final_adjustment',  'label' => 'Adjustment', 'count' => $statusCounts['final_adjustment']],
                    ];
                @endphp
                @foreach($filters as $f)
                    <button wire:click="$set('statusFilter','{{ $f['key'] }}')"
                        class="flex-shrink-0 px-5 py-3.5 text-sm font-medium transition-all relative whitespace-nowrap {{ $statusFilter === $f['key'] ? 'text-primary-600 bg-primary-50' : 'text-zinc-500 hover:text-zinc-700 hover:bg-zinc-50' }}">
                        <span class="flex items-center gap-2">
                            {{ $f['label'] }}
                            @if($f['count'] > 0)
                                <span class="px-1.5 py-0.5 text-xs rounded-full {{ $statusFilter === $f['key'] ? 'bg-primary-200 text-primary-800' : 'bg-zinc-100 text-zinc-600' }}">{{ $f['count'] }}</span>
                            @endif
                        </span>
                        @if($statusFilter === $f['key'])<div class="absolute bottom-0 left-0 right-0 h-0.5 bg-primary-500"></div>@endif
                    </button>
                @endforeach
            </div>
        </div>

        <div class="space-y-4">
            @forelse($myOrders as $order)
                <div class="tc-card hover-lift group cursor-pointer" wire:click="openDetail({{ $order->id }})">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl bg-primary-100 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                <svg class="w-6 h-6 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z"/></svg>
                            </div>
                            <div>
                                <p class="font-mono text-sm font-bold text-primary-600">{{ $order->tracking_number }}</p>
                                <p class="text-sm font-semibold text-zinc-900">{{ $order->garmentType?->name ?? $order->preMadeProduct?->name }}</p>
                                <p class="text-xs text-zinc-500">{{ $order->user?->name }} · Qty: {{ $order->quantity }}</p>
                            </div>
                        </div>
                        <div class="flex flex-col items-end gap-2 shrink-0">
                            <p class="text-lg font-bold text-zinc-900">₱{{ number_format($order->total_amount, 0) }}</p>
                            <span class="tc-badge tc-badge-{{ $order->status }}">{{ ucwords(str_replace('_', ' ', $order->status)) }}</span>
                        </div>
                    </div>

                    @if($order->special_instructions)
                        <div class="mt-3 p-3 rounded-xl bg-amber-50 border border-amber-200 text-sm text-amber-700">
                            <span class="font-semibold">📝 Note:</span> {{ $order->special_instructions }}
                        </div>
                    @endif

                    <!-- Progress -->
                    @php
                        $totalSteps = $order->preMadeProduct ? 5 : 8;
                        $idx = $order->status_index ?? 0;
                    @endphp
                    <div class="flex gap-1.5 mt-4 mb-3">
                        @for($i = 0; $i < $totalSteps; $i++)
                            <div class="h-1.5 flex-1 rounded-full {{ $i <= $idx ? 'bg-primary-500' : 'bg-zinc-200' }}"></div>
                        @endfor
                    </div>

                    @if(!in_array($order->status, ['completed','released']))
                        <div class="flex flex-wrap gap-2 mt-2" wire:click.stop>
                            @php $nextIdx = ($order->status_index ?? 0) + 1; @endphp
                            @if($nextIdx < count($allStatuses))
                                <button wire:click="updateStatus({{ $order->id }}, '{{ $allStatuses[$nextIdx] }}')"
                                    class="px-4 py-2 text-sm font-medium bg-gradient-to-r from-primary-500 to-primary-600 text-white rounded-xl hover:from-primary-600 hover:to-primary-700 transition-all shadow-sm flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                    Advance to {{ ucwords(str_replace('_', ' ', $allStatuses[$nextIdx])) }}
                                </button>
                            @endif
                        </div>
                    @else
                        <div class="flex items-center gap-2 mt-2">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="text-sm font-medium text-emerald-600">Order completed</span>
                        </div>
                    @endif
                </div>
            @empty
                <div class="tc-card py-16 text-center">
                    <div class="w-14 h-14 mx-auto mb-4 rounded-2xl bg-zinc-100 flex items-center justify-center">
                        <svg class="w-7 h-7 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5"/></svg>
                    </div>
                    <p class="text-sm font-semibold text-zinc-600">No assigned orders yet</p>
                    <p class="text-xs text-zinc-400 mt-1">Go to Available Orders and take an order to get started.</p>
                    <button wire:click="$set('activeTab','available')" class="mt-4 px-5 py-2 text-sm font-semibold bg-primary-500 text-white rounded-xl hover:bg-primary-600 transition-colors">
                        Browse Available Orders
                    </button>
                </div>
            @endforelse
        </div>
    @endif

    <!-- Order Detail Modal -->
    @if($showDetailModal && $selectedOrder)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg" @click.stop>
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-semibold text-zinc-900" style="font-family: 'Poppins';">Order Details</h3>
                        <button wire:click="$set('showDetailModal', false)" class="p-2 text-zinc-400 hover:bg-zinc-100 rounded-full transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="p-4 bg-zinc-50 rounded-xl">
                                <p class="text-xs text-zinc-500 mb-1">Tracking #</p>
                                <p class="font-mono font-semibold text-primary-600">{{ $selectedOrder->tracking_number }}</p>
                            </div>
                            <div class="p-4 bg-zinc-50 rounded-xl">
                                <p class="text-xs text-zinc-500 mb-1">Status</p>
                                <span class="tc-badge tc-badge-{{ $selectedOrder->status }}">{{ ucwords(str_replace('_', ' ', $selectedOrder->status)) }}</span>
                            </div>
                            <div class="p-4 bg-zinc-50 rounded-xl">
                                <p class="text-xs text-zinc-500 mb-1">Customer</p>
                                <p class="font-medium text-zinc-900">{{ $selectedOrder->user?->name }}</p>
                            </div>
                            <div class="p-4 bg-zinc-50 rounded-xl">
                                <p class="text-xs text-zinc-500 mb-1">Amount</p>
                                <p class="text-lg font-bold text-zinc-900">₱{{ number_format($selectedOrder->total_amount, 2) }}</p>
                            </div>
                        </div>
                        @if($selectedOrder->special_instructions)
                            <div class="p-4 bg-amber-50 rounded-xl border border-amber-200">
                                <p class="text-xs text-amber-600 font-medium mb-1">Special Instructions</p>
                                <p class="text-sm text-amber-800">{{ $selectedOrder->special_instructions }}</p>
                            </div>
                        @endif
                    </div>
                    <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-zinc-200">
                        <button wire:click="$set('showDetailModal', false)" class="px-5 py-2.5 text-sm font-medium text-zinc-600 bg-zinc-100 rounded-xl hover:bg-zinc-200 transition-colors">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
