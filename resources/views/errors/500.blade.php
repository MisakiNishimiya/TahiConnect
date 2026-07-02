<x-errors.layout title="Server Error">
    <!-- Illustration -->
    <div class="mb-8">
        <div class="relative inline-flex">
            <div class="w-40 h-40 rounded-full bg-gradient-to-br from-amber-100 to-orange-200 dark:from-amber-900/40 dark:to-orange-800/20 flex items-center justify-center shadow-xl border border-amber-200 dark:border-amber-800">
                <svg class="w-20 h-20 text-amber-400 dark:text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437"/>
                </svg>
            </div>
            <div class="absolute -top-3 -right-3 w-16 h-16 bg-gradient-to-br from-amber-500 to-orange-500 rounded-2xl flex items-center justify-center shadow-lg rotate-12">
                <span class="text-white font-black text-lg" style="font-family:'Poppins'">500</span>
            </div>
        </div>
    </div>

    <h1 class="text-5xl font-black text-zinc-900 dark:text-white mb-4" style="font-family:'Poppins'">
        Server Error
    </h1>
    <p class="text-lg text-zinc-500 dark:text-zinc-400 mb-3 leading-relaxed">
        Something went wrong on our end. We're working on it.
    </p>
    <p class="text-sm text-zinc-400 dark:text-zinc-500 mb-10">
        Please try again in a few minutes. If the problem persists, contact support.
    </p>

    <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <a href="{{ url('/') }}"
            class="px-8 py-4 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-semibold rounded-2xl transition-all duration-300 shadow-lg hover:shadow-xl hover:shadow-primary-500/25 flex items-center justify-center gap-3">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75"/>
            </svg>
            Back to Home
        </a>
        <button onclick="window.location.reload()"
            class="px-8 py-4 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 font-semibold rounded-2xl border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-all duration-300 flex items-center justify-center gap-3">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
            </svg>
            Try Again
        </button>
    </div>

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
