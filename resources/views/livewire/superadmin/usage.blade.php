<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Models\User;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Appointment;
use Carbon\Carbon;

new #[Layout('components.layouts.app')] class extends Component {
    public function with(): array
    {
        $now = Carbon::now();

        // Monthly activity for last 6 months
        $monthly = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $monthly[] = [
                'month'       => $date->format('M'),
                'orders'      => Order::whereMonth('created_at', $date->month)->whereYear('created_at', $date->year)->count(),
                'customers'   => User::where('role', 'customer')->whereMonth('created_at', $date->month)->whereYear('created_at', $date->year)->count(),
                'revenue'     => Payment::where('status', 'paid')->whereMonth('payment_date', $date->month)->whereYear('payment_date', $date->year)->sum('amount'),
            ];
        }
        $maxOrders = max(array_column($monthly, 'orders') ?: [1]);

        // Last login timestamps — read from sessions/activity (static placeholders)
        $recentUsers = User::latest('updated_at')->take(10)->get();

        return [
            // Totals
            'totalOrders'    => Order::count(),
            'totalRevenue'   => Payment::where('status', 'paid')->sum('amount'),
            'totalCustomers' => User::where('role', 'customer')->count(),
            'totalStaff'     => User::where('role', 'tailor_staff')->count(),

            // This month
            'ordersThisMonth'   => Order::whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->count(),
            'revenueThisMonth'  => Payment::where('status', 'paid')->whereMonth('payment_date', $now->month)->whereYear('payment_date', $now->year)->sum('amount'),
            'newUsersThisMonth' => User::whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->count(),

            'monthly'     => $monthly,
            'maxOrders'   => $maxOrders ?: 1,
            'recentUsers' => $recentUsers,
        ];
    }
}; ?>

<div class="space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-3xl font-bold text-primary-600 dark:text-primary-400" style="font-family: 'Poppins';">Platform Usage Monitoring</h1>
        <p class="text-zinc-500 dark:text-zinc-400 mt-1">Read-only infrastructure overview — for billing and support purposes</p>
    </div>

    <!-- Note Banner -->
    <div class="p-4 bg-blue-500/10 border border-blue-500/20 rounded-xl flex items-start gap-3">
        <svg class="w-5 h-5 text-blue-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p class="text-sm text-blue-300">This is a read-only infrastructure view. Business operations are managed by the Shop Owner. This data is provided for billing justification and support purposes only.</p>
    </div>

    <!-- All-Time Platform Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach([
            ['Total Orders Processed',   number_format($totalOrders),              'All time',        'text-emerald-600 dark:text-emerald-400', 'bg-emerald-100 dark:bg-emerald-500/20 border-emerald-200 dark:border-emerald-500/30'],
            ['Total Revenue Processed',  '₱'.number_format($totalRevenue, 0),      'All paid payments','text-blue-600 dark:text-blue-400',    'bg-blue-100 dark:bg-blue-500/20 border-blue-200 dark:border-blue-500/30'],
            ['Registered Customers',     number_format($totalCustomers),            'Platform users',   'text-purple-600 dark:text-purple-400', 'bg-purple-100 dark:bg-purple-500/20 border-purple-200 dark:border-purple-500/30'],
            ['Tailor Staff',             number_format($totalStaff),                'Production team',  'text-amber-600 dark:text-amber-400',   'bg-amber-100 dark:bg-amber-500/20 border-amber-200 dark:border-amber-500/30'],
        ] as [$label, $value, $sub, $textColor, $badgeStyle])
        <div class="tc-card">
            <div class="flex items-start justify-between mb-3">
                <span class="text-xs font-medium text-zinc-400 uppercase tracking-wider">{{ $sub }}</span>
                <div class="px-2 py-0.5 text-[10px] font-bold border rounded-full {{ $badgeStyle }} {{ $textColor }}">READ-ONLY</div>
            </div>
            <p class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family:'Poppins'">{{ $value }}</p>
            <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">{{ $label }}</p>
        </div>
        @endforeach
    </div>

    <!-- This Month -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        @foreach([
            ['Orders This Month',     $ordersThisMonth,                          'Current month activity'],
            ['Revenue This Month',    '₱'.number_format($revenueThisMonth, 0),   'Collected this month'],
            ['New Registrations',     $newUsersThisMonth,                        'Users joined this month'],
        ] as [$label, $value, $sub])
        <div class="tc-card text-center">
            <p class="text-3xl font-bold text-primary-600 dark:text-primary-400 mb-1" style="font-family:'Poppins'">{{ $value }}</p>
            <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $label }}</p>
            <p class="text-xs text-zinc-500 mt-0.5">{{ $sub }}</p>
        </div>
        @endforeach
    </div>

    <!-- Monthly Orders Chart -->
    <div class="tc-card">
        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white mb-6" style="font-family:'Poppins'">Monthly Order Volume (Last 6 Months)</h2>
        <div class="flex items-end justify-between gap-3 h-48 px-2">
            @foreach($monthly as $idx => $mo)
                <div class="flex-1 flex flex-col items-center gap-2 group">
                    <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">{{ $mo['orders'] }}</span>
                    <div class="w-full bg-gradient-to-t from-primary-600 to-primary-400 rounded-t-xl hover:from-primary-500 hover:to-primary-300 transition-colors cursor-pointer relative overflow-hidden"
                         style="height: {{ $maxOrders > 0 ? max(($mo['orders'] / $maxOrders * 100), 2) : 2 }}%; min-height: 4px;">
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent"></div>
                    </div>
                    <span class="text-xs text-zinc-400 dark:text-zinc-500 font-medium">{{ $mo['month'] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Recent User Activity -->
    <div class="tc-card">
        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white mb-5" style="font-family:'Poppins'">Recent User Activity</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-100 dark:border-zinc-800">
                        <th class="text-left py-3 px-4 font-semibold text-zinc-500">User</th>
                        <th class="text-left py-3 px-4 font-semibold text-zinc-500">Role</th>
                        <th class="text-left py-3 px-4 font-semibold text-zinc-500 hidden md:table-cell">Email</th>
                        <th class="text-left py-3 px-4 font-semibold text-zinc-500">Last Active</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-50 dark:divide-zinc-800">
                    @foreach($recentUsers as $user)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-primary-100 dark:bg-primary-500/20 flex items-center justify-center text-xs font-bold text-primary-600 dark:text-primary-400">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <span class="font-medium text-zinc-900 dark:text-zinc-200">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                @php
                                    $roleColors = ['super_admin' => 'bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-300', 'shop_owner' => 'bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-300', 'tailor_staff' => 'bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300', 'customer' => 'bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-300'];
                                @endphp
                                <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $roleColors[$user->role] ?? 'bg-zinc-100 dark:bg-zinc-500/20 text-zinc-700 dark:text-zinc-300' }}">
                                    {{ ucwords(str_replace('_', ' ', $user->role)) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-zinc-500 dark:text-zinc-400 hidden md:table-cell">{{ $user->email }}</td>
                            <td class="py-3 px-4 text-zinc-400 text-xs">{{ $user->updated_at->diffForHumans() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
