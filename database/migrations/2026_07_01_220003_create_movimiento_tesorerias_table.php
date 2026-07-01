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
        Schema::create('movimiento_tesorerias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cuenta_id')->constrained('cuenta_financieras')->onDelete('restrict');
            $table->enum('tipo', ['ingreso', 'egreso']);
            $table->decimal('monto', 15, 2);
            $table->string('concepto');
            $table->string('referencia_modulo')->nullable();
            $table->foreignId('usuario_id')->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimiento_tesorerias');
    }
};
