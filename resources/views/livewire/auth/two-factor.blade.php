<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $code = '';
    public bool $useRecoveryCode = false;
    public string $recoveryCode = '';

    public function verify(): void
    {
        // UI only — backend not yet implemented
        $this->addError('code', 'MFA verification is not yet connected to a backend.');
    }

    public function toggleRecoveryCode(): void
    {
        $this->useRecoveryCode = ! $this->useRecoveryCode;
        $this->reset('code', 'recoveryCode');
        $this->resetErrorBag();
    }
}; ?>

<div class="flex flex-col gap-6">
    <!-- Icon + Header -->
    <div class="text-center">
        <div class="w-20 h-20 bg-gradient-to-br from-primary-100 to-primary-200 dark:from-primary-900/30 dark:to-primary-800/30 rounded-3xl flex items-center justify-center mx-auto mb-5 shadow-lg">
            <svg class="w-10 h-10 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
            </svg>
        </div>

        @if (! $useRecoveryCode)
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family:'Poppins'">Two-factor authentication</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-2 max-w-sm mx-auto leading-relaxed">
                Open your authenticator app and enter the 6-digit code to continue.
            </p>
        @else
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family:'Poppins'">Use a recovery code</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-2 max-w-sm mx-auto leading-relaxed">
                Enter one of your recovery codes to regain access to your account.
            </p>
        @endif
    </div>

    <form wire:submit="verify" class="flex flex-col gap-5">

        @if (! $useRecoveryCode)
            <!-- OTP Input -->
            <div class="space-y-1.5">
                <label class="block text-sm font-semibold text-zinc-700 dark:text-zinc-300">Authentication code</label>

                <!-- Single input -->
                <input
                    wire:model="code"
                    type="text"
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    maxlength="6"
                    placeholder="000000"
                    autofocus
                    class="w-full px-4 py-3 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white placeholder-zinc-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all duration-200 text-sm text-center tracking-[0.5em] font-mono text-lg"
                />
                @error('code') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
        @else
            <!-- Recovery Code Input -->
            <div class="space-y-1.5">
                <label class="block text-sm font-semibold text-zinc-700 dark:text-zinc-300">Recovery code</label>
                <input
                    wire:model="recoveryCode"
                    type="text"
                    autocomplete="off"
                    placeholder="xxxx-xxxx-xxxx"
                    autofocus
                    class="w-full px-4 py-3 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white placeholder-zinc-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all duration-200 text-sm font-mono"
                />
                @error('recoveryCode') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Recovery code notice -->
            <div class="flex items-start gap-3 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl">
                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                </svg>
                <p class="text-xs text-amber-800 dark:text-amber-200 leading-relaxed">
                    Each recovery code can only be used once. Make sure to generate new ones after using this.
                </p>
            </div>
        @endif

        <!-- Submit -->
        <button type="submit"
            class="w-full py-3 px-6 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-semibold rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl hover:shadow-primary-500/25 click-feedback flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
            </svg>
            Verify and continue
        </button>
    </form>

    <!-- Toggle between code / recovery -->
    <div class="text-center">
        <button wire:click="toggleRecoveryCode" type="button"
            class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 font-medium transition-colors">
            @if (! $useRecoveryCode)
                Lost access to your app? Use a recovery code
            @else
                Back to authenticator app
            @endif
        </button>
    </div>
</div>
