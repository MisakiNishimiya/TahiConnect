<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Models\Order;
use App\Models\User;
use App\Models\Shop;
use Carbon\Carbon;

new #[Layout('components.layouts.app')] class extends Component {
    public function with(): array
    {
        $user = auth()->user();
        if (!$user->shop_id) abort(403, 'User must be assigned to a shop.');
        $shop = Shop::with('reviews')->findOrFail($user->shop_id);
        return [
            'shop' => $shop,
            'totalOrders' => Order::forShop($user->shop_id)->count(),
            'pendingOrders' => Order::forShop($user->shop_id)->where('status', 'pending')->count(),
            'monthlyRevenue' => Order::forShop($user->shop_id)->whereMonth('created_at', Carbon::now()->month)->sum('total_amount'),
            'staffCount' => User::where('shop_id', $user->shop_id)->where('role', 'tailor_staff')->count(),
            'recentOrders' => Order::with(['user', 'garmentType', 'preMadeProduct'])->forShop($user->shop_id)->latest()->take(5)->get(),
            'statusCounts' => [
                'pending' => Order::forShop($user->shop_id)->where('status', 'pending')->count(),
                'in_production' => Order::forShop($user->shop_id)->where('status', 'in_production')->count(),
                'completed' => Order::forShop($user->shop_id)->whereIn('status', ['completed','released'])->count(),
            ],
        ];
    }
}; ?>

<div class="space-y-6">
    <!-- Hero Header -->
    <div class="tc-card bg-gradient-to-br from-primary-600 to-primary-800 text-white border-0 overflow-hidden relative">
        <div class="absolute inset-0 opacity-5">
            <svg class="w-full h-full" viewBox="0 0 200 100" preserveAspectRatio="none">
                <circle cx="150" cy="20" r="80" fill="white"/>
            </svg>
        </div>
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="flex items-center gap-5">
                <div class="w-20 h-20 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center border border-white/30">
                    <svg class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.001 3.001 0 01-.621-1.875L21.75 3A3.001 3.001 0 0018 0H6a3.001 3.001 0 00-3.75 3l.621 17.25z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-bold" style="font-family: 'Poppins';">{{ $shop->name }}</h1>
                    <p class="text-primary-200 mt-1">Shop Overview · {{ now()->format('F Y') }}</p>
                    <div class="flex items-center gap-2 mt-2">
                        <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        <span class="text-sm font-medium">{{ $shop->rating ?? '0.0' }} rating</span>
                        <span class="text-primary-300 text-sm">({{ $shop->total_reviews ?? 0 }} reviews)</span>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-3">
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl px-4 py-3 text-center border border-white/20">
                    <p class="text-2xl font-bold">{{ $statusCounts['pending'] }}</p>
                    <p class="text-xs text-primary-200">Pending</p>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl px-4 py-3 text-center border border-white/20">
                    <p class="text-2xl font-bold">{{ $statusCounts['in_production'] }}</p>
                    <p class="text-xs text-primary-200">Production</p>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl px-4 py-3 text-center border border-white/20">
                    <p class="text-2xl font-bold">{{ $statusCounts['completed'] }}</p>
                    <p class="text-xs text-primary-200">Completed</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach([
            ['Total Orders', $totalOrders, 'bg-gradient-to-br from-blue-100 to-blue-200 dark:from-blue-900/30 dark:to-blue-800/30', 'text-blue-600 dark:text-blue-400', 'border-blue-200 dark:border-blue-800', 'M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z'],
            ['Monthly Revenue', '₱'.number_format($monthlyRevenue, 2), 'bg-gradient-to-br from-emerald-100 to-emerald-200 dark:from-emerald-900/30 dark:to-emerald-800/30', 'text-emerald-600 dark:text-emerald-400', 'border-emerald-200 dark:border-emerald-800', 'M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z'],
            ['Staff Members', $staffCount, 'bg-gradient-to-br from-purple-100 to-purple-200 dark:from-purple-900/30 dark:to-purple-800/30', 'text-purple-600 dark:text-purple-400', 'border-purple-200 dark:border-purple-800', 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z'],
            ['Shop Rating', $shop->rating ?? '0.0', 'bg-gradient-to-br from-yellow-100 to-yellow-200 dark:from-yellow-900/30 dark:to-yellow-800/30', 'text-yellow-600 dark:text-yellow-400', 'border-yellow-200 dark:border-yellow-800', 'M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z'],
        ] as [$label, $value, $bg, $color, $border, $icon])
        <div class="tc-card hover-lift group {{ $bg }} {{ $border }}">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl {{ $bg }} flex items-center justify-center group-hover:scale-110 transition-transform duration-300 border {{ $border }}">
                    <svg class="w-7 h-7 {{ $color }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">{{ $value }}</p>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $label }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Recent Orders -->
    <div class="tc-card">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">Recent Orders</h2>
            <a href="{{ route('shopowner.orders') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 font-medium flex items-center gap-1">
                View All <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        <div class="space-y-3">
            @forelse($recentOrders as $order)
                <div class="flex items-center justify-between p-4 rounded-2xl bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-100 dark:border-zinc-700 hover:bg-white dark:hover:bg-zinc-800 hover:shadow-sm transition-all duration-300 group">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center group-hover:scale-105 transition-transform duration-300">
                            <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z"/></svg>
                        </div>
                        <div>
                            <p class="font-mono text-sm font-semibold text-primary-600 dark:text-primary-400">{{ $order->tracking_number }}</p>
                            <p class="text-xs text-zinc-500">{{ $order->user?->name }} · {{ $order->garmentType?->name ?? $order->preMadeProduct?->name }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <p class="text-sm font-bold text-zinc-900 dark:text-white hidden sm:block">₱{{ number_format($order->total_amount, 0) }}</p>
                        <span class="tc-badge tc-badge-{{ $order->status }}">{{ ucwords(str_replace('_', ' ', $order->status)) }}</span>
                    </div>
                </div>
            @empty
                <x-enhanced-empty-state icon="orders" title="No orders yet" description="Orders from customers will appear here." :actions="[]" />
            @endforelse
        </div>
    </div>
</div>
