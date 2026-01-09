<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl">Staff</flux:heading>
            <flux:text class="mt-1 text-zinc-500">Manage staff members and their service assignments.</flux:text>
        </div>
        <button wire:click="openCreateModal"
            class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-amber-500 to-amber-600 px-4 py-2.5 text-sm font-medium text-white shadow-lg transition-transform hover:shadow-xl hover:scale-105">
            <flux:icon name="plus" class="h-4 w-4" />
            Add Staff Member
        </button>
    </div>

    {{-- Search --}}
    <flux:input wire:model.live.debounce.300ms="search" placeholder="Search staff..." icon="magnifying-glass" class="max-w-sm" />

    {{-- Staff Grid --}}
    @if($staffMembers->count() > 0)
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($staffMembers as $user)
                <div class="group relative overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm transition-shadow duration-300 hover:shadow-lg dark:border-zinc-700 dark:bg-zinc-800">
                    {{-- Gradient header --}}
                    <div class="h-16 bg-gradient-to-r from-amber-500 to-amber-600"></div>

                    {{-- Avatar overlay --}}
                    <div class="relative px-5 pb-5">
                        <div class="-mt-8 mb-3">
                            <div class="inline-flex h-16 w-16 items-center justify-center rounded-xl bg-white text-xl font-bold text-amber-600 shadow-lg ring-4 ring-white dark:bg-zinc-800 dark:ring-zinc-800">
                                {{ $user->initials() }}
                            </div>
                        </div>

                        <div class="flex items-start justify-between">
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-zinc-900 dark:text-zinc-100 truncate">{{ $user->name }}</h3>
                                <p class="text-sm text-zinc-500 truncate">{{ $user->email }}</p>
                                @if($user->phone)
                                    <p class="text-xs text-zinc-400">{{ $user->phone }}</p>
                                @endif
                            </div>
                            <flux:dropdown position="bottom" align="end">
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                <flux:menu>
                                    <flux:menu.item icon="clock" wire:click="openAvailabilityModal({{ $user->id }})">Availability</flux:menu.item>
                                    <flux:menu.item icon="pencil" wire:click="openEditModal({{ $user->id }})">Edit</flux:menu.item>
                                    <flux:menu.separator />
                                    <flux:menu.item icon="trash" variant="danger" wire:click="confirmDelete({{ $user->id }})">Delete</flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </div>

                        @if($user->bio)
                            <p class="mt-2 text-sm text-zinc-500 line-clamp-2">{{ $user->bio }}</p>
                        @endif

                        {{-- Services --}}
                        <div class="mt-4 flex flex-wrap gap-1.5">
                            @foreach($user->services->take(3) as $service)
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                                    style="background-color: {{ $service->color }}20; color: {{ $service->color }}">
                                    {{ $service->name }}
                                </span>
                            @endforeach
                            @if($user->services->count() > 3)
                                <span class="inline-flex items-center rounded-full bg-zinc-100 px-2 py-0.5 text-xs font-medium text-zinc-600 dark:bg-zinc-700 dark:text-zinc-400">
                                    +{{ $user->services->count() - 3 }}
                                </span>
                            @endif
                            @if($user->services->count() === 0)
                                <span class="text-xs text-zinc-400 italic">No services assigned</span>
                            @endif
                        </div>

                        {{-- Status --}}
                        <div class="mt-4 flex items-center justify-between">
                            @if($user->is_active)
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                    Inactive
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="relative overflow-hidden rounded-xl border-2 border-dashed border-zinc-200 bg-zinc-50 p-12 text-center dark:border-zinc-700 dark:bg-zinc-800/50">
            <div class="mx-auto w-fit rounded-full bg-zinc-100 p-4 dark:bg-zinc-700">
                <flux:icon name="user-circle" class="h-10 w-10 text-zinc-400 dark:text-zinc-500" />
            </div>
            <div class="mt-4 font-medium text-zinc-600 dark:text-zinc-400">No staff members found</div>
            <div class="mt-1 text-sm text-zinc-500">Add your first team member to get started</div>
            <button wire:click="openCreateModal"
                class="mt-6 inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-amber-500 to-amber-600 px-4 py-2 text-sm font-medium text-white shadow-lg transition-transform hover:shadow-xl hover:scale-105">
                <flux:icon name="plus" class="h-4 w-4" />
                Add Staff Member
            </button>
        </div>
    @endif

    @if($staffMembers->hasPages())
        <div class="mt-4">{{ $staffMembers->links() }}</div>
    @endif

    {{-- Create/Edit Modal --}}
    <flux:modal wire:model="showModal" class="max-w-lg">
        <flux:heading size="lg" class="mb-6">{{ $editingUser ? 'Edit Staff Member' : 'Add Staff Member' }}</flux:heading>
        <form wire:submit="save" class="space-y-4">
            <flux:field>
                <flux:label>Name</flux:label>
                <flux:input wire:model="name" placeholder="Full name" />
                <flux:error name="name" />
            </flux:field>

            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Email</flux:label>
                    <flux:input type="email" wire:model="email" />
                    <flux:error name="email" />
                </flux:field>
                <flux:field>
                    <flux:label>Phone</flux:label>
                    <flux:input wire:model="phone" />
                    <flux:error name="phone" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>Bio</flux:label>
                <flux:textarea wire:model="bio" rows="2" />
            </flux:field>

            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>{{ $editingUser ? 'New Password' : 'Password' }}</flux:label>
                    <flux:input type="password" wire:model="password" placeholder="{{ $editingUser ? 'Leave blank to keep' : '' }}" />
                    <flux:error name="password" />
                </flux:field>
                <flux:field>
                    <flux:label>Confirm</flux:label>
                    <flux:input type="password" wire:model="password_confirmation" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>Services</flux:label>
                <div class="mt-2 grid grid-cols-2 gap-2">
                    @foreach($services as $service)
                        <label class="flex items-center gap-2 p-2 rounded-lg border cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                            <input type="checkbox" value="{{ $service->id }}" wire:model="selectedServices" class="rounded" />
                            <span class="h-2 w-2 rounded-full" style="background-color: {{ $service->color }}"></span>
                            <span class="text-sm">{{ $service->name }}</span>
                        </label>
                    @endforeach
                </div>
            </flux:field>

            <flux:checkbox wire:model="is_active" label="Active" />

            <div class="flex justify-end gap-3 pt-4">
                <flux:button variant="ghost" wire:click="closeModal">Cancel</flux:button>
                <button type="submit"
                    class="inline-flex items-center justify-center rounded-lg bg-gradient-to-r from-amber-500 to-amber-600 px-4 py-2 text-sm font-medium text-white shadow-lg transition-transform duration-300 ease-out hover:shadow-xl hover:scale-105">
                    {{ $editingUser ? 'Update' : 'Create' }}
                </button>
            </div>
        </form>
    </flux:modal>

    {{-- Availability Modal --}}
    <flux:modal wire:model="showAvailabilityModal" class="max-w-4xl">
        <flux:heading size="lg" class="mb-6">Availability: {{ $availabilityUser?->name }}</flux:heading>

        @if($availabilityUser && $availabilityUser->services->count() > 0)
            <div class="space-y-6 max-h-[60vh] overflow-y-auto">
                @foreach($availabilityUser->services as $service)
                    <div class="border rounded-xl p-4 dark:border-zinc-700">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="h-3 w-3 rounded-full" style="background-color: {{ $service->color }}"></span>
                            <flux:heading size="sm">{{ $service->name }}</flux:heading>
                        </div>
                        <div class="grid gap-2">
                            @foreach(['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'] as $dayIndex => $dayName)
                                @php $key = "{$service->id}_{$dayIndex}"; @endphp
                                <div class="flex items-center gap-4 py-2 border-b border-zinc-100 dark:border-zinc-800 last:border-0">
                                    <label class="w-28 flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" wire:model="availabilities.{{ $key }}.enabled" class="rounded" />
                                        <span class="text-sm">{{ $dayName }}</span>
                                    </label>
                                    @if(data_get($availabilities, "$key.enabled"))
                                        <div class="flex items-center gap-2">
                                            <input type="time" wire:model="availabilities.{{ $key }}.start_time"
                                                class="text-sm rounded border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800" />
                                            <span class="text-zinc-400">to</span>
                                            <input type="time" wire:model="availabilities.{{ $key }}.end_time"
                                                class="text-sm rounded border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800" />
                                        </div>
                                    @else
                                        <span class="text-sm text-zinc-400">Not available</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="flex justify-end gap-3 pt-4">
                <flux:button variant="ghost" wire:click="$set('showAvailabilityModal', false)">Cancel</flux:button>
                <button type="button" wire:click="saveAvailabilities"
                    class="inline-flex items-center justify-center rounded-lg bg-gradient-to-r from-amber-500 to-amber-600 px-4 py-2 text-sm font-medium text-white shadow-lg transition-transform duration-300 ease-out hover:shadow-xl hover:scale-105">
                    Save Availability
                </button>
            </div>
        @else
            <flux:text>Assign services to this staff member first.</flux:text>
        @endif
    </flux:modal>

    {{-- Delete Modal --}}
    <flux:modal wire:model="showDeleteModal" class="max-w-md">
        <flux:heading size="lg" class="mb-4">Delete Staff Member</flux:heading>
        <flux:text>Delete <strong>{{ $deletingUser?->name }}</strong>? This cannot be undone.</flux:text>
        <div class="flex justify-end gap-3 mt-6">
            <flux:button variant="ghost" wire:click="$set('showDeleteModal', false)">Cancel</flux:button>
            <flux:button variant="danger" wire:click="delete">Delete</flux:button>
        </div>
    </flux:modal>
</div>