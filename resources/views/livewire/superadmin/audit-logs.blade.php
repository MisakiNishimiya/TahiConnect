<?php

use App\Models\ActivityLog;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('components.layouts.app')] class extends Component {
    use WithPagination;

    public string $search = '';

    public function with(): array
    {
        return [
            'logs' => ActivityLog::with('user')
                ->when($this->search, fn($q) => $q->where('action', 'like', "%{$this->search}%")
                    ->orWhere('description', 'like', "%{$this->search}%"))
                ->latest()
                ->paginate(25),
        ];
    }

    public function updatedSearch(): void { $this->resetPage(); }
}; ?>

<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-primary-600 dark:text-primary-400" style="font-family: 'Poppins';">Audit Logs</h1>
        <p class="text-zinc-500 dark:text-zinc-400 mt-1">System activity and access logs for monitoring and troubleshooting</p>
    </div>

    <div class="tc-card !p-4">
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input wire:model.live.debounce.300ms="search" placeholder="Search logs by action or description..."
                class="w-full pl-10 pr-4 py-2.5 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-sm focus:ring-2 focus:ring-primary-500 transition-colors" />
        </div>
    </div>

    <div class="tc-card !p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-100 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50">
                        <th class="text-left py-3 px-4 font-semibold text-zinc-500">User</th>
                        <th class="text-left py-3 px-4 font-semibold text-zinc-500">Action</th>
                        <th class="text-left py-3 px-4 font-semibold text-zinc-500 hidden md:table-cell">Description</th>
                        <th class="text-left py-3 px-4 font-semibold text-zinc-500">Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-50 dark:divide-zinc-800">
                    @forelse($logs as $log)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/30 transition-colors">
                            <td class="py-3 px-4 font-medium text-zinc-900 dark:text-white">{{ $log->user?->name ?? 'System' }}</td>
                            <td class="py-3 px-4 text-zinc-700 dark:text-zinc-300">{{ $log->action }}</td>
                            <td class="py-3 px-4 text-zinc-500 hidden md:table-cell">{{ Str::limit($log->description, 60) }}</td>
                            <td class="py-3 px-4 text-zinc-400 text-xs">{{ $log->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-16 text-center text-zinc-400">No audit logs found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
            <div class="px-4 py-4 border-t border-zinc-100 dark:border-zinc-700">{{ $logs->links() }}</div>
        @endif
    </div>
</div>
