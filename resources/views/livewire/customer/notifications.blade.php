<?php

use App\Models\CustomNotification;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
    public string $activeTab = 'all';
    public bool $showMarkAllModal = false;
    public bool $isLoading = false;

    public function markAsRead($id): void
    {
        $this->isLoading = true;
        CustomNotification::where('id', $id)->where('user_id', auth()->id())->update(['is_read' => true]);
        $this->isLoading = false;
        
        // Add success animation
        $this->dispatch('notification-updated');
    }

    public function markAllAsRead(): void
    {
        $this->isLoading = true;
        CustomNotification::where('user_id', auth()->id())->where('is_read', false)->update(['is_read' => true]);
        $this->showMarkAllModal = false;
        $this->isLoading = false;
        session()->flash('message', 'All notifications marked as read!');
        
        // Refresh with animation
        $this->dispatch('all-notifications-read');
    }

    public function deleteNotification($id): void
    {
        $this->isLoading = true;
        CustomNotification::where('id', $id)->where('user_id', auth()->id())->delete();
        $this->isLoading = false;
        session()->flash('message', 'Notification deleted successfully!');
        
        // Add delete animation
        $this->dispatch('notification-deleted');
    }

    public function getNotificationsByTab()
    {
        $query = CustomNotification::where('user_id', auth()->id());
        
        switch($this->activeTab) {
            case 'unread':
                $query->where('is_read', false);
                break;
            case 'read':
                $query->where('is_read', true);
                break;
            case 'orders':
                $query->where('type', 'order');
                break;
            case 'appointments':
                $query->where('type', 'appointment');
                break;
            case 'payments':
                $query->where('type', 'payment');
                break;
        }
        
        return $query->orderBy('is_read')->latest()->get();
    }

    public function with(): array
    {
        $userId = auth()->id();
        return [
            'notifications' => $this->getNotificationsByTab(),
            'unreadCount' => CustomNotification::where('user_id', $userId)->where('is_read', false)->count(),
            'totalCount' => CustomNotification::where('user_id', $userId)->count(),
            'todayCount' => CustomNotification::where('user_id', $userId)->whereDate('created_at', today())->count(),
            'notificationCounts' => [
                'all' => CustomNotification::where('user_id', $userId)->count(),
                'unread' => CustomNotification::where('user_id', $userId)->where('is_read', false)->count(),
                'read' => CustomNotification::where('user_id', $userId)->where('is_read', true)->count(),
                'orders' => CustomNotification::where('user_id', $userId)->where('type', 'order')->count(),
                'appointments' => CustomNotification::where('user_id', $userId)->where('type', 'appointment')->count(),
                'payments' => CustomNotification::where('user_id', $userId)->where('type', 'payment')->count(),
            ]
        ];
    }
}; ?>

<div class="space-y-6" 
     x-data="{ isLoading: @entangle('isLoading') }"
     x-on:notification-updated.window="$dispatch('refresh')"
     x-on:all-notifications-read.window="$dispatch('refresh')"
     x-on:notification-deleted.window="$dispatch('refresh')">

    <!-- Loading Overlay -->
    <div x-show="isLoading" 
         x-transition:enter="transition-opacity duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/20 backdrop-blur-sm z-40 flex items-center justify-center"
         style="display: none;">
        <div class="bg-white dark:bg-zinc-800 rounded-2xl p-6 shadow-2xl">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 border-4 border-primary-200 border-t-primary-500 rounded-full animate-spin"></div>
                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Processing...</span>
            </div>
        </div>
    </div>

    <!-- Enhanced Header Section -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <h1 class="text-3xl font-bold text-primary-600 dark:text-primary-400" style="font-family: 'Poppins';">Notifications</h1>
                @if($unreadCount > 0)
                    <div class="relative">
                        <span class="px-3 py-1.5 bg-gradient-to-r from-red-500 to-red-600 text-white text-sm font-semibold rounded-full animate-pulse shadow-lg">
                            {{ $unreadCount }} unread
                        </span>
                        <div class="absolute -top-1 -right-1 w-3 h-3 bg-red-400 rounded-full animate-ping"></div>
                    </div>
                @endif
            </div>
            <p class="text-zinc-500 mb-4">Stay updated with your orders, appointments, and account activities.</p>
            
            <!-- Quick Stats -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="tc-card !p-4 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-800/20 border-blue-200 dark:border-blue-800">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-blue-500 flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5-5 5-5h-5m-6 10v-2a3 3 0 00-3-3H5a3 3 0 00-3 3v2a1 1 0 001 1h6a1 1 0 001-1z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-lg font-bold text-blue-900 dark:text-blue-100">{{ $totalCount }}</p>
                            <p class="text-xs text-blue-600 dark:text-blue-300">Total</p>
                        </div>
                    </div>
                </div>
                
                <div class="tc-card !p-4 bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/30 dark:to-red-800/20 border-red-200 dark:border-red-800">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-red-500 flex items-center justify-center {{ $unreadCount > 0 ? 'animate-pulse' : '' }}">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-lg font-bold text-red-900 dark:text-red-100">{{ $unreadCount }}</p>
                            <p class="text-xs text-red-600 dark:text-red-300">Unread</p>
                        </div>
                    </div>
                </div>
                
                <div class="tc-card !p-4 bg-gradient-to-br from-emerald-50 to-emerald-100 dark:from-emerald-900/30 dark:to-emerald-800/20 border-emerald-200 dark:border-emerald-800">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-emerald-500 flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m1 5v6a2 2 0 01-2 2H7a2 2 0 01-2-2v-6m1-5V4a1 1 0 011-1h10a1 1 0 011 1v4a1 1 0 01-1 1H7a1 1 0 01-1-1z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-lg font-bold text-emerald-900 dark:text-emerald-100">{{ $todayCount }}</p>
                            <p class="text-xs text-emerald-600 dark:text-emerald-300">Today</p>
                        </div>
                    </div>
                </div>
                
                <div class="tc-card !p-4 bg-gradient-to-br from-amber-50 to-amber-100 dark:from-amber-900/30 dark:to-amber-800/20 border-amber-200 dark:border-amber-800">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-amber-500 flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-lg font-bold text-amber-900 dark:text-amber-100">{{ $notificationCounts['read'] }}</p>
                            <p class="text-xs text-amber-600 dark:text-amber-300">Read</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Enhanced Actions -->
        @if($unreadCount > 0)
            <div class="flex flex-col sm:flex-row gap-3">
                <button 
                    wire:click="$set('showMarkAllModal', true)"
                    class="px-6 py-3 text-sm font-medium text-white bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-xl hover:from-emerald-600 hover:to-emerald-700 transition-all duration-300 shadow-lg hover:shadow-xl hover:shadow-emerald-500/25 click-feedback flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Mark All Read ({{ $unreadCount }})
                </button>
            </div>
        @endif
    </div>

    <!-- Success Message -->
    @if (session()->has('message'))
        <x-notification-toast 
            type="success" 
            title="Success!" 
            message="{{ session('message') }}"
            :dismissible="true"
        />
    @endif

    <!-- Enhanced Filter Tabs -->
    <div class="tc-card !p-0 overflow-hidden">
        <div class="flex overflow-x-auto border-b border-zinc-100 dark:border-zinc-700 custom-scrollbar">
            @php
                $tabs = [
                    ['key' => 'all', 'label' => 'All Notifications', 'icon' => 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16', 'color' => 'zinc'],
                    ['key' => 'unread', 'label' => 'Unread', 'icon' => 'M15 17h5l-5-5 5-5h-5m-6 10v-2a3 3 0 00-3-3H5a3 3 0 00-3 3v2a1 1 0 001 1h6a1 1 0 001-1z', 'color' => 'red'],
                    ['key' => 'orders', 'label' => 'Orders', 'icon' => 'M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z', 'color' => 'emerald'],
                    ['key' => 'appointments', 'label' => 'Appointments', 'icon' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5', 'color' => 'blue'],
                    ['key' => 'payments', 'label' => 'Payments', 'icon' => 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z', 'color' => 'amber']
                ];
            @endphp

            @foreach($tabs as $tab)
                <button 
                    wire:click="$set('activeTab', '{{ $tab['key'] }}')"
                    class="flex-shrink-0 px-6 py-4 text-sm font-medium transition-all duration-300 relative whitespace-nowrap group {{ $activeTab === $tab['key'] ? 'text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20' : 'text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800/50' }}"
                >
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4 transition-transform duration-300 {{ $activeTab === $tab['key'] ? 'scale-110' : 'group-hover:scale-105' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $tab['icon'] }}"/>
                        </svg>
                        {{ $tab['label'] }}
                        @if(isset($notificationCounts[$tab['key']]) && $notificationCounts[$tab['key']] > 0)
                            <span class="ml-1 px-2 py-0.5 text-xs rounded-full transition-all duration-300 {{ $activeTab === $tab['key'] ? 'bg-primary-200 dark:bg-primary-800 text-primary-800 dark:text-primary-200' : 'bg-zinc-100 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-400' }}">
                                {{ $notificationCounts[$tab['key']] }}
                            </span>
                        @endif
                    </span>
                    @if($activeTab === $tab['key'])
                        <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-primary-500 animate-fade-in-up"></div>
                    @endif
                </button>
            @endforeach
        </div>
    </div>

    <!-- Notifications List -->
    <div class="space-y-3">
        @forelse($notifications as $notif)
            <div class="tc-card hover-lift interactive-card group animate-fade-in-up cursor-pointer" 
                 style="--stagger-index: {{ $loop->index }}"
                 x-data="{ showActions: false }"
                 @mouseenter="showActions = true"
                 @mouseleave="showActions = false">
                 
                <!-- Notification Content -->
                <div class="flex items-start gap-4 {{ !$notif->is_read ? 'relative' : '' }}">
                    
                    <!-- Unread Indicator -->
                    @if(!$notif->is_read)
                        <div class="absolute -left-2 top-1/2 transform -translate-y-1/2 w-1 h-8 bg-gradient-to-b from-primary-400 to-primary-600 rounded-full"></div>
                    @endif

                    <!-- Enhanced Icon -->
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 border transition-all duration-300 group-hover:scale-110
                        {{ match($notif->type) { 
                            'order' => 'bg-gradient-to-br from-emerald-100 to-emerald-200 dark:from-emerald-900/30 dark:to-emerald-800/30 border-emerald-200 dark:border-emerald-800', 
                            'appointment' => 'bg-gradient-to-br from-blue-100 to-blue-200 dark:from-blue-900/30 dark:to-blue-800/30 border-blue-200 dark:border-blue-800', 
                            'payment' => 'bg-gradient-to-br from-amber-100 to-amber-200 dark:from-amber-900/30 dark:to-amber-800/30 border-amber-200 dark:border-amber-800', 
                            default => 'bg-gradient-to-br from-zinc-100 to-zinc-200 dark:from-zinc-700 dark:to-zinc-600 border-zinc-200 dark:border-zinc-600' 
                        } }}">
                        @if($notif->type === 'order')
                            <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                            </svg>
                        @elseif($notif->type === 'appointment')
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                            </svg>
                        @elseif($notif->type === 'payment')
                            <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/>
                            </svg>
                        @else
                            <svg class="w-6 h-6 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                            </svg>
                        @endif
                    </div>
                    
                    <!-- Notification Content -->
                    <div class="flex-1 min-w-0" wire:click="markAsRead({{ $notif->id }})">
                        <div class="flex items-start justify-between gap-2 mb-2">
                            <h3 class="font-semibold {{ !$notif->is_read ? 'text-zinc-900 dark:text-white' : 'text-zinc-700 dark:text-zinc-300' }} group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                                {{ $notif->title }}
                            </h3>
                            <div class="flex items-center gap-2 shrink-0">
                                @if(!$notif->is_read)
                                    <div class="w-2.5 h-2.5 bg-primary-500 rounded-full animate-pulse"></div>
                                @endif
                                <!-- Type Badge -->
                                <span class="text-xs px-2 py-1 rounded-full font-medium {{ match($notif->type) { 
                                    'order' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300', 
                                    'appointment' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300', 
                                    'payment' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300', 
                                    default => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300' 
                                } }}">
                                    {{ ucfirst($notif->type) }}
                                </span>
                            </div>
                        </div>
                        
                        <p class="text-sm text-zinc-600 dark:text-zinc-400 line-clamp-2 mb-3">{{ $notif->message }}</p>
                        
                        <!-- Timestamp with better formatting -->
                        <div class="flex items-center gap-2 text-xs text-zinc-500">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>{{ $notif->created_at->diffForHumans() }}</span>
                            @if($notif->created_at->isToday())
                                <span class="px-1.5 py-0.5 bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 rounded text-xs font-medium">Today</span>
                            @elseif($notif->created_at->isYesterday())
                                <span class="px-1.5 py-0.5 bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-400 rounded text-xs font-medium">Yesterday</span>
                            @endif
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col gap-2 opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-x-2 group-hover:translate-x-0">
                        @if(!$notif->is_read)
                            <button 
                                wire:click.stop="markAsRead({{ $notif->id }})"
                                class="p-2 text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 rounded-lg transition-colors click-feedback"
                                title="Mark as read"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </button>
                        @endif
                        
                        <button 
                            wire:click.stop="deleteNotification({{ $notif->id }})"
                            wire:confirm="Are you sure you want to delete this notification?"
                            class="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors click-feedback"
                            title="Delete notification"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <x-enhanced-empty-state
                icon="notifications"
                title="No notifications yet"
                description="You'll receive updates about your orders, appointments, and payments here. Stay tuned!"
                :actions="[
                    ['type' => 'primary', 'label' => 'Browse Shops', 'onclick' => 'window.location.href=\"' . route('customer.shops') . '\"'],
                    ['type' => 'secondary', 'label' => 'Place Order', 'onclick' => 'window.location.href=\"' . route('customer.orders') . '\"']
                ]"
            />
        @endforelse
    </div>

    <!-- Mark All Read Modal -->
    @if($showMarkAllModal && $unreadCount > 0)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
            <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl w-full max-w-md" @click.stop>
                <div class="p-6">
                    <!-- Header -->
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                            <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Mark All as Read</h3>
                            <p class="text-sm text-zinc-500">This will mark {{ $unreadCount }} notifications as read</p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-3">
                        <button 
                            wire:click="$set('showMarkAllModal', false)"
                            class="flex-1 px-4 py-3 text-sm font-medium text-zinc-600 bg-zinc-100 dark:bg-zinc-800 dark:text-zinc-300 rounded-xl hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors click-feedback"
                        >
                            Cancel
                        </button>
                        <button 
                            wire:click="markAllAsRead"
                            class="flex-1 px-4 py-3 text-sm font-medium text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 transition-colors click-feedback"
                        >
                            Mark All Read
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Floating Action Button -->
    <x-floating-action-button 
        icon="message" 
        tooltip="Notification Settings"
    >
        <!-- Sub Menu Items -->
        <button wire:click="$set('showMarkAllModal', true)" class="w-12 h-12 bg-emerald-500 text-white rounded-full shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center hover:scale-110" title="Mark All Read">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </button>
    </x-floating-action-button>
</div>
