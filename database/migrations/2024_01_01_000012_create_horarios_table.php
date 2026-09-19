<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fichajes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->dateTime('entrada');
            $table->dateTime('salida')->nullable();
            $table->decimal('horas', 6, 2)->default(0);
            $table->string('tipo', 30)->default('jornada'); // jornada, descanso, extra
            $table->text('observaciones')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'entrada']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fichajes');
    }
};
