<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla de categorías plana (sin jerarquía).
     * Ej: Camisas, Tazas, Cobertores, Llaveros, Stickers.
     *
     * Timestamp anterior a 'productos' para garantizar que exista
     * antes de que la FK en productos la referencie.
     */
    public function up(): void
    {
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');               // Ej. "Camisas", "Tazas"
            $table->string('icono')->nullable();    // Heroicon/Lucide slug para UI (ej. 'shirt')
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categorias');
    }
};
