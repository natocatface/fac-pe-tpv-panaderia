<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Configuracion;
use App\Models\MovimientoStock;
use App\Models\Producto;
use App\Models\SesionCaja;
use App\Models\Venta;
use App\Models\VentaLinea;
use App\Services\Sunat\FacturacionElectronicaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TpvController extends Controller
{
    /**
     * Mapeo del tipo de documento interno del TPV al código SUNAT
     * correspondiente. Los tickets y otros documentos no fiscales (presupuestos,
     * albaranes) no se envían a SUNAT.
     */
    private function tipoSunat(string $tipoInterno): ?string
    {
        return match ($tipoInterno) {
            'factura'  => '01',
            'boleta'   => '03',
            default    => null,
        };
    }

    public function index()
    {
        $categorias = Categoria::activas()->orderBy('orden')->get();
        $productos = Producto::vendibles()
            ->with('categoria')
            ->orderBy('orden_tpv')
            ->orderBy('nombre')
            ->get();
        $clientes = Cliente::where('activo', true)->orderBy('nombre')->get();
        $config = Configuracion::actual();

        return view('tpv.index', compact('categorias', 'productos', 'clientes', 'config'));
    }

    public function buscarProductos(Request $request)
    {
        $q = $request->get('q');
        $productos = Producto::vendibles()
            ->where(function ($query) use ($q) {
                $query->where('nombre', 'like', "%{$q}%")
                    ->orWhere('codigo', 'like', "%{$q}%")
                    ->orWhere('codigo_barras', 'like', "%{$q}%");
            })
            ->limit(20)
            ->get();
        return response()->json($productos);
    }

    public function storeVenta(Request $request)
    {
        $request->validate([
            'lineas' => 'required|array|min:1',
            'lineas.*.producto_id' => 'required|exists:productos,id',
            'lineas.*.cantidad' => 'required|numeric|min:0.001',
            'lineas.*.precio_unitario' => 'required|numeric|min:0',
            'forma_pago' => 'required|in:efectivo,tarjeta,transferencia,bizum,mixto,credito',
            'total' => 'required|numeric|min:0',
        ]);

        // Aceptamos 'boleta' como tipo en Perú (mapea a SUNAT 03).
        $venta = DB::transaction(function () use ($request) {
            $config = Configuracion::actual();
            $tipo = $request->get('tipo_documento', 'ticket');
            $codigoSunat = $this->tipoSunat($tipo);

            // Serie / correlativo SUNAT atómicos si aplica, fallback al numerador clásico.
            if ($codigoSunat && $config->sunat_activo) {
                $serie = $codigoSunat === '01'
                    ? ($config->sunat_serie_factura ?: 'F001')
                    : ($config->sunat_serie_boleta  ?: 'B001');
                $correlativo = \App\Models\SerieCorrelativo::siguienteCorrelativo($codigoSunat, $serie);
                $numero = $serie . '-' . str_pad((string) $correlativo, 8, '0', STR_PAD_LEFT);
            } else {
                $serie = match ($tipo) {
                    'factura' => $config->serie_factura,
                    'boleta'  => $config->serie_factura,
                    'presupuesto' => $config->serie_presupuesto,
                    'albaran' => $config->serie_albaran,
                    default => $config->serie_ticket,
                };
                $correlativo = match ($tipo) {
                    'factura', 'boleta' => $config->siguiente_factura,
                    'presupuesto' => $config->siguiente_presupuesto,
                    'albaran' => $config->siguiente_albaran,
                    default => $config->siguiente_ticket,
                };
                $numero = sprintf('%s-%s-%05d', $serie, date('Y'), $correlativo);
            }

            // Snapshot del cliente para reproducir el comprobante aunque cambien sus datos.
            $clienteSnap = null;
            if ($request->cliente_id) {
                $c = Cliente::find($request->cliente_id);
                if ($c) {
                    $clienteSnap = [
                        'tipo_documento_sunat' => $c->tipo_documento_sunat,
                        'numero_documento'     => $c->numero_documento,
                        'nombre'               => $c->razon_social ?: $c->nombre,
                        'direccion'            => $c->direccion_fiscal ?: $c->direccion,
                    ];
                }
            }

            $venta = Venta::create([
                'numero' => $numero,
                'tipo_documento' => $tipo,           // 'ticket'|'factura'|'boleta'|'presupuesto'|'albaran'
                'sunat_tipo_comprobante' => $codigoSunat,
                'moneda' => $config->moneda_codigo ?: 'PEN',
                'serie' => $serie,
                'correlativo' => $correlativo,
                'fecha' => now(),
                'cliente_id' => $request->cliente_id,
                'user_id' => auth()->id(),
                'sesion_caja_id' => session('sesion_caja_id'),
                'subtotal' => $request->subtotal ?? $request->total,
                'descuento' => $request->descuento ?? 0,
                'base_imponible' => $request->base_imponible ?? $request->total,
                'impuestos' => $request->impuestos ?? 0,
                'total' => $request->total,
                'forma_pago' => $request->forma_pago,
                'importe_efectivo' => $request->importe_efectivo ?? 0,
                'importe_tarjeta' => $request->importe_tarjeta ?? 0,
                'cambio' => $request->cambio ?? 0,
                'estado' => 'pagada',
                'observaciones' => $request->observaciones,
                'datos_cliente' => $clienteSnap,
                'estado_sunat' => $codigoSunat ? 'pendiente' : 'no_aplica',
            ]);

            // Líneas y stock
            foreach ($request->lineas as $linea) {
                $producto = Producto::find($linea['producto_id']);
                $cantidad = $linea['cantidad'];
                $precio = $linea['precio_unitario'];
                $iva = $linea['iva'] ?? $producto->iva;
                $descuento = $linea['descuento'] ?? 0;
                $subtotal = $cantidad * $precio;
                $total = $subtotal - ($subtotal * $descuento / 100);

                VentaLinea::create([
                    'venta_id' => $venta->id,
                    'producto_id' => $producto->id,
                    'descripcion' => $producto->nombre,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precio,
                    'descuento' => $descuento,
                    'iva' => $iva,
                    'subtotal' => $subtotal,
                    'total' => $total,
                ]);

                // Movimiento de stock
                if ($producto->controla_stock && $config->descontar_stock_venta) {
                    $stockAnterior = $producto->stock_actual;
                    $producto->decrement('stock_actual', $cantidad);
                    MovimientoStock::create([
                        'producto_id' => $producto->id,
                        'tipo' => 'venta',
                        'cantidad' => -$cantidad,
                        'stock_anterior' => $stockAnterior,
                        'stock_nuevo' => $stockAnterior - $cantidad,
                        'precio_unitario' => $precio,
                        'motivo' => 'Venta ' . $numero,
                        'referencia_tipo' => 'venta',
                        'referencia_id' => $venta->id,
                        'user_id' => auth()->id(),
                        'fecha' => now(),
                    ]);
                }
            }

            // Incrementar correlativo NO-SUNAT. Los comprobantes electrónicos
            // ya consumieron de `series_correlativos`, por lo que solo
            // tocamos los contadores legacy cuando NO se está usando SUNAT.
            if (! $codigoSunat) {
                $columna = match ($tipo) {
                    'factura', 'boleta' => 'siguiente_factura',
                    'presupuesto'       => 'siguiente_presupuesto',
                    'albaran'           => 'siguiente_albaran',
                    default             => 'siguiente_ticket',
                };
                if (\Illuminate\Support\Facades\Schema::hasColumn('configuracion', $columna)) {
                    $config->increment($columna);
                }
            }

            return $venta;
        });

        // Emisión electrónica SUNAT (fuera de la transacción de BD; SOAP lento)
        $config = Configuracion::actual();
        $sunatError = null;
        if ($venta->esElectronico() && $config->sunat_activo) {
            try {
                app(FacturacionElectronicaService::class)->emitir($venta->fresh());
                $venta = $venta->fresh();
            } catch (\Throwable $e) {
                $sunatError = $e->getMessage();
            }
        }

        return response()->json([
            'success'      => true,
            'venta_id'     => $venta->id,
            'numero'       => $venta->numero,
            'ticket_url'   => route('tpv.ticket', $venta->id),
            'sunat'        => [
                'aplica'    => (bool) $venta->esElectronico(),
                'estado'    => $venta->estado_sunat,
                'codigo'    => $venta->codigo_sunat,
                'mensaje'   => $venta->mensaje_sunat ?? $sunatError,
                'detalle_url' => $venta->esElectronico() ? route('sunat.show', $venta->id) : null,
            ],
        ]);
    }

    public function imprimirTicket(Venta $venta)
    {
        $venta->load(['lineas.producto', 'cliente', 'user']);
        $config = Configuracion::actual();
        return view('tpv.ticket', compact('venta', 'config'));
    }

    public function abrirSesion(Request $request)
    {
        $caja = Caja::firstOrCreate(['id' => 1], ['nombre' => 'Caja Principal']);
        $sesion = SesionCaja::create([
            'caja_id' => $caja->id,
            'user_id' => auth()->id(),
            'apertura' => now(),
            'saldo_inicial' => $request->get('saldo_inicial', 0),
            'estado' => 'abierta',
        ]);
        session(['sesion_caja_id' => $sesion->id]);
        return redirect()->route('tpv.index')->with('success', 'Caja abierta correctamente.');
    }

    public function cerrarSesion(Request $request)
    {
        $sesion = SesionCaja::findOrFail(session('sesion_caja_id'));
        $calculado = $sesion->saldo_inicial + $sesion->ventas()->sum('importe_efectivo');
        $sesion->update([
            'cierre' => now(),
            'saldo_calculado' => $calculado,
            'saldo_contado' => $request->saldo_contado,
            'descuadre' => $request->saldo_contado - $calculado,
            'observaciones' => $request->observaciones,
            'estado' => 'cerrada',
        ]);
        session()->forget('sesion_caja_id');
        return redirect()->route('dashboard')->with('success', 'Caja cerrada.');
    }
}
