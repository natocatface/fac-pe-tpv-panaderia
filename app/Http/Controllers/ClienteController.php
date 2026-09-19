<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $clientes = Cliente::when($request->q, fn($q) => $q->where('nombre', 'like', "%{$request->q}%")->orWhere('email', 'like', "%{$request->q}%")->orWhere('cif_nif', 'like', "%{$request->q}%"))
            ->orderBy('nombre')
            ->paginate(20);
        return view('clientes.index', compact('clientes'));
    }

    public function create()
    {
        return view('clientes.form', ['cliente' => new Cliente()]);
    }

    public function store(Request $request)
    {
        Cliente::create($this->validar($request));
        return redirect()->route('clientes.index')->with('success', 'Cliente creado.');
    }

    public function show(Cliente $cliente)
    {
        $cliente->load(['ventas' => fn($q) => $q->latest('fecha')->limit(20)]);
        return view('clientes.show', compact('cliente'));
    }

    public function edit(Cliente $cliente)
    {
        return view('clientes.form', compact('cliente'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $cliente->update($this->validar($request, $cliente->id));
        return redirect()->route('clientes.index')->with('success', 'Cliente actualizado.');
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->delete();
        return redirect()->route('clientes.index')->with('success', 'Cliente eliminado.');
    }

    protected function validar(Request $request, $id = null): array
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
            'descuento' => 'nullable|numeric|min:0|max:100',
            'observaciones' => 'nullable',
            'activo' => 'nullable|boolean',
        ]);
    }
}
