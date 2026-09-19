@extends('layouts.app')
@section('title', 'Stock')
@section('page-title', 'Control de stock')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-boxes-stacked"></i> Control de stock</h1>
        <p class="page-subtitle">Supervisa existencias, mermas y movimientos</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('stock.movimientos') }}" class="btn btn-outline-primary"><i class="fas fa-history"></i> Movimientos</a>
        <a href="{{ route('stock.mermas') }}" class="btn btn-outline-primary"><i class="fas fa-trash-alt"></i> Mermas</a>
        <a href="{{ route('stock.alertas') }}" class="btn btn-warning"><i class="fas fa-exclamation-triangle"></i> Alertas</a>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card accent-info">
        <div class="stat-icon"><i class="fas fa-cubes"></i></div>
        <div class="stat-label">Total productos con stock</div>
        <div class="stat-value">{{ $totalProductos }}</div>
    </div>
    <div class="stat-card accent-danger">
        <div class="stat-icon"><i class="fas fa-exclamation-circle"></i></div>
        <div class="stat-label">Productos en alerta</div>
        <div class="stat-value">{{ $stockBajo }}</div>
    </div>
    <div class="stat-card accent-success">
        <div class="stat-icon"><i class="fas fa-euro-sign"></i></div>
        <div class="stat-label">Valor inventario</div>
        <div class="stat-value">{{ $appConfig?->formatearMoneda($valorInventario) }}</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5>Existencias</h5>
        <button class="btn btn-sm btn-outline-primary" onclick="document.getElementById('modal-ajuste').style.display='flex'">
            <i class="fas fa-sliders-h"></i> Ajustar stock
        </button>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Categoría</th>
                <th class="text-end">Stock actual</th>
                <th class="text-end">Stock mínimo</th>
                <th class="text-end">Stock óptimo</th>
                <th class="text-end">Valor</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($productos as $p)
            <tr>
                <td><strong>{{ $p->nombre }}</strong><br><small class="text-muted">{{ $p->codigo }}</small></td>
                <td>{{ $p->categoria?->nombre ?? '—' }}</td>
                <td class="text-end fw-bold">{{ number_format($p->stock_actual, 2) }} {{ $p->unidad_medida }}</td>
                <td class="text-end text-muted">{{ number_format($p->stock_minimo, 2) }}</td>
                <td class="text-end text-muted">{{ number_format($p->stock_optimo, 2) }}</td>
                <td class="text-end">{{ $appConfig?->formatearMoneda($p->stock_actual * $p->precio_compra) }}</td>
                <td>
                    @if($p->tieneStockBajo())
                        <span class="badge badge-danger">Bajo</span>
                    @elseif($p->stock_actual < $p->stock_optimo)
                        <span class="badge badge-warning">Medio</span>
                    @else
                        <span class="badge badge-success">OK</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div style="padding: 16px;">{{ $productos->links() }}</div>
</div>

<!-- Modal de ajuste -->
<div id="modal-ajuste" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:white; border-radius: 16px; padding: 28px; max-width: 480px; width: 90%;">
        <h3 style="margin: 0 0 16px;">Ajustar stock</h3>
        <form method="POST" action="{{ route('stock.ajuste') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Producto *</label>
                <select name="producto_id" class="form-select" required>
                    <option value="">— Selecciona —</option>
                    @foreach($productos as $p)
                        <option value="{{ $p->id }}">{{ $p->nombre }} (actual: {{ $p->stock_actual }})</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Nuevo stock *</label>
                <input type="number" name="nuevo_stock" step="0.001" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Motivo *</label>
                <input type="text" name="motivo" class="form-control" placeholder="Ej: recuento físico, corrección..." required>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-light" style="flex:1;" onclick="document.getElementById('modal-ajuste').style.display='none'">Cancelar</button>
                <button type="submit" class="btn btn-primary" style="flex:1;"><i class="fas fa-save"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>
@endsection
