<?php

use App\Models\VirtualTryon;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('components.layouts.app')] class extends Component {
    use WithFileUploads;

    public $photoFile = null;
    public $garmentFile = null;
    public bool $photoUploaded = false;
    public bool $garmentUploaded = false;
    public bool $isProcessing = false;
    public bool $showPreview = false;
    public $previewData = null;
    public $currentTryOnId = null;
    public $errorMessage = null;
    public int $processingProgress = 0;
    public $photoPath = null;
    public $garmentPath = null;

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

    public function updatedPhotoFile(): void
    {
        $this->validate(['photoFile' => 'required|image|max:10240|mimes:jpeg,png,jpg,gif']);
        try {
            $path = $this->photoFile->store('uploads/photos', 'local');
            $this->photoPath = storage_path('app/' . $path);
            $this->photoUploaded = true;
            $this->errorMessage = null;
        } catch (\Exception $e) {
            $this->errorMessage = 'Photo upload failed: ' . $e->getMessage();
        }
    }

    public function updatedGarmentFile(): void
    {
        $this->validate(['garmentFile' => 'required|image|max:10240|mimes:jpeg,png,jpg,gif']);
        try {
            $path = $this->garmentFile->store('uploads/garments', 'local');
            $this->garmentPath = storage_path('app/' . $path);
            $this->garmentUploaded = true;
            $this->errorMessage = null;
        } catch (\Exception $e) {
            $this->errorMessage = 'Garment upload failed: ' . $e->getMessage();
        }
    }

    public function generatePreview(): void
    {
        if (!$this->photoUploaded || !$this->garmentUploaded) {
            $this->errorMessage = 'Please upload both photo and garment design';
            return;
        }

        $this->isProcessing = true;
        $this->errorMessage = null;
        $this->processingProgress = 10;

        try {
            $response = \Illuminate\Support\Facades\Http::post(route('api.virtual-tryon.process', [], false), [
                'photo_path' => $this->photoPath,
                'garment_path' => $this->garmentPath,
            ]);

            if ($response->successful()) {
                $this->currentTryOnId = $response->json('tryon_id');
                $this->processingProgress = 30;
                $this->dispatch('poll-status', tryOnId: $this->currentTryOnId);
            } else {
                throw new \Exception($response->json('error') ?? 'Processing failed');
            }
        } catch (\Exception $e) {
            $this->isProcessing = false;
            $this->errorMessage = $e->getMessage();
        }
    }

    #[\Livewire\Attributes\On('poll-status')]
    public function pollStatus($tryOnId): void
    {
        try {
            $response = \Illuminate\Support\Facades\Http::get(route('api.virtual-tryon.status', $tryOnId, false));

            if ($response->successful()) {
                $data = $response->json();
                if ($data['status'] === 'completed') {
                    $this->showPreview = true;
                    $this->previewData = $data['preview_data'];
                    $this->isProcessing = false;
                    $this->processingProgress = 100;
                } elseif ($data['status'] === 'failed') {
                    $this->isProcessing = false;
                    $this->errorMessage = $data['error_message'] ?? 'Processing failed';
                } else {
                    $this->processingProgress = min(95, $this->processingProgress + rand(5, 15));
                    $this->dispatch('poll-status', tryOnId: $tryOnId)->later(milliseconds: 2000);
                }
            }
        } catch (\Exception $e) {
            $this->isProcessing = false;
            $this->errorMessage = 'Status check failed: ' . $e->getMessage();
        }
    }

    public function saveToGallery(): void
    {
        if (!$this->currentTryOnId) return;
        try {
            \Illuminate\Support\Facades\Http::post(route('api.virtual-tryon.save', [], false), [
                'tryon_id' => $this->currentTryOnId,
                'title' => 'Try-on ' . now()->format('M d, Y'),
            ]);
            session()->flash('message', 'Try-on saved to gallery!');
            $this->resetUpload();
        } catch (\Exception $e) {
            $this->errorMessage = 'Failed to save: ' . $e->getMessage();
        }
    }

    public function resetUpload(): void
    {
        $this->photoFile = null;
        $this->garmentFile = null;
        $this->photoUploaded = false;
        $this->garmentUploaded = false;
        $this->isProcessing = false;
        $this->showPreview = false;
        $this->previewData = null;
        $this->currentTryOnId = null;
        $this->errorMessage = null;
        $this->processingProgress = 0;
        $this->photoPath = null;
        $this->garmentPath = null;
    }
}; ?>

<div class="space-y-8" x-data="{ isProcessing: @entangle('isProcessing'), progress: @entangle('processingProgress') }">
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
                        <h1 class="text-3xl font-bold text-primary-600 dark:text-primary-400" style="font-family: 'Poppins';">AI Virtual Try-On</h1>
                        <p class="text-zinc-500 mt-1">Upload your photo & see how garments look on you</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Interface -->
    <div class="tc-card overflow-hidden">
        <h2 class="text-xl font-bold text-zinc-900 dark:text-white mb-6" style="font-family: 'Poppins';">Create Try-On</h2>
        
        <!-- Error Message -->
        @if($errorMessage)
            <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl flex items-start gap-3">
                <svg class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm text-red-700 dark:text-red-400">{{ $errorMessage }}</p>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Step 1: Photo -->
            <div class="space-y-4">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-8 h-8 rounded-full {{ $photoUploaded ? 'bg-emerald-500' : 'bg-primary-500' }} text-white flex items-center justify-center text-sm font-bold">
                        {{ $photoUploaded ? '✓' : '1' }}
                    </div>
                    <h3 class="text-lg font-semibold text-zinc-700 dark:text-zinc-300">Upload Your Photo</h3>
                </div>
                
                <label class="border-2 border-dashed rounded-2xl p-8 text-center cursor-pointer transition-all hover:border-primary-400 {{ $photoUploaded ? 'border-emerald-400 bg-emerald-50 dark:bg-emerald-900/10' : 'border-zinc-300 dark:border-zinc-600 hover:bg-zinc-50 dark:hover:bg-zinc-800/50' }}">
                    <input type="file" wire:model="photoFile" accept="image/*" class="hidden">
                    @if($photoUploaded)
                        <svg class="w-16 h-16 mx-auto text-emerald-500 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm text-emerald-600 font-medium">✓ Photo uploaded!</p>
                        <p class="text-xs text-emerald-500 mt-1">{{ $photoFile?->getClientOriginalName() }}</p>
                    @else
                        <svg class="w-16 h-16 mx-auto text-zinc-300 dark:text-zinc-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                        </svg>
                        <p class="text-sm text-zinc-500 font-medium">Click to upload photo</p>
                        <p class="text-xs text-zinc-400 mt-1">JPG, PNG, GIF (max 10MB)</p>
                    @endif
                </label>
            </div>

            <!-- Step 2: Garment -->
            <div class="space-y-4">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-8 h-8 rounded-full {{ $garmentUploaded ? 'bg-emerald-500' : ($photoUploaded ? 'bg-primary-500' : 'bg-zinc-300') }} text-white flex items-center justify-center text-sm font-bold">
                        {{ $garmentUploaded ? '✓' : '2' }}
                    </div>
                    <h3 class="text-lg font-semibold text-zinc-700 dark:text-zinc-300">Upload Garment Design</h3>
                </div>
                
                <label class="border-2 border-dashed rounded-2xl p-8 text-center cursor-pointer transition-all hover:border-primary-400 {{ $garmentUploaded ? 'border-emerald-400 bg-emerald-50 dark:bg-emerald-900/10' : 'border-zinc-300 dark:border-zinc-600 hover:bg-zinc-50 dark:hover:bg-zinc-800/50' }}">
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
                        <p class="text-sm text-zinc-500 font-medium">Click to upload garment</p>
                        <p class="text-xs text-zinc-400 mt-1">JPG, PNG, GIF (max 10MB)</p>
                    @endif
                </label>
            </div>

            <!-- Step 3: Preview -->
            <div class="space-y-4">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-8 h-8 rounded-full {{ $showPreview ? 'bg-emerald-500' : ($isProcessing ? 'bg-blue-500 animate-pulse' : ($photoUploaded && $garmentUploaded ? 'bg-primary-500' : 'bg-zinc-300')) }} text-white flex items-center justify-center text-sm font-bold">
                        {{ $showPreview ? '✓' : ($isProcessing ? '⟳' : '3') }}
                    </div>
                    <h3 class="text-lg font-semibold text-zinc-700 dark:text-zinc-300">AI Preview</h3>
                </div>
                
                <div class="aspect-[3/4] border-2 rounded-2xl p-4 min-h-[300px] flex items-center justify-center transition-all duration-300
                    {{ $showPreview ? 'border-emerald-400 bg-gradient-to-br from-emerald-50 to-emerald-100 dark:from-emerald-900/20 dark:to-emerald-800/10' : ($isProcessing ? 'border-blue-400 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/10' : 'border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50') }}">
                    @if($isProcessing)
                        <div class="text-center space-y-4 w-full">
                            <div class="w-20 h-20 mx-auto border-4 border-primary-200 border-t-primary-500 rounded-full animate-spin"></div>
                            <div class="space-y-2">
                                <p class="text-sm text-primary-600 dark:text-primary-400 font-medium">Processing...</p>
                                <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-2 overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-primary-500 to-primary-600 rounded-full transition-all duration-300" 
                                         :style="`width: ${progress}%`"></div>
                                </div>
                                <p class="text-xs text-zinc-500" x-text="`${progress}% complete`"></p>
                            </div>
                        </div>
                    @elseif($showPreview && $previewData)
                        <img :src="'{{ $previewData }}'" alt="Try-On Preview" class="w-full h-full object-cover rounded-xl">
                    @else
                        <div class="text-center">
                            <svg class="w-16 h-16 mx-auto text-zinc-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
                            </svg>
                            <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Upload both to generate</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Buttons -->
        <div class="flex justify-center gap-4 mt-8 pt-6 border-t border-zinc-100 dark:border-zinc-700 flex-wrap">
            <button 
                wire:click="generatePreview" 
                :disabled="!@js($photoUploaded) || !@js($garmentUploaded) || isProcessing"
                class="px-8 py-3 bg-gradient-to-r from-primary-500 to-primary-600 text-white font-medium rounded-xl hover:from-primary-600 hover:to-primary-700 transition-all duration-300 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
                </svg>
                <span x-show="!isProcessing">Generate Preview</span>
                <span x-show="isProcessing">Processing...</span>
            </button>

            @if($showPreview)
                <button 
                    wire:click="saveToGallery" 
                    class="px-8 py-3 text-white bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-xl hover:from-emerald-600 hover:to-emerald-700 transition-all duration-300 font-medium flex items-center gap-2"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h12a2 2 0 002-2v-12a2 2 0 00-2-2h-2.293a2 2 0 00-1.414.586l-2.828 2.828A2 2 0 0015.414 6H12a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    Save to Gallery
                </button>
            @endif
            
            @if($photoUploaded || $garmentUploaded || $showPreview)
                <button 
                    wire:click="resetUpload" 
                    class="px-8 py-3 text-zinc-600 dark:text-zinc-300 bg-zinc-100 dark:bg-zinc-700 hover:bg-zinc-200 dark:hover:bg-zinc-600 rounded-xl transition-colors font-medium"
                >
                    Reset
                </button>
            @endif
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
                            <img :src="'{{ $tryon->preview_data }}'" alt="Try-on" class="w-full h-full object-cover">
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
