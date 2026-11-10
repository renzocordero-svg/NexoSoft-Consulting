<?php

use App\Http\Controllers\PerfilController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::resource('usuarios', UsuarioController::class)->except(['show'])->middleware(['auth', 'active', 'role:admin']);
Route::get('/perfil', [PerfilController::class, 'edit'])->middleware(['auth', 'active'])->name('perfil.edit');
Route::put('/perfil', [PerfilController::class, 'update'])->middleware(['auth', 'active'])->name('perfil.update');
