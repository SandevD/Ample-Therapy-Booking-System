<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl">Admins</flux:heading>
            <flux:text class="mt-1 text-zinc-500">Manage administrator accounts.</flux:text>
        </div>
        <button wire:click="openCreateModal"
            class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-red-500 to-red-600 px-4 py-2.5 text-sm font-medium text-white shadow-lg transition-transform hover:shadow-xl hover:scale-105">
            <flux:icon name="plus" class="h-4 w-4" />
            Add Admin
        </button>
    </div>

    {{-- Search --}}
    <flux:input wire:model.live.debounce.300ms="search" placeholder="Search admins..." icon="magnifying-glass"
        class="max-w-sm" />

    {{-- Admins Grid --}}
    @if($admins->count() > 0)
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($admins as $admin)
                <div
                    class="group relative overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm transition-shadow duration-300 hover:shadow-lg dark:border-zinc-700 dark:bg-zinc-800">
                    {{-- Gradient header --}}
                    <div class="h-20 bg-gradient-to-r from-red-500 to-red-600 relative">
                        @if($admin->id === auth()->id())
                            <div class="absolute top-3 right-3">
                                <span
                                    class="inline-flex items-center rounded-full bg-white/20 px-2.5 py-1 text-xs font-medium text-white backdrop-blur-sm">
                                    You
                                </span>
                            </div>
                        @endif
                    </div>

                    {{-- Avatar overlay --}}
                    <div class="relative px-5 pb-5">
                        <div class="-mt-10 mb-3 flex items-end justify-between">
                            <div
                                class="inline-flex h-20 w-20 items-center justify-center rounded-xl bg-white text-2xl font-bold text-rose-600 shadow-lg ring-4 ring-white dark:bg-zinc-800 dark:ring-zinc-800">
                                {{ $admin->initials() }}
                            </div>
                            <flux:dropdown position="bottom" align="end">
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                <flux:menu>
                                    <flux:menu.item icon="pencil" wire:click="openEditModal({{ $admin->id }})">Edit
                                    </flux:menu.item>
                                    @if($admin->id !== auth()->id())
                                        <flux:menu.separator />
                                        <flux:menu.item icon="trash" variant="danger" wire:click="confirmDelete({{ $admin->id }})">
                                            Delete</flux:menu.item>
                                    @endif
                                </flux:menu>
                            </flux:dropdown>
                        </div>

                        <div>
                            <h3 class="font-semibold text-lg text-zinc-900 dark:text-zinc-100">{{ $admin->name }}</h3>
                            <p class="text-sm text-zinc-500">{{ $admin->email }}</p>
                        </div>

                        <div class="mt-4 flex items-center justify-between text-sm">
                            <div class="flex items-center gap-2 text-zinc-500">
                                <flux:icon name="shield-check" class="h-4 w-4 text-rose-500" />
                                <span>Super Admin</span>
                            </div>
                            <span class="text-zinc-400">{{ $admin->created_at->format('M j, Y') }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div
            class="relative overflow-hidden rounded-xl border-2 border-dashed border-zinc-200 bg-zinc-50 p-12 text-center dark:border-zinc-700 dark:bg-zinc-800/50">
            <div class="mx-auto w-fit rounded-full bg-zinc-100 p-4 dark:bg-zinc-700">
                <flux:icon name="shield-check" class="h-10 w-10 text-zinc-400 dark:text-zinc-500" />
            </div>
            <div class="mt-4 font-medium text-zinc-600 dark:text-zinc-400">No admins found</div>
            <div class="mt-1 text-sm text-zinc-500">Add your first administrator</div>
            <button wire:click="openCreateModal"
                class="mt-6 inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-red-500 to-red-600 px-4 py-2 text-sm font-medium text-white shadow-lg transition-transform hover:shadow-xl hover:scale-105">
                <flux:icon name="plus" class="h-4 w-4" />
                Add Admin
            </button>
        </div>
    @endif

    @if($admins->hasPages())
        <div class="mt-4">{{ $admins->links() }}</div>
    @endif

    {{-- Create/Edit Modal --}}
    <flux:modal wire:model="showModal" class="max-w-md">
        <flux:heading size="lg" class="mb-6">{{ $editingUser ? 'Edit Admin' : 'Add Admin' }}</flux:heading>
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
            <div class="flex justify-end gap-3 pt-4">
                <flux:button variant="ghost" wire:click="closeModal">Cancel</flux:button>
                <button type="submit"
                    class="inline-flex items-center justify-center rounded-lg bg-gradient-to-r from-red-500 to-red-600 px-4 py-2 text-sm font-medium text-white shadow-lg transition-transform duration-300 ease-out hover:shadow-xl hover:scale-105">
                    {{ $editingUser ? 'Update' : 'Create' }}
                </button>
            </div>
        </form>
    </flux:modal>

    {{-- Delete Modal --}}
    <flux:modal wire:model="showDeleteModal" class="max-w-md">
        <flux:heading size="lg" class="mb-4">Delete Admin</flux:heading>
        <flux:text>Delete <strong>{{ $deletingUser?->name }}</strong>?</flux:text>
        <div class="flex justify-end gap-3 mt-6">
            <flux:button variant="ghost" wire:click="$set('showDeleteModal', false)">Cancel</flux:button>
            <flux:button variant="danger" wire:click="delete">Delete</flux:button>
        </div>
    </flux:modal>
</div>