<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            [
                'name' => 'Consultation Call with AMPLE Therapy (FREE)',
                'description' => 'Take the first step towards a balanced and fulfilling life. Explore how our integrated approach can help you thrive. Book a free consultation call to discuss your needs.',
                'duration' => 30,
                'price' => 0.00,
                'buffer_time' => 30,
                'color' => '#F43F5E', // Rose/Red
                'is_active' => true,
            ],
            [
                'name' => 'Life Coaching with AMPLE Therapy (1 hr) - Online & In-person',
                'description' => 'Life can be overwhelming, but you don\'t have to navigate it alone. Take a moment for yourself. These sessions are here to support you—your goals, your growth, and your happiness.',
                'duration' => 60,
                'price' => 100.00, // Placeholder
                'buffer_time' => 30,
                'color' => '#D946EF', // Fuchsia
                'is_active' => true,
            ],
            [
                'name' => 'Rapid Transformational Therapy (1 hr) - Online & In-person',
                'description' => 'Let go of what\'s holding you back and rediscover your best self. In this uplifting one-hour session, we\'ll use gentle hypnotherapy to uncover and transform beliefs.',
                'duration' => 60,
                'price' => 150.00, // Placeholder
                'buffer_time' => 30,
                'color' => '#10B981', // Emerald
                'is_active' => true,
            ],
            [
                'name' => 'Rapid Transformational Therapy (2 hr) - Online & In-person',
                'description' => 'Let go of what\'s holding you back and rediscover your best self. In this uplifting two-hour session, we\'ll use gentle hypnotherapy to uncover and transform beliefs.',
                'duration' => 120,
                'price' => 250.00, // Placeholder
                'buffer_time' => 30,
                'color' => '#3B82F6', // Blue
                'is_active' => true,
            ],
        ];

        foreach ($services as $service) {
            Service::create($service);
        }

        // Assign all services to Inayata
        $staff = \App\Models\User::where('email', 'hello@ampletherapy.org.uk')->first();

        if ($staff) {
            $allServices = Service::all();
            $staff->services()->sync($allServices);

            // Create Availability (Mon-Fri, 09:00 - 17:00)
            foreach ($allServices as $service) {
                foreach (range(1, 5) as $day) {
                    \App\Models\Availability::firstOrCreate([
                        'user_id' => $staff->id,
                        'service_id' => $service->id,
                        'day_of_week' => $day,
                        'start_time' => '09:00',
                        'end_time' => '17:00',
                        'is_recurring' => true,
                    ]);
                }
            }
        }
    }
}
