@extends('layouts.app')
@section('title', $documento->numero)
@section('page-title', ucfirst($documento->tipo_documento) . ' ' . $documento->numero)

@section('content')
<div class="page-header">
    <h1 class="page-title">{{ ucfirst($documento->tipo_documento) }} <span class="text-primary">{{ $documento->numero }}</span></h1>
    <div class="d-flex gap-2">
        <a href="{{ route('documentos.pdf', [$documento->tipo_documento, $documento->id]) }}" target="_blank" class="btn btn-primary">
            <i class="fas fa-file-pdf"></i> Descargar PDF
        </a>
        <a href="javascript:window.print()" class="btn btn-light"><i class="fas fa-print"></i> Imprimir</a>
    </div>
</div>

<div class="card">
    <div class="card-body" style="padding: 36px;">
        <div class="d-flex justify-content-between" style="margin-bottom: 32px;">
            <div>
                @if($config->logo)
                    <img src="{{ asset('storage/' . $config->logo) }}" style="max-height: 80px;">
                @endif
                <h2 style="margin: 12px 0 4px;">{{ $config->nombre_empresa }}</h2>
                <div class="text-muted" style="font-size: 13px;">
                    @if($config->cif_nif)CIF: {{ $config->cif_nif }}<br>@endif
                    {{ $config->direccion }}<br>
                    {{ $config->codigo_postal }} {{ $config->ciudad }}<br>
                    {{ $config->telefono }} · {{ $config->email }}
                </div>
            </div>
            <div style="text-align: right;">
                <h1 style="margin: 0 0 12px; color: var(--color-primary); text-transform: uppercase;">{{ $documento->tipo_documento }}</h1>
                <div style="font-size: 24px; font-weight: 800;">{{ $documento->numero }}</div>
                <div class="text-muted">Fecha: {{ $documento->fecha->format('d/m/Y') }}</div>
            </div>
        </div>

        @if($documento->cliente)
        <div class="card" style="margin-bottom: 24px; background: var(--bg-page);">
            <div class="card-body">
                <strong>Cliente:</strong> {{ $documento->cliente->nombre }}<br>
                @if($documento->cliente->cif_nif)CIF/NIF: {{ $documento->cliente->cif_nif }}<br>@endif
                {{ $documento->cliente->direccion }} {{ $documento->cliente->codigo_postal }} {{ $documento->cliente->ciudad }}
            </div>
        </div>
        @endif

        <table class="table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th class="text-end">Cantidad</th>
                    <th class="text-end">Precio</th>
                    <th class="text-end">IVA</th>
                    <th class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($documento->lineas as $l)
                <tr>
                    <td>{{ $l->descripcion }}</td>
                    <td class="text-end">{{ number_format($l->cantidad, 2) }}</td>
                    <td class="text-end">{{ $config->formatearMoneda($l->precio_unitario) }}</td>
                    <td class="text-end">{{ number_format($l->iva, 0) }}%</td>
                    <td class="text-end fw-bold">{{ $config->formatearMoneda($l->total) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div style="display: flex; justify-content: flex-end; margin-top: 24px;">
            <table style="width: 320px;">
                <tr><td>Base imponible:</td><td class="text-end">{{ $config->formatearMoneda($documento->base_imponible) }}</td></tr>
                @if($documento->descuento > 0)
                <tr><td>Descuento:</td><td class="text-end">-{{ $config->formatearMoneda($documento->descuento) }}</td></tr>
                @endif
                <tr><td>IVA:</td><td class="text-end">{{ $config->formatearMoneda($documento->impuestos) }}</td></tr>
                <tr style="font-size: 22px; font-weight: 800; color: var(--color-primary); border-top: 2px solid var(--border-color);"><td style="padding-top: 12px;">TOTAL:</td><td class="text-end" style="padding-top: 12px;">{{ $config->formatearMoneda($documento->total) }}</td></tr>
            </table>
        </div>

        <hr style="margin: 32px 0;">
        <div class="text-muted" style="font-size: 12px;">
            Forma de pago: <strong>{{ ucfirst($documento->forma_pago) }}</strong>
            @if($documento->observaciones)<br>Observaciones: {{ $documento->observaciones }}@endif
        </div>
    </div>
</div>
@endsection
