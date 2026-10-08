<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * CUS-02 FA 3.2.2: si el navegador trae la cookie de sesión iniciada pero ya no hay sesión,
 * la sesión expiró por inactividad y se avisa en la ventana de login.
 */
$avisarSiExpiro = function (Request $request): void {
    if ($request->hasCookie(LoginController::COOKIE_SESION)) {
        $request->session()->flash('expirada', LoginController::MSG_EXPIRADA);
        Cookie::queue(Cookie::forget(LoginController::COOKIE_SESION));
    }
};

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) use ($avisarSiExpiro) {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);

        // Ruta protegida sin sesión → login (CUS-02 post condición 5.2)
        $middleware->redirectGuestsTo(function (Request $request) use ($avisarSiExpiro) {
            $avisarSiExpiro($request);

            return route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions) use ($avisarSiExpiro) {
        // CUS-02 FA 3.2.2 / 3.2.3: un formulario enviado desde una página vieja (sesión expirada
        // o cerrada, botón «atrás») falla el token CSRF; en vez de la página 419 se vuelve al login.
        $exceptions->render(function (HttpException $e, Request $request) use ($avisarSiExpiro) {
            if (!$e->getPrevious() instanceof TokenMismatchException) {
                return null;
            }

            if (Auth::check()) {
                return back()->with('error', 'La página había caducado. Vuelva a intentarlo.');
            }

            $avisarSiExpiro($request);

            return redirect()->route('login');
        });
    })->create();
