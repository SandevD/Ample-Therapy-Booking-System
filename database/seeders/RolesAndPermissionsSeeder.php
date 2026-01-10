<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions
        $permissions = [
            // Dashboard
            'view_dashboard' => 'Access the dashboard',

            // Services
            'view_services' => 'View services list',
            'create_services' => 'Create new services',
            'edit_services' => 'Edit existing services',
            'delete_services' => 'Delete services',

            // Staff
            'view_staff' => 'View staff list',
            'create_staff' => 'Create new staff members',
            'edit_staff' => 'Edit staff members',
            'delete_staff' => 'Delete staff members',

            // Appointments
            'view_appointments' => 'View all appointments',
            'create_appointments' => 'Create appointments',
            'edit_appointments' => 'Edit appointments',
            'delete_appointments' => 'Delete appointments',
            'view_own_appointments' => 'View own appointments only',

            // Users & Roles
            'view_users' => 'View users list',
            'create_users' => 'Create new users',
            'edit_users' => 'Edit user accounts',
            'delete_users' => 'Delete users',
            'manage_roles' => 'Manage roles and permissions',
        ];

        // Create permissions
        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['name' => $name, 'guard_name' => 'web']
            );
        }

        // Create roles and assign permissions

        // Super Admin - gets all permissions via Gate::before in AuthServiceProvider
        $superAdmin = Role::firstOrCreate(
            ['name' => 'Super Admin', 'guard_name' => 'web']
        );
        $superAdmin->syncPermissions(Permission::all());

        // Staff - can manage appointments and view dashboard
        $staff = Role::firstOrCreate(
            ['name' => 'Staff', 'guard_name' => 'web']
        );
        $staff->syncPermissions([
            'view_dashboard',
            'view_services',
            'view_staff',
            'view_appointments',
            'create_appointments',
            'edit_appointments',
            'view_own_appointments',
        ]);

        // Customer - can only view own appointments
        $customer = Role::firstOrCreate(
            ['name' => 'Customer', 'guard_name' => 'web']
        );
        $customer->syncPermissions([
            'view_own_appointments',
        ]);

        // Create sample users for each role
        $this->createSampleUsers();
    }

    private function createSampleUsers(): void
    {
        // Super Admin
        $admin = User::firstOrCreate(
            ['email' => 'sandev.net@gmail.com'],
            [
                'name' => 'Sandev Admin',
                'email' => 'sandev.net@gmail.com',
                'password' => Hash::make('Kalupusa321@'),
                'is_active' => true,
            ]
        );
        $admin->assignRole('Super Admin');

        // Owner (Staff Role)
        $owner = User::firstOrCreate(
            ['email' => 'hello@ampletherapy.org.uk'],
            [
                'name' => 'Inayata Kanji',
                'email' => 'hello@ampletherapy.org.uk',
                'password' => Hash::make('Inayat321@'),
                'phone' => '+44 7000 000000', // Placeholder
                'bio' => 'Owner & Therapist at AMPLE Therapy',
                'is_active' => true,
            ]
        );
        $owner->assignRole('Staff');
    }
}

