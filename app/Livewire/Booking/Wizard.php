<?php

namespace App\Livewire\Booking;

use App\Models\Service;
use App\Models\User;
use App\Models\Appointment;
use App\Models\Availability;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
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

    // Auto-fill (recurring) — used when booking multi-session services
    public string $autoFillInterval = 'weekly';
    public array $autoFillSkipped = [];

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
        $this->sortSelectedSlots();
        $this->selectedDate = $date;
        $this->selectedTime = $time;
        $this->autoFillSkipped = [];
    }

    public function removeSlot(int $index): void
    {
        unset($this->selectedSlots[$index]);
        $this->selectedSlots = array_values($this->selectedSlots);
        $this->sortSelectedSlots();
        $this->selectedTime = null;
        $this->autoFillSkipped = [];
    }

    public function proceedToConfirm(): void
    {
        $service = Service::find($this->selectedServiceId);
        if ($service && count($this->selectedSlots) >= $service->session_count) {
            $this->nextStep();
        }
    }

    private function sortSelectedSlots(): void
    {
        $this->selectedSlots = collect($this->selectedSlots)
            ->sortBy(fn($s) => $s['date'] . ' ' . $s['time'])
            ->values()
            ->all();
    }

    public function autoFillRemaining(): void
    {
        $this->autoFillSkipped = [];
        $service = Service::find($this->selectedServiceId);

        if (!$service || empty($this->selectedSlots)) {
            return;
        }

        $remaining = $service->session_count - count($this->selectedSlots);
        if ($remaining <= 0) {
            return;
        }

        $sorted = collect($this->selectedSlots)
            ->sortBy(fn($s) => $s['date'] . ' ' . $s['time'])
            ->values();
        $anchor = $sorted->last();

        $days = $this->intervalDays($this->autoFillInterval);
        $cursor = Carbon::parse($anchor['date']);
        $time = $anchor['time'];

        for ($i = 0; $i < $remaining; $i++) {
            $cursor->addDays($days);
            $candidateDate = $cursor->format('Y-m-d');

            $duplicate = collect($this->selectedSlots)->contains(
                fn($s) => $s['date'] === $candidateDate && $s['time'] === $time
            );

            if ($duplicate || !$this->isSlotAvailable($candidateDate, $time, $service)) {
                $this->autoFillSkipped[] = $candidateDate;
                continue;
            }

            $this->selectedSlots[] = ['date' => $candidateDate, 'time' => $time];
        }

        $this->sortSelectedSlots();
        $this->selectedDate = $cursor->format('Y-m-d');
    }

    private function intervalDays(string $key): int
    {
        return match ($key) {
            'biweekly' => 14,
            'every_2d' => 2,
            'every_3d' => 3,
            default    => 7,
        };
    }

    /**
     * The coach's recurring availability for the selected staff + service on the
     * weekday of $date, or null if the coach has no availability that day.
     */
    private function availabilityFor(string $date): ?Availability
    {
        return Availability::query()
            ->where('user_id', $this->selectedStaffId)
            ->where('service_id', $this->selectedServiceId)
            ->where('day_of_week', Carbon::parse($date)->dayOfWeek)
            ->where('is_recurring', true)
            ->first();
    }

    /**
     * Whether [time, time+duration] on $date fits inside the coach's availability window.
     */
    private function slotWithinAvailability(string $date, string $time, Service $service): bool
    {
        $availability = $this->availabilityFor($date);
        if (!$availability) {
            return false;
        }

        $startsAt = Carbon::parse($date . ' ' . $time);
        $endsAt   = $startsAt->copy()->addMinutes($service->duration);
        $windowStart = Carbon::parse($date . ' ' . $availability->start_time->format('H:i'));
        $windowEnd   = Carbon::parse($date . ' ' . $availability->end_time->format('H:i'));

        return $startsAt->gte($windowStart) && $endsAt->lte($windowEnd);
    }

    /**
     * Server-side booking guard. Mirrors the UI's "bookable" rule: the slot must be
     * inside the coach's availability and must not collide with a CONFIRMED appointment
     * (pending/booked overlaps are allowed — staff confirm one later).
     */
    private function ensureSlotBookable(string $date, ?string $time, Service $service, string $field): void
    {
        $bookable = $time
            && $this->slotWithinAvailability($date, $time, $service)
            && !$this->hasConfirmedConflict($date, $time, $service);

        if (!$bookable) {
            throw ValidationException::withMessages([
                $field => 'That time is outside the coach\'s availability or no longer available. Please pick another slot.',
            ]);
        }
    }

    private function hasConfirmedConflict(string $date, string $time, Service $service): bool
    {
        $startsAt = Carbon::parse($date . ' ' . $time);
        $endsAt   = $startsAt->copy()->addMinutes($service->duration);

        return Appointment::where('user_id', $this->selectedStaffId)
            ->where('status', 'confirmed')
            ->where('starts_at', '<', $endsAt)
            ->where('ends_at', '>', $startsAt)
            ->exists();
    }

    private function isSlotAvailable(string $date, string $time, Service $service): bool
    {
        $startsAt = Carbon::parse($date . ' ' . $time);
        $endsAt   = $startsAt->copy()->addMinutes($service->duration);

        // Honour the coach's saved availability window; no row => not available that day.
        $availability = $this->availabilityFor($date);
        if (!$availability) {
            return false;
        }

        $businessStart = Carbon::parse($date . ' ' . $availability->start_time->format('H:i'));
        $businessEnd   = Carbon::parse($date . ' ' . $availability->end_time->format('H:i'));
        if ($startsAt->lt($businessStart) || $endsAt->gt($businessEnd)) {
            return false;
        }

        // Auto-fill avoids ANY active appointment (confirmed or pending/booked) to reduce collisions.
        $dbConflict = Appointment::where('user_id', $this->selectedStaffId)
            ->whereDate('starts_at', $date)
            ->whereIn('status', ['confirmed', 'booked'])
            ->where(function ($q) use ($startsAt, $endsAt) {
                $q->where('starts_at', '<', $endsAt)
                  ->where('ends_at', '>', $startsAt);
            })
            ->exists();
        if ($dbConflict) {
            return false;
        }

        $pendingConflict = collect($this->selectedSlots)->contains(function ($s) use ($startsAt, $endsAt, $service) {
            $ps = Carbon::parse($s['date'] . ' ' . $s['time']);
            $pe = $ps->copy()->addMinutes($service->duration);
            return $startsAt->lt($pe) && $endsAt->gt($ps);
        });

        return !$pendingConflict;
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

            // Server-side guard: never trust the client. The slot must fall inside the
            // coach's saved availability and not collide with a confirmed booking.
            $this->ensureSlotBookable($this->selectedDate, $this->selectedTime, $service, 'selectedTime');

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

            // Guard every selected slot before creating any of them.
            foreach ($this->selectedSlots as $slot) {
                $this->ensureSlotBookable($slot['date'], $slot['time'], $service, 'selectedSlots');
            }

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

        // The coach's saved availability for this staff/service on this weekday is the
        // single source of truth for bookable hours. No availability row => coach is
        // closed that day, so there are no slots.
        $availability = $this->availabilityFor($this->selectedDate);
        if (!$availability) {
            return [];
        }

        $service  = Service::find($this->selectedServiceId);
        $slots    = [];
        $current  = Carbon::parse($this->selectedDate . ' ' . $availability->start_time->format('H:i'));
        $endOfDay = Carbon::parse($this->selectedDate . ' ' . $availability->end_time->format('H:i'));

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
