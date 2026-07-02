<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $first_name = '';
    public string $last_name = '';
    public string $email = '';
    public string $contact_number = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function register(): void
    {
        $validated = $this->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'contact_number' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['name'] = $validated['first_name'] . ' ' . $validated['last_name'];
        $validated['password'] = Hash::make($validated['password']);
        $validated['role'] = 'customer';

        event(new Registered(($user = User::create($validated))));

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="flex flex-col gap-6">
    <!-- Header -->
    <div class="text-center">
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family:'Poppins'">Create your account</h1>
        <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">Join TahiConnect for a seamless tailoring experience</p>
    </div>

    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="register" class="flex flex-col gap-5">
        <!-- Name Row -->
        <div class="grid grid-cols-2 gap-3">
            <div class="space-y-1.5">
                <label class="block text-sm font-semibold text-zinc-700 dark:text-zinc-300">First Name</label>
                <input wire:model="first_name" type="text" required autofocus autocomplete="given-name" placeholder="Juan"
                    class="w-full px-4 py-3 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white placeholder-zinc-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all duration-200 text-sm" />
                @error('first_name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="space-y-1.5">
                <label class="block text-sm font-semibold text-zinc-700 dark:text-zinc-300">Last Name</label>
                <input wire:model="last_name" type="text" required autocomplete="family-name" placeholder="Dela Cruz"
                    class="w-full px-4 py-3 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white placeholder-zinc-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all duration-200 text-sm" />
                @error('last_name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <!-- Email -->
        <div class="space-y-1.5">
            <label class="block text-sm font-semibold text-zinc-700 dark:text-zinc-300">Email address</label>
            <input wire:model="email" type="email" required autocomplete="email" placeholder="email@example.com"
                class="w-full px-4 py-3 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white placeholder-zinc-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all duration-200 text-sm" />
            @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Contact Number -->
        <div class="space-y-1.5">
            <label class="block text-sm font-semibold text-zinc-700 dark:text-zinc-300">Contact Number <span class="text-zinc-400 font-normal">(optional)</span></label>
            <input wire:model="contact_number" type="tel" autocomplete="tel" placeholder="+63 9XX XXX XXXX"
                class="w-full px-4 py-3 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white placeholder-zinc-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all duration-200 text-sm" />
        </div>

        <!-- Password -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="space-y-1.5">
                <label class="block text-sm font-semibold text-zinc-700 dark:text-zinc-300">Password</label>
                <input wire:model="password" type="password" required autocomplete="new-password" placeholder="Create a password"
                    class="w-full px-4 py-3 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white placeholder-zinc-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all duration-200 text-sm" />
                @error('password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="space-y-1.5">
                <label class="block text-sm font-semibold text-zinc-700 dark:text-zinc-300">Confirm Password</label>
                <input wire:model="password_confirmation" type="password" required autocomplete="new-password" placeholder="Repeat password"
                    class="w-full px-4 py-3 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white placeholder-zinc-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all duration-200 text-sm" />
            </div>
        </div>

        <!-- Terms note -->
        <p class="text-xs text-zinc-500 dark:text-zinc-400">
            By creating an account you agree to our
            <span class="text-primary-600 dark:text-primary-400 font-medium cursor-pointer">Terms of Service</span>
            and <span class="text-primary-600 dark:text-primary-400 font-medium cursor-pointer">Privacy Policy</span>.
        </p>

        <button type="submit"
            class="w-full py-3 px-6 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-semibold rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl hover:shadow-primary-500/25 click-feedback flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            Create my account
        </button>
    </form>

    <div class="text-center text-sm text-zinc-600 dark:text-zinc-400">
        Already have an account?
        <a href="{{ route('login') }}" wire:navigate class="text-primary-600 dark:text-primary-400 font-semibold hover:text-primary-700">Sign in</a>
    </div>
</div>
