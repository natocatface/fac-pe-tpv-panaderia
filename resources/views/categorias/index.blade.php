@extends('layouts.app')
@section('title', 'Categorías')
@section('page-title', 'Categorías')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-tags"></i> Categorías de productos</h1>
        <p class="page-subtitle">Organiza tus productos por familia</p>
    </div>
    <a href="{{ route('categorias.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Nueva categoría</a>
</div>

<div class="row-grid cols-3">
    @forelse($categorias as $c)
    <div class="card" style="overflow:hidden;">
        <div style="height: 6px; background: {{ $c->color }};"></div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h4 style="margin:0; color: var(--color-chocolate);">{{ $c->nombre }}</h4>
                <span class="badge badge-primary">{{ $c->productos_count }} prod.</span>
            </div>
            @if($c->descripcion)
                <p class="text-muted" style="font-size: 13px;">{{ $c->descripcion }}</p>
            @endif
            <div class="d-flex gap-2 mt-3">
                <a href="{{ route('categorias.edit', $c) }}" class="btn btn-sm btn-light" style="flex:1;"><i class="fas fa-edit"></i> Editar</a>
                <form action="{{ route('categorias.destroy', $c) }}" method="POST" onsubmit="return confirm('¿Eliminar categoría?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="card"><div class="card-body text-center text-muted" style="padding: 40px;">
        Sin categorías. <a href="{{ route('categorias.create') }}">Crea la primera →</a>
    </div></div>
    @endforelse
</div>
@endsection
