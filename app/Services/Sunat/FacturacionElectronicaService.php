<?php

namespace App\Services\Sunat;

use App\Models\ComprobanteLog;
use App\Models\Configuracion;
use App\Models\SerieCorrelativo;
use App\Models\Venta;
use Greenter\Model\Response\BaseResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Orquesta el ciclo de vida de un comprobante electrónico:
 *
 *   asignarNumeracion(): reserva (serie, correlativo) antes de emitir.
 *   emitir():            firma con Greenter, envía a SUNAT, guarda XML/CDR
 *                        en storage/app/sunat/{xml,cdr}/ y actualiza el
 *                        registro de la venta.
 *   anular():            comunicación de baja (resumen) — método auxiliar
 *                        de futuro; en notas SUNAT se anula con NC.
 *
 * Cualquier error (red, CDR rechazado, certificado inválido) se persiste
 * en `comprobantes_log` para diagnóstico posterior.
 */
class FacturacionElectronicaService
{
    private Configuracion $cfg;

    /**
     * Acepta una instancia explícita de Configuracion (útil para pruebas),
     * pero por defecto la obtiene de BD. NO se declara como parámetro
     * tipado nullable porque el container de Laravel resolvería un modelo
     * vacío (`new Configuracion`) vía DI, ocultando la configuración real.
     */
    public function __construct(?Configuracion $cfg = null)
    {
        // Si lo que recibimos es un modelo vacío inyectado por el container,
        // lo descartamos y recargamos desde BD.
        $this->cfg = ($cfg && $cfg->exists) ? $cfg : Configuracion::actual();
    }

    /**
     * Asigna serie y correlativo SUNAT según el tipo de comprobante.
     * Idempotente: si la venta ya tiene serie+correlativo no los altera.
     */
    public function asignarNumeracion(Venta $venta): Venta
    {
        if ($venta->serie && $venta->correlativo) {
            return $venta;
        }

        $tipo = $venta->sunat_tipo_comprobante;
        $serie = match ($tipo) {
            '01' => $this->cfg->sunat_serie_factura  ?: 'F001',
            '03' => $this->cfg->sunat_serie_boleta   ?: 'B001',
            '07' => $venta->ventaModifica?->sunat_tipo_comprobante === '03'
                ? ($this->cfg->sunat_serie_nota_credito_boleta ?: 'BC01')
                : ($this->cfg->sunat_serie_nota_credito_factura ?: 'FC01'),
            '08' => $venta->ventaModifica?->sunat_tipo_comprobante === '03'
                ? ($this->cfg->sunat_serie_nota_debito_boleta ?: 'BD01')
                : ($this->cfg->sunat_serie_nota_debito_factura ?: 'FD01'),
            default => throw new \InvalidArgumentException("Tipo de comprobante inválido: {$tipo}"),
        };

        $correlativo = SerieCorrelativo::siguienteCorrelativo($tipo, $serie);

        $venta->serie = $serie;
        $venta->correlativo = $correlativo;
        $venta->numero = $venta->numero_sunat;
        $venta->save();

        return $venta;
    }

    /**
     * Firma, envía y persiste el resultado de un comprobante.
     * Devuelve la propia Venta refrescada. No lanza excepción ante un
     * rechazo SUNAT — se registra como `estado_sunat = rechazado`.
     */
    public function emitir(Venta $venta): Venta
    {
        if (! $this->cfg->sunat_activo) {
            throw new \RuntimeException(
                'La facturación electrónica SUNAT no está activada en Configuración.'
            );
        }

        $this->asignarNumeracion($venta);

        $venta->estado_sunat      = 'pendiente';
        $venta->fecha_envio_sunat = now();
        $venta->save();

        $builder = new DocumentoBuilder($this->cfg);
        $see     = GreenterFactory::build($this->cfg);
        $doc     = $builder->build($venta);

        $xmlPath = null;
        try {
            // Firma local y envío a SUNAT
            $result  = $see->send($doc);
            $xml     = $see->getXmlSigned($doc);
            $xmlPath = $this->guardarXml($doc->getName(), $xml);
            $venta->xml_path = $xmlPath;
            $venta->hash     = $this->extraerHash($xml);

            ComprobanteLog::create([
                'venta_id'    => $venta->id,
                'accion'      => 'firmado_local',
                'modo'        => $this->cfg->sunat_modo,
                'exitoso'     => true,
                'request_xml' => $xml,
                'user_id'     => auth()->id(),
            ]);

            $this->procesarResultado($venta, $result, $xml);
        } catch (\Throwable $e) {
            $venta->estado_sunat   = 'error';
            $venta->mensaje_sunat  = $e->getMessage();
            $venta->save();

            ComprobanteLog::create([
                'venta_id' => $venta->id,
                'accion'   => 'enviar',
                'modo'     => $this->cfg->sunat_modo,
                'exitoso'  => false,
                'mensaje'  => $e->getMessage(),
                'user_id'  => auth()->id(),
            ]);

            Log::channel('single')->error('SUNAT emisión falló', [
                'venta_id' => $venta->id,
                'error'    => $e->getMessage(),
            ]);
        }

        return $venta->fresh();
    }

    private function procesarResultado(Venta $venta, BaseResult $result, string $xml): void
    {
        if (! $result->isSuccess()) {
            $err = $result->getError();
            $venta->estado_sunat  = 'rechazado';
            $venta->codigo_sunat  = $err?->getCode();
            $venta->mensaje_sunat = $err?->getMessage();
            $venta->save();

            ComprobanteLog::create([
                'venta_id'         => $venta->id,
                'accion'           => 'enviar',
                'modo'             => $this->cfg->sunat_modo,
                'exitoso'          => false,
                'codigo_respuesta' => $err?->getCode(),
                'mensaje'          => $err?->getMessage(),
                'request_xml'      => $xml,
                'user_id'          => auth()->id(),
            ]);
            return;
        }

        // CDR recibido (zip) — extraemos
        $cdrZip = $result->getCdrZip();
        $cdrPath = $this->guardarCdr($venta->numero_sunat, $cdrZip);
        $cdr     = $result->getCdrResponse();

        $aceptado = $cdr && in_array($cdr->getCode(), ['0', 0], true);
        $venta->cdr_path      = $cdrPath;
        $venta->estado_sunat  = $aceptado ? 'aceptado' : ($cdr ? 'observado' : 'enviado');
        $venta->codigo_sunat  = $cdr?->getCode();
        $venta->mensaje_sunat = $cdr?->getDescription();
        $venta->save();

        ComprobanteLog::create([
            'venta_id'         => $venta->id,
            'accion'           => 'enviar',
            'modo'             => $this->cfg->sunat_modo,
            'exitoso'          => $aceptado,
            'codigo_respuesta' => $cdr?->getCode(),
            'mensaje'          => $cdr?->getDescription(),
            'request_xml'      => $xml,
            'user_id'          => auth()->id(),
        ]);
    }

    /**
     * Reintenta el envío de un comprobante previamente errado / no enviado.
     */
    public function reintentar(Venta $venta): Venta
    {
        if (in_array($venta->estado_sunat, ['aceptado', 'anulado'], true)) {
            throw new \RuntimeException(
                "El comprobante {$venta->numero_sunat} ya está {$venta->estado_sunat}. No procede reintento."
            );
        }
        return $this->emitir($venta);
    }

    private function guardarXml(string $nombre, string $xml): string
    {
        $relativo = "sunat/xml/{$nombre}.xml";
        Storage::disk('local')->put($relativo, $xml);
        return $relativo;
    }

    private function guardarCdr(string $nombreSunat, ?string $zipBinario): ?string
    {
        if (! $zipBinario) return null;
        $relativo = "sunat/cdr/R-{$nombreSunat}.zip";
        Storage::disk('local')->put($relativo, $zipBinario);
        return $relativo;
    }

    /**
     * Extrae el DigestValue del XML firmado (hash SHA1 usado en el QR).
     */
    private function extraerHash(string $xml): ?string
    {
        if (preg_match('#<ds:DigestValue>([^<]+)</ds:DigestValue>#', $xml, $m)) {
            return $m[1];
        }
        return null;
    }
}
