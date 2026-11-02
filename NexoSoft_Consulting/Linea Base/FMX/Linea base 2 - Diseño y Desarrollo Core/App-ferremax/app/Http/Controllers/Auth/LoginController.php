<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'nombre_usuario' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $this->checkRateLimit($request);

        $credentials = [
            'nombre_usuario' => $request->nombre_usuario,
            'password' => $request->password,
            'activo' => true,
        ];

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey($request), 300);

            $remaining = 3 - RateLimiter::attempts($this->throttleKey($request));

            throw ValidationException::withMessages([
                'nombre_usuario' => $remaining > 0
                    ? "Credenciales incorrectas. Te quedan {$remaining} intento(s)."
                    : 'Credenciales incorrectas.',
            ]);
        }

        RateLimiter::clear($this->throttleKey($request));
        $request->session()->regenerate();

        Log::info('Inicio de sesión', ['usuario_id' => Auth::id()]);

        return redirect()->intended(route('dashboard'));
    }

    private function throttleKey(Request $request): string
    {
        return Str::lower($request->nombre_usuario).'|'.$request->ip();
    }

    private function checkRateLimit(Request $request): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), 3)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'nombre_usuario' => "Demasiados intentos fallidos. Espera {$seconds} segundo(s) antes de volver a intentarlo.",
        ]);
    }
}
