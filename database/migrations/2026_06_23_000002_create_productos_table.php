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
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->enum('tipo', ['producto', 'material']); // Para diferenciar inventario
            $table->string('sku')->nullable();
            $table->integer('stock')->default(0);
            $table->integer('stock_minimo')->default(0);
            $table->decimal('precio', 10, 2)->nullable(); // Precio de venta (aplica más a 'producto')
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
