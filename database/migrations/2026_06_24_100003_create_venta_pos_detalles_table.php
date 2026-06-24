<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Líneas individuales de cada ticket de venta.
     * Al procesar, descuenta automáticamente el stock en 'productos'.
     */
    public function up(): void
    {
        Schema::create('venta_pos_detalles', function (Blueprint $table) {
            $table->id();

            // Ticket al que pertenece esta línea
            $table->foreignId('venta_pos_id')
                  ->constrained('ventas_pos')
                  ->onDelete('cascade');

            // Producto vendido (guardamos snapshot del precio para historial)
            $table->foreignId('producto_id')
                  ->constrained('productos')
                  ->onDelete('restrict'); // No eliminar producto si tiene ventas

            // Snapshot en el momento de la venta
            $table->string('nombre_producto');          // guardado para historial
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('descuento_linea', 10, 2)->default(0);
            $table->decimal('subtotal', 10, 2);         // (precio_unitario * cantidad) - descuento_linea

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venta_pos_detalles');
    }
};
