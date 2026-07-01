@if(auth()->check() && auth()->user()->role === 'admin')
    <x-layouts.app.admin-sidebar>
        <flux:main>
            {{ $slot }}
        </flux:main>
    </x-layouts.app.admin-sidebar>
@elseif(auth()->check() && auth()->user()->role === 'tailor_staff')
    <x-layouts.app.staff-sidebar>
        <flux:main>
            {{ $slot }}
        </flux:main>
    </x-layouts.app.staff-sidebar>
@elseif(auth()->check() && auth()->user()->role === 'shop_owner')
    <x-layouts.app.shop-owner-sidebar>
        <flux:main>
            {{ $slot }}
        </flux:main>
    </x-layouts.app.shop-owner-sidebar>
@else
    <x-layouts.app.customer-sidebar>
        <flux:main>
            {{ $slot }}
        </flux:main>
    </x-layouts.app.customer-sidebar>
@endif
