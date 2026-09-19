<?php

namespace App\Services\Sunat;

use App\Models\Configuracion;
use Greenter\See;
use Greenter\Ws\Services\SunatEndpoints;

/**
 * Construye una instancia configurada de Greenter\See a partir de la
 * configuración persistida en BD.
 *
 *  - En modo "beta" usa SunatEndpoints::FE_BETA y credenciales fijas
 *    documentadas por SUNAT (RUC 20000000001 / MODDATOS / MODDATOS).
 *  - En modo "produccion" toma RUC + usuario SOL + clave SOL del emisor.
 *  - El certificado puede ser .pfx, en cuyo caso se convierte a PEM al vuelo
 *    usando openssl_pkcs12_read, o un .pem ya extraído.
 */
class GreenterFactory
{
    /** Credenciales del ambiente Beta documentadas por SUNAT/Greenter. */
    public const BETA_RUC      = '20000000001';
    public const BETA_USUARIO  = 'MODDATOS';
    public const BETA_CLAVE    = 'moddatos';

    public static function build(Configuracion $cfg): See
    {
        $see = new See();
        $see->setCertificate(self::cargarCertificadoPem($cfg));

        $modo = $cfg->sunat_modo ?: 'beta';
        // El endpoint personalizado (OSE / mirror) tiene prioridad sobre el
        // estándar de Greenter; útil para OSE Nubefact, Defontana, etc.
        $endpoint = $cfg->sunat_endpoint
            ?: ($modo === 'beta' ? SunatEndpoints::FE_BETA : SunatEndpoints::FE_PRODUCCION);
        $see->setService($endpoint);

        if ($modo === 'beta') {
            // En Beta SUNAT siempre acepta la dupla MODDATOS / moddatos
            // independientemente del RUC emisor. Forzamos esas credenciales
            // a menos que el usuario haya configurado otras explícitamente.
            $see->setCredentials(
                ($cfg->sunat_ruc ?: self::BETA_RUC) . self::BETA_USUARIO,
                self::BETA_CLAVE,
            );
        } else {
            $see->setCredentials(
                $cfg->sunat_ruc . $cfg->sunat_sol_usuario,
                $cfg->sunat_sol_clave,
            );
        }

        return $see;
    }

    /**
     * Convierte el certificado a PEM. Acepta:
     *   - Archivo .pem (devuelto tal cual).
     *   - Archivo .pfx (extraído con openssl_pkcs12_read + clave).
     */
    public static function cargarCertificadoPem(Configuracion $cfg): string
    {
        $path = $cfg->sunat_certificado_path;
        if (! $path || ! is_file($path)) {
            // Si no hay certificado configurado se asume el de pruebas
            // de Greenter (storage/app/sunat/certificate.pem).
            $fallback = storage_path('app/sunat/certificate.pem');
            if (is_file($fallback)) {
                return file_get_contents($fallback);
            }
            throw new \RuntimeException(
                'No hay certificado digital configurado. '
                . 'Cargue el .pfx en Configuración → SUNAT o coloque '
                . 'storage/app/sunat/certificate.pem'
            );
        }

        $contenido = file_get_contents($path);
        if (str_starts_with(ltrim($contenido), '-----BEGIN')) {
            return $contenido; // ya es PEM
        }

        // PKCS#12 → PEM
        $clave = (string) ($cfg->sunat_certificado_password ?? '');
        $resultado = [];
        if (! openssl_pkcs12_read($contenido, $resultado, $clave)) {
            throw new \RuntimeException(
                'No se pudo leer el certificado .pfx. Verifique la contraseña.'
            );
        }
        return $resultado['cert'] . $resultado['pkey'];
    }
}
