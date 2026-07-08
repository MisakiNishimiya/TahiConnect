<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\User;

new #[Layout('components.layouts.app')] class extends Component {
    use WithPagination;

    public string $search     = '';
    public string $roleFilter = '';
    public bool $showRoleModal   = false;
    public bool $showSuspendModal = false;
    public ?int  $selectedUserId = null;
    public string $newRole       = '';

    public function with(): array
    {
        $query = User::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")->orWhere('email', 'like', "%{$this->search}%"))
            ->when($this->roleFilter, fn($q) => $q->where('role', $this->roleFilter))
            ->latest();

        return [
            'users'          => $query->paginate(15),
            'roleCounts'     => [
                'all'         => User::count(),
                'customer'    => User::where('role', 'customer')->count(),
                'tailor_staff'=> User::where('role', 'tailor_staff')->count(),
                'shop_owner'  => User::where('role', 'shop_owner')->count(),
                'super_admin' => User::where('role', 'super_admin')->count(),
            ],
        ];
    }

    public function openRoleModal(int $userId): void
    {
        $this->selectedUserId = $userId;
        $user = User::find($userId);
        $this->newRole        = $user?->role ?? '';
        $this->showRoleModal  = true;
    }

    public function openSuspendModal(int $userId): void
    {
        $this->selectedUserId  = $userId;
        $this->showSuspendModal = true;
    }

    public function closeModals(): void
    {
        $this->showRoleModal    = false;
        $this->showSuspendModal = false;
        $this->selectedUserId  = null;
        $this->newRole         = '';
    }

    public function saveRole(): void
    {
        // UI-only — backend not yet implemented
        session()->flash('message', 'Role change queued. Backend implementation pending.');
        $this->closeModals();
    }

    public function suspendUser(): void
    {
        // UI-only — backend not yet implemented
        session()->flash('message', 'User suspension queued. Backend implementation pending.');
        $this->closeModals();
    }

    public function updatedSearch(): void  { $this->resetPage(); }
    public function updatedRoleFilter(): void { $this->resetPage(); }
}; ?>

<div class="space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-3xl font-bold text-primary-600 dark:text-primary-400" style="font-family: 'Poppins';">User Management</h1>
        <p class="text-zinc-500 dark:text-zinc-400 mt-1">View all users, reassign roles, and manage account access</p>
    </div>

    @if(session()->has('message'))
        <div class="p-4 bg-amber-500/20 border border-amber-500/30 rounded-xl text-amber-300 text-sm">{{ session('message') }}</div>
    @endif

    <!-- Role Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
        @foreach([
            ['All',          $roleCounts['all'],          '',             'bg-zinc-100 dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-200'],
            ['Customers',    $roleCounts['customer'],     'customer',     'bg-blue-50 dark:bg-blue-500/20 border-blue-200 dark:border-blue-500/30 text-blue-700 dark:text-blue-300'],
            ['Staff',        $roleCounts['tailor_staff'], 'tailor_staff', 'bg-emerald-50 dark:bg-emerald-500/20 border-emerald-200 dark:border-emerald-500/30 text-emerald-700 dark:text-emerald-300'],
            ['Shop Owners',  $roleCounts['shop_owner'],   'shop_owner',   'bg-amber-50 dark:bg-amber-500/20 border-amber-200 dark:border-amber-500/30 text-amber-700 dark:text-amber-300'],
            ['Super Admins', $roleCounts['super_admin'],  'super_admin',  'bg-red-50 dark:bg-red-500/20 border-red-200 dark:border-red-500/30 text-red-700 dark:text-red-300'],
        ] as [$label, $count, $role, $style])
        <button wire:click="$set('roleFilter', '{{ $role }}')"
            class="p-4 rounded-xl border text-center transition-all hover:scale-105 {{ $style }} {{ $roleFilter === $role ? 'ring-2 ring-primary-500' : '' }}">
            <p class="text-2xl font-bold" style="font-family:'Poppins'">{{ $count }}</p>
            <p class="text-xs font-medium mt-1 opacity-80">{{ $label }}</p>
        </button>
        @endforeach
    </div>

    <!-- Search + Filter -->
    <div class="tc-card !p-4 flex flex-col sm:flex-row gap-4">
        <div class="flex-1 relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input wire:model.live.debounce.300ms="search" placeholder="Search by name or email..."
                class="w-full pl-10 pr-4 py-2.5 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-200 text-sm focus:ring-2 focus:ring-primary-500 placeholder-zinc-400 dark:placeholder-zinc-600 transition-colors" />
        </div>
        <select wire:model.live="roleFilter" class="px-4 py-2.5 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-200 text-sm focus:ring-2 focus:ring-primary-500">
            <option value="">All Roles</option>
            <option value="customer">Customer</option>
            <option value="tailor_staff">Tailor Staff</option>
            <option value="shop_owner">Shop Owner</option>
            <option value="super_admin">Super Admin</option>
        </select>
    </div>

    <!-- Users Table -->
    <div class="tc-card !p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-100 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-800/50">
                        <th class="text-left py-4 px-4 font-semibold text-zinc-500">User</th>
                        <th class="text-left py-4 px-4 font-semibold text-zinc-500">Role</th>
                        <th class="text-left py-4 px-4 font-semibold text-zinc-500 hidden md:table-cell">Contact</th>
                        <th class="text-left py-4 px-4 font-semibold text-zinc-500 hidden lg:table-cell">Registered</th>
                        <th class="text-right py-4 px-4 font-semibold text-zinc-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-50 dark:divide-zinc-800">
                    @forelse($users as $user)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors group">
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-primary-100 dark:bg-primary-500/20 flex items-center justify-center text-sm font-bold text-primary-600 dark:text-primary-400">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-zinc-900 dark:text-zinc-200">{{ $user->name }}</p>
                                        <p class="text-xs text-zinc-500">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                @php
                                    $roleColors = ['super_admin' => 'bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-300 border-red-200 dark:border-red-500/30', 'shop_owner' => 'bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-500/30', 'tailor_staff' => 'bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-500/30', 'customer' => 'bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-500/30'];
                                @endphp
                                <span class="px-2.5 py-1 text-xs font-medium border rounded-full {{ $roleColors[$user->role] ?? 'bg-zinc-100 dark:bg-zinc-500/20 text-zinc-700 dark:text-zinc-300 border-zinc-200 dark:border-zinc-500/30' }}">
                                    {{ ucwords(str_replace('_', ' ', $user->role)) }}
                                </span>
                            </td>
                            <td class="py-4 px-4 text-zinc-500 dark:text-zinc-400 hidden md:table-cell">{{ $user->contact_number ?? '—' }}</td>
                            <td class="py-4 px-4 text-zinc-400 text-xs hidden lg:table-cell">{{ $user->created_at->format('M d, Y') }}</td>
                            <td class="py-4 px-4 text-right">
                                <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button wire:click="openRoleModal({{ $user->id }})"
                                        class="px-3 py-1.5 text-xs font-medium text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-500/10 rounded-lg transition-colors">
                                        Change Role
                                    </button>
                                    @if($user->role !== 'super_admin')
                                    <button wire:click="openSuspendModal({{ $user->id }})"
                                        class="px-3 py-1.5 text-xs font-medium text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-500/10 rounded-lg transition-colors">
                                        Suspend
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-16 text-center text-zinc-400">No users found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="px-4 py-4 border-t border-zinc-100 dark:border-zinc-800">{{ $users->links() }}</div>
        @endif
    </div>

    <!-- Change Role Modal -->
    @if($showRoleModal && $selectedUserId)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-2xl shadow-2xl w-full max-w-md" @click.stop>
            <div class="p-6">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Change User Role</h3>
                    <button wire:click="closeModals" class="p-2 text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-full transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-3 bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 rounded-xl text-xs text-amber-700 dark:text-amber-300 mb-5">
                    ⚠ Changing a user's role will immediately affect their access permissions. This action is logged.
                </div>
                <div class="mb-5">
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">New Role</label>
                    <select wire:model="newRole" class="w-full px-4 py-2.5 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-200 text-sm focus:ring-2 focus:ring-primary-500">
                        <option value="customer">Customer</option>
                        <option value="tailor_staff">Tailor Staff</option>
                        <option value="shop_owner">Shop Owner</option>
                        <option value="super_admin">Super Admin</option>
                    </select>
                </div>
                <div class="flex gap-3">
                    <button wire:click="closeModals" class="flex-1 px-4 py-2.5 text-sm font-medium text-zinc-600 dark:text-zinc-300 bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 rounded-xl transition-colors">Cancel</button>
                    <button wire:click="saveRole" class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-primary-500 hover:bg-primary-600 rounded-xl transition-colors">Save Role</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Suspend Modal -->
    @if($showSuspendModal && $selectedUserId)
    @php $su = \App\Models\User::find($selectedUserId); @endphp
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-2xl shadow-2xl w-full max-w-md" @click.stop>
            <div class="p-6">
                <div class="flex items-center gap-4 mb-5">
                    <div class="w-12 h-12 rounded-full bg-amber-100 dark:bg-amber-500/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Suspend Account</h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $su?->name }}</p>
                    </div>
                </div>
                <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-5">Suspending this account will prevent the user from logging in. Their data will be preserved. You can reactivate the account at any time.</p>
                <div class="flex gap-3">
                    <button wire:click="closeModals" class="flex-1 px-4 py-2.5 text-sm font-medium text-zinc-600 dark:text-zinc-300 bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 rounded-xl transition-colors">Cancel</button>
                    <button wire:click="suspendUser" class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-amber-600 hover:bg-amber-500 rounded-xl transition-colors">Suspend Account</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
