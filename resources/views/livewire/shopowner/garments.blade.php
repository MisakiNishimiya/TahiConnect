<?php
/**
 * Shop Owner — Pre-Made Products Catalog Management
 *
 * Includes Tolerance-Based Measurement Validation configuration:
 * Shop owners can set measurement_standards (GM) and measurement_tolerances (T)
 * per product so the customer-facing compatibility algorithm can function.
 *
 * For each required measurement:
 *   MV = |CM - GM|  →  Valid when MV <= T
 */

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Models\PreMadeProduct;

new #[Layout('components.layouts.app')] class extends Component {
    public bool $showAddModal       = false;
    public bool $showEditModal      = false;
    public bool $showStandardsModal = false;
    public ?int $editingId          = null;
    public ?int $standardsProductId = null;

    // Basic product fields
    public string $name        = '';
    public string $description = '';
    public string $price       = '';
    public string $sizesInput  = '';
    public bool   $is_active   = true;

    // Measurement standards/tolerances — keyed by field name
    // Each entry: ['standard' => '', 'tolerance' => '']
    public array $standards = [];

    // The 7 measurable fields the algorithm supports
    public array $measurementFields = [
        'chest'         => 'Chest (cm)',
        'waist'         => 'Waist (cm)',
        'hip'           => 'Hip (cm)',
        'shoulder'      => 'Shoulder (cm)',
        'sleeve_length' => 'Sleeve Length (cm)',
        'inseam'        => 'Inseam (cm)',
        'neck'          => 'Neck (cm)',
    ];

    public function mount(): void
    {
        // Initialise standards array with empty values
        foreach (array_keys($this->measurementFields) as $field) {
            $this->standards[$field] = ['standard' => '', 'tolerance' => ''];
        }
    }

    public function with(): array
    {
        return [
            'products'      => PreMadeProduct::latest()->get(),
            'activeCount'   => PreMadeProduct::where('is_active', true)->count(),
            'inactiveCount' => PreMadeProduct::where('is_active', false)->count(),
        ];
    }

    // ── Open/close ────────────────────────────────────────────────────────────

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

    /** Open the measurement standards & tolerances editor for a product */
    public function openStandards(int $id): void
    {
        $p = PreMadeProduct::findOrFail($id);
        $this->standardsProductId = $id;

        $existingStandards  = $p->measurement_standards  ?? [];
        $existingTolerances = $p->measurement_tolerances ?? [];

        foreach (array_keys($this->measurementFields) as $field) {
            $this->standards[$field] = [
                'standard'  => isset($existingStandards[$field])  ? (string) $existingStandards[$field]  : '',
                'tolerance' => isset($existingTolerances[$field]) ? (string) $existingTolerances[$field] : '',
            ];
        }
        $this->showStandardsModal = true;
    }

    // ── Save / update ─────────────────────────────────────────────────────────

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
        session()->flash('message', 'Product added. You can now set its measurement standards via the ruler icon.');
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

    /** Save measurement_standards and measurement_tolerances for a product */
    public function saveStandards(): void
    {
        // Validate each enabled field pair
        $rules = [];
        foreach (array_keys($this->measurementFields) as $field) {
            $rules["standards.{$field}.standard"]  = 'nullable|numeric|min:0|max:300';
            $rules["standards.{$field}.tolerance"] = 'nullable|numeric|min:0|max:50';
        }
        $this->validate($rules);

        $stdOut = [];
        $tolOut = [];

        foreach (array_keys($this->measurementFields) as $field) {
            $std = $this->standards[$field]['standard'] ?? '';
            $tol = $this->standards[$field]['tolerance'] ?? '';
            // Only store fields where both a standard AND tolerance are provided
            if ($std !== '' && $tol !== '') {
                $stdOut[$field] = (float) $std;
                $tolOut[$field] = (float) $tol;
            }
        }

        PreMadeProduct::findOrFail($this->standardsProductId)->update([
            'measurement_standards'  => !empty($stdOut) ? $stdOut : null,
            'measurement_tolerances' => !empty($tolOut) ? $tolOut : null,
        ]);

        $this->showStandardsModal = false;
        $this->standardsProductId = null;
        session()->flash('message', 'Measurement standards and tolerances saved.');
    }

    public function toggleActive(int $id): void
    {
        $p = PreMadeProduct::findOrFail($id);
        $p->update(['is_active' => !$p->is_active]);
    }

    public function closeModals(): void
    {
        $this->showAddModal       = false;
        $this->showEditModal      = false;
        $this->showStandardsModal = false;
        $this->standardsProductId = null;
        $this->reset(['name','description','price','sizesInput','is_active','editingId']);
        // Re-initialise standards to blank
        foreach (array_keys($this->measurementFields) as $field) {
            $this->standards[$field] = ['standard' => '', 'tolerance' => ''];
        }
    }
}; ?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-primary-600 dark:text-primary-400" style="font-family: 'Poppins';">Pre-Made Products Catalog</h1>
            <p class="text-zinc-500 mt-1">Manage ready-to-wear items and their measurement compatibility standards</p>
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

    <!-- Info banner about measurement standards -->
    <div class="p-4 bg-blue-50 border border-blue-100 rounded-xl flex items-start gap-3">
        <svg class="w-5 h-5 text-blue-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <div>
            <p class="text-sm font-medium text-blue-800">Tolerance-Based Measurement Compatibility</p>
            <p class="text-xs text-blue-700 mt-0.5">Set <strong>measurement standards (GM)</strong> and <strong>tolerance values (T)</strong> on each product via the ruler icon. When a customer orders, their body measurements are compared using <code class="bg-blue-100 px-1 rounded">MV = |CM − GM|</code>. If MV ≤ T the measurement is valid; otherwise the garment is incompatible.</p>
        </div>
    </div>

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
            <!-- Image -->
            <div class="w-full h-36 rounded-xl bg-gradient-to-br from-primary-50 to-secondary-50 border border-primary-100 flex items-center justify-center mb-4 overflow-hidden">
                @if($product->image_url)
                    <img src="{{ Storage::url($product->image_url) }}" alt="{{ $product->name }}" class="w-full h-full object-cover rounded-xl">
                @else
                    <svg class="w-10 h-10 text-primary-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                @endif
            </div>

            <div class="flex items-start justify-between mb-2">
                <h3 class="font-bold text-zinc-900 text-sm leading-tight">{{ $product->name }}</h3>
                <span class="text-base font-bold text-primary-600 shrink-0 ml-2">₱{{ number_format($product->price, 2) }}</span>
            </div>

            @if($product->description)
                <p class="text-xs text-zinc-500 mb-2">{{ Str::limit($product->description, 70) }}</p>
            @endif

            <!-- Sizes -->
            <div class="flex flex-wrap gap-1 mb-3">
                @foreach($product->available_sizes as $size)
                    <span class="px-2 py-0.5 text-xs font-medium bg-zinc-100 text-zinc-600 rounded-md">{{ $size }}</span>
                @endforeach
            </div>

            <!-- Measurement standards badge -->
            <div class="mb-3">
                @if($product->hasMeasurementStandards())
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ count($product->measurement_standards) }} measurement{{ count($product->measurement_standards) !== 1 ? 's' : '' }} configured
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200 rounded-lg">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                        No standards set — skips validation
                    </span>
                @endif
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between pt-3 border-t border-zinc-100 gap-2">
                <button wire:click="toggleActive({{ $product->id }})"
                    class="flex items-center gap-1.5 text-xs font-medium {{ $product->is_active ? 'text-emerald-600' : 'text-zinc-400' }} hover:opacity-70 transition-opacity">
                    <div class="w-2 h-2 rounded-full {{ $product->is_active ? 'bg-emerald-500' : 'bg-zinc-300' }}"></div>
                    {{ $product->is_active ? 'Active' : 'Inactive' }}
                </button>
                <div class="flex items-center gap-1">
                    <!-- Measurement standards button -->
                    <button wire:click="openStandards({{ $product->id }})" title="Set measurement standards & tolerances"
                        class="flex items-center gap-1 text-xs font-medium text-blue-600 hover:bg-blue-50 px-2.5 py-1.5 rounded-lg transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3"/></svg>
                        Standards
                    </button>
                    <button wire:click="openEdit({{ $product->id }})"
                        class="flex items-center gap-1 text-xs font-medium text-primary-600 hover:bg-primary-50 px-2.5 py-1.5 rounded-lg transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        Edit
                    </button>
                </div>
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

    {{-- ── ADD MODAL ──────────────────────────────────────────────────────────── --}}
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
                    <p class="text-xs text-zinc-400">After saving, use the <strong>Standards</strong> button to configure measurement tolerances for compatibility checking.</p>
                    <div class="flex gap-3 pt-2">
                        <button type="button" wire:click="closeModals" class="flex-1 px-4 py-2.5 text-sm font-medium text-zinc-600 bg-zinc-100 hover:bg-zinc-200 rounded-xl transition-colors">Cancel</button>
                        <button type="submit" class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-primary-500 hover:bg-primary-600 rounded-xl transition-colors">Add Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- ── EDIT MODAL ─────────────────────────────────────────────────────────── --}}
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

    {{-- ── MEASUREMENT STANDARDS & TOLERANCES MODAL ────────────────────────────── --}}
    @if($showStandardsModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 overflow-y-auto">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl border border-zinc-200 my-auto" @click.stop>
            <div class="p-6">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-blue-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5"/></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-zinc-900">Measurement Standards & Tolerances</h3>
                    </div>
                    <button wire:click="closeModals" class="p-2 text-zinc-400 hover:bg-zinc-100 rounded-full transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <p class="text-sm text-zinc-500 mb-4">
                    Set the expected garment measurement <strong>(GM)</strong> and the maximum allowed variance <strong>(T)</strong> for each field.
                    Leave a row blank to exclude it from validation.
                    When a customer orders, <code class="bg-zinc-100 px-1 rounded text-xs">MV = |CM − GM|</code> is computed per field.
                    The garment is <strong>compatible</strong> only when all enabled fields satisfy <code class="bg-zinc-100 px-1 rounded text-xs">MV ≤ T</code>.
                </p>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-zinc-100 bg-zinc-50 rounded-xl">
                                <th class="text-left py-2.5 px-4 font-semibold text-zinc-600 text-xs uppercase tracking-wide">Measurement</th>
                                <th class="text-left py-2.5 px-4 font-semibold text-zinc-600 text-xs uppercase tracking-wide">Garment Standard (GM) cm</th>
                                <th class="text-left py-2.5 px-4 font-semibold text-zinc-600 text-xs uppercase tracking-wide">Tolerance (T) cm</th>
                                <th class="py-2.5 px-4 font-semibold text-zinc-600 text-xs uppercase tracking-wide text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-50">
                            @foreach($measurementFields as $field => $label)
                            <tr class="hover:bg-zinc-50/50 transition-colors">
                                <td class="py-3 px-4">
                                    <span class="font-medium text-zinc-800">{{ $label }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    <input type="number" step="0.1" wire:model.live="standards.{{ $field }}.standard"
                                        placeholder="e.g. 96"
                                        class="w-full px-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all" />
                                    @error("standards.{$field}.standard") <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                                </td>
                                <td class="py-3 px-4">
                                    <input type="number" step="0.1" wire:model.live="standards.{{ $field }}.tolerance"
                                        placeholder="e.g. 5"
                                        class="w-full px-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all" />
                                    @error("standards.{$field}.tolerance") <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @php
                                        $hasStd = isset($standards[$field]['standard'])  && $standards[$field]['standard']  !== '';
                                        $hasTol = isset($standards[$field]['tolerance']) && $standards[$field]['tolerance'] !== '';
                                        $enabled = $hasStd && $hasTol;
                                    @endphp
                                    @if($enabled)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded-full text-xs font-medium">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            Enabled
                                        </span>
                                    @else
                                        <span class="inline-flex px-2 py-0.5 bg-zinc-100 text-zinc-400 rounded-full text-xs font-medium">Skipped</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 p-3 bg-amber-50 border border-amber-100 rounded-xl text-xs text-amber-700">
                    <strong>Tip:</strong> Only rows with both GM and T filled will be included in validation. A product with no rows filled will skip compatibility checking entirely (treated as compatible for all customers).
                </div>

                <div class="flex gap-3 pt-5 border-t border-zinc-100 mt-5">
                    <button type="button" wire:click="closeModals"
                        class="flex-1 px-4 py-2.5 text-sm font-medium text-zinc-600 bg-zinc-100 hover:bg-zinc-200 rounded-xl transition-colors">
                        Cancel
                    </button>
                    <button wire:click="saveStandards"
                        class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors flex items-center justify-center gap-2">
                        <span wire:loading.remove wire:target="saveStandards">Save Standards & Tolerances</span>
                        <span wire:loading wire:target="saveStandards" class="flex items-center gap-2">
                            <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                            Saving...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>
