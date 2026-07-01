<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla "Padre" — El Blank / Familia de productos.
     *
     * IMPORTANTE: Esta tabla NO tiene stock. Solo agrupa variantes bajo un nombre común.
     * Ej: "Camisa Oversize" es el Padre → sus variantes son "Negra M", "Blanca L", etc.
     *
     * La FK a 'categorias' funciona porque esa migración tiene timestamp anterior (2026_06_22).
     */
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();

            // Categoría a la que pertenece (lista plana, sin jerarquía)
            $table->foreignId('categoria_id')
                  ->nullable()
                  ->constrained('categorias')
                  ->onDelete('set null'); // Si se borra la categoría, el producto no desaparece

            $table->string('nombre');                // Ej. "Camisa Oversize", "Taza Sublimable"
            $table->text('descripcion')->nullable(); // Descripción general del blank
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
