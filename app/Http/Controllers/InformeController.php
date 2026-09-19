<?php

namespace App\Http\Controllers;

use App\Models\Merma;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\VentaLinea;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InformeController extends Controller
{
    public function index()
    {
        return view('informes.index');
    }

    public function ventas(Request $request)
    {
        $desde = $request->get('desde', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $hasta = $request->get('hasta', Carbon::now()->format('Y-m-d'));

        $ventas = Venta::whereBetween('fecha', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])
            ->where('estado', '!=', 'anulada')
            ->orderByDesc('fecha')
            ->get();

        $totalVentas = $ventas->sum('total');
        $totalTickets = $ventas->count();
        $ticketMedio = $totalTickets ? $totalVentas / $totalTickets : 0;
        $totalIVA = $ventas->sum('impuestos');

        // Agrupar por día
        $porDia = $ventas->groupBy(fn($v) => $v->fecha->format('Y-m-d'))
            ->map(fn($g) => [
                'fecha' => $g->first()->fecha->format('d/m'),
                'total' => $g->sum('total'),
                'tickets' => $g->count(),
            ])->values();

        // Por forma de pago
        $porFormaPago = $ventas->groupBy('forma_pago')
            ->map(fn($g) => ['total' => $g->sum('total'), 'count' => $g->count()]);

        return view('informes.ventas', compact('ventas', 'desde', 'hasta', 'totalVentas', 'totalTickets', 'ticketMedio', 'totalIVA', 'porDia', 'porFormaPago'));
    }

    public function productos(Request $request)
    {
        $desde = $request->get('desde', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $hasta = $request->get('hasta', Carbon::now()->format('Y-m-d'));

        $productos = VentaLinea::select('producto_id',
                DB::raw('SUM(cantidad) as total_cantidad'),
                DB::raw('SUM(total) as total_importe'),
                DB::raw('COUNT(DISTINCT venta_id) as num_ventas')
            )
            ->whereHas('venta', fn($q) => $q->whereBetween('fecha', [$desde, $hasta . ' 23:59:59'])->where('estado', '!=', 'anulada'))
            ->with('producto.categoria')
            ->groupBy('producto_id')
            ->orderByDesc('total_importe')
            ->get();

        return view('informes.productos', compact('productos', 'desde', 'hasta'));
    }

    public function margenes(Request $request)
    {
        $productos = Producto::with('categoria')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get()
            ->map(function ($p) {
                $margen = $p->precio_venta - $p->precio_compra;
                $margenPct = $p->precio_compra > 0 ? ($margen / $p->precio_compra) * 100 : 0;
                return (object) [
                    'producto' => $p,
                    'margen' => $margen,
                    'margen_pct' => $margenPct,
                ];
            });
        return view('informes.margenes', compact('productos'));
    }

    public function clientes(Request $request)
    {
        $clientes = DB::table('clientes')
            ->leftJoin('ventas', 'clientes.id', '=', 'ventas.cliente_id')
            ->select('clientes.id', 'clientes.nombre', 'clientes.email',
                DB::raw('COUNT(ventas.id) as total_compras'),
                DB::raw('COALESCE(SUM(ventas.total), 0) as total_gastado'),
                DB::raw('MAX(ventas.fecha) as ultima_compra')
            )
            ->groupBy('clientes.id', 'clientes.nombre', 'clientes.email')
            ->orderByDesc('total_gastado')
            ->get();
        return view('informes.clientes', compact('clientes'));
    }

    public function stock()
    {
        $valoracion = Producto::where('controla_stock', true)
            ->where('activo', true)
            ->select('id', 'nombre', 'unidad_medida', 'stock_actual', 'precio_compra', 'precio_venta',
                DB::raw('stock_actual * precio_compra as valor_coste'),
                DB::raw('stock_actual * precio_venta as valor_venta'))
            ->orderByDesc('valor_coste')
            ->get();
        $totalCoste = $valoracion->sum('valor_coste');
        $totalVenta = $valoracion->sum('valor_venta');
        return view('informes.stock', compact('valoracion', 'totalCoste', 'totalVenta'));
    }

    public function caja(Request $request)
    {
        $fecha = $request->get('fecha', date('Y-m-d'));
        $ventas = Venta::whereDate('fecha', $fecha)->where('estado', '!=', 'anulada')->get();
        $efectivo = $ventas->sum('importe_efectivo');
        $tarjeta = $ventas->sum('importe_tarjeta');
        $otros = $ventas->sum('importe_otros');
        return view('informes.caja', compact('fecha', 'ventas', 'efectivo', 'tarjeta', 'otros'));
    }
}
