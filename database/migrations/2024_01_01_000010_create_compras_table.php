<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compras', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 30)->unique();
            $table->string('referencia_proveedor', 50)->nullable();
            $table->dateTime('fecha');
            $table->dateTime('fecha_recepcion')->nullable();
            $table->foreignId('proveedor_id')->constrained('proveedores');
            $table->foreignId('user_id')->constrained('users');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('descuento', 12, 2)->default(0);
            $table->decimal('impuestos', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->enum('estado', ['borrador', 'pendiente', 'recibida', 'parcial', 'cancelada'])->default('pendiente');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });

        Schema::create('compra_lineas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compra_id')->constrained('compras')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos');
            $table->string('descripcion');
            $table->decimal('cantidad', 10, 3);
            $table->decimal('cantidad_recibida', 10, 3)->default(0);
            $table->decimal('precio_unitario', 10, 4);
            $table->decimal('descuento', 5, 2)->default(0);
            $table->decimal('iva', 5, 2)->default(0);
            $table->decimal('subtotal', 12, 2);
            $table->decimal('total', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compra_lineas');
        Schema::dropIfExists('compras');
    }
};
