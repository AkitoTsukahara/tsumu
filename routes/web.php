<?php

use App\Http\Controllers\Auth\LogoutController;
use App\Livewire\Auth\Login;
use App\Livewire\Today;
use Illuminate\Support\Facades\Route;

Route::livewire('/', Today::class)
    ->middleware('auth')
    ->name('today');

Route::livewire('/login', Login::class)
    ->middleware('guest')
    ->name('login');

Route::post('/logout', LogoutController::class)
    ->middleware('auth')
    ->name('logout');
