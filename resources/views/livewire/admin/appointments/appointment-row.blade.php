<flux:table.row wire:key="{{ $wireKey ?? 'appt-' . ($appointment->id ?? uniqid()) }}" class="{{ isset($sessionNumber) ? 'bg-zinc-50/30 dark:bg-zinc-800/20' : '' }}">
    <flux:table.cell class="{{ isset($sessionNumber) ? 'pl-6' : '' }}">
        <div class="flex items-start gap-3 px-2">
            @if(isset($sessionNumber))
                <div class="mt-1 flex-shrink-0 text-zinc-300 dark:text-zinc-600">
                    <flux:icon name="arrow-turn-down-right" class="w-4 h-4" />
                </div>
            @endif
            <div>
                @if(isset($sessionNumber))
                    <div class="font-bold text-red-600 dark:text-red-400 mb-0.5 text-xs uppercase tracking-wider">Session {{ $sessionNumber }}</div>
                @endif
                <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $appointment->starts_at->format('M j, Y') }}</div>
                <div class="text-sm text-zinc-500 mt-0.5">
                    {{ $appointment->starts_at->format('g:i A') }} -
                    {{ $appointment->ends_at->format('g:i A') }}
                </div>
            </div>
        </div>
    </flux:table.cell>
    <flux:table.cell>
        <div class="font-medium">{{ $appointment->customer_name }}</div>
        <div class="text-sm text-zinc-500">{{ $appointment->customer_email }}</div>
    </flux:table.cell>
    <flux:table.cell>
        <div class="flex items-center gap-2">
            <span class="h-2 w-2 rounded-full"
                style="background-color: {{ $appointment->service->color ?? '#999' }}"></span>
            {{ $appointment->service->name ?? 'Unknown Service' }}
        </div>
    </flux:table.cell>
    <flux:table.cell>
        <div class="flex items-center gap-2">
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
        @hasrole('Super Admin|Staff')
        <flux:dropdown position="bottom" align="start">
            <button type="button" class="inline-flex items-center px-2.5 py-0.5 rounded-sm text-xs font-medium cursor-pointer
                                    @if($appointment->status === 'booked') bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400
                                    @elseif($appointment->status === 'confirmed') bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400
                                    @elseif($appointment->status === 'completed') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400
                                    @elseif($appointment->status === 'cancelled') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400
                                    @else bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-300
                                    @endif">
                {{ ucfirst($appointment->status) }}
            </button>
            <flux:menu>
                <flux:menu.item wire:click="updateStatus({{ $appointment->id }}, 'booked')">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-sm text-xs font-medium bg-amber-100 text-amber-800">Booked</span>
                </flux:menu.item>
                <flux:menu.item wire:click="updateStatus({{ $appointment->id }}, 'confirmed')">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-sm text-xs font-medium bg-blue-100 text-blue-800">Confirmed</span>
                </flux:menu.item>
                <flux:menu.item wire:click="updateStatus({{ $appointment->id }}, 'completed')">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-sm text-xs font-medium bg-green-100 text-green-800">Completed</span>
                </flux:menu.item>
                <flux:menu.item wire:click="updateStatus({{ $appointment->id }}, 'cancelled')">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-sm text-xs font-medium bg-red-100 text-red-800">Cancelled</span>
                </flux:menu.item>
            </flux:menu>
        </flux:dropdown>
        @else
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-sm text-xs font-medium
                @if($appointment->status === 'booked') bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400
                @elseif($appointment->status === 'confirmed') bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400
                @elseif($appointment->status === 'completed') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400
                @elseif($appointment->status === 'cancelled') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400
                @else bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-300
                @endif">
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
