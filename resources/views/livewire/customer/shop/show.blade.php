<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Models\Shop;
use App\Models\GarmentType;
use App\Models\Fabric;

new #[Layout('components.layouts.app')] class extends Component {
    public Shop $shop;
    public $garments;
    public $fabrics;

    public function mount(Shop $shop)
    {
        $this->shop = $shop;
        $this->garments = GarmentType::where('shop_id', $shop->id)->get();
        $this->fabrics = Fabric::where('shop_id', $shop->id)->get();
    }
}; ?>

<div>
    <!-- Back Button -->
    <div class="mb-6">
        <flux:button variant="subtle" icon="arrow-left" href="{{ route('customer.shops') }}" wire:navigate>Back to Shops</flux:button>
    </div>

    <!-- Shop Hero -->
    <div class="tc-card mb-8 overflow-hidden relative">
        <div class="absolute inset-0 bg-primary-900/10 dark:bg-primary-900/30"></div>
        <div class="relative z-10 flex flex-col md:flex-row gap-8 items-start md:items-center">
            <div class="w-24 h-24 rounded-2xl bg-primary-100 dark:bg-primary-800 flex items-center justify-center shrink-0 border-4 border-white dark:border-zinc-800 shadow-lg">
                <flux:icon.building-storefront class="size-10 text-primary-600 dark:text-primary-300" />
            </div>
            <div class="flex-1">
                <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-2">
                    <h1 class="text-3xl font-bold font-heading text-primary-700 dark:text-primary-300">{{ $shop->name }}</h1>
                    @if($shop->is_featured)
                        <flux:badge color="yellow" size="sm" icon="star">Featured</flux:badge>
                    @endif
                </div>
                <div class="flex flex-wrap items-center gap-4 text-sm text-zinc-600 dark:text-zinc-400">
                    <div class="flex items-center gap-1.5">
                        <flux:icon.map-pin class="size-4" />
                        <span>{{ $shop->address }}, {{ $shop->barangay }}</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <flux:icon.star class="size-4 text-yellow-500" />
                        <span class="font-bold text-zinc-700 dark:text-zinc-300">{{ $shop->rating }}</span>
                        <span>({{ $shop->total_reviews }} reviews)</span>
                    </div>
                </div>
                
                <div class="flex flex-wrap gap-2 mt-4">
                    @if(is_array($shop->specialties))
                        @foreach($shop->specialties as $specialty)
                            <flux:badge size="sm" color="zinc">{{ $specialty }}</flux:badge>
                        @endforeach
                    @endif
                </div>
            </div>
            <div class="w-full md:w-auto flex flex-col gap-3">
                <flux:button variant="primary" class="w-full md:w-auto" :href="route('customer.shop.book', $shop->id)" wire:navigate icon="calendar">Book an Appointment</flux:button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Description -->
            <div class="tc-card">
                <h2 class="text-xl font-bold font-heading mb-4">About this Shop</h2>
                <p class="text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    {{ $shop->description ?: 'No description provided by the shop owner yet.' }}
                </p>
            </div>

            <!-- Garment Types -->
            <div class="tc-card">
                <h2 class="text-xl font-bold font-heading mb-4">Garments Available</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @forelse($garments as $garment)
                        <div class="p-4 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-cream-50 dark:bg-zinc-800/50 flex gap-4 items-center">
                            <div class="w-12 h-12 rounded-lg bg-primary-100 dark:bg-primary-900 flex items-center justify-center shrink-0">
                                <flux:icon.sparkles class="size-6 text-primary-600 dark:text-primary-300" />
                            </div>
                            <div>
                                <h3 class="font-bold text-zinc-900 dark:text-white">{{ $garment->name }}</h3>
                                <p class="text-xs text-zinc-500 mb-1 line-clamp-1">{{ $garment->description }}</p>
                                <p class="text-sm font-medium text-primary-600 dark:text-primary-400">Starting at ₱{{ number_format($garment->base_price, 2) }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-6 text-zinc-500">
                            No garments listed in their catalog yet.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Fabrics -->
            <div class="tc-card">
                <h2 class="text-xl font-bold font-heading mb-4">Fabric Selection</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @forelse($fabrics as $fabric)
                        <div class="p-4 rounded-xl border border-zinc-200 dark:border-zinc-700 flex gap-4 items-center">
                            <div class="w-10 h-10 rounded-full bg-secondary-100 dark:bg-secondary-900 flex items-center justify-center shrink-0">
                                <flux:icon.swatch class="size-5 text-secondary-600 dark:text-secondary-400" />
                            </div>
                            <div class="flex-1">
                                <h3 class="font-bold text-zinc-900 dark:text-white text-sm">{{ $fabric->name }}</h3>
                                <p class="text-xs text-zinc-500">{{ $fabric->material }} {{ $fabric->color ? '· '.$fabric->color : '' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium">₱{{ number_format($fabric->price_per_meter, 2) }}</p>
                                <p class="text-[10px] text-zinc-500 uppercase">per meter</p>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-6 text-zinc-500">
                            No fabrics listed in their catalog yet.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Sidebar / Contact -->
        <div class="space-y-6">
            <div class="tc-card bg-primary-50 dark:bg-zinc-800/50 border-primary-100 dark:border-zinc-700">
                <h3 class="font-bold font-heading mb-4">Contact Information</h3>
                <div class="space-y-4">
                    <div class="flex gap-3 text-sm">
                        <flux:icon.phone class="size-5 text-zinc-400 shrink-0" />
                        <div>
                            <p class="font-medium text-zinc-900 dark:text-white">Phone</p>
                            <p class="text-zinc-600 dark:text-zinc-400">{{ $shop->contact_number ?: 'Not provided' }}</p>
                        </div>
                    </div>
                    <div class="flex gap-3 text-sm">
                        <flux:icon.map class="size-5 text-zinc-400 shrink-0" />
                        <div>
                            <p class="font-medium text-zinc-900 dark:text-white">Barangay</p>
                            <p class="text-zinc-600 dark:text-zinc-400">{{ $shop->barangay ?: 'Not specified' }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="tc-card">
                <h3 class="font-bold font-heading mb-4">Ready for your fitting?</h3>
                <p class="text-sm text-zinc-500 mb-4">Book a consultation or a fitting session with this shop. Choose a time that works best for you.</p>
                <flux:button variant="primary" class="w-full" :href="route('customer.shop.book', $shop->id)" wire:navigate icon="calendar">Book Appointment</flux:button>
            </div>
        </div>
    </div>
</div>
