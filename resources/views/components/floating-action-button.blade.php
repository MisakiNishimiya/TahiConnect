@props(['icon' => 'plus', 'tooltip' => '', 'position' => 'bottom-right'])

<div 
    x-data="{ 
        showTooltip: false,
        showMenu: false,
        items: @js($slot->isNotEmpty() ? true : false)
    }"
    class="fixed z-50 {{ match($position) {
        'bottom-right' => 'bottom-6 right-6',
        'bottom-left' => 'bottom-6 left-6',
        'top-right' => 'top-6 right-6',
        'top-left' => 'top-6 left-6',
        default => 'bottom-6 right-6'
    } }}"
>
    <!-- Tooltip -->
    @if($tooltip)
        <div 
            x-show="showTooltip && !showMenu" 
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-90"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-90"
            class="absolute {{ str_contains($position, 'right') ? 'right-0' : 'left-0' }} {{ str_contains($position, 'bottom') ? 'bottom-16' : 'top-16' }} mb-2 px-3 py-2 bg-zinc-900 text-white text-sm rounded-lg whitespace-nowrap shadow-lg"
            style="display: none;"
        >
            {{ $tooltip }}
            <!-- Arrow -->
            <div class="absolute {{ str_contains($position, 'right') ? 'right-6' : 'left-6' }} {{ str_contains($position, 'bottom') ? '-bottom-1' : '-top-1' }} w-2 h-2 bg-zinc-900 transform rotate-45"></div>
        </div>
    @endif

    <!-- Sub Menu (if slot content exists) -->
    @if($slot->isNotEmpty())
        <div 
            x-show="showMenu"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 {{ str_contains($position, 'bottom') ? 'translate-y-4' : '-translate-y-4' }}"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 {{ str_contains($position, 'bottom') ? 'translate-y-4' : '-translate-y-4' }}"
            class="absolute {{ str_contains($position, 'right') ? 'right-0' : 'left-0' }} {{ str_contains($position, 'bottom') ? 'bottom-20' : 'top-20' }} space-y-3"
            style="display: none;"
            @click.away="showMenu = false"
        >
            {{ $slot }}
        </div>
    @endif

    <!-- Main FAB -->
    <button 
        @mouseenter="showTooltip = true"
        @mouseleave="showTooltip = false"
        @click="{{ $slot->isNotEmpty() ? 'showMenu = !showMenu' : ($attributes->get('wire:click') ?: ($attributes->get('onclick') ?: '')) }}"
        {{ $attributes->except(['wire:click', 'onclick']) }}
        class="fab group {{ $slot->isNotEmpty() ? 'transition-transform' : '' }}"
        :class="{ 'rotate-45': showMenu && items }"
    >
        @if($icon === 'plus')
            <svg class="w-6 h-6 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
        @elseif($icon === 'message')
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
        @elseif($icon === 'phone')
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
            </svg>
        @elseif($icon === 'edit')
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
        @else
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/>
            </svg>
        @endif
    </button>
</div>

@if($slot->isEmpty())
<style>
.fab {
    @apply fixed bottom-6 right-6 w-14 h-14 bg-primary-500 text-white rounded-full shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center z-40 hover:scale-110;
}
.fab:hover {
    @apply bg-primary-600;
}
</style>
@endif