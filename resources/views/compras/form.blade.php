@extends('layouts.app')
@section('title', $compra->exists ? 'Editar pedido' : 'Nuevo pedido')
@section('page-title', $compra->exists ? 'Editar pedido' : 'Nuevo pedido de compra')

@section('content')
<form method="POST" action="{{ $compra->exists ? route('compras.update', $compra) : route('compras.store') }}">
    @csrf @if($compra->exists)@method('PUT')@endif

    <div class="row-grid cols-2 mb-3">
        <div class="card">
            <div class="card-header"><h5>Datos del pedido</h5></div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Proveedor *</label>
                    <select name="proveedor_id" class="form-select" required>
                        <option value="">— Selecciona —</option>
                        @foreach($proveedores as $p)
                            <option value="{{ $p->id }}" {{ $compra->proveedor_id == $p->id ? 'selected' : '' }}>{{ $p->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="row-grid cols-2">
                    <div class="form-group">
                        <label class="form-label">Fecha *</label>
                        <input type="date" name="fecha" class="form-control" required value="{{ old('fecha', $compra->fecha?->format('Y-m-d') ?? date('Y-m-d')) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Ref. proveedor</label>
                        <input type="text" name="referencia_proveedor" class="form-control" value="{{ old('referencia_proveedor', $compra->referencia_proveedor) }}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Observaciones</label>
                    <textarea name="observaciones" class="form-control">{{ old('observaciones', $compra->observaciones) }}</textarea>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5>Resumen</h5></div>
            <div class="card-body">
                <div class="d-flex justify-content-between" style="padding: 6px 0;"><span class="text-muted">Subtotal:</span><span id="r-subtotal" class="fw-bold">0,00 €</span></div>
                <div class="d-flex justify-content-between" style="padding: 6px 0;"><span class="text-muted">IVA:</span><span id="r-iva" class="fw-bold">0,00 €</span></div>
                <div class="d-flex justify-content-between" style="padding: 12px 0; font-size: 22px; font-weight: 800; border-top: 2px solid var(--border-color); margin-top: 8px;"><span>TOTAL:</span><span id="r-total" class="text-primary">0,00 €</span></div>
                <button type="submit" class="btn btn-primary w-100 mt-3"><i class="fas fa-save"></i> Guardar pedido</button>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>Líneas del pedido</h5>
            <button type="button" class="btn btn-sm btn-primary" onclick="addLinea()"><i class="fas fa-plus"></i> Añadir línea</button>
        </div>
        <table class="table" id="tabla-lineas">
            <thead><tr><th>Producto</th><th>Cantidad</th><th>Precio</th><th>IVA</th><th class="text-end">Subtotal</th><th></th></tr></thead>
            <tbody></tbody>
        </table>
    </div>
</form>

<script>
const productos = @json($productos);
const productoSel = @json($productoSel);
let idx = 0;
function addLinea(prod = null) {
    const tbody = document.querySelector('#tabla-lineas tbody');
    const row = document.createElement('tr');
    row.innerHTML = `
        <td>
            <select name="lineas[${idx}][producto_id]" class="form-select" required onchange="setProducto(this, ${idx})">
                <option value="">— Producto —</option>
                ${productos.map(p => `<option value="${p.id}" data-precio="${p.precio_compra}" data-iva="${p.iva}" ${prod && p.id == prod.id ? 'selected' : ''}>${p.nombre}</option>`).join('')}
            </select>
        </td>
        <td><input type="number" name="lineas[${idx}][cantidad]" step="0.001" class="form-control" required value="1" onchange="recalc()"></td>
        <td><input type="number" name="lineas[${idx}][precio_unitario]" step="0.0001" class="form-control" required value="${prod?.precio_compra ?? 0}" onchange="recalc()"></td>
        <td><input type="number" name="lineas[${idx}][iva]" step="0.01" class="form-control" value="${prod?.iva ?? 10}" onchange="recalc()" style="width:80px;"></td>
        <td class="text-end fw-bold linea-subtotal">0,00 €</td>
        <td><button type="button" class="btn btn-sm btn-danger" onclick="this.closest('tr').remove(); recalc();"><i class="fas fa-trash"></i></button></td>
    `;
    tbody.appendChild(row);
    idx++;
    if (prod) recalc();
}
function setProducto(sel, i) {
    const opt = sel.selectedOptions[0];
    const row = sel.closest('tr');
    if (opt.dataset.precio) row.querySelector('input[name*="precio_unitario"]').value = opt.dataset.precio;
    if (opt.dataset.iva) row.querySelector('input[name*="iva"]').value = opt.dataset.iva;
    recalc();
}
function recalc() {
    let subtotal = 0, ivaTotal = 0;
    document.querySelectorAll('#tabla-lineas tbody tr').forEach(row => {
        const c = parseFloat(row.querySelector('input[name*="cantidad"]').value) || 0;
        const p = parseFloat(row.querySelector('input[name*="precio_unitario"]').value) || 0;
        const iva = parseFloat(row.querySelector('input[name*="iva"]').value) || 0;
        const sub = c * p;
        const ivaImp = sub * iva / 100;
        row.querySelector('.linea-subtotal').textContent = (sub + ivaImp).toFixed(2).replace('.', ',') + ' €';
        subtotal += sub;
        ivaTotal += ivaImp;
    });
    document.getElementById('r-subtotal').textContent = subtotal.toFixed(2).replace('.', ',') + ' €';
    document.getElementById('r-iva').textContent = ivaTotal.toFixed(2).replace('.', ',') + ' €';
    document.getElementById('r-total').textContent = (subtotal + ivaTotal).toFixed(2).replace('.', ',') + ' €';
}
if (productoSel) addLinea(productoSel);
else addLinea();
</script>
@endsection
