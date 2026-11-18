<?php

use App\Models\VirtualTryon;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('components.layouts.app')] class extends Component {
    use WithFileUploads;

    public $garmentFile = null;
    public bool $garmentUploaded = false;
    public bool $isReady = false;
    public $clientToken = null;
    public $errorMessage = null;
    public $successMessage = null;

    public function with(): array
    {
        return [
            'recentTryons' => VirtualTryon::where('user_id', auth()->id())
                ->where('processing_status', 'completed')
                ->latest()
                ->take(6)
                ->get(),
            'totalTryons' => VirtualTryon::where('user_id', auth()->id())->count(),
        ];
    }

    public function mount(): void
    {
        // Skip health check - will validate connection when user actually tries to use it
        $this->isReady = true;
    }

    public function checkDecartHealth(): void
    {
        // Optional check - not blocking
        \Log::info('Decart health check skipped in development');
    }

    public function updatedGarmentFile(): void
    {
        $this->validate(['garmentFile' => 'required|image|max:10240|mimes:jpeg,png,jpg,gif']);
        try {
            $path = $this->garmentFile->store('uploads/garments', 'public');
            $this->garmentUploaded = true;
            $this->errorMessage = null;

            \Log::info('Garment uploaded successfully', [
                'path' => storage_path('app/public/' . $path),
            ]);
        } catch (\Exception $e) {
            $this->errorMessage = 'Garment upload failed: ' . $e->getMessage();
            \Log::error('Garment upload failed', ['error' => $e->getMessage()]);
        }
    }

    public function initializeTryOn(): void
    {
        if (!$this->garmentUploaded) {
            $this->errorMessage = 'Please upload a garment first.';
            return;
        }

        try {
            // Request a client token from the server
            // The browser will use this to connect to Decart's realtime model
            // Note: This will be called via JavaScript/Alpine when user clicks camera
            \Log::info('Try-on session initialized', [
                'user_id' => auth()->id(),
                'garment_uploaded' => $this->garmentUploaded,
            ]);
        } catch (\Exception $e) {
            $this->errorMessage = 'Failed to start try-on session: ' . $e->getMessage();
            \Log::error('Try-on initialization failed', ['error' => $e->getMessage()]);
        }
    }

    public function clearForm(): void
    {
        $this->garmentFile = null;
        $this->garmentUploaded = false;
        $this->errorMessage = null;
        $this->successMessage = null;
    }
}; ?>

<div class="space-y-8">
    <!-- Header -->
    <div class="tc-card bg-gradient-to-br from-white to-cream-50 dark:from-zinc-800 dark:to-zinc-900">
        <div class="flex flex-col lg:flex-row lg:items-center gap-6">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary-500 to-secondary-500 flex items-center justify-center">
                        <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">AI Virtual Try-On (Real-Time)</h1>
                        <p class="text-zinc-600 dark:text-zinc-400 mt-1">See clothes on you instantly with your camera</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Interface -->
    <div class="tc-card">
        <h2 class="text-xl font-bold text-zinc-900 dark:text-white mb-6" style="font-family: 'Poppins';">Try On Garments</h2>

        <!-- Status Messages -->
        @if($errorMessage)
            <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl flex items-start gap-3">
                <svg class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm text-red-700 dark:text-red-400">{{ $errorMessage }}</p>
            </div>
        @endif

        @if($successMessage)
            <div class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl flex items-start gap-3">
                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm text-emerald-700 dark:text-emerald-400">{{ $successMessage }}</p>
            </div>
        @endif

        <!-- Service Status -->
        <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl flex items-start gap-3">
            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            <p class="text-sm text-blue-700 dark:text-blue-400">
                ✓ Real-time virtual try-on powered by Decart AI. No waiting — see clothes on you instantly!
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Step 1: Upload Garment -->
            <div class="space-y-4">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-8 h-8 rounded-full {{ $garmentUploaded ? 'bg-emerald-500' : 'bg-primary-500' }} text-white flex items-center justify-center text-sm font-bold">
                        {{ $garmentUploaded ? '✓' : '1' }}
                    </div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-200">Upload Garment</h3>
                </div>

                <label class="block border-2 border-dashed rounded-2xl p-6 text-center cursor-pointer transition-all hover:border-primary-400 min-h-[250px] flex flex-col items-center justify-center {{ $garmentUploaded ? 'border-emerald-400 bg-emerald-50 dark:bg-emerald-900/10' : 'border-zinc-300 dark:border-zinc-600 hover:bg-zinc-50 dark:hover:bg-zinc-800/50' }}">
                    <input type="file" wire:model="garmentFile" accept="image/*" class="hidden">
                    @if($garmentUploaded)
                        <svg class="w-16 h-16 mx-auto text-emerald-500 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm text-emerald-600 font-medium">✓ Garment uploaded!</p>
                        <p class="text-xs text-emerald-500 mt-1">{{ $garmentFile?->getClientOriginalName() }}</p>
                    @else
                        <svg class="w-16 h-16 mx-auto text-zinc-300 dark:text-zinc-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42"/>
                        </svg>
                        <p class="text-sm text-zinc-700 dark:text-zinc-300 font-medium">Click to upload garment</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">JPG, PNG, GIF (max 10MB)</p>
                    @endif
                </label>
            </div>

            <!-- Step 2: Try On -->
            <div class="space-y-4">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-8 h-8 rounded-full {{ $garmentUploaded ? 'bg-primary-500' : 'bg-zinc-300' }} text-white flex items-center justify-center text-sm font-bold">
                        2
                    </div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-200">See It On You</h3>
                </div>

                <div class="border-2 border-dashed rounded-2xl p-6 text-center min-h-[250px] flex flex-col items-center justify-center {{ $garmentUploaded ? 'border-primary-400 bg-primary-50 dark:bg-primary-900/10' : 'border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50' }}">
                    @if($garmentUploaded)
                        <svg class="w-16 h-16 mx-auto text-primary-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        <p class="text-sm text-primary-600 dark:text-primary-400 font-medium">Ready to try on!</p>
                        <p class="text-xs text-primary-500 dark:text-primary-300 mt-2">Click the button below to open your camera</p>
                    @else
                        <svg class="w-16 h-16 mx-auto text-zinc-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Upload a garment first</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Start Try-On Button -->
        <div class="flex justify-center gap-4 mt-8 pt-6 border-t border-zinc-100 dark:border-zinc-700">
            <button
                wire:click="initializeTryOn"
                :disabled="!@js($garmentUploaded)"
                onclick="openCameraModal()"
                class="px-8 py-3 bg-gradient-to-r from-primary-500 to-primary-600 text-white font-medium rounded-xl hover:from-primary-600 hover:to-primary-700 transition-all duration-300 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                <span>Open Camera</span>
            </button>

            @if($garmentUploaded)
                <button
                    wire:click="clearForm"
                    class="px-8 py-3 text-zinc-600 dark:text-zinc-300 bg-zinc-100 dark:bg-zinc-700 hover:bg-zinc-200 dark:hover:bg-zinc-600 rounded-xl transition-colors font-medium"
                >
                    Reset
                </button>
            @endif
        </div>
    </div>

    <!-- Camera Modal (will be populated by Alpine/JavaScript) -->
    <div id="decart-modal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" style="display: none;">
        <div class="bg-white dark:bg-zinc-900 rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden flex flex-col">
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-zinc-200 dark:border-zinc-700">
                <h3 class="text-xl font-bold text-zinc-900 dark:text-white">Try On This Garment</h3>
                <button onclick="closeCameraModal()" class="text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Video Feed -->
            <div class="flex-1 bg-black flex items-center justify-center">
                <video id="decart-output" class="w-full h-full object-cover" playsinline autoplay muted></video>
                <div id="loading-spinner" class="absolute text-white flex flex-col items-center gap-4">
                    <div class="animate-spin">
                        <svg class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </div>
                    <p>Connecting to camera...</p>
                </div>
            </div>

            <!-- Controls -->
            <div class="p-6 border-t border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50 flex gap-3">
                <button onclick="closeCameraModal()" class="flex-1 px-4 py-2 bg-zinc-300 dark:bg-zinc-700 text-zinc-900 dark:text-white rounded-lg font-medium hover:bg-zinc-400 dark:hover:bg-zinc-600 transition">
                    Close
                </button>
                <button onclick="saveDecartResult()" class="flex-1 px-4 py-2 bg-primary-500 text-white rounded-lg font-medium hover:bg-primary-600 transition">
                    Save Try-On
                </button>
            </div>
        </div>
    </div>

    <!-- Recent Try-Ons -->
    @if($recentTryons->count() > 0)
        <div class="tc-card">
            <h2 class="text-xl font-bold text-zinc-900 dark:text-white mb-6" style="font-family: 'Poppins';">Recent Try-Ons</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                @foreach($recentTryons as $tryon)
                    <div class="aspect-[3/4] rounded-xl overflow-hidden border border-zinc-200 dark:border-zinc-700 hover:shadow-lg transition-all">
                        @if($tryon->preview_data)
                            <img src="{{ $tryon->preview_data }}" alt="Try-on" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center">
                                <span class="text-xs text-zinc-500">No image</span>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if (session()->has('message'))
        <x-notification-toast type="success" title="Success!" message="{{ session('message') }}" :dismissible="true" />
    @endif
</div>
