<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

// ── Dashboard redirect based on role ─────────────────────────────────────────
Route::get('/dashboard', function () {
    $user = auth()->user();
    return match($user->role) {
        'super_admin'  => redirect()->route('superadmin.dashboard'),
        'shop_owner'   => redirect()->route('shopowner.dashboard'),
        'tailor_staff' => redirect()->route('staff.dashboard'),
        default        => redirect()->route('customer.dashboard'),
    };
})->middleware(['auth', 'verified'])->name('dashboard');

// ── Customer Routes ───────────────────────────────────────────────────────────
// Customers are the end-clients of the tailoring business.
Route::middleware(['auth'])->prefix('customer')->group(function () {
    Volt::route('/dashboard',     'customer.dashboard')->name('customer.dashboard');
    Volt::route('/measurements',  'customer.measurements')->name('customer.measurements');
    Volt::route('/appointments',  'customer.appointments')->name('customer.appointments');
    Volt::route('/orders',        'customer.orders')->name('customer.orders');
    Volt::route('/virtual-tryon', 'customer.virtual-tryon')->name('customer.virtual-tryon');
    Volt::route('/tracking',      'customer.tracking')->name('customer.tracking');
    Volt::route('/payments',      'customer.payments')->name('customer.payments');
    Volt::route('/notifications', 'customer.notifications')->name('customer.notifications');
    Volt::route('/book',          'customer.book')->name('customer.book');
    Volt::route('/catalog',       'customer.catalog')->name('customer.catalog');
    Volt::route('/rates',         'customer.rates')->name('customer.rates');
});

// ── Super Admin Routes ────────────────────────────────────────────────────────
// Super Admin = TahiConnect platform owner.
// Manages system configuration, shop owner accounts, audit logs,
// system monitoring. Does NOT manage day-to-day tailoring operations.
Route::middleware(['auth', 'role:super_admin'])->prefix('superadmin')->group(function () {
    Volt::route('/dashboard',      'superadmin.dashboard')->name('superadmin.dashboard');
    Volt::route('/shop-owners',    'superadmin.shop-owners')->name('superadmin.shop-owners');
    Volt::route('/shop-owners/{id}','superadmin.shop-owner-detail')->name('superadmin.shop-owner-detail');
    Volt::route('/subscription',   'superadmin.subscription')->name('superadmin.subscription');
    Volt::route('/health',         'superadmin.health')->name('superadmin.health');
    Volt::route('/maintenance',    'superadmin.maintenance')->name('superadmin.maintenance');
    Volt::route('/usage',          'superadmin.usage')->name('superadmin.usage');
    Volt::route('/users',          'superadmin.users')->name('superadmin.users');
    Volt::route('/system',         'superadmin.system')->name('superadmin.system');
    Volt::route('/audit-logs',     'superadmin.audit-logs')->name('superadmin.audit-logs');
    Volt::route('/announcements',  'superadmin.announcements')->name('superadmin.announcements');
    Volt::route('/ai-settings',    'superadmin.ai-settings')->name('superadmin.ai-settings');
});

// ── Shop Owner Routes ─────────────────────────────────────────────────────────
// Shop Owner = Tailoring business owner.
// Manages all day-to-day tailoring business operations.
Route::middleware(['auth', 'role:shop_owner'])->prefix('shopowner')->group(function () {
    Volt::route('/dashboard',     'shopowner.dashboard')->name('shopowner.dashboard');
    Volt::route('/orders',        'shopowner.orders')->name('shopowner.orders');
    Volt::route('/appointments',  'shopowner.appointments')->name('shopowner.appointments');
    Volt::route('/staff',         'shopowner.staff')->name('shopowner.staff');
    Volt::route('/customers',     'shopowner.customers')->name('shopowner.customers');
    Volt::route('/garments',      'shopowner.garments')->name('shopowner.garments');
    Volt::route('/rates',         'shopowner.rates')->name('shopowner.rates');
    Volt::route('/payments',      'shopowner.payments')->name('shopowner.payments');
    Volt::route('/reports',       'shopowner.reports')->name('shopowner.reports');
    Volt::route('/subscription',  'shopowner.subscription')->name('shopowner.subscription');
    Volt::route('/settings',      'shopowner.settings')->name('shopowner.settings');
});

// ── Staff Routes ──────────────────────────────────────────────────────────────
// Tailor Staff manages assigned orders, appointments, measurements.
Route::middleware(['auth', 'role:tailor_staff'])->prefix('staff')->group(function () {
    Volt::route('/dashboard',    'staff.dashboard')->name('staff.dashboard');
    Volt::route('/orders',       'staff.orders')->name('staff.orders');
    Volt::route('/appointments', 'staff.appointments')->name('staff.appointments');
});

// ── Settings Routes (all authenticated users) ─────────────────────────────────
Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');
    Volt::route('settings/profile',    'settings.profile')->name('settings.profile');
    Volt::route('settings/password',   'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
