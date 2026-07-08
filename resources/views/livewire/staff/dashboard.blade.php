<?php

use App\Models\Order;
use App\Models\Appointment;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
    public function with(): array
    {
        $user = auth()->user();
        return [
            'assignedOrders' => Order::where('staff_id', $user->id)->whereNotIn('status', ['completed', 'released'])->count(),
            'todayAppointments' => Appointment::where('staff_id', $user->id)->whereDate('date', today())->count(),
            'pendingFittings' => Appointment::where('staff_id', $user->id)->where('type', 'fitting')->whereIn('status', ['pending', 'confirmed'])->count(),
            'completedThisWeek' => Order::where('staff_id', $user->id)->whereIn('status', ['completed', 'released'])->where('updated_at', '>=', now()->startOfWeek())->count(),
            'recentAssignedOrders' => Order::where('staff_id', $user->id)->with(['user', 'garmentType', 'preMadeProduct'])->latest()->take(8)->get(),
            'todaySchedule' => Appointment::where('staff_id', $user->id)->whereDate('date', today())->with('user')->orderBy('time')->get(),
        ];
    }
}; ?>

<div class="space-y-6">
    <!-- Header -->
    <div class="tc-card bg-gradient-to-br from-primary-600 to-primary-800 text-white border-0 overflow-hidden relative">
        <div class="absolute inset-0 opacity-10">
            <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                <polygon points="0,0 100,0 100,40 0,100" fill="white"/>
            </svg>
        </div>
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <p class="text-primary-200 text-sm mb-1">Welcome back,</p>
                <h1 class="text-3xl font-bold" style="font-family: 'Poppins';">{{ auth()->user()->first_name }} 👋</h1>
                <p class="text-primary-200 mt-1">{{ now()->format('l, F d, Y') }}</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl px-5 py-3 text-center border border-white/20">
                    <p class="text-2xl font-bold">{{ $todayAppointments }}</p>
                    <p class="text-xs text-primary-200">Today's Appts</p>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl px-5 py-3 text-center border border-white/20">
                    <p class="text-2xl font-bold">{{ $assignedOrders }}</p>
                    <p class="text-xs text-primary-200">Active Orders</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="tc-card hover-lift group animate-fade-in-up">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-100 to-blue-200 dark:from-blue-900/30 dark:to-blue-800/30 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-7 h-7 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">{{ $assignedOrders }}</p>
                    <p class="text-xs text-zinc-500">Active Orders</p>
                </div>
            </div>
        </div>
        <div class="tc-card hover-lift group animate-fade-in-up animation-delay-100">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-100 to-purple-200 dark:from-purple-900/30 dark:to-purple-800/30 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-7 h-7 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">{{ $todayAppointments }}</p>
                    <p class="text-xs text-zinc-500">Today's Appts</p>
                </div>
            </div>
        </div>
        <div class="tc-card hover-lift group animate-fade-in-up animation-delay-200">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-100 to-amber-200 dark:from-amber-900/30 dark:to-amber-800/30 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-7 h-7 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">{{ $pendingFittings }}</p>
                    <p class="text-xs text-zinc-500">Pending Fittings</p>
                </div>
            </div>
        </div>
        <div class="tc-card hover-lift group animate-fade-in-up animation-delay-300">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-100 to-emerald-200 dark:from-emerald-900/30 dark:to-emerald-800/30 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-7 h-7 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">{{ $completedThisWeek }}</p>
                    <p class="text-xs text-zinc-500">Completed / Week</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Today's Schedule -->
        <div class="tc-card">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">Today's Schedule</h2>
                        <p class="text-xs text-zinc-500">{{ now()->format('l, M d') }}</p>
                    </div>
                </div>
                @if($todayAppointments > 0)
                    <span class="w-6 h-6 bg-purple-500 text-white rounded-full text-xs flex items-center justify-center font-bold">{{ $todayAppointments }}</span>
                @endif
            </div>
            <div class="space-y-3">
                @forelse($todaySchedule as $appt)
                    <div class="flex items-center gap-4 p-4 rounded-2xl bg-gradient-to-r from-zinc-50 to-white dark:from-zinc-700/50 dark:to-zinc-800/30 border border-zinc-100 dark:border-zinc-700 hover-lift interactive-card group">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-primary-100 to-primary-200 dark:from-primary-900/30 dark:to-primary-800/30 flex flex-col items-center justify-center border border-primary-200 dark:border-primary-800 group-hover:scale-105 transition-transform duration-300 shrink-0">
                            <p class="text-sm font-bold text-primary-700 dark:text-primary-300 leading-none">{{ \Carbon\Carbon::parse($appt->time)->format('g:i') }}</p>
                            <p class="text-[10px] text-primary-500 font-medium">{{ \Carbon\Carbon::parse($appt->time)->format('A') }}</p>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-zinc-900 dark:text-white truncate">{{ $appt->user?->name }}</p>
                            <p class="text-xs text-zinc-500">{{ ucwords(str_replace('_', ' ', $appt->type)) }}</p>
                        </div>
                        <span class="tc-badge tc-badge-{{ $appt->status }} shrink-0">{{ ucfirst($appt->status) }}</span>
                    </div>
                @empty
                    <div class="text-center py-10">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-zinc-100 to-zinc-200 dark:from-zinc-700 dark:to-zinc-800 flex items-center justify-center">
                            <svg class="w-8 h-8 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25"/></svg>
                        </div>
                        <p class="text-sm font-medium text-zinc-500">No appointments today</p>
                        <p class="text-xs text-zinc-400 mt-1">Enjoy your free time! ☕</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Active Orders -->
        <div class="tc-card">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">Assigned Orders</h2>
                        <p class="text-xs text-zinc-500">{{ $recentAssignedOrders->count() }} orders total</p>
                    </div>
                </div>
                <a href="{{ route('staff.orders') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 font-medium flex items-center gap-1">
                    View All
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
            <div class="space-y-3">
                @forelse($recentAssignedOrders->take(5) as $order)
                    <div class="flex items-center justify-between p-4 rounded-2xl bg-gradient-to-r from-zinc-50 to-white dark:from-zinc-700/50 dark:to-zinc-800/30 border border-zinc-100 dark:border-zinc-700 hover-lift interactive-card group">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-100 to-primary-200 dark:from-primary-900/30 dark:to-primary-800/30 flex items-center justify-center group-hover:scale-105 transition-transform duration-300">
                                <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-mono font-semibold text-primary-600 dark:text-primary-400">{{ $order->tracking_number }}</p>
                                <p class="text-xs text-zinc-500">{{ $order->user?->name }} · {{ $order->garmentType?->name }}</p>
                            </div>
                        </div>
                        <span class="tc-badge tc-badge-{{ $order->status }}">{{ ucwords(str_replace('_', ' ', $order->status)) }}</span>
                    </div>
                @empty
                    <div class="text-center py-10">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-zinc-100 to-zinc-200 dark:from-zinc-700 dark:to-zinc-800 flex items-center justify-center">
                            <svg class="w-8 h-8 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z"/></svg>
                        </div>
                        <p class="text-sm font-medium text-zinc-500">No assigned orders yet</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
