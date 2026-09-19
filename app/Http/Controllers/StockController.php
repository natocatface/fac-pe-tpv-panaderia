<?php

namespace App\Http\Controllers;

use App\Models\Merma;
use App\Models\MovimientoStock;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $productos = Producto::with('categoria')
            ->where('controla_stock', true)
            ->when($request->q, fn($q) => $q->where('nombre', 'like', "%{$request->q}%"))
            ->orderBy('nombre')
            ->paginate(20);

        $totalProductos = Producto::where('controla_stock', true)->count();
        $stockBajo = Producto::where('controla_stock', true)
            ->whereColumn('stock_actual', '<=', 'stock_minimo')->count();
        $valorInventario = Producto::where('controla_stock', true)
            ->select(DB::raw('SUM(stock_actual * precio_compra) as valor'))
            ->value('valor') ?? 0;

        return view('stock.index', compact('productos', 'totalProductos', 'stockBajo', 'valorInventario'));
    }

    public function movimientos(Request $request)
    {
        $movimientos = MovimientoStock::with(['producto', 'user'])
            ->when($request->producto, fn($q) => $q->where('producto_id', $request->producto))
            ->when($request->tipo, fn($q) => $q->where('tipo', $request->tipo))
            ->when($request->desde, fn($q) => $q->whereDate('fecha', '>=', $request->desde))
            ->when($request->hasta, fn($q) => $q->whereDate('fecha', '<=', $request->hasta))
            ->orderByDesc('fecha')
            ->paginate(30);
        $productos = Producto::orderBy('nombre')->get();
        return view('stock.movimientos', compact('movimientos', 'productos'));
    }

    public function ajuste(Request $request)
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'nuevo_stock' => 'required|numeric',
            'motivo' => 'required|max:200',
        ]);
        $producto = Producto::findOrFail($request->producto_id);
        $stockAnterior = $producto->stock_actual;
        $diferencia = $request->nuevo_stock - $stockAnterior;
        $producto->update(['stock_actual' => $request->nuevo_stock]);
        MovimientoStock::create([
            'producto_id' => $producto->id,
            'tipo' => 'ajuste',
            'cantidad' => $diferencia,
            'stock_anterior' => $stockAnterior,
            'stock_nuevo' => $request->nuevo_stock,
            'motivo' => $request->motivo,
            'user_id' => auth()->id(),
            'fecha' => now(),
        ]);
        return back()->with('success', 'Stock ajustado.');
    }

    public function mermas(Request $request)
    {
        $mermas = Merma::with(['producto', 'user'])
            ->orderByDesc('fecha')
            ->paginate(20);
        $productos = Producto::where('activo', true)->orderBy('nombre')->get();
        $totalMes = Merma::whereMonth('fecha', now()->month)->sum('coste');
        return view('stock.mermas', compact('mermas', 'productos', 'totalMes'));
    }

    public function storeMerma(Request $request)
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|numeric|min:0.001',
            'motivo' => 'required',
        ]);
        $producto = Producto::findOrFail($request->producto_id);
        $coste = $request->cantidad * $producto->precio_compra;
        Merma::create([
            'producto_id' => $producto->id,
            'cantidad' => $request->cantidad,
            'motivo' => $request->motivo,
            'coste' => $coste,
            'observaciones' => $request->observaciones,
            'user_id' => auth()->id(),
            'fecha' => now(),
        ]);
        if ($producto->controla_stock) {
            $stockAnterior = $producto->stock_actual;
            $producto->decrement('stock_actual', $request->cantidad);
            MovimientoStock::create([
                'producto_id' => $producto->id,
                'tipo' => 'merma',
                'cantidad' => -$request->cantidad,
                'stock_anterior' => $stockAnterior,
                'stock_nuevo' => $stockAnterior - $request->cantidad,
                'motivo' => 'Merma: ' . $request->motivo,
                'user_id' => auth()->id(),
                'fecha' => now(),
            ]);
        }
        return back()->with('success', 'Merma registrada.');
    }

    public function alertas()
    {
        $stockBajo = Producto::with('categoria', 'proveedor')
            ->where('controla_stock', true)
            ->whereColumn('stock_actual', '<=', 'stock_minimo')
            ->where('activo', true)
            ->orderBy('stock_actual')
            ->get();
        return view('stock.alertas', compact('stockBajo'));
    }
}
