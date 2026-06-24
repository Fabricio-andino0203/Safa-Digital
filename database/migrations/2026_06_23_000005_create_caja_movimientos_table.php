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
        Schema::create('caja_movimientos', function (Blueprint $table) {
            $table->id();
            // Lógica interna: ingreso/egreso. En Blade será: Depósito/Retiro
            $table->enum('tipo', ['ingreso', 'egreso']); 
            $table->decimal('monto', 10, 2);
            $table->string('concepto');
            $table->string('referencia')->nullable();
            $table->foreignId('pedido_id')->nullable()->constrained('pedidos')->onDelete('set null'); // Relación opcional
            $table->date('fecha');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caja_movimientos');
    }
};
