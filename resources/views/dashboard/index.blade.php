@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Panel de control')
@section('breadcrumb', 'Inicio / Dashboard')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">¡Buen día, {{ explode(' ', auth()->user()->name)[0] }}! 🥐</h1>
        <p class="page-subtitle">{{ now()->translatedFormat('l, d \d\e F \d\e Y') }}</p>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="{{ route('tpv.index') }}" class="btn btn-primary">
            <i class="fas fa-cash-register"></i> Abrir TPV
        </a>
        <a href="{{ route('informes.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-chart-line"></i> Ver informes
        </a>
    </div>
</div>

<!-- KPIs -->
<div class="stats-grid">
    <div class="stat-card accent-bread">
        <div class="stat-icon"><i class="fas fa-euro-sign"></i></div>
        <div class="stat-label">Ventas hoy</div>
        <div class="stat-value">{{ $appConfig?->formatearMoneda($ventasHoy) ?? '€ ' . number_format($ventasHoy, 2, ',', '.') }}</div>
        <div class="stat-trend {{ $variacionVentas < 0 ? 'down' : '' }}">
            <i class="fas fa-arrow-{{ $variacionVentas >= 0 ? 'up' : 'down' }}"></i>
            {{ number_format(abs($variacionVentas), 1) }}% vs ayer
        </div>
    </div>

    <div class="stat-card accent-success">
        <div class="stat-icon"><i class="fas fa-receipt"></i></div>
        <div class="stat-label">Tickets hoy</div>
        <div class="stat-value">{{ $ticketsHoy }}</div>
        <div class="stat-trend">
            Ticket medio: {{ $appConfig?->formatearMoneda($ticketMedio) ?? number_format($ticketMedio, 2) . ' €' }}
        </div>
    </div>

    <div class="stat-card accent-info">
        <div class="stat-icon"><i class="fas fa-calendar-alt"></i></div>
        <div class="stat-label">Ventas del mes</div>
        <div class="stat-value">{{ $appConfig?->formatearMoneda($ventasMes) ?? number_format($ventasMes, 2, ',', '.') . ' €' }}</div>
        <div class="stat-trend">{{ now()->format('F') }}</div>
    </div>

    <div class="stat-card accent-purple">
        <div class="stat-icon"><i class="fas fa-users"></i></div>
        <div class="stat-label">Clientes activos</div>
        <div class="stat-value">{{ $totalClientes }}</div>
        <div class="stat-trend"><i class="fas fa-user-plus"></i> Base de clientes</div>
    </div>

    <div class="stat-card accent-warning">
        <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="stat-label">Stock bajo</div>
        <div class="stat-value">{{ $productosStockBajo }}</div>
        <div class="stat-trend">Productos requieren reposición</div>
    </div>
</div>

<!-- Gráficos -->
<div class="row-grid cols-2" style="margin-bottom: 24px;">
    <div class="card">
        <div class="card-header">
            <h5><i class="fas fa-chart-area"></i> Ventas últimos 7 días</h5>
            <span class="badge badge-primary">Diario</span>
        </div>
        <div class="card-body">
            <div class="chart-container" style="position: relative; height: 280px;">
                <canvas id="chart-ventas"></canvas>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5><i class="fas fa-chart-pie"></i> Ventas por categoría</h5>
            <span class="badge badge-primary">Mes en curso</span>
        </div>
        <div class="card-body">
            <div class="chart-container" style="position: relative; height: 280px;">
                <canvas id="chart-categorias"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Productos top y últimas ventas -->
<div class="row-grid cols-2">
    <div class="card">
        <div class="card-header">
            <h5><i class="fas fa-trophy"></i> Productos más vendidos</h5>
            <a href="{{ route('informes.productos') }}" style="font-size: 12px;">Ver todos →</a>
        </div>
        <div style="overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Producto</th>
                        <th class="text-end">Cantidad</th>
                        <th class="text-end">Importe</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productosTop as $i => $p)
                    <tr>
                        <td><span class="badge badge-primary">{{ $i + 1 }}</span></td>
                        <td>
                            <div style="font-weight: 600;">{{ $p->producto?->nombre ?? 'Producto eliminado' }}</div>
                            <small class="text-muted">{{ $p->producto?->categoria?->nombre }}</small>
                        </td>
                        <td class="text-end fw-bold">{{ number_format($p->total_cantidad, 0) }}</td>
                        <td class="text-end fw-bold text-primary">{{ $appConfig?->formatearMoneda($p->total_importe) ?? number_format($p->total_importe, 2) . ' €' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-muted" style="padding: 30px;">Sin datos aún. ¡Empieza a vender!</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5><i class="fas fa-history"></i> Últimas ventas</h5>
            <a href="{{ route('documentos.tickets') }}" style="font-size: 12px;">Ver todas →</a>
        </div>
        <div style="overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Ticket</th>
                        <th>Cliente</th>
                        <th>Hora</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ultimasVentas as $v)
                    <tr>
                        <td><span class="fw-bold">{{ $v->numero }}</span></td>
                        <td>{{ $v->cliente?->nombre ?? 'Cliente contado' }}</td>
                        <td><small>{{ $v->fecha->format('H:i') }}</small></td>
                        <td class="text-end fw-bold">{{ $appConfig?->formatearMoneda($v->total) ?? number_format($v->total, 2) . ' €' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-muted" style="padding: 30px;">Sin ventas aún</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($stockBajo->count())
<div class="card" style="margin-top: 24px;">
    <div class="card-header">
        <h5><i class="fas fa-exclamation-circle text-warning"></i> Productos con stock bajo</h5>
        <a href="{{ route('stock.alertas') }}" style="font-size: 12px;">Ver todos →</a>
    </div>
    <div style="overflow-x: auto;">
        <table class="table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Categoría</th>
                    <th class="text-end">Stock actual</th>
                    <th class="text-end">Stock mínimo</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stockBajo as $p)
                <tr>
                    <td><span class="fw-bold">{{ $p->nombre }}</span></td>
                    <td>{{ $p->categoria?->nombre ?? '—' }}</td>
                    <td class="text-end"><span class="badge badge-danger">{{ number_format($p->stock_actual, 2) }} {{ $p->unidad_medida }}</span></td>
                    <td class="text-end">{{ number_format($p->stock_minimo, 2) }} {{ $p->unidad_medida }}</td>
                    <td><a href="{{ route('compras.create') }}?producto={{ $p->id }}" class="btn btn-sm btn-warning"><i class="fas fa-truck"></i> Pedir</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@push('scripts')
<script>
    // Gráfico de ventas
    const ventasData = @json($ultimos7dias);
    new Chart(document.getElementById('chart-ventas'), {
        type: 'line',
        data: {
            labels: ventasData.map(d => d.fecha),
            datasets: [{
                label: 'Ventas',
                data: ventasData.map(d => d.total),
                borderColor: '#C8763D',
                backgroundColor: 'rgba(200, 118, 61, 0.15)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#C8763D',
                pointRadius: 5,
                pointHoverRadius: 7,
                borderWidth: 3,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                x: { grid: { display: false } }
            }
        }
    });

    // Gráfico de categorías
    const catData = @json($ventasPorCategoria);
    new Chart(document.getElementById('chart-categorias'), {
        type: 'doughnut',
        data: {
            labels: catData.map(c => c.nombre),
            datasets: [{
                data: catData.map(c => parseFloat(c.total)),
                backgroundColor: catData.map(c => c.color || '#C8763D'),
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: { boxWidth: 12, padding: 10, font: { size: 12 } }
                }
            },
            cutout: '65%',
        }
    });
</script>
@endpush
@endsection
