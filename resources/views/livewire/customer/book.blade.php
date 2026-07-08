<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Models\Shop;
use App\Models\AvailableTimeSlot;
use App\Models\Appointment;

new #[Layout('components.layouts.app')] class extends Component {

    public string $date        = '';
    public string $time        = '';
    public string $type        = 'initial_measurement';
    public string $notes       = '';
    public string $order_key   = '';   // required only for 'adjustment' type
    public bool   $submitted   = false;

    public function with(): array
    {
        $shop = Shop::instance();
        $slots = $this->date
            ? AvailableTimeSlot::whereDate('date', $this->date)
                ->where('is_available', true)
                ->orderBy('start_time')
                ->get()
            : collect();

        return compact('shop', 'slots');
    }

    public function book(): void
    {
        $rules = [
            'date'  => 'required|date|after_or_equal:today',
            'time'  => 'required',
            'type'  => 'required|string',
            'notes' => 'nullable|string|max:1000',
        ];

        // Require order key only when type is adjustment
        if ($this->type === 'adjustment') {
            $rules['order_key'] = 'required|string|min:3';
        }

        $this->validate($rules, [
            'order_key.required' => 'Please enter the Order Key printed on your receipt for adjustment requests.',
        ]);

        $shop = Shop::instance();

        Appointment::create([
            'user_id' => auth()->id(),
            'shop_id' => $shop->id,
            'date'    => $this->date,
            'time'    => $this->time,
            'type'    => $this->type,
            'notes'   => $this->notes . ($this->order_key ? "\n[Order Key: {$this->order_key}]" : ''),
            'status'  => 'pending',
        ]);

        $this->submitted = true;
        $this->reset(['date', 'time', 'type', 'notes', 'order_key']);
        session()->flash('message', 'Appointment booked successfully! We will confirm shortly.');
    }
}; ?>

<div class="space-y-6 max-w-2xl mx-auto">
    <div>
        <h1 class="text-3xl font-bold text-primary-600 dark:text-primary-400" style="font-family: 'Poppins';">Book an Appointment</h1>
        <p class="text-zinc-500 mt-1">Schedule a visit with our tailors</p>
    </div>

    @if (session()->has('message'))
        <x-notification-toast type="success" title="Booked!" message="{{ session('message') }}" :dismissible="true" />
    @endif

    <div class="tc-card">
        <form wire:submit="book" class="space-y-6">
            <!-- Appointment Type -->
            <div>
                <label class="block text-sm font-semibold text-zinc-700 dark:text-zinc-300 mb-2">Appointment Type <span class="text-red-500">*</span></label>
                <select wire:model.live="type" class="w-full px-4 py-3 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white focus:ring-2 focus:ring-primary-500 transition-colors">
                    <option value="initial_measurement">Initial Measurement & Consultation</option>
                    <option value="fabric_selection">Fabric Selection</option>
                    <option value="baste_fitting">Baste Fitting</option>
                    <option value="final_pickup">Final Adjustments & Pickup</option>
                    <option value="consultation">General Consultation</option>
                    <option value="fitting">Fitting</option>
                    <option value="adjustment">Adjustment Request (return for alteration)</option>
                </select>
                @error('type') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Order Key field — only shows when type = adjustment -->
            @if($type === 'adjustment')
            <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl space-y-3">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"/></svg>
                    <div>
                        <p class="text-sm font-semibold text-amber-800">Order Key Required for Adjustment</p>
                        <p class="text-xs text-amber-700 mt-0.5">Enter the unique Order Key found on your receipt or in <strong>My Orders</strong>. This links your appointment to the specific order that needs adjustment.</p>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-zinc-700 mb-1.5">Order Key <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg class="w-4 h-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"/></svg>
                        </div>
                        <input wire:model="order_key"
                            placeholder="e.g. TC-2025-00123"
                            class="w-full pl-10 pr-4 py-3 border border-amber-300 rounded-xl bg-white text-zinc-900 font-mono focus:ring-2 focus:ring-amber-400 focus:border-amber-400 transition-colors"
                        />
                    </div>
                    @error('order_key') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
                    <p class="text-xs text-zinc-500 mt-1.5 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Find your Order Key in My Orders → Order Details, or on your pickup receipt.
                    </p>
                </div>
            </div>
            @endif

            <!-- Date -->
            <div>
                <label class="block text-sm font-semibold text-zinc-700 dark:text-zinc-300 mb-2">Preferred Date <span class="text-red-500">*</span></label>
                <input wire:model.live="date" type="date" min="{{ now()->toDateString() }}"
                    class="w-full px-4 py-3 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white focus:ring-2 focus:ring-primary-500 transition-colors" />
                @error('date') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Time -->
            <div>
                <label class="block text-sm font-semibold text-zinc-700 dark:text-zinc-300 mb-2">Preferred Time <span class="text-red-500">*</span></label>
                @if($date && $slots->count())
                    <div class="grid grid-cols-3 sm:grid-cols-4 gap-2">
                        @foreach($slots as $slot)
                            <button type="button" wire:click="$set('time', '{{ $slot->start_time }}')"
                                class="py-2 px-3 text-sm font-medium rounded-xl border transition-all {{ $time === $slot->start_time ? 'bg-primary-500 text-white border-primary-500' : 'bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border-zinc-200 dark:border-zinc-700 hover:border-primary-400' }}">
                                {{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }}
                            </button>
                        @endforeach
                    </div>
                @else
                    <input wire:model="time" type="time"
                        class="w-full px-4 py-3 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white focus:ring-2 focus:ring-primary-500 transition-colors" />
                @endif
                @error('time') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-sm font-semibold text-zinc-700 dark:text-zinc-300 mb-2">Notes (optional)</label>
                <textarea wire:model="notes" rows="3" placeholder="Any specific requirements or questions..."
                    class="w-full px-4 py-3 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white focus:ring-2 focus:ring-primary-500 transition-colors resize-none"></textarea>
                @error('notes') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="pt-4 border-t border-zinc-100 dark:border-zinc-700 flex gap-3 justify-end">
                <a href="{{ route('customer.appointments') }}" wire:navigate
                    class="px-6 py-3 text-sm font-medium text-zinc-600 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700 rounded-xl hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                    View My Appointments
                </a>
                <button type="submit"
                    class="px-8 py-3 bg-gradient-to-r from-primary-500 to-primary-600 text-white font-semibold rounded-xl hover:from-primary-600 hover:to-primary-700 transition-all duration-300 shadow-lg flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Book Appointment
                </button>
            </div>
        </form>
    </div>
</div>
