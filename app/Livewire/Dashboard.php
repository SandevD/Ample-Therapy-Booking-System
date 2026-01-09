<?php

namespace App\Livewire;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class Dashboard extends Component
{
    public function render()
    {
        $todayCount = Appointment::today()->count();
        $thisWeekCount = Appointment::thisWeek()->count();
        $upcomingAppointments = Appointment::with(['service', 'user'])
            ->upcoming()
            ->take(5)
            ->get();
        $totalServices = Service::active()->count();

        // Handle case where Staff role doesn't exist (e.g., in tests)
        $totalStaff = Role::where('name', 'Staff')->exists()
            ? User::role('Staff')->active()->count()
            : 0;

        return view('livewire.dashboard', [
            'todayCount' => $todayCount,
            'thisWeekCount' => $thisWeekCount,
            'upcomingAppointments' => $upcomingAppointments,
            'totalServices' => $totalServices,
            'totalStaff' => $totalStaff,
        ]);
    }
}
