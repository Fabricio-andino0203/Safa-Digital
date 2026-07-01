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
        Schema::table('pedidos', function (Blueprint $table) {
            $table->index('estado');
        });

        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->index('estado');
        });

        Schema::table('caja_movimientos', function (Blueprint $table) {
            $table->index('tipo');
            $table->index('fecha');
        });

        Schema::table('clientes', function (Blueprint $table) {
            $table->index('nombre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropIndex(['estado']);
        });

        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->dropIndex(['estado']);
        });

        Schema::table('caja_movimientos', function (Blueprint $table) {
            $table->dropIndex(['tipo']);
            $table->dropIndex(['fecha']);
        });

        Schema::table('clientes', function (Blueprint $table) {
            $table->dropIndex(['nombre']);
        });
    }
};
