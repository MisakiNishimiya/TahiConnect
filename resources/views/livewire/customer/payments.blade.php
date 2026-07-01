<?php

use App\Models\Payment;
use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
    public function with(): array
    {
        $userId = auth()->id();
        return [
            'totalPaid' => Payment::where('user_id', $userId)->where('status', 'paid')->sum('amount'),
            'totalOutstanding' => Payment::where('user_id', $userId)->where('status', 'pending')->sum('amount'),
            'totalOrdersValue' => Order::where('user_id', $userId)->sum('total_amount'),
            'payments' => Payment::where('user_id', $userId)->with('order')->latest()->get(),
            'outstandingPayments' => Payment::where('user_id', $userId)->where('status', 'pending')->with('order')->get(),
        ];
    }
}; ?>

<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">Payments</h1>
        <p class="text-zinc-500 mt-1">Manage your payment history and outstanding balances.</p>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="tc-stat-card animate-fade-in-up">
            <div class="tc-stat-icon bg-emerald-100 dark:bg-emerald-900/30">
                <svg class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">₱{{ number_format($totalPaid, 2) }}</p>
                <p class="text-sm text-zinc-500">Total Paid</p>
            </div>
        </div>
        <div class="tc-stat-card animate-fade-in-up animation-delay-100">
            <div class="tc-stat-icon bg-amber-100 dark:bg-amber-900/30">
                <svg class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">₱{{ number_format($totalOutstanding, 2) }}</p>
                <p class="text-sm text-zinc-500">Outstanding</p>
            </div>
        </div>
        <div class="tc-stat-card animate-fade-in-up animation-delay-200">
            <div class="tc-stat-icon bg-blue-100 dark:bg-blue-900/30">
                <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">₱{{ number_format($totalOrdersValue, 2) }}</p>
                <p class="text-sm text-zinc-500">Total Orders Value</p>
            </div>
        </div>
    </div>

    <!-- Outstanding Payments -->
    @if($outstandingPayments->count() > 0)
        <div>
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white mb-3" style="font-family: 'Poppins';">Outstanding Payments</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($outstandingPayments as $op)
                    <div class="tc-card border-l-4 border-l-amber-400">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-mono text-zinc-600 dark:text-zinc-400">{{ $op->order?->tracking_number ?? 'N/A' }}</p>
                                <p class="text-xl font-bold text-zinc-900 dark:text-white mt-1" style="font-family: 'Poppins';">₱{{ number_format($op->amount, 2) }}</p>
                            </div>
                            <flux:button variant="primary" size="sm" class="!bg-primary-500 hover:!bg-primary-600">Pay Now</flux:button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Payment History -->
    <div class="tc-card">
        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4" style="font-family: 'Poppins';">Payment History</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-700">
                        <th class="text-left py-3 px-2 font-medium text-zinc-500">Order #</th>
                        <th class="text-left py-3 px-2 font-medium text-zinc-500">Amount</th>
                        <th class="text-left py-3 px-2 font-medium text-zinc-500 hidden sm:table-cell">Method</th>
                        <th class="text-left py-3 px-2 font-medium text-zinc-500 hidden md:table-cell">Date</th>
                        <th class="text-left py-3 px-2 font-medium text-zinc-500">Status</th>
                        <th class="text-right py-3 px-2 font-medium text-zinc-500">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach($payments as $payment)
                        <tr class="hover:bg-cream-50 dark:hover:bg-zinc-700/30 transition-colors">
                            <td class="py-3 px-2 font-mono text-xs">{{ $payment->order?->tracking_number ?? 'N/A' }}</td>
                            <td class="py-3 px-2 font-semibold">₱{{ number_format($payment->amount, 2) }}</td>
                            <td class="py-3 px-2 hidden sm:table-cell">
                                <span class="tc-badge {{ match($payment->payment_method) { 'gcash' => 'bg-blue-50 text-blue-700 border border-blue-200', 'cash' => 'bg-emerald-50 text-emerald-700 border border-emerald-200', 'bank_transfer' => 'bg-purple-50 text-purple-700 border border-purple-200', 'card' => 'bg-indigo-50 text-indigo-700 border border-indigo-200', default => 'bg-zinc-50 text-zinc-700 border border-zinc-200' } }}">
                                    {{ ucwords(str_replace('_', ' ', $payment->payment_method)) }}
                                </span>
                            </td>
                            <td class="py-3 px-2 text-zinc-500 hidden md:table-cell">{{ $payment->payment_date?->format('M d, Y') ?? '—' }}</td>
                            <td class="py-3 px-2"><span class="tc-badge tc-badge-{{ $payment->status }}">{{ ucfirst($payment->status) }}</span></td>
                            <td class="py-3 px-2 text-right">
                                @if($payment->status === 'paid')
                                    <button class="text-xs text-primary-600 hover:text-primary-800">Receipt</button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
