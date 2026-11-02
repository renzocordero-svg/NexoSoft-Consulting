<?php

use App\Http\Controllers\MovimientoStockController;
use Illuminate\Support\Facades\Route;

Route::get('/movimientos', [MovimientoStockController::class, 'index'])->middleware(['auth', 'active', 'role:admin'])->name('movimientos.index');
