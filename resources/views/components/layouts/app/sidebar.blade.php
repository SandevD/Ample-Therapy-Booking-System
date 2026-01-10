<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">
    <flux:sidebar sticky collapsible="mobile"
        class="border-e border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.header class="!py-4 justify-center items-center">
            <img class="h-16" src="{{ asset('assets/images/ample_therapy_logo.png') }}" alt="">
            <flux:sidebar.collapse class="lg:hidden" />
        </flux:sidebar.header>

        <flux:sidebar.nav class="flex-1 px-2">
            {{-- Platform --}}
            <div class="mb-4">
                <div class="text-[11px] font-medium text-zinc-400 uppercase tracking-wide px-2 mb-1">
                    {{ __('Platform') }}
                </div>
                <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')"
                    wire:navigate>
                    {{ __('Dashboard') }}
                </flux:sidebar.item>
            </div>

            {{-- Booking --}}
            <div class="mb-4">
                <div class="text-[11px] font-medium text-zinc-400 uppercase tracking-wide px-2 mb-1">
                    {{ __('Booking') }}
                </div>
                <flux:sidebar.item icon="plus" :href="route('booking.wizard')"
                    :current="request()->routeIs('booking.wizard')" wire:navigate>
                    {{ __('Book Appointment') }}
                </flux:sidebar.item>
            </div>

            {{-- Appointments --}}
            <div class="mb-4">
                <div class="text-[11px] font-medium text-zinc-400 uppercase tracking-wide px-2 mb-1">
                    {{ __('Appointments') }}
                </div>
                <flux:sidebar.item icon="calendar-days" :href="route('admin.appointments')"
                    :current="request()->routeIs('admin.appointments')" wire:navigate>
                    {{ __('Appointments') }}
                </flux:sidebar.item>
            </div>

            {{-- Calendar --}}
            <div class="mb-4">
                <div class="text-[11px] font-medium text-zinc-400 uppercase tracking-wide px-2 mb-1">
                    {{ __('Calendar') }}
                </div>
                <flux:sidebar.item icon="calendar" :href="route('customer.calendar')"
                    :current="request()->routeIs('customer.calendar')" wire:navigate>
                    {{ __('Calendar') }}
                </flux:sidebar.item>
            </div>

            {{-- Setup --}}
            <div class="mb-4">
                @hasrole('Super Admin|Staff')
                <div class="mb-2 px-4 text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-500">
                    Setup
                </div>

                <flux:navlist variant="outline">
                    <flux:navlist.item icon="squares-plus" href="{{ route('admin.services') }}"
                        :current="request()->routeIs('admin.services')" wire:navigate>
                        Services</flux:navlist.item>
                    <flux:navlist.item icon="users" href="{{ route('admin.staff') }}"
                        :current="request()->routeIs('admin.staff')" wire:navigate>
                        Staff</flux:navlist.item>
                    <flux:navlist.item icon="user-group" href="{{ route('admin.customers') }}"
                        :current="request()->routeIs('admin.customers')" wire:navigate>
                        Customers</flux:navlist.item>
                </flux:navlist>
                @endhasrole
            </div>

            {{-- Administration --}}
            <div class="mb-4">
                @hasrole('Super Admin|Staff')
                <div class="mb-2 px-4 text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-500">
                    Administration
                </div>

                <flux:navlist variant="outline">
                    <flux:navlist.item icon="shield-check" href="{{ route('admin.admins') }}"
                        :current="request()->routeIs('admin.admins')" wire:navigate>
                        Admins</flux:navlist.item>
                    <flux:navlist.item icon="key" href="{{ route('admin.roles') }}"
                        :current="request()->routeIs('admin.roles')" wire:navigate>
                        Roles</flux:navlist.item>
                </flux:navlist>
                @endhasrole
            </div>
        </flux:sidebar.nav>

        <flux:spacer />

        {{-- Footer Links --}}
        <div class="px-2 pb-2">
            <flux:sidebar.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit"
                target="_blank">
                {{ __('Repository') }}
            </flux:sidebar.item>
            <flux:sidebar.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire"
                target="_blank">
                {{ __('Documentation') }}
            </flux:sidebar.item>
        </div>

        {{-- User Menu --}}
        <div class="border-t border-zinc-100 dark:border-zinc-800 p-2">
            <flux:dropdown position="top" align="start" class="w-full">
                <button
                    class="flex w-full items-center gap-3 rounded-lg p-2 text-left hover:bg-zinc-100 dark:hover:bg-zinc-800">
                    <div
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-red-500 to-orange-500 text-sm font-semibold text-white">
                        {{ auth()->user()->initials() }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100 truncate">
                            {{ auth()->user()->name }}
                        </div>
                        <div class="text-xs text-zinc-500 truncate">{{ auth()->user()->email }}</div>
                    </div>
                    <flux:icon name="chevrons-up-down" class="h-4 w-4 shrink-0 text-zinc-400" />
                </button>
                <flux:menu class="w-56">
                    <div class="px-2 py-1.5">
                        <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ auth()->user()->name }}
                        </div>
                        <div class="text-xs text-zinc-500">{{ auth()->user()->email }}</div>
                    </div>
                    <flux:menu.separator />
                    <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                        {{ __('Settings') }}
                    </flux:menu.item>
                    <flux:menu.separator />
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </div>
    </flux:sidebar>

    <!-- Mobile Header -->
    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
        <flux:spacer />
        <flux:dropdown position="top" align="end">
            <button class="flex items-center gap-2 rounded-lg px-2 py-1.5 hover:bg-zinc-100 dark:hover:bg-zinc-800">
                <div
                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-red-500 to-orange-500 text-sm font-semibold text-white">
                    {{ auth()->user()->initials() }}
                </div>
                <flux:icon name="chevron-down" class="h-4 w-4 text-zinc-400" />
            </button>
            <flux:menu class="w-56">
                <div class="px-2 py-1.5">
                    <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ auth()->user()->name }}</div>
                    <div class="text-xs text-zinc-500">{{ auth()->user()->email }}</div>
                </div>
                <flux:menu.separator />
                <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                    {{ __('Settings') }}
                </flux:menu.item>
                <flux:menu.separator />
                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    {{ $slot }}

    @fluxScripts
</body>

</html>