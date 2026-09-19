<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function index()
    {
        $categorias = Categoria::withCount('productos')->orderBy('orden')->orderBy('nombre')->get();
        return view('categorias.index', compact('categorias'));
    }

    public function create()
    {
        return view('categorias.form', ['categoria' => new Categoria()]);
    }

    public function store(Request $request)
    {
        Categoria::create($this->validar($request));
        return redirect()->route('categorias.index')->with('success', 'Categoría creada.');
    }

    public function edit(Categoria $categoria)
    {
        return view('categorias.form', compact('categoria'));
    }

    public function update(Request $request, Categoria $categoria)
    {
        $categoria->update($this->validar($request));
        return redirect()->route('categorias.index')->with('success', 'Categoría actualizada.');
    }

    public function destroy(Categoria $categoria)
    {
        if ($categoria->productos()->exists()) {
            return back()->with('error', 'No se puede eliminar: hay productos asignados.');
        }
        $categoria->delete();
        return redirect()->route('categorias.index')->with('success', 'Categoría eliminada.');
    }

    public function show(Categoria $categoria)
    {
        return redirect()->route('categorias.edit', $categoria);
    }

    protected function validar(Request $request): array
    {
        return $request->validate([
            'nombre' => 'required|max:100',
            'codigo' => 'nullable|max:30',
            'color' => 'nullable|max:10',
            'icono' => 'nullable|max:50',
            'descripcion' => 'nullable',
            'orden' => 'nullable|integer',
            'activa' => 'nullable|boolean',
        ]);
    }
}
