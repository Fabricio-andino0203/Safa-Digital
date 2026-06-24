<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cada fila es un ticket de venta completo procesado desde el POS.
     * Se vincula a una sesión de caja abierta y, opcionalmente, a un cliente.
     */
    public function up(): void
    {
        Schema::create('ventas_pos', function (Blueprint $table) {
            $table->id();

            // Sesión de caja en la que ocurrió la venta
            $table->foreignId('caja_sesion_id')
                  ->constrained('caja_sesiones')
                  ->onDelete('cascade');

            // Cliente opcional (venta rápida sin cliente registrado)
            $table->foreignId('cliente_id')
                  ->nullable()
                  ->constrained('clientes')
                  ->onDelete('set null');

            // Montos
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('descuento', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);

            // Pago
            // Interno: efectivo, transferencia, tarjeta, mixto
            // En UI se mostrará según lo que corresponda
            $table->enum('metodo_pago', ['efectivo', 'transferencia', 'tarjeta', 'mixto'])
                  ->default('efectivo');

            // Para pago en efectivo: cuánto entregó el cliente y el cambio
            $table->decimal('monto_entregado', 10, 2)->nullable();
            $table->decimal('cambio', 10, 2)->nullable();

            // Estado del ticket
            $table->enum('estado', ['completada', 'borrador', 'cancelada'])
                  ->default('completada');

            $table->text('notas')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ventas_pos');
    }
};
