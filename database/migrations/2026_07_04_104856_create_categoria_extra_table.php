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
        Schema::create('categoria_extra', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_id')->constrained('categorias')->cascadeOnDelete();
            $table->foreignId('extra_id')->constrained('producto_extras')->cascadeOnDelete();
            $table->timestamps();
        });

        // Script de Vinculación Automática (Tarea 1.3)
        try {
            $categorias = \App\Models\Categoria::where('nombre', 'LIKE', '%Camisa%')
                ->orWhere('nombre', 'LIKE', '%Camiseta%')
                ->get();

            $extras = \App\Models\ProductoExtra::where('nombre', 'LIKE', '%DTF%')
                ->orWhere('nombre', 'LIKE', '%Sublimación%')
                ->orWhere('nombre', 'LIKE', '%Sublimacion%')
                ->pluck('id');

            if ($categorias->isNotEmpty() && $extras->isNotEmpty()) {
                foreach ($categorias as $cat) {
                    $cat->extras()->sync($extras);
                }
            }
        } catch (\Exception $e) {
            // Ignorar errores si la base de datos no está poblada
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categoria_extra');
    }
};
