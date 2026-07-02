<?php

use App\Models\Payment;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
    public string $statusFilter = '';
    public string $search = '';

    public function with(): array
    {
        $query = Payment::with(['order', 'user'])->latest();
        if ($this->statusFilter) $query->where('status', $this->statusFilter);
        if ($this->search) $query->whereHas('user', fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orWhereHas('order', fn($q) => $q->where('tracking_number', 'like', "%{$this->search}%"));
        return [
            'totalRevenue' => Payment::where('status', 'paid')->sum('amount'),
            'pendingAmount' => Payment::where('status', 'pending')->sum('amount'),
            'completedCount' => Payment::where('status', 'paid')->count(),
            'pendingCount' => Payment::where('status', 'pending')->count(),
            'failedCount' => Payment::where('status', 'failed')->count(),
            'avgPayment' => Payment::where('status', 'paid')->avg('amount') ?? 0,
            'payments' => $query->get(),
        ];
    }
}; ?>

<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-primary-600 dark:text-primary-400" style="font-family: 'Poppins';">Payment Monitoring</h1>
        <p class="text-zinc-500 mt-1">Track all transactions and revenue across the platform.</p>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="tc-card hover-lift group bg-gradient-to-br from-emerald-100 to-emerald-200 dark:from-emerald-900/30 dark:to-emerald-800/30 border-emerald-200 dark:border-emerald-800 animate-fade-in-up">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-500 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-xl font-bold text-zinc-900 dark:text-white" style="font-family:'Poppins'">₱{{ number_format($totalRevenue, 0) }}</p>
                    <p class="text-xs text-emerald-700 dark:text-emerald-300 font-medium">Total Revenue</p>
                </div>
            </div>
        </div>
        <div class="tc-card hover-lift group bg-gradient-to-br from-amber-100 to-amber-200 dark:from-amber-900/30 dark:to-amber-800/30 border-amber-200 dark:border-amber-800 animate-fade-in-up animation-delay-100">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-amber-500 flex items-center justify-center group-hover:scale-110 transition-transform duration-300 {{ $pendingAmount > 0 ? 'animate-pulse' : '' }}">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-xl font-bold text-zinc-900 dark:text-white" style="font-family:'Poppins'">₱{{ number_format($pendingAmount, 0) }}</p>
                    <p class="text-xs text-amber-700 dark:text-amber-300 font-medium">Outstanding</p>
                </div>
            </div>
        </div>
        <div class="tc-card hover-lift group bg-gradient-to-br from-blue-100 to-blue-200 dark:from-blue-900/30 dark:to-blue-800/30 border-blue-200 dark:border-blue-800 animate-fade-in-up animation-delay-200">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-blue-500 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-xl font-bold text-zinc-900 dark:text-white" style="font-family:'Poppins'">{{ $completedCount }}</p>
                    <p class="text-xs text-blue-700 dark:text-blue-300 font-medium">Completed</p>
                </div>
            </div>
        </div>
        <div class="tc-card hover-lift group bg-gradient-to-br from-purple-100 to-purple-200 dark:from-purple-900/30 dark:to-purple-800/30 border-purple-200 dark:border-purple-800 animate-fade-in-up animation-delay-300">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-purple-500 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75"/></svg>
                </div>
                <div>
                    <p class="text-xl font-bold text-zinc-900 dark:text-white" style="font-family:'Poppins'">₱{{ number_format($avgPayment, 0) }}</p>
                    <p class="text-xs text-purple-700 dark:text-purple-300 font-medium">Avg Payment</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="tc-card !p-4 flex flex-col sm:flex-row gap-4">
        <div class="flex-1 relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input wire:model.live.debounce.300ms="search" placeholder="Search by customer or order #..."
                class="w-full pl-10 pr-4 py-2.5 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-sm focus:ring-2 focus:ring-primary-500 transition-colors" />
        </div>
        <div class="flex gap-2">
            @foreach(['' => 'All', 'paid' => 'Paid', 'pending' => 'Pending', 'failed' => 'Failed'] as $key => $label)
                <button wire:click="$set('statusFilter', '{{ $key }}')"
                    class="px-4 py-2.5 text-sm font-medium rounded-xl transition-all duration-300 {{ $statusFilter === $key ? 'bg-primary-500 text-white shadow-lg' : 'bg-white dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700 hover:border-primary-300' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    <!-- Table -->
    <div class="tc-card !p-0 overflow-hidden">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-sm">
                <thead><tr class="border-b border-zinc-100 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50">
                    <th class="text-left py-4 px-4 font-semibold text-zinc-500">Order #</th>
                    <th class="text-left py-4 px-4 font-semibold text-zinc-500">Customer</th>
                    <th class="text-left py-4 px-4 font-semibold text-zinc-500">Amount</th>
                    <th class="text-left py-4 px-4 font-semibold text-zinc-500 hidden sm:table-cell">Method</th>
                    <th class="text-left py-4 px-4 font-semibold text-zinc-500">Status</th>
                    <th class="text-left py-4 px-4 font-semibold text-zinc-500 hidden md:table-cell">Date</th>
                </tr></thead>
                <tbody class="divide-y divide-zinc-50 dark:divide-zinc-800">
                    @forelse($payments as $payment)
                        <tr class="hover:bg-cream-50 dark:hover:bg-zinc-700/30 transition-colors duration-200 group">
                            <td class="py-4 px-4 font-mono text-xs font-semibold text-primary-600 dark:text-primary-400">{{ $payment->order?->tracking_number ?? 'N/A' }}</td>
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary-500 to-secondary-500 flex items-center justify-center text-xs font-bold text-white group-hover:scale-110 transition-transform duration-300">{{ strtoupper(substr($payment->user?->name ?? 'U', 0, 1)) }}</div>
                                    <span class="font-medium text-zinc-900 dark:text-white">{{ $payment->user?->name }}</span>
                                </div>
                            </td>
                            <td class="py-4 px-4 text-lg font-bold text-zinc-900 dark:text-white">₱{{ number_format($payment->amount, 2) }}</td>
                            <td class="py-4 px-4 hidden sm:table-cell">
                                <span class="px-2.5 py-1 text-xs font-medium bg-zinc-100 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300 rounded-full">{{ ucwords(str_replace('_', ' ', $payment->payment_method)) }}</span>
                            </td>
                            <td class="py-4 px-4"><span class="tc-badge tc-badge-{{ $payment->status }}">{{ ucfirst($payment->status) }}</span></td>
                            <td class="py-4 px-4 text-zinc-500 text-sm hidden md:table-cell">{{ $payment->payment_date?->format('M d, Y') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-16 text-center text-zinc-400 font-medium">No payments found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
