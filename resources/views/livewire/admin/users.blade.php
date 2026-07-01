<?php

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
    public string $search = '';
    public string $roleFilter = '';

    public function with(): array
    {
        $query = User::query();
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%");
            });
        }
        if ($this->roleFilter) {
            $query->where('role', $this->roleFilter);
        }
        return [
            'users' => $query->latest()->get(),
            'totalUsers' => User::count(),
            'customerCount' => User::where('role', 'customer')->count(),
            'staffCount' => User::where('role', 'tailor_staff')->count(),
            'adminCount' => User::where('role', 'admin')->count(),
        ];
    }
}; ?>

<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">User Management</h1>
        <p class="text-zinc-500 mt-1">{{ $totalUsers }} total users registered.</p>
    </div>

    <!-- Summary -->
    <div class="flex flex-wrap gap-3">
        <span class="tc-badge tc-badge-customer px-3 py-1.5">{{ $customerCount }} Customers</span>
        <span class="tc-badge tc-badge-tailor_staff px-3 py-1.5">{{ $staffCount }} Staff</span>
        <span class="tc-badge tc-badge-admin px-3 py-1.5">{{ $adminCount }} Admins</span>
    </div>

    <!-- Filters -->
    <div class="tc-card !p-4 flex flex-wrap gap-4 items-center">
        <div class="flex-1 min-w-[200px]">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Search by name or email..." />
        </div>
        <select wire:model.live="roleFilter" class="rounded-lg border-zinc-300 dark:border-zinc-600 dark:bg-zinc-700 text-sm">
            <option value="">All Roles</option>
            <option value="customer">Customer</option>
            <option value="tailor_staff">Tailor Staff</option>
            <option value="admin">Admin</option>
        </select>
    </div>

    <!-- Users Table -->
    <div class="tc-card !p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="border-b border-zinc-200 dark:border-zinc-700 bg-cream-50 dark:bg-zinc-800/50">
                    <th class="text-left py-3 px-4 font-medium text-zinc-500">Name</th>
                    <th class="text-left py-3 px-4 font-medium text-zinc-500 hidden md:table-cell">Email</th>
                    <th class="text-left py-3 px-4 font-medium text-zinc-500">Role</th>
                    <th class="text-left py-3 px-4 font-medium text-zinc-500 hidden lg:table-cell">Contact</th>
                    <th class="text-left py-3 px-4 font-medium text-zinc-500 hidden lg:table-cell">Registered</th>
                    <th class="text-right py-3 px-4 font-medium text-zinc-500">Actions</th>
                </tr></thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach($users as $user)
                        <tr class="hover:bg-cream-50 dark:hover:bg-zinc-700/30 transition-colors">
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-xs font-semibold text-primary-700 dark:text-primary-300">
                                        {{ $user->initials() }}
                                    </div>
                                    <span class="font-medium text-zinc-900 dark:text-white">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-zinc-500 hidden md:table-cell">{{ $user->email }}</td>
                            <td class="py-3 px-4"><span class="tc-badge tc-badge-{{ $user->role }}">{{ ucwords(str_replace('_', ' ', $user->role)) }}</span></td>
                            <td class="py-3 px-4 text-zinc-500 hidden lg:table-cell">{{ $user->contact_number ?? '—' }}</td>
                            <td class="py-3 px-4 text-zinc-500 hidden lg:table-cell">{{ $user->created_at->format('M d, Y') }}</td>
                            <td class="py-3 px-4 text-right">
                                <button class="text-xs text-primary-600 hover:text-primary-800 font-medium">View</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
