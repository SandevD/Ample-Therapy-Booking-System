<flux:card class="p-4 h-full flex flex-col">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $monthName }} {{ $currentYear }}</h3>
        <div class="flex items-center gap-1">
            <button wire:click="previousMonth"
                class="p-1 rounded-md hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors">
                <flux:icon name="chevron-left" class="w-4 h-4 text-zinc-500" />
            </button>
            <button wire:click="nextMonth"
                class="p-1 rounded-md hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors">
                <flux:icon name="chevron-right" class="w-4 h-4 text-zinc-500" />
            </button>
        </div>
    </div>

    <div class="flex-1 min-h-[250px]">
        {{-- Weekday Headers --}}
        <div class="grid grid-cols-7 mb-2">
            @foreach(['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'] as $day)
                <div class="text-center text-xs font-medium text-zinc-400">
                    {{ $day }}
                </div>
            @endforeach
        </div>

        {{-- Calendar Grid --}}
        <div class="grid grid-cols-7 gap-1">
            {{-- Empty cells --}}
            @for ($i = 0; $i < $firstDayOfWeek; $i++)
                <div class="aspect-square"></div>
            @endfor

            {{-- Days --}}
            @for ($day = 1; $day <= $daysInMonth; $day++)
                @php
                    $date = \Carbon\Carbon::createFromDate($currentYear, $currentMonth, $day)->format('Y-m-d');
                    $isToday = $date === now()->format('Y-m-d');
                    $hasEvents = isset($monthlyAppointments[$date]);
                    $events = $monthlyAppointments[$date] ?? collect();
                @endphp

                <button wire:click="selectDate('{{ $date }}')"
                    class="relative aspect-square flex items-center justify-center rounded-md text-sm
                                {{ $isToday ? 'bg-indigo-50 text-indigo-600 font-semibold dark:bg-indigo-900/20 dark:text-indigo-400' : 'hover:bg-zinc-50 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300' }}">

                    {{ $day }}

                    @if($hasEvents)
                        <div class="absolute bottom-1 left-1/2 -translate-x-1/2 flex gap-0.5">
                            @foreach($events->take(3) as $event)
                                <div class="w-1 h-1 rounded-full" style="background-color: {{ $event->service->color }}"></div>
                            @endforeach
                        </div>
                    @endif
                </button>
            @endfor
        </div>
    </div>

    {{-- Tiny details preview context --}}
    @if($showingEvents)
        <flux:modal wire:model="showingEvents" class="max-w-sm">
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <flux:heading size="base">{{ $selectedDate ? $selectedDate->format('M j') : '' }}</flux:heading>
                    <flux:button variant="ghost" size="sm" icon="x-mark" wire:click="$set('showingEvents', false)" />
                </div>

                @if($dayEvents && count($dayEvents) > 0)
                    <div class="space-y-2">
                        @foreach($dayEvents as $event)
                            <div
                                class="flex gap-3 p-3 rounded-lg bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-100 dark:border-zinc-700">
                                <div class="h-2 w-2 mt-1.5 rounded-full shrink-0"
                                    style="background-color: {{ $event->service->color }}"></div>
                                <div>
                                    <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $event->service->name }}
                                    </div>
                                    <div class="text-xs text-zinc-500">{{ $event->starts_at->format('g:i A') }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6 text-sm text-zinc-500">
                        No events on this day.
                    </div>
                @endif

                <div class="pt-2">
                    <a href="{{ route('customer.calendar') }}" wire:navigate
                        class="block w-full text-center text-xs text-indigo-600 hover:text-indigo-500 font-medium">
                        View Full Calendar &rarr;
                    </a>
                </div>
            </div>
        </flux:modal>
    @endif
</flux:card>