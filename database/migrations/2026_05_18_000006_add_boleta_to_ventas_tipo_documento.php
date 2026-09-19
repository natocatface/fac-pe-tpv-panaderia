<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Añade el valor 'boleta' al ENUM `tipo_documento` de `ventas`.
 * MySQL no permite ALTER COLUMN ENUM con Doctrine en Laravel, así que
 * usamos SQL crudo.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE ventas MODIFY COLUMN tipo_documento "
            . "ENUM('ticket','factura','boleta','presupuesto','albaran') "
            . "NOT NULL DEFAULT 'ticket'");
    }

    public function down(): void
    {
        DB::statement("UPDATE ventas SET tipo_documento='factura' WHERE tipo_documento='boleta'");
        DB::statement("ALTER TABLE ventas MODIFY COLUMN tipo_documento "
            . "ENUM('ticket','factura','presupuesto','albaran') "
            . "NOT NULL DEFAULT 'ticket'");
    }
};
