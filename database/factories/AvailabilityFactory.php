<?php

namespace Database\Factories;

use App\Models\Availability;
use App\Models\Service;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Availability>
 */
class AvailabilityFactory extends Factory
{
    protected $model = Availability::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startHour = fake()->numberBetween(8, 12);
        $endHour = $startHour + fake()->numberBetween(4, 8);

        return [
            'staff_id' => Staff::factory(),
            'service_id' => Service::factory(),
            'day_of_week' => fake()->numberBetween(0, 6),
            'start_time' => sprintf('%02d:00:00', $startHour),
            'end_time' => sprintf('%02d:00:00', min($endHour, 20)),
            'is_recurring' => true,
            'specific_date' => null,
        ];
    }

    /**
     * Make availability non-recurring for a specific date.
     */
    public function forDate(\DateTime $date): static
    {
        return $this->state(fn(array $attributes) => [
            'is_recurring' => false,
            'specific_date' => $date,
            'day_of_week' => (int) $date->format('w'),
        ]);
    }
}
