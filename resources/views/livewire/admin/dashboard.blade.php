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
            $monthlyOrders[] = [
                'month' => $date->format('M'),
                'count' => Order::whereMonth('created_at', $date->month)->whereYear('created_at', $date->year)->count(),
            ];
        }
        $maxOrders = max(array_column($monthlyOrders, 'count') ?: [1]);

        $statusDist = [];
        foreach (['pending', 'in_production', 'fitting_scheduled', 'completed', 'released'] as $s) {
            $statusDist[$s] = Order::where('status', $s)->count();
        }
        $totalStatusCount = max(array_sum($statusDist), 1);

        return [
            'totalCustomers' => User::where('role', 'customer')->count(),
            'totalOrders' => Order::count(),
            'totalRevenue' => Payment::where('status', 'paid')->sum('amount'),
            'activeAppointments' => Appointment::whereIn('status', ['pending', 'confirmed'])->where('date', '>=', now()->toDateString())->count(),
            'monthlyOrders' => $monthlyOrders,
            'maxOrders' => $maxOrders,
            'statusDist' => $statusDist,
            'totalStatusCount' => $totalStatusCount,
            'recentOrders' => Order::with(['user', 'garmentType'])->latest()->take(10)->get(),
        ];
    }
}; ?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">Admin Dashboard</h1>
            <p class="text-zinc-500 mt-1">{{ now()->format('l, F d, Y') }}</p>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="tc-stat-card animate-fade-in-up">
            <div class="tc-stat-icon bg-blue-100 dark:bg-blue-900/30">
                <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">{{ $totalCustomers }}</p>
                <p class="text-sm text-zinc-500">Total Customers</p>
            </div>
        </div>
        <div class="tc-stat-card animate-fade-in-up animation-delay-100">
            <div class="tc-stat-icon bg-emerald-100 dark:bg-emerald-900/30">
                <svg class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">{{ $totalOrders }}</p>
                <p class="text-sm text-zinc-500">Total Orders</p>
            </div>
        </div>
        <div class="tc-stat-card animate-fade-in-up animation-delay-200">
            <div class="tc-stat-icon bg-amber-100 dark:bg-amber-900/30">
                <svg class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">₱{{ number_format($totalRevenue, 0) }}</p>
                <p class="text-sm text-zinc-500">Total Revenue</p>
            </div>
        </div>
        <div class="tc-stat-card animate-fade-in-up animation-delay-300">
            <div class="tc-stat-icon bg-purple-100 dark:bg-purple-900/30">
                <svg class="w-6 h-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">{{ $activeAppointments }}</p>
                <p class="text-sm text-zinc-500">Active Appointments</p>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Monthly Orders Bar Chart -->
        <div class="tc-card">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white mb-6" style="font-family: 'Poppins';">Monthly Orders</h2>
            <div class="flex items-end justify-between gap-3 h-48">
                @foreach($monthlyOrders as $idx => $mo)
                    <div class="flex-1 flex flex-col items-center gap-2">
                        <span class="text-xs font-semibold text-zinc-600 dark:text-zinc-400">{{ $mo['count'] }}</span>
                        <div class="w-full bg-primary-500 rounded-t-lg animate-grow-up"
                             style="height: {{ $maxOrders > 0 ? ($mo['count'] / $maxOrders * 100) : 0 }}%; animation-delay: {{ $idx * 0.1 }}s; min-height: 4px;">
                        </div>
                        <span class="text-xs text-zinc-500">{{ $mo['month'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Order Status Distribution -->
        <div class="tc-card">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white mb-6" style="font-family: 'Poppins';">Order Status Distribution</h2>
            <div class="space-y-4">
                @php $colors = ['pending' => 'bg-amber-500', 'in_production' => 'bg-purple-500', 'fitting_scheduled' => 'bg-indigo-500', 'completed' => 'bg-emerald-500', 'released' => 'bg-teal-500']; @endphp
                @foreach($statusDist as $status => $count)
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ ucwords(str_replace('_', ' ', $status)) }}</span>
                            <span class="text-sm font-semibold text-zinc-900 dark:text-white">{{ $count }}</span>
                        </div>
                        <div class="w-full h-2.5 bg-zinc-100 dark:bg-zinc-700 rounded-full overflow-hidden">
                            <div class="{{ $colors[$status] ?? 'bg-zinc-400' }} h-full rounded-full transition-all duration-700" style="width: {{ ($count / $totalStatusCount) * 100 }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Recent Orders Table -->
    <div class="tc-card">
        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4" style="font-family: 'Poppins';">Recent Orders</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="border-b border-zinc-200 dark:border-zinc-700">
                    <th class="text-left py-3 px-2 font-medium text-zinc-500">Tracking #</th>
                    <th class="text-left py-3 px-2 font-medium text-zinc-500">Customer</th>
                    <th class="text-left py-3 px-2 font-medium text-zinc-500 hidden md:table-cell">Garment</th>
                    <th class="text-left py-3 px-2 font-medium text-zinc-500">Status</th>
                    <th class="text-right py-3 px-2 font-medium text-zinc-500">Amount</th>
                </tr></thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach($recentOrders as $order)
                        <tr class="hover:bg-cream-50 dark:hover:bg-zinc-700/30">
                            <td class="py-3 px-2 font-mono text-xs text-primary-600 dark:text-primary-400">{{ $order->tracking_number }}</td>
                            <td class="py-3 px-2">{{ $order->user?->name ?? 'N/A' }}</td>
                            <td class="py-3 px-2 hidden md:table-cell text-zinc-500">{{ $order->garmentType?->name ?? '-' }}</td>
                            <td class="py-3 px-2"><span class="tc-badge tc-badge-{{ $order->status }}">{{ ucwords(str_replace('_', ' ', $order->status)) }}</span></td>
                            <td class="py-3 px-2 text-right font-semibold">₱{{ number_format($order->total_amount, 0) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
