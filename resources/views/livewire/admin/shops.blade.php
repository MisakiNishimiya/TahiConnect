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
                $query->where('name', 'like', "%{$this->search}%")
                      ->orWhere('city', 'like', "%{$this->search}%")
                      ->orWhere('barangay', 'like', "%{$this->search}%");
            })
            ->latest()
            ->paginate(10);

        return [
            'shops' => $shops,
            'verifiedCount' => Shop::where('is_verified', true)->count(),
            'activeCount' => Shop::where('is_active', true)->count(),
            'avgRating' => Shop::avg('rating') ?? 0,
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
    <div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4">
        <div>
            <h1 class="text-3xl font-bold text-primary-600 dark:text-primary-400">Platform Shops</h1>
            <p class="text-zinc-500 dark:text-zinc-400">Manage tailoring shops registered on TahiConnect</p>
        </div>
        <div class="flex gap-3">
            <flux:button variant="subtle" icon="download">Export Data</flux:button>
            <flux:button variant="primary" icon="plus">Register New Shop</flux:button>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="tc-card bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 border-blue-200 dark:border-blue-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-blue-600 dark:text-blue-400 font-medium">Total Shops</p>
                    <p class="text-2xl font-bold text-blue-700 dark:text-blue-300">{{ $shops->total() }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-500 flex items-center justify-center">
                    <flux:icon.building-storefront class="size-6 text-white" />
                </div>
            </div>
        </div>
        <div class="tc-card bg-gradient-to-br from-emerald-50 to-emerald-100 dark:from-emerald-900/20 dark:to-emerald-800/20 border-emerald-200 dark:border-emerald-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-emerald-600 dark:text-emerald-400 font-medium">Verified Shops</p>
                    <p class="text-2xl font-bold text-emerald-700 dark:text-emerald-300">{{ $verifiedCount }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-500 flex items-center justify-center">
                    <flux:icon.check-badge class="size-6 text-white" />
                </div>
            </div>
        </div>
        <div class="tc-card bg-gradient-to-br from-amber-50 to-amber-100 dark:from-amber-900/20 dark:to-amber-800/20 border-amber-200 dark:border-amber-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-amber-600 dark:text-amber-400 font-medium">Active Shops</p>
                    <p class="text-2xl font-bold text-amber-700 dark:text-amber-300">{{ $activeCount }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-amber-500 flex items-center justify-center">
                    <flux:icon.spark class="size-6 text-white" />
                </div>
            </div>
        </div>
        <div class="tc-card bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 border-purple-200 dark:border-purple-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-purple-600 dark:text-purple-400 font-medium">Avg Rating</p>
                    <p class="text-2xl font-bold text-purple-700 dark:text-purple-300">{{ number_format($avgRating, 1) }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-purple-500 flex items-center justify-center">
                    <flux:icon.star class="size-6 text-white" />
                </div>
            </div>
        </div>
    </div>

    <div class="tc-card">
        <!-- Search and Filters -->
        <div class="flex flex-col sm:flex-row gap-4 mb-6">
            <div class="flex-1">
                <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Search shops by name, city, or barangay..." />
            </div>
            <div class="flex gap-2">
                <flux:select placeholder="Status">
                    <flux:select.option value="all">All Status</flux:select.option>
                    <flux:select.option value="verified">Verified</flux:select.option>
                    <flux:select.option value="unverified">Unverified</flux:select.option>
                    <flux:select.option value="active">Active</flux:select.option>
                    <flux:select.option value="inactive">Inactive</flux:select.option>
                </flux:select>
                <flux:button variant="subtle" icon="funnel" size="sm">Filter</flux:button>
            </div>
        </div>

        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Shop Details</flux:table.column>
                    <flux:table.column>Owner</flux:table.column>
                    <flux:table.column>Location</flux:table.column>
                    <flux:table.column>Performance</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>Actions</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse($shops as $shop)
                        <flux:table.row>
                            <flux:table.cell>
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-xl bg-primary-100 dark:bg-primary-900 flex items-center justify-center">
                                        <flux:icon.building-storefront class="size-6 text-primary-600 dark:text-primary-400" />
                                    </div>
                                    <div>
                                        <p class="font-bold text-zinc-900 dark:text-white">{{ $shop->name }}</p>
                                        <p class="text-xs text-zinc-500">{{ $shop->specialties_list }}</p>
                                    </div>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($shop->owner)
                                    <div class="flex items-center gap-2">
                                        <flux:avatar size="sm" :initials="$shop->owner->initials()" />
                                        <div>
                                            <p class="font-medium text-sm">{{ $shop->owner->name }}</p>
                                            <p class="text-xs text-zinc-500">{{ $shop->owner->email }}</p>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-zinc-400 text-sm">Unassigned</span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <div>
                                    <p class="font-medium text-sm">{{ $shop->barangay }}</p>
                                    <p class="text-xs text-zinc-500">{{ $shop->city }}</p>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    <div class="flex items-center gap-1">
                                        <flux:icon.star class="size-3 text-yellow-500" />
                                        <span class="text-sm font-medium">{{ $shop->rating }}</span>
                                    </div>
                                    <span class="text-xs text-zinc-400">({{ $shop->total_reviews }} reviews)</span>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex flex-col gap-1">
                                    @if($shop->is_verified)
                                        <flux:badge color="green" size="sm">Verified</flux:badge>
                                    @else
                                        <flux:badge color="zinc" size="sm">Unverified</flux:badge>
                                    @endif
                                    @if($shop->is_active)
                                        <flux:badge color="blue" size="sm">Active</flux:badge>
                                    @else
                                        <flux:badge color="red" size="sm">Inactive</flux:badge>
                                    @endif
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center gap-1">
                                    <flux:button size="sm" variant="ghost" wire:click="toggleVerification({{ $shop->id }})" icon="check">
                                        {{ $shop->is_verified ? 'Unverify' : 'Verify' }}
                                    </flux:button>
                                    <flux:button size="sm" variant="ghost" icon="eye">View</flux:button>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="6" class="text-center py-8 text-zinc-500">
                                <div class="flex flex-col items-center gap-2">
                                    <flux:icon.building-storefront class="size-12 text-zinc-300" />
                                    <p>No shops found matching your search.</p>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>

        <!-- Mobile Card View -->
        <div class="md:hidden space-y-4">
            @forelse($shops as $shop)
                <div class="border border-zinc-200 dark:border-zinc-700 rounded-xl p-4 bg-white dark:bg-zinc-800">
                    <div class="flex items-start gap-3 mb-3">
                        <div class="w-12 h-12 rounded-xl bg-primary-100 dark:bg-primary-900 flex items-center justify-center">
                            <flux:icon.building-storefront class="size-6 text-primary-600 dark:text-primary-400" />
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-zinc-900 dark:text-white">{{ $shop->name }}</h3>
                            <p class="text-xs text-zinc-500 mb-2">{{ $shop->barangay }}, {{ $shop->city }}</p>
                            <div class="flex items-center gap-2 mb-2">
                                <flux:icon.star class="size-3 text-yellow-500" />
                                <span class="text-sm">{{ $shop->rating }}</span>
                                <span class="text-xs text-zinc-400">({{ $shop->total_reviews }})</span>
                            </div>
                            <div class="flex gap-1">
                                @if($shop->is_verified)
                                    <flux:badge color="green" size="sm">Verified</flux:badge>
                                @endif
                                @if($shop->is_active)
                                    <flux:badge color="blue" size="sm">Active</flux:badge>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-between items-center pt-3 border-t border-zinc-100 dark:border-zinc-700">
                        <p class="text-xs text-zinc-500">Owner: {{ $shop->owner?->name ?? 'Unassigned' }}</p>
                        <div class="flex gap-1">
                            <flux:button size="sm" variant="ghost" wire:click="toggleVerification({{ $shop->id }})" icon="check">
                                {{ $shop->is_verified ? 'Unverify' : 'Verify' }}
                            </flux:button>
                            <flux:button size="sm" variant="ghost" icon="eye">View</flux:button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-12">
                    <flux:icon.building-storefront class="size-16 text-zinc-300 mx-auto mb-4" />
                    <p class="text-zinc-500">No shops found matching your search.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $shops->links() }}
        </div>
    </div>
</div>
