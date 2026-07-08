<x-layouts.app>
    {{-- Redirect to role-based dashboard --}}
    @php
        $user = auth()->user();
        if ($user) {
            $route = match($user->role) {
                'super_admin'  => 'superadmin.dashboard',
                'shop_owner'   => 'shopowner.dashboard',
                'tailor_staff' => 'staff.dashboard',
                default        => 'customer.dashboard',
            };
            redirect()->route($route)->send();
        }
    @endphp
    <div class="flex items-center justify-center min-h-[60vh]">
        <div class="text-center">
            <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-primary-100 to-primary-200 dark:from-primary-900/30 dark:to-primary-800/30 flex items-center justify-center">
                <svg class="w-8 h-8 text-primary-600 dark:text-primary-400 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            <p class="text-zinc-500 dark:text-zinc-400 font-medium">Redirecting to your dashboard...</p>
        </div>
    </div>
</x-layouts.app>
