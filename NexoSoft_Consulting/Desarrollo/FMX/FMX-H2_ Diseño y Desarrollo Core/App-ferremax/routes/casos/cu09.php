<?php

use App\Http\Controllers\ReporteController;
use Illuminate\Support\Facades\Route;

Route::get('/reportes', [ReporteController::class, 'index'])->middleware(['auth', 'active', 'role:admin'])->name('reportes.index');
