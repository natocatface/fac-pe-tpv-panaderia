@extends('layouts.app')
@section('title', 'Alertas de stock')
@section('page-title', 'Alertas de stock')

@section('content')
<div class="page-header">
    <h1 class="page-title"><i class="fas fa-exclamation-triangle text-warning"></i> Productos que requieren reposición</h1>
    <a href="{{ route('stock.index') }}" class="btn btn-light"><i class="fas fa-arrow-left"></i> Volver</a>
</div>

@if($stockBajo->isEmpty())
<div class="card"><div class="card-body text-center" style="padding: 60px;">
    <i class="fas fa-check-circle" style="font-size: 60px; color: var(--color-success); margin-bottom: 16px;"></i>
    <h3>¡Todo en orden!</h3>
    <p class="text-muted">No hay productos por debajo del stock mínimo.</p>
</div></div>
@else
<div class="card">
    <table class="table">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Categoría</th>
                <th>Proveedor</th>
                <th class="text-end">Stock actual</th>
                <th class="text-end">Mínimo</th>
                <th class="text-end">Reponer</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($stockBajo as $p)
            <tr>
                <td><strong>{{ $p->nombre }}</strong><br><small class="text-muted">{{ $p->codigo }}</small></td>
                <td>{{ $p->categoria?->nombre ?? '—' }}</td>
                <td>{{ $p->proveedor?->nombre ?? '—' }}</td>
                <td class="text-end"><span class="badge badge-danger">{{ number_format($p->stock_actual, 2) }}</span></td>
                <td class="text-end">{{ number_format($p->stock_minimo, 2) }}</td>
                <td class="text-end fw-bold text-primary">{{ number_format(max(0, $p->stock_optimo - $p->stock_actual), 2) }} {{ $p->unidad_medida }}</td>
                <td>
                    <a href="{{ route('compras.create') }}?producto={{ $p->id }}" class="btn btn-sm btn-warning">
                        <i class="fas fa-truck"></i> Hacer pedido
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
@endsection
