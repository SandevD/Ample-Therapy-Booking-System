<?php

namespace Tests\Feature;

use App\Livewire\Booking\Wizard;
use App\Models\Appointment;
use App\Models\Availability;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BookingWizardTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(array $attrs = []): User
    {
        /** @var User $user */
        $user = User::factory()->create($attrs);
        return $user;
    }

    private function makeStaff(Service $service, string $name = 'Staff Member'): User
    {
        Role::firstOrCreate(['name' => 'Staff']);
        $staff = $this->makeUser(['name' => $name]);
        $staff->assignRole('Staff');
        $staff->services()->attach($service->id);
        return $staff;
    }

    private function makeMultiSessionService(int $sessions = 3, int $duration = 60): Service
    {
        return Service::create([
            'name' => 'Therapy Package',
            'duration' => $duration,
            'price' => 200,
            'color' => '#ff00ff',
            'is_active' => true,
            'session_count' => $sessions,
            'buffer_time' => 0,
        ]);
    }

    private function giveFullWeekAvailability(Service $service, User $staff): void
    {
        foreach (range(0, 6) as $day) {
            Availability::create([
                'user_id' => $staff->id,
                'service_id' => $service->id,
                'day_of_week' => $day,
                'start_time' => '09:00',
                'end_time' => '17:00',
                'is_recurring' => true,
            ]);
        }
    }

    public function test_booking_wizard_can_render(): void
    {
        $this->actingAs($this->makeUser());

        Livewire::test(Wizard::class)->assertStatus(200);
    }

    public function test_single_session_flow_creates_appointment(): void
    {
        $service = Service::create([
            'name' => 'Consult',
            'duration' => 60,
            'price' => 100,
            'color' => '#ff00ff',
            'is_active' => true,
            'buffer_time' => 0,
        ]);
        $staff = $this->makeStaff($service);
        $customer = $this->makeUser();
        $this->actingAs($customer);

        Livewire::test(Wizard::class)
            ->call('selectService', $service->id)
            ->assertSet('step', 2)
            ->call('selectStaff', $staff->id)
            ->assertSet('step', 3)
            ->set('selectedDate', '2026-06-01')
            ->call('selectDateTime', '2026-06-01', '10:00')
            ->assertSet('step', 4)
            ->call('submit')
            ->assertRedirect(route('admin.appointments'));

        $this->assertDatabaseHas('appointments', [
            'service_id' => $service->id,
            'user_id' => $staff->id,
            'customer_email' => $customer->email,
            'starts_at' => '2026-06-01 10:00:00',
            'booking_group_id' => null,
        ]);
    }

    public function test_multi_session_does_not_auto_advance_when_slots_picked_manually(): void
    {
        $service = $this->makeMultiSessionService(sessions: 3);
        $staff = $this->makeStaff($service);
        $this->actingAs($this->makeUser());

        $cmp = Livewire::test(Wizard::class)
            ->call('selectService', $service->id)
            ->call('selectStaff', $staff->id)
            ->call('selectDateTime', '2026-06-01', '10:00')
            ->call('selectDateTime', '2026-06-02', '10:00')
            ->call('selectDateTime', '2026-06-03', '10:00');

        $cmp->assertSet('step', 3);
        $this->assertCount(3, $cmp->get('selectedSlots'));
    }

    public function test_proceed_to_confirm_advances_only_when_target_reached(): void
    {
        $service = $this->makeMultiSessionService(sessions: 2);
        $staff = $this->makeStaff($service);
        $this->actingAs($this->makeUser());

        // Not enough slots — proceed should no-op.
        $cmp = Livewire::test(Wizard::class)
            ->call('selectService', $service->id)
            ->call('selectStaff', $staff->id)
            ->call('selectDateTime', '2026-06-01', '10:00')
            ->call('proceedToConfirm')
            ->assertSet('step', 3);

        // Complete required slots — proceed now advances.
        $cmp->call('selectDateTime', '2026-06-02', '10:00')
            ->call('proceedToConfirm')
            ->assertSet('step', 4);
    }

    public function test_selected_slots_sort_chronologically_regardless_of_pick_order(): void
    {
        $service = $this->makeMultiSessionService(sessions: 3);
        $staff = $this->makeStaff($service);
        $this->actingAs($this->makeUser());

        $cmp = Livewire::test(Wizard::class)
            ->call('selectService', $service->id)
            ->call('selectStaff', $staff->id)
            // Deliberately out-of-order picks
            ->call('selectDateTime', '2026-06-10', '14:00')
            ->call('selectDateTime', '2026-06-01', '09:00')
            ->call('selectDateTime', '2026-06-05', '11:00');

        $slots = $cmp->get('selectedSlots');
        $this->assertSame('2026-06-01', $slots[0]['date']);
        $this->assertSame('2026-06-05', $slots[1]['date']);
        $this->assertSame('2026-06-10', $slots[2]['date']);
    }

    public function test_auto_fill_weekly_fills_remaining_slots_at_same_time(): void
    {
        $service = $this->makeMultiSessionService(sessions: 5);
        $staff = $this->makeStaff($service);
        $this->giveFullWeekAvailability($service, $staff);
        $this->actingAs($this->makeUser());

        $cmp = Livewire::test(Wizard::class)
            ->call('selectService', $service->id)
            ->call('selectStaff', $staff->id)
            ->call('selectDateTime', '2026-06-01', '10:00')
            ->set('autoFillInterval', 'weekly')
            ->call('autoFillRemaining');

        $slots = $cmp->get('selectedSlots');
        $this->assertCount(5, $slots);
        $this->assertSame('2026-06-01', $slots[0]['date']);
        $this->assertSame('2026-06-08', $slots[1]['date']);
        $this->assertSame('2026-06-15', $slots[2]['date']);
        $this->assertSame('2026-06-22', $slots[3]['date']);
        $this->assertSame('2026-06-29', $slots[4]['date']);
        // All at anchor's time
        foreach ($slots as $s) {
            $this->assertSame('10:00', $s['time']);
        }
        // No forced step advance
        $cmp->assertSet('step', 3);
    }

    public function test_auto_fill_biweekly_interval(): void
    {
        $service = $this->makeMultiSessionService(sessions: 3);
        $staff = $this->makeStaff($service);
        $this->giveFullWeekAvailability($service, $staff);
        $this->actingAs($this->makeUser());

        $cmp = Livewire::test(Wizard::class)
            ->call('selectService', $service->id)
            ->call('selectStaff', $staff->id)
            ->call('selectDateTime', '2026-06-01', '09:00')
            ->set('autoFillInterval', 'biweekly')
            ->call('autoFillRemaining');

        $slots = $cmp->get('selectedSlots');
        $this->assertSame('2026-06-01', $slots[0]['date']);
        $this->assertSame('2026-06-15', $slots[1]['date']);
        $this->assertSame('2026-06-29', $slots[2]['date']);
    }

    public function test_auto_fill_skips_confirmed_conflict_and_records_in_skipped_list(): void
    {
        $service = $this->makeMultiSessionService(sessions: 3);
        $staff = $this->makeStaff($service);
        $this->giveFullWeekAvailability($service, $staff);
        $this->actingAs($this->makeUser());

        // Existing CONFIRMED appointment on 2026-06-08 10:00 — auto-fill should skip this date.
        Appointment::create([
            'service_id' => $service->id,
            'user_id' => $staff->id,
            'customer_name' => 'Existing',
            'customer_email' => 'existing@example.com',
            'starts_at' => Carbon::parse('2026-06-08 10:00:00'),
            'ends_at' => Carbon::parse('2026-06-08 11:00:00'),
            'status' => 'confirmed',
        ]);

        $cmp = Livewire::test(Wizard::class)
            ->call('selectService', $service->id)
            ->call('selectStaff', $staff->id)
            ->call('selectDateTime', '2026-06-01', '10:00')
            ->set('autoFillInterval', 'weekly')
            ->call('autoFillRemaining');

        $slots = collect($cmp->get('selectedSlots'))->pluck('date')->all();
        $this->assertContains('2026-06-01', $slots);
        $this->assertContains('2026-06-15', $slots);
        $this->assertNotContains('2026-06-08', $slots);
        $this->assertContains('2026-06-08', $cmp->get('autoFillSkipped'));
    }

    public function test_auto_fill_skips_pending_booked_conflict(): void
    {
        $service = $this->makeMultiSessionService(sessions: 2);
        $staff = $this->makeStaff($service);
        $this->giveFullWeekAvailability($service, $staff);
        $this->actingAs($this->makeUser());

        Appointment::create([
            'service_id' => $service->id,
            'user_id' => $staff->id,
            'customer_name' => 'Pending Customer',
            'customer_email' => 'pending@example.com',
            'starts_at' => Carbon::parse('2026-06-08 10:00:00'),
            'ends_at' => Carbon::parse('2026-06-08 11:00:00'),
            'status' => 'booked',
        ]);

        $cmp = Livewire::test(Wizard::class)
            ->call('selectService', $service->id)
            ->call('selectStaff', $staff->id)
            ->call('selectDateTime', '2026-06-01', '10:00')
            ->set('autoFillInterval', 'weekly')
            ->call('autoFillRemaining');

        $slots = collect($cmp->get('selectedSlots'))->pluck('date')->all();
        $this->assertNotContains('2026-06-08', $slots);
        $this->assertContains('2026-06-08', $cmp->get('autoFillSkipped'));
    }

    public function test_remove_slot_clears_autofill_skipped_warning(): void
    {
        $service = $this->makeMultiSessionService(sessions: 3);
        $staff = $this->makeStaff($service);
        $this->actingAs($this->makeUser());

        $cmp = Livewire::test(Wizard::class)
            ->call('selectService', $service->id)
            ->call('selectStaff', $staff->id)
            ->call('selectDateTime', '2026-06-01', '10:00')
            ->set('autoFillSkipped', ['2026-06-08'])
            ->call('removeSlot', 0);

        $this->assertSame([], $cmp->get('autoFillSkipped'));
    }

    private function makeSingleSessionService(int $duration = 60): Service
    {
        return Service::create([
            'name' => 'Consult',
            'duration' => $duration,
            'price' => 100,
            'color' => '#ff00ff',
            'is_active' => true,
            'session_count' => 1,
            'buffer_time' => 0,
        ]);
    }

    public function test_time_slots_use_coach_availability_window(): void
    {
        $service = $this->makeSingleSessionService(duration: 60);
        $staff = $this->makeStaff($service);
        $this->actingAs($this->makeUser());

        // Coach is available 13:00-15:00 on the booking date's weekday.
        $date = '2026-06-01';
        Availability::create([
            'user_id' => $staff->id,
            'service_id' => $service->id,
            'day_of_week' => Carbon::parse($date)->dayOfWeek,
            'start_time' => '13:00',
            'end_time' => '15:00',
            'is_recurring' => true,
        ]);

        $cmp = Livewire::test(Wizard::class)
            ->call('selectService', $service->id)
            ->call('selectStaff', $staff->id)
            ->set('selectedDate', $date);

        $times = collect($cmp->instance()->timeSlots)->pluck('time')->all();

        // Only slots within the coach's window — not the old hardcoded 09:00-17:00.
        $this->assertSame(['13:00', '14:00'], $times);
    }

    public function test_no_time_slots_when_coach_has_no_availability_that_day(): void
    {
        $service = $this->makeSingleSessionService(duration: 60);
        $staff = $this->makeStaff($service);
        $this->actingAs($this->makeUser());

        // Availability exists only for a DIFFERENT weekday than the booking date.
        $date = '2026-06-01';
        Availability::create([
            'user_id' => $staff->id,
            'service_id' => $service->id,
            'day_of_week' => Carbon::parse($date)->addDay()->dayOfWeek,
            'start_time' => '09:00',
            'end_time' => '17:00',
            'is_recurring' => true,
        ]);

        $cmp = Livewire::test(Wizard::class)
            ->call('selectService', $service->id)
            ->call('selectStaff', $staff->id)
            ->set('selectedDate', $date);

        $this->assertSame([], $cmp->instance()->timeSlots);
    }

    public function test_multi_session_submit_creates_appointments_with_same_booking_group_id(): void
    {
        $service = $this->makeMultiSessionService(sessions: 2);
        $staff = $this->makeStaff($service);
        $customer = $this->makeUser();
        $this->actingAs($customer);

        Livewire::test(Wizard::class)
            ->call('selectService', $service->id)
            ->call('selectStaff', $staff->id)
            ->call('selectDateTime', '2026-06-01', '10:00')
            ->call('selectDateTime', '2026-06-08', '10:00')
            ->call('proceedToConfirm')
            ->assertSet('step', 4)
            ->call('submit')
            ->assertRedirect(route('admin.appointments'));

        $appts = Appointment::where('customer_email', $customer->email)->get();
        $this->assertCount(2, $appts);
        $this->assertNotNull($appts->first()->booking_group_id);
        $this->assertSame(
            $appts->first()->booking_group_id,
            $appts->last()->booking_group_id,
            'All sessions in a multi-session booking must share the same booking_group_id.'
        );
    }
}
