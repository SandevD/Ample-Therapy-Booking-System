<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Service;
use App\Livewire\Booking\Wizard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class BookingWizardTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_wizard_can_render()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(Wizard::class)
            ->assertStatus(200);
    }

    public function test_booking_wizard_flow()
    {
        // Setup
        $service = Service::create([
            'name' => 'Test Service',
            'duration' => 60,
            'price' => 100,
            'color' => '#ff00ff',
            'is_active' => true
        ]);

        $staff = User::factory()->create(['name' => 'Staff Member']);
        $role = Role::firstOrCreate(['name' => 'staff']);
        $staff->assignRole($role);
        $staff->services()->attach($service->id);

        $customer = User::factory()->create();
        $this->actingAs($customer);

        Livewire::test(Wizard::class)
            // Step 1: Services
            ->assertSee('Select a Service')
            ->assertSee('Test Service')
            ->call('selectService', $service->id)

            // Step 2: Staff
            ->assertSet('step', 2)
            ->assertSee('Select Staff')
            ->assertSee('Staff Member')
            ->call('selectStaff', $staff->id)

            // Step 3: Time
            ->assertSet('step', 3)
            ->assertSee('Select Time')
            ->set('selectedDate', '2023-01-01')
            ->call('selectDateTime', '2023-01-01', '10:00')

            // Step 4: Confirm
            ->assertSet('step', 4)
            ->assertSee('Confirm Booking')
            ->assertSee('Test Service')
            ->assertSee('Staff Member')
            ->call('submit')
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('appointments', [
            'service_id' => $service->id,
            'user_id' => $staff->id,
            'customer_email' => $customer->email,
            'starts_at' => '2023-01-01 10:00:00',
        ]);
    }
}
