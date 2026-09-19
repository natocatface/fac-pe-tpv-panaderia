@extends('layouts.app')
@section('title', 'Backup y mantenimiento')
@section('page-title', 'Backup y mantenimiento del sistema')

@section('content')
<div class="page-header">
    <h1 class="page-title"><i class="fas fa-database"></i> Backup y mantenimiento</h1>
</div>

<div class="row-grid cols-3 mb-3">
    <form method="POST" action="{{ route('backup.crear') }}">
        @csrf
        <div class="card" style="cursor:pointer; height: 100%;" onclick="this.querySelector('button').click()">
            <div class="card-body text-center" style="padding: 24px;">
                <div style="width: 70px; height: 70px; background: rgba(40,167,69,0.12); border-radius: 18px; display:flex; align-items:center; justify-content:center; font-size: 32px; color: var(--color-success); margin: 0 auto 16px;">
                    <i class="fas fa-download"></i>
                </div>
                <h4>Crear copia de seguridad</h4>
                <p class="text-muted" style="font-size: 13px;">Genera una copia completa de toda la base de datos del sistema</p>
                <button class="btn btn-success mt-3"><i class="fas fa-save"></i> Crear backup ahora</button>
            </div>
        </div>
    </form>

    <div class="card" style="cursor:pointer; height: 100%;" onclick="document.getElementById('modal-restaurar').style.display='flex'">
        <div class="card-body text-center" style="padding: 24px;">
            <div style="width: 70px; height: 70px; background: rgba(23,162,184,0.12); border-radius: 18px; display:flex; align-items:center; justify-content:center; font-size: 32px; color: var(--color-info); margin: 0 auto 16px;">
                <i class="fas fa-upload"></i>
            </div>
            <h4>Restaurar copia</h4>
            <p class="text-muted" style="font-size: 13px;">Sube un archivo de backup .sql para restaurar el sistema</p>
            <button class="btn btn-info mt-3"><i class="fas fa-undo"></i> Restaurar backup</button>
        </div>
    </div>

    <div class="card" style="cursor:pointer; height: 100%;" onclick="document.getElementById('modal-reset').style.display='flex'">
        <div class="card-body text-center" style="padding: 24px;">
            <div style="width: 70px; height: 70px; background: rgba(220,53,69,0.12); border-radius: 18px; display:flex; align-items:center; justify-content:center; font-size: 32px; color: var(--color-danger); margin: 0 auto 16px;">
                <i class="fas fa-power-off"></i>
            </div>
            <h4>Resetear sistema</h4>
            <p class="text-muted" style="font-size: 13px;">Borra todos los datos y prepara el sistema para una empresa nueva</p>
            <button class="btn btn-danger mt-3"><i class="fas fa-exclamation-triangle"></i> Resetear todo</button>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-folder-open"></i> Backups disponibles ({{ $archivos->count() }})</h5>
    </div>
    <table class="table">
        <thead><tr><th>Archivo</th><th>Tamaño</th><th>Fecha</th><th></th></tr></thead>
        <tbody>
            @forelse($archivos as $a)
            <tr>
                <td><i class="fas fa-file-code text-primary"></i> <strong>{{ $a['nombre'] }}</strong></td>
                <td>{{ round($a['tamano'] / 1024, 2) }} KB</td>
                <td>{{ $a['fecha'] }}</td>
                <td>
                    <a href="{{ route('backup.descargar', $a['nombre']) }}" class="btn btn-sm btn-primary"><i class="fas fa-download"></i> Descargar</a>
                    <form action="{{ route('backup.eliminar', $a['nombre']) }}" method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar este backup?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="4" class="text-center text-muted" style="padding: 40px;">
                <i class="fas fa-folder-open" style="font-size: 40px; opacity: 0.3; display:block; margin-bottom: 12px;"></i>
                Sin backups todavía. Crea el primero con el botón superior.
            </td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Modal restaurar -->
<div id="modal-restaurar" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:white; border-radius: 16px; padding: 28px; max-width: 480px; width: 90%;">
        <h3><i class="fas fa-upload"></i> Restaurar copia de seguridad</h3>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <div>Esta acción <strong>reemplazará todos los datos actuales</strong>. Te recomendamos crear primero un backup del estado actual.</div>
        </div>
        <form method="POST" action="{{ route('backup.restaurar') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label class="form-label">Archivo .sql *</label>
                <input type="file" name="backup" accept=".sql" class="form-control" required>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-light" style="flex:1;" onclick="document.getElementById('modal-restaurar').style.display='none'">Cancelar</button>
                <button type="submit" class="btn btn-info" style="flex:1;" onclick="return confirm('¿Restaurar? Se perderán los datos actuales.')"><i class="fas fa-undo"></i> Restaurar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal reset -->
<div id="modal-reset" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:white; border-radius: 16px; padding: 28px; max-width: 520px; width: 90%;">
        <h3 style="color: var(--color-danger);"><i class="fas fa-exclamation-triangle"></i> Resetear sistema</h3>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <div>
                <strong>¡PRECAUCIÓN!</strong> Esta acción borrará permanentemente:
                <ul style="margin: 8px 0 0; padding-left: 20px;">
                    <li>Todos los productos, categorías y stock</li>
                    <li>Clientes y proveedores</li>
                    <li>Ventas, tickets, facturas, presupuestos y albaranes</li>
                    <li>Compras y movimientos</li>
                    <li>Fichajes y horarios</li>
                    <li>Configuración personalizada</li>
                </ul>
                Te recomendamos crear un backup antes de continuar.
            </div>
        </div>
        <form method="POST" action="{{ route('backup.reset') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Escribe <strong>RESETEAR</strong> para confirmar *</label>
                <input type="text" name="confirmacion" class="form-control" pattern="RESETEAR" required placeholder="RESETEAR">
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-light" style="flex:1;" onclick="document.getElementById('modal-reset').style.display='none'">Cancelar</button>
                <button type="submit" class="btn btn-danger" style="flex:1;"><i class="fas fa-power-off"></i> Resetear todo</button>
            </div>
        </form>
    </div>
</div>
@endsection
