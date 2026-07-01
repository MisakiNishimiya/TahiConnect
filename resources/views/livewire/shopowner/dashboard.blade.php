<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Models\Order;
use App\Models\User;
use App\Models\Shop;
use Carbon\Carbon;

new #[Layout('components.layouts.app')] class extends Component {
    public function with()
    {
        $shopId = auth()->user()->shop_id;
        $shop = Shop::with('reviews')->find($shopId);
        
        $totalOrders = Order::where('shop_id', $shopId)->count();
        $monthlyRevenue = Order::where('shop_id', $shopId)
            ->whereMonth('created_at', Carbon::now()->month)
            ->sum('total_amount');
            
        $staffCount = User::where('shop_id', $shopId)->where('role', 'tailor_staff')->count();
        $recentOrders = Order::with(['user', 'garmentType'])
            ->where('shop_id', $shopId)
            ->latest()
            ->take(5)
            ->get();

        return [
            'shop' => $shop,
            'totalOrders' => $totalOrders,
            'monthlyRevenue' => $monthlyRevenue,
            'staffCount' => $staffCount,
            'recentOrders' => $recentOrders,
        ];
    }
}; ?>

<div>
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-primary-600 dark:text-primary-400">Shop Overview</h1>
        <p class="text-zinc-500 dark:text-zinc-400">{{ $shop->name }}</p>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="tc-stat-card">
            <div class="tc-stat-icon bg-primary-100 text-primary-600 dark:bg-primary-900/50 dark:text-primary-400">
                <flux:icon.shopping-bag />
            </div>
            <div>
                <p class="text-sm font-medium text-zinc-500">Total Orders</p>
                <h3 class="text-2xl font-bold">{{ $totalOrders }}</h3>
            </div>
        </div>
        <div class="tc-stat-card">
            <div class="tc-stat-icon bg-secondary-100 text-secondary-600 dark:bg-secondary-900/50 dark:text-secondary-400">
                <flux:icon.banknotes />
            </div>
            <div>
                <p class="text-sm font-medium text-zinc-500">Monthly Revenue</p>
                <h3 class="text-2xl font-bold">₱{{ number_format($monthlyRevenue, 2) }}</h3>
            </div>
        </div>
        <div class="tc-stat-card">
            <div class="tc-stat-icon bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                <flux:icon.users />
            </div>
            <div>
                <p class="text-sm font-medium text-zinc-500">Staff Members</p>
                <h3 class="text-2xl font-bold">{{ $staffCount }}</h3>
            </div>
        </div>
        <div class="tc-stat-card">
            <div class="tc-stat-icon bg-yellow-100 text-yellow-600 dark:bg-yellow-900/50 dark:text-yellow-400">
                <flux:icon.star />
            </div>
            <div>
                <p class="text-sm font-medium text-zinc-500">Rating</p>
                <h3 class="text-2xl font-bold flex items-center gap-2">
                    {{ $shop->rating }}
                    <span class="text-yellow-500 text-sm tracking-tighter">{{ $shop->star_rating }}</span>
                </h3>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="tc-card">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold">Recent Orders</h2>
            <flux:button size="sm" variant="outline" :href="route('shopowner.orders')">View All</flux:button>
        </div>

        <div class="overflow-x-auto">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Tracking No.</flux:table.column>
                    <flux:table.column>Customer</flux:table.column>
                    <flux:table.column>Garment</flux:table.column>
                    <flux:table.column>Amount</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($recentOrders as $order)
                        <flux:table.row>
                            <flux:table.cell class="font-medium">{{ $order->tracking_number }}</flux:table.cell>
                            <flux:table.cell>{{ $order->user->name }}</flux:table.cell>
                            <flux:table.cell>{{ $order->garmentType->name }}</flux:table.cell>
                            <flux:table.cell>₱{{ number_format($order->total_amount, 2) }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge color="{{ match($order->status) {
                                    'completed', 'released' => 'green',
                                    'pending' => 'zinc',
                                    'in_production' => 'blue',
                                    default => 'orange'
                                } }}" size="sm">
                                    {{ str_replace('_', ' ', Str::title($order->status)) }}
                                </flux:badge>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
            @if($recentOrders->isEmpty())
                <div class="py-8 text-center text-zinc-500">No orders yet.</div>
            @endif
        </div>
    </div>
</div>
