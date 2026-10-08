# C01 y C02 · Seguridad (Castillejo Huancas)

Código de los casos de uso **CUS-01 Iniciar sesión** (`feature/cu_1`) y
**CUS-02 Cerrar sesión** (`feature/cu_2`). Las carpetas siguen la estructura de
Laravel 11: al integrar en `develop`, los archivos se copian sobre el proyecto
base en la misma ruta.

## C01 · CUS-01 Iniciar sesión

| Especificación (FMX_CU1) | Implementación |
|---|---|
| Pasos 1-2 · ruta `/login` | `routes/web.php`, `resources/views/auth/login.blade.php` |
| Paso 5 · FA 3.2.2 bloqueo tras 3 intentos | `LoginController::login` (`RateLimiter`, 60 s) |
| Paso 6 · FA 3.2.1 credenciales incorrectas | `Auth::attempt` contra el hash de la contraseña |
| Paso 7 · FA 3.2.3 cuenta inactiva | `LoginController::login` descarta la sesión |
| Paso 8 · regeneración de sesión | `session()->regenerate()` |
| Paso 9 · menú según el rol | `config/menu.php` + `resources/views/layouts/app.blade.php` |
| Paso 10 · bienvenida | `resources/views/dashboard.blade.php` |
| FA 3.2.4 · campos vacíos | script en la vista + `app/Http/Requests/LoginRequest.php` |
| Post condición 5.2 · log de acceso | `storage/logs/seguridad-AAAA-MM-DD.log` |

Tabla `users` (DAS, vista de datos): `nombre_usuario`, `password` (hash), `rol`
(`admin`/`vendedor`) y `activo`, en `database/migrations/0001_01_01_000000_create_users_table.php`
(reemplaza a la migración de usuarios que trae Laravel).

### Para los demás casos de uso

- Proteger rutas por rol: `->middleware(['auth', 'role:admin'])` o `role:admin,vendedor`.
- Agregar una opción al menú lateral: una línea en `config/menu.php`.
- Las vistas extienden `layouts.app` (Bootstrap 5.3).

Usuarios de prueba (`php artisan migrate:fresh --seed`): `admin / admin123`,
`vendedor1 / vendedor123`, `inactivo / inactivo123` (cuenta desactivada).

## C02 · CUS-02 Cerrar sesión

| Especificación (FMX_CU2) | Implementación |
|---|---|
| Pasos 1-3 · FA 3.2.1 confirmar o cancelar | modal `#modal-cerrar-sesion` en `layouts/app.blade.php` |
| Pasos 4-6 · cerrar, invalidar, regenerar token | `LoginController::logout` |
| Paso 7 · «Ha cerrado sesión correctamente» | `LoginController::logout` + `auth/login.blade.php` |
| FA 3.2.2 · expiración a los 30 min | `SESSION_LIFETIME=30`; aviso en `bootstrap/app.php` (cookie `fmx_sesion`) |
| FA 3.2.3 · botón «atrás» tras el cierre | `bootstrap/app.php`: token CSRF vencido (419) → login |
| Post condición 5.2 | middleware `auth` en las rutas protegidas |

**Configuración necesaria al integrar:** en `.env` (y en `.env.example`) poner
`SESSION_LIFETIME=30`. Laravel trae 120 minutos por defecto.

## Pruebas

`tests/Feature/Auth/`. Usan `RefreshDatabase`, así que conviene correrlas con
SQLite en memoria para no borrar la base de desarrollo: en `phpunit.xml`,
descomentar `DB_CONNECTION=sqlite` y `DB_DATABASE=:memory:`.

```bash
docker compose exec app php artisan test --filter="IniciarSesionTest|CerrarSesionTest|ControlAccesoPorRolTest"
```
