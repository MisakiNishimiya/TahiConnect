@props([
    'percentage' => 0,
    'size' => 80,
    'strokeWidth' => 6,
    'color' => 'primary',
    'label' => '',
    'animated' => true
])

@php
$radius = ($size - $strokeWidth) / 2;
$circumference = 2 * pi() * $radius;
$dashOffset = $circumference - (($percentage / 100) * $circumference);

$colors = [
    'primary' => 'stroke-primary-500',
    'blue' => 'stroke-blue-500',
    'emerald' => 'stroke-emerald-500',
    'amber' => 'stroke-amber-500',
    'purple' => 'stroke-purple-500',
    'red' => 'stroke-red-500',
];

$bgColors = [
    'primary' => 'stroke-primary-100 dark:stroke-primary-900/30',
    'blue' => 'stroke-blue-100 dark:stroke-blue-900/30',
    'emerald' => 'stroke-emerald-100 dark:stroke-emerald-900/30',
    'amber' => 'stroke-amber-100 dark:stroke-amber-900/30',
    'purple' => 'stroke-purple-100 dark:stroke-purple-900/30',
    'red' => 'stroke-red-100 dark:stroke-red-900/30',
];
@endphp

<div class="flex flex-col items-center">
    <div class="relative inline-flex items-center justify-center">
        <svg 
            width="{{ $size }}" 
            height="{{ $size }}" 
            class="transform -rotate-90"
            x-data="{ percentage: 0, targetPercentage: {{ $percentage }} }"
            x-init="
                if ({{ $animated ? 'true' : 'false' }}) {
                    setTimeout(() => {
                        $el.style.transition = 'all 1s ease-in-out';
                        percentage = targetPercentage;
                    }, 200);
                } else {
                    percentage = targetPercentage;
                }
            "
        >
            <!-- Background Circle -->
            <circle
                cx="{{ $size / 2 }}"
                cy="{{ $size / 2 }}"
                r="{{ $radius }}"
                fill="none"
                class="{{ $bgColors[$color] ?? $bgColors['primary'] }}"
                stroke-width="{{ $strokeWidth }}"
            />
            
            <!-- Progress Circle -->
            <circle
                cx="{{ $size / 2 }}"
                cy="{{ $size / 2 }}"
                r="{{ $radius }}"
                fill="none"
                class="{{ $colors[$color] ?? $colors['primary'] }}"
                stroke-width="{{ $strokeWidth }}"
                stroke-linecap="round"
                stroke-dasharray="{{ $circumference }}"
                :stroke-dashoffset="circumference - ((percentage / 100) * circumference)"
                style="transition: stroke-dashoffset 1s ease-in-out"
            />
        </svg>
        
        <!-- Percentage Text -->
        <div class="absolute inset-0 flex flex-col items-center justify-center">
            <span 
                x-data="{ displayPercentage: 0 }"
                x-init="
                    if ({{ $animated ? 'true' : 'false' }}) {
                        let start = 0;
                        let end = {{ $percentage }};
                        let duration = 1000;
                        let startTime = Date.now();
                        
                        function animate() {
                            let elapsed = Date.now() - startTime;
                            let progress = Math.min(elapsed / duration, 1);
                            displayPercentage = Math.round(start + (end - start) * progress);
                            
                            if (progress < 1) {
                                requestAnimationFrame(animate);
                            }
                        }
                        
                        setTimeout(() => animate(), 200);
                    } else {
                        displayPercentage = {{ $percentage }};
                    }
                "
                x-text="displayPercentage + '%'"
                class="text-sm font-bold text-zinc-900 dark:text-white"
                style="font-family: 'Poppins'"
            ></span>
        </div>
    </div>
    
    @if($label)
    <p class="text-xs text-zinc-500 mt-2 text-center">{{ $label }}</p>
    @endif
</div>