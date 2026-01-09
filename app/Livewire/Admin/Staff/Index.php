<?php

namespace App\Livewire\Admin\Staff;

use App\Models\Service;
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
    public bool $showAvailabilityModal = false;
    public ?User $editingUser = null;
    public ?User $deletingUser = null;
    public ?User $availabilityUser = null;

    // Form fields
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $bio = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $is_active = true;
    public array $selectedServices = [];

    // Availability form
    public array $availabilities = [];

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
            'bio' => 'nullable|string',
            'password' => $passwordRule,
            'is_active' => 'boolean',
            'selectedServices' => 'array',
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
        $this->bio = $user->bio ?? '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->is_active = $user->is_active;
        $this->selectedServices = $user->services->pluck('id')->toArray();
        $this->showModal = true;
    }

    public function openAvailabilityModal(User $user): void
    {
        $this->availabilityUser = $user->load(['availabilities.service', 'services']);
        $this->loadAvailabilities();
        $this->showAvailabilityModal = true;
    }

    private function loadAvailabilities(): void
    {
        $this->availabilities = [];
        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        foreach ($this->availabilityUser->services as $service) {
            foreach (range(0, 6) as $day) {
                $existing = $this->availabilityUser->availabilities
                    ->where('service_id', $service->id)
                    ->where('day_of_week', $day)
                    ->first();

                $key = "{$service->id}_{$day}";
                $this->availabilities[$key] = [
                    'service_id' => $service->id,
                    'service_name' => $service->name,
                    'day_of_week' => $day,
                    'day_name' => $days[$day],
                    'enabled' => $existing !== null,
                    'start_time' => $existing?->start_time?->format('H:i') ?? '09:00',
                    'end_time' => $existing?->end_time?->format('H:i') ?? '17:00',
                ];
            }
        }
    }

    public function saveAvailabilities(): void
    {
        if (!$this->availabilityUser)
            return;

        $this->availabilityUser->availabilities()->delete();

        foreach ($this->availabilities as $avail) {
            if ($avail['enabled']) {
                $this->availabilityUser->availabilities()->create([
                    'service_id' => $avail['service_id'],
                    'day_of_week' => $avail['day_of_week'],
                    'start_time' => $avail['start_time'],
                    'end_time' => $avail['end_time'],
                    'is_recurring' => true,
                ]);
            }
        }

        Flux::toast('Availability updated.', variant: 'success');
        $this->showAvailabilityModal = false;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone ?: null,
            'bio' => $this->bio ?: null,
            'is_active' => $this->is_active,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->editingUser) {
            $this->editingUser->update($data);
            $this->editingUser->services()->sync($this->selectedServices);
            Flux::toast('Staff member updated.', variant: 'success');
        } else {
            $user = User::create($data);
            $user->assignRole('Staff');
            $user->services()->sync($this->selectedServices);
            Flux::toast('Staff member created.', variant: 'success');
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
            Flux::toast('Staff member deleted.', variant: 'success');
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
        $this->bio = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->is_active = true;
        $this->selectedServices = [];
        $this->resetValidation();
    }

    public function render()
    {
        $staffMembers = User::query()
            ->role('Staff')
            ->with('services')
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(10);

        $services = Service::active()->orderBy('name')->get();

        return view('livewire.admin.staff.index', [
            'staffMembers' => $staffMembers,
            'services' => $services,
        ])->layout('components.layouts.app', ['title' => 'Staff']);
    }
}
