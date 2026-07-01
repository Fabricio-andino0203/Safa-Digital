<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cortes_caja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('fecha_apertura');
            $table->timestamp('fecha_cierre');
            $table->decimal('fondo_inicial', 10, 2);
            $table->decimal('ventas_efectivo', 10, 2);
            $table->decimal('total_esperado', 10, 2);
            $table->decimal('efectivo_real', 10, 2);
            $table->decimal('diferencia', 10, 2);
            $table->decimal('retiro_tesoreria', 10, 2);
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cortes_caja');
    }
};
