<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {}; ?>

<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-primary-600 dark:text-primary-400" style="font-family: 'Poppins';">AI Settings</h1>
        <p class="text-zinc-500 dark:text-zinc-400 mt-1">Configure AI Virtual Try-On engine and measurement validation settings</p>
    </div>

    <div class="tc-card">
        <div class="flex items-center gap-4 mb-6">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-purple-100 to-purple-200 dark:from-purple-900/30 dark:to-purple-800/30 flex items-center justify-center">
                <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Virtual Try-On Engine</h2>
                <p class="text-sm text-zinc-500">AI model configuration for garment visualization</p>
            </div>
        </div>
        <div class="p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl text-sm text-amber-800 dark:text-amber-300 flex items-start gap-3">
            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p>AI Virtual Try-On configuration is managed at the infrastructure level. Contact your system developer to update API keys and model endpoints. Configuration values are stored in environment variables.</p>
        </div>
    </div>

    <div class="tc-card">
        <div class="flex items-center gap-4 mb-6">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-100 to-emerald-200 dark:from-emerald-900/30 dark:to-emerald-800/30 flex items-center justify-center">
                <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3"/>
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Measurement Validation</h2>
                <p class="text-sm text-zinc-500">Tolerance-based algorithm settings</p>
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
            @foreach([
                ['Algorithm', 'Tolerance-Based Validation', 'MV = |CM - GM|'],
                ['Validation Rule', 'MV ≤ T (Tolerance Value)', 'Passes if variance within tolerance'],
                ['Default Tolerance', '2 inches / 5 cm', 'Configurable per garment type'],
            ] as [$label, $value, $note])
            <div class="p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-xl border border-zinc-100 dark:border-zinc-700">
                <p class="text-xs text-zinc-500 font-medium mb-1">{{ $label }}</p>
                <p class="font-semibold text-zinc-900 dark:text-white">{{ $value }}</p>
                <p class="text-xs text-zinc-400 mt-1">{{ $note }}</p>
            </div>
            @endforeach
        </div>
    </div>
</div>
