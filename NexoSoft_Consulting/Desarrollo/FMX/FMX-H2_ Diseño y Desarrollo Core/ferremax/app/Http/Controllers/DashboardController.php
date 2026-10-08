<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    // CUS-01 paso 10: ventana principal con la bienvenida al usuario
    public function index()
    {
        return view('dashboard', ['user' => Auth::user()]);
    }
}
