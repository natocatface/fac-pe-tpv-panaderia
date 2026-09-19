@extends('layouts.app')
@section('title', 'Crear ' . $tipo)
@section('page-title', 'Crear ' . ucfirst($tipo))

@section('content')
<div class="card" style="max-width: 1000px; margin: 0 auto;">
    <div class="card-header"><h5><i class="fas fa-file-plus"></i> Nuevo {{ $tipo }}</h5></div>
    <div class="card-body">
        <p class="text-muted">Para generar tickets, usa el módulo de TPV. Aquí puedes crear facturas, presupuestos o albaranes a clientes.</p>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <div>Sugerencia: usa el <strong>TPV</strong> para tickets rápidos. Crea facturas, presupuestos y albaranes desde una venta existente o duplicando un ticket.</div>
        </div>
        <a href="{{ route('tpv.index') }}" class="btn btn-primary"><i class="fas fa-cash-register"></i> Ir al TPV</a>
    </div>
</div>
@endsection
