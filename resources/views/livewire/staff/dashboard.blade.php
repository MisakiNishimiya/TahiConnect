<?php

use App\Models\Order;
use App\Models\Appointment;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
    public function with(): array
    {
        $staffId = auth()->id();
        return [
            'assignedOrders' => Order::where('staff_id', $staffId)->whereNotIn('status', ['completed', 'released'])->count(),
            'todayAppointments' => Appointment::where('staff_id', $staffId)->whereDate('date', today())->count(),
            'pendingFittings' => Appointment::where('staff_id', $staffId)->where('type', 'fitting')->whereIn('status', ['pending', 'confirmed'])->count(),
            'completedThisWeek' => Order::where('staff_id', $staffId)->whereIn('status', ['completed', 'released'])->where('updated_at', '>=', now()->startOfWeek())->count(),
            'recentAssignedOrders' => Order::where('staff_id', $staffId)->with(['user', 'garmentType'])->latest()->take(8)->get(),
            'todaySchedule' => Appointment::where('staff_id', $staffId)->whereDate('date', today())->with('user')->orderBy('time')->get(),
        ];
    }
}; ?>

<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">Staff Dashboard</h1>
        <p class="text-zinc-500 mt-1">Welcome back, {{ auth()->user()->first_name }}! Here's your workload for today.</p>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="tc-stat-card animate-fade-in-up">
            <div class="tc-stat-icon bg-blue-100 dark:bg-blue-900/30"><svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg></div>
            <div><p class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">{{ $assignedOrders }}</p><p class="text-sm text-zinc-500">Active Orders</p></div>
        </div>
        <div class="tc-stat-card animate-fade-in-up animation-delay-100">
            <div class="tc-stat-icon bg-purple-100 dark:bg-purple-900/30"><svg class="w-6 h-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg></div>
            <div><p class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">{{ $todayAppointments }}</p><p class="text-sm text-zinc-500">Today's Appts</p></div>
        </div>
        <div class="tc-stat-card animate-fade-in-up animation-delay-200">
            <div class="tc-stat-icon bg-amber-100 dark:bg-amber-900/30"><svg class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3"/></svg></div>
            <div><p class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">{{ $pendingFittings }}</p><p class="text-sm text-zinc-500">Pending Fittings</p></div>
        </div>
        <div class="tc-stat-card animate-fade-in-up animation-delay-300">
            <div class="tc-stat-icon bg-emerald-100 dark:bg-emerald-900/30"><svg class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
            <div><p class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">{{ $completedThisWeek }}</p><p class="text-sm text-zinc-500">Completed / Week</p></div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Today's Schedule -->
        <div class="tc-card">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4" style="font-family: 'Poppins';">Today's Schedule</h2>
            <div class="space-y-3">
                @forelse($todaySchedule as $appt)
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-cream-50 dark:bg-zinc-700/50">
                        <div class="w-12 h-12 rounded-xl bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-center">
                            <p class="text-sm font-bold text-primary-700 dark:text-primary-300">{{ \Carbon\Carbon::parse($appt->time)->format('g:i') }}<br><span class="text-[10px] font-normal">{{ \Carbon\Carbon::parse($appt->time)->format('A') }}</span></p>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-zinc-900 dark:text-white">{{ $appt->user?->name }}</p>
                            <p class="text-xs text-zinc-500">{{ ucfirst($appt->type) }}</p>
                        </div>
                        <span class="tc-badge tc-badge-{{ $appt->status }}">{{ ucfirst($appt->status) }}</span>
                    </div>
                @empty
                    <p class="text-sm text-zinc-400 text-center py-6">No appointments scheduled for today.</p>
                @endforelse
            </div>
        </div>

        <!-- Active Orders -->
        <div class="tc-card">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4" style="font-family: 'Poppins';">Assigned Orders</h2>
            <div class="space-y-3">
                @forelse($recentAssignedOrders->take(5) as $order)
                    <div class="flex items-center justify-between p-3 rounded-xl bg-cream-50 dark:bg-zinc-700/50">
                        <div>
                            <p class="text-sm font-mono font-semibold text-primary-600 dark:text-primary-400">{{ $order->tracking_number }}</p>
                            <p class="text-xs text-zinc-500">{{ $order->user?->name }} · {{ $order->garmentType?->name }}</p>
                        </div>
                        <span class="tc-badge tc-badge-{{ $order->status }}">{{ ucwords(str_replace('_', ' ', $order->status)) }}</span>
                    </div>
                @empty
                    <p class="text-sm text-zinc-400 text-center py-6">No assigned orders.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
