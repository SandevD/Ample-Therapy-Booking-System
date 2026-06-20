<div class="space-y-6">
    {{-- Header --}}
    <div>
        <flux:heading size="xl">Book Appointment</flux:heading>
        <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">Schedule a new appointment in a few simple steps.</flux:text>
    </div>

    {{-- Progress --}}
    <div class="mb-8">
        <div class="flex items-center justify-between text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-2">
            <span class="{{ $step >= 1 ? 'text-red-600 dark:text-red-400' : '' }}">Service</span>
            <span class="{{ $step >= 2 ? 'text-red-600 dark:text-red-400' : '' }}">Staff</span>
            <span class="{{ $step >= 3 ? 'text-red-600 dark:text-red-400' : '' }}">Date & Time</span>
            <span class="{{ $step >= 4 ? 'text-red-600 dark:text-red-400' : '' }}">Confirm</span>
        </div>
        <div class="h-1.5 bg-zinc-100 rounded-full overflow-hidden dark:bg-zinc-800/70">
            <div class="h-full bg-gradient-to-r from-red-500 to-orange-500 dark:from-red-500/80 dark:to-orange-500/80 transition-all duration-500 ease-out"
                style="width: {{ ($step / 4) * 100 }}%"></div>
        </div>
    </div>

    {{-- Step 1: Select Service --}}
    @if ($step === 1)
        <div class="space-y-6">
            <flux:heading size="xl">Select a Service</flux:heading>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($services as $service)
                    <button wire:click="selectService({{ $service->id }})"
                        class="group relative overflow-hidden rounded-xl border border-zinc-200 bg-white p-5 text-left shadow-sm transition-all duration-300 hover:shadow-lg hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-900/60 dark:hover:border-zinc-700 dark:hover:bg-zinc-900">
                        <div class="relative z-10">
                            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity dark:opacity-[0.08] dark:group-hover:opacity-15">
                                <flux:icon name="squares-plus" class="w-16 h-16 text-current"
                                    style="color: {{ $service->color }}" />
                            </div>
                            <div class="h-1.5 w-10 rounded-full mb-4" style="background-color: {{ $service->color }}"></div>
                            <h3 class="font-semibold text-lg text-zinc-900 dark:text-zinc-100">{{ $service->name }}</h3>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1 line-clamp-2">{{ $service->description }}</p>
                            <div class="mt-4 flex items-center justify-between flex-wrap gap-2">
                                @if((float) $service->price === 0.0)
                                    <span class="inline-flex items-center rounded-md bg-emerald-100 px-2.5 py-0.5 text-xs font-bold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-1 dark:ring-inset dark:ring-emerald-500/20">
                                        FREE
                                    </span>
                                @else
                                    <span class="font-medium text-zinc-900 dark:text-zinc-100">£{{ $service->price }}</span>
                                @endif
                                <div class="flex items-center gap-2">
                                    @if($service->session_count > 1)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-fuchsia-100 px-2 py-0.5 text-xs font-medium text-fuchsia-700 dark:bg-violet-500/10 dark:text-violet-300 dark:ring-1 dark:ring-inset dark:ring-violet-500/20">
                                            <flux:icon name="rectangle-stack" class="w-3 h-3" />
                                            {{ $service->session_count }} sessions
                                        </span>
                                    @endif
                                    <span class="text-xs text-zinc-400 dark:text-zinc-500">{{ $service->duration }} mins</span>
                                </div>
                            </div>
                        </div>
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Step 2: Select Staff --}}
    @if ($step === 2)
        <div class="space-y-6">
            <div class="flex items-center gap-4">
                <flux:button variant="ghost" icon="arrow-left" wire:click="previousStep">Back</flux:button>
                <flux:heading size="xl">Select Staff</flux:heading>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($staffMembers as $staff)
                    <button wire:click="selectStaff({{ $staff->id }})"
                        class="group relative overflow-hidden rounded-xl border border-zinc-200 bg-white p-5 text-left shadow-sm transition-all duration-300 hover:shadow-lg hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-900/60 dark:hover:border-zinc-700 dark:hover:bg-zinc-900">
                        <div class="relative z-10 flex items-center gap-4">
                            <div class="h-12 w-12 rounded-full bg-gradient-to-br from-amber-500 to-amber-600 dark:from-amber-500/90 dark:to-amber-600/90 flex items-center justify-center text-white font-bold text-lg">
                                {{ $staff->initials() }}
                            </div>
                            <div>
                                <h3 class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $staff->name }}</h3>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">Specialist</p>
                            </div>
                        </div>
                    </button>
                @endforeach
            </div>

            @if($staffMembers->isEmpty())
                <div class="text-center py-12">
                    <flux:text>No staff members available for this service.</flux:text>
                </div>
            @endif
        </div>
    @endif

    {{-- Step 3: Select Date & Time --}}
    @if ($step === 3)
        <div class="space-y-6">
            <div class="flex items-center gap-4">
                <flux:button variant="ghost" icon="arrow-left" wire:click="previousStep">Back</flux:button>
                @if($selectedService && $selectedService->session_count > 1)
                    <flux:heading size="xl">
                        Select Time &mdash; Slot {{ count($selectedSlots) + 1 }} of {{ $selectedService->session_count }}
                    </flux:heading>
                @else
                    <flux:heading size="xl">Select Time</flux:heading>
                @endif
            </div>

            {{-- Selected slots --}}
            @if($selectedService && $selectedService->session_count > 1 && count($selectedSlots) > 0)
                @php
                    $picked = count($selectedSlots);
                    $target = $selectedService->session_count;
                    $pct = min(100, ($picked / $target) * 100);
                    $isComplete = $picked >= $target;
                @endphp
                <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900/60 dark:shadow-none">
                    {{-- Header --}}
                    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-300">
                                <flux:icon name="rectangle-stack" class="w-5 h-5" />
                            </div>
                            <div>
                                <h4 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Selected Slots</h4>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $picked }} of {{ $target }} sessions chosen
                                    @if($isComplete)
                                        <span class="text-emerald-600 dark:text-emerald-400 font-medium">&bull; Ready to confirm</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        @if($isComplete)
                            <flux:button wire:click="proceedToConfirm" icon:trailing="arrow-right" variant="primary" size="sm">
                                Next: Confirm
                            </flux:button>
                        @endif
                    </div>

                    {{-- Progress bar --}}
                    <div class="h-1 bg-zinc-100 rounded-full overflow-hidden dark:bg-zinc-800 mb-4">
                        <div class="h-full bg-violet-500/80 dark:bg-violet-500/70 transition-all duration-300"
                            style="width: {{ $pct }}%"></div>
                    </div>

                    {{-- Slot chips --}}
                    <ul class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                        @foreach($selectedSlots as $index => $slot)
                            @php
                                $slotStart = \Carbon\Carbon::parse($slot['date'] . ' ' . $slot['time']);
                                $slotEnd = $slotStart->copy()->addMinutes($selectedService->duration);
                            @endphp
                            <li class="group flex items-center gap-3 rounded-lg border border-zinc-200 bg-zinc-50/60 px-3 py-2 dark:border-zinc-800 dark:bg-zinc-950/40">
                                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-violet-500/10 text-violet-700 text-xs font-semibold dark:text-violet-300">
                                    {{ $index + 1 }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100 truncate">
                                        {{ $slotStart->format('D, M d') }}
                                    </div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                        {{ $slotStart->format('H:i') }} &ndash; {{ $slotEnd->format('H:i') }}
                                    </div>
                                </div>
                                <button wire:click="removeSlot({{ $index }})"
                                    title="Remove slot"
                                    class="shrink-0 rounded-md p-1 text-zinc-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-500/10 dark:hover:text-red-300 transition-colors">
                                    <flux:icon name="x-mark" class="w-4 h-4" />
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Auto-fill remaining slots --}}
            @if($selectedService && $selectedService->session_count > 1 && count($selectedSlots) > 0 && count($selectedSlots) < $selectedService->session_count)
                @php $remainingCount = $selectedService->session_count - count($selectedSlots); @endphp
                <div class="rounded-xl border border-sky-200/60 bg-sky-50/70 p-4 dark:border-sky-500/20 dark:bg-sky-500/5">
                    <div class="flex items-start gap-3">
                        <flux:icon name="bolt" class="w-5 h-5 text-sky-600 dark:text-sky-300 shrink-0 mt-0.5" />
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-sky-900 dark:text-sky-200">
                                Auto-fill remaining {{ $remainingCount }} {{ \Illuminate\Support\Str::plural('session', $remainingCount) }}
                            </p>
                            <p class="mt-1 text-xs text-sky-800/80 dark:text-sky-200/70">
                                Same time as your last selected slot, recurring on the interval you choose. Unavailable dates will be skipped and listed below.
                            </p>
                            <div class="mt-3 flex flex-wrap items-center gap-3">
                                <div class="w-48">
                                    <flux:select wire:model="autoFillInterval" size="sm">
                                        <option value="weekly">Weekly (+7 days)</option>
                                        <option value="biweekly">Bi-weekly (+14 days)</option>
                                        <option value="every_2d">Every 2 days</option>
                                        <option value="every_3d">Every 3 days</option>
                                    </flux:select>
                                </div>
                                <flux:button wire:click="autoFillRemaining" icon="bolt" variant="primary" size="sm">
                                    Auto-fill
                                </flux:button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Skipped dates warning --}}
            @if(!empty($autoFillSkipped))
                <div class="rounded-xl border border-amber-200/60 bg-amber-50/70 p-4 dark:border-amber-500/20 dark:bg-amber-500/5">
                    <div class="flex items-start gap-3">
                        <flux:icon name="exclamation-triangle" class="w-5 h-5 text-amber-600 dark:text-amber-300 shrink-0 mt-0.5" />
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-amber-900 dark:text-amber-200">
                                {{ count($autoFillSkipped) }} {{ \Illuminate\Support\Str::plural('date', count($autoFillSkipped)) }} skipped
                            </p>
                            <p class="mt-1 text-xs text-amber-800/80 dark:text-amber-200/70">
                                These dates were unavailable (conflict or outside business hours). Pick alternatives manually:
                            </p>
                            <ul class="mt-2 grid grid-cols-2 gap-x-4 gap-y-0.5 text-xs text-amber-800 dark:text-amber-200 list-disc pl-4">
                                @foreach($autoFillSkipped as $d)
                                    <li>{{ \Carbon\Carbon::parse($d)->format('D, M d, Y') }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <flux:label>Select Date</flux:label>
                    <flux:input type="date" wire:model.live="selectedDate" class="mt-2" />

                    <div class="mt-6">
                        <!-- Notice -->
                        <div class="rounded-xl bg-blue-50/50 border border-blue-100 p-4 dark:bg-sky-500/5 dark:border-sky-500/20 mb-4">
                            <div class="flex gap-3">
                                <flux:icon name="information-circle" class="w-5 h-5 text-blue-600 dark:text-sky-300 shrink-0" />
                                <div class="text-sm text-blue-700 dark:text-sky-200/90">
                                    <p class="font-medium">Booking Policy: <span class="font-normal opacity-90">All bookings are initially placed in a <strong>Pending</strong> status until confirmed by our staff.</span></p>
                                </div>
                            </div>
                        </div>

                        <!-- Legend -->
                        <div class="flex flex-wrap items-center gap-x-6 gap-y-2 p-3 rounded-lg bg-zinc-50 border border-zinc-100 dark:bg-zinc-900/40 dark:border-zinc-800/70">
                            <span class="text-xs font-semibold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">Status Key</span>
                            <div class="flex items-center gap-2">
                                <span class="flex h-2 w-2 rounded-full bg-emerald-500 ring-4 ring-emerald-500/20"></span>
                                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Available</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="flex h-2 w-2 rounded-full bg-amber-500 ring-4 ring-amber-500/20"></span>
                                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Pending <span class="text-zinc-400 dark:text-zinc-500 font-normal text-xs">(Bookable)</span></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="flex h-2 w-2 rounded-full bg-red-500 ring-4 ring-red-500/20"></span>
                                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Booked</span>
                            </div>
                            @if($selectedService && $selectedService->session_count > 1)
                                <div class="flex items-center gap-2">
                                    <span class="flex h-2 w-2 rounded-full bg-violet-500 ring-4 ring-violet-500/20"></span>
                                    <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Selected</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div>
                    <flux:label>Available Slots</flux:label>
                    {{-- Poll so a coach's mid-session availability change is reflected without a manual reload. --}}
                    <div class="grid grid-cols-3 gap-2 mt-2" wire:poll.30s>
                        @foreach($this->timeSlots as $slot)
                            <button wire:click="selectDateTime('{{ $selectedDate }}', '{{ $slot['time'] }}')"
                                @if(!$slot['is_bookable']) disabled @endif
                                class="px-4 py-2 text-sm font-medium rounded-lg border transition-colors flex flex-col items-center justify-center gap-1
                                    {{ $slot['is_already_selected']
                                        ? 'bg-violet-600 text-white border-violet-600 dark:bg-violet-500/80 dark:border-violet-500/60'
                                        : ($selectedTime === $slot['time'] ? 'bg-red-600 text-white border-red-600 dark:bg-red-500/80 dark:border-red-500/60' : 'bg-white text-zinc-700 border-zinc-200 dark:bg-zinc-900/50 dark:border-zinc-800 dark:text-zinc-200') }}
                                    {{ $slot['is_bookable'] && !$slot['is_already_selected'] ? 'hover:border-red-500 hover:bg-red-50 hover:text-red-700 dark:hover:border-red-500/40 dark:hover:bg-red-500/10 dark:hover:text-red-300' : '' }}
                                    {{ (!$slot['is_bookable'] && !$slot['is_already_selected']) ? 'opacity-60 cursor-not-allowed bg-zinc-50 dark:bg-zinc-900/30' : '' }}">
                                <span>{{ $slot['start_formatted'] }} - {{ $slot['end_formatted'] }}</span>

                                @if($slot['is_already_selected'])
                                    <span class="inline-flex items-center rounded-md bg-white/20 px-1.5 py-0.5 text-xs font-medium text-white">
                                        Selected
                                    </span>
                                @elseif($slot['status'] === 'available')
                                    <span class="inline-flex items-center rounded-md bg-emerald-100 px-1.5 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-1 dark:ring-inset dark:ring-emerald-500/20">
                                        Available
                                    </span>
                                @elseif($slot['status'] === 'booked')
                                    <span class="inline-flex items-center rounded-md bg-amber-100 px-1.5 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-1 dark:ring-inset dark:ring-amber-500/20">
                                        {{ $slot['pending_count'] }} Pending
                                    </span>
                                @elseif($slot['status'] === 'confirmed')
                                    <span class="inline-flex items-center rounded-md bg-red-100 px-1.5 py-0.5 text-xs font-medium text-red-800 dark:bg-red-500/10 dark:text-red-300 dark:ring-1 dark:ring-inset dark:ring-red-500/20">
                                        Booked
                                    </span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Step 4: Confirm --}}
    @if ($step === 4)
        <div class="space-y-6 max-w-2xl mx-auto">
            <div class="flex items-center gap-4">
                <flux:button variant="ghost" icon="arrow-left" wire:click="previousStep">Back</flux:button>
                <flux:heading size="xl">Confirm Details</flux:heading>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-zinc-200 overflow-hidden dark:bg-zinc-900/60 dark:border-zinc-800 dark:shadow-none">

                {{-- Card Header --}}
                <div class="relative overflow-hidden border-b border-zinc-200 dark:border-zinc-800 px-6 py-5 bg-gradient-to-r from-red-500/5 via-orange-500/5 to-transparent dark:from-red-500/10 dark:via-orange-500/5 dark:to-transparent">
                    <div class="flex items-center gap-4">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-red-500 to-orange-500 text-white shadow-md shadow-red-500/20 dark:shadow-none">
                            <flux:icon name="check-circle" class="w-6 h-6" />
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Booking Summary</h3>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Please review your appointment details before confirming</p>
                        </div>
                    </div>
                </div>

                <div class="p-6 space-y-5">
                    {{-- Top row: Service + Specialist --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="rounded-xl border border-zinc-200 bg-zinc-50/50 p-4 dark:border-zinc-800 dark:bg-zinc-950/40">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="flex h-7 w-7 items-center justify-center rounded-md bg-red-500/10 text-red-600 dark:text-red-300">
                                    <flux:icon name="squares-plus" class="w-4 h-4" />
                                </div>
                                <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Service</p>
                            </div>
                            <h4 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 leading-snug">{{ $selectedService->name }}</h4>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">{{ $selectedService->duration }} min per session</p>
                        </div>

                        <div class="rounded-xl border border-zinc-200 bg-zinc-50/50 p-4 dark:border-zinc-800 dark:bg-zinc-950/40">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="flex h-7 w-7 items-center justify-center rounded-md bg-amber-500/10 text-amber-600 dark:text-amber-300">
                                    <flux:icon name="user" class="w-4 h-4" />
                                </div>
                                <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Specialist</p>
                            </div>
                            <h4 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 leading-snug">{{ $selectedStaff->name }}</h4>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">Your therapist</p>
                        </div>
                    </div>

                    {{-- Sessions block --}}
                    @if($selectedService->session_count > 1)
                        <div class="rounded-xl border border-zinc-200 bg-zinc-50/50 p-4 dark:border-zinc-800 dark:bg-zinc-950/40">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-2">
                                    <div class="flex h-7 w-7 items-center justify-center rounded-md bg-violet-500/10 text-violet-600 dark:text-violet-300">
                                        <flux:icon name="rectangle-stack" class="w-4 h-4" />
                                    </div>
                                    <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Scheduled Sessions</p>
                                </div>
                                <span class="text-xs font-medium text-violet-700 bg-violet-500/10 px-2 py-0.5 rounded-full dark:text-violet-300">
                                    {{ $selectedService->session_count }} sessions
                                </span>
                            </div>
                            <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @foreach($selectedSlots as $index => $slot)
                                    @php
                                        $s = \Carbon\Carbon::parse($slot['date'] . ' ' . $slot['time']);
                                        $e = $s->copy()->addMinutes($selectedService->duration);
                                    @endphp
                                    <li class="flex items-center gap-3 rounded-lg border border-zinc-200 bg-white px-3 py-2 dark:border-zinc-800 dark:bg-zinc-900/60">
                                        <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-violet-500/10 text-violet-700 text-xs font-semibold dark:text-violet-300">
                                            {{ $index + 1 }}
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100 truncate">
                                                {{ $s->format('D, M d, Y') }}
                                            </div>
                                            <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                {{ $s->format('H:i') }} &ndash; {{ $e->format('H:i') }}
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-xl border border-zinc-200 bg-zinc-50/50 p-4 dark:border-zinc-800 dark:bg-zinc-950/40">
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="flex h-7 w-7 items-center justify-center rounded-md bg-sky-500/10 text-sky-600 dark:text-sky-300">
                                        <flux:icon name="calendar" class="w-4 h-4" />
                                    </div>
                                    <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Date</p>
                                </div>
                                <h4 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                                    {{ \Carbon\Carbon::parse($selectedDate)->format('D, M d, Y') }}
                                </h4>
                            </div>
                            <div class="rounded-xl border border-zinc-200 bg-zinc-50/50 p-4 dark:border-zinc-800 dark:bg-zinc-950/40">
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="flex h-7 w-7 items-center justify-center rounded-md bg-emerald-500/10 text-emerald-600 dark:text-emerald-300">
                                        <flux:icon name="clock" class="w-4 h-4" />
                                    </div>
                                    <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Time</p>
                                </div>
                                @php
                                    $ss = \Carbon\Carbon::parse($selectedDate . ' ' . $selectedTime);
                                    $ee = $ss->copy()->addMinutes($selectedService->duration);
                                @endphp
                                <h4 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                                    {{ $ss->format('H:i') }} &ndash; {{ $ee->format('H:i') }}
                                </h4>
                            </div>
                        </div>
                    @endif

                    {{-- Total Price --}}
                    <div class="rounded-xl border border-zinc-200 bg-gradient-to-r from-red-500/5 to-orange-500/5 p-4 flex items-center justify-between dark:border-zinc-800 dark:from-red-500/10 dark:to-orange-500/5">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Total Price</p>
                            @if($selectedService->session_count > 1)
                                <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-0.5">package — covers all {{ $selectedService->session_count }} sessions</p>
                            @endif
                        </div>
                        <span class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                            @if((float)$selectedService->price === 0.0)
                                <span class="text-emerald-600 dark:text-emerald-400">FREE</span>
                            @else
                                £{{ $selectedService->price }}
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            <flux:textarea wire:model="notes" label="Special Requests" placeholder="Add any notes for your therapist..." />

            <flux:button wire:click="submit"
                class="w-full h-12 text-lg font-medium bg-gradient-to-r from-red-500 to-orange-500 dark:from-red-500/90 dark:to-orange-500/90 text-white border-0 shadow-lg hover:shadow-xl transition-all duration-300">
                Confirm Booking
            </flux:button>
        </div>
    @endif
</div>
