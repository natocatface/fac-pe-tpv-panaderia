@extends('layouts.app')
@section('title', 'Valoración de stock')
@section('page-title', 'Valoración de stock')

@section('content')
<div class="page-header">
    <h1 class="page-title"><i class="fas fa-warehouse"></i> Valoración del inventario</h1>
    <a href="{{ route('informes.index') }}" class="btn btn-light"><i class="fas fa-arrow-left"></i> Volver</a>
</div>

<div class="stats-grid">
    <div class="stat-card accent-info">
        <div class="stat-icon"><i class="fas fa-euro-sign"></i></div>
        <div class="stat-label">Valor a precio coste</div>
        <div class="stat-value">{{ $appConfig?->formatearMoneda($totalCoste) }}</div>
    </div>
    <div class="stat-card accent-success">
        <div class="stat-icon"><i class="fas fa-money-bill-trend-up"></i></div>
        <div class="stat-label">Valor a precio venta</div>
        <div class="stat-value">{{ $appConfig?->formatearMoneda($totalVenta) }}</div>
    </div>
    <div class="stat-card accent-warning">
        <div class="stat-icon"><i class="fas fa-percent"></i></div>
        <div class="stat-label">Margen potencial</div>
        <div class="stat-value">{{ $appConfig?->formatearMoneda($totalVenta - $totalCoste) }}</div>
    </div>
</div>

<div class="card">
    <table class="table">
        <thead><tr><th>Producto</th><th class="text-end">Stock</th><th class="text-end">P. Compra</th><th class="text-end">Valor coste</th><th class="text-end">P. Venta</th><th class="text-end">Valor venta</th></tr></thead>
        <tbody>
            @foreach($valoracion as $v)
            <tr>
                <td><strong>{{ $v->nombre }}</strong></td>
                <td class="text-end">{{ number_format($v->stock_actual, 2) }} {{ $v->unidad_medida }}</td>
                <td class="text-end">{{ $appConfig?->formatearMoneda($v->precio_compra) }}</td>
                <td class="text-end fw-bold">{{ $appConfig?->formatearMoneda($v->valor_coste) }}</td>
                <td class="text-end">{{ $appConfig?->formatearMoneda($v->precio_venta) }}</td>
                <td class="text-end fw-bold text-success">{{ $appConfig?->formatearMoneda($v->valor_venta) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
