@extends('layouts.app')
@section('title', 'Productos')
@section('page-title', 'Productos')
@section('breadcrumb', 'Inicio / Productos')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-bread-slice"></i> Catálogo de productos</h1>
        <p class="page-subtitle">{{ $productos->total() }} productos · Gestiona pan, bollería, pastelería e ingredientes</p>
    </div>
    <a href="{{ route('productos.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nuevo producto
    </a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" style="display: grid; grid-template-columns: 1fr 200px 200px auto; gap: 12px;">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="🔍 Buscar por nombre o código...">
            <select name="categoria" class="form-select">
                <option value="">Todas las categorías</option>
                @foreach($categorias as $c)
                    <option value="{{ $c->id }}" {{ request('categoria') == $c->id ? 'selected' : '' }}>{{ $c->nombre }}</option>
                @endforeach
            </select>
            <select name="tipo" class="form-select">
                <option value="">Todos los tipos</option>
                <option value="elaborado" {{ request('tipo') == 'elaborado' ? 'selected' : '' }}>Elaborados</option>
                <option value="materia_prima" {{ request('tipo') == 'materia_prima' ? 'selected' : '' }}>Materia prima</option>
                <option value="simple" {{ request('tipo') == 'simple' ? 'selected' : '' }}>Compra-venta</option>
            </select>
            <button class="btn btn-primary"><i class="fas fa-filter"></i> Filtrar</button>
        </form>
    </div>
</div>

<div class="card">
    <div style="overflow-x: auto;">
        <table class="table">
            <thead>
                <tr>
                    <th></th>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th>Tipo</th>
                    <th class="text-end">Precio</th>
                    <th class="text-end">Stock</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($productos as $p)
                <tr>
                    <td><img src="{{ $p->imagenUrl() }}" alt="" style="width: 40px; height: 40px; border-radius: 8px; object-fit: cover;"></td>
                    <td><code>{{ $p->codigo }}</code></td>
                    <td>
                        <a href="{{ route('productos.edit', $p) }}" class="fw-bold">{{ $p->nombre }}</a>
                        @if($p->descripcion)<div class="text-muted" style="font-size: 11px;">{{ Str::limit($p->descripcion, 50) }}</div>@endif
                    </td>
                    <td>
                        @if($p->categoria)
                            <span class="badge" style="background: {{ $p->categoria->color }}20; color: {{ $p->categoria->color }};">{{ $p->categoria->nombre }}</span>
                        @endif
                    </td>
                    <td>
                        @if($p->tipo == 'elaborado')
                            <span class="badge badge-info"><i class="fas fa-utensils"></i> Elaborado</span>
                        @elseif($p->tipo == 'materia_prima')
                            <span class="badge badge-secondary"><i class="fas fa-wheat-awn"></i> Materia prima</span>
                        @else
                            <span class="badge badge-primary"><i class="fas fa-box"></i> Simple</span>
                        @endif
                    </td>
                    <td class="text-end fw-bold">{{ $appConfig?->formatearMoneda($p->precio_venta) ?? number_format($p->precio_venta, 2) . ' €' }}</td>
                    <td class="text-end">
                        @if($p->controla_stock)
                            <span class="badge {{ $p->tieneStockBajo() ? 'badge-danger' : 'badge-success' }}">
                                {{ number_format($p->stock_actual, 0) }} {{ $p->unidad_medida }}
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if($p->activo)
                            <span class="badge badge-success">Activo</span>
                        @else
                            <span class="badge badge-secondary">Inactivo</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('productos.edit', $p) }}" class="btn btn-sm btn-light"><i class="fas fa-edit"></i></a>
                        <form action="{{ route('productos.destroy', $p) }}" method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar este producto?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center text-muted" style="padding: 40px;">
                    <i class="fas fa-bread-slice" style="font-size: 40px; opacity: 0.3; margin-bottom: 12px; display:block;"></i>
                    No hay productos. <a href="{{ route('productos.create') }}">Crea el primero →</a>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding: 16px;">{{ $productos->withQueryString()->links() }}</div>
</div>
@endsection
