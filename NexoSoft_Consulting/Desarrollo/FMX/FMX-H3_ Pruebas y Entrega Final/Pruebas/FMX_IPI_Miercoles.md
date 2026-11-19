# Resultados de integración del miércoles

El 11 de noviembre de 2026 se añadieron cinco pruebas de conciliación y recuperación, con 29 aserciones. Pasaron las 21 pruebas de Hito 3, con 166 aserciones, y las 23 pruebas de regresión existentes, con 124 aserciones. No hubo fallos ni errores; ambas suites notificaron los cuatro avisos de obsolescencia de PHP 8.5 ya documentados. El resultado corresponde a la rama personal; la entrega integrada aún requiere las verificaciones indicadas al final.

## Revisión y entorno

Rama: `tests-jfranm`. Revisión base: `5c517a6cd477c84343c7f519634985f765acd280`, con las pruebas del miércoles sin commit. Windows, PHP 8.5.12, PHPUnit 11.5.56 y SQLite en memoria. Se probó App-ferremax de Desarrollo / FMX / Hito 2, sin modificar su código de negocio ni la línea base.

Al consultar GitHub, develop estaba en `aa047190275e2c2061e9435ca9cae7797bf1a5fb`. No se incorporó esa revisión a la rama ni se ejecutaron pruebas contra ella. Por tanto, este informe no certifica el contenido actual de develop ni de main. Las horas y revisiones exactas de ejecución constan en los metadatos.

## Casos añadidos

| Escenario | Resultado observado |
| --- | --- |
| Compra, venta y ajuste conciliados con reporte y bitácora | Entrada de 10 unidades, venta de 3 y ajuste de salida de 2: stock final y saldo reconstruido de movimientos iguales a 5. Factura 35.40, ganancia 12.00 y tres movimientos auditados. La consulta HTTP de movimientos muestra los tres registros. |
| Ingresos y margen históricos con filtro de vendedor | Dos ventas por 2 y 3 unidades a precio 10 y costo 6: ingresos 59.00, ganancia 20.00 y ticket promedio 29.50. Tras cambiar el catálogo se conservan los resultados. Facturas y serie diaria concilian con los ingresos; al filtrar al primer vendedor, ingresos 23.60 y margen 8.00. |
| Ranking con siete productos vendidos y uno sin ventas | Se muestran los cinco primeros y los cinco últimos según las cantidades, en el orden esperado. El producto sin ventas queda excluido del ranking de menos vendidos. |
| Fallo al registrar auditoría de inventario | Una excepción simulada al crear el movimiento revierte el incremento de stock: saldo 10, sin movimiento parcial. Tras retirar la simulación, la entrada válida deja saldo 15 y un movimiento. |
| Periodo sin ventas y permisos de reportes | Indicadores y serie diaria en cero, rankings vacíos y sin división por cero. La consulta HTTP de reportes de un vendedor recibe 403. |

Los escenarios amplían CP-20 y CP-26 a CP-28 de la matriz original, junto con la integridad de inventario. No equivalen a una comprobación exhaustiva de todos los requisitos.

## Evidencias de esta jornada

Hito 3: [log](Evidencias/20261111-203801-021-Integracion.log), [JUnit](Evidencias/20261111-203801-021-Integracion.junit.xml) y [entorno](Evidencias/20261111-203801-021-Integracion.entorno.json). Resultado: 21 pruebas, 166 aserciones, cero errores, cero fallos y salida 0. Desglose: 7 pruebas iniciales con 54 aserciones, 9 del martes con 83 y 5 del miércoles con 29.

Regresión: [log](Evidencias/20261111-203857-316-Regresion.log), [JUnit](Evidencias/20261111-203857-316-Regresion.junit.xml) y [entorno](Evidencias/20261111-203857-316-Regresion.entorno.json). Resultado: 23 pruebas, 124 aserciones, cero errores, cero fallos y salida 0. Incluye dos pruebas de ejemplo existentes, por lo que el recuento no representa requisitos cubiertos.

## Estado para la entrega

No se observaron defectos funcionales en los escenarios ejecutados. Se mantienen los avisos por `PDO::MYSQL_ATTR_SSL_CA` con PHP 8.5 en la configuración de la aplicación y del framework. Es necesario repetir sobre PHP y base de datos del entorno de entrega.

No se encontró Docker, cliente mysql ni servidor mysqld en las ubicaciones locales revisadas. No se ejecutó CP-14: SQLite y las ventas sucesivas no demuestran la concurrencia de MySQL. Tampoco se ejecutaron pruebas visuales en navegador ni de impresión física. Los tiempos totales de PHPUnit no se usan para aprobar requisitos de rendimiento.

El [registro de verificaciones pendientes](FMX_Verificacion_Manual.md) contiene los pasos, resultados esperados y evidencia necesaria para concurrencia, impresión, gráficos y tiempos de respuesta. Estas verificaciones permanecen pendientes; no se declara cerrado el Hito 3.

Después de integrar mediante PR en develop, deben repetirse las suites sobre esa revisión y registrarse sus resultados. No se crearon commits, PR, merges ni publicaciones desde esta preparación.
