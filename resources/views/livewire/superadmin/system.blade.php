<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Models\Shop;

new #[Layout('components.layouts.app')] class extends Component {
    public $shop;
    public string $app_name = '';
    public string $app_url  = '';

    public function mount(): void
    {
        $this->shop     = Shop::instance();
        $this->app_name = config('app.name');
        $this->app_url  = config('app.url');
    }

    public function saveShopProfile(): void
    {
        $this->validate([
            'shop.name'           => 'required|string|max:255',
            'shop.contact_number' => 'nullable|string|max:50',
            'shop.email'          => 'nullable|email|max:255',
            'shop.description'    => 'nullable|string',
            'shop.address'        => 'nullable|string|max:255',
            'shop.barangay'       => 'nullable|string|max:100',
            'shop.city'           => 'nullable|string|max:100',
            'shop.province'       => 'nullable|string|max:100',
        ]);
        $this->shop->save();
        session()->flash('message', 'Business profile updated successfully.');
    }
}; ?>

<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-primary-600 dark:text-primary-400" style="font-family: 'Poppins';">System Configuration</h1>
        <p class="text-zinc-500 dark:text-zinc-400 mt-1">Platform-level settings and deployed instance configuration</p>
    </div>

    @if(session()->has('message'))
        <x-notification-toast type="success" title="Saved!" message="{{ session('message') }}" :dismissible="true" />
    @endif

    <!-- System Info -->
    <div class="tc-card">
        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white mb-5">Platform Information</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @foreach([
                ['Application Name', $app_name, 'cog-6-tooth'],
                ['Application URL', $app_url, 'globe-alt'],
                ['Laravel Version', app()->version(), 'code-bracket'],
                ['PHP Version', PHP_VERSION, 'server'],
                ['Environment', config('app.env'), 'cpu-chip'],
                ['Database', config('database.default'), 'circle-stack'],
            ] as [$label, $value, $icon])
            <div class="flex items-center gap-3 p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-100 dark:border-zinc-700">
                <div class="w-8 h-8 rounded-lg bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center shrink-0">
                    <flux:icon :name="$icon" class="size-4 text-primary-600 dark:text-primary-400" />
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-zinc-500 font-medium">{{ $label }}</p>
                    <p class="text-sm font-semibold text-zinc-900 dark:text-white truncate">{{ $value }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Business Instance Profile -->
    <div class="tc-card">
        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white mb-5">Deployed Business Instance</h2>
        <p class="text-sm text-zinc-500 mb-5">This is the single tailoring business this installation is deployed for. Configure the business profile here.</p>
        <form wire:submit="saveShopProfile" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Business Name</label>
                    <input wire:model="shop.name" class="w-full px-4 py-2.5 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-sm focus:ring-2 focus:ring-primary-500" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Contact Number</label>
                    <input wire:model="shop.contact_number" class="w-full px-4 py-2.5 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-sm focus:ring-2 focus:ring-primary-500" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Email</label>
                    <input wire:model="shop.email" type="email" class="w-full px-4 py-2.5 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-sm focus:ring-2 focus:ring-primary-500" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">City</label>
                    <input wire:model="shop.city" class="w-full px-4 py-2.5 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-sm focus:ring-2 focus:ring-primary-500" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Province</label>
                    <input wire:model="shop.province" class="w-full px-4 py-2.5 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-sm focus:ring-2 focus:ring-primary-500" />
                </div>
            </div>
            <div class="flex justify-end pt-2">
                <button type="submit" class="px-6 py-2.5 text-sm font-medium text-white bg-primary-500 rounded-xl hover:bg-primary-600 transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Save Configuration
                </button>
            </div>
        </form>
    </div>
</div>
