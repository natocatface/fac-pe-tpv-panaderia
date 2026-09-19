<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->enum('tipo', ['entrada', 'salida', 'merma', 'ajuste', 'elaboracion', 'venta', 'compra', 'devolucion']);
            $table->decimal('cantidad', 10, 3); // positivo o negativo
            $table->decimal('stock_anterior', 10, 3);
            $table->decimal('stock_nuevo', 10, 3);
            $table->decimal('precio_unitario', 10, 4)->default(0);
            $table->string('motivo')->nullable();
            $table->text('observaciones')->nullable();

            // Referencia a origen (venta, compra, etc.)
            $table->string('referencia_tipo')->nullable();
            $table->unsignedBigInteger('referencia_id')->nullable();

            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->dateTime('fecha');
            $table->timestamps();

            $table->index(['producto_id', 'fecha']);
            $table->index(['referencia_tipo', 'referencia_id']);
        });

        Schema::create('mermas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos');
            $table->decimal('cantidad', 10, 3);
            $table->enum('motivo', ['caducidad', 'rotura', 'mal_estado', 'devolucion_cliente', 'error_elaboracion', 'otros'])->default('caducidad');
            $table->decimal('coste', 12, 2)->default(0);
            $table->text('observaciones')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->dateTime('fecha');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mermas');
        Schema::dropIfExists('movimientos_stock');
    }
};
