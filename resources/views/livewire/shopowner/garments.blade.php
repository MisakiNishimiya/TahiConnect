<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Models\PreMadeProduct;

new #[Layout('components.layouts.app')] class extends Component {
    public bool $showAddModal    = false;
    public bool $showEditModal   = false;
    public ?int $editingId       = null;

    // Form fields
    public string $name        = '';
    public string $description = '';
    public string $price       = '';
    public string $sizesInput  = '';  // comma-separated
    public bool   $is_active   = true;

    public function with(): array
    {
        return [
            'products'      => PreMadeProduct::latest()->get(),
            'activeCount'   => PreMadeProduct::where('is_active', true)->count(),
            'inactiveCount' => PreMadeProduct::where('is_active', false)->count(),
        ];
    }

    public function openAdd(): void
    {
        $this->reset(['name','description','price','sizesInput','is_active','editingId']);
        $this->is_active    = true;
        $this->showAddModal = true;
    }

    public function openEdit(int $id): void
    {
        $p = PreMadeProduct::findOrFail($id);
        $this->editingId   = $id;
        $this->name        = $p->name;
        $this->description = $p->description ?? '';
        $this->price       = (string) $p->price;
        $this->sizesInput  = implode(', ', $p->available_sizes ?? []);
        $this->is_active   = $p->is_active;
        $this->showEditModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'name'       => 'required|string|max:150',
            'price'      => 'required|numeric|min:0',
            'sizesInput' => 'required|string',
        ]);

        $sizes = array_values(array_filter(array_map('trim', explode(',', $this->sizesInput))));

        PreMadeProduct::create([
            'name'            => $this->name,
            'description'     => $this->description,
            'price'           => $this->price,
            'available_sizes' => $sizes,
            'is_active'       => $this->is_active,
        ]);

        $this->reset(['name','description','price','sizesInput','is_active','showAddModal']);
        session()->flash('message', 'Product added successfully.');
    }

    public function update(): void
    {
        $this->validate([
            'name'       => 'required|string|max:150',
            'price'      => 'required|numeric|min:0',
            'sizesInput' => 'required|string',
        ]);

        $sizes = array_values(array_filter(array_map('trim', explode(',', $this->sizesInput))));

        PreMadeProduct::findOrFail($this->editingId)->update([
            'name'            => $this->name,
            'description'     => $this->description,
            'price'           => $this->price,
            'available_sizes' => $sizes,
            'is_active'       => $this->is_active,
        ]);

        $this->reset(['name','description','price','sizesInput','is_active','editingId','showEditModal']);
        session()->flash('message', 'Product updated successfully.');
    }

    public function toggleActive(int $id): void
    {
        $p = PreMadeProduct::findOrFail($id);
        $p->update(['is_active' => !$p->is_active]);
    }

    public function closeModals(): void
    {
        $this->showAddModal  = false;
        $this->showEditModal = false;
        $this->reset(['name','description','price','sizesInput','is_active','editingId']);
    }
}; ?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-primary-600 dark:text-primary-400" style="font-family: 'Poppins';">Pre-Made Products Catalog</h1>
            <p class="text-zinc-500 mt-1">Manage your ready-to-wear items available for customers to order</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('shopowner.rates') }}" wire:navigate
                class="px-4 py-2.5 text-sm font-medium text-primary-600 bg-primary-50 hover:bg-primary-100 border border-primary-200 rounded-xl transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                Rates & Fabrics
            </a>
            <button wire:click="openAdd"
                class="px-5 py-2.5 text-sm font-medium bg-gradient-to-r from-primary-500 to-primary-600 text-white rounded-xl hover:from-primary-600 hover:to-primary-700 transition-all shadow-lg flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Product
            </button>
        </div>
    </div>

    @if(session()->has('message'))
        <x-notification-toast type="success" title="Done!" message="{{ session('message') }}" :dismissible="true" />
    @endif

    <!-- Summary -->
    <div class="grid grid-cols-3 gap-4">
        @foreach([
            ['Total Products', $products->count(), 'bg-blue-50 border-blue-200', 'text-blue-700'],
            ['Active',         $activeCount,       'bg-emerald-50 border-emerald-200', 'text-emerald-700'],
            ['Inactive',       $inactiveCount,     'bg-zinc-50 border-zinc-200', 'text-zinc-600'],
        ] as [$label, $count, $style, $textColor])
        <div class="tc-card border {{ $style }} text-center py-4">
            <p class="text-2xl font-bold {{ $textColor }}" style="font-family:'Poppins'">{{ $count }}</p>
            <p class="text-sm text-zinc-600 font-medium mt-0.5">{{ $label }}</p>
        </div>
        @endforeach
    </div>

    <!-- Products Grid -->
    @if($products->count())
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($products as $product)
        <div class="tc-card border {{ $product->is_active ? 'border-zinc-100' : 'border-zinc-200 opacity-70' }} hover-lift group">
            <!-- Image Placeholder -->
            <div class="w-full h-36 rounded-xl bg-gradient-to-br from-primary-50 to-secondary-50 border border-primary-100 flex items-center justify-center mb-4 group-hover:from-primary-100 group-hover:to-secondary-100 transition-all">
                @if($product->image_url)
                    <img src="{{ Storage::url($product->image_url) }}" alt="{{ $product->name }}" class="w-full h-full object-cover rounded-xl">
                @else
                    <svg class="w-10 h-10 text-primary-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                @endif
            </div>

            <div class="flex items-start justify-between mb-2">
                <h3 class="font-bold text-zinc-900 text-sm leading-tight">{{ $product->name }}</h3>
                <div class="flex items-center gap-1 shrink-0 ml-2">
                    <span class="text-base font-bold text-primary-600">₱{{ number_format($product->price, 2) }}</span>
                </div>
            </div>

            @if($product->description)
                <p class="text-xs text-zinc-500 mb-3">{{ Str::limit($product->description, 70) }}</p>
            @endif

            <!-- Sizes -->
            <div class="flex flex-wrap gap-1 mb-3">
                @foreach($product->available_sizes as $size)
                    <span class="px-2 py-0.5 text-xs font-medium bg-zinc-100 text-zinc-600 rounded-md">{{ $size }}</span>
                @endforeach
            </div>

            <!-- Status + Actions -->
            <div class="flex items-center justify-between pt-3 border-t border-zinc-100">
                <button wire:click="toggleActive({{ $product->id }})"
                    class="flex items-center gap-1.5 text-xs font-medium {{ $product->is_active ? 'text-emerald-600' : 'text-zinc-400' }} hover:opacity-70 transition-opacity">
                    <div class="w-2 h-2 rounded-full {{ $product->is_active ? 'bg-emerald-500' : 'bg-zinc-300' }}"></div>
                    {{ $product->is_active ? 'Active' : 'Inactive' }}
                </button>
                <button wire:click="openEdit({{ $product->id }})"
                    class="flex items-center gap-1.5 text-xs font-medium text-primary-600 hover:bg-primary-50 px-2.5 py-1.5 rounded-lg transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    Edit
                </button>
            </div>
        </div>
        @endforeach
    </div>
    @else
        <div class="tc-card py-16 text-center">
            <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-primary-100 flex items-center justify-center">
                <svg class="w-8 h-8 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            </div>
            <h3 class="text-base font-semibold text-zinc-700 mb-2">No products yet</h3>
            <p class="text-sm text-zinc-500 mb-4">Add your first ready-to-wear product to show customers what's available.</p>
            <button wire:click="openAdd" class="px-5 py-2.5 text-sm font-semibold bg-primary-500 text-white rounded-xl hover:bg-primary-600 transition-colors">
                Add First Product
            </button>
        </div>
    @endif

    <!-- Add Modal -->
    @if($showAddModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg border border-zinc-200" @click.stop>
            <div class="p-6">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-lg font-semibold text-zinc-900">Add Pre-Made Product</h3>
                    <button wire:click="closeModals" class="p-2 text-zinc-400 hover:bg-zinc-100 rounded-full transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1.5">Product Name *</label>
                        <input wire:model="name" placeholder="e.g. White Barong Tagalog" class="w-full px-4 py-2.5 border border-zinc-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500" />
                        @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1.5">Description</label>
                        <textarea wire:model="description" rows="2" placeholder="Brief product description..." class="w-full px-4 py-2.5 border border-zinc-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 resize-none"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 mb-1.5">Price (₱) *</label>
                            <input wire:model="price" type="number" step="0.01" placeholder="0.00" class="w-full px-4 py-2.5 border border-zinc-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500" />
                            @error('price') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 mb-1.5">Status</label>
                            <select wire:model="is_active" class="w-full px-4 py-2.5 border border-zinc-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1.5">Available Sizes * <span class="text-zinc-400 font-normal">(comma-separated)</span></label>
                        <input wire:model="sizesInput" placeholder="e.g. S, M, L, XL or 28, 30, 32" class="w-full px-4 py-2.5 border border-zinc-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500" />
                        @error('sizesInput') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="button" wire:click="closeModals" class="flex-1 px-4 py-2.5 text-sm font-medium text-zinc-600 bg-zinc-100 hover:bg-zinc-200 rounded-xl transition-colors">Cancel</button>
                        <button type="submit" class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-primary-500 hover:bg-primary-600 rounded-xl transition-colors">Add Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Edit Modal -->
    @if($showEditModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg border border-zinc-200" @click.stop>
            <div class="p-6">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-lg font-semibold text-zinc-900">Edit Product</h3>
                    <button wire:click="closeModals" class="p-2 text-zinc-400 hover:bg-zinc-100 rounded-full transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form wire:submit="update" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1.5">Product Name *</label>
                        <input wire:model="name" class="w-full px-4 py-2.5 border border-zinc-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500" />
                        @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1.5">Description</label>
                        <textarea wire:model="description" rows="2" class="w-full px-4 py-2.5 border border-zinc-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 resize-none"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 mb-1.5">Price (₱) *</label>
                            <input wire:model="price" type="number" step="0.01" class="w-full px-4 py-2.5 border border-zinc-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500" />
                            @error('price') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 mb-1.5">Status</label>
                            <select wire:model="is_active" class="w-full px-4 py-2.5 border border-zinc-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1.5">Available Sizes *</label>
                        <input wire:model="sizesInput" class="w-full px-4 py-2.5 border border-zinc-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500" />
                        @error('sizesInput') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="button" wire:click="closeModals" class="flex-1 px-4 py-2.5 text-sm font-medium text-zinc-600 bg-zinc-100 hover:bg-zinc-200 rounded-xl transition-colors">Cancel</button>
                        <button type="submit" class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-primary-500 hover:bg-primary-600 rounded-xl transition-colors">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
