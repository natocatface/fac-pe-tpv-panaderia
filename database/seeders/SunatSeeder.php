<?php

namespace Database\Seeders;

use App\Models\Configuracion;
use App\Models\SerieCorrelativo;
use Illuminate\Database\Seeder;

/**
 * Inicializa la configuración SUNAT con valores válidos para el ambiente
 * Beta de Greenter/SUNAT y crea las series electrónicas por defecto.
 *
 *  - RUC                : 20000000001
 *  - Razón social SUNAT : EMPRESA DE PRUEBA SAC
 *  - Modo               : beta (SOAP en Beta de SUNAT)
 *  - SOL                : MODDATOS / moddatos
 *
 * El certificado de pruebas (.pem) debe colocarse manualmente en:
 *   storage/app/sunat/certificate.pem
 */
class SunatSeeder extends Seeder
{
    public function run(): void
    {
        $config = Configuracion::actual();
        $config->fill([
            'sunat_ruc'                          => $config->sunat_ruc ?: '20000000001',
            'sunat_razon_social'                 => $config->sunat_razon_social ?: 'EMPRESA DE PRUEBA SAC',
            'sunat_nombre_comercial'             => $config->sunat_nombre_comercial ?: 'PANADERÍA DEMO',
            'sunat_ubigeo'                       => $config->sunat_ubigeo ?: '150101',
            'sunat_departamento'                 => $config->sunat_departamento ?: 'LIMA',
            'sunat_provincia'                    => $config->sunat_provincia ?: 'LIMA',
            'sunat_distrito'                     => $config->sunat_distrito ?: 'LIMA',
            'sunat_urbanizacion'                 => $config->sunat_urbanizacion ?: '-',
            'sunat_direccion_fiscal'             => $config->sunat_direccion_fiscal ?: 'AV. PRINCIPAL 123',
            'sunat_codigo_pais'                  => 'PE',
            'sunat_sol_usuario'                  => 'MODDATOS',
            'sunat_sol_clave'                    => 'moddatos',
            'sunat_modo'                         => 'beta',
            'sunat_activo'                       => false, // el usuario lo activa cuando esté listo
            'sunat_igv_porcentaje'               => 18.00,
            'sunat_serie_factura'                => 'F001',
            'sunat_serie_boleta'                 => 'B001',
            'sunat_serie_nota_credito_factura'   => 'FC01',
            'sunat_serie_nota_credito_boleta'    => 'BC01',
            'sunat_serie_nota_debito_factura'    => 'FD01',
            'sunat_serie_nota_debito_boleta'     => 'BD01',
            // Forzamos moneda PEN para el TPV Perú
            'moneda_codigo'                      => 'PEN',
            'moneda_simbolo'                     => 'S/',
            'moneda_posicion'                    => 'izquierda',
            'pais'                               => 'Perú',
            'zona_horaria'                       => 'America/Lima',
        ])->save();

        $series = [
            ['01', 'F001'],
            ['03', 'B001'],
            ['07', 'FC01'],
            ['07', 'BC01'],
            ['08', 'FD01'],
            ['08', 'BD01'],
        ];
        foreach ($series as [$tipo, $serie]) {
            SerieCorrelativo::firstOrCreate(
                ['tipo_comprobante' => $tipo, 'serie' => $serie],
                ['ultimo_correlativo' => 0, 'activo' => true]
            );
        }

        $this->command->info('SUNAT: configuración Beta cargada y series creadas.');
        $this->command->warn('Recuerde colocar certificate.pem en storage/app/sunat/ para emitir.');
    }
}
