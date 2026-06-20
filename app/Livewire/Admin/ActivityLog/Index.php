<?php

namespace App\Livewire\Admin\ActivityLog;

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $logName = '';

    #[Url]
    public string $event = '';

    #[Url]
    public string $dateFilter = '';

    /** Friendly labels for the log_name (type) filter. */
    public const LOG_NAMES = [
        'appointment' => 'Appointments',
        'service' => 'Services',
        'user' => 'Users',
        'availability' => 'Availability',
        'auth' => 'Authentication',
    ];

    /** Friendly labels for the event filter. */
    public const EVENTS = [
        'created' => 'Created',
        'updated' => 'Updated',
        'deleted' => 'Deleted',
        'login' => 'Logged in',
        'logout' => 'Logged out',
        'failed_login' => 'Failed login',
        'password_reset' => 'Password reset',
        'password_changed' => 'Password changed',
    ];

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('view_activity_log'), 403);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedLogName(): void
    {
        $this->resetPage();
    }

    public function updatedEvent(): void
    {
        $this->resetPage();
    }

    public function updatedDateFilter(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'logName', 'event', 'dateFilter']);
        $this->resetPage();
    }

    public function render()
    {
        $activities = Activity::query()
            ->with('causer')
            ->when($this->logName, fn ($q) => $q->where('log_name', $this->logName))
            ->when($this->event, fn ($q) => $q->where('event', $this->event))
            ->when($this->dateFilter, fn ($q) => $q->whereDate('created_at', $this->dateFilter))
            ->when($this->search, function ($q) {
                $term = "%{$this->search}%";
                $q->where(function ($q2) use ($term) {
                    $q2->where('description', 'like', $term)
                        ->orWhereHasMorph('causer', [User::class], fn ($q3) => $q3->where('name', 'like', $term)->orWhere('email', 'like', $term));
                });
            })
            ->latest()
            ->paginate(20);

        return view('livewire.admin.activity-log.index', [
            'activities' => $activities,
            'logNames' => self::LOG_NAMES,
            'events' => self::EVENTS,
        ])->layout('components.layouts.app', ['title' => 'Activity Log']);
    }
}
