<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla "Hijo" — La variante real que se vende.
     *
     * Aquí vive TODO lo que importa para operar:
     *   - SKU único e identificable
     *   - Atributos flexibles (JSON) para soportar cualquier tipo de producto
     *   - Costo y precio de venta
     *   - El trío vital de stock: físico / reservado / disponible (calculado)
     *
     * stock_disponible = stock_fisico - stock_reservado  (NO tiene columna, es un accesor)
     */
    public function up(): void
    {
        Schema::create('producto_variantes', function (Blueprint $table) {
            $table->id();

            // Padre — el blank al que pertenece esta variante
            $table->foreignId('producto_id')
                  ->constrained('productos')
                  ->onDelete('cascade'); // Si se elimina el blank, se eliminan sus variantes

            // Identificador comercial único
            $table->string('sku')->unique(); // Ej. "CAM-NEG-M"

            /**
             * Atributos como JSON — la decisión correcta para un catálogo mixto:
             *   Camisa:   {"color": "Negro", "talla": "M"}
             *   Taza:     {"capacidad": "11oz"}
             *   Cobertor: {"modelo": "Samsung A15", "material": "Antigolpe"}
             *   Llavero:  {"forma": "Corazón", "color": "Dorado"}
             *
             * Eloquent castea esto automáticamente con `'atributos' => 'array'` en el modelo.
             */
            $table->json('atributos')->nullable();

            // Finanzas de la variante
            $table->decimal('costo', 10, 2)->default(0);  // Precio de compra/producción
            $table->decimal('precio', 10, 2)->default(0); // Precio de venta al cliente

            // ── El trío vital de stock ──────────────────────────────────────────
            $table->integer('stock_fisico')->default(0);     // Lo que hay físicamente en bodega
            $table->integer('stock_reservado')->default(0);  // Comprometido en pedidos activos
            $table->integer('stock_minimo')->default(0);     // Umbral de alerta de bajo stock
            // stock_disponible = stock_fisico - stock_reservado → Calculado en el modelo

            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_variantes');
    }
};
