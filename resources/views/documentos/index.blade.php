@extends('layouts.app')
@section('title', 'Documentos')
@section('page-title', 'Documentos comerciales')

@section('content')
<div class="page-header">
    <h1 class="page-title"><i class="fas fa-file-invoice"></i> Documentos comerciales</h1>
</div>

<div class="row-grid cols-2">
    <a href="{{ route('documentos.tickets') }}" class="card" style="text-decoration:none; color:inherit;">
        <div class="card-body" style="padding: 24px;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div style="font-size: 12px; color: var(--text-muted); text-transform:uppercase;">Tickets</div>
                    <div style="font-size: 32px; font-weight: 800; color: var(--color-chocolate);">{{ $tickets }}</div>
                </div>
                <div style="width: 60px; height: 60px; background: rgba(200,118,61,0.12); border-radius: 16px; display:flex; align-items:center; justify-content:center; font-size: 28px; color: var(--color-primary);">
                    <i class="fas fa-receipt"></i>
                </div>
            </div>
        </div>
    </a>
    <a href="{{ route('documentos.facturas') }}" class="card" style="text-decoration:none; color:inherit;">
        <div class="card-body" style="padding: 24px;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div style="font-size: 12px; color: var(--text-muted); text-transform:uppercase;">Facturas</div>
                    <div style="font-size: 32px; font-weight: 800; color: var(--color-chocolate);">{{ $facturas }}</div>
                </div>
                <div style="width: 60px; height: 60px; background: rgba(40,167,69,0.12); border-radius: 16px; display:flex; align-items:center; justify-content:center; font-size: 28px; color: var(--color-success);">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
            </div>
        </div>
    </a>
    <a href="{{ route('documentos.presupuestos') }}" class="card" style="text-decoration:none; color:inherit;">
        <div class="card-body" style="padding: 24px;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div style="font-size: 12px; color: var(--text-muted); text-transform:uppercase;">Presupuestos</div>
                    <div style="font-size: 32px; font-weight: 800; color: var(--color-chocolate);">{{ $presupuestos }}</div>
                </div>
                <div style="width: 60px; height: 60px; background: rgba(23,162,184,0.12); border-radius: 16px; display:flex; align-items:center; justify-content:center; font-size: 28px; color: var(--color-info);">
                    <i class="fas fa-file-contract"></i>
                </div>
            </div>
        </div>
    </a>
    <a href="{{ route('documentos.albaranes') }}" class="card" style="text-decoration:none; color:inherit;">
        <div class="card-body" style="padding: 24px;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div style="font-size: 12px; color: var(--text-muted); text-transform:uppercase;">Albaranes</div>
                    <div style="font-size: 32px; font-weight: 800; color: var(--color-chocolate);">{{ $albaranes }}</div>
                </div>
                <div style="width: 60px; height: 60px; background: rgba(244,168,51,0.12); border-radius: 16px; display:flex; align-items:center; justify-content:center; font-size: 28px; color: #f4a833;">
                    <i class="fas fa-truck-loading"></i>
                </div>
            </div>
        </div>
    </a>
</div>
@endsection
