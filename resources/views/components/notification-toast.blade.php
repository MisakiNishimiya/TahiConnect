@props(['type' => 'info', 'title', 'message', 'actions' => [], 'dismissible' => true])

<div 
    x-data="{ show: true }"
    x-show="show"
    x-transition:enter="notification-enter"
    x-transition:leave="notification-exit"
    class="max-w-sm w-full shadow-lg rounded-xl pointer-events-auto overflow-hidden {{ match($type) {
        'success' => 'bg-emerald-50 border border-emerald-200 dark:bg-emerald-900/20 dark:border-emerald-800',
        'error' => 'bg-red-50 border border-red-200 dark:bg-red-900/20 dark:border-red-800',
        'warning' => 'bg-amber-50 border border-amber-200 dark:bg-amber-900/20 dark:border-amber-800',
        'info' => 'bg-blue-50 border border-blue-200 dark:bg-blue-900/20 dark:border-blue-800',
        default => 'bg-white border border-zinc-200 dark:bg-zinc-800 dark:border-zinc-700'
    } }}"
    {{ $attributes }}
>
    <div class="p-4">
        <div class="flex items-start">
            <!-- Icon -->
            <div class="flex-shrink-0">
                @if($type === 'success')
                    <svg class="w-6 h-6 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                @elseif($type === 'error')
                    <svg class="w-6 h-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                @elseif($type === 'warning')
                    <svg class="w-6 h-6 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                @else
                    <svg class="w-6 h-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                @endif
            </div>
            
            <!-- Content -->
            <div class="ml-3 w-0 flex-1 pt-0.5">
                @if(isset($title))
                    <p class="text-sm font-medium {{ match($type) {
                        'success' => 'text-emerald-900 dark:text-emerald-100',
                        'error' => 'text-red-900 dark:text-red-100',
                        'warning' => 'text-amber-900 dark:text-amber-100',
                        'info' => 'text-blue-900 dark:text-blue-100',
                        default => 'text-zinc-900 dark:text-zinc-100'
                    } }}">{{ $title }}</p>
                @endif
                
                <p class="text-sm {{ match($type) {
                    'success' => 'text-emerald-700 dark:text-emerald-300',
                    'error' => 'text-red-700 dark:text-red-300',
                    'warning' => 'text-amber-700 dark:text-amber-300',
                    'info' => 'text-blue-700 dark:text-blue-300',
                    default => 'text-zinc-500 dark:text-zinc-400'
                } }} {{ isset($title) ? 'mt-1' : '' }}">{{ $message }}</p>
                
                <!-- Actions -->
                @if(count($actions) > 0)
                    <div class="mt-3 flex space-x-2">
                        @foreach($actions as $action)
                            <button 
                                type="button"
                                @if(isset($action['wire:click'])) wire:click="{{ $action['wire:click'] }}" @endif
                                @if(isset($action['onclick'])) onclick="{{ $action['onclick'] }}" @endif
                                class="text-sm font-medium {{ $action['primary'] ?? false ? 
                                    match($type) {
                                        'success' => 'text-white bg-emerald-600 hover:bg-emerald-700 px-3 py-1.5 rounded-lg',
                                        'error' => 'text-white bg-red-600 hover:bg-red-700 px-3 py-1.5 rounded-lg',
                                        'warning' => 'text-white bg-amber-600 hover:bg-amber-700 px-3 py-1.5 rounded-lg',
                                        'info' => 'text-white bg-blue-600 hover:bg-blue-700 px-3 py-1.5 rounded-lg',
                                        default => 'text-white bg-zinc-600 hover:bg-zinc-700 px-3 py-1.5 rounded-lg'
                                    } : 
                                    match($type) {
                                        'success' => 'text-emerald-700 dark:text-emerald-300 hover:text-emerald-800 dark:hover:text-emerald-200',
                                        'error' => 'text-red-700 dark:text-red-300 hover:text-red-800 dark:hover:text-red-200',
                                        'warning' => 'text-amber-700 dark:text-amber-300 hover:text-amber-800 dark:hover:text-amber-200',
                                        'info' => 'text-blue-700 dark:text-blue-300 hover:text-blue-800 dark:hover:text-blue-200',
                                        default => 'text-zinc-700 dark:text-zinc-300 hover:text-zinc-800 dark:hover:text-zinc-200'
                                    }
                                }} transition-colors duration-200"
                            >
                                {{ $action['label'] }}
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
            
            <!-- Dismiss Button -->
            @if($dismissible)
                <div class="ml-4 flex-shrink-0 flex">
                    <button 
                        @click="show = false"
                        class="inline-flex {{ match($type) {
                            'success' => 'text-emerald-400 hover:text-emerald-500 focus:text-emerald-500',
                            'error' => 'text-red-400 hover:text-red-500 focus:text-red-500',
                            'warning' => 'text-amber-400 hover:text-amber-500 focus:text-amber-500',
                            'info' => 'text-blue-400 hover:text-blue-500 focus:text-blue-500',
                            default => 'text-zinc-400 hover:text-zinc-500 focus:text-zinc-500'
                        } }} transition ease-in-out duration-150 focus:outline-none"
                    >
                        <span class="sr-only">Close</span>
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>