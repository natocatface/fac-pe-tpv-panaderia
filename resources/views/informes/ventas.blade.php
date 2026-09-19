@extends('layouts.app')
@section('title', 'Informe de ventas')
@section('page-title', 'Informe de ventas')

@section('content')
<div class="page-header">
    <h1 class="page-title"><i class="fas fa-chart-line"></i> Informe de ventas</h1>
    <a href="{{ route('informes.index') }}" class="btn btn-light"><i class="fas fa-arrow-left"></i> Volver</a>
</div>

<div class="card mb-3"><div class="card-body">
    <form method="GET" style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 12px;">
        <div><label class="form-label">Desde</label><input type="date" name="desde" value="{{ $desde }}" class="form-control"></div>
        <div><label class="form-label">Hasta</label><input type="date" name="hasta" value="{{ $hasta }}" class="form-control"></div>
        <div style="display: flex; align-items: flex-end;"><button class="btn btn-primary"><i class="fas fa-filter"></i> Aplicar</button></div>
    </form>
</div></div>

<div class="stats-grid">
    <div class="stat-card accent-bread">
        <div class="stat-icon"><i class="fas fa-euro-sign"></i></div>
        <div class="stat-label">Total ventas</div>
        <div class="stat-value">{{ $appConfig?->formatearMoneda($totalVentas) }}</div>
    </div>
    <div class="stat-card accent-success">
        <div class="stat-icon"><i class="fas fa-receipt"></i></div>
        <div class="stat-label">Tickets emitidos</div>
        <div class="stat-value">{{ $totalTickets }}</div>
    </div>
    <div class="stat-card accent-info">
        <div class="stat-icon"><i class="fas fa-calculator"></i></div>
        <div class="stat-label">Ticket medio</div>
        <div class="stat-value">{{ $appConfig?->formatearMoneda($ticketMedio) }}</div>
    </div>
    <div class="stat-card accent-warning">
        <div class="stat-icon"><i class="fas fa-file-invoice"></i></div>
        <div class="stat-label">IVA recaudado</div>
        <div class="stat-value">{{ $appConfig?->formatearMoneda($totalIVA) }}</div>
    </div>
</div>

<div class="row-grid cols-2 mb-3">
    <div class="card">
        <div class="card-header"><h5>Ventas por día</h5></div>
        <div class="card-body">
            <div style="position: relative; height: 280px;"><canvas id="chart-dia"></canvas></div>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><h5>Por forma de pago</h5></div>
        <div class="card-body">
            <div style="position: relative; height: 280px;"><canvas id="chart-pago"></canvas></div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const porDia = @json($porDia);
new Chart(document.getElementById('chart-dia'), {
    type: 'bar',
    data: { labels: porDia.map(d => d.fecha), datasets: [{ label: 'Ventas', data: porDia.map(d => d.total), backgroundColor: '#C8763D' }] },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
});
const porPago = @json($porFormaPago);
new Chart(document.getElementById('chart-pago'), {
    type: 'doughnut',
    data: {
        labels: Object.keys(porPago).map(k => k.charAt(0).toUpperCase() + k.slice(1)),
        datasets: [{ data: Object.values(porPago).map(v => v.total), backgroundColor: ['#28a745', '#17a2b8', '#f4a833', '#8b5cf6', '#dc3545'] }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right', labels: { boxWidth: 12, padding: 10 } } } }
});
</script>
@endpush
@endsection
