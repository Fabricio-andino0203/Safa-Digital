<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Configuracion;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Configuracion::firstOrCreate(
            ['llave' => 'favicon_ruta'],
            ['grupo' => 'sistema', 'valor' => 'favicon.ico', 'tipo' => 'imagen']
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Configuracion::where('llave', 'favicon_ruta')->delete();
    }
};
