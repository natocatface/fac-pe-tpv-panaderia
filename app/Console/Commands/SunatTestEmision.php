<?php

namespace App\Console\Commands;

use App\Models\Cliente;
use App\Models\Configuracion;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\VentaLinea;
use App\Services\Sunat\FacturacionElectronicaService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Genera una venta dummy y la envía al ambiente Beta de SUNAT.
 * Útil para verificar end-to-end la integración Greenter.
 *
 *  php artisan sunat:test --tipo=01
 *  php artisan sunat:test --tipo=03
 */
class SunatTestEmision extends Command
{
    protected $signature   = 'sunat:test {--tipo=03 : Tipo SUNAT 01 factura / 03 boleta}';
    protected $description = 'Emite una venta de prueba contra SUNAT (modo Beta).';

    public function handle(): int
    {
        $tipo = $this->option('tipo');
        if (! in_array($tipo, ['01', '03'])) {
            $this->error("Tipo no soportado: {$tipo}");
            return self::INVALID;
        }

        $config = Configuracion::actual();
        if (! $config->sunat_activo) {
            $config->update(['sunat_activo' => true]);
            $this->warn('SUNAT estaba inactivo — activado para esta prueba.');
        }

        $cliente = $tipo === '01'
            ? Cliente::firstOrCreate(
                ['numero_documento' => '20000000001'],
                [
                    'nombre' => 'CLIENTE FACTURA SAC',
                    'razon_social' => 'CLIENTE FACTURA SAC',
                    'tipo_documento_sunat' => '6',
                    'direccion_fiscal' => 'AV. CLIENTE 999',
                    'pais' => 'Perú',
                    'activo' => true,
                ]
            )
            : Cliente::firstOrCreate(
                ['numero_documento' => '12345678'],
                [
                    'nombre' => 'CLIENTE BOLETA',
                    'tipo_documento_sunat' => '1',
                    'direccion' => 'CALLE FALSA 123',
                    'pais' => 'Perú',
                    'activo' => true,
                ]
            );

        $producto = Producto::first();
        if (! $producto) {
            $this->error('No hay productos en la BD; ejecute db:seed primero.');
            return self::FAILURE;
        }

        $venta = DB::transaction(function () use ($tipo, $cliente, $producto) {
            $cantidad = 2.0;
            $precio   = (float) ($producto->precio_venta ?: 3.00);

            $venta = Venta::create([
                'numero'                 => 'TST-' . time(),
                'tipo_documento'         => 'factura',
                'sunat_tipo_comprobante' => $tipo,
                'moneda'                 => 'PEN',
                'fecha'                  => now(),
                'cliente_id'             => $cliente->id,
                'user_id'                => 1,
                'subtotal'               => $precio * $cantidad,
                'base_imponible'         => round(($precio * $cantidad) / 1.18, 2),
                'impuestos'              => round(($precio * $cantidad) - ($precio * $cantidad) / 1.18, 2),
                'total'                  => $precio * $cantidad,
                'forma_pago'             => 'efectivo',
                'importe_efectivo'       => $precio * $cantidad,
                'estado'                 => 'pagada',
                'estado_sunat'           => 'pendiente',
            ]);
            VentaLinea::create([
                'venta_id'        => $venta->id,
                'producto_id'     => $producto->id,
                'descripcion'     => $producto->nombre,
                'cantidad'        => $cantidad,
                'precio_unitario' => $precio,
                'subtotal'        => $precio * $cantidad,
                'total'           => $precio * $cantidad,
                'iva'             => 18,
            ]);
            return $venta;
        });

        $this->info("Venta creada #{$venta->id}");
        $sunat = app(FacturacionElectronicaService::class);
        $venta = $sunat->emitir($venta);

        $this->line('--------------------------------------');
        $this->line("Comprobante : {$venta->numero_sunat}");
        $this->line("Estado SUNAT: {$venta->estado_sunat}");
        $this->line("Código      : {$venta->codigo_sunat}");
        $this->line("Mensaje     : {$venta->mensaje_sunat}");
        $this->line("XML         : {$venta->xml_path}");
        $this->line("CDR         : {$venta->cdr_path}");

        return $venta->estado_sunat === 'aceptado' ? self::SUCCESS : self::FAILURE;
    }
}
