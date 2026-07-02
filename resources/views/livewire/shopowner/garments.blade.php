<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Models\GarmentType;
use App\Models\Fabric;

new #[Layout('components.layouts.app')] class extends Component {
    public function with(): array
    {
        $shopId = auth()->user()->shop_id;
        return [
            'garments' => GarmentType::where('shop_id', $shopId)->get(),
            'fabrics' => Fabric::where('shop_id', $shopId)->get(),
        ];
    }
}; ?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-primary-600 dark:text-primary-400" style="font-family: 'Poppins';">Catalog & Fabrics</h1>
            <p class="text-zinc-500 mt-1">Manage your garment offerings and fabric inventory</p>
        </div>
        <div class="flex gap-3">
            <button class="px-5 py-2.5 text-sm font-medium bg-primary-500 text-white rounded-xl hover:bg-primary-600 transition-colors click-feedback flex items-center gap-2 shadow-lg hover:shadow-xl hover:shadow-primary-500/25">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Item
            </button>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        @foreach([
            ['Garments', $garments->count(), 'from-emerald-100 to-emerald-200 dark:from-emerald-900/30 dark:to-emerald-800/30', 'text-emerald-600 dark:text-emerald-400', 'border-emerald-200 dark:border-emerald-800'],
            ['Fabrics', $fabrics->count(), 'from-amber-100 to-amber-200 dark:from-amber-900/30 dark:to-amber-800/30', 'text-amber-600 dark:text-amber-400', 'border-amber-200 dark:border-amber-800'],
            ['In Stock', $fabrics->where('in_stock', true)->count(), 'from-blue-100 to-blue-200 dark:from-blue-900/30 dark:to-blue-800/30', 'text-blue-600 dark:text-blue-400', 'border-blue-200 dark:border-blue-800'],
            ['Out of Stock', $fabrics->where('in_stock', false)->count(), 'from-red-100 to-red-200 dark:from-red-900/30 dark:to-red-800/30', 'text-red-600 dark:text-red-400', 'border-red-200 dark:border-red-800'],
        ] as [$label, $count, $bg, $color, $border])
        <div class="tc-card bg-gradient-to-br {{ $bg }} {{ $border }} hover-lift">
            <p class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family:'Poppins'">{{ $count }}</p>
            <p class="text-sm {{ $color }} font-medium mt-1">{{ $label }}</p>
        </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Garment Offerings -->
        <div class="tc-card">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
                    </div>
                    <h2 class="text-xl font-bold text-zinc-900 dark:text-white">Garment Offerings</h2>
                </div>
                <button class="p-2 text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-xl transition-colors click-feedback" title="Add Garment">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </button>
            </div>
            <div class="space-y-3">
                @forelse($garments as $garment)
                    <div class="flex items-center justify-between p-4 rounded-2xl border border-zinc-100 dark:border-zinc-700 bg-gradient-to-r from-zinc-50 to-white dark:from-zinc-800/50 dark:to-zinc-900/50 hover:shadow-sm hover:-translate-y-0.5 transition-all duration-300 group">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-100 to-primary-200 dark:from-primary-900/30 dark:to-primary-800/30 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-zinc-900 dark:text-white">{{ $garment->name }}</h3>
                                <p class="text-xs text-zinc-500 mt-0.5">{{ Str::limit($garment->description, 45) }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 shrink-0">
                            <span class="text-lg font-bold text-primary-600 dark:text-primary-400">₱{{ number_format($garment->base_price, 2) }}</span>
                            <button class="p-1.5 text-zinc-400 hover:text-zinc-600 hover:bg-zinc-100 dark:hover:bg-zinc-700 rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            </button>
                        </div>
                    </div>
                @empty
                    <x-enhanced-empty-state icon="sparkles" title="No garments yet" description="Add your first garment type to attract customers." :actions="[['type'=>'primary','label'=>'Add Garment','onclick'=>'']]" />
                @endforelse
            </div>
        </div>

        <!-- Fabric Inventory -->
        <div class="tc-card">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-amber-600 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2H7m0 0V9a2 2 0 012-2h10a2 2 0 012 2v4"/></svg>
                    </div>
                    <h2 class="text-xl font-bold text-zinc-900 dark:text-white">Fabric Inventory</h2>
                </div>
                <button class="p-2 text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-xl transition-colors click-feedback" title="Add Fabric">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </button>
            </div>
            <div class="space-y-3">
                @forelse($fabrics as $fabric)
                    <div class="flex items-center justify-between p-4 rounded-2xl border border-zinc-100 dark:border-zinc-700 bg-gradient-to-r from-zinc-50 to-white dark:from-zinc-800/50 dark:to-zinc-900/50 hover:shadow-sm hover:-translate-y-0.5 transition-all duration-300 group">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-secondary-100 to-secondary-200 dark:from-secondary-900/30 dark:to-secondary-800/30 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-5 h-5 text-secondary-600 dark:text-secondary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4z"/></svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-zinc-900 dark:text-white">{{ $fabric->name }}</h3>
                                <p class="text-xs text-zinc-500">{{ $fabric->material }}{{ $fabric->color ? ' · '.$fabric->color : '' }}</p>
                            </div>
                        </div>
                        <div class="flex flex-col items-end gap-1 shrink-0">
                            <span class="font-bold text-secondary-600 dark:text-secondary-400">₱{{ number_format($fabric->price_per_meter, 2) }}<span class="text-xs font-normal text-zinc-500">/m</span></span>
                            @if($fabric->in_stock)
                                <span class="text-xs px-2 py-0.5 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 rounded-full font-medium">In Stock</span>
                            @else
                                <span class="text-xs px-2 py-0.5 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 rounded-full font-medium">Out of Stock</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <x-enhanced-empty-state icon="sparkles" title="No fabrics yet" description="Add your fabric options to help customers choose materials." :actions="[['type'=>'primary','label'=>'Add Fabric','onclick'=>'']]" />
                @endforelse
            </div>
        </div>
    </div>
</div>
