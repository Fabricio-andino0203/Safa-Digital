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
        Schema::table('compras', function (Blueprint $table) {
            if (!Schema::hasColumn('compras', 'pedido_id')) {
                $table->foreignId('pedido_id')->nullable()->after('numero_orden')->constrained('pedidos')->nullOnDelete();
            }
            if (!Schema::hasColumn('compras', 'extras')) {
                $table->json('extras')->nullable()->after('notas');
            }
        });

        Schema::table('compra_detalles', function (Blueprint $table) {
            if (!Schema::hasColumn('compra_detalles', 'nombre_snapshot')) {
                $table->string('nombre_snapshot')->nullable()->after('producto_variante_id');
            }
        });

        Schema::table('categorias', function (Blueprint $table) {
            if (!Schema::hasColumn('categorias', 'es_subcontratado')) {
                $table->boolean('es_subcontratado')->default(false)->after('descripcion');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('compras', function (Blueprint $table) {
            $table->dropForeign(['pedido_id']);
            $table->dropColumn(['pedido_id', 'extras']);
        });

        Schema::table('compra_detalles', function (Blueprint $table) {
            $table->dropColumn(['nombre_snapshot']);
        });

        Schema::table('categorias', function (Blueprint $table) {
            $table->dropColumn(['es_subcontratado']);
        });
    }
};
