<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('venta_pos_detalles')) {
            Schema::table('venta_pos_detalles', function (Blueprint $table) {
                if (!Schema::hasColumn('venta_pos_detalles', 'costo_unitario')) {
                    $table->decimal('costo_unitario', 10, 2)->default(0.00)->after('precio_unitario');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('venta_pos_detalles')) {
            Schema::table('venta_pos_detalles', function (Blueprint $table) {
                if (Schema::hasColumn('venta_pos_detalles', 'costo_unitario')) {
                    $table->dropColumn('costo_unitario');
                }
            });
        }
    }
};
