<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Numerador atómico de comprobantes electrónicos.
 *
 * SUNAT exige que cada serie sea correlativa, sin huecos. Para evitar
 * carreras concurrentes el controlador debe incrementar dentro de una
 * transacción usando `lockForUpdate`.
 *
 * Reglas de la serie (4 caracteres):
 *  - Factura: empieza con F   (F001, F002, ...)
 *  - Boleta:  empieza con B   (B001, B002, ...)
 *  - Nota crédito de factura: FC.. ; de boleta: BC..
 *  - Nota débito  de factura: FD.. ; de boleta: BD..
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('series_correlativos', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_comprobante', 2);     // 01, 03, 07, 08
            $table->string('serie', 4);                // F001, B001, FC01, ...
            $table->unsignedInteger('ultimo_correlativo')->default(0);
            $table->boolean('activo')->default(true);
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->unique(['tipo_comprobante', 'serie']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('series_correlativos');
    }
};
