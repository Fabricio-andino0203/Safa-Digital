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
            $table->string('numero_orden', 20)->unique();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            
            $table->enum('prioridad', ['Normal', 'Urgente', 'Alta Prioridad'])->default('Normal');
            $table->enum('estado', [
                'Pendiente', 'Diseño', 'Esperando Aprobación', 'Producción', 
                'Pausado', 'Listo para Entrega', 'Entregado', 'Cancelado'
            ])->default('Pendiente');
            
            // Finanzas
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('descuento', 10, 2)->default(0);
            $table->decimal('total_pedido', 10, 2)->default(0);
            $table->decimal('total_abonado', 10, 2)->default(0);
            $table->decimal('saldo_pendiente', 10, 2)->default(0);
            
            $table->date('fecha_estimada_entrega')->nullable();
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
