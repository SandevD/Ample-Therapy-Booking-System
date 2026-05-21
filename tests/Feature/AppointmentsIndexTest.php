<?php

namespace Tests\Feature;

use App\Livewire\Admin\Appointments\Index as AppointmentsIndex;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AppointmentsIndexTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(array $attrs = []): User
    {
        /** @var User $user */
        $user = User::factory()->create($attrs);
        return $user;
    }

    private function actingAsSuperAdmin(): User
    {
        Role::firstOrCreate(['name' => 'Super Admin']);
        Role::firstOrCreate(['name' => 'Staff']);
        $admin = $this->makeUser();
        $admin->assignRole('Super Admin');
        $this->actingAs($admin);
        return $admin;
    }

    private function makeStaff(Service $service): User
    {
        Role::firstOrCreate(['name' => 'Staff']);
        $staff = $this->makeUser(['name' => 'Staff Member']);
        $staff->assignRole('Staff');
        $staff->services()->attach($service->id);
        return $staff;
    }

    private function makeService(int $sessions = 1): Service
    {
        return Service::create([
            'name' => 'Test Service',
            'duration' => 60,
            'price' => 100,
            'color' => '#00ff00',
            'is_active' => true,
            'session_count' => $sessions,
            'buffer_time' => 0,
        ]);
    }

    private function makeGroupedAppointments(Service $service, User $staff, array $starts, ?string $groupId = null): array
    {
        $groupId ??= Str::uuid()->toString();
        $appts = [];
        foreach ($starts as $i => $start) {
            $startsAt = Carbon::parse($start);
            $appts[] = Appointment::create([
                'service_id' => $service->id,
                'user_id' => $staff->id,
                'customer_name' => 'Group Customer',
                'customer_email' => 'group@example.com',
                'starts_at' => $startsAt,
                'ends_at' => $startsAt->copy()->addMinutes($service->duration),
                'status' => 'booked',
                'booking_group_id' => $groupId,
            ]);
        }
        return $appts;
    }

    public function test_date_filter_limits_grouped_sessions_to_matching_date(): void
    {
        $this->actingAsSuperAdmin();
        $service = $this->makeService(sessions: 5);
        $staff = $this->makeStaff($service);
        $this->makeGroupedAppointments($service, $staff, [
            '2026-06-01 10:00:00',
            '2026-06-08 10:00:00',
            '2026-06-15 10:00:00',
            '2026-06-22 10:00:00',
            '2026-06-29 10:00:00',
        ]);

        $cmp = Livewire::test(AppointmentsIndex::class)
            ->set('dateFilter', '2026-06-15');

        $groupedAppointments = $cmp->viewData('groupedAppointments');
        $this->assertCount(1, $groupedAppointments, 'Only one booking group should appear.');

        $visible = $groupedAppointments->first();
        $this->assertCount(1, $visible, 'Only the session matching the filtered date should render.');
        $this->assertSame('2026-06-15', $visible->first()->starts_at->format('Y-m-d'));
    }

    public function test_session_numbers_remain_stable_when_filter_hides_earlier_sessions(): void
    {
        $this->actingAsSuperAdmin();
        $service = $this->makeService(sessions: 5);
        $staff = $this->makeStaff($service);
        $appts = $this->makeGroupedAppointments($service, $staff, [
            '2026-06-01 10:00:00',
            '2026-06-08 10:00:00',
            '2026-06-15 10:00:00',
            '2026-06-22 10:00:00',
            '2026-06-29 10:00:00',
        ]);

        $cmp = Livewire::test(AppointmentsIndex::class)
            ->set('dateFilter', '2026-06-29');

        $sessionNumbers = $cmp->viewData('sessionNumbers');

        // Fifth chronological session (Jun 29) must still be labelled session 5 even when filtered alone.
        $lastAppt = $appts[4];
        $this->assertSame(5, $sessionNumbers[$lastAppt->id]);
    }

    public function test_group_totals_reflect_full_booking_count_regardless_of_filter(): void
    {
        $this->actingAsSuperAdmin();
        $service = $this->makeService(sessions: 5);
        $staff = $this->makeStaff($service);
        $groupId = Str::uuid()->toString();
        $this->makeGroupedAppointments($service, $staff, [
            '2026-06-01 10:00:00',
            '2026-06-08 10:00:00',
            '2026-06-15 10:00:00',
            '2026-06-22 10:00:00',
            '2026-06-29 10:00:00',
        ], $groupId);

        $cmp = Livewire::test(AppointmentsIndex::class)
            ->set('dateFilter', '2026-06-15');

        $groupTotals = $cmp->viewData('groupTotals');
        $this->assertSame(5, $groupTotals[$groupId]);
    }

    public function test_status_filter_applies_to_grouped_sessions(): void
    {
        $this->actingAsSuperAdmin();
        $service = $this->makeService(sessions: 3);
        $staff = $this->makeStaff($service);
        $groupId = Str::uuid()->toString();
        $appts = $this->makeGroupedAppointments($service, $staff, [
            '2026-07-01 10:00:00',
            '2026-07-08 10:00:00',
            '2026-07-15 10:00:00',
        ], $groupId);

        // Mark one as confirmed.
        $appts[1]->update(['status' => 'confirmed']);

        $cmp = Livewire::test(AppointmentsIndex::class)
            ->set('statusFilter', 'confirmed');

        $grouped = $cmp->viewData('groupedAppointments');
        $this->assertCount(1, $grouped->first());
        $this->assertSame('confirmed', $grouped->first()->first()->status);
    }

    private function makeSoloAppointment(Service $service, User $staff, string $start, string $email = 'solo@example.com'): Appointment
    {
        $startsAt = Carbon::parse($start);
        return Appointment::create([
            'service_id' => $service->id,
            'user_id' => $staff->id,
            'customer_name' => 'Solo',
            'customer_email' => $email,
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->copy()->addMinutes($service->duration),
            'status' => 'booked',
            'booking_group_id' => null,
        ]);
    }

    public function test_time_filter_defaults_to_upcoming_and_hides_past_appointments(): void
    {
        $this->actingAsSuperAdmin();
        $service = $this->makeService();
        $staff = $this->makeStaff($service);
        $past = $this->makeSoloAppointment($service, $staff, Carbon::now()->subWeek()->toDateTimeString(), 'past@example.com');
        $future = $this->makeSoloAppointment($service, $staff, Carbon::now()->addWeek()->toDateTimeString(), 'future@example.com');

        $cmp = Livewire::test(AppointmentsIndex::class);

        $this->assertSame('upcoming', $cmp->get('timeFilter'));
        $ids = $cmp->viewData('appointments')->pluck('id');
        $this->assertTrue($ids->contains($future->id), 'Upcoming appointment should be visible by default.');
        $this->assertFalse($ids->contains($past->id), 'Past appointment should be hidden by default.');
    }

    public function test_time_filter_past_shows_only_past_appointments(): void
    {
        $this->actingAsSuperAdmin();
        $service = $this->makeService();
        $staff = $this->makeStaff($service);
        $past = $this->makeSoloAppointment($service, $staff, Carbon::now()->subWeek()->toDateTimeString(), 'past@example.com');
        $future = $this->makeSoloAppointment($service, $staff, Carbon::now()->addWeek()->toDateTimeString(), 'future@example.com');

        $ids = Livewire::test(AppointmentsIndex::class)
            ->set('timeFilter', 'past')
            ->viewData('appointments')
            ->pluck('id');

        $this->assertTrue($ids->contains($past->id));
        $this->assertFalse($ids->contains($future->id));
    }

    public function test_time_filter_empty_shows_all_appointments(): void
    {
        $this->actingAsSuperAdmin();
        $service = $this->makeService();
        $staff = $this->makeStaff($service);
        $past = $this->makeSoloAppointment($service, $staff, Carbon::now()->subWeek()->toDateTimeString(), 'past@example.com');
        $future = $this->makeSoloAppointment($service, $staff, Carbon::now()->addWeek()->toDateTimeString(), 'future@example.com');

        $ids = Livewire::test(AppointmentsIndex::class)
            ->set('timeFilter', '')
            ->viewData('appointments')
            ->pluck('id');

        $this->assertTrue($ids->contains($past->id));
        $this->assertTrue($ids->contains($future->id));
    }

    public function test_ungrouped_appointment_is_not_affected_by_session_numbering(): void
    {
        $this->actingAsSuperAdmin();
        $service = $this->makeService();
        $staff = $this->makeStaff($service);

        Appointment::create([
            'service_id' => $service->id,
            'user_id' => $staff->id,
            'customer_name' => 'Solo',
            'customer_email' => 'solo@example.com',
            'starts_at' => Carbon::parse('2026-08-01 10:00'),
            'ends_at' => Carbon::parse('2026-08-01 11:00'),
            'status' => 'booked',
            'booking_group_id' => null,
        ]);

        $cmp = Livewire::test(AppointmentsIndex::class);

        $this->assertSame([], $cmp->viewData('sessionNumbers'));
        $this->assertSame([], $cmp->viewData('groupTotals'));
    }
}
