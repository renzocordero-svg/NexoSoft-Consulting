<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Psr\Log\LoggerInterface;

/**
 * CUS-01 Iniciar sesión (FMX_CU1).
 * Clase de control: coordina la interfaz de login y la entidad User.
 */
class LoginController extends Controller
{
    /** Intentos fallidos permitidos antes del bloqueo (FA 3.2.2). */
    public const MAX_INTENTOS = 3;

    /** Segundos que dura el bloqueo. */
    public const SEGUNDOS_BLOQUEO = 60;

    public const MSG_CREDENCIALES = 'Las credenciales no coinciden con nuestros registros.';
    public const MSG_BLOQUEO      = 'Demasiados intentos. Vuelva a intentarlo en unos minutos.';
    public const MSG_INACTIVA     = 'Su cuenta se encuentra inactiva. Comuníquese con el administrador.';

    // Pasos 1-2: mostrar la ventana de inicio de sesión
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Pasos 3-10 del flujo básico
    public function login(LoginRequest $request)
    {
        $key = $this->throttleKey($request);

        // Paso 5: verificar el límite de intentos fallidos (FA 3.2.2)
        if (RateLimiter::tooManyAttempts($key, self::MAX_INTENTOS)) {
            $this->logSeguridad()->warning('Login bloqueado por demasiados intentos', $this->contexto($request));

            throw ValidationException::withMessages(['login' => self::MSG_BLOQUEO]);
        }

        // Paso 6: validar las credenciales contra el hash almacenado (FA 3.2.1)
        if (!Auth::attempt($request->only('nombre_usuario', 'password'))) {
            RateLimiter::hit($key, self::SEGUNDOS_BLOQUEO);
            $this->logSeguridad()->notice('Credenciales incorrectas', $this->contexto($request) + [
                'intentos' => RateLimiter::attempts($key),
            ]);

            throw ValidationException::withMessages(['login' => self::MSG_CREDENCIALES]);
        }

        // Paso 7: verificar que la cuenta esté activa (FA 3.2.3)
        if (!Auth::user()->activo) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            $this->logSeguridad()->notice('Intento de acceso con cuenta inactiva', $this->contexto($request));

            throw ValidationException::withMessages(['login' => self::MSG_INACTIVA]);
        }

        // Paso 8: regenerar la sesión (evita la fijación de sesión) y reiniciar el contador
        $request->session()->regenerate();
        RateLimiter::clear($key);

        // Post condición 5.2: el acceso queda registrado
        $this->logSeguridad()->info('Inicio de sesión exitoso', $this->contexto($request) + [
            'usuario_id' => Auth::id(),
            'rol'        => Auth::user()->rol,
        ]);

        // Pasos 9-10: el layout arma el menú según el rol y el dashboard da la bienvenida
        return redirect()->intended(route('dashboard'));
    }

    private function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower($request->input('nombre_usuario')) . '|' . $request->ip());
    }

    private function contexto(Request $request): array
    {
        return [
            'nombre_usuario' => $request->input('nombre_usuario'),
            'ip'             => $request->ip(),
        ];
    }

    /** Log de seguridad y de acceso: storage/logs/seguridad-AAAA-MM-DD.log */
    private function logSeguridad(): LoggerInterface
    {
        return Log::build([
            'driver' => 'daily',
            'path'   => storage_path('logs/seguridad.log'),
            'days'   => 30,
        ]);
    }
}
