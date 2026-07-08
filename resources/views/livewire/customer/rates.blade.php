<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Models\GarmentType;
use App\Models\Fabric;
use App\Models\Shop;

new #[Layout('components.layouts.app')] class extends Component {
    public function with(): array
    {
        return [
            'shop'     => Shop::instance(),
            'garments' => GarmentType::orderBy('base_price')->get(),
            'fabrics'  => Fabric::orderBy('name')->get(),
        ];
    }
}; ?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-primary-600 dark:text-primary-400" style="font-family: 'Poppins';">Rates & Pricing</h1>
            <p class="text-zinc-500 dark:text-zinc-400 mt-1">Garment types and fabric options for custom orders</p>
        </div>
        <a href="{{ route('customer.orders') }}" wire:navigate
            class="flex items-center gap-2 px-5 py-2.5 text-sm font-semibold bg-gradient-to-r from-primary-500 to-primary-600 text-white rounded-xl hover:from-primary-600 hover:to-primary-700 transition-all shadow-lg">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Place Custom Order
        </a>
    </div>

    <!-- How Custom Orders Work -->
    <div class="tc-card bg-gradient-to-br from-primary-50 to-secondary-50 border-primary-100">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-primary-100 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
            </div>
            <div>
                <h2 class="text-base font-bold text-primary-800 mb-1">How Custom Orders Work</h2>
                <p class="text-sm text-primary-700 leading-relaxed">
                    The prices shown are <strong>starting rates</strong> — your final cost may vary based on fabric choice, complexity, and modifications.
                    To place a custom order, go to <strong>My Orders → New Order</strong>, select your garment type and preferred fabric, then submit your request.
                    Our tailors will confirm the final price during your measurement appointment.
                </p>
            </div>
        </div>
    </div>

    <!-- Garment Types -->
    <div class="tc-card">
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-zinc-900 dark:text-white" style="font-family:'Poppins'">Custom Garment Rates</h2>
                    <p class="text-xs text-zinc-500">Starting prices — fabric and customization may add to cost</p>
                </div>
            </div>
            <span class="px-3 py-1 text-xs bg-emerald-100 text-emerald-700 rounded-full font-medium">{{ $garments->count() }} types</span>
        </div>

        @if($garments->count())
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-100">
                        <th class="text-left py-3 px-4 font-semibold text-zinc-500">Garment Type</th>
                        <th class="text-left py-3 px-4 font-semibold text-zinc-500 hidden sm:table-cell">Description</th>
                        <th class="text-right py-3 px-4 font-semibold text-zinc-500">Starting Price</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-50">
                    @foreach($garments as $garment)
                    <tr class="hover:bg-zinc-50 transition-colors group">
                        <td class="py-4 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-primary-100 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                    <svg class="w-4 h-4 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813"/></svg>
                                </div>
                                <span class="font-semibold text-zinc-900">{{ $garment->name }}</span>
                            </div>
                        </td>
                        <td class="py-4 px-4 text-zinc-500 hidden sm:table-cell text-xs">{{ $garment->description ? Str::limit($garment->description, 70) : '—' }}</td>
                        <td class="py-4 px-4 text-right">
                            <span class="text-lg font-bold text-primary-600">₱{{ number_format($garment->base_price, 2) }}</span>
                            <span class="block text-xs text-zinc-400">and up</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
            <div class="text-center py-8 text-zinc-400 text-sm">No garment types listed yet.</div>
        @endif
    </div>

    <!-- Fabrics -->
    <div class="tc-card">
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2H7m0 0V9a2 2 0 012-2h10a2 2 0 012 2v4"/></svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-zinc-900 dark:text-white" style="font-family:'Poppins'">Available Fabrics</h2>
                    <p class="text-xs text-zinc-500">Fabric cost is added to your garment base price</p>
                </div>
            </div>
            <span class="px-3 py-1 text-xs bg-amber-100 text-amber-700 rounded-full font-medium">{{ $fabrics->count() }} options</span>
        </div>

        @if($fabrics->count())
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($fabrics as $fabric)
            <div class="flex items-center gap-3 p-4 rounded-xl border {{ $fabric->in_stock ? 'border-zinc-100 bg-zinc-50 hover:bg-white hover:border-zinc-200 hover:shadow-sm' : 'border-zinc-100 bg-zinc-50 opacity-60' }} transition-all duration-200">
                <div class="w-10 h-10 rounded-xl bg-secondary-100 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-secondary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4z"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <h3 class="font-semibold text-zinc-900 text-sm truncate">{{ $fabric->name }}</h3>
                        @if(!$fabric->in_stock)
                            <span class="text-[10px] px-1.5 py-0.5 bg-red-100 text-red-600 rounded font-medium shrink-0">Out of Stock</span>
                        @endif
                    </div>
                    <p class="text-xs text-zinc-500">{{ $fabric->material }}{{ $fabric->color ? ' · '.$fabric->color : '' }}</p>
                    <p class="text-sm font-bold text-amber-600 mt-0.5">₱{{ number_format($fabric->price_per_meter, 2) }}<span class="text-xs font-normal text-zinc-400">/meter</span></p>
                </div>
            </div>
            @endforeach
        </div>
        @else
            <div class="text-center py-8 text-zinc-400 text-sm">No fabrics listed yet.</div>
        @endif
    </div>

    <!-- CTA -->
    <div class="tc-card bg-gradient-to-br from-primary-600 to-primary-800 border-0 text-center py-8">
        <h2 class="text-xl font-bold text-white mb-2" style="font-family:'Poppins'">Ready to place your custom order?</h2>
        <p class="text-primary-200 text-sm mb-5">Go to My Orders and click "New Order" to get started</p>
        <a href="{{ route('customer.orders') }}" wire:navigate
            class="inline-flex items-center gap-2 px-6 py-3 bg-white text-primary-700 font-semibold rounded-xl hover:bg-primary-50 transition-colors shadow-lg">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            New Custom Order
        </a>
    </div>
</div>
