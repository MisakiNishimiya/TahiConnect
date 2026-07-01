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
    <x-auth-header title="Create your account" description="Join TahiConnect for a seamless tailoring experience" />

    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="register" class="flex flex-col gap-6">
        <!-- Name -->
        <div class="grid grid-cols-2 gap-4">
            <flux:input wire:model="first_name" id="first_name" label="{{ __('First Name') }}" type="text" required autofocus autocomplete="given-name" placeholder="Juan" />
            <flux:input wire:model="last_name" id="last_name" label="{{ __('Last Name') }}" type="text" required autocomplete="family-name" placeholder="Dela Cruz" />
        </div>

        <!-- Email -->
        <flux:input wire:model="email" id="email" label="{{ __('Email address') }}" type="email" required autocomplete="email" placeholder="email@example.com" />

        <!-- Contact Number -->
        <flux:input wire:model="contact_number" id="contact_number" label="{{ __('Contact Number') }}" type="tel" autocomplete="tel" placeholder="+63 9XX XXX XXXX" />

        <!-- Password -->
        <flux:input wire:model="password" id="password" label="{{ __('Password') }}" type="password" required autocomplete="new-password" placeholder="Password" />

        <!-- Confirm Password -->
        <flux:input wire:model="password_confirmation" id="password_confirmation" label="{{ __('Confirm password') }}" type="password" required autocomplete="new-password" placeholder="Confirm password" />

        <div class="flex items-center justify-end">
            <flux:button type="submit" variant="primary" class="w-full !bg-primary-500 hover:!bg-primary-600">
                {{ __('Create account') }}
            </flux:button>
        </div>
    </form>

    <div class="space-x-1 text-center text-sm text-zinc-600 dark:text-zinc-400">
        Already have an account?
        <x-text-link href="{{ route('login') }}">Log in</x-text-link>
    </div>
</div>
