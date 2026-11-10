<?php

use App\Http\Controllers\HistorialVentaController;
use Illuminate\Support\Facades\Route;

Route::get('/ventas', HistorialVentaController::class)->middleware(['auth', 'active'])->name('ventas.index');
