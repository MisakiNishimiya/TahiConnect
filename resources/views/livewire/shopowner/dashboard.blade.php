<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Appointment;
use App\Models\User;
use App\Models\Shop;
use Carbon\Carbon;

new #[Layout('components.layouts.app')] class extends Component {
    public function with(): array
    {
        $shop = Shop::instance();
        $now  = Carbon::now();
        $prev = $now->copy()->subMonth();

        // Monthly revenue chart (last 6 months)
        $revenueChart = [];
        for ($i = 5; $i >= 0; $i--) {
            $d = $now->copy()->subMonths($i);
            $revenueChart[] = [
                'month'   => $d->format('M'),
                'revenue' => Payment::where('status', 'paid')
                    ->whereMonth('payment_date', $d->month)->whereYear('payment_date', $d->year)->sum('amount'),
                'orders'  => Order::whereMonth('created_at', $d->month)->whereYear('created_at', $d->year)->count(),
            ];
        }
        $maxRevenue = max(array_column($revenueChart, 'revenue') ?: [1]);

        // Order status distribution
        $statusDist = [];
        foreach (['pending','in_production','fitting_scheduled','completed','released'] as $s) {
            $statusDist[$s] = Order::where('status', $s)->count();
        }

        // Comparisons — this month vs last month
        $ordersThisMonth  = Order::whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->count();
        $ordersLastMonth  = Order::whereMonth('created_at', $prev->month)->whereYear('created_at', $prev->year)->count();
        $revenueThisMonth = Payment::where('status', 'paid')->whereMonth('payment_date', $now->month)->whereYear('payment_date', $now->year)->sum('amount');
        $revenueLastMonth = Payment::where('status', 'paid')->whereMonth('payment_date', $prev->month)->whereYear('payment_date', $prev->year)->sum('amount');

        $orderChange  = $ordersLastMonth  > 0 ? round(($ordersThisMonth - $ordersLastMonth) / $ordersLastMonth * 100, 1) : 0;
        $revenueChange = $revenueLastMonth > 0 ? round(($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth * 100, 1) : 0;

        return [
            'shop'             => $shop,
            'now'              => $now,
            // KPI cards
            'ordersThisMonth'  => $ordersThisMonth,
            'orderChange'      => $orderChange,
            'revenueThisMonth' => $revenueThisMonth,
            'revenueChange'    => $revenueChange,
            'pendingOrders'    => Order::where('status', 'pending')->count(),
            'activeAppts'      => Appointment::whereIn('status', ['pending','confirmed'])->where('date', '>=', now()->toDateString())->count(),
            'totalCustomers'   => User::where('role', 'customer')->count(),
            'newCustomersMonth'=> User::where('role', 'customer')->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->count(),
            'staffCount'       => User::where('role', 'tailor_staff')->count(),
            'totalRevenue'     => Payment::where('status', 'paid')->sum('amount'),
            'completionRate'   => Order::count() > 0 ? round(Order::whereIn('status', ['completed','released'])->count() / Order::count() * 100, 1) : 0,
            // Charts
            'revenueChart'     => $revenueChart,
            'maxRevenue'       => $maxRevenue ?: 1,
            'statusDist'       => $statusDist,
            'totalStatusCount' => max(array_sum($statusDist), 1),
            // Today
            'todayAppointments'=> Appointment::whereDate('date', today())->get(),
            'recentOrders'     => Order::with(['user','garmentType','preMadeProduct'])->latest()->take(5)->get(),
        ];
    }
}; ?>

<div class="space-y-6">
    <!-- Header Banner -->
    <div class="tc-card bg-gradient-to-br from-primary-600 to-primary-800 text-white border-0 overflow-hidden relative">
        <div class="absolute inset-0 opacity-5"><svg class="w-full h-full" viewBox="0 0 200 100" preserveAspectRatio="none"><circle cx="160" cy="20" r="90" fill="white"/></svg></div>
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <p class="text-primary-200 text-sm font-medium uppercase tracking-wide mb-1">{{ $shop->name }}</p>
                <h1 class="text-3xl font-bold text-white" style="font-family: 'Poppins';">Business Dashboard</h1>
                <p class="text-primary-200 mt-1">{{ $now->format('l, F d, Y') }}</p>
            </div>
            <div class="grid grid-cols-3 gap-3 shrink-0">
                <div class="bg-white/10 rounded-2xl px-4 py-3 text-center border border-white/20">
                    <p class="text-2xl font-bold">{{ $ordersThisMonth }}</p>
                    <p class="text-xs text-primary-200">This Month</p>
                </div>
                <div class="bg-white/10 rounded-2xl px-4 py-3 text-center border border-white/20">
                    <p class="text-2xl font-bold">{{ $pendingOrders }}</p>
                    <p class="text-xs text-primary-200">Pending</p>
                </div>
                <div class="bg-white/10 rounded-2xl px-4 py-3 text-center border border-white/20">
                    <p class="text-xl font-bold leading-tight">PHP {{ number_format($revenueThisMonth, 0) }}</p>
                    <p class="text-xs text-primary-200">Revenue</p>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach([
            ['Orders This Month',   $ordersThisMonth,                        ($orderChange >= 0 ? '+' : '').$orderChange.'% vs last month',   'bg-blue-50 border-blue-200',    'text-blue-700',    'bg-blue-100',    'text-blue-600',    'M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z'],
            ['Revenue This Month',  '₱'.number_format($revenueThisMonth,0), ($revenueChange >= 0 ? '+' : '').$revenueChange.'% vs last month', 'bg-emerald-50 border-emerald-200','text-emerald-700','bg-emerald-100','text-emerald-600','M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z'],
            ['Total Customers',     $totalCustomers,                         '+'.$newCustomersMonth.' new this month',                        'bg-purple-50 border-purple-200', 'text-purple-700',  'bg-purple-100',  'text-purple-600',  'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z'],
            ['Order Completion',    $completionRate.'%',                     'All-time rate',                                                  'bg-amber-50 border-amber-200',  'text-amber-700',   'bg-amber-100',   'text-amber-600',   'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        ] as [$label, $value, $sub, $cardStyle, $textColor, $iconBg, $iconColor, $icon])
        <div class="tc-card border {{ $cardStyle }} hover-lift group">
            <div class="flex items-start justify-between mb-3">
                <div class="w-11 h-11 rounded-xl {{ $iconBg }} flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 {{ $iconColor }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                </div>
                <span class="text-xs font-medium {{ str_starts_with($sub, '+') ? 'text-emerald-600' : (str_starts_with($sub, '-') ? 'text-red-500' : 'text-zinc-400') }}">{{ $sub }}</span>
            </div>
            <p class="text-2xl font-bold {{ $textColor }} mb-0.5" style="font-family:'Poppins'">{{ $value }}</p>
            <p class="text-sm text-zinc-600 font-medium">{{ $label }}</p>
        </div>
        @endforeach
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Revenue Chart -->
        <div class="tc-card">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-base font-semibold text-zinc-900" style="font-family:'Poppins'">Revenue (Last 6 Months)</h2>
                <a href="{{ route('shopowner.reports') }}" wire:navigate class="text-xs text-primary-600 hover:text-primary-700 font-medium flex items-center gap-1">
                    Full Report <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
            <div class="flex items-end justify-between gap-2 h-40 px-1">
                @foreach($revenueChart as $idx => $mo)
                <div class="flex-1 flex flex-col items-center gap-1.5 group">
                    <span class="text-[10px] font-semibold text-zinc-500 group-hover:text-primary-600 transition-colors">₱{{ number_format($mo['revenue']/1000, 1) }}k</span>
                    <div class="w-full bg-gradient-to-t from-primary-500 to-primary-400 rounded-t-lg hover:from-primary-600 hover:to-primary-500 transition-colors cursor-pointer"
                         style="height: {{ $maxRevenue > 0 ? max(($mo['revenue'] / $maxRevenue * 100), 3) : 3 }}%; min-height: 4px;">
                    </div>
                    <span class="text-[10px] text-zinc-400 font-medium">{{ $mo['month'] }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Order Status Distribution -->
        <div class="tc-card">
            <h2 class="text-base font-semibold text-zinc-900 mb-5" style="font-family:'Poppins'">Order Status Breakdown</h2>
            <div class="space-y-3">
                @php $colors = ['pending' => ['bg-amber-500','text-amber-600','bg-amber-100'], 'in_production' => ['bg-purple-500','text-purple-600','bg-purple-100'], 'fitting_scheduled' => ['bg-indigo-500','text-indigo-600','bg-indigo-100'], 'completed' => ['bg-emerald-500','text-emerald-600','bg-emerald-100'], 'released' => ['bg-teal-500','text-teal-600','bg-teal-100']]; @endphp
                @foreach($statusDist as $status => $count)
                @php [$bar, $text, $badge] = $colors[$status] ?? ['bg-zinc-400','text-zinc-600','bg-zinc-100']; @endphp
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <div class="flex items-center gap-2">
                            <div class="w-2.5 h-2.5 rounded-full {{ $bar }}"></div>
                            <span class="text-xs text-zinc-600">{{ ucwords(str_replace('_',' ',$status)) }}</span>
                        </div>
                        <span class="text-xs font-bold {{ $text }} {{ $badge }} px-2 py-0.5 rounded-full">{{ $count }}</span>
                    </div>
                    <div class="w-full h-2 bg-zinc-100 rounded-full overflow-hidden">
                        <div class="{{ $bar }} h-full rounded-full" style="width: {{ ($count/$totalStatusCount)*100 }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Today's Schedule + Recent Orders -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Today's Appointments -->
        <div class="tc-card">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-semibold text-zinc-900" style="font-family:'Poppins'">Today's Appointments</h2>
                <a href="{{ route('shopowner.appointments') }}" wire:navigate class="text-xs text-primary-600 hover:text-primary-700 font-medium">View All →</a>
            </div>
            @forelse($todayAppointments as $appt)
                <div class="flex items-center gap-3 p-3 rounded-xl bg-zinc-50 border border-zinc-100 mb-2 hover:bg-white hover:shadow-sm transition-all">
                    <div class="w-10 h-10 rounded-xl bg-primary-100 flex items-center justify-center shrink-0">
                        <p class="text-xs font-bold text-primary-700">{{ \Carbon\Carbon::parse($appt->time)->format('g:i') }}</p>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-zinc-900 text-sm truncate">{{ $appt->user?->name }}</p>
                        <p class="text-xs text-zinc-500">{{ ucwords(str_replace('_',' ',$appt->type)) }} · {{ \Carbon\Carbon::parse($appt->time)->format('g:i A') }}</p>
                    </div>
                    <span class="tc-badge tc-badge-{{ $appt->status }} shrink-0">{{ ucfirst($appt->status) }}</span>
                </div>
            @empty
                <div class="text-center py-8">
                    <div class="w-12 h-12 mx-auto mb-2 rounded-2xl bg-zinc-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5"/></svg>
                    </div>
                    <p class="text-sm text-zinc-400">No appointments today</p>
                </div>
            @endforelse
        </div>

        <!-- Recent Orders -->
        <div class="tc-card">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-semibold text-zinc-900" style="font-family:'Poppins'">Recent Orders</h2>
                <a href="{{ route('shopowner.orders') }}" wire:navigate class="text-xs text-primary-600 hover:text-primary-700 font-medium">View All →</a>
            </div>
            @forelse($recentOrders as $order)
                <div class="flex items-center justify-between p-3 rounded-xl bg-zinc-50 border border-zinc-100 mb-2 hover:bg-white hover:shadow-sm transition-all group">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-primary-100 flex items-center justify-center group-hover:scale-105 transition-transform">
                            <svg class="w-4 h-4 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5"/></svg>
                        </div>
                        <div>
                            <p class="font-mono text-xs font-semibold text-primary-600">{{ $order->tracking_number }}</p>
                            <p class="text-xs text-zinc-500">{{ $order->user?->name }} · {{ $order->garmentType?->name ?? $order->preMadeProduct?->name }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <span class="text-xs font-bold text-zinc-900 hidden sm:block">₱{{ number_format($order->total_amount, 0) }}</span>
                        <span class="tc-badge tc-badge-{{ $order->status }}">{{ ucwords(str_replace('_',' ',$order->status)) }}</span>
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-zinc-400 text-sm">No orders yet.</div>
            @endforelse
        </div>
    </div>

    <!-- Quick Links -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        @foreach([
            ['New Order',     'shopowner.orders',    'M12 4.5v15m7.5-7.5h-15',                                                                                                                                   'bg-primary-50 border-primary-200 text-primary-700'],
            ['Staff',         'shopowner.staff',     'M15 19.128a9.38 9.38 0 002.625.372M12 6.375a3.375 3.375 0 11-6.75 0',                                                                                     'bg-purple-50 border-purple-200 text-purple-700'],
            ['Payments',      'shopowner.payments',  'M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75',                                         'bg-emerald-50 border-emerald-200 text-emerald-700'],
            ['Full Reports',  'shopowner.reports',   'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75z',         'bg-amber-50 border-amber-200 text-amber-700'],
        ] as [$label, $route, $icon, $style])
        <a href="{{ route($route) }}" wire:navigate
            class="flex items-center gap-3 p-4 rounded-2xl border {{ $style }} hover:shadow-sm hover:-translate-y-0.5 transition-all duration-200 group">
            <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
            </div>
            <span class="text-sm font-semibold">{{ $label }}</span>
        </a>
        @endforeach
    </div>

    <!-- Subscription Summary -->
    @php
        $subDaysLeft  = 182;
        $subPlan      = 'Professional';
        $subStatus    = 'active';
        $subExpires   = '2026-01-01';
        $subPct       = 50; // placeholder — backend will calculate
        $subBarColor  = $subDaysLeft < 30 ? 'bg-red-500' : ($subDaysLeft < 90 ? 'bg-amber-400' : 'bg-emerald-500');
        $subTextColor = $subDaysLeft < 30 ? 'text-red-600' : ($subDaysLeft < 90 ? 'text-amber-600' : 'text-emerald-600');
        $subBgColor   = $subDaysLeft < 30 ? 'bg-red-50 border-red-200' : ($subDaysLeft < 90 ? 'bg-amber-50 border-amber-200' : 'bg-emerald-50 border-emerald-200');
    @endphp
    <div class="tc-card border border-zinc-100">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <!-- Left: icon + info -->
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-primary-100 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-0.5">
                        <h3 class="text-base font-semibold text-zinc-900" style="font-family:'Poppins'">My Subscription</h3>
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $subBgColor }} {{ $subTextColor }} border">
                            {{ ucfirst($subStatus) }}
                        </span>
                    </div>
                    <p class="text-sm text-zinc-500">
                        <span class="font-semibold text-zinc-700">{{ $subPlan }} Plan</span>
                        &nbsp;·&nbsp; Expires {{ \Carbon\Carbon::parse($subExpires)->format('M d, Y') }}
                    </p>
                    <!-- Progress bar -->
                    <div class="mt-2 w-48">
                        <div class="flex items-center justify-between text-[10px] text-zinc-400 mb-1">
                            <span>License period</span>
                            <span class="{{ $subTextColor }} font-semibold">{{ $subDaysLeft }} days left</span>
                        </div>
                        <div class="w-full h-1.5 bg-zinc-200 rounded-full overflow-hidden">
                            <div class="{{ $subBarColor }} h-full rounded-full" style="width: {{ $subPct }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: expiry warning or CTA -->
            <div class="flex items-center gap-3 shrink-0">
                @if($subDaysLeft <= 30)
                <div class="flex items-center gap-2 px-3 py-2 bg-red-50 border border-red-200 rounded-xl">
                    <svg class="w-4 h-4 text-red-500 animate-pulse shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"/></svg>
                    <p class="text-xs font-semibold text-red-600">Expiring soon — contact support</p>
                </div>
                @endif
                <a href="{{ route('shopowner.subscription') }}" wire:navigate
                    class="px-4 py-2 text-sm font-medium text-primary-600 bg-primary-50 hover:bg-primary-100 border border-primary-200 rounded-xl transition-colors whitespace-nowrap">
                    View Details
                </a>
            </div>
        </div>
    </div>
</div>
