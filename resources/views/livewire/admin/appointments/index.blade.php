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

    {{-- Callout --}}
    <flux:callout icon="information-circle" color="purple">
        <flux:callout.heading>Cancellations & Reschedules</flux:callout.heading>
        <flux:callout.text>
            Please contact ample therapy on Call <strong>+ (44) 792 004 6036</strong> or Email <strong>hello@ampletherapy.org.uk</strong> for all cancellations and reschedules.
        </flux:callout.text>
    </flux:callout>

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
                @php $renderedGroups = []; @endphp
                @forelse ($appointments as $appointment)
                    @if ($appointment->booking_group_id)
                        @if (in_array($appointment->booking_group_id, $renderedGroups))
                            @continue
                        @endif
                        @php 
                            $renderedGroups[] = $appointment->booking_group_id; 
                            $groupAppointments = App\Models\Appointment::with(['service', 'user'])
                                ->where('booking_group_id', $appointment->booking_group_id)
                                ->orderBy('starts_at', 'asc')
                                ->get();
                        @endphp
                        
                        <flux:table.row wire:key="group-header-{{ $appointment->booking_group_id }}" class="bg-zinc-50 dark:bg-zinc-800/40 border-t-2 border-zinc-200 dark:border-zinc-700/60">
                            <flux:table.cell colspan="6" class="py-4">
                                <div class="flex items-center gap-4 px-4">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-white shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 dark:ring-zinc-700">
                                        <flux:icon name="rectangle-stack" class="h-5 w-5 text-red-500" />
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                                            Booking <span class="font-normal text-zinc-500 dark:text-zinc-400">#{{ strtoupper(substr($appointment->booking_group_id, 0, 8)) }}</span>
                                        </div>
                                        <div class="mt-0.5 text-xs text-zinc-500">
                                            Booked on {{ $groupAppointments->first()->created_at->format('M j, Y') }} &bull; {{ $groupAppointments->count() }} Sessions
                                        </div>
                                    </div>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>

                        @foreach($groupAppointments as $index => $groupAppt)
                            @include('livewire.admin.appointments.appointment-row', ['appointment' => $groupAppt, 'sessionNumber' => $index + 1, 'wireKey' => 'appt-group-' . $groupAppt->id])
                        @endforeach
                    @else
                        @include('livewire.admin.appointments.appointment-row', ['appointment' => $appointment, 'sessionNumber' => null, 'wireKey' => 'appt-single-' . $appointment->id])
                    @endif
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
        <div class="mt-4" wire:key="appointments-pagination-{{ $appointments->currentPage() }}">
            <flux:pagination :paginator="$appointments" />
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