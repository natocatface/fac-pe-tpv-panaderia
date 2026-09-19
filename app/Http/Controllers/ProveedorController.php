<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{
    public function index(Request $request)
    {
        $proveedores = Proveedor::when($request->q, fn($q) => $q->where('nombre', 'like', "%{$request->q}%"))
            ->orderBy('nombre')
            ->paginate(20);
        return view('proveedores.index', compact('proveedores'));
    }

    public function create()
    {
        return view('proveedores.form', ['proveedor' => new Proveedor()]);
    }

    public function store(Request $request)
    {
        Proveedor::create($this->validar($request));
        return redirect()->route('proveedores.index')->with('success', 'Proveedor creado.');
    }

    public function show(Proveedor $proveedor)
    {
        return redirect()->route('proveedores.edit', $proveedor);
    }

    public function edit(Proveedor $proveedor)
    {
        return view('proveedores.form', compact('proveedor'));
    }

    public function update(Request $request, Proveedor $proveedor)
    {
        $proveedor->update($this->validar($request));
        return redirect()->route('proveedores.index')->with('success', 'Proveedor actualizado.');
    }

    public function destroy(Proveedor $proveedor)
    {
        $proveedor->delete();
        return redirect()->route('proveedores.index')->with('success', 'Proveedor eliminado.');
    }

    protected function validar(Request $request): array
    {
        return $request->validate([
            'codigo' => 'nullable|max:30',
            'nombre' => 'required|max:255',
            'razon_social' => 'nullable|max:255',
            'cif_nif' => 'nullable|max:30',
            'direccion' => 'nullable|max:255',
            'codigo_postal' => 'nullable|max:10',
            'ciudad' => 'nullable|max:100',
            'provincia' => 'nullable|max:100',
            'pais' => 'nullable|max:100',
            'telefono' => 'nullable|max:30',
            'email' => 'nullable|email|max:255',
            'contacto' => 'nullable|max:255',
            'dias_entrega' => 'nullable|max:100',
            'observaciones' => 'nullable',
            'activo' => 'nullable|boolean',
        ]);
    }
}
