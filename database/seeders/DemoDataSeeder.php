<?php

namespace Database\Seeders;

use App\Models\Caja;
use App\Models\Cliente;
use App\Models\Compra;
use App\Models\CompraLinea;
use App\Models\Configuracion;
use App\Models\Fichaje;
use App\Models\Merma;
use App\Models\MovimientoStock;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\SesionCaja;
use App\Models\User;
use App\Models\Venta;
use App\Models\VentaLinea;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Demo data seeder.
 * Genera 10+ registros por módulo con fechas distribuidas en los últimos 30 días
 * para que el dashboard refleje datos en KPIs y gráficos.
 *
 * Ejecutar con: php artisan db:seed --class=DemoDataSeeder
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🥖 Generando datos de demostración...');

        DB::transaction(function () {
            $this->actualizarConfiguracion();
            $this->ampliarClientes();
            $this->ampliarProveedores();
            $this->crearCompras();
            $this->crearVentas();
            $this->crearMermas();
            $this->crearFichajes();
        });

        $this->command->info('✅ Datos de demostración generados.');
    }

    protected function actualizarConfiguracion(): void
    {
        // Asegurar configuración base con moneda Soles (S/)
        $config = Configuracion::firstOrCreate(['id' => 1]);
        if (!$config->moneda_simbolo || $config->moneda_simbolo === '€') {
            $config->update([
                'moneda_codigo' => 'PEN',
                'moneda_simbolo' => 'S/',
                'moneda_posicion' => 'izquierda',
                'separador_miles' => ',',
                'separador_decimales' => '.',
            ]);
        }
        // Caja por defecto
        Caja::firstOrCreate(['id' => 1], ['nombre' => 'Caja Principal', 'activa' => true]);
    }

    protected function ampliarClientes(): void
    {
        $extras = [
            ['nombre' => 'Café Aroma Andino', 'cif_nif' => '20512345678', 'email' => 'contacto@aromaandino.pe', 'telefono' => '987654321', 'ciudad' => 'Lima', 'descuento' => 8],
            ['nombre' => 'Hotel Boutique San Isidro', 'cif_nif' => '20587654321', 'email' => 'compras@sanisidro.pe', 'telefono' => '987111222', 'ciudad' => 'Lima', 'descuento' => 12],
            ['nombre' => 'Pedro Quispe', 'telefono' => '999111222', 'ciudad' => 'Arequipa'],
            ['nombre' => 'Lucía Mendoza', 'telefono' => '999333444', 'ciudad' => 'Cusco'],
            ['nombre' => 'Carlos Vargas', 'cif_nif' => '12345678', 'telefono' => '999555666', 'ciudad' => 'Lima'],
            ['nombre' => 'Restaurante La Esquina', 'cif_nif' => '20611223344', 'email' => 'info@laesquina.pe', 'telefono' => '987222333', 'ciudad' => 'Lima', 'descuento' => 10],
            ['nombre' => 'Ana Rodríguez', 'telefono' => '988111222', 'ciudad' => 'Trujillo'],
            ['nombre' => 'Miguel Salazar', 'telefono' => '988333444', 'ciudad' => 'Lima'],
        ];
        foreach ($extras as $c) {
            Cliente::firstOrCreate(['nombre' => $c['nombre']], $c + ['activo' => true]);
        }
    }

    protected function ampliarProveedores(): void
    {
        $extras = [
            ['nombre' => 'Molinos San Martín', 'cif_nif' => '20100200300', 'email' => 'ventas@molinossm.pe', 'telefono' => '988123456', 'contacto' => 'Roberto Silva', 'dias_entrega' => 'L, J', 'ciudad' => 'Lima'],
            ['nombre' => 'Frutas y Sabores SAC', 'cif_nif' => '20200300400', 'email' => 'pedidos@frutasysabores.pe', 'telefono' => '988234567', 'contacto' => 'Patricia Cruz', 'dias_entrega' => 'M, V', 'ciudad' => 'Lima'],
            ['nombre' => 'Embalajes del Sur', 'cif_nif' => '20300400500', 'email' => 'contacto@embsur.pe', 'telefono' => '988345678', 'contacto' => 'Diego Ramos', 'dias_entrega' => 'X', 'ciudad' => 'Arequipa'],
            ['nombre' => 'Cacao Premium Perú', 'cif_nif' => '20400500600', 'email' => 'info@cacaopremium.pe', 'telefono' => '988456789', 'contacto' => 'Laura Vega', 'dias_entrega' => 'L, V', 'ciudad' => 'Lima'],
            ['nombre' => 'Distribuidora Andina', 'cif_nif' => '20500600700', 'email' => 'ventas@andina.pe', 'telefono' => '988567890', 'contacto' => 'Sergio Pérez', 'dias_entrega' => 'Diario', 'ciudad' => 'Lima'],
            ['nombre' => 'Granos del Altiplano', 'cif_nif' => '20600700800', 'email' => 'pedidos@granos.pe', 'telefono' => '988678901', 'contacto' => 'Carmen Lozano', 'dias_entrega' => 'J', 'ciudad' => 'Puno'],
            ['nombre' => 'Bebidas Naturales SAC', 'cif_nif' => '20700800900', 'email' => 'ventas@bebidasnaturales.pe', 'telefono' => '988789012', 'contacto' => 'Jorge Castro', 'dias_entrega' => 'M, J, S', 'ciudad' => 'Lima'],
        ];
        foreach ($extras as $p) {
            Proveedor::firstOrCreate(['nombre' => $p['nombre']], $p + ['activo' => true]);
        }
    }

    protected function crearCompras(): void
    {
        $proveedores = Proveedor::all();
        $productos = Producto::where('tipo', 'materia_prima')->get();
        if ($productos->isEmpty()) $productos = Producto::all()->take(5);
        $admin = User::where('rol', 'admin')->first() ?? User::first();

        $numCompras = max(0, 10 - Compra::count());
        for ($i = 0; $i < $numCompras; $i++) {
            $fecha = Carbon::now()->subDays(rand(2, 28))->setTime(rand(8, 11), rand(0, 59));
            $proveedor = $proveedores->random();
            $numero = 'C-' . $fecha->year . '-' . str_pad(Compra::count() + 1, 5, '0', STR_PAD_LEFT);

            $subtotal = 0;
            $impuestos = 0;
            $lineasData = [];

            $cantidadProductos = rand(2, 5);
            foreach ($productos->random(min($cantidadProductos, $productos->count())) as $producto) {
                $cant = rand(5, 50);
                $precio = (float) $producto->precio_compra ?: rand(50, 500) / 100;
                $sub = $cant * $precio;
                $iva = $sub * ($producto->iva / 100);
                $subtotal += $sub;
                $impuestos += $iva;
                $lineasData[] = [
                    'producto_id' => $producto->id,
                    'descripcion' => $producto->nombre,
                    'cantidad' => $cant,
                    'cantidad_recibida' => $cant,
                    'precio_unitario' => $precio,
                    'iva' => $producto->iva,
                    'subtotal' => $sub,
                    'total' => $sub + $iva,
                ];
            }

            $compra = Compra::create([
                'numero' => $numero,
                'referencia_proveedor' => 'REF-' . rand(1000, 9999),
                'fecha' => $fecha,
                'fecha_recepcion' => $fecha->copy()->addDays(rand(1, 3)),
                'proveedor_id' => $proveedor->id,
                'user_id' => $admin->id,
                'subtotal' => $subtotal,
                'impuestos' => $impuestos,
                'total' => $subtotal + $impuestos,
                'estado' => 'recibida',
                'observaciones' => 'Pedido demo',
            ]);

            foreach ($lineasData as $linea) {
                CompraLinea::create(array_merge($linea, ['compra_id' => $compra->id]));
                // Aumentar stock
                $producto = Producto::find($linea['producto_id']);
                if ($producto && $producto->controla_stock) {
                    $stockAnt = $producto->stock_actual;
                    $producto->increment('stock_actual', $linea['cantidad']);
                    MovimientoStock::create([
                        'producto_id' => $producto->id,
                        'tipo' => 'compra',
                        'cantidad' => $linea['cantidad'],
                        'stock_anterior' => $stockAnt,
                        'stock_nuevo' => $stockAnt + $linea['cantidad'],
                        'precio_unitario' => $linea['precio_unitario'],
                        'motivo' => 'Recepción compra ' . $numero,
                        'referencia_tipo' => 'compra',
                        'referencia_id' => $compra->id,
                        'user_id' => $admin->id,
                        'fecha' => $compra->fecha_recepcion,
                    ]);
                }
            }
        }
    }

    protected function crearVentas(): void
    {
        $productosVenta = Producto::vendibles()->get();
        if ($productosVenta->isEmpty()) {
            $this->command->warn('No hay productos vendibles. Saltando ventas.');
            return;
        }

        $clientes = Cliente::where('activo', true)->get();
        $usuarios = User::where('activo', true)->whereIn('rol', ['admin', 'encargado', 'vendedor'])->get();
        $config = Configuracion::actual();
        $admin = $usuarios->where('rol', 'admin')->first() ?? $usuarios->first();

        // Sesión de caja para hoy
        $sesion = SesionCaja::firstOrCreate(
            ['caja_id' => 1, 'user_id' => $admin->id, 'estado' => 'abierta'],
            ['apertura' => Carbon::today()->setTime(7, 0), 'saldo_inicial' => 100]
        );

        $formasPago = ['efectivo', 'efectivo', 'efectivo', 'tarjeta', 'tarjeta', 'bizum', 'transferencia'];
        $correlativo = Venta::count() + 1;

        // Genera ventas distribuidas en los últimos 14 días
        // Para que el gráfico de 7 días tenga datos diarios y el mes acumule
        for ($dia = 14; $dia >= 0; $dia--) {
            // Más ventas los días recientes para tendencia ascendente
            $numVentas = $dia <= 6 ? rand(3, 6) : rand(1, 3);

            for ($v = 0; $v < $numVentas; $v++) {
                $fecha = Carbon::today()->subDays($dia)
                    ->setTime(rand(7, 20), rand(0, 59), rand(0, 59));

                $numero = sprintf('T-%s-%05d', $fecha->year, $correlativo++);
                $cliente = rand(0, 100) < 30 ? $clientes->random() : null;
                $vendedor = $usuarios->random();
                $formaPago = $formasPago[array_rand($formasPago)];

                $subtotal = 0;
                $impuestos = 0;
                $descuentoTotal = 0;
                $lineasData = [];

                $numLineas = rand(1, 5);
                $productosVenta_aleatorios = $productosVenta->random(min($numLineas, $productosVenta->count()));

                foreach ($productosVenta_aleatorios as $producto) {
                    $cant = rand(1, 5);
                    $precio = (float) $producto->precio_venta;
                    $linea = $cant * $precio;

                    if ($config->precios_con_impuestos) {
                        $base = $linea / (1 + $producto->iva / 100);
                        $iva = $linea - $base;
                    } else {
                        $base = $linea;
                        $iva = $linea * $producto->iva / 100;
                    }

                    $subtotal += $base;
                    $impuestos += $iva;

                    $lineasData[] = [
                        'producto_id' => $producto->id,
                        'descripcion' => $producto->nombre,
                        'cantidad' => $cant,
                        'precio_unitario' => $precio,
                        'iva' => $producto->iva,
                        'descuento' => 0,
                        'subtotal' => $linea,
                        'total' => $linea,
                    ];
                }

                $total = $subtotal + $impuestos;

                $importeEfectivo = $formaPago === 'efectivo' ? $total : 0;
                $importeTarjeta = $formaPago === 'tarjeta' ? $total : 0;
                $importeOtros = !in_array($formaPago, ['efectivo', 'tarjeta']) ? $total : 0;

                $venta = Venta::create([
                    'numero' => $numero,
                    'tipo_documento' => $cliente && rand(0, 100) < 20 ? 'factura' : 'ticket',
                    'serie' => 'T',
                    'correlativo' => $correlativo - 1,
                    'fecha' => $fecha,
                    'cliente_id' => $cliente?->id,
                    'user_id' => $vendedor->id,
                    'sesion_caja_id' => $dia === 0 ? $sesion->id : null,
                    'subtotal' => $subtotal,
                    'descuento' => $descuentoTotal,
                    'base_imponible' => $subtotal,
                    'impuestos' => $impuestos,
                    'total' => $total,
                    'forma_pago' => $formaPago,
                    'importe_efectivo' => $importeEfectivo,
                    'importe_tarjeta' => $importeTarjeta,
                    'importe_otros' => $importeOtros,
                    'cambio' => $formaPago === 'efectivo' ? rand(0, 200) / 100 : 0,
                    'estado' => 'pagada',
                ]);

                foreach ($lineasData as $linea) {
                    VentaLinea::create(array_merge($linea, ['venta_id' => $venta->id]));

                    $producto = Producto::find($linea['producto_id']);
                    if ($producto && $producto->controla_stock) {
                        $stockAnt = $producto->stock_actual;
                        $producto->decrement('stock_actual', $linea['cantidad']);
                        MovimientoStock::create([
                            'producto_id' => $producto->id,
                            'tipo' => 'venta',
                            'cantidad' => -$linea['cantidad'],
                            'stock_anterior' => $stockAnt,
                            'stock_nuevo' => $stockAnt - $linea['cantidad'],
                            'precio_unitario' => $linea['precio_unitario'],
                            'motivo' => 'Venta ' . $numero,
                            'referencia_tipo' => 'venta',
                            'referencia_id' => $venta->id,
                            'user_id' => $vendedor->id,
                            'fecha' => $fecha,
                        ]);
                    }
                }
            }
        }

        // Actualizar el siguiente correlativo en configuración
        $config->update(['siguiente_ticket' => $correlativo]);
    }

    protected function crearMermas(): void
    {
        $productos = Producto::where('controla_stock', true)->where('activo', true)->get();
        $admin = User::where('rol', 'admin')->first() ?? User::first();
        $motivos = ['caducidad', 'rotura', 'mal_estado', 'error_elaboracion', 'devolucion_cliente'];

        $numMermas = max(0, 10 - Merma::count());
        for ($i = 0; $i < $numMermas; $i++) {
            $producto = $productos->random();
            $cantidad = rand(1, 5);
            $fecha = Carbon::now()->subDays(rand(0, 14))->setTime(rand(17, 21), rand(0, 59));
            $coste = $cantidad * $producto->precio_compra;
            $motivo = $motivos[array_rand($motivos)];

            $merma = Merma::create([
                'producto_id' => $producto->id,
                'cantidad' => $cantidad,
                'motivo' => $motivo,
                'coste' => $coste,
                'observaciones' => 'Merma de cierre del día',
                'user_id' => $admin->id,
                'fecha' => $fecha,
            ]);

            if ($producto->controla_stock) {
                $stockAnt = $producto->stock_actual;
                $producto->decrement('stock_actual', $cantidad);
                MovimientoStock::create([
                    'producto_id' => $producto->id,
                    'tipo' => 'merma',
                    'cantidad' => -$cantidad,
                    'stock_anterior' => $stockAnt,
                    'stock_nuevo' => $stockAnt - $cantidad,
                    'motivo' => 'Merma: ' . $motivo,
                    'user_id' => $admin->id,
                    'fecha' => $fecha,
                ]);
            }
        }
    }

    protected function crearFichajes(): void
    {
        $empleados = User::where('activo', true)->whereIn('rol', ['encargado', 'vendedor', 'obrador'])->get();
        if ($empleados->isEmpty()) {
            $empleados = User::where('activo', true)->get();
        }

        // Generar fichajes de los últimos 10 días laborales
        $numFichajes = max(0, 10 - Fichaje::count());
        $generados = 0;

        for ($dia = 1; $dia <= 14 && $generados < $numFichajes; $dia++) {
            $fecha = Carbon::today()->subDays($dia);
            if ($fecha->isSunday()) continue; // No trabajo en domingo

            foreach ($empleados as $empleado) {
                if ($generados >= $numFichajes) break;
                $entrada = $fecha->copy()->setTime(rand(6, 7), rand(0, 30));
                $horasTrabajadas = rand(7, 9) + (rand(0, 60) / 100);
                $salida = $entrada->copy()->addMinutes((int)($horasTrabajadas * 60));

                Fichaje::create([
                    'user_id' => $empleado->id,
                    'entrada' => $entrada,
                    'salida' => $salida,
                    'horas' => round($horasTrabajadas, 2),
                    'tipo' => 'jornada',
                    'ip' => '127.0.0.1',
                ]);
                $generados++;
            }
        }
    }
}
