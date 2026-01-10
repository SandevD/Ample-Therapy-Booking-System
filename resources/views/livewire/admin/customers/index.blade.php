<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl">Customers</flux:heading>
            <flux:text class="mt-1 text-zinc-500">Manage customer accounts.</flux:text>
        </div>
        <button wire:click="openCreateModal"
            class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-sky-500 to-sky-600 px-4 py-2.5 text-sm font-medium text-white shadow-lg transition-transform hover:shadow-xl hover:scale-105">
            <flux:icon name="plus" class="h-4 w-4" />
            Add Customer
        </button>
    </div>

    {{-- Search --}}
    <flux:input wire:model.live.debounce.300ms="search" placeholder="Search customers..." icon="magnifying-glass" class="max-w-sm" />

    {{-- Customers Grid --}}
    @if($customers->count() > 0)
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach ($customers as $customer)
                <div class="group relative overflow-hidden rounded-xl border border-zinc-200 bg-white p-5 shadow-sm transition-shadow duration-300 hover:shadow-lg dark:border-zinc-700 dark:bg-zinc-800">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-sky-500 to-sky-600 text-sm font-bold text-white">
                                {{ $customer->initials() }}
                            </div>
                            <div class="min-w-0">
                                <h3 class="font-semibold text-zinc-900 dark:text-zinc-100 truncate">{{ $customer->name }}</h3>
                                <p class="text-sm text-zinc-500 truncate">{{ $customer->email }}</p>
                            </div>
                        </div>
                        <flux:dropdown position="bottom" align="end">
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                            <flux:menu>
                                <flux:menu.item icon="user" href="{{ route('admin.customers.show', $customer) }}" wire:navigate>View Profile</flux:menu.item>
                                <flux:menu.item icon="pencil" wire:click="openEditModal({{ $customer->id }})">Edit</flux:menu.item>
                                <flux:menu.separator />
                                <flux:menu.item icon="trash" variant="danger" wire:click="confirmDelete({{ $customer->id }})">Delete</flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
                    </div>

                    <div class="mt-4 flex items-center justify-between">
                        <div class="flex items-center gap-2 text-sm text-zinc-500">
                            <flux:icon name="phone" class="h-4 w-4" />
                            <span>{{ $customer->phone ?? 'No phone' }}</span>
                        </div>
                        @if($customer->is_active)
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                Active
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-600 dark:bg-zinc-700 dark:text-zinc-400">
                                <span class="h-1.5 w-1.5 rounded-full bg-zinc-400"></span>
                                Inactive
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="relative overflow-hidden rounded-xl border-2 border-dashed border-zinc-200 bg-zinc-50 p-12 text-center dark:border-zinc-700 dark:bg-zinc-800/50">
            <div class="mx-auto w-fit rounded-full bg-zinc-100 p-4 dark:bg-zinc-700">
                <flux:icon name="users" class="h-10 w-10 text-zinc-400 dark:text-zinc-500" />
            </div>
            <div class="mt-4 font-medium text-zinc-600 dark:text-zinc-400">No customers found</div>
            <div class="mt-1 text-sm text-zinc-500">Add your first customer to get started</div>
            <button wire:click="openCreateModal"
                class="mt-6 inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-sky-500 to-sky-600 px-4 py-2 text-sm font-medium text-white shadow-lg transition-transform hover:shadow-xl hover:scale-105">
                <flux:icon name="plus" class="h-4 w-4" />
                Add Customer
            </button>
        </div>
    @endif

    @if($customers->hasPages())
        <div class="mt-4">{{ $customers->links() }}</div>
    @endif

    {{-- Create/Edit Modal --}}
    <flux:modal wire:model="showModal" class="max-w-md">
        <flux:heading size="lg" class="mb-6">{{ $editingUser ? 'Edit Customer' : 'Add Customer' }}</flux:heading>
        <form wire:submit="save" class="space-y-4">
            <flux:field>
                <flux:label>Name</flux:label>
                <flux:input wire:model="name" />
                <flux:error name="name" />
            </flux:field>
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
            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>{{ $editingUser ? 'New Password' : 'Password' }}</flux:label>
                    <flux:input type="password" wire:model="password" />
                    <flux:error name="password" />
                </flux:field>
                <flux:field>
                    <flux:label>Confirm</flux:label>
                    <flux:input type="password" wire:model="password_confirmation" />
                </flux:field>
            </div>
            <flux:checkbox wire:model="is_active" label="Active" />
            <div class="flex justify-end gap-3 pt-4">
                <flux:button variant="ghost" wire:click="closeModal">Cancel</flux:button>
                <button type="submit"
                    class="inline-flex items-center justify-center rounded-lg bg-gradient-to-r from-sky-500 to-sky-600 px-4 py-2 text-sm font-medium text-white shadow-lg transition-transform duration-300 ease-out hover:shadow-xl hover:scale-105">
                    {{ $editingUser ? 'Update' : 'Create' }}
                </button>
            </div>
        </form>
    </flux:modal>

    {{-- Delete Modal --}}
    <flux:modal wire:model="showDeleteModal" class="max-w-md">
        <flux:heading size="lg" class="mb-4">Delete Customer</flux:heading>
        <flux:text>Delete <strong>{{ $deletingUser?->name }}</strong>?</flux:text>
        <div class="flex justify-end gap-3 mt-6">
            <flux:button variant="ghost" wire:click="$set('showDeleteModal', false)">Cancel</flux:button>
            <flux:button variant="danger" wire:click="delete">Delete</flux:button>
        </div>
    </flux:modal>
</div>