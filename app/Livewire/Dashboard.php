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
        $user = auth()->user();

        if ($user->hasRole('Customer')) {
            $myTotalBookings = Appointment::where('customer_email', $user->email)->count();
            $myUpcomingAppointments = Appointment::where('customer_email', $user->email)
                ->with(['service', 'user'])
                ->upcoming()
                ->take(5)
                ->get();

            return view('livewire.dashboard', [
                'myTotalBookings' => $myTotalBookings,
                'myCompletedBookings' => Appointment::where('customer_email', $user->email)->where('status', 'completed')->count(),
                'myCancelledBookings' => Appointment::where('customer_email', $user->email)->where('status', 'cancelled')->count(),
                'myUpcomingAppointments' => $myUpcomingAppointments,
                'isAdmin' => false,
            ]);
        }

        // Admin Stats
        $query = Appointment::query();

        // Filter for Staff (who are not Super Admin)
        if ($user->hasRole('Staff') && !$user->hasRole('Super Admin')) {
            $query->where('user_id', $user->id);
        }

        $todayCount = (clone $query)->today()->count();

        // "Pending this week"
        $pendingThisWeekCount = (clone $query)->thisWeek()
            ->where('status', 'booked')
            ->count();

        // "Confirmed upcoming week" (Next 7 days including today)
        $confirmedUpcomingCount = (clone $query)->where('status', 'confirmed')
            ->whereBetween('starts_at', [now(), now()->addDays(7)])
            ->count();

        // Reset to showing confirmed appointments only, as requested
        $upcomingAppointments = (clone $query)->with(['service', 'user'])
            ->where('status', 'confirmed')
            ->upcoming()
            ->take(5)
            ->get();

        $totalServices = Service::active()->count();

        return view('livewire.dashboard', [
            'todayCount' => $todayCount,
            'pendingThisWeekCount' => $pendingThisWeekCount,
            'confirmedUpcomingCount' => $confirmedUpcomingCount,
            'upcomingAppointments' => $upcomingAppointments,
            'totalServices' => $totalServices,
            'isAdmin' => true,
        ]);

        return view('livewire.dashboard', [
            'todayCount' => $todayCount,
            'thisWeekCount' => $thisWeekCount,
            'upcomingAppointments' => $upcomingAppointments,
            'totalServices' => $totalServices,
            'totalStaff' => $totalStaff,
            'isAdmin' => true,
        ]);
    }
}
