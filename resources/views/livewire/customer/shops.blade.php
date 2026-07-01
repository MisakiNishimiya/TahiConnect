<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Shop;

new #[Layout('components.layouts.app')] class extends Component {
    use WithPagination;

    public $search = '';
    public $barangay = '';

    public function with()
    {
        $shops = Shop::where('is_active', true)
            ->when($this->search, function($query) {
                $query->where('name', 'like', "%{$this->search}%")
                      ->orWhere('specialties', 'like', "%{$this->search}%");
            })
            ->when($this->barangay, function($query) {
                $query->where('barangay', $this->barangay);
            })
            ->orderBy('is_featured', 'desc')
            ->orderBy('rating', 'desc')
            ->paginate(12);

        $barangays = Shop::select('barangay')->distinct()->pluck('barangay')->filter();

        return [
            'shops' => $shops,
            'barangays' => $barangays,
        ];
    }
    
    public function updatedSearch() { $this->resetPage(); }
    public function updatedBarangay() { $this->resetPage(); }
}; ?>

<div>
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-primary-600 dark:text-primary-400">Discover Tailors in Davao City</h1>
        <p class="text-zinc-500 dark:text-zinc-400">Find the perfect shop for your bespoke garments.</p>
    </div>

    <!-- Filters -->
    <div class="tc-card mb-8">
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Search by name or specialty (e.g., Barong, Gown)..." />
            </div>
            <div class="w-full sm:w-64">
                <flux:select wire:model.live="barangay" placeholder="Any Barangay">
                    <flux:select.option value="">Any Location</flux:select.option>
                    @foreach($barangays as $bgy)
                        <flux:select.option value="{{ $bgy }}">{{ $bgy }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
        </div>
    </div>

    <!-- Shop Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($shops as $shop)
            <div class="tc-card hover:border-primary-300 cursor-pointer flex flex-col h-full">
                <div class="flex justify-between items-start mb-4">
                    <h2 class="text-xl font-bold font-heading text-primary-700 dark:text-primary-300">{{ $shop->name }}</h2>
                    @if($shop->is_featured)
                        <flux:badge color="yellow" size="sm" icon="star">Featured</flux:badge>
                    @endif
                </div>
                
                <div class="flex items-center gap-2 text-sm text-zinc-500 mb-2">
                    <flux:icon.map-pin class="size-4" />
                    <span>{{ $shop->address }}, {{ $shop->barangay }}</span>
                </div>
                
                <div class="flex items-center gap-2 text-sm text-zinc-500 mb-4">
                    <flux:icon.star class="size-4 text-yellow-500" />
                    <span class="font-bold text-zinc-700 dark:text-zinc-300">{{ $shop->rating }}</span>
                    <span>({{ $shop->total_reviews }} reviews)</span>
                </div>

                <div class="flex flex-wrap gap-2 mb-6 flex-grow">
                    @if(is_array($shop->specialties))
                        @foreach(array_slice($shop->specialties, 0, 3) as $specialty)
                            <flux:badge size="sm" color="zinc">{{ $specialty }}</flux:badge>
                        @endforeach
                        @if(count($shop->specialties) > 3)
                            <flux:badge size="sm" color="zinc">+{{ count($shop->specialties) - 3 }}</flux:badge>
                        @endif
                    @endif
                </div>
                
                <flux:button class="w-full" variant="primary" :href="route('customer.shop.show', $shop->id)" wire:navigate>View Shop & Book</flux:button>
            </div>
        @empty
            <div class="col-span-full tc-card text-center py-12 text-zinc-500">
                <flux:icon.building-storefront class="size-12 mx-auto mb-4 text-zinc-300" />
                <h3 class="text-lg font-bold text-zinc-700 dark:text-zinc-300 mb-2">No shops found</h3>
                <p>Try adjusting your search filters.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $shops->links() }}
    </div>
</div>
