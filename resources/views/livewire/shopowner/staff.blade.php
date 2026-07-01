<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\User;

new class extends Component {
    use WithPagination;

    public function with()
    {
        $shopId = auth()->user()->shop_id;
        $staffMembers = User::where('shop_id', $shopId)->where('role', 'tailor_staff')->paginate(10);
        
        return [
            'staffMembers' => $staffMembers,
        ];
    }
}; ?>

<div>
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-primary-600 dark:text-primary-400">Shop Staff</h1>
            <p class="text-zinc-500 dark:text-zinc-400">Manage your tailors and shop assistants</p>
        </div>
        <flux:button variant="primary" icon="plus">Add Staff</flux:button>
    </div>

    <div class="tc-card">
        <div class="overflow-x-auto">
            <flux:table>
                <flux:columns>
                    <flux:column>Name</flux:column>
                    <flux:column>Email</flux:column>
                    <flux:column>Contact</flux:column>
                    <flux:column>Active Orders</flux:column>
                    <flux:column>Actions</flux:column>
                </flux:columns>
                <flux:rows>
                    @forelse($staffMembers as $staff)
                        <flux:row>
                            <flux:cell>
                                <div class="flex items-center gap-3">
                                    <flux:avatar size="sm" :initials="$staff->initials()" />
                                    <span class="font-medium">{{ $staff->name }}</span>
                                </div>
                            </flux:cell>
                            <flux:cell>{{ $staff->email }}</flux:cell>
                            <flux:cell>{{ $staff->contact_number ?? 'N/A' }}</flux:cell>
                            <flux:cell>
                                <flux:badge size="sm">{{ $staff->assignedOrders()->whereNotIn('status', ['completed', 'released'])->count() }} Active</flux:badge>
                            </flux:cell>
                            <flux:cell>
                                <flux:button size="sm" variant="ghost" icon="pencil-square" />
                            </flux:cell>
                        </flux:row>
                    @empty
                        <flux:row>
                            <flux:cell colspan="5" class="text-center py-8 text-zinc-500">No staff members found.</flux:cell>
                        </flux:row>
                    @endforelse
                </flux:rows>
            </flux:table>
        </div>
        
        <div class="mt-4">
            {{ $staffMembers->links() }}
        </div>
    </div>
</div>
