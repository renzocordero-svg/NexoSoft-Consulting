<?php

use App\Http\Controllers\Auth\LogoutController;
use Illuminate\Support\Facades\Route;

Route::post('/logout', LogoutController::class)->middleware(['auth', 'active'])->name('logout');
