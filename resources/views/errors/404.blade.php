<x-errors.layout title="Page Not Found">
    <!-- Illustration -->
    <div class="mb-8">
        <div class="relative inline-flex">
            <div class="w-40 h-40 rounded-full bg-gradient-to-br from-primary-100 to-primary-200 dark:from-primary-900/40 dark:to-primary-800/20 flex items-center justify-center shadow-xl border border-primary-200 dark:border-primary-800">
                <svg class="w-20 h-20 text-primary-400 dark:text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                </svg>
            </div>
            <!-- Error code badge -->
            <div class="absolute -top-3 -right-3 w-16 h-16 bg-gradient-to-br from-primary-500 to-primary-600 rounded-2xl flex items-center justify-center shadow-lg rotate-12">
                <span class="text-white font-black text-lg" style="font-family:'Poppins'">404</span>
            </div>
        </div>
    </div>

    <!-- Content -->
    <h1 class="text-5xl font-black text-zinc-900 dark:text-white mb-4" style="font-family:'Poppins'">
        Page Not Found
    </h1>
    <p class="text-lg text-zinc-500 dark:text-zinc-400 mb-3 leading-relaxed">
        The page you're looking for doesn't exist or has been moved.
    </p>
    <p class="text-sm text-zinc-400 dark:text-zinc-500 mb-10">
        Maybe check the URL or head back home.
    </p>

    <!-- Actions -->
    <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <a href="{{ url('/') }}"
            class="px-8 py-4 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-semibold rounded-2xl transition-all duration-300 shadow-lg hover:shadow-xl hover:shadow-primary-500/25 flex items-center justify-center gap-3">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>
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

    <!-- Quick nav links -->
    <div class="mt-12 pt-8 border-t border-zinc-200 dark:border-zinc-700">
        <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-4">Or try one of these pages:</p>
        <div class="flex flex-wrap gap-2 justify-center">
            @auth
                <a href="{{ route('customer.dashboard') }}" class="px-4 py-2 text-sm text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20 rounded-xl hover:bg-primary-100 dark:hover:bg-primary-900/30 transition-colors font-medium">Dashboard</a>
                <a href="{{ route('customer.shops') }}" class="px-4 py-2 text-sm text-zinc-600 dark:text-zinc-400 bg-zinc-100 dark:bg-zinc-800 rounded-xl hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors font-medium">Find Tailors</a>
                <a href="{{ route('customer.orders') }}" class="px-4 py-2 text-sm text-zinc-600 dark:text-zinc-400 bg-zinc-100 dark:bg-zinc-800 rounded-xl hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors font-medium">My Orders</a>
            @else
                <a href="{{ route('login') }}" class="px-4 py-2 text-sm text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20 rounded-xl hover:bg-primary-100 dark:hover:bg-primary-900/30 transition-colors font-medium">Sign In</a>
                <a href="{{ route('register') }}" class="px-4 py-2 text-sm text-zinc-600 dark:text-zinc-400 bg-zinc-100 dark:bg-zinc-800 rounded-xl hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors font-medium">Create Account</a>
            @endauth
        </div>
    </div>

    <!-- Brand -->
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
