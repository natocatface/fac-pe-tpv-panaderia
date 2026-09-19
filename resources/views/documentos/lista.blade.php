@extends('layouts.app')
@section('title', $titulo)
@section('page-title', $titulo)

@section('content')
<div class="page-header">
    <h1 class="page-title">{{ $titulo }}</h1>
    @if(in_array($tipo, ['factura', 'presupuesto', 'albaran']))
    <a href="{{ route('documentos.crear', $tipo) }}" class="btn btn-primary"><i class="fas fa-plus"></i> Crear {{ Str::singular(strtolower($titulo)) }}</a>
    @endif
</div>

<div class="card mb-3"><div class="card-body">
    <form method="GET" style="display: grid; grid-template-columns: 1fr 1fr 200px 200px auto; gap: 12px;">
        <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Número de documento...">
        <select name="cliente" class="form-select">
            <option value="">Todos los clientes</option>
            @foreach($clientes as $c)
                <option value="{{ $c->id }}" {{ request('cliente') == $c->id ? 'selected' : '' }}>{{ $c->nombre }}</option>
            @endforeach
        </select>
        <input type="date" name="desde" value="{{ request('desde') }}" class="form-control">
        <input type="date" name="hasta" value="{{ request('hasta') }}" class="form-control">
        <button class="btn btn-primary"><i class="fas fa-filter"></i></button>
    </form>
</div></div>

<div class="card">
    <table class="table">
        <thead>
            <tr>
                <th>Número</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Usuario</th>
                <th class="text-end">Total</th>
                <th>Forma pago</th>
                <th>Estado</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($documentos as $d)
            <tr>
                <td><span class="fw-bold">{{ $d->numero }}</span></td>
                <td><small>{{ $d->fecha->format('d/m/Y H:i') }}</small></td>
                <td>{{ $d->cliente?->nombre ?? 'Cliente contado' }}</td>
                <td><small>{{ $d->user?->name }}</small></td>
                <td class="text-end fw-bold">{{ $appConfig?->formatearMoneda($d->total) }}</td>
                <td><span class="badge badge-secondary">{{ ucfirst($d->forma_pago) }}</span></td>
                <td><span class="badge badge-{{ $d->estado_clase }}">{{ ucfirst($d->estado) }}</span></td>
                <td>
                    <a href="{{ route('documentos.show', [$tipo, $d->id]) }}" class="btn btn-sm btn-light"><i class="fas fa-eye"></i></a>
                    <a href="{{ route('documentos.pdf', [$tipo, $d->id]) }}" target="_blank" class="btn btn-sm btn-primary"><i class="fas fa-file-pdf"></i></a>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="text-center text-muted" style="padding: 30px;">Sin documentos</td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="padding: 16px;">{{ $documentos->withQueryString()->links() }}</div>
</div>
@endsection
