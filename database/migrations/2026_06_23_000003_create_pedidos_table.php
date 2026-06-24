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
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->enum('estado', [
                'Pendiente', 'Diseño', 'Aprobación', 'Producción', 'Listo', 'Entregado', 'Cancelado'
            ])->default('Pendiente');
            
            // Finanzas simples sin créditos
            $table->decimal('total', 10, 2)->default(0);
            $table->decimal('adelanto', 10, 2)->default(0);
            $table->decimal('saldo', 10, 2)->default(0); // Se calculará: total - adelanto
            
            $table->date('fecha_entrega')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
