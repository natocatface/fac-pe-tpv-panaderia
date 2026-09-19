@extends('layouts.app')
@section('title', 'Configuración')
@section('page-title', 'Configuración del sistema')

@section('content')
<div class="page-header">
    <h1 class="page-title"><i class="fas fa-cog"></i> Configuración del sistema</h1>
</div>

<div style="display: grid; grid-template-columns: 240px 1fr; gap: 24px;">
    <!-- Tabs verticales -->
    <div class="card" style="height: fit-content;">
        <ul class="sidebar-menu" style="background: white; padding: 0;">
            <li><a href="#empresa" class="tab-link active" onclick="showTab(event, 'empresa')" style="color: var(--color-chocolate);"><i class="fas fa-building"></i> Empresa</a></li>
            <li><a href="#logo" class="tab-link" onclick="showTab(event, 'logo')" style="color: var(--color-chocolate);"><i class="fas fa-image"></i> Logo</a></li>
            <li><a href="#regional" class="tab-link" onclick="showTab(event, 'regional')" style="color: var(--color-chocolate);"><i class="fas fa-globe"></i> Regional</a></li>
            <li><a href="#impuestos" class="tab-link" onclick="showTab(event, 'impuestos')" style="color: var(--color-chocolate);"><i class="fas fa-percent"></i> Impuestos</a></li>
            <li><a href="#documentos" class="tab-link" onclick="showTab(event, 'documentos')" style="color: var(--color-chocolate);"><i class="fas fa-file-invoice"></i> Documentos</a></li>
            <li><a href="#sunat" class="tab-link" onclick="showTab(event, 'sunat')" style="color: var(--color-chocolate);"><i class="fas fa-file-signature"></i> SUNAT (Perú)</a></li>
        </ul>
    </div>

    <div>
        <!-- EMPRESA -->
        <div class="card tab-pane" id="tab-empresa">
            <div class="card-header"><h5><i class="fas fa-building"></i> Datos de la empresa</h5></div>
            <form method="POST" action="{{ route('configuracion.empresa') }}">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Nombre comercial *</label>
                        <input type="text" name="nombre_empresa" class="form-control" required value="{{ $config->nombre_empresa }}">
                    </div>
                    <div class="row-grid cols-2">
                        <div class="form-group">
                            <label class="form-label">Razón social</label>
                            <input type="text" name="razon_social" class="form-control" value="{{ $config->razon_social }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">CIF / NIF</label>
                            <input type="text" name="cif_nif" class="form-control" value="{{ $config->cif_nif }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Dirección</label>
                        <input type="text" name="direccion" class="form-control" value="{{ $config->direccion }}">
                    </div>
                    <div class="row-grid cols-3">
                        <div class="form-group">
                            <label class="form-label">Código postal</label>
                            <input type="text" name="codigo_postal" class="form-control" value="{{ $config->codigo_postal }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Ciudad</label>
                            <input type="text" name="ciudad" class="form-control" value="{{ $config->ciudad }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Provincia</label>
                            <input type="text" name="provincia" class="form-control" value="{{ $config->provincia }}">
                        </div>
                    </div>
                    <div class="row-grid cols-3">
                        <div class="form-group">
                            <label class="form-label">Teléfono</label>
                            <input type="text" name="telefono" class="form-control" value="{{ $config->telefono }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ $config->email }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Web</label>
                            <input type="text" name="web" class="form-control" value="{{ $config->web }}">
                        </div>
                    </div>
                    <button class="btn btn-primary"><i class="fas fa-save"></i> Guardar empresa</button>
                </div>
            </form>
        </div>

        <!-- LOGO -->
        <div class="card tab-pane" id="tab-logo" style="display:none;">
            <div class="card-header"><h5><i class="fas fa-image"></i> Logo de la empresa</h5></div>
            <form method="POST" action="{{ route('configuracion.logo') }}" enctype="multipart/form-data">
                @csrf
                <div class="card-body text-center">
                    @if($config->logo)
                        <img src="{{ asset('storage/' . $config->logo) }}" style="max-height: 200px; margin-bottom: 16px;">
                    @else
                        <div style="height: 200px; display:flex; align-items:center; justify-content:center; background: var(--bg-page); border-radius: 12px; margin-bottom: 16px; font-size: 80px; color: var(--text-muted);">
                            <i class="fas fa-image"></i>
                        </div>
                    @endif
                    <input type="file" name="logo" accept="image/*" class="form-control" required>
                    <p class="form-text">Formatos: JPG, PNG, GIF · Máximo 2MB</p>
                    <button class="btn btn-primary"><i class="fas fa-upload"></i> Subir logo</button>
                </div>
            </form>
        </div>

        <!-- REGIONAL -->
        <div class="card tab-pane" id="tab-regional" style="display:none;">
            <div class="card-header"><h5><i class="fas fa-globe"></i> Configuración regional y moneda</h5></div>
            <form method="POST" action="{{ route('configuracion.regional') }}">
                @csrf
                <div class="card-body">
                    <div class="row-grid cols-3">
                        <div class="form-group">
                            <label class="form-label">Código de moneda *</label>
                            <input type="text" name="moneda_codigo" class="form-control" required value="{{ $config->moneda_codigo }}" placeholder="EUR, USD, MXN...">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Símbolo *</label>
                            <input type="text" name="moneda_simbolo" class="form-control" required value="{{ $config->moneda_simbolo }}" placeholder="€, $, ...">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Posición símbolo *</label>
                            <select name="moneda_posicion" class="form-select" required>
                                <option value="derecha" {{ $config->moneda_posicion == 'derecha' ? 'selected' : '' }}>Derecha (1.000,00 €)</option>
                                <option value="izquierda" {{ $config->moneda_posicion == 'izquierda' ? 'selected' : '' }}>Izquierda ($ 1,000.00)</option>
                            </select>
                        </div>
                    </div>
                    <div class="row-grid cols-3">
                        <div class="form-group">
                            <label class="form-label">Separador miles *</label>
                            <select name="separador_miles" class="form-select" required>
                                <option value="." {{ $config->separador_miles == '.' ? 'selected' : '' }}>Punto (.)</option>
                                <option value="," {{ $config->separador_miles == ',' ? 'selected' : '' }}>Coma (,)</option>
                                <option value=" " {{ $config->separador_miles == ' ' ? 'selected' : '' }}>Espacio</option>
                                <option value="'" {{ $config->separador_miles == "'" ? 'selected' : '' }}>Apóstrofo (')</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Separador decimales *</label>
                            <select name="separador_decimales" class="form-select" required>
                                <option value="," {{ $config->separador_decimales == ',' ? 'selected' : '' }}>Coma (,)</option>
                                <option value="." {{ $config->separador_decimales == '.' ? 'selected' : '' }}>Punto (.)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Nº decimales *</label>
                            <select name="decimales" class="form-select" required>
                                <option value="0" {{ $config->decimales == 0 ? 'selected' : '' }}>0</option>
                                <option value="2" {{ $config->decimales == 2 ? 'selected' : '' }}>2</option>
                                <option value="3" {{ $config->decimales == 3 ? 'selected' : '' }}>3</option>
                                <option value="4" {{ $config->decimales == 4 ? 'selected' : '' }}>4</option>
                            </select>
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <div>Vista previa: <strong>{{ $config->formatearMoneda(1234.56) }}</strong></div>
                    </div>
                    <div class="row-grid cols-3">
                        <div class="form-group">
                            <label class="form-label">Formato fecha *</label>
                            <select name="formato_fecha" class="form-select" required>
                                <option value="d/m/Y" {{ $config->formato_fecha == 'd/m/Y' ? 'selected' : '' }}>31/12/2025</option>
                                <option value="m/d/Y" {{ $config->formato_fecha == 'm/d/Y' ? 'selected' : '' }}>12/31/2025</option>
                                <option value="Y-m-d" {{ $config->formato_fecha == 'Y-m-d' ? 'selected' : '' }}>2025-12-31</option>
                                <option value="d-m-Y" {{ $config->formato_fecha == 'd-m-Y' ? 'selected' : '' }}>31-12-2025</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Formato hora *</label>
                            <select name="formato_hora" class="form-select" required>
                                <option value="H:i" {{ $config->formato_hora == 'H:i' ? 'selected' : '' }}>24h (15:30)</option>
                                <option value="h:i A" {{ $config->formato_hora == 'h:i A' ? 'selected' : '' }}>12h (03:30 PM)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Zona horaria *</label>
                            <select name="zona_horaria" class="form-select" required>
                                <option value="Europe/Madrid">Europe/Madrid</option>
                                <option value="Europe/London">Europe/London</option>
                                <option value="America/Mexico_City">America/Mexico_City</option>
                                <option value="America/Argentina/Buenos_Aires">America/Argentina/Buenos_Aires</option>
                                <option value="America/Bogota">America/Bogota</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Idioma *</label>
                        <select name="idioma" class="form-select" required>
                            <option value="es" {{ $config->idioma == 'es' ? 'selected' : '' }}>Español</option>
                            <option value="en" {{ $config->idioma == 'en' ? 'selected' : '' }}>English</option>
                            <option value="pt" {{ $config->idioma == 'pt' ? 'selected' : '' }}>Português</option>
                            <option value="ca" {{ $config->idioma == 'ca' ? 'selected' : '' }}>Català</option>
                        </select>
                    </div>
                    <button class="btn btn-primary"><i class="fas fa-save"></i> Guardar configuración regional</button>
                </div>
            </form>
        </div>

        <!-- IMPUESTOS -->
        <div class="card tab-pane" id="tab-impuestos" style="display:none;">
            <div class="card-header"><h5><i class="fas fa-percent"></i> Impuestos</h5></div>
            <form method="POST" action="{{ route('configuracion.impuestos') }}">
                @csrf
                <div class="card-body">
                    <label style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                        <input type="checkbox" name="impuestos_activos" value="1" {{ $config->impuestos_activos ? 'checked' : '' }}>
                        Aplicar impuestos a las ventas
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; margin-bottom: 20px;">
                        <input type="checkbox" name="precios_con_impuestos" value="1" {{ $config->precios_con_impuestos ? 'checked' : '' }}>
                        Los precios de venta ya incluyen impuestos (IVA incluido)
                    </label>
                    <div class="row-grid cols-3">
                        <div class="form-group">
                            <label class="form-label">IVA general (%) *</label>
                            <input type="number" step="0.01" name="iva_general" class="form-control" required value="{{ $config->iva_general }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">IVA reducido (%) *</label>
                            <input type="number" step="0.01" name="iva_reducido" class="form-control" required value="{{ $config->iva_reducido }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">IVA superreducido (%) *</label>
                            <input type="number" step="0.01" name="iva_superreducido" class="form-control" required value="{{ $config->iva_superreducido }}">
                        </div>
                    </div>
                    <button class="btn btn-primary"><i class="fas fa-save"></i> Guardar impuestos</button>
                </div>
            </form>
        </div>

        <!-- DOCUMENTOS -->
        <div class="card tab-pane" id="tab-documentos" style="display:none;">
            <div class="card-header"><h5><i class="fas fa-file-invoice"></i> Numeración de documentos</h5></div>
            <form method="POST" action="{{ route('configuracion.documentos') }}">
                @csrf
                <div class="card-body">
                    <div class="row-grid cols-2">
                        <div class="form-group">
                            <label class="form-label">Serie tickets *</label>
                            <input type="text" name="serie_ticket" class="form-control" required value="{{ $config->serie_ticket }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Siguiente número *</label>
                            <input type="number" name="siguiente_ticket" class="form-control" required value="{{ $config->siguiente_ticket }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Serie facturas *</label>
                            <input type="text" name="serie_factura" class="form-control" required value="{{ $config->serie_factura }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Siguiente número *</label>
                            <input type="number" name="siguiente_factura" class="form-control" required value="{{ $config->siguiente_factura }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Serie presupuestos *</label>
                            <input type="text" name="serie_presupuesto" class="form-control" required value="{{ $config->serie_presupuesto }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Siguiente número *</label>
                            <input type="number" name="siguiente_presupuesto" class="form-control" required value="{{ $config->siguiente_presupuesto }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Serie albaranes *</label>
                            <input type="text" name="serie_albaran" class="form-control" required value="{{ $config->serie_albaran }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Siguiente número *</label>
                            <input type="number" name="siguiente_albaran" class="form-control" required value="{{ $config->siguiente_albaran }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Pie de ticket</label>
                        <textarea name="pie_ticket" class="form-control">{{ $config->pie_ticket }}</textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Pie de factura</label>
                        <textarea name="pie_factura" class="form-control">{{ $config->pie_factura }}</textarea>
                    </div>
                    <button class="btn btn-primary"><i class="fas fa-save"></i> Guardar documentos</button>
                </div>
            </form>
        </div>

        <!-- SUNAT (Perú) -->
        <div class="card tab-pane" id="tab-sunat" style="display:none;">
            <div class="card-header">
                <h5><i class="fas fa-file-signature"></i> Facturación electrónica SUNAT (Perú)</h5>
            </div>
            <form method="POST" action="{{ route('configuracion.sunat') }}">
                @csrf
                <div class="card-body">
                    <div class="alert alert-info">
                        <strong>Modo actual:</strong>
                        <span class="badge bg-{{ ($config->sunat_modo ?? 'beta') === 'produccion' ? 'danger' : 'secondary' }}">
                            {{ strtoupper($config->sunat_modo ?? 'beta') }}
                        </span>
                        · Endpoint: <code>{{ $config->sunat_endpoint ?: '(automático Greenter)' }}</code>
                    </div>

                    <h6 class="mt-2">Datos del emisor SUNAT</h6>
                    <div class="row-grid cols-2">
                        <div class="form-group">
                            <label class="form-label">RUC *</label>
                            <input type="text" name="sunat_ruc" class="form-control" maxlength="11" required value="{{ $config->sunat_ruc }}" placeholder="20XXXXXXXXX">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Razón social SUNAT *</label>
                            <input type="text" name="sunat_razon_social" class="form-control" required value="{{ $config->sunat_razon_social }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Nombre comercial</label>
                            <input type="text" name="sunat_nombre_comercial" class="form-control" value="{{ $config->sunat_nombre_comercial }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Ubigeo INEI *</label>
                            <input type="text" name="sunat_ubigeo" class="form-control" maxlength="6" required value="{{ $config->sunat_ubigeo }}" placeholder="150101">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Departamento</label>
                            <input type="text" name="sunat_departamento" class="form-control" value="{{ $config->sunat_departamento }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Provincia</label>
                            <input type="text" name="sunat_provincia" class="form-control" value="{{ $config->sunat_provincia }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Distrito</label>
                            <input type="text" name="sunat_distrito" class="form-control" value="{{ $config->sunat_distrito }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Urbanización</label>
                            <input type="text" name="sunat_urbanizacion" class="form-control" value="{{ $config->sunat_urbanizacion }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Dirección fiscal</label>
                        <input type="text" name="sunat_direccion_fiscal" class="form-control" value="{{ $config->sunat_direccion_fiscal }}">
                    </div>

                    <hr>
                    <h6>Credenciales SOL (solo producción)</h6>
                    <div class="row-grid cols-2">
                        <div class="form-group">
                            <label class="form-label">Usuario SOL secundario</label>
                            <input type="text" name="sunat_sol_usuario" class="form-control" value="{{ $config->sunat_sol_usuario }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Clave SOL</label>
                            <input type="password" name="sunat_sol_clave" class="form-control" value="{{ $config->sunat_sol_clave }}">
                            <small class="text-muted">En Beta se usa MODDATOS/moddatos automáticamente.</small>
                        </div>
                    </div>

                    <hr>
                    <h6>Operación</h6>
                    <div class="row-grid cols-3">
                        <div class="form-group">
                            <label class="form-label">Modo *</label>
                            <select name="sunat_modo" class="form-control" required>
                                <option value="beta" @selected(($config->sunat_modo ?? 'beta')==='beta')>Beta / Homologación</option>
                                <option value="produccion" @selected(($config->sunat_modo ?? '')==='produccion')>Producción</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">IGV % *</label>
                            <input type="number" step="0.01" name="sunat_igv_porcentaje" class="form-control" required value="{{ $config->sunat_igv_porcentaje ?? 18.00 }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Activar facturación electrónica</label>
                            <div class="form-check mt-2">
                                <input type="checkbox" name="sunat_activo" value="1" class="form-check-input" id="sunat_activo" @checked($config->sunat_activo)>
                                <label for="sunat_activo" class="form-check-label">Enviar comprobantes automáticamente a SUNAT al cerrar venta</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Endpoint SOAP (opcional)</label>
                        <input type="text" name="sunat_endpoint" class="form-control" value="{{ $config->sunat_endpoint }}" placeholder="Vacío = Greenter usa el endpoint estándar">
                    </div>

                    <hr>
                    <h6>Series electrónicas</h6>
                    <div class="row-grid cols-3">
                        <div class="form-group">
                            <label class="form-label">Serie Factura (01) *</label>
                            <input type="text" maxlength="4" name="sunat_serie_factura" class="form-control" required value="{{ $config->sunat_serie_factura ?? 'F001' }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Serie Boleta (03) *</label>
                            <input type="text" maxlength="4" name="sunat_serie_boleta" class="form-control" required value="{{ $config->sunat_serie_boleta ?? 'B001' }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">NC sobre Factura *</label>
                            <input type="text" maxlength="4" name="sunat_serie_nota_credito_factura" class="form-control" required value="{{ $config->sunat_serie_nota_credito_factura ?? 'FC01' }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">NC sobre Boleta *</label>
                            <input type="text" maxlength="4" name="sunat_serie_nota_credito_boleta" class="form-control" required value="{{ $config->sunat_serie_nota_credito_boleta ?? 'BC01' }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">ND sobre Factura *</label>
                            <input type="text" maxlength="4" name="sunat_serie_nota_debito_factura" class="form-control" required value="{{ $config->sunat_serie_nota_debito_factura ?? 'FD01' }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">ND sobre Boleta *</label>
                            <input type="text" maxlength="4" name="sunat_serie_nota_debito_boleta" class="form-control" required value="{{ $config->sunat_serie_nota_debito_boleta ?? 'BD01' }}">
                        </div>
                    </div>

                    <button class="btn btn-primary"><i class="fas fa-save"></i> Guardar SUNAT</button>
                </div>
            </form>

            <div class="card-header mt-3">
                <h6><i class="fas fa-shield-alt"></i> Certificado digital</h6>
            </div>
            <form method="POST" action="{{ route('configuracion.sunat.certificado') }}" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                    @if($config->sunat_certificado_path)
                        <div class="alert alert-success small">
                            <i class="fas fa-check-circle"></i>
                            Certificado cargado: <code>{{ basename($config->sunat_certificado_path) }}</code>
                        </div>
                    @else
                        <div class="alert alert-warning small">
                            <i class="fas fa-exclamation-triangle"></i> No hay certificado cargado.
                            En modo Beta se usa el certificado de pruebas de Greenter (storage/app/sunat/certificate.pem).
                        </div>
                    @endif

                    <div class="row-grid cols-2">
                        <div class="form-group">
                            <label class="form-label">Archivo (.pfx / .pem)</label>
                            <input type="file" name="certificado" class="form-control" required accept=".pfx,.p12,.pem,.crt">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Contraseña del certificado</label>
                            <input type="password" name="sunat_certificado_password" class="form-control" placeholder="Solo si es .pfx/.p12">
                        </div>
                    </div>
                    <button class="btn btn-warning"><i class="fas fa-upload"></i> Cargar certificado</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showTab(e, name) {
    e.preventDefault();
    document.querySelectorAll('.tab-pane').forEach(p => p.style.display = 'none');
    document.querySelectorAll('.tab-link').forEach(l => l.classList.remove('active'));
    document.getElementById('tab-' + name).style.display = 'block';
    e.target.classList.add('active');
}
</script>
<style>
.tab-link.active { background: rgba(200,118,61,0.15); color: var(--color-primary) !important; border-left: 3px solid var(--color-primary); }
.tab-link { transition: all 0.2s; border-left: 3px solid transparent; }
.tab-link:hover { background: var(--bg-page); }
</style>
@endsection
