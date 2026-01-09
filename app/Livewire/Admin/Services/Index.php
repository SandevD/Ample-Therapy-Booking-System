<?php

namespace App\Livewire\Admin\Services;

use App\Models\Service;
use Livewire\Component;
use Livewire\WithPagination;
use Flux\Flux;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?Service $editingService = null;
    public ?Service $deletingService = null;

    // Form fields
    public string $name = '';
    public string $description = '';
    public int $duration = 60;
    public float $price = 0;
    public int $buffer_time = 15;
    public bool $is_active = true;
    public string $color = '#3B82F6';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration' => 'required|integer|min:5|max:480',
            'price' => 'required|numeric|min:0',
            'buffer_time' => 'required|integer|min:0|max:120',
            'is_active' => 'boolean',
            'color' => 'required|string|max:7',
        ];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->editingService = null;
        $this->showModal = true;
    }

    public function openEditModal(Service $service): void
    {
        $this->editingService = $service;
        $this->name = $service->name;
        $this->description = $service->description ?? '';
        $this->duration = $service->duration;
        $this->price = (float) $service->price;
        $this->buffer_time = $service->buffer_time;
        $this->is_active = $service->is_active;
        $this->color = $service->color;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'description' => $this->description,
            'duration' => $this->duration,
            'price' => $this->price,
            'buffer_time' => $this->buffer_time,
            'is_active' => $this->is_active,
            'color' => $this->color,
        ];

        if ($this->editingService) {
            $this->editingService->update($data);
            Flux::toast('Service updated successfully.', variant: 'success');
        } else {
            Service::create($data);
            Flux::toast('Service created successfully.', variant: 'success');
        }

        $this->closeModal();
    }

    public function confirmDelete(Service $service): void
    {
        $this->deletingService = $service;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if ($this->deletingService) {
            $this->deletingService->delete();
            Flux::toast('Service deleted successfully.', variant: 'success');
        }

        $this->showDeleteModal = false;
        $this->deletingService = null;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->name = '';
        $this->description = '';
        $this->duration = 60;
        $this->price = 0;
        $this->buffer_time = 15;
        $this->is_active = true;
        $this->color = '#3B82F6';
        $this->resetValidation();
    }

    public function render()
    {
        $services = Service::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.admin.services.index', [
            'services' => $services,
        ])->layout('components.layouts.app', ['title' => 'Services']);
    }
}
