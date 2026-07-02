@props(['images' => [], 'currentIndex' => 0])

<div 
    x-data="{ 
        show: false, 
        currentIndex: {{ $currentIndex }},
        images: @js($images),
        showLightbox(index = 0) {
            this.currentIndex = index;
            this.show = true;
            document.body.style.overflow = 'hidden';
        },
        closeLightbox() {
            this.show = false;
            document.body.style.overflow = '';
        },
        nextImage() {
            this.currentIndex = (this.currentIndex + 1) % this.images.length;
        },
        prevImage() {
            this.currentIndex = this.currentIndex === 0 ? this.images.length - 1 : this.currentIndex - 1;
        },
        handleKeydown(event) {
            if (!this.show) return;
            if (event.key === 'Escape') this.closeLightbox();
            if (event.key === 'ArrowRight') this.nextImage();
            if (event.key === 'ArrowLeft') this.prevImage();
        }
    }"
    @keydown.window="handleKeydown"
    {{ $attributes }}
>
    <!-- Lightbox Modal -->
    <div 
        x-show="show" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 backdrop-blur-sm p-4"
        @click="closeLightbox()"
        style="display: none;"
    >
        <div class="relative max-w-4xl max-h-full w-full h-full flex items-center justify-center">
            <!-- Navigation Controls -->
            <template x-if="images.length > 1">
                <div>
                    <!-- Previous Button -->
                    <button 
                        @click.stop="prevImage()"
                        class="absolute left-4 top-1/2 -translate-y-1/2 z-10 p-3 bg-black/50 text-white rounded-full hover:bg-black/70 transition-colors click-feedback"
                    >
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>

                    <!-- Next Button -->
                    <button 
                        @click.stop="nextImage()"
                        class="absolute right-4 top-1/2 -translate-y-1/2 z-10 p-3 bg-black/50 text-white rounded-full hover:bg-black/70 transition-colors click-feedback"
                    >
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>
            </template>

            <!-- Close Button -->
            <button 
                @click.stop="closeLightbox()"
                class="absolute top-4 right-4 z-10 p-3 bg-black/50 text-white rounded-full hover:bg-black/70 transition-colors click-feedback"
            >
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            <!-- Image Container -->
            <div @click.stop class="relative max-w-full max-h-full">
                <template x-for="(image, index) in images" :key="index">
                    <div 
                        x-show="index === currentIndex"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 scale-90"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-90"
                        class="flex flex-col items-center"
                    >
                        <img 
                            :src="image.url || image" 
                            :alt="image.alt || 'Image'" 
                            class="max-w-full max-h-[80vh] object-contain rounded-lg shadow-2xl"
                        >
                        <template x-if="image.caption">
                            <div class="mt-4 max-w-lg text-center">
                                <p x-text="image.caption" class="text-white text-sm bg-black/50 px-4 py-2 rounded-lg backdrop-blur-sm"></p>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            <!-- Image Counter -->
            <template x-if="images.length > 1">
                <div class="absolute bottom-4 left-1/2 -translate-x-1/2 bg-black/50 text-white px-3 py-1 rounded-full text-sm backdrop-blur-sm">
                    <span x-text="currentIndex + 1"></span> / <span x-text="images.length"></span>
                </div>
            </template>
        </div>
    </div>

    <!-- Trigger Slot -->
    <div class="lightbox-trigger">
        {{ $slot }}
    </div>
</div>