<?php

use App\Models\Order;
use App\Models\OrderStatusHistory;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
    public string $trackingNumber = '';
    public ?Order $trackedOrder = null;
    public $statusHistory = [];

    public function searchOrder(): void
    {
        $this->trackedOrder = Order::with(['garmentType', 'preMadeProduct', 'statusHistory.changedByUser', 'user'])
            ->where('tracking_number', $this->trackingNumber)
            ->first();
        if ($this->trackedOrder) {
            $this->statusHistory = $this->trackedOrder->statusHistory()->orderBy('created_at', 'desc')->get();
        }
    }

    public function trackOrder(string $tn): void
    {
        $this->trackingNumber = $tn;
        $this->searchOrder();
    }

    public function getActiveStepIndex($order, $steps): int
    {
        if (!$order) return 0;
        foreach ($steps as $idx => $step) {
            if ($step['key'] === $order->status) {
                return $idx;
            }
        }
        if ($order->status === 'released') {
            return count($steps) - 1;
        }
        return 0;
    }

    public function with(): array
    {
        $statusSteps = [
            ['key' => 'pending', 'label' => 'Order Received'],
            ['key' => 'measurements_verified', 'label' => 'Measurements Verified'],
            ['key' => 'in_production', 'label' => 'In Production'],
            ['key' => 'fitting_scheduled', 'label' => 'Fitting Scheduled'],
            ['key' => 'final_adjustment', 'label' => 'Final Adjustment'],
            ['key' => 'ready_for_pickup', 'label' => 'Ready for Pickup'],
            ['key' => 'completed', 'label' => 'Completed'],
        ];

        if ($this->trackedOrder && $this->trackedOrder->order_type === 'pre_made') {
            $statusSteps = [
                ['key' => 'pending', 'label' => 'Order Received'],
                ['key' => 'in_production', 'label' => 'Preparing Order'],
                ['key' => 'ready_for_pickup', 'label' => 'Ready for Pickup'],
                ['key' => 'completed', 'label' => 'Completed'],
            ];
        }

        return [
            'recentOrders' => Order::where('user_id', auth()->id())->with(['garmentType', 'preMadeProduct'])->latest()->take(6)->get(),
            'statusSteps' => $statusSteps,
        ];
    }
}; ?>

<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">Track Your Order</h1>
        <p class="text-zinc-500 mt-1">Enter your tracking number to see real-time updates.</p>
    </div>

    <!-- Search Bar -->
    <div class="tc-card bg-gradient-to-br from-primary-50/50 via-white to-secondary-50/30 dark:from-zinc-800 dark:via-zinc-800 dark:to-zinc-700 border-2 border-primary-100 dark:border-primary-900/30">
        <form wire:submit="searchOrder" class="space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
                    <svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <flux:input 
                        wire:model="trackingNumber" 
                        placeholder="Enter tracking number (e.g. TC-2025-1001)" 
                        class="!bg-white/80 dark:!bg-zinc-700/80 backdrop-blur-sm border-primary-200 dark:border-primary-800 focus:!border-primary-400 focus:!ring-primary-200" 
                    />
                </div>
                <flux:button 
                    type="submit" 
                    variant="primary" 
                    class="!bg-primary-500 hover:!bg-primary-600 !px-6 shadow-lg hover:shadow-xl transition-all duration-300 click-feedback"
                >
                    <div wire:loading wire:target="searchOrder" class="btn-spinner">
                        <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full spinner"></div>
                    </div>
                    <span wire:loading.remove wire:target="searchOrder" class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                        </svg>
                        Track
                    </span>
                </flux:button>
            </div>
            <p class="text-xs text-primary-600 dark:text-primary-400 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                You can find your tracking number in the order confirmation email or your order history.
            </p>
        </form>
    </div>

    <!-- Tracked Order Display -->
    @if($trackedOrder)
        <div class="tc-card animate-fade-in-up shadow-soft hover:shadow-colored transition-all duration-500">
            <!-- Order Summary Header -->
            <div class="relative -m-6 mb-6 p-6 bg-gradient-to-br from-primary-500/10 via-primary-400/5 to-secondary-500/10 border-b border-primary-100 dark:border-primary-900/30">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary-100 to-primary-200 dark:from-primary-900/30 dark:to-primary-800/30 flex items-center justify-center border border-primary-200 dark:border-primary-800">
                            @if($trackedOrder->order_type === 'pre_made')
                                <svg class="w-8 h-8 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a.997.997 0 01-1.414 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                            @else
                                <svg class="w-8 h-8 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                                </svg>
                            @endif
                        </div>
                        <div>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">Tracking Number</p>
                            <p class="text-2xl font-bold font-mono text-primary-600 dark:text-primary-400 tracking-wider">{{ $trackedOrder->tracking_number }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-semibold text-zinc-900 dark:text-white mb-1" style="font-family: 'Poppins';">
                            @if($trackedOrder->order_type === 'pre_made')
                                {{ $trackedOrder->preMadeProduct?->name }} (Size: {{ $trackedOrder->product_size }})
                            @else
                                {{ $trackedOrder->garmentType?->name }}
                            @endif
                        </p>
                        <div class="flex items-center gap-2 justify-end">
                            <svg class="w-4 h-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <p class="text-sm text-zinc-500">Ordered {{ $trackedOrder->created_at->format('M d, Y') }}</p>
                        </div>
                        <div class="mt-2">
                            <span class="tc-badge tc-badge-{{ $trackedOrder->status }} text-sm px-3 py-1">
                                {{ ucwords(str_replace('_', ' ', $trackedOrder->status)) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enhanced 7-Step Progress Tracker -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-6 flex items-center gap-2" style="font-family: 'Poppins';">
                    <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Order Progress
                </h3>
                
                <!-- Desktop Horizontal Progress -->
                <div class="hidden md:block">
                    <div class="flex items-center justify-between relative">
                        <!-- Background Line -->
                        <div class="absolute left-0 top-8 w-full h-1 bg-zinc-200 dark:bg-zinc-700 rounded-full -z-10"></div>
                        
                        @foreach($statusSteps as $idx => $step)
                            @php $orderIdx = $this->getActiveStepIndex($trackedOrder, $statusSteps); @endphp
                            <div class="flex flex-col items-center text-center flex-1 relative z-10">
                                <!-- Step Circle -->
                                <div class="w-16 h-16 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-500 mb-4
                                    {{ $idx < $orderIdx ? 'bg-emerald-500 text-white shadow-lg animate-pulse-soft' : 
                                       ($idx === $orderIdx ? 'bg-primary-500 text-white ring-4 ring-primary-200 dark:ring-primary-900 shadow-xl scale-110' : 
                                        'bg-zinc-200 dark:bg-zinc-700 text-zinc-400') }}">
                                    @if($idx < $orderIdx)
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                        </svg>
                                    @elseif($idx === $orderIdx)
                                        <div class="w-3 h-3 bg-white rounded-full animate-ping"></div>
                                    @else
                                        {{ $idx + 1 }}
                                    @endif
                                </div>
                                
                                <!-- Step Label -->
                                <p class="text-sm max-w-[100px] leading-tight transition-colors duration-300
                                    {{ $idx <= $orderIdx ? 'text-zinc-900 dark:text-white font-semibold' : 'text-zinc-400' }}">
                                    {{ $step['label'] }}
                                </p>
                                
                                <!-- Active Step Indicator -->
                                @if($idx === $orderIdx)
                                    <div class="absolute -bottom-2 left-1/2 transform -translate-x-1/2">
                                        <div class="w-2 h-2 bg-primary-500 rounded-full animate-pulse"></div>
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Progress Line Segment -->
                            @if($idx < count($statusSteps) - 1)
                                <div class="absolute top-8 h-1 bg-gradient-to-r transition-all duration-1000 rounded-full
                                    {{ $idx < $orderIdx ? 'from-emerald-500 to-emerald-400' : 'from-zinc-200 to-zinc-200 dark:from-zinc-700 dark:to-zinc-700' }}"
                                     style="left: {{ (($idx + 1) / count($statusSteps)) * 100 }}%; 
                                            width: {{ (1 / count($statusSteps)) * 100 }}%; 
                                            margin-left: -{{ (0.5 / count($statusSteps)) * 100 }}%;">
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
                
                <!-- Mobile Vertical Progress -->
                <div class="md:hidden space-y-4">
                    @foreach($statusSteps as $idx => $step)
                        @php $orderIdx = $this->getActiveStepIndex($trackedOrder, $statusSteps); @endphp
                        <div class="flex items-center gap-4 p-4 rounded-xl transition-all duration-300
                            {{ $idx === $orderIdx ? 'bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800' : '' }}">
                            <!-- Step Circle -->
                            <div class="w-12 h-12 rounded-full flex items-center justify-center text-sm font-bold shrink-0 transition-all duration-500
                                {{ $idx < $orderIdx ? 'bg-emerald-500 text-white shadow-md' : 
                                   ($idx === $orderIdx ? 'bg-primary-500 text-white ring-4 ring-primary-200 dark:ring-primary-800 shadow-lg' : 
                                    'bg-zinc-200 dark:bg-zinc-700 text-zinc-400') }}">
                                @if($idx < $orderIdx)
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                    </svg>
                                @elseif($idx === $orderIdx)
                                    <div class="w-2 h-2 bg-white rounded-full animate-ping"></div>
                                @else
                                    {{ $idx + 1 }}
                                @endif
                            </div>
                            
                            <!-- Step Content -->
                            <div class="flex-1">
                                <p class="font-semibold transition-colors duration-300
                                    {{ $idx <= $orderIdx ? 'text-zinc-900 dark:text-white' : 'text-zinc-400' }}">
                                    {{ $step['label'] }}
                                </p>
                                @if($idx === $orderIdx)
                                    <p class="text-xs text-primary-600 dark:text-primary-400 mt-1">Currently in progress...</p>
                                @elseif($idx < $orderIdx)
                                    <p class="text-xs text-emerald-600 dark:text-emerald-400 mt-1">Completed</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Enhanced Estimated Completion -->
            @if($trackedOrder->estimated_completion)
                <div class="bg-gradient-to-r from-primary-50 to-secondary-50 dark:from-primary-900/20 dark:to-secondary-900/20 rounded-2xl p-6 mb-6 border border-primary-100 dark:border-primary-900/30">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-primary-100 to-primary-200 dark:from-primary-900/50 dark:to-primary-800/50 flex items-center justify-center">
                            <svg class="w-7 h-7 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-primary-700 dark:text-primary-300 mb-1">Estimated Completion Date</p>
                            <p class="text-2xl font-bold text-primary-800 dark:text-primary-200 mb-1" style="font-family: 'Poppins';">
                                {{ $trackedOrder->estimated_completion->format('F d, Y') }}
                            </p>
                            <div class="flex items-center gap-2 text-sm">
                                @php 
                                    $daysLeft = now()->diffInDays($trackedOrder->estimated_completion, false);
                                @endphp
                                @if($daysLeft > 0)
                                    <div class="flex items-center gap-1 text-blue-600 dark:text-blue-400">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ $daysLeft }} days remaining
                                    </div>
                                @elseif($daysLeft === 0)
                                    <div class="flex items-center gap-1 text-amber-600 dark:text-amber-400">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                                        </svg>
                                        Due today
                                    </div>
                                @else
                                    <div class="flex items-center gap-1 text-red-600 dark:text-red-400">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ abs($daysLeft) }} days overdue
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Status History Timeline -->
            @if($statusHistory->count())
                <h3 class="text-md font-semibold text-zinc-900 dark:text-white mb-4" style="font-family: 'Poppins';">Status Updates</h3>
                <div class="tc-timeline">
                    @foreach($statusHistory as $history)
                        <div class="tc-timeline-item">
                            <div class="tc-timeline-dot bg-primary-500"></div>
                            <div>
                                <p class="text-sm font-medium text-zinc-900 dark:text-white">{{ ucwords(str_replace('_', ' ', $history->status)) }}</p>
                                @if($history->notes)
                                    <p class="text-xs text-zinc-500">{{ $history->notes }}</p>
                                @endif
                                <p class="text-xs text-zinc-400 mt-0.5">{{ $history->created_at?->format('M d, Y g:i A') }}
                                    @if($history->changedByUser) · by {{ $history->changedByUser->name }} @endif
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    <!-- Enhanced Recent Orders Quick Access -->
    <div class="animate-fade-in-up" style="--stagger-index: 2">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold text-zinc-900 dark:text-white flex items-center gap-2" style="font-family: 'Poppins';">
                <svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Your Recent Orders
            </h2>
            <a href="{{ route('customer.orders') }}" wire:navigate class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 transition-colors flex items-center gap-1">
                View all orders
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
        </div>
        
        @if($recentOrders->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($recentOrders as $order)
                    <button 
                        wire:click="trackOrder('{{ $order->tracking_number }}')" 
                        class="tc-card text-left hover-lift interactive-card group p-4 cursor-pointer animate-fade-in-up hover:border-primary-200 dark:hover:border-primary-800 transition-all duration-300"
                        style="--stagger-index: {{ $loop->index }}"
                    >
                        <div class="flex items-start justify-between mb-3">
                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-primary-100 to-primary-200 dark:from-primary-900/30 dark:to-primary-800/30 flex items-center justify-center">
                                @if($order->order_type === 'pre_made')
                                    <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a.997.997 0 01-1.414 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                                    </svg>
                                @endif
                            </div>
                            <div class="opacity-0 group-hover:opacity-100 transition-opacity">
                                <svg class="w-4 h-4 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </div>
                        
                        <p class="text-sm font-mono font-bold text-primary-600 dark:text-primary-400 mb-2 tracking-wide">
                            {{ $order->tracking_number }}
                        </p>
                        
                        <p class="text-sm text-zinc-900 dark:text-zinc-100 font-medium mb-2 line-clamp-1">
                            @if($order->order_type === 'pre_made')
                                {{ $order->preMadeProduct?->name ?? 'Pre-made Product' }}
                            @else
                                {{ $order->garmentType?->name ?? 'Custom Tailoring' }}
                            @endif
                        </p>
                        
                        <div class="flex items-center justify-between">
                            <span class="tc-badge tc-badge-{{ $order->status }} text-xs">
                                {{ ucwords(str_replace('_', ' ', $order->status)) }}
                            </span>
                            <span class="text-xs text-zinc-500">
                                {{ $order->created_at->format('M d') }}
                            </span>
                        </div>
                    </button>
                @endforeach
            </div>
        @else
            <x-enhanced-empty-state
                icon="search"
                title="No recent orders"
                description="You haven't placed any orders yet. Start by browsing our shops!"
                :actions="[
                    ['type' => 'primary', 'label' => 'Browse Shops', 'onclick' => 'window.location.href=\"' . route('customer.shops') . '\"'],
                    ['type' => 'secondary', 'label' => 'Create Order', 'onclick' => 'window.location.href=\"' . route('customer.orders') . '\"']
                ]"
            />
        @endif
    </div>
</div>
