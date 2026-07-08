<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Models\Shop;

new #[Layout('components.layouts.app')] class extends Component {
    /**
     * Shop Owner view — shows their own license/subscription status.
     * Managed and assigned by Super Admin.
     * UI-only — backend not yet implemented.
     */
    public function with(): array
    {
        $shop = Shop::instance();
        return [
            'shop'    => $shop,
            'license' => [
                'plan'        => 'Professional',
                'status'      => 'active',
                'started_at'  => '2025-01-01',
                'expires_at'  => '2026-01-01',
                'days_left'   => 182,
                'license_key' => 'TC-PRO-2025-' . strtoupper(substr(md5($shop->id ?? 1), 0, 8)),
            ],
            'features' => [
                'Unlimited Tailor Staff',
                'Full Order Management',
                'Appointment Scheduling',
                'AI Virtual Try-On',
                'Measurement Validation',
                'Customer Management',
                'Business Analytics',
                'Priority Support',
            ],
        ];
    }
}; ?>

<div class="space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-3xl font-bold text-primary-600 dark:text-primary-400" style="font-family: 'Poppins';">My Subscription</h1>
        <p class="text-zinc-500 dark:text-zinc-400 mt-1">Your TahiConnect license and plan details</p>
    </div>

    <!-- License Card -->
    <div class="tc-card bg-gradient-to-br from-primary-600 to-primary-800 border-0 overflow-hidden relative">
        <div class="absolute inset-0 opacity-5">
            <svg class="w-full h-full" viewBox="0 0 200 100" preserveAspectRatio="none">
                <circle cx="160" cy="20" r="90" fill="white"/>
            </svg>
        </div>
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="flex items-center gap-5">
                <div class="w-16 h-16 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center border border-white/30">
                    <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <h2 class="text-2xl font-bold text-white" style="font-family:'Poppins'">{{ $license['plan'] }} Plan</h2>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-400/20 text-emerald-100 border border-emerald-400/30">
                            {{ ucfirst($license['status']) }}
                        </span>
                    </div>
                    <p class="text-primary-200 text-sm">{{ $shop->name }}</p>
                    <p class="text-primary-200 text-xs mt-0.5 font-mono">{{ $license['license_key'] }}</p>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-3 shrink-0">
                <div class="text-center p-3 bg-white/10 rounded-xl border border-white/20">
                    <p class="text-xl font-bold text-white">{{ $license['days_left'] }}</p>
                    <p class="text-xs text-primary-200 mt-1">Days Left</p>
                </div>
                <div class="text-center p-3 bg-white/10 rounded-xl border border-white/20">
                    <p class="text-xs font-semibold text-white">{{ \Carbon\Carbon::parse($license['started_at'])->format('M d, Y') }}</p>
                    <p class="text-xs text-primary-200 mt-1">Started</p>
                </div>
                <div class="text-center p-3 bg-white/10 rounded-xl border border-white/20">
                    <p class="text-xs font-semibold {{ $license['days_left'] < 30 ? 'text-red-300' : 'text-white' }}">{{ \Carbon\Carbon::parse($license['expires_at'])->format('M d, Y') }}</p>
                    <p class="text-xs text-primary-200 mt-1">Expires</p>
                </div>
            </div>
        </div>

        @php
            $totalDays = \Carbon\Carbon::parse($license['started_at'])->diffInDays(\Carbon\Carbon::parse($license['expires_at']));
            $usedDays  = $totalDays - $license['days_left'];
            $pct       = $totalDays > 0 ? round(($usedDays / $totalDays) * 100) : 0;
            $barColor  = $pct > 85 ? 'bg-red-400' : ($pct > 65 ? 'bg-amber-400' : 'bg-emerald-400');
        @endphp
        <div class="mt-5 relative z-10">
            <div class="flex items-center justify-between text-xs text-primary-200 mb-2">
                <span>License period used</span>
                <span>{{ $pct }}%</span>
            </div>
            <div class="w-full h-2 bg-white/20 rounded-full overflow-hidden">
                <div class="{{ $barColor }} h-full rounded-full" style="width: {{ $pct }}%"></div>
            </div>
        </div>
    </div>

    <!-- Expiry Warning -->
    @if($license['days_left'] <= 30)
    <div class="p-4 bg-red-50 border-l-4 border-l-red-500 border border-red-100 rounded-xl flex items-start gap-3">
        <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"/></svg>
        <div>
            <p class="font-semibold text-red-700 text-sm">License Expiring in {{ $license['days_left'] }} days</p>
            <p class="text-xs text-red-600 mt-0.5">Contact TahiConnect support to renew your subscription before it expires.</p>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Included Features -->
        <div class="tc-card">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4" style="font-family:'Poppins'">Included in Your Plan</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                @foreach($features as $feature)
                <div class="flex items-center gap-2.5 p-2.5 rounded-xl bg-emerald-50 border border-emerald-100">
                    <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    <span class="text-xs font-medium text-emerald-800">{{ $feature }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Support & Renewal -->
        <div class="tc-card">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4" style="font-family:'Poppins'">Support & Renewal</h2>
            <div class="space-y-4">
                <div class="p-4 bg-zinc-50 rounded-xl border border-zinc-100">
                    <p class="text-sm font-semibold text-zinc-700 mb-1">Need to renew or upgrade?</p>
                    <p class="text-xs text-zinc-500">Contact TahiConnect support to renew your license or upgrade to a higher plan. Your Super Admin manages your subscription.</p>
                </div>
                <div class="p-4 bg-blue-50 rounded-xl border border-blue-100">
                    <p class="text-sm font-semibold text-blue-700 mb-1">Priority Support</p>
                    <p class="text-xs text-blue-600">You have priority support access as part of the Professional plan. Response time: within 24 hours.</p>
                </div>
                <button class="w-full py-2.5 text-sm font-medium text-primary-600 border border-primary-200 hover:bg-primary-50 rounded-xl transition-colors">
                    Contact TahiConnect Support
                </button>
            </div>
        </div>
    </div>
</div>
