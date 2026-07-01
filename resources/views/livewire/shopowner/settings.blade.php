<?php

use Livewire\Volt\Component;
use App\Models\Shop;

new class extends Component {
    public $shop;

    public function mount()
    {
        $this->shop = Shop::find(auth()->user()->shop_id);
    }
}; ?>

<div>
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-primary-600 dark:text-primary-400">Shop Settings</h1>
        <p class="text-zinc-500 dark:text-zinc-400">Update your shop profile and operational details</p>
    </div>

    <div class="tc-card max-w-3xl">
        <form class="space-y-6">
            <flux:input label="Shop Name" wire:model="shop.name" />
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input label="Contact Number" wire:model="shop.contact_number" />
                <flux:input label="Email Address" wire:model="shop.email" type="email" />
            </div>

            <flux:textarea label="Shop Description" wire:model="shop.description" rows="3" />
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input label="Address" wire:model="shop.address" />
                <flux:input label="Barangay" wire:model="shop.barangay" />
            </div>

            <flux:separator />

            <div class="flex justify-end">
                <flux:button variant="primary">Save Changes</flux:button>
            </div>
        </form>
    </div>
</div>
