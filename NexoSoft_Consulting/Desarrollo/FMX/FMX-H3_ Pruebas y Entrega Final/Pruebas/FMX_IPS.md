# Informe de pruebas de seguridad de FERREMAX

Borrador del informe FMX_IPS (actividad del Hito 3: «Ejecución de pruebas de seguridad: roles, hash de contraseñas y sesiones», del 09/11 al 17/11). Responsable: Castillejo Huancas. La versión final se entrega como `FMX_IPS.docx`, según la nomenclatura del GCS.

Estado al 09/11/2026: casos de prueba definidos y revisión del código de la aplicación. Las pruebas automatizadas aún no se han ejecutado; ningún caso tiene todavía resultado observado.

## Objeto de prueba

Aplicación `App-ferremax` de `FMX-H2_ Diseño y Desarrollo Core`, tal como quedó integrada en la rama `develop`. Componentes revisados:

- `Auth/LoginController` y `Auth/LogoutController`
- Middleware `EnsureActiveUser` (alias `active`) y `RoleMiddleware` (alias `role`)
- `UsuarioController` y `PerfilController` (almacenamiento de contraseñas)
- Rutas de `routes/casos/cu01.php` a `cu09.php`
- `config/session.php` y `.env.example`

## Requisitos de referencia

| Fuente | Requisito |
| --- | --- |
| FMX_LRS R1, R2 | Iniciar y cerrar sesión de forma segura |
| FMX_LRS R3 | Bloqueo temporal tras 3 intentos fallidos consecutivos |
| FMX_LRS R4 | Control de acceso por rol en el servidor mediante middleware |
| FMX_LRS R17 | Contraseñas de usuarios almacenadas con bcrypt |
| FMX_RNF RNF001 | Nivel de acceso según el perfil del usuario |
| FMX_RNF RNF002 | Bcrypt, sesiones con expiración por inactividad y bloqueo tras 3 intentos |
| FMX_RNF RNF017 | Protección contra CSRF, XSS e inyección SQL; comunicaciones por HTTPS |
| FMX_CU1 | Flujo básico y alternativos 3.2.1 a 3.2.4; post condición 5.2 (log de acceso) |
| FMX_CU2 | Flujo básico, alternativos 3.2.1 a 3.2.3 y post condiciones |

## Casos de prueba de seguridad

Numeración propia (CPS-xx) mientras no esté disponible el plan de pruebas FMX_PCP; se ajustará a su numeración cuando se publique.

### Hash de contraseñas

| ID | Caso | Resultado esperado | Requisito |
| --- | --- | --- | --- |
| CPS-01 | Crear un usuario desde la gestión de usuarios | La contraseña se guarda como hash bcrypt (`$2y$`), nunca en texto plano | R17, RNF002 |
| CPS-02 | Cambiar la contraseña desde la edición de usuario y desde el perfil | La nueva contraseña queda como hash bcrypt y permite iniciar sesión | R17, RNF002 |
| CPS-03 | Serializar un usuario a JSON o array | No aparecen `password` ni `remember_token` | RNF002 |
| CPS-04 | Cambiar la contraseña propia en el perfil | Exige la contraseña actual, mínimo 8 caracteres, al menos un número y confirmación | RNF002 |

### Inicio de sesión

| ID | Caso | Resultado esperado | Requisito |
| --- | --- | --- | --- |
| CPS-05 | Login correcto | Se regenera el identificador de sesión (prevención de fijación) | CU1 paso 8 |
| CPS-06 | Contraseña incorrecta o usuario inexistente | Mismo mensaje genérico en ambos casos, sin indicar qué campo falló; se conserva el usuario y no la contraseña | CU1 3.2.1 |
| CPS-07 | Tres intentos fallidos y luego la contraseña correcta | El cuarto intento se rechaza sin validar credenciales | R3, RNF002, CU1 3.2.2 |
| CPS-08 | Esperar a que termine el bloqueo | Tras 5 minutos el usuario puede volver a ingresar | R3, prototipo CU1 |
| CPS-09 | Bloqueo de un usuario | Otro usuario desde la misma IP no queda bloqueado | R3 |
| CPS-10 | Login con una cuenta inactiva | No ingresa y recibe el mensaje de cuenta inactiva | CU1 3.2.3 |
| CPS-11 | Usuario con sesión abierta al que el administrador desactiva | En la siguiente petición se cierra su sesión y vuelve al login | CU1 3.2.3 |

### Control de acceso por rol

| ID | Caso | Resultado esperado | Requisito |
| --- | --- | --- | --- |
| CPS-12 | Vendedor accede a productos, usuarios, movimientos y reportes (GET y POST) | 403 en todas las rutas de administrador | R4, RNF001 |
| CPS-13 | Vendedor accede a ventas, historial y perfil | Acceso permitido | R4 |
| CPS-14 | Invitado accede a cualquier ruta protegida | Redirección al login | CU2 post condición 5.2 |
| CPS-15 | Administrador intenta desactivarse o quitarse el rol de administrador | La operación se rechaza | R4 |

### Cierre y expiración de sesión

| ID | Caso | Resultado esperado | Requisito |
| --- | --- | --- | --- |
| CPS-16 | Cerrar sesión | Se invalida la sesión, se regenera el token CSRF y se redirige al login con «Ha cerrado sesión correctamente» | R2, CU2 pasos 4-7 |
| CPS-17 | Cerrar sesión por GET | Rechazado (405) | R2 |
| CPS-18 | Tras cerrar sesión, volver a una ruta protegida | Redirección al login; las páginas protegidas se sirven con `Cache-Control: no-store` | CU2 3.2.3 |
| CPS-19 | 30 minutos sin actividad | La sesión expira y la siguiente petición va al login | RNF002, CU2 3.2.2 |

### Protecciones y registro

| ID | Caso | Resultado esperado | Requisito |
| --- | --- | --- | --- |
| CPS-20 | Enviar un formulario sin token CSRF | Rechazado (419) | RNF017 |
| CPS-21 | Ingresar texto con etiquetas HTML en campos que luego se muestran | Se muestra escapado, sin ejecutarse | RNF017 |
| CPS-22 | Ingresar comillas o fragmentos SQL en el usuario del login | Se trata como texto; no altera la consulta | RNF017 |
| CPS-23 | Inicios correctos, fallos, bloqueos y cierres de sesión | Cada evento queda registrado en el log | CU1 3.2.2 y 5.2, CU2 |
| CPS-24 | Revisión de la cookie de sesión | `HttpOnly` y `SameSite=Lax`; `Secure` cuando se sirva por HTTPS | RNF017 |

## Observaciones preliminares de la revisión de código

Se obtuvieron leyendo el código en `develop`, sin ejecutarlo. Cada una debe confirmarse con su caso de prueba antes de registrarse como defecto en FMX_RDF.

| ID | Caso | Observación | Referencia |
| --- | --- | --- | --- |
| OBS-01 | CPS-06 | El mensaje es «Credenciales incorrectas. Te quedan N intento(s).», distinto del texto de la especificación («Las credenciales no coinciden con nuestros registros»). No revela qué campo falló. | `LoginController::login` |
| OBS-02 | CPS-10 | `Auth::attempt` incluye `activo = true`: una cuenta inactiva recibe el mismo mensaje que una contraseña errónea y además suma un intento fallido. La especificación pide un mensaje propio de cuenta inactiva. | `LoginController::login` |
| OBS-03 | CPS-19 | El login conserva la casilla «Mantener sesión iniciada». Con ella, la cookie de recordatorio vuelve a autenticar al usuario y la sesión no expira a los 30 minutos. Además, `config/session.php` usa 120 minutos si el `.env` no define `SESSION_LIFETIME` (`.env.example` sí trae 30). | `auth/login.blade.php`, `config/session.php` |
| OBS-04 | CPS-23 | Solo se registra el inicio de sesión exitoso, en el canal general. No se registran los intentos fallidos, los bloqueos ni los cierres de sesión. | `LoginController`, `LogoutController` |
| OBS-05 | CPS-24 | `SESSION_SECURE_COOKIE` no está definido. Depende de servir la aplicación por HTTPS en el despliegue. | `config/session.php` |
| OBS-06 | — | RNF001 menciona un perfil «Cliente» que no existe en el sistema (los roles son `admin` y `vendedor`). Inconsistencia de la documentación de requisitos, no del código. | FMX_RNF |

Puntos que la revisión sí encontró conformes y que quedan por confirmar con ejecución: contraseñas con `Hash::make` al crear, editar y cambiar desde el perfil (CPS-01, CPS-02); `password` y `remember_token` ocultos (CPS-03); rutas de administrador protegidas con `role:admin` (CPS-12); expulsión de cuentas desactivadas con sesión abierta y `Cache-Control: no-store` mediante `EnsureActiveUser` (CPS-11, CPS-18).

## Limitaciones previstas

- PHPUnit desactiva la verificación CSRF al ejecutar pruebas; CPS-20 se verificará con una petición HTTP real sobre el entorno Docker.
- HTTPS (CPS-24) depende del despliegue y no se puede comprobar en SQLite en memoria.
- La expiración de 30 minutos (CPS-19) se simulará adelantando el reloj en la prueba.

## Próximos pasos

1. Implementar los casos automatizables en `tests/SeguridadTest.php`, dentro de esta carpeta de pruebas.
2. Ejecutarlos con `Ejecutar-Pruebas.ps1` y guardar las evidencias en `Evidencias/`.
3. Registrar los resultados observados en este informe y los defectos confirmados en FMX_RDF.
4. Pasar el informe final a `FMX_IPS.docx`.
