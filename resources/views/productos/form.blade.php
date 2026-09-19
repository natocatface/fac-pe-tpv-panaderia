@extends('layouts.app')
@section('title', $producto->exists ? 'Editar producto' : 'Nuevo producto')
@section('page-title', $producto->exists ? 'Editar producto' : 'Nuevo producto')

@section('content')
<form method="POST" action="{{ $producto->exists ? route('productos.update', $producto) : route('productos.store') }}" enctype="multipart/form-data">
    @csrf
    @if($producto->exists)@method('PUT')@endif

    <div class="row-grid cols-2">
        <div>
            <div class="card mb-3">
                <div class="card-header"><h5><i class="fas fa-info-circle"></i> Información básica</h5></div>
                <div class="card-body">
                    <div class="row-grid cols-2">
                        <div class="form-group">
                            <label class="form-label">Código *</label>
                            <input type="text" name="codigo" class="form-control" value="{{ old('codigo', $producto->codigo) }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Código de barras</label>
                            <input type="text" name="codigo_barras" class="form-control" value="{{ old('codigo_barras', $producto->codigo_barras) }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nombre *</label>
                        <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $producto->nombre) }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="2">{{ old('descripcion', $producto->descripcion) }}</textarea>
                    </div>
                    <div class="row-grid cols-2">
                        <div class="form-group">
                            <label class="form-label">Categoría</label>
                            <select name="categoria_id" class="form-select">
                                <option value="">— Sin categoría —</option>
                                @foreach($categorias as $c)
                                    <option value="{{ $c->id }}" {{ old('categoria_id', $producto->categoria_id) == $c->id ? 'selected' : '' }}>{{ $c->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tipo *</label>
                            <select name="tipo" class="form-select" required>
                                <option value="simple" {{ old('tipo', $producto->tipo) == 'simple' ? 'selected' : '' }}>Simple (compra-venta)</option>
                                <option value="elaborado" {{ old('tipo', $producto->tipo) == 'elaborado' ? 'selected' : '' }}>Elaborado (con receta)</option>
                                <option value="materia_prima" {{ old('tipo', $producto->tipo) == 'materia_prima' ? 'selected' : '' }}>Materia prima</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header"><h5><i class="fas fa-euro-sign"></i> Precios e impuestos</h5></div>
                <div class="card-body">
                    <div class="row-grid cols-2">
                        <div class="form-group">
                            <label class="form-label">Precio de compra</label>
                            <input type="number" name="precio_compra" class="form-control" step="0.0001" value="{{ old('precio_compra', $producto->precio_compra) }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Precio de venta *</label>
                            <input type="number" name="precio_venta" class="form-control" step="0.0001" value="{{ old('precio_venta', $producto->precio_venta) }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">IVA (%) *</label>
                            <select name="iva" class="form-select" required>
                                <option value="0">0% (Exento)</option>
                                <option value="4" {{ old('iva', $producto->iva) == 4 ? 'selected' : '' }}>4% (Superreducido)</option>
                                <option value="10" {{ old('iva', $producto->iva) == 10 ? 'selected' : '' }}>10% (Reducido)</option>
                                <option value="21" {{ old('iva', $producto->iva) == 21 ? 'selected' : '' }}>21% (General)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Unidad de medida *</label>
                            <select name="unidad_medida" class="form-select" required>
                                <option value="unidad" {{ old('unidad_medida', $producto->unidad_medida) == 'unidad' ? 'selected' : '' }}>Unidad</option>
                                <option value="kg" {{ old('unidad_medida', $producto->unidad_medida) == 'kg' ? 'selected' : '' }}>Kilogramo</option>
                                <option value="g" {{ old('unidad_medida', $producto->unidad_medida) == 'g' ? 'selected' : '' }}>Gramo</option>
                                <option value="l" {{ old('unidad_medida', $producto->unidad_medida) == 'l' ? 'selected' : '' }}>Litro</option>
                                <option value="ml" {{ old('unidad_medida', $producto->unidad_medida) == 'ml' ? 'selected' : '' }}>Mililitro</option>
                                <option value="docena" {{ old('unidad_medida', $producto->unidad_medida) == 'docena' ? 'selected' : '' }}>Docena</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="card mb-3">
                <div class="card-header"><h5><i class="fas fa-image"></i> Imagen y diseño TPV</h5></div>
                <div class="card-body text-center">
                    <img src="{{ $producto->imagenUrl() }}" id="preview-img" style="max-width: 200px; max-height: 200px; border-radius: 12px; margin-bottom: 12px;">
                    <input type="file" name="imagen" accept="image/*" class="form-control" onchange="document.getElementById('preview-img').src = URL.createObjectURL(this.files[0])">
                    <div class="row-grid cols-2 mt-3">
                        <div class="form-group">
                            <label class="form-label">Color TPV</label>
                            <input type="color" name="color_tpv" class="form-control" style="height: 42px;" value="{{ old('color_tpv', $producto->color_tpv ?? '#C8763D') }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Orden TPV</label>
                            <input type="number" name="orden_tpv" class="form-control" value="{{ old('orden_tpv', $producto->orden_tpv) }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header"><h5><i class="fas fa-boxes"></i> Stock</h5></div>
                <div class="card-body">
                    <label style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                        <input type="hidden" name="controla_stock" value="0">
                        <input type="checkbox" name="controla_stock" value="1" {{ old('controla_stock', $producto->controla_stock ?? true) ? 'checked' : '' }}>
                        Controlar stock de este producto
                    </label>
                    <div class="row-grid cols-3">
                        <div class="form-group">
                            <label class="form-label">Actual</label>
                            <input type="number" name="stock_actual" step="0.001" class="form-control" value="{{ old('stock_actual', $producto->stock_actual) }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Mínimo</label>
                            <input type="number" name="stock_minimo" step="0.001" class="form-control" value="{{ old('stock_minimo', $producto->stock_minimo) }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Óptimo</label>
                            <input type="number" name="stock_optimo" step="0.001" class="form-control" value="{{ old('stock_optimo', $producto->stock_optimo) }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Días de caducidad</label>
                        <input type="number" name="dias_caducidad" class="form-control" value="{{ old('dias_caducidad', $producto->dias_caducidad) }}">
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header"><h5><i class="fas fa-cog"></i> Configuración</h5></div>
                <div class="card-body">
                    <label style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                        <input type="hidden" name="vende_tpv" value="0">
                        <input type="checkbox" name="vende_tpv" value="1" {{ old('vende_tpv', $producto->vende_tpv ?? true) ? 'checked' : '' }}>
                        Mostrar en el TPV
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px;">
                        <input type="hidden" name="activo" value="0">
                        <input type="checkbox" name="activo" value="1" {{ old('activo', $producto->activo ?? true) ? 'checked' : '' }}>
                        Producto activo
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2" style="justify-content:flex-end;">
        <a href="{{ route('productos.index') }}" class="btn btn-light">Cancelar</a>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar producto</button>
    </div>
</form>
@endsection
