<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <script>if(!localStorage.getItem('flux-appearance')){localStorage.setItem('flux-appearance','light');}</script>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-cream-50 dark:bg-zinc-800 bg-mesh-primary">
        <div id="nprogress-bar"></div>
        <flux:sidebar sticky stashable class="border-r border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <div class="flex items-center gap-2 px-2 pb-4 pt-2">
                <div class="flex aspect-square size-8 items-center justify-center rounded-lg bg-primary-500 shrink-0">
                    <x-app-logo-icon class="size-5 fill-current text-white" />
                </div>
                <div>
                    <span class="font-bold text-lg text-primary-600 dark:text-primary-400" style="font-family: 'Poppins';">TahiConnect</span>
                    <p class="text-[10px] text-zinc-500 font-medium uppercase tracking-wider leading-none mt-0.5">Super Admin</p>
                </div>
            </div>

            <flux:navlist variant="outline">
                {{-- Platform Overview --}}
                <flux:navlist.group heading="Platform" class="grid">
                    <flux:navlist.item icon="chart-bar-square" :href="route('superadmin.dashboard')" :current="request()->routeIs('superadmin.dashboard')" wire:navigate>
                        Dashboard
                    </flux:navlist.item>
                    <flux:navlist.item icon="signal" :href="route('superadmin.health')" :current="request()->routeIs('superadmin.health')" wire:navigate>
                        System Health
                    </flux:navlist.item>
                    <flux:navlist.item icon="chart-pie" :href="route('superadmin.usage')" :current="request()->routeIs('superadmin.usage')" wire:navigate>
                        Usage Monitoring
                    </flux:navlist.item>
                </flux:navlist.group>

                {{-- Accounts --}}
                <flux:navlist.group heading="Accounts" class="grid">
                    <flux:navlist.item icon="building-storefront" :href="route('superadmin.shop-owners')" :current="request()->routeIs('superadmin.shop-owners*')" wire:navigate>
                        Shop Owner Accounts
                    </flux:navlist.item>
                    <flux:navlist.item icon="users" :href="route('superadmin.users')" :current="request()->routeIs('superadmin.users')" wire:navigate>
                        User Management
                    </flux:navlist.item>
                    <flux:navlist.item icon="credit-card" :href="route('superadmin.subscription')" :current="request()->routeIs('superadmin.subscription')" wire:navigate>
                        License Management
                    </flux:navlist.item>
                </flux:navlist.group>

                {{-- System --}}
                <flux:navlist.group heading="System" class="grid">
                    <flux:navlist.item icon="cog-6-tooth" :href="route('superadmin.system')" :current="request()->routeIs('superadmin.system')" wire:navigate>
                        System Configuration
                    </flux:navlist.item>
                    <flux:navlist.item icon="wrench-screwdriver" :href="route('superadmin.maintenance')" :current="request()->routeIs('superadmin.maintenance')" wire:navigate>
                        Backup & Maintenance
                    </flux:navlist.item>
                    <flux:navlist.item icon="cpu-chip" :href="route('superadmin.ai-settings')" :current="request()->routeIs('superadmin.ai-settings')" wire:navigate>
                        AI Settings
                    </flux:navlist.item>
                    <flux:navlist.item icon="clipboard-document-list" :href="route('superadmin.audit-logs')" :current="request()->routeIs('superadmin.audit-logs')" wire:navigate>
                        Audit Logs
                    </flux:navlist.item>
                    <flux:navlist.item icon="megaphone" :href="route('superadmin.announcements')" :current="request()->routeIs('superadmin.announcements')" wire:navigate>
                        Announcements
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
                                    <span class="flex h-full w-full items-center justify-center rounded-lg bg-primary-100 dark:bg-primary-800 text-primary-700 dark:text-primary-300">
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>
                                <div class="grid flex-1 text-left text-sm leading-tight">
                                    <span class="truncate font-semibold text-zinc-900 dark:text-zinc-200">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs text-zinc-500">Super Admin</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>
                    <flux:menu.separator />
                    <flux:menu.item href="/settings/profile" icon="cog" wire:navigate>Settings</flux:menu.item>
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
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}
        @fluxScripts
    </body>
</html>
