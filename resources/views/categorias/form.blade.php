@extends('layouts.app')
@section('title', $categoria->exists ? 'Editar categoría' : 'Nueva categoría')
@section('page-title', $categoria->exists ? 'Editar categoría' : 'Nueva categoría')

@section('content')
<form method="POST" action="{{ $categoria->exists ? route('categorias.update', $categoria) : route('categorias.store') }}">
    @csrf
    @if($categoria->exists)@method('PUT')@endif

    <div class="card" style="max-width: 720px; margin: 0 auto;">
        <div class="card-header"><h5>Datos de la categoría</h5></div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">Nombre *</label>
                <input type="text" name="nombre" class="form-control" required value="{{ old('nombre', $categoria->nombre) }}">
            </div>
            <div class="row-grid cols-2">
                <div class="form-group">
                    <label class="form-label">Código</label>
                    <input type="text" name="codigo" class="form-control" value="{{ old('codigo', $categoria->codigo) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Color</label>
                    <input type="color" name="color" class="form-control" style="height: 42px;" value="{{ old('color', $categoria->color ?? '#C8763D') }}">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Descripción</label>
                <textarea name="descripcion" class="form-control">{{ old('descripcion', $categoria->descripcion) }}</textarea>
            </div>
            <div class="row-grid cols-2">
                <div class="form-group">
                    <label class="form-label">Orden de visualización</label>
                    <input type="number" name="orden" class="form-control" value="{{ old('orden', $categoria->orden ?? 0) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">&nbsp;</label>
                    <label style="display: flex; align-items: center; gap: 8px; height: 42px;">
                        <input type="hidden" name="activa" value="0">
                        <input type="checkbox" name="activa" value="1" {{ old('activa', $categoria->activa ?? true) ? 'checked' : '' }}>
                        Categoría activa
                    </label>
                </div>
            </div>
            <div class="d-flex gap-2 mt-3">
                <a href="{{ route('categorias.index') }}" class="btn btn-light">Cancelar</a>
                <button type="submit" class="btn btn-primary" style="margin-left:auto;"><i class="fas fa-save"></i> Guardar</button>
            </div>
        </div>
    </div>
</form>
@endsection
