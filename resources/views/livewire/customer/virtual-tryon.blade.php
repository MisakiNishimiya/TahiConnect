<?php

use App\Models\VirtualTryon;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
    public bool $showPreview = false;
    public bool $isProcessing = false;
    public bool $photoUploaded = false;
    public bool $designUploaded = false;

    public function uploadPhoto(): void { $this->photoUploaded = true; }
    public function uploadDesign(): void { $this->designUploaded = true; }

    public function generatePreview(): void
    {
        $this->isProcessing = true;
        $this->showPreview = false;
        // Simulated AI processing delay
        $this->dispatch('preview-ready');
    }

    #[\Livewire\Attributes\On('preview-ready')]
    public function onPreviewReady(): void
    {
        $this->isProcessing = false;
        $this->showPreview = true;
    }

    public function resetPreview(): void
    {
        $this->showPreview = false;
        $this->isProcessing = false;
        $this->photoUploaded = false;
        $this->designUploaded = false;
    }

    public function with(): array
    {
        return [
            'recentTryons' => VirtualTryon::where('user_id', auth()->id())->latest()->take(4)->get(),
        ];
    }
}; ?>

<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">AI Virtual Try-On</h1>
        <p class="text-zinc-500 mt-1">See how your garment will look before it's made.</p>
    </div>

    <!-- Main Try-On Interface -->
    <div class="tc-card overflow-hidden">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Upload Photo -->
            <div class="space-y-3">
                <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">1. Upload Your Photo</h3>
                <div wire:click="uploadPhoto"
                    class="border-2 border-dashed rounded-2xl p-8 text-center cursor-pointer transition-all hover:border-primary-400
                    {{ $photoUploaded ? 'border-emerald-400 bg-emerald-50 dark:bg-emerald-900/10' : 'border-zinc-300 dark:border-zinc-600' }}">
                    @if($photoUploaded)
                        <svg class="w-16 h-16 mx-auto text-emerald-500 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="text-sm text-emerald-600 font-medium">Photo uploaded</p>
                    @else
                        <svg class="w-16 h-16 mx-auto text-zinc-300 dark:text-zinc-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                        <p class="text-sm text-zinc-500">Click to upload your photo</p>
                        <p class="text-xs text-zinc-400 mt-1">JPG, PNG up to 10MB</p>
                    @endif
                </div>
            </div>

            <!-- Upload Design -->
            <div class="space-y-3">
                <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">2. Upload Garment Design</h3>
                <div wire:click="uploadDesign"
                    class="border-2 border-dashed rounded-2xl p-8 text-center cursor-pointer transition-all hover:border-primary-400
                    {{ $designUploaded ? 'border-emerald-400 bg-emerald-50 dark:bg-emerald-900/10' : 'border-zinc-300 dark:border-zinc-600' }}">
                    @if($designUploaded)
                        <svg class="w-16 h-16 mx-auto text-emerald-500 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="text-sm text-emerald-600 font-medium">Design uploaded</p>
                    @else
                        <svg class="w-16 h-16 mx-auto text-zinc-300 dark:text-zinc-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42"/></svg>
                        <p class="text-sm text-zinc-500">Click to upload garment design</p>
                        <p class="text-xs text-zinc-400 mt-1">JPG, PNG up to 10MB</p>
                    @endif
                </div>
            </div>

            <!-- Preview Area -->
            <div class="space-y-3">
                <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">3. AI Preview</h3>
                <div class="border-2 rounded-2xl p-8 text-center min-h-[200px] flex items-center justify-center
                    {{ $showPreview ? 'border-primary-400 bg-gradient-to-br from-primary-50 to-secondary-50 dark:from-primary-900/20 dark:to-secondary-900/20' : 'border-zinc-200 dark:border-zinc-700' }}">
                    @if($isProcessing)
                        <div class="space-y-4">
                            <div class="w-16 h-16 mx-auto border-4 border-primary-200 border-t-primary-500 rounded-full animate-spin"></div>
                            <p class="text-sm text-primary-600 dark:text-primary-400 font-medium animate-pulse-soft">AI is generating your preview...</p>
                        </div>
                    @elseif($showPreview)
                        <div class="space-y-3">
                            <div class="w-20 h-20 mx-auto bg-gradient-to-br from-primary-400 to-secondary-400 rounded-2xl flex items-center justify-center">
                                <svg class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
                            </div>
                            <p class="text-sm text-emerald-600 font-semibold">Preview Generated!</p>
                            <button class="text-xs text-primary-600 hover:text-primary-800 underline">Download Preview</button>
                        </div>
                    @else
                        <div class="space-y-2">
                            <svg class="w-12 h-12 mx-auto text-zinc-300 dark:text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
                            <p class="text-xs text-zinc-400">Preview will appear here</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-center gap-3 mt-6 pt-6 border-t border-zinc-100 dark:border-zinc-700">
            <flux:button wire:click="generatePreview" variant="primary" class="!bg-primary-500 hover:!bg-primary-600" :disabled="!$photoUploaded || !$designUploaded">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
                Generate Preview
            </flux:button>
            @if($showPreview || $photoUploaded || $designUploaded)
                <flux:button wire:click="resetPreview" variant="ghost">Reset</flux:button>
            @endif
        </div>
    </div>
</div>
