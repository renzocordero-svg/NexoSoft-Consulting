# FERREMAX

Aplicación integrada basada en el proyecto original, las especificaciones CU1–CU9 y las capturas proporcionadas. Laravel 11, PHP 8.3, Blade, Bootstrap 5, Chart.js, SQLite para demostración local y MySQL 8 para Docker. Arquitectura MVC con servicios de ventas, inventario y reportes.

## Ejecutar desde VS Code en Windows

Abre esta carpeta `ferremax`, la que contiene `artisan`. En la terminal PowerShell:

```powershell
$env:Path = "C:\xampp\php;$env:Path"
composer install
.\iniciar.ps1
```

Si la política de PowerShell bloquea el script, abre `iniciar.ps1` para revisarlo o usa los comandos manuales de abajo. No hace falta cambiar permanentemente la política del equipo.

Si Composer no está en el PATH pero lo instalaste por usuario:

```powershell
php "$env:LOCALAPPDATA\ComposerSetup\bin\composer.phar" install
```

El lanzador prepara `.env`, una clave privada local, SQLite, migraciones y cuentas de prueba; no borra una base existente. Abre http://127.0.0.1:8000. Si el puerto está ocupado, usa `.\iniciar.ps1 -Puerto 8010`. Ctrl+C detiene el servidor.

Preparación manual, solo cuando faltan los archivos:

```powershell
if (!(Test-Path .env)) { Copy-Item .env.example .env }
php artisan key:generate
if (!(Test-Path database/database.sqlite)) { New-Item -ItemType File database/database.sqlite }
php artisan migrate --seed
php artisan serve
```

No vuelvas a generar la clave de una instalación que ya funciona: el lanzador solo la genera cuando está vacía.

| Usuario | Contraseña | Rol |
|---|---|---|
| admin | admin123 | Administrador |
| vendedor1 | vendedor123 | Vendedor |

Las cuentas y cinco productos iniciales se crean de forma idempotente; los seeders no restablecen contraseñas ni stock existentes. Para ver datos en reportes, registra ventas. Alternativamente, sobre una instalación de demostración sin ventas: `php artisan db:seed --class=DemoSeeder`. Este seeder crea tres ventas de ejemplo y descuenta sus existencias; se omite si ya hay ventas.

## Funcionalidades

- CU1: usuario y contraseña, recordar sesión, bloqueo por tres fallos durante cinco minutos, sesión de 30 minutos de inactividad y acceso por rol.
- CU2: cierre por POST, invalidación de sesión y renovación del token CSRF.
- CU3: catálogo, búsqueda, alta, edición, desactivación, mínimo de stock, impresión de existencias y entradas/salidas auditadas. El saldo no se edita directamente.
- CU4: carrito recuperable tras un error, validación en el servidor, importes en centavos, IGV 18 %, transacción atómica, bloqueo de existencias y secuencia de factura protegida.
- CU5: factura de solo lectura, acceso del administrador o del vendedor propietario e impresión.
- CU6: historial con filtros de fechas y vendedor; un vendedor solo puede consultar sus propias ventas.
- CU7: bitácora de stock con filtros y vista de impresión de todos los resultados filtrados.
- CU8: usuarios con baja lógica, contraseña de ocho caracteres y un número, protección contra autodesactivación/degradación y cambio de contraseña propia.
- CU9: ingresos, margen histórico sin IGV, ventas, ticket promedio, gráficos y rankings del período y vendedor seleccionados. Los menos vendidos excluyen productos sin ventas, como establece el diseño original.

Se conserva el símbolo `$` de las capturas. Se usa la etiqueta IGV de las especificaciones. Las opciones «Guardar PDF» usan el diálogo de impresión del navegador, seleccionando «Guardar como PDF»; no generan un PDF fiscal ni una descarga automática desde el servidor.

## Pruebas

```powershell
php vendor/bin/phpunit
php artisan view:cache
php artisan route:list
```

PHPUnit usa una base SQLite en memoria independiente de tus datos locales. Incluye controles de acceso, ventas y reversión, duplicados, correlativos, stock, reportes y renderizado de formularios.

## Docker con MySQL

Con Docker Desktop iniciado, desde esta carpeta:

```powershell
docker compose up --build
```

Abre http://localhost:8080. Nginx reenvía a la aplicación Laravel y MySQL permanece en la red interna. El contenedor instala las versiones de `composer.lock`; no descarga un proyecto Laravel nuevo. Los volúmenes conservan base de datos y clave. Es un entorno local de presentación, no una configuración endurecida de producción. Para detenerlo sin borrar datos: `docker compose down`.

## Archivos y límites de validación

No compartir `.env`, `vendor`, SQLite, logs, cachés, claves o sesiones. Se generan por instalación. Bootstrap, iconos y Chart.js se cargan desde CDN y requieren Internet. El código de reportes y facturación es compatible con SQLite y MySQL; la ejecución y pruebas realizadas en este equipo usan SQLite. Docker/MySQL y concurrencia simultánea de múltiples procesos requieren una verificación adicional en un equipo con Docker.

El proyecto original en la carpeta hermana `Ferremax` no se modifica. La copia integrada puede contener una venta local realizada para comprobar el circuito; ese archivo SQLite no forma parte de las entregas.
