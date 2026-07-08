<?php

use App\Models\Order;
use App\Models\Payment;
use App\Models\Appointment;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Carbon\Carbon;

new #[Layout('components.layouts.app')] class extends Component {
    public function with(): array
    {
        $now = Carbon::now();
        $lastMonth = $now->copy()->subMonth();

        // Monthly data for chart (last 6 months)
        $monthlyRevenue = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $monthlyRevenue[] = [
                'month'   => $date->format('M'),
                'revenue' => Payment::where('status', 'paid')
                    ->whereMonth('payment_date', $date->month)
                    ->whereYear('payment_date', $date->year)
                    ->sum('amount'),
                'orders'  => Order::whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->count(),
            ];
        }
        $maxRevenue = max(array_column($monthlyRevenue, 'revenue') ?: [1]);

        return [
            // This month
            'ordersThisMonth'      => Order::whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->count(),
            'revenueThisMonth'     => Payment::where('status', 'paid')->whereMonth('payment_date', $now->month)->whereYear('payment_date', $now->year)->sum('amount'),
            'appointmentsThisMonth'=> Appointment::whereMonth('date', $now->month)->whereYear('date', $now->year)->count(),
            'newCustomersThisMonth'=> User::where('role', 'customer')->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->count(),

            // Last month comparisons
            'ordersLastMonth'   => Order::whereMonth('created_at', $lastMonth->month)->whereYear('created_at', $lastMonth->year)->count(),
            'revenueLastMonth'  => Payment::where('status', 'paid')->whereMonth('payment_date', $lastMonth->month)->whereYear('payment_date', $lastMonth->year)->sum('amount'),

            // All-time
            'totalRevenue'    => Payment::where('status', 'paid')->sum('amount'),
            'totalOrders'     => Order::count(),
            'completionRate'  => Order::count() > 0
                ? round(Order::whereIn('status', ['completed', 'released'])->count() / Order::count() * 100, 1)
                : 0,

            // Chart data
            'monthlyRevenue' => $monthlyRevenue,
            'maxRevenue'     => $maxRevenue ?: 1,
        ];
    }
}; ?>

<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-primary-600 dark:text-primary-400" style="font-family: 'Poppins';">Reports & Analytics</h1>
        <p class="text-zinc-500 mt-1">Business performance overview — {{ now()->format('F Y') }}</p>
    </div>

    <!-- This Month Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @php
            $orderChange  = $ordersLastMonth > 0 ? round(($ordersThisMonth - $ordersLastMonth) / $ordersLastMonth * 100, 1) : 0;
            $revenueChange = $revenueLastMonth > 0 ? round(($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth * 100, 1) : 0;
        @endphp
        @foreach([
            ['Orders This Month', $ordersThisMonth, ($orderChange >= 0 ? '+' : '').$orderChange.'% vs last month', 'from-blue-100 to-blue-200 dark:from-blue-900/30 dark:to-blue-800/30', 'text-blue-600 dark:text-blue-400', 'border-blue-200 dark:border-blue-800'],
            ['Revenue This Month', '₱'.number_format($revenueThisMonth,0), ($revenueChange >= 0 ? '+' : '').$revenueChange.'% vs last month', 'from-emerald-100 to-emerald-200 dark:from-emerald-900/30 dark:to-emerald-800/30', 'text-emerald-600 dark:text-emerald-400', 'border-emerald-200 dark:border-emerald-800'],
            ['Appointments', $appointmentsThisMonth, 'This month', 'from-purple-100 to-purple-200 dark:from-purple-900/30 dark:to-purple-800/30', 'text-purple-600 dark:text-purple-400', 'border-purple-200 dark:border-purple-800'],
            ['New Customers', $newCustomersThisMonth, 'This month', 'from-amber-100 to-amber-200 dark:from-amber-900/30 dark:to-amber-800/30', 'text-amber-600 dark:text-amber-400', 'border-amber-200 dark:border-amber-800'],
        ] as [$label, $value, $sub, $bg, $color, $border])
        <div class="tc-card bg-gradient-to-br {{ $bg }} {{ $border }}">
            <p class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family:'Poppins'">{{ $value }}</p>
            <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mt-1">{{ $label }}</p>
            <p class="text-xs {{ $color }} mt-1">{{ $sub }}</p>
        </div>
        @endforeach
    </div>

    <!-- All-Time Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        @foreach([
            ['Total Revenue', '₱'.number_format($totalRevenue,2), 'All time collected'],
            ['Total Orders', $totalOrders, 'All time placed'],
            ['Completion Rate', $completionRate.'%', 'Orders completed or released'],
        ] as [$label, $value, $sub])
        <div class="tc-card text-center">
            <p class="text-3xl font-bold text-primary-600 dark:text-primary-400" style="font-family:'Poppins'">{{ $value }}</p>
            <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mt-1">{{ $label }}</p>
            <p class="text-xs text-zinc-500 mt-0.5">{{ $sub }}</p>
        </div>
        @endforeach
    </div>

    <!-- Monthly Revenue Chart -->
    <div class="tc-card">
        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white mb-6" style="font-family: 'Poppins';">Monthly Revenue (Last 6 Months)</h2>
        <div class="flex items-end justify-between gap-3 h-48 px-2">
            @foreach($monthlyRevenue as $idx => $mo)
                <div class="flex-1 flex flex-col items-center gap-2 group">
                    <span class="text-xs font-semibold text-zinc-600 dark:text-zinc-400">₱{{ number_format($mo['revenue'] / 1000, 1) }}k</span>
                    <div class="w-full bg-gradient-to-t from-emerald-500 to-emerald-400 rounded-t-xl hover:from-emerald-600 hover:to-emerald-500 transition-colors cursor-pointer relative overflow-hidden"
                         style="height: {{ $maxRevenue > 0 ? max(($mo['revenue'] / $maxRevenue * 100), 2) : 2 }}%; min-height: 4px;">
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent"></div>
                    </div>
                    <span class="text-xs text-zinc-500 font-medium">{{ $mo['month'] }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>
