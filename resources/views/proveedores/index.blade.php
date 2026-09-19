@extends('layouts.app')
@section('title', 'Proveedores')
@section('page-title', 'Proveedores')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-truck"></i> Proveedores</h1>
        <p class="page-subtitle">{{ $proveedores->total() }} proveedores registrados</p>
    </div>
    <a href="{{ route('proveedores.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Nuevo proveedor</a>
</div>

<div class="card mb-3"><div class="card-body">
    <form method="GET"><div class="d-flex gap-2">
        <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="🔍 Buscar proveedor...">
        <button class="btn btn-primary"><i class="fas fa-search"></i></button>
    </div></form>
</div></div>

<div class="card">
    <table class="table">
        <thead><tr><th>Proveedor</th><th>NIF</th><th>Contacto</th><th>Días entrega</th><th>Estado</th><th></th></tr></thead>
        <tbody>
            @forelse($proveedores as $p)
            <tr>
                <td>
                    <a href="{{ route('proveedores.edit', $p) }}" class="fw-bold">{{ $p->nombre }}</a>
                    @if($p->contacto)<br><small class="text-muted">{{ $p->contacto }}</small>@endif
                </td>
                <td>{{ $p->cif_nif ?? '—' }}</td>
                <td>
                    @if($p->email)<div><i class="fas fa-envelope text-muted"></i> {{ $p->email }}</div>@endif
                    @if($p->telefono)<div><i class="fas fa-phone text-muted"></i> {{ $p->telefono }}</div>@endif
                </td>
                <td>{{ $p->dias_entrega ?? '—' }}</td>
                <td>{!! $p->activo ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-secondary">Inactivo</span>' !!}</td>
                <td>
                    <a href="{{ route('proveedores.edit', $p) }}" class="btn btn-sm btn-light"><i class="fas fa-edit"></i></a>
                    <form action="{{ route('proveedores.destroy', $p) }}" method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center text-muted" style="padding: 30px;">Sin proveedores</td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="padding: 16px;">{{ $proveedores->links() }}</div>
</div>
@endsection
