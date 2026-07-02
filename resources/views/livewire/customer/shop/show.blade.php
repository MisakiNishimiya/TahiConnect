<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Models\Shop;
use App\Models\GarmentType;
use App\Models\Fabric;
use App\Models\PreMadeProduct;
use App\Models\Order;

new #[Layout('components.layouts.app')] class extends Component {
    public Shop $shop;
    public $garments;
    public $fabrics;
    public $preMadeProducts;

    // Order Modal State
    public bool $showModal = false;
    public ?PreMadeProduct $selectedProduct = null;
    public string $selectedSize = '';
    public int $quantity = 1;

    public function mount(Shop $shop)
    {
        $this->shop = $shop;
        $this->garments = GarmentType::where('shop_id', $shop->id)->get();
        $this->fabrics = Fabric::where('shop_id', $shop->id)->get();
        $this->preMadeProducts = PreMadeProduct::where('shop_id', $shop->id)->where('is_active', true)->get();
    }

    public function selectProduct($productId)
    {
        $this->selectedProduct = PreMadeProduct::find($productId);
        if ($this->selectedProduct) {
            $this->selectedSize = collect($this->selectedProduct->available_sizes)->first() ?? '';
            $this->quantity = 1;
            $this->showModal = true;
        }
    }

    public function incrementQuantity()
    {
        $this->quantity++;
    }

    public function decrementQuantity()
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }

    public function createPreMadeOrder()
    {
        if (!$this->selectedProduct) {
            return;
        }

        $this->validate([
            'selectedSize' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (!in_array($value, $this->selectedProduct->available_sizes)) {
                        $fail('The selected size is not available for this product.');
                    }
                }
            ],
            'quantity' => 'required|integer|min:1',
        ]);

        // Verify the product belongs to the current shop
        if ($this->selectedProduct->shop_id !== $this->shop->id) {
            session()->flash('error', 'Invalid product selection.');
            return;
        }

        Order::create([
            'user_id' => auth()->id(),
            'shop_id' => $this->shop->id,
            'tracking_number' => 'TC-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
            'order_type' => 'pre_made',
            'pre_made_product_id' => $this->selectedProduct->id,
            'product_size' => $this->selectedSize,
            'garment_type_id' => null,
            'fabric_preference' => null,
            'quantity' => $this->quantity,
            'total_amount' => $this->selectedProduct->price * $this->quantity,
            'status' => 'pending',
            'estimated_completion' => now()->addDays(3)->format('Y-m-d'),
        ]);

        $this->showModal = false;
        $this->selectedProduct = null;
        $this->selectedSize = '';
        $this->quantity = 1;

        session()->flash('message', 'Pre-made product ordered successfully!');
        return $this->redirect(route('customer.orders'), navigate: true);
    }
}; ?>

<div class="space-y-8">
    <!-- Back Button -->
    <div class="flex items-center gap-3">
        <button 
            onclick="window.history.back()" 
            class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-zinc-600 dark:text-zinc-400 hover:text-primary-600 dark:hover:text-primary-400 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-all duration-300 click-feedback"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Shops
        </button>
    </div>

    <!-- Enhanced Shop Hero -->
    <div class="tc-card overflow-hidden relative bg-gradient-to-br from-white to-cream-50 dark:from-zinc-800 dark:to-zinc-900">
        <div class="absolute inset-0 bg-gradient-to-r from-primary-500/5 to-secondary-500/5"></div>
        <div class="relative z-10 flex flex-col lg:flex-row gap-8 items-start lg:items-center">
            <div class="w-28 h-28 rounded-3xl bg-gradient-to-br from-primary-100 to-secondary-100 dark:from-primary-800 dark:to-secondary-800 flex items-center justify-center shrink-0 border-4 border-white dark:border-zinc-700 shadow-xl hover:scale-105 transition-transform duration-300">
                <svg class="w-14 h-14 text-primary-600 dark:text-primary-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 019.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.001 3.001 0 01-.621-1.875L21.75 3A3.001 3.001 0 0018 0H6a3.001 3.001 0 00-3.75 3l.621 17.25z"/>
                </svg>
            </div>
            <div class="flex-1">
                <div class="flex flex-col sm:flex-row sm:items-center gap-4 mb-4">
                    <h1 class="text-4xl font-bold font-heading text-primary-700 dark:text-primary-300">{{ $shop->name }}</h1>
                    @if($shop->is_featured)
                        <span class="inline-flex items-center px-3 py-1.5 text-sm font-semibold text-yellow-700 bg-gradient-to-r from-yellow-100 to-yellow-200 dark:from-yellow-900/30 dark:to-yellow-800/30 border border-yellow-300 dark:border-yellow-700 rounded-full">
                            <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                            Featured Shop
                        </span>
                    @endif
                </div>
                
                <!-- Enhanced Shop Info -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                    <div class="flex items-center gap-3 p-3 bg-white/50 dark:bg-zinc-800/50 rounded-xl border border-zinc-200/50 dark:border-zinc-700/50">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Location</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $shop->barangay ?? 'Not specified' }}</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-3 p-3 bg-white/50 dark:bg-zinc-800/50 rounded-xl border border-zinc-200/50 dark:border-zinc-700/50">
                        <div class="w-10 h-10 rounded-xl bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center">
                            <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $shop->rating ?? '0.0' }} Rating</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">({{ $shop->total_reviews ?? 0 }} reviews)</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-3 p-3 bg-white/50 dark:bg-zinc-800/50 rounded-xl border border-zinc-200/50 dark:border-zinc-700/50">
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Fast Service</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Quick turnaround</p>
                        </div>
                    </div>
                </div>
                
                <!-- Specialties -->
                <div class="flex flex-wrap gap-2 mb-6">
                    @if(is_array($shop->specialties))
                        @foreach($shop->specialties as $specialty)
                            <span class="px-3 py-1.5 text-sm font-medium bg-gradient-to-r from-primary-100 to-primary-200 dark:from-primary-900/30 dark:to-primary-800/20 text-primary-700 dark:text-primary-300 border border-primary-200 dark:border-primary-800 rounded-full">
                                {{ $specialty }}
                            </span>
                        @endforeach
                    @endif
                </div>
            </div>
            
            <!-- Action Button -->
            <div class="w-full lg:w-auto">
                <button 
                    onclick="window.location.href='{{ route('customer.shop.book', $shop->id) }}'"
                    class="w-full lg:w-auto px-8 py-4 bg-gradient-to-r from-primary-500 to-primary-600 text-white font-semibold rounded-2xl hover:from-primary-600 hover:to-primary-700 transition-all duration-300 shadow-lg hover:shadow-xl hover:shadow-primary-500/25 click-feedback flex items-center justify-center gap-3"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m1 5v6a2 2 0 01-2 2H7a2 2 0 01-2-2v-6m1-5V4a1 1 0 011-1h10a1 1 0 011 1v4a1 1 0 01-1 1H7a1 1 0 01-1-1z"/>
                    </svg>
                    Book Appointment
                </button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Enhanced Description -->
            <div class="tc-card bg-gradient-to-br from-cream-50 to-white dark:from-zinc-800 dark:to-zinc-900">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-100 to-blue-200 dark:from-blue-900/30 dark:to-blue-800/30 flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold font-heading text-zinc-900 dark:text-white">About {{ $shop->name }}</h2>
                </div>
                <div class="prose prose-zinc dark:prose-invert max-w-none">
                    <p class="text-zinc-600 dark:text-zinc-400 leading-relaxed">
                        {{ $shop->description ?: 'This shop is dedicated to providing quality tailoring services with attention to detail and customer satisfaction. We specialize in custom garments that fit perfectly and reflect your personal style.' }}
                    </p>
                </div>
            </div>

            <!-- Enhanced Garment Types -->
            <div class="tc-card">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-100 to-emerald-200 dark:from-emerald-900/30 dark:to-emerald-800/30 flex items-center justify-center">
                            <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold font-heading text-zinc-900 dark:text-white">Custom Garments Available</h2>
                    </div>
                    <span class="px-3 py-1.5 text-sm font-medium bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 rounded-full">
                        {{ $garments->count() }} Types
                    </span>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    @forelse($garments as $garment)
                        <div class="p-6 rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-gradient-to-br from-zinc-50 to-white dark:from-zinc-800/50 dark:to-zinc-900/50 hover:shadow-md hover:-translate-y-1 transition-all duration-300 group">
                            <div class="flex gap-4 items-start">
                                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-primary-100 to-primary-200 dark:from-primary-900/30 dark:to-primary-800/30 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform duration-300">
                                    <svg class="w-7 h-7 text-primary-600 dark:text-primary-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-bold text-zinc-900 dark:text-white mb-2">{{ $garment->name }}</h3>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-3 line-clamp-2">{{ $garment->description ?: 'Custom tailored to your measurements and preferences.' }}</p>
                                    <div class="flex items-center justify-between">
                                        <p class="text-lg font-bold text-primary-600 dark:text-primary-400">₱{{ number_format($garment->base_price, 2) }}</p>
                                        <span class="text-xs text-zinc-500 dark:text-zinc-400 bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded-full">Starting price</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full">
                            <x-enhanced-empty-state
                                icon="sparkles"
                                title="No custom garments listed"
                                description="This shop hasn't added their custom garment types yet. Contact them directly to discuss your custom tailoring needs."
                                :actions="[
                                    ['type' => 'primary', 'label' => 'Book Consultation', 'onclick' => 'window.location.href=\"' . route('customer.shop.book', $shop->id) . '\"']
                                ]"
                            />
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Enhanced Pre-made Products -->
            <div class="tc-card">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-purple-100 to-purple-200 dark:from-purple-900/30 dark:to-purple-800/30 flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold font-heading text-zinc-900 dark:text-white">Ready-to-Wear Collection</h2>
                    </div>
                    <span class="px-3 py-1.5 text-sm font-medium bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 rounded-full">
                        {{ $preMadeProducts->count() }} Items
                    </span>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    @forelse($preMadeProducts as $product)
                        <div class="p-6 rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-gradient-to-br from-zinc-50 to-white dark:from-zinc-800/30 dark:to-zinc-900/30 flex flex-col justify-between h-full hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group">
                            <div>
                                <div class="flex justify-between items-start mb-4">
                                    <h3 class="font-bold text-zinc-900 dark:text-white text-lg group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">{{ $product->name }}</h3>
                                    <span class="text-xl font-bold text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/30 px-3 py-1 rounded-xl">₱{{ number_format($product->price, 2) }}</span>
                                </div>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 line-clamp-3">{{ $product->description ?: 'High-quality ready-to-wear garment available in multiple sizes.' }}</p>
                                
                                <div class="mb-6">
                                    <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wide mb-2 block">Available Sizes:</span>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($product->available_sizes as $size)
                                            <span class="px-3 py-1.5 text-sm font-semibold bg-gradient-to-r from-primary-50 to-primary-100 dark:from-primary-950/30 dark:to-primary-900/30 text-primary-700 dark:text-primary-300 rounded-xl border border-primary-200 dark:border-primary-800 hover:scale-105 transition-transform duration-200">{{ $size }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            
                            <button 
                                wire:click="selectProduct({{ $product->id }})"
                                class="w-full py-3 px-6 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-semibold rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl hover:shadow-primary-500/25 click-feedback flex items-center justify-center gap-2"
                            >
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                                Order Now
                            </button>
                        </div>
                    @empty
                        <div class="col-span-full">
                            <x-enhanced-empty-state
                                icon="orders"
                                title="No ready-to-wear items"
                                description="This shop focuses on custom tailoring. Book an appointment to discuss your custom garment needs."
                                :actions="[
                                    ['type' => 'primary', 'label' => 'Book Custom Order', 'onclick' => 'window.location.href=\"' . route('customer.shop.book', $shop->id) . '\"']
                                ]"
                            />
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Enhanced Fabrics -->
            <div class="tc-card">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-100 to-amber-200 dark:from-amber-900/30 dark:to-amber-800/30 flex items-center justify-center">
                            <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zM7 21h10a2 2 0 002-2v-4a2 2 0 00-2-2H7M7 21V9a2 2 0 012-2h10a2 2 0 012 2v4M7 21l4-4H7v4z"/>
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold font-heading text-zinc-900 dark:text-white">Premium Fabric Selection</h2>
                    </div>
                    <span class="px-3 py-1.5 text-sm font-medium bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 rounded-full">
                        {{ $fabrics->count() }} Options
                    </span>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @forelse($fabrics as $fabric)
                        <div class="p-5 rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-gradient-to-br from-white to-zinc-50 dark:from-zinc-800/50 dark:to-zinc-900/50 hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 group">
                            <div class="flex gap-4 items-center">
                                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-secondary-100 to-secondary-200 dark:from-secondary-900/30 dark:to-secondary-800/30 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform duration-300">
                                    <svg class="w-6 h-6 text-secondary-600 dark:text-secondary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zM7 21h10a2 2 0 002-2v-4a2 2 0 00-2-2H7"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-bold text-zinc-900 dark:text-white mb-1">{{ $fabric->name }}</h3>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-2">
                                        {{ $fabric->material }}{{ $fabric->color ? ' • ' . $fabric->color : '' }}
                                    </p>
                                    <div class="flex items-center justify-between">
                                        <span class="text-lg font-bold text-secondary-600 dark:text-secondary-400">₱{{ number_format($fabric->price_per_meter, 2) }}</span>
                                        <span class="text-xs text-zinc-500 dark:text-zinc-400 bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded-full">per meter</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full">
                            <x-enhanced-empty-state
                                icon="sparkles"
                                title="Fabric selection coming soon"
                                description="The shop owner is updating their fabric catalog. Contact them directly to see available materials."
                                :actions="[
                                    ['type' => 'secondary', 'label' => 'Contact Shop', 'onclick' => 'window.location.href=\"' . route('customer.shop.book', $shop->id) . '\"']
                                ]"
                            />
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Enhanced Sidebar -->
        <div class="space-y-8">
            <!-- Contact Information -->
            <div class="tc-card bg-gradient-to-br from-primary-50 to-secondary-50 dark:from-zinc-800/50 dark:to-zinc-900/50 border-primary-100 dark:border-zinc-700">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold font-heading text-zinc-900 dark:text-white">Contact Information</h3>
                </div>
                
                <div class="space-y-6">
                    <div class="flex gap-4">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-zinc-900 dark:text-white mb-1">Phone Number</p>
                            <p class="text-zinc-600 dark:text-zinc-400">{{ $shop->contact_number ?: 'Contact via appointment' }}</p>
                        </div>
                    </div>
                    
                    <div class="flex gap-4">
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-zinc-900 dark:text-white mb-1">Location</p>
                            <p class="text-zinc-600 dark:text-zinc-400">
                                {{ $shop->address ?: 'Address not provided' }}<br>
                                <span class="text-sm">{{ $shop->barangay ?: 'Location not specified' }}</span>
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex gap-4">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-zinc-900 dark:text-white mb-1">Business Hours</p>
                            <p class="text-zinc-600 dark:text-zinc-400 text-sm">
                                Monday - Saturday<br>
                                <span class="text-xs">9:00 AM - 6:00 PM</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Book Appointment CTA -->
            <div class="tc-card bg-gradient-to-br from-primary-500 to-primary-600 text-white border-0">
                <div class="text-center">
                    <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m1 5v6a2 2 0 01-2 2H7a2 2 0 01-2-2v-6m1-5V4a1 1 0 011-1h10a1 1 0 011 1v4a1 1 0 01-1 1H7a1 1 0 01-1-1z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold font-heading mb-2 text-xl">Ready for your fitting?</h3>
                    <p class="text-primary-100 mb-6 text-sm leading-relaxed">Book a consultation or fitting session with {{ $shop->name }}. Choose a time that works best for you.</p>
                    <button 
                        onclick="window.location.href='{{ route('customer.shop.book', $shop->id) }}'"
                        class="w-full py-3 px-6 bg-white text-primary-600 font-semibold rounded-xl hover:bg-primary-50 transition-colors click-feedback flex items-center justify-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m1 5v6a2 2 0 01-2 2H7a2 2 0 01-2-2v-6m1-5V4a1 1 0 011-1h10a1 1 0 011 1v4a1 1 0 01-1 1H7a1 1 0 01-1-1z"/>
                        </svg>
                        Book Appointment
                    </button>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="tc-card">
                <h3 class="font-bold font-heading mb-4 text-zinc-900 dark:text-white">Quick Stats</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between py-2">
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">Custom Garments</span>
                        <span class="font-semibold text-zinc-900 dark:text-white">{{ $garments->count() }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">Ready-to-Wear</span>
                        <span class="font-semibold text-zinc-900 dark:text-white">{{ $preMadeProducts->count() }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">Fabric Options</span>
                        <span class="font-semibold text-zinc-900 dark:text-white">{{ $fabrics->count() }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 pt-4 border-t border-zinc-100 dark:border-zinc-700">
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">Overall Rating</span>
                        <div class="flex items-center gap-1">
                            <svg class="w-4 h-4 text-yellow-500" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                            <span class="font-semibold text-zinc-900 dark:text-white">{{ $shop->rating ?? '4.5' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pre-made Order Modal -->
    @if($showModal && $selectedProduct)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 overflow-y-auto">
        <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl w-full max-w-md flex flex-col my-auto" @click.stop>
            <!-- Header -->
            <div class="px-6 py-4 border-b border-zinc-100 dark:border-zinc-800 flex items-center justify-between">
                <h3 class="text-lg font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">Order Product</h3>
                <button wire:click="$set('showModal', false)" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            
            <!-- Body -->
            <div class="px-6 py-6 space-y-4">
                <div>
                    <h4 class="font-bold text-zinc-900 dark:text-white">{{ $selectedProduct->name }}</h4>
                    <p class="text-xs text-zinc-500 mt-1">{{ $selectedProduct->description }}</p>
                    <p class="text-sm font-semibold text-primary-600 dark:text-primary-400 mt-2">₱{{ number_format($selectedProduct->price, 2) }}</p>
                </div>
                
                <!-- Size Select -->
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Select Size</label>
                    <select wire:model="selectedSize" class="w-full rounded-lg border border-zinc-300 dark:border-zinc-750 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white p-2.5 text-sm">
                        @foreach($selectedProduct->available_sizes as $size)
                            <option value="{{ $size }}">{{ $size }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Quantity Selector -->
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Quantity</label>
                    <div class="flex items-center gap-3">
                        <button type="button" wire:click="decrementQuantity" class="w-10 h-10 rounded-lg border border-zinc-200 dark:border-zinc-700 flex items-center justify-center hover:bg-zinc-50 dark:hover:bg-zinc-850">
                            <flux:icon.minus class="size-4 text-zinc-500" />
                        </button>
                        <span class="w-8 text-center font-bold text-zinc-900 dark:text-white">{{ $quantity }}</span>
                        <button type="button" wire:click="incrementQuantity" class="w-10 h-10 rounded-lg border border-zinc-200 dark:border-zinc-700 flex items-center justify-center hover:bg-zinc-50 dark:hover:bg-zinc-850">
                            <flux:icon.plus class="size-4 text-zinc-500" />
                        </button>
                    </div>
                </div>
                
                <!-- Summary Price -->
                <div class="p-3 bg-zinc-50 dark:bg-zinc-800/40 rounded-xl flex justify-between items-center text-sm">
                    <span class="text-zinc-500">Total Price:</span>
                    <span class="font-bold text-zinc-900 dark:text-white">₱{{ number_format($selectedProduct->price * $quantity, 2) }}</span>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="px-6 py-4 bg-zinc-50 dark:bg-zinc-800/50 border-t border-zinc-100 dark:border-zinc-800 flex justify-end gap-3 rounded-b-2xl">
                <flux:button variant="subtle" wire:click="$set('showModal', false)">Cancel</flux:button>
                <flux:button variant="primary" class="!bg-primary-500 hover:!bg-primary-600" wire:click="createPreMadeOrder">Confirm Order</flux:button>
            </div>
        </div>
    </div>
    @endif
</div>
