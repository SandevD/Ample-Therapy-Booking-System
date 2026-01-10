<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl">Appointments</flux:heading>
            <flux:text class="mt-1 text-zinc-500">View and manage all bookings.</flux:text>
        </div>
        @hasrole('Super Admin|Staff')
        <button wire:click="openCreateModal"
            class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-red-500 to-orange-500 px-4 py-2.5 text-sm font-medium text-white shadow-lg transition-transform hover:shadow-xl hover:scale-105">
            <flux:icon name="plus" class="h-4 w-4" />
            New Appointment
        </button>
        @endhasrole
    </div>

    {{-- Filters --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
        <div class="w-full sm:w-64">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Search customer..."
                icon="magnifying-glass" />
        </div>

        <div class="flex flex-1 flex-col gap-4 sm:flex-row sm:items-center">
            <div class="w-full sm:w-40">
                <flux:select wire:model.live="statusFilter" placeholder="All Statuses">
                    <option value="">All Statuses</option>
                    <option value="booked">Booked</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </flux:select>
            </div>

            @hasrole('Super Admin|Staff')
            <div class="w-full sm:w-48">
                <flux:select wire:model.live="staffFilter" placeholder="All Staff">
                    <option value="">All Staff</option>
                    @foreach($staffMembers as $staff)
                        <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                    @endforeach
                </flux:select>
            </div>
            @endhasrole

            <div class="w-full sm:w-auto">
                <flux:input type="date" wire:model.live="dateFilter" />
            </div>
        </div>
    </div>

    {{-- Appointments Table --}}
    <flux:card class="overflow-hidden">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Date & Time</flux:table.column>
                <flux:table.column>Customer</flux:table.column>
                <flux:table.column>Service</flux:table.column>
                <flux:table.column>Staff</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column class="w-24"></flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @forelse ($appointments as $appointment)
                    <flux:table.row>
                        <flux:table.cell>
                            <div class="font-medium">{{ $appointment->starts_at->format('M j, Y') }}</div>
                            <div class="text-sm text-zinc-500">
                                {{ $appointment->starts_at->format('g:i A') }} -
                                {{ $appointment->ends_at->format('g:i A') }}
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="font-medium">{{ $appointment->customer_name }}</div>
                            <div class="text-sm text-zinc-500">{{ $appointment->customer_email }}</div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full"
                                    style="background-color: {{ $appointment->service->color }}"></span>
                                {{ $appointment->service->name }}
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                <flux:avatar :initials="$appointment->user->initials()" size="xs" />
                                {{ $appointment->user->name }}
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
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-sm text-xs font-medium bg-amber-100 text-amber-800">Booked</span>
                                    </flux:menu.item>
                                    <flux:menu.item wire:click="updateStatus({{ $appointment->id }}, 'confirmed')">
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-sm text-xs font-medium bg-blue-100 text-blue-800">Confirmed</span>
                                    </flux:menu.item>
                                    <flux:menu.item wire:click="updateStatus({{ $appointment->id }}, 'completed')">
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-sm text-xs font-medium bg-green-100 text-green-800">Completed</span>
                                    </flux:menu.item>
                                    <flux:menu.item wire:click="updateStatus({{ $appointment->id }}, 'cancelled')">
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-sm text-xs font-medium bg-red-100 text-red-800">Cancelled</span>
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
                                    <flux:menu.item icon="trash" variant="danger"
                                        wire:click="confirmDelete({{ $appointment->id }})">
                                        Delete
                                    </flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                            @endhasrole
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6" class="text-center py-8">
                            <div class="text-zinc-500">No appointments found.</div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>

    {{-- Pagination --}}
    @if($appointments->hasPages())
        <div class="mt-4">
            {{ $appointments->links() }}
        </div>
    @endif

    {{-- Create/Edit Modal --}}
    <flux:modal wire:model="showModal" class="max-w-lg">
        <div class="space-y-6">
            <flux:heading size="lg">
                {{ $editingAppointment ? 'Edit Appointment' : 'New Appointment' }}
            </flux:heading>

            <form wire:submit="save" class="space-y-4">
                <flux:field>
                    <flux:label>Service</flux:label>
                    <flux:select wire:model.live="service_id">
                        <option value="">Select a service...</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}">
                                {{ $service->name }} ({{ $service->duration }} min -
                                ${{ number_format($service->price, 2) }})
                            </option>
                        @endforeach
                    </flux:select>
                    <flux:error name="service_id" />
                </flux:field>

                <flux:field>
                    <flux:label>Staff Member</flux:label>
                    <flux:select wire:model="user_id" :disabled="!$service_id">
                        <option value="">Select a staff member...</option>
                        @foreach($availableStaff as $staff)
                            <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                        @endforeach
                    </flux:select>
                    @if($service_id && $availableStaff->isEmpty())
                        <flux:description class="text-amber-600">No staff available for this service.</flux:description>
                    @endif
                    <flux:error name="user_id" />
                </flux:field>

                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Date</flux:label>
                        <flux:input type="date" wire:model="date" />
                        <flux:error name="date" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Time</flux:label>
                        <flux:input type="time" wire:model="start_time" />
                        <flux:error name="start_time" />
                    </flux:field>
                </div>

                <flux:separator />

                <flux:field>
                    <flux:label>Customer Name</flux:label>
                    <flux:input wire:model="customer_name" placeholder="Full name" />
                    <flux:error name="customer_name" />
                </flux:field>

                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Email</flux:label>
                        <flux:input type="email" wire:model="customer_email" placeholder="email@example.com" />
                        <flux:error name="customer_email" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Phone</flux:label>
                        <flux:input type="tel" wire:model="customer_phone" placeholder="+1 (555) 000-0000" />
                        <flux:error name="customer_phone" />
                    </flux:field>
                </div>

                @if($editingAppointment)
                    <flux:field>
                        <flux:label>Status</flux:label>
                        <flux:select wire:model="status">
                            <option value="booked">Booked</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </flux:select>
                        <flux:error name="status" />
                    </flux:field>
                @endif

                <flux:field>
                    <flux:label>Notes</flux:label>
                    <flux:textarea wire:model="notes" placeholder="Optional notes..." rows="2" />
                    <flux:error name="notes" />
                </flux:field>

                <div class="flex justify-end gap-3 pt-4">
                    <flux:button variant="ghost" wire:click="closeModal">Cancel</flux:button>
                    <button type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-gradient-to-r from-red-500 to-orange-500 px-4 py-2 text-sm font-medium text-white shadow-lg transition-transform duration-300 ease-out hover:shadow-xl hover:scale-105">
                        {{ $editingAppointment ? 'Update' : 'Create Appointment' }}
                    </button>
                </div>
            </form>
        </div>
    </flux:modal>

    {{-- Delete Confirmation Modal --}}
    <flux:modal wire:model="showDeleteModal" class="max-w-md">
        <div class="space-y-6">
            <flux:heading size="lg">Delete Appointment</flux:heading>
            <flux:text>
                Are you sure you want to delete the appointment for
                <strong>{{ $deletingAppointment?->customer_name }}</strong>?
                This action cannot be undone.
            </flux:text>
            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" wire:click="$set('showDeleteModal', false)">Cancel</flux:button>
                <flux:button variant="danger" wire:click="delete">Delete</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Status Confirmation Modal --}}
    <flux:modal wire:model="showConfirmStatusModal" class="max-w-md">
        <div class="space-y-6">
            <flux:heading size="lg">Confirm Appointment</flux:heading>

            <div class="p-4 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 dark:bg-amber-900/20 dark:border-amber-800 dark:text-amber-300">
                <div class="flex gap-3">
                    <flux:icon name="exclamation-triangle" class="w-5 h-5 shrink-0" />
                    <div class="text-sm">
                        <p class="font-medium">Conflict Detected</p>
                        <p class="mt-1">
                            There are <strong>{{ $conflictingCount }}</strong> other pending appointments for this time slot.
                            Confirming this appointment will automatically <strong>cancel</strong> them.
                        </p>
                    </div>
                </div>
            </div>

            <flux:text>Are you sure you want to proceed?</flux:text>

            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" wire:click="$set('showConfirmStatusModal', false)">Cancel</flux:button>
                <flux:button variant="primary" wire:click="confirmStatusUpdate">Confirm & Cancel Others</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Block Action Modal --}}
    <flux:modal wire:model="showBlockModal" class="max-w-md">
        <div class="space-y-6">
            <flux:heading size="lg">Action Blocked</flux:heading>

            <div class="p-4 rounded-lg bg-red-50 border border-red-200 text-red-800 dark:bg-red-900/20 dark:border-red-800 dark:text-red-300">
                <div class="flex gap-3">
                    <flux:icon name="x-circle" class="w-5 h-5 shrink-0" />
                    <div class="text-sm">
                        <p class="font-medium">Slot Unavailable</p>
                        <p class="mt-1">
                            This time slot is already taken by 
                            <strong>{{ $blockingAppointment?->customer_name }}</strong> 
                            ({{ ucfirst($blockingAppointment?->status) }}).
                        </p>
                    </div>
                </div>
            </div>

            <flux:text>You must change the status of the existing appointment before you can modify this one.</flux:text>

            <div class="flex justify-end">
                <flux:button variant="ghost" wire:click="$set('showBlockModal', false)">Close</flux:button>
            </div>
        </div>
    </flux:modal>
</div>