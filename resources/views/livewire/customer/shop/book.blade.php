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
    
    #[Validate('required|in:initial_measurement,fabric_selection,baste_fitting,final_pickup')]
    public $type = 'initial_measurement';
    
    #[Validate('nullable|string|max:1000')]
    public $notes = '';

    public $isLoading = false;

    public function mount(Shop $shop)
    {
        $this->shop = $shop;
    }

    public function book()
    {
        $this->isLoading = true;
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

        session()->flash('message', 'Appointment booked successfully! We\'ll notify you once confirmed.');
        
        $this->redirect(route('customer.appointments'), navigate: true);
    }

    public function getTimeSlots()
    {
        return [
            '09:00:00' => '9:00 AM - Morning Session',
            '10:00:00' => '10:00 AM - Mid Morning',
            '11:00:00' => '11:00 AM - Late Morning',
            '13:00:00' => '1:00 PM - Early Afternoon',
            '14:00:00' => '2:00 PM - Mid Afternoon',
            '15:00:00' => '3:00 PM - Late Afternoon',
            '16:00:00' => '4:00 PM - Evening Session',
        ];
    }

    public function getAppointmentTypes()
    {
        return [
            'initial_measurement' => [
                'name' => 'Initial Measurement & Consultation',
                'description' => 'First meeting to discuss design and take measurements',
                'duration' => '45-60 minutes',
                'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
            ],
            'fabric_selection' => [
                'name' => 'Fabric Selection & Touch-and-Feel',
                'description' => 'Choose materials and finalize design details',
                'duration' => '30-45 minutes',
                'icon' => 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4z'
            ],
            'baste_fitting' => [
                'name' => 'Baste Fitting (Adjustment & Fitting)',
                'description' => 'Try on the basted garment for adjustments',
                'duration' => '30-45 minutes',
                'icon' => 'M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z'
            ],
            'final_pickup' => [
                'name' => 'Final Adjustments & Pickup',
                'description' => 'Final fitting and pickup of completed garment',
                'duration' => '15-30 minutes',
                'icon' => 'M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z'
            ]
        ];
    }
}; ?>

<div class="max-w-4xl mx-auto space-y-8" 
     x-data="{ isLoading: @entangle('isLoading'), selectedType: @entangle('type') }">

    <!-- Back Button -->
    <div class="flex items-center gap-3">
        <button 
            onclick="window.history.back()" 
            class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-zinc-600 dark:text-zinc-400 hover:text-primary-600 dark:hover:text-primary-400 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-all duration-300 click-feedback"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to {{ $shop->name }}
        </button>
    </div>

    <!-- Enhanced Header -->
    <div class="tc-card bg-gradient-to-br from-white to-cream-50 dark:from-zinc-800 dark:to-zinc-900 overflow-hidden relative">
        <div class="absolute inset-0 bg-gradient-to-r from-primary-500/5 to-secondary-500/5"></div>
        <div class="relative z-10">
            <div class="flex items-center gap-6 mb-6">
                <div class="w-20 h-20 rounded-3xl bg-gradient-to-br from-primary-100 to-secondary-100 dark:from-primary-800 dark:to-secondary-800 flex items-center justify-center border-4 border-white dark:border-zinc-700 shadow-xl">
                    <svg class="w-10 h-10 text-primary-600 dark:text-primary-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m1 5v6a2 2 0 01-2 2H7a2 2 0 01-2-2v-6m1-5V4a1 1 0 011-1h10a1 1 0 011 1v4a1 1 0 01-1 1H7a1 1 0 01-1-1z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h1 class="text-3xl font-bold font-heading text-primary-700 dark:text-primary-300 mb-2">Book an Appointment</h1>
                    <p class="text-zinc-500 text-lg">with <span class="font-bold text-primary-600 dark:text-primary-400">{{ $shop->name }}</span></p>
                </div>
            </div>
            
            <!-- Appointment Process Steps -->
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div class="flex items-center gap-3 p-4 bg-white/50 dark:bg-zinc-800/50 rounded-xl border border-zinc-200/50 dark:border-zinc-700/50">
                    <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center text-sm font-bold">1</div>
                    <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Select Date & Time</span>
                </div>
                <div class="flex items-center gap-3 p-4 bg-white/50 dark:bg-zinc-800/50 rounded-xl border border-zinc-200/50 dark:border-zinc-700/50">
                    <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-sm font-bold">2</div>
                    <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Choose Service Type</span>
                </div>
                <div class="flex items-center gap-3 p-4 bg-white/50 dark:bg-zinc-800/50 rounded-xl border border-zinc-200/50 dark:border-zinc-700/50">
                    <div class="w-8 h-8 rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center text-sm font-bold">3</div>
                    <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Add Instructions</span>
                </div>
                <div class="flex items-center gap-3 p-4 bg-white/50 dark:bg-zinc-800/50 rounded-xl border border-zinc-200/50 dark:border-zinc-700/50">
                    <div class="w-8 h-8 rounded-full bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 flex items-center justify-center text-sm font-bold">4</div>
                    <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Confirm Booking</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div x-show="isLoading" 
         x-transition:enter="transition-opacity duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/20 backdrop-blur-sm z-50 flex items-center justify-center"
         style="display: none;">
        <div class="bg-white dark:bg-zinc-800 rounded-2xl p-8 shadow-2xl">
            <div class="flex items-center gap-4">
                <div class="w-8 h-8 border-4 border-primary-200 border-t-primary-500 rounded-full animate-spin"></div>
                <span class="text-lg font-medium text-zinc-700 dark:text-zinc-300">Booking your appointment...</span>
            </div>
        </div>
    </div>

    <!-- Enhanced Booking Form -->
    <form wire:submit="book" class="space-y-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Left Column: Date & Time -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Date and Time Selection -->
                <div class="tc-card">
                    <h3 class="text-xl font-bold text-zinc-900 dark:text-white mb-6">Select Date & Time</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="date" class="block text-sm font-semibold text-zinc-700 dark:text-zinc-300 mb-3">Preferred Date</label>
                            <input 
                                type="date" 
                                id="date"
                                wire:model="date"
                                min="{{ date('Y-m-d') }}"
                                class="w-full px-4 py-3 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors"
                            />
                            @error('date') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label for="time" class="block text-sm font-semibold text-zinc-700 dark:text-zinc-300 mb-3">Preferred Time</label>
                            <select 
                                id="time"
                                wire:model="time"
                                class="w-full px-4 py-3 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors"
                            >
                                <option value="">Select a time slot</option>
                                @foreach($this->getTimeSlots() as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('time') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    
                    <!-- Time Slot Info -->
                    <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800">
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-sm font-medium text-blue-900 dark:text-blue-100">Scheduling Information</span>
                        </div>
                        <ul class="text-sm text-blue-800 dark:text-blue-200 space-y-1">
                            <li>• Appointments are typically 30-60 minutes</li>
                            <li>• Please arrive 5 minutes early</li>
                            <li>• Cancellations require 24-hour notice</li>
                            <li>• We'll send you a confirmation via notification</li>
                        </ul>
                    </div>
                </div>

                <!-- Service Type Selection -->
                <div class="tc-card">
                    <h3 class="text-xl font-bold text-zinc-900 dark:text-white mb-6">Appointment Type</h3>
                    
                    <div class="space-y-4">
                        @foreach($this->getAppointmentTypes() as $key => $typeInfo)
                            <label class="flex items-start gap-4 p-4 border border-zinc-200 dark:border-zinc-700 rounded-xl cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors {{ $type === $key ? 'ring-2 ring-primary-500 border-primary-500 bg-primary-50 dark:bg-primary-900/20' : '' }}">
                                <input 
                                    type="radio" 
                                    wire:model="type" 
                                    value="{{ $key }}" 
                                    class="mt-1 w-4 h-4 text-primary-600 border-zinc-300 focus:ring-primary-500"
                                />
                                <div class="flex gap-4 flex-1">
                                    <div class="w-12 h-12 rounded-2xl {{ $type === $key ? 'bg-primary-100 dark:bg-primary-900/30' : 'bg-zinc-100 dark:bg-zinc-800' }} flex items-center justify-center shrink-0">
                                        <svg class="w-6 h-6 {{ $type === $key ? 'text-primary-600 dark:text-primary-400' : 'text-zinc-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $typeInfo['icon'] }}"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-zinc-900 dark:text-white mb-1">{{ $typeInfo['name'] }}</h4>
                                        <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-2">{{ $typeInfo['description'] }}</p>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $type === $key ? 'bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-200' : 'bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200' }}">
                                            {{ $typeInfo['duration'] }}
                                        </span>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('type') <span class="text-red-500 text-sm mt-2">{{ $message }}</span> @enderror
                </div>

                <!-- Special Instructions -->
                <div class="tc-card">
                    <h3 class="text-xl font-bold text-zinc-900 dark:text-white mb-6">Special Instructions</h3>
                    
                    <div>
                        <label for="notes" class="block text-sm font-semibold text-zinc-700 dark:text-zinc-300 mb-3">Additional Notes (Optional)</label>
                        <textarea 
                            id="notes"
                            wire:model="notes" 
                            rows="4"
                            placeholder="Let the tailor know what you need (e.g., I want to discuss a 3-piece suit design, specific measurements needed, color preferences, etc.)"
                            class="w-full px-4 py-3 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white placeholder-zinc-500 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors resize-none"
                        ></textarea>
                        @error('notes') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                        
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-2">
                            Help us prepare for your visit by sharing details about your project or any specific requirements.
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Right Column: Booking Summary -->
            <div class="space-y-6">
                <!-- Booking Summary -->
                <div class="tc-card bg-gradient-to-br from-primary-50 to-secondary-50 dark:from-primary-900/20 dark:to-secondary-900/20 border-primary-200 dark:border-primary-800 sticky top-6">
                    <h3 class="text-lg font-bold text-zinc-900 dark:text-white mb-6">Booking Summary</h3>
                    
                    <div class="space-y-4">
                        <!-- Shop Info -->
                        <div class="flex items-center gap-3 pb-4 border-b border-primary-200/50 dark:border-primary-700/50">
                            <div class="w-12 h-12 rounded-xl bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
                                <svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 019.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.001 3.001 0 01-.621-1.875L21.75 3A3.001 3.001 0 0018 0H6a3.001 3.001 0 00-3.75 3l.621 17.25z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-zinc-900 dark:text-white">{{ $shop->name }}</p>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $shop->barangay ?? 'Professional Tailor' }}</p>
                            </div>
                        </div>
                        
                        <!-- Date & Time -->
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Date:</span>
                                <span class="font-semibold text-zinc-900 dark:text-white">
                                    {{ $date ? \Carbon\Carbon::parse($date)->format('M d, Y') : 'Not selected' }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Time:</span>
                                <span class="font-semibold text-zinc-900 dark:text-white">
                                    {{ $time ? \Carbon\Carbon::parse($time)->format('g:i A') : 'Not selected' }}
                                </span>
                            </div>
                            <div class="flex justify-between items-start">
                                <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Service:</span>
                                <div class="text-right">
                                    <span class="font-semibold text-zinc-900 dark:text-white block">
                                        {{ $type ? $this->getAppointmentTypes()[$type]['name'] : 'Not selected' }}
                                    </span>
                                    @if($type)
                                        <span class="text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ $this->getAppointmentTypes()[$type]['duration'] }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Booking Button -->
                    <button 
                        type="submit"
                        :disabled="isLoading || !@js($date) || !@js($time) || !@js($type)"
                        class="w-full mt-6 py-4 px-6 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 disabled:from-zinc-300 disabled:to-zinc-400 text-white font-semibold rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl disabled:shadow-none click-feedback flex items-center justify-center gap-3 disabled:cursor-not-allowed"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m1 5v6a2 2 0 01-2 2H7a2 2 0 01-2-2v-6m1-5V4a1 1 0 011-1h10a1 1 0 011 1v4a1 1 0 01-1 1H7a1 1 0 01-1-1z"/>
                        </svg>
                        <span x-show="!isLoading">Confirm Booking</span>
                        <span x-show="isLoading" class="flex items-center gap-2">
                            <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                            Booking...
                        </span>
                    </button>
                    
                    <div class="mt-4 p-3 bg-amber-50 dark:bg-amber-900/20 rounded-xl border border-amber-200 dark:border-amber-800">
                        <div class="flex items-center gap-2 mb-1">
                            <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-xs font-medium text-amber-800 dark:text-amber-200">Note</span>
                        </div>
                        <p class="text-xs text-amber-700 dark:text-amber-300">
                            Your appointment will be pending until confirmed by the shop. You'll receive a notification once approved.
                        </p>
                    </div>
                </div>

                <!-- Cancel Button -->
                <button 
                    type="button"
                    onclick="window.history.back()"
                    class="w-full py-3 px-6 text-zinc-600 dark:text-zinc-300 bg-zinc-100 dark:bg-zinc-700 hover:bg-zinc-200 dark:hover:bg-zinc-600 rounded-xl transition-colors click-feedback font-medium"
                >
                    Cancel Booking
                </button>
            </div>
        </div>
    </form>
</div>
