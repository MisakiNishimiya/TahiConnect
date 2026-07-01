<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Shop;

new class extends Component {
    use WithPagination;

    public $search = '';

    public function with()
    {
        $shops = Shop::with('owner')
            ->when($this->search, function($query) {
                $query->where('name', 'like', "%{$this->search}%");
            })
            ->latest()
            ->paginate(10);

        return [
            'shops' => $shops,
        ];
    }
    
    public function updatedSearch() { $this->resetPage(); }
    
    public function toggleVerification($shopId)
    {
        $shop = Shop::find($shopId);
        $shop->update(['is_verified' => !$shop->is_verified]);
    }
}; ?>

<div>
    <div class="mb-8 flex justify-between items-end">
        <div>
            <h1 class="text-3xl font-bold text-primary-600 dark:text-primary-400">Platform Shops</h1>
            <p class="text-zinc-500 dark:text-zinc-400">Manage tailoring shops registered on TahiConnect</p>
        </div>
        <flux:button variant="primary" icon="plus">Register Shop</flux:button>
    </div>

    <div class="tc-card">
        <div class="mb-6">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Search shops..." class="max-w-md" />
        </div>

        <div class="overflow-x-auto">
            <flux:table>
                <flux:columns>
                    <flux:column>Shop Name</flux:column>
                    <flux:column>Owner</flux:column>
                    <flux:column>Location</flux:column>
                    <flux:column>Rating</flux:column>
                    <flux:column>Status</flux:column>
                    <flux:column>Actions</flux:column>
                </flux:columns>
                <flux:rows>
                    @forelse($shops as $shop)
                        <flux:row>
                            <flux:cell class="font-bold">{{ $shop->name }}</flux:cell>
                            <flux:cell>{{ $shop->owner?->name ?? 'Unassigned' }}</flux:cell>
                            <flux:cell>{{ $shop->barangay }}, {{ $shop->city }}</flux:cell>
                            <flux:cell>
                                <div class="flex items-center gap-1">
                                    <flux:icon.star class="size-3 text-yellow-500" />
                                    <span>{{ $shop->rating }}</span>
                                </div>
                            </flux:cell>
                            <flux:cell>
                                @if($shop->is_verified)
                                    <flux:badge color="green" size="sm">Verified</flux:badge>
                                @else
                                    <flux:badge color="zinc" size="sm">Unverified</flux:badge>
                                @endif
                            </flux:cell>
                            <flux:cell>
                                <flux:button size="sm" variant="ghost" wire:click="toggleVerification({{ $shop->id }})">Toggle Status</flux:button>
                            </flux:cell>
                        </flux:row>
                    @empty
                        <flux:row>
                            <flux:cell colspan="6" class="text-center py-8 text-zinc-500">No shops found.</flux:cell>
                        </flux:row>
                    @endforelse
                </flux:rows>
            </flux:table>
        </div>
        <div class="mt-4">
            {{ $shops->links() }}
        </div>
    </div>
</div>
