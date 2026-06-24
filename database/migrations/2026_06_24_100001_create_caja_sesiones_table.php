<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Controla los turnos/sesiones de trabajo del operador en caja.
     * Una sesión debe estar ABIERTA para poder procesar ventas POS.
     */
    public function up(): void
    {
        Schema::create('caja_sesiones', function (Blueprint $table) {
            $table->id();

            // El operador que abre el turno
            $table->foreignId('usuario_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            // Control del turno
            $table->enum('estado', ['abierta', 'cerrada'])->default('abierta');

            // Conteo inicial del efectivo físico antes de comenzar
            $table->decimal('monto_inicial', 10, 2)->default(0);

            // Fechas de apertura y cierre
            $table->timestamp('fecha_apertura')->useCurrent();
            $table->timestamp('fecha_cierre')->nullable();

            // Corte de caja: lo que el sistema esperaba vs. lo contado físicamente
            $table->decimal('monto_final_esperado', 10, 2)->nullable();
            $table->decimal('monto_contado_fisico', 10, 2)->nullable();
            $table->decimal('diferencia', 10, 2)->nullable(); // contado - esperado (puede ser negativo)

            $table->text('notas')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('caja_sesiones');
    }
};
