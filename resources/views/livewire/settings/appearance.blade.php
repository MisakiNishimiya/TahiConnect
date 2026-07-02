<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<div class="flex flex-col items-start">
    @include('partials.settings-heading')

    <x-settings.layout heading="Appearance" subheading="Customize how TahiConnect looks on your device">
        <div class="mt-6 space-y-6">
            <!-- Theme Selector -->
            <div>
                <p class="text-sm font-semibold text-zinc-700 dark:text-zinc-300 mb-4">Color Theme</p>
                <flux:radio.group x-data variant="segmented" x-model="$flux.appearance" class="w-full sm:w-auto">
                    <flux:radio value="light" icon="sun">Light</flux:radio>
                    <flux:radio value="dark" icon="moon">Dark</flux:radio>
                    <flux:radio value="system" icon="computer-desktop">System</flux:radio>
                </flux:radio.group>
            </div>

            <!-- Preview Card -->
            <div class="p-5 bg-zinc-50 dark:bg-zinc-800 rounded-2xl border border-zinc-200 dark:border-zinc-700">
                <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-3">Preview</p>
                <div class="space-y-3">
                    <div class="flex items-center gap-3 p-3 bg-white dark:bg-zinc-900 rounded-xl border border-zinc-100 dark:border-zinc-700">
                        <div class="w-8 h-8 rounded-lg bg-primary-500 flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18"/></svg>
                        </div>
                        <div class="flex-1">
                            <div class="h-2.5 bg-zinc-200 dark:bg-zinc-700 rounded-full w-24 mb-1.5"></div>
                            <div class="h-2 bg-zinc-100 dark:bg-zinc-800 rounded-full w-16"></div>
                        </div>
                        <span class="tc-badge tc-badge-completed text-xs">Active</span>
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <div class="h-8 bg-primary-100 dark:bg-primary-900/30 rounded-lg border border-primary-200 dark:border-primary-800"></div>
                        <div class="h-8 bg-secondary-100 dark:bg-secondary-900/30 rounded-lg border border-secondary-200 dark:border-secondary-800"></div>
                        <div class="h-8 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg border border-emerald-200 dark:border-emerald-800"></div>
                    </div>
                </div>
            </div>

            <p class="text-xs text-zinc-400 dark:text-zinc-500">
                System mode follows your device's OS appearance setting automatically.
            </p>
        </div>
    </x-settings.layout>
</div>
