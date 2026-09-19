@extends('layouts.app')
@section('title', 'Informes')
@section('page-title', 'Informes y estadísticas')

@section('content')
<div class="page-header">
    <h1 class="page-title"><i class="fas fa-chart-pie"></i> Informes y estadísticas</h1>
</div>

<div class="row-grid cols-3">
    <a href="{{ route('informes.ventas') }}" class="card" style="text-decoration:none; color:inherit;">
        <div class="card-body" style="padding: 24px;">
            <div style="width: 60px; height: 60px; background: rgba(200,118,61,0.12); border-radius: 16px; display:flex; align-items:center; justify-content:center; font-size: 28px; color: var(--color-primary); margin-bottom: 12px;">
                <i class="fas fa-chart-line"></i>
            </div>
            <h4>Ventas</h4>
            <p class="text-muted" style="font-size: 13px;">Evolución de ventas por día, forma de pago, ticket medio</p>
        </div>
    </a>
    <a href="{{ route('informes.productos') }}" class="card" style="text-decoration:none; color:inherit;">
        <div class="card-body" style="padding: 24px;">
            <div style="width: 60px; height: 60px; background: rgba(40,167,69,0.12); border-radius: 16px; display:flex; align-items:center; justify-content:center; font-size: 28px; color: var(--color-success); margin-bottom: 12px;">
                <i class="fas fa-trophy"></i>
            </div>
            <h4>Productos más vendidos</h4>
            <p class="text-muted" style="font-size: 13px;">Ranking de productos por cantidad e importe</p>
        </div>
    </a>
    <a href="{{ route('informes.margenes') }}" class="card" style="text-decoration:none; color:inherit;">
        <div class="card-body" style="padding: 24px;">
            <div style="width: 60px; height: 60px; background: rgba(23,162,184,0.12); border-radius: 16px; display:flex; align-items:center; justify-content:center; font-size: 28px; color: var(--color-info); margin-bottom: 12px;">
                <i class="fas fa-percentage"></i>
            </div>
            <h4>Márgenes</h4>
            <p class="text-muted" style="font-size: 13px;">Análisis de márgenes de beneficio por producto</p>
        </div>
    </a>
    <a href="{{ route('informes.clientes') }}" class="card" style="text-decoration:none; color:inherit;">
        <div class="card-body" style="padding: 24px;">
            <div style="width: 60px; height: 60px; background: rgba(139,92,246,0.12); border-radius: 16px; display:flex; align-items:center; justify-content:center; font-size: 28px; color: #8b5cf6; margin-bottom: 12px;">
                <i class="fas fa-users"></i>
            </div>
            <h4>Clientes</h4>
            <p class="text-muted" style="font-size: 13px;">Clientes más activos y volumen de compras</p>
        </div>
    </a>
    <a href="{{ route('informes.stock') }}" class="card" style="text-decoration:none; color:inherit;">
        <div class="card-body" style="padding: 24px;">
            <div style="width: 60px; height: 60px; background: rgba(244,168,51,0.12); border-radius: 16px; display:flex; align-items:center; justify-content:center; font-size: 28px; color: #f4a833; margin-bottom: 12px;">
                <i class="fas fa-warehouse"></i>
            </div>
            <h4>Valoración de stock</h4>
            <p class="text-muted" style="font-size: 13px;">Valor total del inventario a coste y venta</p>
        </div>
    </a>
    <a href="{{ route('informes.caja') }}" class="card" style="text-decoration:none; color:inherit;">
        <div class="card-body" style="padding: 24px;">
            <div style="width: 60px; height: 60px; background: rgba(220,53,69,0.12); border-radius: 16px; display:flex; align-items:center; justify-content:center; font-size: 28px; color: var(--color-danger); margin-bottom: 12px;">
                <i class="fas fa-cash-register"></i>
            </div>
            <h4>Arqueo de caja</h4>
            <p class="text-muted" style="font-size: 13px;">Resumen diario de cobros por forma de pago</p>
        </div>
    </a>
</div>
@endsection
