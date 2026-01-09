<?php

namespace App\Livewire\Admin\Customers;

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
    public string $phone = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $is_active = true;

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
            'phone' => 'nullable|string|max:50',
            'password' => $passwordRule,
            'is_active' => 'boolean',
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
        $this->phone = $user->phone ?? '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->is_active = $user->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone ?: null,
            'is_active' => $this->is_active,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->editingUser) {
            $this->editingUser->update($data);
            Flux::toast('Customer updated.', variant: 'success');
        } else {
            $user = User::create($data);
            $user->assignRole('Customer');
            Flux::toast('Customer created.', variant: 'success');
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
            $this->deletingUser->delete();
            Flux::toast('Customer deleted.', variant: 'success');
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
        $this->phone = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        $customers = User::query()
            ->role('Customer')
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.admin.customers.index', [
            'customers' => $customers,
        ])->layout('components.layouts.app', ['title' => 'Customers']);
    }
}
