<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Models\GarmentType;
use App\Models\Fabric;

new #[Layout('components.layouts.app')] class extends Component {
    // ── Modals ────────────────────────────────────────────────────────────────
    public bool $showGarmentModal = false;
    public bool $showFabricModal  = false;
    public ?int $editingGarmentId = null;
    public ?int $editingFabricId  = null;

    // ── Garment form ──────────────────────────────────────────────────────────
    public string $g_name        = '';
    public string $g_description = '';
    public string $g_base_price  = '';

    // ── Fabric form ───────────────────────────────────────────────────────────
    public string $f_name             = '';
    public string $f_material         = '';
    public string $f_color            = '';
    public string $f_price_per_meter  = '';
    public bool   $f_in_stock         = true;

    public function with(): array
    {
        return [
            'garments' => GarmentType::orderBy('name')->get(),
            'fabrics'  => Fabric::orderBy('name')->get(),
        ];
    }

    // ── Garment CRUD ──────────────────────────────────────────────────────────
    public function openAddGarment(): void
    {
        $this->reset(['g_name','g_description','g_base_price','editingGarmentId']);
        $this->showGarmentModal = true;
    }

    public function openEditGarment(int $id): void
    {
        $g = GarmentType::findOrFail($id);
        $this->editingGarmentId = $id;
        $this->g_name           = $g->name;
        $this->g_description    = $g->description ?? '';
        $this->g_base_price     = (string) $g->base_price;
        $this->showGarmentModal = true;
    }

    public function saveGarment(): void
    {
        $this->validate([
            'g_name'       => 'required|string|max:150',
            'g_base_price' => 'required|numeric|min:0',
        ]);

        if ($this->editingGarmentId) {
            GarmentType::findOrFail($this->editingGarmentId)->update([
                'name'        => $this->g_name,
                'description' => $this->g_description,
                'base_price'  => $this->g_base_price,
            ]);
            session()->flash('message', 'Garment type updated.');
        } else {
            GarmentType::create([
                'name'        => $this->g_name,
                'description' => $this->g_description,
                'base_price'  => $this->g_base_price,
            ]);
            session()->flash('message', 'Garment type added.');
        }
        $this->closeModals();
    }

    public function deleteGarment(int $id): void
    {
        GarmentType::findOrFail($id)->delete();
        session()->flash('message', 'Garment type deleted.');
    }

    // ── Fabric CRUD ───────────────────────────────────────────────────────────
    public function openAddFabric(): void
    {
        $this->reset(['f_name','f_material','f_color','f_price_per_meter','f_in_stock','editingFabricId']);
        $this->f_in_stock = true;
        $this->showFabricModal = true;
    }

    public function openEditFabric(int $id): void
    {
        $f = Fabric::findOrFail($id);
        $this->editingFabricId   = $id;
        $this->f_name            = $f->name;
        $this->f_material        = $f->material ?? '';
        $this->f_color           = $f->color    ?? '';
        $this->f_price_per_meter = (string) $f->price_per_meter;
        $this->f_in_stock        = $f->in_stock;
        $this->showFabricModal   = true;
    }

    public function saveFabric(): void
    {
        $this->validate([
            'f_name'            => 'required|string|max:150',
            'f_price_per_meter' => 'required|numeric|min:0',
        ]);

        $data = [
            'name'            => $this->f_name,
            'material'        => $this->f_material,
            'color'           => $this->f_color,
            'price_per_meter' => $this->f_price_per_meter,
            'in_stock'        => $this->f_in_stock,
        ];

        if ($this->editingFabricId) {
            Fabric::findOrFail($this->editingFabricId)->update($data);
            session()->flash('message', 'Fabric updated.');
        } else {
            Fabric::create($data);
            session()->flash('message', 'Fabric added.');
        }
        $this->closeModals();
    }

    public function toggleFabricStock(int $id): void
    {
        $f = Fabric::findOrFail($id);
        $f->update(['in_stock' => !$f->in_stock]);
    }

    public function deleteFabric(int $id): void
    {
        Fabric::findOrFail($id)->delete();
        session()->flash('message', 'Fabric deleted.');
    }

    public function closeModals(): void
    {
        $this->showGarmentModal = false;
        $this->showFabricModal  = false;
        $this->reset(['g_name','g_description','g_base_price','editingGarmentId',
                       'f_name','f_material','f_color','f_price_per_meter','f_in_stock','editingFabricId']);
        $this->f_in_stock = true;
    }
}; ?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-primary-600 dark:text-primary-400" style="font-family: 'Poppins';">Rates & Fabrics</h1>
            <p class="text-zinc-500 mt-1">Manage custom garment pricing and available fabric options</p>
        </div>
        <a href="{{ route('shopowner.garments') }}" wire:navigate
            class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-zinc-600 bg-zinc-100 hover:bg-zinc-200 border border-zinc-200 rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            Pre-Made Catalog
        </a>
    </div>

    @if(session()->has('message'))
        <x-notification-toast type="success" title="Done!" message="{{ session('message') }}" :dismissible="true" />
    @endif

    <!-- Info Banner -->
    <div class="p-4 bg-blue-50 border border-blue-100 rounded-xl flex items-start gap-3">
        <svg class="w-5 h-5 text-blue-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p class="text-sm text-blue-700">These rates are shown to customers on the <strong>Rates & Pricing</strong> page. Garment base prices are starting rates for custom orders — final cost may vary based on fabric and customization.</p>
    </div>

    <!-- ── GARMENT TYPES ────────────────────────────────────────────────────── -->
    <div class="tc-card">
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-zinc-900 dark:text-white" style="font-family:'Poppins'">Custom Garment Types</h2>
                    <p class="text-xs text-zinc-500">{{ $garments->count() }} types · Starting prices for custom orders</p>
                </div>
            </div>
            <button wire:click="openAddGarment"
                class="px-4 py-2 text-sm font-medium bg-emerald-500 text-white rounded-xl hover:bg-emerald-600 transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Garment
            </button>
        </div>

        @if($garments->count())
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-100">
                        <th class="text-left py-3 px-4 font-semibold text-zinc-500">Garment Type</th>
                        <th class="text-left py-3 px-4 font-semibold text-zinc-500 hidden sm:table-cell">Description</th>
                        <th class="text-right py-3 px-4 font-semibold text-zinc-500">Starting Price</th>
                        <th class="text-right py-3 px-4 font-semibold text-zinc-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-50">
                    @foreach($garments as $garment)
                    <tr class="hover:bg-zinc-50 transition-colors group">
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-primary-100 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.813 15.904L9 18.75l-.813-2.846"/></svg>
                                </div>
                                <span class="font-semibold text-zinc-900">{{ $garment->name }}</span>
                            </div>
                        </td>
                        <td class="py-3 px-4 text-zinc-500 text-xs hidden sm:table-cell">{{ $garment->description ? Str::limit($garment->description, 60) : '—' }}</td>
                        <td class="py-3 px-4 text-right font-bold text-primary-600">₱{{ number_format($garment->base_price, 2) }}</td>
                        <td class="py-3 px-4 text-right">
                            <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button wire:click="openEditGarment({{ $garment->id }})"
                                    class="px-3 py-1.5 text-xs font-medium text-primary-600 hover:bg-primary-50 rounded-lg transition-colors">
                                    Edit
                                </button>
                                <button wire:click="deleteGarment({{ $garment->id }})"
                                    wire:confirm="Delete this garment type? This cannot be undone."
                                    class="px-3 py-1.5 text-xs font-medium text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
            <div class="text-center py-10">
                <div class="w-12 h-12 mx-auto mb-3 rounded-2xl bg-zinc-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.813 15.904L9 18.75l-.813-2.846"/></svg>
                </div>
                <p class="text-sm text-zinc-400">No garment types yet. Add your first to set rates for customers.</p>
            </div>
        @endif
    </div>

    <!-- ── FABRICS ──────────────────────────────────────────────────────────── -->
    <div class="tc-card">
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2H7m0 0V9a2 2 0 012-2h10a2 2 0 012 2v4"/></svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-zinc-900 dark:text-white" style="font-family:'Poppins'">Available Fabrics</h2>
                    <p class="text-xs text-zinc-500">{{ $fabrics->count() }} fabrics · {{ $fabrics->where('in_stock', true)->count() }} in stock</p>
                </div>
            </div>
            <button wire:click="openAddFabric"
                class="px-4 py-2 text-sm font-medium bg-amber-500 text-white rounded-xl hover:bg-amber-600 transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Fabric
            </button>
        </div>

        @if($fabrics->count())
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($fabrics as $fabric)
            <div class="flex items-start gap-3 p-4 rounded-xl border {{ $fabric->in_stock ? 'border-zinc-100 bg-zinc-50' : 'border-zinc-100 bg-zinc-50 opacity-70' }} hover:bg-white hover:shadow-sm transition-all duration-200 group">
                <div class="w-10 h-10 rounded-xl bg-secondary-100 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-secondary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4z"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-0.5">
                        <h3 class="font-semibold text-zinc-900 text-sm truncate">{{ $fabric->name }}</h3>
                    </div>
                    <p class="text-xs text-zinc-500">{{ $fabric->material }}{{ $fabric->color ? ' · '.$fabric->color : '' }}</p>
                    <div class="flex items-center justify-between mt-1.5">
                        <span class="text-sm font-bold text-amber-600">₱{{ number_format($fabric->price_per_meter, 2) }}<span class="text-xs font-normal text-zinc-400">/m</span></span>
                        <button wire:click="toggleFabricStock({{ $fabric->id }})"
                            class="text-xs px-2 py-0.5 rounded-full font-medium cursor-pointer transition-all {{ $fabric->in_stock ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' : 'bg-red-100 text-red-600 hover:bg-red-200' }}">
                            {{ $fabric->in_stock ? 'In Stock' : 'Out of Stock' }}
                        </button>
                    </div>
                    <!-- Edit / Delete row -->
                    <div class="flex items-center gap-2 mt-2 pt-2 border-t border-zinc-100 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button wire:click="openEditFabric({{ $fabric->id }})"
                            class="flex-1 text-xs font-medium text-primary-600 hover:bg-primary-50 py-1 rounded-lg transition-colors text-center">
                            Edit
                        </button>
                        <button wire:click="deleteFabric({{ $fabric->id }})"
                            wire:confirm="Delete this fabric? This cannot be undone."
                            class="flex-1 text-xs font-medium text-red-500 hover:bg-red-50 py-1 rounded-lg transition-colors text-center">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
            <div class="text-center py-10">
                <div class="w-12 h-12 mx-auto mb-3 rounded-2xl bg-zinc-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4"/></svg>
                </div>
                <p class="text-sm text-zinc-400">No fabrics yet. Add fabric options for customers to choose from.</p>
            </div>
        @endif
    </div>

    <!-- ── GARMENT MODAL ────────────────────────────────────────────────────── -->
    @if($showGarmentModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md border border-zinc-200" @click.stop>
            <div class="p-6">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-lg font-semibold text-zinc-900">{{ $editingGarmentId ? 'Edit' : 'Add' }} Garment Type</h3>
                    <button wire:click="closeModals" class="p-2 text-zinc-400 hover:bg-zinc-100 rounded-full transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form wire:submit="saveGarment" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1.5">Garment Name *</label>
                        <input wire:model="g_name" placeholder="e.g. Barong Tagalog" class="w-full px-4 py-2.5 border border-zinc-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500" />
                        @error('g_name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1.5">Description</label>
                        <textarea wire:model="g_description" rows="2" placeholder="Brief description shown to customers..." class="w-full px-4 py-2.5 border border-zinc-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 resize-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1.5">Starting Price (₱) *</label>
                        <input wire:model="g_base_price" type="number" step="0.01" placeholder="0.00" class="w-full px-4 py-2.5 border border-zinc-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500" />
                        @error('g_base_price') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="button" wire:click="closeModals" class="flex-1 px-4 py-2.5 text-sm font-medium text-zinc-600 bg-zinc-100 hover:bg-zinc-200 rounded-xl transition-colors">Cancel</button>
                        <button type="submit" class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-emerald-500 hover:bg-emerald-600 rounded-xl transition-colors">{{ $editingGarmentId ? 'Save Changes' : 'Add Garment' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- ── FABRIC MODAL ─────────────────────────────────────────────────────── -->
    @if($showFabricModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md border border-zinc-200" @click.stop>
            <div class="p-6">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-lg font-semibold text-zinc-900">{{ $editingFabricId ? 'Edit' : 'Add' }} Fabric</h3>
                    <button wire:click="closeModals" class="p-2 text-zinc-400 hover:bg-zinc-100 rounded-full transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form wire:submit="saveFabric" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1.5">Fabric Name *</label>
                        <input wire:model="f_name" placeholder="e.g. Jusi" class="w-full px-4 py-2.5 border border-zinc-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500" />
                        @error('f_name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 mb-1.5">Material</label>
                            <input wire:model="f_material" placeholder="e.g. Banana silk" class="w-full px-4 py-2.5 border border-zinc-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 mb-1.5">Color</label>
                            <input wire:model="f_color" placeholder="e.g. Ivory" class="w-full px-4 py-2.5 border border-zinc-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 mb-1.5">Price per Meter (₱) *</label>
                            <input wire:model="f_price_per_meter" type="number" step="0.01" placeholder="0.00" class="w-full px-4 py-2.5 border border-zinc-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500" />
                            @error('f_price_per_meter') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 mb-1.5">Stock Status</label>
                            <select wire:model="f_in_stock" class="w-full px-4 py-2.5 border border-zinc-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500">
                                <option value="1">In Stock</option>
                                <option value="0">Out of Stock</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="button" wire:click="closeModals" class="flex-1 px-4 py-2.5 text-sm font-medium text-zinc-600 bg-zinc-100 hover:bg-zinc-200 rounded-xl transition-colors">Cancel</button>
                        <button type="submit" class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-amber-500 hover:bg-amber-600 rounded-xl transition-colors">{{ $editingFabricId ? 'Save Changes' : 'Add Fabric' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
