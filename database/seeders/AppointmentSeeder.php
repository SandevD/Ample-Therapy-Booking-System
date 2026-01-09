<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Availability;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create services
        $services = [
            ['name' => 'Haircut', 'description' => 'Professional haircut styling', 'duration' => 30, 'price' => 35.00, 'buffer_time' => 10, 'color' => '#8B5CF6'],
            ['name' => 'Hair Coloring', 'description' => 'Full hair coloring service', 'duration' => 90, 'price' => 120.00, 'buffer_time' => 15, 'color' => '#EC4899'],
            ['name' => 'Manicure', 'description' => 'Professional nail care', 'duration' => 45, 'price' => 40.00, 'buffer_time' => 10, 'color' => '#F43F5E'],
            ['name' => 'Massage', 'description' => 'Relaxing full body massage', 'duration' => 60, 'price' => 80.00, 'buffer_time' => 15, 'color' => '#10B981'],
            ['name' => 'Facial', 'description' => 'Deep cleansing facial treatment', 'duration' => 45, 'price' => 65.00, 'buffer_time' => 10, 'color' => '#3B82F6'],
        ];

        foreach ($services as $serviceData) {
            Service::create(array_merge($serviceData, ['is_active' => true]));
        }

        $allServices = Service::all();

        // Create staff members (users with Staff role)
        $staffData = [
            ['name' => 'Sarah Johnson', 'email' => 'sarah@example.com', 'phone' => '+1 555-0101', 'bio' => 'Senior stylist with 10 years experience'],
            ['name' => 'Michael Chen', 'email' => 'michael@example.com', 'phone' => '+1 555-0102', 'bio' => 'Massage therapy specialist'],
            ['name' => 'Emily Davis', 'email' => 'emily@example.com', 'phone' => '+1 555-0103', 'bio' => 'Nail art expert'],
        ];

        foreach ($staffData as $data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'bio' => $data['bio'],
                'password' => Hash::make('password'),
                'is_active' => true,
            ]);
            $user->assignRole('Staff');

            // Assign 2-3 random services to each staff member
            $user->services()->attach($allServices->random(rand(2, 3))->pluck('id'));

            // Create availability for each assigned service
            foreach ($user->services as $service) {
                // Monday to Friday, 9 AM to 5 PM
                foreach (range(1, 5) as $day) {
                    Availability::create([
                        'user_id' => $user->id,
                        'service_id' => $service->id,
                        'day_of_week' => $day,
                        'start_time' => '09:00',
                        'end_time' => '17:00',
                        'is_recurring' => true,
                    ]);
                }
            }
        }

        // Create sample customers
        $customerData = [
            ['name' => 'John Smith', 'email' => 'john@example.com', 'phone' => '+1 555-0201'],
            ['name' => 'Jane Doe', 'email' => 'jane@example.com', 'phone' => '+1 555-0202'],
            ['name' => 'Bob Wilson', 'email' => 'bob@example.com', 'phone' => '+1 555-0203'],
        ];

        foreach ($customerData as $data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => Hash::make('password'),
                'is_active' => true,
            ]);
            $user->assignRole('Customer');
        }

        // Create sample appointments
        $staffMembers = User::role('Staff')->with('services')->get()->filter(fn($u) => $u->services->count() > 0);
        $statuses = ['booked', 'confirmed', 'completed'];

        if ($staffMembers->isEmpty()) {
            return; // No staff with services, skip appointments
        }

        for ($i = 0; $i < 15; $i++) {
            $staff = $staffMembers->random();
            $service = $staff->services->random();
            $daysAhead = rand(-7, 14);
            $hour = rand(9, 15);
            $startsAt = now()->addDays($daysAhead)->setTime($hour, 0);

            // Skip if conflict
            if (Appointment::hasConflict($staff->id, $startsAt, $startsAt->copy()->addMinutes($service->duration))) {
                continue;
            }

            Appointment::create([
                'service_id' => $service->id,
                'user_id' => $staff->id,
                'customer_name' => fake()->name(),
                'customer_email' => fake()->safeEmail(),
                'customer_phone' => fake()->phoneNumber(),
                'starts_at' => $startsAt,
                'ends_at' => $startsAt->copy()->addMinutes($service->duration),
                'status' => $daysAhead < 0 ? 'completed' : $statuses[array_rand($statuses)],
                'notes' => rand(0, 1) ? fake()->sentence() : null,
            ]);
        }
    }
}
