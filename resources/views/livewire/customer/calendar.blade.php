<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl">My Calendar</flux:heading>
            <flux:text class="mt-1 text-zinc-500">View your appointment history and upcoming schedule.</flux:text>
        </div>

        <div class="flex items-center gap-2">
            <button wire:click="previousMonth"
                class="p-2 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors">
                <flux:icon name="chevron-left" class="w-5 h-5 text-zinc-600 dark:text-zinc-400" />
            </button>

            <div class="min-w-[120px] text-center font-semibold text-lg text-zinc-900 dark:text-white">
                {{ $monthName }} {{ $currentYear }}
            </div>

            <button wire:click="nextMonth"
                class="p-2 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors">
                <flux:icon name="chevron-right" class="w-5 h-5 text-zinc-600 dark:text-zinc-400" />
            </button>
        </div>
    </div>

    <flux:card class="p-6">
        {{-- Weekday Headers --}}
        <div class="grid grid-cols-7 mb-4 border-b border-zinc-200 dark:border-zinc-700 pb-2">
            @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                <div class="text-center text-sm font-medium text-zinc-500 dark:text-zinc-400">
                    {{ $day }}
                </div>
            @endforeach
        </div>

        {{-- Calendar Grid --}}
        <div class="grid grid-cols-7 gap-2">
            {{-- Empty cells for start of month --}}
            @for ($i = 0; $i < $firstDayOfWeek; $i++)
                <div class="h-24 sm:h-32"></div>
            @endfor

            {{-- Days --}}
            @for ($day = 1; $day <= $daysInMonth; $day++)
                @php
                    $date = \Carbon\Carbon::createFromDate($currentYear, $currentMonth, $day)->format('Y-m-d');
                    $isToday = $date === now()->format('Y-m-d');
                    $hasEvents = isset($monthlyAppointments[$date]);
                    $events = $monthlyAppointments[$date] ?? collect();
                @endphp

                <div wire:click="selectDate('{{ $date }}')" class="relative h-24 sm:h-32 p-2 border border-zinc-100 dark:border-zinc-800 rounded-lg cursor-pointer hover:border-indigo-500 dark:hover:border-indigo-400 hover:shadow-sm group
                                {{ $isToday ? 'bg-indigo-50/50 dark:bg-indigo-900/10' : 'bg-white dark:bg-zinc-900' }}">

                    <div class="flex justify-between items-start">
                        <span
                            class="text-sm font-medium {{ $isToday ? 'text-indigo-600 dark:text-indigo-400 bg-indigo-100 dark:bg-indigo-900/30 w-6 h-6 flex items-center justify-center rounded-full' : 'text-zinc-700 dark:text-zinc-300' }}">
                            {{ $day }}
                        </span>

                        @if($hasEvents)
                            <span class="flex h-2 w-2">
                                <span
                                    class="animate-ping absolute inline-flex h-2 w-2 rounded-full bg-indigo-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
                            </span>
                        @endif
                    </div>

                    {{-- Event dots/preview for larger screens --}}
                    @if($hasEvents)
                        <div class="mt-2 space-y-1">
                            @foreach($events->take(2) as $event)
                                <div
                                    class="hidden sm:block text-xs truncate px-1.5 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700">
                                    @role('Staff')
                                        {{ $event->customer_name }}
                                    @else
                                        {{ $event->service->name }}
                                    @endrole
                                </div>
                            @endforeach
                            @if($events->count() > 2)
                                <div class="hidden sm:block text-[10px] text-zinc-400 px-1">
                                    +{{ $events->count() - 2 }} more
                                </div>
                            @endif

                            {{-- Mobile indicator --}}
                            <div class="sm:hidden mt-2 flex justify-center">
                                <div
                                    class="px-2 py-0.5 text-[10px] font-medium rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">
                                    {{ $events->count() }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endfor
        </div>
    </flux:card>

    {{-- Day Details Modal --}}
    <flux:modal wire:model="showingEvents" class="w-full max-w-xl">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">Appointments</flux:heading>
                <flux:text>{{ $selectedDate ? $selectedDate->format('F j, Y') : '' }}</flux:text>
            </div>

            @if($dayEvents && count($dayEvents) > 0)
                <div class="space-y-4">
                    @foreach($dayEvents as $event)
                        <div class="group relative overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 hover:shadow-md transition-all duration-300">
                            <!-- Service Color Strip -->
                            <div class="absolute left-0 top-0 bottom-0 w-1.5" style="background-color: {{ $event->service->color }}"></div>

                            <div class="p-5 pl-7">
                                <!-- Header: Service Name & Status -->
                                <div class="flex items-start justify-between gap-3 mb-3">
                                    <h3 class="font-bold text-lg text-zinc-900 dark:text-white leading-tight">
                                        {{ $event->service->name }}
                                    </h3>
                                    @php
                                        $statusColors = [
                                            'booked' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                            'confirmed' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                                            'completed' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                            'cancelled' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400',
                                        ];
                                        $statusColor = $statusColors[$event->status] ?? 'bg-zinc-100 text-zinc-700';
                                    @endphp
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusColor }}">
                                        <span class="w-1.5 h-1.5 rounded-full bg-current opacity-75"></span>
                                        {{ ucfirst($event->status) }}
                                    </span>
                                </div>

                                <!-- Details -->
                                <div class="space-y-2.5">
                                    <!-- Time -->
                                    <div class="flex items-center gap-2.5 text-sm text-zinc-600 dark:text-zinc-400">
                                        <div class="p-1.5 rounded-full bg-zinc-50 dark:bg-zinc-700/50 text-zinc-400 dark:text-zinc-500">
                                            <flux:icon name="clock" class="w-3.5 h-3.5" />
                                        </div>
                                        <span class="font-medium">
                                            {{ $event->starts_at->format('g:i A') }} - {{ $event->ends_at->format('g:i A') }}
                                        </span>
                                    </div>

                                    <!-- Person (Customer or Staff) -->
                                    <div class="flex items-center gap-2.5 text-sm text-zinc-600 dark:text-zinc-400">
                                        <div class="p-1.5 rounded-full bg-zinc-50 dark:bg-zinc-700/50 text-zinc-400 dark:text-zinc-500">
                                            <flux:icon name="user" class="w-3.5 h-3.5" />
                                        </div>
                                        <div class="font-medium">
                                            @role('Staff')
                                                <div class="flex flex-col">
                                                    <span class="text-zinc-900 dark:text-zinc-200">{{ $event->customer_name }}</span>
                                                    @if($event->customer_phone)
                                                        <span class="text-xs text-zinc-400">{{ $event->customer_phone }}</span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-zinc-900 dark:text-zinc-200">with {{ $event->user->name }}</span>
                                            @endrole
                                        </div>
                                    </div>

                                    <!-- Notes -->
                                    @if($event->notes)
                                        <div class="mt-3 pt-3 border-t border-zinc-100 dark:border-zinc-700/50">
                                            <div class="flex gap-2">
                                                <flux:icon name="document-text" class="w-4 h-4 text-zinc-400 mt-0.5 shrink-0" />
                                                <div class="text-sm text-zinc-500 italic">
                                                    "{{ $event->notes }}"
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-zinc-500 dark:text-zinc-400">
                    <flux:icon name="calendar" class="w-12 h-12 mx-auto mb-2 opacity-50" />
                    No appointments scheduled for this day.
                </div>
                <div class="flex justify-center">
                    <a href="{{ route('booking.wizard') }}" wire:navigate
                        class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg transition-colors text-sm font-medium">
                        <flux:icon name="plus" class="w-4 h-4" />
                        Book Appointment
                    </a>
                </div>
            @endif

            <div class="flex justify-end pt-2">
                <flux:button variant="ghost" wire:click="$set('showingEvents', false)">Close</flux:button>
            </div>
        </div>
    </flux:modal>
</div>