<?php

use App\Models\Payment;
use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
    public string $activeTab = 'all';
    public bool $showPaymentModal = false;
    public ?Payment $selectedPayment = null;

    public function showPaymentDetails($paymentId): void
    {
        $this->selectedPayment = Payment::where('id', $paymentId)->where('user_id', auth()->id())->with('order')->first();
        $this->showPaymentModal = true;
    }

    public function closePaymentModal(): void
    {
        $this->showPaymentModal = false;
        $this->selectedPayment = null;
    }

    public function getPaymentsByTab()
    {
        $query = Payment::where('user_id', auth()->id())->with('order');
        
        switch($this->activeTab) {
            case 'pending':
                $query->where('status', 'pending');
                break;
            case 'paid':
                $query->where('status', 'paid');
                break;
            case 'failed':
                $query->where('status', 'failed');
                break;
        }
        
        return $query->latest()->get();
    }

    public function with(): array
    {
        $userId = auth()->id();
        return [
            'totalPaid' => Payment::where('user_id', $userId)->where('status', 'paid')->sum('amount'),
            'totalOutstanding' => Payment::where('user_id', $userId)->where('status', 'pending')->sum('amount'),
            'totalOrdersValue' => Order::where('user_id', $userId)->sum('total_amount'),
            'payments' => $this->getPaymentsByTab(),
            'outstandingPayments' => Payment::where('user_id', $userId)->where('status', 'pending')->with('order')->get(),
            'paymentCounts' => [
                'all' => Payment::where('user_id', $userId)->count(),
                'pending' => Payment::where('user_id', $userId)->where('status', 'pending')->count(),
                'paid' => Payment::where('user_id', $userId)->where('status', 'paid')->count(),
                'failed' => Payment::where('user_id', $userId)->where('status', 'failed')->count(),
            ],
            'recentPayments' => Payment::where('user_id', $userId)->where('status', 'paid')->with('order')->latest()->take(3)->get(),
        ];
    }
}; ?>

<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-primary-600 dark:text-primary-400" style="font-family: 'Poppins';">Payment Center</h1>
            <p class="text-zinc-500 mt-1">Track your payment history and manage outstanding balances.</p>
        </div>
        
        <!-- Quick Actions -->
        @if($totalOutstanding > 0)
            <div class="flex gap-3">
                <button class="px-6 py-3 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white rounded-xl hover:from-emerald-600 hover:to-emerald-700 transition-all duration-300 font-medium shadow-lg hover:shadow-xl hover:shadow-emerald-500/25 click-feedback flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Pay Outstanding (₱{{ number_format($totalOutstanding, 2) }})
                </button>
            </div>
        @endif
    </div>

    <!-- Enhanced Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <!-- Total Paid -->
        <div class="tc-card hover-lift group">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-100 to-emerald-200 dark:from-emerald-900/30 dark:to-emerald-800/30 flex items-center justify-center border border-emerald-200 dark:border-emerald-800 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-7 h-7 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-white mb-1" style="font-family: 'Poppins';">₱{{ number_format($totalPaid, 2) }}</p>
                    <p class="text-sm text-zinc-500">Total Paid</p>
                    <div class="flex items-center gap-1 mt-1">
                        <div class="w-2 h-2 bg-emerald-500 rounded-full"></div>
                        <span class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">Completed</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Outstanding Amount -->
        <div class="tc-card hover-lift group {{ $totalOutstanding > 0 ? 'border-amber-200 dark:border-amber-800 bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-900/10 dark:to-orange-900/10' : '' }}">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-100 to-amber-200 dark:from-amber-900/30 dark:to-amber-800/30 flex items-center justify-center border border-amber-200 dark:border-amber-800 group-hover:scale-110 transition-transform duration-300 {{ $totalOutstanding > 0 ? 'animate-pulse' : '' }}">
                    <svg class="w-7 h-7 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-white mb-1" style="font-family: 'Poppins';">₱{{ number_format($totalOutstanding, 2) }}</p>
                    <p class="text-sm text-zinc-500">Outstanding</p>
                    @if($totalOutstanding > 0)
                        <div class="flex items-center gap-1 mt-1">
                            <div class="w-2 h-2 bg-amber-500 rounded-full animate-pulse"></div>
                            <span class="text-xs text-amber-600 dark:text-amber-400 font-medium">Action Required</span>
                        </div>
                    @else
                        <div class="flex items-center gap-1 mt-1">
                            <div class="w-2 h-2 bg-emerald-500 rounded-full"></div>
                            <span class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">All Paid</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Total Orders Value -->
        <div class="tc-card hover-lift group">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-100 to-blue-200 dark:from-blue-900/30 dark:to-blue-800/30 flex items-center justify-center border border-blue-200 dark:border-blue-800 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-7 h-7 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-white mb-1" style="font-family: 'Poppins';">₱{{ number_format($totalOrdersValue, 2) }}</p>
                    <p class="text-sm text-zinc-500">Total Orders Value</p>
                    <div class="flex items-center gap-1 mt-1">
                        <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                        <span class="text-xs text-blue-600 dark:text-blue-400 font-medium">Lifetime</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Outstanding Payments Alert -->
    @if($outstandingPayments->count() > 0)
        <div class="tc-card border-l-4 border-l-amber-500 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900/10 dark:to-orange-900/10">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center animate-pulse">
                    <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-amber-900 dark:text-amber-100 mb-2">Outstanding Payments</h3>
                    <p class="text-sm text-amber-800 dark:text-amber-200 mb-4">You have {{ $outstandingPayments->count() }} pending payment(s) totaling ₱{{ number_format($totalOutstanding, 2) }}. Complete your payments to proceed with your orders.</p>
                    
                    <!-- Outstanding Payment Cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($outstandingPayments as $op)
                            <div class="bg-white dark:bg-zinc-800 rounded-xl p-4 border border-amber-200 dark:border-amber-800 hover-lift">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-xs font-mono font-semibold text-zinc-500 dark:text-zinc-400">{{ $op->order?->tracking_number ?? 'N/A' }}</span>
                                    <span class="tc-badge tc-badge-pending text-xs">Pending</span>
                                </div>
                                <div class="mb-4">
                                    <p class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">₱{{ number_format($op->amount, 2) }}</p>
                                    <p class="text-xs text-zinc-500">Due: {{ $op->due_date ? $op->due_date->format('M d, Y') : 'ASAP' }}</p>
                                </div>
                                <button class="w-full py-2 px-4 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white font-medium rounded-lg hover:from-emerald-600 hover:to-emerald-700 transition-all duration-300 click-feedback text-sm">
                                    Pay Now
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Filter Tabs -->
    <div class="tc-card !p-0 overflow-hidden">
        <div class="flex overflow-x-auto border-b border-zinc-100 dark:border-zinc-700 custom-scrollbar">
            @php
                $tabs = [
                    ['key' => 'all', 'label' => 'All Payments', 'icon' => 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16'],
                    ['key' => 'pending', 'label' => 'Pending', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['key' => 'paid', 'label' => 'Completed', 'icon' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['key' => 'failed', 'label' => 'Failed', 'icon' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z']
                ];
            @endphp

            @foreach($tabs as $tab)
                <button 
                    wire:click="$set('activeTab', '{{ $tab['key'] }}')"
                    class="flex-shrink-0 px-6 py-4 text-sm font-medium transition-all duration-300 relative whitespace-nowrap {{ $activeTab === $tab['key'] ? 'text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20' : 'text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800/50' }}"
                >
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $tab['icon'] }}"/>
                        </svg>
                        {{ $tab['label'] }}
                        @if(isset($paymentCounts[$tab['key']]) && $paymentCounts[$tab['key']] > 0)
                            <span class="ml-1 px-2 py-0.5 text-xs rounded-full {{ $activeTab === $tab['key'] ? 'bg-primary-200 dark:bg-primary-800 text-primary-800 dark:text-primary-200' : 'bg-zinc-100 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-400' }}">
                                {{ $paymentCounts[$tab['key']] }}
                            </span>
                        @endif
                    </span>
                    @if($activeTab === $tab['key'])
                        <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-primary-500"></div>
                    @endif
                </button>
            @endforeach
        </div>
    </div>

    <!-- Enhanced Payment List -->
    <div class="space-y-4">
        @forelse($payments as $payment)
            <div class="tc-card hover-lift interactive-card group animate-fade-in-up cursor-pointer" 
                 style="--stagger-index: {{ $loop->index }}"
                 wire:click="showPaymentDetails({{ $payment->id }})">
                
                <div class="flex items-center gap-4">
                    <!-- Payment Method Icon -->
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 border transition-all duration-300 group-hover:scale-110
                        {{ match($payment->payment_method) { 
                            'gcash' => 'bg-gradient-to-br from-blue-100 to-blue-200 dark:from-blue-900/30 dark:to-blue-800/30 border-blue-200 dark:border-blue-800', 
                            'cash' => 'bg-gradient-to-br from-emerald-100 to-emerald-200 dark:from-emerald-900/30 dark:to-emerald-800/30 border-emerald-200 dark:border-emerald-800', 
                            'bank_transfer' => 'bg-gradient-to-br from-purple-100 to-purple-200 dark:from-purple-900/30 dark:to-purple-800/30 border-purple-200 dark:border-purple-800',
                            'card' => 'bg-gradient-to-br from-indigo-100 to-indigo-200 dark:from-indigo-900/30 dark:to-indigo-800/30 border-indigo-200 dark:border-indigo-800',
                            default => 'bg-gradient-to-br from-zinc-100 to-zinc-200 dark:from-zinc-700 dark:to-zinc-600 border-zinc-200 dark:border-zinc-600' 
                        } }}">
                        @if($payment->payment_method === 'gcash')
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        @elseif($payment->payment_method === 'cash')
                            <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        @elseif($payment->payment_method === 'bank_transfer')
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                            </svg>
                        @else
                            <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                        @endif
                    </div>
                    
                    <!-- Payment Details -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-3">
                                <span class="text-xs font-mono font-semibold text-primary-600 dark:text-primary-400">{{ $payment->order?->tracking_number ?? 'N/A' }}</span>
                                <span class="tc-badge tc-badge-{{ $payment->status }} text-xs">{{ ucfirst($payment->status) }}</span>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">₱{{ number_format($payment->amount, 2) }}</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between text-sm">
                            <div class="flex items-center gap-4">
                                <span class="text-zinc-600 dark:text-zinc-400">{{ ucwords(str_replace('_', ' ', $payment->payment_method)) }}</span>
                                @if($payment->payment_date)
                                    <span class="text-zinc-500">{{ $payment->payment_date->format('M d, Y g:i A') }}</span>
                                @endif
                            </div>
                            
                            <!-- Quick Actions -->
                            <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-all duration-300">
                                @if($payment->status === 'paid')
                                    <button class="p-1.5 text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-md transition-colors click-feedback" title="Download Receipt">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </button>
                                @elseif($payment->status === 'pending')
                                    <button class="p-1.5 text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 rounded-md transition-colors click-feedback" title="Pay Now">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                    </button>
                                @endif
                                
                                <button class="p-1.5 text-zinc-400 hover:text-zinc-600 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-md transition-colors click-feedback" title="View Details">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <x-enhanced-empty-state
                icon="payments"
                title="No payments found"
                description="No payment records match your current filter. Your payment history will appear here once you place orders."
                :actions="[
                    ['type' => 'primary', 'label' => 'Browse Shops', 'onclick' => 'window.location.href=\"' . route('customer.shops') . '\"'],
                    ['type' => 'secondary', 'label' => 'View Orders', 'onclick' => 'window.location.href=\"' . route('customer.orders') . '\"']
                ]"
            />
        @endforelse
    </div>

    <!-- Payment Details Modal -->
    @if($showPaymentModal && $selectedPayment)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
            <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl w-full max-w-2xl" @click.stop>
                <div class="p-6">
                    <!-- Header -->
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-semibold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">Payment Details</h3>
                        <button 
                            wire:click="closePaymentModal"
                            class="p-2 text-zinc-400 hover:text-zinc-600 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-full transition-colors"
                        >
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Payment Info -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Order Reference</label>
                                <p class="text-lg font-mono font-bold text-primary-600 dark:text-primary-400">{{ $selectedPayment->order?->tracking_number ?? 'N/A' }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Amount</label>
                                <p class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">₱{{ number_format($selectedPayment->amount, 2) }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Payment Method</label>
                                <p class="text-lg font-semibold text-zinc-800 dark:text-zinc-200">{{ ucwords(str_replace('_', ' ', $selectedPayment->payment_method)) }}</p>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Status</label>
                                <span class="tc-badge tc-badge-{{ $selectedPayment->status }} text-lg px-3 py-1">{{ ucfirst($selectedPayment->status) }}</span>
                            </div>
                            
                            @if($selectedPayment->payment_date)
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Payment Date</label>
                                <p class="text-lg font-semibold text-zinc-800 dark:text-zinc-200">{{ $selectedPayment->payment_date->format('F d, Y g:i A') }}</p>
                            </div>
                            @endif
                            
                            @if($selectedPayment->transaction_id)
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Transaction ID</label>
                                <p class="text-sm font-mono text-zinc-600 dark:text-zinc-400">{{ $selectedPayment->transaction_id }}</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-zinc-200 dark:border-zinc-700">
                        @if($selectedPayment->status === 'paid')
                            <button class="px-6 py-2 bg-primary-500 text-white rounded-lg hover:bg-primary-600 transition-colors click-feedback">
                                Download Receipt
                            </button>
                        @elseif($selectedPayment->status === 'pending')
                            <button class="px-6 py-2 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600 transition-colors click-feedback">
                                Pay Now
                            </button>
                        @endif
                        <button 
                            wire:click="closePaymentModal"
                            class="px-6 py-2 text-zinc-600 bg-zinc-100 dark:bg-zinc-800 dark:text-zinc-300 rounded-lg hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors click-feedback"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Floating Action Button -->
    <x-floating-action-button 
        icon="plus" 
        tooltip="Payment Actions"
    >
        <!-- Sub Menu Items -->
        @if($totalOutstanding > 0)
            <button class="w-12 h-12 bg-emerald-500 text-white rounded-full shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center hover:scale-110" title="Pay Outstanding">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </button>
        @endif
        <button class="w-12 h-12 bg-blue-500 text-white rounded-full shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center hover:scale-110" title="Payment History">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v11a2 2 0 002 2h2m0-13h10a2 2 0 012 2v11a2 2 0 01-2 2H9m0-13v13"/>
            </svg>
        </button>
    </x-floating-action-button>
</div>
