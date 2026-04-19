<flux:table.row wire:key="{{ $wireKey ?? 'appt-' . ($appointment->id ?? uniqid()) }}" class="{{ isset($sessionNumber) ? 'bg-zinc-50/40 dark:bg-zinc-900/20' : '' }}">
    <flux:table.cell class="{{ isset($sessionNumber) ? 'pl-6' : '' }}">
        <div class="flex items-start gap-3 px-2">
            @if(isset($sessionNumber))
                <div class="mt-1 flex-shrink-0 text-zinc-300 dark:text-zinc-600">
                    <flux:icon name="arrow-turn-down-right" class="w-4 h-4" />
                </div>
            @endif
            <div>
                @if(isset($sessionNumber))
                    <div class="font-semibold text-red-600/90 dark:text-red-400/80 mb-0.5 text-[11px] uppercase tracking-wider">Session {{ $sessionNumber }}</div>
                @endif
                <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $appointment->starts_at->format('M j, Y') }}</div>
                <div class="text-sm text-zinc-500 dark:text-zinc-400 mt-0.5">
                    {{ $appointment->starts_at->format('g:i A') }} -
                    {{ $appointment->ends_at->format('g:i A') }}
                </div>
            </div>
        </div>
    </flux:table.cell>
    <flux:table.cell>
        <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $appointment->customer_name }}</div>
        <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $appointment->customer_email }}</div>
    </flux:table.cell>
    <flux:table.cell>
        <div class="flex items-center gap-2 text-zinc-700 dark:text-zinc-200">
            <span class="h-2 w-2 rounded-full"
                style="background-color: {{ $appointment->service->color ?? '#999' }}"></span>
            {{ $appointment->service->name ?? 'Unknown Service' }}
        </div>
    </flux:table.cell>
    <flux:table.cell>
        <div class="flex items-center gap-2 text-zinc-700 dark:text-zinc-200">
            @if($appointment->user)
                <flux:avatar :initials="$appointment->user->initials()" size="xs" />
                {{ $appointment->user->name }}
            @else
                <flux:avatar initials="?" size="xs" />
                Unknown
            @endif
        </div>
    </flux:table.cell>
    <flux:table.cell>
        @php
            $statusClasses = [
                'booked'    => 'bg-amber-100 text-amber-800 ring-amber-200/60 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-500/20',
                'confirmed' => 'bg-blue-100 text-blue-800 ring-blue-200/60 dark:bg-sky-500/10 dark:text-sky-300 dark:ring-sky-500/20',
                'completed' => 'bg-green-100 text-green-800 ring-green-200/60 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-500/20',
                'cancelled' => 'bg-red-100 text-red-800 ring-red-200/60 dark:bg-red-500/10 dark:text-red-300 dark:ring-red-500/20',
            ];
            $pillBase = 'inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium ring-1 ring-inset';
            $pillClass = $pillBase . ' ' . ($statusClasses[$appointment->status] ?? 'bg-zinc-100 text-zinc-700 ring-zinc-200 dark:bg-zinc-800/60 dark:text-zinc-300 dark:ring-zinc-700');
        @endphp
        @hasrole('Super Admin|Staff')
        <flux:dropdown position="bottom" align="start">
            <button type="button" class="{{ $pillClass }} cursor-pointer">
                {{ ucfirst($appointment->status) }}
            </button>
            <flux:menu>
                <flux:menu.item wire:click="updateStatus({{ $appointment->id }}, 'booked')">
                    <span class="{{ $pillBase }} {{ $statusClasses['booked'] }}">Booked</span>
                </flux:menu.item>
                <flux:menu.item wire:click="updateStatus({{ $appointment->id }}, 'confirmed')">
                    <span class="{{ $pillBase }} {{ $statusClasses['confirmed'] }}">Confirmed</span>
                </flux:menu.item>
                <flux:menu.item wire:click="updateStatus({{ $appointment->id }}, 'completed')">
                    <span class="{{ $pillBase }} {{ $statusClasses['completed'] }}">Completed</span>
                </flux:menu.item>
                <flux:menu.item wire:click="updateStatus({{ $appointment->id }}, 'cancelled')">
                    <span class="{{ $pillBase }} {{ $statusClasses['cancelled'] }}">Cancelled</span>
                </flux:menu.item>
            </flux:menu>
        </flux:dropdown>
        @else
            <span class="{{ $pillClass }}">
                {{ ucfirst($appointment->status) }}
            </span>
        @endhasrole
    </flux:table.cell>
    <flux:table.cell>
        @hasrole('Super Admin|Staff')
        <flux:dropdown position="bottom" align="end">
            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
            <flux:menu>
                <flux:menu.item icon="pencil" wire:click="openEditModal({{ $appointment->id }})">
                    Edit
                </flux:menu.item>
                <flux:menu.separator />
                <flux:menu.item icon="trash" variant="danger" wire:click="confirmDelete({{ $appointment->id }})">
                    Delete
                </flux:menu.item>
            </flux:menu>
        </flux:dropdown>
        @endhasrole
    </flux:table.cell>
</flux:table.row>
