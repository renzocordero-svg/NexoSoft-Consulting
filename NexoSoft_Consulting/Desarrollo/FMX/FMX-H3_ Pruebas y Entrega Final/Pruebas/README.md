# Pruebas del Hito 3 de FERREMAX

Esta carpeta reúne las pruebas de integración y la evidencia de cada ejecución. Prueba la aplicación de `FMX-H2_ Diseño y Desarrollo Core/App-ferremax`. La línea base se conserva como referencia.

El [informe inicial de integración](FMX_IPI.md) contiene los resultados observados, enlaces a logs y JUnit, incidencias del entorno y comprobaciones pendientes.

La [ampliación del martes sobre facturas e historial](FMX_IPI_Martes.md) documenta nueve casos HTTP adicionales, sus resultados y evidencias. La suite de Hito 3 contiene ahora 16 pruebas: las siete iniciales y las nueve de consulta y permisos.

## Ejecución desde PowerShell

Requisitos: PHP compatible con el proyecto, PDO SQLite y dependencias instaladas desde el `composer.lock` de la aplicación. Dentro de esta carpeta:

```powershell
.\Ejecutar-Pruebas.ps1 -Suite Integracion
.\Ejecutar-Pruebas.ps1 -Suite Regresion
```

Si la política local impide ejecutar scripts, puede revisar el lanzador y ejecutar directamente `php vendor/phpunit/phpunit/phpunit --configuration "<ruta absoluta a Pruebas/phpunit.xml>" --testdox` desde App-ferremax. No es necesario cambiar permanentemente la política del equipo.

`Integracion` ejecuta los casos nuevos de esta carpeta. `Regresion` ejecuta la suite existente del Hito 2. Ambas usan SQLite en memoria y no requieren iniciar el servidor web. El lanzador rechaza una configuración cacheada antes de iniciar PHPUnit.

Cada ejecución mediante el lanzador guarda en `Evidencias/` un log, un JUnit XML y metadatos con hora real, versión de PHP, revisión base, rama y cambios pendientes. Revise los archivos antes de incorporarlos al repositorio. No incluya `.env`, bases de datos, claves ni dependencias. Los metadatos pueden contener rutas locales.

La suite nueva verifica ventas de varios productos, IGV, stock insuficiente, reversión al fallar la factura, correlativos, precios y costos históricos, carrito vacío y última unidad. Los datos de ejemplo de los tests se crean en memoria y se descartan al terminar. La simulación de fallo existe solo durante CP-13.

La ampliación HTTP verifica importes de factura en pantalla, consultas sin cambios en los datos, acceso por propietario y administrador, denegación a otros vendedores, sesión y cuenta activa, filtros del historial, paginación y errores de validación. El HTML de impresión se inspecciona sin abrir un navegador.

SQLite no demuestra los bloqueos de filas de MySQL. CP-14 requiere dos procesos realmente simultáneos y una base MySQL exclusiva de pruebas. Tampoco estas pruebas verifican impresión física, gráficos en navegador ni tiempos de respuesta en el local.
