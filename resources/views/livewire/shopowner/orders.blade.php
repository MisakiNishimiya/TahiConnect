<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Order;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $status = '';

    public function with()
    {
        $shopId = auth()->user()->shop_id;

        $orders = Order::with(['user', 'garmentType', 'staff'])
            ->where('shop_id', $shopId)
            ->when($this->search, function ($query) {
                $query->where('tracking_number', 'like', "%{$this->search}%")
                      ->orWhereHas('user', function ($q) {
                          $q->where('name', 'like', "%{$this->search}%");
                      });
            })
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->latest()
            ->paginate(10);

        return [
            'orders' => $orders,
        ];
    }

    public function updatedSearch() { $this->resetPage(); }
    public function updatedStatus() { $this->resetPage(); }
}; ?>

<div>
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-primary-600 dark:text-primary-400">Shop Orders</h1>
        <p class="text-zinc-500 dark:text-zinc-400">Manage all customer orders for your shop</p>
    </div>

    <div class="tc-card">
        <div class="flex flex-col sm:flex-row gap-4 mb-6">
            <div class="flex-1">
                <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Search by tracking number or customer..." />
            </div>
            <div class="w-full sm:w-64">
                <flux:select wire:model.live="status" placeholder="All Statuses">
                    <flux:select.option value="">All Statuses</flux:select.option>
                    <flux:select.option value="pending">Pending</flux:select.option>
                    <flux:select.option value="measurements_verified">Measurements Verified</flux:select.option>
                    <flux:select.option value="in_production">In Production</flux:select.option>
                    <flux:select.option value="fitting_scheduled">Fitting Scheduled</flux:select.option>
                    <flux:select.option value="final_adjustment">Final Adjustment</flux:select.option>
                    <flux:select.option value="ready_for_pickup">Ready for Pickup</flux:select.option>
                    <flux:select.option value="completed">Completed</flux:select.option>
                    <flux:select.option value="released">Released</flux:select.option>
                </flux:select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <flux:table>
                <flux:columns>
                    <flux:column>Tracking No.</flux:column>
                    <flux:column>Customer</flux:column>
                    <flux:column>Garment</flux:column>
                    <flux:column>Assigned Staff</flux:column>
                    <flux:column>Amount</flux:column>
                    <flux:column>Status</flux:column>
                    <flux:column>Est. Completion</flux:column>
                </flux:columns>
                <flux:rows>
                    @forelse($orders as $order)
                        <flux:row>
                            <flux:cell class="font-medium">{{ $order->tracking_number }}</flux:cell>
                            <flux:cell>
                                <div class="flex items-center gap-2">
                                    <flux:avatar size="xs" :initials="$order->user->initials()" />
                                    <span>{{ $order->user->name }}</span>
                                </div>
                            </flux:cell>
                            <flux:cell>{{ $order->garmentType->name }} (x{{ $order->quantity }})</flux:cell>
                            <flux:cell>
                                @if($order->staff)
                                    <div class="flex items-center gap-2">
                                        <flux:avatar size="xs" :initials="$order->staff->initials()" />
                                        <span>{{ $order->staff->name }}</span>
                                    </div>
                                @else
                                    <span class="text-zinc-400">Unassigned</span>
                                @endif
                            </flux:cell>
                            <flux:cell>₱{{ number_format($order->total_amount, 2) }}</flux:cell>
                            <flux:cell>
                                <flux:badge color="{{ match($order->status) {
                                    'completed', 'released' => 'green',
                                    'pending' => 'zinc',
                                    'in_production' => 'blue',
                                    default => 'orange'
                                } }}" size="sm">
                                    {{ str_replace('_', ' ', Str::title($order->status)) }}
                                </flux:badge>
                            </flux:cell>
                            <flux:cell>{{ $order->estimated_completion?->format('M d, Y') ?? 'N/A' }}</flux:cell>
                        </flux:row>
                    @empty
                        <flux:row>
                            <flux:cell colspan="7" class="text-center py-8 text-zinc-500">No orders found.</flux:cell>
                        </flux:row>
                    @endforelse
                </flux:rows>
            </flux:table>
        </div>
        
        <div class="mt-4">
            {{ $orders->links() }}
        </div>
    </div>
</div>
