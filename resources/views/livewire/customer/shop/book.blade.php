<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\Attributes\Validate;
use App\Models\Shop;
use App\Models\Appointment;

new #[Layout('components.layouts.app')] class extends Component {
    public Shop $shop;
    
    #[Validate('required|date|after_or_equal:today')]
    public $date = '';
    
    #[Validate('required')]
    public $time = '';
    
    #[Validate('required|in:consultation,fitting,pickup')]
    public $type = 'consultation';
    
    #[Validate('nullable|string|max:1000')]
    public $notes = '';

    public function mount(Shop $shop)
    {
        $this->shop = $shop;
    }

    public function book()
    {
        $this->validate();

        Appointment::create([
            'user_id' => auth()->id(),
            'shop_id' => $this->shop->id,
            'date' => $this->date,
            'time' => $this->time,
            'type' => $this->type,
            'status' => 'pending',
            'notes' => $this->notes,
        ]);

        \Flux::toast('Appointment booked successfully!');
        
        $this->redirect(route('customer.appointments'), navigate: true);
    }
}; ?>

<div class="max-w-2xl mx-auto">
    <!-- Back Button -->
    <div class="mb-6">
        <flux:button variant="subtle" icon="arrow-left" :href="route('customer.shop.show', $shop->id)" wire:navigate>Back to Shop</flux:button>
    </div>

    <div class="tc-card">
        <div class="flex items-center gap-4 mb-6 pb-6 border-b border-zinc-200 dark:border-zinc-700">
            <div class="w-16 h-16 rounded-xl bg-primary-100 dark:bg-primary-900 flex items-center justify-center shrink-0">
                <flux:icon.building-storefront class="size-8 text-primary-600 dark:text-primary-300" />
            </div>
            <div>
                <h1 class="text-2xl font-bold font-heading text-primary-700 dark:text-primary-300">Book an Appointment</h1>
                <p class="text-zinc-500">with <span class="font-bold">{{ $shop->name }}</span></p>
            </div>
        </div>

        <form wire:submit="book" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:input type="date" wire:model="date" label="Preferred Date" />
                <flux:select wire:model="time" label="Preferred Time">
                    <flux:select.option value="" disabled>Select a time slot</flux:select.option>
                    <flux:select.option value="09:00:00">09:00 AM</flux:select.option>
                    <flux:select.option value="10:00:00">10:00 AM</flux:select.option>
                    <flux:select.option value="11:00:00">11:00 AM</flux:select.option>
                    <flux:select.option value="13:00:00">01:00 PM</flux:select.option>
                    <flux:select.option value="14:00:00">02:00 PM</flux:select.option>
                    <flux:select.option value="15:00:00">03:00 PM</flux:select.option>
                    <flux:select.option value="16:00:00">04:00 PM</flux:select.option>
                </flux:select>
            </div>

            <flux:select wire:model="type" label="Appointment Type">
                <flux:select.option value="consultation">Initial Consultation / Inquiry</flux:select.option>
                <flux:select.option value="fitting">Garment Fitting & Measurement</flux:select.option>
                <flux:select.option value="pickup">Finished Garment Pickup</flux:select.option>
            </flux:select>

            <flux:textarea wire:model="notes" label="Special Instructions / Notes" placeholder="Let the tailor know what you need (e.g., I want to discuss a 3-piece suit design)" rows="4" />

            <div class="flex justify-end gap-3 pt-4">
                <flux:button variant="ghost" :href="route('customer.shop.show', $shop->id)" wire:navigate>Cancel</flux:button>
                <flux:button variant="primary" type="submit" icon="calendar-days">Confirm Booking</flux:button>
            </div>
        </form>
    </div>
</div>
