<?php

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
    public string $search = '';
    public string $roleFilter = '';

<?php
// User management is now handled by the Shop Owner (manage customers/staff)
// and Super Admin (manage shop owner accounts).
// Redirect based on current user role.
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
new #[Layout('components.layouts.app')] class extends Component {
    public function mount(): mixed
    {
        $role = auth()->user()->role;
        return match($role) {
            'super_admin' => redirect()->route('superadmin.shop-owners'),
            'shop_owner'  => redirect()->route('shopowner.customers'),
            default       => redirect()->route('customer.dashboard'),
        };
    }
}; ?>
<div></div>
        return [
            'users' => $query->latest()->get(),
            'totalUsers' => User::count(),
            'customerCount' => User::where('role', 'customer')->count(),
            'staffCount' => User::where('role', 'tailor_staff')->count(),
            'ownerCount' => User::where('role', 'shop_owner')->count(),
            'superAdminCount' => User::where('role', 'super_admin')->count(),
        ];
    }
}; ?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-primary-600 dark:text-primary-400" style="font-family: 'Poppins';">User Management</h1>
            <p class="text-zinc-500 mt-1">{{ $totalUsers }} registered users on the platform</p>
        </div>
    </div>

    <!-- Role Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach([
            ['Customers', $customerCount, 'customer', 'from-blue-100 to-blue-200 dark:from-blue-900/30 dark:to-blue-800/30', 'text-blue-600 dark:text-blue-400', 'border-blue-200 dark:border-blue-800', 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0z'],
            ['Tailor Staff', $staffCount, 'tailor_staff', 'from-emerald-100 to-emerald-200 dark:from-emerald-900/30 dark:to-emerald-800/30', 'text-emerald-600 dark:text-emerald-400', 'border-emerald-200 dark:border-emerald-800', 'M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63'],
            ['Shop Owners', $ownerCount, 'shop_owner', 'from-amber-100 to-amber-200 dark:from-amber-900/30 dark:to-amber-800/30', 'text-amber-600 dark:text-amber-400', 'border-amber-200 dark:border-amber-800', 'M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18'],
            ['Super Admins', $superAdminCount, 'super_admin', 'from-red-100 to-red-200 dark:from-red-900/30 dark:to-red-800/30', 'text-red-600 dark:text-red-400', 'border-red-200 dark:border-red-800', 'M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z'],
        ] as [$label, $count, $role, $bg, $color, $border, $icon])
        <div class="tc-card bg-gradient-to-br {{ $bg }} {{ $border }} hover-lift cursor-pointer {{ $roleFilter === $role ? 'ring-2 ring-primary-500' : '' }}"
             wire:click="$set('roleFilter', '{{ $roleFilter === $role ? '' : $role }}')">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl {{ $bg }} {{ $border }} border flex items-center justify-center">
                    <svg class="w-6 h-6 {{ $color }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family:'Poppins'">{{ $count }}</p>
                    <p class="text-xs {{ $color }} font-medium">{{ $label }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Filters -->
    <div class="tc-card !p-4 flex flex-col sm:flex-row gap-4">
        <div class="flex-1 relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input wire:model.live.debounce.300ms="search" placeholder="Search users by name or email..." class="w-full pl-10 pr-4 py-2.5 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-sm focus:ring-2 focus:ring-primary-500 transition-colors" />
        </div>
        <select wire:model.live="roleFilter" class="px-4 py-2.5 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-sm focus:ring-2 focus:ring-primary-500">
            <option value="">All Roles</option>
            <option value="customer">Customer</option>
            <option value="tailor_staff">Tailor Staff</option>
            <option value="shop_owner">Shop Owner</option>
            <option value="super_admin">Super Admin</option>
        </select>
    </div>

    <!-- Users Table -->
    <div class="tc-card !p-0 overflow-hidden">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-sm">
                <thead><tr class="border-b border-zinc-100 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50">
                    <th class="text-left py-4 px-4 font-semibold text-zinc-500">User</th>
                    <th class="text-left py-4 px-4 font-semibold text-zinc-500 hidden md:table-cell">Email</th>
                    <th class="text-left py-4 px-4 font-semibold text-zinc-500">Role</th>
                    <th class="text-left py-4 px-4 font-semibold text-zinc-500 hidden lg:table-cell">Contact</th>
                    <th class="text-left py-4 px-4 font-semibold text-zinc-500 hidden lg:table-cell">Registered</th>
                    <th class="text-right py-4 px-4 font-semibold text-zinc-500">Actions</th>
                </tr></thead>
                <tbody class="divide-y divide-zinc-50 dark:divide-zinc-800">
                    @forelse($users as $user)
                        <tr class="hover:bg-cream-50 dark:hover:bg-zinc-700/30 transition-colors duration-200 group">
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-500 to-secondary-500 flex items-center justify-center text-sm font-bold text-white group-hover:scale-110 transition-transform duration-300">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-zinc-900 dark:text-white">{{ $user->name }}</p>
                                        <p class="text-xs text-zinc-500 md:hidden">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4 text-zinc-500 hidden md:table-cell">{{ $user->email }}</td>
                            <td class="py-4 px-4"><span class="tc-badge tc-badge-{{ $user->role }}">{{ ucwords(str_replace('_', ' ', $user->role)) }}</span></td>
                            <td class="py-4 px-4 text-zinc-500 hidden lg:table-cell">{{ $user->contact_number ?? '—' }}</td>
                            <td class="py-4 px-4 text-zinc-500 hidden lg:table-cell text-sm">{{ $user->created_at->format('M d, Y') }}</td>
                            <td class="py-4 px-4 text-right">
                                <button class="px-3 py-1.5 text-xs font-medium text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-lg transition-colors click-feedback opacity-0 group-hover:opacity-100">
                                    View
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-16 text-center text-zinc-400">No users found matching your search.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
