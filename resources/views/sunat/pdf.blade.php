<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $venta->numero_sunat ?? $venta->numero }}</title>
    <style>
        @page { margin: 18mm 14mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; color: #222; }
        h1 { font-size: 14pt; margin: 0; text-transform: uppercase; }
        h2 { font-size: 11pt; margin: 0 0 4px; }
        table { width: 100%; border-collapse: collapse; }
        .header { display: table; width: 100%; }
        .header > div { display: table-cell; vertical-align: top; }
        .col-emisor { width: 55%; }
        .col-doc { width: 45%; text-align: center; border: 1.5pt solid #c8763d; padding: 8pt; border-radius: 4pt; }
        .col-doc h1 { color: #c8763d; }
        .small { font-size: 9pt; color: #555; }
        .mt-2 { margin-top: 8pt; }
        .mt-3 { margin-top: 14pt; }
        .lineas th { background: #f4ead7; border-bottom: 1pt solid #c8763d; padding: 4pt; text-align: left; }
        .lineas td { border-bottom: 1pt dotted #c8763d55; padding: 4pt; }
        .right { text-align: right; }
        .tot th, .tot td { padding: 3pt 6pt; }
        .tot th { text-align: right; background: #faf5ec; }
        .footer { margin-top: 14pt; border-top: 1pt solid #c8763d; padding-top: 6pt; }
        .qr-box { text-align: center; }
        .qr-box img { width: 110pt; height: 110pt; }
        .legend { font-size: 8pt; color: #444; }
        .badge { display: inline-block; padding: 2pt 6pt; background: #c8763d; color: #fff; border-radius: 3pt; font-size: 8pt; }
    </style>
</head>
<body>

<div class="header">
    <div class="col-emisor">
        <h1>{{ strtoupper($config->sunat_razon_social ?: $config->nombre_empresa) }}</h1>
        @if($config->sunat_nombre_comercial && $config->sunat_nombre_comercial !== $config->sunat_razon_social)
            <div class="small">{{ $config->sunat_nombre_comercial }}</div>
        @endif
        <div class="small mt-2">
            <strong>RUC:</strong> {{ $config->sunat_ruc }}<br>
            {{ $config->sunat_direccion_fiscal ?: $config->direccion }}<br>
            {{ $config->sunat_distrito }} - {{ $config->sunat_provincia }} - {{ $config->sunat_departamento }}<br>
            @if($config->telefono) Tel: {{ $config->telefono }} · @endif
            @if($config->email) {{ $config->email }} @endif
        </div>
    </div>
    <div class="col-doc">
        <div class="small">RUC {{ $config->sunat_ruc }}</div>
        <h1>
            @switch($venta->sunat_tipo_comprobante)
                @case('01') Factura electrónica @break
                @case('03') Boleta de venta electrónica @break
                @case('07') Nota de crédito electrónica @break
                @case('08') Nota de débito electrónica @break
            @endswitch
        </h1>
        <h2>{{ $venta->numero_sunat }}</h2>
    </div>
</div>

<div class="mt-3" style="display: table; width: 100%;">
    <div style="display: table-cell; width: 65%; vertical-align: top;">
        <table>
            <tr>
                <td style="width: 28%;"><strong>Cliente:</strong></td>
                <td>{{ $venta->cliente?->razon_social ?: $venta->cliente?->nombre ?: ($venta->datos_cliente['nombre'] ?? '—') }}</td>
            </tr>
            <tr>
                <td><strong>Documento:</strong></td>
                <td>
                    @php
                        $tipoDoc = $venta->cliente?->tipo_documento_sunat ?? ($venta->datos_cliente['tipo_documento_sunat'] ?? '—');
                        $numDoc  = $venta->cliente?->numero_documento     ?? ($venta->datos_cliente['numero_documento']     ?? '—');
                        $nombres = ['6'=>'RUC','1'=>'DNI','4'=>'CE','7'=>'PAS','0'=>'S/D'];
                    @endphp
                    {{ $nombres[$tipoDoc] ?? $tipoDoc }} · {{ $numDoc }}
                </td>
            </tr>
            <tr>
                <td><strong>Dirección:</strong></td>
                <td>{{ $venta->cliente?->direccion_fiscal ?: $venta->cliente?->direccion ?: ($venta->datos_cliente['direccion'] ?? '—') }}</td>
            </tr>
            <tr>
                <td><strong>Fecha emisión:</strong></td>
                <td>{{ $venta->fecha?->format('d/m/Y') }} · <strong>Moneda:</strong> {{ $venta->moneda }}</td>
            </tr>
            @if($venta->ventaModifica)
            <tr>
                <td><strong>Documento afectado:</strong></td>
                <td>{{ $venta->ventaModifica->numero_sunat }} · {{ $venta->motivo_nota }}</td>
            </tr>
            @endif
        </table>
    </div>
    <div style="display: table-cell; width: 35%; vertical-align: top;" class="qr-box">
        @if($qrData)
            <img src="{{ $qrData }}" alt="QR SUNAT">
        @endif
        <div class="small">
            <span class="badge">{{ strtoupper($venta->estado_sunat) }}</span>
        </div>
    </div>
</div>

<table class="lineas mt-3">
    <thead>
        <tr>
            <th>Descripción</th>
            <th class="right">Cant.</th>
            <th class="right">P. Unit.</th>
            <th class="right">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($venta->lineas as $l)
        <tr>
            <td>{{ $l->descripcion }}</td>
            <td class="right">{{ rtrim(rtrim(number_format($l->cantidad, 3), '0'), '.') }}</td>
            <td class="right">{{ number_format($l->precio_unitario, 2) }}</td>
            <td class="right">{{ number_format($l->total, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<table class="tot mt-2" style="width: 40%; margin-left: 60%;">
    <tr><th>Op. gravadas:</th><td class="right">{{ number_format($venta->base_imponible, 2) }}</td></tr>
    <tr><th>IGV ({{ rtrim(rtrim(number_format($config->sunat_igv_porcentaje ?? 18, 2), '0'), '.') }}%):</th><td class="right">{{ number_format($venta->impuestos, 2) }}</td></tr>
    <tr><th>Total {{ $venta->moneda }}:</th><td class="right"><strong>{{ number_format($venta->total, 2) }}</strong></td></tr>
</table>

<div class="footer">
    <div class="legend">
        <strong>Representación impresa del comprobante electrónico.</strong>
        Para verificar la autenticidad consulte en
        <em>https://www.sunat.gob.pe</em> con el RUC del emisor y el número del comprobante.
    </div>
    @if($venta->hash)
        <div class="small mt-2"><strong>Hash:</strong> <code>{{ $venta->hash }}</code></div>
    @endif
    @if($venta->codigo_sunat || $venta->mensaje_sunat)
        <div class="small mt-2"><strong>Respuesta SUNAT:</strong> [{{ $venta->codigo_sunat }}] {{ $venta->mensaje_sunat }}</div>
    @endif
</div>
</body>
</html>
