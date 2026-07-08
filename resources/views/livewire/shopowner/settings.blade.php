<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Models\Shop;

new #[Layout('components.layouts.app')] class extends Component {
    public $shop;
    public bool $saved = false;

    public function mount()
    {
        $this->shop = Shop::instance();
    }

    public function save(): void
    {
        $this->validate([
            'shop.name'           => 'required|string|max:255',
            'shop.contact_number' => 'nullable|string|max:50',
            'shop.email'          => 'nullable|email|max:255',
            'shop.description'    => 'nullable|string',
            'shop.address'        => 'nullable|string|max:255',
            'shop.barangay'       => 'nullable|string|max:100',
        ]);

        $this->shop->save();
        $this->saved = true;
        session()->flash('message', 'Shop settings saved successfully!');
    }
}; ?>

<div class="space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-3xl font-bold text-primary-600 dark:text-primary-400" style="font-family: 'Poppins';">Shop Settings</h1>
        <p class="text-zinc-500 mt-1">Update your shop profile and operational details</p>
    </div>

    @if (session()->has('message'))
        <x-notification-toast type="success" title="Saved!" message="{{ session('message') }}" :dismissible="true" />
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Form -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Info -->
            <div class="tc-card">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349"/></svg>
                    </div>
                    <h2 class="text-xl font-bold text-zinc-900 dark:text-white">Basic Information</h2>
                </div>
                <form wire:submit="save" class="space-y-5">
                    <div>
                        <label class="block text-sm font-semibold text-zinc-700 dark:text-zinc-300 mb-2">Shop Name</label>
                        <input wire:model="shop.name" class="w-full px-4 py-3 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white focus:ring-2 focus:ring-primary-500 transition-colors" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-zinc-700 dark:text-zinc-300 mb-2">Contact Number</label>
                            <input wire:model="shop.contact_number" class="w-full px-4 py-3 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white focus:ring-2 focus:ring-primary-500 transition-colors" />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-zinc-700 dark:text-zinc-300 mb-2">Email Address</label>
                            <input wire:model="shop.email" type="email" class="w-full px-4 py-3 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white focus:ring-2 focus:ring-primary-500 transition-colors" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-zinc-700 dark:text-zinc-300 mb-2">Shop Description</label>
                        <textarea wire:model="shop.description" rows="4" placeholder="Describe your shop, specialties, and what makes you unique..." class="w-full px-4 py-3 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white focus:ring-2 focus:ring-primary-500 transition-colors resize-none"></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-zinc-700 dark:text-zinc-300 mb-2">Address</label>
                            <input wire:model="shop.address" class="w-full px-4 py-3 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white focus:ring-2 focus:ring-primary-500 transition-colors" />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-zinc-700 dark:text-zinc-300 mb-2">Barangay</label>
                            <input wire:model="shop.barangay" class="w-full px-4 py-3 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white focus:ring-2 focus:ring-primary-500 transition-colors" />
                        </div>
                    </div>

                    <div class="pt-4 border-t border-zinc-100 dark:border-zinc-700 flex justify-end">
                        <button type="submit" class="px-8 py-3 bg-gradient-to-r from-primary-500 to-primary-600 text-white font-semibold rounded-xl hover:from-primary-600 hover:to-primary-700 transition-all duration-300 shadow-lg hover:shadow-xl hover:shadow-primary-500/25 click-feedback flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Shop Preview Card -->
            <div class="tc-card bg-gradient-to-br from-primary-50 to-secondary-50 dark:from-primary-900/20 dark:to-secondary-900/20 border-primary-100 dark:border-primary-800">
                <h3 class="font-bold text-zinc-900 dark:text-white mb-4">Shop Preview</h3>
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary-100 to-primary-200 dark:from-primary-800 dark:to-primary-700 flex items-center justify-center border-2 border-white dark:border-zinc-700 shadow">
                        <svg class="w-8 h-8 text-primary-600 dark:text-primary-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18"/></svg>
                    </div>
                    <div>
                        <p class="font-bold text-zinc-900 dark:text-white">{{ $shop->name ?? 'Your Shop' }}</p>
                        <p class="text-xs text-zinc-500">{{ $shop->barangay ?? 'Location not set' }}</p>
                    </div>
                </div>
                <p class="text-sm text-zinc-600 dark:text-zinc-400 line-clamp-3">{{ $shop->description ?? 'No description set yet.' }}</p>
            </div>

            <!-- Quick Info -->
            <div class="tc-card">
                <h3 class="font-bold text-zinc-900 dark:text-white mb-4">Quick Info</h3>
                <div class="space-y-3">
                    <div class="flex items-center gap-3 text-sm">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        </div>
                        <span class="text-zinc-600 dark:text-zinc-400">{{ $shop->contact_number ?? 'No contact number' }}</span>
                    </div>
                    <div class="flex items-center gap-3 text-sm">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                            <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <span class="text-zinc-600 dark:text-zinc-400">{{ $shop->email ?? 'No email set' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
