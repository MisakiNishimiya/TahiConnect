<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\User;

new #[Layout('components.layouts.app')] class extends Component {
    use WithPagination;
    public string $search = '';

    public function with(): array
    {
        $shopId = auth()->user()->shop_id;
        return [
            'staffMembers' => User::where('shop_id', $shopId)->where('role', 'tailor_staff')
                ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")->orWhere('email', 'like', "%{$this->search}%"))
                ->paginate(12),
            'totalStaff' => User::where('shop_id', $shopId)->where('role', 'tailor_staff')->count(),
        ];
    }
    public function updatedSearch() { $this->resetPage(); }
}; ?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-primary-600 dark:text-primary-400" style="font-family: 'Poppins';">Shop Staff</h1>
            <p class="text-zinc-500 mt-1">Manage your tailors and shop assistants</p>
        </div>
        <button class="px-5 py-2.5 text-sm font-medium bg-gradient-to-r from-primary-500 to-primary-600 text-white rounded-xl hover:from-primary-600 hover:to-primary-700 transition-all duration-300 shadow-lg hover:shadow-xl hover:shadow-primary-500/25 click-feedback flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Staff Member
        </button>
    </div>

    <!-- Search & Stats -->
    <div class="flex flex-col sm:flex-row gap-4">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input wire:model.live.debounce.300ms="search" placeholder="Search staff by name or email..." class="w-full pl-10 pr-4 py-3 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-sm focus:ring-2 focus:ring-primary-500 transition-colors" />
        </div>
        <div class="tc-card !p-3 flex items-center gap-3 bg-gradient-to-br from-primary-50 to-primary-100 dark:from-primary-900/20 dark:to-primary-800/10 border-primary-200 dark:border-primary-800">
            <div class="w-10 h-10 rounded-xl bg-primary-500 flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <div>
                <p class="text-xl font-bold text-primary-700 dark:text-primary-300">{{ $totalStaff }}</p>
                <p class="text-xs text-primary-600 dark:text-primary-400">Total Staff</p>
            </div>
        </div>
    </div>

    <!-- Staff Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($staffMembers as $staff)
            <div class="tc-card hover-lift group animate-fade-in-up" style="--stagger-index: {{ $loop->index }}">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center text-white text-lg font-bold group-hover:scale-110 transition-transform duration-300 shadow-lg">
                            {{ strtoupper(substr($staff->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-semibold text-zinc-900 dark:text-white">{{ $staff->name }}</p>
                            <p class="text-xs text-zinc-500 mt-0.5">{{ $staff->email }}</p>
                        </div>
                    </div>
                    <button class="p-1.5 text-zinc-400 hover:text-zinc-600 hover:bg-zinc-100 dark:hover:bg-zinc-700 rounded-lg transition-colors opacity-0 group-hover:opacity-100">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-3 pt-4 border-t border-zinc-100 dark:border-zinc-700">
                    <div class="text-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl">
                        <p class="text-xl font-bold text-blue-700 dark:text-blue-300">
                            {{ $staff->assignedOrders()->whereNotIn('status', ['completed', 'released'])->count() }}
                        </p>
                        <p class="text-xs text-blue-600 dark:text-blue-400 font-medium">Active Orders</p>
                    </div>
                    <div class="text-center p-3 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl">
                        <p class="text-xl font-bold text-emerald-700 dark:text-emerald-300">
                            {{ $staff->assignedOrders()->whereIn('status', ['completed', 'released'])->count() }}
                        </p>
                        <p class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">Completed</p>
                    </div>
                </div>

                @if($staff->contact_number)
                    <div class="flex items-center gap-2 mt-3 text-sm text-zinc-500">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        {{ $staff->contact_number }}
                    </div>
                @endif
            </div>
        @empty
            <div class="col-span-full">
                <x-enhanced-empty-state icon="folder" title="No staff members yet"
                    description="Add your first staff member to start delegating orders."
                    :actions="[['type'=>'primary','label'=>'Add Staff Member','onclick'=>'']]" />
            </div>
        @endforelse
    </div>

    @if($staffMembers->hasPages())
        <div class="flex justify-center">{{ $staffMembers->links() }}</div>
    @endif
</div>
