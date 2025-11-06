<?php

use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Route;

// Public routes
Volt::route('/login', 'login')->name('login');
Volt::route('/register', 'register');

// ADD THIS LINE - Flexible pricing page
Volt::route('/pricing', 'pricing')->name('pricing');

// Logout
Route::get('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
});


// Checkout routes - protected by auth

Route::middleware('auth')->group(function () {
    Volt::route('/checkout/{plan}', 'checkout')->name('checkout');
    Volt::route('/checkout/success', 'checkout-success')->name('checkout.success'); // ✅ Volt
    Volt::route('/checkout/cancel', 'checkout-cancel')->name('checkout.cancel');
});

// Protected routes
Route::middleware('auth')->group(function () {
    Volt::route('/', 'index')->name('index');
    Volt::route('/users', 'users.index');
    Volt::route('/users/create', 'users.create');
    Volt::route('/users/{user}/edit', 'users.edit');
});
