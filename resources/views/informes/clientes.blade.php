@extends('layouts.app')
@section('title', 'Informe de clientes')
@section('page-title', 'Clientes')

@section('content')
<div class="page-header">
    <h1 class="page-title"><i class="fas fa-users"></i> Análisis de clientes</h1>
    <a href="{{ route('informes.index') }}" class="btn btn-light"><i class="fas fa-arrow-left"></i> Volver</a>
</div>

<div class="card">
    <table class="table">
        <thead><tr><th>#</th><th>Cliente</th><th>Email</th><th class="text-end">Total compras</th><th class="text-end">Total gastado</th><th>Última compra</th></tr></thead>
        <tbody>
            @foreach($clientes as $i => $c)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td><strong>{{ $c->nombre }}</strong></td>
                <td>{{ $c->email ?? '—' }}</td>
                <td class="text-end">{{ $c->total_compras }}</td>
                <td class="text-end fw-bold text-primary">{{ $appConfig?->formatearMoneda($c->total_gastado) }}</td>
                <td>{{ $c->ultima_compra ? \Carbon\Carbon::parse($c->ultima_compra)->format('d/m/Y') : '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
