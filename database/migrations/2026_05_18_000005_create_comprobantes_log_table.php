<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Auditoría de todas las interacciones con SUNAT.
 *
 * Cada intento de envío / consulta / anulación queda registrado, de modo
 * que podamos reconstruir el historial de un comprobante aunque se
 * reintente o se anule. Indispensable ante una fiscalización SUNAT.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comprobantes_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->constrained('ventas')->cascadeOnDelete();
            $table->enum('accion', [
                'enviar', 'consultar', 'anular', 'resumen_diario',
                'comunicacion_baja', 'reintento', 'firmado_local',
            ]);
            $table->string('modo', 15)->default('beta');     // beta | produccion
            $table->boolean('exitoso')->default(false);
            $table->string('codigo_respuesta', 10)->nullable();
            $table->text('mensaje')->nullable();
            $table->longText('request_xml')->nullable();
            $table->longText('response_xml')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['venta_id', 'accion']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comprobantes_log');
    }
};
