<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique();
            $table->string('codigo_barras', 50)->nullable()->index();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->foreignId('categoria_id')->nullable()->constrained('categorias')->nullOnDelete();

            // Tipo: materia_prima (ingrediente), elaborado (con receta), simple (compra-venta)
            $table->enum('tipo', ['materia_prima', 'elaborado', 'simple'])->default('simple');

            // Unidades
            $table->string('unidad_medida', 20)->default('unidad'); // unidad, kg, g, l, ml
            $table->decimal('formato', 10, 3)->default(1); // ej: 1 barra = 0.250 kg

            // Precios
            $table->decimal('precio_compra', 10, 4)->default(0);
            $table->decimal('precio_venta', 10, 4)->default(0);
            $table->decimal('precio_coste', 10, 4)->default(0); // calculado
            $table->decimal('margen', 5, 2)->default(0);
            $table->decimal('iva', 5, 2)->default(10.00);

            // Stock
            $table->decimal('stock_actual', 10, 3)->default(0);
            $table->decimal('stock_minimo', 10, 3)->default(0);
            $table->decimal('stock_optimo', 10, 3)->default(0);
            $table->boolean('controla_stock')->default(true);

            // Otros
            $table->foreignId('proveedor_id')->nullable();
            $table->integer('dias_caducidad')->nullable();
            $table->boolean('vende_tpv')->default(true);
            $table->string('imagen')->nullable();
            $table->string('color_tpv', 10)->nullable();
            $table->boolean('activo')->default(true);
            $table->integer('orden_tpv')->default(0);

            $table->timestamps();
        });

        // Tabla de recetas (ingredientes de productos elaborados)
        Schema::create('recetas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->foreignId('ingrediente_id')->constrained('productos')->cascadeOnDelete();
            $table->decimal('cantidad', 10, 4);
            $table->string('unidad', 20)->default('g');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recetas');
        Schema::dropIfExists('productos');
    }
};
