# Ampliación de pruebas de facturas e historial del martes

El martes 10 de noviembre de 2026 se añadieron nueve pruebas HTTP de consulta de facturas, permisos e historial. Las nueve pasaron con 83 aserciones. Junto con las siete pruebas de integración anteriores, la suite del Hito 3 ejecutó 16 pruebas y 137 aserciones, sin errores ni fallos. La regresión existente ejecutó 23 pruebas y 124 aserciones, también sin errores ni fallos. Ambas ejecuciones notificaron los cuatro avisos de obsolescencia ya identificados con PHP 8.5.

## Alcance de esta contribución

Se añadió `tests/ConsultaFacturaHistorialTest.php` dentro de Desarrollo / FMX / Hito 3 / Pruebas. Prueba mediante solicitudes HTTP la aplicación del Hito 2. No modifica controladores, vistas, rutas, migraciones ni dependencias. Se mantienen los siete casos anteriores y su informe como registro de la primera ejecución.

La rama comprobada localmente y en GitHub fue `tests-jfranm`, con revisión base `ac9e391ceddb9e8424372ebb81702e72f73bee08` y los cambios de esta ampliación aún sin commit. Las evidencias describen esta combinación de revisión y archivos pendientes; no corresponden a un merge posterior en develop.

Entorno ejecutor: Windows, PHP 8.5.12, PHPUnit 11.5.56 y SQLite en memoria. Las fechas de ventas usadas en los escenarios son datos ficticios de la base de pruebas. Las marcas temporales de las evidencias son las reales del equipo ejecutor.

## Escenarios ejecutados

| Caso | Comprobación | Resultado observado |
| --- | --- | --- |
| CP-16, factura y lectura sin efectos | Consultar dos veces la factura y abrir el historial; comparar las tablas de negocio antes y después. | Se muestran producto, cantidad, vendedor, factura, precio 25.99, subtotal 51.98, IGV 9.36 y total 61.34. Las consultas no alteran los registros. El HTML contiene la acción de impresión. |
| CP-17, importes históricos en HTTP | Cambiar precio a 999.00 y costo a 888.00 tras vender, y consultar la factura. | Se mantienen en pantalla los importes originales; los nuevos importes no aparecen. El detalle conserva costo histórico 15.50. |
| CP-18, acceso directo a factura | Consultar la misma URL como propietario, otro vendedor y administrador. | Propietario y administrador reciben 200. El otro vendedor recibe 403 y no ve número de factura ni producto reservado. |
| Sesión y estado de cuenta | Solicitar factura e historial sin autenticación y con cuenta desactivada. | Ambas rutas redirigen a login. La cuenta desactivada queda sin sesión autenticada. |
| CP-18, filtro manipulado | Pedir el historial de otro vendedor mediante usuario_id, tanto con un vendedor con ventas como con otro sin ventas. | Solo aparecen las ventas propias. El vendedor sin ventas obtiene cero resultados, aunque envíe el identificador de otro. |
| CP-19, fecha y vendedor combinados | Filtrar entre el inicio del 1 de noviembre y el final del 2 de noviembre, con ventas fuera del rango y de otra persona. | Se incluyen ambos días límite y se excluyen las ventas fuera de fechas o del vendedor elegido. El orden es descendente. |
| CP-19, paginación con filtros | Crear 16 ventas del vendedor seleccionado, una ajena y otra fuera de fecha; avanzar por el enlace de página. | La primera página contiene 15 ventas y la segunda una. El enlace conserva fechas y vendedor. Ninguna página incluye las ventas excluidas. |
| CP-19, validación | Enviar fechas invertidas, fecha mal formada y vendedor inexistente. | Las peticiones JSON reciben 422 con el campo correspondiente; no se generan ventas ni facturas. |
| Factura inexistente | Solicitar una venta inexistente con usuario autenticado. | Respuesta 404, sin registros nuevos. |

Los códigos CP remiten a la matriz de trazabilidad. Los escenarios de sesión, paginación y errores amplían la verificación; no se presentan como nuevos requisitos del producto.

## Evidencias de ejecución

Suite del Hito 3: [log](Evidencias/20261110-223801-688-Integracion.log), [JUnit](Evidencias/20261110-223801-688-Integracion.junit.xml) y [metadatos](Evidencias/20261110-223801-688-Integracion.entorno.json). Resultado: 16 pruebas, 137 aserciones, 0 errores, 0 fallos y código de salida 0. El JUnit desglosa las nueve pruebas nuevas y las siete anteriores; las nuevas aportan 83 aserciones.

Regresión del Hito 2: [log](Evidencias/20261110-223857-416-Regresion.log), [JUnit](Evidencias/20261110-223857-416-Regresion.junit.xml) y [metadatos](Evidencias/20261110-223857-416-Regresion.entorno.json). Resultado: 23 pruebas, 124 aserciones, 0 errores, 0 fallos y código de salida 0. El recuento incluye dos pruebas de ejemplo existentes y no equivale al número de requisitos cubiertos.

## Observaciones y límites

No se observaron defectos funcionales en los escenarios añadidos. Se mantienen los cuatro avisos por `PDO::MYSQL_ATTR_SSL_CA` en PHP 8.5, provenientes de la configuración de base de datos de la aplicación y del framework. No se ocultaron los avisos ni se modificó vendor.

El caso CP-17, que en el informe inicial comprobaba persistencia, queda ampliado a su presentación HTTP. Detectar el botón de impresión en HTML no certifica la impresión física, la vista previa ni la generación de PDF. Las pruebas no ejecutan JavaScript en un navegador.

Permanecen pendientes la concurrencia real de CP-14 sobre MySQL, las comprobaciones de rendimiento del establecimiento, la impresión y los gráficos en navegador y la verificación sobre el entorno Docker de entrega. Estos resultados se obtuvieron antes de integrar los cambios del equipo: la suite debe repetirse sobre develop después del merge.
