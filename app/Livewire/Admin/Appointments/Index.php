<?php

namespace App\Livewire\Admin\Appointments;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;
use Flux\Flux;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $staffFilter = '';
    public string $dateFilter = '';

    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?Appointment $editingAppointment = null;
    public ?Appointment $deletingAppointment = null;

    // Form fields
    public ?int $service_id = null;
    public ?int $user_id = null;
    public string $customer_name = '';
    public string $customer_email = '';
    public string $customer_phone = '';
    public string $date = '';
    public string $start_time = '';
    public string $status = 'booked';
    public string $notes = '';

    protected function rules(): array
    {
        return [
            'service_id' => 'required|exists:services,id',
            'user_id' => 'required|exists:users,id',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'date' => 'required|date',
            'start_time' => 'required',
            'status' => 'required|in:booked,confirmed,completed,cancelled',
            'notes' => 'nullable|string',
        ];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStaffFilter(): void
    {
        $this->resetPage();
    }

    public function updatedDateFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->editingAppointment = null;
        $this->date = Carbon::today()->format('Y-m-d');
        $this->showModal = true;
    }

    public function openEditModal(Appointment $appointment): void
    {
        $this->editingAppointment = $appointment;
        $this->service_id = $appointment->service_id;
        $this->user_id = $appointment->user_id;
        $this->customer_name = $appointment->customer_name;
        $this->customer_email = $appointment->customer_email;
        $this->customer_phone = $appointment->customer_phone ?? '';
        $this->date = $appointment->starts_at->format('Y-m-d');
        $this->start_time = $appointment->starts_at->format('H:i');
        $this->status = $appointment->status;
        $this->notes = $appointment->notes ?? '';
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $service = Service::findOrFail($this->service_id);
        $startsAt = Carbon::parse("{$this->date} {$this->start_time}");
        $endsAt = $startsAt->copy()->addMinutes($service->duration);

        // Check for conflicts (excluding current appointment if editing)
        if (
            Appointment::hasConflict(
                $this->user_id,
                $startsAt,
                $endsAt->copy()->addMinutes($service->buffer_time),
                $this->editingAppointment?->id
            )
        ) {
            $this->addError('start_time', 'This time slot is not available due to an existing booking or buffer time.');
            return;
        }

        $data = [
            'service_id' => $this->service_id,
            'user_id' => $this->user_id,
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'customer_phone' => $this->customer_phone ?: null,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'status' => $this->status,
            'notes' => $this->notes ?: null,
        ];

        if ($this->editingAppointment) {
            // Security check
            if (auth()->user()->hasRole('Customer') && $this->editingAppointment->customer_email !== auth()->user()->email) {
                Flux::toast('Unauthorized action.', variant: 'danger');
                return;
            }

            $this->editingAppointment->update($data);
            Flux::toast('Appointment updated.', variant: 'success');
        } else {
            // Customers should generally use the wizard, but if they use this form:
            if (auth()->user()->hasRole('Customer')) {
                // Enforce their own identity
                $data['customer_email'] = auth()->user()->email;
            }

            Appointment::create($data);
            Flux::toast('Appointment created.', variant: 'success');
        }

        $this->closeModal();
    }

    public bool $showConfirmStatusModal = false;
    public ?Appointment $confirmingAppointment = null;
    public string $pendingStatusUpdate = '';
    public int $conflictingCount = 0;

    public bool $showBlockModal = false;
    public ?Appointment $blockingAppointment = null;

    public function updateStatus(Appointment $appointment, string $status): void
    {
        // Security check: Ensure customers can only update their own appointments
        if (auth()->user()->hasRole('Customer') && $appointment->customer_email !== auth()->user()->email) {
            Flux::toast('Unauthorized action.', variant: 'danger');
            return;
        }

        // Check if there's already a 'confirmed' or 'completed' appointment in this slot (excluding current)
        $blocking = Appointment::where('user_id', $appointment->user_id)
            ->where('id', '!=', $appointment->id)
            ->whereIn('status', ['confirmed', 'completed'])
            ->where(function ($q) use ($appointment) {
                $q->whereBetween('starts_at', [$appointment->starts_at, $appointment->ends_at])
                    ->orWhereBetween('ends_at', [$appointment->starts_at, $appointment->ends_at])
                    ->orWhere(function ($q2) use ($appointment) {
                        $q2->where('starts_at', '<=', $appointment->starts_at)
                            ->where('ends_at', '>=', $appointment->ends_at);
                    });
            })->first();

        if ($blocking) {
            $this->blockingAppointment = $blocking;
            $this->showBlockModal = true;
            return;
        }

        if ($status === 'confirmed') {
            // Check for conflicting 'booked' (pending) appointments
            // Same staff, excluding current appointment, status is booked/pending
            $conflicts = Appointment::where('user_id', $appointment->user_id)
                ->where('id', '!=', $appointment->id)
                ->where('status', 'booked')
                ->where(function ($q) use ($appointment) {
                    $q->whereBetween('starts_at', [$appointment->starts_at, $appointment->ends_at])
                        ->orWhereBetween('ends_at', [$appointment->starts_at, $appointment->ends_at])
                        ->orWhere(function ($q2) use ($appointment) {
                            $q2->where('starts_at', '<=', $appointment->starts_at)
                                ->where('ends_at', '>=', $appointment->ends_at);
                        });
                })->count();

            if ($conflicts > 0) {
                $this->confirmingAppointment = $appointment;
                $this->pendingStatusUpdate = $status;
                $this->conflictingCount = $conflicts;
                $this->showConfirmStatusModal = true;
                return;
            }
        }

        $appointment->update(['status' => $status]);
        Flux::toast('Appointment status updated.', variant: 'success');
    }

    public function confirmStatusUpdate(): void
    {
        if (!$this->confirmingAppointment)
            return;

        $appointment = $this->confirmingAppointment;

        // Cancel conflicts
        Appointment::where('user_id', $appointment->user_id)
            ->where('id', '!=', $appointment->id)
            ->where('status', 'booked')
            ->where(function ($q) use ($appointment) {
                $q->whereBetween('starts_at', [$appointment->starts_at, $appointment->ends_at])
                    ->orWhereBetween('ends_at', [$appointment->starts_at, $appointment->ends_at])
                    ->orWhere(function ($q2) use ($appointment) {
                        $q2->where('starts_at', '<=', $appointment->starts_at)
                            ->where('ends_at', '>=', $appointment->ends_at);
                    });
            })->update(['status' => 'cancelled']);

        $appointment->update(['status' => $this->pendingStatusUpdate]);

        Flux::toast('Appointment confirmed and ' . $this->conflictingCount . ' conflicts cancelled.', variant: 'success');

        $this->showConfirmStatusModal = false;
        $this->confirmingAppointment = null;
        $this->pendingStatusUpdate = '';
        $this->conflictingCount = 0;
    }

    public function confirmDelete(Appointment $appointment): void
    {
        $this->deletingAppointment = $appointment;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if ($this->deletingAppointment) {
            $this->deletingAppointment->delete();
            Flux::toast('Appointment deleted.', variant: 'success');
        }

        $this->showDeleteModal = false;
        $this->deletingAppointment = null;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->service_id = null;
        $this->user_id = null;
        $this->customer_name = '';
        $this->customer_email = '';
        $this->customer_phone = '';
        $this->date = '';
        $this->start_time = '';
        $this->status = 'booked';
        $this->notes = '';
        $this->resetValidation();
    }

    public function getAvailableStaff(): \Illuminate\Support\Collection
    {
        if (!$this->service_id) {
            return collect();
        }

        return User::role('Staff')
            ->active()
            ->whereHas('services', fn($q) => $q->where('services.id', $this->service_id))
            ->orderBy('name')
            ->get();
    }

    public function render()
    {
        $appointments = Appointment::query()
            ->with(['service', 'user'])
            ->when(auth()->user()->hasRole('Customer'), fn($q) => $q->where('customer_email', auth()->user()->email))
            ->when($this->search, fn($q) => $q->where(function ($q2) {
                $q2->where('customer_name', 'like', "%{$this->search}%")
                    ->orWhere('customer_email', 'like', "%{$this->search}%");
            }))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->staffFilter, fn($q) => $q->where('user_id', $this->staffFilter))
            ->when($this->dateFilter, fn($q) => $q->whereDate('starts_at', $this->dateFilter))
            ->orderBy('starts_at', 'desc')
            ->paginate(15);

        $services = Service::active()->orderBy('name')->get();
        $staffMembers = User::role('Staff')->active()->orderBy('name')->get();

        return view('livewire.admin.appointments.index', [
            'appointments' => $appointments,
            'services' => $services,
            'staffMembers' => $staffMembers,
            'availableStaff' => $this->getAvailableStaff(),
        ])->layout('components.layouts.app', ['title' => 'Appointments']);
    }
}
