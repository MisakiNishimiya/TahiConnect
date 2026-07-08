<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Models\User;
use App\Models\ActivityLog;

new #[Layout('components.layouts.app')] class extends Component {
    public User $owner;
    public bool $showResetModal    = false;
    public bool $showReactivateMsg = false;
    public string $newPassword     = '';

    public function mount(int $id): void
    {
        $this->owner = User::where('id', $id)->where('role', 'shop_owner')->firstOrFail();
    }

    public function with(): array
    {
        return [
            'activityLogs' => ActivityLog::where('user_id', $this->owner->id)->latest()->take(10)->get(),
        ];
    }

    public function openResetModal(): void  { $this->showResetModal = true; }
    public function closeResetModal(): void { $this->showResetModal = false; $this->newPassword = ''; }

    public function resetPassword(): void
    {
        $this->validate(['newPassword' => 'required|min:8']);
        // UI-only — backend not yet implemented
        session()->flash('message', 'Password reset queued. Backend implementation pending.');
        $this->closeResetModal();
    }

    public function reactivate(): void
    {
        // UI-only — backend not yet implemented
        session()->flash('message', 'Account reactivation queued. Backend implementation pending.');
    }
}; ?>

<div class="space-y-6">
    <!-- Back + Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('superadmin.shop-owners') }}" wire:navigate
            class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200 bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-all">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Shop Owners
        </a>
    </div>

    @if(session()->has('message'))
        <div class="p-4 bg-amber-500/20 border border-amber-500/30 rounded-xl text-amber-300 text-sm">{{ session('message') }}</div>
    @endif

    <!-- Profile Card -->
    <div class="tc-card">
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-6">
            <div class="flex items-center gap-5">
                <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-amber-500 to-amber-600 flex items-center justify-center text-3xl font-bold text-white shadow-xl">
                    {{ strtoupper(substr($owner->name, 0, 1)) }}
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family:'Poppins'">{{ $owner->name }}</h1>
                    <p class="text-zinc-500 dark:text-zinc-400 text-sm mt-0.5">{{ $owner->email }}</p>
                    <div class="flex items-center gap-2 mt-2">
                        <span class="px-2.5 py-1 text-xs font-semibold bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-500/30 rounded-full">Shop Owner</span>
                        <span class="px-2.5 py-1 text-xs font-medium bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/30 rounded-full">Active</span>
                    </div>
                </div>
            </div>
            <!-- Actions -->
            <div class="flex flex-wrap gap-3">
                <button wire:click="openResetModal"
                    class="px-4 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-200 bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 border border-zinc-200 dark:border-zinc-700 rounded-xl transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"/></svg>
                    Reset Password
                </button>
                <button wire:click="reactivate"
                    class="px-4 py-2 text-sm font-medium text-red-600 dark:text-red-300 bg-red-50 dark:bg-red-500/10 hover:bg-red-100 dark:hover:bg-red-500/20 border border-red-200 dark:border-red-500/30 rounded-xl transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                    Deactivate Account
                </button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Account Details -->
        <div class="tc-card">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white mb-5" style="font-family:'Poppins'">Account Details</h2>
            <div class="space-y-4">
                @foreach([
                    ['Full Name',       $owner->name],
                    ['Email Address',   $owner->email],
                    ['Contact Number',  $owner->contact_number ?? '—'],
                    ['First Name',      $owner->first_name ?? '—'],
                    ['Last Name',       $owner->last_name  ?? '—'],
                    ['Address',         $owner->address    ?? '—'],
                    ['Registered',      $owner->created_at->format('F d, Y g:i A')],
                    ['Last Updated',    $owner->updated_at->diffForHumans()],
                ] as [$label, $value])
                <div class="flex items-start justify-between py-3 border-b border-zinc-100 dark:border-zinc-800 last:border-0 gap-4">
                    <span class="text-sm text-zinc-500 dark:text-zinc-400 shrink-0">{{ $label }}</span>
                    <span class="text-sm text-zinc-900 dark:text-zinc-200 text-right font-medium">{{ $value }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Activity Log -->
        <div class="tc-card">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white mb-5" style="font-family:'Poppins'">Recent Activity</h2>
            <div class="space-y-3">
                @forelse($activityLogs as $log)
                    <div class="flex items-start gap-3 p-3 rounded-xl bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-100 dark:border-zinc-800">
                        <div class="w-2 h-2 rounded-full bg-primary-500 mt-1.5 shrink-0"></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-200">{{ $log->action }}</p>
                            @if($log->description)
                                <p class="text-xs text-zinc-500 mt-0.5">{{ $log->description }}</p>
                            @endif
                            <p class="text-xs text-zinc-400 mt-1">{{ $log->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-10">
                        <div class="w-14 h-14 mx-auto mb-3 rounded-2xl bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center">
                            <svg class="w-7 h-7 text-zinc-400 dark:text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                        </div>
                        <p class="text-sm text-zinc-500">No activity logged yet</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Linked Shop Info -->
    <div class="tc-card">
        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white mb-5" style="font-family:'Poppins'">Linked Shop Instance</h2>
        @if($owner->shop)
            <div class="flex items-center gap-4 p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-xl border border-zinc-200 dark:border-zinc-700">
                <div class="w-12 h-12 rounded-xl bg-primary-100 dark:bg-primary-500/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18"/>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-zinc-900 dark:text-zinc-200">{{ $owner->shop->name }}</p>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $owner->shop->address }}{{ $owner->shop->barangay ? ', '.$owner->shop->barangay : '' }}</p>
                    <p class="text-xs text-zinc-400 mt-0.5">{{ $owner->shop->email ?? 'No email set' }} · {{ $owner->shop->contact_number ?? 'No contact' }}</p>
                </div>
            </div>
        @else
            <div class="p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-xl border border-dashed border-zinc-300 dark:border-zinc-700 text-center text-zinc-500 text-sm">
                No shop linked to this account.
            </div>
        @endif
    </div>

    <!-- Reset Password Modal -->
    @if($showResetModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-2xl shadow-2xl w-full max-w-md" @click.stop>
            <div class="p-6">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Reset Password</h3>
                    <button wire:click="closeResetModal" class="p-2 text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-full transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-5">Set a new password for <strong class="text-zinc-900 dark:text-zinc-200">{{ $owner->name }}</strong>. They will need to use this password on their next login.</p>
                <form wire:submit="resetPassword" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">New Password *</label>
                        <input wire:model="newPassword" type="password" placeholder="Minimum 8 characters"
                            class="w-full px-4 py-2.5 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl text-zinc-900 dark:text-zinc-200 text-sm focus:ring-2 focus:ring-primary-500" />
                        @error('newPassword') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="button" wire:click="closeResetModal"
                            class="flex-1 px-4 py-2.5 text-sm font-medium text-zinc-600 dark:text-zinc-300 bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 rounded-xl transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                            class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-primary-500 hover:bg-primary-600 rounded-xl transition-colors">
                            Reset Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
