<?php

use App\Models\Payment;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
    public function with(): array
    {
        return [
            'totalRevenue' => Payment::where('status', 'paid')->sum('amount'),
            'pendingAmount' => Payment::where('status', 'pending')->sum('amount'),
            'completedCount' => Payment::where('status', 'paid')->count(),
            'pendingCount' => Payment::where('status', 'pending')->count(),
            'payments' => Payment::with(['order', 'user'])->latest()->get(),
        ];
    }
}; ?>

<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">Payment Monitoring</h1>
        <p class="text-zinc-500 mt-1">Track all payments and revenue.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="tc-stat-card animate-fade-in-up">
            <div class="tc-stat-icon bg-emerald-100"><svg class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
            <div><p class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">₱{{ number_format($totalRevenue, 0) }}</p><p class="text-sm text-zinc-500">Total Revenue</p></div>
        </div>
        <div class="tc-stat-card animate-fade-in-up animation-delay-100">
            <div class="tc-stat-icon bg-amber-100"><svg class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
            <div><p class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">₱{{ number_format($pendingAmount, 0) }}</p><p class="text-sm text-zinc-500">Pending</p></div>
        </div>
        <div class="tc-stat-card animate-fade-in-up animation-delay-200">
            <div class="tc-stat-icon bg-blue-100"><svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
            <div><p class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">{{ $completedCount }}</p><p class="text-sm text-zinc-500">Completed</p></div>
        </div>
        <div class="tc-stat-card animate-fade-in-up animation-delay-300">
            <div class="tc-stat-icon bg-orange-100"><svg class="w-6 h-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg></div>
            <div><p class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">{{ $pendingCount }}</p><p class="text-sm text-zinc-500">Pending Count</p></div>
        </div>
    </div>

    <div class="tc-card !p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="border-b border-zinc-200 dark:border-zinc-700 bg-cream-50 dark:bg-zinc-800/50">
                    <th class="text-left py-3 px-4 font-medium text-zinc-500">Order #</th>
                    <th class="text-left py-3 px-4 font-medium text-zinc-500">Customer</th>
                    <th class="text-left py-3 px-4 font-medium text-zinc-500">Amount</th>
                    <th class="text-left py-3 px-4 font-medium text-zinc-500 hidden sm:table-cell">Method</th>
                    <th class="text-left py-3 px-4 font-medium text-zinc-500">Status</th>
                    <th class="text-left py-3 px-4 font-medium text-zinc-500 hidden md:table-cell">Date</th>
                </tr></thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach($payments as $payment)
                        <tr class="hover:bg-cream-50 dark:hover:bg-zinc-700/30">
                            <td class="py-3 px-4 font-mono text-xs">{{ $payment->order?->tracking_number ?? 'N/A' }}</td>
                            <td class="py-3 px-4">{{ $payment->user?->name }}</td>
                            <td class="py-3 px-4 font-semibold">₱{{ number_format($payment->amount, 2) }}</td>
                            <td class="py-3 px-4 hidden sm:table-cell"><span class="tc-badge tc-badge-confirmed">{{ ucwords(str_replace('_', ' ', $payment->payment_method)) }}</span></td>
                            <td class="py-3 px-4"><span class="tc-badge tc-badge-{{ $payment->status }}">{{ ucfirst($payment->status) }}</span></td>
                            <td class="py-3 px-4 text-zinc-500 hidden md:table-cell">{{ $payment->payment_date?->format('M d, Y') ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
