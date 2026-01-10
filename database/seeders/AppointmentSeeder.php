<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Get the single staff member (Inayata) created in RolesAndPermissionsSeeder
        $staff = User::where('email', 'hello@ampletherapy.org.uk')->first();

        if (!$staff) {
            // Fallback if not found (should be there though)
            return;
        }

        // 2. Get the services assigned to her (created in ServiceSeeder)
        // We assume she has the 4 services assigned
        $services = $staff->services;

        if ($services->isEmpty()) {
            return;
        }

        // 3. Create sample customers
        $customerData = [
            ['name' => 'John Smith', 'email' => 'john@example.com', 'phone' => '+1 555-0201'],
            ['name' => 'Jane Doe', 'email' => 'jane@example.com', 'phone' => '+1 555-0202'],
            ['name' => 'Bob Wilson', 'email' => 'bob@example.com', 'phone' => '+1 555-0203'],
        ];

        foreach ($customerData as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'password' => Hash::make('password'),
                    'is_active' => true,
                ]
            );
            $user->assignRole('Customer');
        }

        $customers = User::role('Customer')->get();

        // 4. Create Appointments
        foreach ($customers as $customer) {
            // Create 5-8 appointments for each customer
            $numAppointments = rand(5, 8);

            for ($i = 0; $i < $numAppointments; $i++) {
                $service = $services->random();

                // Mix of past and future appointments
                $daysOffset = rand(-30, 20);

                // Determine status based on date
                if ($daysOffset < 0) {
                    $status = rand(0, 10) > 8 ? 'cancelled' : 'completed';
                } else {
                    $status = rand(0, 10) > 7 ? 'booked' : 'confirmed';
                }

                // Working hours 9-5
                $startsAt = now()->addDays($daysOffset)->setTime(rand(9, 15), 0);
                $endsAt = $startsAt->copy()->addMinutes($service->duration);

                // Skip if conflict (simple check)
                if (Appointment::hasConflict($staff->id, $startsAt, $endsAt)) {
                    continue;
                }

                Appointment::create([
                    'service_id' => $service->id,
                    'user_id' => $staff->id, // Always the single staff member
                    'customer_name' => $customer->name,
                    'customer_email' => $customer->email,
                    'customer_phone' => $customer->phone,
                    'starts_at' => $startsAt,
                    'ends_at' => $endsAt,
                    'status' => $status,
                    'notes' => rand(0, 1) ? fake()->sentence() : null,
                ]);
            }
        }
    }
}
