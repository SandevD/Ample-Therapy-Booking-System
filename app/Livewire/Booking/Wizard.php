<?php

namespace App\Livewire\Booking;

use App\Models\Service;
use App\Models\User;
use App\Models\Appointment;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class Wizard extends Component
{
    // State
    public $step = 1;

    // Selections
    public $selectedServiceId = null;
    public $selectedStaffId = null;
    public $selectedDate = null;
    public $selectedTime = null;

    // Customer Details (if not logged in, but we assume auth for now based on routes)
    public $notes = '';

    public function mount()
    {
        // Initialize if needed
        $this->selectedDate = now()->format('Y-m-d');
    }

    public function selectService($serviceId)
    {
        $this->selectedServiceId = $serviceId;
        $this->nextStep();
    }

    public function selectStaff($staffId)
    {
        $this->selectedStaffId = $staffId;
        $this->nextStep();
    }

    public function selectDateTime($date, $time)
    {
        $this->selectedDate = $date;
        $this->selectedTime = $time;
        // Logic to validate availability would go here
        $this->nextStep();
    }

    public function nextStep()
    {
        $this->step++;
    }

    public function previousStep()
    {
        $this->step--;
    }

    public function submit()
    {
        // Validation
        $this->validate([
            'selectedServiceId' => 'required',
            'selectedStaffId' => 'required',
            'selectedDate' => 'required',
            'selectedTime' => 'required',
        ]);

        $service = Service::find($this->selectedServiceId);
        $startsAt = Carbon::parse($this->selectedDate . ' ' . $this->selectedTime);
        $endsAt = $startsAt->copy()->addMinutes($service->duration);

        // Create Appointment
        Appointment::create([
            'service_id' => $this->selectedServiceId,
            'user_id' => $this->selectedStaffId,
            'customer_name' => auth()->user()->name,
            'customer_email' => auth()->user()->email,
            'customer_phone' => auth()->user()->phone ?? '',
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'status' => 'booked',
            'notes' => $this->notes,
        ]);

        // Reset or Redirect
        return redirect()->route('dashboard');
    }

    public function getTimeSlotsProperty()
    {
        if (!$this->selectedServiceId || !$this->selectedStaffId || !$this->selectedDate) {
            return [];
        }

        $service = Service::find($this->selectedServiceId);
        $slots = [];

        // Start of day
        $current = Carbon::parse($this->selectedDate . ' 09:00:00');

        // End of day (last appointment must finish by 17:00)
        $endOfDay = Carbon::parse($this->selectedDate . ' 17:00:00');

        // Fetch existing appointments for the day to check conflicts efficiently
        $appointments = Appointment::where('user_id', $this->selectedStaffId)
            ->whereDate('starts_at', $this->selectedDate)
            ->where('status', '!=', 'cancelled')
            ->get();

        while (true) {
            $slotStart = $current->copy();
            $slotEnd = $current->copy()->addMinutes($service->duration);

            // If the appointment ends after closing time, stop generating slots
            if ($slotEnd->gt($endOfDay)) {
                break;
            }

            // Check for conflicts
            $conflictingAppointments = $appointments->filter(function ($appointment) use ($slotStart, $slotEnd) {
                return $slotStart->lt($appointment->ends_at) && $slotEnd->gt($appointment->starts_at);
            });

            $hasConfirmed = $conflictingAppointments->contains('status', 'confirmed');
            $pendingCount = $conflictingAppointments->where('status', 'booked')->count();

            $status = 'available';
            if ($hasConfirmed) {
                $status = 'confirmed';
            } elseif ($pendingCount > 0) {
                $status = 'booked'; // This corresponds to 'Pending' in UI
            }

            $slots[] = [
                'time' => $slotStart->format('H:i'),
                'start_formatted' => $slotStart->format('H:i'),
                'end_formatted' => $slotEnd->format('H:i'),
                'status' => $status,
                'pending_count' => $pendingCount,
                'is_bookable' => $status !== 'confirmed',
            ];

            // Next slot starts after duration + buffer
            $current->addMinutes($service->duration + $service->buffer_time);
        }

        return $slots;
    }

    public function render()
    {
        return view('livewire.booking.wizard', [
            'services' => $this->step === 1 ? Service::where('is_active', true)->get() : [],
            'staffMembers' => $this->step === 2 ? User::role('staff')->where('is_active', true)->whereHas('services', function ($q) {
                $q->where('services.id', $this->selectedServiceId);
            })->get() : [],
            'selectedService' => $this->selectedServiceId ? Service::find($this->selectedServiceId) : null,
            'selectedStaff' => $this->selectedStaffId ? User::find($this->selectedStaffId) : null,
        ]);
    }
}
