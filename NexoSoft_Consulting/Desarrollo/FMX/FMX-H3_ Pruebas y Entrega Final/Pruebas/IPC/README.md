# Evidencias de FMX IPC

El informe principal es `../../FMX_IPC.docx`. La suite de esta carpeta verifica atomicidad con SQLite en memoria, no bloqueos concurrentes de MySQL. Los casos y resultados de concurrencia se mantienen pendientes en el informe hasta ejecutar en un entorno MySQL 8.0 aislado.

Desde la carpeta Pruebas:

```powershell
.\Ejecutar-Pruebas.ps1 -Suite Atomicidad
.\Ejecutar-Pruebas.ps1 -Suite Integracion
.\Ejecutar-Pruebas.ps1 -Suite Regresion
```

Se usan las dependencias de App-ferremax del Hito 2 instaladas con `composer install`. El lanzador guarda log, JUnit y metadatos en Evidencias. No se requiere iniciar el servidor HTTP para estas suites. La configuración IPC fuerza SQLite en memoria y el lanzador impide ejecutar con configuración de Laravel cacheada.

AT-01 comprueba confirmación completa; AT-02, rechazo sin efectos; AT-03, rollback al fallar la factura y reintento; AT-04, rollback de una venta de varios productos cuando falla el segundo movimiento; AT-05, rollback al fallar la auditoría de una entrada; AT-06, conservación de una venta previa al rechazar un intento posterior. Las excepciones simuladas solo existen en las pruebas y se retiran mediante finally.

La captura de tablas compara registros de negocio y la secuencia de facturas; no exige que el contador interno de claves autoincrementales sea recuperable tras un rollback en MySQL.

Para concurrencia: seguir CN-01 a CN-04 del informe con procesos independientes, barrera de inicio y evidencia de solapamiento y espera de bloqueos en MySQL. No ejecutar esas comprobaciones contra una base de producción. El caso de la última unidad de las suites existentes es secuencial y no sustituye CN-01.
