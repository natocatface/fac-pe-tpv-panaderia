@extends('layouts.app')
@section('title', 'Mermas')
@section('page-title', 'Mermas y desperdicios')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-trash-alt"></i> Mermas y desperdicios</h1>
        <p class="page-subtitle">Coste del mes en mermas: <strong class="text-danger">{{ $appConfig?->formatearMoneda($totalMes) }}</strong></p>
    </div>
    <button class="btn btn-primary" onclick="document.getElementById('modal-merma').style.display='flex'">
        <i class="fas fa-plus"></i> Registrar merma
    </button>
</div>

<div class="card">
    <table class="table">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Producto</th>
                <th class="text-end">Cantidad</th>
                <th>Motivo</th>
                <th class="text-end">Coste</th>
                <th>Observaciones</th>
                <th>Usuario</th>
            </tr>
        </thead>
        <tbody>
            @forelse($mermas as $m)
            <tr>
                <td><small>{{ $m->fecha->format('d/m/Y H:i') }}</small></td>
                <td><strong>{{ $m->producto?->nombre }}</strong></td>
                <td class="text-end text-danger fw-bold">{{ number_format($m->cantidad, 2) }} {{ $m->producto?->unidad_medida }}</td>
                <td><span class="badge badge-warning">{{ str_replace('_', ' ', ucfirst($m->motivo)) }}</span></td>
                <td class="text-end">{{ $appConfig?->formatearMoneda($m->coste) }}</td>
                <td><small>{{ Str::limit($m->observaciones, 40) }}</small></td>
                <td><small>{{ $m->user?->name }}</small></td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center text-muted" style="padding: 30px;">Sin mermas registradas. ¡Genial!</td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="padding: 16px;">{{ $mermas->links() }}</div>
</div>

<div id="modal-merma" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:white; border-radius: 16px; padding: 28px; max-width: 480px; width: 90%;">
        <h3 style="margin: 0 0 16px;">Registrar merma</h3>
        <form method="POST" action="{{ route('stock.mermas.store') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Producto *</label>
                <select name="producto_id" class="form-select" required>
                    <option value="">— Selecciona —</option>
                    @foreach($productos as $p)
                        <option value="{{ $p->id }}">{{ $p->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="row-grid cols-2">
                <div class="form-group">
                    <label class="form-label">Cantidad *</label>
                    <input type="number" name="cantidad" step="0.001" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Motivo *</label>
                    <select name="motivo" class="form-select" required>
                        <option value="caducidad">Caducidad</option>
                        <option value="rotura">Rotura</option>
                        <option value="mal_estado">Mal estado</option>
                        <option value="error_elaboracion">Error elaboración</option>
                        <option value="devolucion_cliente">Devolución cliente</option>
                        <option value="otros">Otros</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Observaciones</label>
                <textarea name="observaciones" class="form-control"></textarea>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-light" style="flex:1;" onclick="document.getElementById('modal-merma').style.display='none'">Cancelar</button>
                <button type="submit" class="btn btn-primary" style="flex:1;"><i class="fas fa-save"></i> Registrar</button>
            </div>
        </form>
    </div>
</div>
@endsection
