@extends('layouts.app')
@section('title', $compra->numero)
@section('page-title', 'Pedido ' . $compra->numero)

@section('content')
<div class="page-header">
    <h1 class="page-title">Pedido <span class="text-primary">{{ $compra->numero }}</span></h1>
    <div class="d-flex gap-2">
        @if($compra->estado == 'pendiente')
        <form method="POST" action="{{ route('compras.recibir', $compra) }}" onsubmit="return confirm('¿Confirmar recepción? Se actualizará el stock.')">
            @csrf
            <button class="btn btn-success"><i class="fas fa-check-double"></i> Confirmar recepción</button>
        </form>
        @endif
        <a href="{{ route('compras.index') }}" class="btn btn-light"><i class="fas fa-arrow-left"></i> Volver</a>
    </div>
</div>

<div class="row-grid cols-2 mb-3">
    <div class="card">
        <div class="card-header"><h5>Información del pedido</h5></div>
        <div class="card-body">
            <p><strong>Proveedor:</strong> {{ $compra->proveedor->nombre }}</p>
            <p><strong>Fecha pedido:</strong> {{ $compra->fecha->format('d/m/Y') }}</p>
            @if($compra->fecha_recepcion)<p><strong>Fecha recepción:</strong> {{ $compra->fecha_recepcion->format('d/m/Y H:i') }}</p>@endif
            <p><strong>Estado:</strong> <span class="badge badge-{{ $compra->estado == 'recibida' ? 'success' : 'warning' }}">{{ ucfirst($compra->estado) }}</span></p>
            @if($compra->observaciones)<p><strong>Observaciones:</strong> {{ $compra->observaciones }}</p>@endif
        </div>
    </div>
    <div class="card">
        <div class="card-header"><h5>Totales</h5></div>
        <div class="card-body">
            <div class="d-flex justify-content-between" style="padding: 6px 0;"><span class="text-muted">Subtotal:</span><span class="fw-bold">{{ $appConfig?->formatearMoneda($compra->subtotal) }}</span></div>
            <div class="d-flex justify-content-between" style="padding: 6px 0;"><span class="text-muted">IVA:</span><span class="fw-bold">{{ $appConfig?->formatearMoneda($compra->impuestos) }}</span></div>
            <div class="d-flex justify-content-between" style="padding: 12px 0; font-size: 22px; font-weight: 800; border-top: 2px solid var(--border-color); margin-top: 8px;"><span>TOTAL:</span><span class="text-primary">{{ $appConfig?->formatearMoneda($compra->total) }}</span></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h5>Líneas del pedido</h5></div>
    <table class="table">
        <thead><tr><th>Producto</th><th class="text-end">Cantidad</th><th class="text-end">Precio</th><th class="text-end">IVA</th><th class="text-end">Subtotal</th></tr></thead>
        <tbody>
            @foreach($compra->lineas as $l)
            <tr>
                <td><strong>{{ $l->descripcion }}</strong></td>
                <td class="text-end">{{ number_format($l->cantidad, 2) }}</td>
                <td class="text-end">{{ $appConfig?->formatearMoneda($l->precio_unitario) }}</td>
                <td class="text-end">{{ number_format($l->iva, 0) }}%</td>
                <td class="text-end fw-bold">{{ $appConfig?->formatearMoneda($l->total) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
