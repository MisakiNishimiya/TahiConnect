<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Models\User;
use App\Models\Shop;

new #[Layout('components.layouts.app')] class extends Component {
    /**
     * Super Admin view — manages subscriptions/licenses FOR shop owners.
     * The Super Admin (TahiConnect platform owner) assigns plans to
     * shop owner instances and monitors their license status.
     * UI-only — backend not yet implemented.
     */
    public bool $showAssignModal = false;
    public string $selectedOwnerId = '';
    public string $selectedPlan    = 'professional';

    public function with(): array
    {
        $shopOwners = User::where('role', 'shop_owner')->get();
        $shop       = Shop::instance();

        // Static placeholder — will come from a subscriptions table once backend is built
        $subscriptions = $shopOwners->map(fn($owner, $idx) => [
            'owner'      => $owner,
            'plan'       => $idx === 0 ? 'professional' : ($idx === 1 ? 'starter' : 'trial'),
            'status'     => $idx === 0 ? 'active' : ($idx === 1 ? 'active' : 'trial'),
            'started_at' => '2025-01-01',
            'expires_at' => $idx === 0 ? '2026-01-01' : ($idx === 1 ? '2025-10-01' : '2025-08-01'),
            'days_left'  => $idx === 0 ? 182 : ($idx === 1 ? 90 : 28),
            'license_key'=> 'TC-' . strtoupper(substr($owner->name, 0, 3)) . '-2025-' . str_pad($owner->id ?? $idx, 4, '0', STR_PAD_LEFT),
        ]);

        return [
            'shopOwners'    => $shopOwners,
            'subscriptions' => $subscriptions,
            'shop'          => $shop,
            'plans'         => [
                ['key' => 'starter',      'name' => 'Starter',      'price' => '₱999/mo',   'features' => ['1 Shop Owner', '3 Staff', 'Core Modules', 'Email Support']],
                ['key' => 'professional', 'name' => 'Professional', 'price' => '₱2,499/mo', 'features' => ['1 Shop Owner', 'Unlimited Staff', 'All Modules + AI Try-On', 'Priority Support']],
                ['key' => 'enterprise',   'name' => 'Enterprise',   'price' => 'Custom',    'features' => ['Multi-instance', 'SLA Guarantee', 'Dedicated Support', 'Custom Integration']],
            ],
            'stats' => [
                'active'  => $subscriptions->where('status', 'active')->count(),
                'trial'   => $subscriptions->where('status', 'trial')->count(),
                'expired' => $subscriptions->where('status', 'expired')->count(),
                'total'   => $subscriptions->count(),
            ],
        ];
    }

    public function openAssignModal(string $ownerId = ''): void
    {
        $this->selectedOwnerId = $ownerId;
        $this->showAssignModal = true;
    }

    public function assign(): void
    {
        // UI-only — backend not yet implemented
        session()->flash('message', 'License assignment queued. Backend implementation pending.');
        $this->showAssignModal = false;
    }
}; ?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-primary-600 dark:text-primary-400" style="font-family: 'Poppins';">Subscription & License Management</h1>
            <p class="text-zinc-500 dark:text-zinc-400 mt-1">Manage subscription plans and licenses assigned to shop owners</p>
        </div>
        <button wire:click="openAssignModal()"
            class="px-5 py-2.5 text-sm font-medium bg-gradient-to-r from-primary-500 to-primary-600 text-white rounded-xl hover:from-primary-600 hover:to-primary-700 transition-all shadow-lg flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Assign License
        </button>
    </div>

    @if(session()->has('message'))
        <x-notification-toast type="success" title="Done!" message="{{ session('message') }}" :dismissible="true" />
    @endif

    <!-- Platform Context Banner -->
    <div class="p-4 bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/20 rounded-xl flex items-start gap-3">
        <svg class="w-5 h-5 text-blue-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <div>
            <p class="text-sm font-semibold text-blue-700 dark:text-blue-300">Platform License Management</p>
            <p class="text-xs text-blue-600 dark:text-blue-400 mt-0.5">As TahiConnect Super Admin, you assign and manage subscription plans for each shop owner instance. Shop owners view their own license status from their dashboard.</p>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach([
            ['Total Instances', $stats['total'],   'Shop owner accounts',   'bg-zinc-50 border-zinc-200',      'text-zinc-700',    'bg-zinc-100',    'text-zinc-600'],
            ['Active',          $stats['active'],  'Licensed & operational','bg-emerald-50 border-emerald-200','text-emerald-700', 'bg-emerald-100', 'text-emerald-600'],
            ['Trial',           $stats['trial'],   'Evaluation period',     'bg-amber-50 border-amber-200',    'text-amber-700',   'bg-amber-100',   'text-amber-600'],
            ['Expired',         $stats['expired'], 'Needs renewal',         'bg-red-50 border-red-200',        'text-red-700',     'bg-red-100',     'text-red-600'],
        ] as [$label, $count, $sub, $cardStyle, $textColor, $iconBg, $iconColor])
        <div class="tc-card border {{ $cardStyle }}">
            <p class="text-2xl font-bold {{ $textColor }} mb-1" style="font-family:'Poppins'">{{ $count }}</p>
            <p class="text-sm font-medium text-zinc-700">{{ $label }}</p>
            <p class="text-xs text-zinc-500 mt-0.5">{{ $sub }}</p>
        </div>
        @endforeach
    </div>

    <!-- Shop Owner Subscriptions Table -->
    <div class="tc-card !p-0 overflow-hidden">
        <div class="px-6 py-4 border-b border-zinc-100 dark:border-zinc-200 flex items-center justify-between bg-zinc-50">
            <h2 class="text-base font-semibold text-zinc-900" style="font-family:'Poppins'">Shop Owner Licenses</h2>
            <span class="text-xs text-zinc-500 bg-white border border-zinc-200 px-2.5 py-1 rounded-full font-medium">{{ $subscriptions->count() }} instances</span>
        </div>

        @if($subscriptions->isEmpty())
            <div class="py-16 text-center">
                <div class="w-14 h-14 mx-auto mb-3 rounded-2xl bg-zinc-100 flex items-center justify-center">
                    <svg class="w-7 h-7 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"/></svg>
                </div>
                <p class="text-zinc-500 text-sm">No shop owner accounts found. Create a shop owner account first.</p>
                <a href="{{ route('superadmin.shop-owners') }}" wire:navigate class="mt-3 inline-block text-sm text-primary-600 hover:text-primary-700 font-medium">
                    Go to Shop Owner Accounts →
                </a>
            </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-100 bg-white">
                        <th class="text-left py-3 px-4 font-semibold text-zinc-500">Shop Owner</th>
                        <th class="text-left py-3 px-4 font-semibold text-zinc-500">Plan</th>
                        <th class="text-left py-3 px-4 font-semibold text-zinc-500 hidden md:table-cell">License Key</th>
                        <th class="text-left py-3 px-4 font-semibold text-zinc-500">Status</th>
                        <th class="text-left py-3 px-4 font-semibold text-zinc-500 hidden lg:table-cell">Expires</th>
                        <th class="text-left py-3 px-4 font-semibold text-zinc-500 hidden lg:table-cell">Days Left</th>
                        <th class="text-right py-3 px-4 font-semibold text-zinc-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-50">
                    @foreach($subscriptions as $sub)
                    @php
                        $planColors  = ['starter' => 'bg-blue-100 text-blue-700', 'professional' => 'bg-primary-100 text-primary-700', 'trial' => 'bg-amber-100 text-amber-700', 'enterprise' => 'bg-purple-100 text-purple-700'];
                        $statusColors = ['active' => 'bg-emerald-100 text-emerald-700', 'trial' => 'bg-amber-100 text-amber-700', 'expired' => 'bg-red-100 text-red-700', 'suspended' => 'bg-zinc-100 text-zinc-600'];
                        $daysLeft    = $sub['days_left'];
                        $daysColor   = $daysLeft <= 30 ? 'text-red-600 font-semibold' : ($daysLeft <= 90 ? 'text-amber-600' : 'text-zinc-600');
                    @endphp
                    <tr class="hover:bg-zinc-50 transition-colors group">
                        <td class="py-4 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-amber-100 flex items-center justify-center text-sm font-bold text-amber-700">
                                    {{ strtoupper(substr($sub['owner']->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-zinc-900 text-sm">{{ $sub['owner']->name }}</p>
                                    <p class="text-xs text-zinc-500">{{ $sub['owner']->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-4">
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $planColors[$sub['plan']] ?? 'bg-zinc-100 text-zinc-600' }}">
                                {{ ucfirst($sub['plan']) }}
                            </span>
                        </td>
                        <td class="py-4 px-4 hidden md:table-cell">
                            <span class="font-mono text-xs text-zinc-500 bg-zinc-50 border border-zinc-200 px-2 py-1 rounded-lg">{{ $sub['license_key'] }}</span>
                        </td>
                        <td class="py-4 px-4">
                            <span class="px-2.5 py-1 text-xs font-medium rounded-full {{ $statusColors[$sub['status']] ?? 'bg-zinc-100 text-zinc-600' }}">
                                {{ ucfirst($sub['status']) }}
                            </span>
                        </td>
                        <td class="py-4 px-4 text-zinc-500 text-xs hidden lg:table-cell">
                            {{ \Carbon\Carbon::parse($sub['expires_at'])->format('M d, Y') }}
                        </td>
                        <td class="py-4 px-4 hidden lg:table-cell">
                            <span class="text-sm {{ $daysColor }}">
                                {{ $daysLeft }} days
                                @if($daysLeft <= 30)
                                    <span class="ml-1 text-xs font-bold text-red-500 animate-pulse">⚠</span>
                                @endif
                            </span>
                        </td>
                        <td class="py-4 px-4 text-right">
                            <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button wire:click="openAssignModal('{{ $sub['owner']->id }}')"
                                    class="px-3 py-1.5 text-xs font-medium text-primary-600 hover:bg-primary-50 rounded-lg transition-colors">
                                    Change Plan
                                </button>
                                @if($sub['days_left'] <= 90)
                                <button class="px-3 py-1.5 text-xs font-medium text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors">
                                    Renew
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    <!-- Available Plans (reference) -->
    <div class="tc-card">
        <h2 class="text-lg font-semibold text-zinc-900 mb-2" style="font-family:'Poppins'">Available Plans</h2>
        <p class="text-sm text-zinc-500 mb-5">Plans you can assign to shop owners. Contact your billing system to configure pricing.</p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($plans as $plan)
            <div class="p-5 rounded-2xl border border-zinc-200 bg-zinc-50 hover:border-zinc-300 hover:bg-white hover:shadow-sm transition-all duration-200">
                <div class="flex items-start justify-between mb-3">
                    <h3 class="text-base font-bold text-zinc-900">{{ $plan['name'] }}</h3>
                    <span class="text-sm font-bold text-primary-600">{{ $plan['price'] }}</span>
                </div>
                <ul class="space-y-1.5">
                    @foreach($plan['features'] as $feature)
                    <li class="flex items-center gap-2 text-xs text-zinc-600">
                        <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        {{ $feature }}
                    </li>
                    @endforeach
                </ul>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Assign / Change Plan Modal -->
    @if($showAssignModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white border border-zinc-200 rounded-2xl shadow-2xl w-full max-w-md" @click.stop>
            <div class="p-6">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h3 class="text-lg font-semibold text-zinc-900">Assign License Plan</h3>
                        <p class="text-xs text-zinc-500 mt-0.5">Select a plan to assign to this shop owner instance</p>
                    </div>
                    <button wire:click="$set('showAssignModal', false)" class="p-2 text-zinc-400 hover:bg-zinc-100 rounded-full transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                @if($selectedOwnerId)
                    @php $owner = \App\Models\User::find($selectedOwnerId); @endphp
                    @if($owner)
                    <div class="flex items-center gap-3 p-3 bg-zinc-50 rounded-xl border border-zinc-100 mb-5">
                        <div class="w-9 h-9 rounded-xl bg-amber-100 flex items-center justify-center font-bold text-amber-700 text-sm">{{ strtoupper(substr($owner->name,0,1)) }}</div>
                        <div>
                            <p class="font-semibold text-zinc-900 text-sm">{{ $owner->name }}</p>
                            <p class="text-xs text-zinc-500">{{ $owner->email }}</p>
                        </div>
                    </div>
                    @endif
                @else
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-zinc-700 mb-1.5">Shop Owner</label>
                        <select wire:model="selectedOwnerId" class="w-full px-4 py-2.5 border border-zinc-200 rounded-xl bg-white text-zinc-900 text-sm focus:ring-2 focus:ring-primary-500">
                            <option value="">Select shop owner...</option>
                            @foreach($shopOwners as $owner)
                                <option value="{{ $owner->id }}">{{ $owner->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="mb-5">
                    <label class="block text-sm font-medium text-zinc-700 mb-1.5">Plan</label>
                    <select wire:model="selectedPlan" class="w-full px-4 py-2.5 border border-zinc-200 rounded-xl bg-white text-zinc-900 text-sm focus:ring-2 focus:ring-primary-500">
                        @foreach($plans as $plan)
                            <option value="{{ $plan['key'] }}">{{ $plan['name'] }} — {{ $plan['price'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-medium text-zinc-700 mb-1.5">License Duration</label>
                    <select class="w-full px-4 py-2.5 border border-zinc-200 rounded-xl bg-white text-zinc-900 text-sm focus:ring-2 focus:ring-primary-500">
                        <option>1 Month</option>
                        <option selected>3 Months</option>
                        <option>6 Months</option>
                        <option>12 Months</option>
                    </select>
                </div>

                <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-700 mb-5">
                    ⚠ UI-only — assigning a plan will be processed once the subscription backend is implemented.
                </div>

                <div class="flex gap-3">
                    <button wire:click="$set('showAssignModal', false)"
                        class="flex-1 px-4 py-2.5 text-sm font-medium text-zinc-600 bg-zinc-100 hover:bg-zinc-200 rounded-xl transition-colors">
                        Cancel
                    </button>
                    <button wire:click="assign"
                        class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-primary-500 hover:bg-primary-600 rounded-xl transition-colors">
                        Assign License
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
