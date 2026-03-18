<div class="space-y-6">
    {{-- Header --}}
    <div>
        <flux:heading size="xl">Book Appointment</flux:heading>
        <flux:text class="mt-1 text-zinc-500">Schedule a new appointment in a few simple steps.</flux:text>
    </div>

    {{-- Progress --}}
    <div class="mb-8">
        <div class="flex items-center justify-between text-sm font-medium text-zinc-500 mb-2">
            <span class="{{ $step >= 1 ? 'text-red-600' : '' }}">Service</span>
            <span class="{{ $step >= 2 ? 'text-red-600' : '' }}">Staff</span>
            <span class="{{ $step >= 3 ? 'text-red-600' : '' }}">Date & Time</span>
            <span class="{{ $step >= 4 ? 'text-red-600' : '' }}">Confirm</span>
        </div>
        <div class="h-2 bg-zinc-100 rounded-full overflow-hidden">
            <div class="h-full bg-gradient-to-r from-red-500 to-orange-500 transition-all duration-500 ease-out"
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
                        class="group relative overflow-hidden rounded-xl border border-zinc-200 bg-white p-5 text-left shadow-sm transition-shadow duration-300 hover:shadow-lg dark:border-zinc-700 dark:bg-zinc-800">
                        <div class="absolute inset-0 transition-transform duration-300 group-hover:scale-[1.02]"></div>
                        <div class="relative z-10">
                            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                                <flux:icon name="squares-plus" class="w-16 h-16 text-current"
                                    style="color: {{ $service->color }}" />
                            </div>
                            <div class="h-2 w-12 rounded-full mb-4" style="background-color: {{ $service->color }}"></div>
                            <h3 class="font-semibold text-lg">{{ $service->name }}</h3>
                            <p class="text-sm text-zinc-500 mt-1 line-clamp-2">{{ $service->description }}</p>
                            <div class="mt-4 flex items-center justify-between flex-wrap gap-2">
                                @if((float) $service->price === 0.0)
                                    <span class="inline-flex items-center rounded-sm bg-emerald-100 px-2.5 py-0.5 text-xs font-bold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                                        FREE
                                    </span>
                                @else
                                    <span class="font-medium text-zinc-900 dark:text-zinc-100">${{ $service->price }}</span>
                                @endif
                                <div class="flex items-center gap-2">
                                    @if($service->session_count > 1)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-fuchsia-100 px-2 py-0.5 text-xs font-medium text-fuchsia-700 dark:bg-fuchsia-900/30 dark:text-fuchsia-400">
                                            <flux:icon name="rectangle-stack" class="w-3 h-3" />
                                            {{ $service->session_count }} sessions
                                        </span>
                                    @endif
                                    <span class="text-xs text-zinc-400">{{ $service->duration }} mins</span>
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
                        class="group relative overflow-hidden rounded-xl border border-zinc-200 bg-white p-5 text-left shadow-sm transition-shadow duration-300 hover:shadow-lg dark:border-zinc-700 dark:bg-zinc-800">
                        <div class="absolute inset-0 transition-transform duration-300 group-hover:scale-[1.02]"></div>
                        <div class="relative z-10 flex items-center gap-4">
                            <div class="h-12 w-12 rounded-full bg-gradient-to-br from-amber-500 to-amber-600 flex items-center justify-center text-white font-bold text-lg">
                                {{ $staff->initials() }}
                            </div>
                            <div>
                                <h3 class="font-semibold">{{ $staff->name }}</h3>
                                <p class="text-sm text-zinc-500">Specialist</p>
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

            {{-- Selected slots sidebar for multi-session --}}
            @if($selectedService && $selectedService->session_count > 1 && count($selectedSlots) > 0)
                <div class="rounded-xl border border-fuchsia-100 bg-fuchsia-50 p-4 dark:border-fuchsia-900/50 dark:bg-fuchsia-900/10">
                    <h4 class="text-sm font-semibold text-fuchsia-700 dark:text-fuchsia-400 mb-3">
                        Selected Slots ({{ count($selectedSlots) }}/{{ $selectedService->session_count }})
                    </h4>
                    <ul class="space-y-2">
                        @foreach($selectedSlots as $index => $slot)
                            <li class="flex items-center justify-between text-sm">
                                <span class="text-zinc-700 dark:text-zinc-300">
                                    <span class="font-medium">Slot {{ $index + 1 }}:</span>
                                    {{ \Carbon\Carbon::parse($slot['date'])->format('M d, Y') }} at {{ $slot['time'] }}
                                </span>
                                <button wire:click="removeSlot({{ $index }})"
                                    class="text-xs text-red-500 hover:text-red-700 ml-4 shrink-0">
                                    Remove
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <flux:label>Select Date</flux:label>
                    <flux:input type="date" wire:model.live="selectedDate" class="mt-2" />

                    <div class="mt-6">
                        <!-- Notice -->
                        <div class="rounded-xl bg-blue-50/50 border border-blue-100 p-4 dark:bg-blue-900/10 dark:border-blue-800 mb-4">
                            <div class="flex gap-3">
                                <flux:icon name="information-circle" class="w-5 h-5 text-blue-600 dark:text-blue-400 shrink-0" />
                                <div class="text-sm text-blue-700 dark:text-blue-300">
                                    <p class="font-medium">Booking Policy: <span class="font-normal opacity-90">All bookings are initially placed in a <strong>Pending</strong> status until confirmed by our staff.</span></p>
                                </div>
                            </div>
                        </div>

                        <!-- Legend -->
                        <div class="flex flex-wrap items-center gap-x-6 gap-y-2 p-3 rounded-lg bg-zinc-50 border border-zinc-100 dark:bg-zinc-800/30 dark:border-zinc-700/50">
                            <span class="text-xs font-semibold text-zinc-400 uppercase tracking-wider">Status Key</span>
                            <div class="flex items-center gap-2">
                                <span class="flex h-2 w-2 rounded-full bg-emerald-500 ring-4 ring-emerald-500/20"></span>
                                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Available</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="flex h-2 w-2 rounded-full bg-amber-500 ring-4 ring-amber-500/20"></span>
                                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Pending <span class="text-zinc-400 font-normal text-xs">(Bookable)</span></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="flex h-2 w-2 rounded-full bg-red-500 ring-4 ring-red-500/20"></span>
                                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Booked</span>
                            </div>
                            @if($selectedService && $selectedService->session_count > 1)
                                <div class="flex items-center gap-2">
                                    <span class="flex h-2 w-2 rounded-full bg-fuchsia-500 ring-4 ring-fuchsia-500/20"></span>
                                    <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Selected</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div>
                    <flux:label>Available Slots</flux:label>
                    <div class="grid grid-cols-3 gap-2 mt-2">
                        @foreach($this->timeSlots as $slot)
                            <button wire:click="selectDateTime('{{ $selectedDate }}', '{{ $slot['time'] }}')"
                                @if(!$slot['is_bookable']) disabled @endif
                                class="px-4 py-2 text-sm font-medium rounded-lg border transition-colors flex flex-col items-center justify-center gap-1
                                    {{ $slot['is_already_selected']
                                        ? 'bg-fuchsia-600 text-white border-fuchsia-600'
                                        : ($selectedTime === $slot['time'] ? 'bg-red-600 text-white border-red-600' : 'bg-white text-zinc-700 border-zinc-200 dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-200') }}
                                    {{ $slot['is_bookable'] && !$slot['is_already_selected'] ? 'hover:border-red-500 hover:bg-red-50 hover:text-red-700' : '' }}
                                    {{ (!$slot['is_bookable'] && !$slot['is_already_selected']) ? 'opacity-60 cursor-not-allowed bg-zinc-50' : '' }}">
                                <span>{{ $slot['start_formatted'] }} - {{ $slot['end_formatted'] }}</span>

                                @if($slot['is_already_selected'])
                                    <span class="inline-flex items-center rounded-sm bg-fuchsia-200 px-1.5 py-0.5 text-xs font-medium text-fuchsia-800">
                                        Selected
                                    </span>
                                @elseif($slot['status'] === 'available')
                                    <span class="inline-flex items-center rounded-sm bg-emerald-100 px-1.5 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                                        Available
                                    </span>
                                @elseif($slot['status'] === 'booked')
                                    <span class="inline-flex items-center rounded-sm bg-amber-100 px-1.5 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">
                                        {{ $slot['pending_count'] }} Pending
                                    </span>
                                @elseif($slot['status'] === 'confirmed')
                                    <span class="inline-flex items-center rounded-sm bg-red-100 px-1.5 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900/30 dark:text-red-400">
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

            <div class="bg-white rounded-2xl shadow-lg border border-zinc-100 overflow-hidden dark:bg-zinc-800 dark:border-zinc-700">

                {{-- Card Header --}}
                <div class="bg-gradient-to-br from-red-500 to-orange-500 p-8 text-white relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-8 opacity-10">
                        <flux:icon name="check-circle" class="w-32 h-32" />
                    </div>
                    <div class="relative z-10">
                        <h3 class="text-2xl font-bold">Booking Summary</h3>
                        <p class="text-red-50 mt-1">Please review your appointment details</p>
                    </div>
                </div>

                <div class="p-8 space-y-6">
                    {{-- Service --}}
                    <div class="flex items-start gap-4">
                        <div class="p-2 rounded-lg bg-red-50 text-red-600 dark:bg-red-400/10 dark:text-red-400">
                            <flux:icon name="squares-plus" class="w-6 h-6" />
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-zinc-500">Service</p>
                            <h4 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ $selectedService->name }}</h4>
                            <p class="text-sm text-zinc-400">{{ $selectedService->duration }} minutes per session</p>
                        </div>
                    </div>

                    {{-- Staff --}}
                    <div class="flex items-start gap-4">
                        <div class="p-2 rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-400/10 dark:text-amber-400">
                            <flux:icon name="user" class="w-6 h-6" />
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-zinc-500">Specialist</p>
                            <h4 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ $selectedStaff->name }}</h4>
                        </div>
                    </div>

                    {{-- Date / Time --}}
                    @if($selectedService->session_count > 1)
                        {{-- Multi-session: list all slots --}}
                        <div class="flex items-start gap-4">
                            <div class="p-2 rounded-lg bg-fuchsia-50 text-fuchsia-600 dark:bg-fuchsia-400/10 dark:text-fuchsia-400">
                                <flux:icon name="rectangle-stack" class="w-6 h-6" />
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-zinc-500">
                                    Sessions ({{ $selectedService->session_count }})
                                </p>
                                <ul class="mt-1 space-y-1">
                                    @foreach($selectedSlots as $index => $slot)
                                        <li class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                                            Session {{ $index + 1 }}: {{ \Carbon\Carbon::parse($slot['date'])->format('M d, Y') }} at {{ $slot['time'] }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @else
                        {{-- Single session: date + time grid --}}
                        <div class="grid grid-cols-2 gap-6">
                            <div class="flex items-start gap-4">
                                <div class="p-2 rounded-lg bg-sky-50 text-sky-600 dark:bg-sky-400/10 dark:text-sky-400">
                                    <flux:icon name="calendar" class="w-6 h-6" />
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-zinc-500">Date</p>
                                    <h4 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                                        {{ \Carbon\Carbon::parse($selectedDate)->format('M d, Y') }}
                                    </h4>
                                </div>
                            </div>
                            <div class="flex items-start gap-4">
                                <div class="p-2 rounded-lg bg-green-50 text-green-600 dark:bg-green-400/10 dark:text-green-400">
                                    <flux:icon name="clock" class="w-6 h-6" />
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-zinc-500">Time</p>
                                    <h4 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ $selectedTime }}</h4>
                                </div>
                            </div>
                        </div>
                    @endif

                    <flux:separator />

                    <div class="flex items-center justify-between pt-2">
                        <span class="text-lg font-medium text-zinc-600 dark:text-zinc-300">Total Price</span>
                        <div class="text-right">
                            @if($selectedService->session_count > 1)
                                <p class="text-xs text-zinc-400">package price — covers all {{ $selectedService->session_count }} sessions</p>
                            @endif
                            <span class="text-3xl font-bold text-zinc-900 dark:text-white">
                                @if((float)$selectedService->price === 0.0)
                                    FREE
                                @else
                                    ${{ $selectedService->price }}
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <flux:textarea wire:model="notes" label="Special Requests" placeholder="Add any notes for your therapist..." />

            <flux:button wire:click="submit"
                class="w-full h-12 text-lg font-medium bg-gradient-to-r from-red-500 to-orange-500 text-white border-0 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-[1.01]">
                Confirm Booking
            </flux:button>
        </div>
    @endif
</div>
