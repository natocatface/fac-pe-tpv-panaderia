@extends('layouts.app')
@section('title', 'Usuarios')
@section('page-title', 'Usuarios del sistema')

@section('content')
<div class="page-header">
    <h1 class="page-title"><i class="fas fa-user-shield"></i> Usuarios</h1>
    <a href="{{ route('usuarios.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Nuevo usuario</a>
</div>

<div class="card">
    <table class="table">
        <thead><tr><th></th><th>Nombre</th><th>Email</th><th>Rol</th><th>Estado</th><th></th></tr></thead>
        <tbody>
            @foreach($usuarios as $u)
            <tr>
                <td><img src="{{ $u->avatarUrl() }}" style="width: 36px; height: 36px; border-radius: 50%;"></td>
                <td><strong>{{ $u->name }}</strong></td>
                <td>{{ $u->email }}</td>
                <td><span class="badge badge-primary">{{ ucfirst($u->rol) }}</span></td>
                <td>{!! $u->activo ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-secondary">Inactivo</span>' !!}</td>
                <td>
                    <a href="{{ route('usuarios.edit', $u) }}" class="btn btn-sm btn-light"><i class="fas fa-edit"></i></a>
                    @if($u->id !== auth()->id())
                    <form action="{{ route('usuarios.destroy', $u) }}" method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                    </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div style="padding: 16px;">{{ $usuarios->links() }}</div>
</div>
@endsection
