<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Campos SUNAT para la tabla `ventas`:
 *
 *  - sunat_tipo_comprobante: catálogo 01 de SUNAT
 *      01 = Factura electrónica
 *      03 = Boleta de venta electrónica
 *      07 = Nota de crédito
 *      08 = Nota de débito
 *  - hash, xml_path, cdr_path: artefactos firmados y respuesta SUNAT.
 *  - estado_sunat: pendiente | enviado | aceptado | rechazado | observado | anulado | error
 *  - codigo_sunat / mensaje_sunat: respuesta literal del CDR.
 *  - venta_modifica_id + codigo_motivo_nota: referencia (catálogos 09 y 10)
 *    cuando esta venta es una nota de crédito o débito que afecta a otra.
 *  - moneda: PEN (Soles) — el TPV puede operar también en otras monedas.
 *
 * Notas:
 *   - No se modifica el enum `tipo_documento` original (ticket/factura/...)
 *     para no romper consultas existentes; el discriminador SUNAT es nuevo.
 *   - El `numero` original sigue siendo un identificador interno único; la
 *     numeración SUNAT vive en (serie, correlativo) y `series_correlativos`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->string('sunat_tipo_comprobante', 2)->nullable()->after('correlativo');
            $table->string('moneda', 3)->default('PEN')->after('sunat_tipo_comprobante');

            $table->string('hash', 100)->nullable()->after('moneda');
            $table->string('xml_path')->nullable()->after('hash');
            $table->string('cdr_path')->nullable()->after('xml_path');
            $table->string('pdf_path')->nullable()->after('cdr_path');

            $table->enum('estado_sunat', [
                'no_aplica', 'pendiente', 'enviado', 'aceptado',
                'rechazado', 'observado', 'anulado', 'error',
            ])->default('no_aplica')->after('pdf_path');
            $table->string('codigo_sunat', 10)->nullable()->after('estado_sunat');
            $table->text('mensaje_sunat')->nullable()->after('codigo_sunat');
            $table->dateTime('fecha_envio_sunat')->nullable()->after('mensaje_sunat');

            // Para notas de crédito / débito
            $table->foreignId('venta_modifica_id')->nullable()->after('fecha_envio_sunat')
                ->constrained('ventas')->nullOnDelete();
            $table->string('codigo_motivo_nota', 3)->nullable()->after('venta_modifica_id');
            $table->string('motivo_nota')->nullable()->after('codigo_motivo_nota');

            $table->index(['sunat_tipo_comprobante', 'serie', 'correlativo'], 'idx_ventas_sunat_doc');
            $table->index('estado_sunat', 'idx_ventas_estado_sunat');
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropForeign(['venta_modifica_id']);
            $table->dropIndex('idx_ventas_sunat_doc');
            $table->dropIndex('idx_ventas_estado_sunat');
            $table->dropColumn([
                'sunat_tipo_comprobante', 'moneda',
                'hash', 'xml_path', 'cdr_path', 'pdf_path',
                'estado_sunat', 'codigo_sunat', 'mensaje_sunat', 'fecha_envio_sunat',
                'venta_modifica_id', 'codigo_motivo_nota', 'motivo_nota',
            ]);
        });
    }
};
