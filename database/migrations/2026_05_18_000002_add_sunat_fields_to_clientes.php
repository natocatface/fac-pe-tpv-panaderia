<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Añade los campos exigidos por SUNAT al receptor del comprobante:
 *
 *  - tipo_documento_sunat: catálogo 06 de SUNAT
 *      6 = RUC
 *      1 = DNI
 *      4 = Carné de extranjería
 *      7 = Pasaporte
 *      A = Cédula diplomática de identidad
 *      0 = Sin documento (sólo para boletas < S/700)
 *  - numero_documento: 11 caracteres si RUC, 8 si DNI, etc.
 *  - direccion_fiscal: dirección fiscal completa del cliente (para facturas).
 *
 * Mantenemos los campos previos (cif_nif, direccion) para compatibilidad
 * con el resto del sistema; cuando exista numero_documento se usa éste.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('tipo_documento_sunat', 2)->nullable()->after('cif_nif');
            $table->string('numero_documento', 15)->nullable()->after('tipo_documento_sunat');
            $table->string('direccion_fiscal')->nullable()->after('direccion');

            $table->index(['tipo_documento_sunat', 'numero_documento'], 'idx_clientes_doc_sunat');
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropIndex('idx_clientes_doc_sunat');
            $table->dropColumn(['tipo_documento_sunat', 'numero_documento', 'direccion_fiscal']);
        });
    }
};
