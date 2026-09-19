<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 30)->nullable()->unique();
            $table->string('nombre');
            $table->string('razon_social')->nullable();
            $table->string('cif_nif', 30)->nullable();
            $table->string('direccion')->nullable();
            $table->string('codigo_postal', 10)->nullable();
            $table->string('ciudad')->nullable();
            $table->string('provincia')->nullable();
            $table->string('pais')->default('España');
            $table->string('telefono', 30)->nullable();
            $table->string('email')->nullable();
            $table->decimal('descuento', 5, 2)->default(0);
            $table->decimal('saldo', 12, 2)->default(0);
            $table->text('observaciones')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
