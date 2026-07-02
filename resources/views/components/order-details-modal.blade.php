@props(['order', 'show' => false])

@if($show && $order)
<div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 overflow-y-auto">
    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl w-full max-w-2xl flex flex-col my-auto max-h-[90vh]" @click.stop>
        
        <!-- Header -->
        <div class="px-6 py-4 border-b border-zinc-100 dark:border-zinc-800 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-zinc-900 dark:text-white">Order Details</h2>
                <p class="text-sm text-zinc-500 font-mono">{{ $order->tracking_number }}</p>
            </div>
            <button wire:click="$set('showOrderModal', false)" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <!-- Body -->
        <div class="flex-1 overflow-y-auto p-6 space-y-6">
            
            <!-- Order Summary -->
            <div class="bg-cream-50 dark:bg-zinc-800/50 rounded-xl p-4">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h3 class="font-semibold text-zinc-900 dark:text-white">
                            @if($order->order_type === 'pre_made')
                                {{ $order->preMadeProduct?->name }}
                            @else
                                {{ $order->garmentType?->name }}
                            @endif
                        </h3>
                        <p class="text-sm text-zinc-500">{{ $order->shop?->name }}</p>
                    </div>
                    <span class="tc-badge tc-badge-{{ $order->status }}">
                        {{ ucwords(str_replace('_', ' ', $order->status)) }}
                    </span>
                </div>
                
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-zinc-500">Quantity:</span>
                        <span class="font-medium text-zinc-900 dark:text-white ml-2">{{ $order->quantity }}</span>
                    </div>
                    @if($order->order_type === 'pre_made')
                        <div>
                            <span class="text-zinc-500">Size:</span>
                            <span class="font-medium text-zinc-900 dark:text-white ml-2">{{ $order->product_size }}</span>
                        </div>
                    @endif
                    @if($order->fabric_preference)
                        <div>
                            <span class="text-zinc-500">Fabric:</span>
                            <span class="font-medium text-zinc-900 dark:text-white ml-2">{{ $order->fabric_preference }}</span>
                        </div>
                    @endif
                    <div>
                        <span class="text-zinc-500">Total:</span>
                        <span class="font-bold text-primary-600 dark:text-primary-400 ml-2">₱{{ number_format($order->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>
            
            <!-- Progress Tracker -->
            <div>
                <h4 class="font-semibold text-zinc-900 dark:text-white mb-4">Progress</h4>
                <div class="space-y-2">
                    @php
                        $steps = $order->order_type === 'pre_made' 
                            ? ['pending', 'in_production', 'ready_for_pickup', 'completed', 'released']
                            : ['pending', 'measurements_verified', 'in_production', 'fitting_scheduled', 'final_adjustment', 'ready_for_pickup', 'completed', 'released'];
                    @endphp
                    
                    @foreach($steps as $index => $step)
                        <div class="flex items-center gap-3">
                            <div class="w-4 h-4 rounded-full {{ $index <= $order->status_index ? 'bg-primary-500' : 'bg-zinc-200 dark:bg-zinc-700' }}"></div>
                            <span class="text-sm {{ $index <= $order->status_index ? 'text-zinc-900 dark:text-white font-medium' : 'text-zinc-500' }}">
                                {{ ucwords(str_replace('_', ' ', $step)) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
            
            @if($order->special_instructions)
            <!-- Special Instructions -->
            <div>
                <h4 class="font-semibold text-zinc-900 dark:text-white mb-2">Special Instructions</h4>
                <p class="text-sm text-zinc-600 dark:text-zinc-400 bg-amber-50 dark:bg-amber-900/10 p-3 rounded-lg">
                    {{ $order->special_instructions }}
                </p>
            </div>
            @endif
            
            <!-- Timestamps -->
            <div class="grid grid-cols-2 gap-4 text-xs text-zinc-500">
                <div>
                    <span class="block">Ordered:</span>
                    <span class="font-medium">{{ $order->created_at->format('M d, Y g:i A') }}</span>
                </div>
                @if($order->estimated_completion)
                <div>
                    <span class="block">Est. Completion:</span>
                    <span class="font-medium">{{ $order->estimated_completion->format('M d, Y') }}</span>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Footer -->
        <div class="px-6 py-4 bg-zinc-50 dark:bg-zinc-800/50 border-t border-zinc-100 dark:border-zinc-800 flex justify-end gap-3 rounded-b-2xl">
            <flux:button variant="subtle" wire:click="$set('showOrderModal', false)">Close</flux:button>
            <flux:button variant="primary" href="{{ route('customer.tracking') }}" wire:navigate>
                Track Order
            </flux:button>
        </div>
    </div>
</div>
@endif