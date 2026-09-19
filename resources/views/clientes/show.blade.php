@extends('layouts.app')
@section('title', $cliente->nombre)
@section('content')
<div class="page-header">
    <h1 class="page-title">{{ $cliente->nombre }}</h1>
    <a href="{{ route('clientes.edit', $cliente) }}" class="btn btn-primary"><i class="fas fa-edit"></i> Editar</a>
</div>

<div class="card">
    <div class="card-header"><h5>Últimas compras</h5></div>
    <table class="table">
        <thead><tr><th>Documento</th><th>Fecha</th><th class="text-end">Total</th><th>Estado</th></tr></thead>
        <tbody>
            @forelse($cliente->ventas as $v)
            <tr>
                <td>{{ $v->numero }}</td>
                <td>{{ $v->fecha->format('d/m/Y H:i') }}</td>
                <td class="text-end">{{ $appConfig?->formatearMoneda($v->total) }}</td>
                <td><span class="badge badge-{{ $v->estado_clase }}">{{ ucfirst($v->estado) }}</span></td>
            </tr>
            @empty
            <tr><td colspan="4" class="text-center text-muted">Sin compras</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
