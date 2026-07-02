<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-cream-50 dark:bg-zinc-800 bg-mesh-primary">

        <!-- Global Loading Progress Bar -->
        <div id="nprogress-bar"></div>

        <script>
            // Livewire loading progress bar
            document.addEventListener('livewire:navigating', () => {
                const bar = document.getElementById('nprogress-bar');
                bar.classList.remove('done');
                bar.classList.add('loading');
            });
            document.addEventListener('livewire:navigated', () => {
                const bar = document.getElementById('nprogress-bar');
                bar.classList.remove('loading');
                bar.classList.add('done');
                setTimeout(() => bar.classList.remove('done'), 500);
            });
        </script>
        <flux:sidebar sticky stashable class="border-r border-primary-100 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <a href="{{ route('customer.dashboard') }}" class="mr-5 flex items-center space-x-2" wire:navigate>
                <x-app-logo class="size-8" href="#"></x-app-logo>
            </a>

            <flux:navlist variant="outline">
                <flux:navlist.group heading="Menu" class="grid">
                    <flux:navlist.item icon="home" :href="route('customer.dashboard')" :current="request()->routeIs('customer.dashboard')" wire:navigate>Dashboard</flux:navlist.item>
                    <flux:navlist.item icon="building-storefront" :href="route('customer.shops')" :current="request()->routeIs('customer.shops')" wire:navigate>Find a Tailor</flux:navlist.item>
                    <flux:navlist.item icon="chart-bar" :href="route('customer.measurements')" :current="request()->routeIs('customer.measurements')" wire:navigate>My Measurements</flux:navlist.item>
                    <flux:navlist.item icon="sparkles" :href="route('customer.virtual-tryon')" :current="request()->routeIs('customer.virtual-tryon')" wire:navigate>Virtual Try-On</flux:navlist.item>
                    <flux:navlist.item icon="calendar" :href="route('customer.appointments')" :current="request()->routeIs('customer.appointments')" wire:navigate>Appointments</flux:navlist.item>
                    <flux:navlist.item icon="shopping-bag" :href="route('customer.orders')" :current="request()->routeIs('customer.orders')" wire:navigate>Orders</flux:navlist.item>
                    <flux:navlist.item icon="truck" :href="route('customer.tracking')" :current="request()->routeIs('customer.tracking')" wire:navigate>Order Tracking</flux:navlist.item>
                    <flux:navlist.item icon="credit-card" :href="route('customer.payments')" :current="request()->routeIs('customer.payments')" wire:navigate>Payments</flux:navlist.item>
                    <flux:navlist.item :href="route('customer.notifications')" :current="request()->routeIs('customer.notifications')" wire:navigate>
                        <div class="flex items-center justify-between w-full">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                                Notifications
                            </div>
                            @php $unread = \App\Models\CustomNotification::where('user_id', auth()->id())->where('is_read', false)->count(); @endphp
                            @if($unread > 0)
                                <span class="flex items-center justify-center min-w-[20px] h-5 px-1.5 text-[10px] font-bold bg-red-500 text-white rounded-full">{{ $unread > 99 ? '99+' : $unread }}</span>
                            @endif
                        </div>
                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>

            <flux:spacer />

            <!-- Desktop User Menu -->
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
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>
                    <flux:menu.separator />
                    <flux:menu.radio.group>
                        <flux:menu.item href="/settings/profile" icon="cog" wire:navigate>Settings</flux:menu.item>
                    </flux:menu.radio.group>
                    <flux:menu.separator />
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>

        <!-- Mobile Header -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
            <flux:spacer />
            <flux:dropdown position="top" align="end">
                <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />
                <flux:menu>
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
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>
                    <flux:menu.separator />
                    <flux:menu.radio.group>
                        <flux:menu.item href="/settings/profile" icon="cog" wire:navigate>Settings</flux:menu.item>
                    </flux:menu.radio.group>
                    <flux:menu.separator />
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        <!-- Mobile Bottom Navigation Bar (Customer) -->
        <nav class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-white dark:bg-zinc-900 border-t border-zinc-200 dark:border-zinc-700 safe-bottom shadow-lg shadow-zinc-900/10">
            <div class="grid grid-cols-5 gap-0">
                <!-- Dashboard -->
                <a href="{{ route('customer.dashboard') }}" wire:navigate
                    class="flex flex-col items-center justify-center py-3 px-1 transition-colors {{ request()->routeIs('customer.dashboard') ? 'text-primary-600 dark:text-primary-400' : 'text-zinc-500 dark:text-zinc-400 hover:text-primary-500' }}">
                    <svg class="w-5 h-5 mb-1 {{ request()->routeIs('customer.dashboard') ? 'text-primary-600 dark:text-primary-400' : '' }}" fill="{{ request()->routeIs('customer.dashboard') ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75"/>
                    </svg>
                    <span class="text-[10px] font-medium leading-none">Home</span>
                    @if(request()->routeIs('customer.dashboard'))
                        <div class="w-1 h-1 bg-primary-500 rounded-full mt-1"></div>
                    @endif
                </a>

                <!-- Shops -->
                <a href="{{ route('customer.shops') }}" wire:navigate
                    class="flex flex-col items-center justify-center py-3 px-1 transition-colors {{ request()->routeIs('customer.shops*') ? 'text-primary-600 dark:text-primary-400' : 'text-zinc-500 dark:text-zinc-400 hover:text-primary-500' }}">
                    <svg class="w-5 h-5 mb-1" fill="{{ request()->routeIs('customer.shops*') ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614"/>
                    </svg>
                    <span class="text-[10px] font-medium leading-none">Shops</span>
                    @if(request()->routeIs('customer.shops*'))
                        <div class="w-1 h-1 bg-primary-500 rounded-full mt-1"></div>
                    @endif
                </a>

                <!-- Orders (centre, highlighted) -->
                <a href="{{ route('customer.orders') }}" wire:navigate
                    class="flex flex-col items-center justify-center py-2 px-1 relative">
                    <div class="w-12 h-12 -mt-5 rounded-2xl flex items-center justify-center shadow-lg shadow-primary-500/30 transition-transform hover:scale-105 {{ request()->routeIs('customer.orders*') ? 'bg-primary-600' : 'bg-gradient-to-br from-primary-500 to-primary-600' }}">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z"/>
                        </svg>
                    </div>
                    <span class="text-[10px] font-medium leading-none mt-1 {{ request()->routeIs('customer.orders*') ? 'text-primary-600 dark:text-primary-400' : 'text-zinc-500 dark:text-zinc-400' }}">Orders</span>
                </a>

                <!-- Appointments -->
                <a href="{{ route('customer.appointments') }}" wire:navigate
                    class="flex flex-col items-center justify-center py-3 px-1 transition-colors {{ request()->routeIs('customer.appointments') ? 'text-primary-600 dark:text-primary-400' : 'text-zinc-500 dark:text-zinc-400 hover:text-primary-500' }}">
                    <svg class="w-5 h-5 mb-1" fill="{{ request()->routeIs('customer.appointments') ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                    </svg>
                    <span class="text-[10px] font-medium leading-none">Calendar</span>
                    @if(request()->routeIs('customer.appointments'))
                        <div class="w-1 h-1 bg-primary-500 rounded-full mt-1"></div>
                    @endif
                </a>

                <!-- Notifications -->
                <a href="{{ route('customer.notifications') }}" wire:navigate
                    class="flex flex-col items-center justify-center py-3 px-1 transition-colors relative {{ request()->routeIs('customer.notifications') ? 'text-primary-600 dark:text-primary-400' : 'text-zinc-500 dark:text-zinc-400 hover:text-primary-500' }}">
                    @php $unreadCount = \App\Models\CustomNotification::where('user_id', auth()->id())->where('is_read', false)->count(); @endphp
                    <div class="relative mb-1">
                        <svg class="w-5 h-5" fill="{{ request()->routeIs('customer.notifications') ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                        </svg>
                        @if($unreadCount > 0)
                            <span class="absolute -top-1.5 -right-1.5 min-w-[14px] h-[14px] px-0.5 bg-red-500 text-white text-[8px] font-bold rounded-full flex items-center justify-center">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                        @endif
                    </div>
                    <span class="text-[10px] font-medium leading-none">Alerts</span>
                    @if(request()->routeIs('customer.notifications'))
                        <div class="w-1 h-1 bg-primary-500 rounded-full mt-1"></div>
                    @endif
                </a>
            </div>
        </nav>

        <!-- Bottom spacing for mobile nav -->
        <div class="lg:hidden h-20"></div>

        @fluxScripts
    </body>
</html>
