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
        Schema::table('compra_detalles', function (Blueprint $table) {
            $table->unsignedBigInteger('producto_variante_id')->nullable()->change();
        });

        Schema::table('pedido_detalles', function (Blueprint $table) {
            if (!Schema::hasColumn('pedido_detalles', 'costo_unitario')) {
                $table->decimal('costo_unitario', 10, 2)->default(0)->after('cantidad');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('compra_detalles', function (Blueprint $table) {
            $table->unsignedBigInteger('producto_variante_id')->nullable(false)->change();
        });

        Schema::table('pedido_detalles', function (Blueprint $table) {
            if (Schema::hasColumn('pedido_detalles', 'costo_unitario')) {
                $table->dropColumn('costo_unitario');
            }
        });
    }
};
