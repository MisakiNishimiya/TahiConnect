<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-cream-50 dark:bg-zinc-800 bg-mesh-primary">
        <div id="nprogress-bar"></div>
        <flux:sidebar sticky stashable class="border-r border-primary-100 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <div class="flex items-center gap-2 px-2 pb-4 pt-2">
                <x-app-logo class="size-8" />
                <span class="font-bold text-lg text-primary-600 dark:text-primary-400" style="font-family: 'Poppins';">Shop Owner</span>
            </div>

            <flux:navlist variant="outline">
                <flux:navlist.item icon="home" :href="route('shopowner.dashboard')" :current="request()->routeIs('shopowner.dashboard')" wire:navigate>Dashboard</flux:navlist.item>
                <flux:navlist.item :href="route('shopowner.orders')" :current="request()->routeIs('shopowner.orders')" wire:navigate>
                    <div class="flex items-center justify-between w-full">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/></svg>
                            Orders
                        </div>
                        @php $pendingOrders = \App\Models\Order::forCurrentUserShop()->where('status','pending')->count(); @endphp
                        @if($pendingOrders > 0)
                            <span class="flex items-center justify-center min-w-[20px] h-5 px-1.5 text-[10px] font-bold bg-amber-500 text-white rounded-full">{{ $pendingOrders }}</span>
                        @endif
                    </div>
                </flux:navlist.item>
                <flux:navlist.item icon="users" :href="route('shopowner.staff')" :current="request()->routeIs('shopowner.staff')" wire:navigate>Staff</flux:navlist.item>
                <flux:navlist.item icon="swatch" :href="route('shopowner.garments')" :current="request()->routeIs('shopowner.garments')" wire:navigate>Catalog & Fabrics</flux:navlist.item>
                <flux:navlist.item icon="cog-6-tooth" :href="route('shopowner.settings')" :current="request()->routeIs('shopowner.settings')" wire:navigate>Shop Settings</flux:navlist.item>
            </flux:navlist>

            <flux:spacer />

            <flux:dropdown position="bottom" align="start">
                <flux:profile
                    :name="auth()->user()->name"
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevrons-up-down"
                />
                <flux:menu class="w-[220px]">
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span class="flex h-full w-full items-center justify-center rounded-lg bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-300">
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>
                                <div class="grid flex-1 text-left text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs text-zinc-500">Shop Owner</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>
                    <flux:menu.separator />
                    <flux:menu.item href="/settings/profile" icon="cog" wire:navigate>Settings</flux:menu.item>
                    <flux:menu.separator />
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">{{ __('Log Out') }}</flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>

        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
            <flux:spacer />
            <flux:dropdown position="top" align="end">
                <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />
                <flux:menu>
                    <flux:menu.item href="/settings/profile" icon="cog" wire:navigate>Settings</flux:menu.item>
                    <flux:menu.separator />
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">{{ __('Log Out') }}</flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        <!-- Mobile Bottom Navigation (Shop Owner) -->
        <nav class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-white dark:bg-zinc-900 border-t border-zinc-200 dark:border-zinc-700 safe-bottom shadow-lg">
            <div class="grid grid-cols-5 gap-0">
                <a href="{{ route('shopowner.dashboard') }}" wire:navigate
                    class="flex flex-col items-center justify-center py-3 transition-colors {{ request()->routeIs('shopowner.dashboard') ? 'text-primary-600 dark:text-primary-400' : 'text-zinc-500 dark:text-zinc-400' }}">
                    <svg class="w-5 h-5 mb-1" fill="{{ request()->routeIs('shopowner.dashboard') ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75"/></svg>
                    <span class="text-[10px] font-medium">Home</span>
                </a>
                <a href="{{ route('shopowner.orders') }}" wire:navigate
                    class="flex flex-col items-center justify-center py-3 transition-colors relative {{ request()->routeIs('shopowner.orders') ? 'text-primary-600 dark:text-primary-400' : 'text-zinc-500 dark:text-zinc-400' }}">
                    @php $pendingOrders = \App\Models\Order::forCurrentUserShop()->where('status','pending')->count(); @endphp
                    <div class="relative mb-1">
                        <svg class="w-5 h-5" fill="{{ request()->routeIs('shopowner.orders') ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/></svg>
                        @if($pendingOrders > 0)
                            <span class="absolute -top-1.5 -right-1.5 min-w-[14px] h-[14px] px-0.5 bg-amber-500 text-white text-[8px] font-bold rounded-full flex items-center justify-center">{{ $pendingOrders }}</span>
                        @endif
                    </div>
                    <span class="text-[10px] font-medium">Orders</span>
                </a>
                <a href="{{ route('shopowner.staff') }}" wire:navigate
                    class="flex flex-col items-center justify-center py-3 transition-colors {{ request()->routeIs('shopowner.staff') ? 'text-primary-600 dark:text-primary-400' : 'text-zinc-500 dark:text-zinc-400' }}">
                    <svg class="w-5 h-5 mb-1" fill="{{ request()->routeIs('shopowner.staff') ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0z"/></svg>
                    <span class="text-[10px] font-medium">Staff</span>
                </a>
                <a href="{{ route('shopowner.garments') }}" wire:navigate
                    class="flex flex-col items-center justify-center py-3 transition-colors {{ request()->routeIs('shopowner.garments') ? 'text-primary-600 dark:text-primary-400' : 'text-zinc-500 dark:text-zinc-400' }}">
                    <svg class="w-5 h-5 mb-1" fill="{{ request()->routeIs('shopowner.garments') ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
                    <span class="text-[10px] font-medium">Catalog</span>
                </a>
                <a href="{{ route('shopowner.settings') }}" wire:navigate
                    class="flex flex-col items-center justify-center py-3 transition-colors {{ request()->routeIs('shopowner.settings') ? 'text-primary-600 dark:text-primary-400' : 'text-zinc-500 dark:text-zinc-400' }}">
                    <svg class="w-5 h-5 mb-1" fill="{{ request()->routeIs('shopowner.settings') ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span class="text-[10px] font-medium">Settings</span>
                </a>
            </div>
        </nav>
        <div class="lg:hidden h-16"></div>

        @fluxScripts
    </body>
</html>
