@extends('layouts.app')
@section('title', $cliente->exists ? 'Editar cliente' : 'Nuevo cliente')
@section('page-title', $cliente->exists ? 'Editar cliente' : 'Nuevo cliente')

@section('content')
<form method="POST" action="{{ $cliente->exists ? route('clientes.update', $cliente) : route('clientes.store') }}">
    @csrf @if($cliente->exists)@method('PUT')@endif

    <div class="card" style="max-width: 900px; margin: 0 auto;">
        <div class="card-header"><h5><i class="fas fa-user"></i> Datos del cliente</h5></div>
        <div class="card-body">
            <div class="row-grid cols-2">
                <div class="form-group">
                    <label class="form-label">Nombre / Razón social *</label>
                    <input type="text" name="nombre" class="form-control" required value="{{ old('nombre', $cliente->nombre) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">CIF / NIF</label>
                    <input type="text" name="cif_nif" class="form-control" value="{{ old('cif_nif', $cliente->cif_nif) }}">
                </div>
            </div>
            <div class="row-grid cols-2">
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $cliente->email) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Teléfono</label>
                    <input type="text" name="telefono" class="form-control" value="{{ old('telefono', $cliente->telefono) }}">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Dirección</label>
                <input type="text" name="direccion" class="form-control" value="{{ old('direccion', $cliente->direccion) }}">
            </div>
            <div class="row-grid cols-3">
                <div class="form-group">
                    <label class="form-label">Código postal</label>
                    <input type="text" name="codigo_postal" class="form-control" value="{{ old('codigo_postal', $cliente->codigo_postal) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Ciudad</label>
                    <input type="text" name="ciudad" class="form-control" value="{{ old('ciudad', $cliente->ciudad) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Provincia</label>
                    <input type="text" name="provincia" class="form-control" value="{{ old('provincia', $cliente->provincia) }}">
                </div>
            </div>
            <div class="row-grid cols-2">
                <div class="form-group">
                    <label class="form-label">Descuento por defecto (%)</label>
                    <input type="number" step="0.01" name="descuento" class="form-control" value="{{ old('descuento', $cliente->descuento ?? 0) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">&nbsp;</label>
                    <label style="display: flex; align-items: center; gap: 8px; height: 42px;">
                        <input type="hidden" name="activo" value="0">
                        <input type="checkbox" name="activo" value="1" {{ old('activo', $cliente->activo ?? true) ? 'checked' : '' }}>
                        Cliente activo
                    </label>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Observaciones</label>
                <textarea name="observaciones" class="form-control">{{ old('observaciones', $cliente->observaciones) }}</textarea>
            </div>
            <div class="d-flex gap-2 mt-3">
                <a href="{{ route('clientes.index') }}" class="btn btn-light">Cancelar</a>
                <button type="submit" class="btn btn-primary" style="margin-left:auto;"><i class="fas fa-save"></i> Guardar</button>
            </div>
        </div>
    </div>
</form>
@endsection
