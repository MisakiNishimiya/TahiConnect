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

        session()->flash('message', 'Measurements saved successfully!');
    }

    public function with(): array
    {
        return [
            'measurement' => Measurement::where('user_id', auth()->id())->latest()->first(),
        ];
    }
}; ?>

<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">Body Measurements</h1>
        <p class="text-zinc-500 mt-1">Keep your measurements up to date for perfect tailoring.</p>
    </div>

    @if($measurement)
        <div class="flex items-center gap-2">
            <span class="text-sm text-zinc-600 dark:text-zinc-400">Validation Status:</span>
            <span class="tc-badge tc-badge-{{ $measurement->validation_status }}">{{ ucfirst($measurement->validation_status) }}</span>
        </div>
    @endif

    @if (session()->has('message'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm">{{ session('message') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Measurement Form -->
        <div class="lg:col-span-2 tc-card">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white mb-6" style="font-family: 'Poppins';">Your Measurements</h2>
            <form wire:submit="saveMeasurements" class="space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <flux:input wire:model="chest" label="Chest (cm)" type="number" step="0.1" placeholder="e.g. 96.5" />
                    <flux:input wire:model="waist" label="Waist (cm)" type="number" step="0.1" placeholder="e.g. 81.3" />
                    <flux:input wire:model="hip" label="Hip (cm)" type="number" step="0.1" placeholder="e.g. 96.5" />
                    <flux:input wire:model="shoulder" label="Shoulder Width (cm)" type="number" step="0.1" placeholder="e.g. 44.5" />
                    <flux:input wire:model="sleeve_length" label="Sleeve Length (cm)" type="number" step="0.1" placeholder="e.g. 61.0" />
                    <flux:input wire:model="inseam" label="Inseam (cm)" type="number" step="0.1" placeholder="e.g. 78.7" />
                    <flux:input wire:model="neck" label="Neck (cm)" type="number" step="0.1" placeholder="e.g. 38.1" />
                    <flux:input wire:model="height" label="Height (cm)" type="number" step="0.1" placeholder="e.g. 170.2" />
                </div>
                <flux:textarea wire:model="notes" label="Notes" placeholder="Any additional notes for your tailor..." rows="3" />
                <flux:button type="submit" variant="primary" class="!bg-primary-500 hover:!bg-primary-600">
                    {{ $measurement ? 'Update Measurements' : 'Save Measurements' }}
                </flux:button>
            </form>
        </div>

        <!-- Body Guide -->
        <div class="tc-card">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4" style="font-family: 'Poppins';">Measurement Guide</h2>
            <div class="flex justify-center mb-6">
                <svg viewBox="0 0 200 420" class="w-48 h-auto" xmlns="http://www.w3.org/2000/svg">
                    <!-- Human silhouette -->
                    <circle cx="100" cy="35" r="22" fill="none" stroke="#2F5D50" stroke-width="1.5"/>
                    <path d="M100 57 L100 200" stroke="#2F5D50" stroke-width="1.5" fill="none"/>
                    <path d="M100 80 L45 130" stroke="#2F5D50" stroke-width="1.5" fill="none"/>
                    <path d="M100 80 L155 130" stroke="#2F5D50" stroke-width="1.5" fill="none"/>
                    <path d="M100 200 L60 350" stroke="#2F5D50" stroke-width="1.5" fill="none"/>
                    <path d="M100 200 L140 350" stroke="#2F5D50" stroke-width="1.5" fill="none"/>
                    <!-- Measurement lines -->
                    <line x1="60" y1="55" x2="140" y2="55" stroke="#D6B98C" stroke-width="1" stroke-dasharray="3,3"/>
                    <text x="155" y="58" fill="#2F5D50" font-size="9" font-family="Inter">Neck</text>
                    <line x1="55" y1="85" x2="145" y2="85" stroke="#D6B98C" stroke-width="1" stroke-dasharray="3,3"/>
                    <text x="150" y="88" fill="#2F5D50" font-size="9" font-family="Inter">Shoulder</text>
                    <line x1="60" y1="115" x2="140" y2="115" stroke="#D6B98C" stroke-width="1" stroke-dasharray="3,3"/>
                    <text x="145" y="118" fill="#2F5D50" font-size="9" font-family="Inter">Chest</text>
                    <line x1="65" y1="155" x2="135" y2="155" stroke="#D6B98C" stroke-width="1" stroke-dasharray="3,3"/>
                    <text x="140" y="158" fill="#2F5D50" font-size="9" font-family="Inter">Waist</text>
                    <line x1="60" y1="200" x2="140" y2="200" stroke="#D6B98C" stroke-width="1" stroke-dasharray="3,3"/>
                    <text x="145" y="203" fill="#2F5D50" font-size="9" font-family="Inter">Hip</text>
                    <!-- Height line -->
                    <line x1="25" y1="13" x2="25" y2="350" stroke="#4CAF50" stroke-width="1" stroke-dasharray="4,4"/>
                    <text x="10" y="185" fill="#4CAF50" font-size="9" font-family="Inter" transform="rotate(-90 15 185)">Height</text>
                    <!-- Sleeve -->
                    <line x1="100" y1="82" x2="45" y2="130" stroke="#FF9800" stroke-width="1" stroke-dasharray="3,3"/>
                    <text x="35" y="145" fill="#FF9800" font-size="8" font-family="Inter">Sleeve</text>
                    <!-- Inseam -->
                    <line x1="100" y1="200" x2="100" y2="330" stroke="#3B82F6" stroke-width="1" stroke-dasharray="3,3"/>
                    <text x="105" y="270" fill="#3B82F6" font-size="8" font-family="Inter">Inseam</text>
                </svg>
            </div>
            <div class="space-y-2">
                <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">Tips for Accurate Measuring</h3>
                <ul class="text-xs text-zinc-500 space-y-1.5 list-disc list-inside">
                    <li>Use a flexible measuring tape</li>
                    <li>Wear light, fitted clothing</li>
                    <li>Stand straight with arms relaxed</li>
                    <li>Keep the tape snug but not tight</li>
                    <li>Measure twice for accuracy</li>
                    <li>Ask someone to help with hard-to-reach areas</li>
                </ul>
            </div>
        </div>
    </div>
</div>
