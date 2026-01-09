<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    protected $model = Service::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $services = [
            ['name' => 'Consultation', 'duration' => 30, 'price' => 50],
            ['name' => 'Full Session', 'duration' => 60, 'price' => 100],
            ['name' => 'Extended Session', 'duration' => 90, 'price' => 140],
            ['name' => 'Quick Check-in', 'duration' => 15, 'price' => 25],
        ];

        $service = fake()->randomElement($services);

        return [
            'name' => $service['name'],
            'description' => fake()->paragraph(),
            'duration' => $service['duration'],
            'price' => $service['price'],
            'buffer_time' => fake()->randomElement([10, 15, 20, 30]),
            'is_active' => true,
            'color' => fake()->hexColor(),
        ];
    }

    /**
     * Indicate that the service is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }
}
