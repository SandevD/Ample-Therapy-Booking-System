<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl">Roles & Permissions</flux:heading>
            <flux:text class="mt-1 text-zinc-500">Manage roles and their permission assignments.</flux:text>
        </div>
        <button wire:click="openCreateModal"
            class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-fuchsia-500 to-fuchsia-600 px-4 py-2.5 text-sm font-medium text-white shadow-lg transition-transform hover:shadow-xl hover:scale-105">
            <flux:icon name="plus" class="h-4 w-4" />
            Add Role
        </button>
    </div>

    {{-- Roles Grid --}}
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        @foreach($roles as $role)
            @php
                $colors = [
                    'Super Admin' => 'from-red-500 to-red-600',
                    'Staff' => 'from-amber-500 to-amber-600',
                    'Customer' => 'from-sky-500 to-sky-600',
                ];
                $gradient = $colors[$role->name] ?? 'from-fuchsia-500 to-fuchsia-600';
            @endphp
            <div
                class="group relative overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm transition-shadow duration-300 hover:shadow-lg dark:border-zinc-700 dark:bg-zinc-800">
                {{-- Gradient header --}}
                <div class="h-3 bg-gradient-to-r {{ $gradient }}"></div>

                <div class="p-5">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="rounded-xl bg-gradient-to-br {{ $gradient }} p-2.5">
                                <flux:icon name="shield-check" class="h-5 w-5 text-white" />
                            </div>
                            <div>
                                <h3 class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $role->name }}</h3>
                                <p class="text-sm text-zinc-500">{{ $role->permissions->count() }} permissions</p>
                            </div>
                        </div>
                        <flux:dropdown position="bottom" align="end">
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                            <flux:menu>
                                <flux:menu.item icon="pencil" wire:click="openEditModal({{ $role->id }})">Edit
                                </flux:menu.item>
                                @if($role->name !== 'Super Admin')
                                    <flux:menu.separator />
                                    <flux:menu.item icon="trash" variant="danger" wire:click="confirmDelete({{ $role->id }})">
                                        Delete</flux:menu.item>
                                @endif
                            </flux:menu>
                        </flux:dropdown>
                    </div>

                    {{-- Permission Tags --}}
                    <div class="mt-4 flex flex-wrap gap-1.5">
                        @foreach($role->permissions->take(5) as $permission)
                            <span
                                class="inline-flex items-center rounded-full bg-zinc-100 px-2 py-0.5 text-xs font-medium text-zinc-600 dark:bg-zinc-700 dark:text-zinc-400">
                                {{ ucwords(str_replace('_', ' ', $permission->name)) }}
                            </span>
                        @endforeach
                        @if($role->permissions->count() > 5)
                            <span
                                class="inline-flex items-center rounded-full bg-zinc-200 px-2 py-0.5 text-xs font-medium text-zinc-700 dark:bg-zinc-600 dark:text-zinc-300">
                                +{{ $role->permissions->count() - 5 }} more
                            </span>
                        @endif
                        @if($role->permissions->count() === 0)
                            <span class="text-xs text-zinc-400 italic">No permissions assigned</span>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Empty State --}}
    @if($roles->count() === 0)
        <div
            class="relative overflow-hidden rounded-xl border-2 border-dashed border-zinc-200 bg-zinc-50 p-12 text-center dark:border-zinc-700 dark:bg-zinc-800/50">
            <div class="mx-auto w-fit rounded-full bg-zinc-100 p-4 dark:bg-zinc-700">
                <flux:icon name="shield-check" class="h-10 w-10 text-zinc-400 dark:text-zinc-500" />
            </div>
            <div class="mt-4 font-medium text-zinc-600 dark:text-zinc-400">No roles found</div>
            <div class="mt-1 text-sm text-zinc-500">Create your first role to manage permissions</div>
            <button wire:click="openCreateModal"
                class="mt-6 inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-fuchsia-500 to-fuchsia-600 px-4 py-2 text-sm font-medium text-white shadow-lg transition-transform hover:shadow-xl hover:scale-105">
                <flux:icon name="plus" class="h-4 w-4" />
                Add Role
            </button>
        </div>
    @endif

    {{-- Create/Edit Modal --}}
    <flux:modal wire:model="showModal" class="max-w-2xl">
        <div class="space-y-6">
            <flux:heading size="lg">{{ $editingRole ? 'Edit Role' : 'Create Role' }}</flux:heading>

            <form wire:submit="save" class="space-y-6">
                <flux:field>
                    <flux:label>Role Name</flux:label>
                    <flux:input wire:model="name" placeholder="e.g., Manager" />
                    <flux:error name="name" />
                </flux:field>

                <div>
                    <div class="flex items-center justify-between mb-4">
                        <flux:label>Permissions</flux:label>
                        <div class="flex gap-2">
                            <button type="button" wire:click="selectAll"
                                class="text-sm text-zinc-500 hover:text-zinc-700 transition-colors">Select All</button>
                            <span class="text-zinc-300">|</span>
                            <button type="button" wire:click="deselectAll"
                                class="text-sm text-zinc-500 hover:text-zinc-700 transition-colors">Deselect
                                All</button>
                        </div>
                    </div>

                    <div
                        class="space-y-4 max-h-96 overflow-y-auto rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                        @foreach($permissionGroups as $group => $groupPermissions)
                            @php
                                $allSelected = collect($groupPermissions)->every(fn($p) => in_array($p, $selectedPermissions));
                                $someSelected = collect($groupPermissions)->some(fn($p) => in_array($p, $selectedPermissions));
                            @endphp
                            <div class="border-b border-zinc-100 pb-4 last:border-b-0 last:pb-0 dark:border-zinc-800">
                                <label class="flex items-center gap-2 cursor-pointer mb-3">
                                    <input type="checkbox" wire:click="toggleGroup('{{ $group }}')" @checked($allSelected)
                                        class="rounded border-zinc-300" />
                                    <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $group }}</span>
                                    @if($someSelected && !$allSelected)
                                        <span class="text-xs text-zinc-400">(partial)</span>
                                    @endif
                                </label>
                                <div class="ml-6 grid grid-cols-2 gap-2">
                                    @foreach($groupPermissions as $permission)
                                        <label
                                            class="flex items-center gap-2 cursor-pointer rounded-lg border border-zinc-200 p-2.5 hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800 transition-colors">
                                            <input type="checkbox" value="{{ $permission }}" wire:model="selectedPermissions"
                                                class="rounded border-zinc-300" />
                                            <span class="text-sm">{{ ucwords(str_replace('_', ' ', $permission)) }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <flux:error name="selectedPermissions" />
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <flux:button variant="ghost" wire:click="closeModal">Cancel</flux:button>
                    <button type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-gradient-to-r from-fuchsia-500 to-fuchsia-600 px-4 py-2 text-sm font-medium text-white shadow-lg transition-transform duration-300 ease-out hover:shadow-xl hover:scale-105">
                        {{ $editingRole ? 'Update' : 'Create' }}
                    </button>
                </div>
            </form>
        </div>
    </flux:modal>

    {{-- Delete Modal --}}
    <flux:modal wire:model="showDeleteModal" class="max-w-md">
        <div class="space-y-6">
            <flux:heading size="lg">Delete Role</flux:heading>
            <flux:text>Are you sure you want to delete <strong>{{ $deletingRole?->name }}</strong>? Users with this role
                will lose associated permissions.</flux:text>
            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" wire:click="$set('showDeleteModal', false)">Cancel</flux:button>
                <flux:button variant="danger" wire:click="delete">Delete</flux:button>
            </div>
        </div>
    </flux:modal>
</div>