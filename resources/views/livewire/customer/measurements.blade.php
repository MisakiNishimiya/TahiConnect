<?php

use App\Models\Measurement;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
    public string $chest = '';
    public string $waist = '';
    public string $hip = '';
    public string $shoulder = '';
    public string $sleeve_length = '';
    public string $inseam = '';
    public string $neck = '';
    public string $height = '';
    public string $notes = '';
    public bool $showGuideModal = false;
    public string $activeGuide = '';
    public bool $hasChanges = false;

    public function mount(): void
    {
        $m = Measurement::where('user_id', auth()->id())->latest()->first();
        if ($m) {
            $this->chest = (string) ($m->chest ?? '');
            $this->waist = (string) ($m->waist ?? '');
            $this->hip = (string) ($m->hip ?? '');
            $this->shoulder = (string) ($m->shoulder ?? '');
            $this->sleeve_length = (string) ($m->sleeve_length ?? '');
            $this->inseam = (string) ($m->inseam ?? '');
            $this->neck = (string) ($m->neck ?? '');
            $this->height = (string) ($m->height ?? '');
            $this->notes = (string) ($m->notes ?? '');
        }
    }

    public function updated($property)
    {
        $measurementFields = ['chest', 'waist', 'hip', 'shoulder', 'sleeve_length', 'inseam', 'neck', 'height', 'notes'];
        if (in_array($property, $measurementFields)) {
            $this->hasChanges = true;
        }
    }

    public function showGuide($measurement): void
    {
        $this->activeGuide = $measurement;
        $this->showGuideModal = true;
    }

    public function closeGuideModal(): void
    {
        $this->showGuideModal = false;
        $this->activeGuide = '';
    }

    public function saveMeasurements(): void
    {
        $data = $this->validate([
            'chest' => 'nullable|numeric|min:0|max:200',
            'waist' => 'nullable|numeric|min:0|max:200',
            'hip' => 'nullable|numeric|min:0|max:200',
            'shoulder' => 'nullable|numeric|min:0|max:100',
            'sleeve_length' => 'nullable|numeric|min:0|max:100',
            'inseam' => 'nullable|numeric|min:0|max:150',
            'neck' => 'nullable|numeric|min:0|max:60',
            'height' => 'nullable|numeric|min:0|max:250',
            'notes' => 'nullable|string|max:500',
        ]);

        Measurement::updateOrCreate(
            ['user_id' => auth()->id()],
            array_merge($data, ['validation_status' => 'pending'])
        );

        $this->hasChanges = false;
        session()->flash('message', 'Measurements saved successfully!');
    }

    public function getCompletionPercentage(): int
    {
        $fields = [$this->chest, $this->waist, $this->hip, $this->shoulder, $this->sleeve_length, $this->inseam, $this->neck, $this->height];
        $completed = count(array_filter($fields, fn($field) => !empty($field)));
        return (int) (($completed / count($fields)) * 100);
    }

    public function with(): array
    {
        return [
            'measurement' => Measurement::where('user_id', auth()->id())->latest()->first(),
            'measurementHistory' => Measurement::where('user_id', auth()->id())->latest()->take(5)->get(),
            'completionPercentage' => $this->getCompletionPercentage(),
        ];
    }
}; ?>

<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-primary-600 dark:text-primary-400" style="font-family: 'Poppins';">Body Measurements</h1>
            <p class="text-zinc-500 mt-1">Keep your measurements up to date for the perfect fit every time.</p>
        </div>
        
        <!-- Action Buttons -->
        <div class="flex gap-3">
            <button 
                wire:click="showGuide('overview')"
                class="px-4 py-2 text-sm font-medium text-primary-600 bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800 rounded-xl hover:bg-primary-100 dark:hover:bg-primary-900/30 transition-colors click-feedback flex items-center gap-2"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Measuring Guide
            </button>
        </div>
    </div>

    <!-- Status Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Completion Status -->
        <div class="tc-stat-card animate-fade-in-up">
            <div class="tc-stat-icon bg-primary-100 dark:bg-primary-900/30">
                <x-progress-circle 
                    :percentage="$completionPercentage" 
                    size="48" 
                    strokeWidth="4" 
                    color="primary"
                />
            </div>
            <div>
                <p class="text-xl font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">{{ $completionPercentage }}%</p>
                <p class="text-sm text-zinc-500">Profile Complete</p>
            </div>
        </div>

        <!-- Validation Status -->
        <div class="tc-stat-card animate-fade-in-up animation-delay-100">
            <div class="tc-stat-icon {{ $measurement && $measurement->validation_status === 'validated' ? 'bg-emerald-100 dark:bg-emerald-900/30' : 'bg-amber-100 dark:bg-amber-900/30' }}">
                @if($measurement && $measurement->validation_status === 'validated')
                    <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                @else
                    <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                    </svg>
                @endif
            </div>
            <div>
                <p class="text-lg font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">
                    {{ $measurement ? ucfirst($measurement->validation_status) : 'Not Set' }}
                </p>
                <p class="text-sm text-zinc-500">Validation Status</p>
            </div>
        </div>

        <!-- Last Updated -->
        <div class="tc-stat-card animate-fade-in-up animation-delay-200">
            <div class="tc-stat-icon bg-blue-100 dark:bg-blue-900/30">
                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-lg font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">
                    {{ $measurement ? $measurement->updated_at->format('M d') : 'Never' }}
                </p>
                <p class="text-sm text-zinc-500">Last Updated</p>
            </div>
        </div>
    </div>

    <!-- Success Message -->
    @if (session()->has('message'))
        <x-notification-toast 
            type="success" 
            title="Success!" 
            message="{{ session('message') }}"
            :dismissible="true"
        />
    @endif

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">
        
        <!-- Measurement Form -->
        <div class="xl:col-span-3">
            <div class="tc-card">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-semibold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">Your Measurements</h2>
                    @if($hasChanges)
                        <div class="flex items-center gap-2 text-sm text-amber-600 dark:text-amber-400">
                            <div class="w-2 h-2 bg-amber-500 rounded-full animate-pulse"></div>
                            Unsaved changes
                        </div>
                    @endif
                </div>

                <form wire:submit="saveMeasurements" class="space-y-6">
                    
                    <!-- Upper Body Measurements -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium text-zinc-800 dark:text-zinc-200 border-b border-zinc-200 dark:border-zinc-700 pb-2">Upper Body</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            
                            <!-- Chest -->
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Chest (cm)</label>
                                    <button 
                                        type="button"
                                        wire:click="showGuide('chest')"
                                        class="text-xs text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 click-feedback"
                                    >
                                        How to measure?
                                    </button>
                                </div>
                                <div class="form-input-enhanced">
                                    <input 
                                        type="number" 
                                        step="0.1" 
                                        wire:model.live="chest"
                                        placeholder="e.g. 96.5"
                                        class="w-full px-3 py-2 border border-zinc-200 dark:border-zinc-700 rounded-lg text-sm bg-white dark:bg-zinc-800 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all"
                                    >
                                    <div class="form-input-border"></div>
                                </div>
                                @error('chest') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>

                            <!-- Neck -->
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Neck (cm)</label>
                                    <button 
                                        type="button"
                                        wire:click="showGuide('neck')"
                                        class="text-xs text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 click-feedback"
                                    >
                                        How to measure?
                                    </button>
                                </div>
                                <div class="form-input-enhanced">
                                    <input 
                                        type="number" 
                                        step="0.1" 
                                        wire:model.live="neck"
                                        placeholder="e.g. 38.1"
                                        class="w-full px-3 py-2 border border-zinc-200 dark:border-zinc-700 rounded-lg text-sm bg-white dark:bg-zinc-800 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all"
                                    >
                                    <div class="form-input-border"></div>
                                </div>
                                @error('neck') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>

                            <!-- Shoulder -->
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Shoulder (cm)</label>
                                    <button 
                                        type="button"
                                        wire:click="showGuide('shoulder')"
                                        class="text-xs text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 click-feedback"
                                    >
                                        How to measure?
                                    </button>
                                </div>
                                <div class="form-input-enhanced">
                                    <input 
                                        type="number" 
                                        step="0.1" 
                                        wire:model.live="shoulder"
                                        placeholder="e.g. 44.5"
                                        class="w-full px-3 py-2 border border-zinc-200 dark:border-zinc-700 rounded-lg text-sm bg-white dark:bg-zinc-800 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all"
                                    >
                                    <div class="form-input-border"></div>
                                </div>
                                @error('shoulder') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>

                            <!-- Sleeve Length -->
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Sleeve (cm)</label>
                                    <button 
                                        type="button"
                                        wire:click="showGuide('sleeve')"
                                        class="text-xs text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 click-feedback"
                                    >
                                        How to measure?
                                    </button>
                                </div>
                                <div class="form-input-enhanced">
                                    <input 
                                        type="number" 
                                        step="0.1" 
                                        wire:model.live="sleeve_length"
                                        placeholder="e.g. 61.0"
                                        class="w-full px-3 py-2 border border-zinc-200 dark:border-zinc-700 rounded-lg text-sm bg-white dark:bg-zinc-800 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all"
                                    >
                                    <div class="form-input-border"></div>
                                </div>
                                @error('sleeve_length') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Lower Body Measurements -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium text-zinc-800 dark:text-zinc-200 border-b border-zinc-200 dark:border-zinc-700 pb-2">Lower Body</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            
                            <!-- Waist -->
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Waist (cm)</label>
                                    <button 
                                        type="button"
                                        wire:click="showGuide('waist')"
                                        class="text-xs text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 click-feedback"
                                    >
                                        How to measure?
                                    </button>
                                </div>
                                <div class="form-input-enhanced">
                                    <input 
                                        type="number" 
                                        step="0.1" 
                                        wire:model.live="waist"
                                        placeholder="e.g. 81.3"
                                        class="w-full px-3 py-2 border border-zinc-200 dark:border-zinc-700 rounded-lg text-sm bg-white dark:bg-zinc-800 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all"
                                    >
                                    <div class="form-input-border"></div>
                                </div>
                                @error('waist') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>

                            <!-- Hip -->
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Hip (cm)</label>
                                    <button 
                                        type="button"
                                        wire:click="showGuide('hip')"
                                        class="text-xs text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 click-feedback"
                                    >
                                        How to measure?
                                    </button>
                                </div>
                                <div class="form-input-enhanced">
                                    <input 
                                        type="number" 
                                        step="0.1" 
                                        wire:model.live="hip"
                                        placeholder="e.g. 96.5"
                                        class="w-full px-3 py-2 border border-zinc-200 dark:border-zinc-700 rounded-lg text-sm bg-white dark:bg-zinc-800 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all"
                                    >
                                    <div class="form-input-border"></div>
                                </div>
                                @error('hip') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>

                            <!-- Inseam -->
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Inseam (cm)</label>
                                    <button 
                                        type="button"
                                        wire:click="showGuide('inseam')"
                                        class="text-xs text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 click-feedback"
                                    >
                                        How to measure?
                                    </button>
                                </div>
                                <div class="form-input-enhanced">
                                    <input 
                                        type="number" 
                                        step="0.1" 
                                        wire:model.live="inseam"
                                        placeholder="e.g. 78.7"
                                        class="w-full px-3 py-2 border border-zinc-200 dark:border-zinc-700 rounded-lg text-sm bg-white dark:bg-zinc-800 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all"
                                    >
                                    <div class="form-input-border"></div>
                                </div>
                                @error('inseam') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>

                            <!-- Height -->
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Height (cm)</label>
                                    <button 
                                        type="button"
                                        wire:click="showGuide('height')"
                                        class="text-xs text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 click-feedback"
                                    >
                                        How to measure?
                                    </button>
                                </div>
                                <div class="form-input-enhanced">
                                    <input 
                                        type="number" 
                                        step="0.1" 
                                        wire:model.live="height"
                                        placeholder="e.g. 170.2"
                                        class="w-full px-3 py-2 border border-zinc-200 dark:border-zinc-700 rounded-lg text-sm bg-white dark:bg-zinc-800 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all"
                                    >
                                    <div class="form-input-border"></div>
                                </div>
                                @error('height') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Notes Section -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium text-zinc-800 dark:text-zinc-200 border-b border-zinc-200 dark:border-zinc-700 pb-2">Additional Notes</h3>
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Special Instructions for Tailors</label>
                            <textarea 
                                wire:model.live="notes"
                                rows="4"
                                placeholder="Any additional notes, preferences, or special requirements for your tailor..."
                                class="w-full px-3 py-2 border border-zinc-200 dark:border-zinc-700 rounded-lg text-sm bg-white dark:bg-zinc-800 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all resize-none"
                            ></textarea>
                            <p class="text-xs text-zinc-500 mt-1">{{ strlen($notes) }}/500 characters</p>
                            @error('notes') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Save Button -->
                    <div class="flex items-center justify-between pt-6 border-t border-zinc-200 dark:border-zinc-700">
                        <div class="flex items-center gap-2 text-sm text-zinc-500">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            Your measurements are securely stored and only shared with your chosen tailors.
                        </div>
                        <button 
                            type="submit" 
                            class="px-8 py-3 bg-gradient-to-r from-primary-500 to-primary-600 text-white font-medium rounded-xl hover:from-primary-600 hover:to-primary-700 transition-all duration-300 shadow-lg hover:shadow-xl hover:shadow-primary-500/25 click-feedback flex items-center gap-2"
                            {{ !$hasChanges && $measurement ? 'disabled' : '' }}
                        >
                            <div wire:loading wire:target="saveMeasurements" class="btn-spinner">
                                <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full spinner"></div>
                            </div>
                            <span wire:loading.remove wire:target="saveMeasurements">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ $measurement ? 'Update Measurements' : 'Save Measurements' }}
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            
            <!-- Enhanced Body Guide -->
            <div class="tc-card">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4" style="font-family: 'Poppins';">Measurement Guide</h3>
                
                <!-- Interactive Body Diagram -->
                <div class="flex justify-center mb-6 p-4 bg-gradient-to-b from-zinc-50 to-white dark:from-zinc-800 dark:to-zinc-900 rounded-xl">
                    <div class="relative">
                        <svg viewBox="0 0 200 420" class="w-48 h-auto" xmlns="http://www.w3.org/2000/svg">
                            <!-- Human silhouette -->
                            <circle cx="100" cy="35" r="22" fill="none" stroke="#2F5D50" stroke-width="1.5"/>
                            <path d="M100 57 L100 200" stroke="#2F5D50" stroke-width="1.5" fill="none"/>
                            <path d="M100 80 L45 130" stroke="#2F5D50" stroke-width="1.5" fill="none"/>
                            <path d="M100 80 L155 130" stroke="#2F5D50" stroke-width="1.5" fill="none"/>
                            <path d="M100 200 L60 350" stroke="#2F5D50" stroke-width="1.5" fill="none"/>
                            <path d="M100 200 L140 350" stroke="#2F5D50" stroke-width="1.5" fill="none"/>
                            
                            <!-- Interactive Measurement Lines -->
                            <line x1="60" y1="55" x2="140" y2="55" stroke="#D6B98C" stroke-width="1" stroke-dasharray="3,3" class="hover:stroke-primary-500 cursor-pointer"/>
                            <text x="155" y="58" fill="#2F5D50" font-size="9" font-family="Inter" class="cursor-pointer hover:fill-primary-500">Neck</text>
                            
                            <line x1="55" y1="85" x2="145" y2="85" stroke="#D6B98C" stroke-width="1" stroke-dasharray="3,3" class="hover:stroke-primary-500 cursor-pointer"/>
                            <text x="150" y="88" fill="#2F5D50" font-size="9" font-family="Inter" class="cursor-pointer hover:fill-primary-500">Shoulder</text>
                            
                            <line x1="60" y1="115" x2="140" y2="115" stroke="#D6B98C" stroke-width="1" stroke-dasharray="3,3" class="hover:stroke-primary-500 cursor-pointer"/>
                            <text x="145" y="118" fill="#2F5D50" font-size="9" font-family="Inter" class="cursor-pointer hover:fill-primary-500">Chest</text>
                            
                            <line x1="65" y1="155" x2="135" y2="155" stroke="#D6B98C" stroke-width="1" stroke-dasharray="3,3" class="hover:stroke-primary-500 cursor-pointer"/>
                            <text x="140" y="158" fill="#2F5D50" font-size="9" font-family="Inter" class="cursor-pointer hover:fill-primary-500">Waist</text>
                            
                            <line x1="60" y1="200" x2="140" y2="200" stroke="#D6B98C" stroke-width="1" stroke-dasharray="3,3" class="hover:stroke-primary-500 cursor-pointer"/>
                            <text x="145" y="203" fill="#2F5D50" font-size="9" font-family="Inter" class="cursor-pointer hover:fill-primary-500">Hip</text>
                            
                            <!-- Height line -->
                            <line x1="25" y1="13" x2="25" y2="350" stroke="#4CAF50" stroke-width="1" stroke-dasharray="4,4" class="hover:stroke-primary-500 cursor-pointer"/>
                            <text x="10" y="185" fill="#4CAF50" font-size="9" font-family="Inter" transform="rotate(-90 15 185)" class="cursor-pointer hover:fill-primary-500">Height</text>
                            
                            <!-- Sleeve -->
                            <line x1="100" y1="82" x2="45" y2="130" stroke="#FF9800" stroke-width="1" stroke-dasharray="3,3" class="hover:stroke-primary-500 cursor-pointer"/>
                            <text x="35" y="145" fill="#FF9800" font-size="8" font-family="Inter" class="cursor-pointer hover:fill-primary-500">Sleeve</text>
                            
                            <!-- Inseam -->
                            <line x1="100" y1="200" x2="100" y2="330" stroke="#3B82F6" stroke-width="1" stroke-dasharray="3,3" class="hover:stroke-primary-500 cursor-pointer"/>
                            <text x="105" y="270" fill="#3B82F6" font-size="8" font-family="Inter" class="cursor-pointer hover:fill-primary-500">Inseam</text>
                        </svg>
                    </div>
                </div>

                <!-- Measuring Tips -->
                <div class="space-y-3">
                    <h4 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300 flex items-center gap-2">
                        <svg class="w-4 h-4 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                        Essential Tips
                    </h4>
                    <div class="space-y-2">
                        <div class="flex items-start gap-3 p-3 bg-gradient-to-r from-primary-50 to-secondary-50 dark:from-primary-900/20 dark:to-secondary-900/20 rounded-lg">
                            <div class="w-6 h-6 rounded-full bg-primary-500 text-white text-xs flex items-center justify-center font-bold shrink-0 mt-0.5">1</div>
                            <div>
                                <p class="text-sm font-medium text-zinc-800 dark:text-zinc-200">Use a flexible tape measure</p>
                                <p class="text-xs text-zinc-600 dark:text-zinc-400">Fabric tape works best for accurate body measurements</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 p-3 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg">
                            <div class="w-6 h-6 rounded-full bg-blue-500 text-white text-xs flex items-center justify-center font-bold shrink-0 mt-0.5">2</div>
                            <div>
                                <p class="text-sm font-medium text-zinc-800 dark:text-zinc-200">Wear fitted clothing</p>
                                <p class="text-xs text-zinc-600 dark:text-zinc-400">Light, form-fitting clothes give the most accurate measurements</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 p-3 bg-gradient-to-r from-emerald-50 to-teal-50 dark:from-emerald-900/20 dark:to-teal-900/20 rounded-lg">
                            <div class="w-6 h-6 rounded-full bg-emerald-500 text-white text-xs flex items-center justify-center font-bold shrink-0 mt-0.5">3</div>
                            <div>
                                <p class="text-sm font-medium text-zinc-800 dark:text-zinc-200">Get help for accuracy</p>
                                <p class="text-xs text-zinc-600 dark:text-zinc-400">Having someone assist gives better results</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Measurement History -->
            @if($measurementHistory->count() > 1)
                <div class="tc-card">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4 flex items-center gap-2" style="font-family: 'Poppins';">
                        <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Recent Updates
                    </h3>
                    <div class="space-y-3">
                        @foreach($measurementHistory->take(3) as $history)
                            <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg">
                                <div>
                                    <p class="text-sm font-medium text-zinc-900 dark:text-white">Profile Updated</p>
                                    <p class="text-xs text-zinc-500">{{ $history->updated_at->format('M d, Y g:i A') }}</p>
                                </div>
                                <span class="tc-badge tc-badge-{{ $history->validation_status }} text-xs">
                                    {{ ucfirst($history->validation_status) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Floating Action Button -->
    <x-floating-action-button 
        icon="edit" 
        tooltip="Quick Edit Measurements"
    >
        <!-- Sub Menu Items -->
        <button class="w-12 h-12 bg-primary-500 text-white rounded-full shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center hover:scale-110" title="Save Changes">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </button>
        <button wire:click="showGuide('overview')" class="w-12 h-12 bg-blue-500 text-white rounded-full shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center hover:scale-110" title="View Guide">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </button>
    </x-floating-action-button>

    <!-- Measurement Guide Modal -->
    @if($showGuideModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
            <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto" @click.stop>
                <div class="p-6">
                    <!-- Header -->
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-semibold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">
                            How to Measure: {{ ucfirst(str_replace('_', ' ', $activeGuide)) }}
                        </h3>
                        <button 
                            wire:click="closeGuideModal"
                            class="p-2 text-zinc-400 hover:text-zinc-600 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-full transition-colors"
                        >
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Guide Content -->
                    <div class="space-y-4">
                        @if($activeGuide === 'chest')
                            <div class="bg-primary-50 dark:bg-primary-900/20 rounded-xl p-4">
                                <h4 class="font-semibold text-primary-700 dark:text-primary-300 mb-2">Chest Measurement</h4>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-3">Measure around the fullest part of your chest, usually just under the armpits.</p>
                                <ul class="text-sm text-zinc-600 dark:text-zinc-400 space-y-1 list-disc list-inside">
                                    <li>Keep the tape measure parallel to the floor</li>
                                    <li>Don't pull the tape too tight - it should be snug but comfortable</li>
                                    <li>Take the measurement while breathing normally</li>
                                </ul>
                            </div>
                        @elseif($activeGuide === 'waist')
                            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4">
                                <h4 class="font-semibold text-blue-700 dark:text-blue-300 mb-2">Waist Measurement</h4>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-3">Measure around your natural waistline, typically the narrowest part of your torso.</p>
                                <ul class="text-sm text-zinc-600 dark:text-zinc-400 space-y-1 list-disc list-inside">
                                    <li>Find your natural waist by bending to the side</li>
                                    <li>Measure at the point where you naturally bend</li>
                                    <li>Keep one finger's width between the tape and your body</li>
                                </ul>
                            </div>
                        @else
                            <div class="bg-zinc-50 dark:bg-zinc-800/50 rounded-xl p-4">
                                <h4 class="font-semibold text-zinc-700 dark:text-zinc-300 mb-2">General Measuring Guidelines</h4>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-3">Follow these tips for the most accurate measurements:</p>
                                <ul class="text-sm text-zinc-600 dark:text-zinc-400 space-y-1 list-disc list-inside">
                                    <li>Use a flexible cloth measuring tape</li>
                                    <li>Wear minimal, fitted clothing</li>
                                    <li>Stand straight with arms at your sides</li>
                                    <li>Ask someone to help you for better accuracy</li>
                                    <li>Take measurements twice to ensure consistency</li>
                                </ul>
                            </div>
                        @endif
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-zinc-200 dark:border-zinc-700">
                        <button 
                            wire:click="closeGuideModal"
                            class="px-6 py-2 text-sm font-medium text-primary-600 bg-primary-50 dark:bg-primary-900/20 rounded-lg hover:bg-primary-100 dark:hover:bg-primary-900/30 transition-colors"
                        >
                            Got it!
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
