@extends('layouts.app')

@section('title', 'Comprobante ' . ($venta->numero_sunat ?? $venta->numero))

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2 class="mb-0">
            <i class="fas fa-file-signature me-2"></i>
            {{ $venta->numero_sunat ?? $venta->numero }}
        </h2>
        <small class="text-muted">
            @switch($venta->sunat_tipo_comprobante)
                @case('01') Factura electrónica @break
                @case('03') Boleta de venta electrónica @break
                @case('07') Nota de crédito @break
                @case('08') Nota de débito @break
            @endswitch
            · Emisión {{ $venta->fecha?->format('d/m/Y H:i') }}
        </small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('sunat.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Volver
        </a>
        @if($venta->xml_path)
            <a href="{{ route('sunat.xml', $venta) }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-file-code me-1"></i>XML firmado
            </a>
        @endif
        @if($venta->cdr_path)
            <a href="{{ route('sunat.cdr', $venta) }}" class="btn btn-outline-success btn-sm">
                <i class="fas fa-file-archive me-1"></i>CDR SUNAT
            </a>
        @endif
        <a href="{{ route('sunat.pdf', $venta) }}" target="_blank" class="btn btn-outline-danger btn-sm">
            <i class="fas fa-file-pdf me-1"></i>PDF
        </a>
        @if(in_array($venta->estado_sunat, ['pendiente','error','rechazado']))
            <form method="POST" action="{{ route('sunat.reintentar', $venta) }}" class="d-inline">
                @csrf
                <button class="btn btn-warning btn-sm">
                    <i class="fas fa-redo me-1"></i>Reintentar envío
                </button>
            </form>
        @endif
    </div>
</div>

@if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
@if(session('warning')) <div class="alert alert-warning">{{ session('warning') }}</div> @endif
@if(session('error'))   <div class="alert alert-danger">{{ session('error') }}</div> @endif

<div class="row g-3">
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header"><strong>Detalle</strong></div>
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-md-6">
                        <small class="text-muted d-block">Cliente</small>
                        <strong>{{ $venta->cliente?->razon_social ?: $venta->cliente?->nombre ?: ($venta->datos_cliente['nombre'] ?? '—') }}</strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Documento</small>
                        <strong>
                            @php $dc = $venta->datos_cliente ?? []; @endphp
                            {{ $venta->cliente?->tipo_documento_sunat ?? ($dc['tipo_documento_sunat'] ?? '—') }}
                            ·
                            {{ $venta->cliente?->numero_documento ?? ($dc['numero_documento'] ?? '—') }}
                        </strong>
                    </div>
                </div>
                <table class="table table-sm mt-3">
                    <thead><tr><th>Descripción</th><th class="text-end">Cant.</th><th class="text-end">P. Unit.</th><th class="text-end">Total</th></tr></thead>
                    <tbody>
                        @foreach($venta->lineas as $l)
                        <tr>
                            <td>{{ $l->descripcion }}</td>
                            <td class="text-end">{{ rtrim(rtrim(number_format($l->cantidad, 3), '0'), '.') }}</td>
                            <td class="text-end">{{ number_format($l->precio_unitario, 2) }}</td>
                            <td class="text-end">{{ number_format($l->total, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr><th colspan="3" class="text-end">Base imponible</th><th class="text-end">{{ number_format($venta->base_imponible, 2) }}</th></tr>
                        <tr><th colspan="3" class="text-end">IGV</th><th class="text-end">{{ number_format($venta->impuestos, 2) }}</th></tr>
                        <tr><th colspan="3" class="text-end">Total {{ $venta->moneda }}</th><th class="text-end">{{ number_format($venta->total, 2) }}</th></tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><strong>Historial SUNAT</strong></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr><th>Fecha</th><th>Acción</th><th>Modo</th><th>OK</th><th>Código</th><th>Mensaje</th></tr>
                    </thead>
                    <tbody>
                        @forelse($venta->logsSunat as $log)
                        <tr>
                            <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                            <td>{{ $log->accion }}</td>
                            <td><span class="badge bg-{{ $log->modo === 'produccion' ? 'danger' : 'secondary' }}">{{ $log->modo }}</span></td>
                            <td>{!! $log->exitoso ? '<span class="text-success">✓</span>' : '<span class="text-danger">✗</span>' !!}</td>
                            <td>{{ $log->codigo_respuesta }}</td>
                            <td><small>{{ $log->mensaje }}</small></td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-muted text-center py-3">Sin movimientos SUNAT.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header"><strong>Estado SUNAT</strong></div>
            <div class="card-body">
                <span class="badge bg-{{ $venta->estado_sunat_clase }} fs-6">
                    {{ strtoupper($venta->estado_sunat) }}
                </span>
                @if($venta->codigo_sunat)
                    <div class="mt-2"><small class="text-muted">Código:</small> <code>{{ $venta->codigo_sunat }}</code></div>
                @endif
                @if($venta->mensaje_sunat)
                    <div class="mt-2"><small class="text-muted">Mensaje:</small><br>{{ $venta->mensaje_sunat }}</div>
                @endif
                @if($venta->fecha_envio_sunat)
                    <div class="mt-2"><small class="text-muted">Último envío:</small><br>{{ $venta->fecha_envio_sunat->format('d/m/Y H:i:s') }}</div>
                @endif
                @if($venta->hash)
                    <div class="mt-2"><small class="text-muted">Hash:</small><br><code class="small">{{ $venta->hash }}</code></div>
                @endif
            </div>
        </div>

        @if(in_array($venta->sunat_tipo_comprobante, ['01','03']) && $venta->estado_sunat === 'aceptado')
        <div class="card">
            <div class="card-header text-bg-danger"><strong>Anular (emite Nota de Crédito)</strong></div>
            <form method="POST" action="{{ route('sunat.anular', $venta) }}">
                @csrf
                <div class="card-body">
                    <div class="mb-2">
                        <label class="form-label small">Motivo (catálogo 09 SUNAT)</label>
                        <select name="codigo_motivo_nota" class="form-select form-select-sm" required>
                            <option value="01">01 - Anulación de la operación</option>
                            <option value="02">02 - Anulación por error en el RUC</option>
                            <option value="03">03 - Corrección por error en la descripción</option>
                            <option value="04">04 - Descuento global</option>
                            <option value="05">05 - Descuento por ítem</option>
                            <option value="06">06 - Devolución total</option>
                            <option value="07">07 - Devolución por ítem</option>
                            <option value="09">09 - Otros conceptos</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Descripción del motivo</label>
                        <textarea name="motivo_nota" rows="2" class="form-control form-control-sm" required>Anulación de la operación</textarea>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button class="btn btn-danger btn-sm" onclick="return confirm('¿Emitir nota de crédito anulando este comprobante?')">
                        <i class="fas fa-ban me-1"></i>Anular
                    </button>
                </div>
            </form>
        </div>
        @endif
    </div>
</div>
@endsection
