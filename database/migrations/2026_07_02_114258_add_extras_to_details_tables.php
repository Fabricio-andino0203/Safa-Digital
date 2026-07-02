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
        Schema::table('pedido_detalles', function (Blueprint $table) {
            $table->json('extras')->nullable()->after('subtotal');
        });

        Schema::table('cotizacion_detalles', function (Blueprint $table) {
            $table->json('extras')->nullable()->after('subtotal');
        });

        if (Schema::hasTable('venta_pos_detalles')) {
            Schema::table('venta_pos_detalles', function (Blueprint $table) {
                $table->json('extras')->nullable()->after('subtotal');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedido_detalles', function (Blueprint $table) {
            $table->dropColumn('extras');
        });

        Schema::table('cotizacion_detalles', function (Blueprint $table) {
            $table->dropColumn('extras');
        });

        if (Schema::hasTable('venta_pos_detalles')) {
            Schema::table('venta_pos_detalles', function (Blueprint $table) {
                $table->dropColumn('extras');
            });
        }
    }
};
