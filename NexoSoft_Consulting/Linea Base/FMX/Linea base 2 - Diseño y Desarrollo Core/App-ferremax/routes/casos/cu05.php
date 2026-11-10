<?php

use App\Http\Controllers\FacturaController;
use Illuminate\Support\Facades\Route;

Route::get('/ventas/{venta}', FacturaController::class)->whereNumber('venta')->middleware(['auth', 'active'])->name('ventas.show');
