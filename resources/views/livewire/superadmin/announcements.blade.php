<?php

use App\Models\CustomNotification;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
    public string $title   = '';
    public string $message = '';
    public string $target  = 'all'; // all | customers | staff | shop_owner
    public bool $sent      = false;

    public function send(): void
    {
        $this->validate([
            'title'   => 'required|string|max:100',
            'message' => 'required|string|max:1000',
            'target'  => 'required|in:all,customer,tailor_staff,shop_owner',
        ]);

        $query = User::query();
        if ($this->target !== 'all') {
            $query->where('role', $this->target);
        }

        $users = $query->get();
        foreach ($users as $user) {
            CustomNotification::create([
                'user_id' => $user->id,
                'type'    => 'system',
                'title'   => $this->title,
                'message' => $this->message,
                'is_read' => false,
            ]);
        }

        $this->reset(['title', 'message', 'target']);
        $this->sent = true;
        session()->flash('message', "Announcement sent to {$users->count()} user(s) successfully.");
    }
}; ?>

<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-primary-600 dark:text-primary-400" style="font-family: 'Poppins';">Announcements</h1>
        <p class="text-zinc-500 dark:text-zinc-400 mt-1">Send system-wide notifications to users</p>
    </div>

    @if(session()->has('message'))
        <x-notification-toast type="success" title="Sent!" message="{{ session('message') }}" :dismissible="true" />
    @endif

    <div class="tc-card max-w-2xl">
        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white mb-5">Send Announcement</h2>
        <form wire:submit="send" class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Target Audience</label>
                <select wire:model="target" class="w-full px-4 py-2.5 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-sm focus:ring-2 focus:ring-primary-500">
                    <option value="all">All Users</option>
                    <option value="customer">Customers Only</option>
                    <option value="tailor_staff">Tailor Staff Only</option>
                    <option value="shop_owner">Shop Owners Only</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Title *</label>
                <input wire:model="title" placeholder="Announcement title..." class="w-full px-4 py-2.5 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-sm focus:ring-2 focus:ring-primary-500" />
                @error('title') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Message *</label>
                <textarea wire:model="message" rows="5" placeholder="Write your announcement..." class="w-full px-4 py-2.5 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-800 text-sm focus:ring-2 focus:ring-primary-500 resize-none"></textarea>
                @error('message') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="flex justify-end">
                <button type="submit" class="px-8 py-3 text-sm font-semibold text-white bg-gradient-to-r from-primary-500 to-primary-600 rounded-xl hover:from-primary-600 hover:to-primary-700 transition-all shadow-lg flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    Send Announcement
                </button>
            </div>
        </form>
    </div>
</div>
