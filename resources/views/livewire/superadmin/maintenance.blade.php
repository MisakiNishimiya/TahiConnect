<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
    public bool $maintenanceMode = false;
    public bool $showConfirmModal = false;
    public string $confirmAction  = '';

    public function openConfirm(string $action): void
    {
        $this->confirmAction  = $action;
        $this->showConfirmModal = true;
    }

    public function closeConfirm(): void
    {
        $this->showConfirmModal = false;
        $this->confirmAction   = '';
    }

    public function executeAction(): void
    {
        // UI-only — backend not yet implemented
        session()->flash('message', "Action '{$this->confirmAction}' triggered. Backend implementation pending.");
        $this->closeConfirm();
    }
}; ?>

<div class="space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-3xl font-bold text-primary-600 dark:text-primary-400" style="font-family: 'Poppins';">Backup & Maintenance</h1>
        <p class="text-zinc-500 dark:text-zinc-400 mt-1">System maintenance, backups, and cache management</p>
    </div>

    @if(session()->has('message'))
        <div class="p-4 bg-amber-500/20 border border-amber-500/30 rounded-xl text-amber-300 text-sm flex items-center gap-3">
            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('message') }}
        </div>
    @endif

    <!-- Maintenance Mode -->
    <div class="tc-card">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl {{ $maintenanceMode ? 'bg-amber-100 dark:bg-amber-500/20 border-amber-200 dark:border-amber-500/30' : 'bg-emerald-100 dark:bg-emerald-500/20 border-emerald-200 dark:border-emerald-500/30' }} border flex items-center justify-center shrink-0">
                    <svg class="w-7 h-7 {{ $maintenanceMode ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-white" style="font-family:'Poppins'">Maintenance Mode</h2>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-0.5">
                        {{ $maintenanceMode ? 'System is currently in maintenance mode. Only Super Admins can access the platform.' : 'System is live and accessible to all users.' }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-4 shrink-0">
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full {{ $maintenanceMode ? 'bg-amber-400 animate-pulse' : 'bg-emerald-400' }}"></div>
                    <span class="text-sm font-medium {{ $maintenanceMode ? 'text-amber-600 dark:text-amber-300' : 'text-emerald-600 dark:text-emerald-300' }}">
                        {{ $maintenanceMode ? 'Maintenance' : 'Online' }}
                    </span>
                </div>
                <button wire:click="openConfirm('{{ $maintenanceMode ? 'disable_maintenance' : 'enable_maintenance' }}')"
                    class="px-5 py-2.5 text-sm font-medium rounded-xl transition-colors {{ $maintenanceMode ? 'bg-emerald-600 hover:bg-emerald-500 text-white' : 'bg-amber-600 hover:bg-amber-500 text-white' }}">
                    {{ $maintenanceMode ? 'Disable Maintenance' : 'Enable Maintenance' }}
                </button>
            </div>
        </div>
        <div class="mt-5 p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-xl border border-zinc-200 dark:border-zinc-700 text-xs text-zinc-500 dark:text-zinc-400">
            <strong class="text-zinc-700 dark:text-zinc-300">Note:</strong> Enabling maintenance mode will display a maintenance page to all customers and staff. Shop owners and super admins will still have access. All active sessions will be preserved.
        </div>
    </div>

    <!-- Backup Actions -->
    <div class="tc-card">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-500/20 flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white" style="font-family:'Poppins'">Database Backup</h2>
                <p class="text-xs text-zinc-500 dark:text-zinc-500">Last backup: <span class="text-zinc-700 dark:text-zinc-300">Not configured</span></p>
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
            <button wire:click="openConfirm('backup_database')"
                class="flex items-center gap-3 p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800 hover:bg-zinc-100 dark:hover:bg-zinc-700 border border-zinc-200 dark:border-zinc-700 hover:border-zinc-300 dark:hover:border-zinc-600 transition-all group">
                <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-500/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                </div>
                <div class="text-left">
                    <p class="font-medium text-zinc-900 dark:text-zinc-200 text-sm">Backup Database Now</p>
                    <p class="text-xs text-zinc-500">Export a full SQL dump</p>
                </div>
            </button>
            <button wire:click="openConfirm('backup_files')"
                class="flex items-center gap-3 p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800 hover:bg-zinc-100 dark:hover:bg-zinc-700 border border-zinc-200 dark:border-zinc-700 hover:border-zinc-300 dark:hover:border-zinc-600 transition-all group">
                <div class="w-10 h-10 rounded-lg bg-purple-100 dark:bg-purple-500/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                </div>
                <div class="text-left">
                    <p class="font-medium text-zinc-900 dark:text-zinc-200 text-sm">Backup Uploaded Files</p>
                    <p class="text-xs text-zinc-500">Archive storage directory</p>
                </div>
            </button>
        </div>
        <div class="p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-xl border border-dashed border-zinc-300 dark:border-zinc-600 text-center">
            <p class="text-xs text-zinc-400">Automated backup schedule will be configurable after backend implementation.</p>
        </div>
    </div>

    <!-- Cache Management -->
    <div class="tc-card">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-500/20 flex items-center justify-center">
                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
            </div>
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white" style="font-family:'Poppins'">Cache Management</h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            @foreach([
                ['Clear Config Cache', 'clear_config',  'Reloads configuration files', 'bg-amber-100 dark:bg-amber-500/20',  'text-amber-600 dark:text-amber-400'],
                ['Clear View Cache',   'clear_views',   'Recompiles Blade templates',  'bg-blue-100 dark:bg-blue-500/20',    'text-blue-600 dark:text-blue-400'],
                ['Clear Route Cache',  'clear_routes',  'Refreshes route definitions', 'bg-purple-100 dark:bg-purple-500/20','text-purple-600 dark:text-purple-400'],
                ['Clear All Caches',   'clear_all',     'Full cache reset',             'bg-red-100 dark:bg-red-500/20',      'text-red-600 dark:text-red-400'],
            ] as [$label, $action, $desc, $iconBg, $iconColor])
            <button wire:click="openConfirm('{{ $action }}')"
                class="text-left p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800 hover:bg-zinc-100 dark:hover:bg-zinc-700 border border-zinc-200 dark:border-zinc-700 hover:border-zinc-300 dark:hover:border-zinc-600 transition-all group">
                <div class="w-9 h-9 rounded-lg {{ $iconBg }} flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                    <svg class="w-4 h-4 {{ $iconColor }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                </div>
                <p class="font-medium text-zinc-900 dark:text-zinc-200 text-sm mb-0.5">{{ $label }}</p>
                <p class="text-xs text-zinc-500">{{ $desc }}</p>
            </button>
            @endforeach
        </div>
    </div>

    <!-- Confirm Modal -->
    @if($showConfirmModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-2xl shadow-2xl w-full max-w-md" @click.stop>
            <div class="p-6">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 rounded-full bg-amber-100 dark:bg-amber-500/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Confirm Action</h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">This action will affect the live system.</p>
                    </div>
                </div>
                <div class="p-3 bg-zinc-100 dark:bg-zinc-800 rounded-xl mb-5 text-sm font-mono text-zinc-700 dark:text-zinc-300 text-center">{{ $confirmAction }}</div>
                <div class="flex gap-3">
                    <button wire:click="closeConfirm" class="flex-1 px-4 py-2.5 text-sm font-medium text-zinc-600 dark:text-zinc-300 bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 rounded-xl transition-colors">Cancel</button>
                    <button wire:click="executeAction" class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-amber-600 hover:bg-amber-500 rounded-xl transition-colors">Proceed</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
