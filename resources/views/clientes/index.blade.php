@extends('layouts.app')
@section('title', 'Clientes')
@section('page-title', 'Clientes')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-users"></i> Clientes</h1>
        <p class="page-subtitle">{{ $clientes->total() }} clientes registrados</p>
    </div>
    <a href="{{ route('clientes.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Nuevo cliente</a>
</div>

<div class="card mb-3"><div class="card-body">
    <form method="GET"><div class="d-flex gap-2">
        <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="🔍 Buscar por nombre, NIF o email...">
        <button class="btn btn-primary"><i class="fas fa-search"></i></button>
    </div></form>
</div></div>

<div class="card">
    <table class="table">
        <thead><tr><th>Cliente</th><th>NIF/CIF</th><th>Contacto</th><th>Ciudad</th><th class="text-end">Descuento</th><th>Estado</th><th></th></tr></thead>
        <tbody>
            @forelse($clientes as $c)
            <tr>
                <td>
                    <a href="{{ route('clientes.edit', $c) }}" class="fw-bold">{{ $c->nombre }}</a>
                    @if($c->razon_social)<br><small class="text-muted">{{ $c->razon_social }}</small>@endif
                </td>
                <td>{{ $c->cif_nif ?? '—' }}</td>
                <td>
                    @if($c->email)<div><i class="fas fa-envelope text-muted"></i> {{ $c->email }}</div>@endif
                    @if($c->telefono)<div><i class="fas fa-phone text-muted"></i> {{ $c->telefono }}</div>@endif
                </td>
                <td>{{ $c->ciudad ?? '—' }}</td>
                <td class="text-end">{{ number_format($c->descuento, 1) }}%</td>
                <td>{!! $c->activo ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-secondary">Inactivo</span>' !!}</td>
                <td>
                    <a href="{{ route('clientes.edit', $c) }}" class="btn btn-sm btn-light"><i class="fas fa-edit"></i></a>
                    <form action="{{ route('clientes.destroy', $c) }}" method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center text-muted" style="padding: 30px;">Sin clientes</td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="padding: 16px;">{{ $clientes->links() }}</div>
</div>
@endsection
