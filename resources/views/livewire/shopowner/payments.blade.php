<?php

use App\Models\Payment;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('components.layouts.app')] class extends Component {
    use WithPagination;

    public string $status = '';

    public function with(): array
    {
        return [
            'payments' => Payment::with(['order', 'user'])
                ->when($this->status, fn($q) => $q->where('status', $this->status))
                ->latest()
                ->paginate(15),
            'totalRevenue'  => Payment::where('status', 'paid')->sum('amount'),
            'pendingAmount' => Payment::where('status', 'pending')->sum('amount'),
            'paidCount'     => Payment::where('status', 'paid')->count(),
            'pendingCount'  => Payment::where('status', 'pending')->count(),
        ];
    }

    public function updatedStatus(): void { $this->resetPage(); }
}; ?>

<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-primary-600 dark:text-primary-400" style="font-family: 'Poppins';">Payments</h1>
        <p class="text-zinc-500 mt-1">Monitor all payment transactions for the business</p>
    </div>

    <!-- Summary -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach([
            ['Total Revenue', '₱'.number_format($totalRevenue,2), 'Collected', 'from-emerald-100 to-emerald-200 dark:from-emerald-900/30 dark:to-emerald-800/30', 'text-emerald-600 dark:text-emerald-400', 'border-emerald-200 dark:border-emerald-800'],
            ['Pending Amount', '₱'.number_format($pendingAmount,2), 'Awaiting payment', 'from-amber-100 to-amber-200 dark:from-amber-900/30 dark:to-amber-800/30', 'text-amber-600 dark:text-amber-400', 'border-amber-200 dark:border-amber-800'],
            ['Paid Transactions', $paidCount, 'Completed', 'from-blue-100 to-blue-200 dark:from-blue-900/30 dark:to-blue-800/30', 'text-blue-600 dark:text-blue-400', 'border-blue-200 dark:border-blue-800'],
            ['Pending Transactions', $pendingCount, 'Outstanding', 'from-red-100 to-red-200 dark:from-red-900/30 dark:to-red-800/30', 'text-red-600 dark:text-red-400', 'border-red-200 dark:border-red-800'],
        ] as [$label, $value, $sub, $bg, $color, $border])
        <div class="tc-card bg-gradient-to-br {{ $bg }} {{ $border }}">
            <p class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family:'Poppins'">{{ $value }}</p>
            <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $label }}</p>
            <p class="text-xs {{ $color }} mt-1">{{ $sub }}</p>
        </div>
        @endforeach
    </div>

    <!-- Filter -->
    <div class="tc-card !p-4">
        <select wire:model.live="status" class="px-4 py-2.5 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-sm focus:ring-2 focus:ring-primary-500">
            <option value="">All Payments</option>
            <option value="paid">Paid</option>
            <option value="pending">Pending</option>
        </select>
    </div>

    <!-- Table -->
    <div class="tc-card !p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-100 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50">
                        <th class="text-left py-4 px-4 font-semibold text-zinc-500">Customer</th>
                        <th class="text-left py-4 px-4 font-semibold text-zinc-500 hidden md:table-cell">Order</th>
                        <th class="text-left py-4 px-4 font-semibold text-zinc-500 hidden lg:table-cell">Method</th>
                        <th class="text-left py-4 px-4 font-semibold text-zinc-500">Status</th>
                        <th class="text-right py-4 px-4 font-semibold text-zinc-500">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-50 dark:divide-zinc-800">
                    @forelse($payments as $payment)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/30 transition-colors">
                            <td class="py-4 px-4 font-medium text-zinc-900 dark:text-white">{{ $payment->user?->name }}</td>
                            <td class="py-4 px-4 font-mono text-xs text-primary-600 dark:text-primary-400 hidden md:table-cell">{{ $payment->order?->tracking_number }}</td>
                            <td class="py-4 px-4 text-zinc-500 hidden lg:table-cell">{{ ucwords(str_replace('_', ' ', $payment->payment_method)) }}</td>
                            <td class="py-4 px-4"><span class="tc-badge tc-badge-{{ $payment->status }}">{{ ucfirst($payment->status) }}</span></td>
                            <td class="py-4 px-4 text-right font-bold text-zinc-900 dark:text-white">₱{{ number_format($payment->amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-16 text-center text-zinc-400">No payments found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payments->hasPages())
            <div class="px-4 py-4 border-t border-zinc-100 dark:border-zinc-700">{{ $payments->links() }}</div>
        @endif
    </div>
</div>
