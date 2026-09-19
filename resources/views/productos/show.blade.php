@extends('layouts.app')
@section('title', $producto->nombre)
@section('page-title', $producto->nombre)
@section('content')
<div class="page-header">
    <h1 class="page-title">{{ $producto->nombre }}</h1>
    <a href="{{ route('productos.edit', $producto) }}" class="btn btn-primary"><i class="fas fa-edit"></i> Editar</a>
</div>

<div class="row-grid cols-2">
    <div class="card">
        <div class="card-header"><h5>Detalles</h5></div>
        <div class="card-body">
            <p><strong>Código:</strong> {{ $producto->codigo }}</p>
            <p><strong>Tipo:</strong> {{ $producto->tipo }}</p>
            <p><strong>Precio:</strong> {{ $appConfig?->formatearMoneda($producto->precio_venta) }}</p>
            <p><strong>Stock:</strong> {{ $producto->stock_actual }} {{ $producto->unidad_medida }}</p>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h5>Últimos movimientos de stock</h5></div>
        <table class="table">
            <thead><tr><th>Fecha</th><th>Tipo</th><th class="text-end">Cantidad</th><th class="text-end">Stock</th></tr></thead>
            <tbody>
                @forelse($producto->movimientosStock as $m)
                <tr>
                    <td>{{ $m->fecha->format('d/m/Y H:i') }}</td>
                    <td>{{ ucfirst($m->tipo) }}</td>
                    <td class="text-end {{ $m->cantidad < 0 ? 'text-danger' : 'text-success' }}">{{ number_format($m->cantidad, 2) }}</td>
                    <td class="text-end">{{ number_format($m->stock_nuevo, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted">Sin movimientos.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
