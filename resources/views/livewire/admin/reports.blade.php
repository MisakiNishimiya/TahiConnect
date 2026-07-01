<?php

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Carbon\Carbon;

new #[Layout('components.layouts.app')] class extends Component {
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
        ];
    }
}; ?>

<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">Reports & Analytics</h1>
        <p class="text-zinc-500 mt-1">View business performance metrics and generate reports.</p>
    </div>

    <!-- Report Type Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach([
            ['Sales Report', 'bg-emerald-100', 'text-emerald-600', 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['Order Report', 'bg-blue-100', 'text-blue-600', 'M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z'],
            ['Customer Report', 'bg-purple-100', 'text-purple-600', 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z'],
            ['Staff Performance', 'bg-amber-100', 'text-amber-600', 'M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z'],
        ] as $report)
            <div class="tc-card text-center cursor-pointer hover:ring-2 hover:ring-primary-300 transition-all">
                <div class="w-12 h-12 mx-auto mb-3 rounded-xl {{ $report[1] }} flex items-center justify-center">
                    <svg class="w-6 h-6 {{ $report[2] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $report[3] }}"/></svg>
                </div>
                <p class="text-sm font-semibold text-zinc-900 dark:text-white">{{ $report[0] }}</p>
            </div>
        @endforeach
    </div>

    <!-- Comparison Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="tc-card">
            <p class="text-sm text-zinc-500 mb-1">Orders This Month</p>
            <p class="text-3xl font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">{{ $ordersThisMonth }}</p>
            <p class="text-xs mt-2 {{ $ordersThisMonth >= $ordersLastMonth ? 'text-emerald-600' : 'text-red-600' }}">
                vs {{ $ordersLastMonth }} last month
            </p>
        </div>
        <div class="tc-card">
            <p class="text-sm text-zinc-500 mb-1">Revenue This Month</p>
            <p class="text-3xl font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">₱{{ number_format($revenueThisMonth, 0) }}</p>
            <p class="text-xs mt-2 text-zinc-400">vs ₱{{ number_format($revenueLastMonth, 0) }} last month</p>
        </div>
        <div class="tc-card">
            <p class="text-sm text-zinc-500 mb-1">Completion Rate</p>
            <p class="text-3xl font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">{{ $completionRate }}%</p>
            <div class="w-full h-2 bg-zinc-100 dark:bg-zinc-700 rounded-full mt-2">
                <div class="h-full bg-emerald-500 rounded-full" style="width: {{ $completionRate }}%"></div>
            </div>
        </div>
        <div class="tc-card">
            <p class="text-sm text-zinc-500 mb-1">Avg Order Value</p>
            <p class="text-3xl font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">₱{{ number_format($avgOrderValue, 0) }}</p>
            <p class="text-xs mt-2 text-zinc-400">{{ $newCustomers }} new customers this month</p>
        </div>
    </div>

    <!-- Export Section -->
    <div class="tc-card">
        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4" style="font-family: 'Poppins';">Generate Report</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
            <flux:input label="Start Date" type="date" />
            <flux:input label="End Date" type="date" />
            <div class="flex items-end">
                <flux:button variant="primary" class="w-full !bg-primary-500 hover:!bg-primary-600">Generate</flux:button>
            </div>
        </div>
        <div class="flex gap-2">
            <flux:button variant="ghost" size="sm">
                <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                Export PDF
            </flux:button>
            <flux:button variant="ghost" size="sm">
                <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                Export Excel
            </flux:button>
        </div>
    </div>
</div>
