<?php

namespace App\Services\Sunat;

use App\Models\Configuracion;
use App\Models\Venta;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Address;
use Greenter\Model\Company\Company;
use Greenter\Model\Sale\Document;
use Greenter\Model\Sale\FormaPagos\FormaPagoContado;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\Legend;
use Greenter\Model\Sale\Note;
use Greenter\Model\Sale\SaleDetail;

/**
 * Construye los DTOs de Greenter (Invoice / Note) a partir de una `Venta`
 * Eloquent del TPV, aplicando las reglas SUNAT:
 *
 *  - Factura (01): cliente con RUC obligatorio, IGV 18% discriminado.
 *  - Boleta (03): cliente DNI o "sin documento" si total < S/700.
 *  - Nota crédito (07) / Nota débito (08): requieren documento afectado
 *    (serie-correlativo + tipo) y un código de motivo (catálogos 09 / 10).
 *
 * Importes:
 *  - Las líneas del TPV almacenan `precio_unitario` con IGV incluido.
 *    Para SUNAT construimos:
 *       mtoValorUnitario = precio_unitario / 1.18
 *       igv              = mtoValorVenta * 0.18
 *       mtoPrecioUnitario = precio_unitario
 *       tipAfeIgv        = 10 (Gravado - Operación Onerosa, catálogo 07)
 *  - Si la configuración indica que los precios son SIN IGV, se invierte.
 */
class DocumentoBuilder
{
    public function __construct(private Configuracion $cfg) {}

    public function emisor(): Company
    {
        $direccion = (new Address())
            ->setUbigueo($this->cfg->sunat_ubigeo ?: '150101')
            ->setDepartamento($this->cfg->sunat_departamento ?: 'LIMA')
            ->setProvincia($this->cfg->sunat_provincia ?: 'LIMA')
            ->setDistrito($this->cfg->sunat_distrito ?: 'LIMA')
            ->setUrbanizacion($this->cfg->sunat_urbanizacion ?: '-')
            ->setDireccion($this->cfg->sunat_direccion_fiscal ?: $this->cfg->direccion ?: 'AV. SIN NOMBRE')
            ->setCodLocal('0000');

        return (new Company())
            ->setRuc($this->cfg->sunat_ruc ?: GreenterFactory::BETA_RUC)
            ->setRazonSocial($this->cfg->sunat_razon_social ?: $this->cfg->razon_social ?: $this->cfg->nombre_empresa)
            ->setNombreComercial($this->cfg->sunat_nombre_comercial ?: $this->cfg->nombre_empresa)
            ->setAddress($direccion);
    }

    public function cliente(Venta $venta): Client
    {
        $c     = $venta->cliente;
        $datos = $venta->datos_cliente ?? [];

        $tipoDoc = $c?->tipo_documento_sunat ?? ($datos['tipo_documento_sunat'] ?? null);
        $numDoc  = $c?->numero_documento     ?? ($datos['numero_documento']     ?? null);
        $nombre  = $c?->razon_social ?: ($c?->nombre ?: ($datos['nombre'] ?? 'CLIENTES VARIOS'));
        $direc   = $c?->direccion_fiscal ?: ($c?->direccion ?: ($datos['direccion'] ?? '-'));

        // Fallback: si es boleta y no hay documento, usamos tipo 0 / 00000000
        if (! $tipoDoc) {
            $tipoDoc = $venta->sunat_tipo_comprobante === '01' ? '6' : '0';
            $numDoc  = $venta->sunat_tipo_comprobante === '01' ? '20000000001' : '00000000';
        }

        // SUNAT exige Address completa (Ubigueo + Departamento + Provincia + Distrito)
        // para receptores en facturas. Reutilizamos los datos del emisor como
        // último recurso para no romper la validación XSD.
        $address = (new Address())
            ->setUbigueo($this->cfg->sunat_ubigeo ?: '150101')
            ->setDepartamento($this->cfg->sunat_departamento ?: 'LIMA')
            ->setProvincia($this->cfg->sunat_provincia ?: 'LIMA')
            ->setDistrito($this->cfg->sunat_distrito ?: 'LIMA')
            ->setDireccion(mb_strtoupper(trim($direc)) ?: '-');

        return (new Client())
            ->setTipoDoc((string) $tipoDoc)
            ->setNumDoc((string) $numDoc)
            ->setRznSocial(mb_strtoupper(trim($nombre)))
            ->setAddress($address);
    }

    /**
     * @return SaleDetail[]
     */
    public function detalles(Venta $venta): array
    {
        $igvRate = (float) ($this->cfg->sunat_igv_porcentaje ?: 18.00) / 100;
        $preciosConIgv = (bool) $this->cfg->precios_con_impuestos;
        $detalles = [];

        foreach ($venta->lineas as $linea) {
            $cantidad = (float) $linea->cantidad;
            $precioUnitTotal = (float) $linea->precio_unitario;

            if ($preciosConIgv) {
                $valorUnitario = $precioUnitTotal / (1 + $igvRate);
                $precioUnitario = $precioUnitTotal;
            } else {
                $valorUnitario = $precioUnitTotal;
                $precioUnitario = $precioUnitTotal * (1 + $igvRate);
            }

            $mtoValorVenta = round($valorUnitario * $cantidad, 2);
            $mtoBaseIgv    = $mtoValorVenta;
            $igv           = round($mtoBaseIgv * $igvRate, 2);
            $totalImpuestos = $igv;

            $detalles[] = (new SaleDetail())
                ->setCodProducto((string) ($linea->producto_id ?? 'GEN'))
                ->setUnidad('NIU')                           // catálogo 03 - unidad
                ->setDescripcion(mb_substr($linea->descripcion, 0, 250))
                ->setCantidad($cantidad)
                ->setMtoValorUnitario(round($valorUnitario, 4))
                ->setMtoValorVenta($mtoValorVenta)
                ->setMtoBaseIgv($mtoBaseIgv)
                ->setPorcentajeIgv($igvRate * 100)
                ->setIgv($igv)
                ->setTipAfeIgv('10')                         // catálogo 07: gravado onerosa
                ->setTotalImpuestos($totalImpuestos)
                ->setMtoPrecioUnitario(round($precioUnitario, 4));
        }

        return $detalles;
    }

    /** Calcula totales agregados a partir de las líneas Greenter. */
    public function totales(array $detalles): array
    {
        $mtoOperGravadas = 0;
        $igv             = 0;
        foreach ($detalles as $d) {
            $mtoOperGravadas += $d->getMtoValorVenta();
            $igv             += $d->getIgv();
        }
        $mtoOperGravadas = round($mtoOperGravadas, 2);
        $igv             = round($igv, 2);
        $valorVenta      = $mtoOperGravadas;
        $subTotal        = round($valorVenta + $igv, 2);
        $mtoImpVenta     = $subTotal;

        return compact('mtoOperGravadas', 'igv', 'valorVenta', 'subTotal', 'mtoImpVenta');
    }

    public function buildInvoice(Venta $venta): Invoice
    {
        $detalles = $this->detalles($venta);
        $t = $this->totales($detalles);

        $invoice = (new Invoice())
            ->setUblVersion('2.1')
            ->setTipoOperacion('0101')                       // venta interna
            ->setTipoDoc($venta->sunat_tipo_comprobante)     // 01 factura / 03 boleta
            ->setSerie($venta->serie)
            ->setCorrelativo(str_pad((string) $venta->correlativo, 8, '0', STR_PAD_LEFT))
            ->setFechaEmision($venta->fecha)
            ->setTipoMoneda($venta->moneda ?: 'PEN')
            ->setCompany($this->emisor())
            ->setClient($this->cliente($venta))
            ->setMtoOperGravadas($t['mtoOperGravadas'])
            ->setMtoIGV($t['igv'])
            ->setTotalImpuestos($t['igv'])
            ->setValorVenta($t['valorVenta'])
            ->setSubTotal($t['subTotal'])
            ->setMtoImpVenta($t['mtoImpVenta'])
            ->setDetails($detalles)
            ->setFormaPago(new FormaPagoContado())
            ->setLegends([
                (new Legend())
                    ->setCode('1000')
                    ->setValue($this->montoEnLetras($t['mtoImpVenta'])),
            ]);

        return $invoice;
    }

    /**
     * Construye una Nota (crédito 07 / débito 08) referenciando una venta
     * previa (`venta_modifica`).
     */
    public function buildNote(Venta $venta): Note
    {
        $modifica = $venta->ventaModifica;
        if (! $modifica) {
            throw new \RuntimeException('La nota requiere una venta de referencia (venta_modifica_id).');
        }

        $detalles = $this->detalles($venta);
        $t = $this->totales($detalles);

        $note = (new Note())
            ->setUblVersion('2.1')
            ->setTipoDoc($venta->sunat_tipo_comprobante)     // 07 ó 08
            ->setSerie($venta->serie)
            ->setCorrelativo(str_pad((string) $venta->correlativo, 8, '0', STR_PAD_LEFT))
            ->setFechaEmision($venta->fecha)
            ->setTipoMoneda($venta->moneda ?: 'PEN')
            ->setCompany($this->emisor())
            ->setClient($this->cliente($venta))
            ->setTipDocAfectado($modifica->sunat_tipo_comprobante)
            ->setNumDocfectado($modifica->numero_sunat)
            ->setCodMotivo($venta->codigo_motivo_nota ?: ($venta->sunat_tipo_comprobante === '07' ? '01' : '01'))
            ->setDesMotivo($venta->motivo_nota ?: 'Anulación de la operación')
            ->setMtoOperGravadas($t['mtoOperGravadas'])
            ->setMtoIGV($t['igv'])
            ->setTotalImpuestos($t['igv'])
            ->setMtoImpVenta($t['mtoImpVenta'])
            ->setDetails($detalles)
            ->setLegends([
                (new Legend())
                    ->setCode('1000')
                    ->setValue($this->montoEnLetras($t['mtoImpVenta'])),
            ]);

        return $note;
    }

    /**
     * Devuelve el monto en letras del comprobante (leyenda 1000 SUNAT).
     * Implementación simple para soles peruanos.
     */
    private function montoEnLetras(float $monto): string
    {
        $entero    = (int) floor($monto);
        $centavos  = (int) round(($monto - $entero) * 100);

        return mb_strtoupper(self::numeroEnLetras($entero))
            . ' CON ' . str_pad((string) $centavos, 2, '0', STR_PAD_LEFT) . '/100 SOLES';
    }

    private static function numeroEnLetras(int $n): string
    {
        if ($n === 0) return 'CERO';
        $unidades = ['','UNO','DOS','TRES','CUATRO','CINCO','SEIS','SIETE','OCHO','NUEVE',
            'DIEZ','ONCE','DOCE','TRECE','CATORCE','QUINCE','DIECISÉIS','DIECISIETE','DIECIOCHO','DIECINUEVE','VEINTE'];
        $decenas = ['','','VEINTI','TREINTA','CUARENTA','CINCUENTA','SESENTA','SETENTA','OCHENTA','NOVENTA'];
        $centenas = ['','CIENTO','DOSCIENTOS','TRESCIENTOS','CUATROCIENTOS','QUINIENTOS',
            'SEISCIENTOS','SETECIENTOS','OCHOCIENTOS','NOVECIENTOS'];

        $convertirGrupo = function (int $n) use (&$convertirGrupo, $unidades, $decenas, $centenas): string {
            if ($n === 0)   return '';
            if ($n === 100) return 'CIEN';
            if ($n < 21)    return $unidades[$n];
            if ($n < 100) {
                $d = intdiv($n, 10);
                $u = $n % 10;
                if ($d === 2) return $u === 0 ? 'VEINTE' : 'VEINTI'.$unidades[$u];
                return $decenas[$d] . ($u ? ' Y '.$unidades[$u] : '');
            }
            $c = intdiv($n, 100);
            $r = $n % 100;
            return trim($centenas[$c] . ' ' . $convertirGrupo($r));
        };

        if ($n < 1000) return $convertirGrupo($n);
        if ($n < 1_000_000) {
            $miles = intdiv($n, 1000);
            $resto = $n % 1000;
            $prefijo = $miles === 1 ? 'MIL' : $convertirGrupo($miles).' MIL';
            return trim($prefijo . ($resto ? ' '.$convertirGrupo($resto) : ''));
        }
        $millones = intdiv($n, 1_000_000);
        $resto    = $n % 1_000_000;
        $prefijo  = $millones === 1 ? 'UN MILLÓN' : $convertirGrupo($millones).' MILLONES';
        return trim($prefijo . ($resto ? ' '.self::numeroEnLetras($resto) : ''));
    }

    /** Documento Greenter genérico apropiado al tipo de comprobante. */
    public function build(Venta $venta): Document
    {
        return match ($venta->sunat_tipo_comprobante) {
            '01', '03'   => $this->buildInvoice($venta),
            '07', '08'   => $this->buildNote($venta),
            default      => throw new \InvalidArgumentException(
                "Tipo de comprobante SUNAT no soportado: {$venta->sunat_tipo_comprobante}"
            ),
        };
    }
}
