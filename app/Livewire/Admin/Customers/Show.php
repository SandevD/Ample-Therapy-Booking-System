<?php

namespace App\Livewire\Admin\Customers;

use App\Models\User;
use App\Models\Appointment;
use Livewire\Component;
use Livewire\WithPagination;

class Show extends Component
{
    use WithPagination;

    public User $user;

    public function mount(User $user)
    {
        $this->user = $user;
    }

    public function render()
    {
        $stats = [
            'total' => Appointment::where('customer_email', $this->user->email)->count(),
            'confirmed' => Appointment::where('customer_email', $this->user->email)->where('status', 'confirmed')->count(),
            'completed' => Appointment::where('customer_email', $this->user->email)->where('status', 'completed')->count(),
            'cancelled' => Appointment::where('customer_email', $this->user->email)->where('status', 'cancelled')->count(),
        ];

        $appointments = Appointment::where('customer_email', $this->user->email)
            // 'user' relation on Appointment refers to the Staff member provider
            ->with(['service', 'user'])
            ->orderBy('starts_at', 'desc')
            ->paginate(10);

        return view('livewire.admin.customers.show', [
            'stats' => $stats,
            'appointments' => $appointments,
        ])->layout('components.layouts.app', ['title' => 'Customer Profile']);
    }
}
