<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $documento->numero }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; color: #333; }
        h1 { color: #C8763D; margin: 0; }
        .header { display: table; width: 100%; margin-bottom: 30px; }
        .header > div { display: table-cell; vertical-align: top; }
        .cliente { background: #f5f1eb; padding: 12px; margin-bottom: 20px; border-radius: 6px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #C8763D; color: white; padding: 8px; text-align: left; }
        td { padding: 8px; border-bottom: 1px solid #eee; }
        .right { text-align: right; }
        .totales { float: right; width: 280px; }
        .total-final { color: #C8763D; font-size: 18px; font-weight: bold; border-top: 2px solid #C8763D; padding-top: 8px; }
        .footer { font-size: 10px; color: #999; margin-top: 40px; text-align: center; clear: both; padding-top: 20px; border-top: 1px solid #eee; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            @if($config->logo && file_exists(public_path('storage/' . $config->logo)))
                <img src="{{ public_path('storage/' . $config->logo) }}" style="max-height: 70px;">
            @endif
            <h2 style="margin: 5px 0 4px;">{{ $config->nombre_empresa }}</h2>
            <div style="font-size: 11px;">
                @if($config->cif_nif)CIF: {{ $config->cif_nif }}<br>@endif
                {{ $config->direccion }}<br>
                {{ $config->codigo_postal }} {{ $config->ciudad }}<br>
                Tel: {{ $config->telefono }}
            </div>
        </div>
        <div class="right">
            <h1 style="text-transform: uppercase;">{{ $documento->tipo_documento }}</h1>
            <div style="font-size: 18px; font-weight: bold;">{{ $documento->numero }}</div>
            <div>Fecha: {{ $documento->fecha->format('d/m/Y') }}</div>
        </div>
    </div>

    @if($documento->cliente)
    <div class="cliente">
        <strong>Cliente:</strong> {{ $documento->cliente->nombre }}<br>
        @if($documento->cliente->cif_nif)CIF/NIF: {{ $documento->cliente->cif_nif }}<br>@endif
        {{ $documento->cliente->direccion }} {{ $documento->cliente->codigo_postal }} {{ $documento->cliente->ciudad }}
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Concepto</th>
                <th class="right">Cant.</th>
                <th class="right">Precio</th>
                <th class="right">IVA</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($documento->lineas as $l)
            <tr>
                <td>{{ $l->descripcion }}</td>
                <td class="right">{{ number_format($l->cantidad, 2) }}</td>
                <td class="right">{{ $config->formatearMoneda($l->precio_unitario) }}</td>
                <td class="right">{{ number_format($l->iva, 0) }}%</td>
                <td class="right">{{ $config->formatearMoneda($l->total) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totales">
        <tr><td>Base imponible:</td><td class="right">{{ $config->formatearMoneda($documento->base_imponible) }}</td></tr>
        @if($documento->descuento > 0)
        <tr><td>Descuento:</td><td class="right">-{{ $config->formatearMoneda($documento->descuento) }}</td></tr>
        @endif
        <tr><td>IVA:</td><td class="right">{{ $config->formatearMoneda($documento->impuestos) }}</td></tr>
        <tr class="total-final"><td>TOTAL:</td><td class="right">{{ $config->formatearMoneda($documento->total) }}</td></tr>
    </table>

    <div class="footer">
        Forma de pago: {{ ucfirst($documento->forma_pago) }}<br>
        {!! nl2br(e($config->pie_factura ?? 'Gracias por su confianza.')) !!}
    </div>
</body>
</html>
