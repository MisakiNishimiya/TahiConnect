@props([
    'label' => 'Upload File',
    'description' => 'Drag and drop your file here or click to browse',
    'accept' => 'image/*',
    'maxSize' => '10MB',
    'preview' => true,
    'multiple' => false,
    'wire:model' => null,
    'id' => 'file-upload-' . rand(),
])

<div class="space-y-2">
    @if($label)
    <label for="{{ $id }}" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $label }}</label>
    @endif
    
    <div 
        x-data="{
            isDragging: false,
            isUploading: false,
            uploadProgress: 0,
            files: [],
            previewUrls: []
        }"
        x-on:dragover.prevent="isDragging = true"
        x-on:dragleave.prevent="isDragging = false" 
        x-on:drop.prevent="
            isDragging = false;
            let droppedFiles = Array.from($event.dataTransfer.files);
            if (!{{ $multiple ? 'true' : 'false' }}) droppedFiles = droppedFiles.slice(0, 1);
            files = droppedFiles;
            
            // Create preview URLs
            previewUrls = [];
            droppedFiles.forEach(file => {
                if (file.type.startsWith('image/')) {
                    previewUrls.push(URL.createObjectURL(file));
                }
            });
        "
        class="upload-zone border-2 border-dashed rounded-2xl p-8 text-center cursor-pointer transition-all duration-300"
        :class="{
            'dragover border-primary-400 bg-primary-50 dark:bg-primary-900/20 scale-105': isDragging,
            'border-emerald-400 bg-emerald-50 dark:bg-emerald-900/10': files.length > 0,
            'border-zinc-300 dark:border-zinc-600 hover:border-primary-300': files.length === 0 && !isDragging
        }"
        x-on:click="$refs.fileInput.click()"
    >
        <!-- File Input -->
        <input 
            type="file" 
            x-ref="fileInput"
            class="hidden"
            accept="{{ $accept }}"
            {{ $multiple ? 'multiple' : '' }}
            {{ $attributes->whereStartsWith('wire:') }}
            x-on:change="
                files = Array.from($event.target.files);
                previewUrls = [];
                files.forEach(file => {
                    if (file.type.startsWith('image/')) {
                        previewUrls.push(URL.createObjectURL(file));
                    }
                });
            "
        />

        <!-- Upload State: Empty -->
        <div x-show="files.length === 0" class="space-y-4">
            <div class="w-16 h-16 mx-auto rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center">
                <svg class="w-8 h-8 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a2.25 2.25 0 01-2.25-2.25V6.75A2.25 2.25 0 016.75 4.5h10.5a2.25 2.25 0 012.25 2.25v10.5a2.25 2.25 0 01-2.25 2.25h-10.5z" />
                </svg>
            </div>
            <div>
                <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $description }}</p>
                <p class="text-xs text-zinc-500 mt-1">{{ strtoupper(str_replace('*', '', $accept)) }} up to {{ $maxSize }}</p>
            </div>
        </div>

        <!-- Upload State: Files Selected -->
        <div x-show="files.length > 0" class="space-y-4">
            <!-- Success Icon -->
            <div class="w-16 h-16 mx-auto rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                <svg class="w-8 h-8 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            
            <!-- File Info -->
            <div>
                <p class="text-sm font-medium text-emerald-700 dark:text-emerald-300" x-text="files.length === 1 ? files[0].name : files.length + ' files selected'"></p>
                <p class="text-xs text-emerald-600 dark:text-emerald-400 mt-1">Click or drag to replace</p>
            </div>
        </div>

        <!-- Upload State: Dragging -->
        <div x-show="isDragging" class="space-y-4">
            <div class="w-16 h-16 mx-auto rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center animate-bounce">
                <svg class="w-8 h-8 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-primary-700 dark:text-primary-300">Drop your files here!</p>
        </div>

        <!-- Upload Progress Bar -->
        <div x-show="isUploading" class="upload-progress" :style="`--progress: ${uploadProgress}%`"></div>
    </div>

    <!-- Image Previews -->
    @if($preview)
    <div x-show="previewUrls.length > 0" class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
        <template x-for="(url, index) in previewUrls" :key="index">
            <div class="relative group">
                <img :src="url" class="w-full h-24 object-cover rounded-lg border border-zinc-200 dark:border-zinc-700" />
                <button 
                    x-on:click.stop="
                        previewUrls.splice(index, 1);
                        files = Array.from(files).filter((_, i) => i !== index);
                        URL.revokeObjectURL(url);
                    "
                    class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"
                >
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </template>
    </div>
    @endif

    <!-- File Size Validation -->
    <div x-show="files.length > 0" class="text-xs text-zinc-500">
        <template x-for="file in files">
            <div x-show="file.size > 10485760" class="text-red-500">
                <span x-text="file.name"></span> is too large (max {{ $maxSize }})
            </div>
        </template>
    </div>
</div>

<!-- Wire Loading Listener -->
<div 
    wire:loading.class="opacity-50 pointer-events-none" 
    wire:target="{{ str_replace('wire:', '', $attributes->whereStartsWith('wire:')->first()) }}"
    x-init="
        $wire.on('upload:start', () => { isUploading = true; uploadProgress = 0; });
        $wire.on('upload:progress', (progress) => { uploadProgress = progress; });
        $wire.on('upload:finish', () => { isUploading = false; uploadProgress = 100; });
        $wire.on('upload:error', () => { isUploading = false; uploadProgress = 0; });
    "
></div>