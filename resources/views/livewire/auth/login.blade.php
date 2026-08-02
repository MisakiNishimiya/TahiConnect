<?php

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    public function login(): void
    {
        $this->validate();
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }

    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }
        event(new Lockout(request()));
        $seconds = RateLimiter::availableIn($this->throttleKey());
        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }
}; ?>

<div class="flex flex-col gap-6">
    <!-- Header -->
    <div class="text-center">
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family:'Poppins'">Welcome back</h1>
        <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">Sign in to your TahiConnect account</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="login" class="flex flex-col gap-5">
        <!-- Email -->
        <div class="space-y-1.5">
            <label class="block text-sm font-semibold text-zinc-700 dark:text-zinc-300">Email address</label>
            <input wire:model="email" type="email" name="email" required autofocus autocomplete="email"
                placeholder="email@example.com"
                class="w-full px-4 py-3 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white placeholder-zinc-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all duration-200 text-sm" />
            @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Password -->
        <div class="space-y-1.5">
            <div class="flex items-center justify-between">
                <label class="block text-sm font-semibold text-zinc-700 dark:text-zinc-300">Password</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" wire:navigate class="text-xs text-primary-600 dark:text-primary-400 hover:text-primary-700 font-medium">Forgot password?</a>
                @endif
            </div>
            <div class="relative" x-data="{ show: false }">
                <input wire:model="password" :type="show ? 'text' : 'password'" name="password" required autocomplete="current-password"
                    placeholder="Your password"
                    class="w-full px-4 py-3 pr-12 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white placeholder-zinc-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all duration-200 text-sm" />
                <button type="button" @click="show = !show"
                    class="absolute inset-y-0 right-0 flex items-center px-3 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300 focus:outline-none"
                    :aria-label="show ? 'Hide password' : 'Show password'">
                    <!-- Eye icon (password hidden) -->
                    <svg x-show="!show" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <!-- Eye-off icon (password visible) -->
                    <svg x-show="show" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.97 9.97 0 012.063-3.362M6.53 6.53A9.97 9.97 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.97 9.97 0 01-4.073 5.14M15 12a3 3 0 00-3-3m0 0a3 3 0 00-2.121.879M3 3l18 18"/>
                    </svg>
                </button>
            </div>
            @error('password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Remember Me -->
        <label class="flex items-center gap-3 cursor-pointer">
            <input wire:model="remember" type="checkbox" class="w-4 h-4 rounded border-zinc-300 text-primary-600 focus:ring-primary-500" />
            <span class="text-sm text-zinc-600 dark:text-zinc-400">Remember me for 30 days</span>
        </label>

        <button type="submit"
            class="w-full py-3 px-6 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-semibold rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl hover:shadow-primary-500/25 click-feedback flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
            Sign in to your account
        </button>
    </form>

    <div class="text-center text-sm text-zinc-600 dark:text-zinc-400">
        Don't have an account?
        <a href="{{ route('register') }}" wire:navigate class="text-primary-600 dark:text-primary-400 font-semibold hover:text-primary-700">Create one free</a>
    </div>
</div>
