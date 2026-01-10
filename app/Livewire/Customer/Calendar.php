<?php

namespace App\Livewire\Customer;

use App\Models\Appointment;
use Carbon\Carbon;
use Livewire\Component;

class Calendar extends Component
{
    public $currentMonth;
    public $currentYear;
    public $selectedDate;
    public $showingEvents = false;
    public $dayEvents = [];
    public $layout = 'full'; // 'full' or 'widget'

    public function mount($layout = 'full')
    {
        $this->layout = $layout;
        $this->currentMonth = Carbon::now()->month;
        $this->currentYear = Carbon::now()->year;
    }

    public function previousMonth()
    {
        $date = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->subMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
    }

    public function nextMonth()
    {
        $date = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->addMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
    }

    public function selectDate($date)
    {
        $this->selectedDate = Carbon::parse($date);

        // Fetch appointments for this specific date
        $this->dayEvents = Appointment::where('customer_email', auth()->user()->email)
            ->whereDate('starts_at', $this->selectedDate)
            ->with(['service', 'user'])
            ->orderBy('starts_at')
            ->get();

        $this->showingEvents = true;
    }

    public function render()
    {
        $startOfMonth = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1);
        $daysInMonth = $startOfMonth->daysInMonth;
        $firstDayOfWeek = $startOfMonth->dayOfWeek; // 0 (Sunday) to 6 (Saturday)

        // Adjust for Monday start if needed (Flux usually likes Sunday start or configurable)
        // Let's stick to standard Sunday start for grid simplicity 

        // Fetch appointments for the entire month for indicators
        $monthlyAppointments = Appointment::where('customer_email', auth()->user()->email)
            ->whereYear('starts_at', $this->currentYear)
            ->whereMonth('starts_at', $this->currentMonth)
            ->get()
            ->groupBy(function ($date) {
                return Carbon::parse($date->starts_at)->format('Y-m-d');
            });

        $view = $this->layout === 'widget'
            ? 'livewire.customer.calendar-widget'
            : 'livewire.customer.calendar';

        return view($view, [
            'daysInMonth' => $daysInMonth,
            'firstDayOfWeek' => $firstDayOfWeek,
            'monthName' => $startOfMonth->format('F'),
            'monthlyAppointments' => $monthlyAppointments,
        ]);
    }
}
