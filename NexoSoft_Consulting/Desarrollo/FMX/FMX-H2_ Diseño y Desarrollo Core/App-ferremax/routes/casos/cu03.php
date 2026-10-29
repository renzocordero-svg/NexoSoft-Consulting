<?php

use App\Http\Controllers\ProductoController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active', 'role:admin'])->group(function () {
    Route::get('/productos/imprimir', [ProductoController::class, 'imprimir'])->name('productos.imprimir');
    Route::post('/productos/{producto}/stock', [ProductoController::class, 'stock'])->name('productos.stock');
    Route::resource('productos', ProductoController::class)->except(['show']);
});
