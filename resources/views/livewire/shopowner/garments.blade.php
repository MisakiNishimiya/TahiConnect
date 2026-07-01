<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Models\GarmentType;
use App\Models\Fabric;

new #[Layout('components.layouts.app')] class extends Component {
    public function with()
    {
        $shopId = auth()->user()->shop_id;
        $garments = GarmentType::where('shop_id', $shopId)->get();
        $fabrics = Fabric::where('shop_id', $shopId)->get();
        
        return [
            'garments' => $garments,
            'fabrics' => $fabrics,
        ];
    }
}; ?>

<div>
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-primary-600 dark:text-primary-400">Catalog & Fabrics</h1>
        <p class="text-zinc-500 dark:text-zinc-400">Manage what your shop offers</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Garments -->
        <div class="tc-card">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold">Garment Offerings</h2>
                <flux:button size="sm" variant="outline" icon="plus">Add Garment</flux:button>
            </div>
            
            <div class="space-y-4">
                @forelse($garments as $garment)
                    <div class="flex justify-between items-center p-4 border border-zinc-100 dark:border-zinc-700 rounded-xl">
                        <div>
                            <h3 class="font-bold">{{ $garment->name }}</h3>
                            <p class="text-sm text-zinc-500">{{ Str::limit($garment->description, 50) }}</p>
                        </div>
                        <div class="text-right">
                            <span class="font-bold text-primary-600">₱{{ number_format($garment->base_price, 2) }}</span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4 text-zinc-500">No garments added yet.</div>
                @endforelse
            </div>
        </div>

        <!-- Fabrics -->
        <div class="tc-card">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold">Fabric Inventory</h2>
                <flux:button size="sm" variant="outline" icon="plus">Add Fabric</flux:button>
            </div>
            
            <div class="space-y-4">
                @forelse($fabrics as $fabric)
                    <div class="flex justify-between items-center p-4 border border-zinc-100 dark:border-zinc-700 rounded-xl">
                        <div>
                            <h3 class="font-bold">{{ $fabric->name }}</h3>
                            <p class="text-sm text-zinc-500">{{ $fabric->material }} • {{ $fabric->color }}</p>
                        </div>
                        <div class="text-right">
                            <div class="font-bold text-primary-600">₱{{ number_format($fabric->price_per_meter, 2) }}/m</div>
                            @if($fabric->in_stock)
                                <flux:badge color="green" size="sm">In Stock</flux:badge>
                            @else
                                <flux:badge color="red" size="sm">Out of Stock</flux:badge>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4 text-zinc-500">No fabrics added yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
