<?php

namespace App\Livewire\Admin\Roles;

use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Flux\Flux;

class Index extends Component
{
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?Role $editingRole = null;
    public ?Role $deletingRole = null;

    public string $name = '';
    public array $selectedPermissions = [];

    // Group permissions by category for better UI
    public array $permissionGroups = [
        'Dashboard' => ['view_dashboard'],
        'Services' => ['view_services', 'create_services', 'edit_services', 'delete_services'],
        'Staff' => ['view_staff', 'create_staff', 'edit_staff', 'delete_staff'],
        'Appointments' => ['view_appointments', 'create_appointments', 'edit_appointments', 'delete_appointments', 'view_own_appointments'],
        'Users & Roles' => ['view_users', 'create_users', 'edit_users', 'delete_users', 'manage_roles'],
    ];

    protected function rules(): array
    {
        $nameRule = 'required|string|max:255|unique:roles,name';
        if ($this->editingRole) {
            $nameRule .= ',' . $this->editingRole->id;
        }

        return [
            'name' => $nameRule,
            'selectedPermissions' => 'array',
        ];
    }

    public function openCreateModal(): void
    {
        $this->reset(['name', 'selectedPermissions', 'editingRole']);
        $this->showModal = true;
    }

    public function openEditModal(Role $role): void
    {
        $this->editingRole = $role;
        $this->name = $role->name;
        $this->selectedPermissions = $role->permissions->pluck('name')->toArray();
        $this->showModal = true;
    }

    public function toggleGroup(string $group): void
    {
        $groupPermissions = $this->permissionGroups[$group] ?? [];
        $allSelected = collect($groupPermissions)->every(fn($p) => in_array($p, $this->selectedPermissions));

        if ($allSelected) {
            $this->selectedPermissions = array_diff($this->selectedPermissions, $groupPermissions);
        } else {
            $this->selectedPermissions = array_unique(array_merge($this->selectedPermissions, $groupPermissions));
        }
    }

    public function selectAll(): void
    {
        $this->selectedPermissions = Permission::pluck('name')->toArray();
    }

    public function deselectAll(): void
    {
        $this->selectedPermissions = [];
    }

    public function save(): void
    {
        $this->validate();

        if ($this->editingRole) {
            $this->editingRole->update(['name' => $this->name]);
            $this->editingRole->syncPermissions($this->selectedPermissions);
            Flux::toast('Role updated successfully.', variant: 'success');
        } else {
            $role = Role::create(['name' => $this->name, 'guard_name' => 'web']);
            $role->syncPermissions($this->selectedPermissions);
            Flux::toast('Role created successfully.', variant: 'success');
        }

        $this->closeModal();
    }

    public function confirmDelete(Role $role): void
    {
        $this->deletingRole = $role;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if ($this->deletingRole) {
            // Prevent deleting Super Admin
            if ($this->deletingRole->name === 'Super Admin') {
                Flux::toast('Cannot delete Super Admin role.', variant: 'danger');
            } else {
                $this->deletingRole->delete();
                Flux::toast('Role deleted successfully.', variant: 'success');
            }
        }

        $this->showDeleteModal = false;
        $this->deletingRole = null;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['name', 'selectedPermissions', 'editingRole']);
        $this->resetValidation();
    }

    public function render()
    {
        $roles = Role::with('permissions')->orderBy('name')->get();
        $permissions = Permission::orderBy('name')->get();

        return view('livewire.admin.roles.index', [
            'roles' => $roles,
            'permissions' => $permissions,
        ])->layout('components.layouts.app', ['title' => 'Roles & Permissions']);
    }
}
