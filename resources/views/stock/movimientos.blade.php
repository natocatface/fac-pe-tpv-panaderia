@extends('layouts.app')
@section('title', 'Movimientos de stock')
@section('page-title', 'Movimientos de stock')

@section('content')
<div class="page-header">
    <h1 class="page-title"><i class="fas fa-history"></i> Movimientos de stock</h1>
    <a href="{{ route('stock.index') }}" class="btn btn-light"><i class="fas fa-arrow-left"></i> Volver</a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" style="display: grid; grid-template-columns: 1fr 200px 200px 200px auto; gap: 12px;">
            <select name="producto" class="form-select">
                <option value="">Todos los productos</option>
                @foreach($productos as $p)
                    <option value="{{ $p->id }}" {{ request('producto') == $p->id ? 'selected' : '' }}>{{ $p->nombre }}</option>
                @endforeach
            </select>
            <select name="tipo" class="form-select">
                <option value="">Todos los tipos</option>
                <option value="venta" {{ request('tipo') == 'venta' ? 'selected' : '' }}>Venta</option>
                <option value="compra" {{ request('tipo') == 'compra' ? 'selected' : '' }}>Compra</option>
                <option value="merma" {{ request('tipo') == 'merma' ? 'selected' : '' }}>Merma</option>
                <option value="ajuste" {{ request('tipo') == 'ajuste' ? 'selected' : '' }}>Ajuste</option>
            </select>
            <input type="date" name="desde" value="{{ request('desde') }}" class="form-control">
            <input type="date" name="hasta" value="{{ request('hasta') }}" class="form-control">
            <button class="btn btn-primary"><i class="fas fa-filter"></i></button>
        </form>
    </div>
</div>

<div class="card">
    <table class="table">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Producto</th>
                <th>Tipo</th>
                <th class="text-end">Cantidad</th>
                <th class="text-end">Anterior</th>
                <th class="text-end">Nuevo</th>
                <th>Motivo</th>
                <th>Usuario</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movimientos as $m)
            <tr>
                <td><small>{{ $m->fecha->format('d/m/Y H:i') }}</small></td>
                <td>{{ $m->producto?->nombre }}</td>
                <td>
                    @php
                        $colors = ['venta' => 'danger', 'compra' => 'success', 'merma' => 'warning', 'ajuste' => 'info', 'entrada' => 'success', 'salida' => 'danger', 'elaboracion' => 'info'];
                    @endphp
                    <span class="badge badge-{{ $colors[$m->tipo] ?? 'secondary' }}">{{ ucfirst($m->tipo) }}</span>
                </td>
                <td class="text-end {{ $m->cantidad < 0 ? 'text-danger' : 'text-success' }} fw-bold">
                    {{ $m->cantidad > 0 ? '+' : '' }}{{ number_format($m->cantidad, 3) }}
                </td>
                <td class="text-end text-muted">{{ number_format($m->stock_anterior, 2) }}</td>
                <td class="text-end fw-bold">{{ number_format($m->stock_nuevo, 2) }}</td>
                <td>{{ $m->motivo }}</td>
                <td><small>{{ $m->user?->name ?? '—' }}</small></td>
            </tr>
            @empty
            <tr><td colspan="8" class="text-center text-muted" style="padding: 30px;">Sin movimientos</td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="padding: 16px;">{{ $movimientos->withQueryString()->links() }}</div>
</div>
@endsection
