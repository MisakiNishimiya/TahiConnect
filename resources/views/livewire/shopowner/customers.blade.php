<?php

use App\Models\User;
use App\Models\Order;
use App\Models\Measurement;
use App\Models\ShopReview;
use App\Models\Shop;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('components.layouts.app')] class extends Component {
    use WithPagination;

    public string $search    = '';
    public string $activeTab = 'customers';

    public function with(): array
    {
        $shop = Shop::instance();

        return [
            'customers' => User::where('role', 'customer')
                ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%"))
                ->withCount('orders')
                ->latest()
                ->paginate(15),
            'totalCustomers' => User::where('role', 'customer')->count(),
            'reviews'        => ShopReview::with(['user', 'order'])
                ->where('shop_id', $shop->id)
                ->latest()
                ->get(),
            'avgRating'      => ShopReview::where('shop_id', $shop->id)->avg('rating') ?? 0,
            'reviewCount'    => ShopReview::where('shop_id', $shop->id)->count(),
            'ratingDist'     => collect([5,4,3,2,1])->mapWithKeys(fn($r) => [
                $r => ShopReview::where('shop_id', $shop->id)->where('rating', $r)->count()
            ]),
        ];
    }

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedActiveTab(): void { $this->resetPage(); }
}; ?>

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-primary-600 dark:text-primary-400" style="font-family: 'Poppins';">Customers</h1>
            <p class="text-zinc-500 mt-1">{{ $totalCustomers }} registered customers</p>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="tc-card !p-0 overflow-hidden">
        <div class="flex border-b border-zinc-100 dark:border-zinc-700">
            <button wire:click="$set('activeTab','customers')"
                class="flex-1 px-6 py-4 text-sm font-medium transition-all relative {{ $activeTab === 'customers' ? 'text-primary-600 bg-primary-50 dark:bg-primary-900/20' : 'text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800/50' }}">
                <span class="flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                    Customers
                    <span class="px-2 py-0.5 text-xs rounded-full {{ $activeTab === 'customers' ? 'bg-primary-200 dark:bg-primary-800 text-primary-800 dark:text-primary-200' : 'bg-zinc-100 dark:bg-zinc-700 text-zinc-600' }}">{{ $totalCustomers }}</span>
                </span>
                @if($activeTab === 'customers')<div class="absolute bottom-0 left-0 right-0 h-0.5 bg-primary-500"></div>@endif
            </button>
            <button wire:click="$set('activeTab','reviews')"
                class="flex-1 px-6 py-4 text-sm font-medium transition-all relative {{ $activeTab === 'reviews' ? 'text-primary-600 bg-primary-50 dark:bg-primary-900/20' : 'text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800/50' }}">
                <span class="flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                    Reviews
                    @if($reviewCount > 0)
                        <span class="px-2 py-0.5 text-xs rounded-full {{ $activeTab === 'reviews' ? 'bg-primary-200 dark:bg-primary-800 text-primary-800 dark:text-primary-200' : 'bg-zinc-100 dark:bg-zinc-700 text-zinc-600' }}">{{ $reviewCount }}</span>
                    @endif
                </span>
                @if($activeTab === 'reviews')<div class="absolute bottom-0 left-0 right-0 h-0.5 bg-primary-500"></div>@endif
            </button>
        </div>
    </div>

    {{-- Customers Tab --}}
    @if($activeTab === 'customers')
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
                        @if($customer->contact_number)
                            <p class="text-xs text-zinc-400">{{ $customer->contact_number }}</p>
                        @endif
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3 pt-4 border-t border-zinc-100 dark:border-zinc-700">
                    <div class="text-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl">
                        <p class="text-xl font-bold text-blue-700 dark:text-blue-300">{{ $customer->orders_count }}</p>
                        <p class="text-xs text-blue-600 dark:text-blue-400 font-medium">Orders</p>
                    </div>
                    <div class="text-center p-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-xl">
                        <p class="text-xs font-medium text-zinc-600 dark:text-zinc-400">Joined</p>
                        <p class="text-xs text-zinc-500 mt-1">{{ $customer->created_at->format('M Y') }}</p>
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
    @endif

    {{-- Reviews Tab --}}
    @if($activeTab === 'reviews')
    {{-- Rating Summary --}}
    <div class="tc-card">
        <div class="flex flex-col sm:flex-row gap-6 items-start sm:items-center">
            <div class="text-center shrink-0">
                <p class="text-5xl font-bold text-zinc-900 dark:text-white" style="font-family:'Poppins'">{{ number_format($avgRating, 1) }}</p>
                <div class="flex justify-center gap-0.5 my-2">
                    @for($i = 1; $i <= 5; $i++)
                        <svg class="w-5 h-5 {{ $i <= round($avgRating) ? 'text-amber-400' : 'text-zinc-200 dark:text-zinc-600' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    @endfor
                </div>
                <p class="text-sm text-zinc-500">{{ $reviewCount }} {{ Str::plural('review', $reviewCount) }}</p>
            </div>
            {{-- Rating Breakdown --}}
            <div class="flex-1 space-y-2 w-full">
                @foreach($ratingDist as $stars => $count)
                    @php $pct = $reviewCount > 0 ? ($count / $reviewCount) * 100 : 0; @endphp
                    <div class="flex items-center gap-3">
                        <span class="text-xs text-zinc-500 w-4 shrink-0">{{ $stars }}</span>
                        <svg class="w-3.5 h-3.5 text-amber-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <div class="flex-1 bg-zinc-100 dark:bg-zinc-700 rounded-full h-2">
                            <div class="h-2 rounded-full bg-amber-400 transition-all duration-500" style="width: {{ $pct }}%"></div>
                        </div>
                        <span class="text-xs text-zinc-500 w-6 text-right shrink-0">{{ $count }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Reviews List --}}
    <div class="space-y-3">
        @forelse($reviews as $review)
            <div class="tc-card">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center text-white text-sm font-bold shrink-0">
                        {{ strtoupper(substr($review->user?->name ?? 'A', 0, 1)) }}
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <div>
                                <span class="font-semibold text-sm text-zinc-900 dark:text-white">{{ $review->user?->name ?? 'Anonymous' }}</span>
                                @if($review->order)
                                    <span class="ml-2 text-xs text-zinc-400 font-mono">{{ $review->order->tracking_number }}</span>
                                @endif
                            </div>
                            <span class="text-xs text-zinc-400 shrink-0">{{ $review->created_at->diffForHumans() }}</span>
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
            </div>
        @empty
            <div class="tc-card py-16 text-center">
                <svg class="w-12 h-12 text-zinc-200 dark:text-zinc-700 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                <p class="text-sm font-semibold text-zinc-600 dark:text-zinc-400">No reviews yet</p>
                <p class="text-xs text-zinc-400 mt-1">Customer reviews will appear here once submitted.</p>
            </div>
        @endforelse
    </div>
    @endif
</div>
