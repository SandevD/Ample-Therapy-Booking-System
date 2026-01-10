<div class="space-y-6">
    {{-- Header --}}
    <div>
        <flux:heading size="xl">Dashboard</flux:heading>
        <flux:text class="mt-1 text-zinc-500">Welcome back! Here's an overview of your appointments.</flux:text>
    </div>

    @if($isAdmin ?? true)
        {{-- Admin Stats Cards --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {{-- Today's Appointments --}}
            <div
                class="group relative overflow-hidden rounded-xl bg-gradient-to-br from-sky-500 to-sky-600 p-5 text-white shadow-lg transition-all duration-300 ease-out hover:shadow-xl hover:scale-[1.02]">
                <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
                <div class="absolute -right-3 -bottom-8 h-24 w-24 rounded-full bg-white/5"></div>
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm font-medium text-white/80">Today</div>
                            <div class="mt-1 text-4xl font-bold">{{ $todayCount }}</div>
                            <div class="text-sm text-white/70">appointments</div>
                        </div>
                        <div class="rounded-xl bg-white/20 p-3 backdrop-blur-sm">
                            <flux:icon name="calendar" class="h-7 w-7" />
                        </div>
                    </div>
                </div>
            </div>

            {{-- Pending Requests (This Week) --}}
            <div
                class="group relative overflow-hidden rounded-xl bg-gradient-to-br from-green-500 to-green-600 p-5 text-white shadow-lg transition-all duration-300 ease-out hover:shadow-xl hover:scale-[1.02]">
                <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
                <div class="absolute -right-3 -bottom-8 h-24 w-24 rounded-full bg-white/5"></div>
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm font-medium text-white/80">Pending Requests</div>
                            <div class="mt-1 text-4xl font-bold">{{ $pendingThisWeekCount }}</div>
                            <div class="text-sm text-white/70">this week</div>
                        </div>
                        <div class="rounded-xl bg-white/20 p-3 backdrop-blur-sm">
                            <flux:icon name="inbox-stack" class="h-7 w-7" />
                        </div>
                    </div>
                </div>
            </div>

            {{-- Active Services --}}
            <div
                class="group relative overflow-hidden rounded-xl bg-gradient-to-br from-red-500 to-orange-500 p-5 text-white shadow-lg transition-all duration-300 ease-out hover:shadow-xl hover:scale-[1.02]">
                <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
                <div class="absolute -right-3 -bottom-8 h-24 w-24 rounded-full bg-white/5"></div>
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm font-medium text-white/80">Services</div>
                            <div class="mt-1 text-4xl font-bold">{{ $totalServices }}</div>
                            <div class="text-sm text-white/70">active</div>
                        </div>
                        <div class="rounded-xl bg-white/20 p-3 backdrop-blur-sm">
                            <flux:icon name="squares-plus" class="h-7 w-7" />
                        </div>
                    </div>
                </div>
            </div>

            {{-- Upcoming Confirmed (Next 7 Days) --}}
            <div
                class="group relative overflow-hidden rounded-xl bg-gradient-to-br from-purple-500 to-purple-600 p-5 text-white shadow-lg transition-all duration-300 ease-out hover:shadow-xl hover:scale-[1.02]">
                <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
                <div class="absolute -right-3 -bottom-8 h-24 w-24 rounded-full bg-white/5"></div>
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm font-medium text-white/80">Upcoming</div>
                            <div class="mt-1 text-4xl font-bold">{{ $confirmedUpcomingCount }}</div>
                            <div class="text-sm text-white/70">confirmed (7 days)</div>
                        </div>
                        <div class="rounded-xl bg-white/20 p-3 backdrop-blur-sm">
                            <flux:icon name="calendar-days" class="h-7 w-7" />
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Quick Actions & Upcoming Appointments --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- Quick Actions --}}
            <div class="space-y-4">
                <flux:heading size="lg">Quick Actions</flux:heading>
                <div class="grid grid-cols-1 gap-3">
                    {{-- New Appointment --}}
                    <a href="{{ route('admin.appointments') }}" wire:navigate
                        class="group relative overflow-hidden rounded-xl bg-gradient-to-br from-red-500 to-orange-500 p-4 text-white shadow-lg transition-all duration-300 ease-out hover:shadow-xl hover:scale-[1.02]">
                        <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-white/10"></div>
                        <div class="absolute -right-2 -bottom-6 h-20 w-20 rounded-full bg-white/5"></div>
                        <div class="relative flex items-center gap-4">
                            <div class="rounded-lg bg-white/20 p-2.5 backdrop-blur-sm">
                                <flux:icon name="plus" class="h-5 w-5" />
                            </div>
                            <div>
                                <div class="font-semibold">New Appointment</div>
                                <div class="text-sm text-white/80">Book a new slot</div>
                            </div>
                            <flux:icon name="chevron-right"
                                class="ml-auto h-5 w-5 opacity-60 transition-transform group-hover:translate-x-1" />
                        </div>
                    </a>

                    {{-- Manage Services --}}
                    <a href="{{ route('admin.services') }}" wire:navigate
                        class="group relative overflow-hidden rounded-xl bg-gradient-to-br from-green-500 to-green-600 p-4 text-white shadow-lg transition-all duration-300 ease-out hover:shadow-xl hover:scale-[1.02]">
                        <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-white/10"></div>
                        <div class="absolute -right-2 -bottom-6 h-20 w-20 rounded-full bg-white/5"></div>
                        <div class="relative flex items-center gap-4">
                            <div class="rounded-lg bg-white/20 p-2.5 backdrop-blur-sm">
                                <flux:icon name="squares-plus" class="h-5 w-5" />
                            </div>
                            <div>
                                <div class="font-semibold">Services</div>
                                <div class="text-sm text-white/80">Manage offerings</div>
                            </div>
                            <flux:icon name="chevron-right"
                                class="ml-auto h-5 w-5 opacity-60 transition-transform group-hover:translate-x-1" />
                        </div>
                    </a>

                    {{-- Manage Staff --}}
                    <a href="{{ route('admin.staff') }}" wire:navigate
                        class="group relative overflow-hidden rounded-xl bg-gradient-to-br from-amber-500 to-amber-600 p-4 text-white shadow-lg transition-all duration-300 ease-out hover:shadow-xl hover:scale-[1.02]">
                        <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-white/10"></div>
                        <div class="absolute -right-2 -bottom-6 h-20 w-20 rounded-full bg-white/5"></div>
                        <div class="relative flex items-center gap-4">
                            <div class="rounded-lg bg-white/20 p-2.5 backdrop-blur-sm">
                                <flux:icon name="user-circle" class="h-5 w-5" />
                            </div>
                            <div>
                                <div class="font-semibold">Staff</div>
                                <div class="text-sm text-white/80">Team management</div>
                            </div>
                            <flux:icon name="chevron-right"
                                class="ml-auto h-5 w-5 opacity-60 transition-transform group-hover:translate-x-1" />
                        </div>
                    </a>
                </div>
            </div>

            {{-- Upcoming Appointments --}}
            <div class="lg:col-span-2 space-y-4">
                <div class="flex items-center justify-between">
                    <flux:heading size="lg">Upcoming Appointments</flux:heading>
                    <a href="{{ route('admin.appointments') }}" wire:navigate
                        class="inline-flex items-center gap-1 text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100">
                        View All
                        <flux:icon name="arrow-right" class="h-4 w-4" />
                    </a>
                </div>

                @if($upcomingAppointments->count() > 0)
                    <div class="space-y-3">
                        @foreach($upcomingAppointments as $appointment)
                            <div
                                class="group relative overflow-hidden rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-4">
                                        {{-- Color indicator --}}
                                        <div class="h-12 w-1.5 rounded-full"
                                            style="background-color: {{ $appointment->service->color }}"></div>
                                        <div>
                                            <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                                {{ $appointment->customer_name }}
                                            </div>
                                            <div class="mt-0.5 flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
                                                <span>{{ $appointment->service->name }}</span>
                                                <span class="text-zinc-300 dark:text-zinc-600">•</span>
                                                <span>{{ $appointment->user->name }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                            {{ $appointment->starts_at->format('M j') }}
                                        </div>
                                        <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                            {{ $appointment->starts_at->format('g:i A') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div
                        class="relative overflow-hidden rounded-xl border-2 border-dashed border-zinc-200 bg-zinc-50 p-12 text-center dark:border-zinc-700 dark:bg-zinc-800/50">
                        <div class="mx-auto w-fit rounded-full bg-zinc-100 p-4 dark:bg-zinc-700">
                            <flux:icon name="calendar" class="h-10 w-10 text-zinc-400 dark:text-zinc-500" />
                        </div>
                        <div class="mt-4 font-medium text-zinc-600 dark:text-zinc-400">No upcoming appointments</div>
                        <div class="mt-1 text-sm text-zinc-500 dark:text-zinc-500">Get started by creating your first booking
                        </div>
                        <a href="{{ route('admin.appointments') }}" wire:navigate
                            class="mt-6 inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-red-500 to-orange-500 px-4 py-2 text-sm font-medium text-white shadow-lg transition-all duration-300 ease-out hover:shadow-xl hover:scale-105">
                            <flux:icon name="plus" class="h-4 w-4" />
                            Create Appointment
                        </a>
                    </div>
                @endif
            </div>
        </div>
    @else
        {{-- Customer Stats --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div
                class="group relative overflow-hidden rounded-xl bg-gradient-to-br from-red-500 to-orange-500 p-5 text-white shadow-lg transition-all duration-300 ease-out hover:shadow-xl hover:scale-[1.02]">
                <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
                <div class="absolute -right-3 -bottom-8 h-24 w-24 rounded-full bg-white/5"></div>
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm font-medium text-white/80">Total Bookings</div>
                            <div class="mt-1 text-4xl font-bold">{{ $myTotalBookings }}</div>
                            <div class="text-sm text-white/70">all time</div>
                        </div>
                        <div class="rounded-xl bg-white/20 p-3 backdrop-blur-sm">
                            <flux:icon name="calendar" class="h-7 w-7" />
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
                            <div class="mt-1 text-4xl font-bold">{{ $myCompletedBookings }}</div>
                            <div class="text-sm text-white/70">appointments</div>
                        </div>
                        <div class="rounded-xl bg-white/20 p-3 backdrop-blur-sm">
                            <flux:icon name="check-circle" class="h-7 w-7" />
                        </div>
                    </div>
                </div>
            </div>

            {{-- Cancelled --}}
            <div
                class="group relative overflow-hidden rounded-xl bg-gradient-to-br from-zinc-500 to-zinc-600 p-5 text-white shadow-lg transition-all duration-300 ease-out hover:shadow-xl hover:scale-[1.02]">
                <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
                <div class="absolute -right-3 -bottom-8 h-24 w-24 rounded-full bg-white/5"></div>
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm font-medium text-white/80">Cancelled</div>
                            <div class="mt-1 text-4xl font-bold">{{ $myCancelledBookings }}</div>
                            <div class="text-sm text-white/70">appointments</div>
                        </div>
                        <div class="rounded-xl bg-white/20 p-3 backdrop-blur-sm">
                            <flux:icon name="x-circle" class="h-7 w-7" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Layout: Upcoming (Left) & Sidebar (Right) --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- Sidebar (Right Column) --}}
            <div class="space-y-6">
                {{-- Quick Actions --}}
                <div class="space-y-4">
                    <flux:heading size="lg">Quick Actions</flux:heading>
                    <div class="grid grid-cols-1 gap-3">
                        <a href="{{ route('booking.wizard') }}" wire:navigate
                            class="group relative overflow-hidden rounded-xl bg-gradient-to-br from-red-500 to-orange-500 p-4 text-white shadow-lg transition-all duration-300 ease-out hover:shadow-xl hover:scale-[1.02]">
                            <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-white/10"></div>
                            <div class="absolute -right-2 -bottom-6 h-20 w-20 rounded-full bg-white/5"></div>
                            <div class="relative flex items-center gap-4">
                                <div class="rounded-lg bg-white/20 p-2.5 backdrop-blur-sm">
                                    <flux:icon name="plus" class="h-5 w-5" />
                                </div>
                                <div>
                                    <div class="font-semibold">Book Appointment</div>
                                    <div class="text-sm text-white/80">Schedule a new visit</div>
                                </div>
                                <flux:icon name="chevron-right"
                                    class="ml-auto h-5 w-5 opacity-60 transition-transform group-hover:translate-x-1" />
                            </div>
                        </a>

                        <a href="{{ route('admin.appointments') }}" wire:navigate
                            class="group relative overflow-hidden rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 p-4 text-white shadow-lg transition-all duration-300 ease-out hover:shadow-xl hover:scale-[1.02]">
                            <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-white/10"></div>
                            <div class="absolute -right-2 -bottom-6 h-20 w-20 rounded-full bg-white/5"></div>
                            <div class="relative flex items-center gap-4">
                                <div class="rounded-lg bg-white/20 p-2.5 backdrop-blur-sm">
                                    <flux:icon name="list-bullet" class="h-5 w-5" />
                                </div>
                                <div>
                                    <div class="font-semibold">My Appointments</div>
                                    <div class="text-sm text-white/80">View all history</div>
                                </div>
                                <flux:icon name="chevron-right"
                                    class="ml-auto h-5 w-5 opacity-60 transition-transform group-hover:translate-x-1" />
                            </div>
                        </a>
                    </div>
                </div>

                {{-- Calendar Widget --}}
                <div>
                    <livewire:customer.calendar layout="widget" />
                </div>
            </div>

            {{-- Upcoming Appointments (Main Column) --}}
            <div class="lg:col-span-2 space-y-4">
                <div class="flex items-center justify-between">
                    <flux:heading size="lg">Your Upcoming Appointments</flux:heading>
                </div>

                @if($myUpcomingAppointments->count() > 0)
                    <div class="space-y-3">
                        @foreach($myUpcomingAppointments as $appointment)
                            <div
                                class="group relative overflow-hidden rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-4">
                                        <div class="h-12 w-1.5 rounded-full"
                                            style="background-color: {{ $appointment->service->color }}"></div>
                                        <div>
                                            <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                                {{ $appointment->service->name }}
                                            </div>
                                            <div class="mt-0.5 flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
                                                <span>with {{ $appointment->user->name }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                            {{ $appointment->starts_at->format('M j') }}
                                        </div>
                                        <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                            {{ $appointment->starts_at->format('g:i A') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div
                        class="relative overflow-hidden rounded-xl border-2 border-dashed border-zinc-200 bg-zinc-50 p-12 text-center dark:border-zinc-700 dark:bg-zinc-800/50">
                        <div class="mx-auto w-fit rounded-full bg-zinc-100 p-4 dark:bg-zinc-700">
                            <flux:icon name="calendar" class="h-10 w-10 text-zinc-400 dark:text-zinc-500" />
                        </div>
                        <div class="mt-4 font-medium text-zinc-600 dark:text-zinc-400">No upcoming appointments</div>
                        <div class="mt-1 text-sm text-zinc-500 dark:text-zinc-500">Book your next visit now!</div>
                        <a href="{{ route('booking.wizard') }}" wire:navigate
                            class="mt-6 inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-red-500 to-orange-500 px-4 py-2 text-sm font-medium text-white shadow-lg transition-all duration-300 ease-out hover:shadow-xl hover:scale-105">
                            <flux:icon name="plus" class="h-4 w-4" />
                            Book Now
                        </a>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>