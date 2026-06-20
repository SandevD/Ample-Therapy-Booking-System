<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Service extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'description',
        'duration',
        'price',
        'buffer_time',
        'session_count',
        'is_active',
        'color',
    ];

    protected function casts(): array
    {
        return [
            'duration' => 'integer',
            'price' => 'decimal:2',
            'buffer_time' => 'integer',
            'session_count' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('service')
            ->logFillable()
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (string $event) => "Service {$event}");
    }

    /**
     * Get the users (staff) who can provide this service.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * Alias for staff (backward compatibility).
     */
    public function staff(): BelongsToMany
    {
        return $this->users();
    }

    /**
     * Get the availabilities for this service.
     */
    public function availabilities(): HasMany
    {
        return $this->hasMany(Availability::class);
    }

    /**
     * Get the appointments for this service.
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Scope to get only active services.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get total duration including buffer time.
     */
    public function getTotalDurationAttribute(): int
    {
        return $this->duration + $this->buffer_time;
    }
}
