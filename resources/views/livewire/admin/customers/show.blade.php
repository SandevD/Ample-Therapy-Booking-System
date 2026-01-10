<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.customers') }}" wire:navigate
                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-white shadow-sm ring-1 ring-zinc-200 transition-colors hover:bg-zinc-50 hover:text-zinc-900 dark:bg-zinc-800 dark:ring-zinc-700 dark:hover:bg-zinc-700 dark:hover:text-zinc-100">
                    <flux:icon name="chevron-left" class="h-4 w-4 text-zinc-500" />
                </a>
                <div>
                    <flux:heading size="xl">{{ $user->name }}</flux:heading>
                    <flux:text class="text-zinc-500">Customer Profile</flux:text>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Grid --}}
    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Total Bookings --}}
        <div
            class="group relative overflow-hidden rounded-xl bg-gradient-to-br from-sky-500 to-sky-600 p-5 text-white shadow-lg transition-all duration-300 ease-out hover:shadow-xl hover:scale-[1.02]">
            <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
            <div class="absolute -right-3 -bottom-8 h-24 w-24 rounded-full bg-white/5"></div>
            <div class="relative">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium text-white/80">Total Bookings</div>
                        <div class="mt-1 text-4xl font-bold">{{ $stats['total'] }}</div>
                        <div class="text-sm text-white/70">all time</div>
                    </div>
                    <div class="rounded-xl bg-white/20 p-3 backdrop-blur-sm">
                        <flux:icon name="calendar" class="h-7 w-7" />
                    </div>
                </div>
            </div>
        </div>

        {{-- Confirmed --}}
        <div
            class="group relative overflow-hidden rounded-xl bg-gradient-to-br from-purple-500 to-purple-600 p-5 text-white shadow-lg transition-all duration-300 ease-out hover:shadow-xl hover:scale-[1.02]">
            <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
            <div class="absolute -right-3 -bottom-8 h-24 w-24 rounded-full bg-white/5"></div>
            <div class="relative">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium text-white/80">Confirmed</div>
                        <div class="mt-1 text-4xl font-bold">{{ $stats['confirmed'] }}</div>
                        <div class="text-sm text-white/70">active</div>
                    </div>
                    <div class="rounded-xl bg-white/20 p-3 backdrop-blur-sm">
                        <flux:icon name="check" class="h-7 w-7" />
                    </div>
                </div>
            </div>
        </div>

        {{-- Completed --}}
        <div
            class="group relative overflow-hidden rounded-xl bg-gradient-to-br from-green-500 to-green-600 p-5 text-white shadow-lg transition-all duration-300 ease-out hover:shadow-xl hover:scale-[1.02]">
            <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
            <div class="absolute -right-3 -bottom-8 h-24 w-24 rounded-full bg-white/5"></div>
            <div class="relative">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium text-white/80">Completed</div>
                        <div class="mt-1 text-4xl font-bold">{{ $stats['completed'] }}</div>
                        <div class="text-sm text-white/70">sessions</div>
                    </div>
                    <div class="rounded-xl bg-white/20 p-3 backdrop-blur-sm">
                        <flux:icon name="check-circle" class="h-7 w-7" />
                    </div>
                </div>
            </div>
        </div>

        {{-- Cancelled --}}
        <div
            class="group relative overflow-hidden rounded-xl bg-gradient-to-br from-rose-500 to-rose-600 p-5 text-white shadow-lg transition-all duration-300 ease-out hover:shadow-xl hover:scale-[1.02]">
            <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
            <div class="absolute -right-3 -bottom-8 h-24 w-24 rounded-full bg-white/5"></div>
            <div class="relative">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium text-white/80">Cancelled</div>
                        <div class="mt-1 text-4xl font-bold">{{ $stats['cancelled'] }}</div>
                        <div class="text-sm text-white/70">archived</div>
                    </div>
                    <div class="rounded-xl bg-white/20 p-3 backdrop-blur-sm">
                        <flux:icon name="x-circle" class="h-7 w-7" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Profile Info --}}
        <div class="space-y-6">
            <div
                class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm transition-shadow hover:shadow-md dark:border-zinc-700 dark:bg-zinc-800">
                <div class="flex items-center gap-3 mb-6">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-700">
                        <flux:icon name="user" class="h-5 w-5 text-zinc-500" />
                    </div>
                    <h3 class="font-semibold text-lg text-zinc-900 dark:text-zinc-100">Contact Details</h3>
                </div>

                <div class="space-y-5">
                    <div class="flex items-start gap-4">
                        <div class="mt-0.5 rounded-lg bg-zinc-50 p-2 dark:bg-zinc-700/50">
                            <flux:icon name="envelope" class="w-4 h-4 text-zinc-500" />
                        </div>
                        <div>
                            <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Email Address</div>
                            <div class="text-sm text-zinc-500">{{ $user->email }}</div>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="mt-0.5 rounded-lg bg-zinc-50 p-2 dark:bg-zinc-700/50">
                            <flux:icon name="phone" class="w-4 h-4 text-zinc-500" />
                        </div>
                        <div>
                            <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Phone Number</div>
                            <div class="text-sm text-zinc-500">{{ $user->phone ?? 'Not provided' }}</div>
                        </div>
                    </div>

                    @if($user->notes)
                        <div class="flex items-start gap-4">
                            <div class="mt-0.5 rounded-lg bg-zinc-50 p-2 dark:bg-zinc-700/50">
                                <flux:icon name="document-text" class="w-4 h-4 text-zinc-500" />
                            </div>
                            <div>
                                <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Notes</div>
                                <div class="text-sm text-zinc-500">{{ $user->notes }}</div>
                            </div>
                        </div>
                    @endif

                    <div class="pt-5 mt-2 border-t border-zinc-100 dark:border-zinc-700">
                        <div class="flex items-center gap-2 text-xs font-medium text-zinc-400 uppercase tracking-wide">
                            <flux:icon name="clock" class="w-3.5 h-3.5" />
                            <span>Joined {{ $user->created_at->format('F j, Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Appointments List --}}
        <div class="lg:col-span-2 space-y-4">
            <flux:heading size="lg">Appointment History</flux:heading>

            @if($appointments->count() > 0)
                <div class="space-y-3">
                    @foreach($appointments as $appointment)
                        <div
                            class="group relative overflow-hidden rounded-xl border border-zinc-200 bg-white p-4 shadow-sm transition-[box-shadow,border-color] duration-300 ease-out hover:shadow-md hover:border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-zinc-600">
                            {{-- Service Color Strip --}}
                            <div class="absolute left-0 top-0 bottom-0 w-1.5"
                                style="background-color: {{ $appointment->service->color }}"></div>

                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between pl-3">
                                <div class="flex items-center gap-4">
                                    {{-- Date Box --}}
                                    <div
                                        class="flex flex-col items-center justify-center rounded-lg bg-zinc-50 px-3 py-2 text-center min-w-[3.5rem] border border-zinc-100 dark:bg-zinc-700/50 dark:border-zinc-700">
                                        <span
                                            class="text-xs font-bold uppercase text-zinc-400">{{ $appointment->starts_at->format('M') }}</span>
                                        <span
                                            class="text-xl font-bold text-zinc-900 dark:text-zinc-100 leading-none mt-0.5">{{ $appointment->starts_at->format('d') }}</span>
                                    </div>

                                    <div>
                                        <h4 class="font-semibold text-zinc-900 dark:text-zinc-100 text-base">
                                            {{ $appointment->service->name }}
                                        </h4>
                                        <div class="flex items-center gap-2 text-sm text-zinc-500 mt-0.5">
                                            <flux:icon name="clock" class="w-3.5 h-3.5 text-zinc-400" />
                                            <span>{{ $appointment->starts_at->format('g:i A') }} -
                                                {{ $appointment->ends_at->format('g:i A') }}</span>
                                        </div>
                                        <div class="flex items-center gap-2 text-sm text-zinc-500 mt-0.5 lg:hidden">
                                            <span class="text-zinc-400">w/</span> {{ $appointment->user->name }}
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between sm:justify-end gap-4">
                                    <div class="hidden lg:flex items-center gap-2 text-sm text-zinc-500 mr-4">
                                        <div
                                            class="flex h-6 w-6 items-center justify-center rounded-full bg-zinc-100 text-xs font-medium text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">
                                            {{ substr($appointment->user->name, 0, 1) }}
                                        </div>
                                        <span>{{ $appointment->user->name }}</span>
                                    </div>

                                    <flux:badge size="sm" :color="$appointment->status_color" inset="top bottom">
                                        {{ ucfirst($appointment->status) }}
                                    </flux:badge>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div class="mt-4">
                        {{ $appointments->links() }}
                    </div>
                </div>
            @else
                <div
                    class="relative overflow-hidden rounded-xl border-2 border-dashed border-zinc-200 bg-zinc-50 p-12 text-center dark:border-zinc-700 dark:bg-zinc-800/50">
                    <flux:icon name="calendar" class="mx-auto h-12 w-12 text-zinc-300 dark:text-zinc-600" />
                    <h3 class="mt-2 text-sm font-semibold text-zinc-900 dark:text-zinc-100">No appointments</h3>
                    <p class="mt-1 text-sm text-zinc-500">This customer hasn't made any bookings yet.</p>
                </div>
            @endif
        </div>
    </div>
</div>