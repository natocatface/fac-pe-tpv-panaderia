@extends('layouts.app')
@section('title', 'Compras')
@section('page-title', 'Compras y pedidos')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-shopping-cart"></i> Compras y pedidos</h1>
        <p class="page-subtitle">{{ $compras->total() }} pedidos registrados</p>
    </div>
    <a href="{{ route('compras.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Nuevo pedido</a>
</div>

<div class="card mb-3"><div class="card-body">
    <form method="GET" style="display: grid; grid-template-columns: 1fr 200px 200px auto; gap: 12px;">
        <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Número de pedido...">
        <select name="proveedor" class="form-select">
            <option value="">Todos los proveedores</option>
            @foreach($proveedores as $p)
                <option value="{{ $p->id }}" {{ request('proveedor') == $p->id ? 'selected' : '' }}>{{ $p->nombre }}</option>
            @endforeach
        </select>
        <select name="estado" class="form-select">
            <option value="">Todos los estados</option>
            <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
            <option value="recibida" {{ request('estado') == 'recibida' ? 'selected' : '' }}>Recibida</option>
            <option value="parcial" {{ request('estado') == 'parcial' ? 'selected' : '' }}>Parcial</option>
            <option value="cancelada" {{ request('estado') == 'cancelada' ? 'selected' : '' }}>Cancelada</option>
        </select>
        <button class="btn btn-primary"><i class="fas fa-filter"></i></button>
    </form>
</div></div>

<div class="card">
    <table class="table">
        <thead><tr><th>Número</th><th>Fecha</th><th>Proveedor</th><th>Ref. proveedor</th><th class="text-end">Total</th><th>Estado</th><th></th></tr></thead>
        <tbody>
            @forelse($compras as $c)
            <tr>
                <td><span class="fw-bold">{{ $c->numero }}</span></td>
                <td>{{ $c->fecha->format('d/m/Y') }}</td>
                <td>{{ $c->proveedor?->nombre }}</td>
                <td>{{ $c->referencia_proveedor ?? '—' }}</td>
                <td class="text-end fw-bold">{{ $appConfig?->formatearMoneda($c->total) }}</td>
                <td>
                    @php $clases = ['pendiente' => 'warning', 'recibida' => 'success', 'parcial' => 'info', 'cancelada' => 'danger']; @endphp
                    <span class="badge badge-{{ $clases[$c->estado] ?? 'secondary' }}">{{ ucfirst($c->estado) }}</span>
                </td>
                <td>
                    <a href="{{ route('compras.show', $c) }}" class="btn btn-sm btn-light"><i class="fas fa-eye"></i></a>
                    @if($c->estado == 'pendiente')
                    <form action="{{ route('compras.recibir', $c) }}" method="POST" style="display:inline;" onsubmit="return confirm('¿Confirmar recepción? Se actualizará el stock.')">
                        @csrf
                        <button class="btn btn-sm btn-success"><i class="fas fa-check"></i> Recibir</button>
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center text-muted" style="padding: 30px;">Sin pedidos de compra</td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="padding: 16px;">{{ $compras->withQueryString()->links() }}</div>
</div>
@endsection
