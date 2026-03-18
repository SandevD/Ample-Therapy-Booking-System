<?php

namespace App\Livewire\Booking;

use App\Models\Service;
use App\Models\User;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class Wizard extends Component
{
    // State
    public int $step = 1;

    // Selections
    public $selectedServiceId = null;
    public $selectedStaffId = null;
    public $selectedDate = null;
    public $selectedTime = null;

    // Multi-session slot accumulator: [['date' => 'Y-m-d', 'time' => 'H:i'], ...]
    public array $selectedSlots = [];

    public string $notes = '';

    public function mount(): void
    {
        $this->selectedDate = now()->format('Y-m-d');
    }

    public function selectService($serviceId): void
    {
        $this->selectedServiceId = $serviceId;
        $this->nextStep();
    }

    public function selectStaff($staffId): void
    {
        $this->selectedStaffId = $staffId;
        $this->nextStep();
    }

    public function selectDateTime(string $date, string $time): void
    {
        $service = Service::find($this->selectedServiceId);

        if ($service->session_count <= 1) {
            // Single-session: original behaviour
            $this->selectedDate = $date;
            $this->selectedTime = $time;
            $this->nextStep();
            return;
        }

        // Multi-session: prevent duplicate slot selection
        $alreadySelected = collect($this->selectedSlots)->contains(
            fn($slot) => $slot['date'] === $date && $slot['time'] === $time
        );

        if ($alreadySelected) {
            return;
        }

        $this->selectedSlots[] = ['date' => $date, 'time' => $time];
        $this->selectedDate = $date;
        $this->selectedTime = $time;

        if (count($this->selectedSlots) >= $service->session_count) {
            $this->nextStep();
        }
    }

    public function removeSlot(int $index): void
    {
        unset($this->selectedSlots[$index]);
        $this->selectedSlots = array_values($this->selectedSlots);
        // Reset the time highlight so nothing looks selected after removal
        $this->selectedTime = null;
    }

    public function nextStep(): void
    {
        $this->step++;
    }

    public function previousStep(): void
    {
        if ($this->step === 4 && $this->selectedServiceId) {
            $service = Service::find($this->selectedServiceId);
            if ($service && $service->session_count > 1 && count($this->selectedSlots) > 0) {
                array_pop($this->selectedSlots);
                $this->selectedSlots = array_values($this->selectedSlots);
                $this->selectedTime = null;
            }
        }
        $this->step--;
    }

    public function submit()
    {
        $service = Service::find($this->selectedServiceId);

        if ($service->session_count <= 1) {
            $this->validate([
                'selectedServiceId' => 'required',
                'selectedStaffId'   => 'required',
                'selectedDate'      => 'required',
                'selectedTime'      => 'required',
            ]);

            $startsAt = Carbon::parse($this->selectedDate . ' ' . $this->selectedTime);
            $endsAt   = $startsAt->copy()->addMinutes($service->duration);

            Appointment::create([
                'service_id'       => $this->selectedServiceId,
                'user_id'          => $this->selectedStaffId,
                'customer_name'    => auth()->user()->name,
                'customer_email'   => auth()->user()->email,
                'customer_phone'   => auth()->user()->phone ?? '',
                'starts_at'        => $startsAt,
                'ends_at'          => $endsAt,
                'status'           => 'booked',
                'notes'            => $this->notes,
                'booking_group_id' => null,
            ]);
        } else {
            $this->validate([
                'selectedServiceId' => 'required',
                'selectedStaffId'   => 'required',
                'selectedSlots'     => 'required|array|min:' . $service->session_count,
            ]);

            $groupId = Str::uuid()->toString();

            foreach ($this->selectedSlots as $slot) {
                $startsAt = Carbon::parse($slot['date'] . ' ' . $slot['time']);
                $endsAt   = $startsAt->copy()->addMinutes($service->duration);

                Appointment::create([
                    'service_id'       => $this->selectedServiceId,
                    'user_id'          => $this->selectedStaffId,
                    'customer_name'    => auth()->user()->name,
                    'customer_email'   => auth()->user()->email,
                    'customer_phone'   => auth()->user()->phone ?? '',
                    'starts_at'        => $startsAt,
                    'ends_at'          => $endsAt,
                    'status'           => 'booked',
                    'notes'            => $this->notes,
                    'booking_group_id' => $groupId,
                ]);
            }
        }

        return redirect()->route('admin.appointments');
    }

    public function getTimeSlotsProperty(): array
    {
        if (!$this->selectedServiceId || !$this->selectedStaffId || !$this->selectedDate) {
            return [];
        }

        $service  = Service::find($this->selectedServiceId);
        $slots    = [];
        $current  = Carbon::parse($this->selectedDate . ' 09:00:00');
        $endOfDay = Carbon::parse($this->selectedDate . ' 17:00:00');

        // Existing DB appointments for this staff member on this day
        $appointments = Appointment::where('user_id', $this->selectedStaffId)
            ->whereDate('starts_at', $this->selectedDate)
            ->where('status', '!=', 'cancelled')
            ->get();

        // In-memory selected slots on this same date (not yet saved to DB)
        $pendingIntervals = collect($this->selectedSlots)
            ->filter(fn($s) => $s['date'] === $this->selectedDate)
            ->map(function ($s) use ($service) {
                $start = Carbon::parse($s['date'] . ' ' . $s['time']);
                return [
                    'starts_at' => $start,
                    'ends_at'   => $start->copy()->addMinutes($service->duration),
                ];
            });

        while (true) {
            $slotStart = $current->copy();
            $slotEnd   = $current->copy()->addMinutes($service->duration);

            if ($slotEnd->gt($endOfDay)) {
                break;
            }

            // DB conflict check
            $conflictingAppointments = $appointments->filter(
                fn($appt) => $slotStart->lt($appt->ends_at) && $slotEnd->gt($appt->starts_at)
            );

            $hasConfirmed = $conflictingAppointments->contains('status', 'confirmed');
            $pendingCount = $conflictingAppointments->where('status', 'booked')->count();

            $status = 'available';
            if ($hasConfirmed) {
                $status = 'confirmed';
            } elseif ($pendingCount > 0) {
                $status = 'booked';
            }

            // In-memory slot conflict check
            $conflictsWithPending = $pendingIntervals->contains(
                fn($interval) => $slotStart->lt($interval['ends_at']) && $slotEnd->gt($interval['starts_at'])
            );

            $isAlreadySelected = collect($this->selectedSlots)->contains(
                fn($s) => $s['date'] === $this->selectedDate && $s['time'] === $slotStart->format('H:i')
            );

            $slots[] = [
                'time'                => $slotStart->format('H:i'),
                'start_formatted'     => $slotStart->format('H:i'),
                'end_formatted'       => $slotEnd->format('H:i'),
                'status'              => $status,
                'pending_count'       => $pendingCount,
                'is_bookable'         => $status !== 'confirmed' && !$conflictsWithPending,
                'is_already_selected' => $isAlreadySelected,
            ];

            $current->addMinutes($service->duration + $service->buffer_time);
        }

        return $slots;
    }

    public function render()
    {
        $selectedService = $this->selectedServiceId ? Service::find($this->selectedServiceId) : null;

        return view('livewire.booking.wizard', [
            'services'        => $this->step === 1 ? Service::where('is_active', true)->get() : collect(),
            'staffMembers'    => $this->step === 2 ? User::role('Staff')->where('is_active', true)->whereHas('services', function ($q) {
                $q->where('services.id', $this->selectedServiceId);
            })->get() : collect(),
            'selectedService' => $selectedService,
            'selectedStaff'   => $this->selectedStaffId ? User::find($this->selectedStaffId) : null,
        ]);
    }
}
