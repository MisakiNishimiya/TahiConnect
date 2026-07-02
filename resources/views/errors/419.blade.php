<x-errors.layout title="Page Expired">
    <div class="mb-8">
        <div class="relative inline-flex">
            <div class="w-40 h-40 rounded-full bg-gradient-to-br from-blue-100 to-blue-200 dark:from-blue-900/40 dark:to-blue-800/20 flex items-center justify-center shadow-xl border border-blue-200 dark:border-blue-800">
                <svg class="w-20 h-20 text-blue-400 dark:text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="absolute -top-3 -right-3 w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center shadow-lg rotate-12">
                <span class="text-white font-black text-lg" style="font-family:'Poppins'">419</span>
            </div>
        </div>
    </div>

    <h1 class="text-5xl font-black text-zinc-900 dark:text-white mb-4" style="font-family:'Poppins'">
        Page Expired
    </h1>
    <p class="text-lg text-zinc-500 dark:text-zinc-400 mb-3 leading-relaxed">
        Your session has expired for security reasons.
    </p>
    <p class="text-sm text-zinc-400 dark:text-zinc-500 mb-10">
        Please refresh the page or go back and try again.
    </p>

    <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <button onclick="window.location.reload()"
            class="px-8 py-4 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-semibold rounded-2xl transition-all duration-300 shadow-lg hover:shadow-xl hover:shadow-primary-500/25 flex items-center justify-center gap-3">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
            </svg>
            Refresh Page
        </button>
        <button onclick="window.history.back()"
            class="px-8 py-4 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 font-semibold rounded-2xl border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-all duration-300 flex items-center justify-center gap-3">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
            </svg>
            Go Back
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
