<?php

use App\Models\VirtualTryon;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Http;

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

    /**
     * Upload customer photo
     */
    public function uploadPhoto(): void
    {
        $this->validate([
            'photoFile' => 'required|image|max:10240|mimes:jpeg,png,jpg,gif',
        ]);

        try {
            $response = Http::post(route('api.virtual-tryon.upload-photo', [], false), [
                'photo' => fopen($this->photoFile->getRealPath(), 'r'),
            ]);

            if ($response->successful()) {
                $this->photoUploaded = true;
                $this->dispatch('photo-uploaded');
            } else {
                $this->errorMessage = 'Failed to upload photo. ' . ($response->json('error') ?? '');
            }

        } catch (\Exception $e) {
            $this->errorMessage = 'Upload error: ' . $e->getMessage();
        }
    }

    /**
     * Upload garment design
     */
    public function uploadGarment(): void
    {
        $this->validate([
            'garmentFile' => 'required|image|max:10240|mimes:jpeg,png,jpg,gif',
        ]);

        try {
            $response = Http::post(route('api.virtual-tryon.upload-garment', [], false), [
                'garment' => fopen($this->garmentFile->getRealPath(), 'r'),
            ]);

            if ($response->successful()) {
                $this->garmentUploaded = true;
                $this->dispatch('garment-uploaded');
            } else {
                $this->errorMessage = 'Failed to upload garment. ' . ($response->json('error') ?? '');
            }

        } catch (\Exception $e) {
            $this->errorMessage = 'Upload error: ' . $e->getMessage();
        }
    }

    /**
     * Start AI processing
     */
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
            // Get file paths from session
            $photoPath = session('photo_path');
            $garmentPath = session('garment_path');

            if (!$photoPath || !$garmentPath) {
                throw new \Exception('Upload files not found. Please re-upload.');
            }

            // Call backend API to start processing
            $response = Http::post(route('api.virtual-tryon.process', [], false), [
                'photo_path' => $photoPath,
                'garment_path' => $garmentPath,
                'metadata' => [
                    'garment_type' => 'custom',
                    'user_preference' => 'standard',
                ],
            ]);

            if ($response->successful()) {
                $this->currentTryOnId = $response->json('tryon_id');
                $this->processingProgress = 30;

                // Poll for completion
                $this->dispatch('poll-status', tryOnId: $this->currentTryOnId);

            } else {
                throw new \Exception($response->json('error') ?? 'Processing failed');
            }

        } catch (\Exception $e) {
            $this->isProcessing = false;
            $this->errorMessage = $e->getMessage();
        }
    }

    /**
     * Poll processing status
     */
    #[\Livewire\Attributes\On('poll-status')]
    public function pollStatus($tryOnId): void
    {
        try {
            $response = Http::get(route('api.virtual-tryon.status', $tryOnId, false));

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
                    // Still processing, update progress and poll again
                    $this->processingProgress = min(95, $this->processingProgress + rand(5, 15));

                    // Poll again in 2 seconds
                    $this->dispatch('poll-status', tryOnId: $tryOnId)->later(milliseconds: 2000);
                }
            }

        } catch (\Exception $e) {
            $this->isProcessing = false;
            $this->errorMessage = 'Status check failed: ' . $e->getMessage();
        }
    }

    /**
     * Save try-on to gallery
     */
    public function saveToGallery(): void
    {
        if (!$this->currentTryOnId) {
            return;
        }

        try {
            $response = Http::post(route('api.virtual-tryon.save', [], false), [
                'tryon_id' => $this->currentTryOnId,
                'title' => 'Try-on ' . now()->format('M d, Y'),
            ]);

            if ($response->successful()) {
                session()->flash('message', 'Try-on saved to gallery!');
                $this->resetUpload();
            }

        } catch (\Exception $e) {
            $this->errorMessage = 'Failed to save: ' . $e->getMessage();
        }
    }

    /**
     * Share try-on
     */
    public function sharePreview(): void
    {
        if (!$this->currentTryOnId) {
            return;
        }

        session()->flash('message', 'Share link copied to clipboard!');
        $this->dispatch('copy-to-clipboard', url: url('/customer/virtual-tryon/' . $this->currentTryOnId));
    }

    /**
     * Reset form
     */
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
    }
}; ?>

<div class="space-y-8" x-data="{ isProcessing: @entangle('isProcessing'), progress: @entangle('processingProgress') }">
    <!-- Enhanced Header -->
    <div class="relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-primary-500/10 to-secondary-500/10 rounded-3xl transform -skew-y-1 scale-110"></div>
        <div class="relative tc-card bg-gradient-to-br from-white to-cream-50 dark:from-zinc-800 dark:to-zinc-900">
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
                            <p class="text-zinc-500 mt-1">See how your custom garment will look using advanced AI technology</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-800/20 rounded-2xl p-4 border border-blue-200 dark:border-blue-800">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-blue-500 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-lg font-bold text-blue-900 dark:text-blue-100">{{ $totalTryons }}</p>
                                    <p class="text-xs text-blue-600 dark:text-blue-300">Try-Ons Created</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/30 dark:to-purple-800/20 rounded-2xl p-4 border border-purple-200 dark:border-purple-800">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-purple-500 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-lg font-bold text-purple-900 dark:text-purple-100">~30s</p>
                                    <p class="text-xs text-purple-600 dark:text-purple-300">Avg Processing</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Try-On Interface -->
    <div class="tc-card overflow-hidden">
        <h2 class="text-xl font-bold text-zinc-900 dark:text-white mb-6" style="font-family: 'Poppins';">Create New Try-On</h2>
        
        <!-- Error Message -->
        @if($errorMessage)
            <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl flex items-start gap-3">
                <svg class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="text-sm font-medium text-red-800 dark:text-red-300">Error</p>
                    <p class="text-sm text-red-700 dark:text-red-400">{{ $errorMessage }}</p>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Step 1: Upload Photo -->
            <div class="space-y-4">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-8 h-8 rounded-full {{ $photoUploaded ? 'bg-emerald-500' : 'bg-primary-500' }} text-white flex items-center justify-center text-sm font-bold">
                        @if($photoUploaded)
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        @else
                            1
                        @endif
                    </div>
                    <h3 class="text-lg font-semibold text-zinc-700 dark:text-zinc-300">Upload Your Photo</h3>
                </div>
                
                <div wire:filePond="photoFile"
                    class="border-2 border-dashed rounded-2xl p-8 text-center cursor-pointer transition-all hover:border-primary-400 {{ $photoUploaded ? 'border-emerald-400 bg-emerald-50 dark:bg-emerald-900/10' : 'border-zinc-300 dark:border-zinc-600 hover:bg-zinc-50 dark:hover:bg-zinc-800/50' }}">
                    @if($photoUploaded)
                        <svg class="w-16 h-16 mx-auto text-emerald-500 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm text-emerald-600 font-medium">Photo uploaded successfully!</p>
                        <p class="text-xs text-emerald-500 mt-1">{{ $photoFile?->getClientOriginalName() }}</p>
                    @else
                        <svg class="w-16 h-16 mx-auto text-zinc-300 dark:text-zinc-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                        </svg>
                        <p class="text-sm text-zinc-500 font-medium">Click to upload your photo</p>
                        <p class="text-xs text-zinc-400 mt-1">JPG, PNG up to 10MB</p>
                    @endif
                </div>

                <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800">
                    <h4 class="text-sm font-medium text-blue-900 dark:text-blue-100 mb-2">📷 Tips:</h4>
                    <ul class="text-xs text-blue-700 dark:text-blue-300 space-y-1">
                        <li>• Stand facing camera</li>
                        <li>• Good lighting preferred</li>
                        <li>• Fitted clothing works best</li>
                    </ul>
                </div>
            </div>

            <!-- Step 2: Upload Garment -->
            <div class="space-y-4">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-8 h-8 rounded-full {{ $garmentUploaded ? 'bg-emerald-500' : ($photoUploaded ? 'bg-primary-500' : 'bg-zinc-300') }} text-white flex items-center justify-center text-sm font-bold">
                        @if($garmentUploaded)
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        @else
                            2
                        @endif
                    </div>
                    <h3 class="text-lg font-semibold text-zinc-700 dark:text-zinc-300">Upload Garment Design</h3>
                </div>
                
                <div wire:filePond="garmentFile"
                    class="border-2 border-dashed rounded-2xl p-8 text-center cursor-pointer transition-all hover:border-primary-400 {{ $garmentUploaded ? 'border-emerald-400 bg-emerald-50 dark:bg-emerald-900/10' : 'border-zinc-300 dark:border-zinc-600 hover:bg-zinc-50 dark:hover:bg-zinc-800/50' }}">
                    @if($garmentUploaded)
                        <svg class="w-16 h-16 mx-auto text-emerald-500 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm text-emerald-600 font-medium">Garment uploaded successfully!</p>
                        <p class="text-xs text-emerald-500 mt-1">{{ $garmentFile?->getClientOriginalName() }}</p>
                    @else
                        <svg class="w-16 h-16 mx-auto text-zinc-300 dark:text-zinc-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42"/>
                        </svg>
                        <p class="text-sm text-zinc-500 font-medium">Click to upload garment design</p>
                        <p class="text-xs text-zinc-400 mt-1">JPG, PNG up to 10MB</p>
                    @endif
                </div>

                <div class="p-3 bg-amber-50 dark:bg-amber-900/20 rounded-xl border border-amber-200 dark:border-amber-800">
                    <h4 class="text-sm font-medium text-amber-900 dark:text-amber-100 mb-2">✨ Tips:</h4>
                    <ul class="text-xs text-amber-700 dark:text-amber-300 space-y-1">
                        <li>• Clear, high-quality images</li>
                        <li>• Straight-on view preferred</li>
                        <li>• Sketches or photos both OK</li>
                    </ul>
                </div>
            </div>

            <!-- Step 3: AI Preview -->
            <div class="space-y-4">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-8 h-8 rounded-full {{ $showPreview ? 'bg-emerald-500' : ($isProcessing ? 'bg-blue-500 animate-pulse' : ($photoUploaded && $garmentUploaded ? 'bg-primary-500' : 'bg-zinc-300')) }} text-white flex items-center justify-center text-sm font-bold">
                        @if($showPreview)
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        @elseif($isProcessing)
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        @else
                            3
                        @endif
                    </div>
                    <h3 class="text-lg font-semibold text-zinc-700 dark:text-zinc-300">AI Preview</h3>
                </div>
                
                <div class="aspect-[3/4] border-2 rounded-2xl p-4 min-h-[300px] flex items-center justify-center transition-all duration-300
                    {{ $showPreview ? 'border-emerald-400 bg-gradient-to-br from-emerald-50 to-emerald-100 dark:from-emerald-900/20 dark:to-emerald-800/10' : ($isProcessing ? 'border-blue-400 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/10' : 'border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50') }}">
                    @if($isProcessing)
                        <div class="text-center space-y-4 w-full">
                            <div class="w-20 h-20 mx-auto border-4 border-primary-200 border-t-primary-500 rounded-full animate-spin"></div>
                            <div class="space-y-2">
                                <p class="text-sm text-primary-600 dark:text-primary-400 font-medium">AI is processing your try-on...</p>
                                <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-2 overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-primary-500 to-primary-600 rounded-full transition-all duration-300" 
                                         :style="`width: ${progress}%`"></div>
                                </div>
                                <p class="text-xs text-zinc-500" x-text="`${progress}% complete`"></p>
                            </div>
                        </div>
                    @elseif($showPreview && $previewData)
                        <div class="w-full h-full flex flex-col items-center justify-center">
                            <img :src="'{{ $previewData }}'" alt="Virtual Try-On Preview" class="w-full h-full object-cover rounded-xl">
                        </div>
                    @else
                        <div class="text-center space-y-3">
                            <div class="w-16 h-16 mx-auto bg-zinc-200 dark:bg-zinc-700 rounded-2xl flex items-center justify-center">
                                <svg class="w-8 h-8 text-zinc-400 dark:text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">AI Preview</p>
                                <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-1">Upload both files to generate</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-center gap-4 mt-8 pt-6 border-t border-zinc-100 dark:border-zinc-700 flex-wrap">
            <button 
                wire:click="generatePreview" 
                :disabled="!@js($photoUploaded) || !@js($garmentUploaded) || isProcessing"
                class="px-8 py-3 bg-gradient-to-r from-primary-500 to-primary-600 text-white font-medium rounded-xl hover:from-primary-600 hover:to-primary-700 transition-all duration-300 shadow-lg hover:shadow-xl hover:shadow-primary-500/25 click-feedback flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:shadow-lg"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
                </svg>
                <span x-show="!isProcessing">Generate AI Preview</span>
                <span x-show="isProcessing" class="flex items-center gap-2">
                    <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                    Processing...
                </span>
            </button>

            @if($showPreview)
                <button 
                    wire:click="saveToGallery" 
                    class="px-8 py-3 text-white bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl font-medium flex items-center gap-2"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h12a2 2 0 002-2v-12a2 2 0 00-2-2h-2.293a2 2 0 00-1.414.586l-2.828 2.828A2 2 0 0015.414 6H12a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    Save to Gallery
                </button>

                <button 
                    wire:click="sharePreview" 
                    class="px-8 py-3 text-white bg-gradient-to-r from-secondary-500 to-secondary-600 hover:from-secondary-600 hover:to-secondary-700 rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl font-medium flex items-center gap-2"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C9.839 10.053 12.652 8 15.5 8c4.142 0 7.5 3.358 7.5 7.5 0 3.364-2.277 6.265-5.407 7.312m-9.319-1.947L6.75 21m0 0l-.75.75m.75-.75l.75.75m0 0L9 21m0 0l-.75-.75m.75.75l.75-.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Share
                </button>
            @endif
            
            @if($photoUploaded || $garmentUploaded || $showPreview)
                <button 
                    wire:click="resetUpload" 
                    class="px-8 py-3 text-zinc-600 dark:text-zinc-300 bg-zinc-100 dark:bg-zinc-700 hover:bg-zinc-200 dark:hover:bg-zinc-600 rounded-xl transition-colors click-feedback font-medium"
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
                    <div class="aspect-[3/4] rounded-xl overflow-hidden border border-zinc-200 dark:border-zinc-700 hover:shadow-lg transition-all duration-300 hover:-translate-y-1 cursor-pointer group">
                        @if($tryon->preview_data)
                            <img :src="'{{ $tryon->preview_data }}'" alt="Try-on" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-zinc-100 to-zinc-200 dark:from-zinc-700 dark:to-zinc-800 flex items-center justify-center">
                                <svg class="w-8 h-8 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        @endif
                        <div class="absolute top-2 right-2 px-2 py-1 bg-black/70 text-white text-xs rounded-md">
                            {{ $tryon->created_at->format('M j') }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Success Message -->
    @if (session()->has('message'))
        <x-notification-toast 
            type="success" 
            title="Success!" 
            message="{{ session('message') }}"
            :dismissible="true"
        />
    @endif
</div>
