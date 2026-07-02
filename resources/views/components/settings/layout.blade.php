<div class="flex items-start max-md:flex-col gap-8">
    <!-- Settings Sidebar Nav -->
    <div class="w-full md:w-[200px] shrink-0">
        <nav class="space-y-1">
            @php
                $navItems = [
                    ['route' => 'settings.profile', 'label' => 'Profile', 'icon' => 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z'],
                    ['route' => 'settings.password', 'label' => 'Password', 'icon' => 'M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z'],
                    ['route' => 'settings.appearance', 'label' => 'Appearance', 'icon' => 'M4.098 19.902a3.75 3.75 0 005.304 0l6.401-6.402M6.75 21A3.75 3.75 0 013 17.25V4.125C3 3.504 3.504 3 4.125 3h5.25c.621 0 1.125.504 1.125 1.125v4.072M6.75 21a3.75 3.75 0 003.75-3.75V8.197M6.75 21h13.125c.621 0 1.125-.504 1.125-1.125v-5.25c0-.621-.504-1.125-1.125-1.125h-4.072M10.5 8.197l2.88-2.88c.438-.439 1.15-.439 1.59 0l3.712 3.713c.44.44.44 1.152 0 1.59l-2.879 2.88M6.75 17.25h.008v.008H6.75v-.008z'],
                ];
            @endphp

            @foreach($navItems as $item)
                @php $isActive = request()->routeIs($item['route']); @endphp
                <a href="{{ route($item['route']) }}" wire:navigate
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ $isActive ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300' : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-800 hover:text-zinc-900 dark:hover:text-zinc-200' }}">
                    <svg class="w-4 h-4 shrink-0 {{ $isActive ? 'text-primary-600 dark:text-primary-400' : 'text-zinc-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/>
                    </svg>
                    {{ $item['label'] }}
                    @if($isActive)
                        <div class="ml-auto w-1.5 h-1.5 rounded-full bg-primary-500"></div>
                    @endif
                </a>
            @endforeach
        </nav>
    </div>

    <div class="hidden md:block w-px bg-zinc-200 dark:bg-zinc-700 self-stretch"></div>

    <!-- Settings Content -->
    <div class="flex-1 min-w-0">
        <div class="mb-6">
            <h2 class="text-xl font-bold text-zinc-900 dark:text-white" style="font-family:'Poppins'">{{ $heading ?? '' }}</h2>
            <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">{{ $subheading ?? '' }}</p>
        </div>

        <div class="w-full max-w-xl">
            {{ $slot }}
        </div>
    </div>
</div>
