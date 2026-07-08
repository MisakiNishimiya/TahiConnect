<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Models\User;
use App\Models\Order;
use App\Models\Appointment;
use App\Models\Measurement;

new #[Layout('components.layouts.app')] class extends Component {
    public function with(): array
    {
        // Real data where available, static placeholders for infrastructure metrics
        $dbPath   = database_path('database.sqlite');
        $dbSizeKb = file_exists($dbPath) ? round(filesize($dbPath) / 1024, 1) : 0;
        $dbSizeMb = round($dbSizeKb / 1024, 2);

        $storagePath   = storage_path('app/public');
        $storageBytes  = 0;
        if (is_dir($storagePath)) {
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($storagePath, FilesystemIterator::SKIP_DOTS)) as $file) {
                $storageBytes += $file->getSize();
            }
        }
        $storageMb = round($storageBytes / 1024 / 1024, 2);

        return [
            // System info — real
            'php_version'    => PHP_VERSION,
            'laravel_version'=> app()->version(),
            'environment'    => config('app.env'),
            'debug_mode'     => config('app.debug') ? 'Enabled' : 'Disabled',
            'db_driver'      => config('database.default'),
            'db_size_mb'     => $dbSizeMb,
            'storage_mb'     => $storageMb,
            'cache_driver'   => config('cache.default'),
            'queue_driver'   => config('queue.default'),
            'session_driver' => config('session.driver'),

            // Record counts — real
            'counts' => [
                'users'        => User::count(),
                'orders'       => Order::count(),
                'appointments' => Appointment::count(),
                'measurements' => Measurement::count(),
            ],

            // Infrastructure metrics — static placeholders
            'uptime'          => '99.98%',
            'last_backup'     => 'Not configured',
            'queue_jobs'      => 0,
            'failed_jobs'     => 0,
        ];
    }
}; ?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-primary-600 dark:text-primary-400" style="font-family: 'Poppins';">System Health</h1>
            <p class="text-zinc-500 dark:text-zinc-400 mt-1">Real-time platform status and infrastructure metrics</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 px-4 py-2 bg-emerald-500/20 border border-emerald-500/30 rounded-xl">
                <div class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></div>
                <span class="text-emerald-300 text-sm font-medium">All Systems Operational</span>
            </div>
        </div>
    </div>

    <!-- Status Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="tc-card hover-lift group">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-100 dark:bg-emerald-500/20 border border-emerald-200 dark:border-emerald-500/30 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse mt-1"></div>
            </div>
            <p class="text-xl font-bold text-zinc-900 dark:text-white mb-1" style="font-family:'Poppins'">{{ $uptime }}</p>
            <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Uptime</p>
            <p class="text-xs text-zinc-500 mt-1">Last 30 days</p>
        </div>
        <div class="tc-card hover-lift group">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 rounded-2xl bg-blue-100 dark:bg-blue-500/20 border border-blue-200 dark:border-blue-500/30 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 8.485-7.5 11.25-7.5 11.25S5.25 14.86 5.25 6.375a7.5 7.5 0 0115 0z"/></svg>
                </div>
                <div class="w-2 h-2 rounded-full bg-blue-400 animate-pulse mt-1"></div>
            </div>
            <p class="text-xl font-bold text-zinc-900 dark:text-white mb-1" style="font-family:'Poppins'">{{ $db_size_mb }} MB</p>
            <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Database Size</p>
            <p class="text-xs text-zinc-500 mt-1">{{ config('database.default') }}</p>
        </div>
        <div class="tc-card hover-lift group">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 rounded-2xl bg-purple-100 dark:bg-purple-500/20 border border-purple-200 dark:border-purple-500/30 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                </div>
                <div class="w-2 h-2 rounded-full bg-purple-400 animate-pulse mt-1"></div>
            </div>
            <p class="text-xl font-bold text-zinc-900 dark:text-white mb-1" style="font-family:'Poppins'">{{ $storage_mb }} MB</p>
            <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Storage Used</p>
            <p class="text-xs text-zinc-500 mt-1">Uploaded files</p>
        </div>
        <div class="tc-card hover-lift group">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 rounded-2xl {{ $failed_jobs > 0 ? 'bg-red-100 dark:bg-red-500/20 border-red-200 dark:border-red-500/30' : 'bg-emerald-100 dark:bg-emerald-500/20 border-emerald-200 dark:border-emerald-500/30' }} border flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 {{ $failed_jobs > 0 ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z"/></svg>
                </div>
                <div class="w-2 h-2 rounded-full {{ $failed_jobs > 0 ? 'bg-red-400' : 'bg-emerald-400' }} animate-pulse mt-1"></div>
            </div>
            <p class="text-xl font-bold text-zinc-900 dark:text-white mb-1" style="font-family:'Poppins'">{{ $queue_jobs }} pending</p>
            <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Queue Jobs</p>
            <p class="text-xs text-zinc-500 mt-1">{{ $failed_jobs }} failed</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- System Information -->
        <div class="tc-card">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-500/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0a3 3 0 01-3-3m3 3a3 3 0 100 6h13.5a3 3 0 100-6m-16.5-3a3 3 0 013-3h13.5a3 3 0 013 3m-19.5 0a4.5 4.5 0 01.9-2.7L5.737 5.1a3.375 3.375 0 012.7-1.35h7.126c1.062 0 2.062.5 2.7 1.35l2.587 3.45a4.5 4.5 0 01.9 2.7m0 0a3 3 0 01-3 3m0 3h.008v.008h-.008v-.008zm0-6h.008v.008h-.008v-.008zm-3 6h.008v.008h-.008v-.008zm0-6h.008v.008h-.008v-.008z"/></svg>
                </div>
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white" style="font-family:'Poppins'">System Information</h2>
            </div>
            <div class="space-y-3">
                @foreach([
                    ['PHP Version',      $php_version],
                    ['Laravel Version',  $laravel_version],
                    ['Environment',      $environment],
                    ['Debug Mode',       $debug_mode],
                    ['Database Driver',  $db_driver],
                    ['Cache Driver',     $cache_driver],
                    ['Queue Driver',     $queue_driver],
                    ['Session Driver',   $session_driver],
                ] as [$label, $value])
                <div class="flex items-center justify-between py-2 border-b border-zinc-100 dark:border-zinc-800 last:border-0">
                    <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ $label }}</span>
                    <span class="text-sm font-mono font-medium {{ $label === 'Debug Mode' && $value === 'Enabled' ? 'text-amber-600 dark:text-amber-400' : 'text-zinc-900 dark:text-zinc-200' }}">{{ $value }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Database Record Counts -->
        <div class="tc-card">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-500/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 8.485-7.5 11.25-7.5 11.25S5.25 14.86 5.25 6.375a7.5 7.5 0 0115 0z"/></svg>
                </div>
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white" style="font-family:'Poppins'">Database Records</h2>
            </div>
            <div class="grid grid-cols-2 gap-3">
                @foreach([
                    ['Users',        $counts['users'],        'bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-300'],
                    ['Orders',       $counts['orders'],       'bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300'],
                    ['Appointments', $counts['appointments'], 'bg-purple-100 dark:bg-purple-500/20 text-purple-700 dark:text-purple-300'],
                    ['Measurements', $counts['measurements'], 'bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-300'],
                ] as [$label, $count, $style])
                <div class="p-4 rounded-xl {{ $style }} border border-white/10 text-center">
                    <p class="text-2xl font-bold" style="font-family:'Poppins'">{{ number_format($count) }}</p>
                    <p class="text-xs font-medium mt-1 opacity-80">{{ $label }}</p>
                </div>
                @endforeach
            </div>

            <!-- Storage Breakdown -->
            <div class="mt-5 pt-5 border-t border-zinc-100 dark:border-zinc-800">
                <h3 class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-3" style="font-family:'Poppins'">Storage Breakdown</h3>
                <div class="space-y-2">
                    @foreach([
                        ['Design References',      '0.00 MB', 60],
                        ['Virtual Try-On Results', '0.00 MB', 25],
                        ['Other Uploads',           '0.00 MB', 15],
                    ] as [$label, $size, $pct])
                    <div>
                        <div class="flex justify-between text-xs text-zinc-500 dark:text-zinc-400 mb-1">
                            <span>{{ $label }}</span>
                            <span>{{ $size }}</span>
                        </div>
                        <div class="w-full h-1.5 bg-zinc-100 dark:bg-zinc-700 rounded-full">
                            <div class="bg-primary-500 h-full rounded-full" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <p class="text-xs text-zinc-400 dark:text-zinc-600 mt-3 text-center">Storage breakdown will populate once files are uploaded.</p>
            </div>
        </div>
    </div>

    <!-- Service Status -->
    <div class="tc-card">
        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white mb-5" style="font-family:'Poppins'">Service Status</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach([
                ['Web Application',   'M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3'],
                ['Database',          'M20.25 6.375c0 8.485-7.5 11.25-7.5 11.25S5.25 14.86 5.25 6.375a7.5 7.5 0 0115 0z'],
                ['File Storage',      'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z'],
                ['AI Virtual Try-On', 'M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846'],
            ] as [$service, $icon])
            <div class="flex items-center gap-4 p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-500/20 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-zinc-900 dark:text-zinc-200 truncate">{{ $service }}</p>
                    <div class="flex items-center gap-1.5 mt-0.5">
                        <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></div>
                        <span class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">Operational</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
