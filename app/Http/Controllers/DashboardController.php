<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Compra;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\VentaLinea;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $hoy = Carbon::today();
        $ayer = Carbon::yesterday();
        $inicioMes = Carbon::now()->startOfMonth();

        // KPIs
        $ventasHoy = Venta::whereDate('fecha', $hoy)->where('estado', '!=', 'anulada')->sum('total');
        $ventasAyer = Venta::whereDate('fecha', $ayer)->where('estado', '!=', 'anulada')->sum('total');
        $ticketsHoy = Venta::whereDate('fecha', $hoy)->where('estado', '!=', 'anulada')->count();
        $ventasMes = Venta::whereBetween('fecha', [$inicioMes, Carbon::now()])->where('estado', '!=', 'anulada')->sum('total');
        $ticketMedio = $ticketsHoy > 0 ? $ventasHoy / $ticketsHoy : 0;
        $totalClientes = Cliente::where('activo', true)->count();
        $productosStockBajo = Producto::where('controla_stock', true)
            ->whereColumn('stock_actual', '<=', 'stock_minimo')
            ->where('activo', true)
            ->count();

        $variacionVentas = $ventasAyer > 0 ? (($ventasHoy - $ventasAyer) / $ventasAyer) * 100 : 0;

        // Ventas últimos 7 días
        $ultimos7dias = collect();
        for ($i = 6; $i >= 0; $i--) {
            $fecha = Carbon::today()->subDays($i);
            $total = Venta::whereDate('fecha', $fecha)->where('estado', '!=', 'anulada')->sum('total');
            $ultimos7dias->push([
                'fecha' => $fecha->translatedFormat('D d'),
                'total' => (float) $total,
            ]);
        }

        // Productos más vendidos del mes
        $productosTop = VentaLinea::select('producto_id', DB::raw('SUM(cantidad) as total_cantidad'), DB::raw('SUM(total) as total_importe'))
            ->whereHas('venta', fn($q) => $q->whereBetween('fecha', [$inicioMes, Carbon::now()])->where('estado', '!=', 'anulada'))
            ->with('producto')
            ->groupBy('producto_id')
            ->orderByDesc('total_cantidad')
            ->limit(5)
            ->get();

        // Ventas por categoría
        $ventasPorCategoria = VentaLinea::join('productos', 'venta_lineas.producto_id', '=', 'productos.id')
            ->join('categorias', 'productos.categoria_id', '=', 'categorias.id')
            ->join('ventas', 'venta_lineas.venta_id', '=', 'ventas.id')
            ->whereBetween('ventas.fecha', [$inicioMes, Carbon::now()])
            ->where('ventas.estado', '!=', 'anulada')
            ->select('categorias.nombre', 'categorias.color', DB::raw('SUM(venta_lineas.total) as total'))
            ->groupBy('categorias.id', 'categorias.nombre', 'categorias.color')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        // Últimas ventas
        $ultimasVentas = Venta::with(['user', 'cliente'])
            ->orderByDesc('fecha')
            ->limit(8)
            ->get();

        // Productos con stock bajo
        $stockBajo = Producto::where('controla_stock', true)
            ->whereColumn('stock_actual', '<=', 'stock_minimo')
            ->where('activo', true)
            ->orderBy('stock_actual')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact(
            'ventasHoy', 'ventasMes', 'ticketsHoy', 'ticketMedio', 'totalClientes',
            'productosStockBajo', 'variacionVentas', 'ultimos7dias', 'productosTop',
            'ventasPorCategoria', 'ultimasVentas', 'stockBajo'
        ));
    }
}
