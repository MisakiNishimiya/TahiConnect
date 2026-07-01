<?php

use App\Models\CustomNotification;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
    public function markAsRead($id): void
    {
        CustomNotification::where('id', $id)->where('user_id', auth()->id())->update(['is_read' => true]);
    }

    public function markAllAsRead(): void
    {
        CustomNotification::where('user_id', auth()->id())->where('is_read', false)->update(['is_read' => true]);
    }

    public function with(): array
    {
        return [
            'notifications' => CustomNotification::where('user_id', auth()->id())->orderBy('is_read')->latest()->get(),
            'unreadCount' => CustomNotification::where('user_id', auth()->id())->where('is_read', false)->count(),
        ];
    }
}; ?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">Notifications</h1>
            @if($unreadCount > 0)
                <span class="px-2.5 py-0.5 bg-primary-500 text-white text-xs font-semibold rounded-full">{{ $unreadCount }}</span>
            @endif
        </div>
        @if($unreadCount > 0)
            <flux:button wire:click="markAllAsRead" variant="ghost" size="sm">Mark All as Read</flux:button>
        @endif
    </div>

    <div class="space-y-2">
        @forelse($notifications as $notif)
            <div wire:click="markAsRead({{ $notif->id }})"
                class="tc-card cursor-pointer !p-4 flex items-start gap-4 border-l-4 transition-all
                {{ !$notif->is_read ? 'bg-primary-50/50 dark:bg-primary-900/10 border-l-primary-500' : 'border-l-transparent opacity-70' }}
                {{ match($notif->type) { 'order' => '!border-l-emerald-500', 'appointment' => '!border-l-blue-500', 'payment' => '!border-l-amber-500', default => '!border-l-zinc-300' } }}">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0
                    {{ match($notif->type) { 'order' => 'bg-emerald-100 dark:bg-emerald-900/30', 'appointment' => 'bg-blue-100 dark:bg-blue-900/30', 'payment' => 'bg-amber-100 dark:bg-amber-900/30', default => 'bg-zinc-100 dark:bg-zinc-700' } }}">
                    @if($notif->type === 'order')
                        <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                    @elseif($notif->type === 'appointment')
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                    @elseif($notif->type === 'payment')
                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/></svg>
                    @else
                        <svg class="w-5 h-5 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2">
                        <p class="text-sm {{ !$notif->is_read ? 'font-semibold text-zinc-900 dark:text-white' : 'text-zinc-700 dark:text-zinc-300' }}">{{ $notif->title }}</p>
                        @if(!$notif->is_read)
                            <span class="w-2 h-2 bg-primary-500 rounded-full shrink-0 mt-1.5"></span>
                        @endif
                    </div>
                    <p class="text-xs text-zinc-500 mt-0.5 line-clamp-2">{{ $notif->message }}</p>
                    <p class="text-xs text-zinc-400 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
                </div>
            </div>
        @empty
            <div class="tc-card text-center py-12">
                <svg class="w-16 h-16 mx-auto text-zinc-200 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                <p class="text-zinc-400">No notifications yet.</p>
            </div>
        @endforelse
    </div>
</div>
