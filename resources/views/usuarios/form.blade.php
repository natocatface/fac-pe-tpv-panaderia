@extends('layouts.app')
@section('title', $usuario->exists ? 'Editar usuario' : 'Nuevo usuario')
@section('page-title', $usuario->exists ? 'Editar usuario' : 'Nuevo usuario')

@section('content')
<form method="POST" action="{{ $usuario->exists ? route('usuarios.update', $usuario) : route('usuarios.store') }}">
    @csrf @if($usuario->exists)@method('PUT')@endif

    <div class="card" style="max-width: 720px; margin: 0 auto;">
        <div class="card-header"><h5><i class="fas fa-user-shield"></i> Datos del usuario</h5></div>
        <div class="card-body">
            <div class="row-grid cols-2">
                <div class="form-group">
                    <label class="form-label">Nombre completo *</label>
                    <input type="text" name="name" class="form-control" required value="{{ old('name', $usuario->name) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control" required value="{{ old('email', $usuario->email) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Contraseña {{ $usuario->exists ? '(dejar vacío para no cambiar)' : '*' }}</label>
                    <input type="password" name="password" class="form-control" {{ $usuario->exists ? '' : 'required' }}>
                </div>
                <div class="form-group">
                    <label class="form-label">Rol *</label>
                    <select name="rol" class="form-select" required>
                        <option value="admin" {{ $usuario->rol == 'admin' ? 'selected' : '' }}>Administrador</option>
                        <option value="encargado" {{ $usuario->rol == 'encargado' ? 'selected' : '' }}>Encargado</option>
                        <option value="vendedor" {{ $usuario->rol == 'vendedor' ? 'selected' : '' }}>Vendedor</option>
                        <option value="obrador" {{ $usuario->rol == 'obrador' ? 'selected' : '' }}>Obrador</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Teléfono</label>
                    <input type="text" name="telefono" class="form-control" value="{{ old('telefono', $usuario->telefono) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">DNI</label>
                    <input type="text" name="dni" class="form-control" value="{{ old('dni', $usuario->dni) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Salario/hora (€)</label>
                    <input type="number" step="0.01" name="salario_hora" class="form-control" value="{{ old('salario_hora', $usuario->salario_hora ?? 0) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">&nbsp;</label>
                    <label style="display: flex; align-items: center; gap: 8px; height: 42px;">
                        <input type="checkbox" name="activo" value="1" {{ old('activo', $usuario->activo ?? true) ? 'checked' : '' }}>
                        Usuario activo
                    </label>
                </div>
            </div>
            <div class="d-flex gap-2 mt-3">
                <a href="{{ route('usuarios.index') }}" class="btn btn-light">Cancelar</a>
                <button class="btn btn-primary" style="margin-left:auto;"><i class="fas fa-save"></i> Guardar</button>
            </div>
        </div>
    </div>
</form>
@endsection
