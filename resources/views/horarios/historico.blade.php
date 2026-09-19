@extends('layouts.app')
@section('title', 'Histórico de fichajes')
@section('page-title', 'Histórico de fichajes')

@section('content')
<div class="page-header">
    <h1 class="page-title"><i class="fas fa-history"></i> Histórico de fichajes</h1>
    <a href="{{ route('horarios.index') }}" class="btn btn-light"><i class="fas fa-arrow-left"></i> Volver</a>
</div>

<div class="card mb-3"><div class="card-body">
    <form method="GET" style="display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 12px;">
        <div><label class="form-label">Usuario</label>
            <select name="usuario" class="form-select">
                <option value="">Todos</option>
                @foreach($usuarios as $u)
                    <option value="{{ $u->id }}" {{ $usuarioId == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                @endforeach
            </select>
        </div>
        <div><label class="form-label">Desde</label><input type="date" name="desde" value="{{ $desde }}" class="form-control"></div>
        <div><label class="form-label">Hasta</label><input type="date" name="hasta" value="{{ $hasta }}" class="form-control"></div>
        <div style="display:flex; align-items:flex-end;"><button class="btn btn-primary"><i class="fas fa-filter"></i></button></div>
    </form>
</div></div>

<div class="card">
    <div class="card-header"><h5>{{ $fichajes->total() }} fichajes · Total: {{ number_format($totalHoras, 2) }} h</h5></div>
    <table class="table">
        <thead><tr><th>Empleado</th><th>Fecha</th><th>Entrada</th><th>Salida</th><th class="text-end">Horas</th></tr></thead>
        <tbody>
            @forelse($fichajes as $f)
            <tr>
                <td>{{ $f->user->name }}</td>
                <td>{{ $f->entrada->format('d/m/Y') }}</td>
                <td>{{ $f->entrada->format('H:i') }}</td>
                <td>{{ $f->salida?->format('H:i') ?? '—' }}</td>
                <td class="text-end fw-bold">{{ number_format($f->horas, 2) }} h</td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center text-muted">Sin fichajes en este periodo</td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="padding: 16px;">{{ $fichajes->withQueryString()->links() }}</div>
</div>
@endsection
