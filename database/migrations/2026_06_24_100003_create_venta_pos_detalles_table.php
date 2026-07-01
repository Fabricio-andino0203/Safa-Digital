<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Líneas individuales de cada ticket de venta POS.
     *
     * CAMBIO ARQUITECTURAL:
     *   ANTES: producto_id → productos (tabla plana)
     *   AHORA: variante_id → producto_variantes (con stock físico)
     *
     * Al procesar una venta POS, el PosController descuenta directamente
     * stock_fisico de la variante (sin pasar por reserva, es venta directa).
     *
     * Nota: variante_id sin constrained() por orden de migraciones.
     */
    public function up(): void
    {
        Schema::create('venta_pos_detalles', function (Blueprint $table) {
            $table->id();

            // Ticket al que pertenece esta línea
            $table->foreignId('venta_pos_id')
                  ->constrained('ventas_pos')
                  ->onDelete('cascade');

            // Variante vendida (puede ser null si fue eliminada)
            $table->unsignedBigInteger('variante_id')->nullable();

            // Snapshot en el momento de la venta — para historial inmutable
            $table->string('nombre_snapshot');    // Ej. "Camisa Oversize — Negro / M"
            $table->string('sku_snapshot');       // Ej. "CAM-NEG-M"

            $table->integer('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('descuento_linea', 10, 2)->default(0);
            $table->decimal('subtotal', 10, 2); // (precio_unitario * cantidad) - descuento_linea

            $table->timestamps();

            $table->index('variante_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venta_pos_detalles');
    }
};
