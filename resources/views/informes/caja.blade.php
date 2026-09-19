@extends('layouts.app')
@section('title', 'Arqueo de caja')
@section('page-title', 'Arqueo de caja')

@section('content')
<div class="page-header">
    <h1 class="page-title"><i class="fas fa-cash-register"></i> Arqueo de caja</h1>
    <a href="{{ route('informes.index') }}" class="btn btn-light"><i class="fas fa-arrow-left"></i> Volver</a>
</div>

<div class="card mb-3"><div class="card-body">
    <form method="GET">
        <div class="d-flex gap-2 align-items-end">
            <div style="flex:1;">
                <label class="form-label">Fecha</label>
                <input type="date" name="fecha" value="{{ $fecha }}" class="form-control">
            </div>
            <button class="btn btn-primary"><i class="fas fa-filter"></i> Ver</button>
        </div>
    </form>
</div></div>

<div class="stats-grid">
    <div class="stat-card accent-success">
        <div class="stat-icon"><i class="fas fa-money-bill"></i></div>
        <div class="stat-label">Efectivo</div>
        <div class="stat-value">{{ $appConfig?->formatearMoneda($efectivo) }}</div>
    </div>
    <div class="stat-card accent-info">
        <div class="stat-icon"><i class="fas fa-credit-card"></i></div>
        <div class="stat-label">Tarjeta</div>
        <div class="stat-value">{{ $appConfig?->formatearMoneda($tarjeta) }}</div>
    </div>
    <div class="stat-card accent-purple">
        <div class="stat-icon"><i class="fas fa-mobile-alt"></i></div>
        <div class="stat-label">Otros</div>
        <div class="stat-value">{{ $appConfig?->formatearMoneda($otros) }}</div>
    </div>
    <div class="stat-card accent-bread">
        <div class="stat-icon"><i class="fas fa-coins"></i></div>
        <div class="stat-label">Total</div>
        <div class="stat-value">{{ $appConfig?->formatearMoneda($efectivo + $tarjeta + $otros) }}</div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h5>Detalle de ventas del día ({{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }})</h5></div>
    <table class="table">
        <thead><tr><th>Hora</th><th>Ticket</th><th>Usuario</th><th>Forma pago</th><th class="text-end">Total</th></tr></thead>
        <tbody>
            @forelse($ventas as $v)
            <tr>
                <td>{{ $v->fecha->format('H:i') }}</td>
                <td>{{ $v->numero }}</td>
                <td>{{ $v->user?->name }}</td>
                <td><span class="badge badge-secondary">{{ ucfirst($v->forma_pago) }}</span></td>
                <td class="text-end fw-bold">{{ $appConfig?->formatearMoneda($v->total) }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center text-muted" style="padding: 30px;">Sin ventas en esta fecha</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
