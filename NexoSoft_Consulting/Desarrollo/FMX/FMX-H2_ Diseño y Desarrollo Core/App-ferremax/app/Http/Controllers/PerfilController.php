<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PerfilController extends Controller
{
    public function edit()
    {
        return view('perfil.edit');
    }

    public function update(Request $request)
    {
        $data = $request->validate(['actual' => ['required', 'current_password'], 'password' => ['required', 'string', 'min:8', 'regex:/[0-9]/', 'confirmed']]);
        $request->user()->update(['password' => Hash::make($data['password']), 'remember_token' => null]);
        $request->session()->regenerate();

        return back()->with('success', 'Contraseña actualizada.');
    }
}
