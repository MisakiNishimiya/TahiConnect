<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Models\Shop;
use App\Models\PreMadeProduct;
use App\Models\ShopReview;
use App\Models\Order;

new #[Layout('components.layouts.app')] class extends Component {
    public string $search     = '';
    public string $sizeFilter = '';

    // Review form
    public bool $showReviewModal = false;
    public int $reviewRating     = 5;
    public string $reviewComment = '';
    public ?int $reviewOrderId   = null;

    public function submitReview(): void
    {
        $this->validate([
            'reviewRating'  => 'required|integer|min:1|max:5',
            'reviewComment' => 'nullable|string|max:1000',
            'reviewOrderId' => 'nullable|exists:orders,id',
        ]);

        $shop = Shop::instance();

        ShopReview::updateOrCreate(
            ['shop_id' => $shop->id, 'user_id' => auth()->id(), 'order_id' => $this->reviewOrderId],
            ['rating' => $this->reviewRating, 'comment' => $this->reviewComment]
        );

        $this->showReviewModal = false;
        $this->reset(['reviewRating', 'reviewComment', 'reviewOrderId']);
        session()->flash('review_message', 'Thank you for your review!');
    }

    public function with(): array
    {
        $shop = Shop::with('reviews.user')->instance();
        $products = PreMadeProduct::where('is_active', true)
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")->orWhere('description', 'like', "%{$this->search}%"))
            ->get();

        $allSizes = $products->flatMap(fn($p) => $p->available_sizes)->unique()->sort()->values();

        if ($this->sizeFilter) {
            $products = $products->filter(fn($p) => in_array($this->sizeFilter, $p->available_sizes));
        }

        // Reviews
        $reviews     = ShopReview::with('user')->where('shop_id', $shop->id)->latest()->get();
        $avgRating   = $reviews->avg('rating') ?? 0;
        $reviewCount = $reviews->count();

        // Completed orders eligible for review
        $reviewableOrders = Order::where('user_id', auth()->id())
            ->whereIn('status', ['completed', 'released'])
            ->whereDoesntHave('reviews', fn($q) => $q->where('user_id', auth()->id()))
            ->select('id', 'tracking_number')
            ->latest()->get();

        $userReview = ShopReview::where('shop_id', $shop->id)
            ->where('user_id', auth()->id())->first();

        return [
            'shop'            => $shop,
            'products'        => $products,
            'allSizes'        => $allSizes,
            'totalCount'      => PreMadeProduct::where('is_active', true)->count(),
            'reviews'         => $reviews->take(5),
            'avgRating'       => round($avgRating, 1),
            'reviewCount'     => $reviewCount,
            'reviewableOrders'=> $reviewableOrders,
            'userReview'      => $userReview,
        ];
    }
}; ?>

<div class="space-y-6">
    <!-- Shop Info Banner -->
    <div class="tc-card bg-gradient-to-br from-primary-600 to-primary-800 text-white border-0 overflow-hidden relative">
        <div class="absolute inset-0 opacity-5"><svg class="w-full h-full" viewBox="0 0 200 100" preserveAspectRatio="none"><circle cx="160" cy="20" r="90" fill="white"/></svg></div>
        <div class="relative z-10 flex flex-col sm:flex-row items-start sm:items-center gap-5">
            <!-- Logo -->
            <div class="w-20 h-20 rounded-2xl bg-white/20 border-2 border-white/30 flex items-center justify-center shrink-0 overflow-hidden">
                @if($shop->logo_url)
                    <img src="{{ Storage::url($shop->logo_url) }}" alt="{{ $shop->name }}" class="w-full h-full object-cover">
                @else
                    <svg class="w-10 h-10 text-white/80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349"/></svg>
                @endif
            </div>
            <!-- Info -->
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-1">
                    <h2 class="text-2xl font-bold text-white" style="font-family:'Poppins'">{{ $shop->name }}</h2>
                    @if($shop->is_verified)
                        <span class="flex items-center gap-1 px-2 py-0.5 bg-white/20 rounded-full text-xs font-medium text-white border border-white/30">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                            Verified
                        </span>
                    @endif
                </div>
                @if($shop->description)
                    <p class="text-primary-100 text-sm mb-3 max-w-xl">{{ $shop->description }}</p>
                @endif
                <div class="flex flex-wrap gap-4 text-sm text-primary-200">
                    @if($shop->address)
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            {{ $shop->address }}{{ $shop->barangay ? ', '.$shop->barangay : '' }}
                        </span>
                    @endif
                    @if($shop->contact_number)
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            {{ $shop->contact_number }}
                        </span>
                    @endif
                </div>
                @if($shop->specialties && count($shop->specialties))
                    <div class="flex flex-wrap gap-1.5 mt-3">
                        @foreach($shop->specialties as $specialty)
                            <span class="px-2 py-0.5 bg-white/15 text-white text-xs rounded-full border border-white/20">{{ $specialty }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
            <!-- Rating Summary -->
            @if($reviewCount > 0)
            <div class="shrink-0 text-center bg-white/10 rounded-2xl px-5 py-4 border border-white/20">
                <p class="text-3xl font-bold text-white">{{ $avgRating }}</p>
                <div class="flex justify-center gap-0.5 my-1">
                    @for($i = 1; $i <= 5; $i++)
                        <svg class="w-4 h-4 {{ $i <= round($avgRating) ? 'text-amber-300' : 'text-white/30' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    @endfor
                </div>
                <p class="text-xs text-primary-200">{{ $reviewCount }} {{ Str::plural('review', $reviewCount) }}</p>
            </div>
            @endif
        </div>
    </div>

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

    @if (session()->has('review_message'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('review_message') }}
        </div>
    @endif

    <!-- Customer Reviews Section -->
    <div class="tc-card">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h2 class="text-lg font-bold text-zinc-900 dark:text-white" style="font-family:'Poppins'">Customer Reviews</h2>
                @if($reviewCount > 0)
                    <p class="text-sm text-zinc-500">{{ $avgRating }} / 5 · {{ $reviewCount }} {{ Str::plural('review', $reviewCount) }}</p>
                @else
                    <p class="text-sm text-zinc-500">No reviews yet — be the first!</p>
                @endif
            </div>
            @if($reviewableOrders->count() && !$userReview)
                <button wire:click="$set('showReviewModal', true)"
                    class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-xl transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                    Write a Review
                </button>
            @endif
        </div>

        @if($reviews->count())
            <div class="space-y-4">
                @foreach($reviews as $review)
                    <div class="flex gap-4 p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/50">
                        <div class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-sm font-bold text-primary-700 dark:text-primary-300 shrink-0">
                            {{ strtoupper(substr($review->user?->name ?? 'A', 0, 1)) }}
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-semibold text-sm text-zinc-900 dark:text-white">{{ $review->user?->name ?? 'Anonymous' }}</span>
                                <span class="text-xs text-zinc-400">{{ $review->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="flex gap-0.5 mb-2">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-4 h-4 {{ $i <= $review->rating ? 'text-amber-400' : 'text-zinc-200 dark:text-zinc-600' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                @endfor
                            </div>
                            @if($review->comment)
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $review->comment }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <svg class="w-12 h-12 text-zinc-200 dark:text-zinc-700 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                <p class="text-zinc-400 text-sm">No reviews yet. Complete an order to leave one!</p>
            </div>
        @endif
    </div>

    <!-- Review Modal -->
    @if($showReviewModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
        <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl w-full max-w-md" @click.stop>
            <div class="p-6">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-lg font-bold text-zinc-900 dark:text-white">Write a Review</h3>
                    <button wire:click="$set('showReviewModal', false)" class="p-1.5 text-zinc-400 hover:text-zinc-600 rounded-lg">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Star Rating -->
                <div class="mb-5">
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Your Rating</label>
                    <div class="flex gap-1" x-data="{ hovered: 0 }">
                        @for($i = 1; $i <= 5; $i++)
                            <button type="button" wire:click="$set('reviewRating', {{ $i }})"
                                @mouseenter="hovered = {{ $i }}" @mouseleave="hovered = 0"
                                class="transition-transform hover:scale-110">
                                <svg class="w-8 h-8 {{ $i <= $reviewRating ? 'text-amber-400' : 'text-zinc-200 dark:text-zinc-600' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </button>
                        @endfor
                    </div>
                </div>

                <!-- Link to Order -->
                @if($reviewableOrders->count())
                <div class="mb-4">
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Related Order (optional)</label>
                    <select wire:model="reviewOrderId" class="w-full px-4 py-2.5 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-sm focus:ring-2 focus:ring-primary-500">
                        <option value="">No specific order</option>
                        @foreach($reviewableOrders as $ord)
                            <option value="{{ $ord->id }}">{{ $ord->tracking_number }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <!-- Comment -->
                <div class="mb-5">
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Comment (optional)</label>
                    <textarea wire:model="reviewComment" rows="3" placeholder="Share your experience..."
                        class="w-full px-4 py-3 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-sm focus:ring-2 focus:ring-primary-500 resize-none"></textarea>
                </div>

                <div class="flex gap-3">
                    <button wire:click="$set('showReviewModal', false)"
                        class="flex-1 px-4 py-3 text-sm font-medium text-zinc-600 bg-zinc-100 dark:bg-zinc-800 rounded-xl hover:bg-zinc-200 transition-colors">
                        Cancel
                    </button>
                    <button wire:click="submitReview"
                        class="flex-1 px-4 py-3 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-xl transition-colors">
                        Submit Review
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
