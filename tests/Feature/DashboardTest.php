<?php

use App\Livewire\Dashboard;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

test('guests are redirected to the login page', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

test('authenticated users can visit the dashboard', function () {
    $this->actingAs($user = User::factory()->create());

    $this->get('/dashboard')->assertStatus(200);
});

function dashboardSuperAdmin(): User
{
    Role::firstOrCreate(['name' => 'Super Admin']);
    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    return $admin;
}

function dashboardService(): Service
{
    return Service::create([
        'name' => 'Test Service',
        'duration' => 60,
        'price' => 100,
        'color' => '#00ff00',
        'is_active' => true,
        'session_count' => 1,
        'buffer_time' => 0,
    ]);
}

function dashboardAppointment(Service $service, User $staff, Carbon $startsAt, string $status = 'confirmed'): Appointment
{
    return Appointment::create([
        'service_id' => $service->id,
        'user_id' => $staff->id,
        'customer_name' => 'Customer',
        'customer_email' => 'customer@example.com',
        'starts_at' => $startsAt,
        'ends_at' => $startsAt->copy()->addHour(),
        'status' => $status,
        'booking_group_id' => null,
    ]);
}

test('admin dashboard widget shows only confirmed appointments for today by default', function () {
    $this->actingAs($admin = dashboardSuperAdmin());
    $service = dashboardService();

    $todayConfirmed = dashboardAppointment($service, $admin, Carbon::today()->addHours(10));
    $todayBooked = dashboardAppointment($service, $admin, Carbon::today()->addHours(11), 'booked');
    $otherDay = dashboardAppointment($service, $admin, Carbon::today()->addDay()->addHours(10));

    $cmp = Livewire::test(Dashboard::class);

    expect($cmp->get('dateFilter'))->toBe(Carbon::today()->toDateString());

    $ids = $cmp->viewData('upcomingAppointments')->pluck('id')->all();
    expect($ids)->toContain($todayConfirmed->id)        // confirmed today shown
        ->not->toContain($todayBooked->id)              // non-confirmed hidden
        ->not->toContain($otherDay->id);                // other date hidden
});

test('admin dashboard widget date filter shows appointments for the selected date', function () {
    $this->actingAs($admin = dashboardSuperAdmin());
    $service = dashboardService();

    $selected = Carbon::today()->addWeek();
    $onSelected = dashboardAppointment($service, $admin, $selected->copy()->addHours(10));
    $today = dashboardAppointment($service, $admin, Carbon::today()->addHours(10));

    $ids = Livewire::test(Dashboard::class)
        ->set('dateFilter', $selected->toDateString())
        ->viewData('upcomingAppointments')
        ->pluck('id')
        ->all();

    expect($ids)->toContain($onSelected->id)
        ->not->toContain($today->id);
});