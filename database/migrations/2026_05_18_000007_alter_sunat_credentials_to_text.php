<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Las credenciales SUNAT se persisten cifradas mediante el cast `encrypted`
 * de Eloquent. El payload cifrado (Laravel AES-256-CBC + JSON + base64) ronda
 * los 250-450 caracteres aunque el valor original sean 8. Por eso convertimos
 * estas columnas a TEXT para evitar truncamientos (SQLSTATE[22001] 1406).
 *
 * Usamos SQL crudo en lugar de Schema::change() para no depender de
 * doctrine/dbal.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE configuracion MODIFY COLUMN sunat_sol_clave TEXT NULL');
        DB::statement('ALTER TABLE configuracion MODIFY COLUMN sunat_certificado_password TEXT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE configuracion MODIFY COLUMN sunat_sol_clave VARCHAR(60) NULL');
        DB::statement('ALTER TABLE configuracion MODIFY COLUMN sunat_certificado_password VARCHAR(255) NULL');
    }
};
