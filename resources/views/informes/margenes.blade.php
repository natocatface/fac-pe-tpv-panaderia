@extends('layouts.app')
@section('title', 'Márgenes')
@section('page-title', 'Análisis de márgenes')

@section('content')
<div class="page-header">
    <h1 class="page-title"><i class="fas fa-percentage"></i> Márgenes de beneficio</h1>
    <a href="{{ route('informes.index') }}" class="btn btn-light"><i class="fas fa-arrow-left"></i> Volver</a>
</div>

<div class="card">
    <table class="table">
        <thead><tr><th>Producto</th><th>Categoría</th><th class="text-end">P. Compra</th><th class="text-end">P. Venta</th><th class="text-end">Margen €</th><th class="text-end">Margen %</th></tr></thead>
        <tbody>
            @foreach($productos as $row)
            <tr>
                <td><strong>{{ $row->producto->nombre }}</strong></td>
                <td>{{ $row->producto->categoria?->nombre ?? '—' }}</td>
                <td class="text-end">{{ $appConfig?->formatearMoneda($row->producto->precio_compra) }}</td>
                <td class="text-end">{{ $appConfig?->formatearMoneda($row->producto->precio_venta) }}</td>
                <td class="text-end fw-bold {{ $row->margen > 0 ? 'text-success' : 'text-danger' }}">{{ $appConfig?->formatearMoneda($row->margen) }}</td>
                <td class="text-end">
                    <span class="badge badge-{{ $row->margen_pct > 50 ? 'success' : ($row->margen_pct > 20 ? 'warning' : 'danger') }}">
                        {{ number_format($row->margen_pct, 1) }}%
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
