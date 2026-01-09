<?php

namespace App\Livewire\Admin\Admins;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Flux\Flux;
use Illuminate\Support\Facades\Hash;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?User $editingUser = null;
    public ?User $deletingUser = null;

    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    protected function rules(): array
    {
        $emailRule = 'required|email|max:255|unique:users,email';
        if ($this->editingUser) {
            $emailRule .= ',' . $this->editingUser->id;
        }

        $passwordRule = $this->editingUser ? 'nullable|min:8|confirmed' : 'required|min:8|confirmed';

        return [
            'name' => 'required|string|max:255',
            'email' => $emailRule,
            'password' => $passwordRule,
        ];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->editingUser = null;
        $this->showModal = true;
    }

    public function openEditModal(User $user): void
    {
        $this->editingUser = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->password_confirmation = '';
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'email' => $this->email,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->editingUser) {
            $this->editingUser->update($data);
            Flux::toast('Admin updated.', variant: 'success');
        } else {
            $user = User::create($data);
            $user->assignRole('Super Admin');
            Flux::toast('Admin created.', variant: 'success');
        }

        $this->closeModal();
    }

    public function confirmDelete(User $user): void
    {
        $this->deletingUser = $user;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if ($this->deletingUser) {
            if ($this->deletingUser->id === auth()->id()) {
                Flux::toast('Cannot delete yourself.', variant: 'danger');
            } else {
                $this->deletingUser->delete();
                Flux::toast('Admin deleted.', variant: 'success');
            }
        }
        $this->showDeleteModal = false;
        $this->deletingUser = null;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->resetValidation();
    }

    public function render()
    {
        $admins = User::query()
            ->role('Super Admin')
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.admin.admins.index', [
            'admins' => $admins,
        ])->layout('components.layouts.app', ['title' => 'Admins']);
    }
}
