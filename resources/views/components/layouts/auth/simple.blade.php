<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white antialiased dark:bg-zinc-900">
        <div class="flex min-h-svh">
            <!-- Left Branding Panel -->
            <div class="hidden lg:flex lg:w-1/2 bg-primary-500 relative overflow-hidden flex-col items-center justify-center p-12 text-white">
                <!-- Decorative pattern -->
                <div class="absolute inset-0 opacity-10">
                    <svg width="100%" height="100%" xmlns="http://www.w3.org/2000/svg">
                        <pattern id="thread" x="0" y="0" width="60" height="60" patternUnits="userSpaceOnUse">
                            <path d="M0 30 Q15 0 30 30 Q45 60 60 30" stroke="white" fill="none" stroke-width="0.5"/>
                            <circle cx="30" cy="30" r="2" fill="white" opacity="0.3"/>
                        </pattern>
                        <rect width="100%" height="100%" fill="url(#thread)"/>
                    </svg>
                </div>

                <div class="relative z-10 max-w-md text-center">
                    <!-- Logo -->
                    <div class="flex justify-center mb-8">
                        <div class="flex items-center justify-center w-16 h-16 rounded-2xl bg-white/20 backdrop-blur-sm">
                            <svg class="w-10 h-10 text-white" viewBox="0 0 24 24" fill="none">
                                <path d="M6 3C6 3 4.5 5 4.5 7.5C4.5 9.5 6 11 6 11C6 11 7.5 9.5 7.5 7.5C7.5 5 6 3 6 3Z" fill="currentColor" opacity="0.8"/>
                                <path d="M18 3C18 3 16.5 5 16.5 7.5C16.5 9.5 18 11 18 11C18 11 19.5 9.5 19.5 7.5C19.5 5 18 3 18 3Z" fill="currentColor" opacity="0.8"/>
                                <path d="M6 11L12 21L18 11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <circle cx="12" cy="14" r="1.5" fill="currentColor"/>
                                <line x1="12" y1="15.5" x2="12" y2="19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                        </div>
                    </div>

                    <h1 class="text-3xl font-bold mb-3" style="font-family: 'Poppins', sans-serif;">TahiConnect</h1>
                    <p class="text-lg text-primary-100 mb-10">AI-Powered Tailoring Service Management</p>

                    <!-- Feature list -->
                    <div class="space-y-4 text-left">
                        <div class="flex items-center gap-3 bg-white/10 rounded-xl px-4 py-3 backdrop-blur-sm">
                            <svg class="w-6 h-6 shrink-0 text-secondary-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
                            <span class="text-sm">AI-Powered Virtual Try-On</span>
                        </div>
                        <div class="flex items-center gap-3 bg-white/10 rounded-xl px-4 py-3 backdrop-blur-sm">
                            <svg class="w-6 h-6 shrink-0 text-secondary-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                            <span class="text-sm">Real-Time Order Tracking</span>
                        </div>
                        <div class="flex items-center gap-3 bg-white/10 rounded-xl px-4 py-3 backdrop-blur-sm">
                            <svg class="w-6 h-6 shrink-0 text-secondary-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5"/></svg>
                            <span class="text-sm">Smart Measurement Management</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Form Panel -->
            <div class="flex w-full lg:w-1/2 flex-col items-center justify-center gap-6 p-6 md:p-10 bg-white dark:bg-zinc-900">
                <div class="flex w-full max-w-sm flex-col gap-2">
                    <!-- Mobile logo -->
                    <a href="{{ route('home') }}" class="flex flex-col items-center gap-2 font-medium lg:hidden mb-4" wire:navigate>
                        <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary-500">
                            <x-app-logo-icon class="size-7 fill-current text-white" />
                        </span>
                        <span class="text-lg font-semibold" style="font-family: 'Poppins', sans-serif;">TahiConnect</span>
                    </a>
                    <div class="flex flex-col gap-6">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
