<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Líneas de detalle de cada pedido.
     *
     * CAMBIO ARQUITECTURAL:
     *   ANTES: producto_id → productos (tabla plana)
     *   AHORA: variante_id → producto_variantes (la tabla reina con stock)
     *
     * Guardamos snapshots del nombre y SKU para preservar historial
     * incluso si la variante es editada o eliminada en el futuro.
     *
     * Nota sobre FK: variante_id no usa constrained() porque producto_variantes
     * se crea en una migración posterior (2026_06_25). Eloquent gestiona
     * la relación correctamente sin el constraint formal.
     */
    public function up(): void
    {
        Schema::create('pedido_detalles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pedido_id')
                  ->constrained('pedidos')
                  ->onDelete('cascade');

            $table->enum('tipo_producto', ['Inventario', 'Libre'])->default('Inventario');

            // Variante vendida (Inventario)
            $table->unsignedBigInteger('producto_variante_id')->nullable();

            // Ítem libre
            $table->string('nombre_libre')->nullable();
            $table->text('descripcion_libre')->nullable();

            // Snapshots históricos (pueden ser null para ítems libres)
            $table->string('nombre_snapshot')->nullable();
            $table->string('sku_snapshot')->nullable();

            $table->integer('cantidad')->default(1);
            $table->decimal('precio_unitario', 10, 2)->default(0);
            $table->decimal('precio_venta', 10, 2)->default(0);
            $table->decimal('subtotal', 10, 2)->default(0);

            $table->timestamps();

            // Índice para búsquedas por variante (sin FK formal)
            $table->index('producto_variante_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedido_detalles');
    }
};
