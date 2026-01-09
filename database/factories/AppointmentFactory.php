<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Appointment>
 */
class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsAt = Carbon::now()
            ->addDays(fake()->numberBetween(0, 14))
            ->setHour(fake()->numberBetween(9, 17))
            ->setMinute(fake()->randomElement([0, 15, 30, 45]))
            ->setSecond(0);

        $duration = fake()->randomElement([30, 45, 60, 90]);

        return [
            'service_id' => Service::factory(),
            'staff_id' => Staff::factory(),
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'customer_phone' => fake()->phoneNumber(),
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->copy()->addMinutes($duration),
            'status' => fake()->randomElement(['booked', 'confirmed', 'completed']),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Mark appointment as cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'cancelled',
        ]);
    }

    /**
     * Mark appointment as completed.
     */
    public function completed(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'completed',
        ]);
    }

    /**
     * Set appointment for today.
     */
    public function today(): static
    {
        return $this->state(function (array $attributes) {
            $startsAt = Carbon::today()
                ->setHour(fake()->numberBetween(9, 17))
                ->setMinute(fake()->randomElement([0, 15, 30, 45]));

            return [
                'starts_at' => $startsAt,
                'ends_at' => $startsAt->copy()->addMinutes(60),
            ];
        });
    }
}
