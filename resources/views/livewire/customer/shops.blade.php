<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Shop;

new #[Layout('components.layouts.app')] class extends Component {
    use WithPagination;

    public $search = '';
    public $barangay = '';
    public $specialty = '';
    public $minRating = '';
    public $sortBy = 'featured';
    public $showAdvanced = false;

    public function with()
    {
        $query = Shop::where('is_active', true)
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                      ->orWhere('description', 'like', "%{$this->search}%")
                      ->orWhere('specialties', 'like', "%{$this->search}%");
                });
            })
            ->when($this->barangay, function($query) {
                $query->where('barangay', $this->barangay);
            })
            ->when($this->specialty, function($query) {
                $query->where('specialties', 'like', "%{$this->specialty}%");
            })
            ->when($this->minRating, function($query) {
                $query->where('rating', '>=', $this->minRating);
            });

        // Apply sorting
        switch($this->sortBy) {
            case 'rating':
                $query->orderBy('rating', 'desc');
                break;
            case 'reviews':
                $query->orderBy('total_reviews', 'desc');
                break;
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            default: // featured
                $query->orderBy('is_featured', 'desc')->orderBy('rating', 'desc');
        }

        $shops = $query->paginate(12);

        $barangays = Shop::select('barangay')->distinct()->pluck('barangay')->filter();
        
        $specialties = Shop::whereNotNull('specialties')
            ->get()
            ->pluck('specialties')
            ->flatten()
            ->unique()
            ->values()
            ->sort();

        return [
            'shops' => $shops,
            'barangays' => $barangays,
            'specialties' => $specialties,
            'totalShops' => Shop::where('is_active', true)->count(),
            'filteredCount' => $shops->total(),
        ];
    }
    
    public function updatedSearch() { $this->resetPage(); }
    public function updatedBarangay() { $this->resetPage(); }
    public function updatedSpecialty() { $this->resetPage(); }
    public function updatedMinRating() { $this->resetPage(); }
    public function updatedSortBy() { $this->resetPage(); }
    
    public function clearFilters()
    {
        $this->reset(['search', 'barangay', 'specialty', 'minRating']);
        $this->resetPage();
    }

    public function toggleAdvanced()
    {
        $this->showAdvanced = !$this->showAdvanced;
    }
}; ?>

<div>
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <h1 class="text-3xl font-bold text-primary-600 dark:text-primary-400">Discover Tailors in Davao City</h1>
                <p class="text-zinc-500 dark:text-zinc-400">
                    Find the perfect shop for your bespoke garments 
                    <span class="text-sm">({{ $filteredCount }} of {{ $totalShops }} shops)</span>
                </p>
            </div>
            
            <!-- Sort Dropdown -->
            <div class="flex items-center gap-3">
                <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Sort by:</label>
                <select wire:model.live="sortBy" class="px-3 py-2 border border-zinc-200 dark:border-zinc-700 rounded-lg text-sm bg-white dark:bg-zinc-800 focus:ring-2 focus:ring-primary-500">
                    <option value="featured">Featured First</option>
                    <option value="rating">Highest Rated</option>
                    <option value="reviews">Most Reviews</option>
                    <option value="name">Alphabetical</option>
                    <option value="newest">Newest</option>
                </select>
            </div>
        </div>

        <!-- Enhanced Filters -->
        <div class="tc-card space-y-4">
            <!-- Main Search Row -->
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                            </svg>
                        </div>
                        <input 
                            wire:model.live.debounce.300ms="search" 
                            type="text"
                            placeholder="Search by name, specialty, or description..." 
                            class="block w-full pl-10 pr-12 py-3 border border-zinc-200 dark:border-zinc-700 rounded-xl text-sm bg-white dark:bg-zinc-800 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                        >
                        <!-- Clear Search -->
                        <div class="absolute inset-y-0 right-0 flex items-center">
                            @if($search)
                            <button 
                                wire:click="$set('search', '')" 
                                class="p-2 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Quick Filters -->
                <div class="flex gap-2">
                    <select wire:model.live="barangay" class="px-3 py-3 border border-zinc-200 dark:border-zinc-700 rounded-xl text-sm bg-white dark:bg-zinc-800 focus:ring-2 focus:ring-primary-500 min-w-[140px]">
                        <option value="">Any Location</option>
                        @foreach($barangays as $bgy)
                            <option value="{{ $bgy }}">{{ $bgy }}</option>
                        @endforeach
                    </select>
                    
                    <button 
                        wire:click="toggleAdvanced" 
                        class="px-4 py-3 border border-zinc-200 dark:border-zinc-700 rounded-xl text-sm bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors flex items-center gap-2 {{ $showAdvanced ? 'ring-2 ring-primary-500' : '' }}"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"/>
                        </svg>
                        Advanced
                        <svg class="w-4 h-4 transition-transform {{ $showAdvanced ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Advanced Filters (Collapsible) -->
            <div x-show="$wire.showAdvanced" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 max-h-0"
                 x-transition:enter-end="opacity-100 max-h-40"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 max-h-40"
                 x-transition:leave-end="opacity-0 max-h-0"
                 class="overflow-hidden">
                <div class="pt-4 border-t border-zinc-200 dark:border-zinc-700">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <!-- Specialty Filter -->
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Specialty</label>
                            <select wire:model.live="specialty" class="w-full px-3 py-2 border border-zinc-200 dark:border-zinc-700 rounded-lg text-sm bg-white dark:bg-zinc-800 focus:ring-2 focus:ring-primary-500">
                                <option value="">Any Specialty</option>
                                @foreach($specialties as $spec)
                                    <option value="{{ $spec }}">{{ $spec }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Minimum Rating -->
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Minimum Rating</label>
                            <select wire:model.live="minRating" class="w-full px-3 py-2 border border-zinc-200 dark:border-zinc-700 rounded-lg text-sm bg-white dark:bg-zinc-800 focus:ring-2 focus:ring-primary-500">
                                <option value="">Any Rating</option>
                                <option value="4.5">4.5+ Stars</option>
                                <option value="4.0">4.0+ Stars</option>
                                <option value="3.5">3.5+ Stars</option>
                                <option value="3.0">3.0+ Stars</option>
                            </select>
                        </div>

                        <!-- Clear Filters -->
                        <div class="flex items-end">
                            <button 
                                wire:click="clearFilters" 
                                class="w-full px-4 py-2 text-sm font-medium text-zinc-600 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700 rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors"
                            >
                                Clear All Filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Filters Display -->
            @if($search || $barangay || $specialty || $minRating)
            <div class="flex flex-wrap gap-2 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                <span class="text-sm text-zinc-600 dark:text-zinc-400">Active filters:</span>
                
                @if($search)
                <span class="inline-flex items-center gap-1 px-2 py-1 bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 rounded-md text-xs">
                    Search: "{{ $search }}"
                    <button wire:click="$set('search', '')" class="hover:text-primary-900 dark:hover:text-primary-100">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </span>
                @endif

                @if($barangay)
                <span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-md text-xs">
                    Location: {{ $barangay }}
                    <button wire:click="$set('barangay', '')" class="hover:text-blue-900 dark:hover:text-blue-100">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </span>
                @endif

                @if($specialty)
                <span class="inline-flex items-center gap-1 px-2 py-1 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 rounded-md text-xs">
                    Specialty: {{ $specialty }}
                    <button wire:click="$set('specialty', '')" class="hover:text-emerald-900 dark:hover:text-emerald-100">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </span>
                @endif

                @if($minRating)
                <span class="inline-flex items-center gap-1 px-2 py-1 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 rounded-md text-xs">
                    Rating: {{ $minRating }}+ stars
                    <button wire:click="$set('minRating', '')" class="hover:text-amber-900 dark:hover:text-amber-100">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </span>
                @endif
            </div>
            @endif
        </div>
    </div>

    <!-- Shop Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($shops as $shop)
            <div class="tc-card hover-lift interactive-card group animate-fade-in-up cursor-pointer overflow-hidden" 
                 style="--stagger-index: {{ $loop->index }}"
                 wire:click="$redirect('{{ route('customer.shop.show', $shop->id) }}')">
                
                <!-- Shop Header with Gradient -->
                <div class="relative -m-6 mb-4 p-6 bg-gradient-to-br from-primary-500/10 to-secondary-500/10 border-b border-primary-100 dark:border-primary-900/30">
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex-1">
                            <h2 class="text-xl font-bold font-heading text-primary-700 dark:text-primary-300 group-hover:text-primary-600 dark:group-hover:text-primary-200 transition-colors">
                                {{ $shop->name }}
                            </h2>
                            <div class="flex items-center gap-2 text-sm text-zinc-500 mt-1">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span>{{ $shop->address }}, {{ $shop->barangay }}</span>
                            </div>
                        </div>
                        
                        <div class="flex flex-col items-end gap-2">
                            @if($shop->is_featured)
                                <div class="flex items-center gap-1 px-2 py-1 bg-gradient-to-r from-yellow-400 to-yellow-500 text-yellow-900 rounded-full text-xs font-semibold">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    Featured
                                </div>
                            @endif
                            
                            <!-- Rating with Visual Appeal -->
                            <div class="flex items-center gap-1 px-2 py-1 bg-white dark:bg-zinc-800 rounded-full shadow-sm border border-zinc-200 dark:border-zinc-700">
                                <svg class="w-4 h-4 text-yellow-400 fill-current" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                <span class="font-bold text-zinc-700 dark:text-zinc-300 text-sm">{{ $shop->rating }}</span>
                                <span class="text-xs text-zinc-500">({{ $shop->total_reviews }})</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Shop Description -->
                @if($shop->description)
                <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed mb-4 line-clamp-2">
                    {{ $shop->description }}
                </p>
                @endif

                <!-- Specialties with Better Layout -->
                <div class="mb-6">
                    <div class="flex flex-wrap gap-2">
                        @if(is_array($shop->specialties))
                            @foreach(array_slice($shop->specialties, 0, 4) as $specialty)
                                <span class="inline-flex items-center px-2.5 py-1 bg-gradient-to-r from-primary-50 to-secondary-50 dark:from-primary-900/20 dark:to-secondary-900/20 text-primary-700 dark:text-primary-300 border border-primary-200 dark:border-primary-800 rounded-full text-xs font-medium">
                                    {{ $specialty }}
                                </span>
                            @endforeach
                            @if(count($shop->specialties) > 4)
                                <span class="inline-flex items-center px-2.5 py-1 bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 rounded-full text-xs font-medium">
                                    +{{ count($shop->specialties) - 4 }} more
                                </span>
                            @endif
                        @endif
                    </div>
                </div>
                
                <!-- Action Button with Hover Effect -->
                <div class="relative">
                    <button class="w-full py-3 px-4 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-medium rounded-xl transition-all duration-300 group-hover:shadow-lg group-hover:shadow-primary-500/25 click-feedback">
                        <span class="flex items-center justify-center gap-2">
                            View Shop & Book
                            <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                            </svg>
                        </span>
                    </button>
                </div>

                <!-- Hover Overlay Effect -->
                <div class="absolute inset-0 bg-gradient-to-t from-primary-500/5 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none rounded-2xl"></div>
            </div>
        @empty
            <!-- Enhanced Empty State -->
            <div class="col-span-full">
                <div class="tc-card text-center py-16 animate-fade-in-up">
                    <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-gradient-to-br from-zinc-100 to-zinc-200 dark:from-zinc-800 dark:to-zinc-700 flex items-center justify-center">
                        <svg class="w-12 h-12 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-zinc-700 dark:text-zinc-300 mb-2">No shops found</h3>
                    <p class="text-zinc-500 mb-6">Try adjusting your search filters or explore different areas.</p>
                    
                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        <button wire:click="clearFilters" class="px-6 py-3 bg-primary-500 text-white rounded-xl hover:bg-primary-600 transition-colors click-feedback">
                            Clear All Filters
                        </button>
                        <button wire:click="$set('search', 'Barong')" class="px-6 py-3 border border-primary-200 text-primary-600 dark:border-primary-700 dark:text-primary-400 rounded-xl hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors click-feedback">
                            Search "Barong"
                        </button>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Enhanced Pagination -->
    <div class="mt-8">
        <div wire:loading wire:target="updatedSearch,updatedBarangay,updatedSpecialty,updatedMinRating,updatedSortBy">
            <!-- Pagination Loading Skeleton -->
            <div class="flex justify-center">
                <div class="flex space-x-1">
                    @for($i = 0; $i < 5; $i++)
                        <div class="w-10 h-10 bg-zinc-200 dark:bg-zinc-700 rounded animate-pulse"></div>
                    @endfor
                </div>
            </div>
        </div>
        
        <div wire:loading.remove wire:target="updatedSearch,updatedBarangay,updatedSpecialty,updatedMinRating,updatedSortBy">
            {{ $shops->links() }}
        </div>
    </div>
</div>

<!-- Skeleton Loading for Shop Cards -->
<div wire:loading wire:target="updatedSearch,updatedBarangay,updatedSpecialty,updatedMinRating,updatedSortBy" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-8">
    @for($i = 0; $i < 6; $i++)
        <div class="tc-card animate-pulse">
            <!-- Header Skeleton -->
            <div class="relative -m-6 mb-4 p-6 bg-gradient-to-br from-zinc-100 to-zinc-200 dark:from-zinc-800 dark:to-zinc-700 border-b border-zinc-200 dark:border-zinc-700">
                <div class="flex justify-between items-start mb-3">
                    <div class="flex-1">
                        <div class="h-6 bg-zinc-300 dark:bg-zinc-600 rounded w-3/4 mb-2"></div>
                        <div class="h-4 bg-zinc-200 dark:bg-zinc-700 rounded w-1/2"></div>
                    </div>
                    <div class="flex flex-col items-end gap-2">
                        <div class="w-16 h-6 bg-zinc-300 dark:bg-zinc-600 rounded-full"></div>
                        <div class="w-20 h-6 bg-zinc-200 dark:bg-zinc-700 rounded-full"></div>
                    </div>
                </div>
            </div>
            
            <!-- Description Skeleton -->
            <div class="space-y-2 mb-4">
                <div class="h-4 bg-zinc-200 dark:bg-zinc-700 rounded w-full"></div>
                <div class="h-4 bg-zinc-200 dark:bg-zinc-700 rounded w-2/3"></div>
            </div>
            
            <!-- Specialties Skeleton -->
            <div class="flex gap-2 mb-6">
                @for($j = 0; $j < 3; $j++)
                    <div class="h-6 bg-zinc-200 dark:bg-zinc-700 rounded-full w-16"></div>
                @endfor
            </div>
            
            <!-- Button Skeleton -->
            <div class="h-12 bg-zinc-300 dark:bg-zinc-600 rounded-xl"></div>
        </div>
    @endfor
</div>
