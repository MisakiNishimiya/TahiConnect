<?php

use App\Models\Order;
use App\Models\GarmentType;
use App\Models\Fabric;
use App\Models\VirtualTryon;
use App\Models\Shop;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('components.layouts.app')] class extends Component {
    use WithFileUploads;

    public string $activeTab = 'all';

    // Form State
    public int $currentStep = 1;
    public string $garment_type_id = '';
    public string $fabric_preference = '';
    public int $quantity = 1;
    public string $preferred_completion_date = '';
    public string $special_instructions = '';

    // Design Reference State
    public $reference_upload;
    public string $selected_ai_preview = '';

    public bool $showModal = false;

    public function nextStep(): void
    {
        if ($this->currentStep === 1) {
            $this->validate([
                'garment_type_id'          => 'required|exists:garment_types,id',
                'fabric_preference'         => 'nullable|string',
                'quantity'                  => 'required|integer|min:1',
                'preferred_completion_date' => 'nullable|date|after:today',
            ]);
        } elseif ($this->currentStep === 2) {
            $this->validate([
                'reference_upload' => 'nullable|image|max:10240',
            ]);
        }

        if ($this->currentStep < 3) {
            $this->currentStep++;
        }
    }

    public function previousStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function incrementQuantity(): void { $this->quantity++; }
    public function decrementQuantity(): void  { if ($this->quantity > 1) $this->quantity--; }

    public function createOrder(): void
    {
        $this->validate([
            'garment_type_id' => 'required|exists:garment_types,id',
            'quantity'        => 'required|integer|min:1',
        ]);

        $shop = Shop::instance();

        $designPath = null;
        if ($this->reference_upload) {
            $designPath = $this->reference_upload->store('design-references', 'public');
        } elseif ($this->selected_ai_preview) {
            $tryon = VirtualTryon::find($this->selected_ai_preview);
            if ($tryon && $tryon->user_id === auth()->id()) {
                $designPath = $tryon->preview_path;
            }
        }

        Order::create([
            'user_id'                 => auth()->id(),
            'shop_id'                 => $shop->id,
            'tracking_number'         => 'TC-'.date('Y').'-'.str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
            'order_type'              => 'custom',
            'garment_type_id'         => $this->garment_type_id,
            'fabric_preference'       => $this->fabric_preference,
            'quantity'                => $this->quantity,
            'special_instructions'    => $this->special_instructions,
            'design_reference_path'   => $designPath,
            'total_amount'            => GarmentType::find($this->garment_type_id)->base_price * $this->quantity,
            'status'                  => 'pending',
            'estimated_completion'    => $this->preferred_completion_date ?: now()->addDays(21)->format('Y-m-d'),
        ]);

        $this->reset([
            'currentStep', 'garment_type_id', 'fabric_preference', 'quantity',
            'preferred_completion_date', 'special_instructions',
            'reference_upload', 'selected_ai_preview', 'showModal',
        ]);
        session()->flash('message', 'Order placed successfully!');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset([
            'currentStep', 'garment_type_id', 'fabric_preference', 'quantity',
            'preferred_completion_date', 'special_instructions',
            'reference_upload', 'selected_ai_preview',
        ]);
    }

    public function with(): array
    {
        $query = Order::where('user_id', auth()->id())
            ->with(['garmentType', 'preMadeProduct'])
            ->latest();
        if ($this->activeTab !== 'all') {
            $query->where('status', $this->activeTab);
        }

        $uid = auth()->id();
        return [
            'orders'            => $query->get(),
            'garmentTypes'      => GarmentType::select('id', 'name', 'base_price')->get(),
            'fabrics'           => Fabric::where('in_stock', true)->select('id', 'name', 'material')->get(),
            'aiPreviews'        => VirtualTryon::where('user_id', $uid)
                                    ->where('status', 'completed')
                                    ->whereNotNull('preview_path')
                                    ->latest()->get(),
            'statusCounts'      => [
                'all'               => Order::where('user_id', $uid)->count(),
                'pending'           => Order::where('user_id', $uid)->where('status', 'pending')->count(),
                'in_production'     => Order::where('user_id', $uid)->where('status', 'in_production')->count(),
                'fitting_scheduled' => Order::where('user_id', $uid)->where('status', 'fitting_scheduled')->count(),
                'completed'         => Order::where('user_id', $uid)->where('status', 'completed')->count(),
            ],
            'selectedGarment'   => $this->garment_type_id ? GarmentType::find($this->garment_type_id) : null,
            'selectedPreviewUrl' => $this->selected_ai_preview
                                    ? VirtualTryon::find($this->selected_ai_preview)?->preview_path
                                    : null,
        ];
    }
}; ?>

<div class="space-y-6">
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">My Orders</h1>
            <p class="text-zinc-500 mt-1">Track and manage your tailoring orders.</p>
        </div>
        <flux:button wire:click="$set('showModal', true)" variant="primary" class="!bg-primary-500 hover:!bg-primary-600 click-feedback">
            <div wire:loading wire:target="$set('showModal', true)" class="btn-spinner">
                <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full spinner"></div>
            </div>
            <span wire:loading.remove wire:target="$set('showModal', true)" class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                New Order
            </span>
        </flux:button>
    </div>

    @if (session()->has('message'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm">{{ session('message') }}</div>
    @endif

    <!-- Status Tabs -->
    <div class="flex flex-wrap gap-2">
        @foreach(['all' => 'All', 'pending' => 'Pending', 'in_production' => 'In Production', 'fitting_scheduled' => 'Fitting', 'completed' => 'Completed'] as $key => $label)
            <button wire:click="$set('activeTab', '{{ $key }}')"
                class="px-4 py-2 text-sm rounded-lg font-medium transition-all {{ $activeTab === $key ? 'bg-primary-500 text-white' : 'bg-white text-zinc-600 border border-zinc-200 hover:border-primary-300 dark:bg-zinc-800 dark:text-zinc-300 dark:border-zinc-700' }}">
                {{ $label }}
                <span class="ml-1 px-1.5 py-0.5 text-xs rounded-full {{ $activeTab === $key ? 'bg-white/20' : 'bg-zinc-100 dark:bg-zinc-700' }}">{{ $statusCounts[$key] ?? 0 }}</span>
            </button>
        @endforeach
    </div>

    <!-- Order Cards -->
    <div class="grid grid-cols-1 gap-4">
        @forelse($orders as $order)
            <!-- Mobile-First Responsive Card -->
            <div class="tc-card hover-lift interactive-card animate-fade-in-up cursor-pointer" 
                 style="--stagger-index: {{ $loop->index }}"
                 x-data="{ showDetails: false }"
                 @click="showDetails = !showDetails">
                
                <!-- Main Card Content -->
                <div class="flex items-start justify-between gap-4">
                    <!-- Order Info -->
                    <div class="flex items-start gap-3 flex-1 min-w-0">
                        <div class="w-12 h-12 rounded-xl bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center shrink-0">
                            @if($order->order_type === 'pre_made')
                                <svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a.997.997 0 01-1.414 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                            @else
                                <svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                                </svg>
                            @endif
                        </div>
                        
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2 mb-1">
                                <h3 class="font-semibold text-zinc-900 dark:text-white text-sm truncate">
                                    @if($order->order_type === 'pre_made')
                                        {{ $order->preMadeProduct?->name ?? 'Pre-made Product' }}
                                    @else
                                        {{ $order->garmentType?->name ?? 'Custom Tailoring' }}
                                    @endif
                                </h3>
                                <span class="tc-badge tc-badge-{{ $order->status }} text-xs shrink-0">
                                    {{ ucwords(str_replace('_', ' ', $order->status)) }}
                                </span>
                            </div>
                            
                            <p class="text-xs font-mono font-semibold text-primary-600 dark:text-primary-400 mb-2">
                                {{ $order->tracking_number }}
                            </p>
                            
                            <div class="flex items-center justify-between">
                                <p class="text-xs text-zinc-500 truncate">{{ $order->shop?->name ?? 'Unassigned Shop' }}</p>
                                <p class="text-sm font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">
                                    ₱{{ number_format($order->total_amount, 2) }}
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Expand Icon -->
                    <div class="ml-2 shrink-0">
                        <svg class="w-5 h-5 text-zinc-400 transition-transform" 
                             :class="{ 'rotate-180': showDetails }"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="flex gap-1 mt-4 mb-3">
                    @for($i = 0; $i < ($order->order_type === 'pre_made' ? 5 : 8); $i++)
                        <div class="h-1.5 flex-1 rounded-full transition-all duration-500 animate-grow-up" 
                             style="animation-delay: {{ $i * 100 }}ms; {{ $i <= $order->status_index ? 'background-color: rgb(47, 93, 80);' : 'background-color: rgb(228, 228, 231);' }}">
                        </div>
                    @endfor
                </div>
                
                <!-- Expandable Details -->
                <div x-show="showDetails" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 max-h-0"
                     x-transition:enter-end="opacity-100 max-h-96"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 max-h-96"
                     x-transition:leave-end="opacity-0 max-h-0"
                     class="overflow-hidden">
                    <div class="pt-4 mt-4 border-t border-zinc-100 dark:border-zinc-700 space-y-3">
                        
                        @if($order->order_type === 'pre_made' && $order->product_size)
                        <div class="flex justify-between text-sm">
                            <span class="text-zinc-500">Size:</span>
                            <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $order->product_size }}</span>
                        </div>
                        @endif
                        
                        @if($order->fabric_preference)
                        <div class="flex justify-between text-sm">
                            <span class="text-zinc-500">Fabric:</span>
                            <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $order->fabric_preference }}</span>
                        </div>
                        @endif
                        
                        <div class="flex justify-between text-sm">
                            <span class="text-zinc-500">Quantity:</span>
                            <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $order->quantity }}</span>
                        </div>
                        
                        @if($order->estimated_completion)
                        <div class="flex justify-between text-sm">
                            <span class="text-zinc-500">Est. Completion:</span>
                            <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $order->estimated_completion->format('M d, Y') }}</span>
                        </div>
                        @endif
                        
                        @if($order->special_instructions)
                        <div class="p-3 bg-amber-50 dark:bg-amber-900/10 rounded-lg">
                            <p class="text-xs text-amber-700 dark:text-amber-300">
                                <span class="font-semibold">Note:</span> {{ $order->special_instructions }}
                            </p>
                        </div>
                        @endif
                        
                        <!-- Quick Actions -->
                        <div class="flex gap-2 pt-2">
                            <button 
                                @click.stop="$wire.redirect('{{ route('customer.tracking') }}?tracking={{ $order->tracking_number }}')"
                                class="flex-1 py-2 px-3 text-xs font-medium text-primary-600 bg-primary-50 dark:bg-primary-900/20 rounded-lg hover:bg-primary-100 dark:hover:bg-primary-900/30 transition-colors click-feedback">
                                Track Order
                            </button>
                            @if($order->status === 'ready_for_pickup')
                            <button 
                                @click.stop=""
                                class="flex-1 py-2 px-3 text-xs font-medium text-emerald-600 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg hover:bg-emerald-100 dark:hover:bg-emerald-900/30 transition-colors click-feedback">
                                Ready to Collect
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <x-enhanced-empty-state
                icon="orders"
                title="No orders found"
                description="No orders found. Ready to place your first order?"
                :actions="[
                    ['type' => 'primary', 'label' => 'Create Your First Order', 'wire:click' => '$set(\'showModal\', true)']
                ]"
            />
        @endforelse
    </div>

    <!-- Floating Action Button -->
    <x-floating-action-button 
        icon="plus" 
        tooltip="Create New Order" 
        wire:click="$set('showModal', true)"
    />

    <!-- NEW ORDER MODAL REDESIGN -->
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 overflow-y-auto">
        <!-- Modal Container (900px, rounded-2xl) -->
        <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl w-full max-w-4xl flex flex-col my-auto max-h-[90vh]" @click.stop>
            
            <!-- Header -->
            <div class="px-8 py-6 border-b border-zinc-100 dark:border-zinc-800 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center border border-zinc-200 dark:border-zinc-700">
                        <svg class="w-6 h-6 text-zinc-700 dark:text-zinc-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-[24px] font-semibold text-zinc-900 dark:text-white leading-tight">Create New Order</h2>
                        <p class="text-[14px] text-zinc-500 mt-1">Submit garment requirements for your chosen tailor.</p>
                    </div>
                </div>
                <button wire:click="closeModal" class="p-2 text-zinc-400 hover:text-zinc-600 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-full transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Body Area -->
            <div class="flex-1 overflow-y-auto px-8 py-8 bg-zinc-50/50 dark:bg-zinc-900/50">
                
                <!-- Progress Indicator -->
                <div class="mb-12 max-w-2xl mx-auto">
                    <div class="flex justify-between items-center relative w-full">
                        <div class="absolute left-0 top-4 w-full h-0.5 bg-zinc-200 dark:bg-zinc-700 -z-10"></div>
                        
                        <!-- Step 1 -->
                        <div class="relative flex flex-col items-center z-10 w-32">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-semibold {{ $currentStep >= 1 ? 'bg-zinc-900 text-white border-2 border-white dark:border-zinc-900 ring-4 ring-zinc-100 dark:ring-zinc-800' : 'bg-white text-zinc-400 border-2 border-zinc-200' }}">1</div>
                            <span class="text-[13px] font-medium mt-3 whitespace-nowrap {{ $currentStep >= 1 ? 'text-zinc-900 dark:text-white' : 'text-zinc-400' }}">Garment Details</span>
                        </div>
                        
                        <!-- Line 1-2 Overlay -->
                        <div class="absolute left-16 top-4 h-0.5 transition-all duration-300 {{ $currentStep >= 2 ? 'bg-zinc-900 dark:bg-white' : 'bg-transparent' }}" style="width: calc(50% - 4rem); z-index: 5;"></div>

                        <!-- Step 2 -->
                        <div class="relative flex flex-col items-center z-10 w-32">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-semibold transition-colors {{ $currentStep >= 2 ? 'bg-zinc-900 text-white border-2 border-white dark:border-zinc-900 ring-4 ring-zinc-100 dark:ring-zinc-800' : 'bg-white text-zinc-400 border-2 border-zinc-200 dark:bg-zinc-800 dark:border-zinc-700' }}">2</div>
                            <span class="text-[13px] font-medium mt-3 whitespace-nowrap transition-colors {{ $currentStep >= 2 ? 'text-zinc-900 dark:text-white' : 'text-zinc-400' }}">Design Reference</span>
                        </div>
                        
                        <!-- Line 2-3 Overlay -->
                        <div class="absolute right-16 top-4 h-0.5 transition-all duration-300 {{ $currentStep >= 3 ? 'bg-zinc-900 dark:bg-white' : 'bg-transparent' }}" style="width: calc(50% - 4rem); z-index: 5;"></div>

                        <!-- Step 3 -->
                        <div class="relative flex flex-col items-center z-10 w-32">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-semibold transition-colors {{ $currentStep >= 3 ? 'bg-zinc-900 text-white border-2 border-white dark:border-zinc-900 ring-4 ring-zinc-100 dark:ring-zinc-800' : 'bg-white text-zinc-400 border-2 border-zinc-200 dark:bg-zinc-800 dark:border-zinc-700' }}">3</div>
                            <span class="text-[13px] font-medium mt-3 whitespace-nowrap transition-colors {{ $currentStep >= 3 ? 'text-zinc-900 dark:text-white' : 'text-zinc-400' }}">Review & Submit</span>
                        </div>
                    </div>
                </div>

                <!-- Step 1: Garment Details -->
                @if($currentStep === 1)
                <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-zinc-200 dark:border-zinc-700 p-8 shadow-sm">
                    <h3 class="text-[18px] font-semibold text-zinc-900 dark:text-white mb-6">Garment Specifications</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-6">

                        <!-- Garment Type -->
                        <div class="md:col-span-2">
                            <label class="block text-[14px] font-medium text-zinc-700 dark:text-zinc-300 mb-2">Garment Type <span class="text-red-500">*</span></label>
                            <select wire:model="garment_type_id" class="w-full px-4 py-2.5 bg-white dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 rounded-xl text-[14px] focus:ring-2 focus:ring-zinc-900 dark:focus:ring-white focus:border-zinc-900 transition-shadow appearance-none cursor-pointer">
                                <option value="">Select garment type...</option>
                                @foreach($garmentTypes as $gt)
                                    <option value="{{ $gt->id }}">{{ $gt->name }} — ₱{{ number_format($gt->base_price, 2) }}</option>
                                @endforeach
                            </select>
                            @error('garment_type_id') <p class="text-[12px] text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Fabric Preference -->
                        <div>
                            <label class="block text-[14px] font-medium text-zinc-700 dark:text-zinc-300 mb-2">Fabric Preference</label>
                            <select wire:model="fabric_preference" class="w-full px-4 py-2.5 bg-white dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 rounded-xl text-[14px] focus:ring-2 focus:ring-zinc-900 dark:focus:ring-white focus:border-zinc-900 transition-shadow appearance-none cursor-pointer">
                                <option value="">Select fabric (optional)...</option>
                                @foreach($fabrics as $fabric)
                                    <option value="{{ $fabric->name }}">{{ $fabric->name }} ({{ $fabric->material }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Quantity Stepper -->
                        <div>
                            <label class="block text-[14px] font-medium text-zinc-700 dark:text-zinc-300 mb-2">Quantity <span class="text-red-500">*</span></label>
                            <div class="flex items-center w-full">
                                <button type="button" wire:click="decrementQuantity" class="px-5 py-2.5 bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded-l-xl text-zinc-600 hover:bg-zinc-100 transition-colors font-bold">-</button>
                                <input type="text" wire:model.live="quantity" readonly class="w-full text-center py-2.5 bg-white dark:bg-zinc-900 border-y border-zinc-300 dark:border-zinc-700 text-[14px] font-medium focus:ring-0 focus:outline-none">
                                <button type="button" wire:click="incrementQuantity" class="px-5 py-2.5 bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded-r-xl text-zinc-600 hover:bg-zinc-100 transition-colors font-bold">+</button>
                            </div>
                            @error('quantity') <p class="text-[12px] text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Preferred Completion Date -->
                        <div>
                            <label class="block text-[14px] font-medium text-zinc-700 dark:text-zinc-300 mb-2">Preferred Completion Date (Optional)</label>
                            <input type="date" wire:model="preferred_completion_date" min="{{ now()->addDays(1)->format('Y-m-d') }}" class="w-full px-4 py-2.5 bg-white dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 rounded-xl text-[14px] focus:ring-2 focus:ring-zinc-900 dark:focus:ring-white focus:border-zinc-900 transition-shadow">
                            @error('preferred_completion_date') <p class="text-[12px] text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Special Instructions -->
                        <div class="md:col-span-2">
                            <label class="block text-[14px] font-medium text-zinc-700 dark:text-zinc-300 mb-2">Special Instructions</label>
                            <textarea wire:model="special_instructions" rows="4" placeholder="Describe embroidery, fit preference, color requests, sizing concerns, or additional tailoring notes." class="w-full p-4 bg-white dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 rounded-xl text-[14px] focus:ring-2 focus:ring-zinc-900 dark:focus:ring-white focus:border-zinc-900 transition-shadow resize-none"></textarea>
                            <p class="text-[12px] text-zinc-500 mt-2">These notes will be attached to your order and sent directly to the tailor.</p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Step 2: Design Reference -->
                @if($currentStep === 2)
                <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-zinc-200 dark:border-zinc-700 p-8 shadow-sm min-h-[400px]">
                    <h3 class="text-[18px] font-semibold text-zinc-900 dark:text-white mb-2">Design Reference</h3>
                    <p class="text-[14px] text-zinc-500 mb-6">Upload a photo from your device or select an AI-generated preview from your Virtual Try-On sessions.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 h-full">
                        
                        <!-- Upload Area -->
                        <div class="{{ $selected_ai_preview ? 'opacity-40 grayscale pointer-events-none' : '' }}">
                            <x-enhanced-file-upload 
                                label="Upload Design Reference"
                                description="Drag & drop your design image here or click to browse"
                                accept="image/*"
                                maxSize="10MB"
                                wire:model="reference_upload"
                                :preview="true"
                            />
                            @error('reference_upload') 
                                <p class="text-xs text-red-500 mt-2">{{ $message }}</p> 
                            @enderror
                        </div>

                        <!-- AI Previews Area -->
                        <div class="flex flex-col h-full {{ $reference_upload ? 'opacity-40 grayscale pointer-events-none' : '' }}">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-[14px] font-medium text-zinc-700 dark:text-zinc-300">Your AI Previews</h4>
                                @if($selected_ai_preview)
                                    <button type="button" wire:click="$set('selected_ai_preview', '')" class="text-[12px] text-zinc-500 hover:text-red-500">Clear selection</button>
                                @endif
                            </div>

                            @if(count($aiPreviews) > 0)
                                <div class="grid grid-cols-2 gap-3 overflow-y-auto pr-2 max-h-[300px]">
                                    @foreach($aiPreviews as $preview)
                                        <button type="button" wire:click="$set('selected_ai_preview', '{{ $preview->id }}')" class="relative text-left rounded-xl border overflow-hidden transition-all {{ $selected_ai_preview == $preview->id ? 'border-zinc-900 ring-2 ring-zinc-900/20 shadow-md' : 'border-zinc-200 dark:border-zinc-700 hover:border-zinc-300 dark:hover:border-zinc-600' }}">
                                            <div class="aspect-[3/4] bg-zinc-100 dark:bg-zinc-800">
                                                <img src="{{ Storage::url($preview->preview_path) }}" alt="AI Preview" class="w-full h-full object-cover">
                                            </div>
                                            <div class="p-2 bg-white dark:bg-zinc-900 border-t border-zinc-100 dark:border-zinc-800">
                                                <p class="text-[12px] font-medium text-zinc-900 dark:text-white truncate">AI Generation</p>
                                                <p class="text-[10px] text-zinc-500">{{ $preview->created_at->format('M d, Y') }}</p>
                                            </div>
                                            @if($selected_ai_preview == $preview->id)
                                                <div class="absolute top-2 right-2 w-5 h-5 bg-zinc-900 rounded-full flex items-center justify-center shadow-sm">
                                                    <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                                </div>
                                            @endif
                                        </button>
                                    @endforeach
                                </div>
                            @else
                                <div class="flex-1 flex flex-col items-center justify-center border border-zinc-200 dark:border-zinc-700 rounded-2xl bg-zinc-50 dark:bg-zinc-800/30 p-6 text-center">
                                    <div class="w-12 h-12 rounded-xl bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 flex items-center justify-center mb-4">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" /></svg>
                                    </div>
                                    <p class="text-[14px] font-medium text-zinc-900 dark:text-white">No AI previews generated yet</p>
                                    <p class="text-[12px] text-zinc-500 mt-1 mb-4">Use the Virtual Try-On tool to visualize your garments.</p>
                                    <a href="{{ route('customer.virtual-tryon') }}" class="text-[12px] font-medium text-zinc-900 bg-zinc-100 dark:bg-zinc-800 px-4 py-2 rounded-lg hover:bg-zinc-200 transition-colors">Go to Try-On Tool</a>
                                </div>
                            @endif

                        </div>
                    </div>
                </div>
                @endif

                <!-- Step 3: Review & Submit -->
                @if($currentStep === 3)
                <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow-sm overflow-hidden">
                    <div class="px-8 py-6 border-b border-zinc-100 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-800/50">
                        <h3 class="text-[18px] font-semibold text-zinc-900 dark:text-white">Order Summary</h3>
                        <p class="text-[14px] text-zinc-500">Please review your details before submitting.</p>
                    </div>
                    
                    <div class="p-8 grid grid-cols-1 md:grid-cols-3 gap-8">
                        <!-- Left Details Column -->
                        <div class="md:col-span-2 space-y-6">
                            
                            <div>
                                <h4 class="text-[12px] font-bold text-zinc-400 uppercase tracking-wider mb-3">Garment Information</h4>
                                <div class="bg-zinc-50 dark:bg-zinc-900/50 rounded-xl p-4 border border-zinc-100 dark:border-zinc-800">
                                    <div class="grid grid-cols-2 gap-y-4">
                                        <div class="col-span-2">
                                            <p class="text-[12px] text-zinc-500">Tailoring Shop</p>
                                            <p class="text-[15px] font-semibold text-zinc-900 dark:text-white">{{ $selectedShop?->name ?? 'Not specified' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-[12px] text-zinc-500">Garment Type</p>
                                            <p class="text-[14px] font-medium text-zinc-900 dark:text-white">{{ $selectedGarment?->name ?? 'Not specified' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-[12px] text-zinc-500">Fabric</p>
                                            <p class="text-[14px] font-medium text-zinc-900 dark:text-white">{{ $fabric_preference ?: 'None specified' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-[12px] text-zinc-500">Quantity</p>
                                            <p class="text-[14px] font-medium text-zinc-900 dark:text-white">{{ $quantity }} pcs</p>
                                        </div>
                                        <div>
                                            <p class="text-[12px] text-zinc-500">Total Est. Base Price</p>
                                            <p class="text-[14px] font-bold text-zinc-900">₱{{ number_format(($selectedGarment?->base_price ?? 0) * $quantity, 2) }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <h4 class="text-[12px] font-bold text-zinc-400 uppercase tracking-wider mb-2">Target Date</h4>
                                    <p class="text-[14px] font-medium text-zinc-900 dark:text-white">{{ $preferred_completion_date ? \Carbon\Carbon::parse($preferred_completion_date)->format('M d, Y') : 'Flexible (approx 21 days)' }}</p>
                                </div>
                            </div>

                            @if($special_instructions)
                            <div>
                                <h4 class="text-[12px] font-bold text-zinc-400 uppercase tracking-wider mb-2">Special Instructions</h4>
                                <div class="bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4">
                                    <p class="text-[14px] text-zinc-700 dark:text-zinc-300 whitespace-pre-line">{{ $special_instructions }}</p>
                                </div>
                            </div>
                            @endif

                        </div>

                        <!-- Right Reference Column -->
                        <div>
                            <h4 class="text-[12px] font-bold text-zinc-400 uppercase tracking-wider mb-3">Design Reference</h4>
                            @if($reference_upload)
                                <div class="rounded-xl overflow-hidden border border-zinc-200 shadow-sm aspect-[3/4]">
                                    <img src="{{ $reference_upload->temporaryUrl() }}" class="w-full h-full object-cover">
                                </div>
                                <p class="text-[12px] text-center text-zinc-500 mt-2">Uploaded File</p>
                            @elseif($selectedPreviewUrl)
                                <div class="rounded-xl overflow-hidden border border-zinc-200 shadow-sm aspect-[3/4]">
                                    <img src="{{ Storage::url($selectedPreviewUrl) }}" class="w-full h-full object-cover">
                                </div>
                                <p class="text-[12px] text-center text-zinc-900 font-medium mt-2">AI Preview Attached</p>
                            @else
                                <div class="rounded-xl border border-dashed border-zinc-300 dark:border-zinc-700 aspect-[3/4] flex flex-col items-center justify-center bg-zinc-50 dark:bg-zinc-800/50 p-6 text-center">
                                    <svg class="w-8 h-8 text-zinc-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
                                    <p class="text-[12px] text-zinc-500">No reference image attached to this order.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
                
            </div>

            <!-- Sticky Footer -->
            <div class="px-8 py-5 border-t border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 rounded-b-2xl shrink-0 flex items-center justify-between">
                <div>
                    <button wire:click="closeModal" class="px-5 py-2.5 text-[14px] font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white transition-colors">Cancel</button>
                </div>
                <div class="flex items-center gap-3">
                    @if($currentStep > 1)
                        <button wire:click="previousStep" class="px-5 py-2.5 text-[14px] font-medium text-zinc-700 bg-white border border-zinc-300 dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-300 rounded-xl hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors">Back</button>
                    @endif
                    
                    @if($currentStep < 3)
                        <button wire:click="nextStep" class="px-6 py-2.5 text-[14px] font-medium text-white bg-zinc-900 dark:bg-white dark:text-zinc-900 rounded-xl hover:bg-zinc-800 dark:hover:bg-zinc-100 transition-colors shadow-sm click-feedback relative">
                            <div wire:loading wire:target="nextStep" class="absolute inset-0 flex items-center justify-center">
                                <div class="w-4 h-4 border-2 border-white dark:border-zinc-900 border-t-transparent rounded-full spinner"></div>
                            </div>
                            <span wire:loading.remove wire:target="nextStep">Next Step</span>
                        </button>
                    @else
                        <button wire:click="createOrder" wire:loading.attr="disabled" wire:target="createOrder" class="px-6 py-2.5 text-[14px] font-medium text-white bg-zinc-900 dark:bg-white dark:text-zinc-900 rounded-xl hover:bg-zinc-800 dark:hover:bg-zinc-100 transition-colors shadow-sm flex items-center gap-2">
                            <span wire:loading.remove wire:target="createOrder">Place Order</span>
                            <span wire:loading wire:target="createOrder">Processing...</span>
                            <svg wire:loading.remove wire:target="createOrder" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                        </button>
                    @endif
                </div>
            </div>

        </div>
    </div>
    @endif
</div>
