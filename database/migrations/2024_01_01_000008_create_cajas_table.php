<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cajas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->boolean('activa')->default(true);
            $table->timestamps();
        });

        Schema::create('sesiones_caja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caja_id')->constrained('cajas');
            $table->foreignId('user_id')->constrained('users');
            $table->dateTime('apertura');
            $table->dateTime('cierre')->nullable();
            $table->decimal('saldo_inicial', 12, 2)->default(0);
            $table->decimal('saldo_calculado', 12, 2)->default(0);
            $table->decimal('saldo_contado', 12, 2)->nullable();
            $table->decimal('descuadre', 12, 2)->default(0);
            $table->text('observaciones')->nullable();
            $table->enum('estado', ['abierta', 'cerrada'])->default('abierta');
            $table->timestamps();
        });

        Schema::create('movimientos_caja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sesion_caja_id')->constrained('sesiones_caja');
            $table->foreignId('user_id')->constrained('users');
            $table->enum('tipo', ['entrada', 'salida']);
            $table->string('concepto');
            $table->decimal('importe', 12, 2);
            $table->dateTime('fecha');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_caja');
        Schema::dropIfExists('sesiones_caja');
        Schema::dropIfExists('cajas');
    }
};
