<?php

use App\Models\User;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Appointment;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Carbon\Carbon;

new #[Layout('components.layouts.app')] class extends Component {
    public function with(): array
    {
        $monthlyOrders = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthlyOrders[] = ['month' => $date->format('M'), 'count' => Order::whereMonth('created_at', $date->month)->whereYear('created_at', $date->year)->count()];
        }
        $maxOrders = max(array_column($monthlyOrders, 'count') ?: [1]);
        $statusDist = [];
        foreach (['pending','in_production','fitting_scheduled','completed','released'] as $s) {
            $statusDist[$s] = Order::where('status', $s)->count();
        }
        return [
            'totalCustomers' => User::where('role', 'customer')->count(),
            'totalOrders' => Order::count(),
            'totalRevenue' => Payment::where('status', 'paid')->sum('amount'),
            'activeAppointments' => Appointment::whereIn('status', ['pending', 'confirmed'])->where('date', '>=', now()->toDateString())->count(),
            'monthlyOrders' => $monthlyOrders,
            'maxOrders' => $maxOrders,
            'statusDist' => $statusDist,
            'totalStatusCount' => max(array_sum($statusDist), 1),
            'recentOrders' => Order::with(['user', 'garmentType'])->latest()->take(10)->get(),
            'newCustomersThisWeek' => User::where('role', 'customer')->where('created_at', '>=', now()->startOfWeek())->count(),
        ];
    }
}; ?>

<div class="space-y-6">
    <!-- Header -->
    <div class="tc-card bg-gradient-to-br from-zinc-800 to-zinc-900 dark:from-zinc-900 dark:to-black text-white border-0 overflow-hidden relative">
        <div class="absolute inset-0 opacity-10" style="background: radial-gradient(circle at 80% 50%, #2F5D50 0%, transparent 60%)"></div>
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <p class="text-zinc-400 text-sm mb-1">Admin Control Panel</p>
                <h1 class="text-3xl font-bold" style="font-family: 'Poppins';">TahiConnect Overview</h1>
                <p class="text-zinc-400 mt-1">{{ now()->format('l, F d, Y') }}</p>
            </div>
            <div class="flex items-center gap-2 bg-emerald-500/20 border border-emerald-500/30 rounded-2xl px-4 py-2">
                <div class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></div>
                <span class="text-emerald-300 text-sm font-medium">Platform Online</span>
            </div>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach([
            ['Total Customers', $totalCustomers, '+' . $newCustomersThisWeek . ' this week', 'from-blue-100 to-blue-200 dark:from-blue-900/30 dark:to-blue-800/30', 'text-blue-600 dark:text-blue-400', 'border-blue-200 dark:border-blue-800', 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0z'],
            ['Total Orders', $totalOrders, 'All time', 'from-emerald-100 to-emerald-200 dark:from-emerald-900/30 dark:to-emerald-800/30', 'text-emerald-600 dark:text-emerald-400', 'border-emerald-200 dark:border-emerald-800', 'M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z'],
            ['Total Revenue', '₱' . number_format($totalRevenue, 0), 'Paid payments', 'from-amber-100 to-amber-200 dark:from-amber-900/30 dark:to-amber-800/30', 'text-amber-600 dark:text-amber-400', 'border-amber-200 dark:border-amber-800', 'M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75'],
            ['Active Appointments', $activeAppointments, 'Pending & confirmed', 'from-purple-100 to-purple-200 dark:from-purple-900/30 dark:to-purple-800/30', 'text-purple-600 dark:text-purple-400', 'border-purple-200 dark:border-purple-800', 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75'],
        ] as [$label, $value, $sub, $bg, $color, $border, $icon])
        <div class="tc-card hover-lift group bg-gradient-to-br {{ $bg }} {{ $border }} animate-fade-in-up">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 rounded-2xl {{ $bg }} border {{ $border }} flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 {{ $color }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-zinc-900 dark:text-white mb-1" style="font-family:'Poppins'">{{ $value }}</p>
            <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $label }}</p>
            <p class="text-xs text-zinc-500 mt-1">{{ $sub }}</p>
        </div>
        @endforeach
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Monthly Orders Bar Chart -->
        <div class="tc-card">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white mb-6" style="font-family: 'Poppins';">Monthly Orders (Last 6 Months)</h2>
            <div class="flex items-end justify-between gap-3 h-48 px-2">
                @foreach($monthlyOrders as $idx => $mo)
                    <div class="flex-1 flex flex-col items-center gap-2 group">
                        <span class="text-xs font-semibold text-zinc-600 dark:text-zinc-400 group-hover:text-primary-600 transition-colors">{{ $mo['count'] }}</span>
                        <div class="w-full bg-gradient-to-t from-primary-500 to-primary-400 rounded-t-xl animate-grow-up hover:from-primary-600 hover:to-primary-500 transition-colors cursor-pointer relative overflow-hidden"
                             style="height: {{ $maxOrders > 0 ? max(($mo['count'] / $maxOrders * 100), 2) : 2 }}%; animation-delay: {{ $idx * 0.1 }}s; min-height: 4px;">
                            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent animate-shimmer"></div>
                        </div>
                        <span class="text-xs text-zinc-500 font-medium">{{ $mo['month'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Order Status Distribution -->
        <div class="tc-card">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white mb-6" style="font-family: 'Poppins';">Order Status Distribution</h2>
            <div class="space-y-4">
                @php $colors = ['pending' => ['bg-amber-500', 'text-amber-600', 'bg-amber-100 dark:bg-amber-900/30'], 'in_production' => ['bg-purple-500', 'text-purple-600', 'bg-purple-100 dark:bg-purple-900/30'], 'fitting_scheduled' => ['bg-indigo-500', 'text-indigo-600', 'bg-indigo-100 dark:bg-indigo-900/30'], 'completed' => ['bg-emerald-500', 'text-emerald-600', 'bg-emerald-100 dark:bg-emerald-900/30'], 'released' => ['bg-teal-500', 'text-teal-600', 'bg-teal-100 dark:bg-teal-900/30']]; @endphp
                @foreach($statusDist as $status => $count)
                    @php [$barColor, $textColor, $badgeBg] = $colors[$status] ?? ['bg-zinc-400','text-zinc-600','bg-zinc-100']; @endphp
                    <div class="group">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 rounded-full {{ $barColor }}"></div>
                                <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ ucwords(str_replace('_', ' ', $status)) }}</span>
                            </div>
                            <span class="text-sm font-bold text-zinc-900 dark:text-white px-2.5 py-0.5 {{ $badgeBg }} rounded-full {{ $textColor }}">{{ $count }}</span>
                        </div>
                        <div class="w-full h-2.5 bg-zinc-100 dark:bg-zinc-700 rounded-full overflow-hidden">
                            <div class="{{ $barColor }} h-full rounded-full transition-all duration-700" style="width: {{ ($count / $totalStatusCount) * 100 }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="tc-card !p-0 overflow-hidden">
        <div class="p-6 border-b border-zinc-100 dark:border-zinc-700 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">Recent Orders</h2>
            <a href="{{ route('admin.orders') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 font-medium flex items-center gap-1">
                View All <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-sm">
                <thead><tr class="border-b border-zinc-100 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50">
                    <th class="text-left py-3 px-4 font-semibold text-zinc-500">Tracking #</th>
                    <th class="text-left py-3 px-4 font-semibold text-zinc-500">Customer</th>
                    <th class="text-left py-3 px-4 font-semibold text-zinc-500 hidden md:table-cell">Garment</th>
                    <th class="text-left py-3 px-4 font-semibold text-zinc-500">Status</th>
                    <th class="text-right py-3 px-4 font-semibold text-zinc-500">Amount</th>
                </tr></thead>
                <tbody class="divide-y divide-zinc-50 dark:divide-zinc-800">
                    @foreach($recentOrders as $order)
                        <tr class="hover:bg-cream-50 dark:hover:bg-zinc-700/30 transition-colors">
                            <td class="py-3 px-4 font-mono text-xs font-semibold text-primary-600 dark:text-primary-400">{{ $order->tracking_number }}</td>
                            <td class="py-3 px-4 font-medium text-zinc-900 dark:text-white">{{ $order->user?->name ?? 'N/A' }}</td>
                            <td class="py-3 px-4 text-zinc-500 hidden md:table-cell">{{ $order->garmentType?->name ?? '—' }}</td>
                            <td class="py-3 px-4"><span class="tc-badge tc-badge-{{ $order->status }}">{{ ucwords(str_replace('_', ' ', $order->status)) }}</span></td>
                            <td class="py-3 px-4 text-right font-bold text-zinc-900 dark:text-white">₱{{ number_format($order->total_amount, 0) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
