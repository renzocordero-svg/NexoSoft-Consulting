# Verificaciones pendientes del entorno de entrega

Este registro complementa las pruebas automatizadas. Todas las comprobaciones de esta tabla permanecen sin ejecutar. Para aprobarlas se debe registrar el ejecutor, fecha real, revisión del código, entorno, resultado observado y ruta a la evidencia. No se consideran aprobadas por la existencia de este documento.

| ID | Comprobación | Estado |
| --- | --- | --- |
| CP-14 | Venta simultánea de la última unidad sobre MySQL | Pendiente: no hay servidor MySQL ni Docker disponible en el entorno revisado |
| CP-16 manual | Vista previa, impresión y guardado de factura como PDF | Pendiente: falta verificación en navegador |
| CP-28 manual | Gráficos visibles y legibles en navegador | Pendiente: falta verificación en navegador |
| CP-29 | Tiempo de respuesta de reporte mensual | Pendiente: falta medición en el entorno de entrega |
| RNF de venta | Tiempo desde confirmación hasta mostrar factura | Pendiente: falta medición en el entorno de entrega |

## CP-14 sobre MySQL

Usar una instalación desechable de FERREMAX conectada a una base MySQL exclusiva de pruebas. Identificar la revisión exacta de develop y comprobar la conexión seleccionada antes de cargar datos. No utilizar la base operativa de la ferretería. Emplear un servidor que atienda solicitudes en paralelo; una ejecución secuencial o un servidor que serializa peticiones no verifica esta condición.

1. Preparar dos usuarios vendedores activos y un producto activo con exactamente una unidad. Registrar los saldos iniciales y el correlativo actual. Usar un motivo identificable para la entrada inicial.
2. Autenticar a los vendedores en sesiones independientes. Preparar en ambas el carrito con una unidad del mismo producto.
3. Lanzar las dos confirmaciones desde procesos independientes mediante una barrera común. Registrar el inicio y el fin de ambas solicitudes y obtener evidencia del solapamiento de las operaciones y de la espera del bloqueo en MySQL. Dos clics aproximados por sí solos no demuestran el solapamiento.
4. Comprobar que solo una venta termina correctamente y la otra devuelve un rechazo controlado por falta de stock. El stock final debe ser cero.
5. Comparar con el estado inicial: debe existir exactamente una cabecera, un detalle, una factura y un movimiento de salida nuevos para el escenario. La secuencia de facturas debe avanzar una vez. No debe quedar ningún efecto parcial del intento rechazado.
6. Repetir el escenario con otro producto preparado para la prueba, conservando los resultados de cada intento. Registrar errores de bloqueo, deadlocks o respuestas inesperadas como incidencias, sin descartarlos.

La suite SQLite verifica reversión y límites secuenciales; no certifica los bloqueos de filas de MySQL. Este procedimiento sigue pendiente hasta disponer de la evidencia anterior.

## Factura y reportes en navegador

Con la revisión de entrega, registrar una venta conocida y comprobar la factura como propietario y administrador. Abrir la vista previa de impresión, verificar que no aparezcan navegación ni botones, que todos los productos e importes sean legibles y que el total coincida con la venta. Guardar un PDF de prueba y comprobar visualmente que no haya recortes. Registrar navegador, tamaño de papel, captura y archivo generado.

En reportes, elegir un periodo con ventas conocidas y revisar que los indicadores y gráficos correspondan al filtro. Verificar también periodo vacío, productos más y menos vendidos y exportación mediante impresión. Los recursos cargados por CDN deben estar disponibles en el entorno real. La presencia del botón window.print o de datos de gráficos en HTML no sustituye esta revisión.

## Medición de respuesta

Registrar hardware, motor de base de datos, versión de PHP, revisión y cantidad de datos. Medir por separado desde la confirmación de venta hasta la factura visible y desde la solicitud del reporte hasta su visualización completa. Conservar los tiempos de cada repetición y los errores, diferenciando arranque en frío y solicitudes posteriores.

Contrastar con el criterio acordado: venta hasta 2 segundos y reporte mensual hasta 5 segundos en la matriz original; la hoja de RNF contiene umbrales diferentes para algunos reportes. Resolver esa diferencia con el equipo antes de declarar conformidad global. La duración total de PHPUnit no sirve como medida de estos tiempos.
