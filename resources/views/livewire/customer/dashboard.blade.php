<?php

use App\Models\Order;
use App\Models\Appointment;
use App\Models\Payment;
use App\Models\CustomNotification;
use App\Models\ActivityLog;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
    public function with(): array
    {
        $user = auth()->user();
        return [
            'activeOrders' => Order::where('user_id', $user->id)->whereNotIn('status', ['completed', 'released'])->count(),
            'upcomingAppointments' => Appointment::where('user_id', $user->id)->whereIn('status', ['pending', 'confirmed'])->where('date', '>=', now()->toDateString())->count(),
            'pendingPayments' => Payment::where('user_id', $user->id)->where('status', 'pending')->count(),
            'unreadNotifications' => CustomNotification::where('user_id', $user->id)->where('is_read', false)->count(),
            'recentOrders' => Order::where('user_id', $user->id)->with(['garmentType', 'shop', 'preMadeProduct'])->latest()->take(5)->get(),
            'recentActivities' => ActivityLog::where('user_id', $user->id)->latest()->take(5)->get(),
            'upcomingAppointmentsList' => Appointment::where('user_id', $user->id)->with(['shop'])->where('date', '>=', now()->toDateString())->orderBy('date')->take(3)->get(),
        ];
    }
}; ?>

<div class="space-y-6">
    <!-- Welcome -->
    <div>
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins', sans-serif;">
            Welcome back, {{ auth()->user()->first_name ?? auth()->user()->name }}! 👋
        </h1>
        <p class="text-zinc-500 mt-1">Here's what's happening with your tailoring orders.</p>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="tc-stat-card animate-fade-in-up">
            <div class="tc-stat-icon bg-emerald-100 dark:bg-emerald-900/30">
                <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">{{ $activeOrders }}</p>
                <p class="text-sm text-zinc-500">Active Orders</p>
            </div>
        </div>

        <div class="tc-stat-card animate-fade-in-up animation-delay-100">
            <div class="tc-stat-icon bg-blue-100 dark:bg-blue-900/30">
                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">{{ $upcomingAppointments }}</p>
                <p class="text-sm text-zinc-500">Upcoming Appointments</p>
            </div>
        </div>

        <div class="tc-stat-card animate-fade-in-up animation-delay-200">
            <div class="tc-stat-icon bg-amber-100 dark:bg-amber-900/30">
                <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">{{ $pendingPayments }}</p>
                <p class="text-sm text-zinc-500">Pending Payments</p>
            </div>
        </div>

        <div class="tc-stat-card animate-fade-in-up animation-delay-300">
            <div class="tc-stat-icon bg-purple-100 dark:bg-purple-900/30">
                <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">{{ $unreadNotifications }}</p>
                <p class="text-sm text-zinc-500">Notifications</p>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="tc-card">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">Quick Actions</h2>
            <p class="text-sm text-zinc-500">Your tailoring journey</p>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <!-- Browse Shops -->
            <div class="flex flex-col items-center text-center group">
                <div class="relative mb-4">
                    <x-progress-circle 
                        :percentage="$activeOrders > 0 ? 25 : 0" 
                        size="64" 
                        strokeWidth="4" 
                        color="blue"
                    />
                    <div class="absolute inset-0 flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                </div>
                <a href="{{ route('customer.shops') }}" wire:navigate class="w-full py-2 px-3 text-sm font-medium text-blue-600 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors text-center">
                    Browse Shops
                </a>
            </div>

            <!-- New Order -->
            <div class="flex flex-col items-center text-center group">
                <div class="relative mb-4">
                    <x-progress-circle 
                        :percentage="$activeOrders > 0 ? 50 : 0" 
                        size="64" 
                        strokeWidth="4" 
                        color="emerald"
                    />
                    <div class="absolute inset-0 flex items-center justify-center">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/>
                        </svg>
                    </div>
                </div>
                <a href="{{ route('customer.orders') }}" wire:navigate class="w-full py-2 px-3 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors text-center">
                    New Order
                </a>
            </div>

            <!-- Measurements -->
            <div class="flex flex-col items-center text-center group">
                <div class="relative mb-4">
                    <x-progress-circle 
                        :percentage="$activeOrders > 0 ? 75 : 0" 
                        size="64" 
                        strokeWidth="4" 
                        color="purple"
                    />
                    <div class="absolute inset-0 flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3"/>
                        </svg>
                    </div>
                </div>
                <a href="{{ route('customer.measurements') }}" wire:navigate class="w-full py-2 px-3 text-sm font-medium text-purple-600 bg-purple-50 dark:bg-purple-900/20 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-900/30 transition-colors text-center">
                    My Measurements
                </a>
            </div>

            <!-- Appointments -->
            <div class="flex flex-col items-center text-center group">
                <div class="relative mb-4">
                    <x-progress-circle 
                        :percentage="$upcomingAppointments > 0 ? 100 : 20" 
                        size="64" 
                        strokeWidth="4" 
                        color="amber"
                    />
                    <div class="absolute inset-0 flex items-center justify-center">
                        <svg class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                        </svg>
                    </div>
                </div>
                <a href="{{ route('customer.appointments') }}" wire:navigate class="w-full py-2 px-3 text-sm font-medium text-amber-600 bg-amber-50 dark:bg-amber-900/20 rounded-lg hover:bg-amber-100 dark:hover:bg-amber-900/30 transition-colors text-center">
                    Book Appointment
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Orders & Appointments -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Orders -->
        <div class="tc-card">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4" style="font-family: 'Poppins';">Recent Orders</h2>
            <div class="space-y-3">
                @forelse($recentOrders as $order)
                    <div class="flex items-center justify-between p-3 rounded-xl bg-cream-50 dark:bg-zinc-700/50">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
                                <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-zinc-900 dark:text-white font-mono">{{ $order->tracking_number }}</p>
                                <p class="text-xs text-zinc-500">{{ $order->garmentType?->name ?? 'Custom' }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="tc-badge tc-badge-{{ $order->status }}">{{ ucwords(str_replace('_', ' ', $order->status)) }}</span>
                            <p class="text-xs text-zinc-400 mt-1">{{ $order->created_at->format('M d') }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-zinc-400 text-center py-4">No orders yet. Place your first order!</p>
                @endforelse
            </div>
        </div>

        <!-- Upcoming Appointments -->
        <div class="tc-card">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4" style="font-family: 'Poppins';">Upcoming Appointments</h2>
            <div class="space-y-3">
                @forelse($upcomingAppointmentsList as $appt)
                    <div class="flex items-center justify-between p-3 rounded-xl bg-cream-50 dark:bg-zinc-700/50">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-zinc-900 dark:text-white">{{ $appt->date->format('M d, Y') }}</p>
                                <p class="text-xs text-zinc-500">{{ \Carbon\Carbon::parse($appt->time)->format('g:i A') }} · {{ ucfirst($appt->type) }}</p>
                            </div>
                        </div>
                        <span class="tc-badge tc-badge-{{ $appt->status }}">{{ ucfirst($appt->status) }}</span>
                    </div>
                @empty
                    <p class="text-sm text-zinc-400 text-center py-4">No upcoming appointments.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="tc-card">
        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4" style="font-family: 'Poppins';">Recent Activity</h2>
        <div class="tc-timeline">
            @forelse($recentActivities as $activity)
                <div class="tc-timeline-item">
                    <div class="tc-timeline-dot bg-primary-500"></div>
                    <div>
                        <p class="text-sm font-medium text-zinc-900 dark:text-white">{{ $activity->action }}</p>
                        <p class="text-xs text-zinc-500">{{ $activity->description }}</p>
                        <p class="text-xs text-zinc-400 mt-1">{{ $activity->created_at->diffForHumans() }}</p>
                    </div>
                </div>
            @empty
                <p class="text-sm text-zinc-400 text-center py-4">No recent activity.</p>
            @endforelse
        </div>
    </div>
</div>
