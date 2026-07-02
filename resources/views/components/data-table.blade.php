@props([
    'headers' => [],
    'rows' => [],
    'loading' => false,
    'emptyState' => null,
    'sortable' => true,
    'searchable' => false,
    'actions' => true
])

<div class="tc-card overflow-hidden">
    <!-- Table Header with Search -->
    @if($searchable || $sortable)
        <div class="p-6 border-b border-zinc-100 dark:border-zinc-700">
            <div class="flex items-center justify-between gap-4">
                @if($searchable)
                    <div class="relative flex-1 max-w-md">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                            </svg>
                        </div>
                        <input 
                            type="text"
                            placeholder="Search..." 
                            class="block w-full pl-9 pr-3 py-2 border border-zinc-200 dark:border-zinc-700 rounded-lg text-sm bg-white dark:bg-zinc-800 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                        >
                    </div>
                @endif

                @if($sortable)
                    <div class="flex items-center gap-2">
                        <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Sort by:</label>
                        <select class="px-3 py-2 border border-zinc-200 dark:border-zinc-700 rounded-lg text-sm bg-white dark:bg-zinc-800 focus:ring-2 focus:ring-primary-500">
                            <option>Name</option>
                            <option>Date</option>
                            <option>Status</option>
                        </select>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Loading State -->
    @if($loading)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-zinc-50 dark:bg-zinc-800/50">
                    <tr>
                        @foreach($headers as $header)
                            <th class="px-6 py-3 text-left">
                                <div class="h-4 bg-zinc-200 dark:bg-zinc-700 rounded animate-pulse"></div>
                            </th>
                        @endforeach
                        @if($actions)
                            <th class="px-6 py-3 text-right">
                                <div class="h-4 bg-zinc-200 dark:bg-zinc-700 rounded animate-pulse w-16 ml-auto"></div>
                            </th>
                        @endif
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-100 dark:divide-zinc-700">
                    @for($i = 0; $i < 5; $i++)
                        <tr class="animate-pulse">
                            @foreach($headers as $header)
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="h-4 bg-zinc-200 dark:bg-zinc-700 rounded w-{{ rand(16, 32) }}"></div>
                                </td>
                            @endforeach
                            @if($actions)
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="flex justify-end gap-2">
                                        <div class="w-8 h-8 bg-zinc-200 dark:bg-zinc-700 rounded"></div>
                                        <div class="w-8 h-8 bg-zinc-200 dark:bg-zinc-700 rounded"></div>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>
    
    <!-- Data State -->
    @elseif(count($rows) > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-zinc-50 dark:bg-zinc-800/50">
                    <tr>
                        @foreach($headers as $index => $header)
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                @if($sortable && isset($header['sortable']) && $header['sortable'])
                                    <button class="flex items-center gap-1 hover:text-zinc-700 dark:hover:text-zinc-300 transition-colors">
                                        {{ is_array($header) ? $header['label'] : $header }}
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
                                        </svg>
                                    </button>
                                @else
                                    {{ is_array($header) ? $header['label'] : $header }}
                                @endif
                            </th>
                        @endforeach
                        @if($actions)
                            <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-100 dark:divide-zinc-700">
                    @foreach($rows as $index => $row)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors animate-fade-in-up" style="--stagger-index: {{ $index }}">
                            @foreach($row['data'] as $cell)
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if(is_array($cell))
                                        @if(isset($cell['type']) && $cell['type'] === 'badge')
                                            <span class="tc-badge tc-badge-{{ $cell['status'] ?? 'default' }}">
                                                {{ $cell['text'] }}
                                            </span>
                                        @elseif(isset($cell['type']) && $cell['type'] === 'avatar')
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
                                                    <span class="text-sm font-medium text-primary-600 dark:text-primary-400">
                                                        {{ strtoupper(substr($cell['name'], 0, 1)) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium text-zinc-900 dark:text-white">{{ $cell['name'] }}</div>
                                                    @if(isset($cell['subtitle']))
                                                        <div class="text-sm text-zinc-500">{{ $cell['subtitle'] }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        @elseif(isset($cell['type']) && $cell['type'] === 'amount')
                                            <div class="text-sm font-bold text-zinc-900 dark:text-white" style="font-family: 'Poppins';">
                                                ₱{{ number_format($cell['amount'], 2) }}
                                            </div>
                                        @else
                                            {{ $cell['text'] ?? $cell }}
                                        @endif
                                    @else
                                        <div class="text-sm text-zinc-900 dark:text-white">{{ $cell }}</div>
                                    @endif
                                </td>
                            @endforeach
                            @if($actions && isset($row['actions']))
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @foreach($row['actions'] as $action)
                                            @if($action['type'] === 'view')
                                                <button 
                                                    @if(isset($action['wire:click'])) wire:click="{{ $action['wire:click'] }}" @endif
                                                    @if(isset($action['onclick'])) onclick="{{ $action['onclick'] }}" @endif
                                                    class="p-2 text-zinc-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors click-feedback"
                                                    title="{{ $action['title'] ?? 'View' }}"
                                                >
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                </button>
                                            @elseif($action['type'] === 'edit')
                                                <button 
                                                    @if(isset($action['wire:click'])) wire:click="{{ $action['wire:click'] }}" @endif
                                                    @if(isset($action['onclick'])) onclick="{{ $action['onclick'] }}" @endif
                                                    class="p-2 text-zinc-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 rounded-lg transition-colors click-feedback"
                                                    title="{{ $action['title'] ?? 'Edit' }}"
                                                >
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                </button>
                                            @elseif($action['type'] === 'delete')
                                                <button 
                                                    @if(isset($action['wire:click'])) wire:click="{{ $action['wire:click'] }}" @endif
                                                    @if(isset($action['onclick'])) onclick="{{ $action['onclick'] }}" @endif
                                                    class="p-2 text-zinc-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors click-feedback"
                                                    title="{{ $action['title'] ?? 'Delete' }}"
                                                >
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            @endif
                                        @endforeach
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    <!-- Empty State -->
    @else
        @if($emptyState)
            {!! $emptyState !!}
        @else
            <x-enhanced-empty-state
                icon="folder"
                title="No data found"
                description="There are no items to display at this time."
            />
        @endif
    @endif
</div>