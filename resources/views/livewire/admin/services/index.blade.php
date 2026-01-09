<div class="space-y-6">
    {{-- Header with gradient accent --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl">Services</flux:heading>
            <flux:text class="mt-1 text-zinc-500">Manage the services your business offers.</flux:text>
        </div>
        <button wire:click="openCreateModal"
            class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-fuchsia-500 to-fuchsia-600 px-4 py-2.5 text-sm font-medium text-white shadow-lg transition-transform hover:shadow-xl hover:scale-105">
            <flux:icon name="plus" class="h-4 w-4" />
            Add Service
        </button>
    </div>

    {{-- Search --}}
    <div class="flex gap-4">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Search services..." icon="magnifying-glass"
            class="max-w-sm" />
    </div>

    {{-- Services Grid --}}
    @if($services->count() > 0)
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($services as $service)
                <div class="group relative overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm transition-shadow duration-300 hover:shadow-lg dark:border-zinc-700 dark:bg-zinc-800">
                    {{-- Color bar --}}
                    <div class="h-2" style="background-color: {{ $service->color }}"></div>

                    <div class="p-5">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $service->name }}</h3>
                                @if($service->description)
                                    <p class="mt-1 text-sm text-zinc-500 line-clamp-2">{{ $service->description }}</p>
                                @endif
                            </div>
                            <flux:dropdown position="bottom" align="end">
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                <flux:menu>
                                    <flux:menu.item icon="pencil" wire:click="openEditModal({{ $service->id }})">Edit</flux:menu.item>
                                    <flux:menu.separator />
                                    <flux:menu.item icon="trash" variant="danger" wire:click="confirmDelete({{ $service->id }})">Delete</flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </div>

                        <div class="mt-4 grid grid-cols-3 gap-3 text-center">
                            <div class="rounded-lg bg-zinc-50 p-2 dark:bg-zinc-700/50">
                                <div class="text-lg font-bold text-zinc-900 dark:text-zinc-100">{{ $service->duration }}</div>
                                <div class="text-xs text-zinc-500">minutes</div>
                            </div>
                            <div class="rounded-lg bg-zinc-50 p-2 dark:bg-zinc-700/50">
                                <div class="text-lg font-bold text-zinc-900 dark:text-zinc-100">{{ $service->buffer_time }}</div>
                                <div class="text-xs text-zinc-500">buffer</div>
                            </div>
                            <div class="rounded-lg bg-zinc-50 p-2 dark:bg-zinc-700/50">
                                <div class="text-lg font-bold text-zinc-900 dark:text-zinc-100">${{ number_format($service->price, 0) }}</div>
                                <div class="text-xs text-zinc-500">price</div>
                            </div>
                        </div>

                        <div class="mt-4 flex items-center justify-between">
                            @if($service->is_active)
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
                            <span class="text-xs text-zinc-400">{{ $service->users_count ?? 0 }} staff</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="relative overflow-hidden rounded-xl border-2 border-dashed border-zinc-200 bg-zinc-50 p-12 text-center dark:border-zinc-700 dark:bg-zinc-800/50">
            <div class="mx-auto w-fit rounded-full bg-zinc-100 p-4 dark:bg-zinc-700">
                <flux:icon name="squares-plus" class="h-10 w-10 text-zinc-400 dark:text-zinc-500" />
            </div>
            <div class="mt-4 font-medium text-zinc-600 dark:text-zinc-400">No services found</div>
            <div class="mt-1 text-sm text-zinc-500">Get started by adding your first service</div>
            <button wire:click="openCreateModal"
                class="mt-6 inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-fuchsia-500 to-fuchsia-600 px-4 py-2 text-sm font-medium text-white shadow-lg transition-transform hover:shadow-xl hover:scale-105">
                <flux:icon name="plus" class="h-4 w-4" />
                Add Service
            </button>
        </div>
    @endif

    {{-- Pagination --}}
    @if($services->hasPages())
        <div class="mt-4">{{ $services->links() }}</div>
    @endif

    {{-- Create/Edit Modal --}}
    <flux:modal wire:model="showModal" class="max-w-lg">
        <div class="space-y-6">
            <flux:heading size="lg">{{ $editingService ? 'Edit Service' : 'Create Service' }}</flux:heading>

            <form wire:submit="save" class="space-y-4">
                <flux:field>
                    <flux:label>Name</flux:label>
                    <flux:input wire:model="name" placeholder="e.g., Consultation" />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>Description</flux:label>
                    <flux:textarea wire:model="description" placeholder="Describe this service..." rows="3" />
                    <flux:error name="description" />
                </flux:field>

                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Duration (minutes)</flux:label>
                        <flux:input type="number" wire:model="duration" min="5" max="480" />
                        <flux:error name="duration" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Buffer Time (minutes)</flux:label>
                        <flux:input type="number" wire:model="buffer_time" min="0" max="120" />
                        <flux:error name="buffer_time" />
                    </flux:field>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Price</flux:label>
                        <flux:input type="number" wire:model="price" min="0" step="0.01" prefix="$" />
                        <flux:error name="price" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Color</flux:label>
                        <div class="flex items-center gap-2">
                            <input type="color" wire:model="color"
                                class="h-10 w-14 rounded border border-zinc-300 cursor-pointer" />
                            <flux:input wire:model="color" class="flex-1" maxlength="7" />
                        </div>
                        <flux:error name="color" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:checkbox wire:model="is_active" label="Active" description="Only active services can be booked." />
                </flux:field>

                <div class="flex justify-end gap-3 pt-4">
                    <flux:button variant="ghost" wire:click="closeModal">Cancel</flux:button>
                    <button type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-gradient-to-r from-fuchsia-500 to-fuchsia-600 px-4 py-2 text-sm font-medium text-white shadow-lg transition-transform duration-300 ease-out hover:shadow-xl hover:scale-105">
                        {{ $editingService ? 'Update' : 'Create' }}
                    </button>
                </div>
            </form>
        </div>
    </flux:modal>

    {{-- Delete Confirmation Modal --}}
    <flux:modal wire:model="showDeleteModal" class="max-w-md">
        <div class="space-y-6">
            <flux:heading size="lg">Delete Service</flux:heading>
            <flux:text>Are you sure you want to delete <strong>{{ $deletingService?->name }}</strong>? This action cannot be undone.</flux:text>
            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" wire:click="$set('showDeleteModal', false)">Cancel</flux:button>
                <flux:button variant="danger" wire:click="delete">Delete</flux:button>
            </div>
        </div>
    </flux:modal>
</div>