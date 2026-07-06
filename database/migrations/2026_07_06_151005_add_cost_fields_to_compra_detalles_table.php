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
            $table->decimal('costo_proveedor', 10, 2)->default(0.00);
            $table->decimal('costo_extra', 10, 2)->default(0.00);
            $table->decimal('costo_total', 10, 2)->default(0.00);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('compra_detalles', function (Blueprint $table) {
            $table->dropColumn(['costo_proveedor', 'costo_extra', 'costo_total']);
        });
    }
};
