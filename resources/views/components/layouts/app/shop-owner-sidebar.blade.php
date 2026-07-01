<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-cream-50 dark:bg-zinc-800">
        <flux:sidebar sticky stashable class="border-r border-primary-100 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <div class="flex items-center gap-2 px-2 pb-4 pt-2">
                <x-app-logo class="size-8" />
                <span class="font-bold text-lg text-primary-600 dark:text-primary-400" style="font-family: 'Poppins';">Shop Owner</span>
            </div>

            <flux:navlist variant="outline">
                <flux:navlist.item icon="home" :href="route('shopowner.dashboard')" :current="request()->routeIs('shopowner.dashboard')" wire:navigate>Dashboard</flux:navlist.item>
                <flux:navlist.item icon="clipboard-document-list" :href="route('shopowner.orders')" :current="request()->routeIs('shopowner.orders')" wire:navigate>Orders</flux:navlist.item>
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
        @fluxScripts
    </body>
</html>
