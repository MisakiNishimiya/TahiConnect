<?php

use App\Models\User;
use App\Models\Order;
use App\Models\Measurement;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('components.layouts.app')] class extends Component {
    use WithPagination;

    public string $search = '';

    public function with(): array
    {
        return [
            'customers' => User::where('role', 'customer')
                ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%"))
                ->withCount('orders')
                ->latest()
                ->paginate(15),
            'totalCustomers' => User::where('role', 'customer')->count(),
        ];
    }

    public function updatedSearch(): void { $this->resetPage(); }
}; ?>

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-primary-600 dark:text-primary-400" style="font-family: 'Poppins';">Customers</h1>
            <p class="text-zinc-500 mt-1">{{ $totalCustomers }} registered customers</p>
        </div>
    </div>

    <div class="tc-card !p-4">
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input wire:model.live.debounce.300ms="search" placeholder="Search customers by name or email..."
                class="w-full pl-10 pr-4 py-2.5 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-sm focus:ring-2 focus:ring-primary-500" />
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($customers as $customer)
            <div class="tc-card hover-lift group">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white text-lg font-bold group-hover:scale-110 transition-transform duration-300 shadow-lg">
                        {{ strtoupper(substr($customer->name, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-zinc-900 dark:text-white truncate">{{ $customer->name }}</p>
                        <p class="text-xs text-zinc-500 truncate">{{ $customer->email }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3 pt-4 border-t border-zinc-100 dark:border-zinc-700">
                    <div class="text-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl">
                        <p class="text-xl font-bold text-blue-700 dark:text-blue-300">{{ $customer->orders_count }}</p>
                        <p class="text-xs text-blue-600 dark:text-blue-400 font-medium">Orders</p>
                    </div>
                    <div class="text-center p-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-xl">
                        <p class="text-xs font-medium text-zinc-600 dark:text-zinc-400 mt-1">Joined</p>
                        <p class="text-xs text-zinc-500">{{ $customer->created_at->format('M Y') }}</p>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <x-enhanced-empty-state icon="folder" title="No customers yet" description="Customers will appear here once they register and place orders." :actions="[]" />
            </div>
        @endforelse
    </div>

    @if($customers->hasPages())
        <div class="flex justify-center">{{ $customers->links() }}</div>
    @endif
</div>
