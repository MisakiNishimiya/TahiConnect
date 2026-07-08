<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Models\Shop;
use App\Models\PreMadeProduct;

new #[Layout('components.layouts.app')] class extends Component {
    public string $search     = '';
    public string $sizeFilter = '';

    public function with(): array
    {
        $shop = Shop::instance();
        $products = PreMadeProduct::where('is_active', true)
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")->orWhere('description', 'like', "%{$this->search}%"))
            ->get();

        // Collect all unique sizes across products
        $allSizes = $products->flatMap(fn($p) => $p->available_sizes)->unique()->sort()->values();

        if ($this->sizeFilter) {
            $products = $products->filter(fn($p) => in_array($this->sizeFilter, $p->available_sizes));
        }

        return [
            'shop'        => $shop,
            'products'    => $products,
            'allSizes'    => $allSizes,
            'totalCount'  => PreMadeProduct::where('is_active', true)->count(),
        ];
    }
}; ?>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-primary-600 dark:text-primary-400" style="font-family: 'Poppins';">Our Catalog</h1>
            <p class="text-zinc-500 dark:text-zinc-400 mt-1">Browse our ready-to-wear collection — available for immediate order</p>
        </div>
        <a href="{{ route('customer.rates') }}" wire:navigate
            class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-primary-600 bg-primary-50 hover:bg-primary-100 border border-primary-200 rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            View Rates & Fabrics
        </a>
    </div>

    <!-- Info Banner -->
    <div class="p-4 bg-blue-50 border border-blue-100 rounded-xl flex items-start gap-3">
        <svg class="w-5 h-5 text-blue-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <div class="flex-1">
            <p class="text-sm font-medium text-blue-700">Want a custom-made garment instead?</p>
            <p class="text-xs text-blue-600 mt-0.5">
                For custom orders tailored to your measurements, go to
                <a href="{{ route('customer.orders') }}" wire:navigate class="font-semibold underline hover:text-blue-800">My Orders → New Order</a>.
                Check our <a href="{{ route('customer.rates') }}" wire:navigate class="font-semibold underline hover:text-blue-800">Rates page</a> for pricing on custom garments and fabrics.
            </p>
        </div>
    </div>

    <!-- Search + Size Filter -->
    <div class="tc-card !p-4 flex flex-col sm:flex-row gap-3">
        <div class="flex-1 relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input wire:model.live.debounce.300ms="search" placeholder="Search ready-to-wear items..."
                class="w-full pl-10 pr-4 py-2.5 border border-zinc-200 rounded-xl bg-white text-sm focus:ring-2 focus:ring-primary-500 transition-colors" />
        </div>
        @if($allSizes->count())
        <select wire:model.live="sizeFilter" class="px-4 py-2.5 border border-zinc-200 rounded-xl bg-white text-sm focus:ring-2 focus:ring-primary-500">
            <option value="">All Sizes</option>
            @foreach($allSizes as $size)
                <option value="{{ $size }}">Size {{ $size }}</option>
            @endforeach
        </select>
        @endif
        <div class="flex items-center gap-2 text-sm text-zinc-500 shrink-0">
            <span class="px-3 py-1 bg-zinc-100 rounded-full font-medium text-zinc-600">{{ $products->count() }} items</span>
        </div>
    </div>

    <!-- Products Grid -->
    @if($products->count())
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($products as $product)
        <div class="tc-card hover-lift group animate-fade-in-up" style="--stagger-index: {{ $loop->index }}">
            <!-- Product Image Placeholder -->
            <div class="w-full h-44 rounded-2xl bg-gradient-to-br from-primary-50 to-secondary-50 border border-primary-100 flex items-center justify-center mb-4 overflow-hidden group-hover:from-primary-100 group-hover:to-secondary-100 transition-all duration-300">
                @if($product->image_url)
                    <img src="{{ Storage::url($product->image_url) }}" alt="{{ $product->name }}" class="w-full h-full object-cover rounded-2xl">
                @else
                    <div class="text-center">
                        <svg class="w-12 h-12 text-primary-300 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        <p class="text-xs text-primary-400 font-medium">Ready-to-Wear</p>
                    </div>
                @endif
            </div>

            <!-- Product Info -->
            <div class="flex items-start justify-between mb-2">
                <h3 class="font-bold text-zinc-900 text-base leading-tight">{{ $product->name }}</h3>
                <span class="text-xl font-bold text-primary-600 shrink-0 ml-2">₱{{ number_format($product->price, 2) }}</span>
            </div>

            @if($product->description)
                <p class="text-xs text-zinc-500 mb-3 leading-relaxed">{{ Str::limit($product->description, 80) }}</p>
            @endif

            <!-- Sizes -->
            <div class="mb-4">
                <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-2">Available Sizes</p>
                <div class="flex flex-wrap gap-1.5">
                    @foreach($product->available_sizes as $size)
                        <span class="px-2.5 py-1 text-xs font-semibold bg-zinc-100 text-zinc-700 rounded-lg border border-zinc-200 hover:bg-primary-50 hover:border-primary-200 hover:text-primary-700 transition-colors">{{ $size }}</span>
                    @endforeach
                </div>
            </div>

            <!-- Order Button -->
            <a href="{{ route('customer.orders') }}" wire:navigate
                class="block w-full py-2.5 text-center text-sm font-semibold bg-gradient-to-r from-primary-500 to-primary-600 text-white rounded-xl hover:from-primary-600 hover:to-primary-700 transition-all shadow-sm hover:shadow-md hover:shadow-primary-500/20 click-feedback">
                Order Now
            </a>
        </div>
        @endforeach
    </div>
    @else
    <div class="tc-card py-16 text-center">
        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-zinc-100 flex items-center justify-center">
            <svg class="w-8 h-8 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
        </div>
        <h3 class="text-base font-semibold text-zinc-700 mb-1">No items found</h3>
        <p class="text-sm text-zinc-500">
            @if($search || $sizeFilter)
                Try adjusting your search or size filter.
            @else
                No ready-to-wear items are available yet. Check back soon!
            @endif
        </p>
        @if($search || $sizeFilter)
            <button wire:click="$set('search', ''); $set('sizeFilter', '')" class="mt-4 px-4 py-2 text-sm text-primary-600 bg-primary-50 hover:bg-primary-100 rounded-xl transition-colors">
                Clear Filters
            </button>
        @endif
    </div>
    @endif
</div>
