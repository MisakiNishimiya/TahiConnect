<?php

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Hash;

new #[Layout('components.layouts.app')] class extends Component {
    use WithPagination;

    public string $search = '';
    public bool $showCreateModal = false;

    // Create form
    public string $name       = '';
    public string $first_name = '';
    public string $last_name  = '';
    public string $email      = '';
    public string $password   = '';
    public string $contact_number = '';

    public function with(): array
    {
        return [
            'owners' => User::where('role', 'shop_owner')
                ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%"))
                ->latest()
                ->paginate(15),
            'totalOwners' => User::where('role', 'shop_owner')->count(),
        ];
    }

    public function createOwner(): void
    {
        $this->validate([
            'first_name'     => 'required|string|max:100',
            'last_name'      => 'required|string|max:100',
            'email'          => 'required|email|unique:users,email',
            'password'       => 'required|string|min:8',
            'contact_number' => 'nullable|string|max:20',
        ]);

        $shopId = \App\Models\Shop::instance()->id;

        User::create([
            'name'           => trim("{$this->first_name} {$this->last_name}"),
            'first_name'     => $this->first_name,
            'last_name'      => $this->last_name,
            'email'          => $this->email,
            'password'       => Hash::make($this->password),
            'contact_number' => $this->contact_number,
            'role'           => 'shop_owner',
            'shop_id'        => $shopId,
        ]);

        $this->reset(['name', 'first_name', 'last_name', 'email', 'password', 'contact_number', 'showCreateModal']);
        session()->flash('message', 'Shop owner account created successfully.');
    }

    public function deactivate(int $userId): void
    {
        $owner = User::where('id', $userId)->where('role', 'shop_owner')->firstOrFail();
        // Soft-deactivate by clearing password and appending (deactivated) to email
        $owner->update(['email' => 'deactivated_' . time() . '_' . $owner->email]);
        session()->flash('message', "Account for {$owner->name} has been deactivated.");
    }

    public function updatedSearch(): void { $this->resetPage(); }
}; ?>

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-primary-600 dark:text-primary-400" style="font-family: 'Poppins';">Shop Owner Accounts</h1>
            <p class="text-zinc-500 dark:text-zinc-400 mt-1">Create and manage tailoring business owner accounts</p>
        </div>
        <button wire:click="$set('showCreateModal', true)"
            class="px-5 py-2.5 text-sm font-medium bg-gradient-to-r from-primary-500 to-primary-600 text-white rounded-xl hover:from-primary-600 hover:to-primary-700 transition-all shadow-lg flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Create Shop Owner
        </button>
    </div>

    @if(session()->has('message'))
        <x-notification-toast type="success" title="Done!" message="{{ session('message') }}" :dismissible="true" />
    @endif

    <!-- Search -->
    <div class="tc-card !p-4">
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input wire:model.live.debounce.300ms="search" placeholder="Search shop owners by name or email..."
                class="w-full pl-10 pr-4 py-2.5 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-sm focus:ring-2 focus:ring-primary-500 transition-colors" />
        </div>
    </div>

    <!-- Owners Table -->
    <div class="tc-card !p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-100 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50">
                        <th class="text-left py-4 px-4 font-semibold text-zinc-500">Owner</th>
                        <th class="text-left py-4 px-4 font-semibold text-zinc-500 hidden md:table-cell">Contact</th>
                        <th class="text-left py-4 px-4 font-semibold text-zinc-500 hidden lg:table-cell">Registered</th>
                        <th class="text-right py-4 px-4 font-semibold text-zinc-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-50 dark:divide-zinc-800">
                    @forelse($owners as $owner)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/30 transition-colors group">
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center font-bold text-amber-700 dark:text-amber-300 group-hover:scale-105 transition-transform">
                                        {{ strtoupper(substr($owner->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-zinc-900 dark:text-white">{{ $owner->name }}</p>
                                        <p class="text-xs text-zinc-500">{{ $owner->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4 text-zinc-500 hidden md:table-cell">{{ $owner->contact_number ?? '—' }}</td>
                            <td class="py-4 px-4 text-zinc-500 text-sm hidden lg:table-cell">{{ $owner->created_at->format('M d, Y') }}</td>
                            <td class="py-4 px-4 text-right">
                                <a href="{{ route('superadmin.shop-owner-detail', $owner->id) }}" wire:navigate
                                    class="px-3 py-1.5 text-xs font-medium text-primary-400 hover:bg-primary-500/10 rounded-lg transition-colors opacity-0 group-hover:opacity-100">
                                    View Profile
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-16 text-center text-zinc-400">No shop owner accounts found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($owners->hasPages())
            <div class="px-4 py-4 border-t border-zinc-100 dark:border-zinc-700">{{ $owners->links() }}</div>
        @endif
    </div>

    <!-- Create Modal -->
    @if($showCreateModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
        <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl w-full max-w-lg" @click.stop>
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-semibold text-zinc-900 dark:text-white">Create Shop Owner Account</h3>
                    <button wire:click="$set('showCreateModal', false)" class="p-2 text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-full transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form wire:submit="createOwner" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">First Name *</label>
                            <input wire:model="first_name" class="w-full px-4 py-2.5 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-sm focus:ring-2 focus:ring-primary-500 transition-colors" />
                            @error('first_name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Last Name *</label>
                            <input wire:model="last_name" class="w-full px-4 py-2.5 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-sm focus:ring-2 focus:ring-primary-500 transition-colors" />
                            @error('last_name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Email Address *</label>
                        <input wire:model="email" type="email" class="w-full px-4 py-2.5 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-sm focus:ring-2 focus:ring-primary-500 transition-colors" />
                        @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Password *</label>
                        <input wire:model="password" type="password" class="w-full px-4 py-2.5 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-sm focus:ring-2 focus:ring-primary-500 transition-colors" />
                        @error('password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Contact Number</label>
                        <input wire:model="contact_number" class="w-full px-4 py-2.5 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-sm focus:ring-2 focus:ring-primary-500 transition-colors" />
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="button" wire:click="$set('showCreateModal', false)"
                            class="flex-1 px-4 py-2.5 text-sm font-medium text-zinc-600 bg-zinc-100 dark:bg-zinc-800 dark:text-zinc-300 rounded-xl hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                            class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-primary-500 rounded-xl hover:bg-primary-600 transition-colors">
                            Create Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
