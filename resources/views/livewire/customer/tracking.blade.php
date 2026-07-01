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
        $this->trackedOrder = Order::with(['garmentType', 'statusHistory.changedByUser', 'user'])
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

    public function with(): array
    {
        return [
            'recentOrders' => Order::where('user_id', auth()->id())->with('garmentType')->latest()->take(6)->get(),
            'statusSteps' => [
                ['key' => 'pending', 'label' => 'Order Received'],
                ['key' => 'measurements_verified', 'label' => 'Measurements Verified'],
                ['key' => 'in_production', 'label' => 'In Production'],
                ['key' => 'fitting_scheduled', 'label' => 'Fitting Scheduled'],
                ['key' => 'final_adjustment', 'label' => 'Final Adjustment'],
                ['key' => 'ready_for_pickup', 'label' => 'Ready for Pickup'],
                ['key' => 'completed', 'label' => 'Completed'],
            ],
        ];
    }
}; ?>

<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">Track Your Order</h1>
        <p class="text-zinc-500 mt-1">Enter your tracking number to see real-time updates.</p>
    </div>

    <!-- Search Bar -->
    <div class="tc-card bg-gradient-to-r from-cream-100 to-cream-200 dark:from-zinc-800 dark:to-zinc-800">
        <form wire:submit="searchOrder" class="flex gap-3">
            <div class="flex-1">
                <flux:input wire:model="trackingNumber" placeholder="Enter tracking number (e.g. TC-2025-1001)" class="!bg-white dark:!bg-zinc-700" />
            </div>
            <flux:button type="submit" variant="primary" class="!bg-primary-500 hover:!bg-primary-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            </flux:button>
        </form>
    </div>

    <!-- Tracked Order Display -->
    @if($trackedOrder)
        <div class="tc-card animate-fade-in-up">
            <!-- Order Summary -->
            <div class="flex flex-wrap items-center justify-between gap-4 mb-6 pb-6 border-b border-zinc-100 dark:border-zinc-700">
                <div>
                    <p class="text-sm text-zinc-500">Tracking Number</p>
                    <p class="text-xl font-bold font-mono text-primary-600 dark:text-primary-400">{{ $trackedOrder->tracking_number }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-zinc-500">{{ $trackedOrder->garmentType?->name }}</p>
                    <p class="text-sm text-zinc-500">Ordered {{ $trackedOrder->created_at->format('M d, Y') }}</p>
                </div>
            </div>

            <!-- 7-Step Progress Tracker -->
            <div class="mb-8">
                <div class="hidden md:flex items-center justify-between">
                    @foreach($statusSteps as $idx => $step)
                        @php $orderIdx = $trackedOrder->status_index; @endphp
                        <div class="flex flex-col items-center text-center flex-1">
                            <div class="tc-progress-step-dot {{ $idx < $orderIdx ? 'tc-progress-step-dot-completed' : ($idx === $orderIdx ? 'tc-progress-step-dot-active' : 'tc-progress-step-dot-pending') }}">
                                @if($idx < $orderIdx)
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                @else
                                    {{ $idx + 1 }}
                                @endif
                            </div>
                            <p class="text-xs mt-2 max-w-[80px] {{ $idx <= $orderIdx ? 'text-zinc-900 dark:text-white font-medium' : 'text-zinc-400' }}">{{ $step['label'] }}</p>
                        </div>
                        @if($idx < count($statusSteps) - 1)
                            <div class="tc-progress-line {{ $idx < $orderIdx ? 'tc-progress-line-completed' : 'tc-progress-line-pending' }}"></div>
                        @endif
                    @endforeach
                </div>
                <!-- Mobile vertical -->
                <div class="md:hidden space-y-3">
                    @foreach($statusSteps as $idx => $step)
                        @php $orderIdx = $trackedOrder->status_index; @endphp
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-semibold shrink-0
                                {{ $idx < $orderIdx ? 'bg-emerald-500 text-white' : ($idx === $orderIdx ? 'bg-primary-500 text-white ring-4 ring-primary-100' : 'bg-zinc-200 text-zinc-400') }}">
                                @if($idx < $orderIdx) ✓ @else {{ $idx + 1 }} @endif
                            </div>
                            <p class="text-sm {{ $idx <= $orderIdx ? 'text-zinc-900 dark:text-white font-medium' : 'text-zinc-400' }}">{{ $step['label'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Estimated Completion -->
            @if($trackedOrder->estimated_completion)
                <div class="bg-primary-50 dark:bg-primary-900/20 rounded-xl p-4 mb-6 flex items-center gap-3">
                    <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>
                        <p class="text-sm font-medium text-primary-700 dark:text-primary-300">Estimated Completion</p>
                        <p class="text-lg font-bold text-primary-800 dark:text-primary-200" style="font-family: 'Poppins';">{{ $trackedOrder->estimated_completion->format('F d, Y') }}</p>
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

    <!-- Recent Orders Quick Access -->
    <div>
        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4" style="font-family: 'Poppins';">Your Recent Orders</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($recentOrders as $order)
                <button wire:click="trackOrder('{{ $order->tracking_number }}')" class="tc-card text-left hover:ring-2 hover:ring-primary-300 transition-all">
                    <p class="text-sm font-mono font-semibold text-primary-600 dark:text-primary-400">{{ $order->tracking_number }}</p>
                    <p class="text-xs text-zinc-500 mt-1">{{ $order->garmentType?->name ?? 'Custom' }}</p>
                    <span class="tc-badge tc-badge-{{ $order->status }} mt-2">{{ ucwords(str_replace('_', ' ', $order->status)) }}</span>
                </button>
            @endforeach
        </div>
    </div>
</div>
