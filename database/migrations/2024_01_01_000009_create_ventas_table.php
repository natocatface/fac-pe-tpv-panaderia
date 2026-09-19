<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 30)->unique();
            $table->enum('tipo_documento', ['ticket', 'factura', 'presupuesto', 'albaran'])->default('ticket');
            $table->string('serie', 10)->nullable();
            $table->integer('correlativo')->nullable();

            $table->dateTime('fecha');
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('sesion_caja_id')->nullable();

            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('descuento', 12, 2)->default(0);
            $table->decimal('base_imponible', 12, 2)->default(0);
            $table->decimal('impuestos', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            $table->enum('forma_pago', ['efectivo', 'tarjeta', 'transferencia', 'bizum', 'mixto', 'credito'])->default('efectivo');
            $table->decimal('importe_efectivo', 12, 2)->default(0);
            $table->decimal('importe_tarjeta', 12, 2)->default(0);
            $table->decimal('importe_otros', 12, 2)->default(0);
            $table->decimal('cambio', 12, 2)->default(0);

            $table->enum('estado', ['borrador', 'emitida', 'pagada', 'anulada', 'devuelta'])->default('emitida');
            $table->text('observaciones')->nullable();
            $table->json('datos_cliente')->nullable(); // copia de datos al momento de la venta

            $table->timestamps();

            $table->index(['fecha', 'tipo_documento']);
        });

        Schema::create('venta_lineas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->constrained('ventas')->cascadeOnDelete();
            $table->foreignId('producto_id')->nullable()->constrained('productos')->nullOnDelete();
            $table->string('descripcion');
            $table->decimal('cantidad', 10, 3)->default(1);
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
        Schema::dropIfExists('venta_lineas');
        Schema::dropIfExists('ventas');
    }
};
