<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <script>if(!localStorage.getItem('flux-appearance')){localStorage.setItem('flux-appearance','light');}</script>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-cream-50 dark:bg-zinc-800 bg-mesh-primary">
        <div id="nprogress-bar"></div>
        <flux:sidebar sticky stashable class="border-r border-primary-100 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <a href="{{ route('staff.dashboard') }}" class="flex items-center gap-2 px-2 pb-4 pt-2" wire:navigate>
                <div class="flex aspect-square size-8 items-center justify-center rounded-lg bg-primary-500 shrink-0">
                    <x-app-logo-icon class="size-5 fill-current text-white" />
                </div>
                <div>
                    <span class="font-bold text-lg text-primary-600 dark:text-primary-400" style="font-family: 'Poppins';">TahiConnect</span>
                    <p class="text-[10px] text-zinc-500 font-medium uppercase tracking-wider leading-none mt-0.5">Tailor Staff</p>
                </div>
            </a>

            <flux:navlist variant="outline">
                <flux:navlist.group heading="Workshop" class="grid">
                    <flux:navlist.item icon="home" :href="route('staff.dashboard')" :current="request()->routeIs('staff.dashboard')" wire:navigate>Dashboard</flux:navlist.item>

                    {{-- Orders nav item — shows available unassigned badge --}}
                    <flux:navlist.item :href="route('staff.orders')" :current="request()->routeIs('staff.orders')" wire:navigate>
                        <div class="flex items-center justify-between w-full">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z"/></svg>
                                Orders
                            </div>
                            @php
                                $unassignedOrders = \App\Models\Order::whereNull('staff_id')->count();
                                $myActiveOrders   = \App\Models\Order::where('staff_id', auth()->id())->whereNotIn('status', ['completed','released'])->count();
                            @endphp
                            <div class="flex items-center gap-1">
                                @if($unassignedOrders > 0)
                                    <span class="flex items-center justify-center min-w-[20px] h-5 px-1.5 text-[10px] font-bold bg-amber-500 text-white rounded-full" title="{{ $unassignedOrders }} available">{{ $unassignedOrders }}</span>
                                @endif
                                @if($myActiveOrders > 0)
                                    <span class="flex items-center justify-center min-w-[20px] h-5 px-1.5 text-[10px] font-bold bg-primary-500 text-white rounded-full" title="{{ $myActiveOrders }} assigned to you">{{ $myActiveOrders }}</span>
                                @endif
                            </div>
                        </div>
                    </flux:navlist.item>
                    <flux:navlist.item :href="route('staff.appointments')" :current="request()->routeIs('staff.appointments')" wire:navigate>
                        <div class="flex items-center justify-between w-full">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25"/></svg>
                                Appointments
                            </div>
                            @php $todayAppts = \App\Models\Appointment::where('staff_id', auth()->id())->whereDate('date', today())->whereIn('status', ['pending','confirmed'])->count(); @endphp
                            @if($todayAppts > 0)
                                <span class="flex items-center justify-center min-w-[20px] h-5 px-1.5 text-[10px] font-bold bg-amber-500 text-white rounded-full">{{ $todayAppts }}</span>
                            @endif
                        </div>
                    </flux:navlist.item>
                </flux:navlist.group>
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
                                    <span class="truncate text-xs text-zinc-500">Tailor Staff</span>
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

        <!-- Mobile Bottom Navigation (Staff) -->
        <nav class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-white dark:bg-zinc-900 border-t border-zinc-200 dark:border-zinc-700 safe-bottom shadow-lg">
            <div class="grid grid-cols-3 gap-0">
                <a href="{{ route('staff.dashboard') }}" wire:navigate
                    class="flex flex-col items-center justify-center py-3 transition-colors {{ request()->routeIs('staff.dashboard') ? 'text-primary-600 dark:text-primary-400' : 'text-zinc-500 dark:text-zinc-400' }}">
                    <svg class="w-5 h-5 mb-1" fill="{{ request()->routeIs('staff.dashboard') ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75"/></svg>
                    <span class="text-[10px] font-medium">Dashboard</span>
                </a>
                <a href="{{ route('staff.orders') }}" wire:navigate
                    class="flex flex-col items-center justify-center py-3 transition-colors relative {{ request()->routeIs('staff.orders') ? 'text-primary-600 dark:text-primary-400' : 'text-zinc-500 dark:text-zinc-400' }}">
                    @php
                        $unassignedOrders = \App\Models\Order::whereNull('staff_id')->count();
                        $myActiveOrders   = \App\Models\Order::where('staff_id', auth()->id())->whereNotIn('status', ['completed','released'])->count();
                    @endphp
                    <div class="relative mb-1">
                        <svg class="w-5 h-5" fill="{{ request()->routeIs('staff.orders') ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z"/></svg>
                        @if($unassignedOrders > 0)
                            <span class="absolute -top-1.5 -right-1.5 min-w-[14px] h-[14px] px-0.5 bg-amber-500 text-white text-[8px] font-bold rounded-full flex items-center justify-center">{{ $unassignedOrders }}</span>
                        @elseif($myActiveOrders > 0)
                            <span class="absolute -top-1.5 -right-1.5 min-w-[14px] h-[14px] px-0.5 bg-primary-500 text-white text-[8px] font-bold rounded-full flex items-center justify-center">{{ $myActiveOrders }}</span>
                        @endif
                    </div>
                    <span class="text-[10px] font-medium">Orders</span>
                </a>
                <a href="{{ route('staff.appointments') }}" wire:navigate
                    class="flex flex-col items-center justify-center py-3 transition-colors {{ request()->routeIs('staff.appointments') ? 'text-primary-600 dark:text-primary-400' : 'text-zinc-500 dark:text-zinc-400' }}">
                    <svg class="w-5 h-5 mb-1" fill="{{ request()->routeIs('staff.appointments') ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25"/></svg>
                    <span class="text-[10px] font-medium">Appointments</span>
                </a>
            </div>
        </nav>
        <div class="lg:hidden h-16"></div>

        @fluxScripts
    </body>
</html>
