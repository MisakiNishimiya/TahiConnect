<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\User;

new #[Layout('components.layouts.app')] class extends Component {
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
                <flux:table.columns>
                    <flux:table.column>Name</flux:table.column>
                    <flux:table.column>Email</flux:table.column>
                    <flux:table.column>Contact</flux:table.column>
                    <flux:table.column>Active Orders</flux:table.column>
                    <flux:table.column>Actions</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse($staffMembers as $staff)
                        <flux:table.row>
                            <flux:table.cell>
                                <div class="flex items-center gap-3">
                                    <flux:avatar size="sm" :initials="$staff->initials()" />
                                    <span class="font-medium">{{ $staff->name }}</span>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>{{ $staff->email }}</flux:table.cell>
                            <flux:table.cell>{{ $staff->contact_number ?? 'N/A' }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm">{{ $staff->assignedOrders()->whereNotIn('status', ['completed', 'released'])->count() }} Active</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:button size="sm" variant="ghost" icon="pencil-square" />
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5" class="text-center py-8 text-zinc-500">No staff members found.</flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
        
        <div class="mt-4">
            {{ $staffMembers->links() }}
        </div>
    </div>
</div>
