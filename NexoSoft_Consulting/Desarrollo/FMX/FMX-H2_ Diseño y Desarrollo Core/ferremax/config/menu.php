<?php

/*
|--------------------------------------------------------------------------
| Menú lateral por rol (CUS-01, paso 9)
|--------------------------------------------------------------------------
|
| Cada opción indica qué roles la ven. Al implementar un caso de uso, agregue
| aquí su opción; solo se muestra si la ruta existe y el rol del usuario está
| en la lista. Ejemplo:
|
|   ['titulo' => 'Productos', 'ruta' => 'productos.index',
|    'icono' => 'bi-box-seam', 'roles' => ['admin']],
|
*/

return [
    ['titulo' => 'Inicio', 'ruta' => 'dashboard', 'icono' => 'bi-speedometer2', 'roles' => ['admin', 'vendedor']],
];
