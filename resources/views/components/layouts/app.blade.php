@php
    $userRole = auth()->check() ? auth()->user()->role : null;
    $sidebarPath = match($userRole) {
        'super_admin' => 'components.layouts.app.superadmin-sidebar',
        'tailor_staff' => 'components.layouts.app.staff-sidebar',
        'shop_owner' => 'components.layouts.app.shop-owner-sidebar',
        default => 'components.layouts.app.customer-sidebar',
    };
@endphp

@includeIf($sidebarPath, ['slot' => $slot])
