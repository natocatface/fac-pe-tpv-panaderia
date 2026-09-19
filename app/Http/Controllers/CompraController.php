<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\CompraLinea;
use App\Models\MovimientoStock;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompraController extends Controller
{
    public function index(Request $request)
    {
        $compras = Compra::with('proveedor', 'user')
            ->when($request->q, fn($q) => $q->where('numero', 'like', "%{$request->q}%"))
            ->when($request->proveedor, fn($q) => $q->where('proveedor_id', $request->proveedor))
            ->when($request->estado, fn($q) => $q->where('estado', $request->estado))
            ->orderByDesc('fecha')
            ->paginate(20);
        $proveedores = Proveedor::orderBy('nombre')->get();
        return view('compras.index', compact('compras', 'proveedores'));
    }

    public function create()
    {
        $proveedores = Proveedor::where('activo', true)->orderBy('nombre')->get();
        $productos = Producto::orderBy('nombre')->get();
        $productoSel = request('producto') ? Producto::find(request('producto')) : null;
        return view('compras.form', compact('proveedores', 'productos', 'productoSel') + ['compra' => new Compra()]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'proveedor_id' => 'required|exists:proveedores,id',
            'fecha' => 'required|date',
            'lineas' => 'required|array|min:1',
        ]);

        return DB::transaction(function () use ($request) {
            $numero = 'C-' . date('Y') . '-' . str_pad(Compra::count() + 1, 5, '0', STR_PAD_LEFT);
            $subtotal = 0;
            $impuestos = 0;

            foreach ($request->lineas as $l) {
                $sub = $l['cantidad'] * $l['precio_unitario'];
                $subtotal += $sub;
                $impuestos += $sub * ($l['iva'] ?? 0) / 100;
            }

            $compra = Compra::create([
                'numero' => $numero,
                'referencia_proveedor' => $request->referencia_proveedor,
                'fecha' => $request->fecha,
                'proveedor_id' => $request->proveedor_id,
                'user_id' => auth()->id(),
                'subtotal' => $subtotal,
                'impuestos' => $impuestos,
                'total' => $subtotal + $impuestos,
                'estado' => 'pendiente',
                'observaciones' => $request->observaciones,
            ]);

            foreach ($request->lineas as $l) {
                $sub = $l['cantidad'] * $l['precio_unitario'];
                $iva = $sub * ($l['iva'] ?? 0) / 100;
                CompraLinea::create([
                    'compra_id' => $compra->id,
                    'producto_id' => $l['producto_id'],
                    'descripcion' => Producto::find($l['producto_id'])->nombre,
                    'cantidad' => $l['cantidad'],
                    'precio_unitario' => $l['precio_unitario'],
                    'iva' => $l['iva'] ?? 0,
                    'subtotal' => $sub,
                    'total' => $sub + $iva,
                ]);
            }

            return redirect()->route('compras.show', $compra)->with('success', 'Pedido de compra creado.');
        });
    }

    public function show(Compra $compra)
    {
        $compra->load(['proveedor', 'lineas.producto', 'user']);
        return view('compras.show', compact('compra'));
    }

    public function edit(Compra $compra)
    {
        $proveedores = Proveedor::orderBy('nombre')->get();
        $productos = Producto::orderBy('nombre')->get();
        $compra->load('lineas');
        return view('compras.form', compact('compra', 'proveedores', 'productos') + ['productoSel' => null]);
    }

    public function update(Request $request, Compra $compra)
    {
        $compra->update($request->only(['fecha', 'observaciones', 'referencia_proveedor']));
        return redirect()->route('compras.show', $compra)->with('success', 'Compra actualizada.');
    }

    public function destroy(Compra $compra)
    {
        $compra->lineas()->delete();
        $compra->delete();
        return redirect()->route('compras.index')->with('success', 'Compra eliminada.');
    }

    public function recibir(Compra $compra)
    {
        return DB::transaction(function () use ($compra) {
            foreach ($compra->lineas as $linea) {
                $producto = $linea->producto;
                if (!$producto) continue;
                if ($producto->controla_stock) {
                    $stockAnterior = $producto->stock_actual;
                    $producto->increment('stock_actual', $linea->cantidad);
                    $producto->update(['precio_compra' => $linea->precio_unitario]);
                    MovimientoStock::create([
                        'producto_id' => $producto->id,
                        'tipo' => 'compra',
                        'cantidad' => $linea->cantidad,
                        'stock_anterior' => $stockAnterior,
                        'stock_nuevo' => $stockAnterior + $linea->cantidad,
                        'precio_unitario' => $linea->precio_unitario,
                        'motivo' => 'Recepción compra ' . $compra->numero,
                        'referencia_tipo' => 'compra',
                        'referencia_id' => $compra->id,
                        'user_id' => auth()->id(),
                        'fecha' => now(),
                    ]);
                }
                $linea->update(['cantidad_recibida' => $linea->cantidad]);
            }
            $compra->update(['estado' => 'recibida', 'fecha_recepcion' => now()]);
            return back()->with('success', 'Recepción registrada. Stock actualizado.');
        });
    }
}
