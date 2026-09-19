<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket {{ $venta->numero }}</title>
    <style>
        @page { size: 80mm auto; margin: 0; }
        body { font-family: 'Courier New', monospace; width: 76mm; padding: 4mm; margin: 0; font-size: 11px; }
        h1 { text-align: center; margin: 0 0 4px; font-size: 14px; }
        .center { text-align: center; }
        .right { text-align: right; }
        .small { font-size: 10px; }
        hr { border: none; border-top: 1px dashed #000; margin: 6px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 1px 0; vertical-align: top; }
        .total { font-size: 14px; font-weight: bold; }
        .logo { max-width: 60mm; max-height: 30mm; }
    </style>
</head>
<body onload="window.print()">
    <div class="center">
        @if($config->logo)
            <img src="{{ public_path('storage/' . $config->logo) }}" class="logo">
        @endif
        <h1>{{ $config->nombre_empresa }}</h1>
        @if($config->cif_nif)<div class="small">CIF: {{ $config->cif_nif }}</div>@endif
        @if($config->direccion)<div class="small">{{ $config->direccion }}, {{ $config->codigo_postal }} {{ $config->ciudad }}</div>@endif
        @if($config->telefono)<div class="small">Tel: {{ $config->telefono }}</div>@endif
    </div>
    <hr>
    <table>
        <tr>
            <td>{{ ucfirst($venta->tipo_documento) }}:</td>
            <td class="right"><b>{{ $venta->numero }}</b></td>
        </tr>
        <tr>
            <td>Fecha:</td>
            <td class="right">{{ $venta->fecha->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td>Atiende:</td>
            <td class="right">{{ $venta->user->name }}</td>
        </tr>
        @if($venta->cliente)
        <tr>
            <td>Cliente:</td>
            <td class="right">{{ $venta->cliente->nombre }}</td>
        </tr>
        @endif
    </table>
    <hr>
    <table>
        <tr style="border-bottom: 1px solid #000;">
            <td>Producto</td>
            <td class="right">Total</td>
        </tr>
        @foreach($venta->lineas as $linea)
        <tr>
            <td colspan="2">{{ $linea->descripcion }}</td>
        </tr>
        <tr>
            <td class="small">&nbsp;&nbsp;{{ number_format($linea->cantidad, 2, ',', '.') }} × {{ $config->formatearMoneda($linea->precio_unitario) }}</td>
            <td class="right">{{ $config->formatearMoneda($linea->total) }}</td>
        </tr>
        @endforeach
    </table>
    <hr>
    <table>
        <tr><td>Base imponible:</td><td class="right">{{ $config->formatearMoneda($venta->base_imponible) }}</td></tr>
        @if($venta->descuento > 0)
        <tr><td>Descuento:</td><td class="right">-{{ $config->formatearMoneda($venta->descuento) }}</td></tr>
        @endif
        <tr><td>IVA:</td><td class="right">{{ $config->formatearMoneda($venta->impuestos) }}</td></tr>
        <tr class="total"><td>TOTAL:</td><td class="right">{{ $config->formatearMoneda($venta->total) }}</td></tr>
    </table>
    <hr>
    <table>
        <tr><td>Forma de pago:</td><td class="right">{{ ucfirst($venta->forma_pago) }}</td></tr>
        @if($venta->forma_pago === 'efectivo')
        <tr><td>Entregado:</td><td class="right">{{ $config->formatearMoneda($venta->importe_efectivo) }}</td></tr>
        <tr><td>Cambio:</td><td class="right">{{ $config->formatearMoneda($venta->cambio) }}</td></tr>
        @endif
    </table>
    <hr>
    <div class="center small">
        {!! nl2br(e($config->pie_ticket ?? '¡Gracias por su compra! Le esperamos pronto.')) !!}
    </div>
</body>
</html>
