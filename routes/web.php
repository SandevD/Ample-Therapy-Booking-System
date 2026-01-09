<?php

use App\Livewire\Admin\Admins\Index as AdminsIndex;
use App\Livewire\Admin\Appointments\Index as AppointmentsIndex;
use App\Livewire\Admin\Customers\Index as CustomersIndex;
use App\Livewire\Admin\Roles\Index as RolesIndex;
use App\Livewire\Admin\Services\Index as ServicesIndex;
use App\Livewire\Admin\Staff\Index as StaffIndex;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', \App\Livewire\Dashboard::class)->name('dashboard');

    // Appointments
    Route::get('appointments', AppointmentsIndex::class)->name('admin.appointments');

    // Setup
    Route::get('services', ServicesIndex::class)->name('admin.services');
    Route::get('staff', StaffIndex::class)->name('admin.staff');
    Route::get('customers', CustomersIndex::class)->name('admin.customers');

    // Administration
    Route::get('admins', AdminsIndex::class)->name('admin.admins');
    Route::get('roles', RolesIndex::class)->name('admin.roles');
});

require __DIR__ . '/settings.php';
