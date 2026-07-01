<?php

use App\Models\Order;
use App\Models\GarmentType;
use App\Models\Fabric;
use App\Models\VirtualTryon;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('components.layouts.app')] class extends Component {
    use WithFileUploads;

    public string $activeTab = 'all';
    public string $garment_type_id = '';
    public string $fabric_preference = '';
    public string $quantity = '1';
    public string $special_instructions = '';
    
    // New properties for design reference
    public $reference_upload;
    public string $selected_ai_preview = '';
    
    public bool $showModal = false;

    public function createOrder(): void
    {
        $this->validate([
            'garment_type_id' => 'required|exists:garment_types,id',
            'fabric_preference' => 'nullable|string',
            'quantity' => 'required|integer|min:1',
            'special_instructions' => 'nullable|string|max:1000',
            'reference_upload' => 'nullable|image|max:10240', // 10MB Max
        ]);

        $designPath = null;
        
        // Handle file upload
        if ($this->reference_upload) {
            $designPath = $this->reference_upload->store('design-references', 'public');
        } 
        // Or handle selected AI preview
        elseif ($this->selected_ai_preview) {
            $tryon = VirtualTryon::find($this->selected_ai_preview);
            if ($tryon && $tryon->user_id === auth()->id()) {
                $designPath = $tryon->preview_path;
            }
        }

        $garment = GarmentType::find($this->garment_type_id);
        Order::create([
            'user_id' => auth()->id(),
            // Ensure shop_id is assigned if the garment belongs to a shop
            'shop_id' => $garment->shop_id ?? null,
            'tracking_number' => 'TC-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
            'garment_type_id' => $this->garment_type_id,
            'fabric_preference' => $this->fabric_preference,
            'quantity' => (int) $this->quantity,
            'special_instructions' => $this->special_instructions,
            'design_reference_path' => $designPath,
            'total_amount' => $garment->base_price * (int) $this->quantity,
            'status' => 'pending',
            'estimated_completion' => now()->addDays(21),
        ]);

        $this->reset(['garment_type_id', 'fabric_preference', 'quantity', 'special_instructions', 'reference_upload', 'selected_ai_preview', 'showModal']);
        session()->flash('message', 'Order placed successfully!');
    }

    public function with(): array
    {
        $query = Order::where('user_id', auth()->id())->with(['garmentType', 'shop'])->latest();
        if ($this->activeTab !== 'all') {
            $query->where('status', $this->activeTab);
        }
        return [
            'orders' => $query->get(),
            'garmentTypes' => GarmentType::with('shop')->get(),
            'fabrics' => Fabric::where('in_stock', true)->get(),
            'aiPreviews' => VirtualTryon::where('user_id', auth()->id())
                                ->where('status', 'completed')
                                ->whereNotNull('preview_path')
                                ->latest()
                                ->get(),
            'statusCounts' => [
                'all' => Order::where('user_id', auth()->id())->count(),
                'pending' => Order::where('user_id', auth()->id())->where('status', 'pending')->count(),
                'in_production' => Order::where('user_id', auth()->id())->where('status', 'in_production')->count(),
                'fitting_scheduled' => Order::where('user_id', auth()->id())->where('status', 'fitting_scheduled')->count(),
                'completed' => Order::where('user_id', auth()->id())->where('status', 'completed')->count(),
            ],
        ];
    }
}; ?>

<div class="space-y-6">
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">My Orders</h1>
            <p class="text-zinc-500 mt-1">Track and manage your tailoring orders.</p>
        </div>
        <flux:button wire:click="$set('showModal', true)" variant="primary" class="!bg-primary-500 hover:!bg-primary-600">
            <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            New Order
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
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @forelse($orders as $order)
            <div class="tc-card animate-fade-in-up">
                <div class="flex items-start justify-between mb-3">
                    <p class="text-sm font-mono font-semibold text-primary-600 dark:text-primary-400">{{ $order->tracking_number }}</p>
                    <span class="tc-badge tc-badge-{{ $order->status }}">{{ ucwords(str_replace('_', ' ', $order->status)) }}</span>
                </div>
                <h3 class="font-semibold text-zinc-900 dark:text-white">{{ $order->garmentType?->name ?? 'Custom' }}</h3>
                <p class="text-xs text-zinc-500 mb-2">{{ $order->shop?->name ?? 'Unassigned Shop' }}</p>
                <div class="space-y-1 text-sm text-zinc-500">
                    @if($order->fabric_preference)
                        <p>Fabric: {{ $order->fabric_preference }}</p>
                    @endif
                    <p>Qty: {{ $order->quantity }}</p>
                </div>
                <!-- Mini progress bar -->
                <div class="flex gap-1 mt-4">
                    @for($i = 0; $i < 7; $i++)
                        <div class="h-1.5 flex-1 rounded-full {{ $i <= $order->status_index ? 'bg-primary-500' : 'bg-zinc-200 dark:bg-zinc-700' }}"></div>
                    @endfor
                </div>
                <div class="flex items-center justify-between mt-3 pt-3 border-t border-zinc-100 dark:border-zinc-700">
                    <p class="text-lg font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">₱{{ number_format($order->total_amount, 2) }}</p>
                    @if($order->estimated_completion)
                        <p class="text-xs text-zinc-400">Est. {{ $order->estimated_completion->format('M d') }}</p>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full tc-card text-center py-12">
                <svg class="w-16 h-16 mx-auto text-zinc-200 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                <p class="text-zinc-400">No orders found. Place your first order!</p>
            </div>
        @endforelse
    </div>

    <!-- New Order Modal -->
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" wire:click.self="$set('showModal', false)">
        <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-xl max-w-2xl w-full mx-4 p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">Create New Order</h2>
                <button wire:click="$set('showModal', false)" class="text-zinc-400 hover:text-zinc-600"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form wire:submit="createOrder" class="space-y-6">
                
                <!-- Step 1: Garment & Fabric -->
                <div class="space-y-4 pb-6 border-b border-zinc-200 dark:border-zinc-700">
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-white uppercase tracking-wider">1. Garment Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Garment Type <span class="text-red-500">*</span></label>
                            <select wire:model="garment_type_id" class="w-full rounded-lg border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700 text-sm" required>
                                <option value="">Select garment type...</option>
                                @foreach($garmentTypes as $gt)
                                    <option value="{{ $gt->id }}">{{ $gt->name }} ({{ $gt->shop?->name ?? 'Global' }}) — ₱{{ number_format($gt->base_price, 2) }}</option>
                                @endforeach
                            </select>
                            @error('garment_type_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Fabric Preference</label>
                            <select wire:model="fabric_preference" class="w-full rounded-lg border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700 text-sm">
                                <option value="">Select fabric (optional)...</option>
                                @foreach($fabrics as $fabric)
                                    <option value="{{ $fabric->name }}">{{ $fabric->name }} ({{ $fabric->material }}) — ₱{{ number_format($fabric->price_per_meter, 2) }}/m</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-1">
                            <flux:input wire:model="quantity" label="Quantity" type="number" min="1" required />
                        </div>
                        <div class="md:col-span-2">
                            <flux:textarea wire:model="special_instructions" label="Special Instructions" placeholder="Embroidery pattern, fit preference..." rows="2" />
                        </div>
                    </div>
                </div>

                <!-- Step 2: Design Reference -->
                <div class="space-y-4">
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-white uppercase tracking-wider">2. Design Reference</h3>
                    <p class="text-sm text-zinc-500">Provide a visual reference for the tailor. You can either upload a photo or choose an AI preview you generated earlier.</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Option A: Upload -->
                        <div class="border rounded-xl p-4 transition-colors {{ $reference_upload ? 'border-primary-400 bg-primary-50 dark:bg-primary-900/10' : 'border-zinc-200 dark:border-zinc-700' }} {{ $selected_ai_preview ? 'opacity-50 grayscale pointer-events-none' : '' }}">
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Upload Photo</label>
                            <input type="file" wire:model="reference_upload" accept="image/*" class="block w-full text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 dark:file:bg-primary-900/30 dark:file:text-primary-400 cursor-pointer" />
                            <div wire:loading wire:target="reference_upload" class="text-xs text-primary-500 mt-2 font-medium">Uploading...</div>
                            @if ($reference_upload)
                                <div class="mt-3 relative w-20 h-20 rounded-lg overflow-hidden border border-primary-200">
                                    <img src="{{ $reference_upload->temporaryUrl() }}" class="object-cover w-full h-full" alt="Preview">
                                </div>
                            @endif
                            @error('reference_upload') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Option B: Select AI Preview -->
                        <div class="border rounded-xl p-4 transition-colors {{ $selected_ai_preview ? 'border-primary-400 bg-primary-50 dark:bg-primary-900/10' : 'border-zinc-200 dark:border-zinc-700' }} {{ $reference_upload ? 'opacity-50 grayscale pointer-events-none' : '' }}">
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Or Select AI Preview</label>
                            <select wire:model="selected_ai_preview" class="w-full rounded-lg border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700 text-sm mb-3">
                                <option value="">Select a past preview...</option>
                                @foreach($aiPreviews as $preview)
                                    <option value="{{ $preview->id }}">Preview generated on {{ $preview->created_at->format('M d, Y') }}</option>
                                @endforeach
                            </select>
                            @if(count($aiPreviews) === 0)
                                <p class="text-xs text-zinc-500 mt-1">You haven't generated any AI Try-On previews yet.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="pt-4 flex justify-end gap-3 border-t border-zinc-200 dark:border-zinc-700">
                    <flux:button wire:click="$set('showModal', false)" variant="ghost">Cancel</flux:button>
                    <flux:button type="submit" variant="primary" class="!bg-primary-500 hover:!bg-primary-600" wire:loading.attr="disabled" wire:target="createOrder, reference_upload">
                        <span wire:loading.remove wire:target="createOrder">Place Order</span>
                        <span wire:loading wire:target="createOrder">Processing...</span>
                    </flux:button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
