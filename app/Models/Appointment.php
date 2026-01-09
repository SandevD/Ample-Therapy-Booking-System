<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'user_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'starts_at',
        'ends_at',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    /**
     * Get the service for this appointment.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get the staff member (user) for this appointment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Alias for staff relationship (for backward compatibility).
     */
    public function staff(): BelongsTo
    {
        return $this->user();
    }

    /**
     * Scope to get appointments for today.
     */
    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('starts_at', Carbon::today());
    }

    /**
     * Scope to get appointments for this week.
     */
    public function scopeThisWeek(Builder $query): Builder
    {
        return $query->whereBetween('starts_at', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek(),
        ]);
    }

    /**
     * Scope to get upcoming appointments.
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('starts_at', '>=', Carbon::now())
            ->where('status', '!=', 'cancelled')
            ->orderBy('starts_at');
    }

    /**
     * Scope by status.
     */
    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Check if appointment conflicts with existing appointments for the same staff.
     * Includes buffer time from the service.
     */
    public static function hasConflict(
        int $userId,
        Carbon $startsAt,
        Carbon $endsAt,
        ?int $excludeAppointmentId = null
    ): bool {
        $query = static::where('user_id', $userId)
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) use ($startsAt, $endsAt) {
                $q->whereBetween('starts_at', [$startsAt, $endsAt])
                    ->orWhereBetween('ends_at', [$startsAt, $endsAt])
                    ->orWhere(function ($q2) use ($startsAt, $endsAt) {
                        $q2->where('starts_at', '<=', $startsAt)
                            ->where('ends_at', '>=', $endsAt);
                    });
            });

        if ($excludeAppointmentId) {
            $query->where('id', '!=', $excludeAppointmentId);
        }

        return $query->exists();
    }

    /**
     * Get duration in minutes.
     */
    public function getDurationAttribute(): int
    {
        return $this->starts_at->diffInMinutes($this->ends_at);
    }

    /**
     * Get status badge color for UI.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'booked' => 'warning',
            'confirmed' => 'primary',
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'zinc',
        };
    }
}

