<flux:sidebar sticky stashable class="bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700">
    <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

    <div class="flex items-center gap-2 px-2 pb-4 pt-2">
        <x-app-logo class="size-8" />
        <span class="font-bold text-lg text-primary-600 dark:text-primary-400" style="font-family: 'Poppins';">Shop Owner</span>
    </div>

    <flux:navlist variant="outline">
        <flux:navlist.item icon="home" :href="route('shopowner.dashboard')" :current="request()->routeIs('shopowner.dashboard')">Dashboard</flux:navlist.item>
        <flux:navlist.item icon="clipboard-document-list" :href="route('shopowner.orders')" :current="request()->routeIs('shopowner.orders')">Orders</flux:navlist.item>
        <flux:navlist.item icon="users" :href="route('shopowner.staff')" :current="request()->routeIs('shopowner.staff')">Staff</flux:navlist.item>
        <flux:navlist.item icon="swatch" :href="route('shopowner.garments')" :current="request()->routeIs('shopowner.garments')">Catalog & Fabrics</flux:navlist.item>
        <flux:navlist.item icon="cog-6-tooth" :href="route('shopowner.settings')" :current="request()->routeIs('shopowner.settings')">Shop Settings</flux:navlist.item>
    </flux:navlist>

    <flux:spacer />

    <flux:dropdown position="top" align="start" class="max-w-full">
        <flux:profile
            :name="auth()->user()->name"
            :initials="auth()->user()->initials()"
            icon-trailing="chevron-up"
        />
        <flux:menu>
            <flux:menu.item icon="arrow-right-start-on-rectangle" wire:click="logout">Logout</flux:menu.item>
        </flux:menu>
    </flux:dropdown>
</flux:sidebar>
