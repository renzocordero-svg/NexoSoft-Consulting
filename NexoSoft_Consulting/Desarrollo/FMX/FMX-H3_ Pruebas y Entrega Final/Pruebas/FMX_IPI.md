# Informe inicial de pruebas de integración de FERREMAX

Se ejecutaron 7 pruebas nuevas de integración con 54 aserciones y la suite existente con 23 pruebas y 124 aserciones. Todas terminaron sin errores ni fallos de aserción. Ambas suites notificaron 4 avisos de obsolescencia. El resultado valida los escenarios descritos sobre SQLite en memoria; no certifica la concurrencia en MySQL ni la aceptación completa del sistema.

## Identificación de la ejecución

Aplicación: App-ferremax de Desarrollo / FMX / Hito 2. Revisión base: `c73e3df6f5acb4fda0aa022eaca68242c01f6a15`. Rama observada: `main`, con los archivos de pruebas nuevos sin commit. Entorno observado: Windows, PHP 8.5.12, PHPUnit 11.5.56, dependencias instaladas desde el lock existente y SQLite `:memory:`. No se modificaron el código de negocio ni el archivo composer.lock.

El reloj del equipo ejecutor registró estas ejecuciones el 10 de noviembre de 2026, con desplazamiento -05:00. Se conservaron sus marcas temporales originales en los nombres y metadatos.

## Resultados del primer bloque

| Escenario | Resultado observado | Estado |
| --- | --- | --- |
| CP-11 / CP-20, venta de varios productos | Subtotal 62.48, IGV 11.25, total 73.73; dos detalles y movimientos con usuario, una factura y stock final 8 y 17. | Conforme en SQLite |
| CP-12, falta de stock | Rechazo y carrito recuperable; no quedaron registros parciales ni se consumió la secuencia. | Conforme en SQLite |
| CP-13, fallo al crear factura | La excepción simulada revirtió venta, detalles, movimientos, stock y correlativo. La siguiente venta válida emitió FACT-000001. | Conforme en SQLite |
| CP-15, correlativos | Tres ventas sucesivas emitieron FACT-000001, FACT-000002 y FACT-000003. | Conforme secuencial |
| CP-17, persistencia histórica | Cambiar el catálogo no alteró precio 25.99, costo 15.50 ni total de factura 61.34 de la venta anterior. | Conforme en persistencia; falta ampliar consulta HTTP |
| Carrito vacío | Validación rechazada, sin registros de venta ni correlativo consumido. | Conforme |
| Última unidad, intentos sucesivos | Una venta válida, segundo intento rechazado y stock final cero. | Conforme secuencial; no equivale a CP-14 |

## Evidencias

Integración: [salida de PHPUnit](Evidencias/20261110-095509-515-Integracion.log), [resultados JUnit](Evidencias/20261110-095509-515-Integracion.junit.xml) y [entorno](Evidencias/20261110-095509-515-Integracion.entorno.json). Resultado: 7 pruebas, 54 aserciones, cero fallos, cero errores y código de salida 0.

Regresión con detalle de los avisos: [salida de PHPUnit](Evidencias/20261110-095602-106-Regresion.log), [resultados JUnit](Evidencias/20261110-095602-106-Regresion.junit.xml) y [entorno](Evidencias/20261110-095602-106-Regresion.entorno.json). Resultado: 23 pruebas, 124 aserciones, cero fallos, cero errores y código de salida 0. La suite incluye dos pruebas de ejemplo; su número no equivale al número de requisitos verificados.

Se conserva además la primera regresión, `20261110-095524-766-Regresion`, con el mismo número de pruebas y aserciones. La repetición posterior se hizo para mostrar y registrar el origen de los avisos, no para aumentar la cobertura.

## Incidencias y seguimiento

| ID | Observación y reproducción | Impacto | Seguimiento |
| --- | --- | --- | --- |
| OBS-01 | PHPUnit notifica el uso obsoleto de `PDO::MYSQL_ATTR_SSL_CA` con PHP 8.5 en `config/database.php`, líneas 61 y 81, y en los mismos puntos de la configuración del framework. Ejecutar la regresión con `--display-deprecations` reproduce los cuatro avisos. | No produjo fallos de aserción. Existe una diferencia entre el PHP local y el PHP 8.2 documentado para la entrega. | Repetir en el entorno de entrega. Si se adopta PHP 8.5, evaluar el ajuste del código y de la dependencia con el equipo. No se modificó vendor. |
| ENT-01 | El primer intento, `20261110-095420-109-Integracion`, no inició los casos: el sandbox devolvió `is_readable=false` al comprobar el bootstrap, aunque permitía leer el contenido. | Incidencia del entorno ejecutor, sin resultado funcional. Código 2; no se generó JUnit. | Resuelta ejecutando PHPUnit fuera del sandbox con autorización. Se conserva el log y los metadatos del intento. |

No se observaron defectos funcionales en los escenarios ejecutados. Esto no implica ausencia de defectos en escenarios pendientes.

## Alcance pendiente de verificación

Quedan sin certificar la concurrencia real, los tiempos de respuesta del establecimiento, la impresión y los gráficos en navegador, y el despliegue Docker/MySQL. El tiempo total que muestra PHPUnit no se usa como evidencia del tiempo de respuesta de una venta o reporte.

