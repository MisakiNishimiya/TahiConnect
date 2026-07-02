<x-errors.layout title="Access Denied">
    <!-- Illustration -->
    <div class="mb-8">
        <div class="relative inline-flex">
            <div class="w-40 h-40 rounded-full bg-gradient-to-br from-red-100 to-red-200 dark:from-red-900/40 dark:to-red-800/20 flex items-center justify-center shadow-xl border border-red-200 dark:border-red-800">
                <svg class="w-20 h-20 text-red-400 dark:text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                </svg>
            </div>
            <div class="absolute -top-3 -right-3 w-16 h-16 bg-gradient-to-br from-red-500 to-red-600 rounded-2xl flex items-center justify-center shadow-lg rotate-12">
                <span class="text-white font-black text-lg" style="font-family:'Poppins'">403</span>
            </div>
        </div>
    </div>

    <h1 class="text-5xl font-black text-zinc-900 dark:text-white mb-4" style="font-family:'Poppins'">
        Access Denied
    </h1>
    <p class="text-lg text-zinc-500 dark:text-zinc-400 mb-3 leading-relaxed">
        You don't have permission to view this page.
    </p>
    <p class="text-sm text-zinc-400 dark:text-zinc-500 mb-10">
        {{ $exception?->getMessage() ?: 'Please contact your administrator if you believe this is a mistake.' }}
    </p>

    <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <a href="{{ url('/') }}"
            class="px-8 py-4 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-semibold rounded-2xl transition-all duration-300 shadow-lg hover:shadow-xl hover:shadow-primary-500/25 flex items-center justify-center gap-3">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75"/>
            </svg>
            Back to Home
        </a>
        <button onclick="window.history.back()"
            class="px-8 py-4 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 font-semibold rounded-2xl border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-all duration-300 flex items-center justify-center gap-3">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
            </svg>
            Go Back
        </button>
    </div>

    @guest
        <div class="mt-8 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-2xl">
            <p class="text-sm text-amber-800 dark:text-amber-200">
                <span class="font-semibold">Not logged in?</span>
                <a href="{{ route('login') }}" class="underline hover:no-underline ml-1">Sign in to access this page</a>.
            </p>
        </div>
    @endguest

    <div class="mt-10 flex items-center justify-center gap-2 opacity-50">
        <div class="w-6 h-6 rounded-lg bg-primary-500 flex items-center justify-center">
            <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none">
                <path d="M6 3C6 3 4.5 5 4.5 7.5C4.5 9.5 6 11 6 11C6 11 7.5 9.5 7.5 7.5C7.5 5 6 3 6 3Z" fill="currentColor"/>
                <path d="M18 3C18 3 16.5 5 16.5 7.5C16.5 9.5 18 11 18 11C18 11 19.5 9.5 19.5 7.5C19.5 5 18 3 18 3Z" fill="currentColor"/>
                <path d="M6 11L12 21L18 11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <span class="text-sm font-semibold text-zinc-600 dark:text-zinc-400" style="font-family:'Poppins'">TahiConnect</span>
    </div>
</x-errors.layout>
