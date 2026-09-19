<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Añade los campos necesarios para la facturación electrónica directa
 * a SUNAT (Perú) en la tabla `configuracion`.
 *
 *  - Datos del emisor (RUC, razón social, nombre comercial, dirección fiscal,
 *    ubigeo según el INEI, departamento, provincia, distrito, urbanización).
 *  - Certificado digital (.pfx) y su contraseña.
 *  - Credenciales SOL (usuario secundario) para el endpoint SOAP.
 *  - Modo de operación (beta / producción) y endpoint asociado.
 *  - Parámetros tributarios (IGV, moneda PEN, código país).
 *  - Series por defecto (F001 facturas, B001 boletas, FC01 notas crédito,
 *    FD01 notas débito) — la numeración real vive en `series_correlativos`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('configuracion', function (Blueprint $table) {
            // Bloque emisor SUNAT
            $table->string('sunat_ruc', 11)->nullable()->after('cif_nif');
            $table->string('sunat_razon_social')->nullable()->after('sunat_ruc');
            $table->string('sunat_nombre_comercial')->nullable()->after('sunat_razon_social');
            $table->string('sunat_ubigeo', 6)->nullable()->after('sunat_nombre_comercial');
            $table->string('sunat_departamento', 60)->nullable()->after('sunat_ubigeo');
            $table->string('sunat_provincia', 60)->nullable()->after('sunat_departamento');
            $table->string('sunat_distrito', 60)->nullable()->after('sunat_provincia');
            $table->string('sunat_urbanizacion', 100)->nullable()->after('sunat_distrito');
            $table->string('sunat_direccion_fiscal')->nullable()->after('sunat_urbanizacion');
            $table->string('sunat_codigo_pais', 2)->default('PE')->after('sunat_direccion_fiscal');

            // Certificado digital + credenciales SOL.
            // Los campos de credenciales son TEXT porque se cifran con el cast
            // `encrypted` de Eloquent (~250-450 chars tras AES-256-CBC + base64).
            $table->string('sunat_certificado_path')->nullable()->after('sunat_codigo_pais');
            $table->text('sunat_certificado_password')->nullable()->after('sunat_certificado_path');
            $table->string('sunat_sol_usuario', 60)->nullable()->after('sunat_certificado_password');
            $table->text('sunat_sol_clave')->nullable()->after('sunat_sol_usuario');

            // Entorno
            $table->enum('sunat_modo', ['beta', 'produccion'])->default('beta')->after('sunat_sol_clave');
            $table->string('sunat_endpoint')->nullable()->after('sunat_modo');
            $table->boolean('sunat_activo')->default(false)->after('sunat_endpoint');

            // Tributarios Perú
            $table->decimal('sunat_igv_porcentaje', 5, 2)->default(18.00)->after('sunat_activo');

            // Series electrónicas por defecto
            $table->string('sunat_serie_factura', 4)->default('F001')->after('sunat_igv_porcentaje');
            $table->string('sunat_serie_boleta', 4)->default('B001')->after('sunat_serie_factura');
            $table->string('sunat_serie_nota_credito_factura', 4)->default('FC01')->after('sunat_serie_boleta');
            $table->string('sunat_serie_nota_credito_boleta', 4)->default('BC01')->after('sunat_serie_nota_credito_factura');
            $table->string('sunat_serie_nota_debito_factura', 4)->default('FD01')->after('sunat_serie_nota_credito_boleta');
            $table->string('sunat_serie_nota_debito_boleta', 4)->default('BD01')->after('sunat_serie_nota_debito_factura');
        });
    }

    public function down(): void
    {
        Schema::table('configuracion', function (Blueprint $table) {
            $table->dropColumn([
                'sunat_ruc', 'sunat_razon_social', 'sunat_nombre_comercial',
                'sunat_ubigeo', 'sunat_departamento', 'sunat_provincia', 'sunat_distrito',
                'sunat_urbanizacion', 'sunat_direccion_fiscal', 'sunat_codigo_pais',
                'sunat_certificado_path', 'sunat_certificado_password',
                'sunat_sol_usuario', 'sunat_sol_clave',
                'sunat_modo', 'sunat_endpoint', 'sunat_activo', 'sunat_igv_porcentaje',
                'sunat_serie_factura', 'sunat_serie_boleta',
                'sunat_serie_nota_credito_factura', 'sunat_serie_nota_credito_boleta',
                'sunat_serie_nota_debito_factura', 'sunat_serie_nota_debito_boleta',
            ]);
        });
    }
};
