<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Shop;

new #[Layout('components.layouts.app')] class extends Component {
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
                <flux:table.columns>
                    <flux:table.column>Shop Name</flux:table.column>
                    <flux:table.column>Owner</flux:table.column>
                    <flux:table.column>Location</flux:table.column>
                    <flux:table.column>Rating</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>Actions</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse($shops as $shop)
                        <flux:table.row>
                            <flux:table.cell class="font-bold">{{ $shop->name }}</flux:table.cell>
                            <flux:table.cell>{{ $shop->owner?->name ?? 'Unassigned' }}</flux:table.cell>
                            <flux:table.cell>{{ $shop->barangay }}, {{ $shop->city }}</flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center gap-1">
                                    <flux:icon.star class="size-3 text-yellow-500" />
                                    <span>{{ $shop->rating }}</span>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($shop->is_verified)
                                    <flux:badge color="green" size="sm">Verified</flux:badge>
                                @else
                                    <flux:badge color="zinc" size="sm">Unverified</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:button size="sm" variant="ghost" wire:click="toggleVerification({{ $shop->id }})">Toggle Status</flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="6" class="text-center py-8 text-zinc-500">No shops found.</flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
        <div class="mt-4">
            {{ $shops->links() }}
        </div>
    </div>
</div>
