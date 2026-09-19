@extends('layouts.app')
@section('title', 'Productos más vendidos')
@section('page-title', 'Productos más vendidos')

@section('content')
<div class="page-header">
    <h1 class="page-title"><i class="fas fa-trophy"></i> Ranking de productos</h1>
    <a href="{{ route('informes.index') }}" class="btn btn-light"><i class="fas fa-arrow-left"></i> Volver</a>
</div>

<div class="card mb-3"><div class="card-body">
    <form method="GET" style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 12px;">
        <div><label class="form-label">Desde</label><input type="date" name="desde" value="{{ $desde }}" class="form-control"></div>
        <div><label class="form-label">Hasta</label><input type="date" name="hasta" value="{{ $hasta }}" class="form-control"></div>
        <div style="display:flex; align-items:flex-end;"><button class="btn btn-primary"><i class="fas fa-filter"></i></button></div>
    </form>
</div></div>

<div class="card">
    <table class="table">
        <thead><tr><th>#</th><th>Producto</th><th>Categoría</th><th class="text-end">Cant. vendida</th><th class="text-end">Ventas</th><th class="text-end">Importe</th></tr></thead>
        <tbody>
            @foreach($productos as $i => $p)
            <tr>
                <td><span class="badge badge-{{ $i < 3 ? 'warning' : 'primary' }}">{{ $i + 1 }}</span></td>
                <td><strong>{{ $p->producto?->nombre }}</strong></td>
                <td>{{ $p->producto?->categoria?->nombre ?? '—' }}</td>
                <td class="text-end fw-bold">{{ number_format($p->total_cantidad, 2) }}</td>
                <td class="text-end">{{ $p->num_ventas }}</td>
                <td class="text-end fw-bold text-primary">{{ $appConfig?->formatearMoneda($p->total_importe) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
