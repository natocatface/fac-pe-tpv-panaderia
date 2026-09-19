<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracion', function (Blueprint $table) {
            $table->id();
            // Datos empresa
            $table->string('nombre_empresa')->default('Mi Panadería');
            $table->string('razon_social')->nullable();
            $table->string('cif_nif', 30)->nullable();
            $table->string('direccion')->nullable();
            $table->string('codigo_postal', 10)->nullable();
            $table->string('ciudad')->nullable();
            $table->string('provincia')->nullable();
            $table->string('pais')->default('España');
            $table->string('telefono', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('web')->nullable();
            $table->string('logo')->nullable();

            // Configuración regional / formato
            $table->string('moneda_codigo', 5)->default('EUR');
            $table->string('moneda_simbolo', 5)->default('€');
            $table->string('moneda_posicion', 10)->default('derecha'); // izquierda | derecha
            $table->char('separador_miles', 1)->default('.');
            $table->char('separador_decimales', 1)->default(',');
            $table->tinyInteger('decimales')->default(2);
            $table->string('formato_fecha', 20)->default('d/m/Y');
            $table->string('formato_hora', 20)->default('H:i');
            $table->string('zona_horaria')->default('Europe/Madrid');
            $table->string('idioma', 10)->default('es');

            // Impuestos
            $table->boolean('impuestos_activos')->default(true);
            $table->boolean('precios_con_impuestos')->default(true);
            $table->decimal('iva_general', 5, 2)->default(21.00);
            $table->decimal('iva_reducido', 5, 2)->default(10.00);
            $table->decimal('iva_superreducido', 5, 2)->default(4.00);

            // Documentos
            $table->string('serie_ticket', 10)->default('T');
            $table->integer('siguiente_ticket')->default(1);
            $table->string('serie_factura', 10)->default('F');
            $table->integer('siguiente_factura')->default(1);
            $table->string('serie_presupuesto', 10)->default('P');
            $table->integer('siguiente_presupuesto')->default(1);
            $table->string('serie_albaran', 10)->default('A');
            $table->integer('siguiente_albaran')->default(1);
            $table->text('pie_ticket')->nullable();
            $table->text('pie_factura')->nullable();

            // Operativa
            $table->boolean('stock_negativo')->default(false);
            $table->boolean('descontar_stock_venta')->default(true);
            $table->boolean('alertas_stock')->default(true);
            $table->integer('dias_aviso_caducidad')->default(7);

            // Tema visual
            $table->string('tema_color', 20)->default('panaderia'); // panaderia, dark, light
            $table->string('color_principal', 20)->default('#C8763D');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion');
    }
};
