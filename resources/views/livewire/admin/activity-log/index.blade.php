<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl">Activity Log</flux:heading>
            <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">A record of everything that happens across the system.</flux:text>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
        <div class="w-full sm:w-64">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Search description or user..."
                icon="magnifying-glass" />
        </div>

        <div class="flex flex-1 flex-col gap-4 sm:flex-row sm:items-center">
            <div class="w-full sm:w-44">
                <flux:select wire:model.live="logName" placeholder="All Types">
                    <option value="">All Types</option>
                    @foreach($logNames as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </flux:select>
            </div>

            <div class="w-full sm:w-44">
                <flux:select wire:model.live="event" placeholder="All Events">
                    <option value="">All Events</option>
                    @foreach($events as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </flux:select>
            </div>

            <div class="w-full sm:w-auto">
                <flux:input type="date" wire:model.live="dateFilter" />
            </div>

            @if($search || $logName || $event || $dateFilter)
                <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">Clear</flux:button>
            @endif
        </div>
    </div>

    {{-- Activity Table --}}
    <flux:card class="overflow-hidden !bg-white dark:!bg-zinc-950/40 dark:!border-zinc-800/60 dark:!shadow-none">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>When</flux:table.column>
                <flux:table.column>User</flux:table.column>
                <flux:table.column>Action</flux:table.column>
                <flux:table.column>Type</flux:table.column>
                <flux:table.column>Details</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @forelse ($activities as $activity)
                    @php
                        $eventColors = [
                            'created' => 'green',
                            'updated' => 'blue',
                            'deleted' => 'red',
                            'login' => 'zinc',
                            'logout' => 'zinc',
                            'failed_login' => 'amber',
                            'password_reset' => 'amber',
                            'password_changed' => 'amber',
                        ];
                        $color = $eventColors[$activity->event] ?? 'zinc';
                        $changes = $activity->attribute_changes ?? collect();
                        $newAttrs = data_get($changes, 'attributes', []);
                        $oldAttrs = data_get($changes, 'old', []);
                    @endphp
                    <flux:table.row wire:key="activity-{{ $activity->id }}">
                        {{-- When --}}
                        <flux:table.cell class="whitespace-nowrap">
                            <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $activity->created_at->format('M j, Y') }}</div>
                            <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $activity->created_at->format('g:i A') }}</div>
                        </flux:table.cell>

                        {{-- User (causer) --}}
                        <flux:table.cell class="whitespace-nowrap">
                            @if($activity->causer)
                                <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $activity->causer->name }}</div>
                                <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $activity->causer->email }}</div>
                            @else
                                <span class="text-sm text-zinc-400 dark:text-zinc-500">System / Guest</span>
                            @endif
                        </flux:table.cell>

                        {{-- Action --}}
                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                <flux:badge :color="$color" size="sm">{{ $events[$activity->event] ?? ucfirst(str_replace('_', ' ', (string) $activity->event)) }}</flux:badge>
                                <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ $activity->description }}</span>
                            </div>
                        </flux:table.cell>

                        {{-- Type --}}
                        <flux:table.cell class="whitespace-nowrap text-sm text-zinc-600 dark:text-zinc-400">
                            {{ $logNames[$activity->log_name] ?? ucfirst((string) $activity->log_name) }}
                            @if($activity->subject_id)
                                <span class="text-zinc-400 dark:text-zinc-500">#{{ $activity->subject_id }}</span>
                            @endif
                        </flux:table.cell>

                        {{-- Details (changed fields) --}}
                        <flux:table.cell>
                            @if(!empty($newAttrs))
                                <div class="space-y-0.5 text-xs">
                                    @foreach($newAttrs as $field => $newValue)
                                        <div class="text-zinc-600 dark:text-zinc-400">
                                            <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ ucfirst(str_replace('_', ' ', $field)) }}:</span>
                                            @if(array_key_exists($field, (array) $oldAttrs))
                                                <span class="text-zinc-400 line-through">{{ \Illuminate\Support\Str::limit((string) data_get($oldAttrs, $field), 30) ?: '—' }}</span>
                                                <span class="text-zinc-400">→</span>
                                            @endif
                                            <span>{{ \Illuminate\Support\Str::limit((string) $newValue, 30) ?: '—' }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @elseif($email = data_get($activity->properties, 'email'))
                                <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $email }}</span>
                            @else
                                <span class="text-xs text-zinc-400 dark:text-zinc-500">—</span>
                            @endif
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="text-center py-8">
                            <div class="text-zinc-500 dark:text-zinc-400">No activity found.</div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>

    {{-- Pagination --}}
    @if($activities->hasPages())
        @php
            $currentPage = $activities->currentPage();
            $lastPage = $activities->lastPage();
        @endphp
        <div class="@container pt-3 border-t border-zinc-100 dark:border-zinc-800/70 flex justify-between items-center gap-3 mt-4">
            {{-- Results summary --}}
            <div class="text-zinc-500 dark:text-zinc-400 text-xs font-medium whitespace-nowrap">
                Showing {{ $activities->firstItem() }} to {{ $activities->lastItem() }} of {{ $activities->total() }} results
            </div>

            {{-- Page links --}}
            <div class="flex items-center bg-white border border-zinc-200 rounded-[8px] p-[1px] dark:bg-zinc-900/50 dark:border-zinc-800">
                {{-- Previous --}}
                @if($currentPage <= 1)
                    <span class="flex justify-center items-center size-6 rounded-[6px] text-zinc-300 dark:text-zinc-500">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4"><path fill-rule="evenodd" d="M9.78 4.22a.75.75 0 0 1 0 1.06L7.06 8l2.72 2.72a.75.75 0 1 1-1.06 1.06L5.47 8.53a.75.75 0 0 1 0-1.06l3.25-3.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" /></svg>
                    </span>
                @else
                    <button type="button" wire:click="previousPage" class="flex justify-center items-center size-6 rounded-[6px] text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-800 dark:hover:text-zinc-100">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4"><path fill-rule="evenodd" d="M9.78 4.22a.75.75 0 0 1 0 1.06L7.06 8l2.72 2.72a.75.75 0 1 1-1.06 1.06L5.47 8.53a.75.75 0 0 1 0-1.06l3.25-3.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" /></svg>
                    </button>
                @endif

                {{-- Page numbers --}}
                @for($page = 1; $page <= $lastPage; $page++)
                    @if($page == $currentPage)
                        <span class="text-xs h-6 px-2 rounded-[6px] font-medium bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900 shadow-sm flex items-center justify-center">{{ $page }}</span>
                    @else
                        <button type="button" wire:click="gotoPage({{ $page }})" class="text-xs h-6 px-2 rounded-[6px] font-medium text-zinc-400 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-800 dark:hover:text-zinc-100 flex items-center justify-center">{{ $page }}</button>
                    @endif
                @endfor

                {{-- Next --}}
                @if($currentPage >= $lastPage)
                    <span class="flex justify-center items-center size-6 rounded-[6px] text-zinc-300 dark:text-zinc-500">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4"><path fill-rule="evenodd" d="M6.22 4.22a.75.75 0 0 1 1.06 0l3.25 3.25a.75.75 0 0 1 0 1.06l-3.25 3.25a.75.75 0 0 1-1.06-1.06L8.94 8 6.22 5.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" /></svg>
                    </span>
                @else
                    <button type="button" wire:click="nextPage" class="flex justify-center items-center size-6 rounded-[6px] text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-800 dark:hover:text-zinc-100">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4"><path fill-rule="evenodd" d="M6.22 4.22a.75.75 0 0 1 1.06 0l3.25 3.25a.75.75 0 0 1 0 1.06l-3.25 3.25a.75.75 0 0 1-1.06-1.06L8.94 8 6.22 5.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" /></svg>
                    </button>
                @endif
            </div>
        </div>
    @endif
</div>
