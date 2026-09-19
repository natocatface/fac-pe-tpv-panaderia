@extends('layouts.app')

@section('title', 'Comprobantes electrónicos SUNAT')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2 class="mb-0"><i class="fas fa-file-signature me-2"></i>Comprobantes electrónicos SUNAT</h2>
        <small class="text-muted">Facturas, boletas, notas de crédito y débito enviadas a SUNAT (Perú)</small>
    </div>
    <div>
        <a href="{{ route('configuracion.index') }}#sunat" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-cog me-1"></i>Configuración SUNAT
        </a>
    </div>
</div>

@if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
@if(session('warning')) <div class="alert alert-warning">{{ session('warning') }}</div> @endif
@if(session('error'))   <div class="alert alert-danger">{{ session('error') }}</div> @endif

<form method="GET" class="row g-2 mb-3 align-items-end">
    <div class="col-md-3">
        <label class="form-label small">Tipo comprobante</label>
        <select name="tipo" class="form-select form-select-sm">
            <option value="">Todos</option>
            <option value="01" @selected(request('tipo')==='01')>01 - Factura</option>
            <option value="03" @selected(request('tipo')==='03')>03 - Boleta</option>
            <option value="07" @selected(request('tipo')==='07')>07 - Nota crédito</option>
            <option value="08" @selected(request('tipo')==='08')>08 - Nota débito</option>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label small">Estado SUNAT</label>
        <select name="estado_sunat" class="form-select form-select-sm">
            <option value="">Todos</option>
            @foreach(['pendiente','enviado','aceptado','observado','rechazado','anulado','error'] as $e)
                <option value="{{ $e }}" @selected(request('estado_sunat')===$e)>{{ ucfirst($e) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label small">Desde</label>
        <input type="date" name="desde" value="{{ request('desde') }}" class="form-control form-control-sm">
    </div>
    <div class="col-md-2">
        <label class="form-label small">Hasta</label>
        <input type="date" name="hasta" value="{{ request('hasta') }}" class="form-control form-control-sm">
    </div>
    <div class="col-md-2 d-flex gap-2">
        <button class="btn btn-primary btn-sm w-100"><i class="fas fa-filter"></i> Filtrar</button>
    </div>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Comprobante</th>
                    <th>Tipo</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th class="text-end">Total</th>
                    <th>Estado SUNAT</th>
                    <th>Mensaje</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($comprobantes as $c)
                <tr>
                    <td>
                        <a href="{{ route('sunat.show', $c) }}" class="fw-semibold">
                            {{ $c->numero_sunat ?? $c->numero }}
                        </a>
                    </td>
                    <td>
                        @switch($c->sunat_tipo_comprobante)
                            @case('01') <span class="badge bg-primary">01 · Factura</span> @break
                            @case('03') <span class="badge bg-info text-dark">03 · Boleta</span> @break
                            @case('07') <span class="badge bg-warning text-dark">07 · NC</span> @break
                            @case('08') <span class="badge bg-secondary">08 · ND</span> @break
                            @default   <span class="badge bg-light text-dark">{{ $c->sunat_tipo_comprobante }}</span>
                        @endswitch
                    </td>
                    <td>{{ $c->fecha?->format('d/m/Y H:i') }}</td>
                    <td>{{ $c->cliente?->razon_social ?: $c->cliente?->nombre ?: ($c->datos_cliente['nombre'] ?? '—') }}</td>
                    <td class="text-end">{{ $c->moneda }} {{ number_format($c->total, 2) }}</td>
                    <td>
                        <span class="badge bg-{{ $c->estado_sunat_clase }}">
                            {{ strtoupper($c->estado_sunat) }}
                        </span>
                        @if($c->codigo_sunat)
                            <div class="small text-muted">{{ $c->codigo_sunat }}</div>
                        @endif
                    </td>
                    <td><small>{{ Str::limit($c->mensaje_sunat, 60) }}</small></td>
                    <td class="text-end">
                        <a href="{{ route('sunat.show', $c) }}" class="btn btn-sm btn-outline-primary" title="Ver">
                            <i class="fas fa-eye"></i>
                        </a>
                        @if($c->xml_path)
                            <a href="{{ route('sunat.xml', $c) }}" class="btn btn-sm btn-outline-secondary" title="Descargar XML">
                                <i class="fas fa-file-code"></i>
                            </a>
                        @endif
                        @if($c->cdr_path)
                            <a href="{{ route('sunat.cdr', $c) }}" class="btn btn-sm btn-outline-success" title="Descargar CDR">
                                <i class="fas fa-file-archive"></i>
                            </a>
                        @endif
                        @if(in_array($c->estado_sunat, ['pendiente','error','rechazado']))
                            <form method="POST" action="{{ route('sunat.reintentar', $c) }}" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-outline-warning" title="Reintentar envío">
                                    <i class="fas fa-redo"></i>
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No hay comprobantes electrónicos registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $comprobantes->links() }}</div>
@endsection
