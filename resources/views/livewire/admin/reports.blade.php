<?php

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Carbon\Carbon;

new #[Layout('components.layouts.app')] class extends Component {
    public string $startDate = '';
    public string $endDate = '';
    public string $activeReport = 'sales';

    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function with(): array
    {
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();
        return [
            'ordersThisMonth' => Order::where('created_at', '>=', $thisMonth)->count(),
            'ordersLastMonth' => Order::whereBetween('created_at', [$lastMonth, $lastMonthEnd])->count(),
            'revenueThisMonth' => Payment::where('status', 'paid')->where('payment_date', '>=', $thisMonth)->sum('amount'),
            'revenueLastMonth' => Payment::where('status', 'paid')->whereBetween('payment_date', [$lastMonth, $lastMonthEnd])->sum('amount'),
            'newCustomers' => User::where('role', 'customer')->where('created_at', '>=', $thisMonth)->count(),
            'completionRate' => Order::count() > 0 ? round(Order::whereIn('status', ['completed', 'released'])->count() / Order::count() * 100) : 0,
            'avgOrderValue' => Order::avg('total_amount') ?? 0,
            'pendingPayments' => Payment::where('status', 'pending')->sum('amount'),
        ];
    }
}; ?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-primary-600 dark:text-primary-400" style="font-family: 'Poppins';">Reports & Analytics</h1>
            <p class="text-zinc-500 mt-1">View business performance metrics and generate reports.</p>
        </div>
        <div class="flex gap-3">
            <button class="px-4 py-2.5 text-sm font-medium text-zinc-600 dark:text-zinc-300 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors click-feedback flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                Export PDF
            </button>
            <button class="px-4 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-primary-500 to-primary-600 rounded-xl hover:from-primary-600 hover:to-primary-700 transition-all duration-300 shadow-lg hover:shadow-xl hover:shadow-primary-500/25 click-feedback flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                Export Excel
            </button>
        </div>
    </div>

    <!-- Report Type Tabs -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach([
            ['sales', 'Sales Report', 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'from-emerald-100 to-emerald-200 dark:from-emerald-900/30 dark:to-emerald-800/30', 'text-emerald-600 dark:text-emerald-400', 'border-emerald-200 dark:border-emerald-800'],
            ['orders', 'Order Report', 'M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z', 'from-blue-100 to-blue-200 dark:from-blue-900/30 dark:to-blue-800/30', 'text-blue-600 dark:text-blue-400', 'border-blue-200 dark:border-blue-800'],
            ['customers', 'Customer Report', 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0z', 'from-purple-100 to-purple-200 dark:from-purple-900/30 dark:to-purple-800/30', 'text-purple-600 dark:text-purple-400', 'border-purple-200 dark:border-purple-800'],
            ['staff', 'Staff Performance', 'M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z', 'from-amber-100 to-amber-200 dark:from-amber-900/30 dark:to-amber-800/30', 'text-amber-600 dark:text-amber-400', 'border-amber-200 dark:border-amber-800'],
        ] as [$key, $label, $icon, $bg, $color, $border])
        <div wire:click="$set('activeReport', '{{ $key }}')"
             class="tc-card bg-gradient-to-br {{ $bg }} {{ $border }} hover-lift cursor-pointer text-center transition-all duration-300 {{ $activeReport === $key ? 'ring-2 ring-primary-500 shadow-lg' : '' }}">
            <div class="w-12 h-12 mx-auto mb-3 rounded-xl {{ $bg }} border {{ $border }} flex items-center justify-center">
                <svg class="w-6 h-6 {{ $color }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
            </div>
            <p class="text-sm font-semibold text-zinc-900 dark:text-white">{{ $label }}</p>
            @if($activeReport === $key)
                <div class="w-2 h-2 bg-primary-500 rounded-full mx-auto mt-2"></div>
            @endif
        </div>
        @endforeach
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        @php
            $orderDiff = $ordersThisMonth - $ordersLastMonth;
            $revDiff = $revenueThisMonth - $revenueLastMonth;
        @endphp
        <div class="tc-card hover-lift">
            <p class="text-sm text-zinc-500 mb-2">Orders This Month</p>
            <p class="text-3xl font-bold text-zinc-900 dark:text-white mb-2" style="font-family:'Poppins'">{{ $ordersThisMonth }}</p>
            <div class="flex items-center gap-1.5 text-sm {{ $orderDiff >= 0 ? 'text-emerald-600' : 'text-red-500' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $orderDiff >= 0 ? 'M13 7l5 5m0 0l-5 5m5-5H6' : 'M11 17l-5-5m0 0l5-5m-5 5h12' }}"/></svg>
                <span>{{ abs($orderDiff) }} vs last month</span>
            </div>
        </div>
        <div class="tc-card hover-lift">
            <p class="text-sm text-zinc-500 mb-2">Revenue This Month</p>
            <p class="text-3xl font-bold text-zinc-900 dark:text-white mb-2" style="font-family:'Poppins'">₱{{ number_format($revenueThisMonth, 0) }}</p>
            <div class="flex items-center gap-1.5 text-sm {{ $revDiff >= 0 ? 'text-emerald-600' : 'text-red-500' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $revDiff >= 0 ? 'M13 7l5 5m0 0l-5 5m5-5H6' : 'M11 17l-5-5m0 0l5-5m-5 5h12' }}"/></svg>
                <span>vs ₱{{ number_format($revenueLastMonth, 0) }}</span>
            </div>
        </div>
        <div class="tc-card hover-lift">
            <p class="text-sm text-zinc-500 mb-2">Completion Rate</p>
            <p class="text-3xl font-bold text-zinc-900 dark:text-white mb-3" style="font-family:'Poppins'">{{ $completionRate }}%</p>
            <div class="w-full h-2.5 bg-zinc-100 dark:bg-zinc-700 rounded-full overflow-hidden">
                <div class="{{ $completionRate >= 75 ? 'bg-emerald-500' : ($completionRate >= 50 ? 'bg-amber-500' : 'bg-red-500') }} h-full rounded-full transition-all duration-700" style="width: {{ $completionRate }}%"></div>
            </div>
        </div>
        <div class="tc-card hover-lift">
            <p class="text-sm text-zinc-500 mb-2">Avg Order Value</p>
            <p class="text-3xl font-bold text-zinc-900 dark:text-white mb-2" style="font-family:'Poppins'">₱{{ number_format($avgOrderValue, 0) }}</p>
            <p class="text-sm text-blue-600 dark:text-blue-400">{{ $newCustomers }} new customers this month</p>
        </div>
    </div>

    <!-- Date Range Generator -->
    <div class="tc-card">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
            </div>
            <h2 class="text-xl font-bold text-zinc-900 dark:text-white">Generate Custom Report</h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div>
                <label class="block text-sm font-semibold text-zinc-700 dark:text-zinc-300 mb-2">Start Date</label>
                <input type="date" wire:model="startDate" class="w-full px-4 py-3 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white focus:ring-2 focus:ring-primary-500 transition-colors" />
            </div>
            <div>
                <label class="block text-sm font-semibold text-zinc-700 dark:text-zinc-300 mb-2">End Date</label>
                <input type="date" wire:model="endDate" class="w-full px-4 py-3 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white focus:ring-2 focus:ring-primary-500 transition-colors" />
            </div>
            <div class="flex items-end">
                <button class="w-full py-3 px-6 bg-gradient-to-r from-primary-500 to-primary-600 text-white font-semibold rounded-xl hover:from-primary-600 hover:to-primary-700 transition-all duration-300 shadow-lg hover:shadow-xl click-feedback">
                    Generate Report
                </button>
            </div>
        </div>
        <div class="flex flex-wrap gap-2 pt-4 border-t border-zinc-100 dark:border-zinc-700">
            <button class="px-4 py-2 text-sm font-medium text-zinc-600 dark:text-zinc-300 bg-zinc-100 dark:bg-zinc-700 rounded-xl hover:bg-zinc-200 dark:hover:bg-zinc-600 transition-colors click-feedback flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                Export PDF
            </button>
            <button class="px-4 py-2 text-sm font-medium text-zinc-600 dark:text-zinc-300 bg-zinc-100 dark:bg-zinc-700 rounded-xl hover:bg-zinc-200 dark:hover:bg-zinc-600 transition-colors click-feedback flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                Export Excel
            </button>
            <button class="px-4 py-2 text-sm font-medium text-zinc-600 dark:text-zinc-300 bg-zinc-100 dark:bg-zinc-700 rounded-xl hover:bg-zinc-200 dark:hover:bg-zinc-600 transition-colors click-feedback flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z"/></svg>
                Print Report
            </button>
        </div>
    </div>
</div>
