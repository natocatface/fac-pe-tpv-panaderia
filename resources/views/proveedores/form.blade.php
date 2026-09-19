@extends('layouts.app')
@section('title', $proveedor->exists ? 'Editar proveedor' : 'Nuevo proveedor')
@section('page-title', $proveedor->exists ? 'Editar proveedor' : 'Nuevo proveedor')

@section('content')
<form method="POST" action="{{ $proveedor->exists ? route('proveedores.update', $proveedor) : route('proveedores.store') }}">
    @csrf @if($proveedor->exists)@method('PUT')@endif

    <div class="card" style="max-width: 900px; margin: 0 auto;">
        <div class="card-header"><h5><i class="fas fa-truck"></i> Datos del proveedor</h5></div>
        <div class="card-body">
            <div class="row-grid cols-2">
                <div class="form-group">
                    <label class="form-label">Nombre *</label>
                    <input type="text" name="nombre" class="form-control" required value="{{ old('nombre', $proveedor->nombre) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">CIF / NIF</label>
                    <input type="text" name="cif_nif" class="form-control" value="{{ old('cif_nif', $proveedor->cif_nif) }}">
                </div>
            </div>
            <div class="row-grid cols-2">
                <div class="form-group">
                    <label class="form-label">Persona de contacto</label>
                    <input type="text" name="contacto" class="form-control" value="{{ old('contacto', $proveedor->contacto) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Días de entrega</label>
                    <input type="text" name="dias_entrega" class="form-control" placeholder="Ej: L, X, V" value="{{ old('dias_entrega', $proveedor->dias_entrega) }}">
                </div>
            </div>
            <div class="row-grid cols-2">
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $proveedor->email) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Teléfono</label>
                    <input type="text" name="telefono" class="form-control" value="{{ old('telefono', $proveedor->telefono) }}">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Dirección</label>
                <input type="text" name="direccion" class="form-control" value="{{ old('direccion', $proveedor->direccion) }}">
            </div>
            <div class="row-grid cols-3">
                <div class="form-group">
                    <label class="form-label">Código postal</label>
                    <input type="text" name="codigo_postal" class="form-control" value="{{ old('codigo_postal', $proveedor->codigo_postal) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Ciudad</label>
                    <input type="text" name="ciudad" class="form-control" value="{{ old('ciudad', $proveedor->ciudad) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Provincia</label>
                    <input type="text" name="provincia" class="form-control" value="{{ old('provincia', $proveedor->provincia) }}">
                </div>
            </div>
            <label style="display: flex; align-items: center; gap: 8px;">
                <input type="hidden" name="activo" value="0">
                <input type="checkbox" name="activo" value="1" {{ old('activo', $proveedor->activo ?? true) ? 'checked' : '' }}>
                Proveedor activo
            </label>
            <div class="d-flex gap-2 mt-3">
                <a href="{{ route('proveedores.index') }}" class="btn btn-light">Cancelar</a>
                <button type="submit" class="btn btn-primary" style="margin-left:auto;"><i class="fas fa-save"></i> Guardar</button>
            </div>
        </div>
    </div>
</form>
@endsection
