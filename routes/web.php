<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::view('/', 'welcome');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware(['auth', 'verified'])->group(function () {
    Volt::route('dashboard', 'dashboard')->name('dashboard');

    Volt::route('teams', 'teams.index')->name('teams.index');
    Volt::route('teams/{team}', 'teams.show')->name('teams.show');

    Volt::route('vehicles', 'vehicles.index')->name('vehicles.index');
    Volt::route('vehicles/{vehicle}', 'vehicles.show')->name('vehicles.show');

    Volt::route('parts', 'parts.index')->name('parts.index');
});

require __DIR__.'/auth.php';
