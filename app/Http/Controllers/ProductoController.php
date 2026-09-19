<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Receta;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $productos = Producto::with('categoria')
            ->when($request->q, fn($q) => $q->where('nombre', 'like', "%{$request->q}%")->orWhere('codigo', 'like', "%{$request->q}%"))
            ->when($request->categoria, fn($q) => $q->where('categoria_id', $request->categoria))
            ->when($request->tipo, fn($q) => $q->where('tipo', $request->tipo))
            ->orderBy('nombre')
            ->paginate(20);
        $categorias = Categoria::activas()->orderBy('nombre')->get();
        return view('productos.index', compact('productos', 'categorias'));
    }

    public function create()
    {
        $categorias = Categoria::activas()->orderBy('nombre')->get();
        $proveedores = Proveedor::where('activo', true)->orderBy('nombre')->get();
        $ingredientes = Producto::where('tipo', 'materia_prima')->orderBy('nombre')->get();
        return view('productos.form', compact('categorias', 'proveedores', 'ingredientes') + ['producto' => new Producto()]);
    }

    public function store(Request $request)
    {
        $data = $this->validar($request);
        if ($request->hasFile('imagen')) {
            $data['imagen'] = $request->file('imagen')->store('productos', 'public');
        }
        Producto::create($data);
        return redirect()->route('productos.index')->with('success', 'Producto creado correctamente.');
    }

    public function show(Producto $producto)
    {
        $producto->load(['categoria', 'proveedor', 'ingredientes.ingrediente', 'movimientosStock' => fn($q) => $q->latest('fecha')->limit(20)]);
        return view('productos.show', compact('producto'));
    }

    public function edit(Producto $producto)
    {
        $categorias = Categoria::activas()->orderBy('nombre')->get();
        $proveedores = Proveedor::where('activo', true)->orderBy('nombre')->get();
        $ingredientes = Producto::where('tipo', 'materia_prima')->where('id', '!=', $producto->id)->orderBy('nombre')->get();
        $producto->load('ingredientes.ingrediente');
        return view('productos.form', compact('producto', 'categorias', 'proveedores', 'ingredientes'));
    }

    public function update(Request $request, Producto $producto)
    {
        $data = $this->validar($request, $producto->id);
        if ($request->hasFile('imagen')) {
            $data['imagen'] = $request->file('imagen')->store('productos', 'public');
        }
        $producto->update($data);
        return redirect()->route('productos.index')->with('success', 'Producto actualizado.');
    }

    public function destroy(Producto $producto)
    {
        $producto->delete();
        return redirect()->route('productos.index')->with('success', 'Producto eliminado.');
    }

    public function guardarReceta(Request $request, Producto $producto)
    {
        $producto->ingredientes()->delete();
        foreach ($request->ingredientes ?? [] as $ing) {
            if (!empty($ing['ingrediente_id']) && !empty($ing['cantidad'])) {
                Receta::create([
                    'producto_id' => $producto->id,
                    'ingrediente_id' => $ing['ingrediente_id'],
                    'cantidad' => $ing['cantidad'],
                    'unidad' => $ing['unidad'] ?? 'g',
                ]);
            }
        }
        return back()->with('success', 'Receta actualizada.');
    }

    protected function validar(Request $request, $id = null): array
    {
        return $request->validate([
            'codigo' => 'required|max:50|unique:productos,codigo,' . $id,
            'codigo_barras' => 'nullable|max:50',
            'nombre' => 'required|max:255',
            'descripcion' => 'nullable',
            'categoria_id' => 'nullable|exists:categorias,id',
            'proveedor_id' => 'nullable|exists:proveedores,id',
            'tipo' => 'required|in:materia_prima,elaborado,simple',
            'unidad_medida' => 'required|max:20',
            'formato' => 'nullable|numeric',
            'precio_compra' => 'nullable|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'iva' => 'required|numeric|min:0',
            'stock_actual' => 'nullable|numeric',
            'stock_minimo' => 'nullable|numeric',
            'stock_optimo' => 'nullable|numeric',
            'controla_stock' => 'nullable|boolean',
            'vende_tpv' => 'nullable|boolean',
            'activo' => 'nullable|boolean',
            'dias_caducidad' => 'nullable|integer|min:0',
            'color_tpv' => 'nullable|max:10',
            'orden_tpv' => 'nullable|integer',
        ]);
    }
}
