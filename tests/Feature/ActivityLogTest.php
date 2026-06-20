<?php

use App\Livewire\Admin\ActivityLog\Index as ActivityLogIndex;
use App\Models\Service;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

function activityLogSuperAdmin(): User
{
    Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'view_activity_log', 'guard_name' => 'web']);
    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    return $admin;
}

function activityLogStaff(): User
{
    Role::firstOrCreate(['name' => 'Staff', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'view_activity_log', 'guard_name' => 'web']);
    $staff = User::factory()->create();
    $staff->assignRole('Staff');

    return $staff;
}

test('super admin can view the activity log page', function () {
    $this->actingAs(activityLogSuperAdmin());

    $this->get('/activity-log')->assertOk();
});

test('staff cannot view the activity log page', function () {
    $this->actingAs(activityLogStaff());

    $this->get('/activity-log')->assertForbidden();
});

test('model changes are logged with the causer', function () {
    $admin = activityLogSuperAdmin();
    $this->actingAs($admin);

    $service = Service::create([
        'name' => 'Logged Service',
        'duration' => 60,
        'price' => 100,
        'color' => '#abcdef',
        'is_active' => true,
        'session_count' => 1,
        'buffer_time' => 0,
    ]);

    $activity = Activity::where('log_name', 'service')
        ->where('subject_id', $service->id)
        ->where('event', 'created')
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->causer_id)->toBe($admin->id)
        ->and($activity->description)->toBe('Service created');
});

test('updating a model records the field diff', function () {
    $this->actingAs(activityLogSuperAdmin());

    $service = Service::create([
        'name' => 'Before',
        'duration' => 60,
        'price' => 100,
        'color' => '#abcdef',
        'is_active' => true,
        'session_count' => 1,
        'buffer_time' => 0,
    ]);
    $service->update(['name' => 'After']);

    $activity = Activity::where('log_name', 'service')
        ->where('subject_id', $service->id)
        ->where('event', 'updated')
        ->first();

    expect(data_get($activity->attribute_changes, 'old.name'))->toBe('Before')
        ->and(data_get($activity->attribute_changes, 'attributes.name'))->toBe('After');
});

test('login events are recorded in the auth log', function () {
    $user = User::factory()->create();

    event(new Login('web', $user, false));

    $activity = Activity::where('log_name', 'auth')->where('event', 'login')->latest('id')->first();

    expect($activity)->not->toBeNull()
        ->and($activity->causer_id)->toBe($user->id);
});

test('the type filter narrows the results', function () {
    $admin = activityLogSuperAdmin();
    $this->actingAs($admin);

    Service::create([
        'name' => 'Filterable',
        'duration' => 60,
        'price' => 100,
        'color' => '#abcdef',
        'is_active' => true,
        'session_count' => 1,
        'buffer_time' => 0,
    ]);
    event(new Login('web', $admin, false));

    $logNames = Livewire::test(ActivityLogIndex::class)
        ->set('logName', 'auth')
        ->viewData('activities')
        ->pluck('log_name')
        ->unique()
        ->values()
        ->all();

    expect($logNames)->toBe(['auth']);
});

test('password should never appear in user activity logs', function () {
    $this->actingAs(activityLogSuperAdmin());

    $user = User::factory()->create();
    $user->update(['password' => bcrypt('new-secret-password'), 'name' => 'Renamed']);

    $activity = Activity::where('log_name', 'user')
        ->where('subject_id', $user->id)
        ->where('event', 'updated')
        ->first();

    $serialized = json_encode($activity?->attribute_changes);

    expect($serialized)->not->toContain('password')
        ->and($serialized)->not->toContain('secret');
});
