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
        Schema::create('cotizacion_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cotizacion_id')->constrained('cotizaciones')->onDelete('cascade');
            $table->enum('tipo_producto', ['Inventario', 'Libre']);
            
            // Relación a variantes si es de inventario
            $table->foreignId('producto_variante_id')->nullable()->constrained('producto_variantes')->onDelete('set null');
            
            // Campos si es libre
            $table->string('nombre_libre')->nullable();
            $table->text('descripcion_libre')->nullable();
            $table->decimal('costo_libre', 10, 2)->default(0);
            
            // Campos comunes
            $table->decimal('precio_venta', 10, 2);
            $table->integer('cantidad')->default(1);
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cotizacion_detalles');
    }
};
