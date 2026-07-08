<?php

use App\Models\User;
use App\Models\Shop;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Carbon\Carbon;

new #[Layout('components.layouts.app')] class extends Component {
    public function with(): array
    {
        return [
            'shop'           => Shop::instance(),
            'shopOwnerCount' => User::where('role', 'shop_owner')->count(),
            'staffCount'     => User::where('role', 'tailor_staff')->count(),
            'customerCount'  => User::where('role', 'customer')->count(),
            'totalUsers'     => User::count(),
            'recentShopOwners' => User::where('role', 'shop_owner')->latest()->take(5)->get(),
        ];
    }
}; ?>

<div class="space-y-6">
    <!-- Platform Header -->
    <div class="tc-card bg-gradient-to-br from-primary-600 to-primary-800 text-white border-0 overflow-hidden relative">
        <div class="absolute inset-0 opacity-5">
            <svg class="w-full h-full" viewBox="0 0 200 100" preserveAspectRatio="none">
                <circle cx="160" cy="20" r="90" fill="white"/>
            </svg>
        </div>
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <p class="text-primary-200 text-sm mb-1 font-medium uppercase tracking-wide">TahiConnect Platform</p>
                <h1 class="text-3xl font-bold text-white" style="font-family: 'Poppins';">Super Admin Dashboard</h1>
                <p class="text-primary-200 mt-1">{{ now()->format('l, F d, Y') }}</p>
            </div>
            <div class="flex items-center gap-2 bg-white/20 border border-white/30 rounded-2xl px-4 py-2 backdrop-blur-sm">
                <div class="w-2 h-2 bg-emerald-300 rounded-full animate-pulse"></div>
                <span class="text-white text-sm font-medium">System Online</span>
            </div>
        </div>
    </div>

    <!-- Platform Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach([
            ['Active Shop Instance', $shop->name,    'Single deployment', 'bg-primary-50 border-primary-200',  'text-primary-700',  'bg-primary-100',   'text-primary-600',  'M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18'],
            ['Shop Owners',          $shopOwnerCount, 'Business accounts', 'bg-amber-50 border-amber-200',     'text-amber-700',    'bg-amber-100',     'text-amber-600',    'M2.25 21h19.5m-18-18l-.376 9.923A2.25 2.25 0 004.623 15h14.754a2.25 2.25 0 002.249-2.077L21.75 3H2.25z'],
            ['Tailor Staff',          $staffCount,    'Production team',   'bg-emerald-50 border-emerald-200', 'text-emerald-700',  'bg-emerald-100',   'text-emerald-600',  'M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63'],
            ['Customers',             $customerCount, 'Registered clients','bg-blue-50 border-blue-200',       'text-blue-700',     'bg-blue-100',      'text-blue-600',     'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0z'],
        ] as [$label, $value, $sub, $cardStyle, $valueColor, $iconBg, $iconColor, $icon])
        <div class="tc-card hover-lift group border {{ $cardStyle }}">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 rounded-2xl {{ $iconBg }} flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 {{ $iconColor }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold {{ $valueColor }} mb-1 truncate" style="font-family:'Poppins'">{{ $value }}</p>
            <p class="text-sm font-medium text-zinc-700">{{ $label }}</p>
            <p class="text-xs text-zinc-500 mt-1">{{ $sub }}</p>
        </div>
        @endforeach
    </div>

    <!-- Quick Actions -->
    <div class="tc-card">
        <h2 class="text-lg font-semibold text-zinc-900 mb-5" style="font-family: 'Poppins';">Platform Management</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach([
                ['Shop Owner Accounts',  'Create or deactivate shop owner access',    'superadmin.shop-owners',  'M2.25 21h19.5m-18-18l-.376 9.923A2.25 2.25 0 004.623 15h14.754',                                                                                                   'bg-amber-100',   'text-amber-600'],
                ['System Configuration', 'Manage platform settings and integrations', 'superadmin.system',       'M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827', 'bg-blue-100',    'text-blue-600'],
                ['Audit Logs',           'View all system activity and access logs',  'superadmin.audit-logs',   'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z', 'bg-purple-100',  'text-purple-600'],
                ['AI Settings',          'Configure virtual try-on AI integration',   'superadmin.ai-settings',  'M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z',                                                                                                    'bg-emerald-100', 'text-emerald-600'],
            ] as [$title, $desc, $route, $icon, $iconBg, $iconColor])
            <a href="{{ route($route) }}" wire:navigate
                class="p-4 rounded-2xl border border-zinc-100 hover:border-zinc-200 hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 group block bg-white">
                <div class="w-10 h-10 rounded-xl {{ $iconBg }} flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 {{ $iconColor }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-zinc-900 text-sm mb-1">{{ $title }}</h3>
                <p class="text-xs text-zinc-500">{{ $desc }}</p>
            </a>
            @endforeach
        </div>
    </div>

    <!-- Shop Owner Accounts -->
    <div class="tc-card">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-lg font-semibold text-zinc-900" style="font-family: 'Poppins';">Shop Owner Accounts</h2>
            <a href="{{ route('superadmin.shop-owners') }}" wire:navigate class="text-sm text-primary-600 hover:text-primary-700 font-medium flex items-center gap-1">
                Manage All <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        <div class="space-y-3">
            @forelse($recentShopOwners as $owner)
                <div class="flex items-center justify-between p-4 rounded-2xl bg-zinc-50 border border-zinc-100 hover:bg-white hover:shadow-sm transition-all duration-200">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center font-bold text-amber-700 text-sm">
                            {{ strtoupper(substr($owner->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-semibold text-zinc-900 text-sm">{{ $owner->name }}</p>
                            <p class="text-xs text-zinc-500">{{ $owner->email }}</p>
                        </div>
                    </div>
                    <span class="text-xs px-2.5 py-1 bg-amber-100 text-amber-700 rounded-full font-medium">Shop Owner</span>
                </div>
            @empty
                <div class="text-center py-8">
                    <div class="w-12 h-12 mx-auto mb-3 rounded-2xl bg-zinc-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19.128a9.38 9.38 0 002.625.372M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0z"/></svg>
                    </div>
                    <p class="text-sm text-zinc-400">No shop owner accounts yet.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
