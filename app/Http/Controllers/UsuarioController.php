<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = User::orderBy('name')->paginate(20);
        return view('usuarios.index', compact('usuarios'));
    }

    public function create()
    {
        return view('usuarios.form', ['usuario' => new User()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'telefono' => 'nullable',
            'dni' => 'nullable',
            'rol' => 'required|in:admin,encargado,vendedor,obrador',
            'salario_hora' => 'nullable|numeric',
            'activo' => 'nullable|boolean',
        ]);
        $data['password'] = Hash::make($data['password']);
        $data['activo'] = $request->boolean('activo', true);
        User::create($data);
        return redirect()->route('usuarios.index')->with('success', 'Usuario creado.');
    }

    public function edit(User $usuario)
    {
        return view('usuarios.form', compact('usuario'));
    }

    public function show(User $usuario)
    {
        return redirect()->route('usuarios.edit', $usuario);
    }

    public function update(Request $request, User $usuario)
    {
        $data = $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users,email,' . $usuario->id,
            'password' => 'nullable|min:6',
            'telefono' => 'nullable',
            'dni' => 'nullable',
            'rol' => 'required|in:admin,encargado,vendedor,obrador',
            'salario_hora' => 'nullable|numeric',
            'activo' => 'nullable|boolean',
        ]);
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        $data['activo'] = $request->boolean('activo', true);
        $usuario->update($data);
        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado.');
    }

    public function destroy(User $usuario)
    {
        if ($usuario->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminarte a ti mismo.');
        }
        $usuario->delete();
        return redirect()->route('usuarios.index')->with('success', 'Usuario eliminado.');
    }
}
