<?php

namespace Tests\Hito3;

use App\Models\Venta;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ConsultaFacturaHistorialTest extends TestCase
{
    use RefreshDatabase;

    public function createApplication()
    {
        $app = require FMX_APP_PATH.'/bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    public function test_cp16_consultar_factura_muestra_detalle_importes_y_no_modifica_datos(): void
    {
        $vendedor = $this->usuario('vendedor', 'vendedor_factura');
        $producto = $this->producto(['nombre' => 'Martillo de prueba']);
        $venta = $this->vender($vendedor, $producto, 2);
        $antes = $this->estadoPersistente();

        $this->actingAs($vendedor)->get('/ventas/'.$venta->id)
            ->assertOk()->assertViewIs('ventas.show')
            ->assertSee($venta->factura->numero_factura)
            ->assertSee('vendedor_factura')->assertSee('Martillo de prueba')
            ->assertSee('$25.99', false)->assertSee('$51.98', false)
            ->assertSee('IGV (18%)')->assertSee('$9.36', false)->assertSee('$61.34', false)
            ->assertSee('window.print()', false)
            ->assertViewHas('venta', fn ($dato) => $dato->id === $venta->id
                && $dato->detalles->sole()->cantidad === 2);

        $this->get('/ventas/'.$venta->id)->assertOk();
        $this->get('/ventas')->assertOk();
        $this->assertSame($antes, $this->estadoPersistente());
    }

    public function test_cp17_factura_http_conserva_importes_tras_cambiar_precio_y_costo(): void
    {
        $vendedor = $this->usuario('vendedor', 'vendedor_historico');
        $producto = $this->producto();
        $venta = $this->vender($vendedor, $producto, 2);
        $producto->update(['precio_unitario' => '999.00', 'precio_costo' => '888.00']);

        $this->actingAs($vendedor)->get('/ventas/'.$venta->id)
            ->assertOk()->assertSee('$25.99', false)->assertSee('$51.98', false)
            ->assertSee('$9.36', false)->assertSee('$61.34', false)
            ->assertDontSee('$999.00', false)->assertDontSee('$888.00', false)
            ->assertViewHas('venta', fn ($dato) => $dato->detalles->sole()->costo_unitario_momento === '15.50');
    }

    public function test_cp18_propietario_y_administrador_acceden_pero_otro_vendedor_no(): void
    {
        $propietario = $this->usuario('vendedor', 'propietario');
        $otro = $this->usuario('vendedor', 'otro_vendedor');
        $admin = $this->usuario();
        $venta = $this->vender($propietario, $this->producto(['nombre' => 'Producto reservado']));
        $url = '/ventas/'.$venta->id;

        $this->actingAs($propietario)->get($url)->assertOk()->assertSee($venta->factura->numero_factura);
        $this->actingAs($otro)->get($url)->assertForbidden()
            ->assertDontSee($venta->factura->numero_factura)->assertDontSee('Producto reservado');
        $this->actingAs($admin)->get($url)->assertOk()->assertSee($venta->factura->numero_factura);
    }

    public function test_consultas_exigen_sesion_y_rechazan_una_cuenta_desactivada(): void
    {
        $vendedor = $this->usuario('vendedor', 'cuenta_desactivada');
        $venta = $this->vender($vendedor, $this->producto());
        foreach (['/ventas', '/ventas/'.$venta->id] as $url) {
            $this->get($url)->assertRedirect('/login');
        }

        $vendedor->update(['activo' => false]);
        foreach (['/ventas', '/ventas/'.$venta->id] as $url) {
            $this->actingAs($vendedor)->get($url)->assertRedirect('/login');
            $this->assertGuest();
        }
    }

    public function test_cp18_vendedor_no_puede_ampliar_historial_al_suplantar_el_filtro(): void
    {
        $propietario = $this->usuario('vendedor', 'ventas_propias');
        $otro = $this->usuario('vendedor', 'ventas_ajenas');
        $producto = $this->producto();
        $propia = $this->vender($propietario, $producto);
        $ajena = $this->vender($otro, $producto);

        $this->actingAs($propietario)->get('/ventas?usuario_id='.$otro->id)
            ->assertOk()->assertSee($propia->factura->numero_factura)
            ->assertDontSee($ajena->factura->numero_factura)
            ->assertViewHas('ventas', fn ($ventas) => $ventas->total() === 1
                && $ventas->pluck('id')->all() === [$propia->id]);

        $sinVentas = $this->usuario('vendedor', 'sin_ventas');
        $this->actingAs($sinVentas)->get('/ventas?usuario_id='.$propietario->id)
            ->assertOk()->assertDontSee($propia->factura->numero_factura)
            ->assertDontSee($ajena->factura->numero_factura)
            ->assertViewHas('ventas', fn ($ventas) => $ventas->total() === 0);
    }

    public function test_cp19_historial_combina_vendedor_y_fechas_incluyendo_ambos_limites(): void
    {
        $admin = $this->usuario();
        $vendedor = $this->usuario('vendedor', 'filtrado');
        $otro = $this->usuario('vendedor', 'excluido');
        $producto = $this->producto(['cantidad_stock' => 30]);
        $inicio = $this->ventaEn($vendedor, $producto, '2026-11-01 00:00:00');
        $fin = $this->ventaEn($vendedor, $producto, '2026-11-02 23:59:59');
        $anterior = $this->ventaEn($vendedor, $producto, '2026-10-31 23:59:59');
        $posterior = $this->ventaEn($vendedor, $producto, '2026-11-03 00:00:00');
        $ajena = $this->ventaEn($otro, $producto, '2026-11-01 12:00:00');
        $query = http_build_query(['fecha_desde' => '2026-11-01', 'fecha_hasta' => '2026-11-02', 'usuario_id' => $vendedor->id]);

        $respuesta = $this->actingAs($admin)->get('/ventas?'.$query)
            ->assertOk()->assertSee($inicio->factura->numero_factura)->assertSee($fin->factura->numero_factura)
            ->assertViewHas('ventas', fn ($ventas) => $ventas->total() === 2
                && $ventas->pluck('id')->all() === [$fin->id, $inicio->id]);
        foreach ([$anterior, $posterior, $ajena] as $excluida) {
            $respuesta->assertDontSee($excluida->factura->numero_factura);
        }
    }

    public function test_cp19_filtros_se_conservan_al_avanzar_pagina_sin_incluir_otras_ventas(): void
    {
        $admin = $this->usuario();
        $vendedor = $this->usuario('vendedor', 'paginado');
        $otro = $this->usuario('vendedor', 'fuera_de_pagina');
        $producto = $this->producto(['cantidad_stock' => 40]);
        $ids = [];
        for ($i = 0; $i < 16; $i++) {
            $ids[] = $this->ventaEn($vendedor, $producto, '2026-11-02 12:00:00')->id;
        }
        $ajena = $this->ventaEn($otro, $producto, '2026-11-02 12:00:00');
        $fuera = $this->ventaEn($vendedor, $producto, '2026-11-03 12:00:00');
        $filtros = ['fecha_desde' => '2026-11-02', 'fecha_hasta' => '2026-11-02', 'usuario_id' => $vendedor->id];
        $url = '/ventas?'.http_build_query($filtros);
        $paginaUno = $this->actingAs($admin)->get($url)->assertOk();
        $paginador = $paginaUno->viewData('ventas');
        $this->assertSame(16, $paginador->total());
        $this->assertSame(15, $paginador->count());
        $this->assertSame(array_reverse(array_slice($ids, 1)), $paginador->pluck('id')->all());
        parse_str(parse_url($paginador->nextPageUrl(), PHP_URL_QUERY), $querySiguiente);
        foreach ($filtros as $campo => $valor) {
            $this->assertSame((string) $valor, $querySiguiente[$campo]);
        }
        $this->assertSame('2', $querySiguiente['page']);
        $paginaDos = $this->get($paginador->nextPageUrl())->assertOk();
        $this->assertSame([$ids[0]], $paginaDos->viewData('ventas')->pluck('id')->all());
        foreach ([$paginaUno, $paginaDos] as $respuesta) {
            $respuesta->assertDontSee($ajena->factura->numero_factura)->assertDontSee($fuera->factura->numero_factura);
        }
    }

    public function test_cp19_rango_invalido_o_vendedor_inexistente_devuelve_validacion(): void
    {
        $this->actingAs($this->usuario());
        $this->getJson('/ventas?fecha_desde=2026-11-03&fecha_hasta=2026-11-01')
            ->assertUnprocessable()->assertJsonValidationErrors('fecha_hasta');
        $this->getJson('/ventas?fecha_desde=fecha-invalida')
            ->assertUnprocessable()->assertJsonValidationErrors('fecha_desde');
        $this->getJson('/ventas?usuario_id=999999')
            ->assertUnprocessable()->assertJsonValidationErrors('usuario_id');
        $this->assertDatabaseCount('ventas', 0);
        $this->assertDatabaseCount('facturas', 0);
    }

    public function test_factura_inexistente_devuelve_404_para_usuario_autenticado(): void
    {
        $this->actingAs($this->usuario())->get('/ventas/999999')->assertNotFound();
        $this->assertDatabaseCount('ventas', 0);
        $this->assertDatabaseCount('facturas', 0);
    }

    private function ventaEn($usuario, $producto, string $fecha): Venta
    {
        $venta = $this->vender($usuario, $producto);
        $venta->update(['fecha_hora' => $fecha]);

        return $venta->fresh(['factura']);
    }

    private function estadoPersistente(): array
    {
        $estado = [];
        foreach (['users', 'productos', 'ventas', 'detalle_ventas', 'facturas', 'movimiento_stocks'] as $tabla) {
            $estado[$tabla] = DB::table($tabla)->orderBy('id')->get()->map(fn ($fila) => (array) $fila)->all();
        }
        $estado['secuencias'] = DB::table('secuencias')->orderBy('nombre')->get()->map(fn ($fila) => (array) $fila)->all();

        return $estado;
    }
}
