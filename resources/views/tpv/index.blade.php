@extends('layouts.app')

@section('title', 'Punto de Venta')
@section('page-title', 'Punto de Venta')
@section('breadcrumb', 'Inicio / TPV')

@section('content')
<div class="tpv-wrapper">
    <!-- Catálogo -->
    <div class="tpv-products">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <input type="text" id="tpv-search" class="form-control" placeholder="🔍 Buscar por nombre o código..." style="width: 300px;">
            </div>
            <div style="font-size: 13px; color: var(--text-muted);">
                <i class="fas fa-clock"></i> <span id="reloj">{{ now()->format('H:i:s') }}</span>
                · {{ now()->translatedFormat('d M Y') }}
            </div>
        </div>

        <div class="tpv-categories">
            <button class="tpv-category-btn active" data-cat="all">
                <i class="fas fa-th"></i> Todos
            </button>
            @foreach($categorias as $cat)
                <button class="tpv-category-btn" data-cat="{{ $cat->id }}" style="--bs-primary: {{ $cat->color }};">
                    {{ $cat->nombre }}
                </button>
            @endforeach
        </div>

        <div class="tpv-product-grid" id="tpv-grid">
            @foreach($productos as $producto)
                <div class="tpv-product {{ $producto->controla_stock && $producto->stock_actual <= 0 ? 'no-stock' : '' }}"
                     data-id="{{ $producto->id }}"
                     data-cat="{{ $producto->categoria_id }}"
                     data-name="{{ strtolower($producto->nombre) }}"
                     data-price="{{ $producto->precio_venta }}"
                     data-iva="{{ $producto->iva }}"
                     data-stock="{{ $producto->stock_actual }}"
                     data-unidad="{{ $producto->unidad_medida }}">
                    <img src="{{ $producto->imagenUrl() }}" alt="{{ $producto->nombre }}">
                    <div class="name">{{ $producto->nombre }}</div>
                    <div class="price">{{ $config->formatearMoneda($producto->precio_venta) }}</div>
                    @if($producto->controla_stock)
                    <div class="stock">{{ number_format($producto->stock_actual, 0) }} {{ $producto->unidad_medida }}</div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <!-- Carrito -->
    <div class="tpv-cart">
        <div class="tpv-cart-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3><i class="fas fa-shopping-basket"></i> Ticket actual</h3>
                <span id="cart-counter" class="badge" style="background:white; color:var(--color-chocolate);">0 ítems</span>
            </div>
        </div>

        <div class="tpv-cart-customer">
            <i class="fas fa-user-circle" style="color: var(--color-primary); font-size: 18px;"></i>
            <select id="cliente-select" class="form-control" style="flex:1; height: 36px;">
                <option value="">Cliente contado</option>
                @foreach($clientes as $c)
                    <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div class="tpv-cart-items" id="cart-items">
            <div class="tpv-cart-empty">
                <i class="fas fa-shopping-basket"></i>
                <p>Toca productos para añadirlos al ticket</p>
            </div>
        </div>

        <div class="tpv-cart-totals">
            <div class="row"><span>Subtotal</span><span id="t-subtotal">{{ $config->formatearMoneda(0) }}</span></div>
            <div class="row"><span>Descuento</span><span id="t-descuento">{{ $config->formatearMoneda(0) }}</span></div>
            <div class="row"><span>IVA</span><span id="t-iva">{{ $config->formatearMoneda(0) }}</span></div>
            <div class="row total"><span>TOTAL</span><span id="t-total">{{ $config->formatearMoneda(0) }}</span></div>
        </div>

        <div class="tpv-cart-actions">
            <button class="btn btn-light" onclick="vaciarCarrito()">
                <i class="fas fa-trash"></i> Vaciar
            </button>
            <button class="btn btn-warning" onclick="aplicarDescuento()">
                <i class="fas fa-percent"></i> Descuento
            </button>
        </div>

        <div class="tpv-cart-pay">
            <button class="btn btn-success" onclick="abrirPago()" id="btn-pago" disabled>
                <i class="fas fa-credit-card"></i> COBRAR
            </button>
        </div>
    </div>
</div>

<!-- Modal de pago -->
<div id="modal-pago" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:white; border-radius: 16px; padding: 28px; max-width: 480px; width: 90%;">
        <h3 style="margin: 0 0 16px; color: var(--color-chocolate);"><i class="fas fa-cash-register"></i> Cobrar venta</h3>
        <div style="text-align:center; padding: 20px; background: var(--bg-page); border-radius: 12px; margin-bottom: 20px;">
            <div style="font-size: 12px; color: var(--text-muted); text-transform: uppercase;">Total a cobrar</div>
            <div id="modal-total" style="font-size: 40px; font-weight: 800; color: var(--color-primary);">{{ $config->formatearMoneda(0) }}</div>
        </div>

        <div class="form-group">
            <label class="form-label">Forma de pago</label>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;">
                <button type="button" class="btn btn-outline-primary forma-pago-btn active" data-fp="efectivo">
                    <i class="fas fa-money-bill"></i> Efectivo
                </button>
                <button type="button" class="btn btn-outline-primary forma-pago-btn" data-fp="tarjeta">
                    <i class="fas fa-credit-card"></i> Tarjeta
                </button>
                <button type="button" class="btn btn-outline-primary forma-pago-btn" data-fp="bizum">
                    <i class="fas fa-mobile-alt"></i> Bizum
                </button>
            </div>
        </div>

        <div class="form-group" id="grupo-efectivo">
            <label class="form-label">Entregado</label>
            <input type="number" id="entregado" class="form-control form-control-lg" step="0.01" style="font-size: 20px; height: 52px;">
            <div style="text-align:right; margin-top: 8px;">
                <span style="font-size: 12px; color: var(--text-muted);">Cambio: </span>
                <span id="cambio" style="font-size: 20px; font-weight: 700; color: var(--color-success);">{{ $config->formatearMoneda(0) }}</span>
            </div>
        </div>

        <div class="d-flex gap-2 mt-3">
            <button class="btn btn-light" style="flex:1; height: 52px;" onclick="cerrarModalPago()">Cancelar</button>
            <button class="btn btn-success" style="flex:2; height: 52px;" onclick="confirmarVenta()">
                <i class="fas fa-check"></i> Confirmar cobro
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const FORMATO_MONEDA = {
        simbolo: '{{ $config->moneda_simbolo }}',
        posicion: '{{ $config->moneda_posicion }}',
        miles: '{{ $config->separador_miles }}',
        decimales: '{{ $config->separador_decimales }}',
        digitos: {{ $config->decimales }},
    };
    const PRECIOS_CON_IVA = {{ $config->precios_con_impuestos ? 'true' : 'false' }};

    function fm(n) {
        n = parseFloat(n) || 0;
        const partes = n.toFixed(FORMATO_MONEDA.digitos).split('.');
        partes[0] = partes[0].replace(/\B(?=(\d{3})+(?!\d))/g, FORMATO_MONEDA.miles);
        const f = partes.join(FORMATO_MONEDA.decimales);
        return FORMATO_MONEDA.posicion === 'izquierda' ? FORMATO_MONEDA.simbolo + ' ' + f : f + ' ' + FORMATO_MONEDA.simbolo;
    }

    let carrito = [];

    document.querySelectorAll('.tpv-product').forEach(el => {
        el.addEventListener('click', () => {
            if (el.classList.contains('no-stock')) return;
            const id = el.dataset.id;
            const item = carrito.find(i => i.id == id);
            if (item) item.cantidad++;
            else carrito.push({
                id: id,
                nombre: el.querySelector('.name').textContent,
                precio: parseFloat(el.dataset.price),
                iva: parseFloat(el.dataset.iva),
                cantidad: 1,
                descuento: 0,
                unidad: el.dataset.unidad,
            });
            renderCarrito();
        });
    });

    function renderCarrito() {
        const contenedor = document.getElementById('cart-items');
        if (carrito.length === 0) {
            contenedor.innerHTML = '<div class="tpv-cart-empty"><i class="fas fa-shopping-basket"></i><p>Toca productos para añadirlos al ticket</p></div>';
            document.getElementById('btn-pago').disabled = true;
        } else {
            contenedor.innerHTML = carrito.map((it, idx) => `
                <div class="tpv-cart-item">
                    <div>
                        <div class="tpv-cart-item-name">${it.nombre}</div>
                        <div class="tpv-cart-item-price">${fm(it.precio)} × ${it.cantidad} ${it.unidad}</div>
                        <div class="tpv-cart-qty">
                            <button onclick="cambiarCantidad(${idx}, -1)">−</button>
                            <input type="number" value="${it.cantidad}" step="0.001" min="0.001" onchange="setCantidad(${idx}, this.value)">
                            <button onclick="cambiarCantidad(${idx}, 1)">+</button>
                            <button onclick="quitar(${idx})" style="color:var(--color-danger); margin-left:auto;">×</button>
                        </div>
                    </div>
                    <div class="tpv-cart-item-total">${fm(it.precio * it.cantidad)}</div>
                </div>
            `).join('');
            document.getElementById('btn-pago').disabled = false;
        }
        recalcular();
    }

    function recalcular() {
        let subtotal = 0, iva = 0, descuento = 0;
        carrito.forEach(it => {
            const linea = it.precio * it.cantidad;
            const desc = linea * (it.descuento || 0) / 100;
            const neto = linea - desc;
            descuento += desc;
            if (PRECIOS_CON_IVA) {
                const base = neto / (1 + it.iva / 100);
                subtotal += base;
                iva += neto - base;
            } else {
                subtotal += neto;
                iva += neto * it.iva / 100;
            }
        });
        const total = subtotal + iva;
        document.getElementById('t-subtotal').textContent = fm(subtotal);
        document.getElementById('t-descuento').textContent = fm(descuento);
        document.getElementById('t-iva').textContent = fm(iva);
        document.getElementById('t-total').textContent = fm(total);
        document.getElementById('cart-counter').textContent = carrito.length + ' ítems';
        document.getElementById('modal-total').textContent = fm(total);
        window.__totalVenta = total;
        window.__subtotalVenta = subtotal;
        window.__ivaVenta = iva;
        window.__descuentoVenta = descuento;
    }

    function cambiarCantidad(idx, delta) {
        carrito[idx].cantidad = Math.max(0.001, +(carrito[idx].cantidad + delta).toFixed(3));
        renderCarrito();
    }
    function setCantidad(idx, val) {
        carrito[idx].cantidad = Math.max(0.001, parseFloat(val) || 1);
        renderCarrito();
    }
    function quitar(idx) {
        carrito.splice(idx, 1);
        renderCarrito();
    }
    function vaciarCarrito() {
        if (carrito.length && confirm('¿Vaciar el ticket?')) {
            carrito = [];
            renderCarrito();
        }
    }
    function aplicarDescuento() {
        const d = prompt('% de descuento a aplicar a toda la venta:', '0');
        if (d === null) return;
        const pct = parseFloat(d);
        carrito.forEach(it => it.descuento = pct);
        renderCarrito();
    }

    // Filtros
    document.querySelectorAll('.tpv-category-btn').forEach(b => {
        b.addEventListener('click', () => {
            document.querySelectorAll('.tpv-category-btn').forEach(x => x.classList.remove('active'));
            b.classList.add('active');
            const cat = b.dataset.cat;
            document.querySelectorAll('.tpv-product').forEach(p => {
                p.style.display = (cat === 'all' || p.dataset.cat === cat) ? '' : 'none';
            });
        });
    });

    document.getElementById('tpv-search').addEventListener('input', e => {
        const q = e.target.value.toLowerCase();
        document.querySelectorAll('.tpv-product').forEach(p => {
            p.style.display = p.dataset.name.includes(q) ? '' : 'none';
        });
    });

    // Modal de pago
    function abrirPago() {
        if (!carrito.length) return;
        document.getElementById('modal-pago').style.display = 'flex';
        document.getElementById('entregado').value = window.__totalVenta.toFixed(2);
        calcularCambio();
        document.getElementById('entregado').focus();
    }
    function cerrarModalPago() {
        document.getElementById('modal-pago').style.display = 'none';
    }
    document.getElementById('entregado').addEventListener('input', calcularCambio);
    function calcularCambio() {
        const entregado = parseFloat(document.getElementById('entregado').value) || 0;
        document.getElementById('cambio').textContent = fm(Math.max(0, entregado - window.__totalVenta));
    }
    let formaPagoSel = 'efectivo';
    document.querySelectorAll('.forma-pago-btn').forEach(b => {
        b.addEventListener('click', () => {
            document.querySelectorAll('.forma-pago-btn').forEach(x => x.classList.remove('active'));
            b.classList.add('active');
            formaPagoSel = b.dataset.fp;
            document.getElementById('grupo-efectivo').style.display = formaPagoSel === 'efectivo' ? 'block' : 'none';
        });
    });

    function confirmarVenta() {
        const lineas = carrito.map(it => ({
            producto_id: it.id,
            cantidad: it.cantidad,
            precio_unitario: it.precio,
            iva: it.iva,
            descuento: it.descuento || 0,
        }));
        const entregado = parseFloat(document.getElementById('entregado').value) || window.__totalVenta;
        fetch('{{ route("tpv.venta.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
            },
            body: JSON.stringify({
                lineas: lineas,
                cliente_id: document.getElementById('cliente-select').value || null,
                forma_pago: formaPagoSel,
                subtotal: window.__subtotalVenta,
                descuento: window.__descuentoVenta,
                base_imponible: window.__subtotalVenta,
                impuestos: window.__ivaVenta,
                total: window.__totalVenta,
                importe_efectivo: formaPagoSel === 'efectivo' ? entregado : 0,
                importe_tarjeta: formaPagoSel === 'tarjeta' ? window.__totalVenta : 0,
                cambio: formaPagoSel === 'efectivo' ? Math.max(0, entregado - window.__totalVenta) : 0,
            }),
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                window.open(d.ticket_url, 'ticket', 'width=400,height=600');
                carrito = [];
                renderCarrito();
                cerrarModalPago();
                document.getElementById('cliente-select').value = '';
            } else {
                alert('Error al guardar la venta');
            }
        })
        .catch(e => alert('Error: ' + e.message));
    }

    // Reloj
    setInterval(() => {
        const r = document.getElementById('reloj');
        if (r) r.textContent = new Date().toLocaleTimeString('es-ES');
    }, 1000);
</script>
<style>
.forma-pago-btn { padding: 12px 8px; flex-direction: column; height: auto; }
.forma-pago-btn.active { background: var(--color-primary); color: white; }
</style>
@endpush
@endsection
