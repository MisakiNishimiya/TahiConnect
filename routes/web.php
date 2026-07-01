<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Dashboard redirect based on role
Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user->role === 'admin') {
        return redirect()->route('admin.dashboard');
    } elseif ($user->role === 'tailor_staff') {
        return redirect()->route('staff.dashboard');
    } elseif ($user->role === 'shop_owner') {
        return redirect()->route('shopowner.dashboard');
    }
    return redirect()->route('customer.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Customer Routes
Route::middleware(['auth'])->prefix('customer')->group(function () {
    Volt::route('/dashboard', 'customer.dashboard')->name('customer.dashboard');
    Volt::route('/shops', 'customer.shops')->name('customer.shops');
    Volt::route('/measurements', 'customer.measurements')->name('customer.measurements');
    Volt::route('/appointments', 'customer.appointments')->name('customer.appointments');
    Volt::route('/orders', 'customer.orders')->name('customer.orders');
    Volt::route('/virtual-tryon', 'customer.virtual-tryon')->name('customer.virtual-tryon');
    Volt::route('/tracking', 'customer.tracking')->name('customer.tracking');
    Volt::route('/payments', 'customer.payments')->name('customer.payments');
    Volt::route('/notifications', 'customer.notifications')->name('customer.notifications');
});

// Admin Routes
Route::middleware(['auth'])->prefix('admin')->group(function () {
    Volt::route('/dashboard', 'admin.dashboard')->name('admin.dashboard');
    Volt::route('/shops', 'admin.shops')->name('admin.shops');
    Volt::route('/users', 'admin.users')->name('admin.users');
    Volt::route('/appointments', 'admin.appointments')->name('admin.appointments');
    Volt::route('/orders', 'admin.orders')->name('admin.orders');
    Volt::route('/payments', 'admin.payments')->name('admin.payments');
    Volt::route('/reports', 'admin.reports')->name('admin.reports');
});

// Shop Owner Routes
Route::middleware(['auth'])->prefix('shopowner')->group(function () {
    Volt::route('/dashboard', 'shopowner.dashboard')->name('shopowner.dashboard');
    Volt::route('/orders', 'shopowner.orders')->name('shopowner.orders');
    Volt::route('/staff', 'shopowner.staff')->name('shopowner.staff');
    Volt::route('/garments', 'shopowner.garments')->name('shopowner.garments');
    Volt::route('/settings', 'shopowner.settings')->name('shopowner.settings');
});

// Staff Routes
Route::middleware(['auth'])->prefix('staff')->group(function () {
    Volt::route('/dashboard', 'staff.dashboard')->name('staff.dashboard');
    Volt::route('/orders', 'staff.orders')->name('staff.orders');
    Volt::route('/appointments', 'staff.appointments')->name('staff.appointments');
});

// Settings Routes
Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');
    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
