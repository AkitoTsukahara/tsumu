<?php

use App\Http\Controllers\Auth\LogoutController;
use App\Livewire\Auth\Login;
use App\Livewire\Equipment\Index as EquipmentIndex;
use App\Livewire\Today;
use Illuminate\Support\Facades\Route;

Route::livewire('/', Today::class)
    ->middleware('auth')
    ->name('today');

Route::livewire('/login', Login::class)
    ->middleware('guest')
    ->name('login');

Route::livewire('/settings/equipment', EquipmentIndex::class)
    ->middleware('auth')
    ->name('equipment.index');

Route::post('/logout', LogoutController::class)
    ->middleware('auth')
    ->name('logout');
