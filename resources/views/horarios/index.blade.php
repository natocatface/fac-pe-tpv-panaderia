@extends('layouts.app')
@section('title', 'Control horario')
@section('page-title', 'Control horario')

@section('content')
<div class="page-header">
    <h1 class="page-title"><i class="fas fa-clock"></i> Control horario</h1>
    <a href="{{ route('horarios.historico') }}" class="btn btn-light"><i class="fas fa-history"></i> Histórico</a>
</div>

<div class="row-grid cols-2 mb-3">
    <div class="card" style="background: linear-gradient(135deg, var(--color-primary), var(--color-secondary)); color: white;">
        <div class="card-body" style="padding: 32px;">
            <h3 style="margin: 0 0 8px;">{{ auth()->user()->name }}</h3>
            <div style="font-size: 14px; opacity: 0.9;">{{ now()->translatedFormat('l, d \d\e F') }}</div>
            <div style="font-size: 48px; font-weight: 800; margin: 16px 0;" id="reloj-fichaje">{{ now()->format('H:i:s') }}</div>

            @if($abierto)
                <p>Jornada iniciada a las <strong>{{ $abierto->entrada->format('H:i') }}</strong></p>
                <p>Tiempo trabajado: <strong id="duracion">--:--:--</strong></p>
                <form method="POST" action="{{ route('horarios.fichar') }}">
                    @csrf
                    <button class="btn btn-danger w-100" style="height: 60px; font-size: 18px;">
                        <i class="fas fa-sign-out-alt"></i> Fichar SALIDA
                    </button>
                </form>
            @else
                <p>No hay jornada iniciada.</p>
                <form method="POST" action="{{ route('horarios.fichar') }}">
                    @csrf
                    <button class="btn btn-success w-100" style="height: 60px; font-size: 18px;">
                        <i class="fas fa-sign-in-alt"></i> Fichar ENTRADA
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h5>Hoy</h5></div>
        <div class="card-body">
            <div class="d-flex justify-content-between mb-2"><span>Total horas:</span><strong>{{ number_format($horasHoy, 2) }} h</strong></div>
            <hr>
            @forelse($hoy as $f)
                <div class="d-flex justify-content-between" style="padding: 6px 0; border-bottom: 1px solid var(--border-color);">
                    <span>{{ $f->entrada->format('H:i') }} → {{ $f->salida?->format('H:i') ?? '...' }}</span>
                    <strong>{{ $f->salida ? number_format($f->horas, 2) . ' h' : '⏱' }}</strong>
                </div>
            @empty
                <p class="text-muted text-center">Sin fichajes hoy</p>
            @endforelse
        </div>
    </div>
</div>

@if(auth()->user()->esEncargado() && $activos->count())
<div class="card mb-3">
    <div class="card-header"><h5><i class="fas fa-user-check"></i> Empleados con jornada activa</h5></div>
    <table class="table">
        <thead><tr><th>Empleado</th><th>Entrada</th><th>Tiempo transcurrido</th></tr></thead>
        <tbody>
            @foreach($activos as $a)
            <tr>
                <td>{{ $a->user->name }}</td>
                <td>{{ $a->entrada->format('d/m H:i') }}</td>
                <td>{{ $a->entrada->diffForHumans(null, true) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

<div class="card">
    <div class="card-header"><h5>Mi semana</h5></div>
    <table class="table">
        <thead><tr><th>Día</th><th>Entrada</th><th>Salida</th><th class="text-end">Horas</th></tr></thead>
        <tbody>
            @forelse($fichajesSemana as $f)
            <tr>
                <td>{{ $f->entrada->translatedFormat('l d') }}</td>
                <td>{{ $f->entrada->format('H:i') }}</td>
                <td>{{ $f->salida?->format('H:i') ?? '—' }}</td>
                <td class="text-end fw-bold">{{ number_format($f->horas, 2) }} h</td>
            </tr>
            @empty
            <tr><td colspan="4" class="text-center text-muted">Sin fichajes esta semana</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@push('scripts')
<script>
setInterval(() => {
    document.getElementById('reloj-fichaje').textContent = new Date().toLocaleTimeString('es-ES');
    @if($abierto)
    const inicio = new Date('{{ $abierto->entrada->toIso8601String() }}');
    const ahora = new Date();
    const diff = ahora - inicio;
    const h = Math.floor(diff / 3600000);
    const m = Math.floor((diff % 3600000) / 60000);
    const s = Math.floor((diff % 60000) / 1000);
    document.getElementById('duracion').textContent = String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
    @endif
}, 1000);
</script>
@endpush
@endsection
